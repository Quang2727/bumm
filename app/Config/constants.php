<?php

define('CONSUMER_KEY', 'XLBK6CSS8fUbNOih5W9scsieB');
define('CONSUMER_SECRET', 'eIDPMvEnxHvh8tM9mGxHIPFwY5WvRUWAZvXvWjxT9Y5jnZWkLi');
define('ACCESS_TOKEN', '2288909150-U0sCikPOGvC5VxptQDwCFxpRtHxyArLG5jlOVlk');
define('ACCESS_TOKEN_SECRET', '78fJfq8l3GwB39u1PUslBZXwpsf6Sjvg0YlL0aTdE42wH');
define('OAUTH_CALLBACK', 'localhost/twitteroauth/callback.php');

// for flags
define('FLAG_ON', 1);
define('FLAG_OFF', 0);
define('LIST_USER_LIMIT', 30);
define('LIMIT_POST', 10);
define('LIMIT_FRIEND', 100);

define('LIMIT_HEIGHT_POST',200);


define('CACHE_URL', 3);


define('FAVOURITE', 1);

// avatar
define('USER_AVATAR_ALLOWED_SIZE', 2000000); // 2MB

define('EMAIL', 1);
define('FACEBOOK', 2);
define('TWITTER', 3);
define('GOOGLE', 4);

define('NOT_ACCEPT', 0);
define('ACCEPT', 1);
define('REQUEST', 2);

define('READ_FLG_ON', 1);
define('READ_FLG_OFF', 0);


define('TYPE_IMAGE', 1);
define('TYPE_EMOTION', 2);
define('TYPE_RECORDER', 3);
define('TYPE_URL', 4);



// Type of notification

define('NOTI_LIKE', 1);
define('NOTI_FRIEND', 2);
define('NOTI_FRIEND_REQUEST', 3);
define('NOTI_CHAT', 5);

define('FORUM_PHOTOS', 1);

define('MALE', 1);
define('FEMALE', 2);
define('ALL', 0);

define('FLAG_NOT_DELETED', 0);
define('FLAG_DELETED', 1);

define('APIKEY', "9a415bd0");
define('APISECRET', "abc4784f");


define('NOT_CHANGE', 0);

define('CHANGED', 1);

define('EMAIL_TO', "thainn@leverages.jp");

define('LIMIT_PHOTO', 6);

define('ALBUM_NAME', "__default__");

define('PUBLIC_TYPE', 0);

Configure::write('en', array(
    'Unidentified', //0
    'Present', //1
    'Just a moment', //2
    'Less than 1 minute', //3
    'Ago %d minute', //4
    'Ago %d hour', //5
    'Ago %d day', //6
    'Less than a month', //7
    'Ago %d week', //8
    'Ago %d month', //9
    '1 year', //10
    'Ago 1 year', //11
));

define('DATETIME_FORMAT', 'Y/m/d H:i');

Configure::write('vi-VN', array(
    'Không xác định', //0
    'Hiện tại', //1
    'Vừa mới', //2
    'Nhỏ hơn 1 phút', //3
    'cách đây %d phút', //4
    'Cách đây %d giờ', //5
    'Cách đây %d ngày', //6
    'Nhỏ hơn 1 tháng', //7
    'Cách đây %d tuần', //8
    'Cách đây %d tháng', //9
    '1 năm', //10
    'Cách đây 1 năm', //11
));
Configure::write('ja', array(
    'なし', //0
    '今', //1
    'ちょうど今', //2
    '約1分前', //3
    '%d分前', //4
    '%d時間前', //5
    '%d日前', //6
    '1月前', //7
    '%d週間前', //8
    '%d月前', //9
    '1年', //10
    '1年以上前', //11
));
