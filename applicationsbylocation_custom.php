 <!-- <script
  src="https://code.jquery.com/jquery-1.12.4.js"
  integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
  crossorigin="anonymous"></script> -->
 <!-- <script type="text/javascript">
var jQuery_1_12_4 = $.noConflict(true);
</script>   -->


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

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

Html::header(__('histoinst_report_title', 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

$dbu = new DbUtils();

$report = new PluginReportsAutoReport(__('applicationsbylocation_report_title_custom', 'reports'));

// Html::header(__('histoinst_report_title', 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

// Report::title();

?>
<span>
<select class = "names-select2" name="selected[]" ></select>
<input type = "button" id = "findByName" value = "Задать имя для поиска" style="width: 10%">
<select class = "locations-select2" ></select>
</span>
<div id = "versionsGroup">
     <p>Укажите версию</p>
     <select id = "versions-select2" ></select>
</div>
<div id = "findButtonDiv">
     <input type = "button" id = "findButton" value = "Поиск" style = "display: none">
</div>


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.4/datatables.min.css"/>
 
 <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jqc-1.12.4/datatables.min.js"></script>

<table id="table_id" style = "display: none">
    <thead>
        <tr>
            <th>Имя ПО</th>
            <th>Версия ПО</th>
            <th>Имя ПК</th>
            <th>Серийный номер ПК</th>
            <th>Местоположение</th>
            <th>Пользователь</th>
            <th>Дата установки</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script> 


<link rel="stylesheet" type="text/css" href="dt/jquery.dataTables.min.css"/>
 
<script type="text/javascript" src="dt/jquery.dataTables.js"></script>


<script>

$("#versionsGroup").hide();

// $("#findButton").hide();

$(".names-select2").select2({
    minimumInputLength: 1,
    allowClear: true,
    placeholder: "Имя ПО",
    language: 'ru',
    width: '40%',
    ajax: {
       url: "ajax-test-source.php",
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,
       data: function (obj) {
          window.inputtedData = obj.term; 
           return {
                    'action'       : 'getNamesForSelect',
                    'selectedName' : obj.term
                    };
       },
       processResults: function (data, params) { 

        return data;
       }
    }
});

$(".names-select2").on("change", function(){
     window.selectedSoftId = $(".names-select2").select2('val');
     // $.ajax({
     //      url: "ajax-test-source.php",
     //      type: "GET",
     //      dataType: "json",
     //      data:{
     //           'action'              : 'setSoftId',
     //           'selectedSoftId'      : window.selectedSoftId
     //      },
     //      success: function (data) {
     //           window.generatedSQL = data.sql;
     //      }
     // });

     $("#versionsGroup").show();
     $("#findButton").show();


});


$(".locations-select2").select2({
    minimumInputLength: 1,
    allowClear: true,
    placeholder: "Местоположение",
    language: 'ru',
    width: '40%',
    ajax: {
       url: "ajax-test-source.php",
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,
       data: function (obj) {
          //window.inputtedData = obj.term; 
           return {
                    'action'       : 'getLocationsForSelect',
                    'selectedLocationName' : obj.term
                    };
       },
       processResults: function (data, params) { 

        return data;
       }
    }
});

$(".locations-select2").on("change", function(){
     window.selectedLocationId = $(".locations-select2").select2('val');
     $("#findButton").show();


});

$("#versions-select2").select2({
    //minimumInputLength: 1,
    allowClear: true,
    placeholder: "Версия ПО",
    language: 'ru',
    width: '50%',
    ajax: {
       url: "ajax-test-source.php",
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,

       data: function (obj) { 
           return {
                    'action'            : 'getVersionsForSelect',
                    'selectedSoftId'            : window.selectedSoftId
                    //'selectedVersion'   : obj.term
                    };
       },

          processResults: function (data, params) {
               window.generatedSQL = data.sql;
               $("#findButton").show();
               return data;
       }
    }
});

$("#versions-select2").on("change", function(){
     window.selectedVersionId = $("#versions-select2").select2('val');
     if (window.selectedVersionId !== null) {
     //      $.ajax({
     //      url: "ajax-test-source.php",
     //      type: "GET",
     //      dataType: "json",
     //      data:{
     //           'action'  : 'setVersionId',
     //           'versionId'      : window.selectedVersionId
     //      },
     //      success: function (data) {
     //           window.generatedSQL = data.sql;
     //           $("#findButton").show();
     //      }
     // });
     }
     $("#findButton").show();



});

$("#findByName").on("click", function() {
     //$(".names-select2").select2('val') = window.inputtedData;
     $.ajax({
          url: "ajax-test-source.php",
          type: "GET",
          dataType: "json",
          data:{
               'action'  : 'setSoftName',
               'softName': window.inputtedData
          },
          success: function (data) {
               window.generatedSQL = data.sql;
               $("#findButton").show();
          }
     });
});

//findButton

$("#findButton").on("click", function() {
     //console.log(window.generatedSQL);

     console.log(window.inputtedData);
     console.log(window.selectedSoftId);
     console.log(window.selectedVersionId);
     console.log(window.selectedLocationId);
     $.ajax({
          url: "ajaxSetParams.php",
          type: "GET",
          dataType: "json",
          data :
               {
               "softName"   : window.inputtedData,
               "softId"     : window.selectedSoftId,
               "versionId"  : window.selectedVersionId,
               "locationId" : window.selectedLocationId
               },

          success: function (data) {
               //table = $("#table_id").dataTable();
               //table.destroy();
               
               $("#table_id").dataTable({
                    destroy: true,
                    data:data,
                    columns: [
                         {data: 'softwareName'},
                         {data: 'versionName'},
                         {data: 'pcName'},
                         {data: 'pcSerial'},
                         {data: 'locationName'},
                         {data: 'userName'},
                         {data: 'installDate'},
                    ]
               });

               $("#table_id").show();
          }
     });
});


// var data = [
//     [
//         "Tiger Nixon",
//         "System Architect",
//         "Edinburgh",
//         "5421"
//     ],
//     [
//         "Garrett Winters",
//         "Director",
//         "Edinburgh",
//         "8422"
//     ]
// ]





</script>



