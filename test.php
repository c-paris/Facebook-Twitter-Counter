<?php
require "TwitterOauth/autoload.php";
use		Abraham\TwitterOAuth\TwitterOAuth;

$link = "PUT_YOUR_LINK_HERE";

//--------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------FACEBOOK & TWITTER AUTH----------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------//

# Facebook credentials #
$Facebook_Token = "FACEBOOK_TOKEN HERE";

# Twitter credentials #
$consumer_key = "CONSUMER_KEY_HERE";
$consumer_secret = "CONSUMER_SECRET_HERE";
$access_token = "ACCESS_TOKEN_HERE";
$access_token_secret = "ACCESS_TOKEN_SECRET_HERE";

//--------------------------------------------------------------------------------------------------------------------//
//---------------------------------------DO NOT CHANGE ANYTHING HERE--------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------//

$error = 0;

$Facebook_request="https://graph.facebook.com/?fields=engagement&access_token=$Facebook_Token&id=$link";
$Facebook_result = file_get_contents($Facebook_request);
$Facebook_data = json_decode($Facebook_result, true);

$fb_commentCount = ($Facebook_data['engagement']['comment_count']);
$fb_reactionCount = ($Facebook_data['engagement']['reaction_count']);
$fb_shareCount = ($Facebook_data['engagement']['share_count']);
$fb_commentPluginCount = ($Facebook_data['engagement']['comment_plugin_count']);

$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
$content = $connection->get("account/verify_credentials");
$statuses = $connection->get("search/tweets", ["q" => $link, "include_entities" => "false", "count" => 100]);

$test = json_encode($statuses);
$count = substr_count($test, "statuses");
$favorites = substr_count($test, "favorite_count");

ob_start();
var_dump($statuses);
$result = ob_get_clean();
$twitterResults = (string) $result;

$search = array("\t", "\n", "\r", " ","  ", "   ", "    ");
$twitterResults = str_replace($search, '', $twitterResults);
$beginEntriesPos = strpos($twitterResults, '["statuses"]=>array(') + 20;
$endEntriesPos = strpos($twitterResults, ')', $beginEntriesPos);
$entriesNbr = substr($twitterResults, $beginEntriesPos, $endEntriesPos - $beginEntriesPos);
$tweetCounter = 0;
$retweetCounter = 0;
$favoriteCounter = 0;

if(is_numeric($entriesNbr))
{
    for ($i=0; $i < $entriesNbr ; $i++)
    {
        $entry[$i] = strpos($twitterResults, '['.$i.']');
        $beginTweetPos[$i] = strpos($twitterResults, '["text"]=>string', $entry[$i]);
        $endTweetPos[$i] = strpos($twitterResults, '["truncated"]', $entry[$i]);
        $tweetContent[$i] = substr($twitterResults, $beginTweetPos[$i], $endTweetPos[$i] - $beginTweetPos[$i]);
        $isRetweet[$i] = strpos($tweetContent[$i], '"RT@');

        if ($isRetweet[$i] == FALSE)
        {
            $tweetCounter = $tweetCounter + 1;
            $beginRetweetPos[$i] = strpos($twitterResults, '["retweet_count"]=>int(', $entry[$i])+23;
            $endRetweetPos[$i] = strpos($twitterResults, ')', $beginRetweetPos[$i]);
            $retweetCounter = $retweetCounter + substr($twitterResults, $beginRetweetPos[$i], $endRetweetPos[$i] - $beginRetweetPos[$i]);
            $beginFavoritePos[$i] = strpos($twitterResults, '["favorite_count"]=>int(', $entry[$i])+24;
            $endFavoritePos[$i] = strpos($twitterResults, ')', $beginFavoritePos[$i]);
            $favoriteCounter = $favoriteCounter + substr($twitterResults, $beginFavoritePos[$i], $endFavoritePos[$i] - $beginFavoritePos[$i]);
        };
    }
} else {
    $errorMsg = "Error - Entries are not numbers.";
    $error = 1;
}

echo 'Link to get : '.$link.'<br>
	  <h1> Facebook </h1> <br>
	  Comments : '.$fb_commentCount.'<br>
	  Reactions : '.$fb_reactionCount.'<br>
	  Shares : '.$fb_shareCount.'<br>
	  Comments Plugin : '.$fb_commentPluginCount.'<br><br>';
if(empty($error))
{
    echo '<h1> Twitter </h1> <br>
	  Tweets : '.$tweetCounter.'<br>
	  Retweets : '.$retweetCounter.'<br>
	  Likes : '.$favoriteCounter.'';
} else {
    echo $errorMsg;
}

?>