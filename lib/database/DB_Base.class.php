<?php

/**
 * filename: DB_Base.class.php 
 * author: mark babli
 * date: 03/25/2014
 * purpose: general database *base class* contains default params & param 
 *          getters. a constructor which opens a connection to the 
 *          database *using the extended/implemented connectToDatabase()* 
 *          function, in addition to opening a connection, creates a database 
 *          array containing the tables and the columns as an array of tables 
 *          each table containing an array of objects (columns)
 *          also calls the *disconnectFromDatabase* function upon calling the 
 *          default destructor.
 */
abstract class DB_Base {
    
    protected $db;       // the database link resource
    protected $dbDriver; // name of the underlaying driver to connect to db
    
    protected $dbHost;   // hostname
    protected $dbUser;   // database username 
    protected $dbName;   // database name 
    protected $dbPass;   // database password
    
    protected $dbPort=3306;     // port. default 3306 (mysql)
    protected $dbType="mysql";  // default database type (future usage)
    
    protected $dbStructure;         // array of tables/array of column objects
    public    $dbConnected = false; // true = connected, false = not connected
    
    /**
     * returns the database hostname
     * @return string
     */
    public function getDbHost(){
        return $this->dbHost;
    }
    
    /**
     * returns the database username
     * @return string
     */
    public function getDbUser(){
         return $this->dbUser;
    }

    /**
     * returns the database name
     * @return string
     */
    public function getDbName(){
        return $this->dbName;
    }
    
    /**
     * returns the database password
     * @return string
     */
    public function getDbPass(){
        return $this->dbPass;
    }
    
    /**
     * returns the database port
     * @return string
     */
    public function getDbPort(){
        return $this->dbPort;
    }
    
    /**
     * returns the database type
     * @return string
     */
    public function getDbType(){
        return $this->dbType;
    }
    
    /**
     * returns the database structure
     * @return array
     */
    public function getDbStructure(){
        return $this->dbStructure;
    }
    
    /**
     * initiates a connection to the supplied parameters using the abstract 
     * function connectToDatabase and then build the database structure by 
     * calling the abstract function discoverDatavase
     * 
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $db_name
     * @param int $db_port
     * @param string $db_type
     * @throws DB_Base_Exception
     */
    public function __construct($db_host,$db_user,$db_pass,$db_name,
                                               $db_port=3306,$db_type="mysql") {
        if(empty($db_host)){
            throw new DB_Base_Exception("empty/invalid host");
        }
        $this->dbHost = trim($db_host);
        if(empty($db_user)){
            throw new DB_Base_Exception("empty/invalid user");
        }
        $this->dbUser = trim($db_user);
        if(empty($db_pass)){
            throw new DB_Base_Exception("empty/invalid password");
        }
        $this->dbPass = trim($db_pass);
        if(empty($db_name)){
            throw new DB_Base_Exception("empty/invalid database");
        }
        $this->dbName = trim($db_name);
        
        $this->dbPort = (int)$db_port;
        $this->dbType = trim($db_type);
        
        try{
            // attempt a connection to the database
            $this->connectToDatabase();
            
            // attempt to get the database structure 
            $this->discoverDatabase();
            
        } catch (Exception $ex) {
            throw new DB_Base_Exception ("unable to connect to database with ".
                                        $ex->getMessage() . " on line ".
                                        $ex->getLine() . " in file ".
                                        $ex->getFile()
                                        );
        }
    }
    
    /**
     * call the abstract implementation function disconnectFromDatabase() 
     * returns true even on exception
     */
    public function __destruct() {
        try{
            $this->disconnectFromDatabase();
        } catch (Exception $ex) {
            // do nothing! 
        }
    }
    
    /**
     * Analysis function: accepts a column name and returns a list of tables 
     * where that column is currently in.
     * 
     * @param string $colName
     * @return false on not found or an array on found
     * @throws DB_Base_Exception
     */
    public function findColumns($colName){
        if(!is_array($this->dbStructure)){
            throw new DB_Base_Exception('db structure was not created properly');
        }
        
        // get the table names from the internally built structure
        $tables = array_keys($this->dbStructure);
        $foundTables=array();
        
        foreach($tables as $tableName){
            foreach($this->dbStructure[$tableName] as  $tableColumn){
                if(in_array($tableName, $foundTables)){
                    continue;
                }
                if(strcmp($tableColumn->column_name,$colName)==0){
                    if(!in_array($tableName, $foundTables)){
                        $foundTables[]=$tableName;
                    }
                }
            }
        }
        if(count($foundTables)> 0){
            return $foundTables;
        }
        return false;
    }
    
    /**
     * interface function connectToDatabase();
     */
    abstract public function connectToDatabase();
    
    /**
     * interface function disconnectFromDatabase();
     */
    abstract public function disconnectFromDatabase();
    
    /**
     * interface function discoverDatabase();
     * @return an array of tables and calls the function discoverTables on each
     * building the overall dbStructure representation
     */
    abstract public function discoverDatabase();
    
    /**
     * interface function discoverTable();
     * @return an array of objects (columns) belonging to the table 
     * specified (if nothing is specified, the function will return a list 
     * of all tables related to the internal $dbName variable
     */
    abstract public function discoverTables($table_name=null);
    
    /**
     * interface function executeQuery();
     * executes a query and returns the result-set as specified in 
     * the $ret_type (currently supports objects/array) only or an exception
     * on error
     */
    abstract public function executeQuery($query,$ret_type="objects");
    
    
    
}
