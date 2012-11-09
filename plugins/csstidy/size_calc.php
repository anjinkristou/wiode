<?php

$in = stripslashes($_POST['input']);
$out = stripslashes($_POST['output']);

$per = "ERROR";
if(mb_strlen($in)>0 && mb_strlen($out)>0){ $per = number_format(100-((mb_strlen($out)/mb_strlen($in))*100)); }

echo(mb_strlen($out)."/".mb_strlen($in)." - saving approx. ".$per."%");

?>