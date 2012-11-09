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

function loadRoot(p){
    var cur_project = $('#cur_project').val();
    $('#sliders').append('<li class="panel"></li>');
    $('#sliders li:first').load('system/php/modules/simple_file_manager.php?p='+p);
    $('#top_menu .back').attr('rel',p);
}

function bindFileActions(){
    // Folders
    $('#main').delegate('#file_manager .folder','click',function(){  
        var rel = $(this).attr('rel');
        slideLeft('system/php/modules/simple_file_manager.php?p=/'+rel,rel);   
        // Set file "Back" rel value
        $('#top_menu .back').attr('rel',rel);
    });
        
    // Files
    $('#main').delegate('#file_manager .file','click',function(){
        if($(this).hasClass('lock')){
            alert('The file selected is not editable');
        }else{
            var rel = $(this).attr('rel');
            slideLeft('system/php/modules/simple_file_editor.php?p=/'+rel);
            $('#top_menu li').each(function(){ $(this).fadeIn(200); });
            $('#top_menu #save, #top_menu #ftp').attr('rel',rel);
        }
    });
    
    // Back / Close File    
    $('body').delegate('.back','click',function(){   
        var rel = $(this).attr('rel');
        slideRight('system/php/modules/simple_file_manager.php?p=/'+rel);
        $('#top_menu li').each(function(){ $(this).fadeOut(200); });
        $('#top_menu .back').attr('rel',rel);
    });
    
    // Notify of changes (*)
    $('body').delegate('#editor','keypress',function(){
        $('#save').html('Save*').css('font-weight','bold');
    });
    
    // Save changes
    $('#save').click(function(){
        saveFile();
    });
    
    // FTP Upload
    $('#ftp').click(function(){
        var rel = $(this).attr('rel');
        $('#processor').load('system/php/modules/simple_file_ftp.php?p='+rel);
    });
}

function loadWait(){ $('#modal_overlay').show(); }
function unloadWait(){ $('#modal_overlay').hide(); }

function slideLeft(u){
    loadWait();
    var w = $(window).width();
    var h = $(window).height();
    $('#sliders').append('<li class="panel"></li>');
    $('#sliders li:last').css('left',w+'px')
        .load(u, function(){
            $('#sliders li.panel:first').animate({'left':'-'+w+'px'}, 200, function(){ $(this).remove(); });
            $('#sliders li').animate({'left':'0px'}, 200);
            $('#editor').css('height',h+'px').autogrow();
            unloadWait();
        });
}

function slideRight(u){
    loadWait();
    var w = $(window).width();
    $('#sliders').prepend('<li class="panel"></li>');
    $('#sliders li:first').css('left','-'+w+'px')
        .load(u, function(){
            $('#sliders li.panel:last').animate({'left':w+'px'}, 200, function(){ $(this).remove(); });
            $('#sliders li').animate({'left':'0px'}, 200);
            unloadWait();
        });   
}

function saveFile(){
    var rel = $('#save').attr('rel');
    var scode = $('#editor').val();
    $.post('system/php/modules/simple_file_save.php?p='+rel, { code: scode },function(){ $('#save').html('Save').css('font-weight','normal');  });
    
}