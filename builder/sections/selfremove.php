<?php
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