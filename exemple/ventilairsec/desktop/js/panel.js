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

$( document ).ready(function() {
  activMenu('MenuPanelInfo');
  $('.MenuPanelInfo').click(function(e) {
    activMenu('MenuPanelInfo');
  });
  $('.MenuPilot').click(function(e) {
    activMenu('MenuPilot');
  });
  $('.MenuGraph').click(function(e) {
    activMenu('MenuGraph');
  });
  $('.MenuContact').click(function(e) {
    activMenu('MenuContact');
  });
  $('.MenuInstallateur').click(function(e) {
    activMenu('MenuInstallateur');
  });
});

function activMenu(menu){
  $( ".actif" ).removeClass('actif');
  $( "."+menu ).addClass('actif');
  iframeDone(menu);
}

function iframeDone(menu){
   var p = 'panelInfo';
  if(menu == "MenuPanelInfo"){
    p = 'panelInfo';
  }else if(menu == "MenuPilot"){
    p = 'pilot';
  }else if(menu == "MenuGraph"){
    p = 'graph';
  }else if(menu == "MenuContact"){
    p = 'contact';
  }else if(menu == "MenuInstallateur"){
    p = 'installateur';
  }
  $('#iframe').attr('src', $(location).attr('origin')+'/index.php?v=d&m=ventilairsec&p='+p);
}
