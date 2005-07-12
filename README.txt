WordPress Multi User
--------------------

WordPress MU is a multi user version of WordPress.


Install
=======
Unarchive into a web directory and call that directory through your
browser and web server. Follow the instructions and links and all
should work fine.

Apache
======
Apache must be configured so that mod_rewrite works. Here are 
instructions for Apache 2. Apache 1.3 is very similar.

1. Make sure a line like the following appears in your httpd.conf
LoadModule rewrite_module /usr/lib/apache2/modules/mod_rewrite.so

2. In the <Directory> directive of your virtual host, look for this
line
"AllowOverride None"
and change it to
"AllowOverride FileInfo"

Maintenance
===========
If you have PEAR Cache, it'll be used to significantly speed up
things. However, this generates cached files which have to be cleared
from time to time.
Uncomment the code in wp-inst/maintenance.php and make sure it's 
protected by IP checks or username/passwords. You should call this
script at least once a day, and maybe more depending on how busy
your server is.

Support Forum:
http://mu.wordpress.org/forums/

http://mu.wordpress.org/
