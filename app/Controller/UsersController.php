<?php

class UsersController extends AppController {

    public $uses = array('UserAlbum', 'UserInfo', "ReportUser", 'DetailCountry', "AlbumPhoto", 'User', "UserNotification", 'UserLikedList', 'UserFriendList', 'UserBlockedList', "AlbumPhoto");
    public $components = array('Paginator');

    function report() {
//        $this->request->data["report_user_id"] = "1";
//        $this->request->data["user_id"] = "166";
        $dataRequest = $this->request->data;
        $data = $this->User->findById($dataRequest["report_user_id"]);
        if (!empty($data)) {
            $dataReport = $this->ReportUser->find("first", array(
                "conditions" => array(
                    "ReportUser.report_user_id" => $this->request->data["report_user_id"],
                    "ReportUser.user_id" => $this->request->data["user_id"],
                )
            ));
            $dataSave = array();
            if (!empty($dataReport)) {
                $dataReport["ReportUser"]["count"] = $dataReport["ReportUser"]["count"] + 1;
                $dataSave = $dataReport["ReportUser"];
            } else {
                $dataReport = array(
                    "report_user_id" => $dataRequest["report_user_id"],
                    "user_id" => $dataRequest["user_id"],
                    "count" => 1,
                );
            }
            $this->ReportUser->save($dataReport);
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("errors" => 0)));
    }

    // random password_phone
    public function randomPassword() {
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $password = str_pad($code, 9, '0', STR_PAD_LEFT);
        $findUser = $this->User->find('first', array(
            'conditions' => array(
                'User.password' => $password
            ),
            'fields' => array('id'),
            'recursive' => -1,
        ));
        if (!empty($findUser)) {
            $this->randomPassword();
        }
        return array("password" => $password, "code" => $code);
    }

    function updateContact() {
        $this->autoRender = false;
        $this->response->type("json");
        // $this->request->data["user_id"] = 166;
        // $this->request->data["phone_user"] = "+2012346,090 2824549,090 9090909,098 5852587,063 524875";
        $dataRequest = $this->request->data;
        if (!isset($dataRequest["user_id"]))
            return $this->ApiNG();
        $user = $this->User->findById($this->request->data["user_id"]);
        if (!empty($dataRequest["phone_user"])) {
            $dataRequest["phone_user"] = str_replace(array(' ', ' '), "", $dataRequest["phone_user"]);
            $phone_user = explode(",", $dataRequest["phone_user"]);
            $keyCountry = $this->DetailCountry->find("list", array(
                "fields" => array("DetailCountry.key_country", "DetailCountry.key_country")
            ));
            $listPhone = array();
            foreach ($phone_user as $val) {
                if (strpos($val, '+') === false) {
                    foreach ($keyCountry as $value) {
                        $listPhone[] = trim($value . "" . $val);
                    }
                } else {
                    $listPhone[] = $val;
                }
            }
            $blockList = $this->UserBlockedList->getBlockUser($dataRequest['user_id']);
            $friends = $this->UserFriendList->getListFriend($dataRequest['user_id']);
            $not_array = array_merge($blockList, $friends);
            $not_array[] = $dataRequest["user_id"];
            if (!empty($not_array))
                $not_array[] = -1;
            $listUsers = $this->User->find('list', array(
                'conditions' => array(
                    'User.id <>' => array_unique($not_array),
                    'User.phone' => $listPhone,
                ),
                'fields' => array('id'),
                'recursive' => -1,
            ));
            $dataSave = array();
            foreach ($listUsers as $val) {
                $dataSave[] = array(
                    "user_id" => $dataRequest["user_id"],
                    "user_friend_id" => $val,
                    "accepted_flg" => ACCEPT,
                );
                $this->UserLikedList->deleteLike($dataRequest['user_id'], $val);
            }
            if (!empty($dataSave)) {
                $this->UserFriendList->create();
                $this->UserFriendList->saveMany($dataSave);
            }
        }

        $dt = new DateTime();
        $user["User"]["last_update_contact"] = $dt->format('Y-m-d H:i:s');
        $this->User->save($user);
        $timeUpdate = date("H:i, Y/m/d");
        return $this->response->body(json_encode(array(
                    "data" => $timeUpdate,
        )));
    }

    function findShake() {
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        //     $dataRequest["user_id"] = 166;
//                $dataRequest['lat'] = 10.753937;
//         $dataRequest['lng'] = 106.674961;
        if (!isset($dataRequest["user_id"]))
            return $this->ApiNG();
        $this->User->contain("UserInfo");
        $user = $this->User->findById(@$dataRequest["user_id"]);
        $dt = new DateTime();
        $user["User"]["last_update_shake"] = $dt->format('Y-m-d H:i:s');
        $this->User->save($user);
        $conditions["User.deleted_flg"] = false;
        $blockList = $this->UserBlockedList->getBlockUser($dataRequest['user_id']);
        $friends = $this->UserFriendList->getListFriend($dataRequest['user_id']);
        $not_array = array_merge($blockList, $friends);
        $not_array[] = $dataRequest["user_id"];
        $not_array[] = -1;
        $conditions["NOT"] = array("User.id" => array_unique($not_array));
        $distance = null;
        if (!empty($dataRequest['lat']) && !empty($dataRequest['lng'])) {
            $distance = '(((acos(sin(("' . $dataRequest['lat'] . '"*pi()/180))*sin((`UserInfo`.`lat`*pi()/180))+cos(("' . $dataRequest['lat'] . '"*pi()/180))*cos((`UserInfo`.`lat`*pi()/180)) * cos((("' . $dataRequest['lng'] . '"- `UserInfo`.`lng`)*pi()/180))))*180/pi())*60*1.1515)*1609.344 as distance';
        }
//        $this->Paginator->settings = array(
//            "fields" => array("UserInfo.user_id", "User.user_api_cd", "UserInfo.thinking", "UserInfo.name", "UserInfo.gender", "UserInfo.avatar", "UserInfo.birthdate", $distance),
//            "conditions" => $conditions,
//            "page" => $page,
//            'order' => $order,
//            "recursive" => 1,
//            "limit" => LIST_USER_LIMIT,
//        );

        $this->User->contain("UserInfo");
        $data = $this->User->find('all', array(
            "fields" => array("UserInfo.user_id", "UserInfo.thinking", "User.user_api_cd", "UserInfo.name", "UserInfo.gender", "UserInfo.avatar", "UserInfo.birthdate", $distance),
            'conditions' => $conditions,
            'recursive' => -1,
            "limit" => 30,
            "order" => array("User.last_update_shake DESC", "User.modified DESC")
        ));
        shuffle($data);
        $result = $array_1 = $array_2 = array();
        foreach ($data as $val) {
            if (isset($val['0']['distance'])) {
                $array_1[] = $this->convertDataSearch($val);
            } else {
                $array_2[] = $this->convertDataSearch($val);
            }
            if (count($array_1) + count($array_2) >= 20)
                break;
        }
        $result = array_merge($array_1, $array_2);
        return $this->response->body(json_encode(array(
                    "data" => $result,
        )));
    }

    function findPhoneNumber() {
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        if (!isset($dataRequest["phone"]))
            return $this->ApiNG();
        $phone = $this->convertPhone($dataRequest["phone"]);
        $data = $this->User->find("first", array(
            "conditions" => array("User.phone" => $phone, "User.deleted_flg" => false)
        ));
        if (!empty($data)) {
            return $this->response->body(json_encode(array(
                        "data" => $data["User"]["id"],
            )));
        }
        return $this->response->body(json_encode(array(
                    "data" => 0,
        )));
    }

    function configUser() {
//        $this->request->data['user_id'] = 166;
//        $this->request->data['user_find_id'] = 1;
        $this->User->contain("UserInfo");
        $user = $this->User->findById($this->request->data['user_find_id']);
        $dataRequest = $this->request->data;
        if (($ret = $this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["user_find_id"])))
            $isFriends = ACCEPT;
        else if (($this->UserFriendList->checkExistRequest($dataRequest["user_id"], $dataRequest["user_find_id"])))
            $isFriends = REQUEST;
        else
            $isFriends = 0;
        $result["isFriends"] = $isFriends;
        $is_block = $this->UserBlockedList->checkExistBlock($dataRequest["user_id"], $dataRequest["user_find_id"]);
        if (!empty($is_block))
            $result["isBlock"] = 1;
        else
            $result["isBlock"] = 0;
        if (!empty($user)) {
            if (!empty($user['UserInfo']['avatar'])) {
                $result["avatar"] = Router::url('/', true) . $user["UserInfo"]["avatar"];
            } else {
                $result["avatar"] = "";
            }
            $result["gender"] = intval($user["UserInfo"]["gender"]);
            $result["name"] = $user["UserInfo"]["name"];
            if (!empty($ret)) {
                $result["isFavourite"] = $ret["UserFriendList"]["favourite"];
            } else {
                $result["isFavourite"] = 0;
            }
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array(
            "data" => $result,
        )));
    }

    function getListSearch() {
//        $this->request->data['page'] = 1;
//        $this->request->data['gender'] = -1;
//        $this->request->data['user_id'] = 166;
        $this->autoRender = false;
        $this->response->type("json");
        $page = MAX(intval(@$this->request->data['page']), 1);
        $dataRequest = $this->request->data;
        $this->User->contain("UserInfo");
        $data = $this->User->findById(@$dataRequest["user_id"]);
        if (!$data)
            return $this->ApiNG();
        $data["UserInfo"]["lat"] = empty($dataRequest["lat"]) ? $data["UserInfo"]["lat"] : $dataRequest["lat"];
        $data["UserInfo"]["lng"] = empty($dataRequest["lng"]) ? $data["UserInfo"]["lng"] : $dataRequest["lng"];
        $search = (array) json_decode($data['UserInfo']['data_search']);
        if (isset($dataRequest["gender"]) && $dataRequest["gender"] >= 0)
            $gender = $dataRequest["gender"];
        else
            $gender = $search["gender"];
        $search["gender"] = $gender;
        $search["age_start"] = empty($dataRequest["age_start"]) ? @$search["age_start"] : $dataRequest["age_start"];
        $search["age_end"] = empty($dataRequest["age_end"]) ? @$search["age_end"] : $dataRequest["age_end"];
        $data['UserInfo']['data_search'] = json_encode($search);
        $this->UserInfo->Save($data["UserInfo"]);
        $conditions["User.deleted_flg"] = false;
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
        $not_array = array_merge($blockList, $friends);
        $not_array[] = $dataRequest["user_id"];
        if (!empty($not_array)) {
            $not_array[] = -1;
            $conditions["NOT"] = array("UserInfo.user_id" => array_unique($not_array));
        }
        $distance = null;
        $order = array();
        if (!empty($dataRequest['lat']) && !empty($dataRequest['lng'])) {
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
            "limit" => LIST_USER_LIMIT,
        );
        $this->User->contain("UserInfo");
        $data = $this->Paginator->paginate('User');
        $result = $array_1 = $array_2 = array();
        $list_like = array();
        foreach ($data as $val) {
            if (isset($val['0']['distance'])) {
                $array_1[] = $this->convertDataSearch($val);
            } else {
                $array_2[] = $this->convertDataSearch($val);
            }
            $list_like[] = $val["UserInfo"]["user_id"];
        }
        $result_image = $result = array_merge($array_1, $array_2);
        shuffle($result_image);
        $list_image = array();
        $likes = $this->UserLikedList->find("list", array(
            "conditions" => array("UserLikedList.user_like_id" => $list_like,
                "UserLikedList.user_id" => $dataRequest['user_id']),
            "fields" => array("UserLikedList.user_like_id", "UserLikedList.user_id")
        ));
        foreach ($result_image as $key => $val) {
            if ($key > 20) {
                break;
            }
            if (!empty($val["avatar"]) && empty($likes[$val["user_id"]])) {
                $list_image[] = array(
                    "user_id" => $val["user_id"],
                    "avatar" => $val["avatar"],
                    "gender" => $val["gender"],
                );
            }
        }
        $this->response->body(json_encode(array(
            "data" => $result,
            "list_image" => $list_image,
            "page" => $this->request->params['paging']['User']['page'],
            "count" => $this->request->params['paging']['User']['pageCount']
        )));
    }

    function convertDataSearch($val) {
        if (!empty($val['UserInfo']['avatar'])) {
            $val['UserInfo']['avatar'] = Router::url('/', true) . $val['UserInfo']['avatar'];
        } else {
            $val['UserInfo']['avatar'] = "";
        }
        $val['UserInfo']['age'] = intval(date("Y") - date("Y", strtotime($val['UserInfo']['birthdate'])));
        $val['UserInfo']['thinking'] = $val['UserInfo']['thinking'];
        if (isset($val['0']['distance'])) {
            if ($val['0']['distance'] >= '1000') {
                $val['UserInfo']['distance'] = round((intval($val['0']['distance']) / 1000), 1) . ' km';
            } else {
                $val['UserInfo']['distance'] = MAX(round((intval($val['0']['distance'])), -1), 5) . ' m';
            }
        } else {
            $val['UserInfo']['distance'] = '';
        }
        $val['UserInfo']['gender'] = intval($val['UserInfo']['gender']);
        $val['UserInfo']['user_api_cd'] = $val['User']['user_api_cd'];
        return $val['UserInfo'];
    }

    function getListUserBlock() {
        $dataRequest = $this->request->data;
        if (!isset($dataRequest['user_id']))
            return $this->ApiNG();
        $blockList = $this->UserBlockedList->getBlockUser($dataRequest['user_id']);
        $result = array();
        $this->autoRender = false;
        $this->response->type("json");
        $this->UserInfo->contain("User");
        $result = array();
        if (!empty($blockList)) {
            $order = "FIELD(UserInfo.user_id," . implode(", ", $blockList) . ")";
            $findUser = $this->UserInfo->find('all', array(
                'conditions' => array(
                    'UserInfo.user_id' => $blockList
                ),
                'recursive' => -1,
                "order" => $order
            ));

            foreach ($findUser as $val) {
                if (!empty($val['UserInfo']['avatar'])) {
                    $val['UserInfo']['avatar'] = Router::url('/', true) . $val['UserInfo']['avatar'];
                }
                $result[] = $val['UserInfo'];
            }
        }
        $this->response->body(json_encode(array(
            "data" => $result,
            "errors" => "",
        )));
    }

    function findRequest() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
//        $this->request->data['user_id'] = 166;
        $dataRequest = $this->request->data;
        if (empty($dataRequest["user_id"])) {
            return $this->ApiNG();
        }
        $blocks = $this->UserBlockedList->getBlockUser($dataRequest["user_id"]);
        if (!empty($blocks))
            $blocks[] = -1;
        $data = $this->UserFriendList->find("all", array(
            "joins" => array(
                array(
                    "table" => "user_infos",
                    "alias" => "UserInfo",
                    "type" => "LEFT",
                    "conditions" => array(
                        "UserInfo.user_id = UserFriendList.user_id"
                    )
                )),
            "fields" => array("UserFriendList.user_id", "UserFriendList.messages", "UserFriendList.read_flg", "UserFriendList.id", "UserInfo.*"),
            "conditions" => array(
                "UserFriendList.accepted_flg" => REQUEST,
                "UserFriendList.user_friend_id" => $dataRequest['user_id'],
                "UserFriendList.user_id <>" => $blocks,
            ),
            "order" => array("UserFriendList.read_flg ASC", "UserFriendList.modified DESC")
        ));
        $this->UserFriendList->updateAll(
                array('UserFriendList.read_flg' => READ_FLG_ON), array('UserFriendList.user_friend_id' => $dataRequest['user_id'], 'UserFriendList.accepted_flg' => REQUEST)
        );
        $result = array();
        foreach ($data as $val) {
            if (!empty($val["UserInfo"]["avatar"]))
                $val["UserInfo"]["avatar"] = Router::url('/', true) . $val["UserInfo"]["avatar"];
            $result[] = array(
                "id" => $val["UserFriendList"]["id"],
                "user_id" => $val["UserInfo"]["user_id"],
                "name" => $val["UserInfo"]["name"],
                "messages" => $val["UserFriendList"]["messages"],
                "avatar" => $val["UserInfo"]["avatar"],
                "read_flg" => $val["UserFriendList"]["read_flg"],
                "gender" => $val["UserInfo"]["gender"],
            );
        }
        $this->response->body(json_encode(array(
            "data" => $result,
            "errors" => "",
        )));
    }

    function deleteRequest() {
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
        if (!empty($data)) {
            $this->UserFriendList->delete($data["UserFriendList"]["id"]);
            $this->UserLikedList->deleteLike($dataRequest['user_id'], $dataRequest['user_friend_id']);
        }
        $this->response->body(json_encode(array(
            "data" => $ret,
            "errors" => "",
        )));
    }

    function saveRequest() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
//        $dataRequest['user_id'] = 166;
//        $dataRequest['user_find_id'] = 95;
        $dataSave = array(
            "user_id" => $dataRequest['user_id'],
            "user_friend_id" => $dataRequest['user_find_id'],
            "accepted_flg" => REQUEST,
            "messages" => $dataRequest['messages'],
            "read_flg" => READ_FLG_OFF,
        );
        if (!$this->UserFriendList->checkExistRequest($dataRequest["user_id"], $dataRequest["user_find_id"])) {
            $this->UserFriendList->save($dataSave);
            $this->UserNotification->saveNoti($dataRequest["user_id"], $dataRequest["user_find_id"], NOTI_FRIEND_REQUEST);
        }
        return $this->response->body(json_encode(array(
                    "data" => 1,
                    "errors" => "",
        )));
    }

    // check  validate exist phone 
    function checkExistPhone() {
        $phone = $this->request->data["phone"];
        $this->autoRender = false;
        $this->response->type("json");
        $phone = $this->convertPhone($phone);
        $this->User->contain("UserInfo");
        $findUser = $this->User->find('first', array(
            'joins' => array(
                array(
                    'table' => 'detail_countries',
                    'alias' => 'DetailCountry',
                    'type' => 'INNER',
                    'conditions' => array(
                        'DetailCountry.id = UserInfo.detail_countries_id'
                    )
                )
            ),
            'conditions' => array(
                'User.phone' => $phone
            ),
            'fields' => array('User.id', "DetailCountry.*"),
            'recursive' => -1,
        ));
        if (!empty($findUser)) {
            $this->response->body(json_encode(array("data" => "1", "value" => $findUser["DetailCountry"])));
        } else {
            $this->response->body(json_encode(array("data" => "0")));
        }
    }

    // genarator code ios
    function sendSMS() {
        $dataRequest = $this->request->data;
        $phone = $dataRequest["phone"];
        $callAction = $dataRequest["callAction"];
        $this->autoRender = false;
        $this->response->type("json");
        $result = $this->randomPassword();
        $phone = $this->convertPhone($phone);
        $code = $result["code"];
//        $this->log($phone, 'error');
//        $this->log($callAction, 'error');
        if (!empty($callAction)) {
            $this->User->contain("UserInfo");
            $findUser = $this->User->find('first', array(
                'conditions' => array(
                    'User.phone' => $phone
                ),
                'recursive' => -1,
            ));
            if (empty($findUser)) {
                $this->autoRender = false;
                $this->response->type("json");
                $this->response->body(json_encode(array("data" => 0)));
                return;
            }
            $findUser["User"]["password_phone"] = $result["code"];
            $findUser["User"]["password_change"] = NOT_CHANGE;
            $this->User->create();
            $this->User->save($findUser);
            $avatar = Router::url('/', true) . $findUser["UserInfo"]["avatar"];
        }
        // if (!$this->send_SMS_API($code, $phone))
        //    return $this->response->body(json_encode(array("data" => 0)));
        $this->response->body(json_encode(array(
            "code" => $code,
            "password" => $result["password"],
            "data" => "1",
            "user_id" => isset($findUser["User"]["id"]) ? $findUser["User"]["id"] : "",
            "user_api_cd" => isset($findUser["User"]["user_api_cd"]) ? $findUser["User"]["user_api_cd"] : "",
            "password_api" => isset($findUser["User"]["password"]) ? $findUser["User"]["password"] : "",
            "name" => isset($findUser["UserInfo"]["name"]) ? $findUser["UserInfo"]["name"] : "",
            "gender" => isset($findUser["UserInfo"]["gender"]) ? $findUser["UserInfo"]["gender"] : "",
            "avatar" => isset($avatar) ? $avatar : ""
        )));
    }

    //Đăng nhập theo phone

    function login() {
        $dataRequest = $this->request->data;
        $this->User->contain("UserInfo");
        $this->log($dataRequest, "error");
        //  $dataRequest["phone"] = "+841297557909";
        //   $dataRequest["password_phone"] = "111111";
        $dataRequest["phone"] = $this->convertPhone($dataRequest["phone"]);
        $findUser = $this->User->find('first', array(
            'conditions' => array(
                "User.phone" => trim($dataRequest["phone"]),
                "User.deleted_flg" => false,
                "User.password_phone" => $dataRequest["password_phone"],
            ),
            'recursive' => -1,
        ));
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        if (!empty($findUser["UserInfo"]["avatar"]))
            $findUser["UserInfo"]["avatar"] = Router::url('/', true) . $findUser['UserInfo']['avatar'];
        if (!empty($findUser))
            $this->response->body(json_encode(array("status_login" => 1, "data" => $findUser["User"], "dataUser" => $findUser["UserInfo"])));
        else
            $this->response->body(json_encode(array("status_login" => 0)));
    }

    // send sms_api 
    function send_SMS_API($code, $phone) {
        //Sep: 9a415bd0 - abc4784f - 841204516271
        //Nam: a791a311 - 3c536d39 - 84974455098
        $APIKey = APIKEY;
        $SecretKey = APISECRET;
        $from = "ChatApp";
        $text = "(HeartConnect){$code} is activation code for  HeartConnect. Please enter {$code} to activate your HeartConnect account";
        $url = 'https://rest.nexmo.com/sms/json?api_key=' . $APIKey . '&api_secret=' . $SecretKey . '&from=' . $from . '&to=' . $phone . '&text=' . urlencode($text);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execCurl = curl_exec($ch);
        curl_close($ch);
        $dataResult = json_decode($execCurl, true);
        if ($dataResult['messages']['0']['status'] != 0) { // status = 0 : success
            return false;
        }
        return true;
    }

    //register User
    public function register() {
        $dataRequest = $this->request->data;
        $this->log($dataRequest, 'register');
        $dataRequest["phone"] = $this->convertPhone($dataRequest["phone"]);
        $dataUser = array(
            'gg_id' => $dataRequest['gg_id'],
            'tw_id' => $dataRequest['tw_id'],
            'fb_id' => $dataRequest['fb_id'],
            'access_token' => $dataRequest['access_token'],
            'password' => $dataRequest['password'],
            'password_phone' => $dataRequest['password_phone'],
            'phone' => $dataRequest['phone'],
            "user_api_cd" => $dataRequest['user_api_cd']
        );
        $dataUserInfo['name'] = @$dataRequest['name'];
        $dataUserInfo['gender'] = @$dataRequest['gender'];
        $dataUserInfo['country'] = @$dataRequest['country'];
        if (!empty($dataRequest['birthdate'])) {
            $dataRequest['birthdate'] = str_replace('/', '-', $dataRequest['birthdate']);
            $dataUserInfo['birthdate'] = date("Y-m-d", strtotime($dataRequest['birthdate']));
        }
        $dataSave = array(
            'User' => $dataUser,
            'UserInfo' => $dataUserInfo
        );
        $this->log($dataSave, "dataSave");
        $this->User->create();
        $user_id = "";
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        if ($this->User->saveAll($dataSave)) {
            $user_id = $this->User->id;
            return $this->response->body(json_encode(array("data" => 1, "errors" => "", "user_id" => $user_id)));
        } else {
            return $this->response->body(json_encode(array("data" => 0, "errors" => __("can not save user"), "user_id" => $user_id)));
        }
    }

    // update infomation user from social
    function updateInfoSocial() {
        $dataRequest = $this->request->data;
        switch ($dataRequest['type_login']) {
            case FACEBOOK:
                $dataSave = array(
                    "User.mail" => "'" . $dataRequest['email'] . "'",
                    "User.fb_id" => "'" . $dataRequest['key'] . "'",
                    "User.access_token" => "'" . $dataRequest['access_token'] . "'",
                );
                break;
            case TWITTER:
                $dataSave = array(
                    "User.mail" => "'" . $dataRequest['email'] . "'",
                    "User.tw_id" => "'" . $dataRequest['key'] . "'",
                    "User.access_token" => "'" . $dataRequest['access_token'] . "'",
                );
                break;
            case GOOGLE:
                $dataSave = array(
                    "User.mail" => "'" . $dataRequest['email'] . "'",
                    "User.gg_id" => "'" . $dataRequest['key'] . "'",
                    "User.access_token" => "'" . $dataRequest['access_token'] . "'",
                );
                break;
            default:
                $dataSave = array(
                    "User.mail" => "'" . $dataRequest['email'] . "'",
                );
        }
        if ($this->User->updateAll($dataSave, array('User.id' => $dataRequest['user_id']))) {
            $errors = '';
            $success = 1;
        } else {
            $errors = __("can not save user");
            $success = 0;
        }

        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $success, "errors" => $errors)));
    }

    function updatePassword() {
        $dataRequest = $this->request->data;
        //  $dataRequest["password"] = "123456";
        $idUser = $dataRequest["user_id"];
        $this->log($dataRequest, 'error');
        $this->autoRender = false;
        $this->response->type("json");
        $findUser = $this->User->find('first', array(
            'conditions' => array(
                'User.id' => $idUser,
                'User.deleted_flg' => false
            ),
            'recursive' => -1,
        ));
        if (!empty($dataRequest["password_old"]) && $findUser["User"]["password_phone"] != $dataRequest["password_old"]) {
            return $this->response->body(json_encode(array("data" => 0)));
        }
        $findUser["User"]["password_phone"] = $dataRequest["password"];
        $findUser["User"]["password_change"] = CHANGED;
        if ($this->User->save($findUser)) {
            return $this->response->body(json_encode(array("data" => 1)));
        }
        return $this->response->body(json_encode(array("data" => 0)));
    }

    function deleteUser($idUser) {
        $this->autoRender = false;
        $this->response->type("json");
        $findUser = $this->User->find('first', array(
            'conditions' => array(
                'User.id' => $idUser
            ),
            'recursive' => -1,
        ));
        $findUser["User"]["deleted_flg"] = FLAG_DELETED;
        $this->User->create();
        if ($this->User->save($findUser)) {
            return $this->response->body(json_encode(array("data" => 1)));
        }
        return $this->response->body(json_encode(array("data" => 0)));
    }

    //Lấy thông tin User
    function getInfoUser() {
        // $this->request->data["user_id"] = 166;
        $this->request->data["limit"] = 0;
        $idUser = $this->request->data["user_id"];
        $limit_photo = $this->request->data["limit"];
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $data = $this->User->find("first", array(
            "conditions" => array(
                "UserInfo.user_id" => $idUser,
            ),
            "recursive" => 2
        ));
        $data['UserInfo']['id'] = str_pad($data['UserInfo']['id'], 6, "0", STR_PAD_LEFT);
        if (!empty($data['UserInfo']['birthdate'])) {
            $birthdate = str_replace('/', '-', $data['UserInfo']['birthdate']);
            $data['UserInfo']['age'] = intval(date("Y") - date("Y", strtotime($birthdate)));
        } else {
            $data['UserInfo']['age'] = 0;
        }
        $data['UserInfo']['phone'] = $data['User']['phone'];
        $data['UserInfo']['password_change'] = $data['User']['password_change'];
        if ($limit_photo > 0) { // >0
            $limit = LIMIT_PHOTO + 2;
        } else if ($limit_photo < 0) // < 0 
            $limit = 0;
        else {
            $limit = LIMIT_PHOTO + 1; //0
        }
        if (!empty($data['UserInfo'])) {
            $data['UserInfo']["birthdate"] = date("d/m/Y", strtotime($data['UserInfo']["birthdate"]));
        }
        $result = $this->getAlbumPhoto($data, $limit);
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        return $this->response->body(json_encode(array("data" => $data['UserInfo'], "photos" => $result)));
    }

    function deleteAlbumPhoto() {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $folder = new Folder();
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
//        $this->request->data["listId"] = "74,60,33";
//        $this->request->data["user_id"] = "166";
//        $this->request->data["album_id"] = "22";
        $this->log($this->request->data, "error");
        if (!isset($this->request->data["listId"]) || empty($this->request->data["album_id"]) || empty($this->request->data["user_id"])) {
            return $this->response->body(json_encode(array("data" => 0)));
        }
        $user = $this->UserInfo->find("first", array(
            "conditions" => array("UserInfo.user_id" => $this->request->data["user_id"])
        ));
        if (!empty($this->request->data["listId"]))
            $listId = explode(",", $this->request->data["listId"]);
        $listId[] = -1;
        if (count($listId) == 1) {
            $user["UserInfo"]["avatar"] = "";
            $this->UserInfo->save($user);
            $this->UserAlbum->delete($this->request->data["album_id"]);
            $this->AlbumPhoto->deleteAll(array("AlbumPhoto.album_id" => $this->request->data["album_id"]));
            $namePath = 'img' . DS . $this->request->data["user_id"] . DS . "album";
            $realPath = WWW_ROOT . $namePath;
            $folder->delete($realPath);
            return $this->response->body(json_encode(array("data" => 1)));
        } else {
            $listPhoto = $this->AlbumPhoto->find("all", array(
                "conditions" => array(
                    "AlbumPhoto.id <>" => $listId,
                    "AlbumPhoto.album_id" => $this->request->data["album_id"]
                ),
            ));
            $arrayId = array();
            $arrayPhoto = array();
            if (!empty($listPhoto)) {
                foreach ($listPhoto as $val) {
                    $arrayId[] = $val["AlbumPhoto"]["id"];
                    $arrayPhoto[] = $val["AlbumPhoto"]["photo"];
                }
                $this->AlbumPhoto->deleteAll(array("AlbumPhoto.id" => $arrayId));
                foreach ($arrayPhoto as $val) {
                    $realPath = WWW_ROOT . $val;
                    $file = new File($realPath);
                    $file->delete($realPath);
                }
                $album = $this->AlbumPhoto->find("first", array(
                    "conditions" => array(
                        "AlbumPhoto.album_id" => $this->request->data["album_id"]
                    ),
                    "order" => array("AlbumPhoto.rank ASC")
                ));
                if (!empty($album)) {
                    $user["UserInfo"]["avatar"] = $album["AlbumPhoto"]["photo"];
                    $this->UserInfo->save($user);
                }
            }
        }
        return $this->response->body(json_encode(array("data" => 1)));
    }

    function updateRankPhotos() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
//        $this->request->data["listId"] = "33,34,32";
//        $this->request->data["maxRank"] = "8";
//        $this->request->data["user_id"] = "166";

        $listId = explode(",", $this->request->data["listId"]);
        $rank = 0;
        $user = $this->UserInfo->find("first", array(
            "conditions" => array("UserInfo.user_id" => $this->request->data["user_id"])
        ));
        $order = "FIELD(AlbumPhoto.id," . implode(", ", $listId) . ")";
        $listPhoto = $this->AlbumPhoto->find("all", array(
            "conditions" => array("AlbumPhoto.id" => $listId),
            "order" => $order
        ));
        $img = "";
        foreach ($listPhoto as $key => $val) {
            $rank++;
            $val["AlbumPhoto"]["rank"] = $rank;
            if ($key == 0)
                $img = $val["AlbumPhoto"]["photo"];
            $listPhoto[$key] = $val;
        }
        if (!empty($listPhoto)) {
            $user["UserInfo"]["avatar"] = $img;
            $this->AlbumPhoto->create();
            $this->UserInfo->save($user);
            if ($this->AlbumPhoto->saveMany($listPhoto))
                return $this->response->body(json_encode(array("data" => 1)));
        }
        return $this->response->body(json_encode(array("data" => 0)));
    }

    function getAlbumPhoto($data, $limit = null) {
        $listImage = array();
        foreach ($data["Album"] as $val) {
            foreach ($val["AlbumPhoto"] as $value) {
                $value['photo'] = Router::url('/', true) . $value['photo'];
                if (!empty($limit) && count($listImage) >= $limit)
                    return $listImage;
                $listImage[] = $value;
            }
        }
        return $listImage;
    }

//Cập nhật USER Profile
    function updateUser() {
        $data = $this->request->data;
        $idUser = $data["user_id"];
        $this->UserInfo->contain("User");
        $dataUser = $this->UserInfo->find("first", array(
            "conditions" => array(
                "UserInfo.user_id" => $idUser,
            )
        ));
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");

        $dataUser['UserInfo']['gender'] = isset($data['gender']) ? $data['gender'] : $dataUser['UserInfo']['gender'];
        $data['birthday'] = str_replace('/', '-', $data['birthday']);
        if (!empty($data['birthday']))
            $dataUser['UserInfo']['birthdate'] = isset($data['birthday']) ? date("Y-m-d", strtotime($data['birthday'])) : $dataUser['UserInfo']['birthdate'];
        $dataUser['UserInfo']['school'] = isset($data['school']) ? $data['school'] : $dataUser['UserInfo']['school'];
        $dataUser['UserInfo']['company'] = isset($data['company']) ? $data['company'] : $dataUser['UserInfo']['company'];
        $dataUser['UserInfo']['address'] = isset($data['address']) ? $data['address'] : $dataUser['UserInfo']['address'];
        $dataUser['UserInfo']['status_sex'] = isset($data['status_sex']) ? $data['status_sex'] : $dataUser['UserInfo']['status_sex'];
        $dataUser['UserInfo']['thinking'] = isset($data['think']) ? $data['think'] : $dataUser['UserInfo']['thinking'];
        $dataUser['UserInfo']['interest'] = isset($data['interest']) ? $data['interest'] : $dataUser['UserInfo']['interest'];
        $this->UserInfo->create();
        if ($this->UserInfo->save($dataUser))
            $this->response->body(json_encode(array("data" => 1, "errors" => "")));
        else
            $this->response->body(json_encode(array("data" => 0, "errors" => __("can not save user"))));
    }

    //Upload Avatar user
    public function uploadAvatarUser() {
//         $this->request->data["idUser"] = 166;
        $idUser = $this->request->data["user_id"];
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        App::uses('Folder', 'Utility');
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $data = $this->User->find("first", array(
            "conditions" => array(
                "User.id" => $idUser,
            )
        ));
        if (empty($data['UserInfo'])) {
            return $this->response->body(json_encode(array("data" => 0, "errors" => __("can not find user"))));
        }
        $idAlbum = $count = 0;
        if (empty($data["Album"])) {
            $saveAlbum = array(
                "user_id" => $idUser,
                "album_name" => ALBUM_NAME,
                "public_type" => PUBLIC_TYPE
            );
            $this->UserAlbum->create();
            $ret = $this->UserAlbum->save($saveAlbum, false);
            $idAlbum = $ret["UserAlbum"]["id"];
        } else {
            foreach ($data["Album"] as $value) {
                $idAlbum = $value["id"];
                if (!empty($value['AlbumPhoto'])) {
                    foreach ($value['AlbumPhoto'] as $keyPhoto => $valuePhoto) {
                        if ($valuePhoto['rank'] > $count)
                            $count = $valuePhoto['rank'];
                    }
                }
            }
        }
        $count = $count + 1;
        $pathUpload = "";
        $date = new DateTime();
        if (!empty($data)) {
            $id = $data['UserInfo']['user_id'];
            try {
                $new_image_name = $date->getTimestamp() . ".png";
                $namePath = 'img' . DS . $id . DS . "album";
                $realPath = WWW_ROOT . $namePath;
                $folder = new Folder();
                $folder->create($realPath);
                if (!move_uploaded_file($_FILES["userfile"]["tmp_name"], $realPath . "/" . $new_image_name)) {
                    return $this->response->body(json_encode(array("data" => 0)));
                }
                $namePath = 'img' . "/" . $id . "/" . "album";
                $pathUpload = $namePath . "/" . $new_image_name;
                $this->UserInfo->create();
                if ($count == 1) {
                    $data['UserInfo']['avatar'] = $pathUpload;
                    $this->UserInfo->save($data);
                }
                $album_detail = array(
                    "album_id" => $idAlbum,
                    "photo" => $pathUpload,
                    "rank" => $count
                );
                $this->AlbumPhoto->create();
                $this->AlbumPhoto->save($album_detail);
            } catch (Exception $e) {
                return $this->response->body(json_encode(array("data" => 0, "errors" => $e->getMessage())));
            }
        } else {
            return $this->response->body(json_encode(array("data" => 0, "errors" => __("can not find user"))));
        }
        return $this->response->body(json_encode(array("data" => 1, "errors" => "", "avatar" => Router::url('/', true) . $pathUpload)));
    }

    // get avatar chat 
    function getImageChatUser() {
        $this->autoRender = false;
        $this->response->type("json");
//        $this->request->data["user_api_1"] = "1432584";
//        $this->request->data["user_api_2"] = "1646321";
        $user_api_id_1 = @$this->request->data["user_api_1"];
        $user_api_id_2 = @$this->request->data["user_api_2"];
        $this->UserInfo->contain("User");
        $listUser = array(-1);
        $listUser[$user_api_id_1] = $user_api_id_1;
        $listUser[$user_api_id_2] = $user_api_id_2;
        $order = "FIELD(User.user_api_cd," . implode(", ", $listUser) . ")";
        $user = $this->UserInfo->find('all', array(
            'conditions' => array(
                "User.user_api_cd" => $listUser
            ),
            'recursive' => -1,
            "order" => $order
        ));
        if (count($user) < 2)
            return $this->response->body(json_encode(array()));
        $this->response->body(json_encode(array(
            "img_1" => isset($user[0]['UserInfo']['avatar']) ? Router::url('/', true) . $user[0]['UserInfo']['avatar'] : "",
            "img_2" => isset($user[1]['UserInfo']['avatar']) ? Router::url('/', true) . $user[1]['UserInfo']['avatar'] : "",
            "gender_1" => isset($user[0]['UserInfo']['gender']) ? intval($user[0]['UserInfo']['gender']) : "",
            "gender_2" => isset($user[1]['UserInfo']['gender']) ? intval($user[1]['UserInfo']['gender']) : "",
            "name_1" => isset($user[0]['UserInfo']['name']) ? $user[0]['UserInfo']['name'] : "",
            "name_2" => isset($user[1]['UserInfo']['name']) ? $user[1]['UserInfo']['name'] : "",
            "user_id_other" => isset($user[1]['UserInfo']['user_id']) ? $user[1]['UserInfo']['user_id'] : "",
        )));
    }

}
