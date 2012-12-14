<?php
global $wpdb;
RS_LOG("Init Really-static install");
RS_LOG("init Database");
$querystr = "DROP TABLE IF EXISTS `".REALLYSTATICDATABASE."`;CREATE TABLE `" .REALLYSTATICDATABASE."` (`url` CHAR( 32 ) NOT NULL ,	`content` CHAR( 32 ) NOT NULL,`datum` INT(11) NOT NULL ) ;";
RS_LOG($querystr);
$wpdb->get_results ( $querystr, OBJECT );
RS_LOG("init defaults");
add_option($name,"local");
add_option ( 'dateierweiterungen', array('.jpg' =>1,'.png'=>1 ,'.jpeg'=>1 ,'.gif'=>1 ,'.swf'=>1 ,'.gz'=>1,'.tar'=>1 ,'.zip'=>1 ,'.pdf'=>1 ), '', 'yes' );
add_option ( "rs_expertmode",0, '', 'yes' );
add_option ( "rs_stupidfilereplaceA",false, '', 'yes' );
add_option ( "rs_stupidfilereplaceB",false, '', 'yes' );
add_option ( "rs_stupidfilereplaceC",false, '', 'yes' );
add_option ( "rs_stupidfilereplaceD",false, '', 'yes' );
add_option ( "rs_counter",0, '', 'yes' );
add_option ( "rs_firstTime",RSVERSION.RSSUBVERSION, '', 'yes' );
add_option ( 'makestatic-a1', array(array("%indexurl%","")), '', 'yes' );
add_option ( 'makestatic-a2', array(array("%tagurl%","")), '', 'yes' );
add_option ( 'makestatic-a3', array(array("%caturl%","")), '', 'yes' );
add_option ( 'makestatic-a4', array(array("%authorurl%","")), '', 'yes' );
add_option ( 'makestatic-a5', array(array("%dateurl%","")), '', 'yes' );
add_option ( 'makestatic-a6', array(array("%commenturl%","")), '', 'yes' );
add_option ( 'makestatic-a7', array(), '', 'yes' );
add_option('realstaticposteditcreatedelete',array(array("%postname%","")),'','');
add_option ( 'realstaticurlrewriteinto', array(), '', 'yes' );
add_option ( 'realstaticlokalerspeicherpfad', REALLYSTATICHOME.'static/', '', 'yes' );
if($name=="realstaticnonpermanent"){
	if(get_option('permalink_structure')=="")add_option('realstaticnonpermanent',1,'','');
	else add_option('realstaticnonpermanent',0,'','');
}

add_option ( 'realstaticlocalpath', '', '', 'yes' );
add_option ( 'realstaticsubpfad', '', '', 'yes' );
add_option ( 'realstaticremoteurl', REALLYSTATICURLHOME."static/", '', 'yes' );

add_option ( 'realstaticlocalurl', get_option('home')."/", '', 'yes' );

add_option ( 'realstaticremotepath', "/", '', 'yes' );
add_option ( 'realstaticftpserver', "", '', 'yes' );
add_option ( 'realstaticftpuser', "", '', 'yes' );
add_option ( 'realstaticftppasswort', "", '', 'yes' );
add_option ( 'realstaticftpport', "21", '', 'yes' );

add_option ( 'realstaticremotepathsftp', "/", '', 'yes' );
add_option ( 'realstaticsftpserver', "", '', 'yes' );
add_option ( 'realstaticsftpuser', "", '', 'yes' );
add_option ( 'realstaticsftppasswort', "", '', 'yes' );
add_option ( 'realstaticsftpport', "22", '', 'yes' );

add_option ( 'rs_save', "local", '', 'yes' );
add_option ( 'realstaticdesignlocal', get_bloginfo('template_directory')."/", '', 'yes' );
add_option ( 'realstaticdesignremote', get_bloginfo('template_directory')."/", '', 'yes' );
add_option('realstaticeverytime',array(),'','');
add_option('realstaticpageeditcreatedelete',array(),'','');
add_option('realstaticcommenteditcreatedelete',array(),'','');
add_option('realstaticeveryday',array(),'','');

add_option('realstaticdonationid',"",'','');

add_option('maketagstatic',1,'','');
add_option('makecatstatic',1,'','');
add_option('makeauthorstatic',1,'','');
add_option('makedatestatic',1,'','');
add_option("makedatetagstatic",1,'','');
add_option("makedatemonatstatic",1,'','');
add_option("makedatejahrstatic",1,'','');

add_option('makeindexstatic',1,'','');

add_option('rs_hide_adminpannel',array(),'','');
add_option( 'rs_showokmessage',array());
RS_LOG("init defaults done");
?>