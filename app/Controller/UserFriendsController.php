<?php

class UserFriendsController extends AppController {

    public $uses = array('UserInfo', 'User', "UserNotification", 'UserLikedList', 'UserFriendList', 'UserBlockedList');

    // update flag friend ( show number frend requested)  when read data  
    function updateFlgFriend($id = null) {
        $this->UserFriendList->create();
        $this->UserFriendList->updateAll(
                array('UserFriendList.read_flg' => FLAG_ON), array(
            'UserFriendList.user_friend_id =' => $id,
            'UserFriendList.accepted_flg =' => REQUEST
                )
        );
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => 1)));
    }

    function verifyDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    function favourite() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
//        $dataRequest["user_id"] = 166;
//        $dataRequest["favourite_user_id"] = 1;
//        $dataRequest["is_favourite"] = 0;
        $exist = $this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["favourite_user_id"]);
        if (!empty($exist)) {
            $exist["UserFriendList"]["favourite"] = $dataRequest["is_favourite"];
            $this->UserFriendList->save($exist);
        }
        $this->response->body(json_encode(array(
            "errors" => "",
        )));
    }

    function find() {
        // 1,8,9,13,16,17,23,53,57,169,170,175,180,185,190
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
       // $this->request->data['user_id'] = 166;
       // $this->request->data['list_friends'] = "1,2,3,8,9,13,16,17,23,57,169,170,175,185,190,198";
        // $this->request->data['list_friends'] = "";
        $dataRequest = $this->request->data;
        if (empty($dataRequest["user_id"])) {
            return $this->ApiNG();
        }
        $listFriends = array();
        if (!empty($this->request->data['list_friends'])) {
            $listFriends = explode(",", $this->request->data['list_friends']);
        }


        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user["User"]["last_update_contact"]) && $this->verifyDate($user["User"]["last_update_contact"]))
            $timeUpdate = date("H:i, Y/m/d", strtotime($user["User"]["last_update_contact"]));
        else
            $timeUpdate = "-------------";
        $blocks = $this->UserBlockedList->getBlockUser($dataRequest["user_id"]);
        $blocks[] = $dataRequest["user_id"];
        $blocks[] = -1;
        $data = $this->UserFriendList->find("all", array(
            "fields" => array("UserFriendList.user_friend_id", "UserFriendList.favourite", "UserFriendList.accepted_flg", "UserFriendList.read_flg", "UserFriendList.user_id"),
            "conditions" => array(
                "UserFriendList.accepted_flg" => ACCEPT,
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
        $listFavourite = $listUser = array();
        foreach ($data as $val) {
            if ($val["UserFriendList"]["user_id"] == $dataRequest['user_id']) {
                if ($val["UserFriendList"]["favourite"] == FAVOURITE) {
                    $listFavourite[$val["UserFriendList"]["user_friend_id"]] = $val["UserFriendList"]["user_friend_id"];
                }
                $listUser[] = $val["UserFriendList"]["user_friend_id"];
            } else {
                if ($val["UserFriendList"]["favourite"] == FAVOURITE) {
                    $listFavourite[$val["UserFriendList"]["user_id"]] = $val["UserFriendList"]["user_id"];
                }
                $listUser[] = $val["UserFriendList"]["user_id"];
            }
        }

        $this->UserInfo->contain("User");
        $findUser = $this->UserInfo->find('all', array(
            'conditions' => array(
                'User.id' => $listUser,
                'User.id <>' => $blocks,
                'User.deleted_flg' => FALSE
            ),
            'recursive' => -1,
            "fields" => array("UserInfo.user_id", "UserInfo.name", "UserInfo.avatar", "UserInfo.gender")
        ));
        if (!empty($findUser) && empty($listFriends)) {
            foreach ($findUser as $val) {
                $listFriends[] = $val["UserInfo"]["user_id"];
            }
        }
        $buffer = array();
        foreach ($findUser as $val) {
            if (!empty($val["UserInfo"]["avatar"]))
                $val["UserInfo"]["avatar"] = Router::url('/', true) . $val["UserInfo"]["avatar"];
            $buffer[] = $val;
        }
        $findUser = $buffer;
        $countNewRequest = $this->UserFriendList->find("count", array(
            "conditions" => array(
                "UserFriendList.accepted_flg" => REQUEST,
                "UserFriendList.read_flg" => FLAG_OFF,
                "UserFriendList.user_friend_id <>" => $blocks,
                "UserFriendList.user_friend_id" => $dataRequest['user_id'],
            ),
        ));
        $listDataFav = $listIdNews = $listNew = $listName = array();
        foreach ($findUser as $key => $val) {

            if (in_array($val["UserInfo"]["user_id"], $listFriends)) {
                if (!empty($listFavourite[$val["UserInfo"]["user_id"]])) {
                    $listDataFav[] = $val["UserInfo"];
                } else {
                    $listName[$key] = $val["UserInfo"]["name"];
                }
            } else {
                $listNew[] = $val["UserInfo"];
                $listIdNews[] = $val["UserInfo"]["user_id"];
            }
        }
        $listName = array_map('strtolower', $listName);
        $array_out = array();
        foreach ($listName as $key => $value) {
            if (is_numeric($value[0])) {
                $array_out['#'][] = $listName[$key];
                asort($array_out['#']);
            } else {
                $array_out[$value[0]][] = $findUser[$key]["UserInfo"];
                asort($array_out[$value[0]]);
            }
        }
        $result = array();
        $section = array();
        if (!empty($listNew)) {
            $section[] = "New Friends";
            // $result[] = $listNew;
        }
        if (!empty($listDataFav)) {
            $section[] = "Favourite";
            $result[] = $listDataFav;
        }
        // pr($data);exit;
        ksort($array_out);
        foreach ($array_out as $key => $value) {
            $result[] = array_values($value);
            $section[] = $key;
        }
        $section = array_map('strtoupper', $section);
        $dataSearch = array();
        foreach ($findUser as $val) {
            $dataSearch[] = $val['UserInfo'];
        }
        return $this->response->body(json_encode(array(
                    "sections" => $section,
                    "data" => $result,
                    "dataSearch" => $dataSearch,
                    "updateContact" => $timeUpdate,
                    "listIdNews" => $listIdNews,
                    "countFriend" => $countNewRequest,
        )));
    }

    function saveFriend() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $data = $this->UserFriendList->find("first", array(
            "conditions" => array(
                "UserFriendList.accepted_flg" => REQUEST,
                "UserFriendList.user_friend_id" => $dataRequest['user_id'],
                "UserFriendList.user_id" => $dataRequest['user_friend_id'],
            )
        ));
        $ret = NULL;
        if (!empty($data) && !$this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["user_friend_id"])) {
            $data["UserFriendList"]["accepted_flg"] = ACCEPT;
            $ret = $this->UserFriendList->save($data);
            $this->UserNotification->saveNoti($dataRequest["user_id"], $dataRequest["user_friend_id"], NOTI_FRIEND);
            $this->UserLikedList->deleteLike($dataRequest['user_id'], $dataRequest['user_friend_id']);
            $this->UserFriendList->deleteRequest($dataRequest['user_id'], $dataRequest['user_friend_id']);
        }
        $this->response->body(json_encode(array(
            "data" => $ret,
            "errors" => "",
        )));
    }

    function deleteBlock() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $blockList = $this->UserBlockedList->find("first", array(
            "conditions" => array(
                "UserBlockedList.user_id" => $dataRequest["user_id"],
                "UserBlockedList.user_blocked_id" => $dataRequest["user_blocked_id"]
            )
        ));
        if (!empty($blockList)) {
            $this->UserBlockedList->delete($blockList["UserBlockedList"]["id"]);
        }
        if (!empty($dataRequest["return"])) {
            return $this->response->body(json_encode(array()));
        }
        $ret = $this->getListUserBlock();
        $this->response->body(json_encode(array(
            "data" => $ret,
            "errors" => "",
        )));
    }

    function saveBlock() {
//        $this->request->data["user_id"] = 166;
//        $this->request->data["user_blocked_id"] = 10;
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $existData = $this->UserBlockedList->find("first", array(
            "conditions" => array(
                "user_id" => $dataRequest["user_id"],
                "user_blocked_id" => $dataRequest["user_blocked_id"],
            )
        ));
        $ret = NULL;
        if (empty($existData)) {
            $dataSave = array(
                "user_id" => $dataRequest["user_id"],
                "user_blocked_id" => $dataRequest["user_blocked_id"],
            );
            $ret = $this->UserBlockedList->save($dataSave);
        }
        if (!empty($dataRequest["return"])) {
            return $this->response->body(json_encode(array(
                        "data" => 1,
            )));
        }
        return $this->find();
    }

    function deleteFriend() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
//          $this->request->data["user_id"] = 166; 
//        $this->request->data["user_friend_id"] = 1;
        $dataRequest = $this->request->data;
        $data = $this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["user_friend_id"]);
        $ret = NULL;
        if (!empty($data)) {
            $ret = $this->UserFriendList->delete($data["UserFriendList"]["id"]);
        }
        return $this->find();
    }

    function findChat() {
        $this->autoRender = false;
        $this->response->type("json");
        //  $this->request->data["list_user_id"] = "";
        //  $this->request->data["list_time"] = "";
        // $this->request->data["user_api_cd"] = "1226552";
        $dataRequest = $this->request->data;
        $user = $this->User->findByUserApiCd($dataRequest["user_api_cd"]);
        if (!$user)
            return $this->ApiNG();
        $list_user = explode(",", $dataRequest["list_user_id"]);
        $list_time = explode(",", $dataRequest["list_time"]);
        $resultTime = array();
        $list_user = array_diff($list_user, array($dataRequest["user_api_cd"]));
        $key = 0;

        foreach ($list_user as $val) {
            $resultTime[$val] = $list_time[$key];
            $key++;
        }
        if (empty($list_time[0]))
            return $this->ApiNG();
        $this->User->contain("UserInfo");
        $order = "FIELD(User.user_api_cd," . implode(", ", $list_user) . ")";
        $blockList = $this->UserBlockedList->getBlockUser($user["User"]['id']);
        if (!empty($blockList))
            $blockList[] = -1;
        $data = $this->User->find("all", array(
            "conditions" => array(
                "User.user_api_cd" => $list_user,
                "User.id <>" => $blockList,
            ),
            "order" => $order
        ));
        if (!$data)
            return $this->ApiNG();
        $result = array();
        foreach ($data as $val) {
            $result[] = array(
                "user_api_cd" => $val["User"]["user_api_cd"],
                "user_id" => $val["User"]["id"],
                "avatar" => Router::url('/', true) . $val["UserInfo"]["avatar"],
                "name" => $val["UserInfo"]["name"],
                "gender" => $val["UserInfo"]["gender"],
                "modified" => $this->displayPostTime(@$resultTime[$val["User"]["user_api_cd"]]),
            );
        }
        $this->response->body(json_encode(array(
            "data" => $result,
        )));
    }

}
