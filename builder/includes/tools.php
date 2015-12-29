<?php

//html

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


//builder
function packer_write_file($file, $content){
	if($fh = @fopen($file, "wb")){
		if(fwrite($fh, $content)!==false){
			if(!class_exists("ZipArchive")) return true;
			
			if(file_exists($file.".zip")) unlink ($file.".zip");
			$zip = new ZipArchive();
			$filename = "./".$file.".zip";

			if($zip->open($filename, ZipArchive::CREATE)!==TRUE) return false;
			$zip->addFile($file);
			$zip->close();
			return true;
		}
	}
	return false;
}

function packer_html_safe($str){
	return htmlspecialchars($str, 2 | 1);
}

function packer_output($str){
	header("Content-Type: text/plain");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	echo $str;
	die();
}

function packer_strips($str){
	$newStr = '';

	$commentTokens = array(T_COMMENT);
	if(defined('T_DOC_COMMENT')) $commentTokens[] = T_DOC_COMMENT;
	if(defined('T_ML_COMMENT'))	$commentTokens[] = T_ML_COMMENT;

	$tokens = token_get_all($str);
	foreach($tokens as $token){
		if (is_array($token)) {
			if (in_array($token[0], $commentTokens)) 
				continue;

			$token = $token[1];
		}
		
		$newStr .= $token;
	}

	$newStr = preg_replace("/(\s{2,})/", " ", $newStr);
	return $newStr;
}

function packer_pack_js($str){
	$packer = new JavaScriptPacker($str, 0, true, false);
	return $packer->pack();
}
