<?php
    require_once(dirname(__FILE__).'/../lib/sdm_util.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Trust-Us Consultants</title>

    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

    <script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>
    <script type="text/javascript" src="js/jquery.alerts.js"></script>
    <link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
    <link rel="stylesheet" type="text/css" href="css/layout.css" media="screen, projection, tv" />
    <link rel="stylesheet" type="text/css" href="css/jquery.alerts.css" media="screen, projection, tv " />
</head>

<body>

<div id="wrapper">
  <div id="content">
    <div id="header">
      <h1><span class="big darkBrown">O</span>ffshore <span class="big darkBrown">C</span>onsulting</h1>
      <h2><span class="highlight">The tax evasion experts.</span></h2>
    </div>

    <ul id="menu" class="three">
        <?php
            $here = 'class="here"';
			$client_here = $consultant_here = $config_here = "";
            if (isset($_GET['page']) && $_GET['page'] == "query") {
                $query_here = $here;
            }
            else if (isset($_GET['page']) && $_GET['page'] == "result") {
                $result_here = $here;
            }
            else {
                $config_here = $here;
            }
        ?>
      <li><a href="?page=config" title="Configure key and server information" <?php echo $config_here ?>><span class="big">C</span>onfig</a></li>
      <li><a href="?page=query" title="Query interface" <?php echo $query_here ?>><span class="big">Q</span>uery</a></li>
      <li><a href="?page=result" title="Result interface" <?php echo $result_here ?>><span class="big">R</span>esult</a></li>
    </ul>

    <div id="page">
        <?php
            $content = "config";

            if (isset($_GET['page'])) {
                if ($_GET['page'] == "query") {
                    $content = "query";
                }
                else if ($_GET['page'] == "result") {
                    $content = "result";
                }
            }

            include(dirname(__FILE__).'/../include/'.$content.'.inc.php');
        ?>

        <!-- .footer: the site footer text, links and whatever else -->
        <p class="footer">
        Secure Data Management 2011-2012 - Twente University - Design by <a href="http://fullahead.org" title="Visit FullAhead">FullAhead</a>.
        </p>
    </div>

  </div>

</div>

</body>

</html>