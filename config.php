<?php

@session_start();
define('APP_PATH', dirname(__FILE__));
define('APP_VERSION', '1.0.0');

$configRoute = substr(trim($_SERVER['SERVER_NAME']),0, 3);
global $env;

// localhost or dev. we might need more.
if($configRoute == "dev" || $configRoute == "loc"){
        $env = "DEV";
        // database configuration parameters 
        define('DB_HOST', '127.0.0.1');
        define('DB_USER', 'mbabli');
        define('DB_PASS', 'malls4you');
        define('DB_NAME', 'emall_master');

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
}else if($configRoute == "qa"){
        $env = "QA";

        // database configuration parameters 
        define('DB_HOST', '');
        define('DB_USER', '');
        define('DB_PASS', '');
        define('DB_NAME', '');

        error_reporting(E_ERROR);
        ini_set('display_errors', 1);
}else{ // prod
        $env = "PROD";

        // database configuration parameters 
        define('DB_HOST', '');
        define('DB_USER', '');
        define('DB_PASS', '');
        define('DB_NAME', '');

        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
}

class AutoLoader
{

    private static function FindFile($directory, $fileName)
    {
        $Directory = new RecursiveDirectoryIterator($directory);
        foreach (new RecursiveIteratorIterator($Directory) as $file) {
            if (strtolower(basename($file)) == strtolower($fileName) . '.class.php') {
                return $file;
            }
        }
        return false;
    }

    public static function AutoLoad($className)
    {
        $directories = array(APP_PATH . DIRECTORY_SEPARATOR . "inc",
            APP_PATH . DIRECTORY_SEPARATOR . "lib");

        if (strstr($className, '\\')) {
            // get the last portion of the namespace - if any! 
            preg_match('/[a-zA-Z_-]+$/', $className, $matches);
            $className = $matches[0];
        }

        foreach ($directories as $dir) {
            $fileFound = self::FindFile($dir, $className);
            if ($fileFound) {
                require_once $fileFound;
                return true;
            }
        }

        throw new Exception("Unable to find $className");
    }

}

spl_autoload_register('AutoLoader::AutoLoad');


// global flags/user messages
$userMessage    = "";
$errorMessage   = "";
$isError        = false;
$isPostback     = false;
$isAjaxPostBack = false;


// make sure you update this if you change the post determination mechanism
if(isset($_POST['submit'])){
    $isPostback = true;
}

if(isset($_REQUEST['ajax'])){
    $isAjaxPostBack = true;
}