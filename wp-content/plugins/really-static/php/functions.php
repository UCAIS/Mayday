<?php

/**
 * 
 * @desc Teste Zielservereinstellungen
 * @return boolean=true wenn ok
 * @param none
 */
function reallystatic_testdestinationsetting($silent=false) {
	global $rs_messsage;
	$ok = 0;
	$transport = apply_filters ( "rs-transport", array () );
	call_user_func_array ( $transport [loaddaten ( "rs_save" )] [0], array () );
	if (call_user_func_array ( $transport [loaddaten ( "rs_save" )] [5], array () ) !== true){
		if(!$silent) rs_addmessage(true, __ ( "Cannot Login, please check your Logindata", 'reallystatic' ),2);
	}else {
		$da = time () . "test.txt";
		$te = "TESTESTSETSE" . time ();
		call_user_func_array ( $transport [loaddaten ( "rs_save" )] [4], array ($da, $te ) );
		if (really_static_download ( loaddaten ( "realstaticremoteurl", 'reallystatic' ) . $da ) == $te) {
			if(!$silent)rs_addmessage(true, __ ( "TEST passed!", "reallystatic" ),1);
			$ok = 1;
		} else{
			if(!$silent)rs_addmessage(true,  __ ( "TEST failed!", "reallystatic" ),2);
			}
		@call_user_func_array ( $transport [loaddaten ( "rs_save" )] [3], array ($da ) );
	}
	RS_LOG("   ---$ok");
	if ($ok == 1)
		return true;
	else
		return false;
}

?>