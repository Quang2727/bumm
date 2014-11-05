<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config_auth.php');

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
 ACCESS_TOKEN,
 ACCESS_TOKEN_SECRET);

$content = $connection->get('account/verify_credentials');
/* Some example calls */
//$test = $connection->get('users/show', array('screen_name' => 'kids0407'));
var_dump($content);
exit;
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992));
//$connection->post('friendships/destroy', array('id' => 9436992));

