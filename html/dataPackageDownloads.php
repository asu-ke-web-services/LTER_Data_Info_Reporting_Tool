<?php
require_once ('countPackagesInEachQuarter.php');

//This method is used to create the request to fetch the readDataEntity details. This service is called to count the number of downloads of the data package.
function createDataPackagesDownloadsInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageDownloads" );
}
//This method is used to create the request to fetch the readDataPackageArchive details. This service is called to count the number of downloads of the data package archives.
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageArchiveDownloads" );
}
//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createDataPackagesDownloadOutput($xmlData, $quarter, $site) {
	$responseXML = new SimpleXMLElement ( $xmlData );

	$count = countPackages ( $quarter, $responseXML , $site);

	$GLOBALS ['dataPackageDownloads1'] = $count ['1'];
	$GLOBALS ['dataPackageDownloads2'] = $count ['2'];
	$GLOBALS ['dataPackageDownloads3'] = $count ['3'];
	$GLOBALS ['dataPackageDownloads4'] = $count ['4'];
	$GLOBALS ['dataPackageDownloads0'] = $count ['0'];
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
