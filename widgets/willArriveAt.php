<?php
/*  Copyright 2008, 2009, 2010 Yeri "Tuinslak" Tiete (http://yeri.be), and others
    Copyright 2010 Pieter Colpaert (pieter@irail.be - http://bonsansnom.wordpress.com)

    This file is part of iRail.
 *
 * This is an example widget. You can reuse the code to make your own widgets.
*/

// National query page

chdir("../");

include_once("api/DataStructs/ConnectionRequest.php");

include("includes/apiLog.php");

include("api/OutputHandlers/ConnectionOutput.php");
class WidgetOutput extends ConnectionOutput{
    private $connection;
    private $name;
    public function __construct($connection, $name = "") {
        $this -> connection = $connection;
        $this -> name = $name;
    }

    public function printAll() {
        echo $this->name ." will arrive at " . $this-> connection -> getArrival() -> getStation() -> getName() . " in " . $this->calculateMinutes() . "minutes";
    }

    private function calculateMinutes(){
        //date_default_timezone_set("Europe/Brussels");
        //echo date("ymd - H:i",$this -> connection -> getArrival() -> getTime() + $this-> connection -> getDepart() -> getDelay());
        return floor(($this -> connection -> getArrival() -> getTime() + $this-> connection -> getDepart() -> getDelay() - date("U"))/60);
    }

}

$lang = "";
$timesel = "";
$name = "";
$language = "EN";
$date = "";
$time="";
extract($_COOKIE);
extract($_GET);
$lang = $language;
// if bad stations, go back
if(!isset($_GET["from"]) || !isset($_GET["to"]) || $from == $to) {
	header('Location: ..');
}
if(!isset($_POST["timesel"])){
    $timesel = "depart";
}
$results = 1;
$typeOfTransport = "train";
if($date == "") {
    $date = date("dmy");
}

//TODO: move this to constructor of ConnectionRequest

//reform date to needed train structure
preg_match("/(..)(..)(..)/si",$date, $m);
$date = "20" . $m[3] . $m[2] . $m[1];

if($time == "") {
    $time = date("Hi");
}

//reform time to wanted structure
preg_match("/(..)(..)/si",$time, $m);
$time = $m[1] . ":" . $m[2];


try {
    $request = new ConnectionRequest($from, $to, $time, $date, $timesel, $results, $lang, $typeOfTransport);
    $input = $request ->getInput();
    $connections = $input -> execute($request);
    $output = new WidgetOutput($connections[0], $name);
    $output -> printAll();

    // Log request to database
    writeLog("willArriveAtWidget - " . $_SERVER['HTTP_USER_AGENT'], $connections[0] -> getDepart() -> getStation() -> getName(), $connections[0] -> getArrival() -> getStation() -> getName(), "none (iRail.be)", $_SERVER['REMOTE_ADDR']);
}catch(Exception $e) {
    writeLog("willArriveAtWidget - " . $_SERVER['HTTP_USER_AGENT'],"", "", "Error on willArriveAtWidget: " . $e -> getMessage(), $_SERVER['REMOTE_ADDR']);
    //header('Location: ../noresults');
    echo $e->getMessage(); //error handling..
}

?>