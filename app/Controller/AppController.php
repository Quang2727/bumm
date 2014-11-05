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
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array('Session', 'Cookie');

    /**
     * Constructor
     *
     * @param mixed $request
     * @param mixed $response
     */
    function convertPhone($phone) {
        $phone = trim($phone);
        if (strpos($phone, '+') === false) {
            $phone = "+" . $phone;
        }
        return $phone;
    }

    public function __construct($request = null, $response = null) {
        if (Configure::read('env') == 'development' && !$request->is('ajax')) {
            //  $this->components[] = 'DebugKit.Toolbar';
        }
        parent::__construct($request, $response);
    }

    function ApiNG() {
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        return $this->response->body(json_encode(array(
                    "data" => array(),
                    "errors" => "Can not request data",
        )));
    }

    public function beforeFilter() {
//        $varTest = $this->request->data;
//        $varTest['full_url'] = $_SERVER['REQUEST_URI'];
//        $logtest = print_r($varTest, true);
//        $logtest = str_replace(array("\n","\t"), " ", $logtest);
//        $logtest = preg_replace('/\s+/', ' ',$logtest);
//        CakeLog::write('debug', $logtest);
    }

    public function afterFilter() {
        
    }

    public function beforeRender() {
        
    }

    function displayPostTime($datetime, $language = null) {
        if (!empty($language))
            $LG = Configure::read($language);
        else {
            $LG = Configure::read("en");
        }
        if (empty($datetime))
            return "";
        $unix = strtotime($datetime);
        if (!$unix)
            return $LG[0];  //ko co  //0
        $diff = time() - $unix;
        if ($diff > 0) {
            //second diff
            if ($diff < 86400) {
                if ($diff < 60)
                    return $LG[2];  // vua moi   //2
                if ($diff < 120)
                    return $LG[3]; // duoi 1 phut //3
                if ($diff < 3600)
                    return sprintf($LG[4], floor($diff / 60)); // tren 1 phut  //4
                return sprintf($LG[5], floor($diff / 3600));  // tren 1 tieng  //5
            }
            // day diff
            $diff = floor($diff / 86400);

            if ($diff < 7)
                return sprintf($LG[6], $diff); //duoi 1 tuan   //6 
//week diff
            $wdiff = floor($diff / 7);
            if ($wdiff < 5)
                return sprintf($LG[8], $wdiff); // cach day %d tuan //8

            if ($diff < 30)
                return $LG[7]; // duoi  1 thang  //7
//month diff
            $diff = floor($diff / 30);
            if ($diff < 12)
                return sprintf($LG[9], $diff);  // duoi 1 nam  //9

            if ($diff == 12) {
                return $LG[10];  // 1 nam //10
            }
            return $LG[11]; // tren 1 nam   //11
        } else if ($diff == 0) {
            return $LG[2]; // hien tai  //1
        } else {
            return date(DATETIME_FORMAT, $unix);
        }
    }

    function getShortText($str, $max_cut = 160) {
        $str = $this->supertrim((($str)));
        $length = mb_strlen($str, 'utf-8');
        if ($length > $max_cut) {
            return mb_substr($str, 0, $max_cut, 'utf-8') . '>...';
        }
        return $str;
    }

    function supertrim($str) {
        return trim(preg_replace('/\s+/', ' ', $str));
    }

}
