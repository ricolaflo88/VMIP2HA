<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('desktop', 'pilot', 'css', 'ventilairsec');
include_file('desktop', 'calendar', 'css', 'ventilairsec');
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<div class="panelVMI_body">
	<div class="panelVMI_All">
		<div class="panelVMI_ligne">
				<?php include 'plugins/ventilairsec/core/img/BOOST.svg'; ?>
				<span class="p_left iconeLeft verticalAlignClass">{{Boost immédiat}}</span>
				<span class="p_right verticalAlignClass boostSelect"><?php include 'plugins/ventilairsec/core/img/Toggle_inactive.svg'; ?></span>
		</div>
		<div class="panelVMI_ligne">
				<?php include 'plugins/ventilairsec/core/img/VALISE.svg'; ?>
				<span class="p_left iconeLeft verticalAlignClass">{{Vacances}}</span>
				<span class="p_right verticalAlignClass vacanceSelect"><?php include 'plugins/ventilairsec/core/img/Toggle_inactive.svg'; ?></span>
		</div>
		<div class="panelVMI_ligne ligne_70">
			<div class="panelVMI_ligne_title">
				<span>{{Choix du mode de ventilation}}</span>
			</div>
				<div class="panelVMI_ligne_global_ventil">
					<div class="panelVMI_ligne_ventil">
						<span class="bouton_ventil venti2Select"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_1.svg'; ?></span>
					</div>
					<div class="panelVMI_ligne_ventil">
							<span class="bouton_ventil venti3Select"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_2.svg'; ?></span>
					</div>
					<div class="panelVMI_ligne_ventil">
							<span class="bouton_ventil venti4Select"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_3.svg'; ?></span>
					</div>
			</div>
		</div>
		<div class="panelVMI_ligne">
				<div class="panelVMI_groupForm prechauffageClassHide">
					<div class="panelVMI_label"><span class="panelVMI_labelText">{{Consigne Préchauffage Électrique}}</span></div>
					<div class="panelVMI_select_valeur panelVMI_select_valeur_prechauffage"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
				</div>
				<div class="panelVMI_groupForm soufflageClassHide">
					<div class="panelVMI_label"><span class="panelVMI_labelText">{{Température Max Soufflage}}</span></div>
					<div class="panelVMI_select_valeur panelVMI_select_valeur_soufflage"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
				</div>
				<div class="panelVMI_groupForm hydroClassHide">
					<div class="panelVMI_label"><span class="panelVMI_labelText">{{Température Consigne Hydro'R}}</span></div>
					<div class="panelVMI_select_valeur panelVMI_select_valeur_hydro"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
				</div>
				<div class="panelVMI_groupForm solarClassHide">
					<div class="panelVMI_label"><span class="panelVMI_labelText">{{Seuil de confort}}</span></div>
					<div class="panelVMI_select_valeur panelVMI_select_valeur_solar"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
				</div>
		</div>
		<div class="panelVMI_ligne_0">
			<div class="razfiltre cursor">{{Remise à 0 du compteur filtre}}</div>
		</div>
		<div class="panelVMI_ligne panelVMI_ligne_Surv">
			<span class="p_left verticalAlignClass">{{Surventilation}}</span>
			<span class="p_right verticalAlignClass surventilationSelect"><?php include 'plugins/ventilairsec/core/img/Toggle_inactive.svg'; ?></span>
		</div>
		<div class="panelVMI_ligne programmerClick">
			<span class="p_left verticalAlignClass">{{Programmer par plage horaire}}</span>
			<span class="p_right verticalAlignClass"><?php include 'plugins/ventilairsec/core/img/Artboard_right.svg'; ?></span>
		</div>
		<div class="panelVMI_ligne">
			<span class="p_left verticalAlignClass">{{Débit fixe}}</span>
			<span class="p_right verticalAlignClass debitSelect"><?php include 'plugins/ventilairsec/core/img/Toggle_inactive.svg'; ?></span>
		</div>
		</div>
	</div>
	<div class="panelVMI_bodyAlternatif">
	    <?php include_file('desktop', 'calendar', 'php', 'ventilairsec'); ?>
	</div>

	<div id="myModal" class="modal">

	<div class="modal-content">
		<div class="panelVMI_All">
			<?php
			$tempratureMinimum = 0;
			while ($tempratureMinimum <= 45){
				echo '<div class="panelVMI_ligne" id="select_'.$tempratureMinimum.'">
					<span class="modalTemperatureText">';
						if($tempratureMinimum == 0){
							echo 'OFF';
						}else{
							echo $tempratureMinimum.'°C';
						}
					echo '</span>
				</div>';
				?>
				<script>
				$( document ).ready(function() {
					$('#select_<?php echo $tempratureMinimum; ?>').click(function(e) {
						selectExec(<?php echo $tempratureMinimum; ?>);
					});
				});
				</script>
				<?php
				$tempratureMinimum++;
			}
			 ?>
		</div>
  </div>
</div>
	<div id="myModalBoost" class="modal">
	<div class="modal-content">
		<div class="panelVMI_All">
			<div class="panelVMI_ligne">
				<input type="number" id="nbrJourBV" step="1" min="1" autofocus /> <span class="ModalOnBoost">{{Minutes}}<span>
			</div>
			<div class="panelVMI_ligne">
				<div class="panelVMI_0 btn valvalPilot" name="button">{{VALIDER}}</div>
				<div class="panelVMI_0 btn cancelPilot" name="button">{{ANNULER}}</div>
			</div>
		</div>
  </div>
</div>
<div id="myModalRAZ" class="modal">
<div class="modal-content">
	<div class="panelVMI_All">
		<div class="panelVMI_ligne">{{Confirmez-vous votre souhait de remettre à zéro le compteur filtre ?}}</div>
		<div class="panelVMI_ligne">
			<div class="panelVMI_0 btn" name="button" id="validRAZ">{{CONFIRMER}}</div>
			<div class="panelVMI_0 btn" name="button" id="cancelRAZ">{{ANNULER}}</div>
		</div>
	</div>
</div>
</div>

<script>
$( document ).ready(function() {
  $("#nbrJourBV").on("keydown", function(e) {
    if (e.keyCode === 13) {
			envoiJourBV($("#nbrJourBV").value());
			$("#nbrJourBV").value("");
    }
  });
	$(".valvalPilot").click(function(e) {
		envoiJourBV($("#nbrJourBV").value());
		$("#nbrJourBV").value("");
	});
	$(".cancelPilot").click(function(e) {
		if(selectedBoostVacance == 'boost'){
      select('boostSelect',0);
    }else if(selectedBoostVacance == 'vacances'){
      select('vacanceSelect',0);
    }
    selectedBoostVacance = '..';
    $('#myModalBoost').css('display','none');
	});
});
</script>

<?php
include_file('desktop', 'pilot', 'js', 'ventilairsec');
include_file('desktop', 'calendar', 'js', 'ventilairsec');
?>
