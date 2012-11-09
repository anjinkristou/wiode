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

$obj_id = mysql_real_escape_string($_GET['i']);

// Get object type
if($obj_id=="root"){
    $obj_type = 0;
}else{
    $rs = mysql_query("SELECT obj_name,obj_type FROM wiode_objects WHERE obj_id=$obj_id");
    $row = mysql_fetch_array($rs);
    $obj_name = stripslashes($row['obj_name']);
    $obj_type = $row['obj_type'];
    $obj_ext = getExt($obj_name);
}


?>
<ul>
<?php

// Folder or Root
if($obj_type==0 || $obj_type==1){
?>
    <li><span class="ico_add" onclick="loadModal('system/php/modules/object_actions.php?action=create&id=<?php echo($obj_id); ?>','Create Object')">Create New</span></li>
    <li><span class="ico_add_files" onclick="loadModal('system/php/modules/object_actions.php?action=upload&id=<?php echo($obj_id); ?>','Upload Files')">Add Files</span></li>
<?php
}

?>
    <li><span class="ico_duplicate" onclick="loadModal('system/php/modules/object_actions.php?action=duplicate&id=<?php echo($obj_id); ?>','Create Copy')">Create Copy</span></li>
<?php

// No duplicate/rename/delete for root
if($obj_type!=0){
?>
    <li><span class="ico_rename" onclick="loadModal('system/php/modules/object_actions.php?action=rename&id=<?php echo($obj_id); ?>','Rename Object')">Rename</span></li>
    <li><span class="ico_delete" onclick="loadModal('system/php/modules/object_actions.php?action=delete&id=<?php echo($obj_id); ?>','Delete Object')">Delete</span></li>
<?php
}

// Folder
if($obj_type==1){
?>
    <li class="spacer"></li>
    <li><span class="ico_rescan" onclick="loadDirectory(<?php echo($obj_id); ?>);">Rescan</span></li>
<?php
}

// Root
if($obj_type==0){
?>
    <li><span class="ico_rescan" onclick="loadDirectory(0);">Rescan</span></li>
<?php    
}

// Check if FTP is configured
$rs = mysql_query("SELECT ftp_id FROM wiode_ftp_connections WHERE ftp_prj_id=" . $_SESSION['curproject']);
if(mysql_num_rows($rs)!=0){
?>
    <li class="spacer"></li>
    <li><span class="ico_upload" onclick="loadModal('system/php/modules/ftp_dialog.php?action=upload&id=<?php echo($obj_id); ?>','FTP Upload');">FTP Upload</span></li>
    <li><span class="ico_download" onclick="loadModal('system/php/modules/ftp_dialog.php?action=download&id=<?php echo($obj_id); ?>','FTP Download');">FTP Download</span></li>
<?php
}

// Show 'history' option
if($obj_type==2 && in_array(strtolower($obj_ext), $arrEditable)){
?>
    <li class="spacer"></li>
    <li><span class="ico_history" onclick="loadModal('system/php/modules/object_history.php?id=<?php echo($obj_id); ?>','Object History',700);">File History</span></li>
<?php
}
?>
</ul>