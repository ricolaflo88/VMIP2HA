<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('desktop', 'contact', 'css', 'ventilairsec');
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<div class="contactVMI_body">
    <div class="contactVMI_All">
        <div class="contactVMI_ligne">
            <span class="p_left">{{Numéros de série}}</span>
            <span class="p_right idmachine">12345678</span>
        </div>
        <div class="contactVMI_ligne">
            <span class="p_left">{{Version du logiciel}}</span>
            <span class="p_right logicielv">2.0</span>
        </div>
        <div class="contactVMI_ligne">
            <span class="p_left">{{Caisson}}</span>
            <span class="p_right caisson">purevent</span>
        </div>
        <div class="contactVMI_ligne">
            <span class="p_left">{{Type de préchauffage}}</span>
            <span class="p_right typprech">{{Électrique}}</span>
        </div>
        <div class="contactVMI_ligne">
            <span class="p_left">{{Version électronique}}</span>
            <span class="p_right elecv">1.2</span>
        </div>
        <div class="contactVMI_ligne export">
            <span class="p_left">{{Téléchargement historique de donnée}}</span>
            <span class="p_right cursor"><?php include 'plugins/ventilairsec/core/img/download.svg'; ?></span>
        </div>
				<br/>
        <div class="contactVMI_ligne">
            <span class="p_right cursor">
                <a target="_blank" href="https://www.ventilairsec.com/faq/">{{FAQ}}</a>
                <a target="_blank" href="https://filtres.ventilairsec.com/">{{FILTRE}}</a>
            </span>
        </div>
    </div>
</div>

<?php include_file('desktop', 'contact', 'js', 'ventilairsec'); ?>
