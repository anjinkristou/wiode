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

// Load Config File ######################################################################
function changeDir($up_n){
    $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
    for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
}
require_once(changeDir(3)."/config.php");
// #######################################################################################

$error_msg = "";

$login_form = '
<form id="login_form">
<div id="error">Invalid Login Credentials.</div>
<label>Login:</label>
<input type="text" id="usr_login" name="usr_login" autofocus="autofocus" />
<label>Password:</label>
<input type="password" id="usr_password" name="usr_password" />
<label>Interface:</label>
<select id="interface" name="interface">
    <option value="0">Standard (Default)</option>
    <option value="1">Simple (Mobile)</option>
</select>
<input type="button" value="Login" id="login_btn" class="bold" onclick="submitLogin();" />
</form>
';


if(empty($_GET['process'])){

// Show login form ######################################################################

    echo($login_form);
    
}else{
    
// Process Login ########################################################################

    if($_GET['process']==1){
        // Initial login
        $pass = true;
        $usr_login = mysql_real_escape_string($_POST['usr_login']);
        $usr_password = encryptPassword($_POST['usr_password']);
        $interface = $_POST['interface'];
        $rs = mysql_query("SELECT * FROM wiode_users WHERE usr_login='$usr_login' AND usr_password='$usr_password'");
        if(mysql_num_rows($rs)==0){
            echo($login_form . "<script>$(function(){ $('#error').fadeIn(300); });</script>");
        }else{
            // Set authentication cookie
            $row = mysql_fetch_array($rs);
            $usr_id = $row['usr_id'];
            $usr_theme = $row['usr_theme'];
            $_SESSION['auth'] = $usr_id;
            $_SESSION['theme'] = $usr_theme;
            $_SESSION['interface'] = $interface;
            
            // Check for open project
            
            $rs = mysql_query("SELECT * FROM wiode_user_state WHERE ust_usr_id=$usr_id AND ust_obj_type=0");
            if(mysql_num_rows($rs)==0){
                // New User, must select or create a project
                $rs = mysql_query("SELECT * FROM wiode_projects ORDER BY prj_name");
                if(mysql_num_rows($rs)!=0){
                
                // Access controls
            $usr_acl = array();
            $usr_type = "admin";
            if(file_exists($site['absroot'] . "/_users/" . $_SESSION['auth'] . ".usr")){ 
                $usr_type="user"; 
                $usr_acl = explode(",",file_get_contents($site['absroot'] . "/_users/" . $_SESSION['auth'] . ".usr"));
            }
                ?>
                <label>Open Project:</label>
                <select id="prj_chooser">
                    <?php    
                        while($row=mysql_fetch_array($rs)){
                        
                            // Check ACL
                            if($usr_type=="user"){
                                if(in_array($row['prj_id'],$usr_acl)){
                                    echo("<option value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
                                }
                            }else{
                                echo("<option value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
                            }
                        }
                    ?>
                </select>
                <input type="button" value="Open Project" onclick="openProject();" />
                <?php
                }else{
                ?>
                <label>Create New Project:</label>
                <input type="text" id="prj_new" autofocus="autofocus">
                <input type="button" value="Create Project" onclick="createProject();" />
                <?php
                }                
            }else{
                // Set opened project
                $row = mysql_fetch_array($rs);
                $_SESSION['curproject'] = $row['ust_obj_id'];
                echo("<script>$(function(){ loadWorkspace(); });</script>");
                echo("<div id=\"loader\"></div>");
            }
        }
    }else{
        // First time user process
        if(!empty($_GET['open'])){
            // Open existing project
            $prj_id = mysql_real_escape_string($_GET['open']);
            $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id,ust_obj_type,ust_obj_id) VALUES (" . $_SESSION['auth'] . ",0,$prj_id)");
            $_SESSION['curproject'] = $prj_id;
            echo("<script>$(function(){ loadWorkspace(); });</script>");
        }else{
            // Create new project (FIRST LOAD ONLY)
            $prj_name = mysql_real_escape_string(urldecode($_GET['create']));
            $rs = mysql_query("INSERT INTO wiode_projects (prj_name) VALUES ('$prj_name')");
            mkdir($site['absroot'] . "/" . urldecode($_GET['create']), 0777, true);
            
            // Open the project
            $prj_id = mysql_insert_id(); 
            $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id,ust_obj_type,ust_obj_id) VALUES (" . $_SESSION['auth'] . ",0,$prj_id)");
            $_SESSION['curproject'] = $prj_id;
            echo("<script>$(function(){ loadWorkspace(); });</script>");
        }
        
    }

}

?>
<script type="text/javascript">

    $("#prj_new").keypress(function(e){
        var code = e.which || e.keyCode;
        // 65 - 90 for A-Z and 97 - 122 for a-z 95 for _ 45 for - 46 for .
        if (!((code >= 65 && code <= 90) || (code >= 97 && code <= 122) || (code >= 37 && code <= 40) || (code >= 48 && code <= 57) || 
        (code >= 96 && code <= 105) || code == 95 || code == 46 || code == 45)){
            e.preventDefault();
         }
    });
    
    $('#usr_login, #usr_password, #interface').keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { submitLogin(); }
    });
    
    $('#prj_new').keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { createProject(); }
    });
    
    function submitLogin(){ 
        $('#login_btn').val('Processing...');
        $.post("system/php/modules/login.php?process=1", $("#login_form").serialize(), function(data){
            $('#login').html(data);        
        });
    }
    
    function openProject(){
        $('#login').load('system/php/modules/login.php?process=2&open='+$('#prj_chooser').val());        
    }
    
    function createProject(){
        $('#login').load('system/php/modules/login.php?process=2&create='+$('#prj_new').val());
    }
    
    function loadWorkspace(){ location.href='index.php'; }
    
    // Stupid fix...
    $(function(){ $('#usr_login').focus(); });
    
    
</script>