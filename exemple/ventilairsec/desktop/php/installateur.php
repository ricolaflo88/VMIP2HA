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
<div class="params">
<label class="col-sm-12" style="font-size:24px;margin-bottom:25px">{{Étape 1 : Intégration de la VMI}}</label>
<?php
	$allEqLogics = eqLogic::byType('ventilairsec');
	if (count($allEqLogics)  == 0) {
		echo '<span class="col-sm-12 alert alert-danger">{{Aucune VMI incluse. Veuillez mettre en service la VMI et une fois fait procéder à son inclusion sur la page précédente.}} </span>';
	} else if (count($allEqLogics)  == 1){
		echo '<span class="col-sm-12 alert alert-success">{{Une VMI incluse. Vous pouvez passer à la suite.}} </span>';
	} else if (count($allEqLogics) > 1){
		echo '<span class="col-sm-12 alert alert-warning">{{Attention plusieurs VMI incluses. Veuillez en supprimer une.}} </span>';
	}
?>
<hr class="col-sm-12" style="height:1px;border-top:1px solid #D3D3D3" />
<label class="col-sm-12" style="font-size:24px;margin-bottom:25px">{{Étape 2 : Configuration du compte market du client}}</label>
<?php
	$userMarket = config::byKey('market::username','core');
	$userPass = config::byKey('market::password','core');
	if ($userMarket == '' || $userPass == '') {
		echo '<span class="col-sm-12 alert alert-danger">{{Vous n\'avez pas configuré le compte market de la box.}} </span>';
	} else {
		echo '<span class="col-sm-12 alert alert-success">{{Compte market correctement configuré : }}' . $userMarket . '</span>';
	}
?>
<label class="col-sm-3">{{Compte market du client}}</label>
<input class="form-control col-sm-3 param" data-l1key="marketUser"/>
<label class="col-sm-3">{{Mot de passe market du client}}</label>
<input type="password" class="form-control col-sm-3 param" data-l1key="marketUserPass"/>
<label class="col-sm-3">{{Nom de la box Jeedom}}</label>
<input class="form-control col-sm-3 param" data-l1key="boxName"/>
<hr class="col-sm-12" style="height:1px;border-top:1px solid #D3D3D3" />
<label class="col-sm-12" style="font-size:24px;margin-bottom:25px">{{Étape 3 : Ajout de la box à la supervision}}</label>
<?php
	$apipro = config::byKey('clientApiPro','ventilairsec','');
	if ($apipro == '') {
		echo '<span class="col-sm-12 alert alert-danger">{{Vous n\'avez pas configuré la supervision.}} </span>';
	} else {
		echo '<span class="col-sm-12 alert alert-success">{{Clé supervision définie }}' . $apipro . '</span>';
	}
?>
<label class="col-sm-3">{{Compte de l'installateur}}</label>
<input class="form-control col-sm-3 param" data-l1key="integratorUser"/>
<label class="col-sm-3">{{Mot de passe de l'installateur}}</label>
<input type="password" class="form-control col-sm-3 param" data-l1key="integratorPassword"/>
<label class="col-sm-3">{{Nom de l'installation}}</label>
<input class="form-control col-sm-3 param" data-l1key="installName"/>
<label class="col-sm-3">{{Nom du client}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientName"/>
<label class="col-sm-3">{{Adresse de l'installation}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientAdress"/>
<label class="col-sm-3">{{Code postal de l'installation}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientPostal"/>
<label class="col-sm-3">{{Ville de l'installation}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientCity"/>
<label class="col-sm-3">{{Pays de l'installation}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientCountry"/>
<label class="col-sm-3">{{Numéro de téléphone}}</label>
<input class="form-control col-sm-3 param" data-l1key="clientPhone"/>
<a class="btn btn-success pull-right" style="color : white;" id="bt_submit"><i class="fas fa-check"></i> {{Soumettre}}</a>
<label class="col-sm-12" style="font-size:24px;margin-bottom:25px">{{Étape 4 : Configuration de l'application mobile}}</label>
<label class="col-sm-12">{{Cliquer sur déployer la fenêtre / Dans celle-ci : Cliquer sur Ajouter / Choisir un type de mobile et un utilisateur / Cliquer sur Sauver dans la fenêtre ci-dessous / Scanner le Qr-Code }}</label>
<a class="btn btn-success pull-left" style="color : white;" id="bt_deploy"><i class="fas fa-check"></i> {{Déployer/Masquer la fenêtre}}</a>
<iframe class="iframemobile" id="mobileapp"
    width="100%"
    height="800"
    src="/index.php?v=d&m=mobile&p=mobile" style="display:none">
</iframe>
</div>

<script>
configureDeviceLoad();
$('header').remove();
$('.backgroundforJeedom').remove();
$('#bt_deploy').on('click', function () {
	console.log($('.iframemobile').css('display'));
	if ($('.iframemobile').css('display')=='none'){
		$('.iframemobile').show();
	} else {
		$('.iframemobile').hide();
	}
});
$('#bt_submit').on('click', function () {
	var valuesParams= {}
	$('.param').each(function( index ) {
		valuesParams[$(this).attr('data-l1key')] =$(this).value();
	});
	configureDeviceSave(valuesParams);
});
function configureDeviceSave(valuesParams) {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // méthode de transmission des données au fichier php
            url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php", // url du fichier php
            data: {
                action: "setConfiguration",
                parameters: json_encode(valuesParams)
            },
            dataType: 'json',
            error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
			$('#md_modal').dialog('close');
            return;
        }
		if (data.result != '') {
			$('#div_alert').showAlert({message: data.result, level: 'danger'});
			$('#md_modal').dialog('close');
            return;
		}
		$('#div_alert').showAlert({message: 'Paramètres envoyés avec succès', level: 'success'});
		$('#md_modal').dialog('close');
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
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			}
			console.log(data.result);
			$('.params').setValues(data.result,'.param');
        }
    });
    }
</script>
