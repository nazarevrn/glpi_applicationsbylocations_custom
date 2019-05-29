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

 когда я начинал писать этот код, только Бог и я знали, как он работает.
 теперь не знает никто.
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
<!-- <br>
<span>
<input type = "button" id = "#show-export" value = "Показать варианты экспорта" style="width: 10%; display: none;">
</span> -->

<div id = "moreConditionsGroup" style = "display: none">
     <form id = "moreConditionsForm">
     </form>
     <input type = "button" id = "addCondition" value = "Добавить условие" style="width: 10%">
     <input type = "button" id = "deleteCondition" value = "Удалить условие" style="width: 10%; display: none">
     <br>
</div>
<div id = "versionsGroup">
     <p>Укажите версию</p>
     <select id = "versions-select2" ></select>
</div>
<div id = "findButtonDiv">
     <input type = "button" id = "findButton" value = "Поиск" style = "display: none">
     <input type = "button" id = "clearButton" value = "Очистить" style = "display: none">
</div>

<br>
<br>
<br>
<div>
     <p style = "text-align: center">
          <img id = "loading" src="images/loading.gif" width="200" height="200" style = "display: none">
     </p>
</div>

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

<script>

$("#versionsGroup").hide();

// $("#findButton").hide();

$(".names-select2").select2({
     tags: true,
     minimumInputLength: 1,
     allowClear: true,
     placeholder: "Имя ПО",
     language: 'ru',
     width: '40%',
     ajax: {
          url: "ajaxGetDataForSelect.php",
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
       url: "ajaxGetDataForSelect.php",
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,
       data: function (obj) {
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
     $("#clearButton").show();
});

$("#versions-select2").select2({
    allowClear: true,
    placeholder: "Версия ПО",
    language: 'ru',
    width: '40%',
    ajax: {
       url: "ajaxGetDataForSelect.php",
       delay: 250,
       type: "GET",
       dataType: "json",
       cache: true,

       data: function (obj) { 
           return {
                    'action'            : 'getVersionsForSelect',
                    'selectedSoftId'            : window.selectedSoftId
                    };
       },

          processResults: function (data, params) {
               $("#findButton").show();
               return data;
       }
    }
});


$("#versions-select2").on("change", function(){
     window.selectedVersionId = $("#versions-select2").select2('val');
     $("#findButton").show();
     $("#clearButton").show();
});

$("#findByName").on("click", function() {
     $("#findButton").show();
     $("#clearButton").show();
     window.inputtedData = $(".names-select2").select2('val');
     //тут дополнительные условия поиска
     $('#moreConditionsGroup').show();
});



function addCondition (number) {
     return '<select name = "selectCondition' + number + '"><option value = "IN">Содержит</option><option value = "NOT IN">Не содержит</option></select><input type = "text" name = "selectCondition' + number + 'Text"><br>';
}
$('#addCondition').on('click', function () {
     $('#deleteCondition').show();
     if (!window.addConditionNumber) {
          window.addConditionNumber = 0;
     }
     $('#moreConditionsForm').append(addCondition(++window.addConditionNumber));
     //window.addConditionNumber++;
});

$('#deleteCondition').on('click', function(){
     let selectorSelect = "[name = selectCondition" + window.addConditionNumber + "]";
     let selectorText = "[name = selectCondition" + window.addConditionNumber + "Text]";
     $(selectorSelect).remove();
     $(selectorText).remove();
     window.addConditionNumber--;
});

</script>

<script>
     // https://stackoverflow.com/questions/10915263/include-two-versions-of-jquery-on-a-page-without-affecting-old-plugins
    // Save original jQuery version to another variable
    var $Original = jQuery.noConflict(true);
</script>



<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jq-3.3.1/dt-1.10.18/b-1.5.6/datatables.css"/>
 
<script>
    // Save new jQuery version to another variable
    var $v3_3_1 = jQuery.noConflict(true);

    // Replace the original jquery version on $ and jQuery so pre-existing scripts don't break
    // No need to declare "var" here since the $ and jQuery still exist as "undefined"
    $ = $Original;
    jQuery = $Original;

    // Optional: Here I'm saving new jQuery version as a method of "$" -- it keeps it more organized (in my opinion)
    //$.v1_11_0 = $v1_11_0;

function addToData(data,params) {
     for (const key in params) {
          data.push({
               name: key,
               value: params[key]
          });
     }

     // it doesn't matter at all
     // this f*ckin' js already changed your data variable
     // pam-pam
     return data;
}

$("#findButton").on("click", function() {
     // console.log(window.inputtedData);
     // console.log(window.selectedSoftId);
     // console.log(window.selectedVersionId);
     // console.log(window.selectedLocationId);
     
     let data = new Array();
     if(window.addConditionNumber) {
          data = $('#moreConditionsForm').serializeArray();
     }

     if (window.getConditionData) {
          data = window.getConditionData;
     }

     let otherParams =  {
        "softName": window.inputtedData,
        "softId": window.selectedSoftId,
        "versionId": window.selectedVersionId,
        "locationId": window.selectedLocationId,
    };

     $.ajax({
          url: "ajaxGetDataForReport.php",
          type: "GET",
          dataType: "json",
          data: addToData(data,otherParams),
          beforeSend: function() {
               $("#table_id").hide();
               $('#loading').show();
          },
          success: function (data) {  
               $('#loading').hide();
               $("#table_id").show();             
               $v3_3_1("#table_id").dataTable({
                    destroy: true,
                    lengthMenu: [ 25, 50, 75, 100 ],
                    select: true,
                    //dom: 'Bfrtip',
                    dom: 'lfBrtip',
                    buttons: [
                              'copy', 'csv', 'excel', 'pdf'
                              ],
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
          }
     });
});

$("#clearButton").on("click", function() {
     $('#loading').hide();
     $("#table_id").hide();
     delete window.inputtedData;
     delete window.selectedSoftId;
     delete window.selectedVersionId;
     delete window.selectedLocationId;
     $(".names-select2").empty().trigger('change');
     $(".locations-select2").empty().trigger('change'); 
     $("#versions-select2").empty().trigger('change');
     $("#versionsGroup").hide();
     $("#table_id").hide();

});
//


     function getQueryParams(qs) {
          qs = qs.split("+").join(" ");
          var params = {},
               tokens,
               re = /[?&]?([^=]+)=([^&]*)/g;

          while (tokens = re.exec(qs)) {
               params[decodeURIComponent(tokens[1])]
                    = decodeURIComponent(tokens[2]);
          }

     return params;
     }

     var $_GET = getQueryParams(document.location.search);

     //Всё, что ниже нужно для того, что бы отчёт формировался при переходе по ссылке
     if ($_GET.softName) { 
          window.inputtedData = $_GET.softName; //проверка ввода есть на бэке
     }

     if ($_GET.softId) { 
          window.selectedSoftId = $_GET.softId;
     }

     if ($_GET.versionId) { 
          window.selectedVersionId = $_GET.versionId;
     }

     if ($_GET.locationId) { 
          window.selectedLocationId = $_GET.locationId;
     }

     if ($_GET.selectCondition1) {//заданы дополнительные условия для softName (будь они неладны)
          window.getConditionData = new Array();
          for (let condition in $_GET) {
               // condition
               // selectCondition1
               let attributeName = condition;
               let attributeText = $_GET[condition];
               
               console.log(attributeText);

               if (attributeName.indexOf('selectCondition') != -1) {
                    //console.log(attributeName, attributeText);

                    window.getConditionData.push(attributeName, attributeText);
               }

          }
     }

     if ($_GET.softName || $_GET.softId || $_GET.versionId || $_GET.locationId) {
          $('#findButton').trigger('click');
     }





</script>




