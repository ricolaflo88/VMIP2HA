/* VARIABLE */
var labelInfo = [];
var dataGraph = [];
var uniteY = '';
var config = '';
var dayday = 'day';
var valueDay = 'now'
var infodata = [];
var updateCharts = 0;

var middleValueDay = [0,11,23];
var middleValueWeek = [0,6,13];

var titleInfo = [];
var fleche_Right = [];
var fleche_Left = [];

/*
infodata = [{"title":"1500","titleHumain":"15:00","date":"Mercredi 05 fu00e9vrier","valeur":26.3,"unite":"u00b0C","datetime":"2020-02-05 15:05:00"},{"title":"1600","titleHumain":"16:00","date":"Mercredi 05 fu00e9vrier","valeur":26.4,"unite":"u00b0C","datetime":"2020-02-05 16:05:00"},{"title":"1700","titleHumain":"17:00","date":"Mercredi 05 fu00e9vrier","valeur":26.9,"unite":"u00b0C","datetime":"2020-02-05 17:05:00"},{"title":"1800","titleHumain":"18:00","date":"Mercredi 05 fu00e9vrier","valeur":27,"unite":"u00b0C","datetime":"2020-02-05 18:05:00"},{"title":"1900","titleHumain":"19:00","date":"Mercredi 05 fu00e9vrier","valeur":27.1,"unite":"u00b0C","datetime":"2020-02-05 19:05:00"},{"title":"2000","titleHumain":"20:00","date":"Mercredi 05 fu00e9vrier","valeur":26.9,"unite":"u00b0C","datetime":"2020-02-05 20:05:00"},{"title":"2100","titleHumain":"21:00","date":"Mercredi 05 fu00e9vrier","valeur":25.9,"unite":"u00b0C","datetime":"2020-02-05 21:05:00"},{"title":"2200","titleHumain":"22:00","date":"Mercredi 05 fu00e9vrier","valeur":24.8,"unite":"u00b0C","datetime":"2020-02-05 22:05:00"},{"title":"2300","titleHumain":"23:00","date":"Mercredi 05 fu00e9vrier","valeur":24,"unite":"u00b0C","datetime":"2020-02-05 23:05:00"},{"title":"0000","titleHumain":"00:00","date":"Jeudi 06 fu00e9vrier","valeur":23.6,"unite":"u00b0C","datetime":"2020-02-06 00:05:00"},{"title":"0100","titleHumain":"01:00","date":"Jeudi 06 fu00e9vrier","valeur":23.1,"unite":"u00b0C","datetime":"2020-02-06 01:05:00"},{"title":"0200","titleHumain":"02:00","date":"Jeudi 06 fu00e9vrier","valeur":22.6,"unite":"u00b0C","datetime":"2020-02-06 02:05:00"},{"title":"0300","titleHumain":"03:00","date":"Jeudi 06 fu00e9vrier","valeur":22.2,"unite":"u00b0C","datetime":"2020-02-06 03:05:00"},{"title":"0400","titleHumain":"04:00","date":"Jeudi 06 fu00e9vrier","valeur":21.9,"unite":"u00b0C","datetime":"2020-02-06 04:05:00"},{"title":"0500","titleHumain":"05:00","date":"Jeudi 06 fu00e9vrier","valeur":21.5,"unite":"u00b0C","datetime":"2020-02-06 05:05:00"},{"title":"0600","titleHumain":"06:00","date":"Jeudi 06 fu00e9vrier","valeur":21.2,"unite":"u00b0C","datetime":"2020-02-06 06:05:00"},{"title":"0700","titleHumain":"07:00","date":"Jeudi 06 fu00e9vrier","valeur":21.2,"unite":"u00b0C","datetime":"2020-02-06 07:05:00"},{"title":"0800","titleHumain":"08:00","date":"Jeudi 06 fu00e9vrier","valeur":21.2,"unite":"u00b0C","datetime":"2020-02-06 08:05:00"},{"title":"0900","titleHumain":"09:00","date":"Jeudi 06 fu00e9vrier","valeur":21.5,"unite":"u00b0C","datetime":"2020-02-06 09:05:00"},{"title":"1000","titleHumain":"10:00","date":"Jeudi 06 fu00e9vrier","valeur":22,"unite":"u00b0C","datetime":"2020-02-06 10:05:00"},{"title":"1100","titleHumain":"11:00","date":"Jeudi 06 fu00e9vrier","valeur":24.5,"unite":"u00b0C","datetime":"2020-02-06 11:05:00"},{"title":"1200","titleHumain":"12:00","date":"Jeudi 06 fu00e9vrier","valeur":26.3,"unite":"u00b0C","datetime":"2020-02-06 12:05:00"},{"title":"1300","titleHumain":"13:00","date":"Jeudi 06 fu00e9vrier","valeur":26.9,"unite":"u00b0C","datetime":"2020-02-06 13:05:00"},{"title":"1400","titleHumain":"14:00","date":"Jeudi 06 fu00e9vrier","valeur":27.2,"unite":"u00b0C","datetime":"2020-02-06 14:05:00"}];
*/
/*ready*/
$( document ).ready(function() {
  $('header').remove();
  $('.backgroundforJeedom').remove();

  $('.panelVMI_select_valeur_Type').click(function(e) {
    $('#myModal_Type').css('display','block');
  });

  $('.panelVMI_select_valeur_Object').click(function(e) {
    $('#myModal_Object').css('display','block');
  });

  $('.graphVMI_top_3').click(function(e) {
    if (window.myLine) {
      window.myLine.destroy();
      updateCharts =0;
      selecteur(null,null,'day');
    }
  });

  $('.graphVMI_top_4').click(function(e) {
    if (window.myLine) {
      window.myLine.destroy();
      updateCharts =0;
      selecteur(null,null,'week');
    }
  });

  $('.caseSet_0').click(function(e) {
    selecteur(null,null,null,titleInfo[0],$(this).attr('datetime'));
  });

  $('.caseSet_2').click(function(e) {
    selecteur(null,null,null,titleInfo[2],$(this).attr('datetime'));
  });

  $('.fleche_Left').click(function(e) {
    selecteur(null,null,null,fleche_Left['title'],fleche_Left['datetime']);
  });

  $('.fleche_Right').click(function(e) {
    selecteur(null,null,null,fleche_Right['title'],fleche_Right['datetime']);
  });

  selecteur();
});


function selecteur(selectType = null, selectObject = null, selectDay = null, selectValueDay = null, datetime = null){
  if(selectType == null){
    selectType = firstType; /* Type CO2, HUM, Temperature etc.. */
  }else{
    var testObject = 0;
    var i = 0;
    var premierObject = '';
    $('.ObjectAll').hide();
    listTypeToobjects[selectType].forEach(function(objectFor){
      $('#select_'+objectFor+'').show();
      if(firstObject == objectFor){
        testObject = 1
      }
      if(i == 0){
        premierObject = objectFor;
      }
      i++;
    });
    if(testObject == 0){
      firstObject = premierObject;
    }
  }
  if(selectObject == null){
    selectObject = firstObject; /* Object jeedom dans le quel ce trouve le type */
  }
  if(selectDay == null){
    selectDay = dayday; /* sur jour ou sur semaines  pour l'envoi des 5 valeurs*/
  }
  if(selectValueDay == null){
    selectValueDay = valueDay; /* la valeur qui ce trouve au milieu du tableau si now c'est la valeur a l'instant T */
  }

  /* demande de valeur avec les info en haut a toi de jouer LUDO */
  getGraphData(selectObject,selectType,selectDay,datetime);

  if(selectDay == 'day'){
    $('.JoursSVG, .dayTitle').addClass('buttonActifPilot').removeClass('buttonInactifPilot');
    $('.WeekSVG, .weekTitle').addClass('buttonInactifPilot').removeClass('buttonActifPilot');
  }else{
    $('.JoursSVG, .dayTitle').addClass('buttonInactifPilot').removeClass('buttonActifPilot');
    $('.WeekSVG, .weekTitle').addClass('buttonActifPilot').removeClass('buttonInactifPilot');
  }

  firstType = selectType;
  firstObject = selectObject.replace(/_/g,' ');
  dayday = selectDay;
  valueDay = selectValueDay;

  MajCharts(infodata,updateCharts);

  $('.TypeHumain').text(firstType);
  $('.panelVMI_select_valeur_Type > .panelVMI_label_texte').text(firstType);
  $('.panelVMI_select_valeur_Object > .panelVMI_label_texte').text(firstObject);
}


/*Changement dans Chart*/
let draw = Chart.controllers.line.prototype.draw;
Chart.controllers.line = Chart.controllers.line.extend({
  draw: function() {
    draw.apply(this, arguments);
    let ctx = this.chart.chart.ctx;
    let _stroke = ctx.stroke;
    ctx.stroke = function() {
      ctx.save();
      ctx.shadowColor = '#00000029';
      ctx.shadowBlur = 30;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 20;
      _stroke.apply(this, arguments)
      ctx.restore();
    }
  }
});

/* FUNCTIONS */
function fitToContainer(canvas){
  // Make it visually fill the positioned parent
  canvas.style.width ='100%';
  canvas.style.height='100%';
  // ...then set the internal size to match
  canvas.width  = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;
}

function configCreation(labelType, valeurType, dataValue, unite){
  var configCreation = {
    type: 'line',
    data: {
      labels: labelType,
      datasets: [{
        label: valeurType,
        backgroundColor: '#4D009A',
        borderColor: '#4D009A',
        lineTension: 0,
        borderWidth: 5,
        pointRadius: 0,
        data: dataValue,
        fill: false,
      }]
    },
    options: {
      responsive: true,
      legend: {
        display: false,
      },
      title: {
        display: false
      },
      tooltips: {
        mode: 'index',
        intersect: false,
      },
      hover: {
        mode: 'nearest',
        intersect: true
      },
      scales: {
        xAxes: [{
          display: false,
          scaleLabel: {
            display: false
          }
        }],
        yAxes: [{
          ticks: {
            labelOffset: -10,
            mirror : true,
            callback: function(value, index, values) {
              return value+ ' '+unite;
            }
          },
          display: true,
          scaleLabel: {
            display: false,
            labelString: 'Valeur'
          }
        }]
      }
    }
  };
  return configCreation;
}

function MajCharts(info, update = 0){
  if (info != '') {
    labelInfo = [];
    dataGraph=[];
    info.forEach((item, index) => {
      labelInfo[index] = item.title;
      dataGraph[index] = item.valeur;
      uniteY = item.unite;
      if(dayday == 'day'){
        middleValueDay.forEach((valueD, indexD) => {
          if(index == valueD){
            $('.caseSet_'+indexD+' > .textCase').text(item.titleHumain);
            $('.caseSet_'+indexD).attr('datetime',item.datetime);
            titleInfo[indexD] = item.title;
          }
        });
      }else{
        middleValueWeek.forEach((valueW, indexW) => {
          if(index == valueW){
            $('.caseSet_'+indexW+' > .textCase').text(item.titleHumain);
            $('.caseSet_'+indexW).attr('datetime',item.datetime);
            titleInfo[indexW] = item.title;
          }
        });
      }
    });
    if(dayday == 'day'){
      middleValue = middleValueDay[1];
    }else{
      middleValue = middleValueWeek[1];
    }
    if(dayday == 'day'){
      $('.dateHumain').text(info[middleValue].date+' : ');
      $('.XHumain').text(info[middleValue].titleHumain);
    } else {
      $('.dateHumain').text(info[middleValue].date);
      $('.XHumain').text('');
    }
    $('.YHumain').text(info[middleValue].valeur+' ');
    $('.uniteHumain').text(info[middleValue].unite);
    $('.TypeHumain').text(firstType);
    fleche_Left['title'] = info[(middleValue-3)].title;
    fleche_Left['datetime'] = info[(middleValue-3)].datetime;
    fleche_Right['title'] = info[(middleValue+3)].title;
    fleche_Right['datetime'] = info[(middleValue+3)].datetime;

    var ctx = document.getElementById('canvas').getContext('2d');
    if(update == 0){
      config = configCreation(labelInfo,'Valeur',dataGraph,uniteY);
      window.myLine = new Chart(ctx, config);
      updateCharts = 1;
    }else{
      config = configCreation(labelInfo,'Valeur',dataGraph,uniteY);
      // console.log(getMin(config) + ' / ' + getMax(config));
      window.myLine.data.labels=config.data.labels;
      window.myLine.data.datasets[0].data=config.data.datasets[0].data;
      window.myLine.options.scales.yAxes=config.options.scales.yAxes;
      window.myLine.update();
    }
  }
}

var getMax = function(chart) {
  datasets = chart.data.datasets;
  max = 0;
  for(var i=0; i<datasets.length; i++) {
    dataset=datasets[i]
    if(chart.data.datasets[i].hidden) {
      continue;
    }
    dataset.data.forEach(function(d) {
      if(typeof(d)=="number" && d>max) {
        max = d
      }
    })
  }
  return max;
}

var getMin = function(chart) {
  datasets = chart.data.datasets;
  min = 9999;
  for(var i=0; i<datasets.length; i++) {
    dataset=datasets[i]
    if(chart.data.datasets[i].hidden) {
      continue;
    }
    dataset.data.forEach(function(d) {
      if(typeof(d)=="number" && d<min) {
        min = d
      }
    })
  }
  return min;
}
// Modal //
$( document ).ready(function() {
  // Get the modal
  var modalType = document.getElementById("myModal_Type");
  var modalObject = document.getElementById('myModal_Object');

  window.onclick = function(event) {
    if (event.target == modalType) {
      $('#myModal_Type').css('display','none');
    }
    if (event.target == modalObject) {
      $('#myModal_Object').css('display','none');
    }
  }
});

function getGraphData($object,$type,$daytype,$datetime=''){
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
    data: {
      action: "getGraphData",
      object : $object,
      type : $type,
      daytype : $daytype,
      datetime : $datetime,
    },
    dataType: 'json',
    async: false,
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function(dataAjax) {
      if (dataAjax.state != 'ok') {
        $('#div_alert').showAlert({message: dataAjax.result, level: 'danger'});
        return;
      }
      if (dataAjax.state == 'ok') {
        infodata =dataAjax.result;
      }
    }
  });
}
