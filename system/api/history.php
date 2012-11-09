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

    switch($action){
        
        // Return list of all history save-points for requested object
        case "list":
            // Variables
            $id = mysql_real_escape_string($_GET['id']);
            // Get history list
            $rs = mysql_query("SELECT hst_id, hst_usr_id, hst_datetime, hst_notes FROM wiode_object_history WHERE hst_obj_id='$id'");
            $i = 0;
            if(mysql_num_rows($rs)!=0){
                $output ='{"history":[';
                while($row=mysql_fetch_array($rs)){
                    if($i>0){ $output .= ","; }
                    // Get user information
                    $rsUser = mysql_query("SELECT usr_id, usr_login, usr_full_name FROM wiode_users WHERE usr_id=" . $row['hst_usr_id']);
                    if(mysql_num_rows($rsUser)!=0){ 
                        $rowUser = mysql_fetch_array($rsUser);
                        $hst_user_info = '[{ "id":"'.$rowUser['usr_id'].'","login":"'.stripslashes($rowUser['usr_login']).'","full_name":"'.stripslashes($rowUser['usr_full_name']).'"}]'; 
                    }else{
                        $hst_user_info = '[{ "id":"0","login":"MISSING_INFO","full_name":"MISSING_INFO"}]';
                    }
                    // Build output
                    $output .= '{ "id":"' . $row['hst_id'] . '" , "user":' . $hst_user_info . ' , "datetime":"' . $row['hst_datetime'] . '" , "notes":"' . stripslashes($row['hst_notes']) . '" }';
                    $i++;
                }
                $output .= "]}";
                echo($output);
            }else{
                error(1);
            }
            break;
        
        // Return history restore point data
        case "getdata":
            // Variables
            $id = mysql_real_escape_string($_GET['id']);
            // Return history
            $rs = mysql_query("SELECT hst_data FROM wiode_object_history WHERE hst_id='$id'");
            if(mysql_num_rows($rs)!=0){
                $row = mysql_fetch_array($rs);
                echo(stripslashes($row['hst_data']));
            }else{
                error(1);
            }
            break;
            
      }

?>