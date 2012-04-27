<?php

// set the amqp server ip
$ip = '';

include('function.php');
receiver('ffmpeg_exchange1', 'encode.key', 'bonnie', $ip);

?>
