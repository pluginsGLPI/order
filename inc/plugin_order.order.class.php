<?php


/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: Benjamin Fontan
    Purpose of file:
    ----------------------------------------------------------------------*/
class PluginOrder extends CommonDBTM {
	function __construct() {
		$this->table = "glpi_plugin_order";
		$this->type = PLUGIN_ORDER_TYPE;
		$this->entity_assign = true;
		$this->may_be_recursive = true;
		$this->dohistory = true;
	}

	/*clean if order are deleted */
	function cleanDBonPurge($ID) {
		global $DB;

		$query = "DELETE FROM `glpi_doc_device` 
				  WHERE FK_device = '$ID' 
				  AND device_type= '" . PLUGIN_ORDER_TYPE . "' ";
		$DB->query($query);
		$query = "DELETE FROM `glpi_plugin_order_detail`
				  WHERE FK_order='$ID'";
		$DB->query($query);
	}

	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if ($ID > 0) {
			$plugin = new Plugin();
			/* detail */
			$ong[4] = $LANG['plugin_order']['detail'][0];
			if(!plugin_order_canUpdateOrder($ID)) {
            /* delivery */
            $ong[5] = $LANG['plugin_order']['delivery'][1];
            /* item */
            $ong[2] = $LANG['plugin_order']['item'][0];
         }
			/*
						if (haveRight("show_all_ticket", "1")) {
							$ong[6] = $LANG['title'][28];
						}
			*/
			/* documents */
			if (haveRight("document", "r"))
				$ong[3] = $LANG['Menu'][27];
			if (haveRight("notes", "r"))
				$ong[11] = $LANG['title'][37];
			/* all */
			$ong[12] = $LANG['title'][38];
		}
		return $ong;
	}

	function prepareInputForAdd($input) {
		global $LANG;
		if (!isset ($input["numorder"]) || $input["numorder"] == '') {
			addMessageAfterRedirect($LANG['plugin_order'][44], false, ERROR);
			return array ();
		}
		elseif (!isset ($input["name"]) || $input["name"] == '') $input["name"] = $input["numorder"];

		return $input;
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB;

		if (!plugin_order_haveRight("order", "r"))
			return false;
		$spotted = false;
		if ($ID > 0) {
			if ($this->can($ID, 'r')) {
				$spotted = true;
			}
		} else {
			if ($this->can(-1, 'w')) {
				$spotted = true;
				$this->getEmpty();
			}
		}

		if ($spotted) {
			$this->showTabs($ID, $withtemplate, $_SESSION['glpi_tab']);
			$canedit = (plugin_order_canUpdateOrder($ID) && $this->can($ID, 'w') && $this->fields["status"] != ORDER_STATUS_CANCELED);
			$canrecu = $this->can($ID, 'recursive');
			echo "<form method='post' name='form' action=\"$target\">";
			if (empty ($ID) || $ID < 0) {
				echo "<input type='hidden' name='FK_entities' value='" . $_SESSION["glpiactive_entity"] . "'>";
			}

			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,$withtemplate, 2);

			//Display without inside table
			/* title */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][39] . ": </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("name", "glpi_plugin_order", "name", $this->fields["name"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["name"];
			echo "</td>";
			/* date of order */
			$editcalendar = ($withtemplate != 2);
			echo "<td>" . $LANG['plugin_order'][1] . "*:</td><td>";
			if ($canedit)
				if ($this->fields["date"] == NULL)
					showDateFormItem("date", date("Y-m-d"), true, $editcalendar);
				else
					showDateFormItem("date", $this->fields["date"], true, $editcalendar);
			else
				echo convDate($this->fields["date"]);
			echo "</td></tr>";

			/* num order */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][0] . "*: </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("numorder", "glpi_plugin_order", "numorder", $this->fields["numorder"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["numorder"];
			echo "</td>";
			echo "<td>" . $LANG['plugin_order'][3] . ": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_budget", "budget", $this->fields["budget"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_dropdown_budget", $this->fields["budget"]);
			echo "</td></tr>";

			/* num order supplier */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][31] . ": </td><td>";
			if ($canedit)
				autocompletionTextField("numordersupplier", "glpi_plugin_order", "numordersupplier", $this->fields["numordersupplier"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["numordersupplier"];
			echo "</td>";
			
			/* payment */
			echo "<td>" . $LANG['plugin_order'][32] . ": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_plugin_order_payment", "payment", $this->fields["payment"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownname("glpi_dropdown_plugin_order_payment", $this->fields["payment"]);
			echo "</td></tr>";

			/* number of bill */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][28] . ": </td><td>";
			if ($canedit)
				autocompletionTextField("numbill", "glpi_plugin_order", "numbill", $this->fields["numbill"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["numbill"];
			echo "</td>";
			
			/* location */
			echo "<td>" . $LANG['plugin_order'][40] . ": </td>";
			echo "<td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_locations", "location", $this->fields["location"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_dropdown_locations", $this->fields["location"]);
			echo "</td></tr>";
			
			/* supplier of order */
			echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td>";
			echo "<td>";
			if ($canedit)
            if (empty ($ID) || $ID < 0)
               plugin_order_dropdownSuppliers("FK_enterprise", $this->fields["FK_entities"]);
            else
               dropdownValue("glpi_enterprises", "FK_enterprise", $this->fields["FK_enterprise"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_enterprises", $this->fields["FK_enterprise"]);
			echo "</td>";
			
			/* port price */
			echo "<td>".$LANG['plugin_order'][26].": </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("port_price", "glpi_plugin_order", "port_price", (!$this->fields["port_price"]?0:$this->fields["port_price"]), 5, $this->fields["FK_entities"]);
			else
				echo $this->fields["port_price"];
			echo "</td></tr>";
			
			/* linked contact of the supplier of order */
			echo "<tr class='tab_bg_1'><td>".$LANG['common'][18].": </td>";
			echo "<td><span id='show_contact'>";
			if ($canedit && $ID > 0)
            dropdownValue("glpi_contacts", "FK_contact", $this->fields["FK_contact"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_contacts", $this->fields["FK_contact"]);
			echo "</span></td>";
			
			/* status of bill */
			echo "<td>" . $LANG['plugin_order']['status'][0] . ": </td>";
			echo "<td>";
			echo "<input type='hidden' name='status' value=" . ORDER_STATUS_DRAFT . ">";
			echo plugin_order_getDropdownStatus($this->fields["status"]);
			echo "</td></tr>";
			//End

			echo "<tr class='tab_bg_1'><td>";
			//comments of order
			echo $LANG['plugin_order'][2] . ":	</td>";
			echo "<td>";
			if ($canedit)
				echo "<textarea cols='40' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
			else
				echo $this->fields["comment"];
			echo "</td>";

			/* total price (without taxes) */

			if ($ID > 0) {
				$prices = pugin_order_getAllPrices($ID);

				echo "<td colspan='2'>" . $LANG['plugin_order'][13] . " : ";
				echo plugin_order_displayPrice($prices["priceHT"]) . "<br>";
        
        /* total price (with postage) */
				echo $LANG['plugin_order'][15] . " : ";
				$priceHTwithpostage=$prices["priceHT"]+$this->fields["port_price"];
				echo plugin_order_displayPrice($priceHTwithpostage) . "<br>";
				
				/* total price (with taxes) */
				echo $LANG['plugin_order'][14] . " : ";
				echo plugin_order_displayPrice($prices["priceTTC"]) . "</td></tr>";
			} else
				echo "<td colspan='2'></td>";

			echo "</tr>";

			if ($canedit) {
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='4' align='center'>";

				if (empty ($ID) || $ID < 0) {
					echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
				} else {
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";

					if (!$this->fields["deleted"]) {
						echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'>";
					} else {
						echo "&nbsp<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
						echo "&nbsp<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'>";
					}
				}
				echo "</td>";
				echo "</tr>";
			}

			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		} else {
			echo "<div align='center'><b>" . $LANG['plugin_order'][11] . "</b></div>";
			return false;
		}
		return true;
	}

	/**
	 * Print a good title for user pages
	 *
	 *@return nothing (display)
	 **/
	function title() {
		global $LANG, $CFG_GLPI;
		displayTitle($CFG_GLPI["root_doc"] . "/plugins/order/pics/order-icon.png", $LANG['plugin_order']['title'][1], $LANG['plugin_order']['title'][1]);
	}

	function needValidation($ID) {
		global $ORDER_VALIDATION_STATUS;
		if ($ID > 0 && $this->getFromDB($ID))
			return (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS));
		else
			return false;
	}

	function canValidate() {
		global $ORDER_VALIDATION_STATUS;
		$config = plugin_order_getConfig();

		//If no validation process -> can validate if order is in draft state
		if (!$config["use_validation"])
			return ($this->fields["status"] == ORDER_STATUS_DRAFT);
		else {
			//Validation process is used

			//If order is canceled, cannot validate !
			if ($this->fields["status"] == ORDER_STATUS_CANCELED)
				return false;

			//If no right to validate
			if (!plugin_order_haveRight("validation", "w"))
				return false;
			else
				return (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS));
		}
	}

	function canCancelOrder() {
		//If order is canceled, cannot validate !
		if ($this->fields["status"] == ORDER_STATUS_CANCELED)
			return false;

		//If no right to cancel
		if (!plugin_order_haveRight("cancel", "w"))
			return false;

		return true;
	}

	function canDoValidationRequest() {
		$config = plugin_order_getConfig();
		if (!$config["use_validation"])
			return false;
		else
			return ($this->fields["status"] == ORDER_STATUS_DRAFT);
	}

	function canCancelValidationRequest() {
		return ($this->fields["status"] == ORDER_STATUS_WAITING_APPROVAL);
	}

	function canUndoValidation() {
		global $ORDER_VALIDATION_STATUS;
		
		//If order is canceled, cannot validate !
		if ($this->fields["status"] == ORDER_STATUS_CANCELED)
			return false;

		//If order is not validate, cannot undo validation !
		if (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS))
			return false;

		//If no right to cancel
		return (plugin_order_haveRight("undo_validation", "w"));
	}
}
?>