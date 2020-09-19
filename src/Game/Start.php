<?php

use Game\Play;

require('vendor/autoload.php');

$play = new Play;
$input = (int)readline('Enter a difficulty level (0:Low to 3:High) :');
$keys = [0,1,2,3];
if(in_array($input,$keys)){
    $key = $play->testcase($input);
    $play->execute($key);
} else {
    echo 'ABORTING, wrong input'."\n";
    exit;
}