<?php
$mysql_server = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "youtube";

mysql_connect($mysql_server,$mysql_user,$mysql_password);
mysql_select_db($mysql_database);

//0 = all w/o series, -1 = all videos
$series = (isset($_GET['series'])) ? $_GET['series'] : -1;

$series_lookup_query = mysql_query("SELECT * FROM `series`");
$series_lookup = array();
while ($res = mysql_fetch_assoc($series_lookup_query)) $series_lookup[$res['id']] = $res['search'];

function getImage($id)
{
	//return "https://i1.ytimg.com/vi/$id/default.jpg";
	return "https://i1.ytimg.com/vi/$id/mqdefault.jpg";
	//return "https://i1.ytimg.com/vi/$id/hqdefault.jpg";
	//return "https://i1.ytimg.com/vi/$id/sddefault.jpg";
}

function getLink($id)
{
	return "http://www.youtube.com/watch?v=$id";
}

function getXml($id)
{
	return file_get_contents("http://gdata.youtube.com/feeds/api/videos/$id?v=2");
}

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

function getUserID($xml)
{
	$parts1 = explode("<yt:userId>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</yt:userId>",$part1);
	return $parts2[0];
}

function getAuthor($xml)
{
	$parts1 = explode("<name>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</name>",$part1);
	return str_replace('YOGSCAST ','',$parts2[0]);
}

function getPublished($xml)
{
	$parts1 = explode("<published>",$xml);
	$part1 = $parts1[1];
	$parts2 = explode("</published>",$part1);
	$published = strtotime($parts2[0]);
	return date("Y-m-d H:i:s", $published);
}

function identifySeries($title, $series_lookup)
{
	foreach ($series_lookup as $id => $search)
		if (stripos($title,$search) !== false)
			return $id;
	return 0;
}

function identifyPart($title)
{
	for ($i = 200; $i >= 1; $i--) //Count backwards to identify correctly (such that part 10 is not identified as part 1)
	{
		if (stripos($title,"episode $i") !== false)
			return $i;
		if (stripos($title,"#$i") !== false)
			return $i;
		
	}
	for ($i = 200; $i >= 1; $i--) //Repeat with part alone as some EPISODES are in multiple PARTS (we are id'ing episodes)
	{
		if (stripos($title,"part $i") !== false)
			return $i;
	}
	return 0;
}

function insertVideo($id)
{
	global $series_lookup;
	$added = time();
	$xml = getXml($id);
	$title = mysql_real_escape_string(getTitle($xml));
	$author = mysql_real_escape_string(getAuthor($xml));
	$duration = mysql_real_escape_string(getDuration($xml));
	$published = mysql_real_escape_string(getPublished($xml));
	$series = identifySeries($title, $series_lookup);
	$part = identifyPart($title);
	$safexml = mysql_real_escape_string($xml);
	mysql_query("INSERT INTO `video` (`id`,`added`,`title`,`duration`,`author`,`xml`,`published`, `series`, `part`) VALUES ('$id','$added','$title','$duration','$author','$safexml','$published','$series','$part');");	
}

function insertSeries($name, $author, $search)
{
	mysql_query("INSERT INTO `series` (`name`,`author`,`search`) VALUES ('$name','$author','$search');");
	$id = mysql_insert_id();
	
	//Update existing videos not already in a series
	$video_update_query = mysql_query("SELECT * FROM `video` WHERE `series` = '0';");
	while ($res = mysql_fetch_assoc($video_update_query))
		if (stripos($res['title'],$search) !== false)
			mysql_query("UPDATE `video` SET `series` = '$id' WHERE `id` = '{$res['id']}' LIMIT 1;");
	
}

function deleteSeries($id)
{
	mysql_query("DELETE FROM `series` WHERE `id` = '$id' LIMIT 1;");
}

function deleteVideo($id)
{
	mysql_query("DELETE FROM `video` WHERE `id` = '$id' LIMIT 1;");
}

function extractId($link)
{
	$parts = explode("?v=",$link);
	if (count($parts) <= 1) return '';
	return $parts[1];
}

if (isset($_POST['series_create_name']))
{
	insertSeries(mysql_real_escape_string($_POST['series_create_name']),mysql_real_escape_string($_POST['series_create_author']),mysql_real_escape_string($_POST['series_create_search']));
	header("Location:./");
	die();
}

if (isset($_POST['add_link']))
{
	$id = extractId($_POST['add_link']);
	if ($id == '')
	{
		header("Location:./?linkerror");
		die();
	}
	insertVideo($id);
	header("Location:./");
	die();
}

if (isset($_GET['delete']))
{
	deleteVideo(mysql_real_escape_string($_GET['delete']));
	header("Location:./");
	die();
}

if (isset($_GET['watchedvideo']))
{
	mysql_query("UPDATE `video` SET `watched` = '1' WHERE `id` = '" . mysql_real_escape_string($_GET['watchedvideo']) . "';");
	die("OK");
}
if (isset($_GET['unwatchedvideo']))
{
	mysql_query("UPDATE `video` SET `watched` = '0' WHERE `id` = '" . mysql_real_escape_string($_GET['unwatchedvideo']) . "';");
	die("OK");
}

//Video and series selection logic (this looks a bit messy since a lot is going on. Basicly it just selects relevant videos from the database)
$show_from_top = 15;
$show_from_bottom = 3;

$watched = (isset($_GET['watched'])) ? '1' : '0';
$series_where = ($series == -1) ? '' : "AND `series` = '$series'";
$totalq = mysql_query("SELECT count(*) FROM `video` WHERE `watched` = '$watched' $series_where;");
$total = mysql_result($totalq,0,0);
$query = mysql_query("SELECT * FROM `video` WHERE `watched` = '$watched' $series_where ORDER BY `published` ASC LIMIT $show_from_top;");
$top_count = mysql_num_rows($query);
$show_from_bottom = max(0,min($show_from_bottom,$total-$top_count));
$lastq = mysql_query("SELECT * FROM `video` WHERE `watched` = '$watched' $series_where ORDER BY `published` DESC LIMIT $show_from_bottom;");
$extra_videos = $total - $top_count - mysql_num_rows($lastq);
$series_query = mysql_query("SELECT `series`.*, COUNT(`video`.`id`) num FROM `series`, `video` WHERE `video`.`series` = `series`.`id` AND `video`.`watched` = '$watched' GROUP BY `series`.`id` ORDER BY `series`.`author` ASC, `series`.`name` ASC;");
$all_videos_count_query = mysql_query("SELECT count(*) FROM `video` WHERE `watched` = '$watched';");
$all_videos_count = mysql_result($all_videos_count_query,0,0);
$all_videos_woseries_count_query = mysql_query("SELECT count(*) FROM `video` WHERE `watched` = '$watched' AND `series` = '0';");
$all_videos_woseries_count = mysql_result($all_videos_woseries_count_query,0,0);

function displayVideo($res)
{
	?>
	<div class="media">
	  <a class="pull-left" href="<?=getLink($res['id'])?>" target="_blank">
		<img class="media-object" src="<?=getImage($res['id'])?>" onerror="this.src='./img/default-image.png';" width="150" height="84" alt="<?=$res['title']?>" title="<?=$res['title']?>">
	  </a>
	  <div class="media-body">
		<h4 class="media-heading"><?=$res['title']?> <span class='badge'><?=$res['author']?></span></h4>
		<?php if ($res['watched']){?>
			<button class='btn btn-danger unwatchedbtn' data-id="<?=$res['id']?>">Mark as not watched</button>
		<?php } else {?>
			<button class='btn btn-primary watchedbtn' data-id="<?=$res['id']?>">Mark as watched</button>
		<?php }?>
			<a class='btn btn-warning pull-right' href='./?delete=<?=$res['id']?>'>Delete</a>
			<br>
			<span class='badge'><?php printf("%02d:%02d", floor($res['duration'] / 60), $res['duration'] % 60);?></span>
	  </div>
	</div>
	<?php
}
	
?>
<!DOCTYPE html>
<html>
<head>
<title>Youtube</title>
<link rel="shortcut icon" href="img/icon.png">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
<link href="./css/styles.css" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</head>
<body>

<?php if (isset($_GET['linkerror'])) {?>
<div class='alert alert-danger alert-dismissable'>
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<strong>Link error!</strong> The link you tried to add could not be identified as a Youtube video
</div>

<?php } ?>

<!-- Link Drag'n'drop thing-->
<form action="./" method="post" id='add_link_form'>
	<input type='hidden' name='add_link' value='' id='add_link'/>
</form>
<textarea id="link_grabber" style='z-index:25'></textarea>
<div id="notice">Add video<br><i class='glyphicon glyphicon-plus'></i><br/></div>

<!-- Add series modal -->
<div class="modal fade" id="seriesmodal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Add series</h4>
      </div>
	  <form action="./" method="post">
		  <div class="modal-body">
			<div class="form-group">
				<label for="series_create_name">Name</label>
				<input type="text" class="form-control" id="series_create_name" name="series_create_name">
				<span class='help-block'>The name of the series</span>
			</div>
			<div class="form-group">
				<label for="series_create_name">Author</label>
				<input type="text" class="form-control" id="series_create_author" name="series_create_author">
				<span class='help-block'>The author of the series</span>
			</div>
			<div class="form-group">
				<label for="series_create_name">Auto-identify search</label>
				<input type="text" class="form-control" id="series_create_search" name="series_create_search">
				<span class='help-block'>A string that ties a video to this series, if found in in the title of the video</span>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			<input type="submit" class="btn btn-primary" value="Save"/>
		  </div>
	  </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="container">
	
	<!-- Top bar -->
	<div class="row">
		<div class="col-lg-2">
			<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#seriesmodal">
			  <span class='glyphicon glyphicon-plus'></span> Add series
			</button>
		</div>
		<div class="col-lg-offset-8 col-lg-2">
			<?php if (!isset($_GET['watched'])){?>
				<a href='./?watched&amp;series=<?=$series?>' class="btn btn-default btn-sm pull-right">Show watched videos</a>
			  <?php } else {?>
				<a href='./?series=<?=$series?>' class="btn btn-warning btn-sm pull-right">Show unwatched videos</a>
			  <?php }?>
		</div>
	</div><!-- /Top bar -->
	
	<hr/>
	<div class="row">
		<!-- Series box -->
		<div class="col-lg-4 hidden-xs hidden-sm">
			<div class="well">
				<ul class="nav nav-pills nav-stacked">
				  <li <?=($series==-1)?'class="active"':''?>><a href="./?series=-1<?=(isset($_GET['watched']))?'&amp;watched':''?>">All videos <span class='badge pull-right'><?=$all_videos_count?></span></a></li>
				  <li <?=($series==0)?'class="active"':''?>><a href="./?series=0<?=(isset($_GET['watched']))?'&amp;watched':''?>">All w/o series <span class='badge pull-right'><?=$all_videos_woseries_count?></span></a></li>
				  <?php 
				  $author_name = '';
				  while($res = mysql_fetch_assoc($series_query)){ 
					if ($author_name != $res['author'])
					{
						$author_name = $res['author'];
						echo "<li><strong>$author_name</strong></li>";
					}
				  ?>
					<li <?=($series==$res['id'])?'class="active"':''?>><a href="./?series=<?=$res['id']?><?=(isset($_GET['watched']))?'&amp;watched':''?>"><?=$res['name']?><span class='badge pull-right'><?=$res['num']?></span></a></li>
				  <?php }?>
				</ul>
			</div>
		</div><!-- /Series box -->
		
		<!-- Feed -->
		<div class="col-lg-8">
			<?php
			while ($res = mysql_fetch_assoc($query)){
				displayVideo($res);
			}
			if ($extra_videos > 0) {
				?>
					<div class="media">
					  <div class="media-body">
						<h4 class="media-heading">... <?=$extra_videos?> more ...</h4>
					  </div>
					</div>
				<?php
			}
			for ($i = mysql_num_rows($lastq) - 1; $i >= 0; $i--) {
				mysql_data_seek($lastq, $i);
				$lastres = mysql_fetch_assoc($lastq);
				displayVideo($lastres);	
			}
			
			if (mysql_num_rows($query) == 0){
			?>
				<div class="media">
				  <div class="media-body">
					<h4 class="media-heading">There are no videos here</h4>
				  </div>
				</div>
			<?php } ?>
		</div> <!-- /Feed -->
	</div> <!-- /Row -->
</div> <!-- /Container -->

<script type="text/javascript" src="./js/script.js"></script>
</body>
</html>
