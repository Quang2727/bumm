
<?php

App::uses('AppController', 'Controller');

/**
 * Application API Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

class AppApiController extends AppController {

    public $layout = 'ajax';


    /**
     *--------------------------------------------------------------------------
     *
     *--------------------------------------------------------------------------
     *
     * @method
     * @author  Shin <tanmn@leverages.jp>
     * @since
     * @param
     * @return
     */

    public function __construct($request = null, $response = null) {
        if ($this->name === null) {
            $this->name = preg_replace('/apis?controller$/i', '', get_class($this));
        }

        parent::__construct($request, $response);
    }


    /**
     *--------------------------------------------------------------------------
     *
     *--------------------------------------------------------------------------
     *
     * @method
     * @author  Shin <tanmn@leverages.jp>
     * @since
     * @param
     * @return
     */

    public function beforeFilter($options = array()) {
        parent::beforeFilter($options);

        $this->autoLayout = FALSE;
        $this->autoRender = FALSE;
        $this->response->type('json');
    }
}
