<?php
//This method is used to create the request to fetch the createDataPackage details. We set the date, service method to be called and set it as a session variable.
function createTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
#	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&&toTime=" . $endDate; # if we want the total, we need everything
	callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "totalDataPackages");
}

//This method is used to create the request to fetch the updateDataPackage details. We set the date, service method to be called and set it as a session variable.
function updateTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
#	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&&toTime=" . $endDate; # if we want the total, we need everything
	callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "updateDataPackages");
}

// to get proper totals we need to count the data that was enterted older than the earliest quarter we're reporting on
function totalDataPackagesBefore($endDate, $site) {
	require_once('countPackagesInEachQuarter.php');
	global $pastaURL;
	$create_url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&toTime=" . $endDate;
	$create_response = callAuditReportTool( $create_url, $_POST ['username'], $_POST ['password']);
	$delete_url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&toTime=" . $endDate;
	$delete_response = callAuditReportTool( $delete_url, $_POST ['username'], $_POST ['password']);
  return countTotalPackages($create_response, $site) - countTotalPackages($delete_response, $site);
}

//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createTotalDataPackagesOutput($xmlData, $quarter,$deleteCount,$site) {
	$responseXML = new SimpleXMLElement( $xmlData);
	
	require_once('countPackagesInEachQuarter.php');
	$count = countPackages( $quarter, $responseXML,$site);
	
	for($i= 0 ;$i< 5; $i++){
		$finalCount[$i] = $count [$i] - $deleteCount[$i];
	}

	$_SESSION ['totalDataPackages0'] = $finalCount['0'] ;
	$_SESSION ['totalDataPackages1'] = $finalCount['0'] + $finalCount['1'];
	$_SESSION ['totalDataPackages2'] = $finalCount['0'] + $finalCount['1'] + $finalCount['2'];
	$_SESSION ['totalDataPackages3'] = $finalCount['0'] + $finalCount['1'] + $finalCount['2'] + $finalCount['3'];
	$_SESSION ['totalDataPackages4'] = $finalCount['0'] + $finalCount['1'] + $finalCount['2'] + $finalCount['3']+ $finalCount['4'];
	
	
	$_SESSION ['totalDataPackagesCurrentQ'] = $finalCount ['4'];
	$_SESSION ['totalDataPackagesLastQ'] = $finalCount ['3'];
	$_SESSION ['totalDataPackages12Month'] = $finalCount ['1'] + $finalCount ['2'] + $finalCount ['3'] + $finalCount ['4'];
}

//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function updateDataPackagesOutput($xmlData, $quarter, $site) {
	$responseXML = new SimpleXMLElement( $xmlData);
	
	require_once('countPackagesInEachQuarter.php');
	$count = countPackages( $quarter, $responseXML, $site);
	
	$_SESSION ['updateDataPackages1'] = $count ['1'];
	$_SESSION ['updateDataPackages2'] = $count ['2'];
	$_SESSION ['updateDataPackages3'] = $count ['3'];
	$_SESSION ['updateDataPackages4'] = $count ['4'];
	$_SESSION ['updateDataPackages0'] = $count ['0'];
}

//This method is used to populate the network statistics table. This method is a handler class to all the necessary data. 
function countDataPackagesForYearAgo($quarter,$endDate,$site){
	countCreateDataPackagesAYearAgo($endDate,$site);
	countUpdateDataPackagesAYearAgoQuarter($quarter,$site);
	countCreateDataPackagesAYearAgoQuarter($quarter,$site);
}
//This method is used to count the total number of packages upto a year ago.
//Since creating also comes with deletion, we count that as well and then displayed the aggregated number 
function countCreateDataPackagesAYearAgo($endDate,$site){
	global $pastaURL;
	$month = (substr($endDate,5,2));
	$newEndDate = (date("Y") -1)."-".$month."-".(cal_days_in_month(CAL_GREGORIAN, $month,(date("Y")-1)));
	
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&toTime=" . $newEndDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	$responseXML = new SimpleXMLElement( $xmlData);
	
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&toTime=" . $newEndDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$deleteResponseXML = new SimpleXMLElement( $xmlData);
	
	$_SESSION ['totalCreateDataPackageAYearAgo'] = countTotalPackages($responseXML,$site) - countTotalPackages($deleteResponseXML,$site);
}

//Count the total number of update/revisions for the same quarter but a year ago.
function countUpdateDataPackagesAYearAgoQuarter($quarter,$site){
	global $pastaURL;
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-4")!== FALSE){
		$endDate =(date("Y")-1)."-12-".cal_days_in_month(CAL_GREGORIAN, 12,(date("Y")-1));
		$beginDate =(date("Y")-1)."-10-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-3")!== FALSE){
		$endDate =(date("Y")-1)."-09-".cal_days_in_month(CAL_GREGORIAN, 9,(date("Y")-1));
		$beginDate =(date("Y")-1)."-07-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-2")!== FALSE){
		$endDate =(date("Y")-1)."-06-".cal_days_in_month(CAL_GREGORIAN, 6,(date("Y")-1));
		$beginDate =(date("Y")-1)."-04-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-1")!== FALSE){
		$endDate =(date("Y")-1)."-03-".cal_days_in_month(CAL_GREGORIAN, 3,(date("Y")-1));
		$beginDate =(date("Y")-1)."-01-01";
	}
	
	
	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&toTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);

	$_SESSION ['totalUpdateDataPackageAYearAgo'] = countTotalPackages($responseXML,$site);
}
//Count the total number of create and deletes for the same quarter but a year ago.
function countCreateDataPackagesAYearAgoQuarter($quarter,$site){
	global $pastaURL;

	if(strpos($_SESSION ['quarterTitle']['4'],"-4")!== FALSE){
		$endDate =(date("Y")-1)."-12-".cal_days_in_month(CAL_GREGORIAN, 12,(date("Y")-1));
		$beginDate =(date("Y")-1)."-10-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-3")!== FALSE){
		$endDate =(date("Y")-1)."-09-".cal_days_in_month(CAL_GREGORIAN, 9,(date("Y")-1));
		$beginDate =(date("Y")-1)."-07-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-2")!== FALSE){
		$endDate =(date("Y")-1)."-06-".cal_days_in_month(CAL_GREGORIAN, 6,(date("Y")-1));
		$beginDate =(date("Y")-1)."-04-01";
	}
	
	if(strpos($_SESSION ['quarterTitle']['4'],"-1")!== FALSE){
		$endDate =(date("Y")-1)."-03-".cal_days_in_month(CAL_GREGORIAN, 3,(date("Y")-1));
		$beginDate =(date("Y")-1)."-01-01";
	}

	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);
	
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$deleteResponseXML = new SimpleXMLElement( $xmlData);

	$_SESSION ['totalDataPackagesAyear'] = countTotalPackages($responseXML,$site) - countTotalPackages($deleteResponseXML,$site);
}

//Since we are calcualting the createDataPackage, we also need to take care of the number of packages deleted in the same quarter. The total created pacakges will be create - delete of the package.
function countDeletedPackages($beginDate, $endDate,$quarter, $site){	
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$responseXML = new SimpleXMLElement( $xmlData);
	
	require_once('countPackagesInEachQuarter.php');
	$deleteCount = countPackages( $quarter, $responseXML, $site);
	
	return $deleteCount;
}
?>
