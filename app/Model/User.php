<?php

App::uses('AppModel', 'Model');

/**
 * User Model
 *
 * @property UserAlbum $UserAlbum
 * @property UserBlockedList $UserBlockedList
 * @property UserFavList $UserFavList
 * @property UserFriendList $UserFriendList
 * @property UserInfo $UserInfo
 * @property UserLikedList $UserLikedList
 * @property UserNotificationSetting $UserNotificationSetting
 * @property UserProfileSetting $UserProfileSetting
 * @property UserSearchSetting $UserSearchSetting
 * @property UserSnsInfo $UserSnsInfo
 * @property UserVisitorList $UserVisitorList
 */
class User extends AppModel {

    public $actsAs = array('Containable');
    // public $findMethods = array(
    // 'friends' => true,
    // 'follower' => true,
    // 'following' => true,
    // 'liked' => true,
    // 'blocked' => true,
    // 'visitors' => true,
    // 'encounter' => true
    // );

    /**
     * Validation rules
     *
     * @var array
     */
    //The Associations below have been created with all possible keys, those that are not needed can be removed

    public $hasOne = array(
        'UserInfo' => array(
            'className' => 'UserInfo',
            'foreignKey' => 'user_id',
            'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
    );
    public $hasMany = array(
        'Album' => array(
            'className' => 'UserAlbum',
            'foreignKey' => 'user_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'FriendRequest' => array(
            'className' => 'UserFriendList',
            'foreignKey' => 'user_friend_id',
            'dependent' => false,
            'conditions' => array(
                'FriendRequest.accepted_flg' => FLAG_OFF
            ),
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
    );
    public $hasAndBelongsToMany = array(
        'Friend' => array(
            'className' => 'User',
            'joinTable' => NULL,
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'user_friend_id',
            'unique' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'with' => 'UserFriendList'
        ),
        'Liked' => array(
            'className' => 'User',
            'joinTable' => NULL,
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'user_like_id',
            'unique' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'with' => 'UserLikedList'
        ),
        'Blocked' => array(
            'className' => 'User',
            'joinTable' => NULL,
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'user_blocked_id',
            'unique' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'with' => 'UserBlockedList'
        ),
    );

    function ConvertDatJson($val) {
        if (!empty($val['UserInfo']['avatar'])) {
            $val['UserInfo']['avatar'] = Router::url('/', true) . $val['UserInfo']['avatar'];
        }
        if (empty($val["UserInfo"]["background"])) {
            $val["UserInfo"]["background"] = Router::url('/', true) . 'app/systems/background.png';
        } else {
            $val["UserInfo"]["background"] = Router::url('/', true) . $val["UserInfo"]["background"];
        }
        $birthdate = str_replace('/', '-', $val['UserInfo']['birthdate']);
        $val['UserInfo']['age'] = intval(date("Y") - date("Y", strtotime($birthdate)));
        $val['UserInfo']['photos'] = array();
        $photos = array();
        // if (!empty($val['UserInfo']['avatar'])) {
        // $photos[] = $val['UserInfo']['avatar'];
        // }
        if (!empty($val['Album'])) {
            foreach ($val['Album'] as $key => $value) {
                if (!empty($value['AlbumPhoto'])) {
                    foreach ($value['AlbumPhoto'] as $keyPhoto => $valuePhoto) {
                        // if(  $key== 0 && $keyPhoto ==0)
                        // break;
                        $photos[] = Router::url('/', true) . $valuePhoto['photo'];
                    }
                }
            }
        }
        if (empty($photos)) {
            $photos[] = Router::url('/', true) . 'img/system/no_photo.png';
            $val['UserInfo']['count_photo'] = 0;
        } else {
            $val['UserInfo']['count_photo'] = count($photos);
        }
        $val['UserInfo']['photos'] = $photos;
        $val['UserInfo']['user_api_cd'] = $val['User']['user_api_cd'];
        return $val['UserInfo'];
    }

}
