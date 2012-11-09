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


// EVERYTHING YOU NEED: http://codemirror.net/1/js/mirrorframe.js

// Load Config File & Check Token ########################################################
function changeDir($up_n){
    $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
    for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
}
require_once(changeDir(3)."/config.php");
require_once(changeDir(1)."/check_token.php");
// #######################################################################################

$action = $_GET['action'];
$editor = $_GET['editor'];

switch($_GET['action']){

    // Jump to line #####################################################################
    case 'jump':
            ?>
            <p>Line Number:</p>
            <p class="inline_error hide">Couldn't go to specified line number...</p>
            <input type="text" id="linenumber" value="" autofocus="autofocus" />
            <input type="button" class="bold" value="Go To Line" onclick="jumpToLine();" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <?php
        break;
        
    // Find text ########################################################################
    case 'find':
    
            $fval = "";
            if(!empty($_GET['fval'])){ $fval = urldecode($_GET['fval']); }
            switch($_GET['fnum']){
                case 2: $fbutton = "Find Next"; break;
                default: $fbutton = "Find";
            }
    
            ?>
            <p>Search String:</p>
            <input type="text" id="findstring" value="<?php echo($fval); ?>" onkeydown="$('#findbutton').val('Find');" autofocus="autofocus" />
            <input type="button" class="bold" id="findbutton" value="<?php echo($fbutton); ?>" onclick="findStringExec();" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <?php
    
        break;
        
    // Replace text #####################################################################
    case 'replace':
            ?>
            <table border="0" width="100%">
            <tr>
                <td><p>Search:</p></td>
                <td style="width: 15px;">&nbsp;</td>
                <td><p>Replace:</p></td>
            </tr>
            <tr valign="top">
                <td><input type="text" id="replacestring1" value="" /></td>
                <td>&nbsp; </td>
                <td><input type="text" id="replacestring2" value="" /></td>
            </tr>
            </table>
            <input type="button" rel="0" class="bold" value="Replace" onclick="replaceStringExec();" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <?php
        break;
        
    default:
        $do = "nothing";
}
        
?>
<div class="clear"></div>
<script>

    editor = eval('editor<?php echo($editor); ?>');

    $(function(){
    
        hideBusy();
    
        // Look for selection
        var selected = '';
        var i = $('#cur_object').val();
        editor = eval('editor'+i);
        selected = editor.selection();
        
        // Set contents of find and replace fields with selected val
        if(selected!=''){
            setTimeout(function(){ 
                $('#findstring, #replacestring1').val(selected);
            }, 200);
            // The timeout is because of some buggy shit in Chrome...
        }
    
        $('input[type="text"]:first').focus();
    
        $('#linenumber').keypress(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { jumpToLine(); }
        });
        
        $('#findstring').keypress(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { findStringExec(); }
        });
        
        $('#replacestring2').keypress(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { replaceStringExec(); }
        });
        
    });

    // Go To Line

    function jumpToLine(){
        var line = $('#linenumber').val();
        if (line && !isNaN(Number(line))){
            // Jump
            editor.jumpToLine(Number(line));
            // Highlight
            $('#editor<?php echo($editor); ?> .CodeMirror-line-numbers>div').filter(function(){
                return $(this).html() == line;
            }).addClass('breakpoint');
            // Hide modal
            unloadModal();
        }else{
            $('.inline_error').fadeIn(200);
        }
    }
    
    // Find String 
    
    text = "";
    itteration = 0;
    first = true
    
    function findTotalOcc(text){
        c = 0; i = 0;f = true;
        do{
          var cursor = editor.getSearchCursor(text, f);
          f = false;
          while (cursor.findNext()) { c++; }
          i++;
        }while(i<=1);
        return c;
    }
    
    function findStringExec(){
        if(text!=$('#findstring').val()){
            text = $('#findstring').val();
            itteration = 0;
        }
        if(itteration==0){ totalOcc = findTotalOcc(text); }
        if(itteration==0 && totalOcc==0){
            $('#findbutton').val('Not Found!');
        }else{
            if($('#findbutton').val()=='End of File. Restart?'){
                editor.jumpToLine(Number(1));
                $('#findstring').focus();
                $('#findbutton').focus().val('Find Next');
            }       
            var cursor = editor.getSearchCursor(text, first);
            if(cursor.findNext()){
                cursor.select();
                $('#findbutton').val('Find Next').focus();
            }else{
                var cursor = editor.getSearchCursor(text, first);
                if(!cursor.findNext()){
                    $('#findstring').focus();
                    $('#findbutton').val('End of File. Restart?').focus();
                }
            }
            itteration++;
        }
    }
    
    // Replace String
    
    function replaceStringExec(){
        var from = $('#replacestring1').val();
        var to = $('#replacestring2').val();
    
        var cursor = editor.getSearchCursor(from, false);
        while (cursor.findNext())
          cursor.replace(to);
        unloadModal();
    }

</script>