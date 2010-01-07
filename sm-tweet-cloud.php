<?php
/*
Plugin Name: Tweet Cloud
Plugin URI: http://dev.stephenmcintyre.net/tweet-cloud
Description: Takes latest Twitter updates and aggregates them into a cloud for sidebar or otherwise.
Author: Stephen McIntyre
Version: 1.3
Author URI: http://stephenmcintyre.net

	Copyright (c) 2009 Stephen McIntyre (http://stephenmcintyre.net)
	Tweet Cloud is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

*/

function sm_tweet_cloud($username = NULL, $wordlimit = 20, $minchar = 3, $wordlinks = true) {
	
	echo "\n".'<!--Tweet Cloud by Stephen McIntyre of http://stephenmcintyre.net-->'."\n";
	echo '<div class="sm-tweet-cloud">'."\n";
	
	function sm_tweet_cloud_error($string) {
		echo 'Tweet Cloud: ' . $string;
	}
	
	if($username == NULL) {
		sm_tweet_cloud_error('Needs a user name.');
		return;
	}
	
	if(!is_int($wordlimit) || !is_int($minchar)) {
		sm_tweet_cloud_error('Params 2 and 3 must be numbers.');
		return;
	}
	
	$twurl = 'http://search.twitter.com/search.json?rpp=20&from=' . $username;
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $twurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$feed = curl_exec($curl);
	curl_close($curl);
	
	if(!($tw = @json_decode($feed))) {
		sm_tweet_cloud_error('Could not retrieve Twitter data.');
		return;
	}
	
	if($tw->max_id == -1) {
		sm_tweet_cloud_error('Incorrect username.');
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
		
	foreach($tw->results as $item) {
		$pot .= $item->text . ' ';
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
		if($wordlinks) echo '<a href="http://search.twitter.com/search?from=' . $username . '&amp;ands=' . urlencode($text) . '">';
		echo '<span style="font-size:' . ($dbv / 2) . 'em;">' . sm_tweet_cloud_ascii($text) . '</span>';
		if($wordlinks) echo '</a>' . "\n";
	}
	
	echo '</div>'."\n";
}

function sm_tweet_link($username = NULL) {
	if($username == NULL) {
		sm_tweet_cloud_error('Expects user name.');
		return;
	} else {
		echo '<a href="http://twitter.com/' . $username . '">' . sm_tweet_cloud_ascii('@' . $username) . '</a>'."\n";
	}
}

function sm_tweet_widget($username = NULL, $wordlimit = 20, $minchar = 3, $wordlinks = true) {
?>
	<li><h2>Tweet Cloud</h2>
    	<ul>
            <li>
            <?php sm_tweet_cloud($username, $wordlimit, $minchar, $wordlinks = true) ?>
            </li>
            <li><?php sm_tweet_link($username) ?></li>
        </ul>
    </li>
<?php
}
?>