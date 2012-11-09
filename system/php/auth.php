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

$login = false;

// Process logout #######################################################################
if(!empty($_GET['logout'])){
    // Release sessions
    session_unset();
    session_destroy();
    $_SESSION = array();
    // Expire cookies
    setcookie("auth", "", time()-3600);
    setcookie("curproject", "", time()-3600);
    setcookie("theme", "", time()-3600);
    $login = true;
}

// Change project #######################################################################
if(!empty($_GET['project'])){
    // Set session and cookie    
    $_SESSION['curproject'] = mysql_real_escape_string($_GET['project']);
    // Set in database
    $rs = mysql_query("UPDATE wiode_user_state SET ust_obj_id='" . mysql_real_escape_string($_SESSION['curproject']) . "' WHERE ust_usr_id='" . mysql_real_escape_string($_SESSION['auth']) . "' AND ust_obj_type='0'");
}

// Set cookie if SESSION's exist ########################################################
if(isset($_SESSION['auth']) && isset($_SESSION['curproject']) && isset($_SESSION['theme'])){
    setcookie("auth",$_SESSION['auth'], time()+3600*$auth_timeout);
    setcookie("curproject",$_SESSION['curproject'], time()+3600*$auth_timeout);
    setcookie("theme",$_SESSION['theme'], time()+3600*$auth_timeout);
}

// If sessions not set force login ###################################################### 
if(!isset($_SESSION['auth']) || !isset($_SESSION['curproject']) || !isset($_SESSION['theme'])){ $login = true; } 

// Check for install ####################################################################

if(!isset($installer)){ 
    // Fresh install
    $sys_title = "WIODE Installer"; $installer = true; 
}else{
    // Broken config
    if($installer==true){ $sys_title = "WIODE Installer"; }
}

?>