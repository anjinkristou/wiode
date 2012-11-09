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
        
        // Return structure of requested node
        case "list":
            // Get variables
            $prj_id = mysql_real_escape_string($_GET['project']);
            $obj_parent = mysql_real_escape_string($_GET['parent']);
            $rs = mysql_query("SELECT obj_id, obj_name, obj_type FROM wiode_objects WHERE obj_project='$prj_id' AND obj_parent='$obj_parent' ORDER BY obj_type, obj_name");
            $i = 0;
            if(mysql_num_rows($rs)!=0){
                $output ='{"structure":[';
                while($row=mysql_fetch_array($rs)){
                    if($i>0){ $output .= ","; }
                    $output .= '{ "id":"' . $row['obj_id'] . '" , "name":"' . stripslashes($row['obj_name']) . '" , "type":"' . $row['obj_type'] . '" }';
                    $i++;
                }
                $output .= "]}";
                echo($output);
            }else{
                error(1);
            }
            
            break;
            
  }

?>