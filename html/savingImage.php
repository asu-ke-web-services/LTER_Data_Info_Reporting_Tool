<?php
include "helpers.php";

$data = $_POST['data'];
$file = '../download/'.sanitizeFileName($_POST['file']).'.png';

$uri =  substr($data,strpos($data,",")+1);

// save to file
file_put_contents($file, base64_decode($uri));

// return the filename
echo json_encode($file);
?>
