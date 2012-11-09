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

// Check / Build Database
$sql="SELECT * FROM wiode_snippets";
$result=@mysql_query($sql);
if(!$result){ mysql_query('CREATE TABLE `wiode_snippets` (`snp_id` int(10) unsigned NOT NULL AUTO_INCREMENT, `snp_title` varchar(255) NOT NULL, `snp_content` longtext, PRIMARY KEY (`snp_id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;');}

?>
<ul id="snp_list">
    <li id="new" class="bold">+ Add New Snippet</li>
<?php

if(!empty($_GET['filter'])){
    $filter = urldecode($_GET['filter']);
    $rs = mysql_query("SELECT snp_id,snp_title FROM wiode_snippets WHERE snp_title LIKE '%$filter%' OR snp_content LIKE '%$filter%'");
}else{
    $rs = mysql_query("SELECT snp_id,snp_title FROM wiode_snippets");
}

if(mysql_num_rows($rs)!=0){
    while($row=mysql_fetch_array($rs)){
        echo("<li id=\"" . $row['snp_id'] . "\"><img src=\"system/images/tree/script.png\" />" . stripslashes($row['snp_title']) . "</li>");
    }
}

?>
</ul>
<script>
    $(function(){
        $('#snp_list').delegate('li','click',function(){
            $('#snp_region').load('system/php/modules/snippets_editor.php?id='+$(this).attr('id'));
        });
    });
</script>


