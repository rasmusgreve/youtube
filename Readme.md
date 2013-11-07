Youtube series progress keeper-tracker
===============================================
I follow a few Let's Play series on youtube, but don't have enough spare time 
to watch all the videos as soon as they come out. I needed a way of keeping track
of my series watching progress, and since I'm a software developer pen and paper
or a text document wasn't going to do it.

I am aware that youtube itself has some support for keeping track of which videos you
have watched, and which you haven't, but I didn't find it working as well as I had hoped.

This simple webpage+database can be used to keep track of videos that you want to watch
and supports an easy way to let the system automatically group videos in series together.

## Installation
In order to use this you must have a webserver with PHP support and a MySQL database.

I use [WAMP](http://www.wampserver.com/en/) ([XAMPP](http://www.apachefriends.org/en/xampp.html) for other platforms) which contains all this.
Note that there is absolutely no access control, so this should probably not be running anywhere online (without modifications at least). WAMP and XAMPP run localy on your machine.

1. Put all files in a folder on your webserver (e.g. C:/wamp/www/youtube for a standard installation of WAMP)
2. Import [database.sql](./database.sql) into your MySQL database (for WAMP or XAMPP use phpmyadmin)
3. Edit in your credentials for the MySQL database in the top of [index.php](./index.php) if you changed them from the defaults

##Usage
Navigate to [localhost/youtube](http://localhost/youtube) (or whatever you called the directory) in your browser

To add a video to the system, simply drag a link to the video from Youtube and drop it anywhere on the page.

Series are created/added through the `Add Series` button in the top left. When you create a series you are presented with 3 fields;
`Name`, `Author` and `Auto-identify search`.

Name and Author are exactly what they sound like. Auto-identity search covers how you want 
the system to automatically identify videos as belonging to this series. E.g.:

```
You have a series using names like
* Let's play Skyrim - part 1
* Let's play Skyrim - part 2
* Let's play Skyrim - part 3

In auto-identity search you should put: "Let's play Skyrim"
```

To watch a video you simply press the thumbnail image. The video opens in a new window!

After watching you press `Mark as watched`.

##Missing features:

* Manual adding videos to series
* Removing videos from series
* ???
* 

##Known bugs:

* The automatic series identifier seems to dislike ticks (') in names and searches. I'm not sure why since I think I handle it properly.

##Libraries/APIs used:

* [jQuery](http://jquery.com/)
* [Twitter Bootstrap](http://getbootstrap.com/)
* Google Youtube Video API ([http://gdata.youtube.com/feeds/api/videos/VIDEO_ID?v=2](http://gdata.youtube.com/feeds/api/videos/9bZkp7q19f0?v=2))
* Google Youtube Video Thumbnail service ([(https://i1.ytimg.com/vi/VIDEO_ID/hqdefault.jpg)](https://i1.ytimg.com/vi/9bZkp7q19f0/hqdefault.jpg))

(FYI: I discovered that the Youtube video API gets mad at you (403) if you send more than 100 request in very little time - just let it cool off for a bit)


