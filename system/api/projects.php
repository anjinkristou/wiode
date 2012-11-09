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
        
        // Return list of all projects
        case "list":
            $rs = mysql_query("SELECT prj_id, prj_name, prj_status FROM wiode_projects ORDER BY prj_id");
            $i = 0;
            
            if(mysql_num_rows($rs)!=0){
                $output ='{"projects":[';
                while($row=mysql_fetch_array($rs)){
                    if($i>0){ $output .= ","; }
                    $output .= '{ "id":"' . $row['prj_id'] . '" , "name":"' . $row['prj_name'] . '" , "status":"' . $row['prj_status'] . '" }';
                    $i++;
                }
                $output .= "]}";
                echo($output);
            }else{
                error(1);
            }

            break;
        
        // Return root path of requested project
        case "getpath":
            // Variables
            $id = mysql_real_escape_string($_GET['id']);
            // Retrun path
            $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$id");
            if(mysql_num_rows($rs)!=0){
                $row = mysql_fetch_array($rs);
                $prj_path = str_replace(":80","",$site['url']) . "/" . $row['prj_name'] . "/";
                echo($prj_path);
            }else{
                error(1);
            }
            break;
            
        // Create project (handled by project_actions include)
        case "create":
            $_GET['create'] = $_GET['name']; // Modifies to correct passthrough
            break;
        
        // Delete project (handled by project_actions include) 
        case "delete":
            echo("complete");
            break;            
            
  }
  
  // Project actions include 
  require_once('../php/modules/project_actions.php');

?>