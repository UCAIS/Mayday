<?php
add_action("rs-aboutyourplugin",create_function('','echo "<b>SFTP-Snapin (v0.9):</b> programmed by Erik Sefkow and Jim Wigginton <br>";'));




add_filter("rs-transport","sftp");
function sftp($transportfuntions){
	$transportfuntions[sftp]=array(
	"rs_sftp_connect",
	"rs_sftp_disconnect",
	"rs_sftp_writefile",
	"rs_sftp_deletefile",
	"rs_sftp_writecontent",
	"rs_sftp_isconnected",
	"rs_sftp_saveoptions");
	return $transportfuntions;
} 
add_filter("rs-adminmenu-savealltransportsettings","rs_sftp_saveoptions");
function rs_sftp_saveoptions() {
	global $rs_messsage;
	update_option ( 'realstaticsftpserver', $_POST ['realstaticsftpserver'] );
	update_option ( 'realstaticsftpuser', $_POST ['realstaticsftpuser'] );
	update_option ( 'realstaticsftppasswort', $_POST ['realstaticsftppasswort'] );
	update_option ( 'realstaticsftpport', $_POST ['realstaticsftpport'] );
	update_option ( 'realstaticremotepathsftp', $_POST ['realstaticremotepathsftp'] );
	if (substr ( $_POST ['realstaticremotepathsftp'], - 1 ) != "/")
		$rs_messsage [e] [] = __ ( "You may forgot a / at the end of the path!", "reallystatic" );
		$rs_messsage [o] [] = __ ( "Saved", "reallystatic" );
}
add_filter("rs-adminmenu-transport","sftpadmin");
function sftpadmin($array){
	$array[]=array(name=>"sftp",title=>__('work with sftp', 'reallystatic'),main=>'<div style="margin-left:50px;"><table border="0" width="100%"><tr><td width="350px">'.__('SFTP-Server IP', 'reallystatic').':'.__('Port', 'reallystatic').'</td><td>:<input name="realstaticsftpserver" size="50" type="text" value="' . loaddaten ( "realstaticsftpserver" , 'reallystatic') . '"	/>:<input name="realstaticsftpport" size="5" type="text" value="' . loaddaten ( "realstaticsftpport" , 'reallystatic') . '"	/></td></tr>
<tr><td>'.__('SFTP-login User', 'reallystatic').'</td><td>:<input name="realstaticsftpuser" size="50" type="text" value="' . loaddaten ( "realstaticsftpuser", 'reallystatic' ) . '"	/></td></tr>
<tr><td>'.__('SFTP-login Password', 'reallystatic').'</td><td>:<input name="realstaticsftppasswort" size="50" type="password" value="' . loaddaten ( "realstaticsftppasswort" , 'reallystatic') . '"	/></td></tr><tr><td valign="top">'.__('path from SFTP-Root to cachedfiles', 'reallystatic').'</td><td>:<input name="realstaticremotepathsftp" size="50" type="text" value="' . loaddaten ( "realstaticremotepathsftp" , 'reallystatic') . '"	/> <a style="cursor:pointer;"  onclick="toggleVisibility(\'internalftppfad2\');" >[?]</a>
	<div style="max-width:500px; text-align:left; display:none" id="internalftppfad2">('.__('the path inside your FTP account e.g. "/path/".If it should saved to maindirectory write "/" ', 'reallystatic').')</div></td></tr></table></div><br>');
	return $array;
	
}
 
 
 
/*
* 
*/
function rs_sftp_isconnected(){
	global $rs_sftp_isconnectet;
	if($rs_sftp_isconnectet===true)return true;
	else return false;
}
function rs_sftp_connect(){
 
	global $rs_sftp_isconnectet,$sftp;
	if($rs_sftp_isconnectet!==true){
		include('sftp/SFTP.php');
		$sftp = new Net_SFTP(get_option ( 'realstaticsftpserver'),get_option ( 'realstaticsftpport'));
		 
		if (!$sftp->login(get_option ( 'realstaticsftpuser'), get_option ( 'realstaticsftppasswort'))) {
			do_action ( "rs-error", "login error", "SFTP" ,"");
			exit('Login Failed');
		}
		$rs_sftp_isconnectet=true;
	 }
	 return $sftp;
}
function rs_sftp_disconnect(){

}
function rs_sftp_writefile($ziel, $quelle){

	$sftp=rs_sftp_connect();
	$ziel=get_option ( 'realstaticremotepathsftp').$ziel;
	$handle = fopen ($quelle, "r");
	while (!feof($handle)) {
		$content .= fgets($handle, 4096);
	}	
	fclose ($handle);
	if($sftp->put($ziel, $content)===false){
	$dir=rs_sftp_recursivemkdir($ziel);
	if($sftp->put($ziel, $content)===false){
		do_action ( "rs-error", "missing right folder create", $dir,$ziel );
		echo "Have not enoth rigths to create Folders. tryed ($dir): ".$ziel;
		exit;
	}
	}
	 
}

function rs_sftp_readfile($datei){
$sftp=rs_sftp_connect();
$tmp="temp".time()."asde.tmp";
return $sftp->get($datei);
 
}

function rs_sftp_deletefile($file){
	$sftp=rs_sftp_connect();
	$sftp->delete($file);
}
function rs_sftp_writecontent($ziel,$content){
	$sftp=rs_sftp_connect();
	$ziel=get_option ( 'realstaticremotepathsftp').$ziel;
	 $ziel=str_replace("//","/",$ziel);
	 
		if($sftp->put($ziel, $content)===false){
	 
	$dir=rs_sftp_recursivemkdir($ziel);
	 
	if($sftp->put($ziel, $content)===false){
			 	do_action ( "rs-error", "missing right folder create", $ziel ,"" );
		echo "Have not enoth rigths to create Folders. tryed ($dir): ".$ziel;
		exit;
	}
	}
 
	
	
}
 function rs_sftp_recursivemkdir($ziel){
	 global $sftp;
	$dir=split("/", $ziel);
	##
	unset($dir[count($dir)-1]);
	$dir=implode("/",$dir);
	$ddir=$dir;
	do{
		do{
		#echo "$dir<hr>";
			$fh =@$sftp->mkdir($dir);
			$okdir=$dir;
			$dir=split("/",$dir);
			unset($dir[count($dir)-1]);
			$dir=implode("/",$dir);

	 }while($dir!="" and $fh===false);
	 if($fh===false){

	 	do_action ( "rs-error", "missing right write file", $ziel,"" );
	 	die(str_replace("%folder%","$ziel",__("Im no able to create the directory %folder%! Please check writings rights!", 'reallystatic')));
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
function rs_sftp_error($text,$type){


}
?>