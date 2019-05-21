<script
  src="https://code.jquery.com/jquery-1.12.4.js"
  integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
  crossorigin="anonymous"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>

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

$report = new PluginReportsAutoReport(__('applicationsbylocation_report_title_custom', 'reports'));
/*
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
*/
?>

<select class = "names-select2" name="selected[]" ></select>
<input type = "button" id = "findByName" value = "Задать имя для поиска">
<div id = "versionsGroup">
     <p>Укажите версию</p>
     <select id = "versions-select2" ></select>
</div>
<script>

$("#versionsGroup").hide();

$(".names-select2").select2({
    minimumInputLength: 1,
    allowClear: true,
    placeholder: "—",
    language: 'ru',
    width: '50%',
    ajax: {
       url: "ajax-test-source.php", // адрес бэкэн-обработчика (url)
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,
       // что будем отправлять на сервер в запросе
       data: function (obj) {
           //console.log(obj.term);
          window.inputtedData = obj.term;
          //console.log(inputtedData);
           /* obj.term --то что ввёл пользователь, 
            * но вы можете и обработать это ввод пред тем 
            * как отправлять на сервер, 
            * может добавитьдоп. парамерты */
 
           return {
                    'action'       : 'getNamesForSelect',
                    'selectedName' : obj.term
                    };
       },

       processResults: function (data, params) { 
           //console.log(data);

        return data;
       }
    }
});

$(".names-select2").on("change", function(){
     window.selectedSoftId = $(".names-select2").select2('val');
     //console.log($('.names-select2 :selected').text());
     $.ajax({
          url: "ajax-test-source.php",
          type: "GET",
          dataType: "json",
          data:{
               'action'  : 'setSoftId',
               'id'      : window.selectedSoftId
          },
          success: function (data) {
               //console.log(data);
               window.generatedSQL = data.sql;
          }
     });

     $("#versionsGroup").show();


});

$("#versions-select2").select2({
    //minimumInputLength: 1,
    allowClear: true,
    placeholder: "—",
    language: 'ru',
    width: '50%',
    ajax: {
       url: "ajax-test-source.php", // адрес бэкэн-обработчика (url)
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,
       // что будем отправлять на сервер в запросе
       data: function (obj) {
           //console.log(obj.term);
          //window.inputtedData = obj.term;
          //console.log(inputtedData);
           /* obj.term --то что ввёл пользователь, 
            * но вы можете и обработать это ввод пред тем 
            * как отправлять на сервер, 
            * может добавитьдоп. парамерты */
 
           return {
                    'action'            : 'getVersionsForSelect',
                    'selectedSoftId'            : window.selectedSoftId
                    //'selectedVersion'   : obj.term
                    };
       },

          processResults: function (data, params) {
               window.generatedSQL = data.sql;
           //console.log(data);
               return data;
       }
    }
});

$("#versions-select2").on("change", function(){
     window.selectedVersionId = $("#versions-select2").select2('val');
     //console.log(window.selectedVersionId);
     
     $.ajax({
          url: "ajax-test-source.php",
          type: "GET",
          dataType: "json",
          data:{
               'action'  : 'setVersionId',
               'versionId'      : window.selectedVersionId
          },
          success: function (data) {
               //console.log(data);
               window.generatedSQL = data.sql;
          }
     });



});

$("#findByName").on("click", function() {
     console.log(window.inputtedData);
     $.ajax({
          url: "ajax-test-source.php",
          type: "GET",
          dataType: "json",
          data:{
               'action'  : 'setSoftName',
               'softName': window.inputtedData
          },
          success: function (data) {
               //console.log(data);
               window.generatedSQL = data.sql;
          }
     });
});


console.log(window.generatedSQL);



</script>



