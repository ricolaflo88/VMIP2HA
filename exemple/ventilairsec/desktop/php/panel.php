<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('desktop', 'panel', 'css', 'ventilairsec');
?>

<div class="GlobalPage">
  <iframe class="IframeDiv" id="iframe" name="myIframe"></iframe>

  <div class="MenuDiv">
    <div class="Menu MenuPanelInfo actif">
      <div class="iconeMenu actif">
        <?php include 'plugins/ventilairsec/core/img/DASHBOARD.svg'; ?>
      </div>
      <div class="titleMenu">
        {{Dashboard}}
      </div>
    </div>
    <div class="Menu MenuPilot">
      <div class="iconeMenu">
        <?php include 'plugins/ventilairsec/core/img/PILOTAGE.svg'; ?>
      </div>
      <div class="titleMenu">
        {{Pilotage}}
      </div>
    </div>
    <div class="Menu MenuGraph">
      <div class="iconeMenu">
        <?php include 'plugins/ventilairsec/core/img/VISUALISATION.svg'; ?>
      </div>
      <div class="titleMenu">
        {{Visualisation}}
      </div>
    </div>
    <div class="Menu MenuContact">
      <div class="iconeMenu">
        <?php include 'plugins/ventilairsec/core/img/CONTACT.svg'; ?>
      </div>
      <div class="titleMenu">
        {{Aide et contact}}
      </div>
    </div>
  </div>
</div>

<?php include_file('desktop', 'panel', 'js', 'ventilairsec'); ?>
