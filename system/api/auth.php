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

// Verify key

if(isset($_GET['key'])){
    $rs = mysql_query("SELECT apk_id FROM wiode_api_keys WHERE apk_key='" . mysql_real_escape_string($_GET['key']) . "'");
    if(mysql_num_rows($rs)!=0){ $key=true; }
}

// Pass-through for logged-in users (plugins)
if(isset($_SESSION['auth'])){ $key=true; }

?>