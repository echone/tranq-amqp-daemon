<?php

	/* 
	 * Directory RSS Generator
	 *
	 * @author Kevin Muller <kevin@endsdesign.com> (Ends Design)
	 * @version 1.0
	 *
	 * This page (or any portion of it) may not be redistributed without
	 * express written permission of the author. It is distributed 'as is'
	 * without warranty of any kind. The author(s) and Ends Design may not
	 * be held responsible for any damages cause by the function of the
	 * program, or as a result of installing it or its use. 
	 *
	 *
	 */
	    function export_url(){
	     		
	     		exec('ls -d $PWD', $output);
		     		foreach($output as $key) {
		     		return ltrim($key, "/var/www/worker");
	     		}
	     
	     }
	     
	     function export_url_parts() {
	     		$url=array(
	     		'corso'=>'',
	     		'anno'=>'',
	     		'materia'=>'',
	     		'tipo'=>''
	     		);
	     		exec('ls -d $PWD', $output);
		     		foreach($output as $key) {
		     		list($url['corso'], $url['anno'], $url['materia'], $url['tipo']) = explode("/", ltrim($key, "/var/www/worker"));
		     		return $url;
	     		}
	     
	     }
     
  	 /* Basic Feed Info*/
  	$titolo=export_url_parts();
  	$title=$titolo['materia'].' - '.$titolo['tipo'];
	$descrizione = export_url_parts();
	$descrizione=$titolo['corso'].' - '.$titolo['anno'].' - '.$titolo['materia'];	//Brief description of your feed
#	$br_hd=1115;
#	$br_sd=341;
#	$br_audio=64;
#	$categoria="103";
#	$immagine="logo.jpg";
	$data_pubblicazione=$titolo['anno'];
	$lingua="it";
  	 $url_base = "http://www.magicboxes.unimore.it/".export_url();
#	 $title = $titolo." [Audio]"; //Title for your RSS feed
	 $link  = "http://www.magicboxes.unimore.it/".export_url();  //Link to your main site for the feed
	 $description = $descrizione." [File audio in formato MP3]";	//Brief description of your feed
	 $link_base   = $url_base."/";	//Base URL for linking files (INCLUDE ENDING SLASH!!!)
	 $feed_url    = $url_base."/rss_audio.php";	//Full URL to the rss.php file
	 $language    = $lingua; 	//Feed language (en = English)
	 $ttl		  = "10"; 	//TTL setting
	 
	 
	 /* Feed Image */
	 $image_url = $url_base.$immagine;	//URL to your feed image (optional) Ex: http://www.yourdomain.com/images/rss.jpg
	 $image_title = $title;
	 $image_link = $link;	 
	 
	 
		 /* Script/Directory Info */
	 $rss_file_name = "rss_audio.php";	//This needs to be changed if you are changing the name of this file
	 
	 $dir = "./";	//Directory to list contents from (must be path). Set to "." for current directory
	 				//Leave empty to auto-detect
					
	$default_sort = "date";		//Default sorting method for RSS feed
								// date: most recent to oldest
								// odate: oldest to most recent
								// Leave blank for server default
								//
								//These settings can be over-ridden by appending ?sort={sorting-option-here} to the URL when viewing the script


	 /* Filtering Details */
	 $allow = array(		//enter file extensions - all others will be ingored (do NOT include the "." in the extension)
					"mp3",		//File mp4
					//"doc",	//Word Docs
					//"docx",	//Word 2007+ Docs
					//"xls",	//Excel Docs
					//"xlsx",	//Excel 2007+ Docs
					//"ppt",	//Power Point Docs
					//"pptx",	//Power Point 2007+ Docs
					//"jpg",	//JPG images
					//"gif",	//GIF images
					//"png",	//PNG images
					//"php",	//PHP Pages

					);
	 $allow_all = true;	//Override the above settings - set to true to enable all files to be displayed
	 
	 $filter = array(	//enter any files you don't want displayed here
					 $rss_file_name,	//This file
					 "..",		//Up Directory Link
					 ".",		//Current Directory Link
					 ".htaccess", 	//.htaccess file
					 ".passwd",		//.passwd file
					 ".DS_Store",
					 "rss.php",
					 //"config.php",	//example configuration file
					 //"error_log",	//example error log
					 //"some_private_file.pdf",	//Another example
					 
					 );
	 
	 
	 
	 /* YOU SHOULDN'T HAVE TO MODIFY ANYTHING BELOW THIS LINE */
	 
header('Content-type: text/xml');

echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>"; ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:itunesu="http://www.itunesu.com/feed">

	<channel>
		<title><?php echo stripslashes($title); ?> </title>
		<itunes:subtitle><?php echo stripslashes($sottotitolo); ?></itunes:subtitle>
		<link><?php echo stripslashes($link); ?></link>
		<description><?php echo stripslashes($description); ?></description>
		<itunes:author>TV.Unimore</itunes:author>
		<language><?php echo stripslashes($language); ?></language>
		<lastBuildDate><?php echo date("r",time()); ?></lastBuildDate>
		<pubDate><?php echo date("r",time()); ?></pubDate>
		<generator>Centro e-learning di Ateneo</generator>
		
	    
		<itunes:summary><?php echo stripslashes($description); ?></itunes:summary>
		
		<itunes:owner>
            <itunes:name>UNIMORE - Centro e-learning di Ateneo</itunes:name>
            <itunes:email>cea@unimore.it</itunes:email>
        </itunes:owner>
		<itunes:image href="<?php echo stripslashes($image_url); ?>" />
		<ttl><?php echo stripslashes($ttl); ?></ttl>
		<?php if(!empty($image_url)) { ?><image>
			<url><?php echo stripslashes($image_url); ?></url>
			<title><?php echo stripslashes($image_title); ?></title>

			<link><?php echo stripslashes($image_link); ?></link>
		</image><?php } ?>
        <?php
		
		//Auto-Detect Directory
		if(empty($dir)) {
			$dir = $_SERVER['SCRIPT_FILENAME'];
			$dir = str_replace($rss_file_name,'',$dir);
		}
		
		$contents = array();
		$file_link = array();
		$i = 0;
		// Open a known directory, and proceed to read its contents
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					$ext = substr($file, strrpos($file, '.') + 1);
					if(!in_array($file,$filter) && (in_array($ext,$allow) || $allow_all)) {	//exclusions
						$contents[$i] = filemtime($dir.$file);
						$file_link[$i] = $file;
						$i++;
					}
				}
				
				$sort = (isset($_REQUEST['sort']) ? stripslashes($_REQUEST['sort']) : $default_sort);
				
				
				if($sort == '') {
					//do nothing
				} elseif($sort == 'date') {
					arsort($contents);
				} elseif($sort == 'odate') {
					asort($contents);
				}
				
				foreach($contents as $k=>$v) {
					$file = $file_link[$k];
                    $data_pub=date("r",filemtime($file));
                    if ($data_pubblicazione!='')
                    {
                                        $filler=60-$k;
                   
					$data_pub=$data_pubblicazione." 00:".$filler.":00 +0200";
					}
					?>
						<item>
							<title><?php echo str_replace('.mp3','',$file); ?></title>
  							<link><?php echo $link_base.str_replace(' ','%20',$file); ?></link>
                            <guid><?php echo $link_base.str_replace(' ','%20',$file); ?></guid>
                            <enclosure type="audio/x-mpeg" length="<?php echo filesize($file); ?>" url="<?php echo $link_base.str_replace(' ','%20',$file); ?>" />							<pubDate><?php echo $data_pub ; ?></pubDate>
							<description>File MP3: (<?php echo round(filesize($file)/1024/1024,1); ?> MB)</description>
							<itunes:duration>
							<?php
							$mb=round(filesize($file)/1024,1);
							$min=round($mb*8/$br_audio);
							echo $min;
							?>
						
							</itunes:duration>
      <itunes:owner>
        <itunes:name>TV.Unimore</itunes:name>
      </itunes:owner>
      <itunes:explicit>no</itunes:explicit>
													
						</item>					
                    <?php
				}
				
				closedir($dh);
			}
		}	
		?>
	</channel>
</rss>
