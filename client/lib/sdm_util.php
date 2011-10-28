<?php
    /* CONFIGURATION */

    // Default XML-RPC server
    $xmlrpc_server_default = "http://localhost:8000/";

    // SQLite Database file location
    $sqlite_db_path = dirname(__FILE__).'/../db/sdm_db.sqlite';


    
    
    /* SESSION MANAGEMENT */
    # start session
    session_start();

    # set the rpc server location
    if (!isset($_SESSION['xmlrpc_server']))
    {
        $_SESSION['xmlrpc_server'] = $xmlrpc_server_default;
    }
    
    

    /* DATABASE SETUP */
    try {
        $db = new PDO('sqlite:'.$sqlite_db_path);
        echo('sqlite:'.$sqlite_db_path."\n<br/>");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $qry = $db->prepare("CREATE TABLE IF NOT EXISTS clients (id INTEGER, username TEXT, encryption_key TEXT, hash_key TEXT, rsa_keys TEXT, PRIMARY KEY(id))");
        
        $qry->execute();
    }
    catch(PDOException $e) {
      die($e->getMessage());
    }
    
    
    
    
    /* DATABASE FUNCTIONS */
    /**
     * @return True if the column value is unique in that table, False otherwise. 
     */
    function uniqueCheck($table, $column, $value)
    {
    	global $db;
        $table_name = addslashes(fixEncoding($table));
        $column_name = addslashes(fixEncoding($column));
    	$qry = $db->prepare("SELECT COUNT(*) FROM `".$table_name."` WHERE `".$column_name."` = :value");
        $qry->execute(array(':value' => $value));

        // Return true if first element of the first (and only) row of the result set is equal to ZERO
        return (current($qry->fetch()) == 0);
    }
    

    /* HELPER FUNCTIONS */
	function fixEncoding($x){
	  if(mb_detect_encoding($x)=='UTF-8'){
	    return $x;
	  }else{
	    return utf8_encode($x);
	  }
	}     
    
    function addNotification($msg)
    {
    	$_SESSION['notifications'][] = $msg;
    }
    
    function getNotifications()
    {
    	$result = '';
    	if (isset($_SESSION['notifications']) && sizeof($_SESSION['notifications']) > 0)
    	{
    		$result = '<div class="notifications"><ul>'."\n";
	    	foreach ($_SESSION['notifications'] as $msg)
	    	{
	    		$result .= '<li>'.$msg.'</li>'."\n";
	    	}
	    	$result .= '</div></ul>';
	    	
	    	// Clear the queued messages
	    	unset($_SESSION['notifications']);
    	}
    	
    	return $result;
    }

    function strToHex($string)
    {
        $hex='';
        for ($i=0; $i < strlen($string); $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

?>