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
$root =  $site['absroot'] . "/" . $row['prj_name'];

// Loop out files

echo("<ul id=\"file_manager\">");

$arrDirs = array();
$arrFiles = array();

// Define previous folder

if(str_replace("//","/",$path)!=$root){ 
    $path_parts = pathinfo($path);
    $prev = $path_parts['dirname'];
    $arrDirs[] = "<li><span rel=\"$prev\" class=\"back\">..</span></li>"; 
}

// Build arrays

if ($handle = opendir($path)) {
  while (($file = readdir($handle)) !== false) {
      if($file!="." && $file!=".."){
          if(is_dir($path . "/" . $file)){
              // Handle directory
              $arrDirs[] = "<li><span rel=\"" . $path . "/" . $file . "\" class=\"folder\">$file</span></li>"; 
          }else{
              // Handle file
              $non_editable = "";
              if(!in_array(getExt($file),$arrEditable)){ $non_editable = " lock"; }
              $arrFiles[] = "<li><span rel=\"" . $path . "/" . $file . "\" class=\"file ext_" . getExt($file) . $non_editable . "\">$file</span></li>";
          }
      }
  }
  closedir($handle);
}

// Loop out arrays

sort($arrDirs);
sort($arrFiles);

foreach($arrDirs as $v){ echo(str_replace("//","/",$v)); }
foreach($arrFiles as $v){ echo(str_replace("//","/",$v)); }

echo("</ul>");

?>