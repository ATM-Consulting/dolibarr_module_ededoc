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
 * \file class/actions_ededoc.class.php
 * \ingroup ededoc
 * \brief This file is an example hook overload class file
 * Put some comments here
 */

/**
 * Class ActionsEdedoc
 */
class ActionsEdedoc
{
	/**
	 *
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 *
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 *
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return int < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, Facture &$object, &$action, $hookmanager) {
		if (in_array('invoicecard', explode(':', $parameters['context']))) {
			global $langs, $conf, $user;
			$langs->load('ededoc@ededoc');

			if ($action === 'send_ededoc' && $user->rights->ededoc->invoice->send) {
				$result = $this->sendInvoiceEdoc($object);
				if ($result < 0) {
					setEventMessages(null, $this->errors, 'errors');
				} else {
					setEventMessage($langs->trans('InvoiceSentToEdedoc', $object->ref));
				}
			}
		}

		if (in_array('invoicelist', explode(':', $parameters['context']))) {
			global $langs, $conf, $user, $db;
			$langs->load('ededoc@ededoc');
			$massaction = GETPOST('massaction', 'alpha');

			if ($massaction === 'sends_ededoc' && $user->rights->ededoc->invoice->send) {
				$toselect = GETPOST('toselect', 'array');
				if (is_array($toselect) && count($toselect) > 0) {
					$invoice = new Facture($db);
					foreach ( $toselect as $idinvoice ) {
						$result = $invoice->fetch($idinvoice);
						if ($result < 0) {
							setEventMessage($invoice->error, 'errors');
						} elseif (empty($invoice->array_options['options_ededoc_send_date']) && $invoice->statut == 1) {
							$result = $this->sendInvoiceEdoc($invoice);
							if ($result < 0) {
								setEventMessages(null, $this->errors, 'errors');
							} else {
								setEventMessage($langs->trans('InvoiceSentToEdedoc', $invoice->ref));
							}
						}
					}
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return int < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager) {
		global $conf, $user;

		if (in_array('invoicecard', explode(':', $parameters['context']))) {

			global $langs;
			// Can be send EDOC setting OK
			$cansend = false;
			if (! empty($conf->global->EDEDOC_HOST) && ! empty($conf->global->EDEDOC_LOGIN) && ! empty($conf->global->EDEDOC_PASSWORD)) {
				$cansend = true;
			}
			if ($cansend && empty($object->array_options['options_ededoc_send_date']) && $object->statut == 1 && $user->rights->ededoc->invoice->send) {
				$facurl = DOL_VERSION > 5.0 ? '/compta/facture/card.php' : '/compta/facture.php';
				echo '<div class="inline-block divButAction"><a class="butAction" href="' . dol_buildpath($facurl, 1) . '?facid=' . $object->id . '&action=send_ededoc">' . $langs->trans('SendToEdedoc') . '</a></div>';
			} elseif (! $cansend && $user->rights->ededoc->invoice->send) {
				print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="' . $langs->trans("EDEDOCDisabledSetUpNotComplete") . '">' . $langs->trans('SendToEdedoc') . '</a></div>';
			}
		}

		return 0;
	}

	/**
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return int < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager) {
		if (in_array('invoicelist', explode(':', $parameters['context']))) {
			global $langs, $conf;

			if (! empty($conf->global->EDEDOC_HOST) && ! empty($conf->global->EDEDOC_LOGIN) && ! empty($conf->global->EDEDOC_PASSWORD)) {
				$cansend = true;
			}
			if ($cansend) {
				$langs->load('ededoc@ededoc');
				$this->resprints = '<option value="sends_ededoc">' . $langs->trans('SendToEdedoc') . '</option>';
			}
		}
		return 0;
	}

	/**
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return int < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function afterPDFCreation($parameters, &$object, &$action, $hookmanager) {
		global $conf;

		$outputlangs = $parameters['outputlangs'];

		$ret = 0;
		$pagecount = 0;
		$files = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		$object = $parameters['object'];

		if ($object->table_element == 'facture') {

			$arrayidcontact = $object->getIdContact('external', 'BILLING');
			if (count($arrayidcontact) > 0) {
				$usecontact = true;
				$result = $object->fetch_contact($arrayidcontact[0]);
			}
			if ($usecontact) {
				$phone = $object->contact->phone_pro;
				$email = $object->contact->email;
			} else {
				$phone = $object->thirdparty->phone;
				$email = $object->thirdparty->email;
			}

			$pdf = pdf_getInstance();
			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			$pdf->SetFont(pdf_getPDFFont($outputlangs), '', pdf_getPDFFontSize($outputlangs) - 1);

			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) {
				$pdf->SetCompression(false);
			}

			$pagecount = $pdf->setSourceFile($parameters['file']);
			for($i = 1; $i <= $pagecount; $i ++) {
				$tplidx = $pdf->ImportPage($i);
				$s = $pdf->getTemplatesize($tplidx);
				$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
				$pdf->useTemplate($tplidx);
			}

			$pdf->setPage(1);
			$pdf->SetXY(20, 3);
			$pdf->SetTextColor(255, 255, 255);
			// $pdf->SetTextColor(0, 0, 0);
			$pdf->Cell(5, 0, $email, 0, 1, 'L');
			$pdf->SetXY(70, 3);
			$pdf->Cell(5, 0, $phone, 0, 1, 'L');
			$pdf->SetXY(100, 3);
			$pdf->Cell(5, 0, $object->thirdparty->email, 0, 1, 'L');
			$pdf->SetXY(160, 3);
			$pdf->Cell(5, 0, $object->thirdparty->phone, 0, 1, 'L');

			$pdf->Output($parameters['file'], 'F');
			if (! empty($conf->global->MAIN_UMASK)) {
				@chmod($file, octdec($conf->global->MAIN_UMASK));
			}
		}

		return 0;
	}

	/**
	 *
	 * @param Facture $object
	 */
	private function sendInvoiceEdoc(Facture $object) {
		global $conf, $langs;
		$objectref = dol_sanitizeFileName($object->ref);
		$dir = $conf->facture->dir_output . '/' . $objectref;
		$file = $dir . '/' . $objectref . '.pdf';

		if (is_file($file) && is_readable($file)) {
			$ch = curl_init();
			$fp = fopen($file, 'r');
			curl_setopt($ch, CURLOPT_URL, 'ftp://' . $conf->global->EDEDOC_HOST . (! empty($conf->global->EDEDOC_HOST_PORT) ? ':' . $conf->global->EDEDOC_HOST_PORT : '') . '/' . $conf->global->EDEDOC_CUSTOMER_CODE . '/' . $objectref . '.pdf');
			curl_setopt($ch, CURLOPT_USERPWD, $conf->global->EDEDOC_LOGIN . ':' . $conf->global->EDEDOC_PASSWORD);
			curl_setopt($ch, CURLOPT_UPLOAD, 1);
			curl_setopt($ch, CURLOPT_INFILE, $fp);
			curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
			curl_exec($ch);
			$error_no = curl_errno($ch);
			$error = curl_error($ch);

			curl_close($ch);
			if ($error_no == 0) {
				$object->array_options['options_ededoc_send_date'] = dol_now();
				$result = $object->insertExtraFields();
				if ($result < 0) {
					$this->errors[] = $object->error;
					return - 1;
				} else {
					return 1;
				}
			} else {
				$this->errors[] = $langs->trans('ErrorConnectionPutFileFTP') . ' ' . $error_no . ' > ' . $error;
				return - 1;
			}
		} else {
			$this->errors[] = $langs->trans('File') . ' ' . $file . ' do not exists or is not readable';
			return - 1;
		}
	}
}