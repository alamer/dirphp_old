<?php

class Config {

    const USERNAME = 'alamer';
    const PASSWORD = '31337';
    const STATUS = 'OK';

    /**
     * The constructor.
     * 
     */
    public function __construct() {
        
    }

    public function getUsername() {
        return self::USERNAME;
    }

    public function getPassword() {
        return self::PASSWORD;
    }

    public function getStatus() {
        return self::STATUS;
    }

    public function getBaseDir() {
        return $base_dir = getcwd();
    }

}
