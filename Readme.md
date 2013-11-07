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

The system makes use of the Google Youtube Video API and the fact that Google provides structured easy access to thumbnails for videos.
API: http://gdata.youtube.com/feeds/api/videos/VIDEO_ID?v=2
Thumbnails: https://i1.ytimg.com/vi/VIDEO_ID/hqdefault.jpg

