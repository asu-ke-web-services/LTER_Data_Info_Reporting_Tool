<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2013 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2013 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/**
 * Error reporting
 */
	error_reporting ( E_ALL );
	ini_set ( 'display_errors', TRUE );
	ini_set ( 'display_startup_errors', TRUE );

	define ( 'EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />' );

	date_default_timezone_set ( 'Europe/London' );

	/**
	 * Include PHPExcel
	 */
	require_once dirname ( __FILE__ ) . '/../PHPExcel/PHPExcel.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel ();

	// Set document properties
	$objPHPExcel->getProperties ()->setCreator ( "Rohit Srikanta" )->setLastModifiedBy ( "Rohit Srikanta" )->setTitle ( "LTER Report" )->setSubject ( "LTER Report" )->setDescription ( "LTER Report" )->setKeywords ( "LTER Report" )->setCategory ( "LTER Report" );

	// Add some data, we will use printing features
	session_start ();

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(2), "Report for ".$GLOBALS ['site']);
	$objPHPExcel->getActiveSheet()->getStyle("C2")->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(48), "Network Summary Statistics" );

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(50), $GLOBALS['CurrentQuarterDate']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(50), $GLOBALS['PreviousQuarterDate']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(50), "A year Ago");
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'E' .(50), "Last 12 Months");

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(51), "Number of data packages published");
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(51), $GLOBALS['totalDataPackagesCurrentQ']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(51), $GLOBALS['totalDataPackagesLastQ']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(51), $GLOBALS['totalDataPackagesAyear']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'E' .(51), $GLOBALS['totalDataPackages12Month']);

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(52), "Number of data package updates/revisions");
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(52), $GLOBALS['updateDataPackages4']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(52), $GLOBALS['updateDataPackages3']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(52), $GLOBALS['totalUpdateDataPackageAYearAgo']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'E' .(52), ($GLOBALS['updateDataPackages1'] + $GLOBALS['updateDataPackages2'] + $GLOBALS['updateDataPackages3'] + $GLOBALS['updateDataPackages4']));


	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(54), "Current Quarter -".$GLOBALS['AsOfCurrentQuarterDate']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(54), "Previous Quarter - ".$GLOBALS['AsOfPreviousQuarterDate']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(54), "A year ago - ".$GLOBALS['AsOfPreviousYearDate']);

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(55), "Total number of published data packages");
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(55), $GLOBALS['totalDataPackages4']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(55), $GLOBALS['totalDataPackages3']);
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(55), $GLOBALS['totalCreateDataPackageAYearAgo']);

	$data = $GLOBALS ['recentPackages'];
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(58), "Selection of Recently Published Datasets (Last Three Months)" );

	$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .(60), "Data Package Identifier" );
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .(60), "Creators" );
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .(60), "Publication Date" );
	$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .(60), "Title" );

	for($i = 0; $i < 10; $i ++) {
		$objPHPExcel->getActiveSheet ()->setCellValue ( 'A' .($i+61), $data [$i] ['name'] );
		$objPHPExcel->getActiveSheet ()->setCellValue ( 'B' .($i+61), $data [$i] ['author'] );
		$objPHPExcel->getActiveSheet ()->setCellValue ( 'C' .($i+61), $data [$i] ['date'] );
		$objPHPExcel->getActiveSheet ()->setCellValue ( 'D' .($i+61), $data [$i] ['title'] );
	}

	$objPHPExcel->getActiveSheet ()->getHeaderFooter ()->setOddHeader ( '&L&G&C&HPlease treat this document as confidential!' );
	$objPHPExcel->getActiveSheet ()->getHeaderFooter ()->setOddFooter ( '&L&B' . $objPHPExcel->getProperties ()->getTitle () . '&RPage &P of &N' );

	// Set page orientation and size
	$objPHPExcel->getActiveSheet ()->getPageSetup ()->setOrientation ( PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE );
	$objPHPExcel->getActiveSheet ()->getPageSetup ()->setPaperSize ( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

	// Rename worksheet
	$objPHPExcel->getActiveSheet ()->setTitle ( 'Recently Created Data Packages' );

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex ( 0 );

	$sessionID = session_id();
	$fileName = "LTERReport".$sessionID.".xlsx";
	$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel2007' );
	$objWriter->save('../download/'.$fileName);

	require_once ('EmbedImageIntoCSV.php');
	embedImageIntoCSV('../download/1.png', 'B4' ,'../download/'.$fileName,$objPHPExcel);
	embedImageIntoCSV('../download/2.png', 'B25' ,'../download/'.$fileName,$objPHPExcel);
