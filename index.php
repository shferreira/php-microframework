<?php

function utime (){
    $time = explode( " ", microtime());
    $usec = (double)$time[0];
    $sec = (double)$time[1];
    return $sec + $usec;
}

$start = utime();

include('framework.php');
Framework::Route();

$end = utime();
$run = $end - $start;
echo "<!-- Page created in: " . substr($run, 0, 5) . " seconds. //-->";

?>
