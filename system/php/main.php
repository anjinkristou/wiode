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

error_reporting(0);

require_once('auth.php');

?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo(htmlspecialchars($sys_title)); ?></title>
    <link href="//fonts.googleapis.com/css?family=Droid+Sans+Mono:regular" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="system/css/tooltips.css?v=3.1.7">
    <link rel="stylesheet" href="system/css/screen.css?v=3.1.7">
    <link rel="stylesheet" href="system/css/colorpicker.css?v=3.1.7">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    
    <?php if($login==true && $installer==false){ ?>
    
    <div id="login"></div>
    
    <?php }elseif($installer==true){ ?>
    
    <div id="installer"></div>
    
    <?php } else { ?>
        
    <div id="top_bar">
        <div id="project_dropdown">
            <?php require_once('system/php/modules/project_selector.php'); ?>
        </div>
        
        <a onclick="loadDirectory(0);" id="project_rescan" title="Rescan Current Project Tree"><img src="system/images/ico_refresh.png" /></a>
        
        <ul id="top_menu">
            <li title="Save (CTRL+S)" onclick="saveFile();" id="save"><img src="system/images/ico_save.png" /></li>
            <li title="Quick Upload (CTRL+U)" onclick="quickUpload();" id="save"><img src="system/images/ico_quick_upload.png" /></li>
            <li title="View (CTRL+O)" onclick="viewFile();" id="view"><img src="system/images/ico_view.png" /></li>
            <li title="Find (CTRL+F)" onclick="findString();" id="find"><img src="system/images/ico_find.png" /></li>
            <li title="Replace (CTRL+R)" onclick="replaceString();" id="replace"><img src="system/images/ico_replace.png" /></li>
            <li title="GoToLine (CTRL+G)" onclick="jumpTo();" id="goto"><img src="system/images/ico_goto.png" /></li>
            <li title="Print (CTRL+P)" onclick="printEditor();" id="print"><img src="system/images/ico_print.png" /></li>
            <li id="break"></li>
            <li title="Code Snippets (CTRL+I)" onclick="openSnippets();" id="system"><img src="system/images/ico_snippets.png" /></li>
            <li title="Color Picker (CTRL+T)" onclick="openColorpicker();" id="system"><img src="system/images/ico_colorpicker.png" /></li>
            <?php if(!file_exists($site['absroot'] . "/_users/" . $_SESSION['auth'] . ".usr")){ ?>
            <li id="break"></li>
            <li onclick="loadModal('system/php/modules/settings.php','System Settings',850);" title="System Settings" id="system"><img src="system/images/ico_system.png" /></li>
            <?php } ?>
            <li id="break"></li>
            <li title="Help (CTRL+H)" onclick="openHelp();" id="help"><img src="system/images/ico_help.png" /></li>
            <?php
            
            // Load Plugins
            
            $arrPlugins = array();
  
            if ($handle = @opendir($site['plgroot'])) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $arrPlugins[] = $file;
                    }
                }
                closedir($handle);
            }  
            
            if(count($arrPlugins)!=0){ // Only show plugin selector if there are plugins (duh!)
            ?>
            <li id="break"></li>
            <li title="Plugin Selector">
            <select id="plugin_selector">
                <option value="0">- PLUGINS -</option>
            <?php
            
            sort($arrPlugins); // Alphabetical...
            
            foreach($arrPlugins as $p){
                $string = file_get_contents($site['plgroot']."/" . $p . "/config.json");
                $data = json_decode($string,true);
                echo("<option value=\"" . $data['name'] . "||" . $data['win_width'] . "||" . $p . "||" . $data['default'] . "||" . $data['overlay'] . "\">" . $data['name'] . "</option>");
            }
            
            ?>
            </select>
            </li>
            <?php
            }
            ?>
        </ul>
        <div id="cur_user">
                <?php
                
                echo("<a title=\"Preferences\" id=\"preferences\" onclick=\"loadModal('system/php/modules/user_preferences.php','Preferences',300);\"><img src=\"system/images/ico_user.png\" /></a><a title=\"Quit\" id=\"logout\" href=\"?logout=t\"><img src=\"system/images/ico_logout.png\" /></a>");
                
                ?>
        </div>
    </div>
    

    <div id="main">

        <div id="left_frame">
            <ul id="file_manager">
                <li class="root" rel="0"><span rel="root">Project_Root</span>
                    <ul></ul>
                </li>
            </ul>
        </div>
        
        <div id="right_frame">
            <div id="tab_container">
                <div id="scroll_container">
                <a class="tab_scroller" id="tab_scroll_left">&laquo;</a>
                <a class="tab_scroller" id="tab_scroll_right">&raquo;</a>
                </div>
            <ul id="tab_bar">
            </ul>
            </div>
            <div id="workspace"></div>
        </div>

    </div>
    
    <!-- Hidden Objects -->
    <div id="position_display"><div id="pd_line">Line: 0</div><div id="pd_char">Char: 0</div></div>
    <div id="context_menu"><div id="context_menu_arrow_border"></div><div id="context_menu_arrow"></div><div id="context_menu_content"></div></div>
    <div id="busy_overlay"></div>
    <div id="modal_overlay"></div>
    <div id="modal"><div id="modal_title"><span></span><a class="close_modal">X</a></div><div id="modal_content"></div></div>
    <div id="processor" style="display: none;"></div>
    <input type="hidden" id="cur_project" value="<?php echo(htmlspecialchars($_SESSION['curproject'])); ?>" />
    <input type="hidden" id="cur_object" value="0" />
    <input type="hidden" id="cur_user_id" value="<?php echo($_SESSION['auth']); ?>" />
    <input type="hidden" id="cur_plugin" value="" />
    <input type="hidden" id="cur_plugin_data" value="" />
    <iframe src="system/php/modules/activity_poller_response.php?id=<?php echo(htmlspecialchars($_SESSION['auth'])); ?>" id="activity_poller" style="display: none;"></iframe>
    
    <?php } ?>

<!-- Default Scripts -->
<script src="system/js/jquery-1.7.min.js"></script>
<script src="system/js/jquery-ui.js"></script>
<script src="system/js/swfobject.js"></script>
<script src="system/js/jquery.uploadify.js"></script>
<script src="system/js/jquery.rotate.js"></script>
<script src="system/js/jquery.tooltip.js"></script>
<script src="system/js/color_parser.js"></script>
<script src="system/js/jquery.colorpicker.js"></script>
<script src="system/js/timezone.js"></script>
<script src="system/codemirror/js/codemirror.js?v=3.1.7"></script>
<script src="system/codemirror/js/zen_codemirror.min.js?v=3.1.7"></script>
<script src="system/js/plugins.js?v=3.1.7"></script>
<script src="system/js/common.js?v=3.1.7"></script>
<script>$(function(){
<?php

if(isset($_SESSION['auth'])){    
    // Open state-saved objects
    $rs = mysql_query("SELECT ust_obj_id FROM wiode_user_state WHERE ust_usr_id='" . $_SESSION['auth'] . "' AND ust_obj_project='" . $_SESSION['curproject'] . "' ORDER BY ust_id");
    if(mysql_num_rows($rs)!=0){
        while($row=mysql_fetch_array($rs)){
            echo("$('#processor').load('system/php/modules/object_actions.php?action=load&id=" . htmlspecialchars($row['ust_obj_id']) . "&savestate=false', function(){ scrolltabShowHide(); });");
        }
    }
}        

?>
});
</script>
<!--[if IE]><script>$(function(){ alert('This system will probably not work well on IE. May want to try another browser...'); });</script><![endif]-->
</body>
</html>