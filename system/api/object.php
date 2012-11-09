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
        
        // Return data of requested object
        case "getdata":
            // Variables
            $id = mysql_real_escape_string($_GET['id']);
            // Get file
            $obj_id = mysql_real_escape_string($_GET['id']);
            $obj_path = getPath($site,$obj_id,""); 
            if(file_exists($obj_path)){          
                echo(file_get_contents($obj_path));
            }else{
                error(1);
            }
            break;
            
        case "getpath":
            // Variables
            $id = mysql_real_escape_string($_GET['id']);
            // Retrun path
            $path = str_replace(":80","",getPath($site,$id,"",1));
            echo($path);
            break;
            
        // Create object    
        case "create":
            // Convert PARENT value to ID
            if($_GET['parent']==0){
                $_GET['id']='root';
            }else{
                $_GET['id']=$_GET['parent'];
            }
            // All actions handeled by object_action include
            break;
        
        // Save object    
        case "save":
            echo("complete");
            // All actions handeled by object_action include
            break;
            
        // Rename object
        case "rename":
            // All actions handeled by object_action include
            break;
            
        // Delete object
        case "delete":
            $_GET['confirmed'] = true;
            echo("complete");
            // All actions handeled by object_action include
            break;
            
      }

  // Object actions include 
  require_once('../php/modules/object_actions.php');

?>