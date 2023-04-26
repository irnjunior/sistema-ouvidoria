<?php 
try { 
$phar = new Phar('pt_BR.phar'); 
$phar->extractTo('./',null,true); // extract all files 
} catch (Exception $e) { 
echo "there was an error<br>"; 
print_r($e); 
} 