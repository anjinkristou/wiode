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

// Get Path
$path = urldecode($_GET['p']);

// Get project root
$rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=" . mysql_real_escape_string($_SESSION['curproject']));
$row = mysql_fetch_array($rs);
$project_root =  $site['absroot'] . "/" . $row['prj_name'];

// Get FTP information

$rs = mysql_query("SELECT * FROM wiode_ftp_connections WHERE ftp_prj_id=" . mysql_real_escape_string($_SESSION['curproject']));
$row = mysql_fetch_array($rs);
$ftp_host = stripslashes($row['ftp_host']);
$ftp_user = stripslashes($row['ftp_user']);
$ftp_password = stripslashes($row['ftp_password']);
$ftp_remote_path = stripslashes($row['ftp_remote_path']);
$ftp_port = stripslashes($row['ftp_port']);

// Open connection
$conn = @ftp_connect($ftp_host, $ftp_port);
$login_result = @ftp_login($conn, $ftp_user, $ftp_password);
ftp_pasv($conn, true);

// Remove project path
$file_path = str_replace($project_root,"",$path);

// Split path into folder
$arrPath = explode("/",$file_path);

$num_steps = count($arrPath);

// Determine root or nested

if($num_steps==2){
    //Upload to root
    ftp_put($conn,($ftp_remote_path . str_replace("/","",$file_path)), $path, FTP_BINARY);
}else{
    // Step through path, building dirs as neccesary
    $cur_src_path = $project_root;
    $cur_dest_path = $ftp_remote_path;
    foreach($arrPath as $v){
        $cur_src_path .= "/" . $v;
        $cur_dest_path .= "/" . $v;
        if (is_dir($cur_src_path)) { // do the following if it is a directory
           if (!@ftp_nlist($conn, $cur_dest_path)) {
               @ftp_mkdir($conn, $cur_dest_path); // create directories that do not yet exist
           } 
        } else {
           @ftp_put($conn, $cur_dest_path, $cur_src_path, FTP_BINARY); // put the files
        }
    }
}



?>
<script>
    $(function(){
        alert('File Uploaded.');
    });
</script>