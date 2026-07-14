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

require_once __DIR__ . '/../../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	throw new Exception(__('401 - Accès non autorisé', __FILE__));
}
$type = init('type');

switch ($type) {
	case 'csv':
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=histo.csv');
		$directory = dirname(__FILE__) . '/../../data/';
		if (!is_dir($directory)) {
			mkdir($directory);
		}
		$filename= $directory.'/histo.csv';
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		readfile($filename);
		break;
	default:
		break;
}
