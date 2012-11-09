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

$obj_id = mysql_real_escape_string($_GET['id']);

// Process history delete
if(!empty($_GET['action'])){
    if($_GET['action']=='delete'){
        $rs = mysql_query("DELETE FROM wiode_object_history WHERE hst_obj_id=$obj_id");
        echo("<script>$(function(){ unloadModal(); });</script>");
    }
}

// Get object information

$rs = mysql_query("SELECT * FROM wiode_objects WHERE obj_id=$obj_id");
$row = mysql_fetch_array($rs);
$obj_name = stripslashes($row['obj_name']);
$obj_path = getPath($site,$obj_id,"");
$obj_ext = getExt($obj_name);

// Get history

$arrHistory = array();

$rs = mysql_query("SELECT hst_id, hst_usr_id, hst_datetime, hst_notes FROM wiode_object_history WHERE hst_obj_id=$obj_id ORDER BY hst_id DESC");
if(mysql_num_rows($rs)==0){
    $history_exists = false;
}else{
    $history_exists = true;
    $count = mysql_num_rows($rs);
    $i=0;
    while($row=mysql_fetch_array($rs)){
        // Get user information
        $rsUser = mysql_query("SELECT usr_login FROM wiode_users WHERE usr_id=" . $row['hst_usr_id']);
        if(mysql_num_rows($rsUser)==0){
            $hst_user = "Unknown User";
        }else{
            $rowUser = mysql_fetch_array($rsUser);
            $hst_user = stripslashes($rowUser['usr_login']);
        }
        
        $hst_notes = "";
        if($row['hst_notes']!=""){
            $hst_notes = " - " . shortenString(stripslashes($row['hst_notes']),60);
        }
        // Build array
        $arrHistory[] = array(($count-$i),$row['hst_id'],$hst_user,$row['hst_datetime'],$hst_notes);
        $i++;
    }
}


?>
<select id="history_list">
    
</select>

<div id="history_data">
</div>

<input type="button" id="btn_restore" value="Restore Selected Version" style="display: none;" />
<input type="button" id="btn_clear_history" value="Clear History" />
<input type="button" id="btn_save_notes" value="Save Notes" style="display: none;" />
<input type="button" value="Close" onclick="unloadModal();" />

<script>
    $(function(){
        // Create options with correct timezone
        <?php
    
        if($history_exists){
            // Load up drop-down menu
            foreach($arrHistory as $v){
                // Format datetime output
                $hst_datetime = explode(" ", $v[3]);
                $hst_date = explode("-",$hst_datetime[0]);
                $hst_time = explode(":",$hst_datetime[1]);
                $hst_month = date("M",mktime($hst_date[1]));
                $hst_day = $hst_date[2];
                $hst_year = $hst_date[0];
                $hst_hour = $hst_time[0];
                $hst_minute = $hst_time[1];
                $hst_second = $hst_time[2];
                ?>
                // Convert hour to detected timezone
                var timezone = jstz.determine_timezone();
                var offset = timezone.offset();
                var hsthour = Number(<?php echo($hst_hour); ?> + Number(offset.replace(":00","")));
                // AM/PM?
                var ampm = "am";
                if(hsthour>=12){
                    var ampm = "pm"
                    if(hsthour>12){ hsthour = hsthour-12; }
                }
                
                // Prevent negatives from 0
                if(hsthour<=0){
                    hsthour = 12+hsthour;
                    var ampm = "pm";
                }

                
                $('#history_list').append($("<option/>", {
                    value: '<?php echo($v[0] . "|" . $v[1] . "|" . $obj_ext); ?>',
                    text: '<?php echo("v." . $v[0] . " - " . $hst_month . " " . $hst_day . ", " . $hst_year . " at "); ?>'+hsthour+'<?php echo(":" . $hst_minute . ":" . $hst_second); ?>'+ampm+' by <?php echo($v[2] . $v[4]); ?>'
                }));
                <?php
            }
            ?>
            // Load first element into history_data
            loadHistoryObject($('#history_list').val());
            <?php
        }else{
            ?>
            $('#history_list').append($("<option/>", {
                    value: '0',
                    text: 'NO HISTORY EXISTS FOR SELECTED OBJECT'
                }));
            <?php
        }
        ?>
        
        // Change current history object
        $('#history_list').change(function(){
            loadHistoryObject($(this).val());
        });
        
    });
    
    
    function loadHistoryObject(val){
        arrVals = val.split("|");
        $('#history_data').load('system/php/modules/object_history_data.php?objid=<?php echo($obj_id); ?>&hstnum='+arrVals[0]+'&hstid='+arrVals[1]+'&ext='+arrVals[2]);
    }
    
    $('#btn_clear_history').click(function(){
        var answer = confirm("This will remove all history items for this file. Proceed?");
        if (answer){
            // Clear user state
            loadModalContent('system/php/modules/object_history.php?id=<?php echo($obj_id); ?>&action=delete');
        }
    });
    
</script>