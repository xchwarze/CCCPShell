<?php

function mLink($t, $o, $e = '', $m = true){
    if ($m) $o .= ';return false;';
    return "<a href='#' onclick='$o' $e>$t</a>";
}

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


	$newStr = preg_replace('!/\*.*?\*/!s', '', $newStr);
	$newStr = preg_replace('/\n\s*\n/', "\n", $newStr);
	//$newStr = preg_replace("/(\s{2,})/", " ", $newStr);

	return $newStr;
}

function packer_pack_js($str){
	$packer = new JavaScriptPacker($str, 0, true, false);
	return $packer->pack();
}
