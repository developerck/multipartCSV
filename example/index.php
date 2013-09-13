<?php
/**
 * In this file ,i ll explain each method of class that can be access.
 *
 */
include_once '../class/multiPartCSV.php';
?>

<?php
//-settig properties.if you will not set it wil use default
$filename = 'sample.csv'; // zip name will be filename
$nol = 2;  // number of line per csv
// if you specify header and footer will be included with each generated csv
$header = array(
                "first"
                ,"second"
                ,"third"
        );
$footer = array(
                "footer"
                ,"footer"
                ,"footer"
        );
$path = ""; // where you wanna store zip file , defualt wil be zip.

//------ making a object
$multipartCSV = new MultipartCSV($filename, $nol, $header, $footer, $path);

$data = array(array(1,2,3),2,3,4,5,6,7,8,9);


//--- write data in one csv only
//-function 1
//$multipartCSV->dataAtOnceInOneCSV($data ,true);//$zip true
//-function 2 // break down data and write multiple csv, function should be used once.
//$multipartCSV->dataAtOnceInParts($data ,true);//$zip true
//--function -3
//--- data per csv 
//$multipartCSV->dataPerCSV($data);
//$multipartCSV->dataPerCSV($data);
//$multipartCSV->dataPerCSV($data);
//$multipartCSV->makeZip(); return all dile in a zip on specified path

//--function -4
// insert data one by one and auto manage tp parts,e-g. it will create 3 csv
// b/c no of limit per csv is 2 line
//$multipartCSV->oneByOne(array(array(1,2,3),5,6));
//$multipartCSV->oneByOne(array("a","s","d"));
//$multipartCSV->makeZip();


//---data foramt passed to these function should be, a 2-d array row by col
//<array(array(firstcol,secondcol,thirdcol),array(firstcol,secondcol,thirdcol))>
?>