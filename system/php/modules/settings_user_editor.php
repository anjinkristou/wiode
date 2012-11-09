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

$id = str_replace("usr_", "", mysql_real_escape_string($_GET['id']));

$usr_login = "";
$usr_full_name = "";
$usr_email = "";
$usr_password = generatePassword();
$usr_type = "admin";
$usr_acl = array();

if($id!="new"){
    $rs = mysql_query("SELECT * FROM wiode_users WHERE usr_id=$id");
    $row=mysql_fetch_array($rs);
    
    $usr_login = stripslashes($row['usr_login']);
    $usr_full_name = stripslashes($row['usr_full_name']);
    $usr_email = stripslashes($row['usr_email']);
    $usr_password = "<<ENCRYPTED>>";
    // Check if access control file exists
    if(file_exists($site['absroot'] . "/_users/" . $id . ".usr")){ 
        $usr_type="user"; 
        $usr_acl = explode(",",file_get_contents($site['absroot'] . "/_users/" . $id . ".usr"));
    }
}

?>
<hr />
<p style="margin-top: 0;">User Configuration</p>
<table class="data">
    <tr>
        <th>Login</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Type</th>
    </tr>
    <tr>
        <td><input type="text" id="usr_login" value="<?php echo($usr_login); ?>" /></td>
        <td><input type="text" id="usr_full_name" value="<?php echo($usr_full_name); ?>" /></td>
        <td><input type="text" id="usr_email" value="<?php echo($usr_email); ?>" /></td>
        <td><input type="text" <?php if($id!="new"){ ?> disabled="disabled"<?php } ?> id="usr_password" value="<?php echo($usr_password); ?>" /></td>
        <td>
            <select id="usr_type" onchange="evalType(this.value);">
                <option <?php if($usr_type=="admin"){ echo("selected=\"selected\""); } ?> value="admin">Admin</option>
                <option <?php if($usr_type=="user"){ echo("selected=\"selected\""); } ?> value="user">User</option>
            </select>
        </td>
        <input type="hidden" id="usr_id" value="<?php echo($id); ?>" />
    </tr>
    <tr id="acl_selector" <?php if($usr_type=="admin"){ echo("style=\"display: none;\""); } ?>>
        <td colspan="5">
            <strong>Grant Access To:</strong>
            <select multiple="multiple" id="usr_acl" size="10" style="margin: 5px 0;">
            <?php
            
            $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_status=0 ORDER BY prj_name");
            if(mysql_num_rows($rs)!=0){
                while($row=mysql_fetch_array($rs)){
                    $selected = "";
                    if(in_array($row['prj_id'],$usr_acl)){ $selected = "selected=\"selected\""; }
                    echo("<option $selected value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
                }
            }
            
            ?>
            </select>
            <a onclick="$('#usr_acl *').attr('selected','selected'); $('#btn_save_user').show();">Select All</a>
        </td>
    </tr>
</table>
<hr />

<script type="text/javascript">

    $(function(){
    
        $('#btn_save_user, #btn_delete, #btn_reset_pw, #btn_clear_state').unbind('click');
    
        <?php if($id!="new"){ ?>$('#btn_reset_pw').show(); <?php } ?>
        
        $('#usr_login, #usr_full_name, #usr_email, #usr_type, #usr_acl').bind('keyup change', function(){ 
            if($('#usr_login').val()!='' && $('#usr_full_name').val()!='' && $('#usr_email').val()!=''){
                $('#btn_save_user').show();
            }else{
                $('#btn_save_user').hide();
            }
        });
        
        // Save User
        $('#btn_save_user').click(function(){
            var usr_login = $('#usr_login').val();
            var usr_full_name = $('#usr_full_name').val();
            var usr_email = $('#usr_email').val();
            var usr_password = $('#usr_password').val();
            var usr_type = $('#usr_type').val();
            var usr_acl_raw = $("#usr_acl").val() || [];
            var usr_acl = usr_acl_raw.join(",");
            
            if(usr_type=='user' && usr_acl==''){
                alert("You must grant this user access to at least one project.");
            }else{
            
                if($('#usr_id').val()=="new"){
                    $.post("system/php/modules/user_actions.php?action=create&id=new", { l: escape(usr_login), n: escape(usr_full_name), e: escape(usr_email), p: escape(usr_password), t: usr_type, a: escape(usr_acl) },
                           function(data) { 
                               if(data=='fail'){
                                   alert('A user with that login already exists!');
                               }else{
                                   $('#usr_id').val(data); $('#btn_save_user').hide(); 
                               }
                           }
                    );
                }else{
                    var id = $('#usr_id').val();
                    $.post("system/php/modules/user_actions.php?action=modify&id="+id, { l: escape(usr_login), n: escape(usr_full_name), e: escape(usr_email), p: escape(usr_password), t: usr_type, a: usr_acl },
                           function(data){ 
                               if(data=='fail'){
                                   alert('A user with that login already exists!');
                               }else{
                                   $('#btn_save_user').hide(); 
                               }
                           }
                    );
                }
    
                $('#usr_password').attr("disabled", true);
            
            }
        });
 
        // Clear State
        $('#btn_clear_state').click(function(){
            var answer = confirm("This will remove all state information and close open files for this user. Proceed?");
            if (answer){
                // Clear user state
                clearState();
            }
        });   
        
        // Delete User
        $('#btn_delete').click(function(){
            var answer = confirm("Are you sure you wish to delete this user? This action is permanent and cannot be undone.");
            if (answer){
                // Clear user state
                clearState();
                // Delete user and return to list
                var id = $('#usr_id').val();
                $.get('system/php/modules/user_actions.php?action=delete&id='+id, function(){ returnToList(); });
            }
        });
        
        // Reset Password
        $('#btn_reset_pw').click(function(){
            $.get('system/php/modules/user_actions.php?action=password', function(data){ $('#usr_password').val(data).removeAttr("disabled"); $('#btn_save_user').show(); });        
        });
    
    });
    
    function evalType(t){
        if(t=='admin'){ $('#acl_selector').slideUp(300); }else{ $('#acl_selector').slideDown(300); }
    }
    
    function clearState(){
        $.get('system/php/modules/user_actions.php?action=clearstate&id=<?php echo($id) ?>');
    }

</script>
