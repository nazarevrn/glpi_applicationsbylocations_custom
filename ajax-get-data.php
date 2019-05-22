<?php

include ("../../../../inc/includes.php");

$sql = str_replace('\r\n', '', $_GET['sqlQuery']); //чистимся от JS
$result = $DB->query($sql);

while ($data = $DB->fetch_assoc($result)) {
    //$data['versionName']

}

echo json_encode($json);