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

// Get connection values

$ftp_host = urldecode($_POST['ftp_host']);
$ftp_port = urldecode($_POST['ftp_port']);
$ftp_user = urldecode($_POST['ftp_user']);
$ftp_password = urldecode($_POST['ftp_password']);
$ftp_dir = urldecode($_POST['ftp_remote_path']);

// Directory detection

function ftp_is_dir($conn,$object){
    $x = ftp_size($conn,$object);
    if($x!=-1){ return false; }else{ return true; }
}

$ftp_dir = trim ($ftp_dir,'/');

// Open FTP connection

$status = "connected";

if(!$conn = ftp_connect($ftp_host, $ftp_port)){
    $status = "Error connecting to host, please check connection";
}else{
    // Authenticate
    if(!$login_result = @ftp_login($conn, $ftp_user, $ftp_password)){
        $status = "Error authenticating, please check username and password";
    }
}
ftp_pasv($conn, true);


// Begin folder browser

if($status!="connected"){
    // Show error
    echo("<div class=\"ftp_error\"><span>$status</span></div>");
}else{
    // Show folder list
    $output = ftp_nlist($conn,$ftp_dir);
    foreach($output as $v){
        $v = basename($v);
        if(ftp_is_dir($conn,$ftp_dir . "/" . $v)){
            if($v!="."){
                if($v==".."){
                    if($ftp_dir!=""){
                        $arr_back_path = explode("/",$ftp_dir);
                        $arr_count = count($arr_back_path);
                        $i = 0;
                        $back_path = "";
                        while($i<($arr_count-1)){
                            $back_path .= "/" . $arr_back_path[$i];
                            $i++;
                        }
                        echo("<div class=\"ftp_folder\"><span rel=\"$back_path\">&laquo; Up Directory</span></div>");
                    }
                }else{   
                    echo("<div class=\"ftp_folder\"><span rel=\"" . $ftp_dir . "/" . $v . "\">$v</span></div>");
                }
            }
        }
    }
}

?>
<script type="text/javascript">

    $(function(){
        $('.ftp_folder').each(function(){
            $(this).click(function(){
                var p = $(this).children('span').attr('rel');
                $('#ftp_remote_path').val(p);
                browseFTP();
                $('#btn_save_ftp').show();
            });
        });
    });

</script>