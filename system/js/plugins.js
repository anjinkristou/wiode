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

// Initialize plugins ####################################################################

$(function(){

    // Load plugins from selection
    $('#plugin_selector').change(function(){
        var val = $(this).val();
        if(val!=0){
            // Load selected plugin
            var config = val.split("||"); // 0 = name, 1 = win_width, 2 = folder, 3 = default
            
            loadModal('plugins/'+config[2]+'/'+config[3],config[0],config[1]);
            // Disable overlay?
            if(config[4]=='false'){ $('#modal_overlay').hide(); }
            // Set cur_plugin
            $('#cur_plugin').val(config[2]);
            
            // Resturn focus to first element
            $("#plugin_selector").val($("#plugin_selector option:first").val());
        }
    });

});

// Plugin functions ######################################################################

var plugin = {

    // Window / Plugin basic actions #################################

        // Close the modal window
        close: function(){ unloadModal(); $('#cur_plugin, #cur_plugin_data').val(''); },
        
        // Alert
        alert: function(msg){ alert(msg); },
        
        // Confirm
        confirm: function(msg) { var response = confirm(msg); if (response){ return true; }else{ return false; } },
        
        // Show/Hide Overlay
        overlay: function(action){ if(action=='show'){ $('#modal_overlay').show(); }else{ $('#modal_overlay').hide(); } },
        
        // Change Title
        title: function(title){ $('#modal_title span').html(title); },
        
        // Resize
        size: function(w,a){ if(a===undefined){ $('#modal').css({'width':w+'px','margin-left':'-'+Math.round(w/2)+'px'}); }
                             else{ $('#modal').animate({'width':w+'px','margin-left':'-'+Math.round(w/2)+'px'},a); } },
        
        // Load new file/page
        load: function(p){ var cur = $('#cur_plugin').val(); $('#modal_content').load('plugins/'+cur+'/'+p); },
       
        // Retrurn plugin path 
        path: function(){ return 'plugins/'+$('#cur_plugin').val(); },
        
    // Data actions ##################################################
    
        // Store data
        storedata: function(data) { $('#cur_plugin_data').val(data); },
        
        // Get data
        getdata: function() { return $('#cur_plugin_data').val(); },
        
        // Clear data
        cleardata: function() { $('#cur_plugin_data').val(''); },
        
    
    // User actions ##################################################
    
        // Return current user id
        curuser: function(){ return $('#cur_user_id').val(); },
        
        // Log user out of system
        logout: function(){ location.href='?logout=true'; },
    
    // Project actions ###############################################
    
        // Return current project id
        curproject: function(){ return $('#cur_project').val(); },
        
        // Change to supplied project id
        changeproject: function(p){ location.href='?project='+p; },
    
    // Editor actions ################################################
    
        // Return active editor id or 'false'
        checkeditor: function(){ return checkEditor(); },
        
        // Return open editors/tabs
        listeditors: function(){ return listEditors(); },
        
        // Return editor file path
        editorfilepath: function(e){ if(e===undefined){ if($('#cur_object').val()!=0){ return $('#tab_bar li#tab'+$('#cur_object').val()).attr('rel'); } }else{ return $('#tab_bar li#tab'+e).attr('rel'); } },
        
        // Return editor data
        editordata: function(e){ return editorData(e); },
        
        // Retrun selected text from editor
        editorgetselected: function(){ if(checkEditor()){ var editor = eval('editor'+$('#cur_object').val()); return editor.selection(); }else{ return ''; } },
        
        // Insert into editor (will replace selected text)
        editorinsert: function(data,e){ if(e===undefined){ if(checkEditor()){ var editor = eval('editor'+$('#cur_object').val()); editor.replaceSelection(data); } }else{ var editor=eval('editor'+e); editor.jumpToLine(1); editor.replaceSelection(data); } },
        
        // Replace entire contents of editor
        editorreplacedata: function(data,e){ if(e===undefined){ if(checkEditor()){ var editor = eval('editor'+$('#cur_object').val()); editor.setCode(data); } }else{ var editor=eval('editor'+e); editor.setCode(data); } },
        
        // Return editor object (See: http://codemirror.net/1/manual.html for options)
        editorobject: function(e){ if(e===undefined){ if(checkEditor()){ return eval('editor'+$('#cur_object').val()); } }else{ return eval('editor'+e); } },
        
    // API actions ###################################################
    
        // Execute API GET
        apiget: function(params){ return apiGet(params); },
        
        // Execute API POST
        apipost: function(params,fields){ return apiPost(params,fields); }


}

// Supporting functions ##################################################################

// Ensure that an editor is currently opened...
function checkEditor(){ if($('#cur_object').val()==0){ return false; }else{ return $('#cur_object').val(); } }

// List out all open editors/tabs...
function listEditors(){
    var c = $('#tab_bar li').length;
    if(c==0){
        return ''; // No open tabs
    }else{
        var arrTabs = new Array();
        $('#tab_bar li').each(function(){
            var id = $(this).attr('id').replace('tab','');
            var name = $(this).attr('rel');
            if($(this).hasClass('active')){ var act = 'true'; }else{ var act = 'false'; }
            if($(this).hasClass('modded')){ var mod = 'true'; }else{ var mod = 'false'; }
            if($(this).children('span').hasClass('in_use')){ var con = 'true'; }else{ var con = 'false'; }
            arrTabs.push([id,name,act,mod,con]);
        });
        
        data = '{"editors":[';        
        for (var i = 0; i < arrTabs.length; i++){
            if(i>0){ data = data + ','; }
            data = data + '{ "id":"'+arrTabs[i][0]+'","file":"'+arrTabs[i][1]+'","active":"'+arrTabs[i][2]+'","modified":"'+arrTabs[i][3]+'","conflict":"'+arrTabs[i][4]+'"}';
        }       
        data = data + ']}';
        
        return data;
    }
}

// Get editor contents...

function editorData(e){
    if(e===undefined){
        // Editor not specified, get active editor
        if(checkEditor()){
            var editor = eval('editor'+$('#cur_object').val()); 
            return editor.getCode();
        }else{
            return '';
        }
    }else{
        // Get content from specified editor
        var editor = eval('editor'+e);
        return editor.getCode();
    }
}

// API Get...

function apiGet(params){
    var call = $.param(params);
    var result = null;
    var scriptUrl = 'system/api/index.php?'+call;
    $.ajax({
        url: scriptUrl,
        type: 'get',
        dataType: 'html',
        async: false,
        success: function(data) { result = data; } 
    });
    return result;
}

// API Post...

function apiPost(params,fields){
    var call = $.param(params);
    var result = null;
    var scriptUrl = 'system/api/index.php?'+call;
    $.ajax({
        url: scriptUrl,
        type: 'post',
        data: fields,
        dataType: 'html',
        async: false,
        success: function(data) { result = data; } 
    });
    return result;
}
    