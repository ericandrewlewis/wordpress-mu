<?php

if( isset( $wpsmarty ) == false || is_object( $wpsmarty ) == false )
{       
        if( defined( ABSPATH ) == false )
            define( "ABSPATH", "../" );

	require_once( ABSPATH . "Smarty.class.php" );
	$wpsmarty = new Smarty;
}

/* get_the_category( $id = false ) */
function smarty_get_the_category( $params, &$smarty )
{
    $id = false;

    extract( $params );
    return get_the_category( $id );
}
$wpsmarty->register_function( "get_the_category", "smarty_get_the_category" );

/* get_category_link( $category_id ) */
function smarty_get_category_link( $params, &$smarty )
{

    extract( $params );
    return get_category_link( $category_id );
}
$wpsmarty->register_function( "get_category_link", "smarty_get_category_link" );

/* get_the_category_list( $separator = '', $parents='' ) */
function smarty_get_the_category_list( $params, &$smarty )
{
    $separator = '';
    $parents='';

    extract( $params );
    return get_the_category_list( $separator, $parents );
}
$wpsmarty->register_function( "get_the_category_list", "smarty_get_the_category_list" );

/* the_category( $separator = '', $parents='' ) */
function smarty_the_category( $params, &$smarty )
{
    $separator = '';
    $parents='';

    extract( $params );
    return the_category( $separator, $parents );
}
$wpsmarty->register_function( "the_category", "smarty_the_category" );

/* get_the_category_by_ID( $cat_ID ) */
function smarty_get_the_category_by_ID( $params, &$smarty )
{

    extract( $params );
    return get_the_category_by_ID( $cat_ID );
}
$wpsmarty->register_function( "get_the_category_by_ID", "smarty_get_the_category_by_ID" );

/* get_category_parents( $id, $link = FALSE, $separat ) */
function smarty_get_category_parents( $params, &$smarty )
{
    $link = FALSE;

    extract( $params );
    return get_category_parents( $id, $link,  $separat );
}
$wpsmarty->register_function( "get_category_parents", "smarty_get_category_parents" );

/* get_category_children( $id, $before = '/', $after = '' ) */
function smarty_get_category_children( $params, &$smarty )
{
    $before = '/';
    $after = '';

    extract( $params );
    return get_category_children( $id, $before, $after );
}
$wpsmarty->register_function( "get_category_children", "smarty_get_category_children" );

/* the_category_ID( $echo = true ) */
function smarty_the_category_ID( $params, &$smarty )
{
    $echo = true;

    extract( $params );
    return the_category_ID( $echo );
}
$wpsmarty->register_function( "the_category_ID", "smarty_the_category_ID" );

/* the_category_head( $before='', $after='' ) */
function smarty_the_category_head( $params, &$smarty )
{
    $before='';
    $after='';

    extract( $params );
    return the_category_head( $before, $after );
}
$wpsmarty->register_function( "the_category_head", "smarty_the_category_head" );

/* category_description( $category = 0 ) */
function smarty_category_description( $params, &$smarty )
{
    $category = 0;

    extract( $params );
    return category_description( $category );
}
$wpsmarty->register_function( "category_description", "smarty_category_description" );

/* dropdown_cats( $optionall = 1, $all = 'All', $sort_column = 'ID', $sort_order = 'asc',
        $optiondates = 0, $optioncount = 0, $hide_empty = 1, $optionnone=FALSE,
        $selected=0, $hide=0 ) */
function smarty_dropdown_cats( $params, &$smarty )
{
    $optionall = 1;
    $all = 'All';
    $sort_column = 'ID';
    $sort_order = 'asc';
    $optiondates = 0;
    $optioncount = 0;
    $hide_empty = 1;
    $optionnone=FALSE;
    $selected=0;
    $hide=0;

    extract( $params );
    return dropdown_cats( $optionall, $all, $sort_column, $sort_order, $optiondates, $optioncount, $hide_empty, $optionnone, $selected, $hide );
}
$wpsmarty->register_function( "dropdown_cats", "smarty_dropdown_cats" );

/* wp_list_cats( $args = '' ) */
function smarty_wp_list_cats( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_list_cats( $args );
}
$wpsmarty->register_function( "wp_list_cats", "smarty_wp_list_cats" );

/* list_cats( $optionall = 1, $all = 'All', $sort_column = 'ID', $sort_order = 'asc', $file = '', $list = true, $optiondates = 0, $optioncount = 0, $hide_empty = 1, $use_desc_for_title = 1, $children=FALSE, $child_of=0, $categories=0, $recurse=0, $feed = '', $feed_image = '', $exclude = '', $hierarchical=FALSE ) */
function smarty_list_cats( $params, &$smarty )
{
    $optionall = 1;
    $all = 'All';
    $sort_column = 'ID';
    $sort_order = 'asc';
    $file = '';
    $list = true;
    $optiondates = 0;
    $optioncount = 0;
    $hide_empty = 1;
    $use_desc_for_title = 1;
    $children=FALSE;
    $child_of=0;
    $categories=0;
    $recurse=0;
    $feed = '';
    $feed_image = '';
    $exclude = '';
    $hierarchical=FALSE;

    extract( $params );
    return list_cats( $optionall, $all, $sort_column, $sort_order, $file, $list, $optiondates, $optioncount, $hide_empty, $use_desc_for_title, $children, $child_of, $categories, $recurse, $feed, $feed_image, $exclude, $hierarchical );
}
$wpsmarty->register_function( "list_cats", "smarty_list_cats" );

/* in_category( $category ) */
function smarty_in_category( $params, &$smarty )
{

    extract( $params );
    return in_category( $category );
}
$wpsmarty->register_function( "in_category", "smarty_in_category" );

/* get_header(  ) */
function smarty_get_header( $params, &$smarty )
{

    extract( $params );
    return get_header(  );
}
$wpsmarty->register_function( "get_header", "smarty_get_header" );

/* get_footer(  ) */
function smarty_get_footer( $params, &$smarty )
{

    extract( $params );
    return get_footer(  );
}
$wpsmarty->register_function( "get_footer", "smarty_get_footer" );

/* get_sidebar(  ) */
function smarty_get_sidebar( $params, &$smarty )
{

    extract( $params );
    return get_sidebar(  );
}
$wpsmarty->register_function( "get_sidebar", "smarty_get_sidebar" );

/* wp_loginout(  ) */
function smarty_wp_loginout( $params, &$smarty )
{

    extract( $params );
    return wp_loginout(  );
}
$wpsmarty->register_function( "wp_loginout", "smarty_wp_loginout" );

/* wp_register(  $before = '<li>', $after = '</li>'  ) */
function smarty_wp_register( $params, &$smarty )
{
    $before = '<li>';
    $after = '</li>';

    extract( $params );
    return wp_register( $before, $after );
}
$wpsmarty->register_function( "wp_register", "smarty_wp_register" );

/* wp_meta(  ) */
function smarty_wp_meta( $params, &$smarty )
{

    extract( $params );
    return wp_meta(  );
}
$wpsmarty->register_function( "wp_meta", "smarty_wp_meta" );

/* bloginfo( $show='' ) */
function smarty_bloginfo( $params, &$smarty )
{
    $show='';

    extract( $params );
    return bloginfo( $show );
}
$wpsmarty->register_function( "bloginfo", "smarty_bloginfo" );

/* get_bloginfo( $show='' ) */
function smarty_get_bloginfo( $params, &$smarty )
{
    $show='';

    extract( $params );
    return get_bloginfo( $show );
}
$wpsmarty->register_function( "get_bloginfo", "smarty_get_bloginfo" );

/* wp_title( $sep = '&raquo;', $display = true ) */
function smarty_wp_title( $params, &$smarty )
{
    $sep = '&raquo;';
    $display = true;

    extract( $params );
    return wp_title( $sep, $display );
}
$wpsmarty->register_function( "wp_title", "smarty_wp_title" );

/* single_post_title( $prefix = '', $display = true ) */
function smarty_single_post_title( $params, &$smarty )
{
    $prefix = '';
    $display = true;

    extract( $params );
    return single_post_title( $prefix, $display );
}
$wpsmarty->register_function( "single_post_title", "smarty_single_post_title" );

/* single_cat_title( $prefix = '', $display = true  ) */
function smarty_single_cat_title( $params, &$smarty )
{
    $prefix = '';
    $display = true;

    extract( $params );
    return single_cat_title( $prefix, $display );
}
$wpsmarty->register_function( "single_cat_title", "smarty_single_cat_title" );

/* single_month_title( $prefix = '', $display = true  ) */
function smarty_single_month_title( $params, &$smarty )
{
    $prefix = '';
    $display = true;

    extract( $params );
    return single_month_title( $prefix, $display );
}
$wpsmarty->register_function( "single_month_title", "smarty_single_month_title" );

/* get_archives_link( $url, $text, $format = 'html', $before = '', $after = '' ) */
function smarty_get_archives_link( $params, &$smarty )
{
    $format = 'html';
    $before = '';
    $after = '';

    extract( $params );
    return get_archives_link( $url,  $text, $format, $before, $after );
}
$wpsmarty->register_function( "get_archives_link", "smarty_get_archives_link" );

/* wp_get_archives( $args = '' ) */
function smarty_wp_get_archives( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_get_archives( $args );
}
$wpsmarty->register_function( "wp_get_archives", "smarty_wp_get_archives" );

/* get_archives( $type='', $limit='', $format='html', $before = '', $after = '', $show_post_count = false ) */
function smarty_get_archives( $params, &$smarty )
{
    $type='';
    $limit='';
    $format='html';
    $before = '';
    $after = '';
    $show_post_count = false;

    extract( $params );
    return get_archives( $type, $limit, $format, $before, $after, $show_post_count );
}
$wpsmarty->register_function( "get_archives", "smarty_get_archives" );

/* calendar_week_mod( $num ) */
function smarty_calendar_week_mod( $params, &$smarty )
{

    extract( $params );
    return calendar_week_mod( $num );
}
$wpsmarty->register_function( "calendar_week_mod", "smarty_calendar_week_mod" );

/* get_calendar( $daylength = 1 ) */
function smarty_get_calendar( $params, &$smarty )
{
    $daylength = 1;

    extract( $params );
    return get_calendar( $daylength );
}
$wpsmarty->register_function( "get_calendar", "smarty_get_calendar" );

/* allowed_tags(  ) */
function smarty_allowed_tags( $params, &$smarty )
{

    extract( $params );
    return allowed_tags(  );
}
$wpsmarty->register_function( "allowed_tags", "smarty_allowed_tags" );

/* the_date_xml(  ) */
function smarty_the_date_xml( $params, &$smarty )
{

    extract( $params );
    return the_date_xml(  );
}
$wpsmarty->register_function( "the_date_xml", "smarty_the_date_xml" );

/* the_date( $d='', $before='', $after='', $echo = true ) */
function smarty_the_date( $params, &$smarty )
{
    $d='';
    $before='';
    $after='';
    $echo = true;

    extract( $params );
    return the_date( $d, $before, $after, $echo );
}
$wpsmarty->register_function( "the_date", "smarty_the_date" );

/* the_time(  $d = ''  ) */
function smarty_the_time( $params, &$smarty )
{
    $d = '';

    extract( $params );
    return the_time( $d );
}
$wpsmarty->register_function( "the_time", "smarty_the_time" );

/* get_the_time(  $d = ''  ) */
function smarty_get_the_time( $params, &$smarty )
{
    $d = '';

    extract( $params );
    return get_the_time( $d );
}
$wpsmarty->register_function( "get_the_time", "smarty_get_the_time" );

/* get_post_time(  $d = 'U', $gmt = false  ) */
function smarty_get_post_time( $params, &$smarty )
{
    $d = 'U';
    $gmt = false;

    extract( $params );
    return get_post_time( $d, $gmt );
}
$wpsmarty->register_function( "get_post_time", "smarty_get_post_time" );

/* the_weekday(  ) */
function smarty_the_weekday( $params, &$smarty )
{

    extract( $params );
    return the_weekday(  );
}
$wpsmarty->register_function( "the_weekday", "smarty_the_weekday" );

/* the_weekday_date( $before='',$after='' ) */
function smarty_the_weekday_date( $params, &$smarty )
{
    $before='';
    $after='';

    extract( $params );
    return the_weekday_date( $before, $after );
}
$wpsmarty->register_function( "the_weekday_date", "smarty_the_weekday_date" );

/* the_permalink(  ) */
function smarty_the_permalink( $params, &$smarty )
{

    extract( $params );
    return the_permalink(  );
}
$wpsmarty->register_function( "the_permalink", "smarty_the_permalink" );

/* permalink_link(  ) */
function smarty_permalink_link( $params, &$smarty )
{

    extract( $params );
    return permalink_link(  );
}
$wpsmarty->register_function( "permalink_link", "smarty_permalink_link" );

/* permalink_anchor( $mode = 'id' ) */
function smarty_permalink_anchor( $params, &$smarty )
{
    $mode = 'id';

    extract( $params );
    return permalink_anchor( $mode );
}
$wpsmarty->register_function( "permalink_anchor", "smarty_permalink_anchor" );

/* get_permalink( $id = 0 ) */
function smarty_get_permalink( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return get_permalink( $id );
}
$wpsmarty->register_function( "get_permalink", "smarty_get_permalink" );

/* get_page_link( $id = false ) */
function smarty_get_page_link( $params, &$smarty )
{
    $id = false;

    extract( $params );
    return get_page_link( $id );
}
$wpsmarty->register_function( "get_page_link", "smarty_get_page_link" );

/* get_year_link( $year ) */
function smarty_get_year_link( $params, &$smarty )
{

    extract( $params );
    return get_year_link( $year );
}
$wpsmarty->register_function( "get_year_link", "smarty_get_year_link" );

/* get_month_link( $year, $month ) */
function smarty_get_month_link( $params, &$smarty )
{

    extract( $params );
    return get_month_link( $year,  $month );
}
$wpsmarty->register_function( "get_month_link", "smarty_get_month_link" );

/* get_day_link( $year, $month, $day ) */
function smarty_get_day_link( $params, &$smarty )
{

    extract( $params );
    return get_day_link( $year,  $month,  $day );
}
$wpsmarty->register_function( "get_day_link", "smarty_get_day_link" );

/* get_feed_link( $feed='rss2' ) */
function smarty_get_feed_link( $params, &$smarty )
{
    $feed='rss2';

    extract( $params );
    return get_feed_link( $feed );
}
$wpsmarty->register_function( "get_feed_link", "smarty_get_feed_link" );

/* edit_post_link( $link = 'Edit This', $before = '', $after = '' ) */
function smarty_edit_post_link( $params, &$smarty )
{
    $link = 'Edit This';
    $before = '';
    $after = '';

    extract( $params );
    return edit_post_link( $link, $before, $after );
}
$wpsmarty->register_function( "edit_post_link", "smarty_edit_post_link" );

/* edit_comment_link( $link = 'Edit This', $before = '', $after = '' ) */
function smarty_edit_comment_link( $params, &$smarty )
{
    $link = 'Edit This';
    $before = '';
    $after = '';

    extract( $params );
    return edit_comment_link( $link, $before, $after );
}
$wpsmarty->register_function( "edit_comment_link", "smarty_edit_comment_link" );

/* get_previous_post( $in_same_cat = false, $excluded_categories = '' ) */
function smarty_get_previous_post( $params, &$smarty )
{
    $in_same_cat = false;
    $excluded_categories = '';

    extract( $params );
    return get_previous_post( $in_same_cat, $excluded_categories );
}
$wpsmarty->register_function( "get_previous_post", "smarty_get_previous_post" );

/* get_next_post( $in_same_cat = false, $excluded_categories = '' ) */
function smarty_get_next_post( $params, &$smarty )
{
    $in_same_cat = false;
    $excluded_categories = '';

    extract( $params );
    return get_next_post( $in_same_cat, $excluded_categories );
}
$wpsmarty->register_function( "get_next_post", "smarty_get_next_post" );

/* previous_post_link( $format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '' ) */
function smarty_previous_post_link( $params, &$smarty )
{
    $format='&laquo; %link';
    $link='%title';
    $in_same_cat = false;
    $excluded_categories = '';

    extract( $params );
    return previous_post_link( $format, $link, $in_same_cat, $excluded_categories );
}
$wpsmarty->register_function( "previous_post_link", "smarty_previous_post_link" );

/* next_post_link( $format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '' ) */
function smarty_next_post_link( $params, &$smarty )
{
    $format='%link &raquo;';
    $link='%title';
    $in_same_cat = false;
    $excluded_categories = '';

    extract( $params );
    return next_post_link( $format, $link, $in_same_cat, $excluded_categories );
}
$wpsmarty->register_function( "next_post_link", "smarty_next_post_link" );

/* previous_post( $format='%', $previous='previous post: ', $title='yes', $in_same_cat='no', $limitprev=1, $excluded_categories='' ) */
function smarty_previous_post( $params, &$smarty )
{
    $format='%';
    $previous='previous post: ';
    $title='yes';
    $in_same_cat='no';
    $limitprev=1;
    $excluded_categories='';

    extract( $params );
    return previous_post( $format, $previous, $title, $in_same_cat, $limitprev, $excluded_categories );
}
$wpsmarty->register_function( "previous_post", "smarty_previous_post" );

/* next_post( $format='%', $next='next post: ', $title='yes', $in_same_cat='no', $limitnext=1, $excluded_categories='' ) */
function smarty_next_post( $params, &$smarty )
{
    $format='%';
    $next='next post: ';
    $title='yes';
    $in_same_cat='no';
    $limitnext=1;
    $excluded_categories='';

    extract( $params );
    return next_post( $format, $next, $title, $in_same_cat, $limitnext, $excluded_categories );
}
$wpsmarty->register_function( "next_post", "smarty_next_post" );

/* get_pagenum_link( $pagenum = 1 ) */
function smarty_get_pagenum_link( $params, &$smarty )
{
    $pagenum = 1;

    extract( $params );
    return get_pagenum_link( $pagenum );
}
$wpsmarty->register_function( "get_pagenum_link", "smarty_get_pagenum_link" );

/* next_posts( $max_page = 0 ) */
function smarty_next_posts( $params, &$smarty )
{
    $max_page = 0;

    extract( $params );
    return next_posts( $max_page );
}
$wpsmarty->register_function( "next_posts", "smarty_next_posts" );

/* next_posts_link( $label='Next Page &raquo;', $max_page=0 ) */
function smarty_next_posts_link( $params, &$smarty )
{
    $label='Next Page &raquo;';
    $max_page=0;

    extract( $params );
    return next_posts_link( $label, $max_page );
}
$wpsmarty->register_function( "next_posts_link", "smarty_next_posts_link" );

/* previous_posts(  ) */
function smarty_previous_posts( $params, &$smarty )
{

    extract( $params );
    return previous_posts(  );
}
$wpsmarty->register_function( "previous_posts", "smarty_previous_posts" );

/* previous_posts_link( $label='&laquo; Previous Page' ) */
function smarty_previous_posts_link( $params, &$smarty )
{
    $label='&laquo; Previous Page';

    extract( $params );
    return previous_posts_link( $label );
}
$wpsmarty->register_function( "previous_posts_link", "smarty_previous_posts_link" );

/* posts_nav_link( $sep=' &#8212; ', $prelabel='&laquo; Previous Page', $nxtlabel='Next Page &raquo;' ) */
function smarty_posts_nav_link( $params, &$smarty )
{
    $sep=' &#8212; ';
    $prelabel='&laquo; Previous Page';
    $nxtlabel='Next Page &raquo;';

    extract( $params );
    return posts_nav_link( $sep, $prelabel, $nxtlabel );
}
$wpsmarty->register_function( "posts_nav_link", "smarty_posts_nav_link" );

/* get_the_password_form(  ) */
function smarty_get_the_password_form( $params, &$smarty )
{

    extract( $params );
    return get_the_password_form(  );
}
$wpsmarty->register_function( "get_the_password_form", "smarty_get_the_password_form" );

/* the_ID(  ) */
function smarty_the_ID( $params, &$smarty )
{

    extract( $params );
    return the_ID(  );
}
$wpsmarty->register_function( "the_ID", "smarty_the_ID" );

/* the_title( $before = '', $after = '', $echo = true ) */
function smarty_the_title( $params, &$smarty )
{
    $before = '';
    $after = '';
    $echo = true;

    extract( $params );
    return the_title( $before, $after, $echo );
}
$wpsmarty->register_function( "the_title", "smarty_the_title" );

/* get_the_title( $id = 0 ) */
function smarty_get_the_title( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return get_the_title( $id );
}
$wpsmarty->register_function( "get_the_title", "smarty_get_the_title" );

/* get_the_guid(  $id = 0  ) */
function smarty_get_the_guid( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return get_the_guid( $id );
}
$wpsmarty->register_function( "get_the_guid", "smarty_get_the_guid" );

/* the_guid(  $id = 0  ) */
function smarty_the_guid( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return the_guid( $id );
}
$wpsmarty->register_function( "the_guid", "smarty_the_guid" );

/* the_content( $more_link_text = '(more...)', $stripteaser = 0, $more_file = '' ) */
function smarty_the_content( $params, &$smarty )
{
    $more_link_text = '(more...)';
    $stripteaser = 0;
    $more_file = '';

    extract( $params );
    return the_content( $more_link_text, $stripteaser, $more_file );
}
$wpsmarty->register_function( "the_content", "smarty_the_content" );

/* get_the_content( $more_link_text = '(more...)', $stripteaser = 0, $more_file = '' ) */
function smarty_get_the_content( $params, &$smarty )
{
    $more_link_text = '(more...)';
    $stripteaser = 0;
    $more_file = '';

    extract( $params );
    return get_the_content( $more_link_text, $stripteaser, $more_file );
}
$wpsmarty->register_function( "get_the_content", "smarty_get_the_content" );

/* the_excerpt(  ) */
function smarty_the_excerpt( $params, &$smarty )
{

    extract( $params );
    return the_excerpt(  );
}
$wpsmarty->register_function( "the_excerpt", "smarty_the_excerpt" );

/* get_the_excerpt( $fakeit = true ) */
function smarty_get_the_excerpt( $params, &$smarty )
{
    $fakeit = true;

    extract( $params );
    return get_the_excerpt( $fakeit );
}
$wpsmarty->register_function( "get_the_excerpt", "smarty_get_the_excerpt" );

/* wp_link_pages( $args = '' ) */
function smarty_wp_link_pages( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_link_pages( $args );
}
$wpsmarty->register_function( "wp_link_pages", "smarty_wp_link_pages" );

/* link_pages( $before='<br />', $after='<br />', $next_or_number='number', $nextpagelink='next page', $previouspagelink='previous page', $pagelink='%', $more_file='' ) */
function smarty_link_pages( $params, &$smarty )
{
    $before='<br />';
    $after='<br />';
    $next_or_number='number';
    $nextpagelink='next page';
    $previouspagelink='previous page';
    $pagelink='%';
    $more_file='';

    extract( $params );
    return link_pages( $before, $after, $next_or_number, $nextpagelink, $previouspagelink, $pagelink, $more_file );
}
$wpsmarty->register_function( "link_pages", "smarty_link_pages" );

/* get_post_custom(  $post_id = 0  ) */
function smarty_get_post_custom( $params, &$smarty )
{
    $post_id = 0;

    extract( $params );
    return get_post_custom( $post_id );
}
$wpsmarty->register_function( "get_post_custom", "smarty_get_post_custom" );

/* get_post_custom_keys(  ) */
function smarty_get_post_custom_keys( $params, &$smarty )
{

    extract( $params );
    return get_post_custom_keys(  );
}
$wpsmarty->register_function( "get_post_custom_keys", "smarty_get_post_custom_keys" );

/* get_post_custom_values( $key='' ) */
function smarty_get_post_custom_values( $params, &$smarty )
{
    $key='';

    extract( $params );
    return get_post_custom_values( $key );
}
$wpsmarty->register_function( "get_post_custom_values", "smarty_get_post_custom_values" );

/* post_custom(  $key = ''  ) */
function smarty_post_custom( $params, &$smarty )
{
    $key = '';

    extract( $params );
    return post_custom( $key );
}
$wpsmarty->register_function( "post_custom", "smarty_post_custom" );

/* the_meta(  ) */
function smarty_the_meta( $params, &$smarty )
{

    extract( $params );
    return the_meta(  );
}
$wpsmarty->register_function( "the_meta", "smarty_the_meta" );

/* &get_page_children( $page_id, $pages ) */
function &smarty_get_page_children( $params, &$smarty )
{

    extract( $params );
    return get_page_children( $page_id,  $pages );
}
$wpsmarty->register_function( "get_page_children", "smarty_get_page_children" );

/* &get_pages( $args = '' ) */
function &smarty_get_pages( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return get_pages( $args );
}
$wpsmarty->register_function( "get_pages", "smarty_get_pages" );

/* wp_list_pages( $args = '' ) */
function smarty_wp_list_pages( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_list_pages( $args );
}
$wpsmarty->register_function( "wp_list_pages", "smarty_wp_list_pages" );

/* _page_level_out( $parent, $page_tree, $args, $depth = 0, $echo = true ) */
function smarty__page_level_out( $params, &$smarty )
{
    $depth = 0;
    $echo = true;

    extract( $params );
    return _page_level_out( $parent,  $page_tree,  $args, $depth, $echo );
}
$wpsmarty->register_function( "_page_level_out", "smarty__page_level_out" );

/* get_the_author( $idmode = '' ) */
function smarty_get_the_author( $params, &$smarty )
{
    $idmode = '';

    extract( $params );
    return get_the_author( $idmode );
}
$wpsmarty->register_function( "get_the_author", "smarty_get_the_author" );

/* the_author( $idmode = '', $echo = true ) */
function smarty_the_author( $params, &$smarty )
{
    $idmode = '';
    $echo = true;

    extract( $params );
    return the_author( $idmode, $echo );
}
$wpsmarty->register_function( "the_author", "smarty_the_author" );

/* get_the_author_description(  ) */
function smarty_get_the_author_description( $params, &$smarty )
{

    extract( $params );
    return get_the_author_description(  );
}
$wpsmarty->register_function( "get_the_author_description", "smarty_get_the_author_description" );

/* the_author_description(  ) */
function smarty_the_author_description( $params, &$smarty )
{

    extract( $params );
    return the_author_description(  );
}
$wpsmarty->register_function( "the_author_description", "smarty_the_author_description" );

/* get_the_author_login(  ) */
function smarty_get_the_author_login( $params, &$smarty )
{

    extract( $params );
    return get_the_author_login(  );
}
$wpsmarty->register_function( "get_the_author_login", "smarty_get_the_author_login" );

/* the_author_login(  ) */
function smarty_the_author_login( $params, &$smarty )
{

    extract( $params );
    return the_author_login(  );
}
$wpsmarty->register_function( "the_author_login", "smarty_the_author_login" );

/* get_the_author_firstname(  ) */
function smarty_get_the_author_firstname( $params, &$smarty )
{

    extract( $params );
    return get_the_author_firstname(  );
}
$wpsmarty->register_function( "get_the_author_firstname", "smarty_get_the_author_firstname" );

/* the_author_firstname(  ) */
function smarty_the_author_firstname( $params, &$smarty )
{

    extract( $params );
    return the_author_firstname(  );
}
$wpsmarty->register_function( "the_author_firstname", "smarty_the_author_firstname" );

/* get_the_author_lastname(  ) */
function smarty_get_the_author_lastname( $params, &$smarty )
{

    extract( $params );
    return get_the_author_lastname(  );
}
$wpsmarty->register_function( "get_the_author_lastname", "smarty_get_the_author_lastname" );

/* the_author_lastname(  ) */
function smarty_the_author_lastname( $params, &$smarty )
{

    extract( $params );
    return the_author_lastname(  );
}
$wpsmarty->register_function( "the_author_lastname", "smarty_the_author_lastname" );

/* get_the_author_nickname(  ) */
function smarty_get_the_author_nickname( $params, &$smarty )
{

    extract( $params );
    return get_the_author_nickname(  );
}
$wpsmarty->register_function( "get_the_author_nickname", "smarty_get_the_author_nickname" );

/* the_author_nickname(  ) */
function smarty_the_author_nickname( $params, &$smarty )
{

    extract( $params );
    return the_author_nickname(  );
}
$wpsmarty->register_function( "the_author_nickname", "smarty_the_author_nickname" );

/* get_the_author_ID(  ) */
function smarty_get_the_author_ID( $params, &$smarty )
{

    extract( $params );
    return get_the_author_ID(  );
}
$wpsmarty->register_function( "get_the_author_ID", "smarty_get_the_author_ID" );

/* the_author_ID(  ) */
function smarty_the_author_ID( $params, &$smarty )
{

    extract( $params );
    return the_author_ID(  );
}
$wpsmarty->register_function( "the_author_ID", "smarty_the_author_ID" );

/* get_the_author_email(  ) */
function smarty_get_the_author_email( $params, &$smarty )
{

    extract( $params );
    return get_the_author_email(  );
}
$wpsmarty->register_function( "get_the_author_email", "smarty_get_the_author_email" );

/* the_author_email(  ) */
function smarty_the_author_email( $params, &$smarty )
{

    extract( $params );
    return the_author_email(  );
}
$wpsmarty->register_function( "the_author_email", "smarty_the_author_email" );

/* get_the_author_url(  ) */
function smarty_get_the_author_url( $params, &$smarty )
{

    extract( $params );
    return get_the_author_url(  );
}
$wpsmarty->register_function( "get_the_author_url", "smarty_get_the_author_url" );

/* the_author_url(  ) */
function smarty_the_author_url( $params, &$smarty )
{

    extract( $params );
    return the_author_url(  );
}
$wpsmarty->register_function( "the_author_url", "smarty_the_author_url" );

/* get_the_author_icq(  ) */
function smarty_get_the_author_icq( $params, &$smarty )
{

    extract( $params );
    return get_the_author_icq(  );
}
$wpsmarty->register_function( "get_the_author_icq", "smarty_get_the_author_icq" );

/* the_author_icq(  ) */
function smarty_the_author_icq( $params, &$smarty )
{

    extract( $params );
    return the_author_icq(  );
}
$wpsmarty->register_function( "the_author_icq", "smarty_the_author_icq" );

/* get_the_author_aim(  ) */
function smarty_get_the_author_aim( $params, &$smarty )
{

    extract( $params );
    return get_the_author_aim(  );
}
$wpsmarty->register_function( "get_the_author_aim", "smarty_get_the_author_aim" );

/* the_author_aim(  ) */
function smarty_the_author_aim( $params, &$smarty )
{

    extract( $params );
    return the_author_aim(  );
}
$wpsmarty->register_function( "the_author_aim", "smarty_the_author_aim" );

/* get_the_author_yim(  ) */
function smarty_get_the_author_yim( $params, &$smarty )
{

    extract( $params );
    return get_the_author_yim(  );
}
$wpsmarty->register_function( "get_the_author_yim", "smarty_get_the_author_yim" );

/* the_author_yim(  ) */
function smarty_the_author_yim( $params, &$smarty )
{

    extract( $params );
    return the_author_yim(  );
}
$wpsmarty->register_function( "the_author_yim", "smarty_the_author_yim" );

/* get_the_author_msn(  ) */
function smarty_get_the_author_msn( $params, &$smarty )
{

    extract( $params );
    return get_the_author_msn(  );
}
$wpsmarty->register_function( "get_the_author_msn", "smarty_get_the_author_msn" );

/* the_author_msn(  ) */
function smarty_the_author_msn( $params, &$smarty )
{

    extract( $params );
    return the_author_msn(  );
}
$wpsmarty->register_function( "the_author_msn", "smarty_the_author_msn" );

/* get_the_author_posts(  ) */
function smarty_get_the_author_posts( $params, &$smarty )
{

    extract( $params );
    return get_the_author_posts(  );
}
$wpsmarty->register_function( "get_the_author_posts", "smarty_get_the_author_posts" );

/* the_author_posts(  ) */
function smarty_the_author_posts( $params, &$smarty )
{

    extract( $params );
    return the_author_posts(  );
}
$wpsmarty->register_function( "the_author_posts", "smarty_the_author_posts" );

/* the_author_posts_link( $idmode='' ) */
function smarty_the_author_posts_link( $params, &$smarty )
{
    $idmode='';

    extract( $params );
    return the_author_posts_link( $idmode );
}
$wpsmarty->register_function( "the_author_posts_link", "smarty_the_author_posts_link" );

/* get_author_link( $echo = false, $author_id, $author_nicename ) */
function smarty_get_author_link( $params, &$smarty )
{
    $echo = false;

    extract( $params );
    return get_author_link( $echo,  $author_id,  $author_nicename );
}
$wpsmarty->register_function( "get_author_link", "smarty_get_author_link" );

/* wp_list_authors( $args = '' ) */
function smarty_wp_list_authors( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_list_authors( $args );
}
$wpsmarty->register_function( "wp_list_authors", "smarty_wp_list_authors" );

/* list_authors( $optioncount = false, $exclude_admin = true, $show_fullname = false, $hide_empty = true, $feed = '', $feed_image = '' ) */
function smarty_list_authors( $params, &$smarty )
{
    $optioncount = false;
    $exclude_admin = true;
    $show_fullname = false;
    $hide_empty = true;
    $feed = '';
    $feed_image = '';

    extract( $params );
    return list_authors( $optioncount, $exclude_admin, $show_fullname, $hide_empty, $feed, $feed_image );
}
$wpsmarty->register_function( "list_authors", "smarty_list_authors" );

/* get_linksbyname( $cat_name = "noname", $before = '', $after = '<br />',
                         $between = " ", $show_images = true, $orderby = 'id',
                         $show_description = true, $show_rating = false,
                         $limit = -1, $show_updated = 0 ) */
function smarty_get_linksbyname( $params, &$smarty )
{
    $cat_name = "noname";
    $before = '';
    $after = '<br />';
    $between = " ";
    $show_images = true;
    $orderby = 'id';
    $show_description = true;
    $show_rating = false;
    $limit = -1;
    $show_updated = 0;

    extract( $params );
    return get_linksbyname( $cat_name, $before, $after, $between, $show_images, $orderby, $show_description, $show_rating, $limit, $show_updated );
}
$wpsmarty->register_function( "get_linksbyname", "smarty_get_linksbyname" );

/* bool_from_yn( $yn ) */
function smarty_bool_from_yn( $params, &$smarty )
{

    extract( $params );
    return bool_from_yn( $yn );
}
$wpsmarty->register_function( "bool_from_yn", "smarty_bool_from_yn" );

/* wp_get_linksbyname( $category, $args = '' ) */
function smarty_wp_get_linksbyname( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_get_linksbyname( $category, $args );
}
$wpsmarty->register_function( "wp_get_linksbyname", "smarty_wp_get_linksbyname" );

/* wp_get_links( $args = '' ) */
function smarty_wp_get_links( $params, &$smarty )
{
    $args = '';

    extract( $params );
    return wp_get_links( $args );
}
$wpsmarty->register_function( "wp_get_links", "smarty_wp_get_links" );

/* get_links( $category = -1, $before = '', $after = '<br />',
                   $between = ' ', $show_images = true, $orderby = 'name',
                   $show_description = true, $show_rating = false,
                   $limit = -1, $show_updated = 1, $echo = true ) */
function smarty_get_links( $params, &$smarty )
{
    $category = -1;
    $before = '';
    $after = '<br />';
    $between = ' ';
    $show_images = true;
    $orderby = 'name';
    $show_description = true;
    $show_rating = false;
    $limit = -1;
    $show_updated = 1;
    $echo = true;

    extract( $params );
    return get_links( $category, $before, $after, $between, $show_images, $orderby, $show_description, $show_rating, $limit, $show_updated, $echo );
}
$wpsmarty->register_function( "get_links", "smarty_get_links" );

/* get_linkobjectsbyname( $cat_name = "noname" , $orderby = 'name', $limit = -1 ) */
function smarty_get_linkobjectsbyname( $params, &$smarty )
{
    $cat_name = "noname";
    $orderby = 'name';
    $limit = -1;

    extract( $params );
    return get_linkobjectsbyname( $cat_name, $orderby, $limit );
}
$wpsmarty->register_function( "get_linkobjectsbyname", "smarty_get_linkobjectsbyname" );

/* get_linkobjects( $category = -1, $orderby = 'name', $limit = -1 ) */
function smarty_get_linkobjects( $params, &$smarty )
{
    $category = -1;
    $orderby = 'name';
    $limit = -1;

    extract( $params );
    return get_linkobjects( $category, $orderby, $limit );
}
$wpsmarty->register_function( "get_linkobjects", "smarty_get_linkobjects" );

/* get_linkrating( $link ) */
function smarty_get_linkrating( $params, &$smarty )
{

    extract( $params );
    return get_linkrating( $link );
}
$wpsmarty->register_function( "get_linkrating", "smarty_get_linkrating" );

/* get_linksbyname_withrating( $cat_name = "noname", $before = '',
                                    $after = '<br />', $between = " ",
                                    $show_images = true, $orderby = 'id',
                                    $show_description = true, $limit = -1, $show_updated = 0 ) */
function smarty_get_linksbyname_withrating( $params, &$smarty )
{
    $cat_name = "noname";
    $before = '';
    $after = '<br />';
    $between = " ";
    $show_images = true;
    $orderby = 'id';
    $show_description = true;
    $limit = -1;
    $show_updated = 0;

    extract( $params );
    return get_linksbyname_withrating( $cat_name, $before, $after, $between, $show_images, $orderby, $show_description, $limit, $show_updated );
}
$wpsmarty->register_function( "get_linksbyname_withrating", "smarty_get_linksbyname_withrating" );

/* get_links_withrating( $category = -1, $before = '', $after = '<br />',
                              $between = " ", $show_images = true,
                              $orderby = 'id', $show_description = true,
                              $limit = -1, $show_updated = 0 ) */
function smarty_get_links_withrating( $params, &$smarty )
{
    $category = -1;
    $before = '';
    $after = '<br />';
    $between = " ";
    $show_images = true;
    $orderby = 'id';
    $show_description = true;
    $limit = -1;
    $show_updated = 0;

    extract( $params );
    return get_links_withrating( $category, $before, $after, $between, $show_images, $orderby, $show_description, $limit, $show_updated );
}
$wpsmarty->register_function( "get_links_withrating", "smarty_get_links_withrating" );

/* get_linkcatname( $id = 0 ) */
function smarty_get_linkcatname( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return get_linkcatname( $id );
}
$wpsmarty->register_function( "get_linkcatname", "smarty_get_linkcatname" );

/* get_autotoggle( $id = 0 ) */
function smarty_get_autotoggle( $params, &$smarty )
{
    $id = 0;

    extract( $params );
    return get_autotoggle( $id );
}
$wpsmarty->register_function( "get_autotoggle", "smarty_get_autotoggle" );

/* links_popup_script( $text = 'Links', $width=400, $height=400,
                            $file='links.all.php', $count = true ) */
function smarty_links_popup_script( $params, &$smarty )
{
    $text = 'Links';
    $width=400;
    $height=400;
    $file='links.all.php';
    $count = true;

    extract( $params );
    return links_popup_script( $text, $width, $height, $file, $count );
}
$wpsmarty->register_function( "links_popup_script", "smarty_links_popup_script" );

/* get_links_list( $order = 'name', $hide_if_empty = 'obsolete' ) */
function smarty_get_links_list( $params, &$smarty )
{
    $order = 'name';
    $hide_if_empty = 'obsolete';

    extract( $params );
    return get_links_list( $order, $hide_if_empty );
}
$wpsmarty->register_function( "get_links_list", "smarty_get_links_list" );

/* get_profile( $field, $user = false ) */
function smarty_get_profile( $params, &$smarty )
{
    $user = false;

    extract( $params );
    return get_profile( $field, $user );
}
$wpsmarty->register_function( "get_profile", "smarty_get_profile" );

/* mysql2date( $dateformatstring, $mysqlstring, $translate = true ) */
function smarty_mysql2date( $params, &$smarty )
{
    $translate = true;

    extract( $params );
    return mysql2date( $dateformatstring,  $mysqlstring, $translate );
}
$wpsmarty->register_function( "mysql2date", "smarty_mysql2date" );

/* current_time( $type, $gmt = 0 ) */
function smarty_current_time( $params, &$smarty )
{
    $gmt = 0;

    extract( $params );
    return current_time( $type, $gmt );
}
$wpsmarty->register_function( "current_time", "smarty_current_time" );

/* date_i18n( $dateformatstring, $unixtimestamp ) */
function smarty_date_i18n( $params, &$smarty )
{

    extract( $params );
    return date_i18n( $dateformatstring,  $unixtimestamp );
}
$wpsmarty->register_function( "date_i18n", "smarty_date_i18n" );

/* get_weekstartend( $mysqlstring, $start_of_week ) */
function smarty_get_weekstartend( $params, &$smarty )
{

    extract( $params );
    return get_weekstartend( $mysqlstring,  $start_of_week );
}
$wpsmarty->register_function( "get_weekstartend", "smarty_get_weekstartend" );

/* get_lastpostdate( $timezone = 'server' ) */
function smarty_get_lastpostdate( $params, &$smarty )
{
    $timezone = 'server';

    extract( $params );
    return get_lastpostdate( $timezone );
}
$wpsmarty->register_function( "get_lastpostdate", "smarty_get_lastpostdate" );

/* get_lastpostmodified( $timezone = 'server' ) */
function smarty_get_lastpostmodified( $params, &$smarty )
{
    $timezone = 'server';

    extract( $params );
    return get_lastpostmodified( $timezone );
}
$wpsmarty->register_function( "get_lastpostmodified", "smarty_get_lastpostmodified" );

/* user_pass_ok( $user_login,$user_pass ) */
function smarty_user_pass_ok( $params, &$smarty )
{

    extract( $params );
    return user_pass_ok( $user_login, $user_pass );
}
$wpsmarty->register_function( "user_pass_ok", "smarty_user_pass_ok" );

/* get_usernumposts( $userid ) */
function smarty_get_usernumposts( $params, &$smarty )
{

    extract( $params );
    return get_usernumposts( $userid );
}
$wpsmarty->register_function( "get_usernumposts", "smarty_get_usernumposts" );

/* url_to_postid( $url ) */
function smarty_url_to_postid( $params, &$smarty )
{

    extract( $params );
    return url_to_postid( $url );
}
$wpsmarty->register_function( "url_to_postid", "smarty_url_to_postid" );

/* get_settings( $setting ) */
function smarty_get_settings( $params, &$smarty )
{

    extract( $params );
    return get_settings( $setting );
}
$wpsmarty->register_function( "get_settings", "smarty_get_settings" );

/* get_option( $option ) */
function smarty_get_option( $params, &$smarty )
{

    extract( $params );
    return get_option( $option );
}
$wpsmarty->register_function( "get_option", "smarty_get_option" );

/* form_option( $option ) */
function smarty_form_option( $params, &$smarty )
{

    extract( $params );
    return form_option( $option );
}
$wpsmarty->register_function( "form_option", "smarty_form_option" );

/* get_alloptions(  ) */
function smarty_get_alloptions( $params, &$smarty )
{

    extract( $params );
    return get_alloptions(  );
}
$wpsmarty->register_function( "get_alloptions", "smarty_get_alloptions" );

/* update_option( $option_name, $newvalue ) */
function smarty_update_option( $params, &$smarty )
{

    extract( $params );
    return update_option( $option_name,  $newvalue );
}
$wpsmarty->register_function( "update_option", "smarty_update_option" );

/* add_option( $name, $value = '', $description = '', $autoload = 'yes' ) */
function smarty_add_option( $params, &$smarty )
{
    $value = '';
    $description = '';
    $autoload = 'yes';

    extract( $params );
    return add_option( $name, $value, $description, $autoload );
}
$wpsmarty->register_function( "add_option", "smarty_add_option" );

/* delete_option( $name ) */
function smarty_delete_option( $params, &$smarty )
{

    extract( $params );
    return delete_option( $name );
}
$wpsmarty->register_function( "delete_option", "smarty_delete_option" );

/* add_post_meta( $post_id, $key, $value, $unique = false ) */
function smarty_add_post_meta( $params, &$smarty )
{
    $unique = false;

    extract( $params );
    return add_post_meta( $post_id,  $key,  $value, $unique );
}
$wpsmarty->register_function( "add_post_meta", "smarty_add_post_meta" );

/* delete_post_meta( $post_id, $key, $value = '' ) */
function smarty_delete_post_meta( $params, &$smarty )
{
    $value = '';

    extract( $params );
    return delete_post_meta( $post_id,  $key, $value );
}
$wpsmarty->register_function( "delete_post_meta", "smarty_delete_post_meta" );

/* get_post_meta( $post_id, $key, $single = false ) */
function smarty_get_post_meta( $params, &$smarty )
{
    $single = false;

    extract( $params );
    return get_post_meta( $post_id,  $key, $single );
}
$wpsmarty->register_function( "get_post_meta", "smarty_get_post_meta" );

/* update_post_meta( $post_id, $key, $value, $prev_value = '' ) */
function smarty_update_post_meta( $params, &$smarty )
{
    $prev_value = '';

    extract( $params );
    return update_post_meta( $post_id,  $key,  $value, $prev_value );
}
$wpsmarty->register_function( "update_post_meta", "smarty_update_post_meta" );

/* get_postdata( $postid ) */
function smarty_get_postdata( $params, &$smarty )
{

    extract( $params );
    return get_postdata( $postid );
}
$wpsmarty->register_function( "get_postdata", "smarty_get_postdata" );

/* &get_post( &$post, $output = OBJECT ) */
function &smarty_get_post( $params, &$smarty )
{
    $output = OBJECT;

    extract( $params );
    return get_post( &$post, $output );
}
$wpsmarty->register_function( "get_post", "smarty_get_post" );

/* &get_page( &$page, $output = OBJECT ) */
function &smarty_get_page( $params, &$smarty )
{
    $output = OBJECT;

    extract( $params );
    return get_page( &$page, $output );
}
$wpsmarty->register_function( "get_page", "smarty_get_page" );

/* &get_category( &$category, $output = OBJECT ) */
function &smarty_get_category( $params, &$smarty )
{
    $output = OBJECT;

    extract( $params );
    return get_category( &$category, $output );
}
$wpsmarty->register_function( "get_category", "smarty_get_category" );

/* &get_comment( &$comment, $output = OBJECT ) */
function &smarty_get_comment( $params, &$smarty )
{
    $output = OBJECT;

    extract( $params );
    return get_comment( &$comment, $output );
}
$wpsmarty->register_function( "get_comment", "smarty_get_comment" );

/* get_catname( $cat_ID ) */
function smarty_get_catname( $params, &$smarty )
{

    extract( $params );
    return get_catname( $cat_ID );
}
$wpsmarty->register_function( "get_catname", "smarty_get_catname" );

/* gzip_compression(  ) */
function smarty_gzip_compression( $params, &$smarty )
{

    extract( $params );
    return gzip_compression(  );
}
$wpsmarty->register_function( "gzip_compression", "smarty_gzip_compression" );

/* timer_stop( $display = 0, $precision = 3 ) */
function smarty_timer_stop( $params, &$smarty )
{
    $display = 0;
    $precision = 3;

    extract( $params );
    return timer_stop( $display, $precision );
}
$wpsmarty->register_function( "timer_stop", "smarty_timer_stop" );

/* weblog_ping( $server = '', $path = '' ) */
function smarty_weblog_ping( $params, &$smarty )
{
    $server = '';
    $path = '';

    extract( $params );
    return weblog_ping( $server, $path );
}
$wpsmarty->register_function( "weblog_ping", "smarty_weblog_ping" );

/* generic_ping( $post_id = 0 ) */
function smarty_generic_ping( $params, &$smarty )
{
    $post_id = 0;

    extract( $params );
    return generic_ping( $post_id );
}
$wpsmarty->register_function( "generic_ping", "smarty_generic_ping" );

/* trackback( $trackback_url, $title, $excerpt, $ID ) */
function smarty_trackback( $params, &$smarty )
{

    extract( $params );
    return trackback( $trackback_url,  $title,  $excerpt,  $ID );
}
$wpsmarty->register_function( "trackback", "smarty_trackback" );

/* make_url_footnote( $content ) */
function smarty_make_url_footnote( $params, &$smarty )
{

    extract( $params );
    return make_url_footnote( $content );
}
$wpsmarty->register_function( "make_url_footnote", "smarty_make_url_footnote" );

/* xmlrpc_getposttitle( $content ) */
function smarty_xmlrpc_getposttitle( $params, &$smarty )
{

    extract( $params );
    return xmlrpc_getposttitle( $content );
}
$wpsmarty->register_function( "xmlrpc_getposttitle", "smarty_xmlrpc_getposttitle" );

/* xmlrpc_getpostcategory( $content ) */
function smarty_xmlrpc_getpostcategory( $params, &$smarty )
{

    extract( $params );
    return xmlrpc_getpostcategory( $content );
}
$wpsmarty->register_function( "xmlrpc_getpostcategory", "smarty_xmlrpc_getpostcategory" );

/* xmlrpc_removepostdata( $content ) */
function smarty_xmlrpc_removepostdata( $params, &$smarty )
{

    extract( $params );
    return xmlrpc_removepostdata( $content );
}
$wpsmarty->register_function( "xmlrpc_removepostdata", "smarty_xmlrpc_removepostdata" );

/* debug_fopen( $filename, $mode ) */
function smarty_debug_fopen( $params, &$smarty )
{

    extract( $params );
    return debug_fopen( $filename,  $mode );
}
$wpsmarty->register_function( "debug_fopen", "smarty_debug_fopen" );

/* debug_fwrite( $fp, $string ) */
function smarty_debug_fwrite( $params, &$smarty )
{

    extract( $params );
    return debug_fwrite( $fp,  $string );
}
$wpsmarty->register_function( "debug_fwrite", "smarty_debug_fwrite" );

/* debug_fclose( $fp ) */
function smarty_debug_fclose( $params, &$smarty )
{

    extract( $params );
    return debug_fclose( $fp );
}
$wpsmarty->register_function( "debug_fclose", "smarty_debug_fclose" );

/* do_enclose(  $content, $post_ID  ) */
function smarty_do_enclose( $params, &$smarty )
{

    extract( $params );
    return do_enclose(  $content,  $post_ID  );
}
$wpsmarty->register_function( "do_enclose", "smarty_do_enclose" );

/* wp_get_http_headers(  $url  ) */
function smarty_wp_get_http_headers( $params, &$smarty )
{

    extract( $params );
    return wp_get_http_headers(  $url  );
}
$wpsmarty->register_function( "wp_get_http_headers", "smarty_wp_get_http_headers" );

/* start_wp(  ) */
function smarty_start_wp( $params, &$smarty )
{

    extract( $params );
    return start_wp(  );
}
$wpsmarty->register_function( "start_wp", "smarty_start_wp" );

/* setup_postdata( $post ) */
function smarty_setup_postdata( $params, &$smarty )
{

    extract( $params );
    return setup_postdata( $post );
}
$wpsmarty->register_function( "setup_postdata", "smarty_setup_postdata" );

/* is_new_day(  ) */
function smarty_is_new_day( $params, &$smarty )
{

    extract( $params );
    return is_new_day(  );
}
$wpsmarty->register_function( "is_new_day", "smarty_is_new_day" );

/* merge_filters( $tag ) */
function smarty_merge_filters( $params, &$smarty )
{

    extract( $params );
    return merge_filters( $tag );
}
$wpsmarty->register_function( "merge_filters", "smarty_merge_filters" );

/* apply_filters( $tag, $string ) */
function smarty_apply_filters( $params, &$smarty )
{

    extract( $params );
    return apply_filters( $tag,  $string );
}
$wpsmarty->register_function( "apply_filters", "smarty_apply_filters" );

/* add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) */
function smarty_add_filter( $params, &$smarty )
{
    $priority = 10;
    $accepted_args = 1;

    extract( $params );
    return add_filter( $tag,  $function_to_add, $priority, $accepted_args );
}
$wpsmarty->register_function( "add_filter", "smarty_add_filter" );

/* remove_filter( $tag, $function_to_remove, $priority = 10, $accepted_args = 1 ) */
function smarty_remove_filter( $params, &$smarty )
{
    $priority = 10;
    $accepted_args = 1;

    extract( $params );
    return remove_filter( $tag,  $function_to_remove, $priority, $accepted_args );
}
$wpsmarty->register_function( "remove_filter", "smarty_remove_filter" );

/* do_action( $tag, $arg = '' ) */
function smarty_do_action( $params, &$smarty )
{
    $arg = '';

    extract( $params );
    return do_action( $tag, $arg );
}
$wpsmarty->register_function( "do_action", "smarty_do_action" );

/* add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) */
function smarty_add_action( $params, &$smarty )
{
    $priority = 10;
    $accepted_args = 1;

    extract( $params );
    return add_action( $tag,  $function_to_add, $priority, $accepted_args );
}
$wpsmarty->register_function( "add_action", "smarty_add_action" );

/* remove_action( $tag, $function_to_remove, $priority = 10, $accepted_args = 1 ) */
function smarty_remove_action( $params, &$smarty )
{
    $priority = 10;
    $accepted_args = 1;

    extract( $params );
    return remove_action( $tag,  $function_to_remove, $priority, $accepted_args );
}
$wpsmarty->register_function( "remove_action", "smarty_remove_action" );

/* get_page_uri( $page_id ) */
function smarty_get_page_uri( $params, &$smarty )
{

    extract( $params );
    return get_page_uri( $page_id );
}
$wpsmarty->register_function( "get_page_uri", "smarty_get_page_uri" );

/* get_posts( $args ) */
function smarty_get_posts( $params, &$smarty )
{

    extract( $params );
    return get_posts( $args );
}
$wpsmarty->register_function( "get_posts", "smarty_get_posts" );

/* &query_posts( $query ) */
function &smarty_query_posts( $params, &$smarty )
{

    extract( $params );
    return query_posts( $query );
}
$wpsmarty->register_function( "query_posts", "smarty_query_posts" );

/* update_post_cache( &$posts ) */
function smarty_update_post_cache( $params, &$smarty )
{

    extract( $params );
    return update_post_cache( &$posts );
}
$wpsmarty->register_function( "update_post_cache", "smarty_update_post_cache" );

/* update_page_cache( &$pages ) */
function smarty_update_page_cache( $params, &$smarty )
{

    extract( $params );
    return update_page_cache( &$pages );
}
$wpsmarty->register_function( "update_page_cache", "smarty_update_page_cache" );

/* update_post_category_cache( $post_ids ) */
function smarty_update_post_category_cache( $params, &$smarty )
{

    extract( $params );
    return update_post_category_cache( $post_ids );
}
$wpsmarty->register_function( "update_post_category_cache", "smarty_update_post_category_cache" );

/* update_post_caches( &$posts ) */
function smarty_update_post_caches( $params, &$smarty )
{

    extract( $params );
    return update_post_caches( &$posts );
}
$wpsmarty->register_function( "update_post_caches", "smarty_update_post_caches" );

/* update_category_cache(  ) */
function smarty_update_category_cache( $params, &$smarty )
{

    extract( $params );
    return update_category_cache(  );
}
$wpsmarty->register_function( "update_category_cache", "smarty_update_category_cache" );

/* wp_head(  ) */
function smarty_wp_head( $params, &$smarty )
{

    extract( $params );
    return wp_head(  );
}
$wpsmarty->register_function( "wp_head", "smarty_wp_head" );

/* wp_footer(  ) */
function smarty_wp_footer( $params, &$smarty )
{

    extract( $params );
    return wp_footer(  );
}
$wpsmarty->register_function( "wp_footer", "smarty_wp_footer" );

/* is_single ( $post = '' ) */
function smarty_is_single ( $params, &$smarty )
{
    $post = '';

    extract( $params );
    return is_single ( $post );
}
$wpsmarty->register_function( "is_single ", "smarty_is_single " );

/* is_page ( $page = '' ) */
function smarty_is_page ( $params, &$smarty )
{
    $page = '';

    extract( $params );
    return is_page ( $page );
}
$wpsmarty->register_function( "is_page ", "smarty_is_page " );

/* is_archive (  ) */
function smarty_is_archive ( $params, &$smarty )
{

    extract( $params );
    return is_archive (  );
}
$wpsmarty->register_function( "is_archive ", "smarty_is_archive " );

/* is_date (  ) */
function smarty_is_date ( $params, &$smarty )
{

    extract( $params );
    return is_date (  );
}
$wpsmarty->register_function( "is_date ", "smarty_is_date " );

/* is_year (  ) */
function smarty_is_year ( $params, &$smarty )
{

    extract( $params );
    return is_year (  );
}
$wpsmarty->register_function( "is_year ", "smarty_is_year " );

/* is_month (  ) */
function smarty_is_month ( $params, &$smarty )
{

    extract( $params );
    return is_month (  );
}
$wpsmarty->register_function( "is_month ", "smarty_is_month " );

/* is_day (  ) */
function smarty_is_day ( $params, &$smarty )
{

    extract( $params );
    return is_day (  );
}
$wpsmarty->register_function( "is_day ", "smarty_is_day " );

/* is_time (  ) */
function smarty_is_time ( $params, &$smarty )
{

    extract( $params );
    return is_time (  );
}
$wpsmarty->register_function( "is_time ", "smarty_is_time " );

/* is_author ( $author = '' ) */
function smarty_is_author ( $params, &$smarty )
{
    $author = '';

    extract( $params );
    return is_author ( $author );
}
$wpsmarty->register_function( "is_author ", "smarty_is_author " );

/* is_category ( $category = '' ) */
function smarty_is_category ( $params, &$smarty )
{
    $category = '';

    extract( $params );
    return is_category ( $category );
}
$wpsmarty->register_function( "is_category ", "smarty_is_category " );

/* is_search (  ) */
function smarty_is_search ( $params, &$smarty )
{

    extract( $params );
    return is_search (  );
}
$wpsmarty->register_function( "is_search ", "smarty_is_search " );

/* is_feed (  ) */
function smarty_is_feed ( $params, &$smarty )
{

    extract( $params );
    return is_feed (  );
}
$wpsmarty->register_function( "is_feed ", "smarty_is_feed " );

/* is_trackback (  ) */
function smarty_is_trackback ( $params, &$smarty )
{

    extract( $params );
    return is_trackback (  );
}
$wpsmarty->register_function( "is_trackback ", "smarty_is_trackback " );

/* is_admin (  ) */
function smarty_is_admin ( $params, &$smarty )
{

    extract( $params );
    return is_admin (  );
}
$wpsmarty->register_function( "is_admin ", "smarty_is_admin " );

/* is_home (  ) */
function smarty_is_home ( $params, &$smarty )
{

    extract( $params );
    return is_home (  );
}
$wpsmarty->register_function( "is_home ", "smarty_is_home " );

/* is_404 (  ) */
function smarty_is_404 ( $params, &$smarty )
{

    extract( $params );
    return is_404 (  );
}
$wpsmarty->register_function( "is_404 ", "smarty_is_404 " );

/* is_comments_popup (  ) */
function smarty_is_comments_popup ( $params, &$smarty )
{

    extract( $params );
    return is_comments_popup (  );
}
$wpsmarty->register_function( "is_comments_popup ", "smarty_is_comments_popup " );

/* is_paged (  ) */
function smarty_is_paged ( $params, &$smarty )
{

    extract( $params );
    return is_paged (  );
}
$wpsmarty->register_function( "is_paged ", "smarty_is_paged " );

/* get_query_var( $var ) */
function smarty_get_query_var( $params, &$smarty )
{

    extract( $params );
    return get_query_var( $var );
}
$wpsmarty->register_function( "get_query_var", "smarty_get_query_var" );

/* have_posts(  ) */
function smarty_have_posts( $params, &$smarty )
{

    extract( $params );
    return have_posts(  );
}
$wpsmarty->register_function( "have_posts", "smarty_have_posts" );

/* rewind_posts(  ) */
function smarty_rewind_posts( $params, &$smarty )
{

    extract( $params );
    return rewind_posts(  );
}
$wpsmarty->register_function( "rewind_posts", "smarty_rewind_posts" );

/* the_post(  ) */
function smarty_the_post( $params, &$smarty )
{

    extract( $params );
    return the_post(  );
}
$wpsmarty->register_function( "the_post", "smarty_the_post" );

/* get_theme_root(  ) */
function smarty_get_theme_root( $params, &$smarty )
{

    extract( $params );
    return get_theme_root(  );
}
$wpsmarty->register_function( "get_theme_root", "smarty_get_theme_root" );

/* get_theme_root_uri(  ) */
function smarty_get_theme_root_uri( $params, &$smarty )
{

    extract( $params );
    return get_theme_root_uri(  );
}
$wpsmarty->register_function( "get_theme_root_uri", "smarty_get_theme_root_uri" );

/* get_stylesheet(  ) */
function smarty_get_stylesheet( $params, &$smarty )
{

    extract( $params );
    return get_stylesheet(  );
}
$wpsmarty->register_function( "get_stylesheet", "smarty_get_stylesheet" );

/* get_stylesheet_directory(  ) */
function smarty_get_stylesheet_directory( $params, &$smarty )
{

    extract( $params );
    return get_stylesheet_directory(  );
}
$wpsmarty->register_function( "get_stylesheet_directory", "smarty_get_stylesheet_directory" );

/* get_stylesheet_directory_uri(  ) */
function smarty_get_stylesheet_directory_uri( $params, &$smarty )
{

    extract( $params );
    return get_stylesheet_directory_uri(  );
}
$wpsmarty->register_function( "get_stylesheet_directory_uri", "smarty_get_stylesheet_directory_uri" );

/* get_stylesheet_uri(  ) */
function smarty_get_stylesheet_uri( $params, &$smarty )
{

    extract( $params );
    return get_stylesheet_uri(  );
}
$wpsmarty->register_function( "get_stylesheet_uri", "smarty_get_stylesheet_uri" );

/* get_template(  ) */
function smarty_get_template( $params, &$smarty )
{

    extract( $params );
    return get_template(  );
}
$wpsmarty->register_function( "get_template", "smarty_get_template" );

/* get_template_directory(  ) */
function smarty_get_template_directory( $params, &$smarty )
{

    extract( $params );
    return get_template_directory(  );
}
$wpsmarty->register_function( "get_template_directory", "smarty_get_template_directory" );

/* get_template_directory_uri(  ) */
function smarty_get_template_directory_uri( $params, &$smarty )
{

    extract( $params );
    return get_template_directory_uri(  );
}
$wpsmarty->register_function( "get_template_directory_uri", "smarty_get_template_directory_uri" );

/* get_theme_data( $theme_file ) */
function smarty_get_theme_data( $params, &$smarty )
{

    extract( $params );
    return get_theme_data( $theme_file );
}
$wpsmarty->register_function( "get_theme_data", "smarty_get_theme_data" );

/* get_themes(  ) */
function smarty_get_themes( $params, &$smarty )
{

    extract( $params );
    return get_themes(  );
}
$wpsmarty->register_function( "get_themes", "smarty_get_themes" );

/* get_theme( $theme ) */
function smarty_get_theme( $params, &$smarty )
{

    extract( $params );
    return get_theme( $theme );
}
$wpsmarty->register_function( "get_theme", "smarty_get_theme" );

/* get_current_theme(  ) */
function smarty_get_current_theme( $params, &$smarty )
{

    extract( $params );
    return get_current_theme(  );
}
$wpsmarty->register_function( "get_current_theme", "smarty_get_current_theme" );

/* get_query_template( $type ) */
function smarty_get_query_template( $params, &$smarty )
{

    extract( $params );
    return get_query_template( $type );
}
$wpsmarty->register_function( "get_query_template", "smarty_get_query_template" );

/* get_404_template(  ) */
function smarty_get_404_template( $params, &$smarty )
{

    extract( $params );
    return get_404_template(  );
}
$wpsmarty->register_function( "get_404_template", "smarty_get_404_template" );

/* get_archive_template(  ) */
function smarty_get_archive_template( $params, &$smarty )
{

    extract( $params );
    return get_archive_template(  );
}
$wpsmarty->register_function( "get_archive_template", "smarty_get_archive_template" );

/* get_author_template(  ) */
function smarty_get_author_template( $params, &$smarty )
{

    extract( $params );
    return get_author_template(  );
}
$wpsmarty->register_function( "get_author_template", "smarty_get_author_template" );

/* get_category_template(  ) */
function smarty_get_category_template( $params, &$smarty )
{

    extract( $params );
    return get_category_template(  );
}
$wpsmarty->register_function( "get_category_template", "smarty_get_category_template" );

/* get_date_template(  ) */
function smarty_get_date_template( $params, &$smarty )
{

    extract( $params );
    return get_date_template(  );
}
$wpsmarty->register_function( "get_date_template", "smarty_get_date_template" );

/* get_home_template(  ) */
function smarty_get_home_template( $params, &$smarty )
{

    extract( $params );
    return get_home_template(  );
}
$wpsmarty->register_function( "get_home_template", "smarty_get_home_template" );

/* get_page_template(  ) */
function smarty_get_page_template( $params, &$smarty )
{

    extract( $params );
    return get_page_template(  );
}
$wpsmarty->register_function( "get_page_template", "smarty_get_page_template" );

/* get_paged_template(  ) */
function smarty_get_paged_template( $params, &$smarty )
{

    extract( $params );
    return get_paged_template(  );
}
$wpsmarty->register_function( "get_paged_template", "smarty_get_paged_template" );

/* get_search_template(  ) */
function smarty_get_search_template( $params, &$smarty )
{

    extract( $params );
    return get_search_template(  );
}
$wpsmarty->register_function( "get_search_template", "smarty_get_search_template" );

/* get_single_template(  ) */
function smarty_get_single_template( $params, &$smarty )
{

    extract( $params );
    return get_single_template(  );
}
$wpsmarty->register_function( "get_single_template", "smarty_get_single_template" );

/* get_comments_popup_template(  ) */
function smarty_get_comments_popup_template( $params, &$smarty )
{

    extract( $params );
    return get_comments_popup_template(  );
}
$wpsmarty->register_function( "get_comments_popup_template", "smarty_get_comments_popup_template" );

/* htmlentities2( $myHTML ) */
function smarty_htmlentities2( $params, &$smarty )
{

    extract( $params );
    return htmlentities2( $myHTML );
}
$wpsmarty->register_function( "htmlentities2", "smarty_htmlentities2" );

/* is_plugin_page(  ) */
function smarty_is_plugin_page( $params, &$smarty )
{

    extract( $params );
    return is_plugin_page(  );
}
$wpsmarty->register_function( "is_plugin_page", "smarty_is_plugin_page" );

/* add_query_arg(  ) */
function smarty_add_query_arg( $params, &$smarty )
{

    extract( $params );
    return add_query_arg(  );
}
$wpsmarty->register_function( "add_query_arg", "smarty_add_query_arg" );

/* remove_query_arg( $key, $query ) */
function smarty_remove_query_arg( $params, &$smarty )
{

    extract( $params );
    return remove_query_arg( $key,  $query );
}
$wpsmarty->register_function( "remove_query_arg", "smarty_remove_query_arg" );

/* load_template( $file ) */
function smarty_load_template( $params, &$smarty )
{

    extract( $params );
    return load_template( $file );
}
$wpsmarty->register_function( "load_template", "smarty_load_template" );

/* add_magic_quotes( $array ) */
function smarty_add_magic_quotes( $params, &$smarty )
{

    extract( $params );
    return add_magic_quotes( $array );
}
$wpsmarty->register_function( "add_magic_quotes", "smarty_add_magic_quotes" );

/* wp_remote_fopen(  $uri  ) */
function smarty_wp_remote_fopen( $params, &$smarty )
{

    extract( $params );
    return wp_remote_fopen(  $uri  );
}
$wpsmarty->register_function( "wp_remote_fopen", "smarty_wp_remote_fopen" );

/* wp( $query_vars = '' ) */
function smarty_wp( $params, &$smarty )
{
    $query_vars = '';

    extract( $params );
    return wp( $query_vars );
}
$wpsmarty->register_function( "wp", "smarty_wp" );

/* status_header(  $header  ) */
function smarty_status_header( $params, &$smarty )
{

    extract( $params );
    return status_header(  $header  );
}
$wpsmarty->register_function( "status_header", "smarty_status_header" );

/* nocache_headers(  ) */
function smarty_nocache_headers( $params, &$smarty )
{

    extract( $params );
    return nocache_headers(  );
}
$wpsmarty->register_function( "nocache_headers", "smarty_nocache_headers" );

/* get_usermeta(  $user_id, $meta_key = '' ) */
function smarty_get_usermeta( $params, &$smarty )
{
    $meta_key = '';

    extract( $params );
    return get_usermeta(  $user_id, $meta_key );
}
$wpsmarty->register_function( "get_usermeta", "smarty_get_usermeta" );

/* update_usermeta(  $user_id, $meta_key, $meta_value  ) */
function smarty_update_usermeta( $params, &$smarty )
{

    extract( $params );
    return update_usermeta(  $user_id,  $meta_key,  $meta_value  );
}
$wpsmarty->register_function( "update_usermeta", "smarty_update_usermeta" );

/* register_activation_hook( $file, $function ) */
function smarty_register_activation_hook( $params, &$smarty )
{

    extract( $params );
    return register_activation_hook( $file,  $function );
}
$wpsmarty->register_function( "register_activation_hook", "smarty_register_activation_hook" );

/* register_deactivation_hook( $file, $function ) */
function smarty_register_deactivation_hook( $params, &$smarty )
{

    extract( $params );
    return register_deactivation_hook( $file,  $function );
}
$wpsmarty->register_function( "register_deactivation_hook", "smarty_register_deactivation_hook" );

/* plugin_basename( $file ) */
function smarty_plugin_basename( $params, &$smarty )
{

    extract( $params );
    return plugin_basename( $file );
}
$wpsmarty->register_function( "plugin_basename", "smarty_plugin_basename" );

/* get_locale(  ) */
function smarty_get_locale( $params, &$smarty )
{

    extract( $params );
    return get_locale(  );
}
$wpsmarty->register_function( "get_locale", "smarty_get_locale" );

/* __( $text, $domain = 'default' ) */
function smarty___( $params, &$smarty )
{
    $domain = 'default';

    extract( $params );
    return __( $text, $domain );
}
$wpsmarty->register_function( "__", "smarty___" );

/* _e( $text, $domain = 'default' ) */
function smarty__e( $params, &$smarty )
{
    $domain = 'default';

    extract( $params );
    return _e( $text, $domain );
}
$wpsmarty->register_function( "_e", "smarty__e" );

/* __ngettext( $single, $plural, $number, $domain = 'default' ) */
function smarty___ngettext( $params, &$smarty )
{
    $domain = 'default';

    extract( $params );
    return __ngettext( $single,  $plural,  $number, $domain );
}
$wpsmarty->register_function( "__ngettext", "smarty___ngettext" );

/* load_textdomain( $domain, $mofile ) */
function smarty_load_textdomain( $params, &$smarty )
{

    extract( $params );
    return load_textdomain( $domain,  $mofile );
}
$wpsmarty->register_function( "load_textdomain", "smarty_load_textdomain" );

/* load_default_textdomain(  ) */
function smarty_load_default_textdomain( $params, &$smarty )
{

    extract( $params );
    return load_default_textdomain(  );
}
$wpsmarty->register_function( "load_default_textdomain", "smarty_load_default_textdomain" );

/* load_plugin_textdomain( $domain ) */
function smarty_load_plugin_textdomain( $params, &$smarty )
{

    extract( $params );
    return load_plugin_textdomain( $domain );
}
$wpsmarty->register_function( "load_plugin_textdomain", "smarty_load_plugin_textdomain" );

/* load_theme_textdomain( $domain ) */
function smarty_load_theme_textdomain( $params, &$smarty )
{

    extract( $params );
    return load_theme_textdomain( $domain );
}
$wpsmarty->register_function( "load_theme_textdomain", "smarty_load_theme_textdomain" );

/* wptexturize( $text ) */
function smarty_wptexturize( $params, &$smarty )
{

    extract( $params );
    return wptexturize( $text );
}
$wpsmarty->register_function( "wptexturize", "smarty_wptexturize" );

/* clean_pre( $text ) */
function smarty_clean_pre( $params, &$smarty )
{

    extract( $params );
    return clean_pre( $text );
}
$wpsmarty->register_function( "clean_pre", "smarty_clean_pre" );

/* wpautop( $pee, $br = 1 ) */
function smarty_wpautop( $params, &$smarty )
{
    $br = 1;

    extract( $params );
    return wpautop( $pee, $br );
}
$wpsmarty->register_function( "wpautop", "smarty_wpautop" );

/* seems_utf8( $Str ) */
function smarty_seems_utf8( $params, &$smarty )
{

    extract( $params );
    return seems_utf8( $Str );
}
$wpsmarty->register_function( "seems_utf8", "smarty_seems_utf8" );

/* wp_specialchars(  $text, $quotes = 0  ) */
function smarty_wp_specialchars( $params, &$smarty )
{
    $quotes = 0;

    extract( $params );
    return wp_specialchars(  $text, $quotes );
}
$wpsmarty->register_function( "wp_specialchars", "smarty_wp_specialchars" );

/* utf8_uri_encode(  $utf8_string  ) */
function smarty_utf8_uri_encode( $params, &$smarty )
{

    extract( $params );
    return utf8_uri_encode(  $utf8_string  );
}
$wpsmarty->register_function( "utf8_uri_encode", "smarty_utf8_uri_encode" );

/* remove_accents( $string ) */
function smarty_remove_accents( $params, &$smarty )
{

    extract( $params );
    return remove_accents( $string );
}
$wpsmarty->register_function( "remove_accents", "smarty_remove_accents" );

/* sanitize_user(  $username  ) */
function smarty_sanitize_user( $params, &$smarty )
{

    extract( $params );
    return sanitize_user(  $username  );
}
$wpsmarty->register_function( "sanitize_user", "smarty_sanitize_user" );

/* sanitize_title( $title, $fallback_title = '' ) */
function smarty_sanitize_title( $params, &$smarty )
{
    $fallback_title = '';

    extract( $params );
    return sanitize_title( $title, $fallback_title );
}
$wpsmarty->register_function( "sanitize_title", "smarty_sanitize_title" );

/* sanitize_title_with_dashes( $title ) */
function smarty_sanitize_title_with_dashes( $params, &$smarty )
{

    extract( $params );
    return sanitize_title_with_dashes( $title );
}
$wpsmarty->register_function( "sanitize_title_with_dashes", "smarty_sanitize_title_with_dashes" );

/* convert_chars( $content, $flag = 'obsolete' ) */
function smarty_convert_chars( $params, &$smarty )
{
    $flag = 'obsolete';

    extract( $params );
    return convert_chars( $content, $flag );
}
$wpsmarty->register_function( "convert_chars", "smarty_convert_chars" );

/* funky_javascript_fix( $text ) */
function smarty_funky_javascript_fix( $params, &$smarty )
{

    extract( $params );
    return funky_javascript_fix( $text );
}
$wpsmarty->register_function( "funky_javascript_fix", "smarty_funky_javascript_fix" );

/* balanceTags( $text, $is_comment = 0 ) */
function smarty_balanceTags( $params, &$smarty )
{
    $is_comment = 0;

    extract( $params );
    return balanceTags( $text, $is_comment );
}
$wpsmarty->register_function( "balanceTags", "smarty_balanceTags" );

/* format_to_edit( $content ) */
function smarty_format_to_edit( $params, &$smarty )
{

    extract( $params );
    return format_to_edit( $content );
}
$wpsmarty->register_function( "format_to_edit", "smarty_format_to_edit" );

/* format_to_post( $content ) */
function smarty_format_to_post( $params, &$smarty )
{

    extract( $params );
    return format_to_post( $content );
}
$wpsmarty->register_function( "format_to_post", "smarty_format_to_post" );

/* zeroise( $number,$threshold ) */
function smarty_zeroise( $params, &$smarty )
{

    extract( $params );
    return zeroise( $number, $threshold );
}
$wpsmarty->register_function( "zeroise", "smarty_zeroise" );

/* backslashit( $string ) */
function smarty_backslashit( $params, &$smarty )
{

    extract( $params );
    return backslashit( $string );
}
$wpsmarty->register_function( "backslashit", "smarty_backslashit" );

/* trailingslashit( $string ) */
function smarty_trailingslashit( $params, &$smarty )
{

    extract( $params );
    return trailingslashit( $string );
}
$wpsmarty->register_function( "trailingslashit", "smarty_trailingslashit" );

/* addslashes_gpc( $gpc ) */
function smarty_addslashes_gpc( $params, &$smarty )
{

    extract( $params );
    return addslashes_gpc( $gpc );
}
$wpsmarty->register_function( "addslashes_gpc", "smarty_addslashes_gpc" );

/* stripslashes_deep(  ) */
function smarty_stripslashes_deep( $params, &$smarty )
{

    extract( $params );
    return stripslashes_deep(  );
}
$wpsmarty->register_function( "stripslashes_deep", "smarty_stripslashes_deep" );

/* antispambot( $emailaddy, $mailto=0 ) */
function smarty_antispambot( $params, &$smarty )
{
    $mailto=0;

    extract( $params );
    return antispambot( $emailaddy, $mailto );
}
$wpsmarty->register_function( "antispambot", "smarty_antispambot" );

/* make_clickable( $ret ) */
function smarty_make_clickable( $params, &$smarty )
{

    extract( $params );
    return make_clickable( $ret );
}
$wpsmarty->register_function( "make_clickable", "smarty_make_clickable" );

/* wp_rel_nofollow(  $text  ) */
function smarty_wp_rel_nofollow( $params, &$smarty )
{

    extract( $params );
    return wp_rel_nofollow(  $text  );
}
$wpsmarty->register_function( "wp_rel_nofollow", "smarty_wp_rel_nofollow" );

/* convert_smilies( $text ) */
function smarty_convert_smilies( $params, &$smarty )
{

    extract( $params );
    return convert_smilies( $text );
}
$wpsmarty->register_function( "convert_smilies", "smarty_convert_smilies" );

/* is_email( $user_email ) */
function smarty_is_email( $params, &$smarty )
{

    extract( $params );
    return is_email( $user_email );
}
$wpsmarty->register_function( "is_email", "smarty_is_email" );

/* wp_iso_descrambler( $string ) */
function smarty_wp_iso_descrambler( $params, &$smarty )
{

    extract( $params );
    return wp_iso_descrambler( $string );
}
$wpsmarty->register_function( "wp_iso_descrambler", "smarty_wp_iso_descrambler" );

/* get_gmt_from_date( $string ) */
function smarty_get_gmt_from_date( $params, &$smarty )
{

    extract( $params );
    return get_gmt_from_date( $string );
}
$wpsmarty->register_function( "get_gmt_from_date", "smarty_get_gmt_from_date" );

/* get_date_from_gmt( $string ) */
function smarty_get_date_from_gmt( $params, &$smarty )
{

    extract( $params );
    return get_date_from_gmt( $string );
}
$wpsmarty->register_function( "get_date_from_gmt", "smarty_get_date_from_gmt" );

/* iso8601_timezone_to_offset( $timezone ) */
function smarty_iso8601_timezone_to_offset( $params, &$smarty )
{

    extract( $params );
    return iso8601_timezone_to_offset( $timezone );
}
$wpsmarty->register_function( "iso8601_timezone_to_offset", "smarty_iso8601_timezone_to_offset" );

/* iso8601_to_datetime( $date_string, $timezone = USER ) */
function smarty_iso8601_to_datetime( $params, &$smarty )
{
    $timezone = USER;

    extract( $params );
    return iso8601_to_datetime( $date_string, $timezone );
}
$wpsmarty->register_function( "iso8601_to_datetime", "smarty_iso8601_to_datetime" );

/* popuplinks( $text ) */
function smarty_popuplinks( $params, &$smarty )
{

    extract( $params );
    return popuplinks( $text );
}
$wpsmarty->register_function( "popuplinks", "smarty_popuplinks" );

/* sanitize_email( $email ) */
function smarty_sanitize_email( $params, &$smarty )
{

    extract( $params );
    return sanitize_email( $email );
}
$wpsmarty->register_function( "sanitize_email", "smarty_sanitize_email" );

/* human_time_diff(  $from, $to = ''  ) */
function smarty_human_time_diff( $params, &$smarty )
{
    $to = '';

    extract( $params );
    return human_time_diff(  $from, $to );
}
$wpsmarty->register_function( "human_time_diff", "smarty_human_time_diff" );

/* wp_trim_excerpt( $text ) */
function smarty_wp_trim_excerpt( $params, &$smarty )
{

    extract( $params );
    return wp_trim_excerpt( $text );
}
$wpsmarty->register_function( "wp_trim_excerpt", "smarty_wp_trim_excerpt" );

/* ent2ncr( $text ) */
function smarty_ent2ncr( $params, &$smarty )
{

    extract( $params );
    return ent2ncr( $text );
}
$wpsmarty->register_function( "ent2ncr", "smarty_ent2ncr" );

/* wp_insert_post( $postarr = array() ) */
function smarty_wp_insert_post( $params, &$smarty )
{
    $postarr = array();

    extract( $params );
    return wp_insert_post( $postarr );
}
$wpsmarty->register_function( "wp_insert_post", "smarty_wp_insert_post" );

/* wp_get_single_post( $postid = 0, $mode = OBJECT ) */
function smarty_wp_get_single_post( $params, &$smarty )
{
    $postid = 0;
    $mode = OBJECT;

    extract( $params );
    return wp_get_single_post( $postid, $mode );
}
$wpsmarty->register_function( "wp_get_single_post", "smarty_wp_get_single_post" );

/* wp_get_recent_posts( $num = 10 ) */
function smarty_wp_get_recent_posts( $params, &$smarty )
{
    $num = 10;

    extract( $params );
    return wp_get_recent_posts( $num );
}
$wpsmarty->register_function( "wp_get_recent_posts", "smarty_wp_get_recent_posts" );

/* wp_update_post( $postarr = array() ) */
function smarty_wp_update_post( $params, &$smarty )
{
    $postarr = array();

    extract( $params );
    return wp_update_post( $postarr );
}
$wpsmarty->register_function( "wp_update_post", "smarty_wp_update_post" );

/* wp_get_post_cats( $blogid = '1', $post_ID = 0 ) */
function smarty_wp_get_post_cats( $params, &$smarty )
{
    $blogid = '1';
    $post_ID = 0;

    extract( $params );
    return wp_get_post_cats( $blogid, $post_ID );
}
$wpsmarty->register_function( "wp_get_post_cats", "smarty_wp_get_post_cats" );

/* wp_set_post_cats( $blogid = '1', $post_ID = 0, $post_categories = array() ) */
function smarty_wp_set_post_cats( $params, &$smarty )
{
    $blogid = '1';
    $post_ID = 0;
    $post_categories = array();

    extract( $params );
    return wp_set_post_cats( $blogid, $post_ID, $post_categories );
}
$wpsmarty->register_function( "wp_set_post_cats", "smarty_wp_set_post_cats" );

/* wp_delete_post( $postid = 0 ) */
function smarty_wp_delete_post( $params, &$smarty )
{
    $postid = 0;

    extract( $params );
    return wp_delete_post( $postid );
}
$wpsmarty->register_function( "wp_delete_post", "smarty_wp_delete_post" );

/* post_permalink( $post_id = 0, $mode = '' ) */
function smarty_post_permalink( $params, &$smarty )
{
    $post_id = 0;
    $mode = '';

    extract( $params );
    return post_permalink( $post_id, $mode );
}
$wpsmarty->register_function( "post_permalink", "smarty_post_permalink" );

/* get_cat_name( $cat_id ) */
function smarty_get_cat_name( $params, &$smarty )
{

    extract( $params );
    return get_cat_name( $cat_id );
}
$wpsmarty->register_function( "get_cat_name", "smarty_get_cat_name" );

/* get_cat_ID( $cat_name='General' ) */
function smarty_get_cat_ID( $params, &$smarty )
{
    $cat_name='General';

    extract( $params );
    return get_cat_ID( $cat_name );
}
$wpsmarty->register_function( "get_cat_ID", "smarty_get_cat_ID" );

/* get_author_name(  $auth_id  ) */
function smarty_get_author_name( $params, &$smarty )
{

    extract( $params );
    return get_author_name(  $auth_id  );
}
$wpsmarty->register_function( "get_author_name", "smarty_get_author_name" );

/* get_extended( $post ) */
function smarty_get_extended( $params, &$smarty )
{

    extract( $params );
    return get_extended( $post );
}
$wpsmarty->register_function( "get_extended", "smarty_get_extended" );

/* trackback_url_list( $tb_list, $post_id ) */
function smarty_trackback_url_list( $params, &$smarty )
{

    extract( $params );
    return trackback_url_list( $tb_list,  $post_id );
}
$wpsmarty->register_function( "trackback_url_list", "smarty_trackback_url_list" );

/* user_can_create_post( $user_id, $blog_id = 1, $category_id = 'None' ) */
function smarty_user_can_create_post( $params, &$smarty )
{
    $blog_id = 1;
    $category_id = 'None';

    extract( $params );
    return user_can_create_post( $user_id, $blog_id, $category_id );
}
$wpsmarty->register_function( "user_can_create_post", "smarty_user_can_create_post" );

/* user_can_create_draft( $user_id, $blog_id = 1, $category_id = 'None' ) */
function smarty_user_can_create_draft( $params, &$smarty )
{
    $blog_id = 1;
    $category_id = 'None';

    extract( $params );
    return user_can_create_draft( $user_id, $blog_id, $category_id );
}
$wpsmarty->register_function( "user_can_create_draft", "smarty_user_can_create_draft" );

/* user_can_edit_post( $user_id, $post_id, $blog_id = 1 ) */
function smarty_user_can_edit_post( $params, &$smarty )
{
    $blog_id = 1;

    extract( $params );
    return user_can_edit_post( $user_id,  $post_id, $blog_id );
}
$wpsmarty->register_function( "user_can_edit_post", "smarty_user_can_edit_post" );

/* user_can_delete_post( $user_id, $post_id, $blog_id = 1 ) */
function smarty_user_can_delete_post( $params, &$smarty )
{
    $blog_id = 1;

    extract( $params );
    return user_can_delete_post( $user_id,  $post_id, $blog_id );
}
$wpsmarty->register_function( "user_can_delete_post", "smarty_user_can_delete_post" );

/* user_can_set_post_date( $user_id, $blog_id = 1, $category_id = 'None' ) */
function smarty_user_can_set_post_date( $params, &$smarty )
{
    $blog_id = 1;
    $category_id = 'None';

    extract( $params );
    return user_can_set_post_date( $user_id, $blog_id, $category_id );
}
$wpsmarty->register_function( "user_can_set_post_date", "smarty_user_can_set_post_date" );

/* user_can_edit_post_date( $user_id, $post_id, $blog_id = 1 ) */
function smarty_user_can_edit_post_date( $params, &$smarty )
{
    $blog_id = 1;

    extract( $params );
    return user_can_edit_post_date( $user_id,  $post_id, $blog_id );
}
$wpsmarty->register_function( "user_can_edit_post_date", "smarty_user_can_edit_post_date" );

/* user_can_edit_post_comments( $user_id, $post_id, $blog_id = 1 ) */
function smarty_user_can_edit_post_comments( $params, &$smarty )
{
    $blog_id = 1;

    extract( $params );
    return user_can_edit_post_comments( $user_id,  $post_id, $blog_id );
}
$wpsmarty->register_function( "user_can_edit_post_comments", "smarty_user_can_edit_post_comments" );

/* user_can_delete_post_comments( $user_id, $post_id, $blog_id = 1 ) */
function smarty_user_can_delete_post_comments( $params, &$smarty )
{
    $blog_id = 1;

    extract( $params );
    return user_can_delete_post_comments( $user_id,  $post_id, $blog_id );
}
$wpsmarty->register_function( "user_can_delete_post_comments", "smarty_user_can_delete_post_comments" );

/* user_can_edit_user( $user_id, $other_user ) */
function smarty_user_can_edit_user( $params, &$smarty )
{

    extract( $params );
    return user_can_edit_user( $user_id,  $other_user );
}
$wpsmarty->register_function( "user_can_edit_user", "smarty_user_can_edit_user" );

/* wp_blacklist_check( $author, $email, $url, $comment, $user_ip, $user_agent ) */
function smarty_wp_blacklist_check( $params, &$smarty )
{

    extract( $params );
    return wp_blacklist_check( $author,  $email,  $url,  $comment,  $user_ip,  $user_agent );
}
$wpsmarty->register_function( "wp_blacklist_check", "smarty_wp_blacklist_check" );

/* wp_proxy_check( $ipnum ) */
function smarty_wp_proxy_check( $params, &$smarty )
{

    extract( $params );
    return wp_proxy_check( $ipnum );
}
$wpsmarty->register_function( "wp_proxy_check", "smarty_wp_proxy_check" );

/* wp_new_comment(  $commentdata, $spam = false  ) */
function smarty_wp_new_comment( $params, &$smarty )
{
    $spam = false;

    extract( $params );
    return wp_new_comment(  $commentdata, $spam );
}
$wpsmarty->register_function( "wp_new_comment", "smarty_wp_new_comment" );

/* wp_update_comment( $commentarr ) */
function smarty_wp_update_comment( $params, &$smarty )
{

    extract( $params );
    return wp_update_comment( $commentarr );
}
$wpsmarty->register_function( "wp_update_comment", "smarty_wp_update_comment" );

/* do_trackbacks( $post_id ) */
function smarty_do_trackbacks( $params, &$smarty )
{

    extract( $params );
    return do_trackbacks( $post_id );
}
$wpsmarty->register_function( "do_trackbacks", "smarty_do_trackbacks" );

/* get_pung( $post_id ) */
function smarty_get_pung( $params, &$smarty )
{

    extract( $params );
    return get_pung( $post_id );
}
$wpsmarty->register_function( "get_pung", "smarty_get_pung" );

/* get_enclosed( $post_id ) */
function smarty_get_enclosed( $params, &$smarty )
{

    extract( $params );
    return get_enclosed( $post_id );
}
$wpsmarty->register_function( "get_enclosed", "smarty_get_enclosed" );

/* get_to_ping( $post_id ) */
function smarty_get_to_ping( $params, &$smarty )
{

    extract( $params );
    return get_to_ping( $post_id );
}
$wpsmarty->register_function( "get_to_ping", "smarty_get_to_ping" );

/* add_ping( $post_id, $uri ) */
function smarty_add_ping( $params, &$smarty )
{

    extract( $params );
    return add_ping( $post_id,  $uri );
}
$wpsmarty->register_function( "add_ping", "smarty_add_ping" );

/* generate_page_rewrite_rules(  ) */
function smarty_generate_page_rewrite_rules( $params, &$smarty )
{

    extract( $params );
    return generate_page_rewrite_rules(  );
}
$wpsmarty->register_function( "generate_page_rewrite_rules", "smarty_generate_page_rewrite_rules" );

/* get_bloginfo_rss( $show = '' ) */
function smarty_get_bloginfo_rss( $params, &$smarty )
{
    $show = '';

    extract( $params );
    return get_bloginfo_rss( $show );
}
$wpsmarty->register_function( "get_bloginfo_rss", "smarty_get_bloginfo_rss" );

/* bloginfo_rss( $show = '' ) */
function smarty_bloginfo_rss( $params, &$smarty )
{
    $show = '';

    extract( $params );
    return bloginfo_rss( $show );
}
$wpsmarty->register_function( "bloginfo_rss", "smarty_bloginfo_rss" );

/* the_title_rss(  ) */
function smarty_the_title_rss( $params, &$smarty )
{

    extract( $params );
    return the_title_rss(  );
}
$wpsmarty->register_function( "the_title_rss", "smarty_the_title_rss" );

/* the_content_rss( $more_link_text='(more...)', $stripteaser=0, $more_file='', $cut = 0, $encode_html = 0 ) */
function smarty_the_content_rss( $params, &$smarty )
{
    $more_link_text='(more...)';
    $stripteaser=0;
    $more_file='';
    $cut = 0;
    $encode_html = 0;

    extract( $params );
    return the_content_rss( $more_link_text, $stripteaser, $more_file, $cut, $encode_html );
}
$wpsmarty->register_function( "the_content_rss", "smarty_the_content_rss" );

/* the_excerpt_rss(  ) */
function smarty_the_excerpt_rss( $params, &$smarty )
{

    extract( $params );
    return the_excerpt_rss(  );
}
$wpsmarty->register_function( "the_excerpt_rss", "smarty_the_excerpt_rss" );

/* permalink_single_rss( $file = '' ) */
function smarty_permalink_single_rss( $params, &$smarty )
{
    $file = '';

    extract( $params );
    return permalink_single_rss( $file );
}
$wpsmarty->register_function( "permalink_single_rss", "smarty_permalink_single_rss" );

/* comment_link(  ) */
function smarty_comment_link( $params, &$smarty )
{

    extract( $params );
    return comment_link(  );
}
$wpsmarty->register_function( "comment_link", "smarty_comment_link" );

/* comment_author_rss(  ) */
function smarty_comment_author_rss( $params, &$smarty )
{

    extract( $params );
    return comment_author_rss(  );
}
$wpsmarty->register_function( "comment_author_rss", "smarty_comment_author_rss" );

/* comment_text_rss(  ) */
function smarty_comment_text_rss( $params, &$smarty )
{

    extract( $params );
    return comment_text_rss(  );
}
$wpsmarty->register_function( "comment_text_rss", "smarty_comment_text_rss" );

/* comments_rss_link( $link_text = 'Comments RSS', $commentsrssfilename = '' ) */
function smarty_comments_rss_link( $params, &$smarty )
{
    $link_text = 'Comments RSS';
    $commentsrssfilename = '';

    extract( $params );
    return comments_rss_link( $link_text, $commentsrssfilename );
}
$wpsmarty->register_function( "comments_rss_link", "smarty_comments_rss_link" );

/* comments_rss( $commentsrssfilename = '' ) */
function smarty_comments_rss( $params, &$smarty )
{
    $commentsrssfilename = '';

    extract( $params );
    return comments_rss( $commentsrssfilename );
}
$wpsmarty->register_function( "comments_rss", "smarty_comments_rss" );

/* get_author_rss_link( $echo = false, $author_id, $author_nicename ) */
function smarty_get_author_rss_link( $params, &$smarty )
{
    $echo = false;

    extract( $params );
    return get_author_rss_link( $echo,  $author_id,  $author_nicename );
}
$wpsmarty->register_function( "get_author_rss_link", "smarty_get_author_rss_link" );

/* get_category_rss_link( $echo = false, $cat_ID, $category_nicename ) */
function smarty_get_category_rss_link( $params, &$smarty )
{
    $echo = false;

    extract( $params );
    return get_category_rss_link( $echo,  $cat_ID,  $category_nicename );
}
$wpsmarty->register_function( "get_category_rss_link", "smarty_get_category_rss_link" );

/* the_category_rss( $type = 'rss' ) */
function smarty_the_category_rss( $params, &$smarty )
{
    $type = 'rss';

    extract( $params );
    return the_category_rss( $type );
}
$wpsmarty->register_function( "the_category_rss", "smarty_the_category_rss" );

/* rss_enclosure(  ) */
function smarty_rss_enclosure( $params, &$smarty )
{

    extract( $params );
    return rss_enclosure(  );
}
$wpsmarty->register_function( "rss_enclosure", "smarty_rss_enclosure" );

/* comments_template(  $file = '/comments.php'  ) */
function smarty_comments_template( $params, &$smarty )
{
    $file = '/comments.php';

    extract( $params );
    return comments_template( $file );
}
$wpsmarty->register_function( "comments_template", "smarty_comments_template" );

/* clean_url(  $url  ) */
function smarty_clean_url( $params, &$smarty )
{

    extract( $params );
    return clean_url(  $url  );
}
$wpsmarty->register_function( "clean_url", "smarty_clean_url" );

/* get_comments_number(  $comment_id  ) */
function smarty_get_comments_number( $params, &$smarty )
{

    extract( $params );
    return get_comments_number(  $comment_id  );
}
$wpsmarty->register_function( "get_comments_number", "smarty_get_comments_number" );

/* comments_number(  $zero = 'No Comments', $one = '1 Comment', $more = '% Comments', $number = ''  ) */
function smarty_comments_number( $params, &$smarty )
{
    $zero = 'No Comments';
    $one = '1 Comment';
    $more = '% Comments';
    $number = '';

    extract( $params );
    return comments_number( $zero, $one, $more, $number );
}
$wpsmarty->register_function( "comments_number", "smarty_comments_number" );

/* get_comments_link(  ) */
function smarty_get_comments_link( $params, &$smarty )
{

    extract( $params );
    return get_comments_link(  );
}
$wpsmarty->register_function( "get_comments_link", "smarty_get_comments_link" );

/* get_comment_link(  ) */
function smarty_get_comment_link( $params, &$smarty )
{

    extract( $params );
    return get_comment_link(  );
}
$wpsmarty->register_function( "get_comment_link", "smarty_get_comment_link" );

/* comments_link(  $file = '', $echo = true  ) */
function smarty_comments_link( $params, &$smarty )
{
    $file = '';
    $echo = true;

    extract( $params );
    return comments_link( $file, $echo );
}
$wpsmarty->register_function( "comments_link", "smarty_comments_link" );

/* comments_popup_script( $width=400, $height=400, $file='' ) */
function smarty_comments_popup_script( $params, &$smarty )
{
    $width=400;
    $height=400;
    $file='';

    extract( $params );
    return comments_popup_script( $width, $height, $file );
}
$wpsmarty->register_function( "comments_popup_script", "smarty_comments_popup_script" );

/* comments_popup_link( $zero='No Comments', $one='1 Comment', $more='% Comments', $CSSclass='', $none='Comments Off' ) */
function smarty_comments_popup_link( $params, &$smarty )
{
    $zero='No Comments';
    $one='1 Comment';
    $more='% Comments';
    $CSSclass='';
    $none='Comments Off';

    extract( $params );
    return comments_popup_link( $zero, $one, $more, $CSSclass, $none );
}
$wpsmarty->register_function( "comments_popup_link", "smarty_comments_popup_link" );

/* get_comment_ID(  ) */
function smarty_get_comment_ID( $params, &$smarty )
{

    extract( $params );
    return get_comment_ID(  );
}
$wpsmarty->register_function( "get_comment_ID", "smarty_get_comment_ID" );

/* comment_ID(  ) */
function smarty_comment_ID( $params, &$smarty )
{

    extract( $params );
    return comment_ID(  );
}
$wpsmarty->register_function( "comment_ID", "smarty_comment_ID" );

/* get_comment_author(  ) */
function smarty_get_comment_author( $params, &$smarty )
{

    extract( $params );
    return get_comment_author(  );
}
$wpsmarty->register_function( "get_comment_author", "smarty_get_comment_author" );

/* comment_author(  ) */
function smarty_comment_author( $params, &$smarty )
{

    extract( $params );
    return comment_author(  );
}
$wpsmarty->register_function( "comment_author", "smarty_comment_author" );

/* get_comment_author_email(  ) */
function smarty_get_comment_author_email( $params, &$smarty )
{

    extract( $params );
    return get_comment_author_email(  );
}
$wpsmarty->register_function( "get_comment_author_email", "smarty_get_comment_author_email" );

/* comment_author_email(  ) */
function smarty_comment_author_email( $params, &$smarty )
{

    extract( $params );
    return comment_author_email(  );
}
$wpsmarty->register_function( "comment_author_email", "smarty_comment_author_email" );

/* get_comment_author_link(  ) */
function smarty_get_comment_author_link( $params, &$smarty )
{

    extract( $params );
    return get_comment_author_link(  );
}
$wpsmarty->register_function( "get_comment_author_link", "smarty_get_comment_author_link" );

/* comment_author_link(  ) */
function smarty_comment_author_link( $params, &$smarty )
{

    extract( $params );
    return comment_author_link(  );
}
$wpsmarty->register_function( "comment_author_link", "smarty_comment_author_link" );

/* get_comment_type(  ) */
function smarty_get_comment_type( $params, &$smarty )
{

    extract( $params );
    return get_comment_type(  );
}
$wpsmarty->register_function( "get_comment_type", "smarty_get_comment_type" );

/* comment_type( $commenttxt = 'Comment', $trackbacktxt = 'Trackback', $pingbacktxt = 'Pingback' ) */
function smarty_comment_type( $params, &$smarty )
{
    $commenttxt = 'Comment';
    $trackbacktxt = 'Trackback';
    $pingbacktxt = 'Pingback';

    extract( $params );
    return comment_type( $commenttxt, $trackbacktxt, $pingbacktxt );
}
$wpsmarty->register_function( "comment_type", "smarty_comment_type" );

/* get_comment_author_url(  ) */
function smarty_get_comment_author_url( $params, &$smarty )
{

    extract( $params );
    return get_comment_author_url(  );
}
$wpsmarty->register_function( "get_comment_author_url", "smarty_get_comment_author_url" );

/* comment_author_url(  ) */
function smarty_comment_author_url( $params, &$smarty )
{

    extract( $params );
    return comment_author_url(  );
}
$wpsmarty->register_function( "comment_author_url", "smarty_comment_author_url" );

/* comment_author_email_link( $linktext='', $before='', $after='' ) */
function smarty_comment_author_email_link( $params, &$smarty )
{
    $linktext='';
    $before='';
    $after='';

    extract( $params );
    return comment_author_email_link( $linktext, $before, $after );
}
$wpsmarty->register_function( "comment_author_email_link", "smarty_comment_author_email_link" );

/* get_comment_author_url_link(  $linktext = '', $before = '', $after = ''  ) */
function smarty_get_comment_author_url_link( $params, &$smarty )
{
    $linktext = '';
    $before = '';
    $after = '';

    extract( $params );
    return get_comment_author_url_link( $linktext, $before, $after );
}
$wpsmarty->register_function( "get_comment_author_url_link", "smarty_get_comment_author_url_link" );

/* comment_author_url_link(  $linktext = '', $before = '', $after = ''  ) */
function smarty_comment_author_url_link( $params, &$smarty )
{
    $linktext = '';
    $before = '';
    $after = '';

    extract( $params );
    return comment_author_url_link( $linktext, $before, $after );
}
$wpsmarty->register_function( "comment_author_url_link", "smarty_comment_author_url_link" );

/* get_comment_author_IP(  ) */
function smarty_get_comment_author_IP( $params, &$smarty )
{

    extract( $params );
    return get_comment_author_IP(  );
}
$wpsmarty->register_function( "get_comment_author_IP", "smarty_get_comment_author_IP" );

/* comment_author_IP(  ) */
function smarty_comment_author_IP( $params, &$smarty )
{

    extract( $params );
    return comment_author_IP(  );
}
$wpsmarty->register_function( "comment_author_IP", "smarty_comment_author_IP" );

/* get_comment_text(  ) */
function smarty_get_comment_text( $params, &$smarty )
{

    extract( $params );
    return get_comment_text(  );
}
$wpsmarty->register_function( "get_comment_text", "smarty_get_comment_text" );

/* comment_text(  ) */
function smarty_comment_text( $params, &$smarty )
{

    extract( $params );
    return comment_text(  );
}
$wpsmarty->register_function( "comment_text", "smarty_comment_text" );

/* get_comment_excerpt(  ) */
function smarty_get_comment_excerpt( $params, &$smarty )
{

    extract( $params );
    return get_comment_excerpt(  );
}
$wpsmarty->register_function( "get_comment_excerpt", "smarty_get_comment_excerpt" );

/* comment_excerpt(  ) */
function smarty_comment_excerpt( $params, &$smarty )
{

    extract( $params );
    return comment_excerpt(  );
}
$wpsmarty->register_function( "comment_excerpt", "smarty_comment_excerpt" );

/* get_comment_date(  $d = ''  ) */
function smarty_get_comment_date( $params, &$smarty )
{
    $d = '';

    extract( $params );
    return get_comment_date( $d );
}
$wpsmarty->register_function( "get_comment_date", "smarty_get_comment_date" );

/* comment_date(  $d = ''  ) */
function smarty_comment_date( $params, &$smarty )
{
    $d = '';

    extract( $params );
    return comment_date( $d );
}
$wpsmarty->register_function( "comment_date", "smarty_comment_date" );

/* get_comment_time(  $d = '', $gmt = false  ) */
function smarty_get_comment_time( $params, &$smarty )
{
    $d = '';
    $gmt = false;

    extract( $params );
    return get_comment_time( $d, $gmt );
}
$wpsmarty->register_function( "get_comment_time", "smarty_get_comment_time" );

/* comment_time(  $d = ''  ) */
function smarty_comment_time( $params, &$smarty )
{
    $d = '';

    extract( $params );
    return comment_time( $d );
}
$wpsmarty->register_function( "comment_time", "smarty_comment_time" );

/* get_trackback_url(  ) */
function smarty_get_trackback_url( $params, &$smarty )
{

    extract( $params );
    return get_trackback_url(  );
}
$wpsmarty->register_function( "get_trackback_url", "smarty_get_trackback_url" );

/* trackback_url(  $display = true  ) */
function smarty_trackback_url( $params, &$smarty )
{
    $display = true;

    extract( $params );
    return trackback_url( $display );
}
$wpsmarty->register_function( "trackback_url", "smarty_trackback_url" );

/* trackback_rdf( $timezone = 0 ) */
function smarty_trackback_rdf( $params, &$smarty )
{
    $timezone = 0;

    extract( $params );
    return trackback_rdf( $timezone );
}
$wpsmarty->register_function( "trackback_rdf", "smarty_trackback_rdf" );

/* comments_open(  ) */
function smarty_comments_open( $params, &$smarty )
{

    extract( $params );
    return comments_open(  );
}
$wpsmarty->register_function( "comments_open", "smarty_comments_open" );

/* pings_open(  ) */
function smarty_pings_open( $params, &$smarty )
{

    extract( $params );
    return pings_open(  );
}
$wpsmarty->register_function( "pings_open", "smarty_pings_open" );

/* get_lastcommentmodified( $timezone = 'server' ) */
function smarty_get_lastcommentmodified( $params, &$smarty )
{
    $timezone = 'server';

    extract( $params );
    return get_lastcommentmodified( $timezone );
}
$wpsmarty->register_function( "get_lastcommentmodified", "smarty_get_lastcommentmodified" );

/* get_commentdata(  $comment_ID, $no_cache = 0, $include_unapproved = false  ) */
function smarty_get_commentdata( $params, &$smarty )
{
    $no_cache = 0;
    $include_unapproved = false;

    extract( $params );
    return get_commentdata(  $comment_ID, $no_cache, $include_unapproved );
}
$wpsmarty->register_function( "get_commentdata", "smarty_get_commentdata" );

/* pingback( $content, $post_ID ) */
function smarty_pingback( $params, &$smarty )
{

    extract( $params );
    return pingback( $content,  $post_ID );
}
$wpsmarty->register_function( "pingback", "smarty_pingback" );

/* discover_pingback_server_uri( $url, $timeout_bytes = 2048 ) */
function smarty_discover_pingback_server_uri( $params, &$smarty )
{
    $timeout_bytes = 2048;

    extract( $params );
    return discover_pingback_server_uri( $url, $timeout_bytes );
}
$wpsmarty->register_function( "discover_pingback_server_uri", "smarty_discover_pingback_server_uri" );

/* wp_set_comment_status( $comment_id, $comment_status ) */
function smarty_wp_set_comment_status( $params, &$smarty )
{

    extract( $params );
    return wp_set_comment_status( $comment_id,  $comment_status );
}
$wpsmarty->register_function( "wp_set_comment_status", "smarty_wp_set_comment_status" );

/* wp_get_comment_status( $comment_id ) */
function smarty_wp_get_comment_status( $params, &$smarty )
{

    extract( $params );
    return wp_get_comment_status( $comment_id );
}
$wpsmarty->register_function( "wp_get_comment_status", "smarty_wp_get_comment_status" );

/* check_comment( $author, $email, $url, $comment, $user_ip, $user_agent, $comment_type ) */
function smarty_check_comment( $params, &$smarty )
{

    extract( $params );
    return check_comment( $author,  $email,  $url,  $comment,  $user_ip,  $user_agent,  $comment_type );
}
$wpsmarty->register_function( "check_comment", "smarty_check_comment" );


$wpsmarty->template_dir = ABSPATH."/wp-content/blogs/".$wpblog."/templates";
$wpsmarty->compile_dir  = ABSPATH."/wp-content/blogs/".$wpblog."/templates_c";
$wpsmarty->cache_dir    = ABSPATH."/wp-content/blogs/".$wpblog."/smartycache";
$wpsmarty->plugins_dir  = ABSPATH."/wp-content/smarty-plugins";
$wpsmarty->cache_lifetime = -1;
$wpsmarty->caching = true;
$wpsmarty->security = 1;
$wpsmarty->secure_dir = array( ABSPATH."/wp-content/blogs/".$wpblog."/templates", "wp-content/smarty-templates" );
if( isset( $_GET[ "clear" ] ) )
    $wpsmarty->clear_all_cache();
?>