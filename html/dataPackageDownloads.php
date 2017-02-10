<?php
require_once ('countPackagesInEachQuarter.php');

//This method is used to create the request to fetch the readDataEntity details. This service is called to count the number of downloads of the data package.
function createDataPackagesDownloadsInputData($beginDate, $endDate, $quarter, $site) {
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
		$end_month = ((int) (((date('m', $begin_time) - 1))) + 1);
		$end_time = strtotime(date('Y', $begin_time) . "-" . $end_month . "-15");

		if(strtotime($endDate) < $end_time)
			$end = $endDate;
		else
			$end = date("Y-m-t", $end_time);

		$url = $pastaURL . "audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $begin . "&toTime=" . $end;
		callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageDownloads" );
		if (isset ( $GLOBALS ['dataPackageDownloads'] ) && $GLOBALS ['dataPackageDownloads'] != null) {
			createDataPackagesDownloadOutput ( $GLOBALS ['dataPackageDownloads'], $quarter , $site);
			$GLOBALS ['dataPackageDownloads'] = -1;
		}
		$begin = date('Y-m-d', strtotime($end. ' + 1 days'));
	}
}

//This method is used to create the request to fetch the readDataPackageArchive details. This service is called to count the number of downloads of the data package archives.
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate, $quarter, $site) {

	$GLOBALS ['dataPackageArchiveDownloads1'] = 0;
	$GLOBALS ['dataPackageArchiveDownloads2'] = 0;
	$GLOBALS ['dataPackageArchiveDownloads3'] = 0;
	$GLOBALS ['dataPackageArchiveDownloads4'] = 0;
	$GLOBALS ['dataPackageArchiveDownloads0'] = 0;

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

		$url = $pastaURL . "audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $begin . "&toTime=" . $end;
		callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageArchiveDownloads" );

		if (isset ( $GLOBALS ['dataPackageArchiveDownloads'] ) && $GLOBALS ['dataPackageArchiveDownloads'] != null){
			createDataPackagesArchiveDownloadOutput ( $GLOBALS ['dataPackageArchiveDownloads'], $quarter , $site);
			$GLOBALS ['dataPackageArchiveDownloads'] = -1;
		}
		$begin = date('Y-m-d', strtotime($end. ' + 1 days'));
	}
}

//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createDataPackagesDownloadOutput($xmlData, $quarter, $site) {
	$responseXML = new SimpleXMLElement ( $xmlData );
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

	$GLOBALS ['dataPackageArchiveDownloads1'] += $count ['1'];
	$GLOBALS ['dataPackageArchiveDownloads2'] += $count ['2'];
	$GLOBALS ['dataPackageArchiveDownloads3'] += $count ['3'];
	$GLOBALS ['dataPackageArchiveDownloads4'] += $count ['4'];
	$GLOBALS ['dataPackageArchiveDownloads0'] += $count ['0'];
}
?>
