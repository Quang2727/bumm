<?php

App::uses('AppModel', 'Model');

/**
 * UserNotification Model
 *
 * @property User $User
 */
class UserNotification extends AppModel {
    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'UserNoti' => array(
            'className' => 'User',
            'foreignKey' => 'user_notification_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

    function saveNoti($user_id, $user_notification_id, $type_notification) {
        $dataSave = array(
            "user_id" => $user_id,
            "user_notification_id" => $user_notification_id,
            "type_notification" => $type_notification,
            "read_flg" => READ_FLG_OFF,
        );
        return $this->save($dataSave);
    }

    function updateFlag($id) {
        $data = $this->findById($id);
        if (!$data)
            return false;
        $data["read_flg"] = READ_FLG_ON;
        $this->save($data);
    }

    public function push_notification($options = null) {
        $passphrase = 'thai';
        $ctx = stream_context_create();
        $path = WWW_ROOT . "ios" . DS . "ck.pem";
        stream_context_set_option($ctx, 'ssl', 'local_cert', $path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        $options['message'] = "cc clgt";
        $options['loc-key'] = "open";
        $options['data'] = "asd asd asd";
        $options['badge'] = "1";
        $options['deviceToken'] = "6da62ae66ded52cd620e9d1852d25ecaa651fb4654c00d6f9c3f0baa4b7a91bd";
        //   $options['sound'] = "";
        $fp = @stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        $body['aps'] = array(
            'alert' => array(
                'body' => $options['message'],
                'action-loc-key' => $options['loc-key'] // 
            ),
            'data' => $options['data'], //
            'badge' => $options['badge'],
            'sound' => 'oven.caf',
        );

        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $options['deviceToken']) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);
    }

}
