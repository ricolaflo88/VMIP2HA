<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

/* CHARST.JS */		
 echo '<script>';		
 require_once __DIR__  . '/../../3rparty/moment/moment.js';		
 require_once __DIR__  . '/../../3rparty/chart/Chart.min.js';		
 echo '</script>';

include_file('desktop', 'graph', 'css', 'ventilairsec');

/* Tableau Type et Object */
/* tableau a creer par une fonction class du plugin  LUDO*/
$liste = ventilairsec::getGraphList();

/* Ne pas toucher cette fonction elle permet de creer les listes en haut a gauche de la page */
$listeType = [];
$listTypeToobjects = [];
$objects = [];
$firstType = '';
$firstObject = '';
foreach ($liste as $key => $value) {
	$listeType[] = $key;
	$listTypeToobjects[$key] = $value;
	if($firstType == ''){
		$firstType = $key;
	}
	if($firstObject == ''){
		$firstObject = $value[0];
	}
	foreach ($value as $object) {
		if(!in_array($object, $objects)){
			$objects[] = $object;
		}
	}
}
echo '<script>
	var listeType = '.json_encode($listeType).';
	var listTypeToobjects = '.json_encode($listTypeToobjects).';
	var firstType = "'.$firstType.'";
	var firstObject = "'.$firstObject.'";
</script>';
log::add('ventilaisec','debug',print_r($listeType,true));
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<div class="graphVMI_body">
	<div class="graphVMI_top">
		<div class="graphVMI_top_1">
			<div class="panelVMI_select_valeur panelVMI_select_valeur_Type"><span class="panelVMI_label_texte"><?php echo $firstType; ?></span><span style="margin-top:15px" class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
		</div>
		<div class="graphVMI_top_2">
			<div class="panelVMI_select_valeur panelVMI_select_valeur_Object"><span class="panelVMI_label_texte"><?php echo str_replace('_',' ',$firstObject); ?></span><span style="margin-top:15px" class="crossSelect"><?php include 'plugins/ventilairsec/core/img/Artboard_bas.svg'; ?></span></div>
		</div>
		<div class="graphVMI_top_3">
			<div class="iconplusTest">
				<div class="iconGraph">
					<?php include 'plugins/ventilairsec/core/img/jours.svg'; ?>
				</div>
				<span class="titleIcon dayTitle">{{Jour}}</span>
			</div>
		</div>
		<div class="graphVMI_top_4">
			<div class="iconplusTest">
				<div class="iconGraph">
					<?php include 'plugins/ventilairsec/core/img/week.svg'; ?>
				</div>
				<span class="titleIcon weekTitle">{{Semaine}}</span>
			</div>
		</div>
	</div>
	<div class="graphVMI_middle">
		<div class="petiteBarre"></div>
		<canvas id="canvas"></canvas>
	</div>
	<div class="graphVMI_bottom">
		<div class="graphVMI_bottom_time">
			<div class="graphVMI_bottom_time_haut">
				<div class="caseSet caseSet_0"><span class="textCase"></span></div>
				<div class="caseSet caseSet_1"><span class="textCase"></span></div>
				<div class="caseSet caseSet_2"><span class="textCase"></span></div>
			</div>
			<div class="graphVMI_bottom_time_bas">
				<div class="triangle"></div>
			</div>
		</div>
		<div class="graphVMI_bottom_Valeur">
			<div class="graphVMI_bottom_Valeur_middle">
				<div class="graphVMI_bottom_Valeur_middle_top">
					<div class="centerText">
						<span class="dateHumain"></span><span class="XHumain"></span>
					</div>
				</div>
				<div class="graphVMI_bottom_Valeur_middle_middle">
					<div class="centerText">
						<span class="YHumain"></span><span class="uniteHumain"></span>
					</div>
				</div>
				<div class="graphVMI_bottom_Valeur_middle_bottom">
					<div class="centerText">
						<span class="TypeHumain"></span>
					</div>
				</div>
			</div>
			<div class="fleche fleche_Left">
				<?php include 'plugins/ventilairsec/core/img/Artboard_left.svg'; ?>
			</div>
			<div class="fleche fleche_Right">
				<?php include 'plugins/ventilairsec/core/img/Artboard_right.svg'; ?>
			</div>
		</div>
	</div>
</div>

<div id="myModal_Type" class="modal">
	<div class="modal-content">
		<div class="panelVMI_All">
			<?php
			foreach ($listeType as $type){
	    	echo '<div class="panelVMI_ligne" id="select_'.$type.'">
							<span class="modalTemperatureText">';
				echo $type;
				echo '</div>';
				?>
				<script>
				$( document ).ready(function() {
					$('#select_<?php echo $type; ?>').click(function(e) {
						selecteur('<?php echo $type; ?>');
						$('#myModal_Type').css('display','none');
					});
				});
				</script>
				<?php
			}
			 ?>
		</div>
	</div>
</div>

<div id="myModal_Object" class="modal">
	<div class="modal-content">
		<div class="panelVMI_All">
			<?php
			foreach ($objects as $object){
	    	echo '<div class="panelVMI_ligne ObjectAll" id="select_'.$object.'">
							<span class="modalTemperatureText">';
				echo str_replace('_',' ',$object);
				echo '</div>';
				?>
				<script>
				$( document ).ready(function() {
					$('#select_<?php echo $object; ?>').click(function(e) {
						selecteur(null,'<?php echo $object; ?>');
						$('#myModal_Object').css('display','none');
					});
				});
				</script>
				<?php
			}
			 ?>
		</div>
	</div>
</div>

<?php include_file('desktop', 'graph', 'js', 'ventilairsec'); ?>

<script>

var canvas = document.querySelector('canvas');
fitToContainer(canvas);

</script>
