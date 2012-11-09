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

require_once('../../config.php');
if(!isset($_SESSION['auth'])){ 
    // Token not set, redirect to login
    ?>
    <script>location.href='<?php echo($root); ?>/index.php';</script>
    <?php
    exit(); 
}

$obj_project = $_GET['project'];
$obj_parent = $_GET['parent'];
$obj_name = mysql_real_escape_string(urldecode($_GET['name']));
if($obj_parent=='root'){ $obj_parent=0; }

// Check if this is an overwrite

$rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_project=$obj_project AND obj_name='$obj_name' AND obj_parent=$obj_parent");

if(mysql_num_rows($rs)==0){
   
    $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES ($obj_project,'$obj_name',2,$obj_parent)");
    
    $obj_new_id = mysql_insert_id();
    $obj_ext = getExt($obj_name);

    echo("<li class=\"file ext_" . $obj_ext . "\"><span rel=\"" . $obj_new_id . "\" title=\"" . $obj_name . "\">" . shortenString($obj_name, 25) . "</span></li>");

}else{
    echo("stop");
}
    

?>