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

// Load Config File & Check Token ########################################################
function changeDir($up_n){
    $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
    for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
}
require_once(changeDir(3)."/config.php");
require_once(changeDir(1)."/check_token.php");
// #######################################################################################

$obj_id = $_GET['objid'];
$hst_id = $_GET['hstid'];
$ext = $_GET['ext'];

$rs = mysql_query("SELECT hst_data, hst_notes FROM wiode_object_history WHERE hst_id=" . mysql_real_escape_string($hst_id));
if(mysql_num_rows($rs)==0){
    $hst_data = "ERROR!";
    $hst_notes = "ERROR!";
}else{
    $row = mysql_fetch_array($rs);
    $hst_data = $row['hst_data'];
    $hst_notes = stripslashes($row['hst_notes']);
}

?>
<div class="editor">
<textarea id="hst_data"><?php echo(htmlspecialchars($hst_data)); ?></textarea>
</div>
<p>Notes:</p>
<textarea id="hst_notes" rows="4"><?php echo($hst_notes); ?></textarea>
<script>

    $(function(){
        $('#btn_restore, #btn_save_notes').unbind('click');
        $('#btn_restore').show();
        $('#btn_save_notes').hide();
        $('#hst_notes').keypress(function(){ $('#btn_save_notes').show(); });
        
        // Save notes
        $('#btn_save_notes').click(function(){
            var id = $('#hst_id').val()
            $.post('system/php/modules/object_history_save_notes.php?id=<?php echo($hst_id); ?>', { notes: $('#hst_notes').val() } );
            $(this).hide();
        });
        
        // Restore from current object
        $('#btn_restore').click(function(){
            $('#btn_restore').val('Restoring File...');
            // Close if currently opened
            $('#processor').load('system/php/modules/object_actions.php?action=close&id=<?php echo($obj_id); ?>');
            // Save reverted file
            var e = eval('editor_history'); 
            var c = e.getCode();
            $.post('system/php/modules/object_actions.php?action=save&id=<?php echo($obj_id); ?>', { code: c, hstnotes: 'Revert to version <?php echo($_GET['hstnum']); ?>' });
            // Open the file in new tab
            $('#processor').load('system/php/modules/object_actions.php?action=load&id=<?php echo($obj_id); ?>', function(){
                // Close the modal
                unloadModal();
            });   
        });
        
      
    });

    // Load editor
    var fileID = 'history';
    var wswidth = $('#workspace').width();
    var wsheight = $('#workspace').height();
    var textarea = document.getElementById('hst_data');
    var editor_history = new CodeMirror(CodeMirror.replace(textarea), {
    height: '350px',
    width: '628px',
    content: textarea.value,
    parserfile: [
    <?php
    
    switch(strtolower($ext)){
        // CSS
        case 'css':
            ?>
            "parsecss.js"
            <?php
            break;
        // JavaScript
        case 'js':
            ?>
            "tokenizejavascript.js",
            "parsejavascript.js"
            <?php
            break;
        // JSON
        case 'json':
            ?>
            "tokenizejavascript.js",
            "parsejavascript.js"
            <?php
            break;
        // SQL
        case 'sql':
            ?>
            "../contrib/sql/js/parsesql.js"
            <?php
            break;
        // Python
        case 'py':
            ?>
            "../contrib/python/js/parsepython.js"
            <?php
            break;
        // PHP, HTML, Etc.
        default:
            ?>
            "parsexml.js",
            "parsecss.js",
            "tokenizejavascript.js",
            "parsejavascript.js",
            "../contrib/php/js/tokenizephp.js",
            "../contrib/php/js/parsephp.js",
            "../contrib/php/js/parsephphtmlmixed.js"
            <?php
    }
    
    ?>
    ],
    tabMode: 'shift',
    stylesheet: [
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/pythoncolors.css",
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/sqlcolors.css",
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/xmlcolors.css",
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/jscolors.css",
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/csscolors.css",
        "system/codemirror/themes/<?php echo($arrTheme[$_SESSION['theme']]); ?>/phpcolors.css",
        "system/css/scrollbars.css",
        "//fonts.googleapis.com/css?family=Droid+Sans+Mono:regular"],
    path: "system/codemirror/js/",
    autoMatchParens: true,
    lineNumberDelay : 100,
    undoDelay: 100,
    readOnly: true
    });
</script>