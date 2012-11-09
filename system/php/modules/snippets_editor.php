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

$id = mysql_real_escape_string($_GET['id']);
$snp_title = "";
$snp_content = "";

// Save Snippet #############################################################

if(!empty($_GET['save'])){

    $snp_title = mysql_real_escape_string($_POST['snp_title']);
    $snp_content = mysql_real_escape_string($_POST['snp_content']);
    
    if($id=="new"){
    // Save New
        mysql_query("INSERT INTO wiode_snippets (snp_title,snp_content) VALUES ('$snp_title','$snp_content')");
        $id = mysql_insert_id();
    }else{
    // Update Existing
        mysql_query("UPDATE wiode_snippets SET snp_title='$snp_title', snp_content='$snp_content' WHERE snp_id=$id");
    }

}

// Delete Snippet ###########################################################

if(!empty($_GET['delete'])){
    mysql_query("DELETE FROM wiode_snippets WHERE snp_id=$id");
}

// Get Content ##############################################################

if($id!="new"){
    $rs = mysql_query("SELECT * FROM wiode_snippets WHERE snp_id=$id");
    if(mysql_num_rows($rs)!=0){
        $row = mysql_fetch_array($rs);
        $snp_title = stripslashes($row['snp_title']);
        $snp_content = $row['snp_content'];
    }
}

?>
<hr />
<p>Title:</p>
<input type="text" id="snp_title" value="<?php echo($snp_title); ?>" />

<p>Snippet:</p>
<div class="editor">
<textarea id="snp_content"><?php echo(htmlspecialchars(stripslashes($snp_content))); ?></textarea>
</div>


<input type="hidden" id="snp_id" value="<?php echo($id); ?>" />
<hr />

<script>

$(function(){
    $('.btn_break').show();
    $('#snp_insert').show();
    $('#snp_back').show();
    $('#snp_title').keyup(function(){
        if($('#snp_title').val!=''){
            $('#snp_save').show();
        }else{
            $('#snp_save').hide();
        }
    });
    
    // Set selective focus
    <?php if($id=="new"){ ?>
    $('#snp_title').focus();
    <?php }else{ ?>
    $('#snp_insert').focus();
    $('#snp_delete').show();
    <?php } ?>
    
});

// Load editor
var fileID = 'snippet';
var wswidth = $('#workspace').width();
var wsheight = $('#workspace').height();
var textarea = document.getElementById('snp_content');
var editor_snippet = new CodeMirror(CodeMirror.replace(textarea), {
height: '200px',
width: '528px',
content: textarea.value,
parserfile: [
    "parsexml.js",
    "parsecss.js",
    "tokenizejavascript.js",
    "parsejavascript.js",
    "../contrib/php/js/tokenizephp.js",
    "../contrib/php/js/parsephp.js",
    "../contrib/php/js/parsephphtmlmixed.js"
    ],
tabMode: 'shift',
stylesheet: [
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
    $('#snp_save').show();
},
onLoad: function(editor){ zen_editor.bind(editor); }
});

</script>

