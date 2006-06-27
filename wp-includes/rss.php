<?php

/* Much of the code in this file was taken from MagpieRSS
 * by Kellan Elliott-McCrea <kellan@protest.net> which is
 * released under the GPL license.
 *
 * The lastest version of MagpieRSS can be obtained from:
 * http://magpierss.sourceforge.net
 */

function fetch_rss($url) {
	$url = apply_filters('fetch_rss_url', $url);

	$feeder = new WP_Feeder();

	$feed = $feeder->get($url);

	$magpie = $feed->to_magpie();

	return $magpie;
}

class WP_Feeder {
	var $url, $http_client, $last_fetch, $wp_object_cache, $cache;
	var $redirects = 0;
	var $max_redirects = 3;
	var $cache_redirects = true;

	function WP_Feeder () {
		global $wp_object_cache;
		
		if ( $wp_object_cache->cache_enabled ) {
			$this->wp_object_cache = true;
		} else {
			$this->wp_object_cache = false;
			$this->cache = new RSSCache();
		}
	}

	function get ($url) {
		$cached = false;

		$feed = $this->cache_get($url);

		if ( is_object($feed) ) {
			$cached = true;
		} else {
			unset($feed);

			$this->fetch($url);

			$feed = new WP_Feed($this->http_client);
		}

		// Handle redirects
		if ( $feed->status >= 300 && $feed->status < 400 && $this->redirects < $this->max_redirects ) {
			++$this->redirects;

			if ( $this->cache_redirects && !$cached )
				$this->cache_set($url, $feed);

			return $this->get($feed->redirect_location);
		}

		if ( !$cached )
			$this->cache_set($url, $feed);

		return $feed;
	}

	function fetch ($url) {
		$this->last_fetch = $url;
		$parts = parse_url($url);
		$url = ($parts['path'] ? $parts['path'] : '/') . ($parts['query'] ? '?'.$parts['query'] : '');
		$this->http_client = new HttpClient('', 80);
		$this->http_client->handle_redirects = false;
		$this->http_client->host = $parts['host'];
		$this->http_client->port = $parts['port'] ? $parts['port'] : 80;
		$this->http_client->user_agent = 'WordPress ' . $GLOBALS['wp_version'] . ' Feed Client';
		$this->http_client->get($url);
	}
	
	function cache_get ($url) {
		if ( $this->wp_object_cache )
			return unserialize(wp_cache_get($url, 'rss'));

		return $this->cache->get($url);
	}
	
	function cache_set ($url, $object) {
		if ( $this->wp_object_cache )
			return wp_cache_set($url, serialize($object), 'rss', 3600);
		
		return $this->cache->set($url, $object);
	}
}

class RSSCache {
	var $BASE_CACHE = 'wp-content/cache';	// where the cache files are stored
	var $MAX_AGE	= 43200;  		// when are files stale, default twelve hours
	var $ERROR 		= '';			// accumulate error messages

	function RSSCache ($base='', $age='') {
		if ( $base ) {
			$this->BASE_CACHE = $base;
		}
		if ( $age ) {
			$this->MAX_AGE = $age;
		}

	}

	function set ($url, $rss) {
		global $wpdb;
		$cache_option = 'rss_' . $this->file_name( $url );
		$cache_timestamp = 'rss_' . $this->file_name( $url ) . '_ts';

		if ( !$wpdb->get_var("SELECT option_name FROM $wpdb->options WHERE option_name = '$cache_option'") )
			add_option($cache_option, '', '', 'no');
		if ( !$wpdb->get_var("SELECT option_name FROM $wpdb->options WHERE option_name = '$cache_timestamp'") )
			add_option($cache_timestamp, '', '', 'no');

		update_option($cache_option, $rss);
		update_option($cache_timestamp, time() );

		return $cache_option;
	}

	function get ($url) {
		$this->ERROR = "";
		$cache_option = 'rss_' . $this->file_name( $url );

		if ( ! get_option( $cache_option ) ) {
			$this->debug( 
				"Cache doesn't contain: $url (cache option: $cache_option)"
			);
			return 0;
		}

		$rss = get_option( $cache_option );

		return $rss;
	}

	function check_cache ( $url ) {
		$this->ERROR = "";
		$cache_option = $this->file_name( $url );
		$cache_timestamp = 'rss_' . $this->file_name( $url ) . '_ts';

		if ( $mtime = get_option($cache_timestamp) ) {
			// find how long ago the file was added to the cache
			// and whether that is longer then MAX_AGE
			$age = time() - $mtime;
			if ( $this->MAX_AGE > $age ) {
				// object exists and is current
				return 'HIT';
			}
			else {
				// object exists but is old
				return 'STALE';
			}
		}
		else {
			// object does not exist
			return 'MISS';
		}
	}

	function serialize ( $rss ) {
		return serialize( $rss );
	}

	function unserialize ( $data ) {
		return unserialize( $data );
	}

	function file_name ($url) {
		return md5( $url );
	}

	function error ($errormsg, $lvl=E_USER_WARNING) {
		// append PHP's error message if track_errors enabled
		if ( isset($php_errormsg) ) { 
			$errormsg .= " ($php_errormsg)";
		}
		$this->ERROR = $errormsg;
		if ( MAGPIE_DEBUG ) {
			trigger_error( $errormsg, $lvl);
		}
		else {
			error_log( $errormsg, 0);
		}
	}
			function debug ($debugmsg, $lvl=E_USER_NOTICE) {
		if ( MAGPIE_DEBUG ) {
			$this->error("MagpieRSS [debug] $debugmsg", $lvl);
		}
	}
}

class WP_Feed {
	var $status;
	var $raw_xml;
	var $last_updated;
	var $tree;
	var $items;
	var $children;

	var $parser;
	var $feed_type;
	var $feed_version;
	var $stack = array();

	function WP_Feed ($source)
	{
		# if PHP xml isn't compiled in, die
		#
		if (!function_exists('xml_parser_create')) {
			$this->error( "Failed to load PHP's XML Extension. " .
			"http://www.php.net/manual/en/ref.xml.php",
			E_USER_ERROR );
		}

		// Handle overloaded arg (string or HttpClient object)
		if ( is_object($source) ) {
			if ( $source->status >= 200 && $source->status < 300) {
				$this->etag = $source->headers['etag'];
				$this->last_modified = $source->headers['last-modified'];
				$source = $source->content;
			} else {
				$this->scour();
				$this->status = $source->status;
				$this->redirect_location = $source->headers->location;
				$this->bathe();
				return;
			}
		}

		list($parser, $source) = $this->create_parser($source, 'UTF-8', null, true);

		if (!is_resource($parser)) {
			$this->error( "Failed to create an instance of PHP's XML parser. " .
			"http://www.php.net/manual/en/ref.xml.php",
			E_USER_ERROR );
		}

		$this->parser = $parser;

		# pass in parser, and a reference to this object
		# setup handlers
		#
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'start_element', 'end_element');
		xml_set_character_data_handler( $this->parser, 'cdata');

		$status = xml_parse( $this->parser, $source );

		if (! $status ) {
			$errorcode = xml_get_error_code( $this->parser );
			if ( $errorcode != XML_ERROR_NONE ) {
				$xml_error = xml_error_string( $errorcode );
				$error_line = xml_get_current_line_number($this->parser);
				$error_col = xml_get_current_column_number($this->parser);
				$errormsg = "$xml_error at line $error_line, column $error_col";

				$this->error( $errormsg );
			}
		}

		// SUPER SLOPPY FEED DISCOVERY!! TO-DO: AXE THIS CRAP!!
		if ( !is_object($this->feed) || !method_exists($this->feed, 'to_xml') ) {
			if ( preg_match_all('/<link [^>]*href=([^ >]+)[^>]+>/i', $source, $matches) ) {
				$types = array('rss', 'atom');
				foreach ( $types as $type )
					foreach ( $matches[0] as $key => $link )
						if ( preg_match('/rel=.alternate./', $link) && preg_match("/type=[^ >]*{$type}[^ >]*/", $link) )
							break 2;
				$this->scour();
				$this->redirect_location = 'http://xml.wordpress.com/get/' . trim($matches[1][$key], '\'"');
				$this->status = 301;
				return;
			} else {
				$this->scour();
				$this->status = 404;
				return;
			}
		} else {
			$this->status = 200;
		}

		xml_parser_free( $this->parser );
		unset($this->parser);

		$this->bathe();
	}

	function to_xml() {
		if ( is_object($this->feed) && method_exists($this->feed, 'to_xml') )
			return $this->feed->to_xml();

		return false;
	}

	// Called internally by xml_parse(). We create an object and call its start_element method.
	function start_element($p, $element, &$attrs) {
		$el = $element;// = strtolower($element);
		// $attrs = array_change_key_case($attrs, CASE_LOWER);

		// If there is an extended class for this element, use it.
		$class = 'element';

		$maybe_class = $test_class = strtolower(str_replace(':', '_', $el));
		if ( class_exists($maybe_class) ) {
			for ($classes[] = $test_class; $test_class = get_parent_class ($test_class); $classes[] = $test_class);
			if ( in_array($class, $classes) )
				$class = $maybe_class;
		}

		// Instantiate an object for this element.
		$object = new $class();

		// Tell the element to start itself.
		$object->start_element($p, $element, $attrs, $this);
	}

	function cdata ($p, $data) {
		$this->stack[0]->cdata($p, $data, $this);
	}

	function end_element ($p, $el) {
		$this->stack[0]->end_element($p, $el, $this);
	}

	function create_parser($source, $out_enc, $in_enc, $detect) {
		if ( substr(phpversion(),0,1) == 5) {
			$parser = $this->php5_create_parser($in_enc, $detect);
		}
		else {
			list($parser, $source) = $this->php4_create_parser($source, $in_enc, $detect);
		}
		if ($out_enc) {
			$this->encoding = $out_enc;
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $out_enc);
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		}

		return array($parser, $source);
	}

	function php5_create_parser($in_enc, $detect) {
		// by default php5 does a fine job of detecting input encodings
		if(!$detect && $in_enc) {
			return xml_parser_create($in_enc);
		}
		else {
			return xml_parser_create('');
		}
	}

	/**
    * Instaniate an XML parser under PHP4
    *
    * Unfortunately PHP4's support for character encodings
    * and especially XML and character encodings sucks.  As
    * long as the documents you parse only contain characters
    * from the ISO-8859-1 character set (a superset of ASCII,
    * and a subset of UTF-8) you're fine.  However once you
    * step out of that comfy little world things get mad, bad,
    * and dangerous to know.
    *
    * The following code is based on SJM's work with FoF
    * @see http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    *
    */
	function php4_create_parser($source, $in_enc, $detect) {
		if ( !$detect ) {
			return array(xml_parser_create($in_enc), $source);
		}

		if (!$in_enc) {
			if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $source, $m)) {
				$in_enc = strtoupper($m[1]);
				$this->source_encoding = $in_enc;
			}
			else {
				$in_enc = 'UTF-8';
			}
		}

		if ($this->known_encoding($in_enc)) {
			return array(xml_parser_create($in_enc), $source);
		}

		// the dectected encoding is not one of the simple encodings PHP knows

		// attempt to use the iconv extension to
		// cast the XML to a known encoding
		// @see http://php.net/iconv

		if (function_exists('iconv'))  {
			$encoded_source = iconv($in_enc,'UTF-8', $source);
			if ($encoded_source) {
				return array(xml_parser_create('UTF-8'), $encoded_source);
			}
		}

		// iconv didn't work, try mb_convert_encoding
		// @see http://php.net/mbstring
		if(function_exists('mb_convert_encoding')) {
			$encoded_source = mb_convert_encoding($source, 'UTF-8', $in_enc );
			if ($encoded_source) {
				return array(xml_parser_create('UTF-8'), $encoded_source);
			}
		}

		// else
		$this->error("Feed is in an unsupported character encoding. ($in_enc) " .
		"You may see strange artifacts, and mangled characters.",
		E_USER_NOTICE);

		return array(xml_parser_create(), $source);
	}

	function known_encoding($enc) {
		$enc = strtoupper($enc);
		if ( in_array($enc, array('UTF-8', 'US-ASCII', 'ISO-8859-1')) ) {
			return $enc;
		}
		else {
			return false;
		}
	}

	function error ($errormsg, $lvl=E_USER_WARNING) {
		// append PHP's error message if track_errors enabled
		if ( isset($php_errormsg) ) {
			$errormsg .= " ($php_errormsg)";
		}
		if ( MAGPIE_DEBUG ) {
		//	trigger_error( $errormsg, $lvl);
		}
		else {
			error_log( $errormsg, 0);
		}

		$notices = E_USER_NOTICE|E_NOTICE;
		if ( $lvl&$notices ) {
			$this->WARNING = $errormsg;
		} else {
			$this->ERROR = $errormsg;
		}
	}

	// Remove empty and |^_.*| object vars
	function bathe() {
		foreach ( get_object_vars($this) as $key => $data )
			if ( empty($this->$key) || substr($key, 0, 1) == '_' )
				unset($this->$key);
	}

	// Remove ALL object vars
	function scour() {
		foreach ( get_object_vars($this) as $key => $data )
			unset($this->$key);
	}
	
	function to_magpie() {
		$magpie = new stdClass();

		foreach ( $this as $var => $value ) {
			if ( $var == 'feed' ) {
				continue;
			} else {
				$magpie->$var = $this->$var;
			}
		}

		$magpie->items = array();

		if ( is_object($this->feed) && method_exists($this->feed, 'to_magpie') ) {
			$feed = $this->feed->to_magpie();

			if ( is_array($feed) ) {
				foreach ( $this->feed->to_magpie() as $var => $val ) {
					if ( $var == 'items' )
						$magpie->items = $val;
					else 
						$magpie->channel["$var"] = $val;
				}
			}
		}

		return $magpie;
	}
}


class element {
	function element() {
	}

	function start_element($p, $el, $attr, &$mag) {
		$this->name = $el;
		$this->attributes = $attr;

		array_unshift($mag->stack, $this);
	}
	function cdata($p, $data, &$mag) {
		if ( empty($this->children) )
			$this->appendText($data);
	}

	function end_element($p, $el, &$mag) {
		array_shift($mag->stack);

		$this->bathe();

		if ( is_object($mag->stack[0]) )
			$mag->stack[0]->appendChild($this);
	}

	function wrap_cdata() {
		if ( strpos($this->innerText, '<') ) {
			$spacer = (substr($this->innerText, -1) == ']') ? ' ' : '';
			$this->innerText = str_replace(']]>', ']]&gt;', $this->innerText);
			$this->innerText = '<![CDATA[' . $this->innerText . $spacer . ']]>';
		}
	}

	function appendText($text) {
		$this->innerText .= $text;
	}

	function appendChild($object) {
		$this->children[] = & $object;
	}

	// Add self to array
	function to_array(&$array) {
		$self = get_object_vars($this);

		unset($self['children']);

		foreach ( $this->children as $child )
			$child->to_array($self);

		$array[$this->name] = $self;
	}

	// Return self as XML
	function to_xml($indent = "\t", $generation = 0) {
		$self = "<$this->name";
		$self .= $this->attributes ? ' '.string_attributes($this->attributes) : '';
		if ( empty($this->innerText) && empty($this->children) )
			$self .= " />\n";
		else {
			$self .= ">";
			if ( $this->children ) {
				$self .= "\n";
				foreach ( $this->children as $child )
					$self .= $child->to_xml($indent, $generation + 1);
				$self .= $indent ? $this->indent('', $indent, $generation) : '';
			} else {
				$self .= $this->innerText;
			}
			$self .= "</$this->name>\n";
		}
		return $indent ? $this->indent($self, $indent, $generation) : $self;
	}

	function indent($string, $indent, $generation) {
		for ( $i = ''; strlen($i) < $generation; $i .= $indent ) ;
		return $i . $string;
	}

	/**
	 * Gets the innerText of the first matching element in the vars
	 * @param string Provide any number of strings to try
	 * @return mixed
	 */
	function getChildText() {
		foreach ( func_get_args() as $name )
			foreach ( $this->children as $element )
				if ( $element->name == $name )
					return $element->innerText;
		return false;
	}

	/**
	 * Gets a ref to the first matching element
	 * @param string Provide any number of strings to try
	 * @return object
	 */
	function &getChildElement() {
		foreach ( func_get_args() as $name )
			foreach ( $this->children as $key => $element )
				if ( $element->name == $name )
					return $this->children[$key];
		return false;
	}

	function getAttribute($name) {
		if ( is_array($this->attributes) )
			foreach ( $this->attributes as $attr => $value )
				if ( $attr == $name )
					return $value;
		return null;
	}

	function bathe() {
		foreach ( get_object_vars($this) as $var => $value )
			if ( empty($this->$var) )
				unset($this->$var);
	}

	function to_magpie() {
		if ( strlen(trim($this->innerText)) > 0 )
			return $this->innerText;

		if ( is_array($this->attributes) )
			$e = $this->attributes;

		if ( is_array($this->children) && count($this->children) > 0 )
			foreach ( $this->children as $k => $c )
				$e[$k] = $this->children[$k]->to_magpie();

		return $e;
	}
}

// Base Atom class
class feed extends element {
	function end_element($p, $el, &$mag) {
		$mag->feed = $this;
		$mag->is_feed = true;
		$mag->last_modified = $this->last_modified();
		$mag->stack = array();
	}
	function last_modified() {
		$time = parse_w3cdtf($this->getChildText('modified'));
		return gmdate('D, d M Y H:i:s T', $time);
	}
	function to_magpie() {
		foreach ( $this->children as $k => $c ) {
			if ( $this->children[$k]->name == 'entry' )
				$magpie['items'][] = $this->children[$k]->to_magpie();
			else
				$magpie[$this->children[$k]->name] = $this->children[$k]->innerText;
		}

		return $magpie;
	}
}

// RSS base class
class rss extends feed {
	function last_modified() {
		$channel =& $this->getChildElement('channel');
		$date = $channel->getChildText('pubDate', 'lastBuildDate');
		return gmdate('D, d M Y H:i:s T', strtotime($date));
	}
	function to_magpie() {
		return $this->children[0]->to_magpie();
	}
}
class rdf_rdf extends feed {
	function to_magpie() {
		$magpie = array();
		foreach ( $this->children as $k => $child ) {
			if ( $this->children[$k]->name == 'item' )
				$magpie['items'][] = $this->children[$k]->to_magpie();
			elseif ( method_exists($this->children[$k], 'to_magpie') )
				$magpie[$this->children[$k]->name] = $this->children[$k]->to_magpie();
			else
				$magpie[$this->children[$k]->name] = $this->children[$k]->innerText;
		}
		if ( is_array($magpie['channel']) )
			$magpie = array_merge($magpie['channel'], $magpie);

		return $magpie;
	}
}
class channel extends element {
	function to_magpie() {
		foreach ( $this->children as $k => $c ) {
			if ( $this->children[$k]->name == 'item' )
				$magpie['items'][] = $this->children[$k]->to_magpie();
			else
				$magpie[$this->children[$k]->name] = $this->children[$k]->innerText;
		}

		return $magpie;
	}
}

// Atom article class
class entry extends element {
	function to_magpie() {
		foreach ( $this->children as $k => $v ) {
			if ( is_object($this->children[$k]) )
				$value = $this->children[$k]->to_magpie();
			else
				$value = $this->children[$k]->innerText;

			$name = $this->children[$k]->name;

			$norms = array(
					'dc:subject'	=> 'categories',
					'summary'	=> 'description',
					);
			$name = str_replace(array_keys($norms), array_values($norms), $name);

			switch ( $name ) {
				// The ones that needs to be dereferenced
				case 'author':
					$magpie[$name] = $value[0];
					break;

				// The ones that can be multiple
				case 'categories':
					 $magpie[$name][] = $value;
					 break;

				default:
					$magpie[$name] = $value;
			}
		}
		return $magpie;
	}

	function to_array(&$array) {
		$self = get_object_vars($this);

		unset($self['children']);

		foreach ( $this->children as $child )
			$child->to_array($self);

		$array['items'][] = $self;
	}
}

// RSS article class
class item extends entry {
	function to_magpie() {
		foreach ( $this->children as $k => $v ) {
			if ( is_object($this->children[$k]) )
				$value = $this->children[$k]->to_magpie();
			else
				$value = $this->children[$k]->innerText;

			$name = $this->children[$k]->name;

			$norms = array(
					'category'	=> 'categories',
					'content:encoded'	=> 'content',
					'dc:creator'	=> 'author',
					'wfw:commentRss'	=> 'comments',
					'wfw:commentRSS'	=> 'comments',
					'pubDate'	=> 'pubdate',
					);
			$name = str_replace(array_keys($norms), array_values($norms), $name);

			switch ( $name ) {
				// The ones that needs to be dereferenced
				case 'taxo:topics':
					$magpie[$name] = $value[0];
					break;

				// The ones that can be multiple
				case 'categories':
					 $magpie[$name][] = $value;
					 break;

				default:
					$magpie[$name] = $value;
			}
		}
		return $magpie;
	}
}

class category extends element {
	function to_array(&$array) {
		$self = get_object_vars($this);

		unset($self['children']);

		foreach ( $this->children as $child )
			$child->to_array($self);

		$array['categories'][] = $self;
	}
}

class link extends element {
	function end_element($p, $el, &$mag) {
		if ( $href = $this->getAttribute('href') )
			$this->innerText = $href;

		parent::end_element($p, $el, $mag);
	}
}


/* Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
   Manual: http://scripts.incutio.com/httpclient/
*/

if ( !class_exists('HttpClient') ) :
class HttpClient {
    // Request vars
    var $host;
    var $port;
    var $path;
    var $method;
    var $postdata = '';
    var $cookies = array();
    var $referer;
    var $accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
    var $accept_encoding = 'gzip';
    var $accept_language = 'en-us';
    var $user_agent = 'Incutio HttpClient v0.9';
    // Options
    var $timeout = 20;
    var $use_gzip = true;
    var $no_cache = false;
    var $persist_cookies = true;  // If true, received cookies are placed in the $this->cookies array ready for the next request
                                  // Note: This currently ignores the cookie path (and time) completely. Time is not important, 
                                  //       but path could possibly lead to security problems.
    var $persist_referers = true; // For each request, sends path of last request as referer
    var $debug = false;
    var $handle_redirects = true; // Auaomtically redirect if Location or URI header is found
    var $max_redirects = 5;
    var $headers_only = false;    // If true, stops receiving once headers have been read.
    // Basic authorization variables
    var $username;
    var $password;
    // Response vars
    var $status;
    var $headers = array();
    var $content = '';
    var $errormsg;
    // Tracker variables
    var $redirect_count = 0;
    var $cookie_host = '';
    function HttpClient($host, $port=80) {
        $this->host = $host;
        $this->port = $port;
    }
    function get($path, $data = false) {
        $this->path = $path;
        $this->method = 'GET';
        if ($data) {
            $this->path .= '?'.$this->buildQueryString($data);
        }
        return $this->doRequest();
    }
    function post($path, $data) {
        $this->path = $path;
        $this->method = 'POST';
        $this->postdata = $this->buildQueryString($data);
    	return $this->doRequest();
    }
    function buildQueryString($data) {
        $querystring = '';
        if (is_array($data)) {
            // Change data in to postable data
    		foreach ($data as $key => $val) {
    			if (is_array($val)) {
    				foreach ($val as $val2) {
    					$querystring .= urlencode($key).'='.urlencode($val2).'&';
    				}
    			} else {
    				$querystring .= urlencode($key).'='.urlencode($val).'&';
    			}
    		}
    		$querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
    	} else {
    	    $querystring = $data;
    	}
    	return $querystring;
    }
    function doRequest() {
        // Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
		    // Set error message
            switch($errno) {
				case -3:
					$this->errormsg = 'Socket creation failed (-3)';
				case -4:
					$this->errormsg = 'DNS lookup failure (-4)';
				case -5:
					$this->errormsg = 'Connection refused or timed out (-5)';
				default:
					$this->errormsg = 'Connection failed ('.$errno.')';
			    $this->errormsg .= ' '.$errstr;
			    $this->debug($this->errormsg);
			}
			return false;
        }
        socket_set_timeout($fp, $this->timeout);
        $request = $this->buildRequest();
        $this->debug('Request', $request);
        fwrite($fp, $request);
    	// Reset all the variables that should not persist between requests
    	$this->headers = array();
    	$this->content = '';
    	$this->errormsg = '';
    	// Set a couple of flags
    	$inHeaders = true;
    	// Now start reading back the response
   	    $line = fgets($fp, 4096);
        // Deal with first line of returned data
        if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
            $this->errormsg = "Status code line invalid: ".htmlentities($line);
            $this->debug($this->errormsg);
            return false;
        }
        $http_version = $m[1]; // not used
        $this->status = $m[2];
        $status_string = $m[3]; // not used
        $this->debug(trim($line));
    	while (!feof($fp)) {
    	    $line = fgets($fp, 4096);
    	    if ($inHeaders) {
    	        if (trim($line) == '') {
    	            $inHeaders = false;
    	            $this->debug('Received Headers', $this->headers);
    	            if ($this->headers_only) {
    	                break; // Skip the rest of the input
    	            }
    	            continue;
    	        }
    	        if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
    	            // Skip to the next header
    	            continue;
    	        }
    	        $key = strtolower(trim($m[1]));
    	        $val = trim($m[2]);
    	        // Deal with the possibility of multiple headers of same name
    	        if (isset($this->headers[$key])) {
    	            if (is_array($this->headers[$key])) {
    	                $this->headers[$key][] = $val;
    	            } else {
    	                $this->headers[$key] = array($this->headers[$key], $val);
    	            }
    	        } else {
    	            $this->headers[$key] = $val;
    	        }
    	        continue;
    	    }
    	    // We're not in the headers, so append the line to the contents
    	    $this->content .= $line;
        }
        fclose($fp);
        // If data is compressed, uncompress it
        if (isset($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
            $this->debug('Content is gzip encoded, unzipping it');
            $this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
            $this->content = gzinflate($this->content);
        }
        // If $persist_cookies, deal with any cookies
        if ($this->persist_cookies && isset($this->headers['set-cookie']) && $this->host == $this->cookie_host) {
            $cookies = $this->headers['set-cookie'];
            if (!is_array($cookies)) {
                $cookies = array($cookies);
            }
            foreach ($cookies as $cookie) {
                if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
                    $this->cookies[$m[1]] = $m[2];
                }
            }
            // Record domain of cookies for security reasons
            $this->cookie_host = $this->host;
        }
        // If $persist_referers, set the referer ready for the next request
        if ($this->persist_referers) {
            $this->debug('Persisting referer: '.$this->getRequestURL());
            $this->referer = $this->getRequestURL();
        }
        // Finally, if handle_redirects and a redirect is sent, do that
        if ($this->handle_redirects) {
            if (++$this->redirect_count >= $this->max_redirects) {
                $this->errormsg = 'Number of redirects exceeded maximum ('.$this->max_redirects.')';
                $this->debug($this->errormsg);
                $this->redirect_count = 0;
                return false;
            }
            $location = isset($this->headers['location']) ? $this->headers['location'] : '';
            $uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
            if ($location || $uri) {
                $url = parse_url($location.$uri);
                // This will FAIL if redirect is to a different site
                return $this->get($url['path']);
            }
        }
        return true;
    }
    function buildRequest() {
        $headers = array();
        $headers[] = "{$this->method} {$this->path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
        $headers[] = "Host: {$this->host}";
        $headers[] = "User-Agent: {$this->user_agent}";
        $headers[] = "Accept: {$this->accept}";
        if ($this->use_gzip) {
            $headers[] = "Accept-encoding: {$this->accept_encoding}";
        }
        $headers[] = "Accept-language: {$this->accept_language}";
        if ($this->referer) {
            $headers[] = "Referer: {$this->referer}";
        }
    	if ($this->no_cache) {
    		$headers[] = "Pragma: no-cache";
    		$headers[] = "Cache-control: no-cache";
    	}
    	// Cookies
    	if ($this->cookies) {
    	    $cookie = 'Cookie: ';
    	    foreach ($this->cookies as $key => $value) {
    	        $cookie .= "$key=$value; ";
    	    }
    	    $headers[] = $cookie;
    	}
    	// Basic authentication
    	if ($this->username && $this->password) {
    	    $headers[] = 'Authorization: BASIC '.base64_encode($this->username.':'.$this->password);
    	}
    	// If this is a POST, set the content type and length
    	if ($this->postdata) {
    	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    	    $headers[] = 'Content-Length: '.strlen($this->postdata);
    	}
    	$request = implode("\r\n", $headers)."\r\n\r\n".$this->postdata;
    	return $request;
    }
    function getStatus() {
        return $this->status;
    }
    function getContent() {
        return $this->content;
    }
    function getHeaders() {
        return $this->headers;
    }
    function getHeader($header) {
        $header = strtolower($header);
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return false;
        }
    }
    function getError() {
        return $this->errormsg;
    }
    function getCookies() {
        return $this->cookies;
    }
    function getRequestURL() {
        $url = 'http://'.$this->host;
        if ($this->port != 80) {
            $url .= ':'.$this->port;
        }            
        $url .= $this->path;
        return $url;
    }
    // Setter methods
    function setUserAgent($string) {
        $this->user_agent = $string;
    }
    function setAuthorization($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    function setCookies($array) {
        $this->cookies = $array;
    }
    // Option setting methods
    function useGzip($boolean) {
        $this->use_gzip = $boolean;
    }
    function setPersistCookies($boolean) {
        $this->persist_cookies = $boolean;
    }
    function setPersistReferers($boolean) {
        $this->persist_referers = $boolean;
    }
    function setHandleRedirects($boolean) {
        $this->handle_redirects = $boolean;
    }
    function setMaxRedirects($num) {
        $this->max_redirects = $num;
    }
    function setHeadersOnly($boolean) {
        $this->headers_only = $boolean;
    }
    function setDebug($boolean) {
        $this->debug = $boolean;
    }
    // "Quick" static methods
    function quickGet($url) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?'.$bits['query'];
        }
        $client = new HttpClient($host, $port);
        if (!$client->get($path)) {
            return false;
        } else {
            return $client->getContent();
        }
    }
    function quickPost($url, $data) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        $client = new HttpClient($host, $port);
        if (!$client->post($path, $data)) {
            return false;
        } else {
            return $client->getContent();
        }
    }
    function debug($msg, $object = false) {
        if ($this->debug) {
            print '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>HttpClient Debug:</strong> '.$msg;
            if ($object) {
                ob_start();
        	    print_r($object);
        	    $content = htmlentities(ob_get_contents());
        	    ob_end_clean();
        	    print '<pre>'.$content.'</pre>';
        	}
        	print '</div>';
        }
    }
}
endif;

function string_attributes($attrs) {
	return join(' ', array_map(create_function('$k,$v', 'return "$k=\"".htmlspecialchars($v)."\"";'), array_keys($attrs), array_values($attrs) ) );
}

if ( !function_exists('parse_w3cdtf') ) :
function parse_w3cdtf ( $date_str ) {
	# regex to match wc3dtf
	$pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";

	if ( preg_match( $pat, $date_str, $match ) ) {
		list( $year, $month, $day, $hours, $minutes, $seconds) =
				array( $match[1], $match[2], $match[3], $match[4], $match[5], $match[7]);

		# calc epoch for current date assuming GMT
		$epoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year);

		$offset = 0;
		if ( $match[10] == 'Z' ) {
			# zulu time, aka GMT
		}
		else {
			list( $tz_mod, $tz_hour, $tz_min ) =
			array( $match[8], $match[9], $match[10]);

			# zero out the variables
			if ( ! $tz_hour ) { $tz_hour = 0; }
			if ( ! $tz_min ) { $tz_min = 0; }

			$offset_secs = (($tz_hour*60)+$tz_min)*60;

			# is timezone ahead of GMT?  then subtract offset
			#
			if ( $tz_mod == '+' ) {
				$offset_secs = $offset_secs * -1;
			}

			$offset = $offset_secs;
		}
		$epoch = $epoch + $offset;
		return $epoch;
	}
	else {
		return -1;
	}
}
endif;

?>
