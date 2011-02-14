<html>
  <head><title>Embed.ly Example</title>
<style type="text/css">
.embed-wrapper {
  float: left;
  width: 400px;
}
.embed-wrapper img {
  max-width: 400px;
}
.clear {
  clear: both;
}
</style>
</head>
  <body>
<?php
require_once "../Embedly.php";
$api = new Embedly_API(array(
  'user_agent' => 'Mozilla/5.0 (compatible; embedly/example-app; support@embed.ly)'
));

$urls = array(
  "http://www.slideshare.net/doina/happy-easter-from-holland-slideshare", "http://www.scribd.com/doc/13994900/Easter",
  "http://screenr.com/t9d",
  "http://www.5min.com/Video/How-to-Decorate-Easter-Eggs-with-Decoupage-142076462",
  "http://www.howcast.com/videos/328008-How-To-Marble-Easter-Eggs",
  "http://img402.yfrog.com/i/mfe.jpg/",
  "http://tweetphoto.com/14784359",
  "http://www.flickr.com/photos/10349896@N08/4490293418/",
  "http://imgur.com/6pLoN",
  "http://twitgoo.com/1as",
  "http://www.qwantz.com/index.php?comic=1686",
  "http://www.23hq.com/mhg/photo/5498347",
  "http://www.youtube.com/watch?v=gghKdx558Qg",
  "http://www.justin.tv/easter7presents",
  "http://revision3.com/hak5/DualCore",
  "http://www.dailymotion.com/video/xcstzd_greek-wallets-tighten-during-easter_news",
  "http://www.collegehumor.com/video:1682246",
  "http://www.twitvid.com/D9997",
  "http://www.break.com/game-trailers/game/just-cause-2/just-cause-2-lost-easter-egg?res=1",
  "http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid=104063637",
  "http://www.metacafe.com/watch/105023/the_easter_bunny/",
  "http://blip.tv/file/449469",
  "http://video.google.com/videoplay?docid=-5427138374898988918&q=easter+bunny&pl=true",
  "http://revver.com/video/263817/happy-easter/",
  "http://video.yahoo.com/watch/7268801/18963438",
  "http://www.viddler.com/explore/BigAppleChannel/videos/113/",
  "http://www.liveleak.com/view?i=e0b_1239827917",
  "http://www.hulu.com/watch/67313/howcast-how-to-make-braided-easter-bread",
  "http://www.fancast.com/tv/It-s-the-Easter-Beagle,-Charlie-Brown/74789/1078053475/Peanuts:-Specials:-It-s-the-Easter-Beagle,-Charlie-Brown/videos",
  "http://www.funnyordie.com/videos/f6883f54ae/the-unsettling-ritualistic-origin-of-the-easter-bunny",
  "http://vimeo.com/10446922",
  "http://www.ted.com/talks/jared_diamond_on_why_societies_collapse.html",
  "http://www.thedailyshow.com/watch/thu-december-14-2000/intro---easter",
);

$oembeds = $api->oembed(array('urls' => $urls, 'maxwidth' => 400));

foreach ($oembeds as $k => $oembed) {
    $oembed = (array) $oembed;
    print '<hr/>';
    if (array_key_exists('description', $oembed)) {
        print '<p>'.$oembed['description'].'</p>';
    }
    ?><div class="embed-wrapper"><?php
    switch($oembed['type']) {
    case 'photo':
        if (!array_key_exists('title', $oembed)) {
          ?><img src="<?php echo $oembed['url'] ?>"></img><?php
        }
        else {
            ?><img src="<?php echo $oembed['url'] ?>" alt="<?php echo $oembed['title'] ?>"></img><?php
        }
        //print '<pre>';
        //print_r($oembed);
        //print '</pre>';
        break;
    case 'link':
    case 'rich':
    case 'video':
        print $oembed['html'];
        //print '<pre>';
        //print_r($oembed);
        //print '</pre>';
        break;
    case 'error':
    default:
    }
    print '</div>';
    print '<div class="clear"></div>';
}
?></body></html>
