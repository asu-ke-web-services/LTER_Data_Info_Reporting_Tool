<?php
class MyDB extends SQLite3 {
	function __construct() {
		$this->open ( '../db/LTERSavedReports.db' );
	}
}
$db = new MyDB ();

session_start ();

$uniqueRecordID = false;

$reportID = NULL;
while ( ! $uniqueRecordID ) {
	$reportID = rand ( 1000, 999999 );
	$results = $db->query ( 'SELECT ID FROM saveLTERGeneratedReports' );
	$found = false;
	while ( $row = $results->fetchArray () ) {
		if ($row ['ID'] == $reportID) {
			$found = true;
			break;
		}
	}
	if (! $found)
		$uniqueRecordID = true;
}

$value1 = $_SESSION ['quarterTitle'] ['0'];
$value2 = $_SESSION ['quarterTitle'] ['1'];
$value3 = $_SESSION ['quarterTitle'] ['2'];
$value4 = $_SESSION ['quarterTitle'] ['3'];
$value5 = $_SESSION ['quarterTitle'] ['4'];
$value6 = $_SESSION ['totalDataPackages0'];
$value7 = $_SESSION ['totalDataPackages1'];
$value8 = $_SESSION ['totalDataPackages2'];
$value9 = $_SESSION ['totalDataPackages3'];
$value10 = $_SESSION ['totalDataPackages4'];
$value11 = $_SESSION ['dataPackageDownloads0'];
$value12 = $_SESSION ['dataPackageDownloads1'];
$value13 = $_SESSION ['dataPackageDownloads2'];
$value14 = $_SESSION ['dataPackageDownloads3'];
$value15 = $_SESSION ['dataPackageDownloads4'];
$value16 = $_SESSION ['dataPackageArchiveDownloads0'];
$value17 = $_SESSION ['dataPackageArchiveDownloads1'];
$value18 = $_SESSION ['dataPackageArchiveDownloads2'];
$value19 = $_SESSION ['dataPackageArchiveDownloads3'];
$value20 = $_SESSION ['dataPackageArchiveDownloads4'];
$value21 = $_SESSION ['CurrentQuarterDate'];
$value22 = $_SESSION ['PreviousQuarterDate'];
$value23 = $_SESSION ['totalDataPackagesCurrentQ'];
$value24 = $_SESSION ['totalDataPackagesLastQ'];
$value25 = $_SESSION ['totalDataPackagesAyear'];
$value26 = $_SESSION ['totalDataPackages12Month'];
$value27 = $_SESSION ['updateDataPackages1'];
$value28 = $_SESSION ['updateDataPackages2'];
$value29 = $_SESSION ['updateDataPackages3'];
$value30 = $_SESSION ['updateDataPackages4'];
$value31 = $_SESSION ['totalUpdateDataPackageAYearAgo'];
$value32 = $_SESSION ['AsOfCurrentQuarterDate'];
$value33 = $_SESSION ['AsOfPreviousQuarterDate'];
$value34 = $_SESSION ['AsOfPreviousYearDate'];
$value35 = $_SESSION ['totalCreateDataPackageAYearAgo'];
$value36 = $_SESSION ['site'];

$comment1 = $_POST ['comment1'];
$comment2 = $_POST ['comment2'];
$comment3 = $_POST ['comment3'];
$comment4 = $_POST ['comment4'];

date_default_timezone_set ( 'America/Phoenix' );
error_reporting ( E_ERROR | E_PARSE );
$value37 = date ( "D M j G:i:s T Y" );

$recordAlreadypresent = false;

$stmt = $db->prepare ( 'SELECT ID from saveLTERGeneratedReports where quarterTitle0=:quarterTitle0 and quarterTitle1=:quarterTitle1 and quarterTitle2=:quarterTitle2 and
		quarterTitle3=:quarterTitle3 and quarterTitle4=:quarterTitle4 and totalDataPackages0=:totalDataPackages0 and totalDataPackages1=:totalDataPackages1 and totalDataPackages2=:totalDataPackages2
		and totalDataPackages3=:totalDataPackages3 and totalDataPackages4=:totalDataPackages4 and dataPackageDownloads0=:dataPackageDownloads0 and dataPackageDownloads1=:dataPackageDownloads1
		and dataPackageDownloads2=:dataPackageDownloads2 and dataPackageDownloads3=:dataPackageDownloads3 and dataPackageDownloads4=:dataPackageDownloads4 and
		dataPackageArchiveDownloads0=:dataPackageArchiveDownloads0 and dataPackageArchiveDownloads1=:dataPackageArchiveDownloads1 and dataPackageArchiveDownloads2=:dataPackageArchiveDownloads2
		and dataPackageArchiveDownloads3=:dataPackageArchiveDownloads3 and dataPackageArchiveDownloads4=:dataPackageArchiveDownloads4 and CurrentQuarterDate=:CurrentQuarterDate
		and PreviousQuarterDate=:PreviousQuarterDate and totalDataPackagesCurrentQ =:totalDataPackagesCurrentQ and totalDataPackagesLastQ=:totalDataPackagesLastQ and totalDataPackagesAyear=:totalDataPackagesAyear
		and totalDataPackages12Month=:totalDataPackages12Month and updateDataPackages1=:updateDataPackages1 and updateDataPackages2=:updateDataPackages2 and updateDataPackages3=:updateDataPackages3
		and updateDataPackages4=:updateDataPackages4 and totalUpdateDataPackageAYearAgo=:totalUpdateDataPackageAYearAgo and AsOfCurrentQuarterDate=:AsOfCurrentQuarterDate
		and AsOfPreviousQuarterDate=:AsOfPreviousQuarterDate and AsOfPreviousYearDate=:AsOfPreviousYearDate and totalCreateDataPackageAYearAgo=:totalCreateDataPackageAYearAgo and site =:site' );

$stmt->bindValue ( ':quarterTitle0', $value1 );
$stmt->bindValue ( ':quarterTitle1', $value2 );
$stmt->bindValue ( ':quarterTitle2', $value3 );
$stmt->bindValue ( ':quarterTitle3', $value4 );
$stmt->bindValue ( ':quarterTitle4', $value5 );
$stmt->bindValue ( ':totalDataPackages0', $value6 );
$stmt->bindValue ( ':totalDataPackages1', $value7 );
$stmt->bindValue ( ':totalDataPackages2', $value8 );
$stmt->bindValue ( ':totalDataPackages3', $value9 );
$stmt->bindValue ( ':totalDataPackages4', $value10 );
$stmt->bindValue ( ':dataPackageDownloads0', $value11 );
$stmt->bindValue ( ':dataPackageDownloads1', $value12 );
$stmt->bindValue ( ':dataPackageDownloads2', $value13 );
$stmt->bindValue ( ':dataPackageDownloads3', $value14 );
$stmt->bindValue ( ':dataPackageDownloads4', $value15 );
$stmt->bindValue ( ':dataPackageArchiveDownloads0', $value16 );
$stmt->bindValue ( ':dataPackageArchiveDownloads1', $value17 );
$stmt->bindValue ( ':dataPackageArchiveDownloads2', $value18 );
$stmt->bindValue ( ':dataPackageArchiveDownloads3', $value19 );
$stmt->bindValue ( ':dataPackageArchiveDownloads4', $value20 );
$stmt->bindValue ( ':CurrentQuarterDate', $value21 );
$stmt->bindValue ( ':PreviousQuarterDate', $value22 );
$stmt->bindValue ( ':totalDataPackagesCurrentQ', $value23 );
$stmt->bindValue ( ':totalDataPackagesLastQ', $value24 );
$stmt->bindValue ( ':totalDataPackagesAyear', $value25 );
$stmt->bindValue ( ':totalDataPackages12Month', $value26 );
$stmt->bindValue ( ':updateDataPackages1', $value27 );
$stmt->bindValue ( ':updateDataPackages2', $value28 );
$stmt->bindValue ( ':updateDataPackages3', $value29 );
$stmt->bindValue ( ':updateDataPackages4', $value30 );
$stmt->bindValue ( ':totalUpdateDataPackageAYearAgo', $value31 );
$stmt->bindValue ( ':AsOfCurrentQuarterDate', $value32 );
$stmt->bindValue ( ':AsOfPreviousQuarterDate', $value33 );
$stmt->bindValue ( ':AsOfPreviousYearDate', $value34 );
$stmt->bindValue ( ':totalCreateDataPackageAYearAgo', $value35 );
$stmt->bindValue ( ':site', $value36 );

$result = $stmt->execute ();
$retrievedData = $result->fetchArray ();

if ($retrievedData ['ID'] == null)
	$recordAlreadypresent = false;
else {		
	$stmt = $db->prepare ( 'SELECT * FROM saveReportComments where reportID=:id' );
	$stmt->bindValue ( ':id', $retrievedData ['ID'], SQLITE3_INTEGER );
	$result = $stmt->execute ();
	$retrievedComments = $result->fetchArray ();
	if ($comment1 != $retrievedComments ['comment1'] || $comment2 != $retrievedComments ['comment2'] || $comment3 != $retrievedComments ['comment3'] || $comment4 != $retrievedComments ['comment4'])
	{
		$recordAlreadypresent = $retrievedData ['ID'];
		$stmt = $db->prepare ( 'UPDATE saveReportComments set comment1 =:comment1,comment2 =:comment2,comment3 =:comment3,comment4 =:comment4 where reportID=:id' );
		$stmt->bindValue ( ':comment1', $comment1);
		$stmt->bindValue ( ':comment2', $comment2);
		$stmt->bindValue ( ':comment3', $comment3);
		$stmt->bindValue ( ':comment4', $comment4);
		$stmt->bindValue ( ':id', $retrievedData ['ID'], SQLITE3_INTEGER );
		$stmt->execute ();
		echo "Updated-".$recordAlreadypresent;
	}
	else{
		$recordAlreadypresent = $retrievedData ['ID'];
		echo "Old-".$recordAlreadypresent;
	}
}

if (! $recordAlreadypresent) {
	
	if ($db->exec ( "INSERT INTO saveLTERGeneratedReports VALUES ($reportID,'$value1','$value2','$value3','$value4','$value5',$value6,$value7,$value8,$value9,$value10,$value11,$value12,$value13,$value14,$value15,
		$value16,$value17,$value18,$value19,$value20,'$value21','$value22',$value23,$value24,$value25,$value26,$value27,$value28,$value29,$value30,$value31,'$value32','$value33','$value34',$value35,'$value36','$value37')" ))
		echo "New-".$reportID;
	
	if (isset ( $_SESSION ['recentPackages'] )) {
		
		$data = $_SESSION ['recentPackages'];
		
		foreach ( $data as $value ) {
			
			$identifierLink = $value ['identifierLink'];
			$name = $value ['name'];
			$author = $value ['author'];
			$date = $value ['date'];
			$title = $value ['title'];
			
			$db->exec ( "INSERT INTO saveRecentPackages(reportID,identifierLink,name,author,date,title) VALUES ($reportID,'$identifierLink','$name','$author','$date','$title')" );
		}
	}
	
	$db->exec ( "INSERT INTO saveReportComments(reportID,comment1,comment2,comment3,comment4) VALUES ($reportID,'$comment1','$comment2','$comment3','$comment4')" );
}
$db->close ();
unset ( $db );

?>

