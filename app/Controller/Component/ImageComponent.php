<?php

App::uses('Component', 'Controller');
App::import('Vendor', 'ImageWorkshop', array('file' => 'PHPImageWorkshop/ImageWorkshop.php'));

class ImageComponent extends Component {
    public function __call($name, $arguments) {
        return call_user_func_array(array(
            'ImageWorkshop',
            $name
        ), $arguments);
    }

    public function create($width = 100, $height = 100, $backgroundColor = null){
        return ImageWorkshop::initVirginLayer($width, $height, $backgroundColor);
    }

    public function createFromPath($path){
        return ImageWorkshop::initFromPath($path);
    }
}