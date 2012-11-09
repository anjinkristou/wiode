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

if(!$api_call){
    // Load Config File & Check Token ########################################################
    function changeDir($up_n){
        $split = explode("/",$_SERVER['SCRIPT_FILENAME']); array_pop($split);
        for ($i=1; $i<=$up_n; $i++){ array_pop($split); } return implode("/",$split);
    }
    require_once(changeDir(3)."/config.php");
    require_once(changeDir(1)."/check_token.php");
    // #######################################################################################
}

// Set reloader for file manager
$reload = false;

// Set load file
$loadfile = false;
$loadNonEditable = false;

// Set duplicate object
$duplicateObject = false;

// Set rename object
$renameObject = false;

// Set delete object
$deleteObject = false;

// Set create object
$createObject = false;

// Set close file
$closeFile = false;

// Set view file
$viewfile = false;

// Get action type
$action = htmlspecialchars($_GET['action']);

$obj_extension = "";

// Get object data
$obj_id = mysql_real_escape_string($_GET['id']);
if($obj_id=='root'){
    // Project root
    $obj_name = "";
    if($api_call){ 
        $obj_project = $_GET['project']; 
    }else{
        $obj_project = mysql_real_escape_string($_SESSION['curproject']);
    }
    $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$obj_project");
    $row = mysql_fetch_array($rs);
    $obj_type = 0;
    $obj_path = $site['absroot'] . "/" . $row['prj_name'] . "/";
}else{
    $rs = mysql_query("SELECT * FROM wiode_objects WHERE obj_id=$obj_id");
    $row = mysql_fetch_array($rs);
    $obj_project = $row['obj_project'];
    $obj_name = stripslashes($row['obj_name']);
    $obj_type = $row['obj_type'];
    $obj_parent = $row['obj_parent'];
    $obj_path = getPath($site,$obj_id,"");
    $obj_url =  getPath($site,$obj_id,"",1);
    $obj_extension = "";
    if($obj_type==2){ $obj_extension = getExt($obj_name); }
    $rs = mysql_query("SELECT prj_name FROM wiode_projects WHERE prj_id=$obj_project");
    $row = mysql_fetch_array($rs);
    $prj_name = $row['prj_name'];
}

switch ($action){

    // Rescan a directory ###############################################################
    
    case 'rescan':
        scanDirectory($obj_project, $obj_id, $obj_path);
        echo("<script type=\"text/javascript\">$(function(){ reload(); });</script>");
        break;
    
    // Set 'Open' Value #################################################################
    case 'setopenval':
        $rs = mysql_query("SELECT * FROM wiode_user_state WHERE ust_obj_id=" . $obj_id);
        if(mysql_num_rows($rs)==0){
            // Set to open (create entry)
            $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id, ust_obj_type, ust_obj_id) VALUES (" . mysql_real_escape_string($_SESSION['auth']) . ", 1, " . $obj_id . ")");
        }else{
            // Set to closed (delete entry)
            $row = mysql_fetch_array($rs);
            $rs = mysql_query("DELETE FROM wiode_user_state WHERE ust_id=" . $row['ust_id']);
        }
        break;
        
    // Duplicate Object #################################################################
    
    case 'duplicate':
        if(!isset($_GET['dp'])){
            ?>
            <p>Source Object:</p>
            <input type="text" value="<?php echo(str_replace($site['absroot'],"",$obj_path)); ?>" readonly="readonly" />
            
            <p>Destination Project:</p>
            <select id="dup_proj" onchange="loadFolderStack(this.value);">
                <?php
                if($obj_id!="root"){
                    $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_status=0 ORDER BY prj_name");
                }else{
                    $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_status=0 AND prj_id!=" . mysql_real_escape_string($_SESSION['curproject']) . " ORDER BY prj_name");
                }
                while($row=mysql_fetch_array($rs)){
                    $selected = "";
                    if($_SESSION['curproject']==$row['prj_id']){ $selected = "selected=\"selected\""; }
                    echo("<option $selected value=\"" . htmlspecialchars($row['prj_id']) . "\">" . htmlspecialchars($row['prj_name']) . "</option>");    
                }
                ?>
            </select>
            


            <p>Destination Folder:</p>
            <div id="dup_dest"></div>
            <input type="text" readonly="readonly" id="dup_dest_display" />
            
            <input type="button" class="bold" value="Build Copy" onclick="duplicateObject();" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <?php
        }else{
            
            $dest_proj = mysql_real_escape_string($_GET['dp']);
            $dest_dir = mysql_real_escape_string($_GET['dd']);
            
            if($dest_dir==0){
                // Copy to project root
                $rs = mysql_query("SELECT * FROM wiode_projects WHERE prj_id=$dest_proj");
                $row = mysql_fetch_array($rs);
                $dest_path = $site['absroot'] . "/" . $row['prj_name'];
                //$obj_name = "";
            }else{
                $dest_path = getPath($site,$dest_dir,"");    
            }

            $obj_path = rtrim($obj_path,"/");
            $dest_path_raw = $dest_path . "/" . $obj_name;
            
            $i = 0;
            $ext = getExt($obj_name);
            $raw_name = str_replace(".$ext","",$obj_name);
            
            // Root-to-root copy
            if($_GET['id']=='root' && $_GET['dd']==0){
                $dest_path = str_replace("//","/",$dest_path);
            }else{
                // Append (i) for duplicates
                if(file_exists($dest_path_raw)){
                    $file_break = ""; if($obj_type==2){ $file_break = "."; }
                    while(file_exists($dest_path_raw)){
                        $i++;
                        $raw_name = str_replace("($i)","",$raw_name);
                        $new_name = $raw_name . "($i)" . $file_break . $ext;     
                        $dest_path_raw = $dest_path . "/" . $raw_name . "($i)" . $file_break . $ext;
                    }
                    $dest_path = $dest_path_raw;
                }else{
                    $dest_path = $dest_path . "/" . $obj_name;
                    $new_name = $obj_name;
                }
            }

            
            // Proceed with copy
            
            $GLOBALS['scriptDomDup']="";
            
            function createDupDomObject($obj_type,$obj_id,$obj_name,$obj_parent){
                if($obj_parent==0){ $obj_parent="root"; }
                if($obj_type==1){
                    // Create folder 
                    $GLOBALS['scriptDomDup'] .= "var obj = '<li class=\"folder collapsed\"><span rel=\"" . $obj_id . "\" title=\"" . $obj_name . "\">" . shortenString($obj_name, 25) . "</span></li>';";
                }else{
                    // Create file
                    $obj_ext = getExt($obj_name);
                    $GLOBALS['scriptDomDup'] .= "var obj = '<li class=\"file ext_" . $obj_ext . "\"><span rel=\"" . $obj_id . "\" title=\"" . $obj_name . "\">" . shortenString($obj_name, 25) . "</span></li>';";
                }
                $GLOBALS['scriptDomDup'] .= "var parent = $('#file_manager li span[rel=\"" . $obj_parent . "\"]').parent('li'); if(parent.children('ul').size()>0){ parent.children('ul:first').append(obj); }else{ parent.append('<ul>'+obj+'</ul>'); }";         
            }

            if($obj_type==2){
                // Copy file
                @copy($obj_path,$dest_path);
                // Insert into db
                $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES (" . mysql_real_escape_string($_GET['dp']) . ",'" . mysql_real_escape_string($new_name) . "',2,$dest_dir)");  
                // Create DOM object
                if($_SESSION['curproject']==$_GET['dp']){ createDupDomObject(2,mysql_insert_id(),$new_name,$dest_dir); }             
                                
            }else{
                // Copy directory 
                function recurse_copy($src,$dst,$parent) { 
                    $dir = opendir($src); 
                    @mkdir($dst);
                    // Insert into db
                    $new_name = end(explode("/",$dst));
                    $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES (" . mysql_real_escape_string($_GET['dp']) . ",'" . mysql_real_escape_string($new_name) . "',1,$parent)");
                    // Create DOM object
                    if($_SESSION['curproject']==$_GET['dp']){ createDupDomObject(1,mysql_insert_id(),$new_name,$parent); }
                    $parent = mysql_insert_id();
                    // Traverse
                    while(false !== ($file = readdir($dir))) { 
                        if (( $file != '.' ) && ( $file != '..' )) { 
                            if (is_dir($src . '/' . $file)) {                                
                                // Move to next
                                recurse_copy($src . '/' . $file,$dst . '/' . $file,$parent); 
                            } 
                            else { 
                                // Insert into db
                                $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES (" . mysql_real_escape_string($_GET['dp']) . ",'" . mysql_real_escape_string($file) . "',2,$parent)");                             
                                // Create DOM object
                                if($_SESSION['curproject']==$_GET['dp']){ createDupDomObject(2,mysql_insert_id(),$file,$parent); }    
                                @copy($src . '/' . $file,$dst . '/' . $file); 
                            } 
                        } 
                    } 
                    closedir($dir); 
                }
                
                recurse_copy($obj_path,$dest_path,$dest_dir);
            }
            
            
            ?>
            <p>Copy Procedure Complete.</p>
            <?php
            $duplicateObject = true;  
        }
        break;
        
    // Rename Object ####################################################################
    case 'rename':
    
        // Define form
        $renameForm =  "<p>Rename the object:</p>";
        $renameForm .= "<input type=\"text\" id=\"obj_rename\" class=\"obj_name\" value=\"" . $obj_name . "\" autofocus=\"autofocus\" />";
        $renameForm .= "<input type=\"button\" class=\"bold\" value=\"Rename\" onclick=\"renameObject();\" />";
        $renameForm .= "<input type=\"button\" value=\"Cancel\" onclick=\"unloadModal();\" />";
    
        if(!isset($_GET['name'])){
            // Show rename form
            echo($renameForm);
        }else{
            // Prevents trying to rename root (API)
            if($obj_id!=0){ 
                // Process rename
                $obj_new_name = mysql_real_escape_string(urldecode($_GET['name']));
                $obj_new_path = str_replace($obj_name,$obj_new_name,$obj_path);
                if(!file_exists($obj_new_path)){
                    if(!$api_call){ echo("<p>Renaming Object...</p>"); }
                    // Update data
                    $rs = mysql_query("UPDATE wiode_objects SET obj_name='$obj_new_name' WHERE obj_id=$obj_id");
                    // Modify file
                    rename($obj_path,$obj_new_path);
                    $renameObject = true;
                    if($api_call){ echo('pass'); }
                }else{
                    // Display error and rename form
                    $type_def = strtolower($objType[$obj_type]);
                    if($api_call){
                        echo("fail");
                    }else{
                        echo("<p class=\"inline_error\">A $type_def with the name suppied already exists.</p>$renameForm");
                    }
                }
            }else{
                echo("fail");
            }
        }
        
        break;
        
    // Delete Object ####################################################################
    case 'delete':
        
        if(!isset($_GET['confirmed'])){
            // Show confirmation prompt
            ?>
            <p>Are you sure you want to delete the <?php echo($objType[$obj_type] . " \"" . $obj_name . "\""); ?> from the project? This action cannot be undone.</p>
            <input type="button" id="obj_delete_btn" class="bold" value="Confirm" autofocus="autofocus" onclick="loadModalContent('system/php/modules/object_actions.php?action=delete&id=<?php echo($obj_id); ?>&confirmed=y');" />
            <input type="button" value="Cancel" onclick="unloadModal();" />
            <script>$(function(){ $('#obj_delete_btn').focus(); });</script>
            <?php
        }else{
            if(!$api_call){ echo("<p>Deleting Object(s)...</p>"); }
            // Loop through to create delete array            
            $objects = array();
            function createDelArray($project, $parent, $site){
                global $objects;
                $rs = mysql_query("SELECT obj_id FROM wiode_objects WHERE obj_project=" . mysql_real_escape_string($project) . " AND obj_parent=" . mysql_real_escape_string($parent));
                if(mysql_num_rows($rs)!=0){
                    while($row = mysql_fetch_array($rs)){
                        // Add to array
                        array_push($objects,$row['obj_id']);    
                        // Recurse
                        createDelArray($project,$row['obj_id'], $site);    
                    }
                }
            }
            
            // Recursively remove all files and folders
            function rrmdir($dir) { 
               if (is_dir($dir)) { 
                 $objects = scandir($dir); 
                 foreach ($objects as $object) { 
                   if ($object != "." && $object != "..") { if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);  } 
                 } reset($objects); rmdir($dir); 
               } 
             }

            // Process delete
            if($obj_type==1){ // IS FOLDER
                // Create array of all objects
                createDelArray($obj_project,$obj_id,$site);        
                // Delete sub-objects from DB
                foreach($objects as $v){
                    mysql_query("DELETE FROM wiode_objects WHERE obj_id=" . mysql_real_escape_string($v));
                }
                // Delete top-level object from DB
                $rs = mysql_query("DELETE FROM wiode_objects WHERE obj_id=$obj_id");
                rrmdir($obj_path);    
            }else{ // SINGLE FILE
                // Remove file
                unlink($obj_path);
                // Delete object from DB
                $rs = mysql_query("DELETE FROM wiode_objects WHERE obj_id=$obj_id");
            }
            $deleteObject = true; 
        }
        break;
        
    // Create Object ####################################################################
    case 'create':
    
        $obj_name = "";
        $obj_type = 2;
        
        // Define form
        function buildCreateForm($t,$n){
            $select_file = ""; $select_folder = "";
            if($t==2){ $select_file = "selected=\"selected\""; } else { $select_folder = "selected=\"selected\""; }
            $createForm =  "<p>Type of Object:</p>";
            $createForm .= "<select id=\"obj_type\">";
            $createForm .= "<option $select_file value=\"2\">File</option>";
            $createForm .= "<option $select_folder value=\"1\">Folder</option>";
            $createForm .= "</select>";
            $createForm .= "<p>Object Name:</p>";
            $createForm .= "<input type=\"text\" id=\"obj_create\" class=\"obj_name\" value=\"" . $n . "\" autofocus=\"autofocus\" />";
            $createForm .= "<input type=\"button\" class=\"bold\" value=\"Create\" onclick=\"createObject();\" />";
            $createForm .= "<input type=\"button\" value=\"Cancel\" onclick=\"unloadModal();\" />";
            
            return $createForm;
        }
            
        if(!isset($_GET['name'])){
            // Show object creation form
            echo(buildCreateForm($obj_type,$obj_name));
        }else{
        
            $write_to_db = false;
            $obj_type = $_GET['type'];
            $obj_name = urldecode($_GET['name']);
            $obj_new_path = $obj_path . "/" . $obj_name;
                  
            
            if($obj_type==1){
                // Create folder
                if(!file_exists($obj_new_path)){ mkdir($obj_new_path, 0777, true); $write_to_db = true; }
            }else{
                // Create file
                if(!file_exists($obj_new_path)){
                    $obj_ext = getExt($obj_name);
                    $content = "";
                    $fh = fopen($obj_new_path, 'w') or die("Error creating file.");
                    fwrite($fh, $content);
                    fclose($fh);
                    $write_to_db = true;
                }
            }
            
            // Root level?
            if($obj_id=='root'){ $obj_parent_id=0; }else{ $obj_parent_id=$obj_id; }
            // Safe SQL
            $obj_name = mysql_real_escape_string($obj_name);
            // Insert into database
            if($write_to_db==true){ // Ensure the file didn't already exist
                if(!$api_call){ echo("<p>Creating Object...</p>"); }
                $rs = mysql_query("INSERT INTO wiode_objects (obj_project,obj_name,obj_type,obj_parent) VALUES ($obj_project,'" . mysql_real_escape_string($obj_name) . "',"  . mysql_real_escape_string($obj_type) . "," . mysql_real_escape_string($obj_parent_id) . ")");
                $obj_new_id = mysql_insert_id();
                $createObject = true;
                if($api_call){ echo(mysql_insert_id()); }
            }else{
                // Display error and create form
                if($api_call){
                    echo("fail");
                }else{
                    $type_def = strtolower($objType[$obj_type]);
                    echo("<p class=\"inline_error\">A $type_def with the name supplied already exists.</p>" . buildCreateForm($obj_type,$obj_name));
                }
            }
            if(!$api_call){
            // Make sure the folder that is having the create function applied to is 'open'
                if($obj_id!=0){
                    $rs = mysql_query("SELECT ust_id FROM wiode_user_state WHERE ust_obj_id=$obj_id");
                    if(mysql_num_rows($rs)==0){
                        // Set to open (create entry)
                        $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id, ust_obj_type, ust_obj_id) VALUES (" . mysql_real_escape_string($_SESSION['auth']) . ", 1, $obj_id)");
                    }
                }
            }
        }
        
        break;
        
    // Upload Objects ###################################################################
    case 'upload':
        echo("<div class=\"upload_note\">CTRL+Click selects multiple files</div><input id=\"file_upload\" name=\"file_upload\" type=\"file\" />");
        echo("<input type=\"button\" value=\"Cancel\" onclick=\"unloadModal();\" />");
        // Make sure the folder that is having the create function applied to is 'open'
        if($obj_id!=0){
            $rs = mysql_query("SELECT ust_id FROM wiode_user_state WHERE ust_obj_id=$obj_id");
            if(mysql_num_rows($rs)==0){
                // Set to open (create entry)
                $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id, ust_obj_type, ust_obj_id) VALUES (" . mysql_real_escape_string($_SESSION['auth']) . ", 1, $obj_id)");
            }
        }
        break;
        
    // Load File Into Editor ############################################################
    case 'load':
        
        $createRestorePoint = false;
        if(in_array(strtolower(getExt($obj_name)), $arrEditable)){
            $loadfile = true;
            if(empty($_GET['savestate'])){
                $rs = mysql_query("INSERT INTO wiode_user_state (ust_usr_id,ust_obj_type,ust_obj_id,ust_obj_project) VALUES (" . mysql_real_escape_string($_SESSION['auth']) . ",2,$obj_id,$obj_project)");
            }
            // Check for restore point(s)
            $rs = mysql_query("SELECT hst_id FROM wiode_object_history WHERE hst_obj_id=$obj_id");
            if(mysql_num_rows($rs)==0){
                // Check file (no need to create restore for new/blank files)
                $code = file_get_contents($obj_path);
                if($code!=""){
                    // Create starting restore point
                    $createRestorePoint = true;
                }
            }        
            
                
        }else{
            $loadNonEditable = true;
        }
        
        break;
        
    // Save file ########################################################################
    case 'save':
            
        // Save current file         
        if($api_call){ $usr_id = 0; }else{ $usr_id = mysql_real_escape_string($_SESSION['auth']); }
        $code = $_POST['code'];
        $hst_notes = mysql_real_escape_string("");
        if(!empty($_POST['hstnotes'])){ $hst_notes = $_POST['hstnotes']; }
        
        if($api_call){ if(isset($_POST['notes'])){ $hst_notes = mysql_real_escape_string($_POST['notes']); } }

        // Save file
        $fh = fopen($obj_path, 'w') or die("can't open file");
        if(get_magic_quotes_gpc()){ $code = stripslashes($code); }
        fwrite($fh, $code);
        fclose($fh);
    
        // Ensure history table exists
        $sql="SELECT * FROM wiode_object_history LIMIT 1";
        $result=@mysql_query($sql);
        if(!$result){ mysql_query('CREATE TABLE `wiode_object_history` (`hst_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `hst_obj_id` int(11) NOT NULL, `hst_usr_id` int(11) NOT NULL, `hst_data` longtext, `hst_datetime` datetime, `hst_notes` longtext, PRIMARY KEY (`hst_id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;');}
    
        // Save to history
        if(isset($_SESSION['auth']) || isset($hst_notes)){
            $rs = mysql_query("INSERT INTO wiode_object_history (hst_obj_id,hst_usr_id,hst_data,hst_datetime,hst_notes) VALUES ($obj_id,$usr_id,'".mysql_real_escape_string($code)."',UTC_TIMESTAMP(),'" . mysql_real_escape_string($hst_notes) . "')");
        }
        
        break;
        
    // Close file #######################################################################
    case 'close':
        $closeFile = true;
        $rs = mysql_query("DELETE FROM wiode_user_state WHERE ust_usr_id=" . mysql_real_escape_string($_SESSION['auth']) . " AND ust_obj_id=$obj_id");
        break;
        
    // View file in browser #############################################################
    case 'view':
        $viewfile = true;
        break;
        
    // Default - do nothing (error trap) ################################################
    default:
        break;
    
}

// BEGIN JAVASCRIPT ACTIONS #############################################################
// ######################################################################################
// ######################################################################################

if(!$api_call){

// Reload file manager ##################################################################

if($reload==true){
?>
<script type="text/javascript">$(function(){ reload(); });</script>
<?php
}

// Duplicate Object(s) ##################################################################

if($duplicateObject==true){
?>
<script type="text/javascript">
    $(function(){ <?php echo($GLOBALS['scriptDomDup']); ?> unloadModal(); });    
</script>
<?php
}

// Rename Object ########################################################################

if($renameObject==true){
?>
<script type="text/javascript">
    $(function(){ 
        var obj = $('#file_manager li span[rel="<?php echo($obj_id); ?>"]');
        var new_name = '<?php echo($obj_new_name); ?>';
        var new_ext = new_name.split('.').pop();
        // Modify name
        obj.attr('title',new_name);
        obj.html('<?php echo(shortenString($obj_new_name,15)); ?>');
        
        // If this is a file rename...
        if(obj.parent('li').hasClass('file')){
            
            // Modify extension
            obj.parent('li').removeClass('ext_<?php echo($obj_extension); ?>');
            obj.parent('li').addClass('ext_'+new_ext);
            
            // Modify open tab
            <?php
            $dispPath = str_replace($site['absroot'],"",$obj_new_path);
            $dispPath = str_replace($prj_name."/","",$dispPath);
            ?>
            $('#tab_bar li#tab<?php echo($obj_id); ?>').attr('title','<?php echo($dispPath); ?>');
            $('#tab_bar li#tab<?php echo($obj_id); ?>').html('<span><?php echo(shortenString($obj_new_name,15)); ?></span><a rel="<?php echo($obj_id); ?>" class="close">X</a>');
        }
        
        // Close Modal
        unloadModal();
        
    });
</script>
<?php
}

// Delete Object ########################################################################

if($deleteObject==true){
?>
<script type="text/javascript">
    $(function(){ 
        var obj = $('#file_manager li span[rel="<?php echo($obj_id); ?>"]').parent('li'); 
        obj.children('ul').remove();
        obj.remove();
        unloadModal();
    });
</script>
<?php
}

// Create Object #######################################################################

if($createObject==true){
?>
<script type="text/javascript">
    $(function(){
        <?php
            if($obj_type==1){
                // Create folder 
               echo("var obj = '<li class=\"folder expanded\"><span rel=\"" . $obj_new_id . "\" title=\"" . $obj_name . "\">" . shortenString($obj_name, 25) . "</span></li>';");
            }else{
                // Create file
                echo("var obj = '<li class=\"file ext_" . $obj_ext . "\"><span rel=\"" . $obj_new_id . "\" title=\"" . $obj_name . "\">" . shortenString($obj_name, 25) . "</span></li>';");
            }
        ?>
        var parent = $('#file_manager li span[rel="<?php echo($obj_id); ?>"]').parent('li');
        if(parent.children('ul').size()>0){
            // Parent folder has children
            parent.children('ul:first').append(obj);        
        }else{
            // This is the first child element
            parent.append('<ul>'+obj+'</ul>');
        } 
        unloadModal();     
    });
</script>
<?php
}

// Load a file ##########################################################################

if($loadfile==true){
?>
<script type="text/javascript">
    
    $(function(){
        // Tab
        <?php
        $dispPath = str_replace($site['absroot'],"",$obj_path);
        $dispPath = str_replace($prj_name."/","",$dispPath);
        ?>
        $('#tab_bar li.active').removeClass('active');
        $('#tab_bar').append('<li class="active" id="tab<?php echo($obj_id); ?>" rel="<?php echo($dispPath); ?>" title="<?php echo($dispPath); ?>"><span><?php echo(shortenString($obj_name,15)); ?><span><a rel="<?php echo($obj_id); ?>" class="close">X</a></li>');
        
        $("#tab_bar").sortable({ axis: 'x', placeholder: 'placeholder', opacity: 0.6, tolerance: 'pointer' });
        $("#tab_bar").disableSelection();
        
        // Cur_Object
        $('#cur_object').val('<?php echo($obj_id); ?>');
        $('#file_manager li span.active').removeClass('active');
        $('#file_manager li span[rel="<?php echo($obj_id); ?>"]').addClass('active');
        
        // Workspace
        $('.editor').hide();
        $('#workspace').append('<div class="editor" id="editor<?php echo($obj_id); ?>"></div>');
        $('#editor<?php echo($obj_id); ?>').load('system/php/modules/editor.php?id=<?php echo($obj_id); ?>&ref=<?php echo(date('his')); ?>', function(){
            // Create initial restore point?
            <?php if($createRestorePoint==true){ ?>
            setTimeout("saveFile();",500);
            <?php } ?>
        });

        // Rebind tooltips
        initTabTooltips();
        // Check tab scroller
        scrolltabShowHide();
        

    });
    
</script>
<?php
}

// Load non-editable object (pop-up) ####################################################

if($loadNonEditable==true && $obj_extension!=""){
?>
<script type="text/javascript">
    $(function(){ popup('<?php echo($obj_url); ?>','non_editable',500,500); });
</script>
<?php
}

// View file in new window ##############################################################

if($viewfile==true){
?>
<script type="text/javascript">

    var viewerwidth = $(window).width()-100;
    var viewerheight = $(window).height()-100;
    $('#modal').css({ 'width' : viewerwidth+'px', 'height' : viewerheight+'px', 'top' : '50px', 'left' : '50px', 'margin':'0' });
    // Set title
    $('#modal_title span').html('File Viewer: <?php echo(str_replace($site['absroot'],"",$obj_path)); ?>&nbsp;(<a onclick="unloadModal();" href="<?php echo($obj_url); ?>" target="_blank">New Window</a>)');
    // Set close event
    $('#modal_title a.close_modal').click(function(){ unloadModal(); });
    // Show modal/overlay and load script
    $('#modal, #modal_overlay').fadeIn(200);
    $('#modal_content').html('<iframe style="border: 1px solid #999; background: #fff; height:'+(viewerheight-65)+'px;" width="100%" src="<?php echo($obj_url); ?>"></iframe>');
    $('#modal').draggable({ handle: '#modal_title', iframeFix: true });

</script>
<?php
}

// Close a file #########################################################################

if($closeFile==true){
?>
<script type="text/javascript">
    $(function(){ 
        $('#tab<?php echo($obj_id); ?>').remove();
        $('#editor<?php echo($obj_id); ?>').remove();
        $('#cur_object').val('0'); 
        $('#file_manager li span[rel="<?php echo($obj_id); ?>"]').removeClass('active');
        scrolltabShowHide();
    });
</script>
<?php
}

// All other scripts ####################################################################
?>
<script type="text/javascript">
    
    // Special character validation #####################################################
    $(function(){
        $(".obj_name").keypress(function(e){
            var code = e.which || e.keyCode;
            // 65 - 90 for A-Z and 97 - 122 for a-z 95 for _ 45 for - 46 for .
            if (!((code >= 65 && code <= 90) || (code >= 97 && code <= 122) || (code >= 37 && code <= 40) || (code >= 48 && code <= 57) || 
            (code >= 96 && code <= 105) || code == 95 || code == 46 || code == 45)){
                e.preventDefault();
             }
        });
        
        $('#file_upload').uploadify({
            'uploader'  : 'system/uploader/uploadify.swf',
            'script'    : 'system/uploader/uploadify.php?session=<?php echo(session_id());?>',
            'cancelImg' : 'system/uploader/cancel.png',
            'buttonImg' : 'system/uploader/btn_select_files.png',
            'folder'    : '<?php echo($obj_path); ?>',
            'auto'      : true,
            'multi'     : true,
            'onComplete': function(event, ID, fileObj, response, data) {
                $.get('system/uploader/post_upload_handler.php?project=<?php echo($obj_project); ?>&parent=<?php echo($obj_id); ?>&name='+fileObj.name, function(data){
                    var parent = $('#file_manager li span[rel="<?php echo($obj_id); ?>"]').parent('li');
                    if(data!="stop"){
                        if(parent.hasClass('expanded') || parent.hasClass('root')){
                            if(parent.children('ul').size()>0){
                                // Parent folder has children
                                parent.children('ul:first').append(data);        
                            }else{
                                // This is the first child element
                                parent.append('<ul>'+data+'</ul>');
                            }
                        }
                    }
                });
            },
            'onAllComplete' : function(event,data) { unloadModal(); }
        });
        
        loadFolderStack($('#dup_proj').val());
    });
    
    // Duplicate functions ##############################################################
    
    function loadFolderStack(i){
            $('#dup_dest').html('<p>Loading Folders...</p>');
            $('#dup_dest').load('system/php/modules/folder_selector.php?p='+i+'&f=0&e=<?php echo($obj_id); ?>');
    }
    
    function duplicateObject(){
        loadModalContent('system/php/modules/object_actions.php?action=duplicate&id=<?php echo($obj_id); ?>&dp='+$('#dup_proj').val()+'&dd='+$('#dup_dir').val());
    }
    
    // Rename functions #################################################################
    
    $('#obj_rename').keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { renameObject(); }
    });
    
    function renameObject(){
        loadModalContent('system/php/modules/object_actions.php?action=rename&id=<?php echo($obj_id); ?>&name='+$('#obj_rename').val());
    }
    
    // Create functions #################################################################
    
    $('#obj_create').keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { createObject(); }
    });
    
    function createObject(){
        if($('#obj_create').val()!=""){
            loadModalContent('system/php/modules/object_actions.php?action=create&id=<?php echo($obj_id); ?>&name='+$('#obj_create').val()+'&type='+$('#obj_type').val());
        }
    }
    
    // Reloader
    
    function reload(){ loadFileManager(); unloadModal(); }
    
</script>
<div class="clear"></div>
<?php } ?>