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
    // #######################################################################################
}

$action = $_GET['action'];
if(!empty($_GET['id'])){ $id = mysql_real_escape_string($_GET['id']); }

$psw_alert = false;

// User Control #########################################################################
function userLevel($i,$t,$a,$site){
    // Make sure user control folder exists
    $user_path = $site['absroot'] . "/_users/";
    $user_file = $i . ".usr";
    // Build dir if not_exists
    if(!file_exists($user_path)){ mkdir($user_path); }
    
    if($t=='admin'){
        // User is admin (remove user file)
        if(file_exists($user_path.$user_file)){ unlink($user_path.$user_file); }
    }else{
        // Standard user, set ACL
        $fp = fopen($user_path.$user_file, 'w');
        fwrite($fp, $a);
        fclose($fp);
    }    
}


switch($action){
    
    // Create User ######################################################################
    
    case "create":
        
        $usr_login = mysql_real_escape_string(urldecode($_POST['l']));
        $usr_full_name = mysql_real_escape_string(urldecode($_POST['n']));
        $usr_email = mysql_real_escape_string(urldecode($_POST['e']));
        $usr_password = mysql_real_escape_string(urldecode($_POST['p']));
        $usr_type = $_POST['t'];
        $usr_access = $_POST['a'];
        
        if($usr_login=="" || $usr_full_name=="" || $usr_email=="" || $usr_password==""){
            echo("fail");
        }else{
        
            $usr_password = encryptPassword($usr_password);
            
            $rs = mysql_query("SELECT * FROM wiode_users WHERE usr_login='$usr_login'");
            if(mysql_num_rows($rs)==0){ 
            
                $rs = mysql_query("INSERT INTO wiode_users (usr_login,usr_password,usr_full_name,usr_email) VALUES ('$usr_login','$usr_password','$usr_full_name','$usr_email')");           
                $new_id = mysql_insert_id();           
                userLevel($new_id,$usr_type,$usr_access,$site);           
                echo($new_id);
            
            }else{
                echo("fail");
            }
        
        }
        
        break;
        
    // Modify User ######################################################################
    
    case "modify":
    
        $usr_login = mysql_real_escape_string(urldecode($_POST['l']));
        $usr_full_name = mysql_real_escape_string(urldecode($_POST['n']));
        $usr_email = mysql_real_escape_string(urldecode($_POST['e']));
        $usr_password = urldecode($_POST['p']);
        $usr_type = mysql_real_escape_string($_POST['t']);
        $usr_access = mysql_real_escape_string($_POST['a']);
        
        if($usr_login=="" || $usr_full_name=="" || $usr_email=="" || $usr_password==""){
            echo("fail");
        }else{
        
            $rs = mysql_query("SELECT * FROM wiode_users WHERE usr_login='$usr_login' AND usr_id!=$id");
            if(mysql_num_rows($rs)==0){ 
        
                if($usr_password=="<<ENCRYPTED>>"){
                    // Leave password alone
                    $rs = mysql_query("UPDATE wiode_users SET usr_login='$usr_login', usr_full_name='$usr_full_name', usr_email='$usr_email' WHERE usr_id=$id");
                }else{
                    // Update all fields
                    $rs = mysql_query("UPDATE wiode_users SET usr_login='$usr_login', usr_full_name='$usr_full_name', usr_email='$usr_email', usr_password='" . encryptPassword($usr_password) . "' WHERE usr_id=$id");
                }
                
                userLevel($id,$usr_type,$usr_access,$site);
                
                if($api_call){ echo("complete"); }
                
            }else{
                echo("fail");
            }
        }
        
        break;
        
    // Update Preferences ###############################################################
    
    case "update":
        
        $usr_theme = $_GET['theme'];
        $rs = mysql_query("UPDATE wiode_users SET usr_theme=$usr_theme WHERE usr_id=" . mysql_real_escape_string($_SESSION['auth']));
        
        break;
    
    // Delete User ######################################################################
    
    case "delete":
        
        $rs = mysql_query("DELETE FROM wiode_users WHERE usr_id='$id'");
        
        break;
        
    // Clear object locks/activity #########################################################
    
    case "clearstate":
        
        $rs = mysql_query("DELETE FROM wiode_user_state WHERE ust_usr_id=$id");
        
        break;
    
    // Generate Password ###################################################################
    
    case "password":
    
        $password = generatePassword();
        echo($password);
    
        break;
        
    // User reset password #################################################################
    
    case "user_password":
    
        $password = $_POST['password'];        
        $psw_enc = encryptPassword($password);
        
        $rs = mysql_query("UPDATE wiode_users SET usr_password='$psw_enc' WHERE usr_id=" . mysql_real_escape_string($_SESSION['auth']));

        break;   
    
}

?>