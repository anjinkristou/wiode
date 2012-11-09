<?php

/*
 * This file is part of the WIODE Web IDE Application, developed and 
 * distributed by Kent Safranski and the WIODE team.
 * <http://www.wiode.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Load Config File & Check Token ########################################################
function changeDir($up_n){
    $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
    for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
}
require_once(changeDir(3)."/config.php");
require_once(changeDir(1)."/check_token.php");
// #######################################################################################

// Get object data
$obj_id = mysql_real_escape_string($_GET['id']);
$obj_path = getPath($site,$obj_id,"");

$rs = mysql_query("SELECT obj_name FROM wiode_objects WHERE obj_id=$obj_id");
$row = mysql_fetch_array($rs);
$ext = getExt($row['obj_name']);

$file = str_replace($site['absroot'],"",$obj_path);
$date = date('m-d-Y \a\t H:i') . ' UTC';

$content = str_replace("<","<",file_get_contents($obj_path));
$content = str_replace("<?php", "<?php", $content);

?>
<!doctype html>
<html lang="en">
<head>
<title>WIODE Printout</title>

<style>
    html, body { width: 100%; height: 100%; margin: 0 auto; padding: 0; line-height: 100%; font: normal 11px Courier, monospace; }
    #title { padding: 10px 0; font-size: 13px; font-weight: bold; border-bottom: 1px solid #ccc; }
    #date { float: right; }
    pre { width: 100%; height: 100%; word-wrap: break-word; color: #333; }
    ol.code { margin: 0; padding: 0 0 0 20px; background: #e8e8e8; }
    ol.code li { margin: 0 0 0 20px; padding: 3px 10px 3px 10px; line-height: 100%; word-wrap: break-word; border-left: 1px solid #ccc; color: #999; background: #fff; }
    ol.code li pre { display: block; margin: 0; padding: 0; color: #333; }
</style>

</head>
<body>
<div id="title"><div id="date"><?php echo($date); ?></div><?php echo($file); ?></div>
<pre><?php echo(htmlspecialchars($content)); ?></pre>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script>!window.jQuery && document.write(unescape('%3Cscript src="system/js/jquery-1.6.1.min.js"%3E%3C/script%3E'));</script>
<script>

    $(function(){
        $("pre").html(function(index, html) {
            output = html.replace(/^(.*)$/mg, "<li class=\"line\"><pre>$1</pre></li>");
        });
        
        $('pre').remove();
        $('body').append('<ol class="code">'+output+'</ol>');
        
        setTimeout("window.print();",2000);
    });

</script>
</body>
</html>