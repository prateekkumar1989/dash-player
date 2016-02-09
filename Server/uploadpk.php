<?php

$segmentNumber=1;
$videoname="";
$videodir="";
$videopath="";
$fullname="";
$folderName = "";

if(file_exists("logs/logs.txt"))
        $logfp = fopen("logs/logs.txt", "a");
else
        $logfp = fopen("logs/logs.txt", "w");


/*
Convinent method to facilitate debugging
*/
function debug($msg)
{
	global $logfp;
	$msg = "upload.php ===> " .$msg . "\n";
	echo $msg;
	fwrite($logfp, $msg);
}

/*
Parsing the name of the file uploaded and extract details
from the name. 
*/
function parseName($name)
{
	global $segmentNumber,$videoname;
	preg_match("/^(.+)---(\d+).mp4$/",$name, $result);
	$videoname = $result[1];
	$segmentNumber = $result[2];
}

/*
Method that transcodes the segments uploaded to 
240*160 and 480*320 resolutions in .mp4 and .ts formats.
*/
function transcode()
{
	global $videoname, $videopath, $segmentNumber,$folderName;
	$lowdir = "/home/cs5248-15/team06/public_html/upl/$folderName/low";
	$lowvideoname = "$lowdir/$videoname";
	$middir = "/home/cs5248-15/team06/public_html/upl/$folderName/mid";
	$midvideoname = "$middir/$videoname";
	$bitratelow = "200";
	$bitratemid = "768";
	$fps = "29.97";
	$lowres = "240x160";
	$midres = "480x320";
	$asps = "44100";
	$audiobitrate = "64";

	$lowts = "$lowdir/$videoname-240*160---$segmentNumber.ts";
	$midts = "$middir/$videoname-480*320---$segmentNumber.ts";

	if(!file_exists($lowdir))
		mkdir($lowdir, 0777, true);
	
	if(!file_exists($middir))
		mkdir($middir, 0777, true);

	$cmd1 = "/usr/local/bin/convert.sh $videopath $bitratelow $fps $lowres $asps $audiobitrate $lowvideoname";
	$cmd2 = "/usr/local/bin/convert.sh $videopath $bitratemid $fps $midres $asps $audiobitrate $midvideoname";
	$cmd3 = "/usr/local/bin/mp42ts $lowvideoname $lowts";
	$cmd4 = "/usr/local/bin/mp42ts $midvideoname $midts";
	
	//Execution of command for transcoding and generation of .ts format video

	debug("Executing $cmd1");
	system($cmd1);
	debug("Low transcoding for $videopath complete");

	debug("Executing $cmd2");
	system($cmd2);
	debug("Medium transcoding for $videopath complete");
	
	debug("Executing $cmd3");
	system($cmd3);
	debug("Created $lowts from $lowvideoname");
	
	debug("Executing $cmd4");
	system($cmd4);
	debug("Created $midts from $midvideoname");

}


/*
Logic handling upload of the streamlets
*/
if (($_FILES["uploaded"]["size"] < 20000000))
  {
  if ($_FILES["uploaded"]["error"] > 0)
    {
		echo "sunny";
	$error = "Return Code: " . $_FILES["uploaded"]["error"];
	debug($error);
    }
  else
    {
	echo "sunny1";
	debug("Upload: " . $_FILES["uploaded"]["name"]);
	debug("Type: " . $_FILES["uploaded"]["type"]);
	debug("Size: " . ($_FILES["uploaded"]["size"] / 1024) . " Kb");
	debug("Temp file: " . $_FILES["uploaded"]["tmp_name"]);
	#$fullname = $_FILES["uploaded"]["name"];
	#parseName($fullname);
	$videoname = $_FILES["uploaded"]["name"];
	$findme   = '.';
	$pos = strpos($videoname, $findme);
	$folderName = substr($videoname,0, $pos);
	$videodirtemp = "/home/cs5248-15/team06/public_html/upl1/" .$folderName. "/temp";
	$videopathtemp = "$videodirtemp/$videoname";
	$videodir = "/home/cs5248-15/team06/public_html/upl1/" .$folderName. "/high";
	$videodirmid = "/home/cs5248-15/team06/public_html/upl1/" .$folderName. "/mid";
	$videodirlow = "/home/cs5248-15/team06/public_html/upl1/" .$folderName. "/low";
	$videopath = "$videodir/$videoname";
	$bitratelow = "200";
	$bitratemid = "768";
	$bitratehigh = "1536";
	$fps = "29.97";
	$lowres = "240x160";
	$midres = "480x320";
	$highres = "720x480";
	$asps = "44100";
	$audiobitrate = "64";
	echo "$videoname ########" ."$videodir ^^^^^"."$videopath @@@@@@@@";
	if(!file_exists($videodirtemp))
	{
		mkdir($videodirtemp, 0777, true);
	}

	if(!file_exists($videodir))
	{
		mkdir($videodir, 0777, true);
	}
	if(!file_exists($videodirmid))
	{
		mkdir($videodirmid, 0777, true);
	}
	if(!file_exists($videodirlow))
	{
		mkdir($videodirlow, 0777, true);
	}
	move_uploaded_file($_FILES["uploaded"]["tmp_name"], $videopathtemp);
	$cmdvar = "/usr/local/bin/convert.sh ".$videopathtemp." ".$bitratehigh." ".$fps." ".$highres." ".$asps." ".$audiobitrate." ".$videodir."/output1.mp4";
	$cmdvar1 = "/usr/local/bin/convert.sh ".$videopathtemp." ".$bitratemid." ".$fps." ".$midres." ".$asps." ".$audiobitrate." ".$videodirmid."/output1.mp4";
	$cmdvar2 = "/usr/local/bin/convert.sh ".$videopathtemp." ".$bitratelow." ".$fps." ".$lowres." ".$asps." ".$audiobitrate." ".$videodirlow."/output1.mp4";
	#$cmdvar = "/usr/local/bin/ffmpeg -version > /home/cs5248-15/team06/public_html/log.txt"; #-i ".$videopathtemp." -codec:v libx264 -profile:v high -preset slow -b:v 500k -maxrate 500k -bufsize 1000k -vf scale=-1:720 -threads 0 -codec:a libfdk_aac -b:a 128k -y output1.mp4";
	
	debug("cmdvar  $cmdvar");
	echo $cmdvar;
	system($cmdvar);
	system($cmdvar1);
	system($cmdvar2);
	/*move_uploaded_file($videodirtemp."/output1.mp4", $videopath);
	debug("Stored in: $videopath");

	$tspath = "$videodir/$videoname-720*480---$segmentNumber.ts";
	echo "/usr/local/bin/mp42ts $videopath $tspath";
	system("/usr/local/bin/mp42ts $videopath $tspath");

	transcode(); */
	$xmldoc = new DomDocument( '1.0' );
	$xmldoc->preserveWhiteSpace = false;
	$xmldoc->formatOutput = true;
	
	if( $xml = file_get_contents( 'infopk.xml') ) {
	echo "sunnnnnnnnnns";
	debug("sunny####  $folderName");
	debug("sunny@@@@  $xml");
    $xmldoc->loadXML( $xml, LIBXML_NOBLANKS );

    // find the headercontent tag
    $root = $xmldoc->getElementsByTagName('SegmentsPath')->item(0);

    // create the <product> tag
    $product = $xmldoc->createElement('Segment');
    $numAttribute = $xmldoc->createAttribute("value");
    $numAttribute->value = $folderName;
    $product->appendChild($numAttribute);

    // add the product tag before the first element in the <headercontent> tag
    $root->insertBefore( $product, $root->firstChild );

    // create other elements and add it to the <product> tag.
    $nameElement = $xmldoc->createElement('High');
    $product->appendChild($nameElement);
    $nameText = $xmldoc->createTextNode("/high/output1.mp4");
    $nameElement->appendChild($nameText);

    $categoryElement = $xmldoc->createElement('Medium');
    $product->appendChild($categoryElement);
    $categoryText = $xmldoc->createTextNode("/high/output2.mp4");
    $categoryElement->appendChild($categoryText);

    $availableElement = $xmldoc->createElement('Low');
    $product->appendChild($availableElement);
    $availableAttribute = $xmldoc->createTextNode("/high/output3.mp4");
    $availableElement->appendChild($availableAttribute);

    $xmldoc->save('infopk.xml');
}
	
	
	
	
#      }
    }
  }
else
  {
  echo "Invalid file";
  }

	fclose($logfp);
	exit;
?>
