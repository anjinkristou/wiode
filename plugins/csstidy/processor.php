<?php

include('csstidy-1.3/class.csstidy.php');

$css = new csstidy();

// Compression levels

switch($_POST['level']){

    case 1:
        // Maximum
        $css->set_cfg('remove_bslash;',TRUE);
        $css->set_cfg('compress_colors',TRUE);
        $css->set_cfg('compress_font_weight;',TRUE);
        $css->set_cfg('lowercase_s',TRUE);
        $css->set_cfg('optimise_shorthands',1);
        $css->set_cfg('remove_last_;',TRUE);
        $css->set_cfg('case_properties',1);
        $css->set_cfg('sort_properties',TRUE);
        $css->set_cfg('sort_selectors',FALSE);
        $css->set_cfg('merge_selectors',2);
        $css->set_cfg('discard_invalid_properties',TRUE);
        $css->set_cfg('css_level','CSS2.1');
        $css->set_cfg('preserve_css',FALSE);
        $css->set_cfg('timestamp',FALSE);
        
        break;
        
    case 2:
        // Medium
        $css->set_cfg('remove_bslash;',TRUE);
        $css->set_cfg('compress_colors',TRUE);
        $css->set_cfg('compress_font_weight;',TRUE);
        $css->set_cfg('lowercase_s',TRUE);
        $css->set_cfg('optimise_shorthands',1);
        $css->set_cfg('remove_last_;',TRUE);
        $css->set_cfg('case_properties',1);
        $css->set_cfg('sort_properties',TRUE);
        $css->set_cfg('sort_selectors',FALSE);
        $css->set_cfg('merge_selectors',0);
        $css->set_cfg('discard_invalid_properties',TRUE);
        $css->set_cfg('css_level','CSS2.1');
        $css->set_cfg('preserve_css',TRUE);
        $css->set_cfg('timestamp',FALSE);
        
        break;
        
    case 3:
        // Low
        $css->set_cfg('remove_bslash;',FALSE);
        $css->set_cfg('compress_colors',FALSE);
        $css->set_cfg('compress_font_weight;',FALSE);
        $css->set_cfg('lowercase_s',FALSE);
        $css->set_cfg('optimise_shorthands',0);
        $css->set_cfg('remove_last_;',TRUE);
        $css->set_cfg('case_properties',1);
        $css->set_cfg('sort_properties',TRUE);
        $css->set_cfg('sort_selectors',FALSE);
        $css->set_cfg('merge_selectors',2);
        $css->set_cfg('discard_invalid_properties',FALSE);
        $css->set_cfg('css_level','CSS2.1');
        $css->set_cfg('preserve_css',TRUE);
        $css->set_cfg('timestamp',FALSE);
        
        break;
        
}

$css->parse(stripslashes($_POST['code']));

$output = $css->print->plain();

if($_POST['level']==1){ $output = str_replace(array('\r\n', '\r', '\n'), ' ', $output); }

echo($output);

?>