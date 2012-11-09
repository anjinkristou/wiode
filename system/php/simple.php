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

require_once('auth.php');

?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo($sys_title); ?></title>
    <link href="//fonts.googleapis.com/css?family=Droid+Sans+Mono:regular" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="system/css/screen.css?v=3.0.0">
    <link rel="stylesheet" href="system/css/simple.css?v=3.0.0">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>

<body class="simple">
    
    <?php if($login==true && $installer==false){ ?>
    
    <div id="login"></div>
    
    <?php }elseif($installer==true){ ?>
    
    <div id="installer"></div>
    
    <?php }else{ ?>
        
    <div id="top_bar">
        <div id="project_dropdown">
            <?php require_once('system/php/modules/project_selector.php'); ?>
        </div>        

        <ul id="top_menu">
            <li rel="" id="save">Save</li>
            <li class="back" rel="" id="close">Close</li>
            <?php
            $rs = mysql_query("SELECT ftp_id FROM wiode_ftp_connections WHERE ftp_prj_id=" . $_SESSION['curproject']);
            if(mysql_num_rows($rs)!=0){
            ?>
            <li rel="" id="ftp">FTP Upload</li>
            <?php } ?>
        </ul>
        
        <div id="cur_user">
                <a id="logout" href="?logout=t">Logout</a>
        </div>
    </div>
    

    <div id="main">
        <ul id="sliders">
        </ul>
    </div>
    
    <!-- Hidden Objects -->
    <div id="context_menu"></div>
    <div id="modal_overlay"></div>
    <div id="modal"><div id="modal_title"><span></span><a>X</a></div><div id="modal_content"></div></div>
    <div id="processor"></div>
    <input type="hidden" id="cur_project" value="<?php echo($_SESSION['curproject']); ?>" />
    <input type="hidden" id="cur_object" value="0" />
    
    <?php } ?>


<!-- Default Scripts -->
<script src="system/js/jquery-1.7.min.js"></script>
<script src="system/js/jquery.textarea_autogrow.js"></script>
<script src="system/js/jquery.rotate.js"></script>
<script src="system/js/jquery.tooltip.js"></script>
<script src="system/js/common.js?v=3.0.0"></script>
<script>
<?php 
// Get project root
$rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=" . $_SESSION['curproject']);
$row = mysql_fetch_array($rs);
$root =  $site['absroot'] . "/" . $row['prj_name'];
?>

$(function(){
    loadRoot('<?php echo($root); ?>');
    bindFileActions();
});

</script>
<script src="system/js/simple.js?v=2.0"></script>

</body>
</html>
