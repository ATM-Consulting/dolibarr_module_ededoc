<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}

global $db;

dol_include_once('/core/class/extrafields.class.php');
$extrafields=new ExtraFields($db);
$res = $extrafields->addExtraField('ededoc_send_date', 'EdedocSendDate', 'date', 0, '', 'facture');


/* uncomment


dol_include_once('/mymodule/class/xxx.class.php');

$PDOdb=new TPDOdb;

$o=new TXXX($db);
$o->init_db_by_vars($PDOdb);
*/
