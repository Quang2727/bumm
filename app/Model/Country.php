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
class Country extends AppModel {

    public $actsAs = array('Containable');
    public $hasMany = array(
        'DetailCountry' => array(
            'className' => 'DetailCountry',
            'foreignKey' => 'country_id',
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
    );

}
