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
					<li class="active"><a href="index.php">Home</a></li>
					<li><a href="submitReportID.php">Retrieve Old Reports</a></li>
					<li><a href="aboutLTER.html">About</a></li>
					<li><a href="contact.html">Contact</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="starter-template">
			<h1>
				<img src="../assets/ico/LTER.png">&nbsp;&nbsp;Welcome to LTER
				Network Information System Reporting Tool
			</h1>
			<br>
			<p class="lead">This report describes the current status of the data
				package inventory as published in the LTER network information
				system. It is produced to highlight the volume of public access data
				produced by the LTER network of research sites. This report is
				intended for the LTER Personnel, National Science Foundation
				program officers, and other interested parties</p>
			<hr>
		</div>
		<div class="col-md-12">

		 <?php

			global $errorStatus;
			if ($errorStatus === "reportError") {
				echo '<script> alert("There was a problem during error generation. Please try again.");
			window.location="index.php"; </script> ';
			}
			?>

		<?php

		global $errorStatus;
		if ($errorStatus === "invalidLogin") {
			echo '<script> alert("Unable to generate the report. Please verify the login credentials and try again.");
			window.location="index.php"; </script> ';
		}
		?>

			<p align="center">
				<i>Please provide the login information to generate LTER Network
					System Report. <br>Please note that the report generation may take
					time.
				</i>
			</p>
			<form id="reportForm" class="form-signin" method="POST"
				action="generatedReport.php">
				<input id="username" name="username" type="text"
					class="form-control" placeholder="Username" autofocus>
				<input id="password" name="password" type="password" class="form-control"
					placeholder="Password"><br>

				<div class="dropdown">
					Select site :
					<button type="button" id="siteSelectButton"
						class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						Select &nbsp;<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li><a>All Sites</a></li>
						<li role="presentation" class="divider"></li>
    				            <?php
									$rows = populateDropdownContent();
									for($i = 0; $i < sizeof($rows); $i ++) {
								?>
									<li><a><?php echo $rows[$i]?></a></li>
								<?php } ?>
							</ul>
					</div>
					<input id="site" name="site" type="hidden" value="">
				<p><br>
					Generate LTER Network Report : <br> <input type="radio" name="quarter" checked value="current">&nbsp;Including Current Quarter
					<br> <input type="radio" name="quarter" value="previous">&nbsp;Excluding Current Quarter<br>
				</p>
				<button class="btn btn-lg btn-primary btn-block" type="submit"
					name="submitReport">Generate LTER Network Information System Report</button>
			</form>

			<div class="starter-template" id="afterSubmit">
				<p class="lead">Please wait while we generate the report.....</p>
			</div>

		</div>

	</div>
	<!-- /.container -->

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="../assets/js/jquery.js"></script>
	<script src="../dist/js/bootstrap.min.js"></script>

	<script language="JavaScript">
	$(document).ready(function() {
		$('#afterSubmit').hide();
		$('#reportForm').submit(function() {
				if(($('#username').val().length == 0) || ($('#password').val().length == 0)){
					alert("Please enter username and password for report generation");
					return false;
				}
				if($.trim($('.dropdown').find('#siteSelectButton').text()) == "Select"){
					alert("Please select the site to generate the report");
					return false;
				}
				$('#reportForm').hide();
				$('#afterSubmit').show();
				$('#site').val($('.dropdown').find('#siteSelectButton').text());

		}).change();

		$(".dropdown-menu li a").click(function(){
			  var selText = $(this).text();
			  $(this).parents('.dropdown').find('#siteSelectButton').html(selText+' <span class="caret"></span>');
			});

	});
</script>

	<script
		src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script>
</body>
</html>
