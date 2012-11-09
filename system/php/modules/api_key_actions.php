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

$action = $_GET['action'];
if(!empty($_GET['id'])){ $id = mysql_real_escape_string($_GET['id']); }

    switch($action){
    
        case "create":
            // Ensure API Key table exists
            $sql="SELECT * FROM wiode_api_keys LIMIT 1";
            $result=@mysql_query($sql);
            if(!$result){ mysql_query('CREATE TABLE `wiode_api_keys` (`apk_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `apk_key` longtext, `apk_notes` longtext, PRIMARY KEY (`apk_id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;');}
            
            // Create key
            $rs = mysql_query("INSERT INTO wiode_api_keys (apk_key,apk_notes) VALUES ('" . mysql_real_escape_string($_POST['k']) . "','" . mysql_real_escape_string($_POST['n']) . "')");
            
            // Return new id
            echo(mysql_insert_id());
        
            break;
            
        case "modify":     
            if($_POST['k']!=""){
                $rs = mysql_query("UPDATE wiode_api_keys SET apk_key='" . mysql_real_escape_string($_POST['k']) . "', apk_notes='" . mysql_real_escape_string($_POST['n']) . "' WHERE apk_id='$id'");
            }
            break;
            
        case "delete":
            $rs = mysql_query("DELETE FROM wiode_api_keys WHERE apk_id='$id'");       
            break;
            
    }

?>