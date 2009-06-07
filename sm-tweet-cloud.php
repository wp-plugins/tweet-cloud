<?php
/*
Plugin Name: Tweet Cloud
Plugin URI: http://dev.stephenmcintyre.net/tweet-cloud
Description: Takes latest Twitter updates and aggregates them into a cloud for sidebar or otherwise.
Author: Stephen McIntyre
Version: 1.1
Author URI: http://stephenmcintyre.net

	Copyright (c) 2009 Stephen McIntyre (http://stephenmcintyre.net)
	Tweet Cloud is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

*/

function sm_tweet_cloud($username = NULL, $userid = NULL, $wordlimit = 20, $minchar = 3) {
	
	echo "\n".'<!--Tweet Cloud by Stephen McIntyre of http://stephenmcintyre.net-->'."\n";
	echo '<div class="sm-tweet-cloud">'."\n";
	
	function sm_tweet_cloud_error($string) {
		echo 'Tweet Cloud: ' . $string;
	}
	
	if($username == NULL || $userid == NULL) {
		sm_tweet_cloud_error('Needs at least a name and ID.');
		return;
	}
	
	if(!is_int($wordlimit) || !is_int($minchar)) {
		sm_tweet_cloud_error('Params 3 and 4 must be numbers.');
		return;
	}
	
	$twurl = 'http://twitter.com/statuses/user_timeline/' . $userid . '.rss';
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $twurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$feed = curl_exec($curl);
	curl_close($curl);
	
	if(!($tw = @simplexml_load_string($feed))) {
		sm_tweet_cloud_error('Could not retrieve Twitter data.');
		return;
	}
	
	if(substr($tw->channel->item[0]->title, 0, strlen($username)) != $username) {
		sm_tweet_cloud_error('Incorrect user name / ID combination.');
		return;
	}
	
	function sm_tweet_cloud_ascii( $string ){
		$arr = str_split( $string );
		$new_string = '';
		foreach( $arr as $char ){
			if( !eregi( '[a-z0-9 \.\,\-]', $char ) ){
				if( $char != htmlspecialchars( $char ) ){
					$new_string .= htmlspecialchars( $char );
				}
				else{
					$new_string .= '&#' . ord( $char ) . ';';
				}
			}
			else{
				$new_string .= $char;
			}
		}
		return $new_string;
	}
	
	foreach($tw->channel->item as $item) {
		$pot .= substr($item->title, strlen($username . ': '), strlen($item->title)) . ' ';
	}
	$words = explode(' ', $pot);
	
	foreach($words as $word) {
		while(!eregi('^[0-9a-z\@\#]', $word) && $word != '') {
			$word = substr($word, 1);
		}
		while(!eregi('[0-9a-z]$', $word) && $word != '') {
			$word = substr($word, 0, -1);
		}
		$pre = array('http://', 'www.');
		if($word != '' && !ereg('^[0-9]$', $word) && strlen($word) > $minchar) {
			$pass = true;
			foreach($pre as $pr) if(substr($word, 0, strlen($pr)) == $pr) $pass = false;
			if($pass) {
				$db['count'][strtolower($word)]++;
				$db['words'][strtolower($word)][$word]++;
			}
		}
	}
	
	if($wordlimit != 0) {
		arsort($db['count'], SORT_NUMERIC);
		
		array_splice($db['count'], $wordlimit);
	}
	
	ksort($db['count'], SORT_STRING);
	
	foreach($db['count'] as $dbk => $dbv) {
		arsort($db['words'][$dbk], SORT_NUMERIC);
		array_splice($db['words'][$dbk], 1);
		$text = key($db['words'][$dbk]);
		echo '<span style="font-size:' . ($dbv / 2) . 'em;">' . sm_tweet_cloud_ascii($text) . '</span>'."\n";
	}
	
}

function sm_tweet_link($username = NULL) {
	if($username == NULL) {
		sm_tweet_cloud_error('Expects user name.');
		return;
	} else {
		echo '<a href="http://twitter.com/' . $username . '">' . sm_tweet_cloud_ascii('@' . $username) . '</a>';
	}
}

function sm_tweet_widget($username = NULL, $userid = NULL, $wordlimit = 20, $minchar = 3) {
?>
	<li><h2>Tweet Cloud</h2>
    	<ul>
            <li>
            <?php sm_tweet_cloud($username, $userid, $wordlimit, $minchar) ?>
            </li>
            <li><?php sm_tweet_link($username) ?></li>
        </ul>
    </li>
<?php
}
?>