<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/ededoc.php
 * 	\ingroup	ededoc
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/ededoc.lib.php';

// Translations
$langs->load("ededoc@ededoc");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if ($action=='setvar')
{
	$error=0;
	foreach ($_POST as $key=>$val) {
		if (strpos($key, 'EDEDOC_') !==false) {
			$result=dolibarr_set_const($db, $key, GETPOST($key), 'chaine', 0, '', $conf->entity);
			if ($result<0)
			{
				$error++;
				setEventMessage($langs->trans("Error") . " " . $db->lasterror,'errors');
			}
		}
	}
	if (empty($error)) {
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
}

/*
 * View
 */
$page_name = "EdedocSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = ededocAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104047Name"),
    0,
    "ededoc@ededoc"
);

// Setup page goes here
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";


// Example with a yes / no select
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("EDEDOC_HOST").'</td>';
print '<td align="right" width="300">';
print '<input type="text" name="EDEDOC_HOST" value="'.$conf->global->EDEDOC_HOST.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("EDEDOC_HOST_PORT").'</td>';
print '<td align="right" width="300">';
print '<input type="text" name="EDEDOC_HOST_PORT" value="'.$conf->global->EDEDOC_HOST_PORT.'">';
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("EDEDOC_LOGIN").'</td>';
print '<td align="right" width="300">';
print '<input type="text" name="EDEDOC_LOGIN" value="'.$conf->global->EDEDOC_LOGIN.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("EDEDOC_PASSWORD").'</td>';
print '<td align="right" width="300">';
print '<input type="password" name="EDEDOC_PASSWORD" value="'.$conf->global->EDEDOC_PASSWORD.'">';
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("EDEDOC_CUSTOMER_CODE").'</td>';
print '<td align="right" width="300">';
print '<input type="text" name="EDEDOC_CUSTOMER_CODE" value="'.$conf->global->EDEDOC_CUSTOMER_CODE.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td colspan="2" align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</td></tr>';

print '</table>';

print '</form>';

llxFooter();

$db->close();