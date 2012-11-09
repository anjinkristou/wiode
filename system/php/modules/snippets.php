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

?>
<input type="text" id="snp_filter" value="Filter" class="snp_unfiltered" style="width: 550px;" />

<div id="snp_region">

</div>

<input type="button" id="snp_back" value="Back" onclick="loadSnippets();" style="display: none;" />
<input type="button" id="snp_close" value="Close" onclick="unloadModal();" />
<div class="btn_break" style="display: none;"></div>
<input type="button" id="snp_insert" value="Insert Snippet" onclick="insertSnippet();" style="display: none;" />
<input type="button" id="snp_save" value="Save" onclick="saveSnippet();$(this).hide();" style="display: none;" />
<input type="button" id="snp_delete" value="Delete" onclick="deleteSnippet();" style="display: none;" />
<div class="clear"></div>

<script>

    $(function(){
        loadSnippets();
        $('#snp_filter').keyup(function(){ loadSnippets($(this).val()); });
        $('#snp_filter').focus(function(){
            if($(this).hasClass('snp_unfiltered')){
                $(this).val('').removeClass('snp_unfiltered');
            }
        });
    });
    
    function loadSnippets(){
        // Hide editor buttons
        $('#snp_insert, #snp_back, #snp_save, #snp_delete, .btn_break').hide();
        // Show list
        if($('#snp_filter').hasClass('snp_unfiltered')){
            var f = '';
        }else{
            var f = escape($('#snp_filter').val());
        }
        
        $('#snp_region').load('system/php/modules/snippets_list.php?filter='+f);

    }
    
    function insertSnippet(){

        var i = $('#cur_object').val();
        if(i!=0){
            var e = eval('editor_snippet'); 
            var c = e.getCode();
        
            if($('#snp_save').is(":visible")){
                var answer = confirm("Save changes to the snippet?");
                if (answer){ saveSnippet(); }
            }
            editor = eval('editor'+i);
            editor.replaceSelection(c);
            unloadModal();
        }else{
            alert('No active editor file. Please open the file you wish to insert this snippet into.'); 
        }
        
    }
    
    function saveSnippet(){
        var e = eval('editor_snippet'); 
        var c = e.getCode();
        $.post('system/php/modules/snippets_editor.php?id='+$('#snp_id').val()+'&save=true', { snp_title: $('#snp_title').val(), snp_content: c },
        function(data) {
            $('#snp_region').html(data);
        });
    }
    
    function deleteSnippet(){
        var answer = confirm("Delete the current snippet?");
        if (answer){
            $('#snp_delete').hide();
            $.get('system/php/modules/snippets_editor.php?id='+$('#snp_id').val()+'&delete=true');
            setTimeout("loadSnippets();",500);
        }       
    }

</script>