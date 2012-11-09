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

$id = str_replace("prj_", "", mysql_real_escape_string($_GET['id']));

$rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_id=$id");
$row=mysql_fetch_array($rs);
$prj_name = stripslashes($row['prj_name']);
$ar=getDirectorySize($site['absroot'] . "/" . stripslashes($row['prj_name'])); 
$prj_total_size = sizeFormat($ar['size']); 
$prj_num_files = $ar['count']; 
$prj_num_directories = $ar['dircount'];

?>
<hr />
<p style="margin-top: 0;">Project Information</p>
<table class="data">
    <tr>
        <th>Project Name</th>
        <th width="5">Size</th>
        <th width="5">Folders</th>
        <th width="5">Files</th>
    </tr>
    <tr>
        <td><?php echo($prj_name); ?></td>
        <td><?php echo($prj_total_size); ?></td>
        <td><?php echo($prj_num_directories); ?></td>
        <td><?php echo($prj_num_files); ?></td>
    </tr>    
</table>

<p>FTP Settings</p>
<?php                                
$ftp_host = "";
$ftp_user = "";
$ftp_password = "";
$ftp_remote_path = "";
$ftp_port = "21";

$rsFTP = mysql_query("SELECT * FROM wiode_ftp_connections WHERE ftp_prj_id=$id");
if(mysql_num_rows($rsFTP)!=0){
    $row = mysql_fetch_array($rsFTP);
    $ftp_host = stripslashes($row['ftp_host']);
    $ftp_user = stripslashes($row['ftp_user']);
    $ftp_password = stripslashes($row['ftp_password']);
    $ftp_remote_path = stripslashes($row['ftp_remote_path']);
    $ftp_port = $row['ftp_port'];
}
?>
<table class="data">
<tr>
    <th>Host</th>
    <th>User</th>
    <th>Password</th>
    <th>Path (<a id="ftp_browse">Browse</a>)</th>
    <th>Port</th>
</tr>
<tr>
    <td><input type="text" id="ftp_host" value="<?php echo($ftp_host); ?>" /></td>
    <td><input type="text" id="ftp_user" value="<?php echo($ftp_user); ?>" /></td>
    <td><input type="password" id="ftp_password" value="<?php echo($ftp_password); ?>" /></td>
    <td><input type="text" id="ftp_remote_path" value="<?php echo($ftp_remote_path); ?>" /></td>
    <td><input type="text" id="ftp_port" value="<?php echo($ftp_port); ?>" /></td>
</tr>
</table>

<div id="ftp_browser" style="display: none;"></div>

<hr />

<script type="text/javascript">

    $(function(){
    
        $('#ftp_browse').click(function(){ browseFTP(); });
        
        $('#btn_backup, #btn_archive, #btn_restore, #btn_delete, #btn_save_ftp').unbind('click');
    
        $('#btn_backup').click(function(){ backupProject(); });
        $('#btn_archive').click(function(){ archiveProject(); });
        $('#btn_restore').click(function(){ restoreProject(); });
        $('#btn_delete').click(function(){ deleteProject(); });
        $('#btn_save_ftp').click(function(){ saveFTP(); });
        
        $('#ftp_host, #ftp_user, #ftp_password, #ftp_remote_path, #ftp_port').bind('change keypress',function(){
            $('#btn_save_ftp').show();
        });
    
    });
    
    function backupProject(){
        $('#processor').load('system/php/modules/project_actions.php?action=backup&id=<?php echo($id) ?>');
    }
    
    function archiveProject(){
        var answer = confirm("Are you sure you wish to archive this project?");
        if (answer){
            $.get('system/php/modules/project_actions.php?action=archive&id=<?php echo($id) ?>', function(){ returnToList(); } );
        }
    }
    
    function restoreProject(){
        var answer = confirm("Are you sure you wish to restore this project to active status?");
        if (answer){
            $.get('system/php/modules/project_actions.php?action=qrestore&id=<?php echo($id) ?>', function(){ returnToList(); } );
        }
    }
    
    function deleteProject(){
        var answer = confirm("Are you sure you wish to delete this project? This action is permanent and cannot be undone.");
        if (answer){
            $('#processor').load('system/php/modules/project_actions.php?action=delete&id=<?php echo($id) ?>', function(){ reloadProjectSelector(); returnToList(); });
        }
    }
    
    function browseFTP(){
        var ftphost = $('#ftp_host').val();
        var ftpuser = $('#ftp_user').val();
        var ftppassword = $('#ftp_password').val();
        var ftpremotepath = $('#ftp_remote_path').val();
        var ftpport = $('#ftp_port').val();
        $.post('system/php/modules/settings_project_ftp_util.php',{ ftp_host: ftphost , ftp_user: ftpuser, ftp_password: ftppassword, ftp_remote_path: ftpremotepath, ftp_port: ftpport },function(data){
            $('#ftp_browser').slideDown(200).html(data);
        });
    }
    
    function saveFTP(){
        var ftphost = $('#ftp_host').val();
        var ftpuser = $('#ftp_user').val();
        var ftppassword = $('#ftp_password').val();
        var ftpremotepath = $('#ftp_remote_path').val();
        var ftpport = $('#ftp_port').val();
        
        // POST data
        $.post("system/php/modules/project_actions.php?action=saveftp&id=<?php echo($id); ?>", { ftp_host: ftphost , ftp_user: ftpuser, ftp_password: ftppassword, ftp_remote_path: ftpremotepath, ftp_port: ftpport },
         function() {
           $('#btn_save_ftp').hide();
         });
    }

</script>
