<?php
/*
Plugin Name: Tweet Cloud
Plugin URI: http://dev.stephenmcintyre.net/tweet-cloud
Description: Takes latest Twitter updates and aggregates them into a cloud for sidebar or otherwise.
Author: Stephen McIntyre
Version: 1.4
Author URI: http://stephenmcintyre.net

	Copyright (c) 2009 Stephen McIntyre (http://stephenmcintyre.net)
	Tweet Cloud is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

*/

function sm_tweet_cloud(
		$username = NULL,
		$wordlimit = 20,
		$wordlinks = true,
		$remove_types = array(),
		$excludes = array('a','an','and','are','as','at','be','but','by','can','can\'t','do','does','don\'t','for','from','get','have','he','her','his','i','i\'m','in','is','it','me','my','not','of','on','one','or','say','she','that','the','their','they','this','to','we','will','won\'t','with','you')
	){
	
	$types = array(
		'@' => true,
		'#' => true,
		'RT' => true
	);
	
	if( !empty( $remove_types ) ){
		foreach( $remove_types as $type ){
			$types[$type] = false;
		}
	}
	
	echo "\n" . '<!--Tweet Cloud by Stephen McIntyre of http://stephenmcintyre.net-->' . "\n";
	echo '<div class="sm-tweet-cloud">' . "\n";
	
	if( $username == NULL ){
		sm_tweet_cloud_error( 'Needs a user name.' );
		return;
	}
	
	$txt_name = '/sm-tweet-cloud-data.txt';
	
	$txt_curl = curl_init();
	curl_setopt( $txt_curl, CURLOPT_URL, 'http://localhost/' . PLUGINDIR . $txt_name );
	curl_setopt( $txt_curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $txt_curl, CURLOPT_FILETIME, true );
	$txt_file = curl_exec( $txt_curl );
	$txt_modtime = curl_getinfo( $txt_curl, CURLINFO_FILETIME );
	curl_close( $txt_curl);
	
	if( date( 'd m y', $txt_modtime ) != date( 'd m y' ) ){
		
		$twurl = 'http://search.twitter.com/search.json?rpp=20&from=' . $username;
		
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $twurl );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		$feed = curl_exec( $curl );
		curl_close( $curl );
		
		if( !( $tw = @json_decode( $feed ) ) ){
			sm_tweet_cloud_error( 'Could not retrieve Twitter data.' );
			return;
		}
		
		if( $tw->max_id == -1 ){
			sm_tweet_cloud_error( 'Incorrect username.' );
			return;
		}
				
		foreach( $tw->results as $item ){
			$pot .= $item->text . ' ';
		}
		$words = explode( ' ', $pot );
		
		foreach( $words as $word ){
			if(
				!(
					( $word == '' )
					||
					( !$types['@'] && substr($word, 0, 1) == '@' )
					||
					( !$types['#'] && substr($word, 0, 1) == '#' )
					||
					( !$types['RT'] && substr($word, 0, 2) == 'RT' )
					||
					( substr($word, 0, 7) == 'http://' || substr($word, 0, 4) == 'www.' )
					||
					( in_array( strtolower( $word ), $excludes ) )
				)
			){
				$word = html_entity_decode( $word );
				while( !preg_match( '/^[0-9a-z@#]/i', $word ) && $word != '' ){
					$word = substr( $word, 1 );
				}
				while( !preg_match( '/[0-9a-z]$/i', $word ) && $word != '' ){
					$word = substr( $word, 0, -1 );
				}
				if( $word != '' && !preg_match( '/^[0-9]$/', $word ) ){
					$lower_word = strtolower( $word );
					$db['count'][$lower_word]++;
					$db['words'][$lower_word][$word]++;
				}
			}
		}
		
		if( $wordlimit != 0 ){
			arsort( $db['count'], SORT_NUMERIC );
			
			array_splice( $db['count'], $wordlimit );
		}
		
		ksort( $db['count'], SORT_STRING );
		
		foreach( $db['count'] as $dbk => $dbv ){
			arsort( $db['words'][$dbk], SORT_NUMERIC );
			$text = key( $db['words'][$dbk] );
			$db['sorted'][$text] = $db['words'][$dbk][$text];
		}
		
		$file = fopen( WP_PLUGIN_DIR . $txt_name, 'w' );
		fwrite( $file, json_encode( $db['sorted'] ) );
		fclose( $file );
	} else{
		$db['sorted'] = json_decode( $txt_file );
	}	
	
	foreach( $db['sorted'] as $dsk => $dsv ){
		if( $wordlinks ) echo '<a href="http://search.twitter.com/search?from=' . $username . '&amp;ands=' . urlencode( $dsk ) . '">';
		echo '<span style="font-size:' . ( $dsv / 2 ) . 'em;">' . htmlentities( $dsk ) . '</span>';
		if( $wordlinks ) echo '</a>' . "\n";
	}
	
	echo '</div>' . "\n";
}

function sm_tweet_link( $username = NULL ){
	if( $username == NULL ) {
		sm_tweet_cloud_error( 'Expects user name.' );
		return;
	} else {
		echo '<a href="http://twitter.com/' . $username . '">@' . $username . '</a>' . "\n";
	}
}

function sm_tweet_widget( $username = NULL, $wordlimit = 20, $wordlinks = true, $remove_types = array() ){
?>
	<li><h2>Tweet Cloud</h2>
    	<ul>
            <li>
            <?php sm_tweet_cloud( $username, $wordlimit, $wordlinks, $remove_types ) ?>
            </li>
            <li><?php sm_tweet_link( $username ) ?></li>
        </ul>
    </li>
<?php 
}

function sm_tweet_cloud_error( $string ){
	echo 'Tweet Cloud: ' . $string . '</div>' . "\n";
}
?>