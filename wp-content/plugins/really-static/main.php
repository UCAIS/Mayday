<?php

/*
Plugin Name: Really Static
Plugin URI: http://www.php-welt.net/really-static/index.html
Description:  Make your Blog really static! Please study the <a href="http://www.php-welt.net/really-static/">quick start instuctions</a>! 
Author: Erik Sefkow
Version: 0.31
Author URI: http://erik.sefkow.net/
*/

/**
 * Make your Blog really static!
 *
 * Copyright 2008-2012 Erik Sefkow
 *
 * Really static is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Really static is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author Erik Sefkow 
 * @version 0.5
 */
 
if (! defined ( "WP_CONTENT_URL" ))
	define ( "WP_CONTENT_URL", get_option ( "siteurl" ) . "/wp-content" );
if (! defined ( "WP_CONTENT_DIR" ))
	define ( "WP_CONTENT_DIR", ABSPATH . "wp-content" );
if (! defined ( 'REALLYSTATICHOME' ))
	define ( 'REALLYSTATICHOME', dirname ( __FILE__ ) . '/' );
if (! defined ( 'REALLYSTATICURLHOME' ))
	define ( 'REALLYSTATICURLHOME', WP_CONTENT_URL . str_replace ( "\\", "/", substr ( dirname ( __FILE__ ), strpos ( dirname ( __FILE__ ), "wp-content" ) + 10 ) ) . '/' );
if (! defined ( 'REALLYSTATICBASE' ))
	define ( 'REALLYSTATICBASE', plugin_basename ( __FILE__ ) );
if (! defined ( 'REALLYSTATICMAINFILE' ))
	define ( 'REALLYSTATICMAINFILE', __FILE__);



global $wpdb;
if (! defined ( 'REALLYSTATICDATABASE' ))
	define ( 'REALLYSTATICDATABASE',$wpdb->prefix . "realstatic" );
#	@set_time_limit ( 0 );

error_reporting(E_ERROR | E_WARNING | E_PARSE);
#### PLEASE Do not change anything here!
global $rs_version, $rs_rlc;

$rs_version = "0.5";
$rs_rlc = 20121012;

define ( 'RSVERSION', $rs_version );
define ( 'RSSUBVERSION', $rs_rlc );
define ( 'RSVERSIONCHECK', 7 );
if (preg_match ( '#' . basename ( __FILE__ ) . '#', $_SERVER ['PHP_SELF'] )) {
	die ( 'Get it here: <a title="really static wordpress plugin" href="http://really-static-support.php-welt.net/current-development-snapshot-t5.html">really static wordpress plugin</a>' );
}
$currentLocale = get_locale ();
if (! empty ( $currentLocale )) {
	$moFile = dirname ( __FILE__ ) . "/languages/reallystatic-" . $currentLocale . ".mo";
	if (@file_exists ( $moFile ) && is_readable ( $moFile ))
		load_textdomain ( 'reallystatic', $moFile );
}
require_once ("php/ftp.php");
require_once ("php/sftp.php");
require_once ("php/local.php");

if(is_multisite()) define ( 'LOGFILE', REALLYSTATICHOME . $wpdb->blogid.'-log.html' );
else define ( 'LOGFILE', REALLYSTATICHOME . 'log.html' );
if (get_option ( 'permalink_structure' ) == "")
	define ( 'REALSTATICNONPERMANENT', true );
else
	define ( 'REALSTATICNONPERMANENT', false );
require ("rewrite.php");

/**
 * @return void
 * @param string logfiletext
 * @param int LOGTYP 1=error;2=erweiterter hinweis(lese+cachehit);3=schreiboperationen
 * @desc Write a text into Logfile
 */
function RS_LOG($line, $typ = 0,$file=LOGFILE) {
	#if($typ==2)return;
	if ($line === false) {
		$fh = @fopen ( $file, "w+" );
		@fwrite ( $fh, "<pre>" );
		@fclose ( $fh );
		return;
	}
	$fh = @fopen ( $file, "a+" );
	@fwrite ( $fh, date ( "d M Y H:i:s", time () + (get_option ( 'gmt_offset' ) * 3600) ) . ": " . $line . "\r\n" );
	@fclose ( $fh );
}
/**
 * @return void
 * @param String $d
 * @desc Just for devoloping! Zählt hoch und gibt werte aus
 */
function RS_LOGD($d="") {
	global $debugcount;
	$debugcount ++;
	RS_LOG ( "DEBUG$debugcount: " . $d );
}
function RS_LOGF($d="",$fp=""){
	global $debugcount;
	$debugcount ++;
	$myFile= REALLYSTATICHOME.$fp.time().$debugcount.".txt";
	RS_LOG("writing debuglogfile: ".$myFile);
	
	$fh = fopen($myFile, 'w+') or die("can't open file");
	fwrite($fh, $d);
	fclose($fh);
}
/**
 * @return void
 * @param Array $array
 * @desc Write a Array into Logfile
 */
function RS_LOGA($array) {
	ob_start ();
	print_R ( $array );
	$out1 = ob_get_contents ();
	ob_end_clean ();
	RS_LOG ( $out1 );
}
function multiloaddaten($name) {
	#echo get_site_option( $name )." $name<hr>";
	if (get_site_option ( $name ) !== false)
		return get_site_option ( $name );
	if ($name == "realstaticdonationid")
		add_site_option ( $name, "-" );
	return get_site_option ( $name );
}

/**
 * @desc settingsdaten laden
 * @param string variablenname
 * @return string gespeicherter inhalt der Variable 
 */
function loaddaten($name) {
	if ($name == "localpath"){
		//RS_LOG("ABSPATH ".ABSPATH);
		return ABSPATH;
	}
	if ($name == "subpfad")
		$name = "realstaticsubpfad";
	if ($name == "localurl")
		$name = "realstaticlocalurl";
	if ($name == "remotepath")
		$name = "realstaticremotepath";
	if ($name == "remoteurl")
		$name = "realstaticremoteurl";
	

	if ($name == "realstaticposteditcreatedelete") {
		$r = get_option ( $name );
		if (count ( $r ) > 0) {
			if (! is_array ( $r [0] )) {
				foreach ( $r as $k )
					$rr [] = array ($k, "" );
				
				update_option ( $name, $rr );
				unset ( $rr );
				unset ( $r );
			}
		}
	}
	
	if ($name == "realstaticpageeditcreatedelete") {
		$r = get_option ( $name );
		if (count ( $r ) > 0) {
			if (! is_array ( $r [0] )) {
				foreach ( $r as $k )
					$rr [] = array ($k, "" );
				update_option ( $name, $rr );
				unset ( $rr );
				unset ( $r );
			}
		}
	}
	if ($name == "realstaticcommenteditcreatedelete") {
		$r = get_option ( $name );
		if (count ( $r ) > 0) {
			if (! is_array ( $r [0] )) {
				foreach ( $r as $k )
					$rr [] = array ($k, "" );
				update_option ( $name, $rr );
				unset ( $rr );
				unset ( $r );
			}
		}
	}
	
	if ($name == "realstaticeveryday") {
		$r = get_option ( $name );
		if (count ( $r ) > 0) {
			if (! is_array ( $r [0] )) {
				foreach ( $r as $k )
					$rr [] = array ($k, "" );
				update_option ( $name, $rr );
				unset ( $rr );
				unset ( $r );
			}
		}
	}
	if ($name == "realstaticeverytime") {
		$r = get_option ( $name );
		if (count ( $r ) > 0) {
			if (! is_array ( $r [0] )) {
				foreach ( $r as $k )
					$rr [] = array ($k, "" );
				update_option ( $name, $rr );
				unset ( $rr );
				unset ( $r );
			}
		}
	}
	if($name=="realstaticdonationid" and multiloaddaten("realstaticdonationid")!="" and multiloaddaten("realstaticdonationid")!="-")return multiloaddaten("realstaticdonationid");
	return get_option ( $name );
}
 
#add_filter ( 'post_updated_messages', 'rs_pagetitlechanged' );
function rs_pagetitlechanged($messages){
		#RS_LOGA($messages);
	 
	 
	foreach($p=array("page","post") as $v)
	 $messages[$v][$_GET['message']].="<br />".__("Attention! You changed the title of this page. You may need to refresh your static files.");
 
		 
		#RS_LOGA($messages);
		return $messages;
}

add_action ( 'shutdown', 'arbeite' );
/**
 * @desc führt alle update & delete operationen durch
 * @param int doit wenn =true dann wirds sofort ausgefürt egal von wo
 * @return void
 */
function arbeite($doit=false,$silent=false) {
	global $arbeitsliste, $wpdb, $allrefresh,$eigenerools, $arbeitetrotzdem;
	if ($doit!==true &&  $arbeitetrotzdem!== true and (! is_array ( $arbeitsliste ) or substr ( $_SERVER ['PHP_SELF'], - 9 ) == "index.php"))
		return;
	RS_LOG("rs_onwork".get_option ( "rs_onwork"));
	if(get_option ( "rs_onwork")!=0)wp_die( __( 'Please wait! Another really-static instance is running!' ));
	update_option ( "rs_onwork", "1" );
	RS_LOG("rs_onwork".get_option ( "rs_onwork"));
			
	unset($arbeitsliste[update][""]);
	unset($arbeitsliste[delete][""]);
	RS_LOGA ( $arbeitsliste );
	#return;
	$arbeitsliste = apply_filters ( "rs-todolist", $arbeitsliste );
	
	
	RS_log ( "Verbinde", 2 );
	$pre_remoteserver = loaddaten ( "remotepath" );
	$pre_localserver = loaddaten ( "localpath" );
	$transport = apply_filters ( "rs-transport", array () );
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [0], array () );
	

	$table_name = REALLYSTATICDATABASE;
 
	//Loeschen
	if (is_array ( $arbeitsliste [delete] )) {
		foreach ( $arbeitsliste [delete] as $push => $get ) {
			if (! isset ( $arbeitsliste [update] [$push] )) {
				$push = stupidfilereplace ( $push, 2 );
				RS_log ( __ ( 'Deleteing File:' ) . " $push", 3 );
				call_user_func_array ( $transport [loaddaten ( "rs_save" )] [3], array ($push ) );
				rs_cachedelete($push);
			}
		}
	}
 
	//UPDATEN
	if (is_array ( $arbeitsliste [update] )) {
		foreach ( $arbeitsliste [update] as $push => $get ) {
			$push=urldecode($push);
			 
			if(substr($push,-1)=="/")$push.="index.html";
			$get = apply_filters ( "rs-updatefile-source",$get );
			$push = apply_filters ( "rs-updatefile-destination",$push );
		 
			$push = stupidfilereplace ( $push, 2 );
		 
			$dontrefresh = false;
			RS_log ( __ ( 'get', 'reallystatic' ) . " $get", 2 );
			$content = @really_static_geturl (  ($get) );
			
			if ($content !== false) {
				 

				if($allrefresh=="123")$ignorecache=false;
				else $ignorecache=true;
				if(rs_cachetest($push,$content, $ignorecache,$get)){
				
					$geschrieben = call_user_func_array ( $transport [loaddaten ( "rs_save" )] [4], array ($push, $content ) );
					 
					RS_log ( __ ( 'writing', 'reallystatic' ) . ": " . $push . " " . strlen ( $content ) . " Byte", 3 );
					
					do_action ( "rs-write-file", "content", $push, loaddaten ( "realstaticspeicherart", 'reallystatic' ) );
				
				}
			}
		}
	}
				 
	#echo "###UNSET###";
	unset ( $arbeitsliste );
	#print_R($arbeitsliste);
	RS_LOG("rs_onwork".get_option ( "rs_onwork"));
		update_option ( "rs_onwork", "0" );
		RS_LOG("rs_onwork".get_option ( "rs_onwork"));

	return;
}

function rs_cachedelete($ziel){
	global $wpdb;
	$table_name = REALLYSTATICDATABASE;
	$wpdb->query ( "Delete FROM $table_name where url='" . md5 ( $ziel ) . "'" );
	
	
}

/**
$ziel pfad bis zur datei
$content kompletter content der heruntergelanden datei
$ignorecache true= tut so als wäre datei noch nie geladen worden
$quelle quellpfad der datei

return false wenn cachehit
*/
function rs_cachetest($ziel,$content,$ignorecache,$quelle){
	global $wpdb;
	#$content ="qwewqe";
	$table_name = REALLYSTATICDATABASE;
	$contentmd52 = md5 ( $content );
	$dontrefresh = false;
	if(!$ignorecache){
		$querystr = "SELECT datum,content  FROM 	$table_name where url='" . md5 ( $ziel ) . "'";
		$ss = $wpdb->get_results ( $querystr, OBJECT );
		$contentmd5 = $ss [0]->content; 
		$lastmodifieddatum = $ss [0]->datum;
		if ( $contentmd5 == $contentmd52) {
			RS_log ( __ ( 'Cachehit' ) . ": " . loaddaten ( "remotepath" ) . "$ziel @ " . date ( "d.m.Y H:i:s", $lastmodifieddatum ), 2 );
			if(!$silent)reallystatic_configok( __ ( 'Cachehit' ) . ": " . loaddaten ( "remotepath" ) . "$ziel @ " . date ( "d.m.Y H:i:s", $lastmodifieddatum ), 2 );
			$dontrefresh = true;
		}
	}  
	#RS_LOG("rs_cachetest($ziel,".md5($content)."!=$contentmd5,$ignorecache,$quelle)");
	if(!$dontrefresh   ){
		$wpdb->query ( "Delete FROM 	$table_name where url='" . md5 ( $ziel ) . "'" );
		$wpdb->query ( "INSERT INTO `$table_name` (`url` ,`content`,`datum`)VALUES ('" . md5 ( $ziel ) . "', '" . $contentmd52 . "','" . time () . "');" );
	}
	$dontrefresh = apply_filters ( "rs-cachetest-result", $dontrefresh ,$ziel,$content,$ignorecache,$quelle);
	return !$dontrefresh;
}
add_action ( 'wp_update_comment_count', 'rs_setthisactionascomment', 550 );
/**
 * stellt sicher das erstellen von kommentar nicht als seitenedit ausgelegt wird
 * @param none
 * @return none
 */
function rs_setthisactionascomment() {
	#RS_LOG("COMMENTi");
	global $iscomment;
	$iscomment = true;
}

add_action ( 'delete_post', 'delete_poster' );
add_action ( 'deleted_post', 'delete_poster' );
/**
 * wird aufgerufen wenn ein Post geloescht wird
 * @param int Postid
 * @return void
 */
function delete_poster($id) {
	global $arbeitsliste;
	$arbeitsliste [delete] [get_page_link ( $id )] = 1;
}



/**
 * laed eine datei herunter
 * @param string URL der herunterzuladenden DAtei
 * @return string Dateiinhalt
 */
function really_static_download($url) {
	update_option ( "rs_counter", loaddaten ( "rs_counter" ) + 1 );
	$file = apply_filters ( "rs-do-download", false );
	if ($file !== false)
		return $file;
	if (function_exists ( 'file_get_contents' ) and ini_get ( 'allow_url_fopen' ) == 1) {
		$file = @file_get_contents ( $url );
			
	} else {
		$curl = curl_init ( $url );
		curl_setopt ( $curl, CURLOPT_HEADER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		$file = curl_exec ( $curl );
		curl_close ( $curl );
	 
	}
	if ($file === false)do_action ( "rs-error", "loading url", $url,"" );
	//	RS_log ( sprintf ( __ ( "Getting a error (timeout or 404 or 500 ) while loading: %s", 'reallystatic' ), $url ), 1 );
	do_action ( "rs-downloaded-url", $file, $url );
	return $file;
}

 
add_action("rs-info","really_static_infotologfile",10,2);
add_action("rs-error","really_static_infotologfile",10,2);
/**
 * @desc fängt meldungen ab und schreibt diese in die logfile
 * @param String $was Fehlerbezeichner
 * @param Object $info
 * @param Object $info2
 */
function really_static_infotologfile($was, $info, $info2 = "") {

	switch ($was) {
		case ("loading url") :
			RS_log ( sprintf ( __ ( "Getting a error (timeout or 404 or 500 ) while loading: %s", 'reallystatic' ), $info ), 1 );
			break;
		
		case ("write imgfile") :
			RS_log ( __ ( 'Writing ImageFile:', 'reallystatic' ) . " $info", 3 );
			break;
		
		case ("cachehit uploadfile") :
			RS_LOG ( "Cachehit: " . $info, 2 );
			break;
		case ("write file") :
			RS_log ( __ ( 'Writing File:', 'reallystatic' ) . " $info => $info2 @" . get_option ( "realstaticlokalerspeicherpfad" ), 3 );
			break;
		case ("missing right folder create") :
		update_option ( "rs_onwork", "0" );
			RS_LOG ( "Have not enoth rigths to create Folders. tryed ($info): " . $info2 );
			break;
		case ("missing right write file") :
		update_option ( "rs_onwork", "0" );
			RS_log ( __ ( "Really-Static dont have enoth writing Rights at the destinationfolder ( $info ) or the foldername may consist illigal signs. please check<a href='http://php-welt.net/really-static/fehler-chmod.html'>manualpage</a>", 'reallystatic' ), 1 );
			break;
		case ("login error") :
			RS_log ( __ ( "Really-Static dont have enoth writing Rights at the destinationfolder ( $info ) or the foldername may consist illigal signs. please check<a href='http://php-welt.net/really-static/fehler-chmod.html'>manualpage</a>", 'reallystatic' ), 1 );
			break;	
	}

}


remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
 

# remove_action('wp_head', 'post_comments_feed_link');
  #remove_action('wp_head', 'pingback_url');
 
 
#add_filter('post_comments_feed_link',create_function('$a','return;'));
#add_filter('comments_feed_link',create_function('$a','return;'));
#add_filter('comments_rss2_url',create_function('$a','return;'));
#add_filter('bloginfo_url',create_function('$a,$b','if($b=="comments_rss2_url")return; else return $a;'),11,2);  
 
/**
 * @desc Laed eine HTML-Datei herunter und passt sie entsprechend an
 * @param string Url der Datei
 * @return string HTML-Code 
 */
function really_static_geturl($url) {
 
	$file = really_static_download ( $url );
#return $file;
	
	#RS_LOG("#".get_option ( 'home' ) . "/" .'(.+?).html/(/d+)#i' . get_option ( 'home' ) . "/" .'page/$2/$1.html');
	## fuer <!--nextpage-->

	 
	#############################
	if ($file === false)
		return $file;
	$file = apply_filters ( "rs-pre-rewriting-filecontent", $file, $url );
	#if(substr($url,-2)=="/2")
	#	RS_LOG($file,0,REALLYSTATICHOME."out.html");
	$file=preg_replace(  "#".get_option ( 'home' ) . '\/([^/]*)\/(\d+)$#i', get_option ( 'home' ) . '/page/$2/$1',$file);
 		
	
		
	if ($file {0} == "\n")
		$file = substr ( $file, 1 );
	if ($file {0} == "\r")
		$file = substr ( $file, 1 );
	$file = preg_replace_callback ( array ('#<link>(.*?)</link>#', '#<wfw:commentRss>(.*?)</wfw:commentRss>#', '#<comments>(.*?)</comments>#', '# RSS-Feed" href="(.*?)" />#', '# Atom-Feed" href="(.*?)" />#' ), create_function ( '$treffer', 'return str_replace(loaddaten("localurl"),loaddaten("remoteurl"),$treffer[0]);' ), $file );
	global $rs_version;
 
	$file = preg_replace ( '#<generator>http://wordpress.org/?v=(.*?)</generator>#', '<generator>http://www.php-welt.net/really-static/version.php?v=$1-RS' . $rs_version . '</generator>', $file );
	#$file = preg_replace ( '#<link rel=(\'|")EditURI(\'|")(.*?)>\n#', "", $file );
	#$file = preg_replace ( '#<link rel=(\'|")wlwmanifest(\'|")(.*?)>#', "", $file );
	#$file = preg_replace ( '#<link rel=(\'|")alternate(\'|")(.*?) hre>#', "", $file );
	$file = preg_replace ( '#<link rel=(\'|")pingback(\'|")(.*?)>\n#', "", $file );
	
	$file = preg_replace ( '#<link rel=(\'|")shortlink(\'|")(.*?)>\n#', "", $file );	# entfernt<link rel='shortlink' href='http://xyz.de/?p=15/' /> 
	
	$file = preg_replace ( '#<meta name=(\'|")generator(\'|") content=(\'|")WordPress (.*?)(\'|") />#', '<meta name="generator" content="WordPress $4 - really-static ' . $rs_version . '" />', $file );
	$file = str_replace ( loaddaten ( "realstaticdesignlocal" ), loaddaten ( "realstaticdesignremote" ), $file );
	
	if (substr ( $file, 0, 5 ) != "<?xml") {
		$file = preg_replace_callback ( '#<a(.*?)href=("|\')(.*?)("|\')(.*?)>(.*?)</a>#si', "urlrewirte", $file );
	} else {
		$file = preg_replace ( "#<wfw\:commentRss>(.*?)<\/wfw\:commentRss>#", "", $file );
		$file = preg_replace_callback ( '#<(link|comments)>(.*?)<\/(link|comments)>#si', "reallystatic_url_rewirte_sitemap", $file );
		
		$file = preg_replace_callback ( '#<(\?xml\-stylesheet)(.*?)(href)=("|\')(.*?)("|\')(\?)>#si', "reallystatic_url_rewirte_sitemap2", $file );
	} 
	
	$file = preg_replace_callback ( '#<(link|atom\:link|content)(.*?)(href|xml\:base)=("|\')(.*?)("|\')(.*?)>#si', "url_rewirte_metatag", $file );
 
	$file = preg_replace_callback ( '#<img(.*?)src=("|\')(.*?)("|\')(.*?)>#si', "really_static_bildurlrewrite", $file );
	
	#$file = preg_replace_callback ( '#<link rel="canonical" href="(.*?)" />#si', "canonicalrewrite", $file );
 

	if (substr ( $url, - 11 ) == "sitemap.xml") {
		$file = preg_replace_callback ( '#<loc>(.*?)</loc>#si', "sitemaprewrite", $file );
	}
 
	if (loaddaten ( "rewritealllinked" ) == 1) {
		$file = str_replace ( loaddaten ( "realstaticlocalurl", 'reallystatic' ), loaddaten ( "realstaticremoteurl", 'reallystatic' ), $file );
		if (substr ( loaddaten ( "realstaticlocalurl", 'reallystatic' ), - 1 ) == "/")
			$file = str_replace ( substr ( loaddaten ( "realstaticlocalurl", 'reallystatic' ), 0, - 1 ), loaddaten ( "realstaticremoteurl", 'reallystatic' ), $file );
		$file = str_replace ( urlencode ( loaddaten ( "realstaticlocalurl", 'reallystatic' ) ), urlencode ( loaddaten ( "realstaticremoteurl", 'reallystatic' ) ), $file );
 
		if (substr ( loaddaten ( "realstaticlocalurl", 'reallystatic' ), - 1 ) == "/")
			$file = str_replace ( urlencode ( substr ( loaddaten ( "realstaticlocalurl", 'reallystatic' ), 0, - 1 ) ), urlencode ( loaddaten ( "realstaticremoteurl", 'reallystatic' ) ), $file );
	
	} 

	$file = apply_filters ( "rs-post-rewriting-filecontent", $file, $url );
	#l&auml;uft mit <a href="http://wordpress.org/">WordPress</a>
	#$file = str_replace ( '<a href="http://wordpress.org/">WordPress</a>', '<a href="http://www.php-welt.net/really-static/">Realstatic WordPress</a>', $file );
	$file = preg_replace ( '#(Powered by)(\s+)<a(.*?)href=("|\')(.*?)("|\')(.*?)>WordPress</a>#is', '$1$2<a$3href=$4http://www.php-welt.net/really-static/$6$7>really static WordPress</a>', $file );
	//$file = preg_replace ( '#<a(.*?)href=("|\')(.*?)("|\')(.*?)(rel="generator")>Proudly powered by WordPress.</a>#is', '<a$1href=$2http://www.php-welt.net/really-static/$4$5$6>Proudly powered by really static WordPress</a>', $file );
	$file = preg_replace ( '#(Powered by)(\s+)<a(.*?)href=("|\')(.*?)("|\')(.*?)>WordPress MU</a>#si', '$1$2<a$3href=$4http://www.php-welt.net/really-static/$6$7>really static WordPress</a>', $file );
	$file = preg_replace ( '#(Powered by)(\s+)<a(.*?)href=("|\')(.*?)("|\')(.*?)>WordPress MU</a>#si', '$1$2<a$3href=$4http://www.php-welt.net/really-static/$6$7>really static WordPress</a>', $file );
	$file = preg_replace ( '#<div id="site-generator">(.*?)<a href="http://wordpress.org(.*?)title="Semantic Personal Publishing Platform" rel="generator">(.*?)roudly powered by WordPress(.*?)</div>#sim', '<div id="site-generator"><a href="http://www.php-welt.net/really-static/">really static WordPress</a></div>', $file );
	

	 
	return $file;

}
/**
 * Ersetzt im geladenen sourcedokument urls so das z.b. nichtspecherbare sonderzeichen entfallen oder z.b. bla.html/2 zu bla/2 :-)
 */
function stupidfilereplace($text, $art = 1) {
	return $text;
/*	#stupidfilereplaceA wird in stupidfilereplaceB umgeschrieben
	

	if (get_option ( "rs_stupidfilereplaceA" ) === false) {
		
		$a = array_merge ( ( array ) loaddaten ( 'realstaticposteditcreatedelete' ), ( array ) loaddaten ( 'realstaticpageeditcreatedelete' ), ( array ) loaddaten ( 'realstaticcommenteditcreatedelete' ), ( array ) loaddaten ( 'realstaticeveryday' ), ( array ) loaddaten ( 'realstaticeverytime' ) );
		
		$stupidfilereplaceA = array ();
		
		foreach ( $a as $k ) {
			if ($k [1] != "") {
				
				$stupidfilereplaceA [] = '@^' . str_replace ( array ("?", "." ), array ("\?", "\." ), loaddaten ( "remoteurl" ) . $k [0] ) . "$@";
				$stupidfilereplaceB [] = loaddaten ( "remoteurl" ) . $k [1];
				
				if (substr ( $k [0], - 1 ) != "/") {
					$stupidfilereplaceA [] = '@^' . str_replace ( array ("?", "." ), array ("\?", "\." ), loaddaten ( "remoteurl" ) . $k [0] . "/index.html" ) . "$@";
					$stupidfilereplaceB [] = loaddaten ( "remoteurl" ) . $k [1];
				} else {
					$stupidfilereplaceA [] = '@^' . str_replace ( array ("?", "." ), array ("\?", "\." ), loaddaten ( "remoteurl" ) . $k [0] . "index.html" ) . "$@";
					$stupidfilereplaceB [] = loaddaten ( "remoteurl" ) . $k [1];
				}
			
			}
		}
		
		//URL felher ersetzer
		//"lala"
		$stupidfilereplaceA [] = '@\%e2\%80\%93@';
		$stupidfilereplaceB [] = "-";
		$stupidfilereplaceA [] = '@\%e2\%80\%9c@';
		$stupidfilereplaceB [] = "-";
		//ï¿½
		$stupidfilereplaceA [] = '@\%c2\%b4@';
		$stupidfilereplaceB [] = "-";
		
		#multipage
		$stupidfilereplaceA [] = '@(.html)\/(.*?)@';
		$stupidfilereplaceB [] = "/$2";
		foreach ( $stupidfilereplaceA as $k => $v ) {
			$stupidfilereplaceC = str_replace ( str_replace ( array ("?", "." ), array ("\?", "\." ), loaddaten ( "remoteurl" ) ), "", $stupidfilereplaceA );
			$stupidfilereplaceD = str_replace ( loaddaten ( "remoteurl" ), "", $stupidfilereplaceB );
		
		}
		update_option ( "rs_stupidfilereplaceA", $stupidfilereplaceA );
		update_option ( "rs_stupidfilereplaceB", $stupidfilereplaceB );
		update_option ( "rs_stupidfilereplaceC", $stupidfilereplaceC );
		update_option ( "rs_stupidfilereplaceD", $stupidfilereplaceD );
	}
	if ($art == 1)
		return preg_replace ( get_option ( "rs_stupidfilereplaceA" ), get_option ( "rs_stupidfilereplaceB" ), $text );
	else
		return preg_replace ( get_option ( "rs_stupidfilereplaceC" ), get_option ( "rs_stupidfilereplaceD" ), $text );
*/
}
/**
 * Korregiert den connonicallink
 */
function canonicalrewrite($array) {
	$path_parts = pathinfo ( $array [1] );
	if ($path_parts ["extension"] == "") {
		if (substr ( $$array [1], - 1 ) != "/")
			$array [1] .= "/";
	}
	return '<link rel="canonical" href="' . $array [1] . '" />';
}
function sitemaprewrite($array) {
		if (REALSTATICNONPERMANENT === false){
		// wp_link_pages Problemfix
		#nnen.html/2
		#nnen/page/2/index.html
		 $array [1]=preg_replace ( "#.html/([0-9]+)$#i", '/page/$1/index.html',  $array [1] );
		}
		
		
	$path_parts = pathinfo ( $array [1] );
	if ($path_parts ["extension"] == "") {
		if (substr ( $$array [1], - 1 ) != "/")
			$array [1] .= "/";
	}
	return '<loc>' . $array [1] . '</loc>';
}
/**
*	@desc Korrigiert Links zu Bildern und läd sie hoch
*	@param ARRAY mit kompletten Bild HTML-Code
*	@return String mit HTML-Code vom Bild
*/
function really_static_bildurlrewrite($array) {
	// RS_LOGA($array);
	if (loaddaten ( "dontrewritelinked" ) == 1)
		return "<img" . $array [1] . "src=" . $array [2] . $array [3] . $array [4] . $array [5] . ">";
	//RS_LOG("AA");
	if(strpos($array [3],loaddaten ( "realstaticdesignlocal"))!==false)return "<img" . $array [1] . "src=" . $array [2] . loaddaten ( "realstaticdesignremote" ).substr($array [3],strlen(loaddaten ( "realstaticdesignlocal"))) . $array [4] . $array [5] . ">";
	//RS_LOG("BB");
	$url = $array [3];
	$l = strlen ( loaddaten ( "localurl" ) );
	$ll = strrpos ( $url, "/" );
	

	
	
	
	
	if (substr ( $url, 0, $l ) != loaddaten ( "localurl" ))
		return "<img" . $array [1] . "src=" . $array [2] . $array [3] . $array [4] . $array [5] . ">";
		//RS_LOG("CC");
	//$a = str_replace ( loaddaten ( "localurl" ), "", $a );
	//$ppp = ABSPATH;
	
	#global $arbeitsliste;
	#$arbeitsliste [update] [$a] =  $ppp .$a;
	
	   //$out =rs_writefilewithlogin ( $ppp . $a, $a, true );
	$aa = substr ( $url, strlen ( get_option("fileupload_url") ) );
	$a = substr ( $url, strlen ( loaddaten ( "localurl" ) ) );
	
	if(is_multisite())  $out =rs_writefilewithlogin ( loaddaten ( "localpath" ).get_option("upload_path") . $aa, $a, true);
	else   $out =rs_writefilewithlogin ( loaddaten ( "localpath" ) . $a, $a, true );	
	
	if ($out === false) # cachehit
		return "<img" . $array [1] . "src=" . $array [2] . loaddaten ( "remoteurl" ).$a. $array [4] . $array [5] . ">";
	
	
	return "<img" . $array [1] . "src=" . $array [2] . loaddaten ( "remoteurl" ).$a . $array [4] . $array [5] . ">";
}
/**
 * Quelle mit kompletten localepfade
 * Ziel nur relativ vom startpunkt, also pfad vom static order in unterordner wo es hinsoll
 * wenn datumscheck return =false wenn datei schon neuste
 */
function rs_writefilewithlogin($quelle, $ziel, $datumcheck = true) {
	//RS_LOG("($quelle, $ziel, $datumcheck = true)");
	$transport = apply_filters ( "rs-transport", array () );
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [0], array () );
	
	global $wpdb;
	
	$table_name = REALLYSTATICDATABASE;
	$ss = $wpdb->get_results ( "SELECT datum  FROM 	$table_name where url='" . md5 ( $ziel ) . "'", OBJECT );
	$fs = @filemtime ( $quelle );
	if($fs===false){
		do_action ( "rs-error", "localfile not found", $quelle,"" );
		return -1;
	}
	if ($datumcheck == true && $ss [0]->datum == $fs){
		do_action ( "rs-info", "cachehit", $quelle,$fs );
		return false;
	}
	$wpdb->query ( "Delete FROM $table_name where url='" . md5 ( $ziel ) . "'" );
	$wpdb->query ( "INSERT INTO `" . $wpdb->prefix . "realstatic` (`url` ,`datum`)VALUES ('" . md5 ( $ziel ) . "', '$fs');" );
	global $internalrun;
	if ($internalrun == true)
		reallystatic_configok ( $quelle, 2 );
	
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [2], array ($ziel, $quelle ) );
	
	/*	if (loaddaten ( "realstaticspeicherart", 'reallystatic' ) == 1)
	rs_writefile ( loaddaten ( "remotepath" ) . $ziel, $quelle );
	elseif (loaddaten ( "realstaticspeicherart", 'reallystatic' ) == 2)
	rs_writefile ( loaddaten ( "realstaticlokalerspeicherpfad", 'reallystatic' ) . $ziel, $quelle );
	elseif (loaddaten ( "realstaticspeicherart", 'reallystatic' ) == 3)
	rs_writefile ( loaddaten ( "realstaticremotepathsftp", 'reallystatic' ) . $ziel, $quelle );*/
	do_action ( "rs-info", "write imgfile", $a ,"" );
	
	do_action ( "rs-write-file", "img", loaddaten ( "remotepath" ) . $a, loaddaten ( "realstaticspeicherart", 'reallystatic' ) );
	
	return true;
}
/**
 * passt ein Links in einem Sitemap an
 * @param array url
 * @return string url
 */
function reallystatic_url_rewirte_sitemap($array) {
	$url = str_replace ( loaddaten ( "localurl" ), loaddaten ( "remoteurl" ), $array [2] );
	if (strpos ( $url, loaddaten ( "remoteurl" ) ) !== false) {
		$url = stupidfilereplace ( $url );
		$url = nonpermanent ( $url );
	}
	return "<" . $array [1] . ">" . $url . "</" . $array [3] . ">";
}
/**
 * passt ein Links in einem XML-Stylesheet an
 * @param array xml-url
 * @return string xml-url
 */
function reallystatic_url_rewirte_sitemap2($array) {
	
	$url = loaddaten ( "remoteurl" ) .substr($array [5],strlen( get_option ( 'home' ) . "/"));
	if (strpos ( $url, loaddaten ( "remoteurl" ) ) !== false) {
		$url = stupidfilereplace ( $url );
		$url = nonpermanent ( $url );
	}
	return "<" . $array [1] . "" . $array [2] . "" . $array [3] . "=" . $array [4] . $url . $array [6] . "" . $array [7] . "" . $array [8] . ">";
}
/**
 * passt ein Links in Metatags an
 * @param array url
 * @return string url
 */
function url_rewirte_metatag($array) {
	$url = $array [5];
	if($url==@reallystatic_rewrite(get_bloginfo('comments_rss2_url'),1))return"";
	if (strpos ( $url, loaddaten ( "remoteurl" ) ) !== false) {
		$url = stupidfilereplace ( $url );
		$url = nonpermanent ( $url );
	}
	return "<" . $array [1] . $array [2] . $array [3] . "=" . $array [4] . $url . $array [6] . $array [7] . ">";
}
/*
* @desc ersetzt im Textgefundene Links; sollte eigentlich ausgemustert werden, komme sonst aber nicht an alles
* @param Array $array zerlegter HTML-link
* @returm String kompletter Link
*/
function urlrewirte($array) {
  
	$url = $array [3];
	
	if (REALSTATICNONPERMANENT === false) {
		$url = preg_replace ( "#/index.html/([0-9]+)$#i", '/$1/index.html', $url );
		$url = preg_replace ( "#.html/([0-9]+)$#i", '/$1/index.html', $url );
	}
	
	if (strpos ( $url, get_option ( 'siteurl' ) . "/" ) !== false) {
		$exts = loaddaten ( "dateierweiterungen" );
		$ext = strrchr ( strtolower ( $url ), '.' );
		if (loaddaten ( "dontrewritelinked" ) != 1 && 1 == $exts [$ext]) {
			$ll = substr ( $url, strlen ( get_option("fileupload_url") ) );
			$l = substr ( $url, strlen ( loaddaten ( "localurl" ) ) );

			
			#RS_LOG( loaddaten ( "localurl" )."-->".get_option("fileupload_url"));
			#RS_LOG( loaddaten ( "localpath" ).get_option("upload_path") . $l.",". $l);
			if(is_multisite())really_static_uploadfile ( loaddaten ( "localpath" ).get_option("upload_path") . $ll, $l );
			else really_static_uploadfile ( loaddaten ( "localpath" ) . $l, $l );
			$url = loaddaten ( "remoteurl" ) . $l;
		} else {
			if (loaddaten ( "dontrewritelinked" ) == 1 && 1 == $exts [$ext])
				$url = $array [3];
			$url = stupidfilereplace ( $url );
			$url = nonpermanent ( $url );
		}
	}
	global $seitenlinktransport;
	$seitenlinktransport = "";
	#if (strpos ( $url, "page=" ) !== false)
	#	$url = reallystatic_urlrewrite ( $url, 1 );

	return "<a" . $array [1] . "href=" . $array [2] . $url . $array [4] . $array [5] . ">" . $array [6] . "</a>";
}
/**
 * 
 * @desc Lad eine datei auf den ziel Server
 * @param String $local lokaler Dateipfad
 * @param String $remote entfernter Dateipfad
 * @return boolean je nach Erfolg
 */
function really_static_uploadfile($local, $remote) {
	//RS_LOG("!! ($local, $remote)");
	$fs = @filemtime ( $local );
 	if($fs===false){
 		do_action ( "rs-info", "error file not accessable", $local,$remote );
 	}
	global $wpdb;
	$table_name = REALLYSTATICDATABASE;
	$ss = $wpdb->get_results ( "SELECT datum  FROM 	$table_name where url='" . md5 ( $local ) . "'", OBJECT );
 
	if ($ss [0]->datum == $fs) {
		
		do_action ( "rs-info", "cachehit uploadfile", $local,$remote );
		return false;
	}
	$transport = apply_filters ( "rs-transport", array () );
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [0], array () );
	//RS_LOG("$local,$remote ");
	do_action ( "rs-info", "write file", $local,$remote );
	#RS_log ( __ ( 'Writing File:', 'reallystatic' ) . " $local => $remote @" . get_option ( "realstaticlokalerspeicherpfad" ), 3 );
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [2], array ($remote, $local ) );
	do_action ( "rs-write-file", "any", $local, loaddaten ( "realstaticspeicherart", 'reallystatic' ) );
	$wpdb->query ( "Delete FROM $table_name where url='" . md5 ( $local ) . "'" );
	$wpdb->query ( "INSERT INTO `" . $wpdb->prefix . "realstatic` (`url` ,`datum`)VALUES ('" . md5 ( $local ) . "', '$fs');" );
	return true;
}
/*
* Erneuern einer einzelnen seite
* Hauptfunktion 2
*/
function getnpush($get, $push, $allrefresh = false) {
	global $notagain, $wpdb;
	if (loaddaten ( "dontrewritelinked" ) != 1) {
		$push = str_replace ( loaddaten ( "remoteurl" ), "", stupidfilereplace ( loaddaten ( "remoteurl" ) . $push ) );
		$push = nonpermanent ( $push );
	}
	$path_parts = pathinfo ( $push );
	
	if ($path_parts ["extension"] == "") {
		if (substr ( $push, - 1 ) != "/")
			$push .= "/index.html";
		else
			$push .= "index.html";
	}
	
	$table_name = REALLYSTATICDATABASE;
	if ($allrefresh !== false) {
		//timeout hile
		$querystr = "SELECT datum,content  FROM 	$table_name where url='" . md5 ( $push ) . "'";
		$ss = $wpdb->get_results ( $querystr, OBJECT );
		$contentmd5 = $ss [0]->content;
		$lastmodifieddatum = $ss [0]->datum;
		if ($allrefresh === true and $lastmodifieddatum > 0) {
			return;
		}
	}
	
	global $arbeitsliste;
	$arbeitsliste [update] [$push] = $get;
}


/**
 * @desc Entfernt moegliche urlvorsetze --> realer pfad wordpresspfad ohne domain davor
 */
function cleanupurl($url) {
	return str_replace ( get_option ( 'home' ) . "/", "", $url );
	return str_replace ( array (get_option ( 'siteurl' ) . "/", get_option ( 'siteurl' ), loaddaten ( "localurl" ), loaddaten ( "remoteurl" ) ), array ("", "", "", "" ), $url );
}
function rs_arbeitsliste_create_add($get,$push){
	global $arbeitsliste;
	$arbeitsliste [update] [$get] = $push;
}

add_filter('rs-todolist-add-indexlink',"rsindexlink",999,2);
function rsindexlink($url,$replace){
	if(ereg("%indexurl%",$url))$url=str_replace("%indexurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-taglink',"rstaglink",999,2);
function rstaglink($url,$replace){
	if(ereg("%tagurl%",$url))$url=str_replace("%tagurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-catlink',"rscatlink",999,2);
function rscatlink($url,$replace){
	if(ereg("%caturl%",$url))$url=str_replace("%caturl%",$replace,$url);
	return $url;
}

add_filter('rs-todolist-add-authorlink',"rsauthorlink",999,2);
function rsauthorlink($url,$replace){
	if(ereg("%authorurl%",$url))$url=str_replace("%authorurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-datedaylink',"rsdatedaylink",999,2);
function rsdatedaylink($url,$replace){
	if(ereg("%dateurl%",$url))$url=str_replace("%dateurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-datemonthlink',"rsdatemonthlink",999,2);
function rsdatemonthlink($url,$replace){
	if(ereg("%dateurl%",$url))$url=str_replace("%dateurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-dateyearlink',"rsdateyearlink",999,2);
function rsdateyearlink($url,$replace){
	if(ereg("%dateurl%",$url))$url=str_replace("%dateurl%",$replace,$url);
	return $url;
}
add_filter('rs-todolist-add-commentlink',"rscommentlink",999,2);
function rscommentlink($url,$replace){
	if(ereg("%dcommenturl%",$url))$url=str_replace("%commenturl%",$replace,$url);
	return $url;
}


add_filter('trash_comment', "sdfsdcc");
add_filter('spam_comment', "sdfsdcc"); 
add_filter('untrash_comment', "sdfsdcc");
add_filter('unspam_comment', "sdfsdcc");

/**
 * sammelt vorabinformationen
 */
function sdfsdcc($cid) {
 
	$c = get_comment ( $cid );
	global $rs_commentpostionsinfo;
	$rs_commentpostionsinfo = rs_commentpageinfo ( $c->comment_post_ID, $c->comment_ID );
}



 

add_filter('comment_save_pre', "sdfsda"); #wp_update_comment holt id aus 
add_filter('preprocess_comment', "sdfsdb"); #erstell
add_filter('get_comment', "sdfsdc");
function sdfsda($a){
 
	global $rs_cid,$rs_commentpostionsinfo;  $c=get_comment($rs_cid);
#RS_LOGA($c);
$rs_commentpostionsinfo=rs_commentpageinfo($c->comment_post_ID,$c->comment_ID);

return $a;}
function sdfsdb($c){
 
global $rs_commentpostionsinfo;
$rs_commentpostionsinfo=rs_commentpageinfo($c[comment_post_ID]);return $c;}
function sdfsdc($a){global $rs_cid; $rs_cid=$a->comment_ID;return $a;}


add_action('pre_post_update', "sdfsd");


function sdfsd($id,$return=false){
	$gp=get_post ( $id );
	if($return==false)global $oldpagepost;
	foreach(wp_get_post_categories($id) as $v)$oldpagepost[cat]=get_category_parentids($oldpagepost[cat],$v,$gp );
	foreach(wp_get_post_tags($id) as $v)$oldpagepost[tag]=get_tag_parentids($oldpagepost[tag],$v->term_id,$gp );
	#get_category_parentids($oldpagepost[cat],$v );
	
	
	
	
	
	
		$oldpagepost[date]=$gp->post_date;
	
#	$oldpagepost[tag]=wp_get_post_tags($id, array( 'fields' => 'ids' ) );
	$oldpagepost[author][art]=$gp->post_author;
	$oldpagepost[author][gesamt]=sdfsdfs($gp->post_author,0);
	$oldpagepost[author][page]=sdfsdfs($gp->post_author, strtotime($oldpagepost[date]));
	
	$oldpagepost[page][postgesamt]=main_count_post();
	$oldpagepost[page][post]=main_count_post_until($oldpagepost[date]);
	
	
	

	
	$oldpagepost[page][date_day_gesamt]=rs_post_on_page(date ( "Y-m-d 00:00:00", strtotime($oldpagepost[date])),date("Y-m-d 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	$oldpagepost[page][date_month_gesamt]=rs_post_on_page(date ( "Y-m-1 00:00:00", strtotime($oldpagepost[date])),date("Y-m-". date ( "t", $oldpagepost[date] )." 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	$oldpagepost[page][date_year_gesamt]=rs_post_on_page(date ( "Y-1-1 00:00:00", strtotime($oldpagepost[date])),date("Y-12-31 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	
	$oldpagepost[page][date_day]=1+$oldpagepost[page][date_day_gesamt]-rs_post_on_page(date ( "Y-m-d H:i:s", strtotime($oldpagepost[date])),date("Y-m-d 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	$oldpagepost[page][date_month]=1+$oldpagepost[page][date_month_gesamt]-rs_post_on_page(date ( "Y-m-1 H:i:s", strtotime($oldpagepost[date])),date("Y-m-". date ( "t", $oldpagepost[date] )." 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	$oldpagepost[page][date_year]=1+$oldpagepost[page][date_year_gesamt]-rs_post_on_page(date ( "Y-1-1 H:i:s", strtotime($oldpagepost[date])),date("Y-12-31 23:59:59", strtotime($oldpagepost[date])),"post_type='post' and post_status = 'publish'");
	
#	foreach(wp_get_post_tags($id) as $v){
	#	$oldpagepost[page]["tag".$v->term_id]=getinnewer ( $gp->post_date, get_option("posts_per_page"), $v->term_id, 'post_tag' );
	#	$oldpagepost[page]["tag".$v->term_id."_gesamt"]=getinnewer ( 0, get_option("posts_per_page"), $v->term_id, 'post_tag' );
	#}
	#RS_LOGA($oldpagepost);
	
	#RS_LOG("...................");
	#RS_LOGA(wp_get_post_categories($id ));
	if($return==true)return $oldpagepost;
	
	
#RS_LOGA(get_post ( $id ));

}

 

function rs_posteditdiff($postid){
	global $oldpagepost;
	$nd=sdfsd($postid,true);
	$do[cat]=array_diff_key ($oldpagepost[cat][page],$nd[cat][page]);
	#$do[tag]=array_diff_key ($oldpagepost[tag][page],$nd[tag][page]);
	
	
#	RS_LOG("#####################");
#	RS_LOGA($oldpagepost[tag][page]);
#	RS_LOGA($nd[tag][page]);
#	RS_LOGA(array_keys(array_diff_key ($oldpagepost[tag][page],$nd[tag][page])));
#	RS_LOGA(array_keys(array_diff_key ($nd[tag][page],$oldpagepost[tag][page])));
#	RS_LOGA(array_merge(array_keys(array_diff_key ($oldpagepost[tag][page],$nd[tag][page])),array_keys(array_diff_key ($nd[tag][page],$oldpagepost[tag][page]))));
#	
#	RS_LOG("#####################");
	
	
	$do[tag]=array_merge(array_keys(array_diff_key ($oldpagepost[tag][page],$nd[tag][page])),array_keys(array_diff_key ($nd[tag][page],$oldpagepost[tag][page])));
	
	$do[author_post]=$nd[author];
	$do[author_pre]=$oldpagepost[author];
	
	
	if($oldpagepost[date]!=$nd[date])$do[date]=array($oldpagepost[date],$nd[date]);
	else $do[date]=array("",$nd[date]);
	
	$do[pre_page]=$oldpagepost[page];
	$do[post_page]=$nd[page];
	
	
	$do[cat_pre]=$oldpagepost[cat];
	$do[cat_post]=$nd[cat];
	
	$do[tag_pre]=$oldpagepost[tag];
	$do[tag_post]=$nd[tag];
	
	#RS_LOGA($do);

	return $do;
}
function rs_post_on_page($oben,$unten,$subbedingung="1"){
	global $wpdb;
	$q="SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_status = 'publish' AND post_date>='" .$oben. "' and post_date<='$unten' and $subbedingung";
	 //RS_LOG("rs_post_on_page:".$wpdb->get_var( $wpdb->prepare($q) ) ."       via: $q");
	 
	  return $wpdb->get_var( $wpdb->prepare($q) );
}
/**
postid
commentid
*/
function rs_commentpageinfo($id,$cid=-1){
	global $post;
	#RS_LOG( "$id,$cid");
	#RS_LOGA( $post);
	if($id=="")$id=$post->ID;
	
//	RS_LOG("last_changed".	wp_cache_get('last_changed', 'comment'));
	wp_cache_set('last_changed',mt_rand(0,time()-99) , 'comment'); // bekomme immer aktuelle werte
	//wp_cache_flush();
//	RS_LOG("last_changed".	wp_cache_get('last_changed', 'comment'));
	$args = array(
			'author_email' => '',
			'ID' => '',
			'karma' => '',
			'number' => '',
			'offset' => '',
			'orderby' => '',
			'order' => get_option("comment_order"),
			'parent' => '0',
			'post_id' => $id,
			'post_author' => '',
			'post_name' => '',
			'post_parent' => '',
			'post_status' => '',
			'post_type' => '',
			'status' => '',
			'type' => '',
			'user_id' => '',
			'search' => '',
			'count' => false
	);
#	$a[gesamt]=floor(count(get_comments( $args ))/ get_option ( "comments_per_page" ));
	$a[gesamt]=count(get_comments( $args ));
	#RS_LOG(" rs_commentpageinfo($id,$cid) = ".$a[gesamt]);
	
	
	if($cid==-1)return $a;
	
 
	
		$args = array(
			'author_email' => '',
			'ID' => '',
			'karma' => '',
			'number' => '',
			'offset' => '',
			'orderby' => '',
			'order' => 'DESC',
			'parent' => '',
			'post_id' => $id,
			'post_author' => '',
			'post_name' => '',
			'post_parent' => '',
			'post_status' => '',
			'post_type' => '',
			'status' => '',
			'type' => '',
			'user_id' => '',
			'search' => '',
			'count' => false
	);
	$i=0;
	#RS_LOGA(get_comments( $args ));
	foreach(get_comments( $args ) as $k=>$v){
		if($v->comment_parent==0)$i++;
		if($v->comment_ID==$cid)break;
	
	}
	$a[page]= $i ;
	return $a;
}

// Wird aufgerufen wenn ein Post editiert wird
add_action ( 'publish_to_publish', create_function ( '$array', ' 	RS_log ("edit POST ".$array->ID);renewrealstaic($array->ID); ' ) );

// Post wurde veroeffentlicht
add_action ( 'private_to_published', create_function ( '$array', ' 	RS_log ("publ POST ".$array);	renewrealstaic($array, 123,"postcreate");rs_refreshallcomments($array->ID);' ) );

// Post wurde gelöscht
add_action ( 'publish_to_trash', create_function ( '$array', ' 	RS_log ("del POST ");renewrealstaic($array->ID, 123,"postdelete");

		rs_refreshallcomments($array->ID);
		
		
		
		' ) );
 
function rs_refreshallcomments($postid){
 
	$args = array(
			"author_email" => "",
			"ID" => "",
			"karma" => "",
			"number" => "",
			"offset" => "",
			"orderby" => "",
			"order" =>"",
			"parent" => "0",
			"post_id" => $postid,
			"post_author" => "",
			"post_name" => "",
			"post_parent" => "",
			"post_status" => "",
			"post_type" => "",
			"status" => "",
			"type" => "",
			"user_id" => "",
			"search" => "",
			"count" => false
	);
 
	foreach(get_comments( $args ) as $v)reallystatic_editkommentar($v->comment_ID,true);
}

/**
 * @desc Die Hauptfunktion die alle subseiten mit statisiert
 *
 * @param int $id
 * @param int $allrefresher
 * @param string $operation
 * @param array $subarbeitsliste ...enthält arbeitszeugs v: d(elete)/r(efrsh)
 * @return void
 */

function renewrealstaic($id, $allrefresher = 123, $operation = "", $subarbeitsliste = "") {
	global $post;
	$post = get_post ( $id );
	global $iscomment, $allrefresh, $homeurllaenge;
	$homeurllaenge = strlen ( get_option ( 'home' ) . "/" );
	$allrefresh = $allrefresher;
	global $wpdb, $notagain, $wp_rewrite;
	//test ob es ein Entwurf ist
	$table_name = $wpdb->prefix . "posts";
	//Eintraege pro post
	$querystr = " SELECT post_status  FROM $table_name where id='" . $id . "'";
	$post_status = $wpdb->get_results ( $querystr, OBJECT );
	$post_status = $post_status [0]->post_status;
	
	if ($post_status == "draft" or wp_is_post_autosave ( $id ))
		return;
	global $arbeitsliste;
	$notagain [$id] = 1;
	global $publishingpost;
	if ($_POST ["originalaction"] == "post")
		$publishingpost = true;
	$table_name = $wpdb->prefix . "options";
	//Eintraege pro post
	$querystr = " SELECT option_value  FROM $table_name where option_name='posts_per_page'";
	$pageposts = $wpdb->get_results ( $querystr, OBJECT );
	$pageposts = $pageposts [0]->option_value;
	$table_name = $wpdb->prefix . "posts";
	//Eintraege pro post
	$querystr = "SELECT post_date  FROM $table_name where ID='$id'";
	$erstell = $wpdb->get_results ( $querystr, OBJECT );
	$erstell = $erstell [0]->post_date; //wann wurde post erstellt
	
	
	
 
	
	
	$posteditdiff = @rs_posteditdiff ( $id );
		

	if ($operation != "komentaredit" && ! ($post->post_type == "page")) {
		if ($operation != "komentarerstellt" or loaddaten ( "realstaticrefreshallac" )) {
			index_refresh ( $erstell,$pageposts,$homeurllaenge,$posteditdiff );
		}
	}	

	
 


	
	if ($operation != "komentaredit" or isset ( $subarbeitsliste ["seiteselber"] )) {
		#	RS_LOG("Seite selber");
		seiteselberrefresh($id,$operation,$homeurllaenge,$subarbeitsliste,$post_status);


	}
	if (is_array ( $subarbeitsliste ))
		unset ( $subarbeitsliste ["seiteselber"] );
	
		#RS_LOG("subcommentarseiten");
	

	if (is_array ( $subarbeitsliste ))comment_refresh($id,$homeurllaenge,$subarbeitsliste);
	if ($post->post_type == "page")
		return;
	if ($operation == "komentaredit")
		return;
	if ($operation == "komentarerstellt" and ! loaddaten ( "realstaticrefreshallac" ))
		return;
	
	author_refresh($id,$posteditdiff,$erstell, $pageposts, $operation, $homeurllaenge,$authorid);
	
	categorry_refresh($posteditdiff,$homeurllaenge,$pageposts,$operation);
	
	tag_refresh( $posteditdiff,$erstell, $pageposts, $k, $operation, $homeurllaenge,$pageposts);
	
	date_refresh($posteditdiff, $erstell, $operation, $homeurllaenge, $pageposts);
	
	
	



}
/**
 * weitere Statische dateien hinzu
 */
function reallystaticsinglepagehook($url) {
	
	global $arbeitsliste;
	
	$arbeitsliste [update] [$url] = loaddaten ( "localurl" ) . $url;

}
/**
 * weitere Statische dateien loeschen
 */
function reallystaticsinglepagedeletehook($url) {
	global $arbeitsliste;
	$arbeitsliste [delete] [$url] = 1;
}
/**
 * Loeschen durchfuehren
 */
function reallystaticdeletepage($url) {
	global $wpdb;
	
	global $arbeitsliste;
	
	$table_name = REALLYSTATICDATABASE;
	if ($url != "") {
		$arbeitsliste [delete] [loaddaten ( "remotepath" ) . $url] = 1;
	} else {
		global $reallystaticsinglepagedelete;
		if (isset ( $reallystaticsinglepagedelete )) {
			foreach ( $reallystaticsinglepagedelete as $v ) {
				$arbeitsliste [delete] [loaddaten ( "remotepath" ) . $v] = 1;
			}
		}
	
	}

}

/**
 * Fuer das AdminMenue
 * @param void
 * @return void
 */
add_action ( 'admin_menu', 'really_static_mainmenu', 22 );
function really_static_mainmenu() {
	if (function_exists ( 'add_submenu_page' ))
		add_submenu_page ( 'options-general.php', 'Really Static', 'Really Static', 10, REALLYSTATICMAINFILE, 'really_static_settings' );
	else
		add_options_page ( 'Really Static', 'Really Static', 8, REALLYSTATICMAINFILE, 'really_static_settings' );
}

add_filter('plugin_row_meta', "rs_aditionallinks", 10, 2);
function rs_aditionallinks($links, $file) {
	if ($file == basename(dirname(__FILE__)) . '/' . basename(__FILE__)) {
		$links[] = '<a href="options-general.php?page=' . REALLYSTATICBASE .'&menu=123">goto the 1-2-3 quicksetup again</a>';
		$links[] = '<a href="http://really-static-support.php-welt.net/">' . __('Support', 'really_static') . '</a>';
	}
	return $links;
}

function really_static_multimainmenu() {
	add_submenu_page( 'settings.php', 'Really Static', 'Really Static', 'manage_options', REALLYSTATICMAINFILE, 'really_static_multisettings' );
}
add_action('network_admin_menu', 'really_static_multimainmenu');
function really_static_multisettings() {
	/*if(get_site_option( 'testtest' )===false)add_site_option( 'testtest', 0 );
	update_site_option( 'testtest',get_site_option( 'testtest' ) +1);
	echo "!!".get_site_option( 'testtest' );*/
	require_once ("php/multiadmin.php");
}
/**
 * Setingsmenue
 * @param void
 * @return void
 */
function really_static_settings() {
	require_once("php/functions.php");
	$base = plugin_basename ( __FILE__ );
	if (is_array ( $_POST ))
		require_once ("php/configupdate.php");
	if ($_GET ["menu"] == "123")
		require_once ("php/123.php");
	else {
		$h = "";
		$reallystaticfile = filemtime ( __FILE__ );
		require_once ("php/admin.php");
		 
	}
}

/**
 * Zeigt ein Problemmeldung an
 * @param int Formatierungsart
 * @param string Textliche Problemmeldung
 * @return void
 */
function reallystatic_configerror($id, $addinfo = "") {
	if ($id == 0) {
		echo '<div id="front-page-warning" class="updated" style="background-color:#FF9999;border-color:#990000;">
	<p>' . $addinfo . '</p>
</div>';
	} else {
		echo '<div id="front-page-warning" class="updated fade-ff0000">
	<p>';
		reallystatic_errorinfo ( $id, $addinfo );
		echo '</p>
</div>';
	}
}
/**
 * Zeigt ein OK ab
 * @param string
 * @param int Formatierungsart
 * @return void
 */
function reallystatic_configok($text, $typ = 1) {
	if ($typ == 1)
		echo '<div id="message" class="updated" style="background: #FFA; border: 1px #AA3 solid;"><p>' . $text . '</p></div>';
	elseif ($typ == 3) {
		echo '<script type="text/javascript">doingout("<b>Ready</b> <a href=\'#end\'>' . __ ( "jump to end" ) . '</a>");</script><a name="end"></a>';
	} else {
		global $showokinit;
		if ($showokinit != 2) {
		#RS_LOGA($_POST);
		#RS_LOG($text);
			if ($_POST ["pos"] == "3")
				echo "<h2>Generating Files</h2>" . __ ( "Really-Staic is now generating, static files out of your Blog. Please wait until really-static is ready." );
			echo '<form  method="post" id="my_fieldset"><input type="hidden" name="strid2" value="rs_refresh" />
<input type="hidden" name="hideme" value="hidden" />
<input type="hidden" name="pos" value="3" />
<input type="submit" value=" If this stop befor its ready (because of a timeout) Press this Button"></form>';
			$showokinit = 2;
			echo '<div id="okworking"  class="updated fade"> blabla </div><script type="text/javascript">	function doingout(text){
	document.getElementById(\'okworking\').innerHTML = text;
	}</script><b>Done:</b><br>';
		}
		echo '<script type="text/javascript">doingout("<b>Working on:</b> ' . $text . '");</script>';#' . $text . "<br>";
	}
	ob_flush ();
	flush ();

}

function reallystatic_errorinfo($id, $addinfo = "") {
	
	require ("php/errorid.php");
}
/**
 * a = entscheider
 * t=1 = nur true false; t=2 rest
 * dann b
 * sonst c
 */
function ison($a, $t, $b, $c = "", $d = "") {
	global $ison;
	if ($t == 1) {
		if ($a === true) {
			$ison ++;
			return $b;
		} else {
			$ison --;
			return $c;
		}
	} elseif ($t == 2) {
		if ($a == true) {
			$ison ++;
			return $b;
		} else {
			$ison --;
			return $c;
		}
	} elseif ($t == 3) {
		if ($a == $d) {
			$ison ++;
			return $b;
		} else {
			$ison --;
			return $c;
		}
	}
}



/**
 * 
 * @desc wird aufgerufen fuer Kategorien
 * @param unknown_type $operation  art des postedits
 * @param unknown_type $erstell timestamp
 * @param unknown_type $pageposts post pro seite
 * @param unknown_type $category
 * @param unknown_type $allrefresh
 * @param unknown_type $muddicat
 * @param unknown_type $url
 */
function really_static_categoryrefresh($category,$homeurllaenge, $seite , $seitemax ,$art) {
	
	#RS_LOG("really_static_categoryrefresh $seite , $seitemax ,$art");
	global $publishingpost, $rscatnewpage, $arbeitsliste, $homeurllaenge;
	for($seiter = $seite; $seiter <= $seitemax; $seiter ++) {
		if ($seiter > 1) {
			if (REALSTATICNONPERMANENT == true)
				$seitee = "&paged=$seiter";
			else
				$seitee = "/page/$seiter";
		} else
			$seitee = "";
		foreach ( loaddaten ( "makestatic-a3" ) as $value ) {
			$url = $value [1];
			if ($url == "")
				$url = $value [0];
			global $seitenlinktransport;
			$seitenlinktransport = $seitee;
			$templink = apply_filters ( "rs-todolist-add-catlink", $url, substr ( get_category_link ( $category ), $homeurllaenge ) );
			if($art=="update"){
				$templink = apply_filters ( "rs-todolist-add", $templink );
				if ($templink !== false)
					rs_arbeitsliste_create_add ( reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ) );
			}else{
				$templink = apply_filters ( "rs-todolist-delete", $templink );
				if ($templink !== false)
					rs_arbeitsliste_delete_add ( reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ) );				
				
			}
		}
	}
}
/**
 * @desc Teste ob es eine neue Version von Really-Static gibt
 * @return boolean true wenn neue version
 */
function testfornewversion() {
	$rz = get_option ( "rs_versionsize" );
	$rd = get_option ( "rs_versiondate" );
	#$rd = get_option ( "rs_versiondate" );
	if ((time () - $rd) > (86400 * RSVERSIONCHECK)) {
		$out = @get_headers ( "http://downloads.wordpress.org/plugin/really-static.zip", 1 );
		update_option ( "rs_versiondate", time () );
		update_option ( "rs_versionsize", $out [Content - Length] );
		if ($out [Content - Length] != $rz)
			return true;
	}
	return false;
}
/**
 * @desc Behandelt alle Links mit ? und formt sie um
 * @param string $url URL die bearbeitet werden soll
 * @return string ueberarbeite URL
 */
function nonpermanent($url) {
	return $url;
	if ($url == "")
		return "index.html";
	if (REALSTATICNONPERMANENT != true) {
		
		if (substr ( $url, - 1 ) != "/" && strpos ( str_replace ( loaddaten ( "remoteurl" ), "", $url ), "." ) === false) {
			if (strpos ( $url, "#" ) === false)
				return $url . "/";
			else
				return $url;
		} else
			return $url;
	}
	#	RS_LOGD($url);
	$url = preg_replace ( "#\&amp;cpage=(\d+)#", "", $url );
	if (strpos ( $url, "?" ) !== false) {
		$url = str_replace ( "&#038;", "/", $url );
		$url = str_replace ( "&", "/", $url );
		if (strpos ( $url, "#" ) !== false)
			$url = str_replace ( "#", "/index.html#", str_replace ( "?", "", $url ) );
		else
			$url = str_replace ( "?", "", $url ) . "/";
	}
	$url = preg_replace ( "#" . loaddaten ( "remoteurl" ) . "wp-trackback.phpp\=(\d+)#", loaddaten ( "localurl" ) . "wp-trackback.php?p=$1", $url );
	if (substr ( $url, - 2 ) == "//")
		$url = substr ( $url, 0, - 1 );
	if (substr ( $url, - 1 ) == "/")
		$url = $url . "index.html";
	
		#RS_LOGD("OUT:".$url); 
	return $url;
}

// Fuegt Links ins PLUGIN Menue von Wordpress
add_filter ( 'plugin_row_meta', create_function ( '$links, $file', '$base = plugin_basename ( __FILE__ );	if ($file == $base) {		$links [] = "<a href=\"options-general.php?page=" . $base . "\">" . __ ( "Settings" ) . "</a>";		$links [] = "<a href=\"http://blog.phpwelt.net/tag/really-static/\">" . __ ( "Support" ) . "</a>";		$links [] = "<a href=\"http://www.php-welt.net/really-static/index.html#donate\">" . __ ( "Donate" ) . "</a>";	}	return $links;' ), 10, 2 );

add_action ( 'wp_default_scripts', 'wp_default_scripts2' );
/**
 * @desc Erinnert den Benutzer doch etwas zu s p e n d e n bei der nachrichteneingabe
 * @param object
 * @return object
 */
function wp_default_scripts2($scripts) {
	if (loaddaten ( "realstaticdonationid" ) == "-" and loaddaten ( "rs_counter" ) > 2000) {
		$scripts->add ( 'word-count', "/wp-admin/js/word-count$suffix.js", array ('jquery' ), '20090422' );
		$scripts->add_data ( 'word-count', 'group', 1 );
		$scripts->localize ( 'word-count', 'wordCountL10n', array ('count' => "<strong>You are using Really-static for a long time. Please donate</strong><br>" . __ ( 'Word count: %d' ), 'l10n_print_after' => 'try{convertEntities(wordCountL10n);}catch(e){};' ) );
	}
	return $scripts;
}
/**
 * @desc Fuegt ein index.html an Ordnernamen an
 * @param string $url URL die bearbeitet werden soll
 * @return string
 */
function urlcorrect($url) {
	if (REALSTATICNONPERMANENT != true) {
		$path_parts = pathinfo ( $url );
		if ($path_parts ["extension"] == "") {
			
			if (substr ( $url, - 1 ) != "/")
				$url .= "/index.html";
			else
				$url .= "index.html";
		}
	}
	return $url;
}

add_action ( 'delete_attachment ', create_function ( '$a', 'RS_log("delteattach:".$a);' ) );
add_action ( 'edit_attachment ', create_function ( '$a', 'RS_log("editattach:".$a);' ) );

 



############
#
#
# COMMENTAR START
#
#
###############
#add_action ( 'trash_comment', 'reallystatic_deletekommentar' );

add_action ( 'comment_post', 'reallystatic_newkommentar', 999, 2 );
add_action ( 'edit_comment', 'reallystatic_editkommentar' );
//add_action ( 'transition_comment_status', 'reallystatic_kommentarjob',10,3 );
#add_action ( 'trashed_comment', 'reallystatic_editkommentar' );
#add_action ( 'spammed_comment', 'reallystatic_editkommentar' );
add_action ( 'transition_comment_status', 'rs_transition_comment_status',10,3 );
#add_action ( 'untrashed_comment', 'reallystatic_editkommentar' );
#add_action ( 'unspammed_comment', 'reallystatic_editkommentar' );
 


function rs_transition_comment_status($n,$o,$c){

 
	
	if(($o=="unapproved" && $n=="approved") || ($o=="approved" && $n=="unapproved")||($o=="approved" && $n=="spam")||($o=="spam" && $n=="approved")
		||  ($o=="approved" && $n=="trash")||($o=="trash" && $n=="approved")){
		global $post;
		
		if(!$post)$post=get_post($c->comment_post_ID);
		reallystatic_editkommentar($c->comment_post_ID);
}
	
	RS_LOG("$o ======>>>> $n");
}
function commentseite($gesamt,$page,$proseite){
	
	 if(get_option('default_comments_page')=="newest")$o= (floor(($gesamt-$page)/$proseite))+1;
	 else $o= floor(($gesamt-$page)/$proseite)+1;
	 
	#RS_LOG(" commentseite($gesamt,$page,$proseite) = $o");
	return $o;
}


/**
 * 
 * @desc Wird nach einer Komentaredition aufgerufen
 * @param int $id ID des Kommentars
 * @return void
 */
function reallystatic_editkommentar($id,$zwangsgrill=false) {
	global $rs_commentpostionsinfo;
 
	 
	if($zwangsgrill==false){
	#RS_LOG ( "reallystatic_editkommentar" );
	$comment = get_comment ( $id );
	
	#RS_LOGA ( $rs_commentpostionsinfo );
	#RS_LOGA ( rs_commentpageinfo ( $comment->comment_post_ID, $comment->comment_ID ) );
	
	$vorher = $rs_commentpostionsinfo;
	$nachher = rs_commentpageinfo ( $comment->comment_post_ID, $comment->comment_ID );
	#RS_LOGA($vorher);
#	RS_LOGA($nachher);
	
	if ($vorher [gesamt] < $nachher [gesamt]) {
	#	RS_LOG ( "commentar add" .get_option('default_comments_page'));
		if(get_option('default_comments_page')=="newest") $bis= commentseite ( $nachher [gesamt],1, get_option ( "comments_per_page" ));
				else  $bis= commentseite ( $nachher [gesamt], 1, get_option ( "comments_per_page" ));
				$von=commentseite ( $nachher [gesamt], $nachher [page], get_option ( "comments_per_page" ) );
					if(get_option('default_comments_page')=="newest"){
						if (ceil ( $vorher [gesamt] / get_option ( "comments_per_page" ) ) != ceil ( $nachher [gesamt] / get_option ( "comments_per_page" ) )) {
							$von=1;
						}
						
					}
				
				
		for($i = $von; $i <= $bis; $i ++) {
			 #RS_LOG("###########$i");
				$arbeiter [$i] = "r";
		}
	 
		
	} else if ($vorher [gesamt] == $nachher [gesamt]) {
		RS_LOG ( "commentar edit" );
		if ($vorher [page] == $nachher [page]) {
		#	RS_LOG ( "text edit" );
			$arbeiter [commentseite ( $nachher [gesamt], $nachher [page], get_option ( "comments_per_page" ) )] = "r";
		} else {
		#	RS_LOG ( "datums edit" );
			$arbeiter [commentseite ( $vorher [gesamt], $vorher [page], get_option ( "comments_per_page" ) )] = "r";
			$arbeiter [commentseite ( $nachher [gesamt], $nachher [page], get_option ( "comments_per_page" ) )] = "r";
		}
	} else {
	#	RS_LOG ( "commentar grill" .get_option('default_comments_page'));
		
		if (ceil ( $vorher [gesamt] / get_option ( "comments_per_page" ) ) != ceil ( $nachher [gesamt] / get_option ( "comments_per_page" ) )) {
			
			if(get_option('default_comments_page')=="newest"){
				$arbeiter [commentseite ( $vorher [gesamt],1, get_option ( "comments_per_page" ) )] = "d";
			}
			else $arbeiter [commentseite ( $vorher [gesamt], 1, get_option ( "comments_per_page" ) )] = "d";
		}
		$von =commentseite ( $nachher [gesamt], $nachher [page], get_option ( "comments_per_page" ) );
		$bis=commentseite ( $nachher [gesamt], $nachher [gesamt], get_option ( "comments_per_page" ) );
		if(get_option('default_comments_page')=="newest"){
			
			if (ceil ( $vorher [gesamt] / get_option ( "comments_per_page" ) ) != ceil ( $nachher [gesamt] / get_option ( "comments_per_page" ) )) {
				$bis=commentseite ( $nachher [gesamt], 1, get_option ( "comments_per_page" ) );
			}
			
		}
		for($i = $von; $i <= $bis; $i ++) {
		#	RS_LOG("###$von => $bis = $i");
			if (! $arbeiter [$i])
				$arbeiter [$i] = "r";
		}
		
		//RS_LOG ( "<<<<<<<<<<<<<<<<<<<<<<" . commentseite ( $nachher [gesamt], 1, get_option ( "comments_per_page" ) ) );
	///	RS_LOG ( ">>>>>>>>>>>>>>>>>>>>>>>" . commentseite ( $nachher [gesamt], $nachher [gesamt], get_option ( "comments_per_page" ) ) );
	}
//	RS_LOG("GSDFSDF:".get_option("default_comments_page")."   ".ceil($nachher [gesamt]/ get_option ( "comments_per_page" )));
	#RS_LOGA ( $arbeiter );

		}else{
		//RS_LOG("###".rs_commentpageinfo ( $comment->comment_post_ID, $comment->comment_ID ));
		$oo=rs_commentpageinfo ( $comment->comment_post_ID, $comment->comment_ID );
	for($i = 1; $i <= ceil($oo[gesamt]/ get_option ( "comments_per_page" )); $i ++) {
			#	RS_LOG("###$von => $bis = $i");
			 
				$arbeiter [$i] = "d";
		}
	}
	
	
	if( get_option("default_comments_page")=="oldest" && $arbeiter [1]){
		$arbeiter  ["seiteselber"] = "1";
		unset($arbeiter [1]);
	}else	if(get_option('default_comments_page')=="newest" && ($arbeiter [ceil($nachher [gesamt]/ get_option ( "comments_per_page" ) )]or $nachher [gesamt]==0)){
		$arbeiter  ["seiteselber"] = "1";
		if($nachher [gesamt]==0)unset($arbeiter [1]);
			else unset($arbeiter [ceil($nachher [gesamt]/ get_option ( "comments_per_page" ) )]);
	}
	if($zwangsgrill==true)unset(	$arbeiter  ["seiteselber"]);
	//
	#RS_LOGA ( $arbeiter );

	renewrealstaic ( $comment->comment_post_ID, 123, "komentaredit", $arbeiter );
	return;
	
 
}


/**
 * komentar wird gepostet und benutzer wird weiter geleitet
 * @param int $id Kommmentarid
 * @param int $status ==0 wenn in spam; ==1 wenn approved  
 * @return void
 */
function reallystatic_newkommentar($id, $status = 0) {
	if($status==0)return;
	RS_LOG("reallystatic_newkommentar");
	reallystatic_editkommentar($id);
	return;
}

############
#
#
# COMMENTAR END
#
#
###############
/**
 * @param url: url ebend
 * @param typ: =1 hin =2 zurück
 * @param von: was soll erstezt werden
 * @param nach: womit wird es ersetzt
 */
function reallystatic_rewrite($url, $typ, $von = "", $nach = "") {
	$url = apply_filters ( "rs-pre-urlrewriter", $url, $typ, $von, $nach );
	if ($typ == 1) {
		if ($nach != "")
			$url = str_replace ( $von, $nach, $url );
		$url = really_wp_url_make_to_static ( $url );
	} elseif ($typ == 2) {
	}
	$url = apply_filters ( "rs-post-urlrewriter", $url, $typ, $von, $nach );
	return $url;
}

/**
 * @desc resetting internal filecache database
 * @param none
 * @return none
 */
function really_static_resetdatabase(){
	do_action ( "rs-database-reset-start");
	global $wpdb;
	$table_name = REALLYSTATICDATABASE;
	$wpdb->query ( "  Delete FROM $table_name" );
	do_action ( "rs-database-reset-done");
}
/**
 * @desc rebuild the entire Blog
 * @param boolean silent
 * @return none
 */
function really_static_rebuildentireblog($silent =false) {
	global $internalrun, $test, $showokinit, $arbeitsliste;
	$internalrun = true;
	global $wpdb;
	RS_LOG ( "everyday" );
	$a = loaddaten ( 'realstaticeveryday' );
	if(!$silent) reallystatic_configok ( "->Everyday", 2 );
	if (is_array ( $a )) {
		foreach ( $a as $v ) {
			$arbeitsliste [update] [urlcorrect ( $v [0] )] = loaddaten ( "localurl" ) . $v [0];
			RS_LOG ( $arbeitsliste [update] [urlcorrect ( $v [0] )] );
		}
	}
	RS_LOG ( "posteditcreatedelete" );
	$a = loaddaten ( 'realstaticposteditcreatedelete' );
	if(!$silent) reallystatic_configok ( "->posteditcreatedelete:", 2 );
	if (is_array ( $a )) {
		foreach ( $a as $v ) {
			$v [0] = str_replace ( "%postname%", str_replace ( array (loaddaten ( "localurl" ), loaddaten ( "remoteurl" ) ), array ("", "" ), get_permalink ( $id ) ), $v [0] );
			#	getnpush ( loaddaten ( "localurl" ) . $v [0], $v [0], true );
			if ($v [0] == "" or substr ( $v [0], - 1 ) == "/")
				$v [0] .= "index.html";
			$arbeitsliste [update] [urlcorrect ( $v [0] )] = loaddaten ( "localurl" ) . $v [0];
			RS_LOG ( $arbeitsliste [update] [urlcorrect ( $v [0] )] );
		}
	}
	RS_LOG ( "postssss" );
	$table_name = REALLYSTATICDATABASE;
	if(!$silent) reallystatic_configok ( "->main", 2 );
	$lastposts = get_posts ( 'numberposts=9999 ' );
	foreach ( $lastposts as $post ) {
		$querystr = "SELECT datum  FROM 	$table_name where url='" . md5 ( get_page_link ( $post->ID ) ) . "'";
		$ss = $wpdb->get_results ( $querystr, OBJECT );
		if ($ss [0]->datum > 0) {
			if(!$silent) reallystatic_configok(__ ( "Skiping existing:", 'reallystatic' ) . " " . get_page_link ( $post->ID ) );
		} else {
			
			$allowedtypes = array ('comment' => '', 'pingback' => 'pingback', 'trackback' => 'trackback' );
			$comtypewhere = ('all' != $args ['type'] && isset ( $allowedtypes [$args ['type']] )) ? " AND comment_type = '" . $allowedtypes [$args ['type']] . "'" : '';
			$allcoms = ($wpdb->get_var ( $wpdb->prepare ( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_parent = 0 AND comment_approved = '1' " . $comtypewhere, $post->ID ) ) - 1) / get_option ( "comments_per_page" );
			$arbeiter = Array ();
			
			if ((ceil ( $allcoms ) == $allcoms)) {
				for($i = 1; $i <= $allcoms; $i ++)
					$arbeiter [($i)] = "r";
			}
			
			renewrealstaic ( $post->ID, true, "", $arbeiter );
		
		#RS_LOG( "renewrealstaic ( ".$post->ID.", true );");
		}
	
	}
	
	//Statische seitem
	RS_LOG ( "statische seitem" );
	$lastposts = get_pages ( 'numberposts=999' );
	foreach ( $lastposts as $post ) {
		
		foreach ( loaddaten ( 'realstaticposteditcreatedelete' ) as $v ) {
			
			$t = str_replace ( "%postname%", cleanupurl ( get_page_link ( $post->ID ) ), $v [0] );
			
			$querystr = "SELECT datum  FROM 	$table_name where url='" . md5 ( $t ) . "'";
			$ss = $wpdb->get_results ( $querystr, OBJECT );
			if ($ss [0]->datum > 0) {
				if(!$silent)  reallystatic_configok( __ ( "Skiping existing:", 'reallystatic' ) . " " . $t  );
			} else {
				
				$allowedtypes = array ('comment' => '', 'pingback' => 'pingback', 'trackback' => 'trackback' );
				$comtypewhere = ('all' != $args ['type'] && isset ( $allowedtypes [$args ['type']] )) ? " AND comment_type = '" . $allowedtypes [$args ['type']] . "'" : '';
				$allcoms = ($wpdb->get_var ( $wpdb->prepare ( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_parent = 0 AND comment_approved = '1' " . $comtypewhere, $post->ID ) ) - 1) / get_option ( "comments_per_page" );
				$arbeiter = Array ();
				
				if ((ceil ( $allcoms ) == $allcoms)) {
					for($i = 1; $i <= $allcoms; $i ++)
						$arbeiter [($i)] = "r";
				}
				renewrealstaic ( $post->ID, true, "", $arbeiter );
				RS_LOG ( "renewrealstaic ( " . $post->ID . ", true );" );
			}
		}
	}
	
	global $allrefresh;
	$allrefresh = true;
}

/**
 * 
 * @desc just for development
 * @param unknown_type $errno
 * @param unknown_type $errstr
 * @param unknown_type $errfile
 * @param unknown_type $errline
 */
function rsErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        RS_LOG("<b>My ERROR</b> [$errno] $errstr<br />\n");
        RS_LOG( "  Fatal error on line $errline in file $errfile");
        RS_LOG( ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n");
        RS_LOG( "Aborting...<br />\n");
        exit(1);
        break;

    case E_USER_WARNING:
        RS_LOG( "<b>My WARNING</b> [$errno] $errstr<br />\n");
        break;

    case E_USER_NOTICE:
        RS_LOG( "<b>My NOTICE</b> [$errno] $errstr<br />\n");
        break;

    default:
        RS_LOG( "Unknown error type: [$errno] $errstr @ $errfile, $errline");
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}
//$old_error_handler = set_error_handler("rsErrorHandler");
/**
true wenn abfrage durch RS, geht auch nur im demomodus
*/
function really_static_selfdetect(){
##RS_LOG("HOMECHANGE".get_option ( 'home' ) ."!=". get_option ( 'siteurl' ));
#RS_LOG ("really_static_demodetect: ".really_static_demodetect());
#RS_LOG("rs_onwork:".get_option ( "rs_onwork"));
 if(!really_static_demodetect())return true;# kein demomode
if(	get_option ( "rs_onwork")==1)return true;
return false;
/*
if($_SERVER ['SERVER_ADDR']!=$_SERVER ['REMOTE_ADDR'])return false;
return true;*/
} 

/*
demo wenn home = site dann true
*/
function really_static_demodetect(){
	if(get_option ( 'home' ) != get_option ( 'siteurl' ))return 0;
	return 1;
}


function gt($a,$b){
	if($a>$b)return $a;
	else return $b;
	
}
function lt($a,$b){
	 
	if($a<$b)return $a;
	else return $b;
}
function rs_dayupdate($e,$erstell,$operation,$homeurllaenge,$pageposts,$von=false,$bis=false,$art="update"){
	#RS_LOG("rs_dayupdate $von => $bis $art          $e,$erstell");
	
	global $wpdb;

	
	if($von===false){
		//Tag
		$oben = date ( "Y-m-d 23:59:59", ($e) );
		$unten = $erstell;
		if ($operation == "postcreate") {
			#tag =  seitenanzahl
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_status = 'publish' AND post_date>'" . date ( "Y-m-d 0:0:0", ($e) ) . "' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			if (($bis [0]->outa % $pageposts) > 0) {
				$von = 1;
				$bis = 1;
			} else {
				$bis = floor ( $bis [0]->outa / $pageposts );
				$von = 1;
			}
		} else {
			#tag = seite auf der der post erstellt wurde
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_type='post' AND post_status = 'publish' AND post_date>'$unten' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			$bis = 1 + floor ( $bis [0]->outa / $pageposts );
			$von = $bis;
		}
	}
		for($tag = $von; $tag <= $bis; $tag ++) {
			if ($tag > 1) {
				if (REALSTATICNONPERMANENT == true)
					$text = "&paged=$tag";
				else
					$text = "/page/$tag";
			} else
				$text = "";
			foreach ( loaddaten ( "makestatic-a5" ) as $value ) {
				$url = $value [1];
				if ($url == "")
					$url = $value [0];
				global $seitenlinktransport;
				$seitenlinktransport = $text;
				$templink = apply_filters ( "rs-todolist-add-datedaylink",$url, substr ( get_day_link ( date ( "Y", $e ), date ( "m", $e ), date ( "d", $e ) ), $homeurllaenge ) ,$e);
	
				#$templink = str_replace ( "%dateurl%", substr ( get_day_link ( date ( "Y", $e ), date ( "m", $e ), date ( "d", $e ) ), $homeurllaenge ), $url );
				if($art=="update"){
				$templink = apply_filters ( "rs-todolist-add", $templink );
				if ($templink !== false)
					rs_arbeitsliste_create_add(reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) .really_static_make_to_wp_url (  $templink ));
				}else{
					$templink = apply_filters ( "rs-todolist-delete", $templink );
					if ($templink !== false)
						rs_arbeitsliste_delete_add(reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) .really_static_make_to_wp_url (  $templink ));
						
				}
			}}
	 
}
function rs_monthupdate($e,$erstell,$operation,$homeurllaenge,$pageposts,$von=false,$bis=false,$art="update"){
	#RS_LOG("rs_monthupdate $von => $bis $art");
	global $wpdb;
 
		//Monat
	if($von===false){
		$t = date ( "t", $e );
		$oben = date ( "Y-m-$t 23:59:59", ($e) );
		if ($operation == "postcreate") {
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_status = 'publish' AND post_date>'" . date ( "Y-m-1 0:0:0", ($e) ) . "' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			if (($bis [0]->outa % $pageposts) > 0) {
				$von = 1;
				$bis = 1;
			} else {
				$bis = floor ( $bis [0]->outa / $pageposts );
				$von = 1;
			}
		} else {
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_type='post' AND post_status = 'publish' AND post_date>'$unten' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			$bis = 1 + floor ( $bis [0]->outa / $pageposts );
			$von = $bis;
		}}
		for($monat = $von; $monat <= $bis; $monat ++) {
			if ($monat > 1) {
				if (REALSTATICNONPERMANENT == true)
					$text = "&paged=$monat";
				else
					$text = "/page/$monat";
			} else
				$text = "";
			foreach ( loaddaten ( "makestatic-a5" ) as $value ) {
				$url = $value [1];
				if ($url == "")
					$url = $value [0];
				global $seitenlinktransport;
				$seitenlinktransport = $text;
	
				#					$templink = str_replace ( "%dateurl%", substr ( get_month_link ( date ( "Y", $e ), date ( "m", $e ) ), $homeurllaenge ), $url );
				$templink = apply_filters ( "rs-todolist-add-datemonthlink",$url, substr ( get_month_link ( date ( "Y", $e ), date ( "m", $e ) ), $homeurllaenge ) ,$e);
if($art=="update"){
				$templink = apply_filters ( "rs-todolist-add", $templink );
				
				if ($templink !== false)
					rs_arbeitsliste_create_add(reallystatic_rewrite ( $templink, 1 ),loaddaten ( "localurl" ) .  really_static_make_to_wp_url ( $templink ));
}else{
	$templink = apply_filters ( "rs-todolist-delete", $templink );
	
	if ($templink !== false)
		rs_arbeitsliste_delete_add(reallystatic_rewrite ( $templink, 1 ),loaddaten ( "localurl" ) .  really_static_make_to_wp_url ( $templink ));
	
}
	
			}}
		 
}

function rs_yearupdate($e,$erstell,$operation,$homeurllaenge,$pageposts,$von=false,$bis=false,$art="update"){
	global $wpdb;
	#RS_LOG("rs_yearupdate $von => $bis $art");
	
		//Jahr
	if($von===false){
		$oben = date ( "Y-12-31 23:59:59", ($e) );
		if ($operation == "postcreate") {
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_status = 'publish' AND post_date>'" . date ( "Y-1-1 0:0:0", ($e) ) . "' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			if (($bis [0]->outa % $pageposts) > 0) {
				$von = 1;
				$bis = 1;
					
			} else {
				$bis = floor ( $bis [0]->outa / $pageposts );
				$von = 1;
			}
	
		} else {
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_type='post' AND post_status = 'publish' AND post_date>'$unten' and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			$bis = 1 + floor ( $bis [0]->outa / $pageposts );
			$von = $bis;
		}}
	
		for($jahr = $von; $jahr <= $bis; $jahr ++) {
			if ($jahr > 1) {
				if (REALSTATICNONPERMANENT == true)
					$text = "&paged=$jahr";
				else
					$text = "/page/$jahr";
			} else
				$text = "";
			foreach ( loaddaten ( "makestatic-a5" ) as $value ) {
				$url = $value [1];
				if ($url == "")
					$url = $value [0];
				global $seitenlinktransport;
				$seitenlinktransport = $text;
	
				#$templink = str_replace ( "%dateurl%", substr ( get_year_link ( date ( "Y", $e ) ), $homeurllaenge ), $url );
				$templink = apply_filters ( "rs-todolist-add-dateyearlink",$url,  substr ( get_year_link ( date ( "Y", $e ) ), $homeurllaenge ),$e);
				#RS_LOG("DDD:".get_month_link ( date ( "Y", $e ), date ( "m", $e ))."  ".$templink);
				if($art=="update"){
				$templink = apply_filters ( "rs-todolist-add", $templink );
						if ($templink !== false)
							rs_arbeitsliste_create_add(reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ));
				}else{
					
					$templink = apply_filters ( "rs-todolist-delete", $templink );
					if ($templink !== false)
						rs_arbeitsliste_delete_add(reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ));
					
				}
	
			}
			}
			 
}
function rs_tagupdate($erstell,$pageposts, $tagoty,$operation,$homeurllaenge,$seite=false,$seitemax=false,$art="update",$post=false){

	#RS_LOG("rs_tagupdate $seite,$seitemax $art");
	if($seite===false){
		$a=get_tag_parentids(array(),$tagoty->term_id,$post);
		
		
	$seite = (ceil ( $a[page][$tagoty->term_id] / $pageposts ));
	#$seite = (ceil ( getinnewer ( $erstell, $pageposts, $tagoty->term_id, 'post_tag' ) / $pageposts ));
	// RS_LOG("TAG BEGIN: $seite $operation ");
	if ($operation != "postcreate") {
	
		if ($seite > 1) {
			if (REALSTATICNONPERMANENT == true)
				$seitee = "&paged=$seite";
			else
				$seitee = "/page/$seite";
		} else
			$seitee = "";
		
		
		$seitemax=$seite;
	 
	} else {

		$seitemax = (ceil (  $a[gesamt][$tagoty->term_id] / $pageposts ));
		if (($seitemax % $pageposts) == 0) {
			$seitemax = 1;
		} else {
			$seitemax = ceil ( $seitemax / $pageposts );
		}
	}
	}
		for($seiter = $seite; $seiter <= $seitemax; $seiter ++) {
			if ($seiter > 1) {
				if (REALSTATICNONPERMANENT == true)
					$seitee = "&paged=$seiter";
				else
					$seitee = "/page/$seiter";
			} else
				$seitee = "";
			foreach ( loaddaten ( "makestatic-a2" ) as $value ) {
				$url = $value [1];
				if ($url == "")
					$url = $value [0];
				global $seitenlinktransport;
				$seitenlinktransport = $seitee;
				$m = apply_filters ( "rs-todolist-add-taglink", $url,substr (  get_tag_link ( $tagoty ), $homeurllaenge ) );
				
				if($art=="update"){
				$url2 = apply_filters ( "rs-todolist-add", $m );
				if ($url2 !== false)
					rs_arbeitsliste_create_add ( reallystatic_rewrite ( $url2, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url2 ) );
					}else{
					$url2 = apply_filters ( "rs-todolist-delete", $m );
					rs_arbeitsliste_delete_add ( reallystatic_rewrite ( $url2, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url2 ) );
					}
					
					
			}
		}
	
}
function rs_arbeitsliste_delete_add($get,$push){
	global $arbeitsliste;
	$arbeitsliste [delete] [$get] = $push;
}

/**
 * returns array with sum of post inside cat and position of post inside category
 */
function get_category_parentids($a, $id, $post = false) {
	$p = get_posts ( array (
			'category' => $id 
	) );
	$a [gesamt] [$id] = count ( $p );
	$i = 1;
	foreach ( $p as $v ) {
		
		if ($v == $post)
			break;
		$i ++;
	}
	$a [page][$id] = $i;
	$parent = &get_category ( $id );
	if ($parent->parent != 0)
		$a = get_category_parentids ( $a, $parent->parent, $post );
	return $a;
}
function get_tag_parentids($a, $id, $post = false) {
	$p=query_posts( array('tag_id='.$id,'posts_per_page' => -1 ));
	 
	$a [gesamt] [$id] = count ( $p );
	$i = 1;
	foreach ( $p as $v ) {
 		if ($v->ID == $post->ID)
			break;
		$i ++;
	}
	
	
	if($i<=$a [gesamt] [$id] )$a [page][$id] = $i;
	$parent = &get_tag ( $id );
	if ($parent->parent != 0)
		$a = get_tag_parentids ( $a, $parent->parent, $post );
	return $a;
}



function sdfsdfs($author,$timestamp){
	global $wpdb;
	if($timestamp>0) $s=" and post_date>='". date ( "Y-m-d H:i:s", ($timestamp) )."'";
	$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_author='" . $author . "' AND post_status = 'publish' $s";
	#RS_LOG($querystr);
	$bis = $wpdb->get_results ( $querystr, OBJECT );
	return $bis [0]->outa ;
}


function rs_authorupdate($id, $erstell, $pageposts, $operation, $homeurllaenge, $authorid = false, $von = false, $bis = false, $art = "update") {
	#RS_LOG ( "rs_authorupdate  $von => $bis" );
	global $wpdb;
	if ($von === false) {
		$e = strtotime ( $erstell );
		$oben = date ( "Y-m-d 23:59:59", ($e) );
		$unten = $erstell;
		$querystr = "SELECT post_author as outo FROM " . $wpdb->prefix . "posts	WHERE ID='$id'";
		$authorid2 = $wpdb->get_results ( $querystr, OBJECT );
		$authorid = $authorid2 [0]->outo;
		if ($operation == "postcreate") {
			// ag = seitenanzahl
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_author='" . $authorid . "' AND post_status = 'publish'  and post_date<='$oben'";
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			
			if (($bis [0]->outa % $pageposts) > 0) {
				$von = 1;
				$bis = 1;
			} else {
				$bis = floor ( $bis [0]->outa / $pageposts );
				$von = 1;
			}
		} else {
			// ag = seite auf der der post erstellt wurde
			$querystr = "SELECT count(ID) as outa FROM " . $wpdb->prefix . "posts where post_author='" . $authorid . "' AND post_status = 'publish' AND post_date>'$unten' and post_type='post'  "; // nd
			                                                                                                                                                                                        // post_date<='$oben'
			$bis = $wpdb->get_results ( $querystr, OBJECT );
			$bis = 1 + floor ( $bis [0]->outa / $pageposts );
			$von = $bis;
		}
	}
	for($seite = $von; $seite <= $bis; $seite ++) {
		if ($seite > 1) {
			if (REALSTATICNONPERMANENT == true)
				$text = "&paged=$seite";
			else
				$text = "/page/$seite";
		} else
			$text = "";
		foreach ( loaddaten ( "makestatic-a4" ) as $value ) {
			$url = $value [1];
			if ($url == "")
				$url = $value [0];
			global $seitenlinktransport;
			$seitenlinktransport = $text;
			
			$templink = apply_filters ( "rs-todolist-add-authorlink", $url, substr ( get_author_posts_url ( $authorid, '' ), $homeurllaenge ) );
			
			// templink = str_replace ( "%authorurl%", substr (
			// get_author_posts_url ( $authorid [0]->outo, '' ), $homeurllaenge
			// ), $url );
			
			// S_LOG("AA:".get_author_posts_url ( $authorid [0]->outo, '' )."
			// ".$templink);
			if ($art == "update") {
				$url = apply_filters ( "rs-todolist-add", $templink );
				if ($url !== false)
					rs_arbeitsliste_create_add ( reallystatic_rewrite ( $url, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url ) );
			} else {
				$url = apply_filters ( "rs-todolist-delete", $templink );
				if ($url !== false)
					rs_arbeitsliste_delete_add ( reallystatic_rewrite ( $url, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url ) );
			}
		}
	}
}










function categorry_refresh($posteditdiff, $homeurllaenge, $pageposts, $operation) {

	if (loaddaten ( "makecatstatic" ) == 1 and is_array ( loaddaten ( "makestatic-a3" ) )) {
			#RS_LOG("categorry_refresh");
		
		if ($operation == "postcreate") {
			#RS_LOG ( "erstell aus cat $k" );
			foreach ( $posteditdiff [cat_post] [page] as $k => $v ) {
				#RS_LOG ( "UPDATE SEITE " . ceil ( $v / $pageposts ) . " bis " . ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ) . " @ $k" );
				really_static_categoryrefresh ( $k, $homeurllaenge, ceil ( $v / $pageposts ), ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ), "update" );
			}
		} elseif ($posteditdiff [date] [0] != "" && $posteditdiff [date] [1] != "") {
			#RS_LOG ( "innerhalb von cat verschoben" );
			foreach ( $posteditdiff [cat_pre] [page] as $k => $v ) {
				// S_LOG ( "verschiebe aus cat $v nach ". $posteditdiff
				// [cat_post][page][$k] );
				#RS_LOG ( "UPDATE SEITE " . ceil ( $posteditdiff [cat_pre] [page] [$k] / $pageposts ) . " UND " . ceil ( $posteditdiff [cat_post] [page] [$k] / $pageposts ) . " @ $k" );
				really_static_categoryrefresh ( $k, $homeurllaenge, ceil ( $posteditdiff [cat_pre] [page] [$k] / $pageposts ), ceil ( $posteditdiff [cat_pre] [page] [$k] / $pageposts ), "update" );
				really_static_categoryrefresh ( $k, $homeurllaenge, ceil ( $posteditdiff [cat_post] [page] [$k] / $pageposts ), ceil ( $posteditdiff [cat_post] [page] [$k] / $pageposts ), "update" );
			}
		} else {
			foreach ( $posteditdiff [cat_post][gesamt] as $k => $v ) {
				if ($posteditdiff [cat_pre] [gesamt] [$k]) {
				#	RS_LOG ( "Lösche aus cat $k" );
					
					if (ceil ( $posteditdiff [cat_pre] [gesamt] [$k] / $pageposts ) != ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts )) {
						#RS_LOG ( "DELETE SEITE " . ceil ( $posteditdiff [cat_pre] [gesamt] [$k] / $pageposts ) . " @ $k" );
						really_static_categoryrefresh ( $k, $homeurllaenge, ceil ( $posteditdiff [cat_pre] [gesamt] [$k] / $pageposts ), ceil ( $posteditdiff [cat_pre] [gesamt] [$k] / $pageposts ), "delete" );
					}
					if (ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ) > 0){
						really_static_categoryrefresh ( $k, $homeurllaenge,ceil ( $posteditdiff [cat_pre] [page] [$k] / $pageposts ), ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ) , "update" );
						#RS_LOG ( "UPDATE SEITE " . ceil ( $posteditdiff [cat_pre] [page] [$k] / $pageposts ) . " bis " . ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ) . " @ $k" );
						
					}
				} else {
					#RS_LOG ( "erstell aus cat $k" );
					 
					#RS_LOG ( "UPDATE SEITE " . ceil ( $posteditdiff [cat_post] [page] [$k] / $pageposts ) . " bis " . ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ) . " @ $k" );
					really_static_categoryrefresh ( $k, $homeurllaenge, ceil ( $posteditdiff [cat_post] [page] [$k] / $pageposts ), ceil ( $posteditdiff [cat_post] [gesamt] [$k] / $pageposts ), "update" );
				}
			}
		}
	}
}

function author_refresh($id,$posteditdiff,$erstell, $pageposts, $operation, $homeurllaenge,$authorid){
	global $wpdb;
	if (loaddaten ( "makeauthorstatic" ) == 1 and is_array ( loaddaten ( "makestatic-a4" ) )) {
		$querystr = "SELECT post_author as outo FROM " . $wpdb->prefix . "posts	WHERE ID='$id'";
		$authorid2 = $wpdb->get_results ( $querystr, OBJECT );
		$authorid= $authorid2 [0]->outo ;
	
	if($posteditdiff[author_pre][gesamt]> $posteditdiff[author_post][gesamt]){
		#gelöscht
		if(floor($posteditdiff[author_pre][gesamt]/$pageposts)!= floor($posteditdiff[author_pre][gesamt]-1/$pageposts)){
			#beim alten ist es nun euine sache weniger
			rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, ceil($posteditdiff[author_pre][gesamt]/$pageposts),ceil($posteditdiff[author_pre][gesamt]/$pageposts),  "delete");
			rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, ceil($posteditdiff[author_pre][page]/$pageposts),ceil($posteditdiff[author_pre][gesamt]/$pageposts)-1,  "update");
		}else{
			rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, ceil($posteditdiff[author_pre][page]/$pageposts),ceil($posteditdiff[author_pre][gesamt]/$pageposts),  "update");
		}
	}else	if($posteditdiff[author_pre][gesamt]< $posteditdiff[author_post][gesamt]){
		#erstell
		rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, ceil($posteditdiff[author_post][page]/$pageposts),ceil($posteditdiff[author_post][gesamt]/$pageposts),  "update");
	}elseif($posteditdiff[author_pre][art]==$posteditdiff[author_post][art] ){
			#autorgleich
		 if($posteditdiff[author_pre][page] != $posteditdiff[author_post][page]){
				#verschoben
				RS_LOG("author verschoben");
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_pre][page]/$pageposts),floor($posteditdiff[author_pre][page]/$pageposts),  "update");
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_post][page]/$pageposts),floor($posteditdiff[author_post][page]/$pageposts),  "update");
	
			}else{
				#tue nix
				RS_LOG("author tue nix, nur seite selber");
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_post][page]/$pageposts),floor($posteditdiff[author_post][page]/$pageposts),  "update");
			}
		}else{
			#autor geandert, refresh bei neuem author
			RS_LOG("author geandert");
			rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_post][page]/$pageposts),floor($posteditdiff[author_post][gesamt]/$pageposts),  "update");
			if(floor($posteditdiff[author_pre][gesamt]/$pageposts)!= floor($posteditdiff[author_pre][gesamt]-1/$pageposts)){
				#beim alten ist es nun euine sache weniger
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_pre][gesamt]/$pageposts),floor($posteditdiff[author_pre][gesamt]/$pageposts),  "delete");
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_pre][page]/$pageposts),floor($posteditdiff[author_pre][gesamt]/$pageposts)-1,  "update");
			}else{
				rs_authorupdate($id,$erstell, $pageposts, $operation, $homeurllaenge,$authorid, floor($posteditdiff[author_pre][page]/$pageposts),floor($posteditdiff[author_pre][gesamt]/$pageposts),  "update");
			}
		}
	
			
		#rs_authorupdate($erstell, $pageposts, $operation, $homeurllaenge, $von = false, $bis = false, $art = "update");
	}
	
}

function date_refresh($posteditdiff, $erstell, $operation, $homeurllaenge, $pageposts) {
#	RS_LOGA($posteditdiff);
	// date
	if (loaddaten ( "makedatestatic" ) == 1 and is_array ( loaddaten ( "makestatic-a5" ) )) {
		#RS_LOG("date_refresh");
		if (loaddaten ( "makedatetagstatic" ) == 1) {
			if ($operation == "postcreate") {
			#	RS_LOG ( "ERSTELL" );
				$a = ceil ( $posteditdiff [post_page] [date_day] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_day_gesamt] / $pageposts );
				
				rs_dayupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} elseif ($posteditdiff [date] [0] == "" && $operation != "postdelete") {
			#	RS_LOG ( "DATUM BLIEB GLEICH" );
				$a = ceil ( $posteditdiff [post_page] [date_day] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_day_gesamt] / $pageposts );
				rs_dayupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} else {
				if (substr ( $posteditdiff [date] [0], 0, 10 ) == substr ( $posteditdiff [date] [1], 0, 10 )) {
					#RS_LOG ( "EDIT TAG BLIEB GLEICH, uhrzeit anders" );
					$a = floor ( lt ( $posteditdiff [pre_page] [date_day], $posteditdiff [post_page] [date_day] ) / $pageposts );
					$b = floor ( gt ( $posteditdiff [pre_page] [date_day], $posteditdiff [post_page] [date_day] ) / $pageposts );
					rs_dayupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
				} else {
				#	RS_LOG ( "EDIT TAG geändert" );
					$oo = $posteditdiff [pre_page] [date_day_gesamt] / $pageposts;
					rs_dayupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [post_page] [date_day] / $pageposts ), ceil ( $posteditdiff [post_page] [date_day_gesamt] / $pageposts ), "update" );
					if (ceil ( $oo ) != ($oo)) {
						if ($operation == "postdelete")
							rs_dayupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						else
							rs_dayupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						$oo --;
					}
					if ($operation == "postdelete")
						rs_dayupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_day] / $pageposts ), ceil ( $oo ), "update" );
					else
						rs_dayupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_day] / $pageposts ), ceil ( $oo ), "update" );
				}
			}
		}
		// ##########
		if (loaddaten ( "makedatemonatstatic" ) == 1) {
			if ($operation == "postcreate") {
				#RS_LOG ( "Monat ERSTELL" );
				$a = ceil ( $posteditdiff [post_page] [date_month] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_month_gesamt] / $pageposts );
				rs_monthupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} elseif ($posteditdiff [date] [0] == "" && $operation != "postdelete") {
			#	RS_LOG ( "Monat DATUM BLIEB GLEICH" );
				$a = ceil ( $posteditdiff [post_page] [date_month] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_month_gesamt] / $pageposts );
				rs_monthupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} else {
				if (substr ( $posteditdiff [date] [0], 0, 10 ) == substr ( $posteditdiff [date] [1], 0, 10 )) {
					
					$a = floor ( lt ( $posteditdiff [pre_page] [date_month], $posteditdiff [post_page] [date_month] ) / $pageposts );
					$b = floor ( gt ( $posteditdiff [pre_page] [date_month], $posteditdiff [post_page] [date_month] ) / $pageposts );
					#RS_LOG ( "Monat EDIT TAG BLIEB GLEICH, uhrzeit anders $a $b" );
					rs_monthupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
				} else {
				#	RS_LOG ( "Monat EDIT TAG geändert" );
					// neues up
					rs_monthupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [post_page] [date_month] / $pageposts ), ceil ( $posteditdiff [post_page] [date_month_gesamt] / $pageposts ), "update" );
					// vorheriges up
					$oo = $posteditdiff [pre_page] [date_month_gesamt] / $pageposts;
					if (floor ( $oo ) != ($oo)) {
						if ($operation == "postdelete")
							rs_monthupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						else
							rs_monthupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						$oo --;
					}
					if ($oo >= 0) {
						if ($operation == "postdelete")
							rs_monthupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_month] / $pageposts ), ceil ( $oo ), "update" );
						else
							rs_monthupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_month] / $pageposts ), ceil ( $oo ), "update" );
					}
				}
			}
		}
			// ################
		if (loaddaten ( "makedatejahrstatic" ) == 1) {
			if ($operation == "postcreate") {
			#	RS_LOG ( "ERSTELL" );
				$a = ceil ( $posteditdiff [post_page] [date_year] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_year_gesamt] / $pageposts );
				rs_yearupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} elseif ($posteditdiff [date] [0] == "" && $operation != "postdelete") {
			#	RS_LOG ( "DATUM BLIEB GLEICH" );
				$a = ceil ( $posteditdiff [post_page] [date_year] / $pageposts );
				$b = ceil ( $posteditdiff [post_page] [date_year_gesamt] / $pageposts );
				rs_yearupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
			} else {
				if (substr ( $posteditdiff [date] [0], 0, 10 ) == substr ( $posteditdiff [date] [1], 0, 10 )) {
				#	RS_LOG ( "EDIT TAG BLIEB GLEICH, uhrzeit anders" );
					$a = floor ( lt ( $posteditdiff [pre_page] [date_year], $posteditdiff [post_page] [date_year] ) / $pageposts );
					$b = floor ( gt ( $posteditdiff [pre_page] [date_year], $posteditdiff [post_page] [date_year] ) / $pageposts );
					rs_yearupdate ( strtotime ( $erstell ), $erstell, $operation, $homeurllaenge, $pageposts, $a, $b, "update" );
				} else {
				#	RS_LOG ( "EDIT TAG geändert" );
					$oo = $posteditdiff [pre_page] [date_year_gesamt] / $pageposts;
					rs_yearupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [post_page] [date_year] / $pageposts ), ceil ( $posteditdiff [post_page] [date_year_gesamt] / $pageposts ), "update" );
					if (floor ( $oo ) != ($oo)) {
						if ($operation == "postdelete")
							rs_yearupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						else
							rs_yearupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $oo ), ceil ( $oo ), "delete" );
						$oo --;
					}
					if ($operation == "postdelete")
						rs_yearupdate ( strtotime ( $posteditdiff [date] [1] ), $posteditdiff [date] [1], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_year] / $pageposts ), ceil ( $oo ), "update" );
					else
						rs_yearupdate ( strtotime ( $posteditdiff [date] [0] ), $posteditdiff [date] [0], $operation, $homeurllaenge, $pageposts, ceil ( $posteditdiff [pre_page] [date_year] / $pageposts ), ceil ( $oo ), "update" );
				}
			}
		}
		// ###########
	}
}


function tag_refresh($posteditdiff, $erstell, $pageposts, $k, $operation, $homeurllaenge, $pageposts) {
	
	
	// Tags
	if (loaddaten ( "maketagstatic" ) == 1 and is_array ( loaddaten ( "makestatic-a2" ) )) {
	#	RS_LOG("tag_refresh");
		 

		
		foreach (  $posteditdiff [tag_post] [gesamt] as $v => $vvvvv) {
			
			$s = $posteditdiff [tag_pre] [page] [$v]; // grill
			if ($s == "")
				$s = $posteditdiff[tag_post] [page] [$v]; // hinzu
			
			$ges = $posteditdiff [tag_pre] [gesamt] [$v];
			if ($ges == "")
				$ges = $posteditdiff[tag_post] [gesamt] [$v];
			
			if ($posteditdiff[tag_pre] [page] [$v]&& $posteditdiff [tag_post] [page] [$v]) {
			#	 RS_LOG("tag blieb");
				 #refresh nur die seite
				 rs_tagupdate ( $erstell, $pageposts, $v, $operation, $homeurllaenge, ceil ( $s / $pageposts ), ceil ( $s / $pageposts ), "update" );
				 	
			} elseif ($posteditdiff [tag_pre] [page] [$v]) {
			#	RS_LOG("tag wurde entfernt $ges $pageposts");
				if (ceil ( $ges / $pageposts ) != ceil ( ($ges - 1) / $pageposts )) {
			#		RS_LOG("letzte seite weggefallen".$s." ".$ges." ".$pageposts);
					rs_tagupdate ( $erstell, $pageposts, $v, $operation, $homeurllaenge, ceil ( $ges / $pageposts ), ceil ( $ges / $pageposts ), "delete" );
				}
				rs_tagupdate ( $erstell, $pageposts, $v, $operation, $homeurllaenge, ceil ( $posteditdiff [tag_pre] [page] [$v] / $pageposts ), ceil (  ($posteditdiff [tag_pre] [gesamt] [$v]-1) / $pageposts ), "update" );
			} else {
			#	RS_LOG("tag wurde hinzugefuegt @ $v $s $ges $pageposts ");
				// ".ceil($s/$pageposts)."#".ceil($ges/$pageposts));
				rs_tagupdate ( $erstell, $pageposts, $v, $operation, $homeurllaenge, ceil ( $s / $pageposts ), ceil ( $posteditdiff[tag_post] [gesamt] [$v] / $pageposts ), "update" );
			}
		}
	/*	if (count ( $posteditdiff [tag] ) == 0) {
			// efresh nur die seite
			RS_LOG ( "tag nur refresh" );
			if($operation=="postdelete") $aaa="delete";
				else $aaa="update";
			foreach ( $posteditdiff [tag_post] [page] as $k => $v ) 
				
				
			  rs_tagupdate ( $erstell, $pageposts, $k, $operation, $homeurllaenge, ceil ( $v / $pageposts ), ceil ( $v / $pageposts ), "update" );
		}*/
		
		
		
	}
}

function index_refresh($erstell, $pageposts, $homeurllaenge, $posteditdiff) {
	if (loaddaten ( "makeindexstatic" ) == 1 and is_array ( loaddaten ( "makestatic-a1" ) )) {
		#RS_LOGA($posteditdiff);
		if ($posteditdiff [pre_page] [postgesamt] == $posteditdiff [post_page] [postgesamt]) {
		#	RS_LOG("index edit");
			if ($posteditdiff [pre_page] [post] == $posteditdiff [post_page] [post]) {
			#	RS_LOG("index nix passiert");
				index_update($erstell,$pageposts,$homeurllaenge,floor($posteditdiff [pre_page] [post]/$pageposts),floor($posteditdiff [pre_page] [post]/$pageposts),"update");
				
			} elseif ($posteditdiff [pre_page] [post] != $posteditdiff [post_page] [post]) {
				#RS_LOG("index verschoben");
				index_update($erstell,$pageposts,$homeurllaenge,floor($posteditdiff [pre_page] [post]/$pageposts),floor($posteditdiff [pre_page] [post]/$pageposts),"update");
				index_update($erstell,$pageposts,$homeurllaenge,floor($posteditdiff [post_page] [post]/$pageposts),floor($posteditdiff [post_page] [post]/$pageposts),"update");
				
				
			}
		} elseif ($posteditdiff [pre_page] [postgesamt] > $posteditdiff [post_page] [postgesamt]) {
		 
			
			
			
			$bis=ceil($posteditdiff [pre_page] [postgesamt]/$pageposts);
			$bis2=ceil($posteditdiff [post_page] [postgesamt]/$pageposts);
		#	RS_LOG("index gelöscht $bis > $bis2");
			
			
		if($bis>$bis2){
			#letze seite weg
			index_update($erstell,$pageposts,$homeurllaenge,$bis,$bis,"delete");
			$bis--;
		}
		index_update($erstell,$pageposts,$homeurllaenge,floor($posteditdiff [post_page] [post]/$pageposts),$bis,"update");
		
			
			
		} elseif ($posteditdiff [pre_page] [postgesamt] < $posteditdiff [post_page] [postgesamt]) {
	#	RS_LOG("index add");
			index_update($erstell,$pageposts,$homeurllaenge,ceil($posteditdiff [post_page] [post]/$pageposts),ceil($posteditdiff [post_page] [postgesamt]/$pageposts),"update");
				
		}
	}
}

function index_update($erstell,$pageposts,$homeurllaenge,$von=false,$bis=false,$art="update") {
	global $wpdb;
	#RS_LOG("index_update $von $bis $art");
		if($von===false){
		if ($operation == "postcreate") {
			$querystr = "SELECT count(ID) as outo FROM " . $wpdb->prefix . "posts	WHERE post_type='post' and post_status = 'publish' ";
			$normaleseiten = $wpdb->get_results ( $querystr, OBJECT );
			$bis = ceil ( $normaleseiten [0]->outo / $pageposts );
			$von = 1;
		} else {
			$querystr = "SELECT count(ID) as outo FROM " . $wpdb->prefix . "posts	WHERE post_type='post' and post_status = 'publish' AND post_date>'$erstell'";
			$normaleseiten = $wpdb->get_results ( $querystr, OBJECT );
			$von = $bis = ceil ( $normaleseiten [0]->outo / $pageposts );
		}
		}
		for($normaleseiten = $von; $normaleseiten <= $bis; $normaleseiten ++) {
			
			if ($normaleseiten > 1) {
				if (REALSTATICNONPERMANENT == true)
					$text = "?paged=$normaleseiten";
				else
					$text = "page/$normaleseiten";
			} else
				$text = "";
			foreach ( loaddaten ( "makestatic-a1" ) as $value ) {
				$url = $value [1];
				if ($url == "")
					$url = $value [0];
				global $seitenlinktransport;
				$seitenlinktransport = $text;
				
				if ($text == "")
					$normaleseiten2 = "index.html";
				else
					$normaleseiten2 = "";
				if (strpos ( $normaleseiten2, get_option ( 'home' ) . "/" ) === false)
					$normaleseiten2 = get_option ( 'home' ) . "/" . $normaleseiten2;
					
					// S_LOG($normaleseiten2 );
				$normaleseiten2 = (really_static_rewrite1 ( $normaleseiten2 ));
				// S_LOG($normaleseiten2 );
				// templink = get_option ( 'home' ) . "/" . str_replace (
				// "%indexurl%", substr ( $normaleseiten2, $homeurllaenge ),
				// $url );
				// templink = substr ( $templink, $homeurllaenge );
				$templink = apply_filters ( "rs-todolist-add-indexlink", $url, substr ( $normaleseiten2, $homeurllaenge ) );
				

				if($art=="update"){
				$templink = apply_filters ( "rs-todolist-add", $templink );
				if ($templink !== false)
					rs_arbeitsliste_create_add ( reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ) );
				}else{
					$templink = apply_filters ( "rs-todolist-delete", $templink );
					if ($templink !== false)
						rs_arbeitsliste_delete_add ( reallystatic_rewrite ( $templink, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $templink ) );
				}

			}
			
			// $arbeitsliste[update][nonpermanent(urlcorrect(str_replace (
		// array("%indexurl%","//"), array($normaleseiten,"/"), $url
		// )))]=loaddaten ( "localurl" ) . str_replace (
		// array("%indexurl%","//"), array($normaleseiten,"/"), $url );
		}
 
}
function comment_refresh($id,$homeurllaenge, $subarbeitsliste) {
	global $wpdb;
	if (get_option ( 'page_comments' )) {
		$allowedtypes = array (
				'comment' => '',
				'pingback' => 'pingback',
				'trackback' => 'trackback' 
		);
		$comtypewhere = ('all' != $args ['type'] && isset ( $allowedtypes [$args ['type']] )) ? " AND comment_type = '" . $allowedtypes [$args ['type']] . "'" : '';
		
		$seitenanzahl = ceil ( $wpdb->get_var ( $wpdb->prepare ( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_parent = 0 AND comment_approved = '1' " . $comtypewhere, $id ) ) ) / get_option ( "comments_per_page" );
		
		if (! is_array ( $subarbeitsliste )) {
			for($i = 1; $i <= ($seitenanzahl); $i ++) {
				if (! (($i == $seitenanzahl && 'newest' == get_option ( 'default_comments_page' )) or ($i == 1 && 'newest' != get_option ( 'default_comments_page' ))))
					$subarbeitsliste [$i] = "r";
			}
		}
		if (is_array ( $subarbeitsliste )) {
			foreach ( $subarbeitsliste as $i => $akt ) {
				
				$templink = substr ( get_comments_pagenum_link ( $i ), $homeurllaenge );
				if(strpos($templink,"#")!==false)$templink=substr($templink,0,strpos($templink,"#"));
				#RS_LOG("templink $templink");
				$url = apply_filters ( "rs-todolist-add", $templink );
				if ($url !== false) {
					foreach ( loaddaten ( "makestatic-a6" ) as $value ) {
						$url = str_replace ( '%commenturl%', $url, $value [0] );
						if ($akt == "r")
							rs_arbeitsliste_create_add (reallystatic_rewrite ( $url, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url ));
						elseif ($akt == "d")
							rs_arbeitsliste_delete_add (reallystatic_rewrite ( $url, 1 ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url ));
					}
				}
			}
		}
	}
}
function seiteselberrefresh($id, $operation, $homeurllaenge, $subarbeitsliste,$post_status) {
	global $wpdb;
	$a = loaddaten ( 'realstaticposteditcreatedelete' );
	if (is_array ( $a )) {
		
		$querystr = "SELECT post_content as outo FROM " . $wpdb->prefix . "posts	WHERE ID='$id'";
		$normaleseiten = $wpdb->get_results ( $querystr, OBJECT );
		$pagecontent = $normaleseiten [0]->outo;
		foreach ( $a as $v ) {
			if ($operation != "komentarerstellt" or ereg ( "%postname%", $v [0] )) {#Quickfix, das beim kommentar erstellen, nur wichtiges
				if (! isset ( $subarbeitsliste ["seiteselber"] ) or ereg ( "%postname%", $v [0] )) {
					// nset ( $sourcefile );
					$normaleseiten = apply_filters ( "rs-pagecount", 1 + substr_count ( $pagecontent, "<!--nextpage-->" ), $pagecontent, $qq );
					if (strpos ( $v [0], "%postname%" ) !== false) {
						$normaleseiten2 = $normaleseiten;
					} else
						$normaleseiten2 = 1;
						
					#RS_LOG("SEITE SELBER: for($seite = 1; $seite <= $normaleseiten2; $seite ++) {");
					for($seite = 1; $seite <= $normaleseiten2; $seite ++) {
						if ($seite > 1) {
							if (REALSTATICNONPERMANENT == true)
								$text = "&page=$seite";
							else
								$text = "/page/$seite";
						} else
							$text = "";
						global $seitenlinktransport;

						if (ereg ( "%postname%", $v [0] ))
							$qq = get_option ( 'home' ) . "/" . str_Replace ( "%postname%", substr ( get_permalink ( $id ), $homeurllaenge ), $v [0] );
						else
							$qq = get_option ( 'home' ) . "/" . $v [0];
						
						$templink = substr ( $qq, $homeurllaenge );
						

						if ($post_status == "trash") {
							$url = apply_filters ( "rs-todolist-delete", $templink );
							if ($url !== false)
								rs_arbeitsliste_delete_add ( reallystatic_rewrite ( $url, 1, $v [0], $v [1] ), 1 );
						} else {
							$url = apply_filters ( "rs-todolist-add", $templink );
							
							if ($url !== false)
								rs_arbeitsliste_create_add ( reallystatic_rewrite ( $url, 1, $v [0], $v [1] ), loaddaten ( "localurl" ) . really_static_make_to_wp_url ( $url ) );
						}
					}
				}
			}
		}
	}
}

function main_count_post(){
	global $wpdb;
	$querystr = "SELECT count(ID) as outo FROM " . $wpdb->prefix . "posts	WHERE post_type='post' and post_status = 'publish' ";
	$normaleseiten = $wpdb->get_results ( $querystr, OBJECT );
	return  $normaleseiten [0]->outo;
 
	
}
function main_count_post_until($timestamp){
	global $wpdb;
	$querystr = "SELECT count(ID) as outo FROM " . $wpdb->prefix . "posts	WHERE post_type='post' and post_status = 'publish' AND post_date>'$timestamp'";
	$normaleseiten = $wpdb->get_results ( $querystr, OBJECT );
	return $normaleseiten [0]->outo ;

}

/**
 * Cronjob: Taeglich
 *
 * @since 0.3
 * @param none
 * @return bool everytime true
 */
function reallystatic_cronjob() {
	$a = loaddaten ( 'realstaticeveryday' );
	if (is_array ( $a )) {
		foreach ( $a as $v ) {
			getnpush ( loaddaten ( "localurl" ) . $v [0], $v [0], 123 );
		}
	}
	return true;
}
add_action ( 'reallystatic_daylyevent', 'reallystatic_cronjob' );




########## NO docmentation, because outsourced
add_action('admin_init', 'rs_upgrade' );
function rs_upgrade()
{
	if(get_option ( 'rs_firstTime')!= RSVERSION . RSSUBVERSION){
		RS_LOG("rs_upgrade");
		require_once("sonstiges/wppluginintegration.php");
		rs_upgrade_real();
	}
}
register_activation_hook ( __FILE__, 'rs_activation' );
function rs_activation() {
	 
	
	RS_LOG("rs_activation");
	require_once("sonstiges/wppluginintegration.php");
	rs_activation_real();
}

register_deactivation_hook ( __FILE__, 'rs_deactivation' );
function rs_deactivation() {
	RS_LOG("rs_deactivation");
	require_once("sonstiges/wppluginintegration.php");
	rs_deactivation_real();
}



add_filter('get_sample_permalink_html','rs_showstaticlinkoneditor',10,2);
function rs_showstaticlinkoneditor($tt,$id){
	if(!really_static_demodetect())return $tt;
	$p=get_post($id);
	return $tt."<span id='view-post-btn'><a href='".really_wp_url_make_to_static($p->guid)."' class='button' target='_blank'>View static page</a></span>";
}
 


add_action( 'admin_notices' , 'rs_showinfohints' );
function rs_showinfohints() {
 
	$t = get_option ( 'rs_showokmessage' );
	
 
	
	if (count ( $t ) == 0)
		return;
	 
	foreach ( $t as $k => $v ) {
		
		if ($v [0] == 1)
			echo '<div class="updated"> <p>' . $v [1] . '</p>  </div>';
		else
			echo '<div class="error"> <p>' . $v [1] . '</p>  </div>';
		
		if ($v [2] == "" or $_GET [$v [2]] ==$v[3])
			unset ( $t [$k] );
	}
	update_option ( 'rs_showokmessage', $t );
}
/*
 * 1=info
 * 2=error
 * 3= fatal error => wp_die
 */
function rs_addmessage($shownow,$text,$art=1,$getname="",$getvalue=""){
	 
	if($art==3)wp_die("<h1>fatal error</h1>".$text.'<br><br><a href="javascript:history.back()">back to previous page</a>',"fatal error");
	if($shownow){
		if ($art == 1)
			echo '<div class="updated"> <p>' .$text . '</p>  </div>';
		else
			echo '<div class="error"> <p>' . $text . '</p>  </div>';
		return;
	}
	$m=get_option( 'rs_showokmessage');
	$m[]=array($art,$text,$getname,$getvalue);
	update_option( 'rs_showokmessage',$m);
}
 
?>