<?php
/*
   Smarty plugin
   -------------------------------------------------------------
   File:     function.cork.php
   Type:     function
   Name:     cork
   Purpose:  Piratize your content!
   -------------------------------------------------------------

   $Id: function.cork.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $
*/

function smarty_function_cork($params, &$smarty)
{
    extract( $params );

    if( '0919' != date('md') )
    {
        return $content;
    }
        // Always replace these:
    $patterns = array(
            '%\bmy\b%' => 'me',
            '%\bcareful\b%' => 'wide',
            '%\bfooling\b%' => 'codding',
            '%\bjoking\b%' => 'codding',
            '%\b[Ee]xcuse me\b%' => 'C\'mere',
            '%\bcommon sense\b%' => 'cop on',
            '%\bkick\b%' => 'funt',
            '%\bgood looking\b%' => 'fine half',
            '%\bgood looking guy\b%' => 'flah',
            '%\bgood looking girl\b%' => 'flah',
            '%\bbeautiful girl\b%' => 'flah',
            '%\bbeautiful woman\b%' => 'flah',
            '%\btired\b%' => 'flahed out',
            '%\bexhausted\b%' => 'flahed out',
            '%\bunpleasant\b%' => 'ganky',
            '%\bterrible\b%' => 'nawful',
            '%\bnot nice\b%' => 'ganky',
            '%\blook\b%' => 'gawk',
            '%\bidiot\b%' => 'gowl',
            '%\bthat\'s good\b%' => 'how bad?',
            '%\b[Nn]o!\b%' => 'I wil, yeah!',
            '%\blovely\b%' => 'massive',
            '%\bLovely\b%' => 'Massive',
            '%\bnot real\b%' => 'mockeyah',
            '%\bgirlfriend\b%' => 'oul doll',
            '%\bskipping school\b%' => 'on the hop',
            '%\bvery lucky\b%' => 'poxed',
            '%\btrainers\b%' => 'rubber dollies',
            '%\brunners\b%' => 'rubber dollies',
            '%\bsports shoes\b%' => 'rubber dollies',
            '%\bake a look\b%' => 'ake a sconse',
            '%\bis vain\b%' => 'is septic',
            '%\bfemale\b%' => 'wan',
            '%\b\wouldn\'t dareb%' => 'would yeah',
            '%\bHe looks bad\b%' => 'State a him la',
            '%\bvery\b%' => 'pure',
            '%\bcare\b%' => 'give two f*cks',
            '%\bprostitue\b%' => 'brasser',
            '%\bmale\b%' => 'fein',
            '%\bman\b%' => 'feen',
            '%\bstupid\b%' => 'gowl',
            '%\bpotatoes\b%' => 'poppies',
            '%\becstasy\b%' => 'yolkies',
            '%\b[Gg]arda\b%' => 'Shades',
            '%\b[Pp]olice\b%' => 'Law',
            '%\bGarda motorbike\b%' => 'Speedy',
            '%\bdeformed\b%' => 'gammy',
            '%\bbroken\b%' => 'gammy',
            '%\bdrink\b%' => 'on the tear',
            '%\bdrinking\b%' => 'gattin',
	    '%\bGreat\b%' => 'How Bad!',
            '%\bave a go\b%' => 'ave a lash',
            '%\bmother\b%' => 'mam',
            '%\blook\b%' => 'lamp',
            '%\bhome\b%' => 'gaff',
            '%\bhouse\b%' => 'gaff',
            '%\bnose\b%' => 'gonzer',
            '%\bHow\'s the form?\b%' => 'How are you?',
            '%\bhaircut\b%' => 'bazzer',
            '%\bsatisfaction\b%' => 'soot',
            '%\bvomiting\b%' => 'gawking',
            '%\bvomit\b%' => 'gawk',
            '%\bsnail\b%' => 'shelityhorn',
            '%\bvery nice\b%' => 'me daza',
            '%\bThis\b%' => 'Dis'
            #'%\b\b%' => '',
            );

    // Replace the words:
    $content = array_apply_regexp($patterns,$content);
    
    // Word ending mangling:
    $patterns = array(
                '/ing\b/' => "in'",
                // '/([a-zA-Z]{3,}[^lbro])ly(\W)/' => '$1-like$2',
                );

    $content = array_apply_regexp($patterns,$content);

    // Random exclamations and such:
    $patterns = array(
                '/(\.\s)/e' => 'avast("$0",3)',
                );

    $content = array_apply_regexp($patterns,$content);
    
    // Let's increase the chance for exclamation marks and question marks
    $patterns = array(
                '/([!\?]\s)/e' => 'avast("$0",5)',
                );
            
    $content = array_apply_regexp($patterns,$content);
    
    return $content; 
}

// support function for cork()
function avast($stub = '',$chance = 5) {
    $shouts = array(
                ", like!",
		", you know like?",
		", la!",
		", boy!",
		"Like eh, $stub"
                );
                
    shuffle($shouts);
    
    return (((1 == rand(1,$chance))?array_shift($shouts):$stub) . ' ');
}

// This function takes an array of ('/pattern/' => 'replacement') pairs
// and applies them all to $content.
function array_apply_regexp($patterns,$content) {
    // Extract the values:
    $keys = array_keys($patterns);
    $values = array_values($patterns);
    
    // Modify the key patterns to avoid modifying the contents
    // of HTML tags
    for ($i = 0; $i < count($keys); $i++) {
        $regexp = $keys[$i];
        
//        $regexp = preg_replace('%^(.)(.+)(.)$%','$1(?!<[^>])$2(?![>])$3',$regexp);
        
        $keys[$i] = $regexp;
    }

    // Replace the words:
    $content = preg_replace($keys,$values,$content);

    return $content;
}
?>
