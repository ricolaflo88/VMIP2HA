
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/*VALUE*/
var prechauffage = "0";
var soufflage = "20";
var hydro = "8";
var solar = "18";
var valeurJours = 1;
var hashydro =0;
var prechauffageCalendar = 0;
var heuredebut = 0;
var heurefin = 24;

var selectedValue = "..";
var selectedAction = "..";
var selectedBoostVacance = "..";

$( document ).ready(function() {
  $('header').remove();
  changeSelectValue(); /*Permet de set les temperatures */
  $('.backgroundforJeedom').remove();
  changeAgenda();
  $('.boostSelect').click(function(e) {
    if($(this).data("value") == 1){
      selectedBoostVacance = '..';
      sendAction('boost',0);
    }else{
      selectedBoostVacance = 'boost';
      $('.ModalOnBoost').html('{{Minute(s)}}');
      $('#myModalBoost').css('display','block');
    }
    select('boostSelect', 'auto');
  });
  $('.vacanceSelect').click(function(e) {
    if($(this).data("value") == 1){
      selectedBoostVacance = '..';

      sendAction('vacances',0);
    }else{
      selectedBoostVacance = 'vacances';
      $('.ModalOnBoost').html('{{Jour(s)}}');

      $('#myModalBoost').css('display','block');
    }
    select('vacanceSelect', 'auto');
  });
  $('.calendarSelect').click(function(e) {
    if($(this).data("value") == 1){
      sendAction('agendaActive',0);
      $('.Calendar_panelVMI_Calendrier').hide();
    }else{
      sendAction('agendaActive',1);
      $('.Calendar_panelVMI_Calendrier').show();
    }
    select('calendarSelect', 'auto');
  });
  $('.surventilationSelect').click(function(e) {
    if($(this).data("value") == 1){
      sendAction('surventilation',0);
    }else{
      sendAction('surventilation',1);
    }
    select('surventilationSelect', 'auto');
  });
  $('.debitSelect').click(function(e) {
    if($(this).data("value") == 1){
      sendAction('debit',0);
    }else{
      sendAction('debit',1);
    }
    select('debitSelect', 'auto');
  });
  $('.venti2Select').click(function(e) {
    sendAction('modefonc',1);
    select('venti2Select', 1);
    select('venti3Select',0);
    select('venti4Select',0);
  });
  $('.venti3Select').click(function(e) {
    sendAction('modefonc',2);
    select('venti3Select', 1);
    select('venti2Select',0);
    select('venti4Select',0);
  });
  $('.venti4Select').click(function(e) {
    sendAction('modefonc',3);
    select('venti4Select', 1);
    select('venti3Select',0);
    select('venti2Select',0);
  });
  $('.programmerClick').click(function(e) {
    changeAgenda(1);
  });
  $('.panelVMI_select_valeur_prechauffage').click(function(e) {
    selectValueBlue(prechauffage);
    ChoixTemperatureSelect('prechauffage');
    selectedAction = 'prechauffage';
    $('#myModal').css('display','block');
  });
  $('.panelVMI_select_valeur_soufflage').click(function(e) {
    selectValueBlue(soufflage);
    ChoixTemperatureSelect('soufflage');
    selectedAction = 'soufflage';
    $('#myModal').css('display','block');
  });
  $('.panelVMI_select_valeur_hydro').click(function(e) {
    selectValueBlue(hydro);
    ChoixTemperatureSelect('hydro');
    selectedAction = 'hydro';
    $('#myModal').css('display','block');
  });
  $('.panelVMI_select_valeur_solar').click(function(e) {
    selectValueBlue(solar);
    ChoixTemperatureSelect('solar');
    selectedAction = 'solar';
    $('#myModal').css('display','block');
  });
  $('.razfiltre').click(function(e) {
    $('#myModalRAZ').css('display','block');
  });
  $('#validRAZ').click(function() {
    sendAction('razfiltre',4);
    $('#myModalRAZ').css('display','none');
  });
  $('#cancelRAZ').click(function() {
    $('#myModalRAZ').css('display','none');
  });
});

function envoiJourBV(value){
  if(selectedBoostVacance == 'vacances'){
    sendAction('vacances',value);
  }else if(selectedBoostVacance == 'boost'){
    sendAction('boost',value);
  }
  $('#myModalBoost').css('display','none');
}

function ligneHideShow(type, display = 'hide'){
  /*Type possible :  solar, hydro, soufflage, prechauffage, bypass */
  if(display == 'hide'){
    $('.'+type+'ClassHide').hide();
  }else{
    $('.'+type+'ClassHide').show();
  }
}

function changeSelectValue(){
  if(prechauffage == '0'){
    $('.panelVMI_select_valeur_prechauffage .panelVMI_label_texte').html('OFF');
  }else{
    $('.panelVMI_select_valeur_prechauffage .panelVMI_label_texte').html(prechauffage+'°C');
  }
  if(prechauffageCalendar == '0'){
    $('.panelVMI_select_valeur_prechauffage_calendar .panelVMI_label_texte').html('OFF');
  }else{
    $('.panelVMI_select_valeur_prechauffage_calendar .panelVMI_label_texte').html(prechauffageCalendar+'°C');
  }
  $('.panelVMI_select_valeur_soufflage .panelVMI_label_texte').html(soufflage+'°C');
  $('.panelVMI_select_valeur_hydro .panelVMI_label_texte').html(hydro+'°C');
  $('.panelVMI_select_valeur_solar .panelVMI_label_texte').html(solar+'°C');
  $('.panelVMI_select_valeur_horaire_First .panelVMI_label_texte').html((heuredebut-1)+'h');
  $('.panelVMI_select_valeur_horaire_Two .panelVMI_label_texte').html((heurefin-1)+'h');
}

function ChoixTemperatureSelect(type,horaire = null){
  if(type == 'prechauffage'){
    $('#select_0').show();
    var minTemp = 12;
    var maxTemp = 20;
  }else if(type == 'soufflage'){
    $('#select_0').hide();
    var minTemp = 22;
    var maxTemp = 45;
  }else if(type == 'hydro'){
    $('#select_0').hide();
    var minTemp = 6;
    var maxTemp = 28;
  }else if(type == 'solar'){
    $('#select_0').hide();
    var minTemp = 20;
    var maxTemp = 28;
  }

  if(type == 'heuredebut' || type == 'heurefin'){
    if(horaire == null){
      var minTemp = 0;
      var maxTemp = 24;
    }else{
      var minTemp = horaire['minTemp']-1;
      var maxTemp = horaire['maxTemp']-1;
    }
    var n = 0;
    //console.log('n debut = '+n);
    while(n <= 45){
      //console.log('n > '+n);
      //console.log('compare // '+n+' >= '+minTemp+' && '+n+' <= '+maxTemp);
      if(n >= minTemp && n <= maxTemp){
        //console.log('show');
        $('#select_'+n).show();
        $('#select_'+n+ ' .modalTemperatureText').html(n+'h');
      }else{
        //console.log('hide');
        $('#select_'+n).hide();
      }
      n++;
    }
  }else{
    var n = 1;
    while(n <= 45){
      if(n >= minTemp && n <= maxTemp){
        $('#select_'+n).show();
        if(n == 1){
          $('#select_'+n+ ' .modalTemperatureText').html('OFF');
        }else{
          $('#select_'+n+ ' .modalTemperatureText').html(n+'°C');
        }
      }else{
        $('#select_'+n).hide();
      }
      n++;
    }
  }
}

function selectValueBlue(value){
  if(selectedValue != '..'){
    $('#select_'+selectedValue).removeClass('ligneSelect');
  }
  $('#select_'+value).addClass('ligneSelect');
  selectedValue = value;
}

function selectExec(value){
  if(selectedAction == 'prechauffage'){
    prechauffage = value;
  }else if(selectedAction == 'soufflage'){
    soufflage = value;
  }else if(selectedAction == 'hydro'){
    hydro = value;
  }else if(selectedAction == 'solar'){
    solar = value;
  }else if(selectedAction == 'prechauffageCalendar'){
    prechauffageCalendar = value;
    changeSelectValue();
    $('#myModal').css('display','none');
    return;
  }else if(selectedAction == 'heuredebut'){
    heuredebut = value+1;
    changeSelectValue();
    $('#myModal').css('display','none');
    return;
  }else if(selectedAction == 'heurefin'){
    heurefin = value+1;
    changeSelectValue();
    $('#myModal').css('display','none');
    return;
  }
  sendAction(selectedAction,value);
  changeSelectValue();
  $('#myModal').css('display','none');
}

function select(classType, value){
  var entree = value;
  if(value == 'auto'){
    if($('.'+classType).data("value") == 1){
      entree = 0;
    }else{
      entree = 1;
    }
  }
  changeSelect(classType, entree);
};

function changeSelect(classType, value){
  if(value == 1){
    $('.'+classType).data("value",1);
    $('.'+classType+' svg .activeSelect').hide();
    $('.'+classType+' svg .desactiveSelect').show();
  }else{
    $('.'+classType).data("value",0);
    $('.'+classType+' svg .activeSelect').show();
    $('.'+classType+' svg .desactiveSelect').hide();
  }
  if(classType == 'calendarSelect'){
    if(value == 1){
      $('.Calendar_panelVMI_Calendrier').show();
    }else{
      $('.Calendar_panelVMI_Calendrier').hide();
    }
  }
};

function changeAgenda(open = 0){
  if(open == 1){
    $('.panelVMI_bodyAlternatif').show();
    $('.panelVMI_body').hide();
  }else{
    $('.panelVMI_body').show();
    $('.panelVMI_bodyAlternatif').hide();
  }
}

function eventRefresh($id){
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
    data: {
      action: "eventRefresh",
      id : $id,
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      //console.log(data);
      if (data.result.result == 'ok') {
        updatepanel(data.result);
        //console.log('RESULTATTTTT > '+JSON.stringify(data.result));
      }
    }
  });
}

function sendAction($type,$value){
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
    data: {
      action: "sendAction",
      type : $type,
      value : $value,
    },
    dataType: 'json',
    async: true,
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });
}

function updatepanel(data){
  if (data.hydror != 1) {
    ligneHideShow('hydro','hide');
  } else {
    hashydro =1;
    ligneHideShow('hydro', 'show');
  }
  if (data.solarr != 1) {
    ligneHideShow('solar','hide');
  } else {
    ligneHideShow('solar', 'show');
  }
  if (data.booststate == 1) {
    select('boostSelect', 1);
  } else {
    select('boostSelect', 0);
  }
  if (data.vacancesstate == 1) {
    select('vacanceSelect', 1);
  } else {
    select('vacanceSelect', 0);
  }
  if (data.calendarstate == 1) {
    select('calendarSelect', 1);
  } else {
    select('calendarSelect', 0);
  }
  if (data.surventilationstate == 1) {
    select('surventilationSelect', 1);
  } else {
    select('surventilationSelect', 0);
  }
  if (data.debitstate == 1) {
    select('debitSelect', 1);
  } else {
    select('debitSelect', 0);
  }
  if (data.modefonc == 1) {
    select('venti2Select',1);
    $( "#venti2" ).removeClass( "buttonInactifPilot" ).addClass( "buttonActifPilot" );
    select('venti3Select',0);
    $( "#venti3" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti4Select',0);
    $( "#venti4" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
  } else if (data.modefonc == 2) {
    select('venti2Select',0);
    $( "#venti2" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti3Select',1);
    $( "#venti3" ).removeClass( "buttonInactifPilot" ).addClass( "buttonActifPilot" );
    select('venti4Select',0);
    $( "#venti4" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
  } else if (data.modefonc == 3) {
    select('venti2Select',0);
    $( "#venti2" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti3Select',0);
    $( "#venti3" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti4Select',1);
    $( "#venti4" ).removeClass( "buttonInactifPilot" ).addClass( "buttonActifPilot" );
  } else {
    select('venti2Select',0);
    $( "#venti2" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti3Select',0);
    $( "#venti3" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
    select('venti4Select',0);
    $( "#venti4" ).removeClass( "buttonActifPilot" ).addClass( "buttonInactifPilot" );
  }

  prechauffage=data.tempcelec;
  soufflage=data.tempcsoufflage;
  hydro=data.tempchydror;
  solar=data.tempcsolarr;
  changeSelectValue();
}

$('body').on('ventilairsec::modified', function (_event,_options) {
  eventRefresh(_options);
});



// Modal //
$( document ).ready(function() {
  // Get the modal
  var modal = document.getElementById("myModal");
  var modalBoost = document.getElementById("myModalBoost");
  eventRefresh('');

  window.onclick = function(event) {
    if (event.target == modal) {
      $('#myModal').css('display','none');
    }
    if (event.target == modalBoost) {
      if(selectedBoostVacance == 'boost'){
        select('boostSelect','auto');
      }else if(selectedBoostVacance == 'vacances'){
        select('vacanceSelect','auto');
      }
      selectedBoostVacance = '..';
      $('#myModalBoost').css('display','none');
    }
  }
});
