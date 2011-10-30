<?php


class SqliteDb 
{
    private static $db;
    
    public static function getDb()
    {
        global $sqlite_db_path;
        
        if (!isset(self::$db))
        {
            /* DATABASE SETUP */
            try {
                self::$db = new PDO('sqlite:'.$sqlite_db_path);

                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $qry = self::$db->prepare("CREATE TABLE IF NOT EXISTS clients (id INTEGER, username TEXT, encryption_key TEXT, hash_key TEXT, rsa_keys TEXT, PRIMARY KEY(id))");
                
                $qry->execute();
            }
            catch(PDOException $e) {
              die($e->getMessage());
            }
        }
        
        return self::$db;
    }
    
}

?>
