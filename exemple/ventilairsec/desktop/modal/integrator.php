<?php
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

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$allEqLogics = eqLogic::byType('ventilairsec');
if (count($allEqLogics)  == 0) {
	$ventilairsec = $allEqLogics[0];
}
?>

<div class="col-xs-12" style="display: none" id="div_md_alert"></div>
<div class="params" style="user-select: none;">
	<div id="step1">
		<label style="font-size:22px;width:100%;">{{Étape 1 : Intégration de la VMI}}
			<?php
			$allEqLogics = eqLogic::byType('ventilairsec');
			if (count($allEqLogics)  == 0) {
				echo '<i class="icon_red fas fa-times-circle"></i></label>';
				echo '<div class="col-xs-12 alert alert-danger">{{Aucune VMI incluse. Veuillez mettre en service la VMI et procéder à l\'appairage en cliquant sur ce bouton : }}';
				echo '<a id="includeVMI" class="btn btn-default pull-right"><i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Appairage de la VMI}}</a></div>';
			} else if (count($allEqLogics)  == 1){
				echo '<i class="icon_green fas fa-check-circle"></i></label>';
				echo '<div class="col-xs-12 alert alert-success">{{VMI incluse. Vous pouvez passer à l\'étape suivante.}} </div>';
			} else if (count($allEqLogics) > 1){
				echo '<i class="icon_orange fas fa-exclamation-circle"></i></label>';
				echo '<div class="col-xs-12 alert alert-warning">{{Attention plusieurs VMI sont incluses. Veuillez en supprimer.}} </div>';
			}
			?>

			<div class="col-xs-12 text-center" style="margin:8px 0;">
				<small>{{Étape suivante}}</small>
				<br/>
				<i class="fas fa-arrow-down fa-2x cursor"></i>
			</div>
		</div>

		<div id="step2" style="display:none;">
			<label style="font-size:22px;width:100%;">{{Étape 2 : Configuration du compte market du client}}</label>

			<div class="row form-horizontal">
				<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Compte market du client}}</label>
				<div class="col-xs-11 col-sm-7">
					<input class="form-control param" data-l1key="marketUser"/>
				</div>
				<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Mot de passe market du client}}</label>
				<div class="col-xs-11 col-sm-7">
					<input type="password" class="form-control param" data-l1key="marketUserPass"/>
				</div>
				<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Nom de la box Jeedom}}</label>
				<div class="col-xs-11 col-sm-7">
					<input class="form-control param" data-l1key="boxName"/>
				</div>
			</div>

			<div class="col-xs-12 text-center" style="margin:8px 0;">
				<small>{{Étape suivante}}</small>
				<br/>
				<i class="fas fa-arrow-down fa-2x cursor"></i>
			</div>
		</div>


		<div id="step3" style="display:none;">
			<label style="font-size:22px;width:100%;">{{Étape 3 : Ajout de la box à la supervision}}
				<?php
				$apipro = config::byKey('clientApiPro','ventilairsec','');
				if ($apipro == '') {
					echo '<i class="icon_red fas fa-times-circle"></i></label>';
					echo '<div class="col-xs-12 alert alert-danger">{{Veuillez configurer la supervision ci-dessous svp.}} </div>';
				} else {
					echo '<i class="icon_green fas fa-check-circle"></i></label>';
					echo '<div class="col-xs-12 alert alert-success">{{Clé de supervision définie : }} ' . $apipro . '</div>';
				}
				?>
				<div class="row form-horizontal">
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Compte de l'installateur}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="integratorUser"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Mot de passe de l'installateur}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input type="password" class="form-control param" data-l1key="integratorPassword"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Nom de l'installation}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="installName"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Nom du client}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientName"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Adresse de l'installation}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientAdress"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Code postal de l'installation}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientPostal"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Ville de l'installation}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientCity"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Pays de l'installation}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientCountry"/>
					</div>
					<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Numéro de téléphone}}</label>
					<div class="col-xs-11 col-sm-7 col-md-3">
						<input class="form-control param" data-l1key="clientPhone"/>
					</div>

					<div class="col-xs-12 text-center">
						<a class="btn btn-success" id="bt_submit" style="margin-top:5px"><i class="fas fa-check"></i> {{Soumettre}}</a>
					</div>
				</div>

				<div class="col-xs-12 text-center" style="margin:8px 0;">
					<small>{{Étape suivante}}</small>
					<br/>
					<i class="fas fa-arrow-down fa-2x cursor"></i>
				</div>
			</div>

			<div id="step4" style="display:none;">
				<label style="font-size:22px;width:100%;">{{Étape 4 : Configuration de l'application mobile}}</label>
				<?php $pluginMobile = plugin::byId('mobile');
				if (!is_object($pluginMobile)) {
					echo '<div class="col-xs-12 alert alert-danger">{{Le plugin}} <a class="btn btn-default" href="https://market.jeedom.com/index.php?v=d&p=market_display&id=2030" target="_blank"> {{App Mobile}}</a> {{doit être installé}}</div>';
				}
				else {
					$pluginMobile->setIsEnable(1);

					$logicalId1 = config::bykey('mobile1','ventilairsec', null);
					if($logicalId1 != null){
						$mobile1 = eqLogic::byLogicalId($logicalId1,'mobile');
					}
					$logicalId2 = config::bykey('mobile2','ventilairsec', null);
					if($logicalId2 != null){
						$mobile2 = eqLogic::byLogicalId($logicalId2,'mobile');
					}
					$logicalId3 = config::bykey('mobile3','ventilairsec', null);
					if($logicalId3 != null){
						$mobile3 = eqLogic::byLogicalId($logicalId3,'mobile');
					}
					$logicalId4 = config::bykey('mobile4','ventilairsec', null);
					if($logicalId4 != null){
						$mobile4 = eqLogic::byLogicalId($logicalId4,'mobile');
					}

					if(!is_object($mobile1)){
						$mobile1 = new mobile();
						$mobile1->setEqType_name('mobile');
						$mobile1->setName('Mobile1');
						$mobile1->setConfiguration('type_mobile', 'ios');
						$mobile1->setConfiguration('affect_user', '1');
						$mobile1->setIsEnable(0);
						$mobile1->save();
						config::save('mobile1',$mobile1->getLogicalId(),'ventilairsec');
					}
					if(!is_object($mobile2)){
						$mobile2 = new mobile();
						$mobile2->setEqType_name('mobile');
						$mobile2->setName('Mobile2');
						$mobile2->setConfiguration('type_mobile', 'ios');
						$mobile2->setConfiguration('affect_user', '1');
						$mobile2->setIsEnable(0);
						$mobile2->save();
						config::save('mobile2',$mobile2->getLogicalId(),'ventilairsec');
					}
					if(!is_object($mobile3)){
						$mobile3 = new mobile();
						$mobile3->setEqType_name('mobile');
						$mobile3->setName('Mobile3');
						$mobile3->setConfiguration('type_mobile', 'ios');
						$mobile3->setConfiguration('affect_user', '1');
						$mobile3->setIsEnable(0);
						$mobile3->save();
						config::save('mobile3',$mobile3->getLogicalId(),'ventilairsec');
					}
					if(!is_object($mobile4)){
						$mobile4 = new mobile();
						$mobile4->setEqType_name('mobile');
						$mobile4->setName('Mobile4');
						$mobile4->setConfiguration('type_mobile', 'ios');
						$mobile4->setConfiguration('affect_user', '1');
						$mobile4->setIsEnable(0);
						$mobile4->save();
						config::save('mobile4',$mobile4->getLogicalId(),'ventilairsec');
					}

					ventilairsec::checknotif();

					?>
					<div class="col-xs-12">
						<label class="col-xs-12 col-sm-4 col-md-3 control-label">{{Combien souhaitez-vous configurer de mobile :}}</label>
						<div class="col-xs-11 col-sm-7 col-md-3">
							<select class="form-control param" data-l1key="nbrMobile" id="selectNbrMobile">
								<option value="1">{{1 Mobile}}</option>
								<option value="2">{{2 Mobiles}}</option>
								<option value="3">{{3 Mobiles}}</option>
								<option value="4">{{4 Mobiles}}</option>
							</select>
						</div>
					</div>
					<br />
					<div class="col-xs-12">
						<div class="col-xs-12">
							<div class="col-xs-6" style="border-radius:10px; border: 1px solid black; padding:10px;" id="mobile1Div">
								<label style="font-size:18px;width:100%;">{{Configuration 1er Mobile :}}</label>
								<div class="col-xs-8">
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Nom :}}</label>
										</div>
										<div class="col-xs-9">
											<input class="form-control param" data-l1key="nomMobile1" value="<?php echo $mobile1->getName(); ?>"/>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Type :}}</label>
										</div>
										<div class="col-xs-9">
											<select class="form-control param" data-l1key="typeMobile1">
												<option value="ios" <?php if($mobile1->getConfiguration('type_mobile') == 'ios'){echo 'selected';} ?>>{{iOS}}</option>
												<option value="android" <?php if($mobile1->getConfiguration('type_mobile') == 'android'){echo 'selected';} ?>>{{Android}}</option>
											</select>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-5">
											<label class="control-label">{{Notification :}}</label>
										</div>
										<div class="col-xs-7">
											<input type="checkbox" class="form-control param" data-l1key="NotifMobile1" checked/>
										</div>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="QrCode1"></div>
								</div>
							</div>
							<div class="col-xs-6" style="border-radius:10px; border: 1px solid black; padding:10px;" id="mobile2Div">
								<label style="font-size:18px;width:100%;">{{Configuration 2eme Mobile :}}</label>
								<div class="col-xs-8">
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Nom :}}</label>
										</div>
										<div class="col-xs-9">
											<input class="form-control param" data-l1key="nomMobile2" value="<?php echo $mobile2->getName(); ?>"/>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Type :}}</label>
										</div>
										<div class="col-xs-9">
											<select class="form-control param" data-l1key="typeMobile2">
												<option value="ios" <?php if($mobile2->getConfiguration('type_mobile') == 'ios'){echo 'selected';} ?>>{{iOS}}</option>
												<option value="android" <?php if($mobile2->getConfiguration('type_mobile') == 'android'){echo 'selected';} ?>>{{Android}}</option>
											</select>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-5">
											<label class="control-label">{{Notification :}}</label>
										</div>
										<div class="col-xs-7">
											<input type="checkbox" class="form-control param" data-l1key="NotifMobile2" checked/>
										</div>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="QrCode2"></div>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<div class="col-xs-6" style="border-radius:10px; border: 1px solid black; padding:10px;" id="mobile3Div">
								<label style="font-size:18px;width:100%;">{{Configuration 3eme Mobile :}}</label>
								<div class="col-xs-8">
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Nom :}}</label>
										</div>
										<div class="col-xs-9">
											<input class="form-control param" data-l1key="nomMobile3" value="<?php echo $mobile3->getName(); ?>"/>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Type :}}</label>
										</div>
										<div class="col-xs-9">
											<select class="form-control param" data-l1key="typeMobile3">
												<option value="ios" <?php if($mobile3->getConfiguration('type_mobile') == 'ios'){echo 'selected';} ?>>{{iOS}}</option>
												<option value="android" <?php if($mobile3->getConfiguration('type_mobile') == 'android'){echo 'selected';} ?>>{{Android}}</option>
											</select>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-5">
											<label class="control-label">{{Notification :}}</label>
										</div>
										<div class="col-xs-7">
											<input type="checkbox" class="form-control param" data-l1key="NotifMobile3" checked/>
										</div>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="QrCode3"></div>
								</div>
							</div>
							<div class="col-xs-6" style="border-radius:10px; border: 1px solid black; padding:10px;" id="mobile4Div">
								<label style="font-size:18px;width:100%;">{{Configuration 4eme Mobile :}}</label>
								<div class="col-xs-8">
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Nom :}}</label>
										</div>
										<div class="col-xs-9">
											<input class="form-control param" data-l1key="nomMobile4" value="<?php echo $mobile4->getName(); ?>"/>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-3">
											<label class="control-label">{{Type :}}</label>
										</div>
										<div class="col-xs-9">
											<select class="form-control param" data-l1key="typeMobile4">
												<option value="ios" <?php if($mobile4->getConfiguration('type_mobile') == 'ios'){echo 'selected';} ?>>{{iOS}}</option>
												<option value="android" <?php if($mobile4->getConfiguration('type_mobile') == 'android'){echo 'selected';} ?>>{{Android}}</option>
											</select>
										</div>
									</div>
									<div class="col-xs-12">
										<div class="col-xs-5">
											<label class="control-label">{{Notification :}}</label>
										</div>
										<div class="col-xs-7">
											<input type="checkbox" class="form-control param" data-l1key="NotifMobile4" checked/>
										</div>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="QrCode4"></div>
								</div>
							</div>
						</div>
					</div>
				<?php
			}
			?>
			<div class="col-xs-12 text-center">
				<a class="btn btn-success" id="bt_submitFinal" style="margin-top:5px"><i class="fas fa-check"></i> {{Finalisation}}</a>
			</div>
		</div>
	</div>
	<script>
	$( document ).ready(function() {

		configureDeviceLoad();

		$('input.param[data-l1key=marketUser], input.param[data-l1key=marketUserPass], input.param[data-l1key=boxName]').on('change', function() {
			if ($('input.param[data-l1key=marketUser]').value() == '' || $('input.param[data-l1key=marketUserPass]').value() == ''|| $('input.param[data-l1key=boxName]').value() == '') {
				$('#step2 > label').find('i').remove();
				$('#step2').find('div.alert').remove();
				$('#step2 > label').append(' <i class="icon_red fas fa-times-circle"></i>').after('<div class="col-xs-12 alert alert-danger">{{Le compte market du client n\'est pas configuré sur la box. Veuillez compléter les champs suivants :}}</div>');
			}
			else {
				$('#step2 > label').find('i').remove();
				$('#step2').find('div.alert').remove();
				$('#step2 > label').append(' <i class="icon_green fas fa-check-circle"></i>').after('<div class="col-xs-12 alert alert-success">{{Compte market configuré pour }}' + $('input.param[data-l1key=marketUser]').value() + '.</div>');;
			}
		})

		if ($('#step1 > label > i').hasClass('fa-check-circle')) {
			$('#step1').find('.fa-arrow-down').click();
		}
		$('#step1').find('.fa-arrow-down').on('click', function() {
			$('#step2').toggle();
			if ($('#step2 > label > i').hasClass('fa-check-circle')) {
				$('#step2').find('.fa-arrow-down').click();
			}
		});
		$('#step2').find('.fa-arrow-down').on('click', function() {
			$('#step3').toggle();
			if ($('#step3 > label > i').hasClass('fa-check-circle')) {
				$('#step3').find('.fa-arrow-down').click();
			}
		});
		$('#step3').find('.fa-arrow-down').on('click', function() {
			$('#step4').toggle();
		});

	});

	$('#activeAppMobile').on('click', function () {
		jeedom.plugin.toggle({
			id: 'mobile',
			state: 1,
			error: function(error) {
				$('#div_md_alert').showAlert({message: error.message, level: 'danger'})
			},
			success: function(data) {
				$('#md_modal').dialog('close');
				$('#md_modal').dialog({title: "{{Intégrateur VMI}}"});
				$('#md_modal').load('index.php?v=d&plugin=ventilairsec&modal=integrator').dialog('open');
			}
		})
	});

	$('#bt_deploy').on('click', function () {
		$('.iframemobile').toggle();
		$('#mobileapp').contents().find('body').css('padding-top', 'unset').find('header').hide();
		$('#mobileapp').contents().find('.ui-widget-overlay').hide();
	});

	$('#bt_submitFinal').on('click', function () {
		var valuesParams= {}
		$('.param').each(function( index ) {
			valuesParams[$(this).attr('data-l1key')] =$(this).value();
		});
		configureMobileSave(valuesParams);
	});

	$('#bt_submit').on('click', function () {
		var valuesParams= {}
		$('.param').each(function( index ) {
			valuesParams[$(this).attr('data-l1key')] = $(this).value();
		});
		configureDeviceSave(valuesParams);
	});

	$('#includeVMI').on('click', function () {
		$.ajax({
			type: "POST",
			url: "plugins/openenocean/core/ajax/openenocean.ajax.php",
			data: {
				action: "changeIncludeState",
				state: 1,
				mode: 1,
				type: 0,
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
	});

	function configureMobileSave(valuesParams) {
		$.ajax({
			type: "POST",
			url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
			data: {
				action: "setMobileConfiguration",
				parameters: json_encode(valuesParams)
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function (data) {
				$('#div_md_alert').showAlert({message: 'Paramètres envoyés avec succès', level: 'success'});
			}
		});
	}

	function configureDeviceSave(valuesParams) {
		$.ajax({
			type: "POST",
			url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
			data: {
				action: "setConfiguration",
				parameters: json_encode(valuesParams)
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_md_alert').showAlert({message: data.result, level: 'danger'});
					return;
				}
				if (data.result != '') {
					$('#div_md_alert').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('#div_md_alert').showAlert({message: 'Paramètres envoyés avec succès', level: 'success'});
			}
		});
	}

	function configureDeviceLoad() {
		$.ajax({// fonction permettant de faire de l'ajax
			type: "POST", // méthode de transmission des données au fichier php
			url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
			data: {
				action: "getConfiguration"
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error, $('#div_configureDeviceAlert'));
			},
			success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
					$('#div_md_alert').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('.params').setValues(data.result,'.param');
				QrCodeView();
			}
		});
	}

	$('.ui-dialog-titlebar-close').on('click', function () {
		setTimeout(function() {refresh()}, 1500);
	});

	$('#selectNbrMobile').change(function(){
		switch ($(this).val()) {
			case '1':
				$('#mobile1Div').show();
				$('#mobile2Div').hide();
				$('#mobile3Div').hide();
				$('#mobile4Div').hide();
			break;
			case '2':
				$('#mobile1Div').show();
				$('#mobile2Div').show();
				$('#mobile3Div').hide();
				$('#mobile4Div').hide();
			break;
			case '3':
				$('#mobile1Div').show();
				$('#mobile2Div').show();
				$('#mobile3Div').show();
				$('#mobile4Div').hide();
			break;
			case '4':
				$('#mobile1Div').show();
				$('#mobile2Div').show();
				$('#mobile3Div').show();
				$('#mobile4Div').show();
			break;
			default:
				$('#mobile1Div').show();
				$('#mobile2Div').hide();
				$('#mobile3Div').hide();
				$('#mobile4Div').hide();
		}
	})

	function QrCodeView(){
		qrCodeReload(<?php echo $mobile1->getId(); ?>,$('.QrCode1'));
		qrCodeReload(<?php echo $mobile2->getId(); ?>,$('.QrCode2'));
		qrCodeReload(<?php echo $mobile2->getId(); ?>,$('.QrCode3'));
		qrCodeReload(<?php echo $mobile2->getId(); ?>,$('.QrCode4'));
	}

	function qrCodeReload(eqLogicId, qrCodeDiv){
		$.ajax({
        type: "POST",
        url: "plugins/mobile/core/ajax/mobile.ajax.php",
        data: {
            action: "getQrCode",
            id: eqLogicId,
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
            if (data.result == 'internalError') {
              qrCodeDiv.empty().append('{{Erreur Pas d\'adresse interne (voir configuration de votre Jeedom !)}}');
          }else if(data.result == 'externalError'){
              qrCodeDiv.empty().append('{{Erreur Pas d\'adresse externe (voir configuration de votre Jeedom !)}}');
          }else if(data.result == 'UserError'){
              qrCodeDiv.empty().append('{{Erreur Pas d\'utilisateur selectionné}}');
          }else{
              qrCodeDiv.empty().append('<img src="data:image/png;base64, '+data.result+'" />');
          }
      }
  });
	}

	$('#mobile1Div').show();
	$('#mobile2Div').hide();
	$('#mobile3Div').hide();
	$('#mobile4Div').hide();
	</script>
