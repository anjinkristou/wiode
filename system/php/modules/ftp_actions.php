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

$GLOBALS['scriptDom']="";

// Start Timer
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;

$obj_id = mysql_real_escape_string($_GET['id']);
$ftp_connection = true;
$upload_complete = false;
$download_complete = false;

// Log function #########################################################################

function logFTP($output,$site,$project_path){
    $fh = fopen($_SESSION['ftp_log'], 'a');
    $output = str_replace($project_path,"",$output);
    $output = str_replace(" ","&nbsp;",$output);
    $output = str_replace("//","/",$output);
    fwrite($fh, "<li>".$output."</li>");
}

// Get Object Data ######################################################################

$obj_project = mysql_real_escape_string($_SESSION['curproject']);
$rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$obj_project");
$row = mysql_fetch_array($rs);
$project_path = $site['absroot'] . "/" . $row['prj_name'] . "/";

if($obj_id=='root'){
    // Project root
    $obj_type = 0;
    $obj_id = 0;
    $obj_path = $project_path;
    $obj_rel_path = "";
    $obj_name = "";
}else{
    $rs = mysql_query("SELECT * FROM wiode_objects WHERE obj_id=$obj_id");
    $row = mysql_fetch_array($rs);
    $obj_project = $row['obj_project'];
    $obj_name = stripslashes($row['obj_name']);
    $obj_type = $row['obj_type'];
    $obj_parent = $row['obj_parent'];
    $obj_path = getPath($site,$obj_id,"");
    $obj_rel_path = getRelPath($obj_id,"");
}

// Remote File differntiation ##########################################################

function ftp_is_dir($conn,$object){
    $x = ftp_size($conn,$object);
    if($x!=-1){ return false; }else{ return true; }
}
function ftp_is_file($conn,$object){
    $x = ftp_size($conn,$object);
    if($x!=-1){ return true; }else{ return false; }
}

// Get FTP Connection information #######################################################

$rs = mysql_query("SELECT * FROM wiode_ftp_connections WHERE ftp_prj_id=$obj_project");
if(mysql_num_rows($rs)!=0){
    $row = mysql_fetch_array($rs);
    $ftp_host = stripslashes($row['ftp_host']);
    $ftp_user = stripslashes($row['ftp_user']);
    $ftp_password = stripslashes($row['ftp_password']);
    $ftp_remote_path = stripslashes($row['ftp_remote_path']);
    $ftp_port = stripslashes($row['ftp_port']);
}else{
    $ftp_connection = false;
}

if($ftp_connection==true){
        
    // Process FTP request

    // Load FTP Object ######################################################################
    
    $obj_remote_path = $ftp_remote_path . $obj_rel_path;
    
    // Open FTP connection
    if($conn = ftp_connect($ftp_host, $ftp_port)){
        logFTP("STAT:   Connected to Remote Server.",$site,$project_path);
        // Authenticate
        if($login_result = @ftp_login($conn, $ftp_user, $ftp_password)){
            logFTP("STAT:   Authentication Successful.",$site,$project_path);
        }else{
            logFTP("!ERROR: Authentication Failed.",$site,$project_path);
        }
    }else{
        logFTP("!ERROR: Cannot Connect to Remote Server.",$site,$project_path);
    }
    ftp_pasv($conn, true);
    
    switch($_GET['action']){
    
        case 'upload':
        
            // PROCESS FTP UPLOAD REQUEST ##################################################
        
            if($login_result!=1){
                // No connection
            }else{
            
                $path_exists = false;
                
                $parent_path = str_replace($obj_name,"",$obj_remote_path);
                
                if(@ftp_chdir($conn, $parent_path)){ $path_exists=true; }
                
                if($path_exists==false){
                    logFTP("!ERROR: Remote path does not exist - $parent_path",$site,$project_path);
                }else{
                
                    if($obj_type==2){
                        // Single file upload
                        logFTP("PUT:    $obj_path",$site,$project_path);
                        if(!ftp_put($conn, $obj_remote_path, $obj_path, FTP_BINARY)){
                            logFTP("!ERROR: Cannot PUT $obj_path",$site,$project_path);
                        }
                        $upload_complete = true;
                    }else{
                        // Folder upload (recursive)
                        
                        function ftp_putAll($conn_id, $src_dir, $dst_dir, $site, $project_path) {
                           $d = dir($src_dir);
                           while($file = $d->read()) { // do this for each file in the directory
                               if ($file != "." && $file != "..") { // to prevent an infinite loop
                                   if (is_dir($src_dir."/".$file)) { // do the following if it is a directory
                                       if (!@ftp_nlist($conn_id, $dst_dir."/".$file)) {
                                           logFTP("MKDIR:  $src_dir/$file",$site,$project_path);
                                           if(!ftp_mkdir($conn_id, $dst_dir."/".$file)){
                                               logFTP("!ERROR: Cannot MKDIR $src_dir/$file",$site,$project_path);
                                           }
                                       }
                                       ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file,$site,$project_path); // recursive part
                                   } else {
                                       logFTP("PUT:    $src_dir/$file",$site,$project_path);
                                       if(!ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY)){
                                           logFTP("!ERROR: Cannot PUT $src_dir/$file",$site,$project_path);
                                       }
                                   }
                               }
                           }
                           $d->close();
                        }
        
                        // Build parent
                        if($obj_name!=""){
                            // Not root, check if dir exists...
                            if(@ftp_chdir($conn, $obj_remote_path)==false){
                                // Create the directory...
                                logFTP("MKDIR:  $obj_remote_path",$site,$project_path);
                                if(!ftp_mkdir($conn, $obj_remote_path)){
                                    logFTP("!ERROR: Cannot MKDIR $obj_remote_path",$site,$project_path);
                                }
                            }
                        }
                        
                        // Put files and folders
                        ftp_putAll($conn, $obj_path, $obj_remote_path,$site,$project_path);
                        $upload_complete = true;
                    }
                }
            }
        
            break;
        
        
        case 'download':
        
            // PROCESS FTP DOWNLOAD REQUEST ################################################
            
            if($obj_type==2){
                // Single file download
                logFTP("GET:    /$obj_path",$site,$project_path);
                if(!ftp_get($conn, $obj_path, $obj_remote_path, FTP_BINARY)){
                    logFTP("!ERROR: Cannot GET /$obj_path",$site,$project_path);
                }
                $download_complete = true;
            }else{ 
                // Download folder, subs, and files
                function scanRemote($conn, $path, $parent, $ftp_remote_path, $project_path, $site){
                    $raw_contents = ftp_nlist($conn, $path);
                    sort($raw_contents);
                    $arr_contents = array();
                    $i = 0;
                    
                    // Group folders
                    foreach($raw_contents as $v){
                        $v = basename($v);
                        if($v!="." && $v!=".." && ftp_is_dir($conn,$path."/".$v)){
                           $arr_contents[$i]=$v;
                           $i++;
                        }           
                    }
                    // Group files
                    foreach($raw_contents as $v){
                        $v = basename($v);
                        if($v!="." && $v!=".." && ftp_is_file($conn,$path."/".$v)){
                           $arr_contents[$i]=$v;
                           $i++;
                        }
                    }
                    
                    foreach($arr_contents as $v){
                        if($v!="." && $v!=".."){
                            $v = basename($v);
                            $remote_path = $path . "/" . $v;
                            $local_path = str_replace($ftp_remote_path,$project_path,$remote_path);
                            if(ftp_is_dir($conn,$path."/".$v)){
                                // Folder
                                if(!file_exists($local_path)){
                                    // Make directory
                                    logFTP("MKDIR:  /$local_path",$site,$project_path);
                                    if(!mkdir($local_path)){
                                        logFTP("!ERROR: Cannot MKDIR /$local_path",$site,$project_path);
                                    }       
                                    // Create Object
                                    $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES (" . $_SESSION['curproject'] . ",'" . $v . "',1,$parent)");
                                    $new_parent = mysql_insert_id();
                                }else{
                                    $rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_name='$v' AND obj_parent=$parent");
                                    $row = mysql_fetch_array($rs);
                                    $new_parent = $row['obj_id'];
                                }
                                // Recurse
                                scanRemote($conn, $remote_path."/", $new_parent, $ftp_remote_path, $project_path, $site);          
                            }else{
                                echo("Local: $local_path, Remote: $remote_path\n");
                                if(!file_exists($local_path)){
                                    // Get file
                                    logFTP("GET:    /$local_path",$site,$project_path);
                                    if(ftp_get($conn, $local_path, $remote_path, FTP_BINARY)){       
                                        // Create Object
                                        $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES (" . $_SESSION['curproject'] . ",'" . $v . "',2,$parent)");                             
                                    }else{
                                        logFTP("!ERROR: Cannot GET /$local_path",$site,$project_path);
                                    }
                                }else{
                                    logFTP("GET:    /$local_path",$site,$project_path);
                                    if(!ftp_get($conn, $local_path, $remote_path, FTP_BINARY)){
                                        logFTP("!ERROR: Cannot GET /$local_path",$site,$project_path);
                                    }
                                }
                            }
                        }
                    }
                }
                scanRemote($conn,$obj_remote_path,$obj_id,$ftp_remote_path,$project_path, $site);
            }
        
            break;
        
        default:
        
            $doNothing = true;
        
    }
                    
    // Close connection
    unset($conn);
      
}else{
    // No FTP connection set for this project
    logFTP("!ERROR: NO FTP CONNECTION CONFIGURED",$site,$project_path);
}

// PROCESS COMPLETE

// Display time
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = round(($endtime - $starttime),5); 

// Write process-complete msg to log
logFTP("STAT:   Process Completed ($totaltime seconds).",$site,$project_path);

if($GLOBALS['scriptDom']){ 

    logFTP("SYSTEM: Loading New Objects Into File Manager",$site,$project_path);

    ?>
    <script>
        $(function(){
        <?php echo($GLOBALS['scriptDom']); ?>
        });
    </script>
    <?php
    
    logFTP("SYSTEM: Process Complete",$site,$project_path);
}
?>