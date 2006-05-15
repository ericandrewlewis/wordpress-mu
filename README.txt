WordPress Multi User
--------------------

WordPress MU is a multi user version of WordPress.

If you're not comfortable editing PHP code, taking care of a complex
webserver and database system and being pro-active about following
developments of this project then run, don't walk, to 
http://wordpress.com/ and sign yourself and your friends up to free blogs.
It's easier in the long run and you'll save yourself a lot of pain
and angst. ;)

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
"AllowOverride FileInfo Options"

PHP
===
For security reasons, it's very important that PHP be configured as follows:

1. Don't display error messages to the browser. This is almost always
turned off but sometimes when you're testing you turn this on and forget
to reset it.

2. GLOBAL variables must be turned off. This is one of the first things
any security aware admin will do. These days the default is for it to
be off!

3. If you want to restrict blog signups, set the restrict domain email 
setting in the admin.

The easiest way of configuring it is via the .htaccess file that is
created during the install. If you haven't installed WPMU yet then edit
the file htaccess.dist in this directory and add these two lines at the
top:

php_flag register_globals 0
php_flag display_errors 0

This is NOT included in that file by default because it doesn't work on
all machines. If it doesn't work on your machine, you'll get a cryptic
"500 internal error" after you install WPMU. To remove the offending lines
just edit the file ".htaccess" in your install directory and you'll see
them at the top. Delete and save the file again.
Read here for how to enable this: http://ie.php.net/configuration.changes

If you don't want to edit your .htaccess file then you need to change your
php.ini. It's beyond the scope of this README to know exactly where it is
on your machine, but if you're on a shared hosted server you probably
don't have access to it as it requires root or administrator privileges
to change.

If you do have root access, try "locate php.ini" or check in:

/etc/php4/apache2/php.ini
/usr/local/lib/php.ini

Once you have opened your php.ini, look for the sections related to 
register_globals and display_errors. Make sure both are Off like so:

display_errors = Off
register_globals = Off

You'll have to restart Apache after you modify your php.ini for the 
settings to be updated.

Support Forum:

http://mu.wordpress.org/forums/

http://mu.wordpress.org/
