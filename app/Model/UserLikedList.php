<?php

App::uses('AppModel', 'Model');

/**
 * UserLikedList Model
 *
 * @property User $User
 * @property UserLike $UserLike
 */
class UserLikedList extends AppModel {

    /**
     * Use table
     *
     * @var mixed False or table name
     */
    public $useTable = 'user_liked_list';

    function checkExistLike($user_id, $user_like_id) {
        $data = $this->find("first", array(
            "conditions" => array(
                array(
                    'user_id' => $user_id,
                    'user_like_id' => $user_like_id,
                ),
            )
        ));
        if (!empty($data)) {
            return $data;
        }
        return NULL;
    }

    function getListLike($user_id) {
        $data = $this->find("list", array(
            "conditions" => array(
                array(
                    'user_id' => $user_id,
                ),
            ),
            "fields" => array("id", "user_like_id")
        ));
        return $data;
    }

    function deleteLike($user_id, $user_like_id) {
        $data = $this->find("first", array(
            "conditions" => array(
                "OR" => array(
                    array(
                        'user_id' => $user_id,
                        'user_like_id' => $user_like_id,
                    ),
                    array(
                        'user_id' => $user_like_id,
                        'user_like_id' => $user_id
                    ))
            )
        ));
        if (!empty($data)) {
            $this->delete($data["UserLikedList"]["id"]);
        }
        return true;
    }

}
