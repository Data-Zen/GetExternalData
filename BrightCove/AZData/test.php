<?php

echo "\nStart Timer \n";
$start = microtime(true); 

 #DO  SOMETHING
#sleep(2);
$end = round((microtime(true) - $start),2);
echo "\nelapsed time: $end seconds \n";






?>