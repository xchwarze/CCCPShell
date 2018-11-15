<?php
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