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

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array('Session', 'Auth', 'Cookie');
    public $helpers = array('Text', 'Time', 'Number');
    public $uses = array('User');

    public $loginUser = NULL;
    public $isAdmin = FALSE;

    public $paginate = array(
        'conditions' => array(),
        'limit' => 20
    );

    public $layout = 'master';


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

        if ($this->request->isAjax()) {
            $this->autoLayout = FALSE;
            $this->autoRender = FALSE;
            $this->response->type('json');
        }

        $this->initCookie();

        $this->initAuth();

        $this->initVariables();
    }

    public function beforeRender() {
        parent::beforeRender();

        if ($this->Session->check('Message.flash')) {
            $flash = $this->Session->read('Message.flash');

            if ($flash['element'] == 'default') {
                $flash['element'] = 'Flash/default';
                $this->Session->write('Message.flash', $flash);
            }
        }

        if ($this->name == 'CakeError') {
            CakeLog::write('login', '[' . $this->request->clientIp() . '] entered an error page [' . $this->request->here() . '].');
            $this->set('title_for_layout', __('Page Not Found'));
        }
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

    public function initCookie() {
        $this->Cookie->name = 'BUMM';
        $this->Cookie->time = '2 weeks';
        // $this->Cookie->path = '/';
        // $this->Cookie->domain = '';
        // $this->Cookie->key = Configure::read('Security.salt');
        $this->Cookie->secure = FALSE;
        $this->Cookie->httpOnly = TRUE;
        $this->Cookie->type('aes');
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

    public function initAuth() {
        //default login page
        $this->Auth->loginAction = '/login';

        //default login fields
        $this->Auth->authenticate = array(
            'Form' => array(
                'fields' => array(
                    'username' => 'phone',
                    'password' => 'password'
                ),
                'scope' => array(
                    'delete_flg' => FLAG_OFF,
                    'leave_flg' => FLAG_OFF
                ),
                'recursive' => -1,
                'contain' => NULL,
                'userModel' => 'User'
            )
        );

        //try to login from cookie
        $this->rememberMe();
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

    public function initVariables() {
        if ($this->Auth->loggedIn()) {
            $this->loginUser = $this->Auth->user();

            $this->set('User', $this->loginUser);
        }

        $this->set('title_for_layout', 'Bumm');
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

    protected function getLoginUser() {
        $user = $this->Auth->user();

        if ($user)
            return $user;

        return null;
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

    protected function manualLogin($user_data = NULL, $via = 'normal login') {
        $user = $user_data;

        if (isset($user_data['User'])) {
            $user = $user_data['User'];
            unset($user_data['User']);
            $user = array_merge($user, $user_data);
        }

        //track last login
        if (isset($user['id'])) {
            $this->User->id = $user['id'];
        }

        //check delete flag
        if((isset($user['delete_flg']) && $user['delete_flg'] === FLAG_ON)
            || (isset($user['leave_flg']) && $user['leave_flg'] === FLAG_ON)){
            return FALSE;
        }

        // Log login
        CakeLog::write('login', 'User ' . $user['id'] . ' has logged in via ' . $via . ' [IP:' . $this->request->clientIp() . '].');

        //do login
        return $this->Auth->login($user);
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

    protected function rememberMe() {
        if($this->Auth->loggedIn()) return;

        //check when it has cookie and user is not logged in then load saved session
        if ($this->Cookie->check(USER_COOKIE_NAME)) {
            $cookieData = $this->Cookie->read(USER_COOKIE_NAME);

            $result = $this->checkUserCookie($cookieData);

            if ($result) {
                $user_from_cookie = $this->getLoginUser();
                $this->Session->setFlash(__('Welcome back, %s!', $user_from_cookie['username']), 'Flash/info');

                //renew cookies
                $this->Cookie->write(USER_COOKIE_NAME, $cookieData, TRUE, USER_COOKIE_TIMEOUT * 60);

                //redirect after login
                return $this->redirect(Router::url(NULL, TRUE));
            } else {
                //remove invalid cookie
                $this->Cookie->delete(USER_COOKIE_NAME);
            }
        }
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

    protected function checkUserCookie($cookie_data) {
        list($id, $hash) = explode('-', $cookie_data);

        if (!is_numeric($id))
            return FALSE;

        $this->User->id = $id;
        $this->User->recursive = -1;
        $user = $this->User->read();

        $user_hash = $this->generateUserCookie($user);

        if ($cookie_data == $user_hash) {
            return $this->manualLogin($user, 'cookie');
        } else {
            return FALSE;
        }
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

    protected function generateUserCookie($user_data) {
        $user = $user_data;

        if (isset($user_data['User'])) {
            $user = $user_data['User'];
        }

        $unique_str = $user['id'] . '-' . $user['username'] . '-' . $user['mail_address'] . '-' . $user['password'];

        $unique_hash = $this->Auth->password($unique_str);

        return $user['id'] . '-' . $unique_hash;
    }

}
