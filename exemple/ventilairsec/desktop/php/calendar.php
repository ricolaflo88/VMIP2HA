<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div class="FloatAbsol" hidden>
	<div class="panelVMI_0 btn syncAgenda" name="button">{{SYNC.}}</div>
</div>
	<div class="Calendar_panelVMI_haut">
		<div class="Calendar_left_cross">
			<?php include 'plugins/ventilairsec/core/img/Artboard_left.svg'; ?>
		</div>
		<div class="Calendar_right_Panel">
			<div class="Bloc_haut Bloc_Jour">
				<div class="iconeHautCalendrier">
					<?php include 'plugins/ventilairsec/core/img/3jours_inactif.svg'; ?>
					<?php include 'plugins/ventilairsec/core/img/3jours_actif.svg'; ?>
				</div>
				<span class="textHautCalendrier">{{3 Jours}}</span>
			</div>
			<div class="Bloc_haut Bloc_Semaine">
				<div class="iconeHautCalendrier">
					<?php include 'plugins/ventilairsec/core/img/week_inactif.svg'; ?>
					<?php include 'plugins/ventilairsec/core/img/week_actif.svg'; ?>
				</div>
				<span class="textHautCalendrier">{{Semaine}}</span>
			</div>
			<div class="Bloc_haut Bloc_Actif">
					<span class="p_right verticalAlignClass calendarSelect"><?php include 'plugins/ventilairsec/core/img/Toggle_inactive.svg'; ?></span>
			</div>
		</div>
	</div>
	<div class="Calendar_panelVMI_Calendrier">
		<div class="Calendar_Day" id="Calendar_Day_ID">

		</div>
		<div class="calendar_grip_sur">
			<div class="Calendar_grip">
				<div class="calendrierParent" id="calendrierParent_ID">

				</div>
			</div>
		</div>
	</div>

	<div id="myModalCalendar" class="modal">
		<div class="modal-content">
			<div class="panelVMI_All">
				<div class="vmiPanelHide">
					<div class="panelVMI_ligne">
						<span class="TitleModal">
							<div class="panelVMI_groupForm">
								<div class="panelVMI_label"><span class="panelVMI_labelText"> {{de}} </span></div>
								<div class="panelVMI_select_valeur panelVMI_select_valeur_horaire_First"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
								<div style="width:20px;"></div>
								<div class="panelVMI_label"><span class="panelVMI_labelText"> {{à}} </span></div>
								<div class="panelVMI_select_valeur panelVMI_select_valeur_horaire_Two"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
							</div>
						</span>
					</div>
				</div>
				<div class="panelVMI_ligne ligne_70">
					<div class="panelVMI_ligne_global_ventil">
						<div class="panelVMI_ligne_ventil">
							<span class="bouton_ventil venti2SelectCalendar"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_1.svg'; ?></span>
						</div>
						<div class="panelVMI_ligne_ventil">
								<span class="bouton_ventil venti3SelectCalendar"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_2.svg'; ?></span>
						</div>
						<div class="panelVMI_ligne_ventil">
								<span class="bouton_ventil venti4SelectCalendar"><?php include 'plugins/ventilairsec/core/img/Radio_button_inactive.svg'; ?></span> <span class="venti"> <?php include 'plugins/ventilairsec/core/img/SOUFFLE_3.svg'; ?></span>
						</div>
					</div>
				</div>
				<div class="panelVMI_ligne">
					<div class="panelVMI_groupForm">
						<div class="panelVMI_label"><span class="panelVMI_labelText">{{Consigne Préchauffage}}</span></div>
						<div class="panelVMI_select_valeur panelVMI_select_valeur_prechauffage_calendar"><span class="panelVMI_label_texte"></span><span class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
					</div>
				</div>
				<div class="panelVMI_ligne_end">
					<div class="panelVMI_0 btn valval" name="button">{{VALIDER}}</div>
					<div class="panelVMI_0 btn supsup" name="button">{{SUPPRIMER}}</div>
				</div>
			</div>
	  </div>
	</div>

	<div id="myModalCalendarDay" class="modal">
		<div class="modal-content">
			<div class="panelVMI_All">
				<div class="panelVMI_ligne">
					<span class="TitleModalDay">{{Choisir les jours}}</span>
				</div>
				<div class="panelVMI_ligne">
					<div class="panelVMI_0 btn DayMardi" name="button">{{Lundi au Mercredi}}</div>
					<div class="panelVMI_0 btn DayMercredi" name="button">{{Mercredi au Vendredi}}</div>
					<div class="panelVMI_0 btn DaySamedi" name="button">{{Vendredi au Dimanche}}</div>
				</div>

			</div>
	  </div>
	</div>

	<style>
	<?php
		$j = 1;
		while($j <= $nbrTime){
			if($j < 10){
				echo '.time-0'.$j.'00{grid-row: '.$j.';}';
			}else{
				echo '.time-'.$j.'00{grid-row: '.$j.';}';
			}
			$j++;
		}
	 ?>
	</style>

<!-- NE PAS TOUCHER UTILISER PAR LE JS -->
	<div hidden id="venti1IconeHidden" class="iconInIn">
		<?php include 'plugins/ventilairsec/core/img/SOUFFLE_1.svg'; ?>
	</div>

	<div hidden id="venti2IconeHidden" class="iconInIn">
		<?php include 'plugins/ventilairsec/core/img/SOUFFLE_2.svg'; ?>
	</div>

	<div hidden id="venti3IconeHidden" class="iconInIn">
		<?php include 'plugins/ventilairsec/core/img/SOUFFLE_3.svg'; ?>
	</div>
