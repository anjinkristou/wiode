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

// Load config only if AJAX initiated
if(!empty($_GET['dynamic'])){ 
    // Load Config File & Check Token ########################################################
    function changeDir($up_n){
        $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
        for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
    }
    require_once(changeDir(3)."/config.php");
    require_once(changeDir(1)."/check_token.php");
    // #######################################################################################
}

// Access controls
$usr_acl = array();
$usr_type = "admin";
if(file_exists($site['absroot'] . "/_users/" . $_SESSION['auth'] . ".usr")){ 
    $usr_type="user"; 
    $usr_acl = explode(",",file_get_contents($site['absroot'] . "/_users/" . $_SESSION['auth'] . ".usr"));
}

?>
<select id="project_selector" onchange="changeProject(this.value);">
    <?php
    
    $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_status=0 ORDER BY prj_name");
    if(mysql_num_rows($rs)!=0){
        while($row=mysql_fetch_array($rs)){
            $selected = "";
            if($_SESSION['curproject']==$row['prj_id']){ $selected = "selected=\"selected\""; }
            // Check ACL
            if($usr_type=="user"){
                if(in_array($row['prj_id'],$usr_acl)){
                    echo("<option $selected value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
                }
            }else{
                echo("<option $selected value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
            }
        }
    }
    
    // Only show in standard interface
    if($_SESSION['interface']==0 && $usr_type=="admin"){
    ?>
    <option value="new">--CREATE NEW PROJECT--</option>
    <option value="restore">--RESTORE FROM ARCHIVE--</option>
    <?php
    }
    ?>
</select>