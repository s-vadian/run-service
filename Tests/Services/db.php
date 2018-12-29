<?php

define("BASE_NAME","test_app");
define("HOST_NAME","localhost:5432");
define("USER_NAME","postgres");
define("PASSWORD","postgres");
define("TYPE_BASE","postgres");

include_once("/paysys/dtcapp/external/adodb5/adodb.inc.php");

$db = NewADOConnection(TYPE_BASE);
$db->Connect(HOST_NAME, USER_NAME, PASSWORD, BASE_NAME);
$db->execute("SET client_encoding='win1251'");

if(!$db) die("Ошибка соединения с БД mCash");

//print_r($db);