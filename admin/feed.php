<?php

include_once("../functions.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if ($_POST['action'] == 'ALL') {


    response('ok', '', getAllFeed());

}

?>