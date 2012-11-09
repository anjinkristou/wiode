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

?>
<select id="setting_chooser">
    <optgroup label="- PROJECTS -"> 
        <option value="0">Active Projects</option>
        <option value="1">Archived Projects</option>
    </optgroup>
    <optgroup label="- USERS -">
        <option value="2">System Users</option>
    </optgroup>
    <optgroup label="- API -">
        <option value="3">API Keys</option>
    </optgroup>
</select>

<div id="set_region">
    <div id="loader"></div>
</div>

<input type="button" class="action_button" id="btn_back" value="Back" style="display: none;" onclick="returnToList();" />
<input type="button" value="Close Settings" onclick="unloadModal();" />
<div class="btn_break" style="display: none;"></div>
<!-- Project Buttons -->
<input type="button" class="action_button" id="btn_backup" value="Create Backup" style="display: none;" />
<input type="button" class="action_button" id="btn_archive" value="Archive" style="display: none;" />
<input type="button" class="action_button" id="btn_restore" value="Restore" style="display: none;" />
<input type="button" class="action_button" id="btn_delete" value="Delete" style="display: none;" />
<input type="button" class="action_button bold" id="btn_save_ftp" value="Save FTP Settings" style="display: none;" />
<!-- User Buttons -->
<input type="button" class="action_button" id="btn_save_user" value="Save Changes" style="display: none;" />
<input type="button" class="action_button" id="btn_reset_pw" value="Reset Password" style="display: none;" />
<input type="button" class="action_button" id="btn_clear_state" value="Clear State" style="display: none;" />
<!-- API Key Buttons -->
<input type="button" class="action_button" id="btn_save_key" value="Save Key" style="display: none;" />


<script type="text/javascript">
    
    $(function(){
        loadSettingList(0);
        $('#setting_chooser').change(function(){
            var n = $(this).val();
            if(n!='break'){ loadSettingList(n); }
        });
    });
    
    function returnToList(){
        loadSettingList($('#setting_chooser').val());
        $('.action_button, .btn_break').hide();
    }
    
    function loadSettingList(n){
        $('#set_region').load('system/php/modules/settings_list.php?show='+n);
    }
    
</script>