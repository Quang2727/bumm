<?php

App::uses('AppModel', 'Model');

/**
 * UserBlockedList Model
 *
 * @property User $User
 * @property UserBlocked $UserBlocked
 */
class UserBlockedList extends AppModel {

    //get  data block user
    function getBlockUser($user_id) {
        $data = $this->find("all", array(
            "conditions" => array(
                "OR" => array(
                    array(
                        'user_id' => $user_id,
                    ),
                    array(
                        'user_blocked_id' => $user_id
                    ))
            ),
            "fields" => array("user_id", "user_blocked_id"),
            "order" => array("UserBlockedList.modified DESC")
        ));

        $result = array();
        foreach ($data as $val) {
            if ($val["UserBlockedList"]["user_id"] != $user_id)
                $result[] = $val["UserBlockedList"]["user_id"];
            else
                $result[] = $val["UserBlockedList"]["user_blocked_id"];
        }

        return $result;
    }

    function checkExistBlock($user_id_a, $user_id_b) {
        $blockList = $this->find("first", array(
            "fields" => array("UserBlockedList.user_blocked_id"),
            "conditions" => array(
                "OR" => array(
                    array(
                        'user_id' => $user_id_a,
                        'user_blocked_id' => $user_id_b,
                    ),
                    array(
                        'user_id' => $user_id_b,
                        'user_blocked_id' => $user_id_a,
                    ))
            ),
            "order" => array("UserBlockedList.modified DESC")
        ));
        if (!empty($blockList))
            return $blockList;
        return NULL;
    }

}
