<?php
/*
This file is not for normal use.
It is/was only used to add information to videos already in the database during development of the system


*/
mysql_connect("localhost","root","");
mysql_select_db("youtube");
/*
for ($i = 1; $i <= 100; $i++)
{
	mysql_query("UPDATE `video` SET `part` = $i WHERE `title` LIKE '%episode $i %'");
	echo "Updated " . mysql_affected_rows() . " videos to part $i";
}
*/

function getTitle($xml)
{
	$parts1 = explode("<title>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</title>",$part1);
	return $parts2[0];
}

function getDuration($xml)
{
	$parts1 = explode("<yt:duration seconds='",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("'/>",$part1);
	return $parts2[0];
}

function getAuthor($xml)
{
	$parts1 = explode("<name>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</name>",$part1);
	return $parts2[0];
}

function getPublished($xml)
{
	$parts1 = explode("<published>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</published>",$part1);
	return $parts2[0];
}

//$title = string, $series = id -> search
function identifySeries($title, $series)
{
	foreach ($series as $id => $search)
	{
		if (stripos($title,$search) !== false)
			return $id;
	}
	return 0;
}

//Build series thing:
$series_query = mysql_query("SELECT * FROM `series`");
$series = array();
while ($res = mysql_fetch_assoc($series_query))
{
	$series[$res['id']] = $res['search'];
}

$query = mysql_query("SELECT * FROM `video`;");
while ($res = mysql_fetch_assoc($query)){
	//$xml = $res['xml'];
	$sid = identifySeries($res['title'],$series);
	if ($res['series'] != 0 && $res['series'] != $sid)
	{
		echo "<b>ALERT! Auto identified series wrongly on video {$res['id']}. Auto: $sid, pre: {$res['series']}. Not updating the series</b><br>\n";
		continue;
	}
	if ($sid != 0)
	{
		mysql_query("UPDATE `video` SET `series` = '$sid' WHERE `id` = '{$res['id']}' LIMIT 1;");
		echo "Updated series of video " . $res['id'] . " to be $sid ({$series[$sid]})<br/>\n";	
	}
	else
		echo "Didn't identify any series for video: {$res['title']}<br>\n";
}
echo "Done with ".mysql_num_rows($query)." elements.";
//*/
?>