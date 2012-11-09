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

    $installer = false;

// Error reporting  #####################################################################

    /*error_reporting(E_ALL);
    ini_set('display_errors', '1');*/
    error_reporting(0);
    
// Check config file ####################################################################

    if(!isset($root)){ $installer = true; }
    if(!isset($sys_title)){ $sys_title = "WIODE"; }
    if(!isset($auth_timeout)){ $auth_timeout = 120; }
        
// Connect to database ##################################################################      
    
    if($conn = mysql_connect($dbHost, $dbUser, $dbPass)){ 
        if(!mysql_select_db($dbName, $conn)){ $installer = true; }
    }else{ $installer = true; }

// Pathing ##############################################################################

    // Determine Protocol
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on"){ $protocol="https://"; }else{ $protocol="http://"; }

    // System Paths
    $site['absroot']    = $_SERVER['DOCUMENT_ROOT'] . $root . "/workspace";
    $site['url']        = $protocol . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $root . "/workspace";
    
    // Backups
    $site['backups']    = $_SERVER['DOCUMENT_ROOT'] . $root . "/backups";
    $site['backup_url'] = $protocol . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $root . "/backups";

    // Plugins
    $site['plgroot']   = $_SERVER['DOCUMENT_ROOT'] . $root . "/plugins";

// Session initialization ###############################################################

    @ini_set("session.cookie_path",$root);
    ini_set("session.cookie_lifetime","0");
    session_start();
    
// If sessions not set but cookie exists, set sessions ##################################

    if(!isset($_SESSION['auth']) || !isset($_SESSION['curproject']) || !isset($_SESSION['theme'])){
        if(isset($_COOKIE['auth']) && isset($_COOKIE['curproject']) && isset($_COOKIE['theme'])){
            $_SESSION['auth'] = $_COOKIE['auth'];
            $_SESSION['curproject'] = $_COOKIE['curproject'];
            $_SESSION['theme'] = $_COOKIE['theme'];
        }    
    }

// Object types #########################################################################

    $objType[0] = "Root";
    $objType[1] = "Folder";
    $objType[2] = "File";
    
// Editor Themes ########################################################################

    $arrTheme[0] = "dark";
    $arrTheme[1] = "light";

// Define editable file extensions ######################################################

    $arrEditable = array("html","htm","css","js","txt","php","xml","rss","htaccess","tpl","json","ini","conf","sql","py"); 

// Pasword Encryption ###################################################################

    function encryptPassword($p){ return sha1(md5($p)); }
    
// Get file extension ###################################################################

    function getExt($n){
        return substr(strrchr($n,'.'),1);
    }
    
// Get path #############################################################################

    // getPath($site,ID,"",TYPE)
    // TYPE defaults to directory/file path, enter "1" for URL

    function getPath($site,$i,$p,$t=0){
        $obj_parent = "";
        $rs = mysql_query("SELECT * FROM wiode_objects WHERE obj_id=$i");
        $row = mysql_fetch_array($rs);
        $obj_project = $row['obj_project'];
        $obj_name = stripslashes($row['obj_name']);
        $obj_parent = $row['obj_parent'];
            
        if($obj_parent==0){
            $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$obj_project");
            $row = mysql_fetch_array($rs);
            $prj_name = $row['prj_name'];
            if($t==0){ $root = $site['absroot']; }else{ $root = $site['url']; }
            if($p==""){
                return(rtrim(($root . "/" . $prj_name . "/" . $obj_name),"/"));
            }else{
                return(rtrim(($root . "/" . $prj_name . "/" . $obj_name . "/" . $p),"/"));    
            }
        }else{
            return getPath($site,$obj_parent,$obj_name . "/" . $p, $t);
        }
    }
    
    
    function getRelPath($i,$p){
        $obj_parent = "";
        $rs = mysql_query("SELECT * FROM wiode_objects WHERE obj_id=$i");
        $row = mysql_fetch_array($rs);
        $obj_project = $row['obj_project'];
        $obj_name = stripslashes($row['obj_name']);
        $obj_parent = $row['obj_parent'];
            
        if($obj_parent==0){
            $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$obj_project");
            $row = mysql_fetch_array($rs);
            $prj_name = $row['prj_name'];
            if($p==""){
                return(rtrim(($obj_name),"/"));
            }else{
                return(rtrim(($obj_name . "/" . $p),"/"));    
            }
        }else{
            return getRelPath($obj_parent,$obj_name . "/" . $p);
        }
    }
    
// Shorten string #######################################################################

    function shortenString($v,$l){
        if(strlen($v) > $l){ return(rtrim(substr($v, 0, $l)) . "…"); }else{ return($v); }
    }

// Time/Date formatting #################################################################

    function formatTimestamp($v){
        return str_replace(" ", " ", date('n/j/y \a\t ', strtotime($v)) . (date('g', strtotime($v))) . date(':i a', strtotime($v)));
    }
    
    function formatTimestampShort($v){
        return str_replace(" ", " ", date('M j', strtotime($v)));
    }
    
// Generate Password ####################################################################

    function generatePassword(){
        $len = 8;
        $hex = md5("1n3B82Gh6Ti52o905" . uniqid("", true));
        $pack = pack('H*', $hex);
        $uid = base64_encode($pack);        // max 22 chars
        $uid = ereg_replace("[^A-Za-z0-9]", "", $uid);    // mixed case
        if ($len<4) $len=4;
        if ($len>128) $len=128;                       // prevent silliness, can remove
        while (strlen($uid)<$len)
            $uid = $uid . generatePassword(22);     // append until length achieved
        $key = substr($uid, 0, $len);
        return $key;  
    }
    
// Get Project Info #####################################################################

    function getDirectorySize($path){ 
      $totalsize = 0; 
      $totalcount = 0; 
      $dircount = 0; 
      if ($handle = opendir ($path)) { 
        while (false !== ($file = readdir($handle))){ 
          $nextpath = $path . '/' . $file; 
          if ($file != '.' && $file != '..' && !is_link ($nextpath)){ 
            if (is_dir ($nextpath)){ 
              $dircount++; 
              $result = getDirectorySize($nextpath); 
              $totalsize += $result['size']; 
              $totalcount += $result['count']; 
              $dircount += $result['dircount']; 
            }elseif (is_file ($nextpath)){ 
              $totalsize += filesize ($nextpath); 
              $totalcount++; 
            } 
          } 
        } 
      } 
      closedir ($handle); 
      $total['size'] = $totalsize; 
      $total['count'] = $totalcount; 
      $total['dircount'] = $dircount; 
      return $total; 
    } 
    
    function sizeFormat($size){ 
        if($size<1024){ 
            return $size."&nbsp;bytes"; 
        }else if($size<(1024*1024)){ 
            $size=round($size/1024,1); 
            return $size."&nbsp;KB"; 
        }else if($size<(1024*1024*1024)){ 
            $size=round($size/(1024*1024),1); 
            return $size."&nbsp;MB"; 
        }else{ 
            $size=round($size/(1024*1024*1024),1); 
            return $size."&nbsp;GB"; 
        } 
    }

?>