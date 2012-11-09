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

// Get object data
$obj_id = mysql_real_escape_string($_GET['id']);
$obj_path = getPath($site,$obj_id,"");

$rs = mysql_query("SELECT obj_name FROM wiode_objects WHERE obj_id=$obj_id");
$row = mysql_fetch_array($rs);
$ext = getExt($row['obj_name']);

$content = file_get_contents($obj_path);

?>
<script>!window.jQuery && document.write(unescape('%3Cscript src="system/js/jquery-1.6.1.min.js"%3E%3C/script%3E'));</script>
<textarea id="code"><?php echo(htmlspecialchars($content)); ?></textarea>
<script>
    
// Load editor
var fileID = <?php echo($obj_id); ?>;
var wswidth = $('#workspace').width();
var wsheight = $('#workspace').height();
var textarea = document.getElementById('code');
var editor<?php echo($obj_id); ?> = new CodeMirror(CodeMirror.replace(textarea), {
//height: wsheight+'px',
height: '100%',
width: wswidth-43+'px',
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
onChange: function (n) { 
    markChanged(<?php echo($obj_id); ?>);
    // Ensure this is current object
    $('#cur_object').val(<?php echo($obj_id); ?>);
},
saveFunction: function(){ saveFile(); },
uploadFunction: function(){ quickUpload(); },
viewFunction: function(){ quickView(); },
gotoFunction: function(){ jumpTo(); },
findFunction: function(){ findString(); },
replaceFunction: function(){ replaceString(); },
printFunction: function(){ printEditor(); },
helpFunction: function(){ openHelp(); },
snippetsFunction: function(){ openSnippets(); },
colorpickerFunction: function(){ openColorpicker(); },
onCursorActivity: function(){
    var l = editor<?php echo($obj_id); ?>.currentLine();
    var c = editor<?php echo($obj_id); ?>.cursorPosition().character;
    changeLineChar(l,c); 
},
syntax: '<?php echo($ext) ?>',
onLoad: function(editor){ 
    <?php if(strtolower($ext)!='css'){ ?>
    zen_editor.bind(editor);
    <?php } ?>
    changeLineChar('0','0');
}
});
    
</script>