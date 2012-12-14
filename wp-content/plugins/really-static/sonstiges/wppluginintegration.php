<?php
function rs_upgrade_real()
{
 RS_LOG("rs_upgrade_real");
	if(get_option ( 'rs_firstTime')==""){
		#on init
	
		 
		$defaultsettingsfile=REALLYSTATICHOME."autosetup/".md5(get_option("siteurl")).".php";
		if(file_exists(REALLYSTATICHOME."autosetup/all.php")){
			require_once (REALLYSTATICHOME."autosetup/all.php");
		}
		if(file_exists($defaultsettingsfile)){
			require_once ($defaultsettingsfile);
		}		
		require ("install_rs.php");
		wp_schedule_event ( mktime ( 4, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) ), 'daily', 'reallystatic_daylyevent' );
		
	rs_addmessage( 0, sprintf ( __ ( "You need to configure your Really-Static plugin. Use our <a href='%s'>quick setup</a> or click <a href='%s'>here</a> to jump into the settingsmenu.", 'reallystatic' ), "options-general.php?page=" . REALLYSTATICBASE . "&menu=123", "options-general.php?page=" . REALLYSTATICBASE ) ,1,"page",REALLYSTATICBASE);
		add_action( 'admin_notices' , 'jwl_visit_settings_page' );
		
		
		update_option ( 'rs_firstTime', RSVERSION . RSSUBVERSION );
		
	}elseif(get_option ( 'rs_firstTime')<0){
		#reaktivate
		update_option ( 'rs_firstTime', get_option ( 'rs_firstTime')*-1 );
		if(get_option ( 'rs_firstTime')!= RSVERSION . RSSUBVERSION )require_once("upgrade.php");
		update_option ( 'rs_firstTime', RSVERSION . RSSUBVERSION );
	}elseif(get_option ( 'rs_firstTime')!= RSVERSION . RSSUBVERSION ){
		#update
		require_once("upgrade_rs.php");
		update_option ( 'rs_firstTime', RSVERSION . RSSUBVERSION );
	}
	

}

/**
 * Installationsroutine
 *
 * @param void
 * @return void
*/
function rs_activation_real() {
	if (ini_get ( 'allow_url_fopen' ) != 1 and ! function_exists ( "curl_init" )) {
		deactivate_plugins ( $_GET ['plugin'] );
		rs_addmessage(0, __("Funktion allow_url_fopen is disabled and curl_init is also not enabled on your server!<br> For more Informations check this <a href='http://sorben.org/really-static/fehler-allow_url_fopen.html'>manualpage</a><br> Really-static is automaticly deactivated", 'reallystatic'),3);
		
	}
	
	do_action ( "rs-install" );
}
/**
 * Deinstallationsreoutine
 *
*/
function rs_deactivation_real() {
	update_option ( 'rs_firstTime', - 1*	get_option ( 'rs_firstTime') );
	wp_clear_scheduled_hook ( 'reallystatic_daylyevent' );
	update_option("home",get_option('siteurl') );
	
	do_action ( "rs-deinstall" );
}
?>