<?php

App::uses('AppModel', 'Model');

/**
 * UserFriendList Model
 *
 * @property User $User
 * @property UserFriend $UserFriend
 */
class UserFriendList extends AppModel {

    function checkExistFriend($user_id, $friend_id) {
        $data = $this->find("first", array(
            "conditions" => array(
                "accepted_flg" => ACCEPT,
                "OR" => array(
                    array(
                        'user_id' => $user_id,
                        'user_friend_id' => $friend_id,
                    ),
                    array(
                        'user_id' => $friend_id,
                        'user_friend_id' => $user_id
                    ))
            )
        ));
        if (!empty($data))
            return $data;
        return NULL;
    }

    function checkExistRequest($user_id, $friend_id) {
        $data = $this->find("first", array(
            "conditions" => array(
                "accepted_flg" => REQUEST,
                array(
                    'user_id' => $user_id,
                    'user_friend_id' => $friend_id,
                ),
            )
        ));
        if (!empty($data))
            return $data;
        return NULL;
    }

    function deleteRequest($user_id, $friend_id) {
        $data = $this->find("first", array(
            "conditions" => array(
                "accepted_flg" => REQUEST,
                "OR" => array(
                    array(
                        'user_id' => $user_id,
                        'user_friend_id' => $friend_id,
                    ),
                    array(
                        'user_id' => $friend_id,
                        'user_friend_id' => $user_id
                    ))
            )
        ));
        return $this->delete($data["UserFriendList"]["id"]);
    }

    function getListFriend($user_id) {
        $data = $this->find("all", array(
            "conditions" => array(
                "accepted_flg" => ACCEPT,
                "OR" => array(
                    array(
                        'user_id' => $user_id,
                    ),
                    array(
                        'user_friend_id' => $user_id
                    ))
            ),
            "fields" => array("user_id", "user_friend_id")
        ));

        $result = array();
        foreach ($data as $val) {
            if ($val["UserFriendList"]["user_id"] != $user_id)
                $result[] = $val["UserFriendList"]["user_id"];
            else
                $result[] = $val["UserFriendList"]["user_friend_id"];
        }
        return $result;
    }

}
