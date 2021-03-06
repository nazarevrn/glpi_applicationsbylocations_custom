<?php

include ("../../../../inc/includes.php");

$softName = $DB->escape($_GET['softName']);
$softId = $DB->escape($_GET['softId']);
$versionId = $DB->escape($_GET['versionId']);
$locationId = $DB->escape($_GET['locationId']);

$extraNameConditions = [];
foreach (array_keys($_GET) as $key) {

    if (strpos($key, 'selectCondition') !== false && strlen($key) < 20) { // оствляем только ключи вида selectCondition* . Им соответствует условие IN или NOT IN
        $extraNameConditions[] = [$_GET[$key] => $_GET[$key . 'Text']];          // ключу вида selectCondition* соответствует ключ selectCondition*Text. А сам текст $_GET[selectCondition*Text]
    }
}

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
    )";
    
    if (!empty($extraNameConditions)) {
        /*
        если были заданы дополнительные параметры для имени ПО 
        вида
        Array
        (
            [0] => Array
                (
                    [NOT IN] => 123
                )

            [1] => Array
                (
                    [IN] => 321
                )

            [2] => Array
                (
                    [NOT IN] => 111
                )

        )
        */
        foreach ($extraNameConditions as $key => $oneConditionData) {
            /*
            array_keys($oneConditionData)[0] - условие IN или NOT IN
            array_shift($condition) - текст условия
            */

            $condition = array_keys($oneConditionData)[0];
            $conditionText = array_shift($oneConditionData);
            $valueSafe = $DB->escape($conditionText);

            if ($valueSafe) {
                if ($condition == 'IN') {
                    $addString = " AND s.name LIKE '%$valueSafe%' )";
                    //mb_strcut($sqlSoft, 0, strlen($sqlSoft) - 1) - убираем ) в конце $sqlSoft
                    $sqlSoft = mb_strcut($sqlSoft, 0, strlen($sqlSoft) - 1) . $addString;
                } elseif ($condition == 'NOT IN') {
                    $addString = " AND s.name NOT LIKE '%$valueSafe%' )";
                    $sqlSoft = mb_strcut($sqlSoft, 0, strlen($sqlSoft) - 1) . $addString;
                }
            }
            
        }
    }
    
} else {
    die;
}

if ( !empty($softId) && is_numeric($softId) ) {
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

if ( !empty($versionId) && is_numeric($locationId) ) {
    $sqlSoft = "($versionId)";
}

if ( !empty($locationId) && is_numeric($locationId) ) {
    $sqlLocation = " AND pc.locations_id = $locationId";
}

$sql = "
    SELECT
        soft2.name as softwareName,
        soft2.id as softwareId,
        soft_ver.name as versionName,
        pc_ver_2.softwareversions_id as versionId,
        pc.name as pcName,
        pc.id as pcId,
        pc.`serial` as pcSerial,
        l.name as locationName,
        us.name as userName,
        us.id as userId,
        pc_ver_2.date_install as installDate
    FROM 
        glpi_computers pc
    INNER JOIN 
        glpi_users us ON us.name = pc.contact
    LEFT JOIN
        glpi_locations l ON l.id = pc.locations_id 
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
        " . $sqlLocation . "
    GROUP BY
        pc.name
    ORDER BY
        pc.name	
";

$result = $DB->query($sql);
$prefix = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$linkForSoft    =   $prefix . '/front/software.form.php?id=';
$linkForVersion =   $prefix . '/front/softwareversion.form.php?id=';
$linkForPc      =   $prefix . '/front/computer.form.php?id=';
$linkforUser    =   $prefix . '/front/user.form.php?id=';

while ($data = $DB->fetch_assoc($result)) {

    $oneRow = [
        'softwareName' => '<a href = "' . $linkForSoft . $data['softwareId'] . '" target="_blank">' . $data['softwareName'] . '</a>',
        'versionName'  => '<a href = "' . $linkForVersion . $data['versionId'] . '" target="_blank">' . $data['versionName'] . '</a>',
        'pcName'       => '<a href = "' . $linkForPc . $data['pcId'] . '" target="_blank">' . $data['pcName'] . '</a>',
        'pcSerial'     => $data['pcSerial'], 
        'locationName' => $data['locationName'],
        'userName'     => '<a href = "' . $linkforUser . $data['userId'] . '" target="_blank">' . $data['userName'] . '</a>',
        'installDate'  => $data['installDate']
    ];
    $json[] = $oneRow; 
}

echo json_encode($json, JSON_UNESCAPED_SLASHES);
