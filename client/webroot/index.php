<?php
    require_once(dirname(__FILE__).'/../lib/sdm_util.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Trust-Us Consultants</title>

    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

    <script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
    <link rel="stylesheet" type="text/css" href="css/layout.css" media="screen, projection, tv" />
</head>

<body>

<div id="wrapper">
  <div id="content">
    <div id="header">
      <h1><span class="big darkBrown">T</span>rust-<span class="big darkBrown">U</span>s <span class="big darkBrown">C</span>onsultants</h1>
      <h2><span class="highlight">Heads we win - tails, you pay!</span></h2>
    </div>

    <ul id="menu" class="three">
        <?php
            $here = 'class="here"';
			$client_here = $consultant_here = $config_here = "";
            if (isset($_GET['page']) && $_GET['page'] == "client") {
                $client_here = $here;
            }
            else if (isset($_GET['page']) && $_GET['page'] == "consultant") {
                $consultant_here = $here;
            }
            else {
                $config_here = $here;
            }
        ?>
      <li><a href="?page=config" title="Configure key and server information" <?php echo $config_here ?>><span class="big">C</span>onfig</a></li>
      <li><a href="?page=client" title="Client interface" <?php echo $client_here ?>><span class="big">C</span>lient</a></li>
      <li><a href="?page=consultant" title="Consultant interface" <?php echo $consultant_here ?>><span class="big">C</span>onsultant</a></li>
    </ul>

    <div id="page">
        <?php
            $content = "config";

            if (isset($_GET['page'])) {
                if ($_GET['page'] == "client") {
                    $content = "client";
                }
                else if ($_GET['page'] == "consultant") {
                    $content = "consultant";
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