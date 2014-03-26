<?php
require_once './config.php';

try {
    $dbObj = new DB_Mysql(DB_HOST,DB_USER,DB_PASS,DB_NAME);
    echo "Stage 1<pre>";
    print_r($dbObj->findColumns('category_fk'));
} catch ( Exception $ex )  {
    die( $ex->getMessage() );
}