<?php
App::uses('AppModel', 'Model');
/**
 * ReportPost Model
 *
 * @property User $User
 * @property ForumPost $ForumPost
 */
class ReportPost extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

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
		'ForumPost' => array(
			'className' => 'ForumPost',
			'foreignKey' => 'forum_post_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
