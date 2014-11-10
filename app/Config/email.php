<?php

class EmailConfig {
    public $default;

    public $development = array(
        'transport' => 'Smtp',
        'from' => array('info@lev-vn.dev' => 'DEV@VN'),
        'host' => '192.168.1.220',
        'port' => 25,
        'timeout' => 30,
        'username' => 'info@lev-vn.dev',
        'password' => 'lev-vn',
        'client' => null,
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
    );

    public $test = array(
        'transport' => 'Smtp',
        'from' => array('info@lev-vn.dev' => 'TEST@VN'),
        'host' => '192.168.1.220',
        'port' => 25,
        'timeout' => 30,
        'username' => 'info@lev-vn.dev',
        'password' => 'lev-vn',
        'client' => null,
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
    );

    public $production = array(
        'transport' => 'Smtp',
        'from' => array('site@localhost' => 'Bumm Customer Mailer'),
        'host' => 'localhost',
        'port' => 25,
        'timeout' => 30,
        'username' => 'user',
        'password' => 'secret',
        'client' => null,
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
    );

/*
    public $default = array(
        'transport' => 'Mail',
        'from' => 'you@localhost',
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );

    public $smtp = array(
        'transport' => 'Smtp',
        'from' => array('site@localhost' => 'My Site'),
        'host' => 'localhost',
        'port' => 25,
        'timeout' => 30,
        'username' => 'user',
        'password' => 'secret',
        'client' => null,
        'log' => false,
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );

    public $fast = array(
        'from' => 'you@localhost',
        'sender' => null,
        'to' => null,
        'cc' => null,
        'bcc' => null,
        'replyTo' => null,
        'readReceipt' => null,
        'returnPath' => null,
        'messageId' => true,
        'subject' => null,
        'message' => null,
        'headers' => null,
        'viewRender' => null,
        'template' => false,
        'layout' => false,
        'viewVars' => null,
        'attachments' => null,
        'emailFormat' => null,
        'transport' => 'Smtp',
        'host' => 'localhost',
        'port' => 25,
        'timeout' => 30,
        'username' => 'user',
        'password' => 'secret',
        'client' => null,
        'log' => true,
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );
*/


    /**
     *--------------------------------------------------------------------------
     *
     *--------------------------------------------------------------------------
     *
     * @method
     * @author  Shin <tanmn@leverages.jp>
     * @since
     * @param
     * @return  void
     */

    public function __construct(){
        // default environment settings for production
        $this->default = $this->production;

        // switch configuration for testing
        if(Configure::read('env') == 'development'){
            $this->default = $this->development;
        }else if(Configure::read('env') == 'test'){
            $this->default = $this->test;
        }
    }
}