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
<p>Select Project to Restore:</p>
<select id="project_restore">
    <?php
    
    $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_status=1 ORDER BY prj_name");
    if(mysql_num_rows($rs)!=0){
        while($row=mysql_fetch_array($rs)){
            echo("<option value=\"" . $row['prj_id'] . "\">" . stripslashes($row['prj_name']) . "</option>");
        }
    }
    
    ?>
</select>
<input type="button" class="strong" value="Restore" onclick="restoreProject();" />
<div class="clear"></div>
<script type="text/javascript">
    function restoreProject(){
        var i = $('#project_restore').val();
        $('#processor').load('system/php/modules/project_actions.php?action=restore&id='+i);
    }
</script>
