<?php


/**
 * filename: DB_mysql.class.php 
 * author: mark babli
 * date: 03/25/2014
 * purpose: implementation class for DB_Base.class.php 
 *          a constructor which opens a connection to the 
 *          database using the implemented connectToDatabase() 
 *          function. 
 * This class detects the currently loaded extension for mysql access 
 *      * mysqli or 
 *      * mysql 
 *        giving priority to mysqli if found. 
 */

class DB_Mysql extends DB_Base{
    
    /**
     * set the mysql driver as mysql or mysqli (give preference to mysqli) 
     * if found
     * @todo: support for generic db drivers
     * 
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $db_name
     * @throws Exception (if mysql and mysqli extensions are not found)
     * 
     */
    public function __construct($db_host, $db_user, $db_pass, $db_name) {
        if(extension_loaded('mysqli')){
            $this->dbDriver = 'mysqli';
        }elseif(extension_loaded('mysql')){
            $this->dbDriver = 'mysql';
        }else{
            // no drivers
            throw new Exception('unable to find mysql or mysqli extensions');
        }
        parent::__construct($db_host, $db_user, $db_pass, $db_name);
    }
    
    /**
     * connects to mysql database using the driver found/set in the constructor
     * function.
     * @throws Exception if unable to connect
     */
    public function connectToDatabase() {
        if($this->dbDriver == 'mysqli'){
           $this->db = mysqli_connect(
                   $this->dbHost, $this->dbUser, $this->dbPass, 
                   $this->dbName, $this->dbPort);
           if(!$this->db){
               throw new Exception('unable to connect to mysql using mysqli'
                                    .' with error '. mysqli_error($this->db));
           }
        }else{
           $this->db = mysql_connect($this->dbHost,$this->dbUser,$this->dbPass);
           
           if(!mysql_select_db($this->dbName)){
               throw new Exception('invalid database ['.mysql_error().']');
           }
        }
        $this->dbConnected = true;
    }
    
    /**
     * disconnect from the mysql database using the proper loaded extension
     */
    public function disconnectFromDatabase() {
        if($this->dbConnected){
            if($this->dbDriver == 'mysqli'){
                mysqli_close($this->db);
            }else{
                mysql_close($this->db);
            }
        }
    }
    
    /**
     * discovers a list of tables for the specified database and loops through
     * them and calls function discoverTables() passing the table name to the 
     * function 
     * 
     * @return true on success or false on failure
     * @throws Exception if the user does not have previliges to read from the
     * information_schema
     * 
     * @todo use a gauranteed SQL permission set, like calling describe $table 
     * 
     */
    public function discoverDatabase() {
        
        $query = "SELECT DISTINCT 
                      `TABLE_NAME` 
                  FROM 
                       information_schema.columns 
                  WHERE 
                        table_schema='{$this->dbName}';";
                        
        $resultTables = $this->executeQuery($query);
        if(!$resultTables){
            throw new Exception('unable to get database tables');
        }
        foreach($resultTables as $table){
            try{
                $tableStructure = $this->discoverTables($table->TABLE_NAME);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
            
            $this->dbStructure[$table->TABLE_NAME]=$tableStructure;
        }
        return true;
    }
    
    /**
     * 
     * @param type $table_name
     * @return array of standard objects (columns) with the following properties 
     *      column_name,column_position,column_type,is_nullable and 
     *      column_default
     * @throws Exception
     */
    public function discoverTables($table_name = null) {
        $query = "SELECT DISTINCT 
                    `COLUMN_NAME` column_name,
                    `ORDINAL_POSITION` column_position,
                    `COLUMN_TYPE` column_type,
                    `IS_NULLABLE` is_nullable,
                    `COLUMN_DEFAULT` column_default
                 FROM 
                    `INFORMATION_SCHEMA`.`COLUMNS` 
                 WHERE 
                    `TABLE_SCHEMA`='{$this->dbName}' 
                     AND 
                    `TABLE_NAME`='$table_name'
                 ORDER BY 2;";
        $result = $this->executeQuery($query);
        if(!$result){
            throw new Exception('unable to discover table structure');
        }
        return $result;
    }
    
    /**
     * 
     * @param string $query
     * @param objects/array (default objects)
     * @return array of objects/array of arrays $ret_type
     * @throws Exception
     */
    public function executeQuery($query,$ret_type="objects") {
        if(!$this->dbConnected){
            // how did this happen?
            throw new Exception('db connection not available!!!');
        }
        
        $result = null;
        $retRows = array();
        
        if($this->dbDriver == 'mysqli'){
            $result = mysqli_query($this->db, $query);
            if(!$result){
               throw new Exception(' query returned an error '. mysqli_error(
                       $this->db));
            }
            
            switch(strtolower($ret_type)){
                case 'objects':
                    while($row = mysqli_fetch_object($result)){
                        $retRows[] = $row;
                    }
                    break;
                default:
                    while($row = mysqli_fetch_assoc($result))
                    break;
            }
        }else{
            $result = mysql_query($query,$this->db);
            if(!$result){
               throw new Exception(' query returned an error '. mysql_error(
                       $this->db));
            }
            switch(strtolower($ret_type)){
                case 'objects':
                    while($row = mysql_fetch_object($result)){
                        $retRows[] = $row;
                    }
                    break;
                default:
                    while($row = mysql_fetch_assoc($result))
                    break;
            }
        }
        
        return $retRows;
    }
}
