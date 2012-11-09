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

<div id="colorpicker_region">

</div>

<input type="button" id="snp_insert" value="Insert HEX" onclick="insertColorHEX();" />
<input type="button" id="snp_insert" value="Insert RGB" onclick="insertColorRGB();" />
<input type="button" id="snp_close" value="Close" onclick="unloadModal();" />
<div class="clear"></div>

<script>

    // Init color picker
    $(function(){
    
        i = $('#cur_object').val();
        editor = eval('editor'+i);
        selected = editor.selection();
        sellength = selected.length;
        
        var colorRegEx = /^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/;
        seltest = colorRegEx.test(selected);

       
        // Fix format issues with rgb for parser
        returnRGBWrapper = true;
        if(selected.indexOf(',')>0 && selected.indexOf('rgb')){
            selected='rgb('+selected+')';
            returnRGBWrapper = false;
        }
    
        var color = new RGBColor(selected);
        if (color.ok) { // 'ok' is true when the parsing was a success
            $('#colorpicker_region').ColorPicker({flat: true, color: color.toHex() });
        }else{
            $('#colorpicker_region').ColorPicker({flat: true, color: '#454b8a' });
        }
    });
    
    // Handle insert
    function insertColorHEX(){    
        if(i!=0){
            var color = $('.colorpicker_hex input').val();
            if(sellength==3 || sellength ==6){
                if(seltest){
                    editor.replaceSelection(color);
                }else{
                    editor.replaceSelection('#'+color);
                }
            }else{
                editor.replaceSelection('#'+color);
            }
            unloadModal();
        }else{
            alert('No active editor file. Please open the file you wish to insert this color into.'); 
        }
    
    }
    
    function insertColorRGB(){    
        if(i!=0){
            var color = $('.colorpicker_rgb_r input').val()+','+$('.colorpicker_rgb_g input').val()+','+$('.colorpicker_rgb_b input').val();
            if(returnRGBWrapper==false){
                editor.replaceSelection(color);
            }else{
                editor.replaceSelection('rgb('+color+')');
            }
            unloadModal();
        }else{
            alert('No active editor file. Please open the file you wish to insert this color into.'); 
        }
    
    }

</script>