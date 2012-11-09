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

$rs = mysql_query("SELECT usr_login, usr_theme FROM wiode_users WHERE usr_id=" . mysql_real_escape_string($_SESSION['auth']));
$row = mysql_fetch_array($rs);
$usr_login = $row['usr_login'];
$usr_theme = $row['usr_theme'];

?>
<p>Editor Theme:</p>
<select id="usr_theme">
    <option <?php if($usr_theme==0){ echo("selected=\"selected\""); } ?> value="0">Dark Background</option>
    <option <?php if($usr_theme==1){ echo("selected=\"selected\""); } ?> value="1">Light Background</option>
</select>
<input type="button" value="Save Preferences" onclick="savePreferences();" />
<div class="clear"></div>
<hr />
<p>New Password:</p>
<input type="password" id="p1" />
<p>New Password (Verify):</p>
<input type="password" id="p2" />
<input type="button" value="Change Password" onclick="changePassword();" />
<div class="clear"></div>

<script type="text/javascript">
        
    function savePreferences(){
        var t = $('#usr_theme').val();
        $.get('system/php/modules/user_actions.php?action=update&theme='+t);
        alert('Selection Saved. You must log out and back in to see changes.');
    }
    
    function changePassword(){
        var p1 = $('#p1').val();
        var p2 = $('#p2').val();
        
        if(p1==p2){
            $.post('system/php/modules/user_actions.php?action=user_password', { password: p1 } );
            alert('Password Saved.');
            $('#p1,#p2').val('');
        }else{
            alert('Passwords do not match.');
        }
    }
        
</script>