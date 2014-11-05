<?php

App::uses('AppModel', 'Model');

class ForumPost extends AppModel {

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
    public $hasMany = array(
        'LikePost' => array(
            'className' => 'LikePost',
            'foreignKey' => 'forum_post_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'CommentPost' => array(
            'className' => 'CommentPost',
            'foreignKey' => 'forum_post_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
    );

}
