<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 
// ----------------------------------------------------------------------
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderMailing extends CommonDBTM {

	//! mailing type (contract,infocom,cartridge,consumable)
	var $orders_id = 0;
	var $action = "";
	var $entities_id = "";
	var $users_id = 0;
	var $message = "";

	/**
	 * Constructor
	 * @param $type mailing type (new,attrib,followup,finish)
	 * @param $message Message to send
	 * @return nothing 
	 */

	function __construct($orders_id, $action, $entities_id = -1, $users_id = 0, $message = '') {
		$this->orders_id = $orders_id;
		$this->entities_id = $entities_id;
		$this->action = $action;
		$this->users_id = $users_id;
		$this->message = $message;
	}

	/**
	 * Format the mail body to send
	 * @return mail body string
	 */
	function get_mail_body($format = "text") {
		global $CFG_GLPI, $LANG;

		// Create message body from Job and type
		$body = "";
		$order = new PluginOrderOrder;
		$order->getFromDB($this->orders_id);

		if ($format == "html") {
			$body .= "<html><head><style  type='text/css'>body {font-family: Verdana;font-size: 11px;text-align: left;} td {font-family: Verdana;font-size: 11px;text-align: left;}</style></head><body>";
			$body .= "<table class='tab_cadre_fixe' border='1' cellspacing='2' cellpadding='3'>";
			$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['common'][16] . "</td><td bgcolor='#CCCCCC'>" . $order->fields["name"] . "</td></tr>";
			$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['financial'][18] . "</td><td bgcolor='#CCCCCC'>" . $order->fields["num_order"] . "</td></tr>";
			$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['plugin_order'][1] . "</td><td bgcolor='#CCCCCC'>" . convDate($order->fields["order_date"]) . "</td></tr>";
			$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['joblist'][0] . "</td><td bgcolor='#CCCCCC'>" . $order->getDropdownStatus($order->fields["states_id"]) . "</td></tr>";
			if ($this->message != '')
				$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['common'][25] . "</td><td bgcolor='#CCCCCC'>" . $this->message . "</td></tr>";

			switch ($this->action) {
				case "ask" :
					$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['plugin_order']['validation'][1] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "</td></tr>";
					break;
				case "validation" :
					$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['plugin_order']['validation'][10] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "</td></tr>";
					break;
				case "cancel" :
					$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['plugin_order']['validation'][5] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "</td></tr>";
					break;
				case "undovalidation" :
					$body .= "<tr><td bgcolor='#CCCCCC'>" . $LANG['plugin_order']['validation'][16] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "</td></tr>";
					break;
			}

			if ($CFG_GLPI["url_in_mail"] && !empty ($CFG_GLPI["url_base"])) {
				$body .= "<tr><td bgcolor='#CCCCCC' colspan='2'>URL :<a href=\"" . $CFG_GLPI["url_base"] . "/index.php?redirect=plugin_order_" . $this->orders_id . "\">" . $CFG_GLPI["url_base"] . "/index.php?redirect=plugin_order_" . $this->orders_id . " </a></td></tr>";
			}

			$body .= "</table>";
			$body .= "</body></html>";

		} else { // text format
			$body .= $LANG['common'][16] . " : " . $order->fields["name"] . "\n";
			$body .= $LANG['financial'][18] . " : " . $order->fields["num_order"] . "\n";
			$body .= $LANG['plugin_order'][1] . " : " . convDate($order->fields["order_date"]) . "\n";
			$body .= $LANG['joblist'][0] . " : " . $order->getDropdownStatus($order->fields["states_id"]) . "\n";
			if ($this->message != '')
				$body .= $LANG['common'][25] . " : " . $this->message . "\n";

			switch ($this->action) {
				case "ask" :
					$body .= $LANG['plugin_order']['validation'][1] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "\n";
					break;
				case "validation" :
					$body .= $LANG['plugin_order']['validation'][10] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "\n";
					break;
				case "cancel" :
					$body .= $LANG['plugin_order']['validation'][5] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "\n";
					break;
				case "undovalidation" :
					$body .= $LANG['plugin_order']['validation'][16] . " " . $LANG['plugin_order']['mailing'][2] . "</td><td bgcolor='#CCCCCC'>" . getUserName($this->users_id) . "\n";
					break;
			}

			if ($CFG_GLPI["url_in_mail"] && !empty ($CFG_GLPI["url_base"])) {
				$body .= "URL :<a href=\"" . $CFG_GLPI["url_base"] . "/index.php?redirect=plugin_order_" . $this->orders_id . "\">" . $CFG_GLPI["url_base"] . "/index.php?redirect=plugin_order_ID=" . $this->orders_id . " </a>\n";
			}

			$body = str_replace("<br />", "\n", $body);
			$body = str_replace("<br>", "\n", $body);
		}
		return $body;
	}
	/**
	 * Give mails to send the mail
	 * 
	 * Determine email to send mail using global config and Mailing type
	 *
	 * @return array containing email
	 */
	function get_users_to_send_mail() {
		global $DB, $CFG_GLPI;

		$emails = array ();

		$query = "SELECT * FROM `glpi_plugin_order_mailingsettings` WHERE `type` = '" . $this->action . "'";
		$result = $DB->query($query);
		if ($DB->numrows($result)) {
			while ($data = $DB->fetch_assoc($result)) {
				switch ($data["item_type"]) {
					case USER_MAILING_TYPE :
						switch ($data["FK_item"]) {
							// ADMIN SEND
							case ADMIN_MAILING :
								if (isValidEmail($CFG_GLPI["admin_email"]) && !in_array($CFG_GLPI["admin_email"], $emails))
									$emails[] = $CFG_GLPI["admin_email"];
								break;
						}
						break;
					case PROFILE_MAILING_TYPE :

						$query="SELECT `glpi_users`.`email` AS EMAIL
								FROM `glpi_profiles_users`
								INNER JOIN `glpi_users` ON (`glpi_profiles_users`.`users_id` = `glpi_users`.`id`)
								WHERE `glpi_profiles_users`.`profiles_id` = '".$data["items_id"]."'
								".getEntitiesRestrictRequest("AND","glpi_profiles_users","entities_id",$this->entities_id,true);

						if ($result2 = $DB->query($query)) {
							if ($DB->numrows($result2))
								while ($data = $DB->fetch_assoc($result2)) {
									if (isValidEmail($data["EMAIL"]) && !in_array($data["EMAIL"], $emails)) {
										$emails[] = $data["EMAIL"];
									}
								}
						}
						break;
					case GROUP_MAILING_TYPE :
						$query="SELECT `glpi_users`.`email` AS EMAIL
								FROM `glpi_users_groups`
								INNER JOIN `glpi_users` ON (`glpi_users_groups`.`users_id` = `glpi_users`.`id`)
								WHERE `glpi_users_groups`.`groups_id` = '".$data["items_id"]."'";

						if ($result2 = $DB->query($query)) {
							if ($DB->numrows($result2))
								while ($data = $DB->fetch_assoc($result2)) {
									if (isValidEmail($data["EMAIL"]) && !in_array($data["EMAIL"], $emails)) {
										$emails[] = $data["EMAIL"];
									}
								}
						}
						break;
				}
			}
		}

		return $emails;
	}

	function mailing() {
		global $DB, $LANG, $CFG_GLPI;

		if ($CFG_GLPI["use_mailing"]) {
			// get users to send mail
			$users = $this->get_users_to_send_mail();

			$order = new PluginOrderOrder;
			$order->getFromDB($this->orders_id);

			if (isMultiEntitiesMode())
				$entities_id = Dropdown::getDropdownName("glpi_entities",$this->entities_id)." | ";
			else
				$entities_id = "";

			for ($i = 0; $i < count($users); $i++) {

				$mail = new glpi_phpmailer();
				$mail->From = $CFG_GLPI["admin_email"];
				$mail->FromName = $CFG_GLPI["admin_email"];
				$mail->AddAddress($users[$i], "");

				switch ($this->action) {
					case "ask" :
						$mail->Subject = $LANG['plugin_order']['mailing'][0] . " \"" . $order->fields["name"] . "\" " . $LANG['plugin_order']['mailing'][2] . " " . getUserName($this->users_id);
						break;
					case "validation" :
						$mail->Subject = $LANG['plugin_order']['validation'][2] . " \"" . $order->fields["name"] . "\" " . $LANG['plugin_order']['mailing'][2] . " " . getUserName($this->users_id);
						break;
					case "cancel" :
						$mail->Subject = $LANG['plugin_order']['validation'][5] . " \"" . $order->fields["name"] . "\" " . $LANG['plugin_order']['mailing'][2] . " " . getUserName($this->users_id);
						break;
					case "undovalidation" :
						$mail->Subject = $LANG['plugin_order']['validation'][16] . " \"" . $order->fields["name"] . "\" " . $LANG['plugin_order']['mailing'][2] . " " . getUserName($this->users_id);
						break;
					default :
						$mail->Subject = $order->fields["name"];
						break;
				}

				$mail->Body = $this->get_mail_body("html");
				$mail->isHTML(true);
				$mail->AltBody = $this->get_mail_body("text");

				if (!$mail->Send()) {
					addMessageAfterRedirect($LANG['mailing'][47], false, ERROR);
					return false;
				}
				$mail->ClearAddresses();
			}

		}
	}
}

?>