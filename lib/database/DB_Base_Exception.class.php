<?php

class DB_Base_Exception extends Exception{
    //put your code here
    public function __construct($message, $code=null, $previous=null) {
        parent::__construct($message, $code, $previous);
    }
}
