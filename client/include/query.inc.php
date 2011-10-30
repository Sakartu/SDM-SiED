<?php
    require_once(dirname(__FILE__).'/../lib/sdm_util.php');
    
    require_once(dirname(__FILE__).'/../include/query_process.inc.php');
?>

<h1>Select Client</h1>

<p>Select the client identity that should be used for the query. In a real life scenario, each client only knows the key information that corresponds to his/her own identity. The consultant possesses all the keys.</p>

<?php
// Check if the current client_id in the session is still valid, otherwise clear it.
if (isset($_SESSION['client_id'])) {
    $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE id = :id");
    $qry->execute(array(':id' => $_SESSION['client_id']));

    if (!$qry->fetch()) {
    	// No results, clear the client_id!
    	unset($_SESSION['client_id']);
    }
}

// Check if we need to initialize the client_id to a default value
if (!isset($_SESSION['client_id'])) {
    $qry = SqliteDb::getDb()->query("SELECT * FROM clients ORDER BY id");

    // Try to initialize the client_id to the first client
    if ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['client_id'] = $row['id'];
    }
}

?>

<div class="wideForm">
	<form method="post" id="clientSelectForm">
	    <label for="client">Current client: </label>
	    <select name="client" onchange="document.getElementById('clientSelectForm').submit()">
	        <?php
	           $qry = SqliteDb::getDb()->query("SELECT * FROM clients ORDER BY id");

                while ($result = $qry->fetch(PDO::FETCH_ASSOC))
                {
                	if (isset($_SESSION['client_id']) && $_SESSION['client_id'] == $result['id']) {
                	   $selected = ' selected="selected"';
                	} else {
                	   $selected = '';
                	}

                	echo '<option value="'.$result['id'].'"'.$selected.'>'.$result['username'].'</option>'."\n";
                }
	        ?>
	    </select>
	    <div class="fm-submit">
	        <input name="submitBtn" type="submit" value="Select" />
	    </div>
	</form>
</div>

<br/>
<?php echo getNotifications(); ?>

<h1>Insert/Replace Tree</h1>
<div>
	<form method="post">
	    <script language="javascript">a='<\?xml version="1.0" encoding="UTF-8"\?>\n'+
                                        '<bookstore>\n'+
                                        '<book>\n'+
                                          '<title lang="eng">Harry Potter</title>\n'+
                                          '<price>29.99</price>\n'+
                                        '</book>\n'+
                                        '<book>\n'+
                                          '<title lang="eng">Learning XML</title>\n'+
                                          '<price>39.95</price>\n'+
                                        '</book>\n'+
                                        '</bookstore>'; 
        </script>
	    <span class="minimizedText" onClick="$('#xmlinput').val(a)">test-document #1</span>
		<textarea id="xmlinput" name="xml" cols="67" rows="10">&lt;?xml version="1.0" encoding="UTF-8"?&gt;</textarea>
		<div class="fm-submit">
		  <input type="submit" name="submit" value="Submit" />
		</div>
	</form>
</div>
<br/>


<h1>Query</h1>

<p>Enter an XPath query below.</p>

<div class="wideForm">
	<form method="post" id="queryForm">
        <label for="query" class="lessWide">Query: </label>
	    <input type="text" name="query" class="wideText" />
        <div class="fm-submit">
            <input name="submit" type="submit" value="Go!" />
        </div>
	</form>
</div>

<br/>
<br/>