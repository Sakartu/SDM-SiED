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
                                        '<moneylaundering>\n'+
                                        '<offshore>\n'+
                                          '<name country="caymans">Pyramid Investment Holding B.V.</name>\n'+
                                          '<account>345122359</account>\n'+
                                          '<amount>5,000,000</amount>\n'+
                                        '</offshore>\n'+
                                        '<offshore>\n'+
                                          '<name country="switzerland">Global Enrichment Group</name>\n'+
                                          '<account>3133757</account>\n'+
                                          '<amount>25,000</amount>\n'+
                                        '</offshore>\n'+
                                        '</moneylaundering>'; 
        </script>
	    <span class="minimizedText" onClick="$('#xmlinput').val(a)">test-document #1</span>
        <script language="javascript">              b=  '<\?xml version="1.0" encoding="ISO-8859-1"\?>\n' +
                    '<CATALOG>\n' +
                        '<CD>\n' +
                            '<TITLE>Empire Burlesque</TITLE>\n' +
                            '<ARTIST>Bob Dylan</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>Columbia</COMPANY>\n' +
                            '<PRICE>10.90</PRICE>\n' +
                            '<YEAR>1985</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Hide your heart</TITLE>\n' +
                            '<ARTIST>Bonnie Tyler</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>CBS Records</COMPANY>\n' +
                            '<PRICE>9.90</PRICE>\n' +
                            '<YEAR>1988</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Greatest Hits</TITLE>\n' +
                            '<ARTIST>Dolly Parton</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>RCA</COMPANY>\n' +
                            '<PRICE>9.90</PRICE>\n' +
                            '<YEAR>1982</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Still got the blues</TITLE>\n' +
                            '<ARTIST>Gary Moore</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Virgin records</COMPANY>\n' +
                            '<PRICE>10.20</PRICE>\n' +
                            '<YEAR>1990</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Eros</TITLE>\n' +
                            '<ARTIST>Eros Ramazzotti</ARTIST>\n' +
                            '<COUNTRY>EU</COUNTRY>\n' +
                            '<COMPANY>BMG</COMPANY>\n' +
                            '<PRICE>9.90</PRICE>\n' +
                            '<YEAR>1997</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>One night only</TITLE>\n' +
                            '<ARTIST>Bee Gees</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Polydor</COMPANY>\n' +
                            '<PRICE>10.90</PRICE>\n' +
                            '<YEAR>1998</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Sylvias Mother</TITLE>\n' +
                            '<ARTIST>Dr.Hook</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>CBS</COMPANY>\n' +
                            '<PRICE>8.10</PRICE>\n' +
                            '<YEAR>1973</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Maggie May</TITLE>\n' +
                            '<ARTIST>Rod Stewart</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Pickwick</COMPANY>\n' +
                            '<PRICE>8.50</PRICE>\n' +
                            '<YEAR>1990</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Romanza</TITLE>\n' +
                            '<ARTIST>Andrea Bocelli</ARTIST>\n' +
                            '<COUNTRY>EU</COUNTRY>\n' +
                            '<COMPANY>Polydor</COMPANY>\n' +
                            '<PRICE>10.80</PRICE>\n' +
                            '<YEAR>1996</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>When a man loves a woman</TITLE>\n' +
                            '<ARTIST>Percy Sledge</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>Atlantic</COMPANY>\n' +
                            '<PRICE>8.70</PRICE>\n' +
                            '<YEAR>1987</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Black angel</TITLE>\n' +
                            '<ARTIST>Savage Rose</ARTIST>\n' +
                            '<COUNTRY>EU</COUNTRY>\n' +
                            '<COMPANY>Mega</COMPANY>\n' +
                            '<PRICE>10.90</PRICE>\n' +
                            '<YEAR>1995</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>1999 Grammy Nominees</TITLE>\n' +
                            '<ARTIST>Many</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>Grammy</COMPANY>\n' +
                            '<PRICE>10.20</PRICE>\n' +
                            '<YEAR>1999</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>For the good times</TITLE>\n' +
                            '<ARTIST>Kenny Rogers</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Mucik Master</COMPANY>\n' +
                            '<PRICE>8.70</PRICE>\n' +
                            '<YEAR>1995</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Big Willie style</TITLE>\n' +
                            '<ARTIST>Will Smith</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>Columbia</COMPANY>\n' +
                            '<PRICE>9.90</PRICE>\n' +
                            '<YEAR>1997</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Tupelo Honey</TITLE>\n' +
                            '<ARTIST>Van Morrison</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Polydor</COMPANY>\n' +
                            '<PRICE>8.20</PRICE>\n' +
                            '<YEAR>1971</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Soulsville</TITLE>\n' +
                            '<ARTIST>Jorn Hoel</ARTIST>\n' +
                            '<COUNTRY>Norway</COUNTRY>\n' +
                            '<COMPANY>WEA</COMPANY>\n' +
                            '<PRICE>7.90</PRICE>\n' +
                            '<YEAR>1996</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>The very best of</TITLE>\n' +
                            '<ARTIST>Cat Stevens</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Island</COMPANY>\n' +
                            '<PRICE>8.90</PRICE>\n' +
                            '<YEAR>1990</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Stop</TITLE>\n' +
                            '<ARTIST>Sam Brown</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>A and M</COMPANY>\n' +
                            '<PRICE>8.90</PRICE>\n' +
                            '<YEAR>1988</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Bridge of Spies</TITLE>\n' +
                            '<ARTIST>TPau</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Siren</COMPANY>\n' +
                            '<PRICE>7.90</PRICE>\n' +
                            '<YEAR>1987</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Private Dancer</TITLE>\n' +
                            '<ARTIST>Tina Turner</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>Capitol</COMPANY>\n' +
                            '<PRICE>8.90</PRICE>\n' +
                            '<YEAR>1983</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Midt om natten</TITLE>\n' +
                            '<ARTIST>Kim Larsen</ARTIST>\n' +
                            '<COUNTRY>EU</COUNTRY>\n' +
                            '<COMPANY>Medley</COMPANY>\n' +
                            '<PRICE>7.80</PRICE>\n' +
                            '<YEAR>1983</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Pavarotti Gala Concert</TITLE>\n' +
                            '<ARTIST>Luciano Pavarotti</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>DECCA</COMPANY>\n' +
                            '<PRICE>9.90</PRICE>\n' +
                            '<YEAR>1991</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>The dock of the bay</TITLE>\n' +
                            '<ARTIST>Otis Redding</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>Atlantic</COMPANY>\n' +
                            '<PRICE>7.90</PRICE>\n' +
                            '<YEAR>1987</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Picture book</TITLE>\n' +
                            '<ARTIST>Simply Red</ARTIST>\n' +
                            '<COUNTRY>EU</COUNTRY>\n' +
                            '<COMPANY>Elektra</COMPANY>\n' +
                            '<PRICE>7.20</PRICE>\n' +
                            '<YEAR>1985</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Red</TITLE>\n' +
                            '<ARTIST>The Communards</ARTIST>\n' +
                            '<COUNTRY>UK</COUNTRY>\n' +
                            '<COMPANY>London</COMPANY>\n' +
                            '<PRICE>7.80</PRICE>\n' +
                            '<YEAR>1987</YEAR>\n' +
                        '</CD>\n' +
                        '<CD>\n' +
                            '<TITLE>Unchain my heart</TITLE>\n' +
                            '<ARTIST>Joe Cocker</ARTIST>\n' +
                            '<COUNTRY>USA</COUNTRY>\n' +
                            '<COMPANY>EMI</COMPANY>\n' +
                            '<PRICE>8.20</PRICE>\n' +
                            '<YEAR>1987</YEAR>\n' +
                        '</CD>\n' +
                    '</CATALOG>\n';
        </script>
        <span class="minimizedText" onClick="$('#xmlinput').val(b)">test-document #2</span>
	    
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
        <p><input type="checkbox" name="allquery"/>   consultant query</p>
        <label for="query" class="lessWide">Query: </label>
	    <input type="text" name="query" class="wideText" />
        <div class="fm-submit">
            <input name="submit" type="submit" value="Go!" />
        </div>
	</form>
</div>

<br/>
<br/>
