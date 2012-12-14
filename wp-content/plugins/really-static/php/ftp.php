<?php
add_action("rs-aboutyourplugin",create_function('','echo "<b>FTP-Snapin (v0.91):</b> programmed by Erik Sefkow,paul.ren and TOMO<br>";'));

add_filter("rs-transport","ftp");
function ftp($transportfuntions){
	$transportfuntions[ftp]=array(
	"rs_ftp_connect",
	"rs_ftp_disconnect",
	"rs_ftp_writefile",
	"rs_ftp_deletefile",
	"rs_ftp_writecontent",
	"rs_ftp_isconnected",
	"rs_ftp_savesettings");
	return $transportfuntions;
} 
add_filter("rs-adminmenu-transport","ftpadmin");
function ftpadmin($array){
if (get_option ( "ftpsaveroutine" ) === false)add_option ( "ftpsaveroutine",1, '', 'yes' );

	$array[]=array(name=>"ftp",title=>__('work with ftp', 'reallystatic'),main=>'<div style="margin-left:50px;"><table border="0" width="100%"><tr><td width="350px">'.__('FTP-Server IP', 'reallystatic').':'.__('Port', 'reallystatic').'</td><td>:<input name="realstaticftpserver" size="50" type="text" value="' . loaddaten ( "realstaticftpserver" , 'reallystatic') . '"	/>:<input name="realstaticftpport" size="5" type="text" value="' . loaddaten ( "realstaticftpport" , 'reallystatic') . '"	/></td></tr>'
. '<tr><td>'.__('FTP-login User', 'reallystatic').'</td><td>:<input name="realstaticftpuser" size="50" type="text" value="' . loaddaten ( "realstaticftpuser", 'reallystatic' ) . '"	/></td></tr>'
. '<tr><td>'.__('FTP-login Password', 'reallystatic').'</td><td>:<input name="realstaticftppasswort" size="50" type="password" value="' . loaddaten ( "realstaticftppasswort" , 'reallystatic') . '"	/></td></tr>'
. '<tr><td valign="top">'.__('path from FTP-Root to cachedfiles', 'reallystatic').'</td><td>:<input name="realstaticremotepath" size="50" type="text" value="' . loaddaten ( "realstaticremotepath" , 'reallystatic') . '"	/> <a style="cursor:pointer;"  onclick="toggleVisibility(\'internalftppfad\');" >[?]</a>
	<div style="max-width:500px; text-align:left; display:none" id="internalftppfad">('.__('the path inside your FTP account e.g. "/path/".If it should saved to maindirectory write "/" ', 'reallystatic').')</div></td></tr>'
. '<tr><td>'.__('Use alternative FTP transport routine', 'reallystatic').'</td><td>:<input type="checkbox" name="ftpsaveroutine" '.ison(loaddaten ( "ftpsaveroutine"),3,'checked ','',2).' value="2"></td></tr>'
. '
</table></div><br>');
	return $array;
	
}
add_filter("rs-adminmenu-savealltransportsettings","rs_ftp_savesettings");
function rs_ftp_savesettings(){
 global $rs_messsage;
			if( $_POST ['ftpsaveroutine'])update_option ( 'ftpsaveroutine', 2 );
			else update_option ( 'ftpsaveroutine', 1 );
			update_option ( 'realstaticftpserver', $_POST ['realstaticftpserver'] );
			update_option ( 'realstaticftpuser', $_POST ['realstaticftpuser'] );
			update_option ( 'realstaticftppasswort', $_POST ['realstaticftppasswort'] );
			update_option ( 'realstaticftpport', $_POST ['realstaticftpport'] );
			update_option ( 'realstaticremotepath', $_POST ['realstaticremotepath'] );
	
	 if(substr ( $_POST ['realstaticremotepath'], - 1 ) != "/")$rs_messsage[e][]= __("You may forgot a / at the end of the path!", "reallystatic" );
	$rs_messsage[o][]= __("Saved", "reallystatic" );
}


 #echo "<hr>1";
if(function_exists("ftp_connect") and loaddaten ( "ftpsaveroutine")!=2) {
 
	//////////////////////////////////////////////////
	//ftp class
	//author:paul.ren
	//e-mail:rsr_cn@yahoo.com.cn
	//website:www.yawill.com
	//create:2004-6-23 09:22
	//modify:
	//////////////////////////////////////////////////
 #echo "<hr>2";

	class ClsFTP{
		var $host = "localhost";//FTP HOST
		var $port = "21";		//FTP port
		var $user = "Anonymous";//FTP user
		var $pass = "Email";	//FTP password
		var $link_id = "";		//FTP hand
		var $is_login = "";		//is login 
		var $debug = 1;
		var $local_dir = "";	//local path for upload or download
		var $rootdir = "";		//FTP root path of FTP server
		var $dir = "/";			//FTP current path
		
		
		function ClsFTP($user="Anonymous",$pass="Email",$host="localhost",$port="21"){
			if($host) $this->host = $host;
			if($port) $this->port = $port;
			if($user) $this->user = $user;
			if($pass) $this->pass = $pass;
			$this->login();
			$this->rootdir 	= $this->pwd();
			$this->dir 		= $this->rootdir;
		}
		function halt($msg,$line=__LINE__){
			RS_LOG( "FTP Error in line:$line");
			RS_LOG( "FTP Error message:$msg");
			#exit();
		}
		function login(){	# echo "<hr>4";
			if(!$this->link_id){
				$this->link_id = @ftp_connect($this->host,$this->port) or $this->halt("can not connect to host:$this->host:$this->port",__LINE__);
				
			}
			if(!$this->is_login){
				$this->is_login = @ftp_login($this->link_id, $this->user, $this->pass) or $this->halt("ftp login faild.invaid user or password",__LINE__);
				 
			}
		}


		function systype(){
			return ftp_systype($this->link_id);
		}
		function pwd(){
			$this->login();
			$dir = ftp_pwd($this->link_id);
			$this->dir = $dir;
			return $dir;
		}
		function cdup(){
			$this->login();
			$isok =  ftp_cdup($this->link_id);
			if($isok) $this->dir = $this->pwd();
			return $isok;
		}
		function cd($dir){
			$this->login();
			$isok = ftp_chdir($this->link_id,$dir);
			if($isok) $this->dir = $dir;
			return $isok;
		}
		function nlist($dir=""){
			$this->login();
			if(!$dir) $dir = ".";
			$arr_dir = ftp_nlist($this->link_id,$dir);
			return $arr_dir;
		}
		function rawlist($dir="/"){
			$this->login();
			$arr_dir = ftp_rawlist($this->link_id,$dir);
			return $arr_dir;
		}
		function mkdir($dir){
			$this->login();
			return @ftp_mkdir($this->link_id,$dir);
		}
		function file_size($file){
			$this->login();
			$size = ftp_size($this->link_id,$file);
			return $size;
		}
		function chmod($file,$mode=0666){
			$this->login();
			return ftp_chmod($this->link_id,$file,$mode);
		}
		function delete($remote_file){
			$this->login();
			return @ftp_delete($this->link_id,$remote_file);
		}
		function get($local_file,$remote_file,$mode=FTP_BINARY){
			$this->login();
			return ftp_get($this->link_id,$local_file,$remote_file,$mode);
		}
		function put($remote_file,$local_file,$mode=FTP_BINARY){
			$this->login();
			$t=@ftp_put($this->link_id,$remote_file,$local_file,$mode);
			if($t===false){ftp_pasv ( $this->link_id,true );$t=@ftp_put($this->link_id,$remote_file,$local_file,$mode);}
			return $t;
		}
		function put_string($remote_file,$data,$mode=FTP_BINARY){
			$this->login();
			$tmp = "/tmp";//ini_get("session.save_path");
			$tmpfile = @tempnam($tmp,"tmp_");
			if($tmpfile===false)$tmpfile =@tempnam(ini_get("session.save_path"),"tmp_");
			if($tmpfile===false)$tmpfile =@tempnam(ini_get("upload_tmp_dir"),"tmp_");
			if($tmpfile===false)die("cant write a tempfile => <a href='http://www.sorben.org/really-static/tempoary-file-error.html'>more informations</a>");
			
			$fp = @fopen($tmpfile,"w+");
			if($fp){
				fwrite($fp,$data);
				fclose($fp);
			}else return 0;
			$isok = $this->put($remote_file,$tmpfile,FTP_BINARY);
			@unlink($tmpfile);
			return $isok;
		}
		function p($msg){
			echo "<pre>";
			print_r($msg);
			echo "</pre>";
		}

		function close(){
			@ftp_quit($this->link_id);
		}
	}


	##############Erik Sefkow
	/*
	 *
	 */
	function rs_ftp_connect() {
			global $rs_ftp_isconnectet;
			if (! isset ( $rs_ftp_isconnectet ) or $rs_ftp_isconnectet === false) {
				
				$ftp_host = get_option ( "realstaticftpserver" );
				$ftp_user = get_option ( "realstaticftpuser" );
				$ftp_pass = get_option ( "realstaticftppasswort" );
				$ftp_port = get_option ( "realstaticftpport" );
				
				if ($ftp_host == "" and $ftp_user == "" and $ftp_pass == "")
					rs_ftp_error ( "Logindata missing!!", 3 );
				
				$ftp = new ClsFTP ( $ftp_user, $ftp_pass, $ftp_host, $ftp_port );
				
				$rs_ftp_isconnectet = $ftp;
			}
			
			return $rs_ftp_isconnectet;
		}
				function rs_ftp_isconnected(){
				global $rs_ftp_isconnectet;
		# echo "<hr>3".$rs_ftp_isconnectet->link_id."---". $rs_ftp_isconnectet->is_login;
	if($rs_ftp_isconnectet->link_id && $rs_ftp_isconnectet->is_login)return true;
	return false;
}
	function rs_ftp_disconnect(){
		global $rs_ftp_isconnectet;
		$rs_ftp_isconnectet->close();
		 
		$rs_ftp_isconnectet=false;
	 
	}
	function rs_ftp_readfile($datei){
		$rs_ftp_isconnectet= rs_ftp_connect();
		$tmp="temp".time()."asde.tmp";
	$rs_ftp_isconnectet->get($tmp,$datei);
	$d=file_get_contents($tmp);
	unlink($tmp);
	return $d;
	}


	 
	function rs_ftp_writefile($ziel, $quelle){
	 		$ziel=get_option ( 'realstaticremotepath').$ziel;
	 $ziel=str_replace("//","/",$ziel);
	 
		$rs_ftp_isconnectet= rs_ftp_connect();
		
		if($rs_ftp_isconnectet->put ($ziel,$quelle )===false){
		 
		$dir=rs_ftp_recursivemkdir($ziel);
		 
		if($rs_ftp_isconnectet->put ($ziel,$quelle )===false){
			echo "Have not enoth rigths to create Folders. tryed ($dir): ".$ziel;
			exit;
		}
		}
		
		
	}
	function rs_ftp_writecontent($ziel,$content){
			$ziel=get_option ( 'realstaticremotepath').$ziel;
	 $ziel=str_replace("//","/",$ziel);
		#RS_LOG("FTP: $ziel");
		$rs_ftp_isconnectet= rs_ftp_connect();
		if($rs_ftp_isconnectet->put_string ( $ziel,$content )===false){
			$dir=rs_ftp_recursivemkdir($ziel);
			if($rs_ftp_isconnectet->put_string ( $ziel,$content )===false){
				echo "Have not enoth rigths to create Files. tryed ($dir): ".$ziel;
				exit;
			}
		}
		
	}
	function rs_ftp_deletefile($ziel){
			$ziel=get_option ( 'realstaticremotepath').$ziel;
	 $ziel=str_replace("//","/",$ziel);
		$rs_ftp_isconnectet= rs_ftp_connect();
		$rs_ftp_isconnectet->delete($ziel);

	}
	function rs_ftp_recursivemkdir($ziel){
	#RS_LOG("rs_ftp_recursivemkdir: $ziel");
	#echo $ziel;
		$rs_ftp_isconnectet= rs_ftp_connect();
		$dir=split("/", $ziel);
		##
		unset($dir[count($dir)-1]);
			#RS_LOGA($dir);
		$dir=implode("/",$dir);
		$ddir=$dir;
		do{
			do{
			#echo "$dir<hr>";
				$fh =@$rs_ftp_isconnectet->mkdir($dir);
				$okdir=$dir;
				$dir=split("/",$dir);
				unset($dir[count($dir)-1]);
				$dir=implode("/",$dir);

		 }while($dir!="" and $fh===false);
		 if($fh===false)die(str_replace("%folder%","$ziel",__("Im no able to create the directory %folder%! Please check writings rights!", 'reallystatic')));
		 $dir=$ddir;
		}while($okdir!=$dir);
		##
		
		return $dir;

	}
	/**
	 * text= errortex
	 * type 1=just debug 2=error-> halt
	 */
	function rs_ftp_error($text,$type){
		global $rs_messsage;
		#$fh = @fopen("transportlog.txt", "a+");
		#@fwrite($fh, date("d M Y H:i:s").": ".$text."\r\n");
		#@fclose($fh);
		#if($type==3)die($text);
	$rs_messsage[e][]= $text;
	if($type==3)die("$text");
	}


################
}else{
RS_LOG("FTPART 2");

	/*********************************************************************
	 *
	 *    PHP FTP Client Class By TOMO ( groove@spencernetwork.org )
	 *
	 *  - Version 0.12 (2002/01/11)
	 *  - This script is free but without any warranty.
	 *  - You can freely copy, use, modify or redistribute this script
	 *    for any purpose.
	 *  - But please do not erase this information!!.
	 *
	 ********************************************************************/



	/*********************************************************************
	Example

	$ftp_host = "ftp.example.com";
	$ftp_user = "username";
	$ftp_pass = "password";

	$ftp = new ftp();

	$ftp->debug = TRUE;

	if (!$ftp->ftp_connect($ftp_host)) {
		die("Cannot connect\n");
	}

	if (!$ftp->ftp_login($ftp_user, $ftp_pass)) {
		$ftp->ftp_quit();
		die("Login failed\n");
	}

	if ($pwd = $ftp->ftp_pwd()) {
		echo "Current directory is ".$pwd."\n";
	} else {
		$ftp->ftp_quit();
		die("Error!!\n");
	}

	if ($sys = $ftp->ftp_systype()) {
		echo "Remote system is ".$sys."\n";
	} else {
		$ftp->ftp_quit();
		die("Error!!\n");
	}


	$local_filename  = "local.file";
	$remote_filename = "remote.file";

	if ($ftp->ftp_file_exists($remote_filename) == 1) {
		$ftp->ftp_quit();
		die($remote_filename." already exists\n");
	}

	if ($ftp->ftp_put($remote_filename, $local_filename)) {
		echo $local_filename." has been uploaded as ".$remote_filename."\n";
	} else {
		$ftp->ftp_quit();
		die("Error!!\n");
	}


	$ftp->ftp_quit();
	*********************************************************************/



	/*********************************************************************
	List of available functions

	ftp_connect($server, $port = 21)
	ftp_login($user, $pass)
	ftp_pwd()
	ftp_size($pathname)
	ftp_mdtm($pathname)
	ftp_systype()
	ftp_cdup()
	ftp_chdir($pathname)
	ftp_delete($pathname)
	ftp_rmdir($pathname)
	ftp_mkdir($pathname)
	ftp_file_exists($pathname)
	ftp_rename($from, $to)
	ftp_nlist($arg = "", $pathname = "")
	ftp_rawlist($pathname = "")
	ftp_get($localfile, $remotefile, $mode = 1)
	ftp_put($remotefile, $localfile, $mode = 1)
	ftp_site($command)
	ftp_quit()

	*********************************************************************/



	class ftp
	{
		/* Public variables */
		var $debug;
		var $umask;
		var $timeout;

		/* Private variables */
		var $ftp_sock;
		var $ftp_resp;

		/* Constractor */
		function ftp()
		{
	 
			$this->debug = FALSE;
			$this->umask = 0022;
			$this->timeout = 30;

			if (!defined("FTP_BINARY")) {
				define("FTP_BINARY", 1);
			}
			if (!defined("FTP_ASCII")) {
				define("FTP_ASCII", 0);
			}

			$this->ftp_resp = "";
		}

		/* Public functions */

		
		
		function ftp_connect($server, $port = 21)
		{
			$this->ftp_debug("Trying to ".$server.":".$port." ...\n");
			$this->ftp_sock = @fsockopen($server, $port, $errno, $errstr, $this->timeout);
	if (!$this->ftp_sock){
	die("FTP Logindaten falsch");
	}
			if (!$this->ftp_sock || !$this->ftp_ok()) {
				$this->ftp_debug("Error : Cannot connect to remote host \"".$server.":".$port."\"\n");
				$this->ftp_debug("Error : fsockopen() ".$errstr." (".$errno.")\n");
				return FALSE;
			}
			$this->ftp_debug("Connected to remote host \"".$server.":".$port."\"\n");

			return TRUE;
		}

		function ftp_login($user, $pass)
		{
			$this->ftp_putcmd("USER", $user);
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : USER command failed\n");
				return FALSE;
			}

			$this->ftp_putcmd("PASS", $pass);
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : PASS command failed\n");
				return FALSE;
			}
			$this->ftp_debug("Authentication succeeded\n");

			return TRUE;
		}

		function ftp_pwd()
		{
			$this->ftp_putcmd("PWD");
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : PWD command failed\n");
				return FALSE;
			}

			return ereg_replace("^[0-9]{3} \"(.+)\" .+\r\n", "\\1", $this->ftp_resp);
		}

		function ftp_size($pathname)
		{
			$this->ftp_putcmd("SIZE", $pathname);
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : SIZE command failed\n");
				return -1;
			}

			return ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
		}

		function ftp_mdtm($pathname)
		{
			$this->ftp_putcmd("MDTM", $pathname);
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : MDTM command failed\n");
				return -1;
			}
			$mdtm = ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
			$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
			$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);

			return $timestamp;
		}

		function ftp_systype()
		{
			$this->ftp_putcmd("SYST");
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : SYST command failed\n");
				return FALSE;
			}
			$DATA = explode(" ", $this->ftp_resp);

			return $DATA[1];
		}

		function ftp_cdup()
		{
			$this->ftp_putcmd("CDUP");
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : CDUP command failed\n");
			}
			return $response;
		}

		function ftp_chdir($pathname)
		{
			$this->ftp_putcmd("CWD", $pathname);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : CWD command failed\n");
			}
			return $response;
		}

		function ftp_delete($pathname)
		{
			$this->ftp_putcmd("DELE", $pathname);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : DELE command failed\n");
			}
			return $response;
		}

		function ftp_rmdir($pathname)
		{
			$this->ftp_putcmd("RMD", $pathname);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : RMD command failed\n");
			}
			return $response;
		}

		function ftp_mkdir($pathname)
		{
			$dir=split("/", $pathname);
			#print_r($dir);
			$pathname="";
			$ret = true;
			for ($i=0;$i<count($dir);$i++)
			{
			$old=$pathname;
				$pathname.=$dir[$i]."/";

				$bool=$this->ftp_chdir($pathname);
							#var_dump($bool);
				
				if($bool===false){
	 $this->ftp_chdir($old);
	 #echo "<b>$pathname -> $old</b>";
					$this->ftp_putcmd("MKD", $dir[$i]);
					$response = $this->ftp_ok();
					if (!$response) {
						$this->ftp_debug("Error : MKD command failed\n");
						#$pwd = $ftp->ftp_pwd();
		#echo "Current directory is ".$pwd."\n";
		#print_r($this->nlist());
						die("<hr>mkdir $pathname fehler".$dir[$i]);
					}
			
					$this->ftp_chdir($pathname);
					 
				}
			 
			}
		 
		return true;
		
		
		
		
			$this->ftp_putcmd("MKD", $pathname);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : MKD command failed\n");
			}
			return $response;
		}

		function ftp_file_exists($pathname)
		{
			if (!($remote_list = $this->ftp_nlist("-a"))) {
				$this->ftp_debug("Error : Cannot get remote file list\n");
				return -1;
			}
			
			reset($remote_list);
			while (list(,$value) = each($remote_list)) {
				if ($value == $pathname) {
					$this->ftp_debug("Remote file ".$pathname." exists\n");
					return 1;
				}
			}
			$this->ftp_debug("Remote file ".$pathname." does not exist\n");

			return 0;
		}

		function ftp_rename($from, $to)
		{
			$this->ftp_putcmd("RNFR", $from);
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : RNFR command failed\n");
				return FALSE;
			}
			$this->ftp_putcmd("RNTO", $to);

			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : RNTO command failed\n");
			}
			return $response;
		}

		function ftp_nlist($arg = "", $pathname = "")
		{
			if (!($string = $this->ftp_pasv())) {
				return FALSE;
			}

			if ($arg == "") {
				$nlst = "NLST";
			} else {
				$nlst = "NLST ".$arg;
			}
			$this->ftp_putcmd($nlst, $pathname);

			$sock_data = $this->ftp_open_data_connection($string);
			if (!$sock_data || !$this->ftp_ok()) {
				$this->ftp_debug("Error : Cannot connect to remote host\n");
				$this->ftp_debug("Error : NLST command failed\n");
				return FALSE;
			}
			$this->ftp_debug("Connected to remote host\n");

			while (!feof($sock_data)) {
				$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
			}
			$this->ftp_close_data_connection($sock_data);
			$this->ftp_debug(implode("\n", $list));

			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : NLST command failed\n");
				return FALSE;
			}

			return $list;
		}

		function ftp_rawlist($pathname = "")
		{
			if (!($string = $this->ftp_pasv())) {
				return FALSE;
			}

			$this->ftp_putcmd("LIST", $pathname);

			$sock_data = $this->ftp_open_data_connection($string);
			if (!$sock_data || !$this->ftp_ok()) {
				$this->ftp_debug("Error : Cannot connect to remote host\n");
				$this->ftp_debug("Error : LIST command failed\n");
				return FALSE;
			}

			$this->ftp_debug("Connected to remote host\n");

			while (!feof($sock_data)) {
				$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
			}
			$this->ftp_debug(implode("\n", $list));
			$this->ftp_close_data_connection($sock_data);

			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : LIST command failed\n");
				return FALSE;
			}

			return $list;
		}

		function ftp_get($localfile, $remotefile, $mode = 1)
		{
			umask($this->umask);

			if (@file_exists($localfile)) {
				$this->ftp_debug("Warning : local file will be overwritten\n");
			}

			$fp = @fopen($localfile, "w");
			if (!$fp) {
				$this->ftp_debug("Error : Cannot create \"".$localfile."\"");
				$this->ftp_debug("Error : GET command failed\n");
				return FALSE;
			}

			if (!$this->ftp_type($mode)) {
				$this->ftp_debug("Error : GET command failed\n");
				return FALSE;
			}

			if (!($string = $this->ftp_pasv())) {
				$this->ftp_debug("Error : GET command failed\n");
				return FALSE;
			}

			$this->ftp_putcmd("RETR", $remotefile);

			$sock_data = $this->ftp_open_data_connection($string);
			if (!$sock_data || !$this->ftp_ok()) {
				$this->ftp_debug("Error : Cannot connect to remote host\n");
				$this->ftp_debug("Error : GET command failed\n");
				return FALSE;
			}
			$this->ftp_debug("Connected to remote host\n");
			$this->ftp_debug("Retrieving remote file \"".$remotefile."\" to local file \"".$localfile."\"\n");
			while (!feof($sock_data)) {
				fputs($fp, fread($sock_data, 4096));
			}
			fclose($fp);

			$this->ftp_close_data_connection($sock_data);

			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : GET command failed\n");
			}
			return $response;
		}
		function ftp_write($remotefile, $data123, $mode = 1){
			$r=$this->ftp_write2($remotefile, $data123, $mode );
			if($r=="123"&& $r!==true)$this->ftp_write2($remotefile, $data123, $mode );
		}
		function ftp_write2($remotefile, $data123, $mode = 1)
		{
			
			$this->ftp_debug("start filewrite\n");

	 

			if (!$this->ftp_type($mode)) {
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			if (!($string = $this->ftp_pasv())) {
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			$this->ftp_putcmd("STOR", $remotefile);
			$sock_data = $this->ftp_open_data_connection($string);
			if (!$sock_data || !$this->ftp_ok()) {
				//verzeichniss nicht da?
				$dir=split("/", $remotefile);
				unset($dir[count($dir)-1]);
				$this->ftp_mkdir(implode("/",$dir));
				$this->ftp_putcmd("STOR", $remotefile);
				
				if (!$sock_data || !$this->ftp_ok()) {
					$this->ftp_debug("Error : Cannot connect to remote host\n");
					$this->ftp_debug("Error : PUT command failed\n");
					return FALSE;
				}
					$this->ftp_close_data_connection($sock_data);
					$response = $this->ftp_ok();
				return 123;
	}	
	 
			
			$this->ftp_debug("Connected to remote host\n");
			$this->ftp_debug("Storing data $data123 to remote file \"".$remotefile."\"\n");
			
				fputs($sock_data, $data123);
	 
			$this->ftp_close_data_connection($sock_data);

			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : PUT command failed\n");
			}
			return $response;
		}
		
		
		
		
		
		
		
		
		
		
		function ftp_put($remotefile, $localfile, $mode = 1){
	$r=$this->ftp_put2($remotefile, $localfile, $mode );
		 if($r=="123"&& $r!==true)$this->ftp_put2($remotefile, $localfile, $mode );
		}
		
		
		
		
		function ftp_put2($remotefile, $localfile, $mode = 1)
		{
			
			if (!@file_exists($localfile)) {
				$this->ftp_debug("Error : No such file or directory \"".$localfile."\"\n");
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			$fp = @fopen($localfile, "r");
			if (!$fp) {
				$this->ftp_debug("Error : Cannot read file \"".$localfile."\"\n");
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			if (!$this->ftp_type($mode)) {
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			if (!($string = $this->ftp_pasv())) {
				$this->ftp_debug("Error : PUT command failed\n");
				return FALSE;
			}

			$this->ftp_putcmd("STOR", $remotefile);

			$sock_data = $this->ftp_open_data_connection($string);
	if (!$sock_data || !$this->ftp_ok()) {
				//verzeichniss nicht da?
				$dir=split("/", $remotefile);
				unset($dir[count($dir)-1]);
				$this->ftp_mkdir(implode("/",$dir));
				$this->ftp_putcmd("STOR", $remotefile);
				
				if (!$sock_data || !$this->ftp_ok()) {
					$this->ftp_debug("Error : Cannot connect to remote host\n");
					$this->ftp_debug("Error : PUT command failed\n");
					return FALSE;
				}
					$this->ftp_close_data_connection($sock_data);
					$response = $this->ftp_ok();
				return 123;
	}	
	 
			$this->ftp_debug("Connected to remote host\n");
			$this->ftp_debug("Storing local file \"".$localfile."\" to remote file \"".$remotefile."\"\n");
			while (!feof($fp)) {
				fputs($sock_data, fread($fp, 4096));
			}
			fclose($fp);

			$this->ftp_close_data_connection($sock_data);

			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : PUT command failed\n");
			}
			return $response;
		}

		function ftp_site($command)
		{
			$this->ftp_putcmd("SITE", $command);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : SITE command failed\n");
			}
			return $response;
		}

		function ftp_quit()
		{
			$this->ftp_putcmd("QUIT");
			if (!$this->ftp_ok() || !fclose($this->ftp_sock)) {
				$this->ftp_debug("Error : QUIT command failed\n");
				return FALSE;
			}
			$this->ftp_debug("Disconnected from remote host\n");
			return TRUE;
		}

		/* Private Functions */

		function ftp_type($mode)
		{
			if ($mode) {
				$type = "I"; //Binary mode
			} else {
				$type = "A"; //ASCII mode
			}
			$this->ftp_putcmd("TYPE", $type);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : TYPE command failed\n");
			}
			return $response;
		}

		function ftp_port($ip_port)
		{
			$this->ftp_putcmd("PORT", $ip_port);
			$response = $this->ftp_ok();
			if (!$response) {
				$this->ftp_debug("Error : PORT command failed\n");
			}
			return $response;
		}

		function ftp_pasv()
		{
			$this->ftp_putcmd("PASV");
			if (!$this->ftp_ok()) {
				$this->ftp_debug("Error : PASV command failed\n");
				return FALSE;
			}

			$ip_port = ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*\r\n$", "\\1", $this->ftp_resp);
			return $ip_port;
		}

		function ftp_putcmd($cmd, $arg = "")
		{
			if ($arg != "") {
				$cmd = $cmd." ".$arg;
			}

			fputs($this->ftp_sock, $cmd."\r\n");
			$this->ftp_debug("> ".$cmd."\n");

			return TRUE;
		}

		function ftp_ok()
		{
		if($this->ftp_sock===false) return FALSE;
		
			$this->ftp_resp = "";
			do {
				$res = @fgets($this->ftp_sock, 512) or die("FGETS error");
				$this->ftp_resp .= $res;
			} while (substr($res, 3, 1) != " ");

			$this->ftp_debug(str_replace("\r\n", "\n", $this->ftp_resp));

			if (!ereg("^[123]", $this->ftp_resp)) {
				return FALSE;
			}

			return TRUE;
		}

		function ftp_close_data_connection($sock)
		{
			$this->ftp_debug("Disconnected from remote host\n");
			return fclose($sock);
		}

		function ftp_open_data_connection($ip_port)
		{
			if (!ereg("[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+", $ip_port)) {
				$this->ftp_debug("Error : Illegal ip-port format(".$ip_port.")\n");
				return FALSE;
			}

			$DATA = explode(",", $ip_port);
			$ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
			$port   = $DATA[4]*256 + $DATA[5];
			$this->ftp_debug("Trying to ".$ipaddr.":".$port." ...\n");
			$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
			if (!$data_connection) {
				$this->ftp_debug("Error : Cannot open data connection to ".$ipaddr.":".$port."\n");
				$this->ftp_debug("Error : ".$errstr." (".$errno.")\n");
				return FALSE;
			}

			return $data_connection;
		}

		function ftp_debug($message = "")
		{
			if ($this->debug) {
				RS_LOG($message);
			}

			return TRUE;
		}
	}


	##############Erik Sefkow
	/*
	 *
	 */
	function rs_ftp_connect(){

		global $rs_ftp_isconnectet;
		if(!isset($rs_ftp_isconnectet)){
				$ftp_host = get_option ( "realstaticftpserver" );
				$ftp_user = get_option ( "realstaticftpuser" );
				$ftp_pass = get_option ( "realstaticftppasswort" );
				$ftp_port = get_option ( "realstaticftpport" );
			 if($ftp_host==""and $ftp_user==""and $ftp_pass=="")rs_ftp_error( "Logindata missing!!" ,3);
			$ftp = new ftp ($ftp_host,$ftp_user, $ftp_pass );
			$ftp->debug = False;

			if (! $ftp->ftp_connect ( $ftp_host,$ftp_port )) {
				rs_ftp_error ( "Cannot connect",3 );
			}
			if (! $ftp->ftp_login ( $ftp_user, $ftp_pass )) {
				$ftp->ftp_quit ();
				rs_ftp_error ( "Login failed",3 );
			}
			if ($pwd = $ftp->ftp_pwd ()) {
			} else {
				$ftp->ftp_quit ();
				rs_ftp_error ( "Error!!" ,3);
			}
			if ($sys = $ftp->ftp_systype ()) {
			} else {
				$ftp->ftp_quit ();
				rs_ftp_error( "Error!!" ,3);
			}
			$rs_ftp_isconnectet=$ftp;
				 
	 
		}

		return $rs_ftp_isconnectet;
	}
	
					function rs_ftp_isconnected(){
				global $ftperror;
		# echo "<hr>3".$rs_ftp_isconnectet->link_id."---". $rs_ftp_isconnectet->is_login;
	if($ftperror=="")return true;
	return false;
}
	
	function rs_ftp_readfile($datei){
		$rs_ftp_isconnectet= rs_ftp_connect();
		$tmp="temp".time()."asde.tmp";
		$rs_ftp_isconnectet->ftp_get($tmp,$datei);
		$d=file_get_contents($tmp);
		unlink($tmp);
		return $d;
	}
	function rs_ftp_disconnect(){
		global $rs_ftp_isconnectet;
		$rs_ftp_isconnectet->ftp_quit ();
		unset($rs_ftp_isconnectet);
	}
	function rs_ftp_writefile($ziel, $quelle){
	$ziel=get_option ( 'realstaticremotepath').$ziel;
	 $ziel=str_replace("//","/",$ziel);
		$rs_ftp_isconnectet= rs_ftp_connect();
		$rs_ftp_isconnectet->ftp_put ($ziel,$quelle );
	}
	function rs_ftp_writecontent($ziel,$content){
		$ziel=get_option ( 'realstaticremotepath').$ziel;
		$ziel=str_replace("//","/",$ziel);
		$rs_ftp_isconnectet= rs_ftp_connect();
		$rs_ftp_isconnectet->ftp_write ( $ziel,$content );
	}
	function rs_ftp_deletefile($ziel){
		$ziel=get_option ( 'realstaticremotepath').$ziel;
	 $ziel=str_replace("//","/",$ziel);
		$rs_ftp_isconnectet= rs_ftp_connect();
		$rs_ftp_isconnectet->ftp_delete($ziel);

	}
	function rs_ftp_recursivemkdir(){
		#function rs_ftp_connect();

	}
	/**
	 * text= errortex
	 * type 1=just debug 2=error-> halt
	 */
	function rs_ftp_error($text,$type){
	global $ftperror;
	$ftperror[]=$text;
	/*	$fh = @fopen("transportlog.txt", "a+");
		@fwrite($fh, date("d M Y H:i:s").": ".$text."\r\n");
		@fclose($fh);
		if($type==3)die($text);
	*/
	}
}
?>