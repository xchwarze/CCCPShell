<?php
# System variables
$config['charset'] = 'utf8'; //'utf-8', 'big5', 'gbk', 'iso-8859-2', 'euc-kr', 'euc-jp'
$config['date'] = 'd/m/Y';
$config['datetime'] = 'd/m/Y H:i:s';
$config['hd_lines'] = 16;   //lines in hex preview file
$config['hd_rows'] = 32;    //16, 24 or 32 bytes in one line
$config['FMLimit'] = 100;   //file manager item limit. false = No limit
$config['SQLLimit'] = 50;   //sql manager result limit.
$config['checkBDel'] = true;//Check Before Delete: true = On 
$config['consNames'] = array('post'=>'dsr', 'slogin'=>'cccpshell', 'sqlclog'=>'conlog'); //Constants names
$config['sPass'] = '775a373fb43d8101818d45c28036df87'; // md5(pass) //cccpshell
$config['rc4drop'] = 123;  //drop size
