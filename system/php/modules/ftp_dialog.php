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

// Logging ################################################
$log_path = $site['absroot'] . "/_ftp_logs/";
$log_name = "ftp_log_" . date("m-d-y_H-i-s") . ".html";
// Build dir in not_exists
if(!file_exists($log_path)){ mkdir($log_path); }

// Cleanup old log files
$max_age = 10; // in days...
if ($handle = opendir($log_path)) {
    while (false !== ($filename = readdir($handle))){ 
        if ($filename != '.' && $filename != '..' && eregi("ftp_log_", $filename) && filemtime($log_path.$filename) < strtotime ("-$max_age days") ){
            unlink($log_path.$filename) ;
        }
    }
    closedir($handle); 
}

$quick_upload_log = "";
if(!empty($_GET['quick'])){ $quick_upload_log = "<li>STAT:&nbsp;&nbsp;&nbsp;File Saved (Quick Upload).</li>"; }

// Build initial log
$fh = fopen($log_path . $log_name, 'w');
    fwrite($fh, "<li>STAT:&nbsp;&nbsp;&nbsp;Starting FTP Process...</li>$quick_upload_log");
$_SESSION['ftp_log'] = $log_path . $log_name;

$reloadDirectory = "";
// Determine if reload is needed (folder download)
if($_GET['action']=="download"){
    $obj_id = $_GET['id'];
    if($obj_id=='root'){
        $reloadDirectory = "loadDirectory(0);";
    }else{
        $rs = mysql_query("SELECT obj_type FROM wiode_objects WHERE obj_id=$obj_id");
        $row = mysql_fetch_array($rs);
        if($row['obj_type']==1){ $reloadDirectory = "loadDirectory($obj_id);"; }
    }
}

?>
<ul id="ftp_log">
    <li style="width: 335px; word-wrap: break-word;" id="processing_wait">Processing</li>
</ul>

<div style="float: right; text-align: right; margin: 15px 5px 0 0; line-height: 100%;">
    <input type="checkbox" id="auto_close" checked="yes" value="yes"><label for="auto_close" style="display: inline; font-weight: normal; padding: 0 0 0 3px; margin; 0;">Auto-Close</label>
</div>

<input type="button" id="ftp_close" value="Close" class="bold" onclick="closeDialog();" />
<input type="button" id="ftp_log_btn" style="display: none;" value="Open Log File" onclick="openLog();" />



<script type="text/javascript">

    $(function(){
        
        // Init process
        $('#processor').load('system/php/modules/ftp_actions.php?action=<?php echo($_GET['action']); ?>&id=<?php echo($_GET['id']); ?>',function(){
            setTimeout( function() { clearInterval(logger); }, 200 );
            $('#ftp_close').focus();
            $('#ftp_status').hide(); 
            // Show log
            $('#ftp_log').load('<?php echo($site["url"]."/_ftp_logs/$log_name"); ?>',function(){
                var objDiv = document.getElementById("ftp_log");
                objDiv.scrollTop = objDiv.scrollHeight;
            });
            $('#ftp_log_btn').show();
            <?php echo($reloadDirectory); ?>
            
            // Auto-close
            setTimeout(function(){
                if($('#auto_close').is(':checked')){ unloadModal(); }
            },1000);
            
        });
        // Load log on interval
        logger = setInterval("loadLog()",200);
    });
        
    function loadLog(){ 
        $('#processing_wait').html($('#processing_wait').html()+'.');
        var objDiv = document.getElementById("ftp_log");
        objDiv.scrollTop = objDiv.scrollHeight;
    }
    
    function closeDialog(){
        unloadModal();
    }
    
    function openLog(){
        popup('<?php echo("system/php/modules/ftp_log_viewer.php?log=$log_name"); ?>','FTP Log',500,500);
    }

</script>