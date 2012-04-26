<?php

///2011-09-27 
///Quando esegui modifiche alle funzioni ricordati di fare il restart del demone amqp-daemon
///
///

/// CONF ///
//$cpath='/var/www/worker';
////////////

function sender($text, $rk, $exchange, $ip){
	
	$connection=amqp_connection('guest', 'guest', $ip);
     
	$ex = new AMQPExchange($connection);
	$ex->declare($exchange, AMQP_EX_TYPE_DIRECT, AMQP_DURABLE | AMQP_AUTODELETE);
	
	$msg=$ex->publish($text, $rk);
	if (!$msg){echo "error";}echo 'Sended '.$msg.'<br>';
	
	if (!$connection->disconnect()) {
		throw new Exception('Could not disconnect');
	} else {
		echo "disconnected";
	}
}

function receiver($exchange, $rk, $queuename, $ip) {
	
	$connection=amqp_connection('guest', 'guest', $ip);

	$queue = new AMQPQueue($connection);
	$queue->declare($queuename);
	$queue->bind($exchange, $rk);
	
	while(true){
		$msg=$queue->get();
		list($path, $url) = explode(' ',$msg['msg']);
		if ($msg['count'] > -1){
			
#			echo "\n--------\n";
#			print_r($msg['count']);
#			echo "\n--------\n";
#			print_r($path);
#			echo "\n--------\n";
#			print_r($url);
#			echo "\n--------\n";
			sync('/var/www/worker', $url, $path, $ip);
			encoder('/var/www/worker', $url, $path);
			rsync('/var/www/worker', $url, $path, $ip);
		} else {
			sleep(1);
		}
	}		
	if (!$connection->disconnect()) {
		throw new Exception('Could not disconnect');
	} 
}

function sync($cpath, $url, $path, $ip) {
	
	if(!file_exists($cpath.'/'.$url)) mkdir($cpath.'/'.$url, 0755, true);
	if($url==''){return;}
	exec('sudo -S rsync -az root@'.$ip.':'.$cpath.'/'.$url.'/ '.$cpath.'/'.$url.'/');
}

function rsync($cpath, $url, $path, $ip) {

	if(!file_exists($cpath.'/'.$url)) mkdir($cpath.'/'.$url, 0755, true);
	if($url==''){return;}
	exec('sudo -S rsync -az '.$cpath.'/'.$url.'/ root@'.$ip.':'.$cpath.'/'.$url.'/');
}

function encoder($cpath, $url, $path) {
	
	$date = date('y-m-d');
	$log=new Logger($cpath.'/log/ffmpeg-calls_'.$date.'.log','a+');
	$log->log(date('Y-m-d H:i:s').' > file syncronized for '.$path);
	$log->log(date('Y-m-d H:i:s').' > process_start for '.$path);
	$info=pathinfo($path);
	$input=$cpath.'/'.$url.'/'.$path;
	$audio_dir= $cpath.'/'.$url.'/audio/';
	$video_dir= $cpath.'/'.$url.'/video/';
	$mobile_dir= $cpath.'/'.$url.'/mobile/';
	$audio_file= $audio_dir.'AU_'.basename($path,'.'.$info['extension']).'.mp3';
	$video_file= $video_dir.'HD_'.basename($path,'.'.$info['extension']).'.avi';
	$mobile_file= $mobile_dir.'MOB_'.basename($path,'.'.$info['extension']).'.mp4';
	$feed_audio=$cpath.'/rss_audio.php';
	$feed_video=$cpath.'/rss_hd.php';
	$feed_mobile=$cpath.'/rss_mobile.php';
	
	//Processo audio 
		
	$log->log(date('Y-m-d H:i:s').' > process_audio_start for '.$path);	
	if(!file_exists($audio_dir)) mkdir($audio_dir, 0755, true);
	exec('ffmpeg -y -i '.$input.' -acodec libmp3lame -apre audio-fad '.$audio_file.' 2> '.$cpath.'/log/log'.$path.'audio.txt');
	copy($feed_audio, $audio_dir.'rss_audio.php');
	$log->log(date('Y-m-d H:i:s').' > process_audio_end for '.$path);
	
	//Processo video
	
	$log->log(date('Y-m-d H:i:s').' > process_video_start for '.$path);
	if(!file_exists($video_dir)) mkdir($video_dir, 0755, true);
	exec('ffmpeg -threads 4 -y -i '.$input.' -acodec libmp3lame -ab 64k -ar 44100 -vcodec libx264 -dct 4 -bf 2 -g 30 -r 5 -b 336k -s 720x406 -aspect 16:9 -f avi '.$video_file.' 2> '.$cpath.'/log/log'.$path.'video.txt');
	copy($feed_video, $video_dir.'rss_hd.php');
	$log->log(date('Y-m-d H:i:s').' > process_video_end for '.$path);
	
	//Processo mobile
	
	$log->log(date('Y-m-d H:i:s').' > process_mobile_start for '.$path);
	if(!file_exists($mobile_dir)) mkdir($mobile_dir, 0755, true);
	exec('ffmpeg -threads 2 -y -i '.$input.' -acodec libfaac -ar 44100 -ab 64k -ac 2 -vcodec libx264 -vpre ipod320-fad -crf 1 -r 25 -s 640x360 -aspect 16:9 -f ipod -vsync 2 '.$mobile_file.' 2> '.$cpath.'/log/log'.$path.'mobile.txt');
	copy($feed_mobile, $mobile_dir.'rss_mobile.php');
	$log->log(date('Y-m-d H:i:s').' > process_mobile_end for '.$path);
	//break;
	
	$log->log(date('Y-m-d H:i:s').' > process_end for '.$path);
	$log->log();
}

class Logger{
	
	protected $logfile;
	protected $logtext=array();
	private $logged=false;

	public function __construct($file){
		$this->logfile=fopen($file,'a+');
		$this->closer=$closer;
	}

	public function __destruct(){
		if(!$this->logged) $this->log();
		fclose($this->logfile);
	}

	public function log($log){
		fwrite($this->logfile,$log."\n");
	}
}

 function amqp_connection($login, $pass, $ip) {

	$connection = new AMQPConnection();
	$connection->setLogin($login);
	$connection->setPassword($pass);
	$connection->setHost($ip);
	$connection->connect();

	if (!$connection->isConnected()) {
		 echo "Cannot connect to the broker";
	}
	return $connection;
}


    
?>
