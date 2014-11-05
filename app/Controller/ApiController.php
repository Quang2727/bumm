<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('AppController', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class ApiController extends AppController {

    //remove
    //public $components = array('Auth');
    public $components = array('Paginator');
    public $paginate = array('limit' => 10);

    const FIELD_FB = 'id,name,birthday,gender';

    public $uses = array('UserInfo', 'User', "UserNotification", 'UserLikedList', 'UserFriendList', 'UserBlockedList');
    public $conditions = array();
    public $notListData = array();
    public $result = array();
    public $order = array();

    public function findSearch() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        if (empty($this->request->data["user_id"])) {
            return $this->ApiNG();
        }
        $user_id = $this->request->data["user_id"];
        $data = $this->UserInfo->find("first", array(
            "conditions" => array(
                "UserInfo.user_id" => $user_id,
            ),
            "recursive" => -1,
            "fields" => array("data_search", "gender")
        ));
        $search = array();
        $count = 0;
        if (!empty($data['UserInfo']['data_search'])) {
            $search = (array) json_decode($data['UserInfo']['data_search']);
            $count = count($search);
        }

        $this->response->body(json_encode(array("data" => $search, "count" => $count)));
    }

    public function saveSearch() {
        $dataRequest = $this->request->data;
        $userInfoId = $this->UserInfo->findByUserId($dataRequest['user_id']);
        if (empty($this->request->data["user_id"])) {
            return $this->ApiNG();
        }
        $userInfoId['UserInfo']['data_search'] = json_encode(array(
            "gender" => $dataRequest['gender'],
            "age_start" => $dataRequest['age_start'],
            "age_end" => $dataRequest['age_end']
        ));
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        if ($this->UserInfo->save($userInfoId['UserInfo'], false)) {
            return $this->response->body(json_encode(array("data" => 1)));
        } else {
            return $this->response->body(json_encode(array("data" => 0)));
        }
    }

    function sendMail() {
//        $this->request->data["email"] = "test@gmail.com";
//        $this->request->data["name"] = "name ";
//        $this->request->data["title"] = "title ";
//        $this->request->data["content"] = "content ";
        App::uses('CakeEmail', 'Network/Email');
        $data = $this->request->data;
        $this->log($data, "error");
        $Email = new CakeEmail('smtp');
        $Email->viewVars(array('data' => $data));
        $result = 1;
        try {
            $Email->template('sendMail')
                    ->emailFormat('html')
                    ->to(EMAIL_TO)
                    ->from(array($data['email'] => $data['name']))
                    ->subject($data['title'])
                    ->send();
        } catch (Exception $e) {
            $result = 0;
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $result)));
    }

 

}
