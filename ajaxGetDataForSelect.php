<?php

include ("../../../../inc/includes.php");

switch ($_GET['action']) {
    case 'getNamesForSelect' :
        $selectedName = $DB->escape($_GET['selectedName']);
        $result = $DB->query("SELECT `id`, `name` FROM glpi_softwares WHERE `name` LIKE '%" . $selectedName . "%'");

        while ($data = $DB->fetch_assoc($result)) {
            $json['results'][] = [
                'text' => $data['name'] . ' ' . $data['id'],
                'id'    => $data['id']
            ];
        
        }
        
        echo json_encode($json);
        break;
    case 'getVersionsForSelect' :
        $selectedId = $DB->escape($_GET['selectedSoftId']);        
        $result = $DB->query("SELECT `id`, `name` FROM glpi_softwareversions WHERE softwares_id = $selectedId");

        while ($data = $DB->fetch_assoc($result)) {

            $json['results'][] = [
                'text' => $data['name'],
                'id'    => $data['id']
            ];
        
        }
        echo json_encode($json);
        break;

    case 'getLocationsForSelect' :
        $selectedLocationName = $DB->escape($_GET['selectedLocationName']);
        $sql = "SELECT `id`, `name` FROM glpi_locations WHERE `name` LIKE '%" . $selectedLocationName . "%'";
        $result = $DB->query($sql);
        while ($data = $DB->fetch_assoc($result)) {

            $json['results'][] = [
                'text' => $data['name'],
                'id'    => $data['id']
            ];
        
        }
        echo json_encode($json);
        break;
}


