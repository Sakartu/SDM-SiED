<?php
    require_once(dirname(__FILE__).'/../lib/sdm_util.php');

    require_once(dirname(__FILE__).'/../include/config_process.inc.php');
    
    require_once(dirname(__FILE__).'/../lib/sdm_symmetric_crypt.php');
?>

<h1>Server Information</h1>
<br/>
<form method="post">
    <div>
        <label for="xmlrpc_server">Location: </label>
           <input type="text" name="xmlrpc_server" value="<?php echo $_SESSION['xmlrpc_server']; ?>" class="width50" />
    </div>

    <div class="fm-submit">
        <input name="submit" type="submit" value="Save" />
        <input name="reset" type="submit" value="Reset" />
    </div>
</form>

<br/><br/>
<?php echo getNotifications(); ?>


<h1>Client Identities</h1>
<?php
$qry = SqliteDb::getDb()->prepare("SELECT * FROM clients");
$qry->execute();

while ($result = $qry->fetch(PDO::FETCH_ASSOC)) {
    echo '<blockquote class="clientInfo">'."\n";
    echo '<div class="big darkGreen">Client #'.$result['id'].'</div>'."\n";
    echo '<div><span class="labelText">Username: </span><span class="centerText">&nbsp</span><span class="valueText">'.$result['username'].'</span></div>'."\n";
    echo '<div><span class="labelText">Encryption Key: </span><span class="centerText">base64</span><span class="valueText">'.$result['encryption_key'].'</span></div>'."\n";
    echo '<div><span class="labelText">&nbsp;</span><span class="centerText">utf-8</span><span class="valueText">'.fixEncoding(base64_decode($result['encryption_key'])).'</span></div>'."\n";
    echo '<div><span class="labelText">Hash Key: </span><span class="centerText">base64</span><span class="valueText">'.$result['hash_key'].'</span></div>'."\n";
    echo '<div><span class="labelText">&nbsp;</span><span class="centerText">utf-8</span><span class="valueText">'.fixEncoding(base64_decode($result['hash_key'])).'</span></div>'."\n";
    echo '<div><span class="labelText">RSA Keys: </span><span class="centerText">&nbsp;</span>';
    echo '<span class="minimizedText" id="blockShow'.$result['id'].'" onClick="'."$('#rsapem".$result['id']."').toggle('slow'); $('#blockShow".$result['id']."').hide(); $('#blockHide".$result['id']."').show();". '">show...</span>';
    echo '<span class="minimizedText" style="display: none" id="blockHide'.$result['id'].'" onClick="'."$('#rsapem".$result['id']."').toggle('slow'); $('#blockHide".$result['id']."').hide(); $('#blockShow".$result['id']."').show();". '">hide...</span>';    
    echo '</div>'."\n";
    echo '<div class="keyBlock" id="rsapem'.$result['id'].'">'.$result['rsa_keys'].'</div>';
    echo '<div>'."\n";
    echo '<span class="block small green"><a title="Delete user" href="?page=config&action=delclient&id='.$result['id'].'">delete</a> this client identity</span>'."\n";
    echo '</div></blockquote>'."\n";
}

?>

<form method="post">
	<blockquote class="clientInfo addBlock">
        <div class="big darkGreen">Add New Client</div>
        <div class="block small green">
            <ul>
                <li>The consultant manages these identities, and can <a href="?page=config&action=sync">sync</a> the public keys with the server.</li>
                <li>Username must be unique and at least 3 characters long.</li>
                <li>Keys must be unique, 128 bits, and entered in <a href="http://home2.paulschou.net/tools/xlate/">base64</a> encoding.</li>
                <li>Key fields that are left empty will be generated automatically.</li>
            </ul>
        </div>
        <div>
            <span class="labelText">Username: </span><span class="centerText">&nbsp</span>
            <input class="valueText" type="text" value="" name="username" />
        </div>
        <div>
            <span class="labelText">Encryption key: </span><span class="centerText">base64</span>
            <input class="valueText" type="text" value="" name="encryption_key" />
        </div>
        <div>
            <span class="labelText">Hash key:</span><span class="centerText">base64</span>
            <input class="valueText" type="text" value="" name="hash_key" />
        </div>
        <div>
            <span class="labelText">RSA keys:</span><span class="centerText">&nbsp;</span>
            <span class="valueTextLite">&lt;automatically generated&gt;</span>
        </div>
        <div class="alignCenter fm-submit">
            <input type="submit" value="Add" name="add" />
        </div>
    </blockquote>	   
</form>