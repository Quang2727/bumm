<?php

App::uses('AppModel', 'Model');

/**
 * UserAlbum Model
 *
 * @property User $User
 */
class UserAlbum extends AppModel {

    public $actsAs = array('Containable');
    protected $_last_owner = NULL;

    const DEFAULT_NAME = '__default__';

    public $virtualFields = array(
        'original_album_name' => 'album_name'
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
        )
    );
    public $hasMany = array(
        'AlbumPhoto' => array(
            'className' => 'AlbumPhoto',
            'foreignKey' => 'album_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => 'AlbumPhoto.rank ASC',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
    );

}
