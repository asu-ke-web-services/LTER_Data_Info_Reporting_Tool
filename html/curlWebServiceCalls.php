<?php 

//This method is used to make curl calls to PASTA to fetch the information. This method sets the returned value into a session variable.
function callAuditReportTool($url, $username, $password, $var_name_to_set=null) {
	$curl = curl_init ();
	// Optional Authentication:
	curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt ( $curl, CURLOPT_USERPWD, "uid=" . $username . ",o=LTER,dc=ecoinformatics,dc=org:" . $password );

	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );

	curl_setopt ( $curl, CURLOPT_FAILONERROR, true );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );

	$retValue = curl_exec ( $curl );
	curl_close ( $curl );
  if(!is_null($var_name_to_set )) {
    $GLOBALS [$var_name_to_set] = $retValue;
  }
  return $retValue;
}
?>
