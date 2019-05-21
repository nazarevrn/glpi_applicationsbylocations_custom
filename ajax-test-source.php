<?php

include ("../../../../inc/includes.php");

function setFilterParams($param, $value) {

    switch ($param) {
        case 'setSoftId' :
            $sqlWhere = "s.id = $value";
            break;
        case 'setSoftName' :
            $sqlWhere = "s.name LIKE '%" . $value . "%'";
            break;
    }
    //$sqlWhere = "s.name LIKE '%" . $enteredName . "%'";



    $sql =  "
    
        SELECT
            us.id as userId,
            us.name as userName,
            pc.name as pcName,
            pc.id as pcId,
            l.name as locationName,
            pc_ver_2.softwareversions_id as versionId,
            pc_ver_2.date_install as installDate,
            soft_ver.name as versionName,
            soft2.name as softwareName,
            pc.`serial` as pcSerial
        FROM 
            glpi_computers pc
        INNER JOIN 
            glpi_users us ON us.name = pc.contact
        LEFT JOIN
            glpi_locations l ON l.id = us.locations_id
        LEFT JOIN
            glpi_computers_softwareversions pc_ver_2
        ON
            pc_ver_2.computers_id = pc.id AND pc_ver_2.softwareversions_id IN 
            (
                SELECT
                    sv.id as versionId
                FROM 
                    glpi_softwares s
                INNER JOIN
                    glpi_softwareversions sv ON sv.softwares_id = s.id
                WHERE $sqlWhere 
            )
                
        LEFT JOIN
            glpi_softwareversions soft_ver ON soft_ver.id = pc_ver_2.softwareversions_id
        LEFT JOIN
            glpi_softwares soft2 ON soft2.id IN 
                (
                SELECT 
                    softwares_id
                FROM
                    glpi_softwareversions
                WHERE
                    id = pc_ver_2.softwareversions_id
                )
        WHERE
            pc.id IN (
                SELECT 
                    pc_ver.computers_id
                FROM 
                    glpi_computers_softwareversions pc_ver
                WHERE
                    pc_ver.softwareversions_id 	IN (
                        SELECT
                            sv.id as versionId
                        FROM 
                            glpi_softwares s
                        INNER JOIN
                            glpi_softwareversions sv ON sv.softwares_id = s.id
                        WHERE $sqlWhere 
                        )
                AND
                pc_ver.is_deleted_computer != 1)
        GROUP BY
            pc.name
        ORDER BY
            pc.name	

    
    
    
    ";
    $json = [
                'sql' => $sql

    ];

    return json_encode($json);
}

switch ($_GET['action']) {
    case 'getNamesForSelect' :
        $selectedName = $DB->escape($_GET['selectedName']);
        $result = $DB->query("SELECT `id`, `name` FROM glpi_softwares WHERE `name` LIKE '%" . $selectedName . "%'");
        //print '<pre>';
        while ($data = $DB->fetch_assoc($result)) {
            // print '<pre>';
            // print_r($data['id']);
            // print_r($data['name']);
            // print '</pre>';
            $json['results'][] = [
                'text' => $data['name'] . ' ' . $data['id'],
                'id'    => $data['id']
            ];
        
        }
        
        echo json_encode($json);
        break;
    case 'getVersionsForSelect' :
        
        break;
    case 'setSoftId' :
        //echo 'softid ' . $_GET['id'];
        $selectedId = $DB->escape($_GET['id']);
        echo setFilterParams('setSoftId', $selectedId);
        break;

    case 'setSoftName':
        //echo 'softName ' . $_GET['softName'];
        $enteredName = $DB->escape($_GET['softName']);
        echo setFilterParams('setSoftName', $enteredName);
        break;
}

