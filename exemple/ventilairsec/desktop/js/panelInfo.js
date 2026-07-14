
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

/* variable */
var timeinit = 1000;

$( document ).ready(function() {
  $('header').remove();
  $('.backgroundforJeedom').remove();

  treeAnimation();
  eventRefresh('');
});

/* functions */
var treeAnimation = function(){
  $( ".arbre_one" ).addClass('hiddenClass');
  setTimeout(function(){
    $( ".arbre_two" ).addClass('hiddenClass');
    setTimeout(function(){
      $( ".arbre_tree" ).addClass('hiddenClass');
      setTimeout(function(){
        $( ".arbre_zero" ).addClass('hiddenClass');
        setTimeout(function(){
          $( ".arbre_zero" ).removeClass('hiddenClass');
          setTimeout(function(){
            $( ".arbre_tree" ).removeClass('hiddenClass');
            setTimeout(function(){
              $( ".arbre_two" ).removeClass('hiddenClass');
              setTimeout(function(){
                $( ".arbre_one" ).removeClass('hiddenClass');
                setTimeout(function(){
                  treeAnimation();
                },timeinit);
              },timeinit);
            },timeinit);
          },timeinit);
        },timeinit);
      },timeinit);
    },timeinit);
  },timeinit);
};

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
      if (data.result.result == 'ok') {
        updatepanel(data.result);
      }
    }
  });
}

function updatepanel(data){
  $( ".filtre" ).empty().append(data.filter);
  $( ".soufflage" ).empty().append(data.tempsoufflage);
  $( ".humval" ).empty().append(data.humiditeAss);
  $( ".co2val" ).empty().append(data.co2value);

  if (data.co2value=='-') {
    $('div.panelVMI_div_co2').hide();
  } else if (data.co2value<1000) {
    $('.circle_co2').css("background-color", "#60A86E");
  } else if (data.co2value<1500){
    $('.circle_co2').css("background-color", "#FFFFB4");
  } else {
    $('.circle_co2').css("background-color", "red");
  }

  if (data.humiditeAss<30) {
    $('.circle_hum').css("background-color", "red");
  } else if (data.humiditeAss<40){
    $('.circle_hum').css("background-color", "#FFFFB4");
  } else if (data.humiditeAss<60){
    $('.circle_hum').css("background-color", "#60A86E");
  } else if (data.humiditeAss<70){
    $('.circle_hum').css("background-color", "#FFFFB4");
  } else {
    $('.circle_hum').css("background-color", "red");
  }

  if (data.surven == 0) {
    $('div.panelVMI_div_surventilation').hide();
    $('div.panelVMI_div_turbochauffe').hide();
  }
  else {
    $('div.panelVMI_div_surventilation').show();
    $('div.panelVMI_div_turbochauffe').show();
    if (data.saison==1 || data.saison==2) {
      $('div.panelVMI_div_surventilation > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/surventilation-off.png" height=85%/>');
      $('div.panelVMI_div_turbochauffe > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/turbochauffe-on.png" height=85%/>');
    } else if (data.saison==0) {
      $('div.panelVMI_div_surventilation > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/surventilation-on.png" height=85%/>');
      $('div.panelVMI_div_turbochauffe > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/turbochauffe-off.png" height=85%/>');
    }
  }

  if (data.puissancechauff > 0) {
    $('div.panelVMI_div_resistance > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/prechauffage-elec-on.png" height=80%/>');
    $('div.panelVMI_div_resistance > .panelVMI_div_title').html('{{Préchauffage ON}}');
  } else {
    $('div.panelVMI_div_resistance > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/prechauffage-elec-off.png" height=80%/>');
    $('div.panelVMI_div_resistance > .panelVMI_div_title').html('{{Préchauffage OFF}}');
  }

  if (data.hydror == 0) {
    $('div.panelVMI_div_hydror').hide();
  }
  else {
    $('div.panelVMI_div_hydror').show()
    if (data.hydrorV > 0) {
      $('div.panelVMI_div_hydror > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/hydro-r-on.png" height=75%/>');
    } else {
      $('div.panelVMI_div_hydror > .panelVMI_div_valeur').empty().append('<img src="plugins/ventilairsec/core/img/hydro-r-off.png" height=75%/>');
    }
  }

  if (data.posbypass == 1 || data.posbypass == 2 || data.posbypass == 3) {
    $('div.panelVMI_div_bypass').show();
    $('.bypass_gauge div').css('width', data.ouvbypass +'%');
  }
  else {
    $('div.panelVMI_div_bypass').hide();
  }

  $(".panelVMI_leftAll > .panelVMI_div:visible").each(function(index) {
    if (index == 0 || index == 3 || index == 6) {
      $(this).css('background-color', 'rgb(215, 213, 214)');
    } else if (index == 1 || index == 4 || index == 7) {
      $(this).css('background-color', 'rgba(215, 213, 214, 0.67)');
    } else if (index == 2 || index == 5 || index == 8) {
      $(this).css('background-color', 'rgba(215, 213, 214, 0.37)');
    }
  });

  if (data.vitessemoteur == 0) {
    timeinit = 3500;
  } else if (data.vitessemoteur == 1) {
    timeinit = 3000;
  } else if (data.vitessemoteur == 2) {
    timeinit = 2500;
  } else if (data.vitessemoteur == 3) {
    timeinit = 2000;
  } else if (data.vitessemoteur == 4) {
    timeinit = 1500;
  } else if (data.vitessemoteur == 5) {
    timeinit = 1000;
  }
  if (data.mode == 1) {
    $( "#venti1" ).removeClass( "buttonInactif" ).addClass( "buttonActif" );
    $( "#venti2" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti3" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti4" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti5" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
  } else if (data.mode == 2) {
    $( "#venti1" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti2" ).removeClass( "buttonInactif" ).addClass( "buttonActif" );
    $( "#venti3" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti4" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti5" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
  } else if (data.mode == 3) {
    $( "#venti1" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti2" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti3" ).removeClass( "buttonInactif" ).addClass( "buttonActif" );
    $( "#venti4" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti5" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
  } else if (data.mode == 4) {
    $( "#venti1" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti2" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti3" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti4" ).removeClass( "buttonInactif" ).addClass( "buttonActif" );
    $( "#venti5" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
  } else if (data.mode == 5) {
    $( "#venti1" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti2" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti3" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti4" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti5" ).removeClass( "buttonInactif" ).addClass( "buttonActif" );
  } else {
    $( "#venti1" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti2" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti3" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti4" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
    $( "#venti5" ).removeClass( "buttonActif" ).addClass( "buttonInactif" );
  }
  if (data.erreur != '') {
    $('.noAlert').addClass('Alert').removeClass('noAlert');
    $('.spanAlert').empty().append(data.erreur);
  } else {
    $('.Alert').addClass('noAlert').removeClass('Alert');
  }
}

$('body').on('ventilairsec::modified', function (_event,_options) {
  eventRefresh(_options);
});
