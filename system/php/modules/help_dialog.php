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

?>
<div style="height: 400px; overflow: scroll;">

    <p>System / Editor:</p>
    
    <table class="data_help">
        <tr><th style="width: 100px;">Key</th><th>Action</th></tr>
        <tr><td>Ctrl+S</td><td>Save Current File</td></tr>
        <tr><td>Ctrl+U</td><td>Save And Upload</td></tr>
        <tr><td>Ctrl+O</td><td>Open Current File in Browser</td></tr>
        <tr><td>Ctrl+F</td><td>Find in Current File</td></tr>
        <tr><td>Ctrl+R</td><td>Find+Replace in Current File</td></tr>
        <tr><td>Ctrl+G</td><td>Go To Line</td></tr>
        <tr><td>Ctrl+P</td><td>Print Code</td></tr>
        <tr><td>Ctrl+H</td><td>Show Help</td></tr>
        <tr><td>Ctrl+I</td><td>Insert Snippet</td></tr>
        <tr><td>Ctrl+T</td><td>Color Picker</td></tr>
        <tr><td>DEL</td><td>Delete Selected</td></tr>
        <tr><td>F2</td><td>Rename Selected</td></tr>
    </table>
    
    <p>Zen Coding</p>
    
    <table class="data_help">
        <tr><th style="width: 100px;">Key</th><th>Action</th></tr>
        <tr><td>TAB</td><td>Convert Abbreviation String</td></tr>
        <tr><td>Ctrl+E</td><td>Expand Abbreviation</td></tr>
        <tr><td>Ctrl+D</td><td>Balance Tag Outward</td></tr>
        <tr><td>Shift+Ctrl+D</td><td>Balance Tag inward</td></tr>
        <tr><td>Shift+Ctrl+A</td><td>Wrap with Abbreviation</td></tr>
        <tr><td>Ctrl+Alt+→</td><td>Next Edit Point</td></tr>
        <tr><td>Ctrl+Alt+←</td><td>Previous Edit Point</td></tr>
        <tr><td>Ctrl+L</td><td>Select Line</td></tr>
        <tr><td>Ctrl+Shift+M</td><td>Merge Lines</td></tr>
        <tr><td>Ctrl+/</td><td>Toggle Comment</td></tr>
        <tr><td>Ctrl+J</td><td>Split/Join Tag</td></tr>
        <tr><td>Ctrl+K</td><td>Remove Tag</td></tr>
        <tr><td>Ctrl+Y</td><td>Evaluate Math Expression</td></tr>
        <tr><td>Ctrl+↑</td><td>Increment number by 1</td></tr>
        <tr><td>Ctrl+↓</td><td>Decrement number by 1</td></tr>
        <tr><td>Alt+↑</td><td>Increment number by 0.1</td></tr>
        <tr><td>Alt+↓</td><td>Decrement number by 0.1</td></tr>
        <tr><td>Ctrl+Alt+↑</td><td>Increment number by 10</td></tr>
        <tr><td>Ctrl+Alt+↓</td><td>Decrement number by 10</td></tr>
        <tr><td>Ctrl+.</td><td>Select Next Item</td></tr>
        <tr><td>Ctrl+,</td><td>Select Previous Item</td></tr>
        <tr><td>Ctrl+B</td><td>Reflect CSS Value</td></tr>
        
    </table>

</div>

<input type="button" id="help_close" value="Close" class="bold" onclick="unloadModal();" />
<div class="clear"></div>