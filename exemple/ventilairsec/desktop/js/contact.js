$( document ).ready(function() {
  $('header').remove();
  $('.backgroundforJeedom').remove();
  eventRefresh('');
  $(".export").on('click', function () {
    window.location.href = 'plugins/ventilairsec/core/php/export.php?type=csv';
  });
});

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
      // console.log(data);
      if (data.result.result == 'ok') {
        updatepanel(data.result);
      }
    }
  });
}

function updatepanel(data){
  $( ".caisson" ).empty().append(data.caisson);
  $( ".idmachine" ).empty().append(data.idmachine);
  $( ".logicielv" ).empty().append(data.logicielv);
  $( ".elecv" ).empty().append(data.elecv);
  $( ".typprech" ).empty().append(data.typprech);
}

$('body').on('ventilairsec::modified', function (_event,_options) {
  eventRefresh(_options);
});
