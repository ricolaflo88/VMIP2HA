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
    <div class="form-group">
      <label class="col-md-4 control-label">{{Mise à jour des mobiles pouvent recevoir les Notifications.}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Cliquer sur le bouton pour mettre à jour la liste des mobiles pouvent recevoir les Notifications.}}"></i></sup>
      </label>
      <div class="col-md-4">
        <a class="form-control btn btn-warning" id="bt_checknotif"><i class="fas fa-download"></i> {{Mettre à jour la liste}}</a>
      </div>
    </div>
<br /><br />
			<?php
			$arrayNotif = config::bykey('notif','ventilairsec', array());
			if($arrayNotif == array()){
				echo '<div class="form-group">';
				echo '<label class="col-md-4 control-label">{{Pas de Mobiles dans le plugin mobile.}}</label>';
				echo '</div>';
			}
			foreach ($arrayNotif as $key => $value){
				log::add('ventilairsec','debug','mobile > '.$key.' > '.$value);
				$mobileNotification = cmd::byId($key);
				if(is_object($mobileNotification)){
					$mobile = eqLogic::byId($mobileNotification->getEqLogic_id());
					log::add('ventilairsec','debug','mobile > '.$mobile->getName());
					if($value == 1){
						$check = 'checked';
					}else{
						$check = '';
					}
					?>
					<div class="form-group">
						<input type="checkbox" class="NotifCheck" id="<?php echo $key; ?>" name="<?php echo $mobile->getName(); ?>" <?php echo $check; ?>> <label class="col-md-4 control-label">{{Notification d'alerte sur le mobile}} "<?php echo $mobile->getName(); ?>"</label>
					</div>
					<?php
				}
			}
			 ?>
	</fieldset>
	</form>
<script>
$('#bt_savePluginConfig').off('click').on('click',function(){
	var yourArray = [];
	$("input:checkbox.NotifCheck").each(function(){
			if($(this).val() == "on"){
				yourArray[this.id] = 1;
			}else{
				yourArray[this.id] = 0;
			}
	});
	jeedom.config.save({
        configuration: {
          'notif': yourArray,
					'plugin': 'ventilairsec'
        },
				plugin : 'ventilairsec',
        error: function(error) {
          $.fn.showAlert({
            message: error.message,
            level: 'danger'
          })
        },
        success: function(data) {
					$.fn.showAlert({
            message: '{{Configuration effectuée}}',
            level: 'success'
          })
					$.ajax({
			      type: "POST",
			      url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
			      data: {
			        action: "checknotif"
			      },
			      dataType: 'json',
			      error: function (request, status, error) {
			        handleAjaxError(request, status, error);
			      },
			      success: function (data) {
							return;
			      }
			    });
        }
      })
})
$('#bt_checknotif').off('click').on('click',function(){
    $.ajax({
      type: "POST",
      url: "plugins/ventilairsec/core/ajax/ventilairsec.ajax.php",
      data: {
        action: "checknotif"
      },
      dataType: 'json',
      error: function (request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function (data) {
				return;
      }
    });
  })
</script>
