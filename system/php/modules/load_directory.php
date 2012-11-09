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
$parent = urldecode($_GET['p']);
$prj_id = mysql_real_escape_string($_SESSION['curproject']);

if($parent==0){
    // Get project root
    $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$prj_id");
    $row = mysql_fetch_array($rs);
    $path =  $site['absroot'] . "/" . $row['prj_name'];
}else{
    // Get current path
    $path = getPath($site,mysql_real_escape_string($_GET['p']),"",0);
}

// Cleanup
$rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_project=$prj_id AND obj_parent=$parent");
if(mysql_num_rows($rs)!=0){
    while($row=mysql_fetch_array($rs)){
        if(!file_exists(getPath($site,$row['obj_id'],""))){
            mysql_query("DELETE FROM wiode_objects WHERE obj_id=" . $row['obj_id']);
        }
    }
}

// Loop out files
echo("<ul>");

$arrDirs = array();
$arrFiles = array();


// Build arrays

if ($handle = opendir($path)) {
  while (($file = readdir($handle)) !== false) {
      if($file!="." && $file!=".."){
          if(is_dir($path . "/" . $file)){
                // Handle directory
                $rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_project=$prj_id AND obj_name='$file' AND obj_parent=$parent");
                if(mysql_num_rows($rs)==0){
                    // Create object in database
                    $rsCreate = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES ($prj_id,'$file',1,$parent)");
                    $arrDirs[] = array($file,"<li class=\"folder\"><span rel=\"" . mysql_insert_id() . "\" title=\"$file\">" . shortenString($file, 25) . "</span><ul></ul></li>"); 
                }else{
                    // Already exists in database
                    $row = mysql_fetch_array($rs);
                    $arrDirs[] = array($file,"<li class=\"folder\"><span rel=\"" . $row['obj_id'] . "\" title=\"$file\">" . shortenString($file, 25) . "</span><ul></ul></li>"); 
                }
                  
          }else{
                // Handle file
                $rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_project=$prj_id AND obj_name='$file' AND obj_parent=$parent");
                if(mysql_num_rows($rs)==0){
                    // Create object in database
                    $rsCreate = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES ($prj_id,'$file',2,$parent)");
                    $arrFiles[] = array($file,"<li class=\"file ext_" . getExt($file) . "\"><span rel=\"" . mysql_insert_id() . "\" title=\"$file\">" . shortenString($file, 25) . "</span><ul></ul></li>"); 
                }else{
                    // Already exists in database
                    $row = mysql_fetch_array($rs);
                    $arrFiles[] = array($file,"<li class=\"file ext_" . getExt($file) . "\"><span rel=\"" . $row['obj_id'] . "\" title=\"$file\">" . shortenString($file, 25) . "</span><ul></ul></li>"); 
                }
          }
      }
  }
  closedir($handle);
}

// Loop out arrays

sort($arrDirs);
sort($arrFiles);

foreach($arrDirs as $v){ echo(str_replace("//","/",$v[1])); }
foreach($arrFiles as $v){ echo(str_replace("//","/",$v[1])); }

echo("</ul>");

?>