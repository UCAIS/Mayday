<?php

/**
 * Return true if path dosnt end with / or scheme or host are not detectet
 * @retrun: true if url is wrong
 */
function reallystatic_urltypeerror($url) {
	$p = parse_url ( $url );
	if ($p [scheme] != "" and $p [host] != "" and substr ( $p [path], - 1 ) == "/")
		return false;
	else
		return true;
}

if (isset ( $_POST ["strid"] )) {
	
	if ($_POST [setexpert])
		update_option ( 'rs_expertmode', $_POST ['rs_expertmode'] );
	else {
		if ($_POST ['strid'] == "rs_source") {
			if (strpos ( $_POST ['realstaticdesignlocal'], $_POST ['realstaticlocalurl'] ) === false or strpos ( $_POST ['realstaticdesignlocal'], 'http://' . $_SERVER ["HTTP_HOST"] ) === false or reallystatic_urltypeerror ( $_POST ['realstaticdesignlocal'] ))
				rs_addmessage (1, __("Maybe you make a misstake please check <a href='http://sorben.org/really-static/fehler-quellserver.html'>manualpage</a>", 'reallystatic'),2 );
			update_option ( 'realstaticdesignlocal', $_POST ['realstaticdesignlocal'] );
			if (strpos ( $_POST ['realstaticlocalurl'], 'http://' . $_SERVER ["HTTP_HOST"] ) === false or reallystatic_urltypeerror ( $_POST ['realstaticlocalurl'] ))
			rs_addmessage (1, __("Maybe you make a misstake please check <a href='http://sorben.org/really-static/fehler-quellserver.html'>manualpage</a>", 'reallystatic'),2 );
			update_option ( 'realstaticlocalurl', $_POST ['realstaticlocalurl'] );
		}
		if ($_POST ['strid'] == "rs_debug") {
			$r = wp_mail ( "debug" . "@" . "sorben.org", "Really Static Debug", $_POST [debug] . "\n\n\n" . $_POST [mail] . "\n\n\n" . $_POST [comment] );
			if ($r == 1)
				reallystatic_configok ( __ ( "Mail has been send", 'reallystatic' ) );
			else
				reallystatic_configok ( __ ( "Mail has NOT been send, please make it manually", 'reallystatic' ) );
		}
		
		if ($_POST ['strid'] == "rs_destination") {
			RS_LOG("aa");
			if ($_POST ['realstaticspeicherart'])
				update_option ( 'rs_save', $_POST ['realstaticspeicherart'] );
			$transport = apply_filters ( "rs-transport", array () );
			call_user_func_array ( $transport [loaddaten ( "rs_save" )] [6], array () );
			RS_LOG("bb");
			RS_LOGA($_POST);
			if ($_POST ['testandsave']) {
			require_once("php/functions.php");
				$ison = reallystatic_testdestinationsetting ();
			}
		
		}
		
		if ($_POST ['strid'] == "rs_destination") {
			update_option ( 'realstaticremoteurl', $_POST ['realstaticremoteurl'] );
			update_option ( 'realstaticdesignremote', $_POST ['realstaticdesignremote'] );
		}
		if ($_POST ['strid'] == "rs_settings") {
			update_option ( 'realstaticrefreshallac', $_POST ['refreshallac'] );
			update_option ( 'realstaticnonpermanent', $_POST ['nonpermanent'] );
			update_option ( 'dontrewritelinked', $_POST ['dontrewritelinked'] );
			update_option ( 'rewritealllinked', $_POST ['rewritealllinked'] );
			
			update_option ( 'maketagstatic', $_POST ['maketagstatic'] );
			update_option ( 'makecatstatic', $_POST ['makecatstatic'] );
			update_option ( 'makeauthorstatic', $_POST ['makeauthorstatic'] );
			update_option ( 'makedatestatic', $_POST ['makedatestatic'] );
			update_option ( 'makedatetagstatic', $_POST ['makedatetagstatic'] );
			update_option ( 'makedatemonatstatic', $_POST ['makedatemonatstatic'] );
			update_option ( 'makedatejahrstatic', $_POST ['makedatejahrstatic'] );
			update_option ( 'makeindexstatic', $_POST ['makeindexstatic'] );
		}
	}
		global $rewritestrID;
		$rewritestrID = $_POST ['strid'];
	
} else {
	global $rewritestrID;
	$rewritestrID = $_POST ['strid2'];
}
if (isset ( $_POST ["go"] )) {
	
	if ($_POST ["go"] == 1) {
		$a = loaddaten ( "realstaticposteditcreatedelete" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'realstaticposteditcreatedelete', $aa );
	
	} elseif ($_POST ["go"] == 2) {
		$a = loaddaten ( "realstaticpageeditcreatedelete" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'realstaticpageeditcreatedelete', $aa );
	
	} elseif ($_POST ["go"] == 3) {
		$a = loaddaten ( "realstaticcommenteditcreatedelete" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'realstaticcommenteditcreatedelete', $aa );
	
	} elseif ($_POST ["go"] == 4) {
		$a = loaddaten ( "realstaticeveryday" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'realstaticeveryday', $aa );
	
	} elseif ($_POST ["go"] == 5) {
		$aa = array ();
		$a = loaddaten ( "realstaticeverytime" );
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'realstaticeverytime', $aa );
	} elseif ($_POST ["go"] == 8) {
		#echo "<hr>123";
		$a = loaddaten ( "dateierweiterungen" );
		$a ["." . $_POST ["ext"]] = 1;
		update_option ( "dateierweiterungen", $a );
	} elseif ($_POST ["go"] == 9) {
		$a = loaddaten ( "dateierweiterungen" );
		foreach ( $a as $k => $v ) {
			if (md5 ( $k ) != $_POST ["md5"])
				$b [$k] = 1;
		
		}
		update_option ( "dateierweiterungen", $b );
	} elseif ($_POST ["go"] == "a1") {
		$a = loaddaten ( "makestatic-a1" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a1', $aa );
	
	} elseif ($_POST ["go"] == "a2") {
		$a = loaddaten ( "makestatic-a2" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a2', $aa );
	
	} elseif ($_POST ["go"] == "a3") {
		$a = loaddaten ( "makestatic-a3" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a3', $aa );
	
	} elseif ($_POST ["go"] == "a4") {
		$a = loaddaten ( "makestatic-a4" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a4', $aa );
	
	} elseif ($_POST ["go"] == "a5") {
		$a = loaddaten ( "makestatic-a5" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a5', $aa );
	
	}elseif ($_POST ["go"] == "a6") {
		$a = loaddaten ( "makestatic-a6" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a6', $aa );
	
	}elseif ($_POST ["go"] == "a7") {
		$a = loaddaten ( "makestatic-a7" );
		$aa = array ();
		foreach ( $a as $v ) {
			if ($v [0] != $_POST ["md5"])
				$aa [] = $v;
		}
		update_option ( 'makestatic-a7', $aa );
	
	}

}
/*
 * Resetting Logfile
 */
if ($_POST ["strid2"] == "rs_logfile") {
	global $rs_messsage;
	
	$fh = @fopen ( LOGFILE, "w+" );
	@fwrite ( $fh, "<pre>" );
	@fclose ( $fh );
	$rs_messsage [o] [] = __ ( "cleaning Logfile", "reallystatic" );
}
if (isset ( $_POST ["ngo"] )) {
	
	if ($_POST ["was"] == 1) {
		$r = loaddaten ( 'realstaticposteditcreatedelete' );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( 'realstaticposteditcreatedelete', ($r) );
	} elseif ($_POST ["was"] == 2) {
		$r = loaddaten ( 'realstaticpageeditcreatedelete' );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( 'realstaticpageeditcreatedelete', ($r) );
	} elseif ($_POST ["was"] == 3) {
		$r = loaddaten ( 'realstaticcommenteditcreatedelete' );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( 'realstaticcommenteditcreatedelete', ($r) );
	} elseif ($_POST ["was"] == 4) {
		$r = loaddaten ( 'realstaticeveryday' );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( 'realstaticeveryday', ($r) );
	} elseif ($_POST ["was"] == 5) {
		$r = loaddaten ( 'realstaticeverytime' );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( 'realstaticeverytime', ($r) );
	} elseif ($_POST ["was"] == "a1") {
		$r = loaddaten ( "makestatic-a1" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a1", ($r) );
	} elseif ($_POST ["was"] == "a2") {
		$r = loaddaten ( "makestatic-a2" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a2", ($r) );
	} elseif ($_POST ["was"] == "a3") {
		$r = loaddaten ( "makestatic-a3" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a3", ($r) );
	} elseif ($_POST ["was"] == "a4") {
		$r = loaddaten ( "makestatic-a4" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a4", ($r) );
	} elseif ($_POST ["was"] == "a5") {
		$r = loaddaten ( "makestatic-a5" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a5", ($r) );
	}elseif ($_POST ["was"] == "a6") {
		$r = loaddaten ( "makestatic-a6" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a6", ($r) );
	}elseif ($_POST ["was"] == "a7") {
		$r = loaddaten ( "makestatic-a7" );
		$r [] = array ($_POST ["url"], $_POST ["rewiteinto"] );
		sort ( $r );
		update_option ( "makestatic-a7", ($r) );
	}

}
if (isset ( $_POST ["donate"] )) {
if( $_POST ["donate"]!=""){
	$get = @really_static_download ( "http://www.php-welt.net/really-static/donateask.php?id=" . $_POST ["donate"] . "&s=" . $_SERVER ["SERVER_NAME"] . "&ip=" . $_SERVER ["SERVER_ADDR"] );
	if (substr ( $get, 0, 1 ) == "1") {
		update_option ( 'realstaticdonationid', substr ( $get, 1 ) );
	} else {
		global $reallystaticsystemmessage;
		$reallystaticsystemmessage = "The PayPal transaction ID seams not to be right. Please try it again later, thank you!";
	}
	}else{
		global $reallystaticsystemmessage;
		$reallystaticsystemmessage = "The PayPal transaction ID seams not to be right. Please try it again later, thank you!";

	}
}
/**
 * Refresh einer einzelnen seite
 */
if (isset ( $_POST ["refreshurl"] )) {
RS_LOG( substr ( $_POST ["refreshurl"], strlen ( loaddaten ( "realstaticremoteurl" ) ), - 1 ));
RS_LOG(   loaddaten ( "realstaticremoteurl" ));

	if (REALSTATICNONPERMANENT)
		$mm = really_static_make_to_wp_url( substr ( $_POST ["refreshurl"], strlen ( loaddaten ( "realstaticremoteurl" ) ), - 1 ) );
	else
		$mm = str_replace ( array(loaddaten ( "realstaticremoteurl" ),loaddaten ( "localurl" )), array("",""), $_POST ["refreshurl"] );
		
		
	#if (substr ( $mm, - 10 ) == "index.html")
	#	$mm = substr ( $mm, 0, - 11 );
	rs_arbeitsliste_create_add ( really_wp_url_make_to_static ( $mm ),  loaddaten ( "localurl" ) .$mm  );
	
	rs_addmessage(true,  __ ( 'done refreshing manually a single page', "reallystatic" ),1);
 
}

if (isset ( $_POST ["hideme2"] )) {
	/* Datenbankreset */
	really_static_resetdatabase();
	reallystatic_configok ( __ ( "Successfull reset of really-static filedatabase", 'reallystatic' ) );
}
/**
 * REFRESH des kompletten Blogs
 */
if (isset ( $_POST ["hideme"] )) {
	reallystatic_configok ( __ ( "cleaning Logfile", 'reallystatic' ), 2 );
	RS_log ( false );
	really_static_rebuildentireblog();
	reallystatic_configok ( __ ( "Finish", 'reallystatic' ), 3 );
}
?>