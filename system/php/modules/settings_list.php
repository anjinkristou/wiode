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

$show = $_GET['show'];

switch($show){

    case 0:
        ?>
        <ul class="set_list" id="set_active_projects">
        <?php
        $rs = mysql_query("SELECT prj_id, prj_name FROM wiode_projects WHERE prj_status=0 ORDER BY prj_name");
            if(mysql_num_rows($rs)!=0){
                while($row=mysql_fetch_array($rs)){
                    $prj_id = $row['prj_id'];
                    $prj_name = stripslashes($row['prj_name']);
                    echo("<li id=\"prj_" . $prj_id . "\"><img src=\"system/images/tree/root.png\" />$prj_name</li>");
                }
            }else{
                echo("<strong>No active projects exist.</strong>");
            }
        ?>
        </ul>
        <?php
        
        break;
    
    case 1:
        ?>
        <ul class="set_list" id="set_archived_projects">
        <?php
        $rs = mysql_query("SELECT prj_id,prj_name FROM wiode_projects WHERE prj_status=1 ORDER BY prj_name");
            if(mysql_num_rows($rs)!=0){
                while($row=mysql_fetch_array($rs)){
                    $prj_id = $row['prj_id'];
                    $prj_name = stripslashes($row['prj_name']);
                    echo("<li id=\"prj_" . $prj_id . "\"><img src=\"system/images/tree/root.png\" />$prj_name</li>");
                }
            }else{
                echo("<strong>No archived projects exist.</strong>");
            }
        ?>
        </ul>
        <?php
        
        break;

    case 2:
        ?>
        <ul class="set_list" id="set_users">
        <li id="usr_new"><strong>+ Add New User</strong></li>
        <?php
        $rs = mysql_query("SELECT * FROM wiode_users ORDER BY usr_login");
        while($row=mysql_fetch_array($rs)){
            echo("<li id=\"usr_" . $row['usr_id'] . "\"><img src=\"system/images/ico_user_color.png\" />" . stripslashes($row['usr_login']) . " (" . stripslashes($row['usr_full_name']) . ")" . "</li>");
        }
        ?>
        </ul>
        <?php
        break;
        
    case 3:
        ?>
        <ul class="set_list" id="set_api_keys">
        <li id="apk_new"><strong>+ Add New API Key</strong></li>
        <?php
        $rs = mysql_query("SELECT * FROM wiode_api_keys");
        if(mysql_num_rows($rs)!=0){
            while($row=mysql_fetch_array($rs)){
                $apk_notes = "";
                if($row['apk_notes']!=""){
                    $apk_notes = "&nbsp;-&nbsp;" . shortenString(stripslashes($row['apk_notes']),50);
                }
                echo("<li id=\"apk_" . $row['apk_id'] . "\"><img src=\"system/images/ico_key_color.png\" />" . stripslashes($row['apk_key']) . $apk_notes . "</li>");
            }
        }
        ?>
        </ul>
        <?php
        break;
}

?>
<script type="text/javascript">

    $(function(){
    
        // Active Projects
        $('#set_active_projects li').each(function(){
            $(this).click(function(){
                var id = $(this).attr('id');
                showBusy();
                $('#set_region').load('system/php/modules/settings_project_editor.php?id='+id, function(){
                    $('#btn_back, #btn_backup, #btn_archive, #btn_delete, #btn_clear_state, .btn_break').show();
                    hideBusy();
                });
            });
        });
        
        // Archived Projects
        $('#set_archived_projects li').each(function(){
            $(this).click(function(){
                var id = $(this).attr('id');
                showBusy();
                $('#set_region').load('system/php/modules/settings_project_editor.php?id='+id, function(){
                    $('#btn_back, #btn_backup, #btn_restore, #btn_delete, .btn_break').show();
                    hideBusy();
                });
            });
        });
        
        // Users
        $('#set_users li').each(function(){
            $(this).click(function(){
                var id = $(this).attr('id');
                showBusy();
                $('#set_region').load('system/php/modules/settings_user_editor.php?id='+id, function(){
                    $('#btn_back, #btn_delete, #btn_clear_state, .btn_break').show();
                    hideBusy();
                });
            });
        });
        
        // API Keys
        $('#set_api_keys li').each(function(){
            $(this).click(function(){
                var id = $(this).attr('id');
                showBusy();
                $('#set_region').load('system/php/modules/settings_api_key_editor.php?id='+id, function(){
                    $('#btn_back, #btn_delete, .btn_break').show();
                    hideBusy();
                });
            });
        });
    
    
    });

</script>