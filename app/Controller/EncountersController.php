<?php

class EncountersController extends AppController {

    public $uses = array('UserAlbum', 'UserInfo', "UserLikedList", 'User', 'UserBlockedList', 'UserFriendList', "UserNotification");
    public $components = array('Paginator');

    function find($isFriends = null) {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        //  $dataRequest['user_id'] = 166;
        //   $dataRequest['page'] = 1;
//           $dataRequest['gender'] = 2;
//        $dataRequest['lat'] = 10.753937;
//         $dataRequest['lng'] = 106.674961;
        $conditions = array();
        $page = $dataRequest['page'];
        $user = $this->UserInfo->findByUserId(@$dataRequest["user_id"]);
        if (!$user)
            return $this->ApiNG();
        $search = (array) json_decode($user['UserInfo']['data_search']);
        if (isset($dataRequest["gender"]) && $dataRequest["gender"] >= 0)
            $gender = $dataRequest["gender"];
        else
            $gender = $search["gender"];
        $search["gender"] = $gender;
        $search["age_start"] = empty($dataRequest["age_start"]) ? @$search["age_start"] : $dataRequest["age_start"];
        $search["age_end"] = empty($dataRequest["age_end"]) ? @$search["age_end"] : $dataRequest["age_end"];
        $user['UserInfo']['data_search'] = json_encode($search);
        $this->UserInfo->Save($user["UserInfo"]);
        $search = (array) json_decode($user['UserInfo']['data_search']);
        $conditions["User.deleted_flg"] = false;
        //   $conditions["User.id"] = 4;
        if (!empty($search["gender"]))
            $conditions["UserInfo.gender"] = $search["gender"];
        if (isset($search["age_start"])) {
            $conditions["YEAR(NOW()) - YEAR(UserInfo.birthdate) >="] = $search['age_start'];
        }
        if (isset($search['age_end']) && $search['age_end'] < 50) {
            $conditions["YEAR(NOW()) - YEAR(UserInfo.birthdate) <="] = $search['age_end'];
        }
        $blockList = $this->UserBlockedList->getBlockUser($dataRequest['user_id']);
        $friends = $this->UserFriendList->getListFriend($dataRequest['user_id']);
        $likes = $this->UserLikedList->getListLike($dataRequest['user_id']);
        $not_array = array_merge($blockList, $friends);
        $not_array = array_merge($not_array, $likes);
        $not_array[] = $dataRequest["user_id"];
        $page_count = 0;
        if (!empty($dataRequest['user_id_show'])) {
            if ($page == 1) {
                $conditions = array();
                $conditions["UserInfo.user_id"] = $dataRequest['user_id_show'];
                $page_count =2;
            } else {
                $not_array[] = $dataRequest['user_id_show'];
                $not_array[] = -1;
                $conditions["NOT"] = array("UserInfo.user_id" => array_unique($not_array));
            }
        } else {
            if (!empty($not_array)) {
                $not_array[] = -1;
                $conditions["NOT"] = array("UserInfo.user_id" => array_unique($not_array));
            }
        }
        $distance = null;
        if (isset($dataRequest['lat']) && isset($dataRequest['lng'])) {
            $distance = '(((acos(sin(("' . $dataRequest['lat'] . '"*pi()/180))*sin((`UserInfo`.`lat`*pi()/180))+cos(("' . $dataRequest['lat'] . '"*pi()/180))*cos((`UserInfo`.`lat`*pi()/180)) * cos((("' . $dataRequest['lng'] . '"- `UserInfo`.`lng`)*pi()/180))))*180/pi())*60*1.1515)*1609.344 as distance';
            $order = '`distance` ASC ,User.modified DESC';
        } else {
            $order = "UserInfo.modified DESC";
        }
        $this->Paginator->settings = array(
            "fields" => array("UserInfo.user_id", "User.user_api_cd", "UserInfo.thinking", "UserInfo.name", "UserInfo.gender", "UserInfo.avatar", "UserInfo.birthdate", $distance),
            "conditions" => $conditions,
            "page" => $page,
            'order' => $order,
            "recursive" => 1,
            "limit" => 1,
        );
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $data = $this->Paginator->paginate('User');
        $result = array();
        foreach ($data as $val) {
            $result = $this->User->ConvertDatJson($val);
        }
        if(empty($page_count))
            $page_count = $this->request->params['paging']['User']['page'];
        return $this->response->body(json_encode(array(
                    "data" => $result,
                    "page" => $page_count,
                    "count" => 555,
                    "isFriend" => $isFriends
        )));
    }

    function saveLike() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $isFriends = 0;
        if (!$dataRequest['user_id'] || !$dataRequest['user_like_id'])
            return $this->ApiNG();
        if (!($this->UserFriendList->checkExistFriend($dataRequest["user_like_id"], $dataRequest["user_id"])) && !($this->UserBlockedList->checkExistBlock($dataRequest["user_like_id"], $dataRequest["user_id"])) && !$this->UserLikedList->checkExistLike($dataRequest["user_id"], $dataRequest["user_like_id"])
        ) {
            $likeFriend = $this->UserLikedList->checkExistLike($dataRequest["user_like_id"], $dataRequest["user_id"]);
            if (!empty($likeFriend)) {
                $dataSave = array(
                    "user_id" => $dataRequest["user_id"],
                    "user_friend_id" => $dataRequest["user_like_id"],
                    "accepted_flg" => ACCEPT,
                );
                $isFriends = 1;
                $this->UserFriendList->save($dataSave);
                $this->UserLikedList->deleteLike($dataRequest["user_id"], $dataRequest["user_like_id"]);
                $this->UserNotification->saveNoti($dataRequest["user_id"], $dataRequest["user_like_id"], NOTI_FRIEND);
                $this->UserFriendList->deleteRequest($dataRequest['user_id'], $dataRequest['user_like_id']);
            } else {
                $dataSave = array(
                    "user_id" => $dataRequest["user_id"],
                    "user_like_id" => $dataRequest["user_like_id"],
                );
                $this->UserLikedList->save($dataSave);
                $this->UserNotification->saveNoti($dataRequest["user_id"], $dataRequest["user_like_id"], NOTI_LIKE);
            }
        }
        return $this->find($isFriends);
    }

    function findByUser() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
//        $dataRequest['user_id'] = 166;
//        $dataRequest['page'] = 2;
//        $dataRequest['user_find_id'] = 191;
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $user = $this->User->findById(@$dataRequest["user_find_id"]);

        if (!$user)
            return $this->ApiNG();
        $result = $this->User->ConvertDatJson($user);
        if (($this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["user_find_id"])))
            $isFriends = ACCEPT;
        else if (($this->UserFriendList->checkExistRequest($dataRequest["user_id"], $dataRequest["user_find_id"])))
            $isFriends = REQUEST;
        else
            $isFriends = 0;
        return $this->response->body(json_encode(array(
                    "data" => $result,
                    "isFriend" => $isFriends
        )));
    }

}
