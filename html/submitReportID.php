<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="shortcut icon" href="../assets/ico/LTER.png">

<title>LTER Network Information System Reporting Tool</title>

<!-- Bootstrap core CSS -->
<link href="../dist/css/bootstrap.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="../assets/css/index.css" rel="stylesheet">
  <link href="../assets/css/print.css" rel="stylesheet" media="print"/>

</head>
<?php

// Global declaration of the pasta URL so that if we have to make a change, it can be done in one place.
$pastaURL = "http://pasta.lternet.edu/";
$errorStatus = "";


function populateDropdownContent() {
	global $pastaURL;
	$url = $pastaURL . "package/eml";
  $site_list = file_get_contents($url);
  //Split up the site names based on the newline
  $dropdown = preg_split('/\s+/', $site_list);
  return $dropdown;
}

?>
  <body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".navbar-collapse">
					<span class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">LTER Network Information
					System Report</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li ><a href="index.php">Home</a></li>
					<li class="active"><a href="submitReportID.php">Retrieve Old Reports</a></li>
					<li><a href="aboutLTER.html">About</a></li>
					<li><a href="contact.html">Contact</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>
<br><br><br><br><br><br><br><br>
	<div class="container">
	
	
	<form id="reportForm" class="form-signin" method="GET"
				action="recreateReport.php">
		<div class="col-md-12">	
		<p class="lead">Please provide the report ID to fetch all the details related to that report</p> &nbsp;
				<input id="ID" name="ID" type="number"
					class="form-control" placeholder="Enter Report ID" autofocus> <br>
					<button class="btn btn-lg btn-primary btn-block" type="submit">Fetch Report Details</button>
		</div>
		
	</div>
	<!-- /.container -->

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="../assets/js/jquery.js"></script>
	<script src="../dist/js/bootstrap.min.js"></script>

	<script
		src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script>
	<script>

</script>
</body>
</html>
