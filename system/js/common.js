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

// ONLOAD FUNCTIONS

$(function(){
    loadInstaller();
    loadLogin();
    fixFullWH();
    $(window).resize(function(){ fixFullWH(); });
    contextMenu();
    preventRtClick();
    loadFileManager();
    tabEvents();
    bindKeys();
    setBreakpoint();
    initTooltips();
    busyHandler();
    activityPoller();
    scrollTabs();
});

// Load Installer Screen ################################################################

function loadInstaller() { $('#installer').html('<div id=\"loader\"></div>').load('system/php/installer/default.php'); }

// Load Login Screen ####################################################################

function loadLogin(){ 
    $('#login').html('<div id=\"loader\"></div>').load('system/php/modules/login.php',function(){ 
        // Detect simple interface default
        var ww = $(window).width();
        //alert(ww);
        if(ww<1050){ $('#interface').val('1').attr('selected',true); } 
    });
}

// Activity Poller ######################################################################

function activityPoller(){
    setInterval(function(){
        // Clear all...
        $('#tab_bar li span.in_use').removeClass('in_use');
        // Get contents from poller iframe
        var o = $('#activity_poller').contents().find('#poller').html();
        var oarr = o.split(',');
        // Mark tabs if in use
        for (var i = 0; i < oarr.length; i++) {
            $('#tab_bar li#tab'+oarr[i]+'>span').addClass('in_use');
        }
    },3000);
}

// Prevent Right-Click ##################################################################

function preventRtClick(){ $('body').live("contextmenu", function(e){ e.preventDefault(); }); }

// Fix Full Height ######################################################################

function fixFullWH(){
    var wh = $(window).height();var ww = $(window).width();
    var topbh = $('#top_bar').height();var tabbh = $('#tab_bar').height();
    var lfw = $('#left_frame').width();
    $('#main').css('height' , (wh-topbh)+'px');
    $('#workspace').css('height', ((wh-topbh-tabbh)-2)+'px');
    $('#workspace').css('width', ((ww-lfw)-2)+'px');
    $('#workspace, .editor').css('width', ((ww-lfw)-2)+'px');
    //$('.CodeMirror-wrapping').css('height', ((wh-topbh-tabbh)-2)+'px');
    $('.CodeMirror-wrapping').css('width', ((ww-lfw)-43)+'px');
    $('#tab_container').css('width', ((ww-lfw)-30)+'px');
    scrolltabShowHide();
}

// Change Project #######################################################################

function changeProject(i){
    
    switch(i){
        case 'new':
            loadModal('system/php/modules/project_actions.php?action=create&id=new','Create Project');
            break;
        case 'restore':
            loadModal('system/php/modules/project_restore_archived.php','Restore Project');
            break;
        default:
            location.href='index.php?project='+i;
            break;
    }
}

function reloadProjectSelector(){ $('#project_dropdown').load('system/php/modules/project_selector.php?dynamic=t'); }


// Context Menu #########################################################################

function contextMenu(){
    $('#file_manager').delegate('li>span','contextmenu', function(e){
        // Get ID from REL
        var id = $(this).attr('rel');
        cm_obj = $('#file_manager li span[rel="'+id+'"]');
        cm_obj.addClass('menu_active');
        // Get cursor position
        var x = e.pageX;
        //var y = e.pageY;
        p = cm_obj.offset()
        // Show menu
        $('#context_menu').css({'top' : (p.top)-3, 'left' : (x-10)});
        $('#context_menu_content').load('system/php/modules/context_menu.php?i='+id, function(){  $('#context_menu').fadeIn(300); });
        // Hide menu on mouseleave & click events
        $('#context_menu').mouseleave(function(){ $(this).fadeOut(100); $('.menu_active').removeClass('menu_active'); });
        $('#context_menu').click(function(){ $(this).fadeOut(100); });
    });
}

// Modal Window #########################################################################

// loadModal(SCRIPT,TITLE,WIDTH*)

function loadModal(s,t,w){
    // Set defaults
    if(w===undefined){ w = 405; }
    // Set width and position
    $('#modal').stop(true, true).css({ 'width' : w+'px', 'left' : '50%', 'top': '15%', 'height' : 'auto', 'margin-left' : '-'+(Math.round(w/2))+'px' });
    // Set title
    $('#modal_title span').html(t);
    // Set close event
    $('#modal_title a').click(function(){ unloadModal(); });
    // Show modal/overlay and load script
    $('#modal, #modal_overlay').fadeIn(200);
    loadModalContent(s);
    $('#modal').draggable({ handle: '#modal_title' });
    // Bind Escape Key
    $(document).keyup(function(e) { if (e.keyCode == 27) { unloadModal(); } });
}

// showAlert(TITLE,MESSAGE)

function showAlert(t,m){
    $('#modal').css({ 'width' : '400px', 'left' : '50%', 'top': '15%', 'margin-left' : '-200px' });
    // Set title
    $('#modal_title span').html(t);
    // Set close event
    $('#modal_title a').click(function(){ unloadModal(); });
    // Show modal/overlay and load script
    $('#modal, #modal_overlay').fadeIn(200);
    $('#modal_content').html('<p>'+m+'</p><input class="bold" type="button" value="Close" onclick="unloadModal();" autofocus="autofocus" /><div class="clear"></div>');
    // Bind Escape Key
    $(document).keyup(function(e) { if (e.keyCode == 27) { unloadModal(); } });
}

function unloadModal(){ $('#modal, #modal_overlay').fadeOut(200); $('#tiptip_holder').hide();  }

function loadModalContent(s){ $('#modal_content').html('<div id="loader"></div>').load(s); }

// Load File Manager ####################################################################

function loadFileManager(){
    // Initial project load
    loadDirectory(0);
    
    // Folder Double-Click
    $('#file_manager').delegate('.folder>span','dblclick',function(){
        var obj = $(this).attr('rel');
        var node = $('span[rel="'+obj+'"]').parent('li');
        if(node.hasClass('expanded')){
            // Collapse
            node.children('ul').slideUp(200);
            node.removeClass('expanded');
        }else{
            loadDirectory(obj);
        }
    });
    
    // File Double-Click
    $('#file_manager').delegate('.file>span','dblclick',function(){
        var obj = $(this).attr('rel');
        // Check if file is already open
        if($('#editor'+obj).length){
            // Go To Tab
            $('#tab_bar li.active').removeClass('active');
            $('#tab_bar li#tab'+obj).addClass('active');
            $('.editor').hide();
            $('#editor'+obj).show();
            $('#file_manager li span.active').removeClass('active');
            $('#file_manager li span.selected').removeClass('selected');
            $('#file_manager li span[rel="'+obj+'"]').addClass('active');
        }else{
            // Load new editor instance
            $('#processor').load('system/php/modules/object_actions.php?action=load&id='+obj);    
        }
        checkActivity(obj);
        changeLineChar(0,0);
    });
    
    // Select folder (Single Click)
    $('#file_manager').delegate('.folder span','click',function(){
        var id = $(this).attr('rel');
        $('#file_manager li span.selected').removeClass('selected');
        if(!$(this).hasClass('selected')){ $(this).addClass('selected'); }
    });
    
    // Select file (Single Click)
    $('#file_manager').delegate('.file span','click',function(){
        var id = $(this).attr('rel');
        $('#file_manager li span.selected').removeClass('selected');
        if(!$(this).hasClass('selected')){ $(this).addClass('selected'); }
    });
    
    // Remove Current Selection
    $('#file_manager').delegate('.selected','click',function(){
        $('#file_manager li span.selected').removeClass('selected');
    });

    // Prevent text highlighting
    $('#file_manager li, #tab_bar, #top_bar, #tab_container').attr('unselectable', 'on')
        .css('-moz-user-select', 'none')
        .each(function() { this.onselectstart = function() { return false; }; });
    
}

function loadDirectory(obj){
    showBusy();
    var angle = 0;
    var rotator = setInterval(function(){ angle+=50; $("#project_rescan img").rotate(angle); },50);
    if(obj==0){
        // Project root
        $('.root').addClass('nodewait');
        $('#file_manager ul').load('system/php/modules/load_directory.php?p=0',function(){
            $('.root').removeClass('nodewait');
        });
    }else{
        // Sub-node
        var node = $('span[rel="'+obj+'"]').parent('li');
        node.addClass('nodewait');
        // Load and expand
        node.children('ul')
            .load('system/php/modules/load_directory.php?p='+obj, function(data){
                if(!node.hasClass('expanded')){
                    node.children('ul').hide();
                    if(data=="<ul></ul>"){
                        // Just expose the ul for DOM alteration
                        node.children('ul').show();
                    }else{
                        // Children li's exist, slide down
                        node.children('ul').slideDown(200);
                    }   
                }
                node.removeClass('nodewait'); 
                node.addClass('expanded');
            });
    }
    setTimeout(function(){ clearInterval(rotator); }, 500);
    $('#project_rescan img').rotate({angle:0});
}


// Tabs #################################################################################

function tabEvents(){
    
    // Change tabs
    $('#tab_bar').delegate('span','click',function(){
        var id = $(this).parent('li').attr('id');
        var id = id.replace("tab","");
        $('#tab_bar li.active').removeClass('active');
        $(this).parent('li').addClass('active');
        $('.editor').hide();
        $('#editor'+id).show();
        $('#cur_object').val(id);
        $('#file_manager li span.active').removeClass('active');
        $('#file_manager li span[rel="'+id+'"]').addClass('active');
        checkActivity(id);
        changeLineChar(0,0);
    });
    // Close tab
    $('#tab_bar').delegate('a','click',function(){
        var i = $(this).attr('rel');
        if($('#tab_bar li#tab'+i).hasClass('modded')){
            var answer = confirm("Are you sure you wish to close the file without saving?");
            if (answer){
                $('#processor').load('system/php/modules/object_actions.php?action=close&id='+i);
                // Check user and mark inactive if owner
                $.get('system/php/modules/object_activity.php?state=inactive&id='+i);
            }    
        }else{
            $('#processor').load('system/php/modules/object_actions.php?action=close&id='+i);
            // Check user and mark inactive if owner
            $.get('system/php/modules/object_activity.php?state=inactive&id='+i);
        }

    });
}

function markChanged(i){
    if(!($('#tab_bar li#tab'+i).hasClass('modded'))){
        var cur = $('#tab_bar li#tab'+i+' span').html();
        $('#tab_bar li#tab'+i).addClass('modded').children('span').prepend('<span class="mod">*</span>');
    }
}

var lcTimer;
function changeLineChar(line,char){
    $('#position_display #pd_line').html('Line: '+line);
    $('#position_display #pd_char').html('Char:'+char);
    clearTimeout(lcTimer);
    $('#position_display').animate({ opacity: 0.9 }, 200);
    lcTimer = setTimeout(function(){
        $('#position_display').animate({ opacity: 0.3 }, 200);
    },3000);
}

// Check if object is active ############################################################

function checkActivity(i){
    $('#processor').load('system/php/modules/object_activity.php?state=verify&id='+i);
}

// Save File ############################################################################

function saveFile(){
    var i = $('#cur_object').val();
    if(i!=0){
        var e = eval('editor'+i); 
        var c = e.getCode();
        $.post('system/php/modules/object_actions.php?action=save&id='+i, { code: c },
            function(){
                $('#tab_bar li#tab'+i).removeClass('modded');
                $('#tab_bar li#tab'+i+' span > span.mod').remove();
        });
        // Mark as inactive
        $.get('system/php/modules/object_activity.php?state=inactive&id='+i);
    }else{
        showAlert('Nothing to Save','Please open a file in the editor before attempting to save.');
    }
}

// View File ############################################################################

function viewFile(){
    var i = $('#cur_object').val();
    if(i!=0){
        $('#processor').load('system/php/modules/object_actions.php?action=view&id='+i);
    }else{
        showAlert('Nothing to View','Please open a file in the editor before attempting to view in browser.');
    }
}


// Key Bindings #########################################################################

$.ctrl = function(key, callback, args) {
    $(document).keydown(function(e) {
        if(!args) args=[]; // IE barks when args is null
        if(e.keyCode == key.charCodeAt(0) && e.ctrlKey) {
            e.preventDefault();
            callback.apply(this, args);
            return false;
        }
    });
};

function bindKeys(){
    
    // Save (s) ############################
    $.ctrl('S', function() { saveFile(); });
    
    // Upload Current (u) ##################
    $.ctrl('U', function() { quickUpload(); });
    
    // View Current (o) ####################
    $.ctrl('O', function() { viewFile(); });
    
    // Goto Line (g) #######################
    $.ctrl('G', function() { jumpTo(); });
    
    // Find String (f) ####################
    $.ctrl('F', function() { findString(); });
    
    // Replace String (r) #################
    $.ctrl('R', function() { replaceString(); });
    
    // Replace String (p) #################
    $.ctrl('P', function() { printEditor(); });
    
    // Snippets (i) #######################
    $.ctrl('I', function() { openSnippets(); });
    
    // Help (h) ###########################
    $.ctrl('H', function() { openHelp(); });
    
    // Color Picker (t) ###################
    $.ctrl('T', function() { openColorpicker(); });
    
    // Filemanager Keys ###################
    $(document).keyup(function(e){
        
        // Delete Selected ################
        if (e.keyCode == 46){
            var id = '';
            var id = $('#file_manager li span.selected').attr('rel');
            if(id){ loadModal('system/php/modules/object_actions.php?action=delete&id='+id,'Delete Object'); }
        }
        
        // Rename Selected ################
        if (e.keyCode == 113){
            var id = '';
            var id = $('#file_manager li span.selected').attr('rel');
            if(id){ loadModal('system/php/modules/object_actions.php?action=rename&id='+id,'Rename Object'); }
        }
        
    });
    
}

// Quick functions ######################################################################

function quickUpload(){
    saveFile();
    var i = $('#cur_object').val();
    if(i!=0){
        loadModal('system/php/modules/ftp_dialog.php?action=upload&id='+i+'&quick=t','FTP Upload');
    }else{
        showAlert('Nothing to Upload','Please open a file in the editor before attempting to upload.');
    }  
}

function quickView(){
    var i = $('#cur_object').val();
    if(i!=0){
        $('#processor').load('system/php/modules/object_actions.php?action=view&id='+i);
    }else{
        showAlert('Nothing to View','Please open a file in the editor before attempting to view in browser.');
    }
}

// Editor functions ####################################################################

function jumpTo(){
   var i = $('#cur_object').val();
    if(i!=0){
        loadModal('system/php/modules/editor_actions.php?action=jump&editor='+i,'GoToLine','300');
    }else{
        showAlert('No Active Editor','Please open a file in the editor.');
    } 
}

function findString(){
   var i = $('#cur_object').val();
    if(i!=0){
        loadModal('system/php/modules/editor_actions.php?action=find&editor='+i+'&fnum=1','Find','350');
        $('#modal_overlay').hide();
    }else{
        showAlert('No Active Editor','Please open a file in the editor.');
    } 
}

function replaceString(){
   var i = $('#cur_object').val();
    if(i!=0){
        loadModal('system/php/modules/editor_actions.php?action=replace&editor='+i,'Replace','350');
    }else{
        showAlert('No Active Editor','Please open a file in the editor.');
    } 
}

function printEditor(){
    var i = $('#cur_object').val();
    if(i!=0){
        window.open('system/php/modules/print.php?id='+i, 'print', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=700,height=500');
    }else{
        showAlert('No Active Editor','Please open a file in the editor.');
    } 
}


function openSnippets(){
    loadModal('system/php/modules/snippets.php','Code Snippets',600);
}

function openColorpicker(){
    loadModal('system/php/modules/colorpicker.php','Color Picker',385);
}


// PopUp Window #########################################################################

function popup(url,title,w,h){
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    var targetWin = window.open (url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
}

// Resource Tabs ########################################################################

$(function(){
    $('#tab_box_top li').click(function(){
        $('#tab_box_top li.active').removeClass('active');
        $(this).addClass('active');
        $('#resource_frame').attr('src',$(this).attr('rel'));            
    });
});

// Open Help Dialog #####################################################################

function openHelp(){ loadModal('system/php/modules/help_dialog.php','WIODE Help',400); }

// Highlight line number ################################################################

function setBreakpoint(){
    $('#main').delegate('.CodeMirror-line-numbers>div','click',function(){
        var line = $(this);
        if(line.hasClass('breakpoint')){
            line.removeClass('breakpoint');
        }else{
            line.addClass('breakpoint');
        }
    });
}

// Enable tooltips ######################################################################

function initTooltips(){
    // Top bar
    $("#top_menu li, #cur_user a, #project_rescan").tipTip({maxWidth: "auto", edgeOffset: 5, delay: 800, fadeIn: 100 });
}

function initTabTooltips(){
    // Top bar
    $("#tab_bar li").tipTip({maxWidth: "auto", edgeOffset: 13, delay: 500});
    $('#tab_bar').delegate('li','mousedown',function(){ $('#tiptip_holder').hide(); });
}

// ScrollTabs ###########################################################################

function scrollTabs(){
    $('#tab_scroll_left').click(function(){
        $('#tab_container').animate({scrollLeft:'+=173'},200, function(){ scrolltabShowHide(); });
        $(this).animate({opacity:1},30); $(this).animate({opacity:0.5},30);  
    });
    $('#tab_scroll_right').click(function(){
        $('#tab_container').animate({scrollLeft:'-=173'},200, function(){ scrolltabShowHide(); });
        $(this).animate({opacity:1},30); $(this).animate({opacity:0.5},30);   
    });
    
    
}

function scrolltabShowHide(){
    var tbw = ($('#tab_bar li').length)*173;
    var tcw = $('#tab_container').width();
    var sl = $('#tab_container').scrollLeft();
    if(tbw>tcw){
        $('#scroll_container').fadeIn(200);
    }else{
        $('#scroll_container').fadeOut(200);
    }
    
    // Lock/Unlock left scroller
    if(sl>=(tbw-tcw)){ $('#tab_scroll_left').addClass('locked'); }else{ $('#tab_scroll_left').removeClass('locked'); }
    // Lock/Unlock right scroller
    if(sl<=10){ $('#tab_scroll_right').addClass('locked'); }else{ $('#tab_scroll_right').removeClass('locked'); }       
    
}

// Busy handler #########################################################################

function busyHandler(){
    $('*').ajaxStart(function(){ showBusy(); });
    $('*').ajaxComplete(function(){ hideBusy(); });
}

closebusy = "";
function showBusy(){ clearTimeout(closebusy); $('#busy_overlay').show(); $('body').css('cursor','wait'); }
function hideBusy(){ closebusy = setTimeout( function() { $('#busy_overlay').hide(); $('body').css('cursor','default'); }, 50 ); }