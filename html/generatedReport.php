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
// error_reporting(E_ALL);
// Global declaration of the pasta URL so that if we have to make a change, it can be done in one place.
$pastaURL = "http://pasta.lternet.edu/";
$errorStatus = "";

// Including the file that has information on how to call LTER Data Portal
require_once ('curlWebServiceCalls.php');

// Checking if the PHP Post variable submitReport has been set. This variable will be set when the user clicks on Generate LTER Report in the main page.
if (isset ( $_POST ['submitReport'] )) {
	global $errorStatus;
	$errorStatus = "";

	// Calling the starter method to generate report.
	$reportGenerationStatus = generateReport ($_POST ['site']);

	//echo "dataPackageArchiveDownloads: ".$GLOBALS["dataPackageArchiveDownloads"];

	$GLOBALS['site'] = $_POST ['site'];

	// If the user credentials is not correct, exit the report generation without computing the report.
	if ($reportGenerationStatus == "invalidLogin") {
		global $errorStatus;
		$errorStatus = "invalidLogin";
	}
	// If there was any error during reporting, throw the error to the user.
	if ($reportGenerationStatus != "success" && $reportGenerationStatus != "invalidLogin") {
		global $errorStatus;
		$errorStatus = "reportError";
	}
}

// The main starter method where we process all the reports in sequence. This method controls all the methods that call PASTA to retrive the necessary information.
function generateReport($site) {
	session_start ();
	$username = $_POST ['username'];
	$password = $_POST ['password'];
	$endDate = NULL;
	$beginDate = NULL;

	// Setting the start date to one year ago from current time.
	date_default_timezone_set ( 'MST' );

	// If the user has choosen include current quarter, then include the data until present date
	if ($_POST ['quarter'] === 'current') {
		$endDate = date ( "Y-m-d" );
		$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ) - 3, 01, date ( "Y" ) - 1 ) ) );
		$beginDate = $beginDate->format ( "Y-m-d" );
	} 	// If the report has to be generated until previous quarter, find the previous quarter date and make webs services calls with that date.
	else {
		$currentmonth = date ( "m" );
		$endMonth = $currentmonth - ($currentmonth % 3 == 0 ? 3 : $currentmonth % 3);
		$endDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, $endMonth, cal_days_in_month ( CAL_GREGORIAN, $endMonth, (date ( "Y" )) ), date ( "Y" ) ) ) );
		$endDate = $endDate->format ( "Y-m-d" );
		$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, $endMonth - 3, 01, date ( "Y" ) - 1 ) ) );
		$beginDate = $beginDate->format ( "Y-m-d" );
	}

  // If its an authenticated user, then only continue to generate the report.
	if (! authenticatedUser ()) {
		unset ( $GLOBALS ['submitReport'] );
		return "invalidLogin";
	}
	deleteFilesInDownloadDir();

	// First compute all the 4 quarters thats necessary to generate the report.
	$quarter = determineFourQuarters ( substr ( $endDate, 5, 2 ), $_POST ['quarter'] );

	// Include the file that is used to compute the total number of packages and compute it
	require_once ('totalNumberOfDataPackages.php');
	createTotalDataPackagesInputData ( $beginDate, $endDate, $quarter , $site );
	// Adding a sleep command as making numerous calls to PASTA in a short interval results in failure to get the information.
	sleep ( 2 );

	// Include the file that is used to compute the total number of package downloads and compute it
	require_once ('dataPackageDownloads.php');
	createDataPackagesDownloadsInputData ( $beginDate, $endDate, $quarter , $site);

	createDataPackagesArchiveDownloadsInputData ( $beginDate, $endDate, $quarter, $site );

	updateTotalDataPackagesInputData ( $beginDate, $endDate, $quarter , $site);

	countDataPackagesForYearAgo ($quarter, $endDate, $site);

	// Include the file that is used to compute the random list to of packages created in the last three months.
	require_once ('recentlyPublishedDatasets.php');
	recentlyPublishedDataSetsInput ( $endDate );

	if (isset ( $GLOBALS ['recentlyCreatedDataPackages'] ) && $GLOBALS ['recentlyCreatedDataPackages'] != null){
		recentlyPublishedDataSets ( $GLOBALS ['recentlyCreatedDataPackages'] , $site);
		$GLOBALS ['recentlyCreatedDataPackages'] = -1;
	}
	return "success";
}
// Method to compute the quarter to which we generate the report. Since we are calculating the report for one year, this report will have exactly 4 quarters
function determineFourQuarters($month) {
	$monthList = array (
			'12' => '12',
			'11' => '11',
			'10' => '10',
			'9' => '9',
			'8' => '8',
			'7' => '7',
			'6' => '6',
			'5' => '5',
			'4' => '4',
			'3' => '3',
			'2' => '2',
			'1' => '1'
	);
	// Creating a cyclic array to pick the 4 quarters. 4th quarter is the latest quarter and we go back 3 months and assign months to that quarter.
	// 0th quarter is the 4th quarter but a year before it.
	$key = array_search ( $month, array_keys ( $monthList ) );
	$month1 = array_slice ( $monthList, $key );
	$month2 = array_slice ( $monthList, 0, $key );
	$newMonthArray = array_merge ( $month1, $month2 );

	$currentQuarter = $month % 3;
	if ($currentQuarter == 0)
		$currentQuarter = 3;

	$quarter ['4'] = array_slice ( $newMonthArray, 0, $currentQuarter );
	$quarter ['3'] = array_slice ( $newMonthArray, $currentQuarter, 3 );
	$quarter ['2'] = array_slice ( $newMonthArray, $currentQuarter + 3, 3 );
	$quarter ['1'] = array_slice ( $newMonthArray, $currentQuarter + 6, 3 );

	// The 0th quarter is basically the 4th quarter along with the missing months if any.
	if ($currentQuarter != 0) {
		$tempArray = array_slice ( $newMonthArray, $currentQuarter + 9, 3 );
		$quarter ['0'] = array_merge ( $tempArray, $quarter ['4'] );
	} else {
		$quarter ['0'] = $quarter ['4'];
	}

	// Quarter names as suffix
	$quarterNames = array (
			"-1",
			"-2",
			"-3",
			"-4"
	);

	// Based on the value of month in the array, we create the quarter titles
	if ($month >= 10 && $month <= 12) {
		$quarterTitle ['4'] = date ( "Y" )       . $quarterNames [3];
		$quarterTitle ['3'] = date ( "Y" )       . $quarterNames [2];
		$quarterTitle ['2'] = date ( "Y" )       . $quarterNames [1];
		$quarterTitle ['1'] = date ( "Y" )       . $quarterNames [0];
		$quarterTitle ['0'] = (date ( "Y" ) - 1) . $quarterNames [3];
	}

	if ($month >= 7 && $month <= 9) {
		$quarterTitle ['4'] = date ( "Y" )       . $quarterNames [2];
		$quarterTitle ['3'] = date ( "Y" )       . $quarterNames [1];
		$quarterTitle ['2'] = date ( "Y" )       . $quarterNames [0];
		$quarterTitle ['1'] = (date ( "Y" ) - 1) . $quarterNames [3];
		$quarterTitle ['0'] = (date ( "Y" ) - 1) . $quarterNames [2];
	}

	if ($month >= 4 && $month <= 6) {
		$quarterTitle ['4'] = date ( "Y" )       . $quarterNames [1];
		$quarterTitle ['3'] = date ( "Y" )       . $quarterNames [0];
		$quarterTitle ['2'] = (date ( "Y" ) - 1) . $quarterNames [3];
		$quarterTitle ['1'] = (date ( "Y" ) - 1) . $quarterNames [2];
		$quarterTitle ['0'] = (date ( "Y" ) - 1) . $quarterNames [1];
	}

	if ($month >= 1 && $month <= 3) {
		$quarterTitle ['4'] = date ( "Y" )       . $quarterNames [0];
		$quarterTitle ['3'] = (date ( "Y" ) - 1) . $quarterNames [3];
		$quarterTitle ['2'] = (date ( "Y" ) - 1) . $quarterNames [2];
		$quarterTitle ['1'] = (date ( "Y" ) - 1) . $quarterNames [1];
		$quarterTitle ['0'] = (date ( "Y" ) - 1) . $quarterNames [0];
	}

	// Creating the custom labels which will be added to the graph and table.
	$GLOBALS ['quarterTitle'] = $quarterTitle;

	if ($_POST ['quarter'] === 'current')
		$GLOBALS ['CurrentQuarterDate'] = "From " . $quarter ['4'] [count ( $quarter ['4'] ) - 1] . "/01/" . date ( "Y" ) . " to " . $quarter ['4'] [0] . "/" . (date ( "d" )) . "/" . date ( "Y" );
	else
		$GLOBALS ['CurrentQuarterDate'] = "From " . $quarter ['4'] [2] . "/01/" . date ( "Y" ) . " to " . $quarter ['4'] [0] . "/" . cal_days_in_month ( CAL_GREGORIAN, $quarter ['4'] [count ( $quarter ['4'] ) - 1], (date ( "Y" )) ) . "/" . date ( "Y" );

	$GLOBALS ['PreviousQuarterDate'] = "From " . $quarter ['3'] [2] . "/01/" . date ( "Y" ) . " to " . $quarter ['3'] [0] . "/" . cal_days_in_month ( CAL_GREGORIAN, $quarter ['3'] [0], (date ( "Y" )) ) . "/" . date ( "Y" );
	$GLOBALS ['AsOfCurrentQuarterDate'] = "As of " . date ( "m" ) . "/" . date ( "d" ) . "/" . date ( "Y" );
	$GLOBALS ['AsOfPreviousQuarterDate'] = "As of " . $quarter ['3'] [0] . "/" . cal_days_in_month ( CAL_GREGORIAN, $quarter ['3'] [0], (date ( "Y" )) ) . "/" . date ( "Y" );
	$GLOBALS ['AsOfPreviousYearDate'] = "As of " . date ( "m" ) . "/" . date ( "d" ) . "/" . (date ( "Y" ) - 1);

	return $quarter;
}

//Funtion to clear the download directory before report generation
function deleteFilesInDownloadDir(){

	$files = glob('../download/*'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file))
			unlink($file); // delete file
	}
}

// This method is used to authenticate the user credentials. We make a simple call to fetch all the eml identifier. This will fetch all the identifiers in PASTA.
// Since knb-lter-cap has to be present as one of the eml, we check if its present in the response. If so, the user credentials is correct, if not, we throw a error message.
function authenticatedUser() {
	global $pastaURL;
	$url = $pastaURL . "package/eml";
	$test = callAuditReportTool( $url, $_POST ['username'], $_POST ['password'] );
	$pos = strpos ( $test, "knb-lter-cap" );
	if ($pos === false)
		return false;
	else
		return true;
}

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

		if (isset ( $GLOBALS ['totalDataPackages4'] )) {

			?>

			<div class="starter-template"><hr>
			<p class="lead">Report for Site : <?php echo $_POST ['site'] ;?> </p> &nbsp;
					<div class="span3" style="text-align: center">
					<button id="reportButton" type="button" class="btn btn-primary">Save report as a file</button>
				<br>
				</div><hr>
				<p class="lead">Total Number Of Data Packages In Network Information
					System</p>
				<p>This report reflects the total number of data packages published
					by LTER sites in the network information system. It includes the
					total by quarter.</p>
			</div>
			<div id="comment_1" style="text-align: center"></div><br><br>
			<div id="chart_div_totalDataPackages"
				style="width: 1000px; height: 400px;"></div>
			<div class="span3" style="text-align: center">
				<button type="button" class="btn btn-primary"
					onclick="saveAsImg(document.getElementById('chart_div_totalDataPackages'));">Save
					as Image</button>
			</div>
				<?php
		}

		if (isset ( $GLOBALS ['dataPackageDownloads4'] )) {
			?>
      <div class="page-break"> </div>
      <div class="starter-template">
				<p class="lead">Number of Data Package Downloads</p>
				<p>This graphic reflects the number of data package downloads from
					the LTER network information system by quarter.</p>
			</div>
			<div id="comment_2" style="text-align: center"></div><br><br>
			<div id="chart_div_dataPackagesDownloads"
				style="width: 1000px; height: 400px;"></div>
			<div class="span3" style="text-align: center">
				<button type="button" class="btn btn-primary"
					onclick="saveAsImg(document.getElementById('chart_div_dataPackagesDownloads'));">Save
					as Image</button>
			</div>
			<?php
		}
		?>

		<?php
		if ((isset ( $GLOBALS ['totalDataPackages4'] )) && (isset ( $GLOBALS ['updateDataPackages4'] ))) {
			?>
		<div class="starter-template">
				<p class="lead">Network Summary Statistics</p>
				<div id="comment_3" style="text-align: center"></div><br><br>
				<table class="table table-striped table-bordered">
					<tr>
						<th></th>
						<th><?php echo $GLOBALS['CurrentQuarterDate']; ?></th>
						<th><?php echo $GLOBALS['PreviousQuarterDate']; ?></th>
						<th>A year Ago</th>
						<th>Last 12 Months</th>
					</tr>
					<tr>
						<td>Number of data packages published</td>
						<td><?php echo $GLOBALS['totalDataPackagesCurrentQ']; ?></td>
						<td><?php echo $GLOBALS['totalDataPackagesLastQ']; ?></td>
						<td><?php echo $GLOBALS['totalDataPackagesAyear']; ?></td>
						<td><?php echo $GLOBALS['totalDataPackages12Month']; ?></td>
					</tr>
					<tr>
						<td>Number of data package updates/revisions</td>
						<td><?php echo $GLOBALS['updateDataPackages4']; ?></td>
						<td><?php echo $GLOBALS['updateDataPackages3']; ?></td>
						<td><?php echo $GLOBALS['totalUpdateDataPackageAYearAgo']; ?></td>
						<td><?php echo ($GLOBALS['updateDataPackages1'] + $GLOBALS['updateDataPackages2'] + $GLOBALS['updateDataPackages3'] + $GLOBALS['updateDataPackages4']); ?></td>
					</tr>
				</table>

				<table class="table table-striped table-bordered">
					<tr>
						<th></th>
						<th>Current Quarter - <?php echo $GLOBALS['AsOfCurrentQuarterDate']; ?></th>
						<th>Previous Quarter - <?php echo $GLOBALS['AsOfPreviousQuarterDate']; ?></th>
						<th>A year ago - <?php echo $GLOBALS['AsOfPreviousYearDate']; ?></th>
					</tr>
					<tr>
						<td>Total number of published data packages</td>
						<td><?php echo $GLOBALS['totalDataPackages4']; ?></td>
						<td><?php echo $GLOBALS['totalDataPackages3']; ?></td>
						<td><?php echo $GLOBALS['totalCreateDataPackageAYearAgo']; ?></td>
					</tr>
				</table>
			</div>

		<?php
		}
		?>

		<?php
		if ((isset ( $GLOBALS ['recentlyCreatedDataPackages'])) && (isset($GLOBALS ['recentPackages']))) {

			?>
    <div class="page-break"> </div>
		<div class="starter-template">
				<p class="lead">Selection of Recently Published Datasets (Last Three
					Months)</p>
				<p>This table presents a random selection of data packages published
					during the current reporting period. It is intended to provide a
					flavor of the type of research data being made accessible through
					the LTER Network Information System.</p>
					<div id="comment_4" style="text-align: center"></div><br><br>
				<table class="table table-striped table-bordered">
					<tr>
						<th style="text-align: center">Data Package Identifier</th>
						<th style="text-align: center">Creators</th>
						<th style="text-align: center">Publication Date</th>
						<th style="text-align: center">Title</th>
					</tr>
					<?php

			$data = $GLOBALS ['recentPackages'];
			$size = (count($data) > 10 ? 10 : count($data));
			for($i = 0; $i < $size; $i ++) {
				?><tr>
						<td><a href=<?php echo $data[$i]['identifierLink'];?>><?php echo $data[$i]['name']; ?></a></td>
						<td><?php echo $data[$i]['author']; ?></td>
						<td><?php echo $data[$i]['date']; ?></td>
						<td><?php echo $data[$i]['title']; ?></td>
					</tr>
					<?php } ?>
				</table>
			</div>
		<?php

		} // end if isset( recentlyCreatedDataPackages and recentPackages)
		//saveCurrentPage();
	/*
		if (isset ( $GLOBALS ['totalDataPackages'] )){
			unset ( $GLOBALS ['totalDataPackages'] );
		}
		if (isset ( $GLOBALS ['updateDataPackages'] )){
			unset ( $GLOBALS ['updateDataPackages'] );
		}
		if (isset ( $GLOBALS ['dataPackageDownloads'] )){
			unset ( $GLOBALS ['dataPackageDownloads'] );
		}
		if (isset ( $GLOBALS ['dataPackageArchiveDownloads'] )){
			unset ( $GLOBALS ['dataPackageArchiveDownloads'] );
		}

		if (isset ( $GLOBALS ['recentlyCreatedDataPackages'] )) {
			unset ( $GLOBALS ['recentlyCreatedDataPackages'] );
		}
   */
		?>

		<?php if (isset ( $_POST ['submitReport'] )) { ?>
		<div class="span3" style="text-align: center">
					<button id="reportButton1" type="button" class="btn btn-primary">Save
						report as a file</button>
				<br><br>
				</div>

					<div class="span3" style="text-align: center">
					<button id=saveReport type="button" class="btn btn-primary">Save report on server for future reference</button>
					<div id="reportIDDiv" >
			       		<span id="textSpan"></span>
					</div>
					<div id="reportIDLinkDiv" >
					<span id="linkSpan"></span>
					</div>
					<div id="reportIDEmailDiv" >
					<span id="EmailSpan"></span>
					</div>
				</div>
				<br><br><br><br>
				</div>
		<?php
		}
		?>
		</div>

	</div>
	<!-- /.container -->

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script type="text/javascript" src="../assets/js/jquery.js"></script>
	<script type="text/javascript" src="../dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//www.google.com/jsapi"></script>

  <script type="text/javascript" src="https://canvg.github.io/canvg/rgbcolor.js"></script>
  <script type="text/javascript" src="https://canvg.github.io/canvg/StackBlur.js"></script>
  <script type="text/javascript" src="https://canvg.github.io/canvg/canvg.js"></script>
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChartTotalDataPackages);
      google.setOnLoadCallback(drawChartDataPackageDownloads);

      function drawChartTotalDataPackages() {
        var data = google.visualization.arrayToDataTable([
          ['Quarter', 'Total Packages'],
          [<?php echo "'".$GLOBALS['quarterTitle']['0']."', ".$GLOBALS['totalDataPackages0']; ?>],
          [<?php echo "'".$GLOBALS['quarterTitle']['1']."', ".$GLOBALS['totalDataPackages1']; ?>],
          [<?php echo "'".$GLOBALS['quarterTitle']['2']."', ".$GLOBALS['totalDataPackages2']; ?>],
          [<?php echo "'".$GLOBALS['quarterTitle']['3']."', ".$GLOBALS['totalDataPackages3']; ?>],
          [<?php echo "'".$GLOBALS['quarterTitle']['4']."', ".$GLOBALS['totalDataPackages4']; ?>],
        ]);

        var options = {
          title: 'LTER Network Data Packages',
          hAxis: {title: 'Quarter Reporting Period'},
          vAxis: {title: "Total Data Packages"},
          colors: ['#F87431'],
          is3D:true
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_totalDataPackages'));
        chart.draw(data, options);
      }

      function drawChartDataPackageDownloads() {
          var data = google.visualization.arrayToDataTable([
            ['Quarter', 'Number of Data Downloads', 'Number of Data Archive Downloads'],
            [<?php echo "'".$GLOBALS['quarterTitle']['0']."'"; ?>, <?php echo $GLOBALS['dataPackageDownloads0']; ?>,  <?php echo $GLOBALS['dataPackageArchiveDownloads0']; ?>],
            [<?php echo "'".$GLOBALS['quarterTitle']['1']."'"; ?>, <?php echo $GLOBALS['dataPackageDownloads1']; ?>,  <?php echo $GLOBALS['dataPackageArchiveDownloads1']; ?>],
            [<?php echo "'".$GLOBALS['quarterTitle']['2']."'"; ?>, <?php echo $GLOBALS['dataPackageDownloads2']; ?>,  <?php echo $GLOBALS['dataPackageArchiveDownloads2']; ?>],
            [<?php echo "'".$GLOBALS['quarterTitle']['3']."'"; ?>, <?php echo $GLOBALS['dataPackageDownloads3']; ?>,  <?php echo $GLOBALS['dataPackageArchiveDownloads3']; ?>],
            [<?php echo "'".$GLOBALS['quarterTitle']['4']."'"; ?>, <?php echo $GLOBALS['dataPackageDownloads4']; ?>,  <?php echo $GLOBALS['dataPackageArchiveDownloads4']; ?>],
          ]);

          var options = {
            title: 'Number of Network Downloads',
            isStacked: true,
            hAxis: {title: 'Quarter Reporting Period'},
            vAxis: {title: "Number of Downloads"},
            colors: ['#F87431','red']
          };

          var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_dataPackagesDownloads'));
          chart.draw(data, options);
      }
</script>


	<script type="text/javascript">

    function getImgData(chartContainer) {
        var chartArea = chartContainer.getElementsByTagName('svg')[0].parentNode;
        var svg = chartArea.innerHTML;
        var doc = chartContainer.ownerDocument;
        var canvas = doc.createElement('canvas');
        canvas.setAttribute('width', chartArea.offsetWidth);
        canvas.setAttribute('height', chartArea.offsetHeight);

        canvas.setAttribute(
            'style',
            'position: absolute; ' +
            'top: ' + (-chartArea.offsetHeight * 2) + 'px;' +
            'left: ' + (-chartArea.offsetWidth * 2) + 'px;');
        doc.body.appendChild(canvas);
        canvg(canvas, svg);
        var imgData = canvas.toDataURL('image/png');
        canvas.parentNode.removeChild(canvas);
        return imgData;
      }

      function saveAsImg(chartContainer) {

    	var imgData = getImgData(chartContainer);
    	$.post("savingImage.php",{data:imgData,file:"ImageFile"},downloadImage);
      }

      function downloadImage() {
    	  window.location.href =  "download.php?path="+"../download/ImageFile.png";
       }

  	  function downloadReport() {
  		  sessionID = getPHPSessId();
  		  if(sessionID == ''){
  	  		  alert("Report cannot be saved as a file. Please try again later.");
  	  		  return;
  		  }
  		  fileName = "LTERReport"+sessionID+".xlsx"
    	  window.location.href =  "download.php?path="+"../download/"+fileName;
      }

  	function genReport(){
  	  var imgData = getImgData(document.getElementById('chart_div_totalDataPackages'));
  	  $.post("savingImage.php",{data:imgData,file:"1"});
  	  var imgData = getImgData(document.getElementById('chart_div_dataPackagesDownloads'));
  	  $.post("savingImage.php",{data:imgData,file:"2"});
  	  $.ajax({url: 'htmlToCSVConversion.php',success:downloadReport});
  }

  function getPHPSessId() {
      var phpSessionId = document.cookie.match(/PHPSESSID=[^;]+/);

      if(phpSessionId == null)
          return '';

      if(typeof(phpSessionId) == 'undefined')
          return '';

      if(phpSessionId.length <= 0)
          return '';

      phpSessionId = phpSessionId[0];

      var end = phpSessionId.lastIndexOf(';');
      if(end == -1) end = phpSessionId.length;

      return phpSessionId.substring(10, end);
  }

  function saveReportCall(){
	  		var comment1 =  sanitizeInput($("#comment_1").html());
	  		var comment2 =  sanitizeInput($("#comment_2").html());
	  		var comment3 =  sanitizeInput($("#comment_3").html());
	  		var comment4 =  sanitizeInput($("#comment_4").html());



	  		$.ajax({
  	            type :'POST',
  	            url  : 'saveCurrentPageInDB.php',
  	            data : {comment1: comment1,comment2: comment2,comment3: comment3,comment4: comment4},
  	            success : function(data) {

  	            var link = document.URL;

  	             if(data.indexOf("New-") !== -1){
  	            	data = data.replace("New-","")	;
	  	            $('#reportIDDiv span').text('Report Created Successfully. Report ID : '+data);
	  	            $('#reportIDLinkDiv').html('<a href="recreateReport.php?ID='+data+'">Link to the Report</a>');
	  	         	var newLink = "recreateReport.php?ID="+data;
	  	  	  		link = link.replace("generatedReport.php",newLink);
	  	            $('#reportIDEmailDiv').html('<a href="mailto:?Subject=LTER%20Network%20Information%20System%20Report&body=Please use the following report ID to retrieve the LTER report.%0d%0a%0d%0aReport ID : '+data+' %0d%0a%0d%0a'+link+'">Email Report ID</a>');
  	             }
  	           	if(data.indexOf("Old-") !== -1){
  	           	    data = data.replace("Old-","")	;
	  	            $('#reportIDDiv span').text('Record already present in database. Report ID : '+data);
	  	            $('#reportIDLinkDiv').html('<a href="recreateReport.php?ID='+data+'">Link to the Report</a>');
	  	          	var newLink = "recreateReport.php?ID="+data;
	  	  	  		link = link.replace("generatedReport.php",newLink);
	  	            $('#reportIDEmailDiv').html('<a href="mailto:?Subject=LTER%20Network%20Information%20System%20Report&body=Please use the following report ID to retrieve the LTER report.%0d%0a%0d%0aReport ID : '+data+' %0d%0a%0d%0a'+link+'">Email Report ID</a>');
 	             }
  	           if(data.indexOf("Updated-") !== -1){
	            	data = data.replace("Updated-","")	;
	  	            $('#reportIDDiv span').text('Record comments updated in database. Report ID : '+data);
	  	            $('#reportIDLinkDiv').html('<a href="recreateReport.php?ID='+data+'">Link to the Report</a>');
	  	         	var newLink = "recreateReport.php?ID="+data;
	  	  	  		link = link.replace("generatedReport.php",newLink);
	  	            $('#reportIDEmailDiv').html('<a href="mailto:?Subject=LTER%20Network%20Information%20System%20Report&body=Please use the following report ID to retrieve the LTER report.%0d%0a%0d%0aReport ID : '+data+' %0d%0a%0d%0a'+link+'">Email Report ID</a>');
	             }
  	           }
  	        })
  	}

	function sanitizeInput(comment){

		if(typeof comment === 'undefined')
			return "";

		if(comment.indexOf("script") !== -1){
			comment = comment.replace("<script>", "");
  			comment = comment.replace("<\/script>", "");
		}
  		if(comment == "Click to add comments")
	  		comment = "";

		return comment;
	}

	</script>

<script type="text/javascript" src="../dist/js/jquery.jeditable.js"></script>
	<script>
$(document).ready(function(){

  $("#reportButton").click(genReport);
  $("#reportButton1").click(genReport);
  $("#saveReport").click(saveReportCall);

  $("#comment_1").editable(function(value, settings) {return(value);},
          {
    			 type :'textarea',
	             rows : 8,
	             cols : 80,
	             "submit" : "Save text",
	             placeholder : "Click to add comments",
	             onblur: "submit",
	             tooltip   : 'Click to edit...'
		     }
   );
  $("#comment_2").editable(function(value, settings) {return(value);},
          {
  			 type :'textarea',
	             rows : 8,
	             cols : 80,
	             "submit" : "Save text",
	             placeholder : "Click to add comments",
	             onblur: "submit",
	             tooltip   : 'Click to edit...'
		     }
   );
	$("#comment_3").editable(function(value, settings) {return(value);},
          {
  			 type :'textarea',
	             rows : 8,
	             cols : 80,
	             "submit" : "Save text",
	             placeholder : "Click to add comments",
	             onblur: "submit",
	             tooltip   : 'Click to edit...'
		     }
   );
	$("#comment_4").editable(function(value, settings) {return(value);},
          {
  			 type :'textarea',
	             rows : 8,
	             cols : 80,
	             placeholder : "Click to add comments",
	             "submit" : "Save text",
	             onblur: "submit",
	             tooltip   : 'Click to edit...'
		     }
   );

});

</script>
</body>
</html>
