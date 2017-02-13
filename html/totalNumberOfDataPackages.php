<?php
//This method is used to create the request to fetch the createDataPackage details. We set the date, service method to be called and set it as a session variable.
function createTotalDataPackagesInputData($beginDate, $endDate, $quarter , $site ) {

	// Initializing counts to 0.
	$GLOBALS ['totalDataPackages0'] = 0;
	$GLOBALS ['totalDataPackages1'] = 0;
	$GLOBALS ['totalDataPackages2'] = 0;
	$GLOBALS ['totalDataPackages3'] = 0;
	$GLOBALS ['totalDataPackages4'] = 0;
	$GLOBALS ['totalDataPackagesCurrentQ'] = 0;
	$GLOBALS ['totalDataPackagesLastQ'] = 0;
	$GLOBALS ['totalDataPackages12Month'] = 0;

	global $pastaURL;
	$begin = $beginDate;
	$begin_time = strtotime($beginDate);
	$end_time = strtotime($endDate);

	// Flag is to check 1st call. If 1st call, then fetch all data from start of time to the beginDate.
	$flag = 1;
	// Comparing the end of quarter with end_date
	while(strtotime($begin) < strtotime($endDate)){
		$begin_time = strtotime($begin);
		$end_month = ((int) (((date('m', $begin_time) - 1) / 3)) + 1) * 3;
		$end_time = strtotime(date('Y', $begin_time) . "-" . $end_month . "-15");

		if(strtotime($endDate) < $end_time)
			$end = $endDate;
		else
			$end = date("Y-m-t", $end_time);

		if($flag){
			$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&&toTime=" . $end; # if we want the total, we need everything
			$flag = 0;
		}
		else{
			$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $begin . "&toTime=" . $end;
		}

		callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "totalDataPackages");
		if (isset ( $GLOBALS ['totalDataPackages'] ) && $GLOBALS ['totalDataPackages'] != null) {
			$deleteCount = countDeletedPackages ( $begin, $end, $quarter , $site);
			createTotalDataPackagesOutput ( $GLOBALS ['totalDataPackages'], $quarter, $deleteCount, $site );
			$GLOBALS ['totalDataPackages'] = -1;
		}
		$begin = date('Y-m-d', strtotime($end. ' + 1 days'));
	}
}

//This method is used to create the request to fetch the updateDataPackage details. We set the date, service method to be called and set it as a session variable.
function updateTotalDataPackagesInputData($beginDate, $endDate, $quarter , $site) {
	global $pastaURL;

	$GLOBALS ['updateDataPackages1'] = 0;
	$GLOBALS ['updateDataPackages2'] = 0;
	$GLOBALS ['updateDataPackages3'] = 0;
	$GLOBALS ['updateDataPackages4'] = 0;
	$GLOBALS ['updateDataPackages0'] = 0;

	$begin = $beginDate;
	$begin_time = strtotime($beginDate);
	$end_time = strtotime($endDate);

	// Flag is to check 1st call. If 1st call, then fetch all data from start of time to the beginDate.
	$flag = 1;
	// Comparing the end of quarter with end_date
	while(strtotime($begin) < strtotime($endDate)){
		$begin_time = strtotime($begin);
		$end_month = ((int) (((date('m', $begin_time) - 1) / 3)) + 1) * 3;
		$end_time = strtotime(date('Y', $begin_time) . "-" . $end_month . "-15");

		if(strtotime($endDate) < $end_time)
			$end = $endDate;
		else
			$end = date("Y-m-t", $end_time);

		if($flag){
			$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&&toTime=" . $end; # if we want the total, we need everything
			$flag = 0;
		}
		else {
			$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $begin . "&toTime=" . $end;
		}

		callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "updateDataPackages");
		if (isset ( $GLOBALS ['updateDataPackages'] ) && $GLOBALS ['updateDataPackages'] != null){
			updateDataPackagesOutput ( $GLOBALS ['updateDataPackages'], $quarter, $site );
			$GLOBALS ['updateDataPackages'] = -1;
		}
		$begin = date('Y-m-d', strtotime($end. ' + 1 days'));
	}
}

//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function createTotalDataPackagesOutput($xmlData, $quarter,$deleteCount,$site) {
	$responseXML = new SimpleXMLElement( $xmlData);

	require_once('countPackagesInEachQuarter.php');
	$count = countPackages( $quarter, $responseXML, $site);

	for($i= 0 ;$i< 5; $i++){
		$finalCount[$i] = $count [$i] - $deleteCount[$i];
	}

	$GLOBALS ['totalDataPackages0'] += $finalCount['0'] ;
	$GLOBALS ['totalDataPackages1'] += $finalCount['0'] + $finalCount['1'];
	$GLOBALS ['totalDataPackages2'] += $finalCount['0'] + $finalCount['1'] + $finalCount['2'];
	$GLOBALS ['totalDataPackages3'] += $finalCount['0'] + $finalCount['1'] + $finalCount['2'] + $finalCount['3'];
	$GLOBALS ['totalDataPackages4'] += $finalCount['0'] + $finalCount['1'] + $finalCount['2'] + $finalCount['3']+ $finalCount['4'];

	$GLOBALS ['totalDataPackagesCurrentQ'] += $finalCount ['4'];
	$GLOBALS ['totalDataPackagesLastQ'] += $finalCount ['3'];
	$GLOBALS ['totalDataPackages12Month'] += $finalCount ['1'] + $finalCount ['2'] + $finalCount ['3'] + $finalCount ['4'];
}

//Once we have the response from PASTA, we need to count the number of packages present and set those values which will be used to plot the graph.
function updateDataPackagesOutput($xmlData, $quarter, $site) {
	$responseXML = new SimpleXMLElement( $xmlData);

	require_once('countPackagesInEachQuarter.php');
	$count = countPackages( $quarter, $responseXML, $site);

	$GLOBALS ['updateDataPackages1'] += $count ['1'];
	$GLOBALS ['updateDataPackages2'] += $count ['2'];
	$GLOBALS ['updateDataPackages3'] += $count ['3'];
	$GLOBALS ['updateDataPackages4'] += $count ['4'];
	$GLOBALS ['updateDataPackages0'] += $count ['0'];
}

//This method is used to populate the network statistics table. This method is a handler class to all the necessary data.
function countDataPackagesForYearAgo($quarter, $endDate, $site){
	countCreateDataPackagesAYearAgo($endDate, $site);
	countUpdateDataPackagesAYearAgoQuarter($quarter, $site);
	countCreateDataPackagesAYearAgoQuarter($quarter, $site);
}
//This method is used to count the total number of packages upto a year ago.
//Since creating also comes with deletion, we count that as well and then displayed the aggregated number
function countCreateDataPackagesAYearAgo($endDate,$site){
	global $pastaURL;
	$month = (substr($endDate,5,2));
	$newEndDate = (date("Y") -1)."-".$month."-".(cal_days_in_month(CAL_GREGORIAN, $month,(date("Y")-1)));

	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&toTime=" . $newEndDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);
	$responseXML = new SimpleXMLElement( $xmlData);

	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&toTime=" . $newEndDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);

	$deleteResponseXML = new SimpleXMLElement( $xmlData);
	$GLOBALS ['totalCreateDataPackageAYearAgo'] = countTotalPackages($responseXML,$site) - countTotalPackages($deleteResponseXML,$site);
}

//Count the total number of update/revisions for the same quarter but a year ago.
function countUpdateDataPackagesAYearAgoQuarter($quarter,$site){
	global $pastaURL;

	if(strpos($GLOBALS ['quarterTitle']['4'],"-4")!== FALSE){
		$endDate =(date("Y")-1)."-12-".cal_days_in_month(CAL_GREGORIAN, 12,(date("Y")-1));
		$beginDate =(date("Y")-1)."-10-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-3")!== FALSE){
		$endDate =(date("Y")-1)."-09-".cal_days_in_month(CAL_GREGORIAN, 9,(date("Y")-1));
		$beginDate =(date("Y")-1)."-07-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-2")!== FALSE){
		$endDate =(date("Y")-1)."-06-".cal_days_in_month(CAL_GREGORIAN, 6,(date("Y")-1));
		$beginDate =(date("Y")-1)."-04-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-1")!== FALSE){
		$endDate =(date("Y")-1)."-03-".cal_days_in_month(CAL_GREGORIAN, 3,(date("Y")-1));
		$beginDate =(date("Y")-1)."-01-01";
	}


	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&toTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);
	$GLOBALS ['totalUpdateDataPackageAYearAgo'] = countTotalPackages($responseXML,$site);
}
//Count the total number of create and deletes for the same quarter but a year ago.
function countCreateDataPackagesAYearAgoQuarter($quarter,$site){
	global $pastaURL;

	if(strpos($GLOBALS ['quarterTitle']['4'],"-4")!== FALSE){
		$endDate =(date("Y")-1)."-12-".cal_days_in_month(CAL_GREGORIAN, 12,(date("Y")-1));
		$beginDate =(date("Y")-1)."-10-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-3")!== FALSE){
		$endDate =(date("Y")-1)."-09-".cal_days_in_month(CAL_GREGORIAN, 9,(date("Y")-1));
		$beginDate =(date("Y")-1)."-07-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-2")!== FALSE){
		$endDate =(date("Y")-1)."-06-".cal_days_in_month(CAL_GREGORIAN, 6,(date("Y")-1));
		$beginDate =(date("Y")-1)."-04-01";
	}

	if(strpos($GLOBALS ['quarterTitle']['4'],"-1")!== FALSE){
		$endDate =(date("Y")-1)."-03-".cal_days_in_month(CAL_GREGORIAN, 3,(date("Y")-1));
		$beginDate =(date("Y")-1)."-01-01";
	}

	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);

	$deleteResponseXML = new SimpleXMLElement( $xmlData);
	$GLOBALS ['totalDataPackagesAyear'] = countTotalPackages($responseXML,$site) - countTotalPackages($deleteResponseXML,$site);
}

//Since we are calcualting the createDataPackage, we also need to take care of the number of packages deleted in the same quarter. The total created pacakges will be create - delete of the package.
function countDeletedPackages($beginDate, $endDate,$quarter, $site){
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = callAuditReportTool( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);

	require_once('countPackagesInEachQuarter.php');
	$deleteCount = countPackages( $quarter, $responseXML, $site);

	return $deleteCount;
}
?>
