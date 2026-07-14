
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

var debutSemaine = 2;
var finSemaine = 8;
var ouverture = 0;
var semaine = 'complet';
var calendarVMI = 0;
var vmiCalendar = 0;
var jourCalendar = 0;
var heureStartCalendar = 0;
var heureEndCalendar = 0;

var infodataSortie = [];
var idTableau = 0;
var infoDataModal = [];
var infodata = [];
var infoNumberTable = [];
var nombreDeTableau = 5;
var temperatureClass = 'None';

infoNumberTable['lundi'] = 0;
infoNumberTable['mardi'] = 0;
infoNumberTable['mercredi'] = 0;
infoNumberTable['jeudi'] = 0;
infoNumberTable['vendredi'] = 0;
infoNumberTable['samedi'] = 0;
infoNumberTable['dimanche'] = 0;

$( document ).ready(function() {
  //vueSemaine('fract','mardi'); // permet de morceler le calandrier
  vueSemaine('complet'); // permet de mettre le calandrier sur une semaine complete

  getAgendaData();

  if(infodata == ''){
    infodata = [
      {'id' : 1,'name': 'TEST','jour': 'lundi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 2,'name': 'TEST','jour': 'mardi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 4,'name': 'TEST','jour': 'mercredi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 7,'name': 'TEST','jour': 'jeudi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 8,'name': 'TEST','jour': 'vendredi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 9,'name': 'TEST','jour': 'samedi','start': 0,'end': 24, 'ventil': 0, 'temperature': 0},
      {'id' : 10,'name': 'TEST','jour': 'dimanche','start': 0,'end': 24, 'ventil': 0, 'temperature': 0}
    ];
  }

  //console.log(infodata);

  creationTableauGlobal(infodata); // creation le calendrier

  $('.Calendar_left_cross').click(function(){
    changeAgenda();
  });
  $('.Bloc_Jour').click(function(){
    $('#myModalCalendarDay').css('display','block');
    //vueSemaine('fract','mardi');
  });
  $('.DayMardi').click(function(){
    $('#myModalCalendarDay').css('display','none');
    vueSemaine('fract','mardi');
  });
  $('.DayMercredi').click(function(){
    $('#myModalCalendarDay').css('display','none');
    vueSemaine('fract','jeudi');
  });
  $('.DaySamedi').click(function(){
    $('#myModalCalendarDay').css('display','none');
    vueSemaine('fract','samedi');
  });
  $('.Bloc_Semaine').click(function(){
    vueSemaine('complet');
  });
  $('.panelVMI_select_valeur_prechauffage_calendar').click(function(e) {
    selectValueBlue(prechauffageCalendar);
	if (hashydro != 1) {
		ChoixTemperatureSelect('prechauffage');
	} else {
		ChoixTemperatureSelect('hydro');
	}
    selectedAction = 'prechauffageCalendar';
    $('#myModal').css('display','block');
  });

  $('.venti2SelectCalendar').click(function(e) {
		select('venti2SelectCalendar', 1);
		select('venti3SelectCalendar',0);
		select('venti4SelectCalendar',0);
    calendarVMI = 1;
    console.log('calendarVMI > '+calendarVMI);
  });
  $('.venti3SelectCalendar').click(function(e) {
    select('venti3SelectCalendar', 1);
		select('venti2SelectCalendar',0);
		select('venti4SelectCalendar',0);
    calendarVMI = 2;
    console.log('calendarVMI > '+calendarVMI);
  });
  $('.venti4SelectCalendar').click(function(e) {
    select('venti4SelectCalendar', 1);
		select('venti3SelectCalendar',0);
		select('venti2SelectCalendar',0);
    calendarVMI = 3;
    console.log('calendarVMI > '+calendarVMI);
  });

  $('.valval').click(function(e){
    // creation du tableau envoyer au back pour enregistrement.
    //console.log('calendarVMI > '+calendarVMI);
    var tableauCalendar = {};
    tableauCalendar.jour = jourCalendar.toLowerCase();
    tableauCalendar.start = heuredebut;
    tableauCalendar.end = heurefin;
    if(calendarVMI == 0){
      calendarVMI = 2;
    }
    tableauCalendar.ventil = calendarVMI;
    tableauCalendar.temperature = prechauffageCalendar;
    //console.log('Creation dun Drive > '+JSON.stringify(tableauCalendar));
	deleteDrive(tableauCalendar.jour,tableauCalendar.start);
    addDrive(tableauCalendar.jour,tableauCalendar.start,tableauCalendar.end,tableauCalendar.ventil,tableauCalendar.temperature);
    $('#myModalCalendar').css('display','none');
    $('.FloatAbsol').show();
    calendarVMI = 0;
  });

  $('.supsup').click(function(e){
    // creation du tableau envoyer au back pour suppression d'une balise.
    var tableauCalendar = {};
    tableauCalendar.jour = jourCalendar.toLowerCase();
    tableauCalendar.start = heuredebut;
    tableauCalendar.end = heurefin;

    deleteDrive(tableauCalendar.jour,tableauCalendar.start);
    $('#myModalCalendar').css('display','none');
    $('.FloatAbsol').show();
    calendarVMI = 0;
  });

  $('.syncAgenda').on('click', function(){
    sendAction('agenda',infodataSortie);
    $('.FloatAbsol').hide();
  });
});

function chargeSelectVMI(calendarVMI){
  if(calendarVMI == 0){
    calendarVMI = 2;
  }
  if(calendarVMI == 1){
		select('venti2SelectCalendar',1);
		select('venti3SelectCalendar',0);
		select('venti4SelectCalendar',0);
  }
  if(calendarVMI == 2){
    		select('venti3SelectCalendar',1);
		select('venti2SelectCalendar',0);
		select('venti4SelectCalendar',0);
  }
  if(calendarVMI == 3){
    select('venti4SelectCalendar', 1);
		select('venti3SelectCalendar',0);
		select('venti2SelectCalendar',0);
  }
}

function createModal(clickconfig){
  var day = clickconfig.style['grid-column'];
  var tableau = day.split(" ");
  prechauffageCalendar = $(clickconfig).attr('dataTemp');
  vmiCalendar = $(clickconfig).attr('datavmi');
  heureStartCalendar = clickconfig.style['grid-row-start'];
  heureEndCalendar = clickconfig.style['grid-row-end'];
  heuredebut = clickconfig.style['grid-row-start'];
  heurefin = clickconfig.style['grid-row-end'];
  if(vmiCalendar == 0){
    $('.supsup').hide();
    $('.vmiPanelHide').show();
    $('.valval').show();
  }else{
    $('.supsup').show();
    $('.valval').show();
    $('.vmiPanelHide').hide();
  }
  changeSelectValue();
  chargeSelectVMI(vmiCalendar);
  if(vmiCalendar == 0){
    var selectedFrist = '';
    var selectedTwo = '';
    let n = clickconfig.style['grid-row-start']-1;
    while (n <= clickconfig.style['grid-row-end']-1) {
      if(clickconfig.style['grid-row-start']-1 == n){
        selectedFrist = ' selected';
      }else{
        selectedFrist = '';
      }
      if(clickconfig.style['grid-row-end']-1 == n){
        selectedTwo = ' selected';
      }else{
        selectedTwo = '';
      }
      n++;
    }
  }else{
  }
  $('#myModalCalendar').css('display','block');
  jourCalendar =  nbrDay(tableau[0],1);
  
  var horaire = [];
  horaire['minTemp'] = heureStartCalendar;
  horaire['maxTemp'] = heureEndCalendar;

  $('.panelVMI_select_valeur_horaire_First').click(function(e) {
    selectValueBlue(heuredebut-1);
	  ChoixTemperatureSelect('heuredebut',horaire);
    selectedAction = 'heuredebut';
    $('#myModal').css('display','block');
  });

  $('.panelVMI_select_valeur_horaire_Two').click(function(e) {
    selectValueBlue(heurefin-1);
	  ChoixTemperatureSelect('heurefin',horaire);
    selectedAction = 'heurefin';
    $('#myModal').css('display','block');
  });
}

function creationTableauGlobal(infoValeurData){
  vueSemaine('complet');
  infoNumberTable['lundi'] = 0;
  infoNumberTable['mardi'] = 0;
  infoNumberTable['mercredi'] = 0;
  infoNumberTable['jeudi'] = 0;
  infoNumberTable['vendredi'] = 0;
  infoNumberTable['samedi'] = 0;
  infoNumberTable['dimanche'] = 0;
  $('#calendrierParent_ID').empty();
  $('#calendrierParent_ID').innerHTML = "";
  //infodata = infoValeurData;
  infodataSortie = [];
  createHoraire();

  infoValeurData.forEach((item, index) => {
    createGrip(item.id,item.name,item.jour,item.start,item.end,item.ventil,item.temperature);
  });

  var tableauComplet = [];

  for (var day = 2; day <= 8; day++){
    var dayName = nbrDay(day,1);
    tableauComplet[day] = [];
    for (var i = 0; i <= 24; i++) {
      infoValeurData.forEach((item, index) => {
        if(dayName.toLowerCase() == item.jour){
          if(item.start <= i && item.end > i){
            tableauComplet[day][i] = 1;
          }else{
            if(tableauComplet[day][i] != 1){
              tableauComplet[day][i] = 0;
            }
          }
        }
      });

    }
    var start = 0;
    var variablePassation = 0;
    for (var i = 0; i <= 24; i++) {
      if(i == 0 && tableauComplet[day][i] == 0){
        start = i;
        variablePassation = 0;
      }else if (i == 0 && tableauComplet[day][i] == 1) {
        variablePassation = 1;
      }else{
        if(variablePassation != tableauComplet[day][i]){
          if(tableauComplet[day][i] == 0){
            start = i;
            variablePassation = 0;
          }else if(!isset(tableauComplet[day][i])){
            // Fix
          }else{
            createGrip(0,'new0',dayName.toLowerCase(),start,(i),0,0);
            start = i+1;
            variablePassation = 1;
          }
        }
      }
      if(i == 24 && variablePassation == 0){
        createGrip(0,'new1',dayName.toLowerCase(),start,(i),0,0);
      }
    }
  }

  $('.clickOnCalendar').click(function(){
    createModal(this);
  });
}

function nbrDay(day,returnFunction = 0){
  if('Lundi' == day || 'lundi' == day || 2 == day){
    if(returnFunction == 0){
      return 2;
    }else if (returnFunction == 2) {
      return 'LUN.'
    }else{
      return 'Lundi';
    }
  }
  if('Mardi' == day || 'mardi' == day || 3 == day){
    if(returnFunction == 0){
      return 3;
    }else if (returnFunction == 2) {
      return 'MAR.'
    }else{
      return 'Mardi';
    }
  }
  if('Mercredi' == day || 'mercredi' == day || 4 == day){
    if(returnFunction == 0){
      return 4;
    }else if (returnFunction == 2) {
      return 'MER.'
    }else{
      return 'Mercredi';
    }
  }
  if('Jeudi' == day || 'jeudi' == day || 5 == day){
    if(returnFunction == 0){
      return 5;
    }else if (returnFunction == 2) {
      return 'JEU.'
    }else{
      return 'Jeudi';
    }
  }
  if('Vendredi' == day || 'vendredi' == day || 6 == day){
    if(returnFunction == 0){
      return 6;
    }else if (returnFunction == 2) {
      return 'VEN.'
    }else{
      return 'Vendredi';
    }
  }
  if('Samedi' == day || 'samedi' == day || 7 == day){
    if(returnFunction == 0){
      return 7;
    }else if (returnFunction == 2) {
      return 'SAM.'
    }else{
      return 'Samedi';
    }
  }
  if('Dimanche' == day || 'dimanche' == day || 8 == day){
    if(returnFunction == 0){
      return 8;
    }else if (returnFunction == 2) {
      return 'DIM.'
    }else{
      return 'Dimanche';
    }
  }
}

function createGrip(id,name,day,start,end,vmi,temperature){
  if(start < 23){
    var nbrday = nbrDay(day);
    if(nbrday >= debutSemaine && nbrday <= finSemaine){
      var style = 'grid-column: '+(nbrday - ouverture)+'; grid-row-start: '+(start+1)+'; grid-row-end: '+(end+1)+';';
      var html = '<div id="'+vmi+'" style="'+style+'" class="case_'+vmi+' clickOnCalendar day'+nbrday+'" dataVmi="'+vmi+'" dataTemp="'+temperature+'">'+inDiv(vmi,temperature,nbrday)+'</div>';
      var htmlCalendrier = $('#calendrierParent_ID').html();

      $('#calendrierParent_ID').html(htmlCalendrier+' '+ html);
      if(!in_array(day,start,infodataSortie)){
        infodataSortie.push({'id' : parseInt(id),'name': name,'jour': day,'start': parseInt(start),'end': parseInt(end), 'ventil': vmi, 'temperature': parseInt(temperature)});
        infoNumberTable[day] = infoNumberTable[day]+1;
        //console.log('Tableau Number > '+JSON.stringify(infoNumberTable['mercredi']))
        if(nombreDeTableau < infoNumberTable[day]){
          setTimeout(function(){$('.FloatAbsol').hide();}, 100);
          alert('{{Attention vous avez depassez le nombre de changements par jour qui est de}} '+nombreDeTableau+' {{Maximum. Merci}}.');
        }
      }
    }
  }
}

function addDrive(day,start,end,vmi,temperature){
  //console.log('lancement du add drive');
  var infoTableau = clearZeroDay(day,infodataSortie);
  //console.log('infoTableau > '+JSON.stringify(infoTableau));
  //console.log('clear');
  infoTableau.push({'id' : 0,'name': 'ADD','jour': day,'start': start-1,'end': end-1, 'ventil': vmi, 'temperature': temperature});
  //console.log('Nouveau tableau > '+JSON.stringify(infoTableau));
  infodataSortie = [];
  //console.log('Creation de l\'agenda');
  creationTableauGlobal(infoTableau);
}

function deleteDrive(day,start){
  var infoTableau = clearZeroDay(day,infodataSortie);
  for(i=0; i<infoTableau.length; i++){
    if(infoTableau[i]['jour'] == day && infoTableau[i]['start'] == start-1){
      infoTableau.splice(i, 1);
    }
  }
  infodataSortie = [];
  creationTableauGlobal(infoTableau);
}

function clearZeroDay(day,tableau){
  for(i=0; i<=tableau.length; i++){
    if(tableau[i]){
      if(tableau[i]['jour'] == day && tableau[i]['ventil'] == 0){
        tableau.splice(i, 1);
      }
    }
  }
  return tableau;
}

function in_array(day, start, array){
    var result = false;
    for(i=0; i<array.length; i++){
      if(array[i]['jour'] == day && array[i]['start'] == start){
          return true;
      }
    }
    return result;
}

function inDiv(vmi,temperature,nbrday){
  var html = '';
  if(vmi == 0){
    html += '<span class="icone iconePlus plusday'+nbrday+'"> <i class="fas fa-plus-circle"></i> </span>';
  }else if(vmi > 0){
    var venti = $('#venti'+vmi+'IconeHidden').html();
    html += '<div class="icone">'+ venti;
    html += '<span class="temperatureClass" style="display:'+ temperatureClass +'"> '+ temperature +'° </span>';
    html +=  '</div>';
  }
  return html;
}

function traductionGrid(day){
	if(day == 'LUN.'){
    	return '{{LUN.}}';
    }
  	if(day == 'Lundi'){
    	return '{{Lundi}}';
    }
  	if(day == 'MAR.'){
    	return '{{MAR.}}';
    }
  	if(day == 'Mardi'){
    	return '{{Mardi}}';
    }
  	if(day == 'MER.'){
    	return '{{MER.}}';
    }
  	if(day == 'Mercredi'){
    	return '{{Mercredi}}';
    }
  	if(day == 'JEU.'){
    	return '{{JEU.}}';
    }
  	if(day == 'Jeudi'){
    	return '{{Jeudi}}';
    }
  	if(day == 'VEN.'){
    	return '{{VEN.}}';
    }
  	if(day == 'Vendredi'){
    	return '{{Vendredi}}';
    }
  	if(day == 'SAM.'){
    	return '{{SAM.}}';
    }
  	if(day == 'Samedi'){
    	return '{{Samedi}}';
    }
  	if(day == 'DIM.'){
    	return '{{DIM.}}';
    }
  	if(day == 'Dimanche'){
    	return '{{Dimanche}}';
    }
  	return day;
}

function createDay(start,end){
  $('#calendrierParent_ID').removeClass('calendrierParent').removeClass('calendrierParent_3').removeClass('calendrierParent_3_Mercredi').removeClass('calendrierParent_3_Samedi');
  $('#Calendar_Day_ID').removeClass('Calendar_Day').removeClass('Calendar_Day_3').removeClass('Calendar_Day_3_Mercredi').removeClass('Calendar_Day_3_Samedi');
  debutSemaine = nbrDay(start);
  finSemaine = nbrDay(end);
  var Vraidebut = nbrDay('lundi');
  var VraiFin = nbrDay('dimanche');
  ouverture = (Vraidebut - 2);
  var i = Vraidebut;
  if((finSemaine - debutSemaine) + 1 > 3){
    var nbSemaine = 2;
    $('#calendrierParent_ID').addClass('calendrierParent');
    $('#Calendar_Day_ID').addClass('Calendar_Day').removeClass('Calendar_Day_3');
  }else{
    if(start == 'mercredi'){
      $('#calendrierParent_ID').addClass('calendrierParent_3_Mercredi');
      $('#Calendar_Day_ID').addClass('Calendar_Day_3_Mercredi');
    }else if(start == 'vendredi'){
      $('#calendrierParent_ID').addClass('calendrierParent_3_Samedi');
      $('#Calendar_Day_ID').addClass('Calendar_Day_3_Samedi');
    }else{
      $('#calendrierParent_ID').addClass('calendrierParent_3');
      $('#Calendar_Day_ID').addClass('Calendar_Day_3');
    }

    var nbSemaine = 1;
  }
  $('.iconePlus').hide();
  while(i <= finSemaine){
    if(i >= debutSemaine && debutSemaine <= finSemaine){
      $('.plusday'+i).show();
      var style = 'grid-column: '+(i-ouverture)+';grid-row : 1;';
      var html = '<div class="dayday" style="'+style+'">'+traductionGrid(nbrDay(i,nbSemaine))+'</div>';
      var htmlCalendar_Day = $('#Calendar_Day_ID').html();
      $('#Calendar_Day_ID').html(htmlCalendar_Day+' '+ html);
    }else{
      var style = 'grid-column: '+(i-ouverture)+';grid-row : 1; display:none;';
      var html = '<div class="dayday" style="'+style+'">'+traductionGrid(nbrDay(i,nbSemaine))+'</div>';
      var htmlCalendar_Day = $('#Calendar_Day_ID').html();
      $('#Calendar_Day_ID').html(htmlCalendar_Day+' '+ html);
    }
    i++;
  }
}

function vueSemaine(type, middle){
  $('#Calendar_Day_ID').html(' ');
  semaine = type;
  if(type == 'complet'){
    $('.temperatureClass').hide();
    temperatureClass = 'None';
    $('.3JoursInactif').show();
    $('.3JoursActif').hide();
    $('.WeekInactif').hide();
    $('.WeekActif').show();
    createDay('lundi','dimanche');
  }else{
    $('.temperatureClass').show();
    temperatureClass = 'inline';
    $('.3JoursInactif').hide();
    $('.3JoursActif').show();
    $('.WeekInactif').show();
    $('.WeekActif').hide();
    if(middle == 'mardi'){
      createDay('lundi','mercredi');
    }else if(middle == 'jeudi'){
      createDay('mercredi','vendredi');
    }else if(middle == 'samedi'){
      createDay('vendredi','dimanche');
    }else{
      createDay('lundi','dimanche');
    }
  }
}

function createHoraire(){
    var i = 1;
    for (i = 1; i <= 24; i++){
      if(i < 10){
        var html = '<div class="time-0'+i+'00 horaireCase"><span class="heureText"> 0'+i+'h </span></div>';
      }else{
        var html = '<div class="time-'+i+'00 horaireCase"><span class="heureText"> '+i+'h </span></div>';
      }
      var htmlCalendrier = $('#calendrierParent_ID').html();
      $('#calendrierParent_ID').html(htmlCalendrier+' '+ html);
    }
}

// Modal //
$( document ).ready(function() {
// Get the modal
var modal = document.getElementById("myModalCalendar");
var modalday = document.getElementById("myModalCalendarDay");

window.onclick = function(event) {
  if (event.target == modal) {
    $('#myModalCalendar').css('display','none');
  }
  if (event.target == modalday) {
    $('#myModalCalendarDay').css('display','none');
  }
}
});

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

function getAgendaData(){
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
            data: {
            	action: "getAgendaData",
            },
            dataType: 'json',
			async: false,
			global: false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
			success: function(data) {
			if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
			if (data.state == 'ok') {
				infodata =data.result;
			}
			//console.log(infodata);
        }
    });
}
