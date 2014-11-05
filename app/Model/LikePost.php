<?php

App::uses('AppModel', 'Model');

class LikePost extends AppModel {

    public $actsAs = array('Containable');
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
  

}
