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

if(!$api_call){
    // Load Config File & Check Token ########################################################
    function changeDir($up_n){
        $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
        for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
    }
    require_once(changeDir(3)."/config.php");
    require_once(changeDir(1)."/check_token.php");
    // #######################################################################################;
}

$action = "";
$prj_id = "";

// Get action type and prj_id
if(isset($_GET['action'])){ $action = mysql_real_escape_string($_GET['action']); }
if(isset($_GET['id'])){ $prj_id = mysql_real_escape_string($_GET['id']); }

$downloadBackup = false;

switch($action){
    
    // Create project ###################################################################
    
    case "create":
        
        if(empty($_GET['create'])){
            // Show form
            ?>
            <p>Enter new project name:</p>
            <input type="text" id="prj_name" autofocus="autofocus" />
            <input type="button" class="bold" value="Create Project" onclick="createProject();" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <?php        
        }else{        
            // Create new project
            $name = mysql_real_escape_string(urldecode($_GET['create']));
            $name = ereg_replace('[^A-Za-z0-9_]','',$name);
            $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_name='$name'");
            if(mysql_num_rows($rs)==0){
                mkdir($site['absroot'] . "/" . urldecode($_GET['create']), 0777, true);
                $rs = mysql_query("INSERT INTO wiode_projects (prj_name) VALUES ('$name')");
                if($api_call){
                    echo(mysql_insert_id());
                }else{
                    echo("<script>$(function(){ location.href='index.php?project=" .  mysql_insert_id() . "'; });</script>");
                }
            }else{
                if($api_call){
                    echo("fail");
                }else{
                    echo("<p>Project Already Exists!</p>");
                }
            }    
        }
        
        break;
        
    // Save FTP Connection ##############################################################
    
    case "saveftp":
        
        // Values
        $ftp_prj_id = $prj_id;
        $ftp_host = mysql_real_escape_string($_POST['ftp_host']);
        $ftp_user = mysql_real_escape_string($_POST['ftp_user']);
        $ftp_password = mysql_real_escape_string($_POST['ftp_password']);
        $ftp_remote_path = mysql_real_escape_string($_POST['ftp_remote_path']);
        $ftp_port = mysql_real_escape_string($_POST['ftp_port']);
        
        // Ensure beginning and trailing slashes on remote_path
        $first_char = substr($ftp_remote_path,0,1);
        $last_char = substr($ftp_remote_path, strlen($path)-1,1);
        if($first_char!="/"){ $ftp_remote_path = "/" . $ftp_remote_path; }
        if($last_char!="/"){ $ftp_remote_path = $ftp_remote_path . "/"; }
        
        if($ftp_remote_path=="//"){ $ftp_remote_path = "/"; }
        
        $rs = mysql_query("SELECT * FROM wiode_ftp_connections WHERE ftp_prj_id=$prj_id");
        if(mysql_num_rows($rs)==0){
            // Create new record
            mysql_query("INSERT INTO wiode_ftp_connections (ftp_prj_id,ftp_host,ftp_user,ftp_password,ftp_remote_path,ftp_port) VALUES ($ftp_prj_id,'$ftp_host','$ftp_user','$ftp_password','$ftp_remote_path',$ftp_port)");
        }else{
            // Update existing record
            mysql_query("UPDATE wiode_ftp_connections SET ftp_host='$ftp_host',ftp_user='$ftp_user',ftp_password='$ftp_password',ftp_remote_path='$ftp_remote_path',ftp_port=$ftp_port WHERE ftp_prj_id=$ftp_prj_id");
        }
        
        break;
        
    // Backup Project ###################################################################
    
    case "backup":
        $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$prj_id");
        if(mysql_num_rows($rs)!=0){
            $row = mysql_fetch_array($rs);
            $prj_name = stripslashes($row['prj_name']);
            $backup_name = $prj_name . "_" . date('m_d_y_his') . ".tar";
            $backup_url = $site['backup_url'] . "/$backup_name";
            
            //Zip($site['absroot'] . "/$prj_name/", $site['backups'] . "/$backup_name");
            $archive = $site['backups'] . "/$backup_name";
            $directory = $site['absroot'] . "/$prj_name"; 
            exec("tar cf $archive $directory");
            exec("gzip $archive");
            
            $downloadBackup = true;    
        }        
        
        break;
        
    // Archive Project ##################################################################
    
    case "archive":
        
        // Set project status to 1
        $rs = mysql_query("UPDATE wiode_projects SET prj_status=1 WHERE prj_id=$prj_id");
        
        break;
        
    // Restore Project (Quiet) #########################################################
    
    case "qrestore":
        
        // Set project status to 0
        $rs = mysql_query("UPDATE wiode_projects SET prj_status=0 WHERE prj_id=$prj_id");
        
        break;
        
    // Restore Project ##################################################################
    
    case "restore":
        
        // Set project status to 0, open project
        $rs = mysql_query("UPDATE wiode_projects SET prj_status=0 WHERE prj_id=$prj_id");
        echo("<script>$(function(){ location.href='index.php?project=$prj_id'; });</script>");    
        
        break;
        
    // Delete Project ###################################################################
        
    case "delete":
    
        // Recursively remove all files and folders
        function rrmdir($dir) { 
           if (is_dir($dir)) { 
             $objects = scandir($dir); 
             foreach ($objects as $object) { 
               if ($object != "." && $object != "..") { if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);  } 
             } reset($objects); rmdir($dir); 
           } 
         }
    
        // Delete all files and folders
        $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$prj_id");
        if(mysql_num_rows($rs)!=0){
            $row = mysql_fetch_array($rs);
            $prj_name = $row['prj_name'];
            rrmdir($site['absroot'] . "/$prj_name");
        }  
        
        // Delete project record
        $rs = mysql_query("DELETE FROM wiode_projects WHERE prj_id=$prj_id");
        // Delete project object records
        $rs = mysql_query("DELETE FROM wiode_objects WHERE obj_project=$prj_id");
        // Delete ftp connections
        $rs = mysql_query("DELETE FROM wiode_ftp_connections WHERE ftp_prj_id=$prj_id");
        
        // Check if user deleted current project...
        if(!$api_call){
            if($prj_id==$_SESSION['curproject']){
                $rs = mysql_query("SELECT prj_id FROM wiode_projects WHERE prj_status=0 ORDER BY prj_name");
                if(mysql_num_rows($rs)!=0){
                    $row = mysql_fetch_array($rs);
                    $_SESSION['curproject'] = $row['prj_id'];
                    echo("<script>$(function(){ changeProject(" . $row['prj_id'] . ") });</script>");
                }
            }
        }
        
        break;
    
}

if(!$api_call){ // Don't show scripts if this is API-induced function

if($downloadBackup==true){
?>
<script>
    $(function(){ location.href='<?php echo($backup_url); ?>.gz'; });
</script>
<?php
}
?>
<script type="text/javascript">

$(function(){
    
    $('#prj_name').keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { createProject(); }
    });
    
    $("#prj_name").keypress(function(e){
        var code = e.which || e.keyCode;
        // 65 - 90 for A-Z and 97 - 122 for a-z 95 for _ 45 for - 46 for .
        if (!((code >= 65 && code <= 90) || (code >= 97 && code <= 122) || (code >= 37 && code <= 40) || (code >= 48 && code <= 57) || 
        (code >= 96 && code <= 105) || code == 95 || code == 46 || code == 45)){
            e.preventDefault();
         }
    });
    
});    
    
function createProject(){
    var name = $('#prj_name').val();
    loadModal('system/php/modules/project_actions.php?action=create&create='+name+'&id=new','Create Project');    
}
    
</script>
<div class="clear"></div>

<?php

}

?>