<?php
//This is the main method that is used to count the xml records that is being passed as input. In all the computation we have to count the number of records in the response. 
//We also have to compute the results based on the quarters that we have identified and returns those counts.
function countPackages($quarter, $data,$site) {
  $result = array_fill(0,5,0); // array from [0..4] with 0's 
  $everything_else = 0;
	$currentYear = date("Y");
	
	$site = str_replace(' ', '', $site);
	foreach ( $data as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		$year = substr ( $record->entryTime, 0, 4 );
		
		//If we are generating report for all sites, then exclude ecotrends, if not count only site specific entries.
		if(($site == "AllSites") && (strpos($record->resourceId, "ecotrends") !== false))
			continue;
		if(($site != "AllSites") && (strpos($record->resourceId, $site) == false))
			continue;

		if (in_array ( $month, $quarter ['1'] ))
			$result[1] += 1;
		else if (in_array ( $month, $quarter ['2'] ))
			$result[2] += 1;
		else if (in_array ( $month, $quarter ['3'] ))
			$result[3] += 1;
		else if ((in_array ( $month, $quarter ['4'])) && ($currentYear == $year))
			$result[4] += 1;
		else if ((in_array ( $month, $quarter ['0']))  && ($currentYear != $year))
			$result[0] += 1;
    else
      $everything_else += 1;

	}
  # if theres other stuff that falls outside of this quater then its before and thus should be included in the total
  foreach($result as $value) {
    $value += $everything_else;
  }
  #echo "everything else:".$everything_else;
  return $result;
}

//This method is used to get the total count irrespective of dates in it.
function countTotalPackages($data,$site){
	
	$count = 0;
	$site = str_replace(' ', '', $site);
	foreach ( $data as $record ) {
		//If we are generating report for all sites, then exclude ecotrends, if not count only site specific entries.
		if(($site == "AllSites") && (strpos($record->resourceId, "ecotrends") !== false))
			continue;
		if(($site != "AllSites") && (strpos($record->resourceId, $site) == false))
			continue;
		$count++;
	}	
	
	return $count;
}

?>
