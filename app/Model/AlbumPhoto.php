<?php

App::uses('AppModel', 'Model');

/**
 * AlbumPhoto Model
 *
 * @property Album $Album
 */
class AlbumPhoto extends AppModel {

    

    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
        'Album' => array(
            'className' => 'UserAlbum',
            'foreignKey' => 'album_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );


}
