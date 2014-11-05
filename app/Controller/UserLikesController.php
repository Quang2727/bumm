<?php

class UserLikesController extends AppController {

    public $uses = array('UserAlbum', 'UserInfo', 'User', 'UserLikedList', 'UserBlockedList', 'UserFriendList', "UserNotification");
    public $components = array('Paginator');

    

    function findByUser() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
//        $dataRequest['user_id'] = 166;
//        $dataRequest['page'] = 2;
//        $dataRequest['user_find_id'] = 3;
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $user = $this->User->findById(@$dataRequest["user_find_id"]);
        if (!$user)
            return $this->ApiNG();
        $result = $this->User->ConvertDatJson($user);
        if (($this->UserFriendList->checkExistFriend($dataRequest["user_find_id"], $dataRequest["user_id"])))
            $isFriends = 1;
        else
            $isFriends = 0;
        return $this->response->body(json_encode(array(
                    "data" => $result,
                    "isFriend" => $isFriends
        )));
    }

}
