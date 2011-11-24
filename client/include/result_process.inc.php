<?php

require_once(dirname(__FILE__)."/../lib/sdm_symmetric_crypt.php");
require_once(dirname(__FILE__)."/../lib/sdm_xmlrpc_stubs.php");

$redirect = false;

if(isset($_GET['action']) && isset($_SESSION['result']))
{
    $result = $_SESSION['result'];
    if($_GET['action'] == "update")
    {
        $type = trim($_GET['type']);
        $pre = trim($_GET['pre']);
        $txt = trim($_GET['txt']); // new value
        $gcid = $_GET['cid'];
        
        if (isset($type) && isset($pre) && isset($txt) && !empty($txt) && !empty($type) && isset($gcid))
        {
            if ($pre >= 0)
            {
                $cid = (int)$gcid; //$result['clientId'];

                // Check if the clientId still exists, because we will need its keys
                $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE id = :client_id");
                $qry->execute(array(':client_id' => $cid));
                
                if($client_info = $qry->fetch())
                {
                    
                    $enctag = '';
                    $encval = '';
                    if ($type == "t")
                    {
                        // tag update
                        $enctag = SdmSymmetricCrypt::encryptForSearching(base64_decode($client_info['encryption_key']), 
                               base64_decode($client_info['hash_key']), $txt, $pre);
                    }
                    else if ($type == "a")
                    {
                        // attribute update
                        $txt = "@".$txt;
                        $enctag = SdmSymmetricCrypt::encryptForSearching(base64_decode($client_info['encryption_key']), 
                               base64_decode($client_info['hash_key']), $txt, $pre);
                    }
                    else
                    {
                        // value update
                        $encval = SdmSymmetricCrypt::encryptForSearching(base64_decode($client_info['encryption_key']), 
                               base64_decode($client_info['hash_key']), $txt, $pre);
                    }
                    
                    echo '<pre>Updating '.$pre.' to '."\n".$enctag." \n=>\n".$encval."\n".'cid='.$cid.' and '."\n".'treeId='.$result[$cid]['treeId'];
                    
                    //updateRow(client_id, $treeId, $pre, $tag, $value)
                    XmlRpcStubs::updateRow($cid, $result[$cid]['treeId'], $pre, $enctag, $encval);
                    
                    // If the update succeeded then we must update the rows for this PRE in the cached result values.
                    // The alternative would be to re-execute the query, but that is an expensive operation.

                    $newArrays = array();
                    foreach ($result[$cid]['rowArrays'] as $rowArrayIndex => $rowArray)
                    {
                        $newRowArray = array();
                        
                        foreach ($rowArray as $nodeRowString)
                        {
                            $nodeRowObj = unserialize($nodeRowString);
                            if ($nodeRowObj->pre == $pre)
                            {
                                if($type == "t" || $type == "a") 
                                {
                                    // tag update
                                    $nodeRowObj->tag = $txt;
                                    $nodeRowObj->enctag = $enctag;
                                    //echo 'Update of tag:'."\n";
                                    //var_dump($nodeRowObj);
                                }
                                {
                                    // value update
                                    $nodeRowObj->val = $txt;
                                    $nodeRowObj->encval = $encval;
                                    //echo 'Update of value:'."\n";
                                    //var_dump($nodeRowObj);
                                }
                            }

                            $newRowArray[] = serialize($nodeRowObj);
                        }
                        
                        $newArrays[] = $newRowArray;
                    }
                    $_SESSION['result'][$cid]['rowArrays'] = $newArrays;
                }
                
            }
        }
    }
    $redirect = true;
}

if ($redirect)
{
    header("Location:index.php?page=result");
    exit;
}

?>
