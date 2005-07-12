#
# Make a WPMU release. 
# Needs Smarty from http://smarty.php.net/
# Copyright Donncha O Caoimh, donncha@linux.ie
#

# Create stable release

export STABLEWP=2618
export STABLEWPMU=78
export RELEASE=1_5_1_3

rm -fr wpmu-stable trunk wordpress-$RELEASE

svn export http://svn.automattic.com/wordpress/tags/1.5.1.3
mv 1.5.1.3 wordpress-$RELEASE

svn export -r $STABLEWPMU http://svn.automattic.com/wordpress-smarty/trunk/
mv trunk wpmu-stable

svn co -r $STABLEWPMU http://svn.automattic.com/wordpress-smarty/trunk && cd trunk && svn -v log > ../Changelog.txt && cd ..
rm -fr trunk

export WPMUDIR=wpmu-stable-$RELEASE
rm -fr $WPMUDIR

mkdir -p $WPMUDIR/wp-inst/
cp -r wordpress-$RELEASE/* $WPMUDIR/wp-inst/
rm -fr $WPMUDIR/wp-inst/wp-content/themes
cp -af wpmu-stable/* $WPMUDIR/
rm -fr wpmu-stable

cp -av ./Smarty-2.6.9/libs/internals $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Smarty_Compiler.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Smarty.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Config_File.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/plugins/* $WPMUDIR/wp-inst/wp-content/smarty-plugins/

cd $WPMUDIR/wp-inst/wp-includes/ && php create_smarty_template.php > class-smarty.php && cd ../../..
mv Changelog.txt $WPMUDIR/

zip -r $WPMUDIR.zip $WPMUDIR
tar zcvf $WPMUDIR.tar.gz $WPMUDIR

# Create unstable release
rm -fr wpmu-latest wordpress-latest wordpress-smarty
svn export http://svn.automattic.com/wordpress/trunk/
mv trunk wordpress-latest
svn export http://svn.automattic.com/wordpress-smarty/
mv wordpress-smarty/trunk wpmu-latest
svn co http://svn.automattic.com/wordpress-smarty/trunk && cd trunk && svn -v log > ../Changelog.txt && cd ..
rm -fr trunk wordpress-smarty

export WPMUDIR=wpmu-`date +%Y-%m-%d`
rm -fr $WPMUDIR

mkdir -p $WPMUDIR/wp-inst/
mv wordpress-latest/* $WPMUDIR/wp-inst/
rm -fr wordpress-latest
cp -af wpmu-latest/* $WPMUDIR/
rm -fr wpmu-latest

cp -av ./Smarty-2.6.9/libs/internals $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Smarty_Compiler.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Smarty.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/Config_File.class.php $WPMUDIR/wp-inst/
cp -av ./Smarty-2.6.9/libs/plugins/* $WPMUDIR/wp-inst/wp-content/smarty-plugins/

cd $WPMUDIR/wp-inst/wp-includes/ && php create_smarty_template.php > class-smarty.php && cd ../../..
mv Changelog.txt $WPMUDIR/

zip -r $WPMUDIR.zip $WPMUDIR
tar zcvf $WPMUDIR.tar.gz $WPMUDIR
