<?php
/*
 *
*/
add_action("rs-aboutyourplugin",create_function('','echo "<b>Localfile-Snapin (v1.00 final):</b> programmed by Erik Sefkow<br>";'));

add_filter("rs-transport","local");
function local($transportfuntions){
	$transportfuntions[local]=array(
			"rs_local_connect",
			"rs_local_disconnect",
			"rs_local_writefile",
			"rs_local_deletefile",
			"rs_local_writecontent",
			"rs_local_isconnected",
			"rs_local_saveoptions");
	return $transportfuntions;
}
add_filter("rs-adminmenu-transport","localadmin");
function localadmin($array){
	$array[]=array(name=>"local",title=>__('work with local filesystem', 'reallystatic'),main=>'<div style="margin-left:50px;"><table border="0" width="100%"><tr><td valign="top" width="350px">'.__('internal filepath from to cachedfiles', 'reallystatic').'</td><td>:<input name="realstaticlokalerspeicherpfad" size="50" type="text" value="' . loaddaten ( "realstaticlokalerspeicherpfad" , 'reallystatic') . '"	/> <a style="cursor:pointer;"  onclick="toggleVisibility(\'internallocalpfad\');" >[?]</a>  <div style="max-width:500px; text-align:left; display:none" id="internallocalpfad">('.__('the path inside your system e.g. "/www/html/".If it should saved to maindirectory write "/" ', 'reallystatic').')</div></td></tr></table></div><br>');
	return $array;

}
#if($_POST ['strid'] == "rs_destination" and $_POST['realstaticspeicherart']=="local"){
add_filter("rs-adminmenu-savealltransportsettings","rs_local_saveoptions");


function rs_local_saveoptions(){
	global $rs_messsage;
	update_option ( 'realstaticlokalerspeicherpfad', $_POST ['realstaticlokalerspeicherpfad'] );
	if (substr ( $_POST ['realstaticdesignremote'], - 1 ) != "/")$rs_messsage[e][]= __("You may forgot a / at the end of the path!", "reallystatic" );
	$rs_messsage[o][]= __("Saved", "reallystatic" );
}
function rs_local_isconnected(){
	return true;
}
function rs_local_connect(){
	global $rs_isconnectet;
	$rs_isconnectet=true;
}
function rs_local_disconnect(){
	global $rs_isconnectet;
	$rs_isconnectet=false;
}
function rs_local_writefile($ziel, $quelle){
	 
	$ziel= get_option ( "realstaticlokalerspeicherpfad").$ziel;
	 
	$fh=@copy($quelle,$ziel);
 
	if($fh===false){
		$dir=rs_local_recursivemkdir($ziel);
	}
	$fh=@copy($quelle,$ziel);
	if($fh===false){
		do_action ( "rs-error", "missing right folder create", $dir,$ziel );
		echo "Have not enoth rigths to create Folders. tryed ($dir): ".$ziel;
		exit;
	}
	return $ziel;

}
function rs_local_deletefile($datei){
	unlink(get_option ( "realstaticlokalerspeicherpfad" ).$datei);

}
function rs_local_writecontent($ziel, $content) {
 
	$ziel = get_option ( "realstaticlokalerspeicherpfad" ) . $ziel;
	$fh = @fopen ( $ziel, 'w+' );
	if ($fh === false) {
		$dir = rs_local_recursivemkdir ( $ziel );
		$fh = @fopen ( $ziel, 'w+' );
		if ($fh === false) {
			do_action ( "rs-error", "missing right folder create", $dir,$ziel );
			#RS_LOG ( "Have not enoth rigths to create Folders. tryed ($dir): " . $ziel );
			exit ();
		}
	}
	fwrite ( $fh, $content );
	fclose ( $fh );
	return $ziel;
}
function rs_local_recursivemkdir($ziel){
	#RS_LOGD("rs_local_recursivemkdir($ziel)");
	$dir=split("/", $ziel);
	##
	unset($dir[count($dir)-1]);
	$dir=implode("/",$dir);
	$ddir=$dir;
	do{
		do{
			#echo "$dir<hr>";
			$fh =@mkdir($dir);
			$okdir=$dir;
			$dir=split("/",$dir);
			unset($dir[count($dir)-1]);
			$dir=implode("/",$dir);

	 }while($dir!="" and $fh===false);
	 if($fh===false){
	 	do_action ( "rs-error", "missing right write file", $ziel,"" );
	 	#RS_LOG(reallystatic_configerror(3,$ziel));
	 	exit;
	 }
	 $dir=$ddir;
	}while($okdir!=$dir);
	##
	return $dir;

}
/**
 * text= errortex
 * type 1=just debug 2=error-> halt
 */
function rs_local_error($text,$type){


}
?>