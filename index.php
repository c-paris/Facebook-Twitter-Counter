<?php
require "TwitterOauth/autoload.php";
use		Abraham\TwitterOAuth\TwitterOAuth;

$link = "PUT_YOUR_LINK_HERE";

# Facebook credentials #
$Facebook_Token = "FACEBOOK_TOKEN HERE";

# Twitter credentials #
$consumer_key = "CONSUMER_KEY_HERE";
$consumer_secret = "CONSUMER_SECRET_HERE";
$access_token = "ACCESS_TOKEN_HERE";
$access_token_secret = "ACCESS_TOKEN_SECRET_HERE";

/*-------------------------------------------------------------------------------------------------------
--------------------------------------------------------------------------------------------------------*/

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
$resultats_twitter = (string) $result;
$search = array("\t", "\n", "\r", " ","  ", "   ", "    ");
$resultats_twitter = str_replace($search, '', $resultats_twitter);
$position_debut_nbr_entrees = strpos($resultats_twitter, '["statuses"]=>array(') + 20;
$position_fin_nbr_entrees = strpos($resultats_twitter, ')', $position_debut_nbr_entrees);
$nbr_entrees = substr($resultats_twitter, $position_debut_nbr_entrees, $position_fin_nbr_entrees - $position_debut_nbr_entrees);
$compteur_tweet = 0;
$compteur_retweet = 0;
$compteur_favorite = 0;
if(is_numeric($nbr_entrees)){
for ($i=0; $i < $nbr_entrees ; $i++) {
    $entree[$i] = strpos($resultats_twitter, '['.$i.']');
    $position_debut_tweet[$i] = strpos($resultats_twitter, '["text"]=>string', $entree[$i]);
    $position_fin_tweet[$i] = strpos($resultats_twitter, '["truncated"]', $entree[$i]);
    $contenu_tweet[$i] = substr($resultats_twitter, $position_debut_tweet[$i], $position_fin_tweet[$i] - $position_debut_tweet[$i]);
    $estce_retweet[$i] = strpos($contenu_tweet[$i], '"RT@');
    if ($estce_retweet[$i] == FALSE) {
        $compteur_tweet = $compteur_tweet + 1;
        $position_debut_nbr_retweet[$i] = strpos($resultats_twitter, '["retweet_count"]=>int(', $entree[$i])+23;
        $position_fin_nbr_retweet[$i] = strpos($resultats_twitter, ')', $position_debut_nbr_retweet[$i]);
        $compteur_retweet = $compteur_retweet + substr($resultats_twitter, $position_debut_nbr_retweet[$i], $position_fin_nbr_retweet[$i] - $position_debut_nbr_retweet[$i]);
        $position_debut_nbr_favorite[$i] = strpos($resultats_twitter, '["favorite_count"]=>int(', $entree[$i])+24;
        $position_fin_nbr_favorite[$i] = strpos($resultats_twitter, ')', $position_debut_nbr_favorite[$i]);
        $compteur_favorite = $compteur_favorite + substr($resultats_twitter, $position_debut_nbr_favorite[$i], $position_fin_nbr_favorite[$i] - $position_debut_nbr_favorite[$i]);

    };
}
} else {
    $errorMsg = "Error - Can't show any informations in Twitter. Please change link.";
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
	  Tweets : '.$compteur_tweet.'<br>
	  Retweets : '.$compteur_retweet.'<br>
	  Likes : '.$compteur_favorite.'';
} else {
	echo $errorMsg;
}
?>
