<?php
if (!isConnect('admin')) {
	throw new Exception('Error 401 Unauthorized');
}
$plugin = plugin::byId('openenocean');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
function sortByOption($a, $b) {
	return strcmp($a['name'], $b['name']);
}
if (config::byKey('include_mode', 'openenocean', 0) == 1) {
	echo '<div class="alert jqAlert alert-warning" id="div_inclusionAlert" style="margin : 0px 5px 15px 15px; padding : 7px 35px 7px 15px;">{{Vous etes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}</div>';
} else {
	echo '<div id="div_inclusionAlert"></div>';
}
$actuators = array();
$sensors = array();

foreach (openenocean::devicesParameters() as $key => $info) {
	if (isset($info['actuator']) && $info['actuator'] == 1) {
		$actuators[$key] = $info;
	} else {
		$sensors[$key] = $info;
	}
}
uasort($sensors, 'sortByOption');
uasort($actuators, 'sortByOption');
?>

<div class="row row-overflow">
	<div class="col-lg-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br/>
				<span>{{Ajouter}}</span>
			</div>
			<?php
			if (config::byKey('include_mode', 'openenocean', 0) == 1) {
				echo '<div class="cursor changeIncludeState include card logoSecondary" data-mode="1" data-state="0">';
				echo '<i class="fas fa-sign-in-alt fa-rotate-90"></i>';
				echo '<br/>';
				echo '<span>{{Arrêter inclusion}}</span>';
				echo '</div>';
			} else {
				echo '<div class="cursor changeIncludeState include card logoSecondary" data-mode="1" data-state="1">';
				echo '<i class="fas fa-sign-in-alt fa-rotate-90"></i>';
				echo '<br/>';
				echo '<span>{{Mode inclusion}}</span>';
				echo '</div>';
			}
			if (config::byKey('exclude_mode', 'openenocean', 0) == 1) {
				echo '<div class="cursor changeIncludeState exclude card logoSecondary" data-mode="0" data-state="0" >';
				echo '<i class="fas fa-sign-out-alt fa-rotate-90"></i>';
				echo '<br/>';
				echo '<span>{{Arrêter exclusion}}</span>';
				echo '</div>';
			} else {
				echo '<div class="cursor changeIncludeState exclude card logoSecondary" data-mode="0" data-state="1">';
				echo '<i class="fas fa-sign-out-alt fa-rotate-90"></i>';
				echo '<br/>';
				echo '<span>{{Mode exclusion}}</span>';
				echo '</div>';
			}
			?>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf" >
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_healthopenenocean">
				<i class="fas fa-medkit"></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i>  {{Mes équipements EnOcean}}</legend>
		<div class="input-group" style="margin:5px;">
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
		<div class="input-group-btn">
			<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
		</div>
	</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				$alternateImg = $eqLogic->getConfiguration('iconModel');
				if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $alternateImg . '.jpg')) {
					echo '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $alternateImg . '.jpg"/>';
				} elseif (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg')) {
					echo '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg"/>';
				} else {
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				}
				echo '<br/>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>
	
	<div class="col-lg-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="row">
					<div class="col-sm-7">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom de l'équipement EnOcean}}</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="lastRepeat" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="Nom de l'équipement EnOcean"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ID}}</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="Logical ID"/>
									</div>
									<label class="col-sm-2 control-label action-id" style="display:none;">{{ID émission}}</label>
									<div class="col-sm-7 action-id" style="display:none;">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="actionid" placeholder="ID émission"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label"></label>
									<div class="col-sm-7">
										<label class="checkbox-inline help" data-help="{{Utile est valable que pour les commandes de type generic}}"><input type="checkbox" class="eqLogicAttr twoids" data-l1key="configuration" data-l2key="twoids" />{{Différencier IDs émission/réception}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label"></label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Objet parent}}</label>
									<div class="col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">Aucun</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Catégorie}}</label>
									<div class="col-sm-9">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>
										
									</div>
								</div>
							</fieldset>
						</form>
					</div>
					<div class="col-sm-5">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label"></label>
									<div class="col-sm-8">
										<a class="btn btn-danger" id="bt_autoDetectModule"><i class="fas fa-search" title="{{Recréer les commandes}}"></i>  {{Recréer les commandes}}</a>
										<a class="btn btn-primary paramDevice" id="bt_configureDevice" style="display:none"><i class="fas fa-wrench"></i>  {{Configuration}}</a>
										<a class="btn btn-primary repeatDevice" id="bt_configureRepeat" style="display:none"><i class="fa fa-rss"></i>  {{Répéteur}}</a>
										<a class="btn btn-warning remcom" id="bt_configureRemcom" style="display:none"><i class="fa fa-qrcode"></i>  {{Remote Management}}</a>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Equipement}}</label>
									<div class="col-sm-8">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device">
											<option value="">Aucun</option>
											<?php
											$groups = array();
											
											foreach (openenocean::devicesParameters() as $key => $info) {
												if (isset($info['groupe'])) {
													$info['key'] = $key;
													if (!isset($groups[$info['groupe']])) {
														$groups[$info['groupe']][0] = $info;
													} else {
														array_push($groups[$info['groupe']], $info);
													}
												}
											}
											ksort($groups);
											foreach ($groups as $group) {
												usort($group, function ($a, $b) {
													return strcmp($a['name'], $b['name']);
												});
												foreach ($group as $key => $info) {
													if ($key == 0) {
														echo '<optgroup label="{{' . $info['groupe'] . '}}">';
													}
													echo '<option value="' . $info['key'] . '">' . $info['name'] . '</option>';
												}
												echo '</optgroup>';
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group modelList" style="display:none;">
									<label class="col-sm-2 control-label">{{Modèle}}</label>
									<div class="col-sm-8">
										<select class="eqLogicAttr form-control listModel" data-l1key="configuration" data-l2key="iconModel">
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Rorg}}</label>
									<div class="col-sm-2">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="rorg" >
									</div>
									<label class="col-sm-1 control-label">{{Func}}</label>
									<div class="col-sm-2">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="func" >
									</div>
									<label class="col-sm-1 control-label">{{Type}}</label>
									<div class="col-sm-2">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Création}}</label>
									<div class="col-sm-3">
										<span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="createtime" title="{{Date de création de l'équipement}}" style="font-size : 1em;cursor : default;"></span>
									</div>
									<label class="col-sm-3 control-label">{{Communication}}</label>
									<div class="col-sm-3">
										<span class="eqLogicAttr label label-default" data-l1key="status" data-l2key="lastCommunication" title="{{Date de dernière communication}}" style="font-size : 1em;cursor : default;"></span>
									</div>
								</div>
								<center>
									<img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;"  onerror="this.src='plugins/openenocean/plugin_info/openenocean_icon.png'"/>
								</center>
								<div class="form-group sourceTemp">
									<label class="col-sm-3 control-label">{{Température Source}}</label>
									<div class="input-group col-sm-9">
										<input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="sourceTemp" data-concat="1"/>
										<span class="input-group-btn">
											<a class="btn btn-default listCmdInfo roundedRight"><i class="fas fa-list-alt"></i></a>
										</span>
									</div>
								</div>
							</fieldset>
						</form>
					</br>
					<div class="alert alert-info globalRemark" style="display:none"></div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="commandtab">
			
			<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
			<table id="table_cmd" class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th style="width: 300px;">{{Nom}}</th>
						<th style="width: 130px;">{{Type}}</th>
						<th>{{Logical ID}}</th>
						<th>{{Paramètres}}</th>
						<th style="width: 300px;">{{Options}}</th>
						<th style="width: 150px;"></th>
					</tr>
				</thead>
				<tbody>
					
				</tbody>
			</table>
			
		</div>
	</div>
	
</div>
</div>

<?php include_file('desktop', 'openenocean', 'js', 'openenocean');?>
<?php include_file('core', 'plugin.template', 'js');?>
