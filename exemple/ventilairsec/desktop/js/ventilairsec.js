
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
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name"></td>';
  tr += '<td>';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none;">';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

$('#bt_sync').on('click', function () {
  bootbox.confirm('{{Êtes-vous sûr de vouloir lancer une détection des VMIS ?}}', function (result) {
    if (result) {
      $.ajax({
        type: "POST", // méthode de transmission des données au fichier php
        url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
        data: {
          action: "sync",
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
          handleAjaxError(request, status, error);
        },
        success: function (data) {
          if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
          }
          if (data.result == '0') {
            $('#div_alert').showAlert({message: 'Aucune VMI trouvée', level: 'danger'});
            return
          } else {
            $('#div_alert').showAlert({message: data.result + ' VMI trouvée(s)', level: 'success'});
          }
          setTimeout(function() { refresh() }, 1500);
        }
      });
    }
  });
});

$('body').off('openenocean::includeDevice').on('openenocean::includeDevice', function (_event,_options) {
  $('#div_alert, #div_md_alert').showAlert({message: '{{Recherche en cours...}}', level: 'warning'});
  $.ajax({
    type: "POST", // méthode de transmission des données au fichier php
    url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
    data: {
      action: "sync",
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert, #div_md_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      if (data.result == '0') {
        $('#div_alert, #div_md_alert').showAlert({message: 'Aucune VMI trouvée', level: 'danger'});
        return;
      } else {
        $('#div_alert, #div_md_alert').showAlert({message: data.result + ' VMI trouvée(s)', level: 'success'});
        if ($('#md_modal').is(':visible')){
          $('#md_modal').dialog('close');
          $('#md_modal').dialog({title: "{{Intégrateur VMI}}"});
          $('#md_modal').load('index.php?v=d&plugin=ventilairsec&modal=integrator').dialog('open');
        } else {
          setTimeout(function() { refresh() }, 1500);
        }
      }

    }
  });
});

$('.changeIncludeState').off('click').on('click', function () {
  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  } else {
    changeIncludeState(state, mode,0);
  }
});

$('body').off('openenocean::includeState').on('openenocean::includeState', function (_event,_options) {
  var inclusionButton = $('.changeIncludeState');
  if (_options['mode'] == 'learn') {
    if (_options['state'] == 1) {
      if(inclusionButton.attr('data-state') != 0){
        $.hideAlert();
        inclusionButton.attr('data-state', 0);
        inclusionButton.find('span').text('{{Arrêter l\'inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Cliquez à nouveau sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
        $('#div_md_alert').showAlert({message: '{{Vous êtes en mode inclusion.}}', level: 'warning'});
      }
    } else {
      if(inclusionButton.attr('data-state') != 1){
        $.hideAlert();
        inclusionButton.attr('data-state', 1);
        inclusionButton.find('span').text('{{Mode inclusion}}');
      }
    }
  }
});

function changeIncludeState(_state,_mode,_type='') {
  $.ajax({
    type: "POST",
    url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
    data: {
      action: "changeIncludeState",
      state: _state,
      mode: _mode,
      type: _type,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert, #div_md_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });
}

function refresh() {
  if (!($('#md_modal').is(':visible'))){
    document.location.reload(true);
  }
}

$('#bt_int').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Intégrateur VMI}}"});
  $('#md_modal').load('index.php?v=d&plugin=ventilairsec&modal=integrator').dialog('open');
});
