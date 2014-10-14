<?php
//This method is used to create the request to fetch the createDataPackage details. This service is called to get all the details of the data pacakges created in the last 3 months.
function recentlyPublishedDataSetsInput($endDate) {
	global $pastaURL;
	
	$month = (substr($endDate,5,2))-3;
	$newBeginDate =date("Y")."-".$month."-".date("d") ;
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $newBeginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "recentlyCreatedDataPackages" );
}
//Once we have the data that we want, we then randomly pick 10 data packages from the list. With these data packages, we retrieve the package metadata and populate the last table.
function recentlyPublishedDataSets($xmlData, $site) {
	global $pastaURL;
	$responseXML = new SimpleXMLElement ( $xmlData );
	
	$site = str_replace(' ', '', $site);
	$j = 0;
	//Store the resource id of all the fetched data packages. If the package id contains ecotrends, ignore that data package.
	foreach ( $responseXML as $record ) {
		
		//If we are generating report for all sites, then exclude ecotrends, if not count only site specific entries.
		if(($site == "AllSites") && (strpos($record->resourceId, "ecotrends") !== false))
			continue;
		if(($site != "AllSites") && (strpos($record->resourceId, $site) == false))
			continue;
		
		$recentDataPackages [$j++] = substr ( $record->resourceId, 38 );
	}
	//There can be a situation where in no updates are present for that site in the last 3 months. If thats the case, then do not show the 4th table
	if(!isset($recentDataPackages)){
		if (isset ( $_SESSION ['recentPackages'] ))
			unset ( $_SESSION ['recentPackages'] );
		return;
	}
	//Randomly pick 10 data packages that will be shown on the webpage
	$randomNumbers = array();
	$size = (count($recentDataPackages) > 10 ? 10 : count($recentDataPackages));
	for($i = 0; $i < $size; $i++) {
		$randomValue = mt_rand ( 0, (count($recentDataPackages)-1));
		
		if(in_array($randomValue,$randomNumbers,true))
		{	
			$i--;
			continue;
		}
		else
			$randomNumbers [$i] = $randomValue;
	}
	sort ( $randomNumbers );
	
	//For every randomly picked data package, retrieve the metadata that contains information such as author, date and title
	
	$size = (count($recentDataPackages) > 10 ? 10 : count($recentDataPackages));
	for($i = 0; $i < $size; $i ++) {
		$url = $pastaURL . "package/metadata/eml/" . $recentDataPackages [$randomNumbers [$i]];
		$returnvalue = callAuditReportTool( $url, $_POST ['username'], $_POST ['password'] );
	  try {
      $XML = new SimpleXMLElement ( $returnvalue );
    } catch(Exception $e) {
      # TODO: this could request a datapackage that has been deleted from PASTA, which results in an xml parse error
      error_log('Execption Parsing XML from '.$url.' '.$e);
    }
		$authorName = "";
		$authorCount = 0;
		foreach ( $XML->dataset->creator as $name ) {
			if ($name->individualName != "") {
				if ($authorCount != 0)
					$tempName = ( string ) ", " . $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				else
					$tempName = ( string ) $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				$authorCount ++;
				$authorName = $authorName . $tempName;
			}
		}
		
		if ($authorName == null || $authorName == "" || $authorName == ",") {
			foreach ( $XML->dataset->creator as $name ) {
				if ($authorCount != 0)
					$tempName = ( string ) ", " . $name->organizationName;
				else
					$tempName = ( string ) $name->organizationName;
				$authorCount ++;
				$authorName = $authorName . $tempName;
			}
		}
		//Format the output to get a user friendly output
		$scope = strstr ( $recentDataPackages [$randomNumbers [$i]], '/', true );
		$identifier = strstr ( $recentDataPackages [$randomNumbers [$i]], '/' );
		$identifier = strstr ( substr ( $identifier, 1 ), '/', true );
		$temp = array (
				"name" => ( string ) str_replace ( "/", ".", $recentDataPackages [$randomNumbers [$i]] ),
				"title" => ( string ) $XML->dataset->title,
				"date" => ( string ) $XML->dataset->pubDate,
				"author" => ( string ) $authorName,
				"identifierLink" => ( string ) "https://portal.lternet.edu/nis/mapbrowse?scope=" . $scope . "&identifier=" . $identifier 
		);
		$packageDetails [$i] = $temp;
		$authorName = "";
	}
	
	$_SESSION ['recentPackages'] = $packageDetails;
}
?>
