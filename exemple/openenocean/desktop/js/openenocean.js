
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
 $('#bt_resetSearch').off('click').on('click', function () {
     $('#in_searchEqlogic').val('')
     $('#in_searchEqlogic').keyup();
 })
 
$(".eqLogic").off('click','.listCmdInfo').on('click','.listCmdInfo', function () {
  var el = $(this).closest('.form-group').find('.eqLogicAttr');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    if (el.attr('data-concat') == 1) {
      el.atCaret('insert', result.human);
    } else {
      el.value(result.human);
    }
  });
});

$('.changeIncludeState').off('click').on('click', function () {
  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  }
  else {
    var dialog_title = '';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '{{Démarrer l\'inclusion}}';
    dialog_message += '<label class="control-label" > {{Sélectionner le type d\'inclusion ?}} </label> ' +
    '<div> <div class="radio"> <label > ' +
    '<input type="radio" name="type" id="auto" value="0" checked="checked"> {{Inclusion automatique}} </label> ' +
    '</div>' +
    '<div class="radio"> <label > ' +
    '<input type="radio" name="type" id="send" value="1"> {{Inclusion par envoi de signal (à venir)}}</label> ' +
    '</div> ' +
    '<div class="radio"> <label > ' +
    '<input type="radio" name="type" id="remote" value="2"> {{Inclusion remote learn (à venir)}}</label> ' +
    '</div> ' +
    '<div class="radio"> <label > ' +
    '<input type="radio" name="type" id="direct" value="3"> {{Inclusion direct learn (à venir)}}</label> ' +
    '</div> ' +
    '</div><br>'+
    '<label class="lbl lbl-warning" for="type">{{Sans aucune contre indication, il est préférable de choisir l\'inclusion automatique}}</label> ';
    dialog_message += '</form>';
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {
            var type = $("input[name='type']:checked").val();
            if (type == 0) {
              changeIncludeState(state, mode,type);
            } else {
            }
          }
        },
      }
    });
  }
});
$('#bt_configureDevice').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Configuration du module}}"});
  $('#md_modal').load('index.php?v=d&plugin=openenocean&modal=configureModule&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});

$('#bt_configureRemcom').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Remote Management}}"});
  $('#md_modal').load('index.php?v=d&plugin=openenocean&modal=remMan&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});

$('#bt_configureRepeat').off('click').on('click', function () {
  var eqId = $('.eqLogicAttr[data-l1key=id]').value();
  var lastRepeat = $('.eqLogicAttr[data-l1key=configuration][data-l2key=lastRepeat]').value();
  var dialog_title = '';
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
  dialog_title = '{{Répéteur}}';
  dialog_message += '<label class="control-label" > {{Sélectionner le mode de répéteur}} </label> ' +
  '<div> <div class="radio"> <label > ';
  if (lastRepeat == "0" || lastRepeat == '') {
    dialog_message += '<input type="radio" name="type" id="none" value="0" checked="checked"> {{Aucun (niveau 0)}} </label> ';
  } else {
    dialog_message += '<input type="radio" name="type" id="none" value="0"> {{Aucun (niveau 0)}} </label> ';
  }
  dialog_message += '</div>' +
  '<div class="radio"> <label > ';
  if (lastRepeat == "1") {
    dialog_message += '<input type="radio" name="type" id="none" value="1" checked="checked"> {{Répétition de niveau 1}} </label> ';
  } else {
    dialog_message += '<input type="radio" name="type" id="none" value="1"> {{Répétition de niveau 1}} </label> ';
  }
  dialog_message += '</div> ' +
  '<div class="radio"> <label > ';
  if (lastRepeat == "2") {
    dialog_message += '<input type="radio" name="type" id="none" value="2" checked="checked"> {{Répétition de niveau 1}} </label> ';
  } else {
    dialog_message += '<input type="radio" name="type" id="none" value="2"> {{Répétition de niveau 2}} </label> ';
  }
  dialog_message += '</div> ' +
  '</div><br>'+
  '<label class="lbl lbl-warning" for="type">{{Attention en Enocean les répétitions peuvent causer des conflits de trames}}</label> ';
  dialog_message += '</form>';
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function () {
        }
      },
      success: {
        label: "{{Appliquer}}",
        className: "btn-success",
        callback: function () {
          var type = $("input[name='type']:checked").val();
          changeRepeatMode(eqId,type);
        }
      },
    }
  });
});

$('#bt_healthopenenocean').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Santé EnOcean}}"});
  $('#md_modal').load('index.php?v=d&plugin=openenocean&modal=health').dialog('open');
});

$('.twoids').off('change').on('change', function ()  {
  if (this.checked) {
    $(".action-id").show();
  } else {
    $(".action-id").hide();
  }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').on('change', function () {
  if($('.eqLogicAttr[data-l1key=id]').value() != ''){
    getModelListParam($(this).value(),$('.eqLogicAttr[data-l1key=id]').value());
  }else{
    $('#img_device').attr("src",'plugins/openenocean/plugin_info/openenocean_icon.png');
  }
});

$('#bt_autoDetectModule').off('click').on('click', function () {
  var dialog_title = '{{Recharge configuration}}';
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
  dialog_title = '{{Recharger la configuration}}';
  dialog_message += '<label class="control-label" > {{Sélectionner le mode de rechargement de la configuration ?}} </label> ' +
  '<div> <div class="radio"> <label > ' +
  '<input type="radio" name="command" id="command-0" value="0" checked="checked"> {{Sans supprimer les commandes}} </label> ' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="command" id="command-1" value="1"> {{En supprimant et recréant les commandes}}</label> ' +
  '</div> ' +
  '</div><br>' +
  '<label class="lbl lbl-warning" for="name">{{Attention, "En supprimant et recréant" va supprimer les commandes existantes.}}</label> ';
  dialog_message += '</form>';
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function () {
        }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function () {
          if ($("input[name='command']:checked").val() == "1"){
            bootbox.confirm('{{Etes-vous sûr de vouloir récréer toutes les commandes ? Cela va supprimer les commandes existantes}}', function (result) {
              if (result) {
                $.ajax({
                  type: "POST",
                  url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
                  data: {
                    action: "autoDetectModule",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    createcommand: 1,
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
                    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
                    $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
                  }
                });
              }
            });
          } else {
            $.ajax({
              type: "POST",
              url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
              data: {
                action: "autoDetectModule",
                id: $('.eqLogicAttr[data-l1key=id]').value(),
                createcommand: 0,
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
                $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
                $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
              }
            });
          }
        }
      },
    }
  });
  
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=iconModel]').off('change').on('change', function () {
  if($(this).value() != '' && $(this).value() != null){
    $('#img_device').attr("src", 'plugins/openenocean/core/config/devices/'+$(this).value()+'.jpg');
  }
});

function getModelListParam(_conf,_id) {
  $.ajax({
    type: "POST",
    url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
    data: {
      action: "getModelListParam",
      conf: _conf,
      id: _id,
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
      var options = '';
      for (var i in data.result[0]) {
        if (data.result[0][i]['selected'] == 1){
          options += '<option value="'+i+'" selected>'+data.result[0][i]['value']+'</option>';
        } else {
          options += '<option value="'+i+'">'+data.result[0][i]['value']+'</option>';
        }
      }
      if (data.result[1] == true){
        $(".paramDevice").show();
      } else {
        $(".paramDevice").hide();
      }
      if (data.result[2] != false){
        $(".globalRemark").show();
        $(".globalRemark").empty().append(data.result[2]);
      } else {
        $(".globalRemark").empty()
        $(".globalRemark").hide();
      }
      if (data.result[3] == true){
        $(".repeatDevice").show();
      } else {
        $(".repeatDevice").hide();
      }
      if (data.result[4] == true){
        $(".remcom").show();
      } else {
        $(".remcom").hide();
      }
	   if (data.result[5] == true){
        $(".sourceTemp").show();
      } else {
        $(".sourceTemp").hide();
      }
      $(".modelList").show();
      $(".listModel").html(options);
      icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=iconModel]').value();
      if(icon != '' && icon != null){
        $('#img_device').attr("src", 'plugins/openenocean/core/config/devices/'+icon+'.jpg');
      } else {
        $('#img_device').attr("src", 'plugins/openenocean/plugin_info/openenocean_icon.png');
      }
    }
  });
}

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<div class="row">';
  tr += '<div class="col-sm-6">';
  tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icône</a>';
  tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
  tr += '</div>';
  tr += '<div class="col-sm-6">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
  tr += '</div>';
  tr += '</div>';
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="La valeur de la commande vaut par défaut la commande">';
  tr += '<option value="">Aucune</option>';
  tr += '</select>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += '</td>';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0" style="width : 70%; display : inline-block;" placeholder="{{Commande}}"><br/>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="width:48%;display:inline-block;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}" style="width:48%;display:inline-block;margin-left:2px;">';
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdId" style="display : none;margin-top : 5px;" title="Commande d\'information à mettre à jour">';
  tr += '<option value="">Aucune</option>';
  tr += '</select>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;">';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;display:inline-block;margin-left:2px;">';
  tr += '<input class="tooltips cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste de valeur|texte séparé par ;}}" title="{{Liste}}">';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> Tester</a>';
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  var tr = $('#table_cmd tbody tr').last();
  jeedom.eqLogic.builSelectCmd({
    id:  $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });
}

$('body').off('openenocean::includeState').on('openenocean::includeState', function (_event,_options) {
  if (_options['mode'] == 'learn') {
    if (_options['state'] == 1) {
      if($('.include').attr('data-state') != 0){
        $.hideAlert();
        $('.include').attr('data-state', 0);
        $('.include.card span center').text('{{Arrêter l\'inclusion}}');
        $('.include:not(.card)').html('<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Arreter inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous etes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
      }
    } else {
      if($('.include').attr('data-state') != 1){
        $.hideAlert();
        $('.include').attr('data-state', 1);
        $('.include:not(.card)').html('<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Mode inclusion}}');
        $('.include.card span center').text('{{Mode inclusion}}');
      }
    }
  } else {
    if (_options['state'] == 1) {
      if($('.exclude').attr('data-state') != 0){
        $.hideAlert();
        $('.exclude').attr('data-state', 0);
        $('.exclude.card span center').text('{{Arrêter l\'exclusion}}');
        $('.exclude:not(.card)').html('<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Arreter inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous etes en mode exclusion. Recliquez sur le bouton d\'exclusion pour sortir de ce mode}}', level: 'warning'});
      }
    } else {
      if($('.exclude').attr('data-state') != 1){
        $.hideAlert();
        $('.exclude').attr('data-state', 1);
        $('.exclude:not(.card)').html('<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Mode exclusion}}');
        $('.exclude.card span center').text('{{Mode exclusion}}');
      }
    }
  }
});

$('body').off('openenocean::includeDevice').on('openenocean::includeDevice', function (_event,_options) {
  if (modifyWithoutSave) {
    $('#div_inclusionAlert').showAlert({message: '{{Un périphérique vient d\'être inclu/exclu. Veuillez réactualiser la page}}', level: 'warning'});
  } else {
    if (_options == '') {
      window.location.reload();
    } else {
      window.location.href = 'index.php?v=d&p=openenocean&m=openenocean&id=' + _options;
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
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });
}
function changeRepeatMode(eqId,type) {
  $.ajax({
    type: "POST",
    url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
    data: {
      action: "changeRepeater",
      id: eqId,
      type: type,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });
}
