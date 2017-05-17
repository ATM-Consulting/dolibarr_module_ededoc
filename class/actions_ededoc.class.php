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
 * \file    class/actions_ededoc.class.php
 * \ingroup ededoc
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsEdedoc
 */
class ActionsEdedoc
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		
		if (in_array('invoicecard', explode(':', $parameters['context'])))
		{
		  	global $langs, $conf;
		  	$langs->load('ededoc@ededoc');
		  	
		  	if($action === 'send_ededoc') {
		  		
		  		$objectref = dol_sanitizeFileName($object->ref);
		  		$dir = $conf->facture->dir_output . '/' . $objectref;
		  		$file = $dir . '/' . $objectref . '.pdf';
		  		
		  		if(empty($conf->global->EDEDOC_HOST)){
		  			setEventMessage($langs->trans('ErrorNoHostFTP'),'errors');
		  		}
		  		else {
		  			$conn_id = ftp_connect($conf->global->EDEDOC_HOST, $conf->global->EDEDOC_HOST_PORT);
		  			if($conn_id === false){
		  				setEventMessage($langs->trans('ErrorConnectionHostFTP'),'errors');
		  			}
		  			else {
		  				
		  				$login_result = ftp_login($conn_id, $conf->global->EDEDOC_LOGIN, $conf->global->EDEDOC_PASSWORD);
		  				ftp_pasv($conn_id, false);
		  				if($login_result === false) {
		  					setEventMessage($langs->trans('ErrorConnectionLoginFTP'),'errors');
		  				}
		  				else{
		  				//	var_dump(ftp_pwd($conn_id));exit;
		  					$upload = ftp_put($conn_id, '/'.basename($file), $file, FTP_ASCII);  // upload the file
		  					if ($upload === false) {
		  						setEventMessage($langs->trans('ErrorConnectionPutFileFTP'),'errors');
		  					}
		  					else{
		  						setEventMessage($langs->trans('InvoiceSentToEdedoc'));
		  					}
		  				}
		  				
		  				ftp_close($conn_id);
		  				
		  			}
		  		}
		  		
		  	}
		  	
		}

		
	}
	
	
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		
		if (in_array('invoicecard', explode(':', $parameters['context'])))
		{
		
			if(empty($object->array_options['options_ededoc_send_date']) && $object->modelpdf==='crabe_ededoc' && $object->statut == 1) {
				global $langs;
				echo '<div class="inline-block divButAction"><a class="butAction" href="'.dol_buildpath('/compta/facture/card.php',1).'?facid='.$object->id.'&action=send_ededoc">'.$langs->trans('SendToEdedoc').'</a></div>';
				
			}
			
		}
		
		
	}
}