<?php

$foldername = $_POST["foldername"];
#$foldername = "DASH_Video_01_11_2014_10_20_00";
$videopath = "/home/cs5248-15/team06/public_html/upl/" . $foldername . "/high/";

$$basicxml ="";

if( $xml = file_get_contents( 'basicxml.xml') ) {
	
    $basicxml->loadXML( $xml, LIBXML_NOBLANKS );
}


$playlistxml = "";
if( $xml = file_get_contents( 'playlistxml.xml') ) {
	
    $playlistxml->loadXML( $xml, LIBXML_NOBLANKS );
}


function add2MPD()
{
	echo "addToMPD\n";
	global $mpd, $foldername;
	$high = $mpd->Period->Group->Representation[0]->SegmentInfo; 
	$mid = $mpd->Period->Group->Representation[1]->SegmentInfo; 
	$low = $mpd->Period->Group->Representation[2]->SegmentInfo; 
	
	
		$highChild = $high->addChild("Url");
		$highChild->addAttribute("sourceUrl" , $foldername . "/high/" . "output1".".mp4");

		$midChild = $mid->addChild("Url");
		$midChild->addAttribute("sourceUrl" , $foldername . "/mid/" . "output1".".mp4");
		
		$lowChild = $low->addChild("Url");
		$lowChild->addAttribute("sourceUrl" , $foldername . "/low/" ."output1".".mp4");

	
	echo "end";
}

function addToPlaylist($mpdFilePath)
{

	global $baseurl, $playlistxml;
	
	$attr = $baseurl . $mpdFilePath;

	$playlistpath = "/home/cs5248-15/team06/public_html/upl/playlist.xml";

	if(file_exists($playlistpath))
	{
		
	$playlist = simplexml_load_file($playlistpath);
		
	}
	else
	{
		try {
		
		$playlist = new SimpleXMLElement($playlistxml);
		
		} catch(Exception $e) {
			
			echo $e->getMessage();
		}
	}	
	
	
	$mpdchild = $playlist->addChild("mpd");

	$mpdchild->addAttribute("path", $attr);
	
	
	$fp = fopen($playlistpath, "w") or die("Unable to open playlist file");
	
	fwrite($fp, formatXML($playlist));
	
	fclose($fp);
	
}


function formatXML($oldxml)
{
	$dom = new DOMDocument('1.0');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($oldxml->asXML());
	return $dom->saveXML();
}

function createM3U8()
{
	global $foldername,$baseurl;
	$lowts = fopen("/home/cs5248-15/team06/public_html/upl/$foldername/low/low.m3u8", "w") or die("Unable to create m3u8 file");
	$midts = fopen("/home/cs5248-15/team06/public_html/upl/$foldername/mid/mid.m3u8", "w") or die("Unable to create m3u8 file");
	$hights = fopen("/home/cs5248-15/team06/public_html/upl/$foldername/high/high.m3u8", "w") or die("Unable to create m3u8 file");
	$rootts = fopen("/home/cs5248-15/team06/public_html/upl/$foldername/root.m3u8", "w") or die("Unable to create m3u8 file");

	fwrite($lowts, "#EXTM3U\n#EXT-X-TARGETDURATION:3\n");
	fwrite($midts, "#EXTM3U\n#EXT-X-TARGETDURATION:3\n");
	fwrite($hights, "#EXTM3U\n#EXT-X-TARGETDURATION:3\n");
	fwrite($rootts, "#EXTM3U\n");
	
	fwrite($lowts, "#EXTINF:3,\n$output1.ts\n");
	fwrite($midts, "#EXTINF:3,\n$output1.ts\n");
	fwrite($hights, "#EXTINF:3,\n$output1.ts\n");

	fwrite($lowts, "#EXT-X-ENDLIST");
	fwrite($midts, "#EXT-X-ENDLIST");
	fwrite($hights, "#EXT-X-ENDLIST");

	fwrite($rootts, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=200000,RESOLUTION=240x160\n");
	fwrite($rootts, "low/low.m3u8\n");
	fwrite($rootts, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=768000,RESOLUTION=480x320\n");
	fwrite($rootts, "mid/mid.m3u8\n");
	fwrite($rootts, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=3000000,RESOLUTION=720x480\n");
	fwrite($rootts, "high/high.m3u8\n");

	fclose($lowts);
	fclose($midts);
	fclose($hights);

}

//Creates playlist in HTML format for Safari browser to play videos
function createHlsPlaylist()
{
	global $foldername;
	$hlsPlaylist = "/home/cs5248-15/team06/public_html/upload/hlsplaylist.html";
	$url = "<li><a href='http://pilatus.d1.comp.nus.edu.sg/~team06/upl/$foldername/root.m3u8'>$foldername</li>\n";
	
	if(file_exists($hlsPlaylist))
	{
		$file = fopen($hlsPlaylist, "a");
	}
	else
	{
		$file = fopen($hlsPlaylist, "w");
	}

	fwrite($file, $url);
	fclose($file);
}

$mpd = new SimpleXMLElement($basicxml);
$baseurl = $mpd->BaseURL;
add2MPD();
createM3U8();

#$mpdFilePath = "upload/" . $foldername . "/" . $foldername . ".mpd";
$mpdFilePath = "/home/cs5248-15/team06/public_html/upl/$foldername.mpd";
echo $mpdFilePath;


$mpdfp = fopen($mpdFilePath, "w") or die("Unable to open file");
fwrite($mpdfp, formatXML($mpd));
fclose($mpdfp);

$mpdPath = "$foldername/$foldername.mpd";
addToPlaylist($mpdPath);
createHlsPlaylist();


//fclose($logfp);
?>
