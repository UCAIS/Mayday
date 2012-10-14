<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<title><?php
    /*
     * Print the <title> tag based on what is being viewed.
     */
    global $page, $paged;

    wp_title( '|', true, 'right' );

    // Add the blog name.
    bloginfo( 'name' );

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        echo " | $site_description";

    // Add a page number if necessary:
    if ( $paged >= 2 || $page >= 2 )
        echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );

    ?></title>
<link rel="stylesheet" rev="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/main.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery-1.7.1.min.js" charset="UTF-8"></script>
<?php
    wp_head();
?>
</head>

<body <?php body_class(); ?> id="article">

<div id="page-container">
<div id="header">
    <h1 id="title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
	<object id="head_motto" data="<?php echo get_template_directory_uri(); ?>/images/head_motto.swf" type="application/x-shockwave-flash" width="310" height="80">
		<param name="movie" value="<?php echo get_template_directory_uri(); ?>/images/head_motto.swf" />
		<param name="wmode" value="transparent"/>
	</object>
	<object id="head_pic" data="<?php echo get_template_directory_uri(); ?>/images/head_pic.swf" type="application/x-shockwave-flash" width="200" height="120">
		<param name="movie" value="<?php echo get_template_directory_uri(); ?>/images/head_pic.swf" />
		<param name="wmode" value="transparent"/>
	</object>
</div><!-- #header end -->