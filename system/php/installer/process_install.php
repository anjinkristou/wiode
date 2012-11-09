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

// Get config variables and connect to database #########################################
if (isset($_POST['root'])) { 
    $root = $_POST['root']; 
    $abs = $_SERVER['DOCUMENT_ROOT'] . $root;
}else{
    exit();
}

if ((isset($_POST['host'])) && (isset($_POST['username'])) && (isset($_POST['password']))) {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $database = $_POST['database'];
    $conn = mysql_connect($host, $username, $password);
    mysql_select_db($database, $conn);
} else {
    exit();
}

if(isset($_POST['init_username']) && isset($_POST['init_password1']) && isset($_POST['init_full_name']) && isset($_POST['init_email'])){
    $init_username = mysql_real_escape_string($_POST['init_username']);
    $init_password = sha1(md5($_POST['init_password1']));
    $init_full_name = mysql_real_escape_string($_POST['init_full_name']);
    $init_email = mysql_real_escape_string($_POST['init_email']);
}else{
    exit();
}

// Build config file ####################################################################

$config_file = "<?php

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

// Database #############################################################################

    \$dbHost = \"".$host."\";
    \$dbUser = \"".$username."\";
    \$dbPass = \"".$password."\";
    \$dbName = \"".$database."\";

// Paths ################################################################################

    // PATH TO WIODE WORKSPACE - NO TRAILING SLASHES, LEAVE EMPTY FOR ROOT
    \$root = \"".$root."\";

// Misc Settings ########################################################################

    \$sys_title = \"WIODE\";

// Authentication timeout (In hours, default = 120) #####################################

    \$auth_timeout = 120;

// Include Functions ####################################################################

    include(dirname(__file__) . '/system/php/functions.php');

// Timezone #############################################################################

    date_default_timezone_set('UTC');
    
?>";

// Only create config if file is empty
if(filesize($abs."/config.php")<=10){
    $config_file_path = $abs."/config.php";
    $handler = fopen($config_file_path, 'w');
    fwrite($handler, $config_file);
    fclose($handler);
}

// Build database #######################################################################

$create_wiode_ftp_connections = 
"CREATE TABLE wiode_ftp_connections (
  ftp_id int(11) NOT NULL AUTO_INCREMENT,
  ftp_prj_id int(11) NOT NULL,
  ftp_host varchar(255) NOT NULL,
  ftp_user varchar(255) NOT NULL,
  ftp_password varchar(255) NOT NULL,
  ftp_remote_path varchar(255) NOT NULL,
  ftp_port int(11) NOT NULL,
  PRIMARY KEY (ftp_id)
) ENGINE=MyISAM";

mysql_query($create_wiode_ftp_connections);

$create_wiode_objects = 
"CREATE TABLE IF NOT EXISTS wiode_objects (
  obj_id int(11) NOT NULL AUTO_INCREMENT,
  obj_project int(11) NOT NULL,
  obj_name varchar(255) NOT NULL,
  obj_type int(1) NOT NULL DEFAULT '0',
  obj_parent int(11) NOT NULL,
  PRIMARY KEY (obj_id)
) ENGINE=MyISAM";

mysql_query($create_wiode_objects);

$create_wiode_projects = 
"CREATE TABLE IF NOT EXISTS wiode_projects (
  prj_id int(11) NOT NULL AUTO_INCREMENT,
  prj_name varchar(255) NOT NULL,
  prj_status int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (prj_id)
) ENGINE=MyISAM";

mysql_query($create_wiode_projects);

$create_wiode_users = 
"CREATE TABLE IF NOT EXISTS wiode_users (
  usr_id int(11) NOT NULL AUTO_INCREMENT,
  usr_login varchar(255) NOT NULL,
  usr_password varchar(255) NOT NULL,
  usr_full_name varchar(255) NOT NULL,
  usr_email varchar(255) NOT NULL,
  usr_theme int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (usr_id)
) ENGINE=MyISAM";

mysql_query($create_wiode_users);

$insert_wiode_users = 
"INSERT INTO wiode_users (usr_id, usr_login, usr_password, usr_full_name, usr_email, usr_theme) VALUES
(1, '$init_username', '$init_password', '$init_full_name', '$init_email', 0);";

mysql_query($insert_wiode_users);

$create_wiode_user_state =
"CREATE TABLE IF NOT EXISTS wiode_user_state (
  ust_id int(11) NOT NULL AUTO_INCREMENT,
  ust_usr_id int(11) NOT NULL,
  ust_obj_type int(11) NOT NULL,
  ust_obj_id int(11) NOT NULL,
  ust_obj_project int(11) DEFAULT NULL,
  PRIMARY KEY (ust_id)
) ENGINE=MyISAM";

mysql_query($create_wiode_user_state);

?>