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

$id = str_replace("apk_", "", mysql_real_escape_string($_GET['id']));

$apk_key = generatePassword() . generatePassword();
$apk_notes = "";

if($id!="new"){
    $rs = mysql_query("SELECT * FROM wiode_api_keys WHERE apk_id=$id");
    $row=mysql_fetch_array($rs);
    
    $apk_key = stripslashes($row['apk_key']);
    $apk_notes = stripslashes($row['apk_notes']);
}

?>
<hr />
<p style="margin-top: 0;">API Key Settings</p>
<table class="data">
    <tr>
        <th width="5">API&nbsp;Key</th>
        <th>Notes</th>
    </tr>
    <tr>
        <td><input type="text" style="width: 200px; margin: 0;" id="apk_key" value="<?php echo($apk_key); ?>" /></td>
        <td><input type="text" id="apk_notes" value="<?php echo($apk_notes); ?>" /></td>
        <input type="hidden" id="apk_id" value="<?php echo($id); ?>" />
    </tr>
</table>
<hr />

<script type="text/javascript">

    $(function(){

        $('#btn_save_key, #btn_delete').unbind('click');
    
        <?php if($id=="new"){ ?>$('#btn_save_key').show(); $('#btn_delete').hide(); <?php } ?>
        
        $('#apk_key, #apk_notes').bind('keyup change', function(){ 
            if($('#apk_key').val()!=''){
                $('#btn_save_key').show();
            }else{
                $('#btn_save_key').hide();
            }
        });
        
        // Save Key
        $('#btn_save_key').click(function(){
            var apk_key = $('#apk_key').val();
            var apk_notes = $('#apk_notes').val();
            var id = $('#apk_id').val();
            if(id=='new'){
                $.post("system/php/modules/api_key_actions.php?action=create&id="+id, { k: apk_key, n: apk_notes },function(data){ 
                    $('#btn_save_key').hide();
                    $('#apk_id').val(data);
                    $('#btn_delete').show();
                });

            }else{
                $.post("system/php/modules/api_key_actions.php?action=modify&id="+id, { k: apk_key, n: apk_notes },function(){ 
                    $('#btn_save_key').hide();
                });
            }
        });
        
        // Delete Key
        $('#btn_delete').click(function(){
            var id = $('#apk_id').val();
            $.get("system/php/modules/api_key_actions.php?action=delete&id="+id);
            returnToList();
        });
        
    });


</script>
