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
        
        // Return list of all users
        case "list":
            $rs = mysql_query("SELECT usr_id, usr_login, usr_full_name, usr_email FROM wiode_users");
            $i = 0;           
            if(mysql_num_rows($rs)!=0){
                $output ='{"users":[';
                while($row=mysql_fetch_array($rs)){
                    if($i>0){ $output .= ","; }
                    // Check ACL
                    $usr_access = 0;
                    if(file_exists($site['absroot'] . "/_users/" . $row['usr_id'] . ".usr")){ 
                        $usr_access = urldecode(file_get_contents($site['absroot'] . "/_users/" . $row['usr_id'] . ".usr"));
                    }
                    // Build Output
                    $output .= '{ "id":"' . $row['usr_id'] . '" , "login":"' . stripslashes($row['usr_login']) . '" , "full_name":"' . stripslashes($row['usr_full_name']) . '" , "email":"' . stripslashes($row['usr_email']) . '", "access":"' . $usr_access . '" }';
                    $i++;
                }
                $output .= "]}";
                echo($output);
            }else{
                error(1);
            }

            break;
        
        // Verify user credentials
        case "verify":
            // Get variables
            if(isset($_GET['login'])){
                // Check by Login
                $check_type = "usr_login='" . mysql_real_escape_string(urldecode($_GET['login'])) . "'";
            }else{
                // Check by ID
                $check_type = "usr_id=" . mysql_real_escape_string($_GET['id']);
            }
            $password = encryptPassword(urldecode($_GET['password']));
            // Process verification
            $rs = mysql_query("SELECT usr_id FROM wiode_users WHERE $check_type AND usr_password='$password'");
            if(mysql_num_rows($rs)!=0){
                echo("pass");
            }else{
                echo("fail");
            }
            break;
            
        // Create new user
        case "create":
            // All actions handeled by user_action include
            break;
            
        // Modify existing user
        case "modify":
            // All actions handeled by user_action include
            break;
            
        // Delete user
        case "delete":
            // All actions handeled by user_action include
            echo("complete");
            break;
            
            
  }
  
  // User actions include 
  require_once('../php/modules/user_actions.php');

?>