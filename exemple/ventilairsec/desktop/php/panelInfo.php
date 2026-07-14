<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('desktop', 'panelInfo', 'css', 'ventilairsec');
?>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<div class="panelVMI_body">
	<div class="panelVMI_alert noAlert">
		<div class="panelVMI_alert_left">
			<?php include 'plugins/ventilairsec/core/img/panne.svg'; ?>
		</div>
		<div class="panelVMI_alert_right">
			<span class="spanAlert">
				{{Alerte Moteur : XXX}}
			</span>
		</div>
	</div>
	<div class="panelVMI_in noAlert">
		<div class="panelVMI_top">
			<?php include 'plugins/ventilairsec/core/img/filtre.svg'; ?>
			<div class="panelVMI_top_jours">
				<div class="panelVMI_top_jours_text">
					<span class="panelVMI_top_jours_valeur filtre">
						N/A
					</span>
					<span class="panelVMI_top_jours_unite">
						{{% d'encrassement}}
					</span>
				</div>
			</div>
		</div>

		<div class="panelVMI_central">

			<div class="panelVMI_leftAll text-center">
				<div class="panelVMI_div panelVMI_div_co2">
					<div class="circle_div"><span class="panelVMI_circle circle_co2"></span></div>
					<div class="panelVMI_div_valeur">
							<span class="panelVMI_div_a_valeur co2val"> N/A </span>
							<span class="panelVMI_div_a_unite"> ppm </span>
					</div>
					<div class="panelVMI_div_title">
							<span>{{CO2}}</span>
					</div>
				</div>
				<div class="panelVMI_div panelVMI_div_hum">
					<div class="circle_div"><span class="panelVMI_circle circle_hum"></span></div>
					<div class="panelVMI_div_valeur">
							<span class="panelVMI_div_a_valeur humval">	N/A	</span>
							<span class="panelVMI_div_a_unite">	% </span>
					</div>
					<div class="panelVMI_div_title">{{Humidité intérieure}}</div>
				</div>
				<div class="panelVMI_div panelVMI_div_temp">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
							<span class="panelVMI_div_a_valeur soufflage"> N/A </span>
							<span class="panelVMI_div_a_unite">	°C </span>
					</div>
					<div class="panelVMI_div_title">{{T° de soufflage}}</div>
				</div>
				<div class="panelVMI_div panelVMI_div_surventilation">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
					</div>
					<div class="panelVMI_div_title">{{Surventilation}}</div>
				</div>
				<div class="panelVMI_div panelVMI_div_turbochauffe">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
					</div>
					<div class="panelVMI_div_title">{{TurboChauffe}}</div>
				</div>
				<div class="panelVMI_div panelVMI_div_resistance">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
					</div>
					<div class="panelVMI_div_title"></div>
				</div>
				<div class="panelVMI_div panelVMI_div_hydror">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
					</div>
					<div class="panelVMI_div_title">{{Hydro'R}}</div>
				</div>
				<div class="panelVMI_div panelVMI_div_bypass">
					<div class="circle_div"></div>
					<div class="panelVMI_div_valeur">
						<div class="bypasss_gauge_display">
						<img src="plugins/ventilairsec/core/img/by-pass-froid.png" height="85%"/>
						<div class="bypass_gauge"><div></div></div>
						<img src="plugins/ventilairsec/core/img/by-pass-chaud.png" height="85%"/>
						</div>
					</div>
					<div class="panelVMI_div_title">{{Position Bypass Smart}}</div>
				</div>
			</div> <!-- /.panelVMI_leftAll -->


			<div class="panelVMI_rightAll">
				<div class="panelVMI_right_top">
						<?php include 'plugins/ventilairsec/core/img/SOUFFLE_1.svg'; ?>
						<?php include 'plugins/ventilairsec/core/img/SOUFFLE_2.svg'; ?>
						<?php include 'plugins/ventilairsec/core/img/SOUFFLE_3.svg'; ?>
						<?php include 'plugins/ventilairsec/core/img/VALISE.svg'; ?>
						<?php include 'plugins/ventilairsec/core/img/BOOST.svg'; ?>
				</div>
				<div class="panelVMI_right_tree">
						<?php include 'plugins/ventilairsec/core/img/arbre.svg'; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'panelInfo', 'js', 'ventilairsec'); ?>
