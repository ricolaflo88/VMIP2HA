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

/* * ***************************Includes**********************************/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class ventilairsec extends eqLogic {
	/***************************Attributs*******************************/
	public static function discover(){
		$allEnoceanEqLogics = eqLogic::byType('openenocean');
		$found = 0;
		foreach ($allEnoceanEqLogics as $eqLogic) {
			if ($eqLogic->getConfiguration('device','') == 'd1079-01-00') {
				log::add('ventilairsec','info','Found a VMI with name ' . $eqLogic->getName());
				$found += 1;
				self::createVmi($eqLogic);
			}
		}
		return $found;
	}

	public static function cron(){
		log::add('ventilairsec','info','Checking capteur');
		$allEqLogics = eqLogic::byType('ventilairsec');
		foreach ($allEqLogics as $eqLogic) {
			$eqLogic->checkCapteur();
		}
	}
	
	public static function cron5(){
		log::add('ventilairsec','info','Checking health');
		$allEqLogics = eqLogic::byType('ventilairsec');
		foreach ($allEqLogics as $ventilairsec) {
			$labelerror = '';
			$errorlabel1 = '';
			$errorlabel2 = '';
			$erreur1=$ventilairsec->getCmd(null, 'CERR1::value')->execCmd();
			$erreur2=$ventilairsec->getCmd(null, 'CERR2::value')->execCmd();
			if ($erreur1 != '255' && $erreur1 != ''){
				$errorlabel1 = strtoupper(str_pad(dechex($erreur1),2,'0',STR_PAD_LEFT));
			}
			if ($erreur2 != '255' && $erreur2 != ''){
				$errorlabel2 = strtoupper(str_pad(dechex($erreur2),2,'0',STR_PAD_LEFT));
			}
			if ($errorlabel1.$errorlabel2 != ''){
				$labelerror = __('Pannes : ', __FILE__);
				if ($errorlabel1 != '') {
					$labelerror .= self::ErrorLabel()[$errorlabel1];
				}
				if ($errorlabel2 != '') {
					$labelerror .= ' / ' .self::ErrorLabel()[$errorlabel2];
				}
				ventilairsec::sendNotif($labelerror,__('Notifications VMI', __FILE__));
			}
			$update = $ventilairsec->getStatus('lastCommunication', date('Y-m-d H:i:s'));
			if ($update < date('Y-m-d H:i:s', strtotime('-45 minutes' . date('Y-m-d H:i:s')))){
				ventilairsec::sendNotif(__('Pas de communication de la VMI depuis 45 minutes', __FILE__),__('Notifications VMI', __FILE__));
			}
		}
	}

	public static function cronHourly(){
		log::add('ventilairsec','info','Sending Hour');
		self::sendAction('hour','');
		if (!(date('H')%3)){
			log::add('ventilairsec','info','Checking 3hours');
			self::rapportcsv();
		}
	}

	public static function cronDaily() {
		log::add('ventilairsec','info','send new mobile');
		ventilairsec::checknotif();
  }

	public static function ErrorLabel(){
		return array(
						'01' => __('Panne résistance', __FILE__),
						'02' => __('Trop froid pour chauffage', __FILE__),
						'10' => __('Panne moteur', __FILE__),
						'20' => __('Filtre à changer', __FILE__),
						'30' => __('Panne d’un capteur QAI', __FILE__),
						'31' => __('Panne capteur QAI n°1', __FILE__),
						'32' => __('Panne capteur QAI n°2', __FILE__),
						'33' => __('Panne capteur QAI n°3', __FILE__),
						'34' => __('Panne capteur QAI n°4', __FILE__),
						'35' => __('Panne capteur QAI n°5', __FILE__),
						'36' => __('Panne capteur QAI n°6', __FILE__),
						'37' => __('Panne capteur QAI n°7', __FILE__),
						'38' => __('Panne capteur QAI n°8', __FILE__),
						'39' => __('Panne capteur QAI n°9', __FILE__),
						'3A' => __('Problème sur plusieurs capteurs QAI', __FILE__),
						'3B' => __('Problème d’appairage d’un capteur QAI', __FILE__),
						'40' => __('Problème inconnu sur l’assistant', __FILE__),
						'41' => __('Erreur de température de l’assistant', __FILE__),
						'42' => __('Perte de communication de l’assistant', __FILE__),
						'43' => __('Piles faibles de l’assistant', __FILE__),
						'44' => __('Plusieurs pannes sur l’assistant', __FILE__),
						'51' => __('Panne sur la sonde en sortie des résistances', __FILE__),
						'52' => __('Panne sur la sonde en entrée de VMI®', __FILE__),
						'53' => __('Panne sur la sonde en sortie de HydroR®', __FILE__),
						'55' => __('Panne sur la sonde en sortie du Bypass', __FILE__),
						'56' => __('Panne sur la sonde côté air extérieur du Bypass', __FILE__),
						'57' => __('Panne sur la sonde côté comble du Bypass', __FILE__),
						'5F' => __('Plusieurs pannes sondes', __FILE__),
						'61' => __('Erreur sur le Bypass', __FILE__),
						'65' => __('Erreur téléchargement logiciel', __FILE__),
						'70' => __('Multiples erreurs', __FILE__),
						'81' => __('Problème de communication ByPass', __FILE__),
						'82' => __('Problème de communication ByPass', __FILE__),
						'83' => __('Volet du ByPass bloqué', __FILE__)
	);
}

	public static function immediateAction($_options){
		$ventilairsec = eqLogic::byId($_options['ventilairsec_id']);
		if (!is_object($ventilairsec)) {
			return;
		}
		if (!$ventilairsec->getIsEnable()){
			return;
		}
		log::add('ventilairsec', 'debug', $ventilairsec->getHumanName().' - Immediate Trigger from ' . print_r($_options,true));
		$target =null;
		foreach ($ventilairsec->getCmd('info') as $cmd) {
			if ($cmd->getConfiguration('oriId','') == $_options['event_id']){
				$target = $cmd;
				break;
			}
		}
		if (is_object($target)){
			$ventilairsec->checkAndUpdateCmd($target, $_options['value']);
			event::add('ventilairsec::modified', $ventilairsec->getId());
			if ($target->getLogicalId() =='IEFIL::value') {
				if ($_options['value'] > 90) {
					message::add('ventilairsec',__('Niveau filtre faible', __FILE__),'','ventilairsecErreurFiltre');
					ventilairsec::sendNotif(__('Niveau filtre faible', __FILE__),__('Notifications VMI', __FILE__));
				} else {
					message::removeAll('ventilairsec','ventilairsecErreurFiltre');
				}
			} else if ($target->getLogicalId() =='TGLOB::raw_value') {
				if ($_options['value'] == 0) {
					message::add('ventilairsec',__('Erreur de Sondes', __FILE__),'','ventilairsecErreurSonde');
					ventilairsec::sendNotif(__('Erreur de Sondes', __FILE__),__('Notifications VMI', __FILE__));
				} else {
					message::removeAll('ventilairsec','ventilairsecErreurSonde');
				}
			}
		}
	}

	public static function eventRefresh($_id){
		$ventilairsec = eqLogic::byId($_id);
		if (!is_object($ventilairsec)) {
			$allEqLogics = eqLogic::byType('ventilairsec');
			if (count($allEqLogics) >0) {
				$ventilairsec = $allEqLogics[0];
			}
		}
		if (!is_object($ventilairsec)) {
			return array('result'=>'nok');
		}
		$filter =  $ventilairsec->getCmd(null, 'IEFIL::value')->execCmd();
		$vitessemoteur = $ventilairsec->getCmd(null, 'CVITM::raw_value')->execCmd();
		$tempsoufflage = $ventilairsec->getCmd(null, 'TEMP0::value')->execCmd();
		if ($ventilairsec->getCmd(null, 'POS2::raw_value')->execCmd() == '2') {
			$tempsoufflage = $ventilairsec->getCmd(null, 'TEMP2::value')->execCmd();
		}
		//erreur
		$labelerror = '';
		$errorlabel1 = '';
		$errorlabel2 = '';
		$erreur1=$ventilairsec->getCmd(null, 'CERR1::value')->execCmd();
		$erreur2=$ventilairsec->getCmd(null, 'CERR2::value')->execCmd();
		if ($erreur1 != '255' && $erreur1 != ''){
			$errorlabel1 = strtoupper(str_pad(dechex($erreur1),2,'0',STR_PAD_LEFT));
		}
		if ($erreur2 != '255' && $erreur2 != ''){
			$errorlabel2 = strtoupper(str_pad(dechex($erreur2),2,'0',STR_PAD_LEFT));
		}
		if ($errorlabel1.$errorlabel2 != ''){
			$labelerror = __('Pannes : ', __FILE__);
			if ($errorlabel1 != '') {
				$labelerror .= self::ErrorLabel()[$errorlabel1];
			}
			if ($errorlabel2 != '') {
				$labelerror .= ' / ' .self::ErrorLabel()[$errorlabel2];
			}
		}
      		$update = $ventilairsec->getStatus('lastCommunication', date('Y-m-d H:i:s'));
		if ($update < date('Y-m-d H:i:s', strtotime('-45 minutes' . date('Y-m-d H:i:s')))){
        		log::add('ventilairsec','info','No communication');
            		$labelerror = __('Pannes : Pas de communication de la VMI depuis plus de 45 minutes', __FILE__);
		}
		//mode
		$modefonc=$ventilairsec->getCmd(null, 'MF::raw_value')->execCmd();
		$booststate = $ventilairsec->getCmd(null, 'BOOS::raw_value')->execCmd();
		$vacancesstate = $ventilairsec->getCmd(null, 'VAC::raw_value')->execCmd();
		if ($booststate == 1){
			$mode = 1;
		} else if ($vacancesstate == 1) {
			$mode = 5;
		} else if ($modefonc == 1) {
			$mode = 2;
		} else if ($modefonc == 2) {
			$mode = 3;
		} else if ($modefonc == 3) {
			$mode = 4;
		} else {
			$mode = 6;
		}
		//contact
		$idmachine = $ventilairsec->getCmd(null, 'IDMACH::value')->execCmd();
		$elecv = substr(str_pad(dechex($ventilairsec->getCmd(null, 'VELEC::value')->execCmd()),2,'0'),0,1) . '.' .substr(str_pad(dechex($ventilairsec->getCmd(null, 'VELEC::value')->execCmd()),2,'0'),1,1) ;
		$logicielv = substr(str_pad(dechex($ventilairsec->getCmd(null, 'VLOG::value')->execCmd()),2,'0'),0,1). '.'.substr(str_pad(dechex($ventilairsec->getCmd(null, 'VLOG::value')->execCmd()),2,'0'),1,1);
		$caisson =  $ventilairsec->getCmd(null, 'TYPCAI::value')->execCmd();
		$hydror =  $ventilairsec->getCmd(null, 'HYDROR::raw_value')->execCmd();
		$solarr =  $ventilairsec->getCmd(null, 'SOLARR::raw_value')->execCmd();
		$typprech = 'Electrique';
		if ($hydror == 1 && $solarr ==1) {
			$typprech = "Hydro'R + Solar'R";
		} else if ($hydror ==1) {
			$typprech = "Hydro'R";
		} else if ($solarr == 1){
			$typprech = "Solar'R";
		}
		//pilot
		$surventilationstate = $ventilairsec->getCmd(null, 'SURV::raw_value')->execCmd();
		$debitstate = $ventilairsec->getCmd(null, 'DEBF::raw_value')->execCmd();
		$modefonc = $ventilairsec->getCmd(null, 'MF::raw_value')->execCmd();
		$modebypass = $ventilairsec->getCmd(null, 'BYP::raw_value')->execCmd();
		$bypassamo = $ventilairsec->getCmd(null, 'BYPAMO::raw_value')->execCmd();
		$tempelec = $ventilairsec->getCmd(null, 'TEMPCELEC::value')->execCmd();
		$tempcsoufflage = $ventilairsec->getCmd(null, 'TEMPMSOUFFL::value')->execCmd();
		$temphydror = $ventilairsec->getCmd(null, 'TEMPCHYDROR::value')->execCmd();
		$tempsolarr = $ventilairsec->getCmd(null, 'TEMPCSOLAR::value')->execCmd();

		//capteurs
		$assistantInfo = $ventilairsec->getCmd(null, 'CAPTEUR9::value')->execCmd();
		$humiditeAss = '-';
		if ($assistantInfo != '') {
			$detailsAss = explode('|',$assistantInfo);
			$id = $detailsAss[0];
			$type = $detailsAss[2];
			if ($type=='MSC Assistant Ventilairsec') {
				$eqLogicAss = eqLogic::byLogicalId($id,'openenocean');
				if (is_object($eqLogicAss)) {
					$humiditeAss = $eqLogicAss->getCmd(null, 'HUM::value')->execCmd();
				}
			}
		}
		//v2
		$saison = $ventilairsec->getCmd(null, 'SAIS::raw_value')->execCmd();
		$surven = $ventilairsec->getCmd(null, 'SURV::raw_value')->execCmd();
		$posbypass = $ventilairsec->getCmd(null, 'POSBY1::raw_value')->execCmd();
		$ouvbypass = $ventilairsec->getCmd(null, 'OUVBY1::value')->execCmd();
		$puissancechauff = $ventilairsec->getCmd(null, 'PCHAUFF::value')->execCmd();
		$hydrorV = $ventilairsec->getCmd(null, 'OUVHYDR::value')->execCmd();
		$co2value = '-';
		for($i = 0; $i < 10; $i+=1) {
			$co2Info = $ventilairsec->getCmd(null, 'CAPTEUR'.$i.'::value')->execCmd();
			if ($co2Info != '') {
				$detailsCo2 = explode('|',$co2Info);
				$id = $detailsCo2[0];
				$type = $detailsCo2[2];
				if ($type=='A5_09_04') {
					$eqLogicCo2 = eqLogic::byLogicalId($id,'openenocean');
					if (is_object($eqLogicCo2)) {
						$co2value = $eqLogicCo2->getCmd(null, 'CONC::value')->execCmd();
						break;
					}
				}
				if ($type=='D2_04_08') {
					$eqLogicCo2 = eqLogic::byLogicalId($id,'openenocean');
					if (is_object($eqLogicCo2)) {
						$co2value = $eqLogicCo2->getCmd(null, 'CO2::value')->execCmd();
						break;
					}
				}
			}
		}

		//calendar
		$calendarstate = config::byKey('agendaActive','ventilairsec',1);

		$datas = array('result'=>'ok',
						'filter' => $filter,
						'vitessemoteur' =>$vitessemoteur,
						'tempsoufflage' =>$tempsoufflage,
						'erreur' =>$labelerror,
						'humiditeAss' =>$humiditeAss,
						'co2value' =>$co2value,
						'mode' =>$mode,
						'idmachine' => $idmachine,
						'logicielv' => $logicielv,
						'elecv' => $elecv,
						'typprech' => $typprech,
						'hydror' => $hydror,
						'solarr' => $solarr,
						'caisson' => $caisson,
						'booststate' => $booststate,
						'calendarstate' => $calendarstate,
						'vacancesstate' => $vacancesstate,
						'surventilationstate' => $surventilationstate,
						'debitstate' => $debitstate,
						'modefonc' => $modefonc,
						'modebypass' => $modebypass,
						'bypassamo' => $bypassamo,
						'tempcelec' => $tempelec,
						'tempcsoufflage' => $tempcsoufflage,
						'tempchydror' => $temphydror,
						'tempcsolarr' => $tempsolarr,
						'saison' => $saison,
						'surven' => $surven,
						'posbypass' => $posbypass,
						'ouvbypass' => $ouvbypass,
						'puissancechauff' => $puissancechauff,
						'hydrorV' => $hydrorV,
		);
		return $datas;
	}

	public function rapportcsv() {
		$captType=array('Température','Humidité','Co2');
		$typeprof = array('MSC Assistant Ventilairsec' => array('Température'=>'TEMP::value','Humidité'=>'HUM::value'),
							'A5_04_01' => array('Température'=>'TMP::value','Humidité'=>'HUM::value'),
							'A5_09_04' => array('Température'=>'TMP::value','Humidité'=>'HUM::value','Co2'=>'CONC::value'),
							'D2_04_08' => array('Température'=>'TMP::value','Humidité'=>'HUM::value','Co2'=>'CO2::value'),
					);
		$csvCmds = array('MF::value'=>array('name'=>'Mode','type'=>'string'),
						'ETATS::value'=>array('name'=>'Etat système','type'=>'string'),
						'PLH::raw_value'=>array('name'=>'Plage horaire actives','type'=>'binary'),
						'DEBF::raw_value'=>array('name'=>'Débit fixe','type'=>'binary'),
						'SURV::raw_value'=>array('name'=>'Surventilation','type'=>'binary'),
						'VAC::raw_value'=>array('name'=>'Vacances','type'=>'binary'),
						'BOOS::raw_value'=>array('name'=>'Boost','type'=>'binary'),
						'TEMPCELEC::value'=>array('name'=>'Temp Consigne Elec','type'=>'numeric'),
						'TEMPMSOUFFL::value'=>array('name'=>'Tmax','type'=>'numeric'),
						'TEMPCHYDROR::value'=>array('name'=>'Temp consigne HydroR','type'=>'numeric'),
						'TEMPCSOLAR::value'=>array('name'=>'Temp Confort Utilisateur','type'=>'numeric'),
						'SAIS::value'=>array('name'=>'Saison','type'=>'string'),
						'DEBAS::value'=>array('name'=>'Débit air (m3/h)','type'=>'numeric'),
						'PCHAUFF::value'=>array('name'=>'Puissance Chauffage','type'=>'numeric'),
						'CVITM::raw_value'=>array('name'=>'Consigne vitesse moteur','type'=>'numeric'),
						'TEMPEXT::value'=>array('name'=>'Température extérieure','type'=>'numeric'),
						'CPDIFF::value'=>array('name'=>'Consigne pression diff (Pa)','type'=>'numeric'),
						'IEFIL::value'=>array('name'=>'Encrassement filtre (%)','type'=>'numeric'),
						'NBSDMAINT::value'=>array('name'=>'Nb semaine depuis maintenance','type'=>'numeric'),
						'DFONC::value'=>array('name'=>'Durée fonctionnement (j)','type'=>'numeric'),
						'CERR1::value'=>array('name'=>'Code erreur 1','type'=>'errorhex'),
						'CERR2::value'=>array('name'=>'Code erreur 2','type'=>'errorhex'),
						'BYPAMO::raw_value'=>array('name'=>'Bypass amont présent','type'=>'binary'),
						'VVENT::value'=>array('name'=>'Volume à ventiler (m3)','type'=>'numeric'),
						'VLOG::value'=>array('name'=>'Version logiciel embarqué','type'=>'stringhex'),
						'POS0::value'=>array('name'=>'Sonde 0','type'=>'string'),
						'TEMP0::value'=>array('name'=>'Température 0','type'=>'numeric'),
						'HUM0::value'=>array('name'=>'Humidité 0','type'=>'numeric'),
						'POS1::value'=>array('name'=>'Sonde 1','type'=>'string'),
						'TEMP1::value'=>array('name'=>'Température 1','type'=>'numeric'),
						'HUM1::value'=>array('name'=>'Humidité 1','type'=>'numeric'),
						'POS2::value'=>array('name'=>'Sonde 2','type'=>'string'),
						'TEMP2::value'=>array('name'=>'Température 2','type'=>'numeric'),
						'HUM2::value'=>array('name'=>'Humidité 2','type'=>'numeric'),
						'POS3::value'=>array('name'=>'Sonde 3','type'=>'string'),
						'TEMP3::value'=>array('name'=>'Température 3','type'=>'numeric'),
						'HUM3::value'=>array('name'=>'Humidité 3','type'=>'numeric'),
						'POS4::value'=>array('name'=>'Sonde 4','type'=>'string'),
						'TEMP4::value'=>array('name'=>'Température 4','type'=>'numeric'),
						'HUM4::value'=>array('name'=>'Humidité 4','type'=>'numeric'),
						'CAPTEUR0::value'=>array('name'=>'Capteur 0','type'=>'string'),
						'CAPTEUR1::value'=>array('name'=>'Capteur 1','type'=>'string'),
						'CAPTEUR2::value'=>array('name'=>'Capteur 2','type'=>'string'),
						'CAPTEUR3::value'=>array('name'=>'Capteur 3','type'=>'string'),
						'CAPTEUR4::value'=>array('name'=>'Capteur 4','type'=>'string'),
						'CAPTEUR5::value'=>array('name'=>'Capteur 5','type'=>'string'),
						'CAPTEUR6::value'=>array('name'=>'Capteur 6','type'=>'string'),
						'CAPTEUR7::value'=>array('name'=>'Capteur 7','type'=>'string'),
						'CAPTEUR8::value'=>array('name'=>'Capteur 8','type'=>'string'),
						'CAPTEUR9::value'=>array('name'=>'Capteur 9','type'=>'string'),
						'OUVHYDR::value'=>array('name'=>'Ouverture vanne HydroR','type'=>'numeric'),
						'BYP::value'=>array('name'=>'Bypass Type','type'=>'string'),
						'OUVBY1::value'=>array('name'=>'Ouverture bypass 1','type'=>'numeric'),
						'POSBY1::value'=>array('name'=>'Type bypass 1','type'=>'string'),
						'OUVBY2::value'=>array('name'=>'Ouverture bypass 2','type'=>'numeric'),
						'POSBY2::value'=>array('name'=>'Type bypass 2','type'=>'string'),
						'OUVBY3::value'=>array('name'=>'Ouverture bypass 3','type'=>'numeric'),
						'POSBY3::value'=>array('name'=>'Type bypass 3','type'=>'string'),
						'dBm'=>array('name'=>'Signal Radio VMI (dBm)','type'=>'numeric')
					);
		log::add('ventilairsec','debug','Begin Rapport CSV');
		$directory = dirname(__FILE__) . '/../../data/';
		if (!is_dir($directory)) {
			mkdir($directory);
		}
		$allEqLogics = eqLogic::byType('ventilairsec');
		if (count($allEqLogics) >0) {
			$ventilairsec = $allEqLogics[0];
		}
		if (!is_object($ventilairsec)) {
			return;
		}
		$createDate = $ventilairsec->getConfiguration('createtime');
		$csvarray=array();
		$now=date('Y-m-d H:i:00');
		$arrayCmd=[];
		foreach ($csvCmds as $logical=>$data) {
			$cmd=$ventilairsec->getCmd('info',$logical);
			$time=$now;
			$foundTime = $time;
			$lastvalue=$cmd->execCmd();
			if ($data['type'] == 'binary'){
				if ($lastvalue == ''){
					$lastvalue = 0;
				}
				$lastvalue = ceil($lastvalue);
			} else if ($data['type'] == 'stringhex') {
				$lastvalue = substr(str_pad(dechex($lastvalue),2,'0'),0,1) . '.' .substr(str_pad(dechex($lastvalue),2,'0'),1,1);
			} else if ($data['type'] == 'numeric') {
				if ($lastvalue == ''){
					$lastvalue = 0;
				}
				$lastvalue =round($lastvalue,1);
			} else if ($data['type'] == 'errorhex'){
				$lastvalue=strtoupper(str_pad(dechex($lastvalue),2,'0',STR_PAD_LEFT));
			}
			log::add('ventilairsec','debug',$cmd->getName());
			$arrayCmd[]=$data['name'];
			if ($data['type'] == 'numeric') {
				$csvarray[$time][$data['name']]=str_replace('.',',',$lastvalue);
			} else {
				$csvarray[$time][$data['name']]=$lastvalue;
			}
			$hist = $cmd->getHistory();
			for($i = 0; $i < 1000; $i+=1) {
				$delta = 10;
				if ($i>500){
					$delta = 240;
				} else if ($i>250){
					$delta = 120;
				} else if ($i>144){
					$delta = 60;
				}
				$time = date('Y-m-d H:i:00',strtotime($time. ' -'.$delta.' minutes'));
				if (strtotime($time) < strtotime($createDate)){
					break;
				}
				$found ='none';
				foreach($hist as $history){
					if(strtotime($history->getDatetime()) < strtotime($time) || strtotime($history->getDatetime()) < strtotime($foundTime)){
						$found = $history->getValue();
						$foundTime = $history->getDatetime();
					} else {
						break;
					}
				}
				if ($found == 'none'){
					$value = $lastvalue;
				} else {
					if ($data['type'] == 'binary'){
						if ($found == ''){
							$found = 0;
						}
						$found = ceil($found);
					} else if ($data['type'] == 'stringhex') {
						$found = substr(str_pad(dechex($found),2,'0'),0,1) . '.' .substr(str_pad(dechex($found),2,'0'),1,1);
					} else if ($data['type'] == 'numeric') {
						if ($found == ''){
							$found = 0;
						}
						$found = round($found,1);
					} else if ($data['type'] == 'errorhex'){
						$found=strtoupper(str_pad(dechex($found),2,'0',STR_PAD_LEFT));
					}
					$value = $found;
				}
				if ($data['type'] == 'numeric') {
					$csvarray[$time][$data['name']]=str_replace('.',',',$value);
				} else {
					$csvarray[$time][$data['name']]=$value;
				}
				$lastvalue=$value;
			}
			if (substr($logical,0,7) == 'CAPTEUR'){
				log::add('ventilairsec','debug','Capteur to check ' . $lastvalue);
				$detailsCapt = explode('|',$cmd->execCmd());
				foreach ($captType as $Type) {
					$arrayCmd[]=$Type.' Capteur '.substr($logical,7,1);
					$timecapt=$now;
					$foundTimecapt = $timecapt;
					$targetEq = eqLogic::byLogicalId($detailsCapt[0],'openenocean');
					if (isset($detailsCapt[2]) && isset($typeprof[$detailsCapt[2]][$Type]) && is_object($targetEq)){
							$cmd = $targetEq->getCmd('info', $typeprof[$detailsCapt[2]][$Type]);
							if (is_object($cmd)){
								$lastvaluecapt=$cmd->execCmd();
								$histcapt = $cmd->getHistory();
								log::add('ventilairsec','debug',$cmd->getName());
							} else {
								$histcapt = array();
								$lastvaluecapt=0;
							}
					} else {
						$histcapt = array();
						$lastvaluecapt=0;
					}
					if ($lastvaluecapt == ''){
						$lastvaluecapt = 0;
					}
					$lastvaluecapt =round($lastvaluecapt,1);
					$csvarray[$timecapt][$Type.' Capteur '.substr($logical,7,1)]=str_replace('.',',',$lastvaluecapt);
					for($y = 0; $y < 1000; $y+=1) {
						$delta = 10;
						if ($y>500){
							$delta = 240;
						} else if ($y>250){
							$delta = 120;
						} else if ($y>144){
							$delta = 60;
						}
						$timecapt = date('Y-m-d H:i:00',strtotime($timecapt. ' -'.$delta.' minutes'));
						if (strtotime($timecapt) < strtotime($createDate)){
							break;
						}
						if (isset($detailsCapt[2]) && isset($typeprof[$detailsCapt[2]][$Type])){
							$cmd = $targetEq->getCmd('info', $typeprof[$detailsCapt[2]][$Type]);
							if (is_object($cmd)){
								$foundcapt ='none';
								foreach($histcapt as $historycapt){
									if(strtotime($historycapt->getDatetime()) < strtotime($timecapt) || strtotime($historycapt->getDatetime()) < strtotime($foundTimecapt)){
										$foundcapt = $historycapt->getValue();
										$foundTimecapt = $historycapt->getDatetime();
									} else {
										break;
									}
								}
								if ($foundcapt == 'none'){
									$value = $lastvaluecapt;
								} else {
									$value = $foundcapt;
								}
							} else {
								$value = 0;
							}
						} else {
								$value = 0;
						}
						if ($value == ''){
							$value = 0;
						}
						$value= round($value,1);
						$csvarray[$timecapt][$Type.' Capteur '.substr($logical,7,1)]= str_replace('.',',',$value);
						$lastvaluecapt=$value;
					}
				}
			}
		}
		$filename= $directory.'/histo.csv';
		$data = 'Identifiant machine : ' . $ventilairsec->getCmd('info','IDMACH::value')->execCmd()."\n";
		$data .= 'SolarR : ' . $ventilairsec->getCmd('info','SOLARR::raw_value')->execCmd()."\n";
		$data .= 'HyrdroR : ' . $ventilairsec->getCmd('info','HYDROR::raw_value')->execCmd()."\n";
		$versionElec = $ventilairsec->getCmd('info','VELEC::value')->execCmd();
		$versionElec = substr(str_pad(dechex($versionElec),2,'0'),0,1) . '.' .substr(str_pad(dechex($versionElec),2,'0'),1,1) ;
		$data .= utf8_decode('Version électronique : ') . $versionElec."\n";
		$data .= 'Date installation : ' .$ventilairsec->getCmd('info','JINST::value')->execCmd() . '/' .$ventilairsec->getCmd('info','MINST::value')->execCmd().'/'.$ventilairsec->getCmd('info','AINST::value')->execCmd() ."\n";
		$data .= 'Date ajout Jeedom : ' .$createDate."\n";
		$data .= 'Compte Market : ' .config::byKey('market::username','core')."\n";
		$data .= 'DNS : ' .config::byKey('jeedom::url','core')."\n";
		$data .= "Date";
		foreach ($arrayCmd as $name) {
			$data .=';'.utf8_decode($name);
		}
		$data .= "\n";
		foreach (array_reverse($csvarray) as $key =>$value) {
			$data.=$key;
			foreach ($value as $namecmd => $realvalue){
				$data.=';'.$realvalue;
			}
			$data .= "\n";
		}
		$fp = fopen($filename, 'w');
		fwrite($fp,$data);
		fclose($fp);
	}

	public static function getGraphList(){
		$allEqLogics = eqLogic::byType('ventilairsec');
		if (count($allEqLogics) >0) {
			$ventilairsec = $allEqLogics[0];
		}
		if (!is_object($ventilairsec)) {
			return;
		}
		$resultArray=array();
		for($i = 0; $i < 10; $i+=1) {
			$captInfo = $ventilairsec->getCmd(null, 'CAPTEUR'.$i.'::value')->execCmd();
			if ($captInfo != '') {
				$detailsCapt = explode('|',$captInfo);
				$type = $detailsCapt[2];
				$room = str_replace(' ','_',$detailsCapt[1]);
				if ($type=='A5_09_04') {
					if (isset($resultArray['Température'])) {
						if (!in_array($room,$resultArray['Température'])){
							$resultArray['Température'][] = $room;
						}
					} else {
						$resultArray['Température'] = array();
						$resultArray['Température'][] = $room;
					}
					if (isset($resultArray['Humidité'])) {
						if (!in_array($room,$resultArray['Humidité'])){
							$resultArray['Humidité'][] = $room;
						}
					} else {
						$resultArray['Humidité'] = array();
						$resultArray['Humidité'][] = $room;
					}
					if (isset($resultArray['Co2'])) {
						if (!in_array($room,$resultArray['Co2'])){
							$resultArray['Co2'][] = $room;
						}
					} else {
						$resultArray['Co2'] = array();
						$resultArray['Co2'][] = $room;
					}
				}
				else if ($type=='D2_04_08') {
					if (isset($resultArray['Température'])) {
						if (!in_array($room,$resultArray['Température'])){
							$resultArray['Température'][] = $room;
						}
					} else {
						$resultArray['Température'] = array();
						$resultArray['Température'][] = $room;
					}
					if (isset($resultArray['Humidité'])) {
						if (!in_array($room,$resultArray['Humidité'])){
							$resultArray['Humidité'][] = $room;
						}
					} else {
						$resultArray['Humidité'] = array();
						$resultArray['Humidité'][] = $room;
					}
					if (isset($resultArray['Co2'])) {
						if (!in_array($room,$resultArray['Co2'])){
							$resultArray['Co2'][] = $room;
						}
					} else {
						$resultArray['Co2'] = array();
						$resultArray['Co2'][] = $room;
					}
				}
				else if ($type=='A5_04_01') {
					if (isset($resultArray['Température'])) {
						if (!in_array($room,$resultArray['Température'])){
							$resultArray['Température'][] = $room;
						}
					} else {
						$resultArray['Température'] = array();
						$resultArray['Température'][] = $room;
					}
					if (isset($resultArray['Humidité'])) {
						if (!in_array($room,$resultArray['Humidité'])){
							$resultArray['Humidité'][] = $room;
						}
					} else {
						$resultArray['Humidité'] = array();
						$resultArray['Humidité'][] = $room;
					}
				}
				else if ($type=='MSC Assistant Ventilairsec') {
					if (isset($resultArray['Température'])) {
						if (!in_array($room,$resultArray['Température'])){
							$resultArray['Température'][] = $room;
						}
					} else {
						$resultArray['Température'] = array();
						$resultArray['Température'][] = $room;
					}
					if (isset($resultArray['Humidité'])) {
						if (!in_array($room,$resultArray['Humidité'])){
							$resultArray['Humidité'][] = $room;
						}
					} else {
						$resultArray['Humidité'] = array();
						$resultArray['Humidité'][] = $room;
					}
				}
			}
		}
		return $resultArray;
	}

	public static function getGraphData($_object,$_type,$_daytype,$_center = ''){
		$_object = str_replace('_',' ',$_object);
		$typeprof = array('MSC Assistant Ventilairsec' => array('Température'=>'TEMP::value','Humidité'=>'HUM::value'),
							'A5_04_01' => array('Température'=>'TMP::value','Humidité'=>'HUM::value'),
							'A5_09_04' => array('Température'=>'TMP::value','Humidité'=>'HUM::value','Co2'=>'CONC::value'),
							'D2_04_08' => array('Température'=>'TMP::value','Humidité'=>'HUM::value','Co2'=>'CO2::value'),
					);
		$allEqLogics = eqLogic::byType('ventilairsec');
		if (count($allEqLogics) >0) {
			$ventilairsec = $allEqLogics[0];
		}
		if (!is_object($ventilairsec)) {
			return;
		}
		$histcmd ='';
		for($i = 0; $i < 10; $i+=1) {
			$captInfo = $ventilairsec->getCmd(null, 'CAPTEUR'.$i.'::value')->execCmd();
			if ($captInfo != '') {
				$detailsCapt = explode('|',$captInfo);
				if ($detailsCapt[1] == $_object) {
					if (isset($typeprof[$detailsCapt[2]][$_type])){
						log::add('ventilairsec','debug','Found graph capteur : ' .$detailsCapt[0] . ' with logical command ' . $typeprof[$detailsCapt[2]][$_type]);
						$targetEq = eqLogic::byLogicalId($detailsCapt[0],'openenocean');
						$histcmd = $targetEq->getCmd(null, $typeprof[$detailsCapt[2]][$_type]);
						break;
					}
				}
			}
		}
		if ($histcmd != '') {
			log::add('ventilairsec','debug', 'Getting history ' . $_daytype . ' for cmd ' .$histcmd->getId());
			if ($_daytype == 'day'){
				if ($_center == '') {
					$end = date('Y-m-d H:59:59');
					$start = date('Y-m-d H:59:59',strtotime('-24 hours'));
				} else {
					$end = date('Y-m-d H:59:59',strtotime($_center . ' +12 hours'));
					if ($end>date('Y-m-d H:59:59')){
						$end = date('Y-m-d H:59:59');
						$start = date('Y-m-d H:59:59',strtotime('-24 hours'));
					} else {
						$start = date('Y-m-d H:59:59',strtotime($_center . ' -12 hours'));
					}
				}
			} else {
				if ($_center == '') {
					$end = date('Y-m-d 23:59:59');
					$start = date('Y-m-d 23:59:59',strtotime('-14 days'));
				} else {
					$end = date('Y-m-d 23:59:59',strtotime($_center . ' +7 days'));
					if ($end>date('Y-m-d 23:59:59')){
						$end = date('Y-m-d 23:59:59');
						$start = date('Y-m-d 23:59:59',strtotime('-14 days'));
					} else {
						$start = date('Y-m-d 23:59:59',strtotime($_center . ' -7 days'));
					}
				}
			}
			$historyResult = self::getGraph($histcmd, $start,$end,$_daytype);
		} else {
			log::add('ventilairsec','debug', 'No valid CMD found ');
			 $historyResult =array();
		}
		return $historyResult;
	}

	public static function getGraph($_cmd,$_start,$_end,$_daytype){
		log::add('ventilairsec','debug',$_start . ' - ' . $_end);
		$nom_jour_fr = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi");
		$mois_fr = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août",
		"septembre", "octobre", "novembre", "décembre");
		$values = array(
			'cmd_id' => $_cmd->getId(),
		);
		if ($_start !== null) {
			$values['startTime'] = $_start;
		}
		if ($_end !== null) {
			$values['endTime'] = $_end;
		}
		$sql = 'SELECT `cmd_id`,`datetime`,AVG(CAST(value AS DECIMAL(12,2))) as value';
		$sql .= ' FROM (SELECT `cmd_id`,`datetime`,value FROM history WHERE cmd_id=:cmd_id ';
		if ($_start !== null) {
			$sql .= ' AND datetime>=:startTime';
		}
		if ($_end !== null) {
			$sql .= ' AND datetime<=:endTime';
		}
		$sql .= ' UNION SELECT `cmd_id`,`datetime`,value';
		$sql .= ' FROM historyArch WHERE cmd_id=:cmd_id ';
		if ($_start !== null) {
			$sql .= ' AND datetime>=:startTime';
		}
		if ($_end !== null) {
			$sql .= ' AND datetime<=:endTime';
		}

		$sql .= ') as x';
		if ($_daytype == 'day') {
			$sql .= ' GROUP BY HOUR(`datetime`)';
		} else {
			$sql .= ' GROUP BY DAY(`datetime`)';
		}
		$sql.= ' ORDER BY `datetime` ASC';
		log::add('ventilairsec','debug',$sql);
		$result = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL,PDO::FETCH_CLASS, 'history');
		$historyResult = array();
		foreach ($result as $hist){
			$arraypoint=array();
			$date = strtotime($hist->getDatetime());
			if ($_daytype == 'day') {
				$arraypoint['title'] = date('H', $date).'h00';
				$arraypoint['titleHumain'] =date('H', $date).':00';
			} else {
				$arraypoint['title'] = date('d/m/Y', $date);
				$arraypoint['titleHumain'] =$nom_jour_fr[date('w', $date)] . ' ' .date('d/m', $date);
			}
			$arraypoint['date'] = $nom_jour_fr[date('w', $date)] . ' ' .date('d', $date) . ' ' . $mois_fr[date('n', $date)];
			$arraypoint['valeur'] = round($hist->getValue(),1);
			$arraypoint['unite'] =$_cmd->getUnite();
			$arraypoint['datetime'] =date('Y', $date) . '-' . date('m', $date) . '-' .  date('d', $date) . ' ' . date('H', $date) . ':59:59';
			$historyResult[] = $arraypoint;
		}
		$numberpoints = 24;
		if ($_daytype != 'day'){
			$numberpoints = 14;
		}
		log::add('ventilairsec','debug',print_r($historyResult,true));
		$count = count($historyResult);
		log::add('ventilairsec','debug',$count);
		if ($count<$numberpoints && $count>0) {
			for($i = 0; $i < $numberpoints-$count; $i+=1) {
				array_unshift($historyResult , $historyResult[0]);
			}
		}
		if ($count>$numberpoints) {
			for($i = 0; $i < $count-$numberpoints; $i+=1) {
				array_shift($historyResult);
			}
		}
		return $historyResult;
	}

	public static function getAgendaData(){
		$array = config::byKey('agendaData','ventilairsec');
		log::add('ventilairsec','debug',print_r($array,true));
		$newarray = array();
		foreach ($array as $line) {
			foreach ($line as $key => $value){
				if (in_array($key, array('id','start','end','temperature','ventil'))) {
					$line[$key]= intval($value);
				}
			}
			$newarray[]=$line;
		}
		log::add('ventilairsec','debug',print_r($newarray,true));
		return $newarray;
	}

	public static function sendAction($_type, $_value){
		$allEqLogics = eqLogic::byType('ventilairsec');
		if (count($allEqLogics) >0) {
			$ventilairsec = $allEqLogics[0];
		}
		if (!is_object($ventilairsec)) {
			return;
		}
		if ($_type == 'boost') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,BOOST:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Boost value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		}
		else if ($_type == 'vacances') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,VACS:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Vacances value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		}
		else if ($_type == 'modefonc') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,MODEFONC:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Mode value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		}
		else if ($_type == 'surventilation') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,FONC:#message#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			if (is_object($oriCmd)){
				$bypass=$ventilairsec->getCmd(null, 'BYP::raw_value')->execCmd();
				$plage=$ventilairsec->getCmd(null, 'PLH::raw_value')->execCmd();
				$debitfixe=$ventilairsec->getCmd(null, 'DEBF::raw_value')->execCmd();
				$value = '00'.strval($bypass).strval($plage).strval($debitfixe).'0'.$_value.'0';
				log::add('ventilairsec','debug','Surventilation value changed to ' . $value);
				$oriCmd->execute(array('message'=>$value));
			}
		}
		else if ($_type == 'debit') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,FONC:#message#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			if (is_object($oriCmd)){
				$bypass=$ventilairsec->getCmd(null, 'BYP::raw_value')->execCmd();
				$plage=$ventilairsec->getCmd(null, 'PLH::raw_value')->execCmd();
				$surv=$ventilairsec->getCmd(null, 'SURV::raw_value')->execCmd();
				$value = '00'.strval($bypass).strval($plage).$_value.'0'.strval($surv).'0';
				log::add('ventilairsec','debug','Débit value changed to ' . $value);
				$oriCmd->execute(array('message'=>$value));
			}
		}
		else if ($_type == 'bypass') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,FONC:#message#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			if (is_object($oriCmd)){
				$debitfixe=$ventilairsec->getCmd(null, 'DEBF::raw_value')->execCmd();
				$plage=$ventilairsec->getCmd(null, 'PLH::raw_value')->execCmd();
				$surv=$ventilairsec->getCmd(null, 'SURV::raw_value')->execCmd();
				$value = '00'.$_value.strval($plage).strval($debitfixe).'0'.strval($surv).'0';
				log::add('ventilairsec','debug','Bypass value changed to ' . $value);
				$oriCmd->execute(array('message'=>$value));
			}
		} else if ($_type == 'plage') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,FONC:#message#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			if (is_object($oriCmd)){
				$bypass=$ventilairsec->getCmd(null, 'BYP::raw_value')->execCmd();
				$debitfixe=$ventilairsec->getCmd(null, 'DEBF::raw_value')->execCmd();
				$plage=$ventilairsec->getCmd(null, 'PLH::raw_value')->execCmd();
				$surv=$ventilairsec->getCmd(null, 'SURV::raw_value')->execCmd();
				$value = '00'.strval($bypass).$_value.strval($debitfixe).'0'.strval($surv).'0';
				log::add('ventilairsec','debug','Plage value changed to ' . $value);
				$oriCmd->execute(array('message'=>$value));
			}
		} else if ($_type == 'razfiltre') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,COMMAND:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Raz filter');
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>4));
			}
		} else if ($_type == 'prechauffage') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,TEMPEL:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Prechauffage value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		} else if ($_type == 'soufflage') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,TEMPSOUF:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Soufflage value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		} else if ($_type == 'hydro') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,TEMPHYD:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Hydro value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		} else if ($_type == 'solar') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:0,TEMPSOL:#slider#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			log::add('ventilairsec','debug','Solar value changed to ' . $_value);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('slider'=>$_value));
			}
		} else if ($_type == 'hour') {
			$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:1,HOUR:#message#');
			$oriId= $cmd->getConfiguration('oriId','');
			$oriCmd = cmd::byId($oriId);
			$message='';
			$year= str_pad(dechex(date('y')),2,'0',STR_PAD_LEFT);
			$month=  str_pad(dechex(date('m')),2,'0',STR_PAD_LEFT);
			$day=  str_pad(dechex(date('d')),2,'0',STR_PAD_LEFT);
			$hour=  str_pad(dechex(date('H')),2,'0',STR_PAD_LEFT);
			$minute=  str_pad(dechex(date('i')),2,'0',STR_PAD_LEFT);
			$second=  str_pad(dechex(date('s')),2,'0',STR_PAD_LEFT);
			log::add('ventilairsec','debug','Time value changed to ' . $year.$month.$day.$hour.$minute.$second);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('message'=>$year.$month.$day.$hour.$minute.$second));
			}
		} else if ($_type == 'agenda') {
			log::add('ventilairsec','debug',print_r($_value,true));
			config::save('agendaData',json_encode($_value,true),'ventilairsec');
			self::computeAgenda();
		} else if ($_type == 'agendaActive') {
			log::add('ventilairsec','debug',print_r($_value,true));
			config::save('agendaActive',$_value,'ventilairsec');
			self::sendAction('plage', $_value);
		}
	}

	public static function createVmi($_eqLogic){
		log::add('ventilairsec', 'info', 'Creating VMI for ' . $_eqLogic->getName());
		$eqLogic = eqLogic::byLogicalId($_eqLogic->getLogicalId(), 'ventilairsec');
		if (!is_object($eqLogic)) {
			$eqLogic = new self();
			$eqLogic->setLogicalId($_eqLogic->getLogicalId());
			$eqLogic->setCategory('energy', 1);
			$eqLogic->setName('VMI - ' . $_eqLogic->getName());
			$eqLogic->setEqType_name('ventilairsec');
			$eqLogic->setIsVisible(1);
			$eqLogic->setIsEnable(1);
			$eqLogic->setConfiguration('initId', $_eqLogic->getId());
		}
		$eqLogic->save();
		self::sendAction('hour','');
	}

	public function getImage(){
		return 'plugins/ventilairsec/plugin_info/ventilairsec_icon.png';
	}

	public function proApi(){
		$return = array();
		$return['plugin'] = __CLASS__;
		$return['eqlogics'] = array();
		foreach (eqLogic::byType('ventilairsec') as $ventilairsec) {
			$eqLogicData = array();
			$eqLogicData['name']=$ventilairsec->getName();
			$eqLogicData['id']=$ventilairsec->getId();
			$eqLogicData['datas']=array();
			$bypamo = $ventilairsec->getCmd(null, 'BYPAMO::raw_value')->execCmd();
			$idmachcmd = $ventilairsec->getCmd(null, 'IDMACH::value');
			$idmachvalue = $idmachcmd->execCmd();
			$eqLogicData['datas'][] = array('name'=>$idmachcmd->getName(),'value'=>$idmachvalue,'color'=>'green','unite'=>'');

			$typcaissoncmd= $ventilairsec->getCmd(null, 'TYPCAI::value');
			$typcaissonvalue= $typcaissoncmd->execCmd();
			$eqLogicData['datas'][] = array('name'=>$typcaissoncmd->getName(),'value'=>$typcaissonvalue,'color'=>'green','unite'=>'');

			$volumecmd= $ventilairsec->getCmd(null, 'VVENT::value');
			$volumevalue= $volumecmd->execCmd();
			$volumeUnite = $volumecmd->getUnite();
			if ($volumevalue == '') {
				$volumevalue = 0;
			}
			$eqLogicData['datas'][] = array('name'=>$volumecmd->getName(),'value'=>$volumevalue,'color'=>'green','unite'=>$volumeUnite);

			if ($bypamo == 1){
				$bypasscmd= $ventilairsec->getCmd(null, 'BYP::value');
				$bypassvalue= $bypasscmd->execCmd();
				$eqLogicData['datas'][] = array('name'=>$bypasscmd->getName(),'value'=>$bypassvalue,'color'=>'green','unite'=>'');
			}

			$dureecmd= $ventilairsec->getCmd(null, 'DFONC::value');
			$dureevalue= $dureecmd->execCmd();
			$dureeUnite = $dureecmd->getUnite();
			if ($dureevalue == '') {
				$dureevalue = 0;
			}
			$eqLogicData['datas'][] = array('name'=>$dureecmd->getName(),'value'=>$dureevalue,'color'=>'green','unite'=>$dureeUnite);

			$filterCmd = $ventilairsec->getCmd(null, 'IEFIL::value');
			$filterValue = $filterCmd->execCmd();
			$filterUnite = $filterCmd->getUnite();
			$filterColor= 'green';
			if ($filterValue > 90) {
				$filterColor = 'red';
			}
			$eqLogicData['datas'][] = array('name'=>$filterCmd->getName(),'value'=>$filterValue,'color'=>$filterColor,'unite'=>$filterUnite);

			$testsondecmd= $ventilairsec->getCmd(null, 'TSOND::raw_value');
			$testsondevalue= $testsondecmd->execCmd();
			$colorSonde= 'green';
			$sondemessage = 'OK';
			if ($testsondevalue == 0) {
				$colorSonde = 'red';
				$sondemessage = 'KO';
			}
			$eqLogicData['datas'][] = array('name'=>$testsondecmd->getName(),'value'=>$sondemessage,'color'=>$colorSonde,'unite'=>'');

			$testprechcmd= $ventilairsec->getCmd(null, 'TPCHAU::raw_value');
			$testprechvalue= $testprechcmd->execCmd();
			$colorPrech= 'green';
			$prechmessage = 'OK';
			if ($testprechvalue == 0) {
				$colorPrech = 'red';
				$prechmessage = 'KO';
			}
			$eqLogicData['datas'][] = array('name'=>$testprechcmd->getName(),'value'=>$prechmessage,'color'=>$colorPrech,'unite'=>'');

			$testmotcmd= $ventilairsec->getCmd(null, 'TMOT::raw_value');
			$testmotvalue= $testmotcmd->execCmd();
			$colorMot= 'green';
			$motmessage = 'OK';
			if ($testmotvalue == 0) {
				$colorMot = 'red';
				$motmessage = 'KO';
			}
			$eqLogicData['datas'][] = array('name'=>$testmotcmd->getName(),'value'=>$motmessage,'color'=>$colorMot,'unite'=>'');

			$testqaicmd= $ventilairsec->getCmd(null, 'TQAI::raw_value');
			$testqaivalue= $testqaicmd->execCmd();
			$colorQai= 'green';
			$qaimessage = 'OK';
			if ($testqaivalue == 0) {
				$colorQai = 'red';
				$qaimessage = 'KO';
			}
			$eqLogicData['datas'][] = array('name'=>$testqaicmd->getName(),'value'=>$qaimessage,'color'=>$colorQai,'unite'=>'');

			$erreur1cmd= $ventilairsec->getCmd(null, 'CERR1::value');
			$erreur1value= $erreur1cmd->execCmd();
			$colorErreur1= 'green';
			if ($erreur1value != 255) {
				$colorErreur1 = 'red';
				$erreur1value=strtoupper(str_pad(dechex($erreur1value),2,'0',STR_PAD_LEFT));
			} else {
				$erreur1value = "Pas d'erreur";
			}
			$eqLogicData['datas'][] = array('name'=>$erreur1cmd->getName(),'value'=>self::ErrorLabel()[$erreur1value],'color'=>$colorErreur1,'unite'=>'');

			$erreur2cmd= $ventilairsec->getCmd(null, 'CERR2::value');
			$erreur2value= $erreur2cmd->execCmd();
			$colorErreur2= 'green';
			if ($erreur2value != 255) {
				$colorErreur2 = 'red';
				$erreur2value=strtoupper(str_pad(dechex($erreur2value),2,'0',STR_PAD_LEFT));
			}else {
				$erreur2value = "Pas d'erreur";
			}
			$eqLogicData['datas'][] = array('name'=>$erreur2cmd->getName(),'value'=>self::ErrorLabel()[$erreur2value],'color'=>$colorErreur2,'unite'=>'');

			$vitesseMoteurCmd = $ventilairsec->getCmd(null, 'CVITM::raw_value');
			$vitesseMoteurValue = $vitesseMoteurCmd->execCmd();
			$vitesseMoteurUnite = $vitesseMoteurCmd->getUnite();
			$vitesseMoteurColor= 'green';
			$eqLogicData['datas'][] = array('name'=>$vitesseMoteurCmd->getName(),'value'=>$vitesseMoteurValue,'color'=>$vitesseMoteurColor,'unite'=>$vitesseMoteurUnite);
			$return['eqlogics'][]=$eqLogicData;
		}
		log::add('ventilairsec','debug',json_encode($return,true));
		return $return;
	}

	public function computeAgenda() {
		$allEqLogics = eqLogic::byType('ventilairsec');
		if (count($allEqLogics) >0) {
			$ventilairsec = $allEqLogics[0];
		}
		if (!is_object($ventilairsec)) {
			return;
		}
		$day = array('lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6,'dimanche'=>7);
		log::add('ventilairsec','debug','Computing Agenda');
		$agenda = config::byKey('agendaData','ventilairsec','');
		$cleanedAgenda =array();
		foreach($agenda as $line) {
			$line['nbday'] = $day[$line['jour']];
			$cleanedAgenda[]=$line;
		}
		$sort = array();
		foreach($cleanedAgenda as $k=>$v) {
			$sort['nbday'][$k] = $v['nbday'];
			$sort['start'][$k] = $v['start'];
		}
		array_multisort($sort['nbday'], SORT_ASC, $sort['start'], SORT_ASC,$cleanedAgenda);
		log::add('ventilairsec','debug',print_r($cleanedAgenda,true));
		$dictAgenda = array();
		foreach ($cleanedAgenda as $lineagenda) {
			$dictAgenda[$lineagenda['jour']][] = array('start'=>$lineagenda['start'],'temp'=>$lineagenda['temperature'],'mode'=>$lineagenda['ventil']);
		}
		log::add('ventilairsec','debug',json_encode($dictAgenda,true));
		$cmd = $ventilairsec->getCmd(null, 'MSC:1,command:2,AGENDA:#message#');
		$oriId= $cmd->getConfiguration('oriId','');
		$oriCmd = cmd::byId($oriId);
		foreach ($day as $key=> $value) {
			$trame='';
			$count = 0;
			foreach($dictAgenda[$key] as $daycal){
				if ($count == 5) {
					break;
				}
				if ($daycal['mode'] != 0) {
					$start = str_pad(dechex($daycal['start']),2,'0',STR_PAD_LEFT);
					$tempbin = str_pad(decbin($daycal['temp']),6,'0',STR_PAD_LEFT);
					$modebin = str_pad(decbin($daycal['mode']-1),2,'0',STR_PAD_LEFT);
					$config = str_pad(dechex(bindec($modebin.$tempbin)),2,'0',STR_PAD_LEFT);
					log::add('ventilairsec','debug',$start . ' ' . $modebin . ' '. $tempbin . ' ' . $config);
					$trame.=$start.$config;
					$count += 1;
				} else {
					//$start = str_pad(dechex($daycal['start']),2,'0',STR_PAD_LEFT);
					$trame.= 'FF00';
					$count += 1;
				}
			}
			if ($count<5) {
				for($i = 0; $i < 5-$count; $i+=1){
					$trame .= 'FF00';
				}
			}
			$trame .= str_pad(dechex($day[$key]),2,'0',STR_PAD_LEFT);
			log::add('ventilairsec','debug',$key . ' : ' .$trame);
			if (is_object($oriCmd)){
				$oriCmd->execute(array('message'=>$trame));
			}
			usleep(1000000);
		}
	}

	public function checkCapteur() {
		for($i = 0; $i < 10; $i+=1) {
			$value = $this->getCmd(null, 'CAPTEUR'.$i.'::value')->execCmd();
			if ($value != '') {
				log::add('ventilairsec','debug', 'Found ' . $value . ' for capteur '.$i);
				$details = explode('|',$value);
				$eqLogic = eqLogic::byLogicalId($details[0],'openenocean');
				if (is_object($eqLogic)){
					log::add('ventilairsec','debug', 'Capteur with id ' . $details[0] . ' already exists in Jeedom');
				} else {
					log::add('ventilairsec','debug', 'Capteur with id ' . $details[0] . ' doesn\'t exists in Jeedom, creating it');
					$rorg = 'd1079';
					$func = '00';
					$type = '00';
					if ($details[2] != 'MSC Assistant Ventilairsec'){
						$detailprofil = explode('_',strtolower($details[2]));
						$rorg = $detailprofil[0];
						$func = $detailprofil[1];
						$type = $detailprofil[2];
					}
					$devtype = $rorg.'-'.$func.'-'.$type;
					$neweqLogic = new openenocean();
					$neweqLogic->setName('VMILINK-'.$details[0]);
					$neweqLogic->setLogicalId(strtoupper($details[0]));
					$neweqLogic->setEqType_name('openenocean');
					$neweqLogic->setIsEnable(1);
					$neweqLogic->setIsVisible(1);
					$neweqLogic->setConfiguration('device', $devtype);
					$neweqLogic->setConfiguration('rorg', $rorg);
					$neweqLogic->setConfiguration('func', $func);
					$neweqLogic->setConfiguration('type', $type);
					$model = $neweqLogic->getModelListParam();
					if (count($model) > 0) {
						if ($devtype == 'a5-09-04'){
							$neweqLogic->setConfiguration('iconModel', 'a5-09/a5-09-04_nexelec_insafe+_carbon_co2_humidite_temperature');
						} else if ($devtype == 'a5-04-01') {
							$neweqLogic->setConfiguration('iconModel', 'a5-04/a5-04-01_nexelec_insafe+_pilot_temperature_humidite');
						} else if ($devtype == 'd2-04-08') {
							$neweqLogic->setConfiguration('iconModel', 'd2-04/d2-04-08_nanosense_e4000_NG');
						} else if ($devtype == 'd1079-00-00') {
							$neweqLogic->setConfiguration('iconModel', 'd1079-00/d1079-00-00_assistant_ventilairsec');
						} else {
							$neweqLogic->setConfiguration('iconModel', array_keys($model[0])[0]);
						}
					}
					$neweqLogic->save();

				}
			}
		}
	}

	public static function checknotif() {
		$Mobiles = eqLogic::byType('mobile');
			$arrayNotifSave = config::bykey('notif','ventilairsec', array());
			$arrayNotif = array();
			foreach ($Mobiles as $Mobile) {
				if(is_object($Mobile)){
					if($Mobile->getIsEnable() == 1){
						$notif = cmd::byEqLogicIdAndLogicalId($Mobile->getId(),'notif');
						if(is_object($notif)){
							log::add('ventilairsec','debug','MOBILES > '.$notif->getName().' > '.$notif->getId());
							if($arrayNotifSave[$notif->getId()]){
								$arrayNotif[$notif->getId()] = $arrayNotifSave[$notif->getId()];
							}else{
								$arrayNotif[$notif->getId()] = 1;
							}
						}
					}
				}
			}
			config::save('notif', $arrayNotif, 'ventilairsec');
			return true;
	}

	public static function sendNotif($message, $title = 'Notifications VMI'){
		$arrayNotif = config::bykey('notif','ventilairsec', array());
		if($arrayNotif == array()){
			log::add('ventilairsec','info','Notif not send for not mobile detected');
			return false;
		}
		foreach ($arrayNotif as $key => $value){
			if($value == 1){
				$cmd = cmd::byEqLogicIdAndLogicalId($key,'notif');
				$eqLogic = eqLogic::byId($key);
				if(is_object($eqLogic) && is_object($cmd)){
					if($eqLogic->getIsEnable() == 1){
						$cmd->execCmd($options=array('message' => $message,'title' => $title), $cache=0);
					}
				}
			}
		}
	}

	public function postSave() {
		$oriEqlogic = eqLogic::byId($this->getConfiguration('initId',''));
		if (is_object($oriEqlogic)) {
			$this->removeListener();
			$listenner_number = 0;
			$listener = new listener();
			$listener->setClass(__CLASS__);
			$listener->setFunction('immediateAction');
			$listener->setOption(array('ventilairsec_id' => intval($this->getId()),'listenner_number' => $listenner_number));
			$listener->emptyEvent();
			$nblistener = 0;
			foreach ($oriEqlogic->getCmd('info') as $cmdOri) {
				log::add('ventilairsec', 'info', 'Creating listener for ' . $cmdOri->getName());
				$nblistener += 1;
				$listener->addEvent($cmdOri->getId());
				if($nblistener >= 15){
					$listener->save();
					$listenner_number++;
					$listener = new listener();
					$listener->setClass(__CLASS__);
					$listener->setFunction('immediateAction');
					$listener->setOption(array('ventilairsec_id' => intval($this->getId()),'listenner_number' => $listenner_number));
					$listener->emptyEvent();
					$nblistener =0;
				}
				$cmd = $this->getCmd(null, $cmdOri->getLogicalId());
				if (!is_object($cmd)) {
					log::add('ventilairsec', 'info', 'Creating command for ' . $cmdOri->getName());
					$cmd = new ventilairseccmd();
					$cmd->setIsVisible($cmdOri->getIsVisible());
					$cmd->setIsHistorized(1);
				}
				$cmd->setName(__($cmdOri->getName(), __FILE__));
				$cmd->setEqLogic_id($this->getId());
				$cmd->setLogicalId($cmdOri->getLogicalId());
				$cmd->setType($cmdOri->getType());
				$cmd->setSubType($cmdOri->getSubType());
				$cmd->setUnite($cmdOri->getUnite());
				$cmd->setOrder($cmdOri->getOrder());
				$cmd->setGeneric_type($cmdOri->getGeneric_type());
				$cmd->setDisplay('icon', $cmdOri->getDisplay('icon'));
				$cmd->setDisplay('invertBinary', $cmdOri->getDisplay('invertBinary'));
				$cmd->setConfiguration('oriId', $cmdOri->getId());
				$cmd->setConfiguration('listValue', $cmdOri->getConfiguration('listValue', ''));
				$cmd->setConfiguration('historizeMode','none');
				$cmd->setConfiguration('historyPurge','-3 month');
				foreach ($cmdOri->getTemplate() as $key => $value) {
					$cmd->setTemplate($key, $value);
				}
				$cmd->save();
				$this->checkAndUpdateCmd($cmd,$cmdOri->execCmd());
			}
			if ($nblistener >0) {
				$listener->save();
			}
			foreach ($oriEqlogic->getCmd('action') as $cmdOri) {
				$cmd = $this->getCmd(null, $cmdOri->getLogicalId());
				if (!is_object($cmd)) {
					log::add('ventilairsec', 'info', 'Creating command for ' . $cmdOri->getName());
					$cmd = new ventilairseccmd();
					$cmd->setIsVisible($cmdOri->getIsVisible());
				}
				$cmd->setName(__($cmdOri->getName(), __FILE__));
				$cmd->setEqLogic_id($this->getId());
				$cmd->setLogicalId($cmdOri->getLogicalId());
				$cmd->setType($cmdOri->getType());
				$cmd->setSubType($cmdOri->getSubType());
				$cmd->setUnite($cmdOri->getUnite());
				$cmd->setOrder($cmdOri->getOrder());
				$cmd->setGeneric_type($cmdOri->getGeneric_type());
				$cmd->setDisplay('icon', $cmdOri->getDisplay('icon'));
				$cmd->setConfiguration('oriId', $cmdOri->getId());
				$cmd->setDisplay('invertBinary', $cmdOri->getDisplay('invertBinary'));
				$cmd->setConfiguration('listValue', $cmdOri->getConfiguration('listValue', ''));
				foreach ($cmdOri->getTemplate() as $key => $value) {
					$cmd->setTemplate($key, $value);
				}
				$cmd->save();
			}
		}
	}

	public function preRemove() {
		$this->removeListener();
	}

	public function removeListener() {
		$listeners = listener::byClass(__CLASS__);
		foreach ($listeners as $listener) {
			if ($listener->getFunction() != 'immediateAction') {
				continue;
			}
			$options = $listener->getOption();
			if (!isset($options['ventilairsec_id']) || $options['ventilairsec_id'] != $this->getId()) {
				continue;
			}
			$listener->remove();
		}
	}

	public function setMobileConfiguration($_params = ''){
			$mobile1 = eqLogic::byLogicalId(config::bykey('mobile1','ventilairsec',null),'mobile');
			$mobile2 = eqLogic::byLogicalId(config::bykey('mobile2','ventilairsec',null),'mobile');
			$mobile3 = eqLogic::byLogicalId(config::bykey('mobile3','ventilairsec',null),'mobile');
			$mobile4 = eqLogic::byLogicalId(config::bykey('mobile4','ventilairsec',null),'mobile');
			if(!is_object($mobile1) || !is_object($mobile2) || !is_object($mobile3) || !is_object($mobile4)){
				return false;
			}
			$params = json_decode($_params, true);
			foreach ($params as $key => $value) {
				if ($key == 'nbrMobile'){
					if($value == 1){
						$mobile1->setIsEnable(1);
						$mobile2->setIsEnable(0);
						$mobile3->setIsEnable(0);
						$mobile4->setIsEnable(0);
					}
					if($value == 2){
						$mobile1->setIsEnable(1);
						$mobile2->setIsEnable(1);
						$mobile3->setIsEnable(0);
						$mobile4->setIsEnable(0);
					}
					if($value == 3){
						$mobile1->setIsEnable(1);
						$mobile2->setIsEnable(1);
						$mobile3->setIsEnable(1);
						$mobile4->setIsEnable(0);
					}
					if($value == 4){
						$mobile1->setIsEnable(1);
						$mobile2->setIsEnable(1);
						$mobile3->setIsEnable(1);
						$mobile4->setIsEnable(1);
					}
					$mobile1->save();
					$mobile2->save();
					$mobile3->save();
					$mobile4->save();
					ventilairsec::checknotif();
					continue;
				}
				if ($key =='nomMobile1'){
					$mobile1->setName($value);
					continue;
				}
				if ($key =='nomMobile2'){
					$mobile2->setName($value);
					continue;
				}
				if ($key =='nomMobile3'){
					$mobile3->setName($value);
					continue;
				}
				if ($key =='nomMobile4'){
					$mobile4->setName($value);
					continue;
				}

				if ($key =='typeMobile1'){
					$mobile1->setConfiguration('type_mobile', $value);
					continue;
				}
				if ($key =='typeMobile2'){
					$mobile2->setConfiguration('type_mobile', $value);
					continue;
				}
				if ($key =='typeMobile3'){
					$mobile3->setConfiguration('type_mobile', $value);
					continue;
				}
				if ($key =='typeMobile4'){
					$mobile4->setConfiguration('type_mobile', $value);
					continue;
				}
				if ($key == 'NotifMobile1'){
					$arrayNotif = config::bykey('notif','ventilairsec', array());
					if($mobile1->getIsEnable() == 1){
						$notif = cmd::byEqLogicIdAndLogicalId($mobile1->getId(),'notif');
						if($value == 1 || $value == '1'){
							$arrayNotif[$notif->getId()] = 1;
						}else{
							$arrayNotif[$notif->getId()] = 0;
						}
						config::save('notif', $arrayNotif, 'ventilairsec');
					}
					continue;
				}
				if ($key == 'NotifMobile2'){
					$arrayNotif = config::bykey('notif','ventilairsec', array());
					if($mobile2->getIsEnable() == 1){
						$notif = cmd::byEqLogicIdAndLogicalId($mobile2->getId(),'notif');
						if($value == 1 || $value == '1'){
							$arrayNotif[$notif->getId()] = 1;
						}else{
							$arrayNotif[$notif->getId()] = 0;
						}
						config::save('notif', $arrayNotif, 'ventilairsec');
					}
					continue;
				}
				if ($key == 'NotifMobile3'){
					$arrayNotif = config::bykey('notif','ventilairsec', array());
					if($mobile3->getIsEnable() == 1){
						$notif = cmd::byEqLogicIdAndLogicalId($mobile3->getId(),'notif');
						if($value == 1 || $value == '1'){
							$arrayNotif[$notif->getId()] = 1;
						}else{
							$arrayNotif[$notif->getId()] = 0;
						}
						config::save('notif', $arrayNotif, 'ventilairsec');
					}
					continue;
				}
				if ($key == 'NotifMobile4'){
					$arrayNotif = config::bykey('notif','ventilairsec', array());
					if($mobile4->getIsEnable() == 1){
						$notif = cmd::byEqLogicIdAndLogicalId($mobile4->getId(),'notif');
						if($value == 1 || $value == '1'){
							$arrayNotif[$notif->getId()] = 1;
						}else{
							$arrayNotif[$notif->getId()] = 0;
						}
						config::save('notif', $arrayNotif, 'ventilairsec');
					}
					continue;
				}


			}
			$mobile1->save();
			$mobile2->save();
			$mobile3->save();
			$mobile4->save();
			log::add('ventilairsec','debug','All Array > '.json_encode($arrayNotif));
			return true;
	}

	public function setIntConfiguration($_params = '') {
		$params = json_decode($_params, true);
		foreach ($params as $key => $value) {
			if ($key =='boxName'){
				config::save('name',$value, 'core');
				continue;
			}
			if ($key =='marketUser'){
				config::save('market::username',$value, 'core');
				continue;
			}
			if ($key =='marketUserPass'){
				config::save('market::password',$value, 'core');
				continue;
			}
			config::save($key,$value, 'ventilairsec');
			if(config::byKey('market::allowDNS', 'core', 0) != 1){
				config::save('market::allowDNS',1);
				network::dns_start();
				continue;
			}
		}
		$output = self::sendInt();
		return $output;
	}

	public function getIntConfiguration() {
		$params =array();
		$params['integratorUser'] = config::byKey('integratorUser','ventilairsec');
		$params['integratorPassword'] = config::byKey('integratorPassword','ventilairsec');
		$params['clientApiPro'] = config::byKey('clientApiPro','ventilairsec');
		$params['installName'] = config::byKey('installName','ventilairsec');
		$params['clientName'] = config::byKey('clientName','ventilairsec');
		$params['clientAdress'] = config::byKey('clientAdress','ventilairsec');
		$params['clientPostal'] = config::byKey('clientPostal','ventilairsec');
		$params['clientCity'] = config::byKey('clientCity','ventilairsec');
		$params['clientCountry'] = config::byKey('clientCountry','ventilairsec');
		$params['clientPhone'] = config::byKey('clientPhone','ventilairsec');
		$params['boxName'] = config::byKey('name','core');
		$params['marketUser'] = config::byKey('market::username','core');
		$params['marketUserPass'] = config::byKey('market::password','core');
		return $params;
	}

	public function sendInt() {
		repo_market::test();
		$market = repo_market::getJsonRpc();
		if (!$market->sendRequest('user::getAccesskey')) {
			throw new Exception($market->getError(), $market->getErrorCode());
		}
		$arrayResult = $market->getResult();
		user::supportAccess(false);
		user::supportAccess(true);
		$accesskey = $arrayResult['accesskey'];
		config::save('clientApiPro',$accesskey, 'ventilairsec');
		if (config::byKey('apipro','core','') == '') {
			config::save('apipro', config::genKey());
		}
		sleep(1);
		$apiInturl = 'https://pro.jeedom.com/core/api/api.php?type=install';
		$apiInturl .= '&integratorUser='.urlencode(config::byKey('integratorUser','ventilairsec','None'));
		$apiInturl .= '&integratorPassword='.urlencode(config::byKey('integratorPassword','ventilairsec','None'));
		$apiInturl .= '&clientApiPro='.urlencode(config::byKey('clientApiPro','ventilairsec','None'));
		$apiInturl .= '&installName='.urlencode(config::byKey('installName','ventilairsec','None'));
		$apiInturl .= '&clientName='.urlencode(config::byKey('clientName','ventilairsec','None'));
		$apiInturl .= '&clientAdress='.urlencode(config::byKey('clientAdress','ventilairsec','None'));
		$apiInturl .= '&clientPostal='.urlencode(config::byKey('clientPostal','ventilairsec','None'));
		$apiInturl .= '&clientCity='.urlencode(config::byKey('clientCity','ventilairsec','None'));
		$apiInturl .= '&clientCountry='.urlencode(config::byKey('clientCountry','ventilairsec','None'));
		$apiInturl .= '&clientPhone='.urlencode(config::byKey('clientPhone','ventilairsec','None'));
		$apiInturl .= '&clientLogin='.urlencode(config::byKey('market::username','core','None'));
		$apiInturl .= '&hwkey='.urlencode(jeedom::getHardwareKey());
		$apiInturlfull = $apiInturl.'&boxapikey='.urlencode(config::byKey('apipro','core','None'));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiInturlfull);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($ch);
		curl_close($ch);
		log::add('ventilairsec','debug',$output);
		log::add('ventilairsec','debug',$apiInturlfull);
		sleep(3);
		$apiInturlfull = $apiInturl.'&boxapikey='.urlencode(config::byKey('apipro','core','None'));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiInturlfull);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($ch);
		curl_close($ch);
		log::add('ventilairsec','debug',$output);
		log::add('ventilairsec','debug',$apiInturlfull);
		return $output;
	}

}

class ventilairsecCmd extends cmd {
	/***************************Attributs*******************************/


	/*************************Methode static****************************/

	/***********************Methode d'instance**************************/

	public function execute($_options = null) {
		$eqLogic = $this->getEqlogic();
		$logical = $this->getLogicalId();
	}

	/************************Getteur Setteur****************************/
}
?>
