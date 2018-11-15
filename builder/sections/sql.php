<?php
    // SQL based on b374k by DSR!
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
        foreach(explode('{;}', $p['code']) as $query){
            if (trim($query) !== ''){
                $query = str_replace(array('{start}', '{limit}', '{oraclelimit}'), array($start, $config['SQLLimit'], $oracleLimit), $query);
                $sBuff .= '<hr /><p><b>' . tText('sq8', 'Executed') . ':</b> ' . hsc($query) . ';&nbsp;&nbsp;';
                $res = sql_query($p['type'], $query, $con);
                if ($res !== false && !is_bool($res)){
                    $tmp = "<table id='sort' class='explore sortable' style='width:100%;'><tr>";
                    
                    $t = sql_num_fields($p['type'], $res, $con);
                    for ($i = 0; $i < $t; $i++)
                        $tmp .= '<th class="touch">' . @hsc(sql_field_name($p['type'], $res, $i)) . '</th>';
                    $tmp .= '</tr>';
                    
                    $c = 0;
                    
                    while($rows = sql_fetch_data($p['type'], $res)){
                        $c++;
                        $tmp .= '<tr>';
                        foreach($rows as $r)
                            $tmp .= '<td>' . @hsc($r) . '</td>';
                        $tmp .= '</tr>';
                    }
                    
                    $pag = genPaginator($p['pg'], ($c < $config['SQLLimit'] ? $p['pg'] : -1), false) . '';
                    $sBuff .= "<b>[ ok ]</b></p><br>{$pag}<br>{$tmp}</table><br>{$pag}<br>";
                    unset($c, $tmp);
                } else if ($res === false)
                    $sBuff .= "<b>[ ERROR ] ({$res})</b></p><br>";
                else
                    $sBuff .= "<b>[ ok ] ({$res})</b></p><br>";
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
                <p>' . mSubmit(tText('go', 'Go!'), 'dbexec(euc(d.getElementById(&quot;code&quot;).value))') . '&nbsp;&nbsp;
                ' . tText('sq4', 'Separate multiple commands with') . ' <span>{;}</span> ' . tText('sq9', 'Variables for use in pagination') . ' <span>{start}, {limit}, {oraclelimit}</span></p><br>
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
                                            
                                        $sBuff .= '<tr><td><a href="#" onclick="dbexec(euc(\'' . $dumptbl . '\'));return false;">' . $tables . '</a></td></tr>';
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
                    mInput('host', 'localhost', '<span id="sh">' . tText('sq7', 'Host') . '</span>', 1, '', 'style="width: 99%;"') . 
                    '<span id="su">' . mInput('user', '', tText('sq0', 'Username'), 1, '', 'style="width: 99%;"')  . '</span>' . 
                    '<span id="sp">' . mInput('pass', '', tText('sq1', 'Password'), 1, '', 'style="width: 99%;"')  . '</span>' . 
                    '<span id="so">' . mInput('port', '', tText('sq2', 'Port (optional)'), 1, '', 'style="width: 99%;"') . '</span>' .
                    mSelect('type', $sqllist, false, false, 'dbengine(this)', tText('sq3', 'Engine')) . 
                    mHide('me', 'sql') . mHide('sqlinit', 'init') . mHide('jseval', 'dbengine(d.getElementById("type"));dbhistory("v");') . 
                    '<center>' . mSubmit(tText('go', 'Go!'), 'ajaxLoad(serialize(d.forms[0]));', 1) . '</center>' .
                    '</form><br>Or use www.adminer.org</div>
            </div>';
    }