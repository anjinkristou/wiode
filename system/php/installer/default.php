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
<h1>WIODE Installer</h1>
<?php

// Get paths ############################################################################

$path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$_SERVER['PHP_SELF']);
$root = str_replace("/system/php/installer/default.php","",$path);
$abs = $_SERVER['DOCUMENT_ROOT'] . $root;

// Ensure that PHP v. 5 (or better) is currently running ################################

$version = explode('.', phpversion());
if ($version[0]!='5'){
    ?>
    <p class="error">WIODE requires PHP version 5 (or better) to run correctly.<p>
    <p>Please upgrade to v.5+ or install the system on a different server.</p>
    <?php

// Run check of config file to ensure it's not configured ###############################

}elseif(filesize($abs."/config.php")>0){
    ?>
    <p class="error">It appears that the system has already been installed.</p><p> If you are running
    this install for the first time please ensure that config.php does not contain any
    data (including spaces or blank lines).</p><p>If you have already installed check to ensure that 
    the /config.php file is not corrupted.</p>
    <input type="button" class="bold" value="Reload System" onclick="location.reload(true);" />
    <?php

// Ensure config.php, /workspace & /backups are writable ###############################

}elseif(!is_writeable($abs."/config.php") || !is_writeable($abs."/workspace") || !is_writeable($abs."/backups")){
    ?>
    <p class="error">The following files and directories must be writeable in order to continue the installation process
     and then reload the system:<br /><br />
<pre>/config.php
/workspace
/backups</pre>
    </p>
    <input type="button" class="bold" value="Reload System" onclick="location.reload(true);" />
    <?php
    
// Requirement met, show config form ##################################################

}else{
?>
<div id="install_config">
    <p>All dependencies have been met, please fill out the following to continue:</p>
    
    <hr />
    
    <form id="config" method="post">
    
        <input type="hidden" name="root" value="<?php echo($root); ?>" />
        
        <div id="config_database">
        
            <h2>Configure Database Connection</h2>
            
            <p>MySQL Host:</p>
            <input type="text" name="host" class="db_config" />
            
            <p>Username:</p>
            <input type="text" name="username" class="db_config" />
            
            <p>Password:</p>
            <input type="password" name="password" class="db_config" />
            
            <p>Database:</p>
            <input type="text" name="database" class="db_config" />
            
            <input type="button" id="btn_verify_db" class="bold" value="Verify &amp; Proceed" onclick="verifyDB();" />

        
        </div> <!-- /# config_database -->
    
        <div id="config_user" style="display: none;">
            
            <h2>Create A User Account</h2>
            
            <p>Login:</p>
            <input type="text" name="init_username" class="user_config" />
            
            <p>Password:</p>
            <input type="password" name="init_password1" id="pw1" class="user_config" />
            
            <p>Password (Verify):</p>
            <input type="password" name="init_password2" id="pw2" class="user_config" />
            
            <p>Full Name:</p>
            <input type="text" name="init_full_name" class="user_config" />
            
            <p>Email Address:</p>
            <input type="text" name="init_email" class="user_config" />
            
            <input type="button" id="btn_install" class="bold" value="Install System" onclick="verifyUser();" />
        
        </div> <!--/#config_user -->
        
    </form>
    
</div><!-- /#install_config -->


<div id="install_success" style="display: none;">
    <p>Congratulations, WIODE has been succesfully installed!</p>
    <p>You may now proceed to the login screen and begin using the system.</p>
    <p>It is HIGHLY recommended that you remove the following directory from your server:</p>
<pre>
/system/php/installer
</pre>
    <input type="button" value="Proceed to Login" onclick="location.reload(true);" />
</div>
<div id="install_error" style="display: none;">
    <p class="error">There was a problem installing the system. Please see the error message below for more information:</p>
    <pre id="install_error_msg" style="max-height: 300px; overflow: scroll;">
    </pre>
</div><!-- /#install_success -->


<?php
}
?>
<script>
    
    function verifyDB(){
        $.post('system/php/installer/verify_db.php',$("#config").serialize(),function(data){
            if(data=="pass"){
                $('#config_database').hide();
                $('#config_user').show();
            }else{
                alert('Could not connect to the database given the provided connection information. Please ensure the information is correct and try again.');
            }
        });
    }
    
    function verifyUser(){
        pass_user_config = true;
        // Ensure all fields are filled out
        $('.user_config').each(function(){
            if($(this).val()==""){
                pass_user_config = false; 
            }
        });
        // Show validation message
        if(pass_user_config==false){ alert('Please fill out all fields before continuing.'); }
          
        // Ensure passwords match
        if($('#pw1').val()!=$('#pw2').val()){
            if(pass_user_config==true){
                pass_user_config = false;
                alert('The passwords you provided do not match.');
            }
        }
        
        // Minimum password length
        var pw = $('#pw1').val();
        if(pw.length<8){
            if(pass_user_config==true){
                var answer = confirm("It is HIGHLY recommended you use a password with at least 8 characters. You can continue with the supplied password or cancel this process and provide a stronger password");
                if(answer){
                    pass_user_config = true;
                }else{
                    pass_user_config = false;
                }
            }
        }
        
        // All requirements met, process install
        if(pass_user_config==true){
            processInstall();
        }
    }
    
    function processInstall(){
        $.post('system/php/installer/process_install.php',$("#config").serialize(),function(data){
            if(data==""){
                $('#install_config').hide();
                $('#install_success').show();
            }else{
                $('#install_config').hide();
                $('#install_error').show();
                $('#install_error_msg').html(data);
            }
        });
    }
</script>
