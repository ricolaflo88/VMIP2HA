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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
    <fieldset>
      <legend><i class="fas fa-list-alt"></i> {{Général}}</legend>
      <div class="form-group">
        <label class="col-lg-4 control-label">{{Supprimer automatiquement les périphériques exclus}}</label>
        <div class="col-lg-3">
           <input type="checkbox" class="configKey" data-l1key="autoRemoveExcludeDevice" />
       </div>
	</div>
	<div class="form-group">
	<label class="col-lg-4"></label>
	<div class="col-lg-8">
		<a class="btn btn-warning changeLogLive" data-log="logdebug"><i class="fas fa-cogs"></i> {{Mode debug forcé temporaire}}</a>
		<a class="btn btn-success changeLogLive" data-log="lognormal"><i class="fa fa-paperclip"></i> {{Remettre niveau de log local}}</a>
	</div>
	</div>
</div>
</fieldset>
<form class="form-horizontal">
    <fieldset>
       <legend><i class="icon loisir-darth"></i> {{Démon local}}</legend>
       <div class="form-group">
        <label class="col-lg-4 control-label">{{Port EnOcean}}</label>
        <div class="col-lg-4">
            <select class="configKey form-control" data-l1key="port">
                <option value="none">{{Aucun}}</option>
                <option value="auto">{{Auto}}</option>
                <?php
					if (class_exists('aruba')) {
						echo '<option value="aruba">Aruba</option>';
					}
foreach (jeedom::getUsbMapping('', true) as $name => $value) {
	echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
}
foreach (ls('/dev/', 'tty*') as $value) {
	echo '<option value="/dev/' . $value . '">/dev/' . $value . '</option>';
}

?>
           </select>
       </div>
   </div>
   <div class="form-group">
    <label class="col-lg-4 control-label">{{Port socket interne}}</label>
    <div class="col-lg-2">
        <input class="configKey form-control" data-l1key="socketport" />
    </div>
</div>
<div class="form-group">
    <label class="col-sm-4 control-label">{{Cycle (s)}}</label>
    <div class="col-sm-2">
        <input class="configKey form-control" data-l1key="cycle" />
    </div>
</div>
<div class="form-group">
    <label class="col-lg-4 control-label">{{Base ID}}</label>
    <div class="col-lg-2">
        <span class="configKey label label-info" data-l1key="baseid" style="font-size : 1em;" />
    </div>
</div>
<div class="form-group">
    <label class="col-lg-4 control-label">{{Informations sur le contrôleur}}</label>
    <div class="col-lg-8">
        <span class="configKey label label-info" data-l1key="app_description" title="{{Description de l'application}}"/>
        <span class="configKey label label-info" data-l1key="app_version" title="{{Version de l'application}}"/>
        <span class="configKey label label-info" data-l1key="api_version" title="{{Version de l'api}}"/>
        <span class="configKey label label-info" data-l1key="chip_version" title="{{Version de la puce}}"/>
    </div>
</div>
</fieldset>
</form>
<script>
 $('.changeLogLive').on('click', function () {
	 $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/openenocean/core/ajax/openenocean.ajax.php", // url du fichier php
            data: {
                action: "changeLogLive",
				level : $(this).attr('data-log')
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: '{{Réussie}}', level: 'success'});
            }
        });
});
</script>
