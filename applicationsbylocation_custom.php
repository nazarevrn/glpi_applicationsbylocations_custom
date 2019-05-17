<?php
/**
 * @version $Id: applicationsbylocation.php 366 2018-09-13 09:24:43Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2018 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */
ini_set("display_errors", true);
error_reporting(~E_NOTICE);

// $memcache->connect('localhost', 11211) or die ("Не могу подключиться");

// $version = $memcache->getVersion();
// echo "Версия сервера: ".$version."<br/>\n";

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

$dbu = new DbUtils();

//TRANS: The name of the report = Applications by locations and versions
$report = new PluginReportsAutoReport(__('applicationsbylocation_report_title_custom', 'reports'));

$softwareName = new PluginReportsTextCriteria($report, '`s`.`name`', 'SoftwareName');


$report->displayCriteriasForm();


// Form validate and only one software with license
if ($report->criteriasValidated()) {


   //$report->setSubNameAuto();
   $inputtedName = mysql_real_escape_string ($softwareName->getParameterValue());

   $query = '  SELECT
                    s.name as softName, sv.name as versionName
               FROM 
                    glpi_softwares s
               LEFT JOIN glpi_softwareversions sv ON s.id = sv.softwares_id 
               WHERE s.name = \'' .  $inputtedName . '\'';


   $report->setSqlRequest($query);

   $report->execute();
}





