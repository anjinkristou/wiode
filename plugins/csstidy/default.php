<label>Original Code</label>
<textarea id="cst_input" class="cst_code"></textarea>

<label>Compression<label>
<select id="cst_level">
    <option value="0"> - SELECT COMPRESSION LEVEL - </option>
    <option value="1">Maximum - High compression, no CSS Preservation</option>
    <option value="2">Medium - Preserve CSS, apply most other optimizations</option>
    <option value="3">Low - Basic clean-up, Preserves CSS</option>
</select>

<label>Output Code</label>
<textarea id="cst_output" class="cst_code"></textarea>

<div id="cst_size"></div>

<input type="button" value="Close" id="cst_close" />
<input type="button" value="Replace File Contents" id="cst_replace" />

<style>
    .cst_code { display: block; width: 100%; height: 150px; border: none; 
        font-family: 'Droid Sans Mono', monospace; font-size: 14px; background: #1a1a1a; color: #fff; padding: 10px 0 0 10px; overflow: scroll;
        word-wrap: normal; white-space:nowrap; }
</style>

<script>

    $(function(){
        
        // Check if active file is CSS and load into input
        var curfile = plugin.editorfilepath();
        if(curfile!=''){
            // Check extension
            var ext = curfile.split('.').pop();
            var ext = ext.toLowerCase();
            if(ext=='css'){ $('#cst_input').val(plugin.editordata()); }
        }
        
        // Process
        $('#cst_level').change(function(){
            // Get level
            var level = $(this).val();
            // Send POST
            var processor = plugin.path()+'/processor.php';
            $.post(processor,{ code: $('#cst_input').val(), level: $('#cst_level').val() },function(data){ $('#cst_output').val(data); getSizeCalc(); });    
        });
        
        // Close button    
        $('#cst_close').click(function(){ plugin.close(); });
        
        // Replace Contents button
        $('#cst_replace').click(function(){ plugin.editorreplacedata($('#cst_output').val()); });
    
    });
    
    // Size calc return
    function getSizeCalc(){
        $('#cst_size').html('Loading...');
        var processor = plugin.path()+'/size_calc.php';
        $.post(processor,{ input: $('#cst_input').val(), output: $('#cst_output').val() }, function(data){ $('#cst_size').html(data); });
    }
    
</script>