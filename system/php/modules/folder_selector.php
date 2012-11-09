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

$project = mysql_real_escape_string($_GET['p']);
$folder = mysql_real_escape_string($_GET['f']);
$exclude = mysql_real_escape_string($_GET['e']);
if($exclude=="root"){ $exclude=-1; }

// Get project name
$rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$project");
if(mysql_num_rows($rs)!=0){
    $row = mysql_fetch_array($rs);
    $project_name = stripslashes($row['prj_name']);
}

function buildDropDown($project,$parent=0,$exclude,$site){
    $rs = mysql_query("SELECT obj_id, obj_name FROM wiode_objects WHERE (obj_project=$project AND obj_type=1 AND obj_parent=$parent) AND obj_id!=$exclude ORDER BY obj_name");
    if(mysql_num_rows($rs)!=0){
        $c = "";
        if($parent>0){ $c = " style=\"display: none;\""; }
        echo("<ul>");
        while($row=mysql_fetch_array($rs)){
            echo("<li><span rel=\"" . $row['obj_id'] . "\">" . $row['obj_name'] . "</span></li>");
        }
        echo("</ul>");   
    }
}

if($folder!=0){
    $go_back = 0;
    $rs = mysql_query("SELECT obj_parent FROM wiode_objects WHERE obj_id=$folder");
    if(mysql_num_rows($rs)!=0){
        $row = mysql_fetch_array($rs);
        $go_back = $row['obj_parent'];
    }
    echo("<ul><li><span class=\"active\" rel=\"$go_back\">Previous Directory</span>");
}else{
    echo("<ul><li class=\"root\"><span class=\"active\" rel=\"$folder\">Project Root</span>");
}

buildDropDown($project,$folder,$exclude,$site);

echo("</li>");

echo("</ul>");

?>
<script type="text/javascript">
    $(function(){
    
        $('#dup_dest_display').val('<?php echo(rtrim("/$project_name/" . getRelPath($folder,""),"/")); ?>');
        
        $('#dup_dest li>span').dblclick(function(){
            var i = $(this).attr('rel');
            $('#dup_dest').load('system/php/modules/folder_selector.php?p=<?php echo($project); ?>&f='+i+'&e=<?php echo($exclude); ?>');
        });
        
        $('#dup_dest li span').click(function(){
            $('#dup_dir').val($(this).attr('rel'));
            $('#dup_dest_display').val('<?php echo(rtrim("/$project_name/" . getRelPath($folder,""),"/")); ?>/'+$(this).html());
            $('#dup_dest li span.active').removeClass('active');
            $(this).addClass('active');
        });
        
        // Prevent text highlighting
        $('#dup_dest li').attr('unselectable', 'on')
            .css('-moz-user-select', 'none')
            .each(function() { this.onselectstart = function() { return false; }; });
    });
</script>
<input type="hidden" id="dup_dir" value="<?php echo($folder); ?>" />
