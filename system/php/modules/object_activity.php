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

// Set active object warning variable
$obj_active = false;

$obj_id = mysql_real_escape_string($_GET['id']);
$usr_id = mysql_real_escape_string($_SESSION['auth']);


// Check if object is active

$rs = mysql_query("SELECT * FROM wiode_user_state WHERE ust_obj_id=$obj_id AND ust_usr_id!=$usr_id");
if(mysql_num_rows($rs)!=0){
    $obj_active = true;
    $act_users = array();
    while($row = mysql_fetch_array($rs)){
        $act_usr_id = $row['ust_usr_id'];
        $rs2 = mysql_query("SELECT usr_login, usr_full_name FROM wiode_users WHERE usr_id=$act_usr_id");
        if(mysql_num_rows($rs2)!=0){
            $row2 = mysql_fetch_array($rs2);
            $act_users[] = stripslashes($row2['usr_full_name']) . " (" . stripslashes($row2['usr_login']) . ")";
        }
    }
    
    switch(count($act_users)){
        case 0:
            $act_user_list = "ERROR IDENTIFYING USERS";
            $add_s = "";
            break;
        case 1:
            $act_user_list = implode(", ", $act_users);
            $add_s = "";
            break;
        default:
            $act_user_list = implode(", ", $act_users);
            $add_s = "s";
    }
    
}


if($obj_active==true){
?>
<script type="text/javascript">
    $(function(){
        showAlert('File In Use!','The file you are accessing is currently being accessed by the following user<?php echo($add_s); ?>:<br /><br /><?php echo($act_user_list); ?><br /><br />Modifying this file may result in conflicts or data loss.');    
        $('#tab_bar li#tab<?php echo($obj_id); ?>>span').addClass('in_use');
    });
</script>
<?php
}
?>