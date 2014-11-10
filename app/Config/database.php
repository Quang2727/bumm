<?php
class DATABASE_CONFIG {
    public $default;

    public $development = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => '127.0.0.1',
        'login' => 'root',
        //'password' => 'password',
        'database' => 'bumm_test',
        'encoding' => 'utf8'
    );

    public $test = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'root',
        //'password' => 'password',
        'database' => 'badoo_db',
        'encoding' => 'utf8'
    );

    public $production = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => '127.0.0.1',
        'login' => 'root',
        //'password' => 'password',
        'database' => 'database',
        'encoding' => 'utf8'
    );

/*
    public $mysql = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'root',
        'password' => '',
        'database' => 'cakephp',
        'encoding' => 'utf8'
    );

    public $postgres = array(
        'datasource' => 'Database/Postgres',
        'persistent' => false,
        'host' => 'localhost',
        'port' => 5432,
        'login' => 'postgres',
        'password' => 'postgres',
        'database' => 'cakephp',
        'schema' => 'public',
        'encoding' => 'utf8'
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