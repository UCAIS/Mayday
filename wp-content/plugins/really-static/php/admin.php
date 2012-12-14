<?php
global $rewritestrID;
$ll=WPLANG;
if($ll=="" or $ll{2}!="_")$ll="en_US";
function reallystatic_settingssubmitbutton($titel, $akt = 1, $onclick = "", $name = "submit") {
	if ($akt == 1)
		$farbe = "button-primary";
	else
		$farbe = "button-secondary";
	if ($onclick != "")
		$onclick = ' onclick="' . $onclick . '"';
	return '<span class="submit"><input' . $onclick . ' type="submit" name="' . $name . '" id="submit" class="' . $farbe . '" value="' . $titel . '"  /></span>';
}


#####################
#Source
#$menu[]=array("id"=>"rs_start","name"=>"","content"=>'<iframe src="http://www.sorben.org/really-static/iframe/'.$ll.'.html" style="border:0px #FFFFFF none;" name="meinIFrame" scrolling="no" frameborder="0" align=aus marginheight="0px" marginwidth="0px" height="600" width="800"></iframe>');
#$menu[]=array("id"=>"rs_start","name"=>" ","content"=>'HHHH');
$menu[]=array("id"=>"rs_source","name"=>__('Source', 'reallystatic'),"content"=>'<form method="post"><table border="0" width="100%">
<tr><td width="400px">'.__('url to wordpressinstalltion', 'reallystatic').'</td><td>:<input name="realstaticlocalurl" size="58" type="text" value="' . loaddaten ( "realstaticlocalurl", 'reallystatic' ) . '"	/>  </td></tr>
<tr><td>'.__('url path to the actuall used templatefolder', 'reallystatic').'</td><td>:<input name="realstaticdesignlocal" size="58" type="text" value="' . loaddaten ( "realstaticdesignlocal", 'reallystatic' ) . '"	/> </td></tr>
</table><br><a target="_blank" href="http://sorben.org/really-static/fehler-quellserver.html">'.__('If you need help please check this manualpage', 'reallystatic').'</a><br>
<input type="hidden" name="strid" value="rs_source" />
'.reallystatic_settingssubmitbutton(__('Save', 'reallystatic')).'
</form>');
#--------------- ZIEL
$dest=apply_filters ( "rs-adminmenu-transport",array());
$desti='<form method="post">';
foreach($dest as $v){
	$desti.='<input type="radio" onchange="hideshowupload(this);" name="realstaticspeicherart" value="'.$v[name].'" '.ison(loaddaten ( "rs_save"),3,'checked ','',$v[name]).'id="fp'.$v[name].'"><label for="fp'.$v[name].'">'.$v[title].'</label><br><div id="shower'.$v[name].'">'.$v[main]."</div>";
}
$desti.='<br>
	<table border="0" width="100%"><tr><td valign="top" width="400px">
	'.__('Domainprefix for your cached files', 'reallystatic').'</td><td>:<input name="realstaticremoteurl" size="50" type="text" value="' . loaddaten ( "realstaticremoteurl" , 'reallystatic') . '"	/> <a style="cursor:pointer;"  onclick="toggleVisibility(\'remoteurl\');" >[?]</a>  <div style="max-width:500px; text-align:left; display:none" id="remoteurl">( where your visitors find your blog )</div></td></tr>'

. '<tr><td valign="top" >'.__('Url to the templatefolder', 'reallystatic').'</td><td>:<input name="realstaticdesignremote" size="50" type="text" value="' . loaddaten ( "realstaticdesignremote" ) . '"	/> <a style="cursor:pointer;"  onclick="toggleVisibility(\'designurl\');" >[?]</a>  <div style="max-width:500px; text-align:left; display:none" id="designurl">( for example: '. loaddaten ( "realstaticdesignlocal", 'reallystatic' ).' )</div></td></tr>'
. '</table> <input type="hidden" name="strid" value="rs_destination" />
'.reallystatic_settingssubmitbutton(__('Save', 'reallystatic'))
. '&nbsp;'.reallystatic_settingssubmitbutton(__('Test and Save', 'reallystatic'),2,'',"testandsave").'</form>';
$menu[]=array("id"=>"rs_destination","name"=>__('Destination', 'reallystatic'),"content"=>$desti );
 
#------------------ SETTINGS
$tmp="";
foreach(loaddaten ( "dateierweiterungen" ) as $k=>$v)$tmp.= ' <form method="post">'.reallystatic_settingssubmitbutton(__('remove', 'reallystatic')).'<input type="hidden" name="strid2" value="rs_settings" />'.$k.'<input type="hidden" name="go" value="9" /><input type="hidden" name="md5" value="'.md5($k).'" /></form>'."";
$menu[]=array("id"=>"rs_settings","name"=>__('Settings', 'reallystatic'),"content"=>'
<form method="post">'
. '<input type="checkbox" name="refreshallac" '.ison(loaddaten ( "realstaticrefreshallac" ),2," checked ").' value="true"> '.__('On the category/tag page e.g. is a commentcounter (not recomended)', 'reallystatic').' <a target="_blank" href="http://www.sorben.org/really-static/semi-dynamic-categorytag-pages.html">[?]</a><br>'
//. '<input type="checkbox" name="nonpermanent"'.ison(REALSTATICNONPERMANENT,2," checked ").' value="true"> '.__('I want that Really-Static try to handle with the ? in the url', 'reallystatic').'<br>'
. '<input type="checkbox" name="dontrewritelinked"'.ison(loaddaten ( "dontrewritelinked" ),2," checked ").' value="1"> '.__('Don\'t copy any linked file to the static file folder, just the static Wordpressfiles', 'reallystatic').'<br>'
. '<input type="checkbox" name="rewritealllinked"'.ison(loaddaten ( "rewritealllinked" ),2," checked ").' value="1"> '.str_replace(array("%blogurl%","%staticurl%"),array(loaddaten ( "realstaticlocalurl", 'reallystatic' ),loaddaten ( "realstaticremoteurl", 'reallystatic' )),__('Rewrite every %blogurl% with %staticurl% (high security)', 'reallystatic')).'  <a target="blank" href="http://www.sorben.org/really-static/highsecurity.html">[?]</a><br><br>'
. "<b>".__('Also make static:', 'reallystatic').'</b> <a target="blank" href="http://www.sorben.org/really-static/just-a-small-page.html">[?]</a><br>'
. '<input type="checkbox" name="maketagstatic"'.ison(loaddaten ( "maketagstatic" ),2," checked ").' value="1"> '.__('make tag-pages static', 'reallystatic').'<br>'
. '<input type="checkbox" name="makecatstatic"'.ison(loaddaten ( "makecatstatic" ),2," checked ").' value="1"> '.__('make category-pages static', 'reallystatic').'<br>'
. '<input type="checkbox" name="makeauthorstatic"'.ison(loaddaten ( "makeauthorstatic" ),2," checked ").' value="1"> '.__('make author-pages static', 'reallystatic').'<br>'

. '<input type="checkbox" name="makedatestatic"'.ison(loaddaten ( "makedatestatic" ),2," checked ").' value="1"> '.__('make date-pages static', 'reallystatic').'&nbsp;&nbsp;<input type="checkbox" name="makedatetagstatic"'.ison(loaddaten ( "makedatetagstatic" ),2," checked ").' value="1"> '.__('dayly-pages', 'reallystatic').'&nbsp;&nbsp;<input type="checkbox" name="makedatemonatstatic"'.ison(loaddaten ( "makedatemonatstatic" ),2," checked ").' value="1"> '.__('monthly-pages', 'reallystatic').'&nbsp;&nbsp;<input type="checkbox" name="makedatejahrstatic"'.ison(loaddaten ( "makedatejahrstatic" ),2," checked ").' value="1"> '.__('yearly-pages', 'reallystatic').'<br>'
. '<input type="checkbox" name="makeindexstatic"'.ison(loaddaten ( "makeindexstatic" ),2," checked ").' value="1"> '.__('make index-pages static', 'reallystatic').'<br><br>'
. ' <input type="hidden" name="strid" value="rs_settings" />'.reallystatic_settingssubmitbutton(__('Save', 'reallystatic')).'</form><br><br>'
.'<b>'.__('Copy all attached files with following extensions to the destination server:', 'reallystatic').'</b> <a target="blank" href="http://www.sorben.org/really-static/attached-files.html">[?]</a><br>'
.$tmp.'<form method="post"><input type="hidden" name="strid2" value="rs_settings" /><input type="hidden" name="go" value="8" />.<input name="ext" size="10" type="text" value=""	/>'.reallystatic_settingssubmitbutton(__('Add', 'reallystatic')).'</form>');
#------------- Reset
$menu[]=array("id"=>"rs_reset","name"=>__('Reset', 'reallystatic'),"content"=>
 '<form  method="post" id="my_fieldset"><input type="hidden" name="strid2" value="rs_reset" /><input type="hidden" name="hideme2" value="hidden" />'
. __('If you want to renew all static files, first press the "reset filedatabase" button and then the "write all files" button at the "Manual Refresh" tab', 'reallystatic').'<br>'
. ' '.reallystatic_settingssubmitbutton(__('reset filedatabase', 'reallystatic')).'</form><br>');
#-------- Manual
global $reallystaticsystemmessage;
$lastposts = get_posts ( 'numberposts=1 ' );
foreach ( $lastposts as $post ) {
	$tmp=get_permalink ( $post->ID );
	$tmp=nonpermanent($tmp);
}
$menu[]=array("id"=>"rs_refresh","name"=>__('Manual Refresh', 'reallystatic'),"content"=>'<h3>'.__('Refresh a single site manualy', 'reallystatic').'</h3>'
. '<font color="red">'.$reallystaticsystemmessage.'</font>'.

apply_filters("rs-adminmenu-refreshsinglepage",'<form method="post"><input type="hidden" name="strid2" value="rs_refresh" />'
. '<input name="refreshurl" size="50" type="text" value=""	/> '.__('(complete url of the static page)', 'reallystatic').'<a style="cursor:pointer;"  onclick="toggleVisibility(\'manual\');" >[?]</a><b style="max-width:500px; text-align:left; display:none" id="manual">('.__('for example', 'reallystatic').'):'.$tmp.'</b>'
. ' '.reallystatic_settingssubmitbutton(__('Refresh', 'reallystatic'),2).'</form><br>')


. "<h3>".__('Refresh all sites manualy', 'reallystatic')."</h3>".
apply_filters("rs-adminmenu-refreshallpages", '<form  method="post" id="my_fieldset"><input type="hidden" name="strid2" value="rs_refresh" /><input type="hidden" name="hideme" value="hidden" />'
. __('If this Plugin is installed on a Blog with exsiting Posts or for example you changed your design so you shold press the "write all files" Button. If the process is terminatet (e.g. because of a timeout), just press this button again until this menu again appears.', 'reallystatic')
. '<br>'.reallystatic_settingssubmitbutton(__('Write all files', 'reallystatic')).'</form>').'<br>');

#--------Advanced
$tmp="";
$a=loaddaten ( 'realstaticeveryday' );
if(is_array($a)and count($a)>0){
	$tmp.= "<h3>".__( 'Rewrite every 24 hours' , 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="4" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a= loaddaten ( 'realstaticeverytime' );
if(is_array($a)and count($a)>0){
	$tmp.= "<h3>".__('Rewrite on every run of Really-Static', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="5" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten ( 'realstaticposteditcreatedelete' );
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite on create, edit or delete a post', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="1" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a7");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite on create, edit or delete a comment', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a7" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
###
$a=loaddaten("makestatic-a1");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a index-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a1" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a2");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a tag-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a2" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a3");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a category-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a3" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a4");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a author-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a4" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a5");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a date-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a5" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$a=loaddaten("makestatic-a6");
if(is_array($a) and count($a)>0){
	$tmp.= "<h3>".__('Rewrite when a comment-page is createt', 'reallystatic')."</h3>";
	foreach ($a as $v){
		$tmp.= ' <form method="post"><input type="hidden" name="strid2" value="rs_advanced" />'.$v[0];
		if($v[1]!="")$tmp.= " rewrite into ".$v[1];
		$tmp.= '<input type="hidden" name="go" value="a6" /><input type="hidden" name="md5" value="'.$v[0].'" /><input name="Submit1" type="submit" value="x" /></form>'."\n";
	}
}
$menu[]=array("id"=>"rs_advanced","name"=>__('Advanced', 'reallystatic'),"content"=>'<h2>'.__('What should Really-Static do', 'reallystatic').'</h2>'
. __('This option allows you to give really-static accurate information about special URL\'s, that should made static while specified operations. By using this Form for example really-static get the order to refresh sitemaps or reload a page after 24 hours.', 'reallystatic')
. '<br><br><br><form  method="post"><input type="hidden" name="strid2" value="rs_advanced" /><input type="hidden" name="ngo" value="1" />
	'.__('Get', 'reallystatic').' '.loaddaten ( "localurl" ).'<input name="url" type="text" /><a style="cursor:pointer;"  onclick="toggleVisibility(\'a1\');" >[?]</a><b style="max-width:500px; text-align:left; display:none" id="a1">('.__('source file e.g.: "?feed=atom"', 'reallystatic').')</b>'.__(', rewrite the filename into', 'reallystatic').' <input name="rewiteinto" type="text" /> <a style="cursor:pointer;"  onclick="toggleVisibility(\'a2\');" >[?]</a><b style="max-width:500px; text-align:left; display:none" id="a2">('.__('destination filename. keep it clear if you want to use the filename from source file', 'reallystatic').')</b> '.__('and make it static', 'reallystatic').' <select name="was" style="width: 340px;">
	<option></option>
	<option value="1">'.__('when a Post is created, edited or deleted', 'reallystatic').'</option>
			<option value="a7">'.__('when a Comment is created, edited or deleted', 'reallystatic').'</option>
	<option value="4">'.__('every 24 hours', 'reallystatic').'</option>
	<option value="5">'.__('everytime Really-Static runs', 'reallystatic').'</option>
	<option value="a1">'.__('when a index-page is createt', 'reallystatic').'</option>
	<option value="a2">'.__('when a tag-page is createt', 'reallystatic').'</option>
	<option value="a3">'.__('when a category-page is createt', 'reallystatic').'</option>
	<option value="a4">'.__('when a author-page is createt', 'reallystatic').'</option>
	<option value="a5">'.__('when a date-page is createt', 'reallystatic').'</option>
		<option value="a6">'.__('when a comment-page is createt', 'reallystatic').'</option>

	</select>&nbsp; <input name="Submit1" type="submit" value="'.__('Submit', 'reallystatic').'" /></form>
'.$tmp);
unset($tmp);

/*
 * LOGFILE
 */
$array = @file( LOGFILE );
if(is_array($array)){
	$cc=count($array);
	if($cc==1){
	$tmp= __("Logfile is empty!", 'reallystatic');
		if (! @touch ( LOGFILE ))$tmp.= __("Check writing-rights: log.html", 'reallystatic') ;
	}
	else{
		global $wpdb;
		if(is_multisite()) $file= $wpdb->blogid.'-log.html' ;
		else $file='log.html';
		$tmp= sprintf(__("Last 40 Logfileentrys (<a href='%s'>full logfile</a>)", 'reallystatic'),REALLYSTATICURLHOME.$file).": <pre>";
		if($cc>40)$tt=$cc-41;
		else$tt=0;
		$merk="";
		for($i=$cc;$i>$tt;$i--){
			$merk.=  $array[$i];
	 }
	 $tmp.= preg_replace('&(http:\/\/)([\#0-9a-z.\/\-\_\?\=\&]*)&i','<a href="\1\2">\1\2</a>',$merk)."</pre>";
	}
}else $tmp=__("Unable to read logfile!", 'reallystatic').__("Check writing-rights: log.html", 'reallystatic');
$menu[]=array("id"=>"rs_logfile","name"=>__('Logfile', 'reallystatic'),"content"=>$tmp.'<form method="post"><input type="hidden" name="strid2" value="rs_logfile" /><input name="Submit1" type="submit" value="Reset Logfile" /></form>');
/*
 * DEBUG
 */
global $rs_version,$rs_rlc;
$t=5-strlen($rs_version.$rs_rlc);
if($t>0)$tmp= "Relaseid: ".$rs_version.str_repeat("0",$t).$rs_rlc."\n";
else $tmp= "Relaseid: ".$rs_version.$rs_rlc."\n";
$menu[]=array("id"=>"rs_debug","name"=>__('Debug', 'reallystatic'),"content"=>
  __("If you think there is a bug or you got any questions feel free to use this form, or use the <a href='http://really-static-support.php-welt.net/' taget='_blank'>offical support forum</a>. <br>Debugdata:", 'reallystatic')."<br>"
	
		
. '<form method="post"><textarea name="debug" cols="140" rows="25" readonly>'
. "Fileversion: ". date ("F d Y / H:i:s",$reallystaticfile)."\n".$tmp."
Language: ".$ll."\n"
. "\nCURL: ".ison(function_exists("curl_init"),1,"available","not available")."\n"
. "file_get_contents: ".ison(function_exists("file_get_contents"),1,"available","not available")."\n"
. "allow_url_fopen: ".ison(ini_get('allow_url_fopen'),2,"active","not active")."\n"
. "\nLocal: ".loaddaten ( "realstaticlocalurl", 'reallystatic' )."\n"
. "Remote: ".loaddaten ( "realstaticremoteurl", 'reallystatic' )."\n"
. "\nLocal Design: ".loaddaten ( "realstaticdesignlocal", 'reallystatic' ) ."\n"
. "Remote Design: ".loaddaten ( "realstaticdesignremote", 'reallystatic' )."\n"
. "Permastructure: ".get_option ( 'permalink_structure' )."\n"

. "Last 40 Logfileentrys:
=====================\n $merk"
. '</textarea><br>'
. __('You can send me your Debug by <a target="_blank" href="http://erik.sefkow.net/impressum.html">Email</a> or with this form.', 'reallystatic')
. '<br><LABEL ACCESSKEY=U>Your emailaddress: <INPUT TYPE=text NAME=mail SIZE=8 value="'.$_POST[mail].'"></LABEL><br>'
. '<LABEL ACCESSKEY=U>'.__("Describe your Problem (please only in German or English):", 'reallystatic').'<br><textarea name="comment" cols="140" rows="15">'.$_POST[comment].'</textarea></LABEL><br>'
. '<input type="hidden" name="strid2" value="rs_debug" /><input type="hidden" name="strid" value="rs_debug" /><input name="Submit1" type="submit" value="Send this debug information to the developer" /></form>');
 



#####################
#echo '<link href="' . REALLYSTATICURLHOME . 'sonstiges/admin.css" rel="stylesheet" type="text/css" />';
echo '<style type="text/css">
		
		
h1.reallystatic {
	background: #fff url('.REALLYSTATICURLHOME.'/sonstiges/pluginbild.jpg) right center no-repeat;
	padding: 16px 2px;
	margin: 25px 0;
	border: 1px solid #ddd;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
} 

ul#rstatic_tabs {
	list-style-type: none;
	margin: 0;
	padding: 0;
	height: 1%;
}

ul#rstatic_tabs li {
	float: left;
	border-top: 1px solid #EAF3FA;
	border-left: 1px solid #EAF3FA;
	margin: 0;
	padding: 0;
}

ul#rstatic_tabs li.last {
	border-right: 1px solid #EAF3FA;
}

ul#rstatic_tabs li a {
	font-family: "Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana;
	font-weight: bold;
	font-size: 1.1em;
	float: left;
	border: none;
	color: #00019B;
	padding: 5px 8px;
	outline: none;
	text-decoration: none;
	-moz-outline: none;
}

ul#rstatic_tabs li a:hover {
	background: #F8F8F8;
	text-decoration: underline;
}

ul#rstatic_tabs li.rstatic_active a {
	background: #EAF3FA;
}

div.rstatic_panel {
	clear: both;
	height: 1%;
	background: #EAF3FA;
	padding: 10px;
}
		</style>
';
echo "\n";
echo '<script type="text/javascript" src="' . REALLYSTATICURLHOME . 'sonstiges/admin.js"></script>';
echo '<script type="text/javascript" src="' . REALLYSTATICURLHOME . 'sonstiges/admin_2.js"></script>';
echo "\n";


echo'<h1 class="reallystatic">'.__("Really Static Settings", 'reallystatic').'</h1>';
global $rs_messsage;

if(is_array($rs_messsage[e]))foreach ($rs_messsage[e] as $v)reallystatic_configerror ( 0,$v );#errormessages
if(is_array($rs_messsage[o]))foreach ($rs_messsage[o] as $v)reallystatic_configok ( $v, 1 ); # okmessages
if(is_array($rs_messsage[m]))foreach ($rs_messsage[m] as $v)reallystatic_configok ( $v, 1 ); # js messages
unset($rs_messsage);

echo '	<form method="post" id="rstatic_option-form">

<script type="text/javascript">
//
     var url =document.URL;
	 var u=url.split("/");
	 url=u[u.length-1];
	 u=url.split("#");
	 if(u[1]!="")var strID="#"+u[1];
	 else var strID="#rs_source";
	if(strID=="#undefined")  strID="#rs_source";
	';
#if(loaddaten("realstaticdonationid")=="")echo 'strID="#rs_start";';
if($rewritestrID!="")echo 'strID="#'.$rewritestrID.'";';

echo '
 
	function hideshowupload(a){
	';
$dest=apply_filters ( "rs-adminmenu-transport",array());
foreach ($dest as $v)echo 'if(a.id=="fp'.$v[name].'")document.getElementById("shower'.$v[name].'").style.display="inline";
else document.getElementById("shower'.$v[name].'").style.display="none";';
echo '
			
	
	}
	
</script>




 <div class="tabs">
	   <ul class="tabNavigation" id="rstatic_tabs">';
     
     
     
$menu=apply_filters ( "rs-adminmenu-show",$menu);
$text="";
$rs_hide_adminpannel=get_option('rs_hide_adminpannel');
for($i = 0; $i < count($menu); $i++){
	if(isset($rs_hide_adminpannel[$menu[$i][id]]))continue;
if($menu[$i][name]!="")echo '<li id="'.$menu[$i][id].'"><a href="#'.$menu[$i][id].'">'.$menu[$i][name].'</a></li>';
else echo '<li id="'.$menu[$i][id].'"></li>';
$text.= '<div class="rstatic_panel" id="'.$menu[$i][id].'">'.$menu[$i][content].'</div>';	
}

#RS_LOG(loaddaten("realstaticdonationid"));
#RS_LOG(multiloaddaten("realstaticdonationid"));
echo '<li id="rs_donate" class="last"><a href="#rs_donate">';
if(loaddaten("realstaticdonationid")!="-" and loaddaten("realstaticdonationid")!="")echo __('About', 'reallystatic');
else{
if(loaddaten("realstaticdonationid")=="")echo __('<font color="red">please</font> ', 'reallystatic');
 echo ''.__('Donate', 'reallystatic').'';
 }
echo '</a></li></ul>';
echo '<div class="rstatic_panel" id="rs_donate">';
global $reallystaticsystemmessage;
if(loaddaten("realstaticdonationid")=="")echo '<form method="post"><input type="hidden" name="strid2" value="rs_donate" /><font color="red">'.$reallystaticsystemmessage.'</font>
  My plugins for Wordpress are "donationware". I develop, release, and maintain them for free, and you can use them for free, but I hope you find them worthy of a donation of thanks or encouragement. Registration costs one cent (a PayPal fee). If you choose to make a payment of $0.01 (or whatever minimum PayPal allows for your currency), PayPal takes that as a fee and I receive nothing, and that`s perfectly acceptable.
  <br><br>
  Your PayPal transaction ID is a fully valid registration code: <input name="donate" type="text" /><input name="Submit1" type="submit" value="Submit" /></form>
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="9157614">
<input type="image" src="https://www.paypal.com/'.$ll.'/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen mit PayPal.">
<img alt="" border="0" src="https://www.paypal.com/'.$ll.'/i/scr/pixel.gif" width="1" height="1">
</form>

  '.testfornewversion();
  elseif(loaddaten("realstaticdonationid")!="-")echo "<h2>License</h2>".loaddaten("realstaticdonationid");
else{
	echo "Thank you, for supporting really static!";
}
echo "<h2>Languagepack</h2>".__("This languagepack is written by <a target='_blank' href='http://erik.sefkow.net'>Erik Sefkow</a> please <a target='_blank' href='http://really-static-support.php-welt.net/why-donate--t8.html'>donate me</a>", 'reallystatic');
echo "<h2>Really-Static Plugins</h2>";
do_action ( "rs-aboutyourplugin");
echo '</div>';
#echo str_replace("</form>",' <input type="hidden" id="rs_expertmodehidden" name="rs_expertmode" value="'.loaddaten ( "rs_expertmode").'"></form>',$text);
echo $text;

echo '<form method="post" name="expert"><input type="hidden" name="setexpert" value="1" /><input type="hidden" name="strid" value="'.$_POST[strid].'" /><input type="checkbox" name="rs_expertmode" onclick="document.expert.submit();" id="showexpertid" value="1" '.ison(loaddaten ( "rs_expertmode"),3,'checked ','',1).'> <label for="showexpertid">show Expertsettings</label></form></div>
<script type="text/javascript">showexpert(document.getElementById("showexpertid"));hideshowupload(document.getElementById("fp'.loaddaten ( "rs_save").'"));</script>';
/*if(loaddaten("realstaticdonationid")=="")echo '<br><br><center><!-- Facebook Badge START --><a href="http://www.facebook.com/pages/really-static-Wordpress-Plugin/177723978808" title="really static Wordpress Plugin" target="_TOP"><img src="http://badge.facebook.com/badge/177723978808.4655.159374760.png" width="360" height="101" style="border: 0px;" /></a><!-- Facebook Badge END --></center>';*/
 
?>