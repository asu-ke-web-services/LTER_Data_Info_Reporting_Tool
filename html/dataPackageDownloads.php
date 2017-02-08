<?php
require_once ('countPackagesInEachQuarter.php');

//This method is used to create the request to fetch the readDataEntity details. This service is called to count the number of downloads of the data package.
function createDataPackagesDownloadsInputData($beginDate, $endDate, $quarter , $site) {

	$GLOBALS ['dataPackageDownloads1'] = 0;
	$GLOBALS ['dataPackageDownloads2'] = 0;
	$GLOBALS ['dataPackageDownloads3'] = 0;
	$GLOBALS ['dataPackageDownloads4'] = 0;
	$GLOBALS ['dataPackageDownloads0'] = 0;

	global $pastaURL;

	$begin = $beginDate;
	$begin_time = strtotime($beginDate);
	$end_time = strtotime($endDate);

	while(strtotime($begin) < strtotime($endDate)){
		$begin_time = strtotime($begin);
		$end_month = ((int) (((date('m', $begin_time) - 1) / 3)) + 1) * 3;
		$end_time = strtotime(date('Y', $begin_time) . "-" . $end_month . "-15");

		if(strtotime($endDate) < $end_time)
			$end = $endDate;
		else
			$end = date("Y-m-t", $end_time);

		$url = $pastaURL . "audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $begin . "&toTime=" . $end;

		# $url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&&toTime=" . $endDate; # if we want the total, we need everything
		// echo '<script>console.log("'.$url.'")</script>';
		callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageDownloads" );
		// echo '<script>console.log("createDataPackagesDownloadsInputData - AuditReportTool Executed: '.memory_get_usage().'")</script>';

		if (isset ( $GLOBALS ['dataPackageDownloads'] ) && $GLOBALS ['dataPackageDownloads'] != null) {
			createDataPackagesDownloadOutput ( $GLOBALS ['dataPackageDownloads'], $quarter , $site);
			// echo '<script>console.log("createDataPackagesDownloadOutput: '.memory_get_usage().'")</script>';
	    $GLOBALS ['dataPackageDownloads'] = -1;
			// echo '<script>console.log("Unset: '.memory_get_usage().'")</script>';
	  }
		$begin = date('Y-m-d', strtotime($end. ' + 1 days'));
	}
}

//This method is used to create the request to fetch the readDataPackageArchive details. This service is called to count the number of downloads of the data package archives.
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageArchiveDownloads" );
}
//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createDataPackagesDownloadOutput($xmlData, $quarter, $site) {
	// echo '<script>console.log("createDataPackagesDownloadOutput - Before SimpleXMLElement: '.memory_get_usage().'")</script>';
	$responseXML = new SimpleXMLElement ( $xmlData );
	// echo '<script>console.log("createDataPackagesDownloadOutput - After SimpleXMLElement: '.memory_get_usage().'")</script>';
	$count = countPackages ( $quarter, $responseXML , $site);

	$GLOBALS ['dataPackageDownloads1'] += $count ['1'];
	$GLOBALS ['dataPackageDownloads2'] += $count ['2'];
	$GLOBALS ['dataPackageDownloads3'] += $count ['3'];
	$GLOBALS ['dataPackageDownloads4'] += $count ['4'];
	$GLOBALS ['dataPackageDownloads0'] += $count ['0'];
}
//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createDataPackagesArchiveDownloadOutput($xmlData, $quarter, $site) {
	$responseXML = new SimpleXMLElement ( $xmlData );

	$count = countPackages ( $quarter, $responseXML, $site );

	$GLOBALS ['dataPackageArchiveDownloads1'] = $count ['1'];
	$GLOBALS ['dataPackageArchiveDownloads2'] = $count ['2'];
	$GLOBALS ['dataPackageArchiveDownloads3'] = $count ['3'];
	$GLOBALS ['dataPackageArchiveDownloads4'] = $count ['4'];
	$GLOBALS ['dataPackageArchiveDownloads0'] = $count ['0'];
}
?>
