<?php

class NotificationsController extends AppController {

    public $uses = array('UserInfo', 'User', "UserNotification", 'UserLikedList', 'UserFriendList', 'UserBlockedList');

    
    // get notification realTime ( not used)
    function getNewEvens() {
        APP::import("Model", array("User", "UserFriendList", "UserNotification", "UserBlockedList"));
        $this->User = new User();
        $this->UserBlockedList = new UserBlockedList();
        $this->UserNotification = new UserNotification();
        $this->UserFriendList = new UserFriendList();
        $dataRequest = $this->request->data;
        $dataRequest['user_id'] = 164;
        $dataRequest['user_friend_id'] = "1,2,3,";
        $dataRequest['user_noti_id'] = "1,2,3";
        $noti_friend = 0;
        $noti_Noti = 0;
        $getNotification = $this->UserNotification->find("first", array(
            "fields" => array("UserNotification.user_id"),
            "conditions" => array(
                "UserNotification.delete_flg" => FLAG_OFF,
                "UserNotification.read_flg" => FLAG_OFF,
                "UserNotification.user_notification_id" => $dataRequest['user_id'],
            ),
            "order" => "UserNotification.created DESC"
        ));
        if (!empty($getNotification))
            $noti_Noti = 1;
        $blockList = $this->UserBlockedList->find("list", array(
            "fields" => array("UserBlockedList.user_blocked_id", "UserBlockedList.user_blocked_id"),
            "conditions" => array("UserBlockedList.user_id" => $dataRequest['user_id'])
        ));
        $getFriend = $this->UserFriendList->find("all", array(
            "fields" => array("UserFriendList.user_friend_id", "UserFriendList.accepted_flg", "UserFriendList.read_flg", "UserFriendList.user_id"),
            "conditions" => array(
                "UserFriendList.accepted_flg" => array(REQUEST, ACCEPT),
                "UserFriendList.deleted_flg" => FLAG_OFF,
                "OR" => array(
                    array(
                        "UserFriendList.user_id" => $dataRequest['user_id'],
                    ),
                    array(
                        "UserFriendList.user_friend_id" => $dataRequest['user_id'],
                    ),
                )
            ),
        ));
        foreach ($getFriend as $val) {
            if ($val['UserFriendList']['accepted_flg'] == ACCEPT) {
                $idSelect = $val['UserFriendList']['user_friend_id'];
                if ($idSelect == $dataRequest['user_id']) {
                    $idSelect = $val['UserFriendList']['user_id'];
                }
                if (empty($blockList[$idSelect]))
                    $listIdFriend[$idSelect] = $idSelect;
            } else {
                if ($val['UserFriendList']['read_flg'] == FLAG_OFF && $val['UserFriendList']['user_friend_id'] == $dataRequest['user_id'] && empty($blockList[$val['UserFriendList']['user_id']])) {
                    $countFriend[] = $val['UserFriendList']['user_id'];
                }
            }
        }
        $listIdFriend = implode(",", $listIdFriend);
        if (!empty($countFriend)) {
            $noti_friend = 1;
        }
        if ($listIdFriend != $dataRequest['user_friend_id']) {
            $noti_friend = 1;
        }
        $dataNoti = array(
            "friends" => $noti_friend,
            "noti" => $noti_Noti
        );
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $dataNoti)));
    }



}
