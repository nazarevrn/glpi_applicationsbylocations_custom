<?php

include ("../../../../inc/includes.php");

$softName = $DB->escape($_GET['softName']);
$softId = $DB->escape($_GET['softId']);
$versionId = $DB->escape($_GET['versionId']);
$locationId = $DB->escape($_GET['locationId']);

// if ( isset($softId) ) {
//     if ( isset($versionId) ) {
//         if ( isset($locationId) ) {
//             $sqlLocation = " AND l.id = $locationId";
//             $sqlVersion = "$versionId";
//         }
//     }
// }

if ( !empty($softName)) {
    $sqlSoft = "
    (
            SELECT
                sv.id as versionId
            FROM 
                glpi_softwares s
            INNER JOIN
                glpi_softwareversions sv ON sv.softwares_id = s.id
            WHERE s.name LIKE '%" . $softName . "%'
    )
    ";
} else {
    die;
}

if ( !empty($softId) ) {
    $sqlSoft = "
    ( 		            
        SELECT
            sv.id as versionId
        FROM 
            glpi_softwares s
        INNER JOIN
            glpi_softwareversions sv ON sv.softwares_id = s.id
        WHERE s.id = $softId
    )";
}

if ( !empty($versionId) ) {
    $sqlSoft = "($versionId)";
}

if ( !empty($locationId) ) {
    $sqlLocation = " AND l.id = $locationId";
}

$sql = "
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
        glpi_locations l ON l.id = us.locations_id " . $sqlLocation . "
    LEFT JOIN
        glpi_computers_softwareversions pc_ver_2
        ON
            pc_ver_2.computers_id = pc.id AND pc_ver_2.softwareversions_id IN " . $sqlSoft . "
        
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
        pc.id IN 
            (
                SELECT 
                    pc_ver.computers_id
                FROM 
                    glpi_computers_softwareversions pc_ver
                WHERE
                    pc_ver.softwareversions_id 	IN " . $sqlSoft . "
                AND
                pc_ver.is_deleted_computer != 1
            )
    GROUP BY
        pc.name
    ORDER BY
        pc.name	
";
print '<pre>';
print_r($sql);
print '</pre>';

$result = $DB->query($sql);

while ($data = $DB->fetch_assoc($result)) {
    print '<pre>';
    print_r($data);
    print '</pre>';


}