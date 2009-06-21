=== Tweet Cloud ===
Contributors: Stephen McIntyre
Donate link: http://dev.stephenmcintyre.net/tweet-cloud
Tags: tweet, cloud, twitter
Requires at least: 1.0
Tested up to: 2.8
Stable tag: 1.3

Cloud of popular words and phrases from a user's Twitter profile.

== Description ==

Cloud of popular words and phrases from a user's Twitter profile. Instead of using a tag cloud, which is dependent on your posts and options, Tweet Cloud aggregates the most common words mentioned on your Twitter profile and keeps your users up to date with the latest trending topics that are interesting you.

== Installation ==
1. Upload `sm-tweet-cloud.php` into the `wp-content/plugins/` directory.
2. Activate `Tweet Cloud` from the `Plugins` menu in the `Admin Control Panel`.
3. Copy and paste one of the code examples in the `Code` section in `Other Notes` into any PHP page on your site.
4. Enjoy :)

== Code ==

Tweet Cloud

`<?php sm_tweet_cloud("USERNAME", WORDLIMIT, MINCHAR, WORDLINKS, RESULTS) ?>`

Profile Link

`<?php sm_tweet_link("USERNAME") ?>`

Sidebar Widget

Copy and paste this into `sidebar.php` after a `</li>` tag or the first `<ul>` tag

`<?php sm_tweet_widget("USERNAME", WORDLIMIT, MINCHAR, WORDLINKS, RESULTS) ?>`

== Parameters ==

USERNAME (remember to enclose in quotations "")

The user name assigned to your account

WORDLIMIT (can only be a number, don't enclose in quotations)

The amount of words displayed in the Cloud

default = 20

MINCHAR (can only be a number, don't enclose in quotations)

Filters words to this set minimum amount of characters so they won't be counted and listed

default = 3

WORDLINKS (can only be true or false, don't enclose in quotations)

Sets whether words display as links in the cloud (on = true, off = false)

default = true

== Screenshots ==
1. screenshot-1.png
2. screenshot-2.png

== Frequently Asked Questions ==

To ask a question or give feedback, send an email to emailme@stephenmcintyre.net

== Release Notes ==

1.3

* Uses JSON formatted Twitter Search API to speed up data retrieval process
* Doesn't require user ID parameter
* Results parameter added to alter number of tweets processed at a time

1.2

* Links each word to a Twitter Search result of your status updates that they originated from
* Wordlinks parameter added to set links on/off
* XHTML compliance with W3C standards

1.1

* Displays words in most common case format (i.e. WordPress instead of wordpress)
* cURL used for feed access instead of relying on `allow_url_fopen` being on
* Improved error handling including newly added user name check