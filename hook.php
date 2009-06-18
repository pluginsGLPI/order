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
   ----------------------------------------------------------------------
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/

foreach (glob(GLPI_ROOT . '/plugins/order/inc/*.php') as $file)
	include_once ($file);

function plugin_order_install() {
	global $DB, $LANG, $CFG_GLPI;
	include_once (GLPI_ROOT . "/inc/profile.class.php");

	global $DB;

	if (!TableExists("glpi_plugin_order")) {
		$query = "CREATE TABLE `glpi_plugin_order` (
						`ID` int(11) NOT NULL auto_increment,
						`name` varchar(255) UNIQUE collate utf8_unicode_ci NOT NULL default '',
						`numordersupplier` varchar(255) NOT NULL collate utf8_unicode_ci default '',
						`numbill`varchar(255) NOT NULL collate utf8_unicode_ci default '',
						`numorder`varchar(255) NOT NULL collate utf8_unicode_ci default '',
						`budget` int (11) NOT NULL default 0,
						`payment` int (11) NOT NULL default 0,
						`status` int(11) NOT NULL default 1,
						`FK_entities` int(11) NOT NULL default 0,
						`date` date,
						`FK_enterprise` INT(11) NOT NULL DEFAULT 0,
					    `location` int(11) NOT NULL default 0,
					    `FK_contact` int(11) NOT NULL default 0,
						`recursive` INT(1) NOT NULL default 1,
						`deleted` INT(1) NOT NULL default 0,
						`notes` TEXT,
						`comment` varchar(255) collate utf8_unicode_ci NOT NULL default '',
						PRIMARY KEY  (`ID`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_dropdown_plugin_order_status")) {
		$query = "CREATE TABLE `glpi_dropdown_plugin_order_status` (
							`ID` int(11) NOT NULL auto_increment,
							`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
							`comments` text,
							PRIMARY KEY  (`ID`),
							KEY `name` (`name`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_dropdown_plugin_order_payment")) {
		$query = "CREATE TABLE `glpi_dropdown_plugin_order_payment` (
						`ID` int(11) NOT NULL auto_increment,
						`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
						`comments` text,
						PRIMARY KEY  (`ID`),
						KEY `name` (`name`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_dropdown_plugin_order_taxes")) {
		$query = "CREATE TABLE `glpi_dropdown_plugin_order_taxes` (
							`ID` int(11) NOT NULL auto_increment,
							`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
							`comments` text,
							PRIMARY KEY  (`ID`),
							KEY `name` (`name`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);

		$query = "INSERT INTO `glpi_dropdown_plugin_order_taxes`(ID,name) VALUES (1,'5,5'), " .
		"(2,'19,6');";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_detail")) {
		$query = "CREATE TABLE `glpi_plugin_order_detail` (
							  `ID` int(11) NOT NULL auto_increment,
							  `FK_order` int(11) NOT NULL default 0,
							  `FK_device` int(11) NOT NULL default 0,
							  `FK_reference` int(11) NOT NULL default 0,
		  					  `deliverynum` varchar(255) NOT NULL collate utf8_unicode_ci default '',
							  `price_taxfree` FLOAT NOT NULL default 0,
							  `price_discounted` FLOAT NOT NULL default 0,
							  `price_ati` FLOAT NOT NULL default 0,
							  `status` int(1) NOT NULL default 0,
							  `date`date NOT NULL default 0,
							  PRIMARY KEY  (`ID`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_device")) {
		$query = "CREATE TABLE `glpi_plugin_order_device` (
								`ID` int(11) NOT NULL auto_increment,
								`FK_order` int(11)  NOT NULL default 0,
								`FK_device` int(11) NOT NULL default 0,
								`device_type` int(11) NOT NULL default 0,
								PRIMARY KEY  (`ID`),
								KEY `FK_device` (`FK_device`,`device_type`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_profiles")) {
		$query = "CREATE TABLE `glpi_plugin_order_profiles` (
								`ID` int(11) NOT NULL auto_increment,
								`name` varchar(255) collate utf8_unicode_ci default NULL,
								`order` char(1) default NULL,
							    `reference` char(1) default NULL,
							    `validation` char(1) default NULL,
							    `cancel` char(1) default NULL,
							    `undo_validation` char(1) default NULL,
								PRIMARY KEY  (`ID`),
								KEY `name` (`name`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_config")) {
		$query = "CREATE TABLE `glpi_plugin_order_config` (
								`ID` int(11) NOT NULL auto_increment,
								`use_validation` int(11) NOT NULL default 0,
								`default_taxes` int(11) NOT NULL default 0,
								PRIMARY KEY  (`ID`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());

		$query = "INSERT INTO `glpi_plugin_order_config`(ID,use_validation,default_taxes) VALUES (1,0,0);";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_references")) {
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
						  `ID` int(11) NOT NULL auto_increment,
						  `FK_entities` int(11) NOT NULL DEFAULT 0,
						  `FK_manufacturer` int(11) NOT NULL DEFAULT 0,
						  `FK_type` INT(11) NOT NULL DEFAULT 0,
						  `FK_model` INT(11) NOT NULL DEFAULT 0,
						  `name` varchar(255) character set latin1 NOT NULL,
						  `type` int(11) NOT NULL DEFAULT 0,
						  `template` int(11) NOT NULL DEFAULT 0,
						  `recursive` int(11) NOT NULL DEFAULT 0,
						  `deleted` int(11) NOT NULL DEFAULT 0,
						  `comments` text character set latin1 NULL,
						  PRIMARY KEY  (`ID`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_references_manufacturers")) {
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_manufacturers` (
						  `ID` int(11) NOT NULL auto_increment,
						  `FK_entities` int(11) NOT NULL DEFAULT 0,
						  `FK_reference` int(11) NOT NULL DEFAULT 0,
						  `FK_enterprise` int(11) NOT NULL DEFAULT 0,
						  `price_taxfree` float NOT NULL DEFAULT 0,
						  PRIMARY KEY  (`ID`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$DB->query($query) or die($DB->error());
	}

	if (!TableExists("glpi_plugin_order_mailing")) {
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_mailing` (
				  `ID` int(11) NOT NULL auto_increment,
				  `type` varchar(255) collate utf8_unicode_ci default NULL,
				  `FK_item` int(11) NOT NULL default '0',
				  `item_type` int(11) NOT NULL default '0',
				  PRIMARY KEY  (`ID`),
				  UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
				  KEY `type` (`type`),
				  KEY `FK_item` (`FK_item`),
				  KEY `item_type` (`item_type`),
				  KEY `items` (`item_type`,`FK_item`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query) or die($DB->error());

	}
	$query = "INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','1','1','0'),
					(NULL,'3150','2','2','0'),
					(NULL,'3150','3','3','0'),
					(NULL,'3150','4','4','0'),
					(NULL,'3150','5','5','0'),
					(NULL,'3150','6','6','0'),
					(NULL,'3150','7','7','0'),
					(NULL,'3150','8','8','0'),
					(NULL,'3150','9','9','0'),
					(NULL,'3150','10','10','0');";
	$DB->query($query) or die($DB->error());

	$query = "INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )
					VALUES (NULL, 3151, 1, 1, 0),
						   (NULL, 3151, 2, 4, 0),
						   (NULL, 3151, 6, 6, 0),
						   (NULL, 3151, 4, 5, 0),
						   (NULL, 3151, 7, 7, 0),
						   (NULL, 3151, 8, 8, 0),
						   (NULL, 3151, 5, 9, 0);";
	$DB->query($query) or die($DB->error());

	plugin_order_createfirstaccess($_SESSION['glpiactiveprofile']['ID']);
	return true;
}

function plugin_order_uninstall() {
	global $DB;

	/* drop all the plugin tables */
	$tables = array (
		"glpi_plugin_order",
		"glpi_plugin_order_detail",
		"glpi_plugin_order_device",
		"glpi_plugin_order_profiles",
		"glpi_dropdown_plugin_order_status",
		"glpi_dropdown_plugin_order_taxes",
		"glpi_dropdown_plugin_order_payment",
		"glpi_plugin_order_references",
		"glpi_plugin_order_references_manufacturers",
		"glpi_plugin_order_config"
	);

	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");

	$in = "IN (" . implode(',', array (
		PLUGIN_ORDER_TYPE,
		PLUGIN_ORDER_REFERENCE_TYPE,
		PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE
	)) . ")";
	/* clean glpi_display */
	$query = "DELETE FROM `glpi_display` WHERE type " . $in;
	$DB->query($query);

	$tables = array (
		"glpi_doc_device",
		"glpi_bookmark",
		"glpi_history"
	);

	foreach ($tables as $table) {
		$query = "DELETE FROM `$table` WHERE device_type " . $in;
		$DB->query($query);
	}

	if (TableExists("glpi_plugin_data_injection_models"))
		$DB->query("DELETE FROM `glpi_plugin_data_injection_models`, `glpi_plugin_data_injection_mappings`, `glpi_plugin_data_injection_infos` USING `glpi_plugin_data_injection_models`, `glpi_plugin_data_injection_mappings`, `glpi_plugin_data_injection_infos`
							WHERE glpi_plugin_data_injection_models.device_type=" .
		PLUGIN_ORDER_TYPE . "
							AND glpi_plugin_data_injection_mappings.model_id=glpi_plugin_data_injection_models.ID
							AND glpi_plugin_data_injection_infos.model_id=glpi_plugin_data_injection_models.ID");
	plugin_init_order();
	cleanCache("GLPI_HEADER_" . $_SESSION["glpiID"]);
	return true;
}

/* define dropdown relations */
function plugin_order_getDatabaseRelations() {
	$plugin = new Plugin();
	if ($plugin->isInstalled("order") && $plugin->isActivated("order"))
		return array (
			"glpi_dropdown_plugin_order_status" => array (
				"glpi_plugin_order" => "status"
			),
			"glpi_dropdown_plugin_order_payment" => array (
				"glpi_plugin_order" => "payment"
			),
			"glpi_dropdown_plugin_order_taxes" => array (
				"glpi_plugin_order" => "taxes"
			),
			"glpi_entities" => array (
				"glpi_plugin_order" => "FK_entities"
			)
		);
	else
		return array ();
}

/* define dropdown tables to be manage in GLPI : */
function plugin_order_getDropdown() {
	/* table => name */
	global $LANG;

	$plugin = new Plugin();
	if ($plugin->isInstalled("order") && $plugin->isActivated("order"))
		return array (
			"glpi_dropdown_plugin_order_status" => $LANG['plugin_order']['status'][0],
			"glpi_dropdown_plugin_order_taxes" => $LANG['plugin_order'][25],
			"glpi_dropdown_plugin_order_payment" => $LANG['plugin_order'][32]
		);
	else
		return array ();
}

/* ------ SEARCH FUNCTIONS ------ (){ */
/* define search option for types of the plugins */
function plugin_order_getSearchOption() {
	global $LANG;

	$sopt = array ();
	if (plugin_order_haveRight("order", "r")) {
		/* part header */
		$sopt[PLUGIN_ORDER_TYPE]['common'] = $LANG['plugin_order'][4];
		/* order number */
		$sopt[PLUGIN_ORDER_TYPE][1]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][1]['field'] = 'numorder';
		$sopt[PLUGIN_ORDER_TYPE][1]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][1]['name'] = $LANG['plugin_order'][0];
		$sopt[PLUGIN_ORDER_TYPE][1]['datatype'] = 'itemlink';
		/* date */
		$sopt[PLUGIN_ORDER_TYPE][2]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][2]['field'] = 'date';
		$sopt[PLUGIN_ORDER_TYPE][2]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][2]['name'] = $LANG['plugin_order'][1];
		/* budget */
		$sopt[PLUGIN_ORDER_TYPE][3]['table'] = 'glpi_dropdown_budget';
		$sopt[PLUGIN_ORDER_TYPE][3]['field'] = 'name';
		$sopt[PLUGIN_ORDER_TYPE][3]['linkfield'] = 'budget';
		$sopt[PLUGIN_ORDER_TYPE][3]['name'] = $LANG['plugin_order'][3];

		/* location */
		$sopt[PLUGIN_ORDER_TYPE][4]['table'] = 'glpi_dropdown_locations';
		$sopt[PLUGIN_ORDER_TYPE][4]['field'] = 'name';
		$sopt[PLUGIN_ORDER_TYPE][4]['linkfield'] = 'location';
		$sopt[PLUGIN_ORDER_TYPE][4]['name'] = $LANG['plugin_order'][40];
		/* status */
		$sopt[PLUGIN_ORDER_TYPE][5]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][5]['field'] = 'status';
		$sopt[PLUGIN_ORDER_TYPE][5]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][5]['name'] = $LANG['plugin_order']['status'][0];
		/* supplier */
		$sopt[PLUGIN_ORDER_TYPE][6]['table'] = 'glpi_enterprises';
		$sopt[PLUGIN_ORDER_TYPE][6]['field'] = 'name';
		$sopt[PLUGIN_ORDER_TYPE][6]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][6]['name'] = $LANG['financial'][26];
		/* payment */
		$sopt[PLUGIN_ORDER_TYPE][7]['table'] = 'glpi_dropdown_plugin_order_payment';
		$sopt[PLUGIN_ORDER_TYPE][7]['field'] = 'name';
		$sopt[PLUGIN_ORDER_TYPE][7]['linkfield'] = 'payment';
		$sopt[PLUGIN_ORDER_TYPE][7]['name'] = $LANG['plugin_order'][32];

		/* bill number */
		$sopt[PLUGIN_ORDER_TYPE][9]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][9]['field'] = 'numbill';
		$sopt[PLUGIN_ORDER_TYPE][9]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][9]['name'] = $LANG['plugin_order'][28];
		/* title */
		$sopt[PLUGIN_ORDER_TYPE][10]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][10]['field'] = 'name';
		$sopt[PLUGIN_ORDER_TYPE][10]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][10]['name'] = $LANG['plugin_order'][39];

		/* comments */
		$sopt[PLUGIN_ORDER_TYPE][16]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][16]['field'] = 'comment';
		$sopt[PLUGIN_ORDER_TYPE][16]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][16]['name'] = $LANG['plugin_order'][2];
		$sopt[PLUGIN_ORDER_TYPE][16]['datatype'] = 'text';
		/* ID */
		$sopt[PLUGIN_ORDER_TYPE][30]['table'] = 'glpi_plugin_order';
		$sopt[PLUGIN_ORDER_TYPE][30]['field'] = 'ID';
		$sopt[PLUGIN_ORDER_TYPE][30]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_TYPE][30]['name'] = $LANG['common'][2];
		/* entity */
		$sopt[PLUGIN_ORDER_TYPE][80]['table'] = 'glpi_entities';
		$sopt[PLUGIN_ORDER_TYPE][80]['field'] = 'completename';
		$sopt[PLUGIN_ORDER_TYPE][80]['linkfield'] = 'FK_entities';
		$sopt[PLUGIN_ORDER_TYPE][80]['name'] = $LANG['entity'][0];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE]['common'] = $LANG['plugin_order']['reference'][1];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][1]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][1]['field'] = 'ID';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][1]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][1]['name'] = "ID";
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][1]['datatype'] = 'itemlink';

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][2]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][2]['field'] = 'name';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][2]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][2]['name'] = $LANG['plugin_order']['detail'][2];
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][2]['datatype'] = 'itemlink';
		
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][3]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][3]['field'] = 'comments';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][3]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][3]['name'] = $LANG['common'][25];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][4]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][4]['field'] = 'FK_type';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][4]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][4]['name'] = $LANG['state'][6];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][5]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][5]['field'] = 'template';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][5]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][5]['name'] = $LANG['common'][13];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][6]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][6]['field'] = 'FK_manufacturer';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][6]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][6]['name'] = $LANG['common'][5];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][7]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][7]['field'] = 'type';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][7]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][7]['name'] = $LANG['common'][17];

		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][8]['table'] = 'glpi_plugin_order_references';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][8]['field'] = 'FK_model';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][8]['linkfield'] = '';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][8]['name'] = $LANG['common'][22];


		/* entity */
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][80]['table'] = 'glpi_entities';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][80]['field'] = 'completename';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][80]['linkfield'] = 'FK_entities';
		$sopt[PLUGIN_ORDER_REFERENCE_TYPE][80]['name'] = $LANG['entity'][0];

		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['table'] = 'glpi_plugin_order_references_manufacturers';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['field'] = 'price_taxfree';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['linkfield'] = 'price_taxfree';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['name'] = $LANG['plugin_order']['detail'][4];

	}
	return $sopt;
}

function plugin_order_addSelect($type,$ID,$num){
	global $SEARCH_OPTION;

	$table=$SEARCH_OPTION[$type][$ID]["table"];
	$field=$SEARCH_OPTION[$type][$ID]["field"];
	
	if ($table == "glpi_plugin_order_references" && !$num)
		return $table.".FK_manufacturer as manufacturer, ".
		$table.".type AS device_type, ".
		$table.".FK_type AS type, ".
		$table.".FK_model AS model, ".
		$table.".template AS template, ".
		$table.".$field as ITEM_$num, ";
	else
		return "";	
}

/* display custom fields in the search */
function plugin_order_giveItem($type, $ID, $data, $num) {
	global $CFG_GLPI, $INFOFORM_PAGES, $LANG, $SEARCH_OPTION, $LINK_ID_TABLE, $DB;
	$table = $SEARCH_OPTION[$type][$ID]["table"];
	$field = $SEARCH_OPTION[$type][$ID]["field"];

	switch ($table . '.' . $field) {
		/* display associated items with order */
		case "glpi_plugin_order.status" :
			return plugin_order_getDropdownStatus($data["ITEM_" . $num]);
		case "glpi_plugin_order_references.FK_type":
			$commonitem = new CommonItem;
			$commonitem->setType($data["device_type"]);
			return $commonitem->getType();
		case "glpi_plugin_order_references.manufacturer":
			return getDropdownName("glpi_dropdown_manufacturers",$data["manufacturer"]);
		case "glpi_plugin_order_references.type":
			return getDropdownName(plugin_order_getTypeTable($data["device_type"]),$data["type"]);
		case "glpi_plugin_order_references.FK_model":
			return getDropdownName(plugin_order_getModelTable($data["device_type"]),$data["model"]);
		case "glpi_plugin_order_references.template":
			if (!$data["template"])
				return " ";
			else	
				return plugin_order_getTemplateName($data["device_type"],$data["template"]); 
	}
	return "";
}

/* ----- SPECIFIC MODIF MASSIVE FUNCTIONS ----- */
function plugin_order_MassiveActions($type) {
	global $LANG;

	switch ($type) {
		case PLUGIN_ORDER_TYPE :
			return array (
				/* GLPI core one */
				"add_document" => $LANG['document'][16],
				/* tranfer order to another entity */
				"plugin_order_transfert" => $LANG['buttons'][48],

				
			);
			break;
	}
	return array ();
}

function plugin_order_MassiveActionsDisplay($type, $action) {
	global $LANG, $CFG_GLPI;

	switch ($type) {
		case PLUGIN_ORDER_TYPE :
			switch ($action) {
				/* no case for add_document : use GLPI core one */
				case "plugin_order_transfert" :
					dropdownValue("glpi_entities", "FK_entities", '');
					echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG['buttons'][2] . "\" >";
					break;
			}
			break;
	}
	if (in_array($type, array (
			COMPUTER_TYPE,
			MONITOR_TYPE,
			NETWORKING_TYPE,
			PERIPHERAL_TYPE,
			PHONE_TYPE,
			PRINTER_TYPE,
			SOFTWARE_TYPE,
			TRACKING_TYPE,
			CONTRACT_TYPE
		))) {
		plugin_order_dropdownorder("conID");
		echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG['buttons'][2] . "\" >";
	}
	return "";
}

function plugin_order_MassiveActionsProcess($data) {
	global $LANG, $DB;

	switch ($data['action']) {
		case "plugin_order_transfert" :
			if ($data['device_type'] == PLUGIN_ORDER_TYPE) {
				foreach ($data["item"] as $key => $val) {
					if ($val == 1) {
						$plugin_order = new PluginOrder;
						$plugin_order->getFromDB($key);
						$query = "UPDATE `glpi_plugin_order` 
								  SET `FK_entities` = '" . $data['FK_entities'] . "' 
								  WHERE `ID` ='$key'";
						$DB->query($query);
					}
				}
			}
			break;
	}
}

/* hook done on delete item case */
function plugin_pre_item_delete_order($input) {
	if (isset ($input["_item_type_"]))
		switch ($input["_item_type_"]) {
			case PROFILE_TYPE :
				/* manipulate data if needed */
				$plugin_order_Profile = new PluginOrderProfile;
				$plugin_order_Profile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

function plugin_pre_item_update_order($input) {
	global $LANG;
	if (isset ($input["_item_type_"]))
		switch ($input["_item_type_"]) {
			case INFOCOM_TYPE :
				$infocom = new InfoCom;
				$infocom->getFromDB($input["ID"]);

				$device = new PluginOrderDevice;
				if ($device->isDeviceLinkedToOrder($infocom->fields["device_type"], $infocom->fields["FK_device"])) {
					$field_set = false;
					$unset_fields = array (
						"num_commande",
						"bon_livraison",
						"budget",
						"FK_enterprise",
						"facture",
						"value",
						"buy_date"
					);
					foreach ($unset_fields as $field)
						if (isset ($input[$field])) {
							$field_set = true;
							unset ($input[$field]);
						}
					if ($field_set)
						addMessageAfterRedirect($LANG['plugin_order']['infocom'][1], true, ERROR);
				}
				break;
		}
	return $input;
}

/* hook done on purge item case */
function plugin_item_purge_order($parm) {
	if (in_array($parm["type"], array (
			COMPUTER_TYPE,
			MONITOR_TYPE,
			NETWORKING_TYPE,
			PERIPHERAL_TYPE,
			PHONE_TYPE,
			PRINTER_TYPE,
			SOFTWARE_TYPE,
			CONTRACT_TYPE,
			PROFILE_TYPE
		))) {
		$plugin_order = new PluginOrder;
		$plugin_order->cleanItems($parm["ID"], $parm["type"]);
		$detail = new PluginOrderDetail;
		$detail->cleanItems($parm["ID"], $parm["type"]);
		return true;
	}
	elseif (in_array($parm["type"], array (
		DOCUMENT_TYPE
	))) {
		$plugin_order = new PluginOrder;
		$plugin_order->cleanDocuments($parm["ID"]);
		return true;
	} else
		return false;
}

/* define headings added by the plugin */
function plugin_get_headings_order($type, $withtemplate = '') {
	global $LANG, $ORDER_AVAILABLE_TYPES;

	$types = $ORDER_AVAILABLE_TYPES;
	$types[] = ENTERPRISE_TYPE;
	$types[] = PROFILE_TYPE;
	$types[] = "mailing";
	if (in_array($type, $types)) {
		/* template case */
		if ($withtemplate = '')
			return array ();
		/* non template case */
		else
			return array (
				1 => $LANG['plugin_order'][4],

				
			);
	} else
		return false;
}

/* define headings actions added by the plugin */
function plugin_headings_actions_order($type) {
	global $ORDER_AVAILABLE_TYPES;
	$types = $ORDER_AVAILABLE_TYPES;
	$types[] = ENTERPRISE_TYPE;
	$types[] = PROFILE_TYPE;
	$types[] = "mailing";
	if (in_array($type, $types)) {
		return array (
			1 => "plugin_headings_order",

			
		);
	} else
		return false;
}

/* action heading */
function plugin_headings_order($type, $ID, $withtemplate = 0) {
	global $CFG_GLPI, $LANG, $ORDER_AVAILABLE_TYPES;

	switch ($type) {
		case ENTERPRISE_TYPE :
			echo "<div align='center'>";
			plugin_order_showReferencesBySupplierID($ID);
			echo "</div>";
			break;
		case PROFILE_TYPE :
			$profile = new profile;
			$profile->GetfromDB($ID);
			if ($profile->fields["interface"] != "helpdesk") {
				$prof = new PluginOrderProfile();
				if (!$prof->GetfromDB($ID))
					plugin_order_createaccess($ID);
				$prof->showForm($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.profile.php", $ID);
			} else {
				echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'><td align='center'>";
				echo $LANG['plugin_order']['setup'][2];
				echo "</td></tr></table>";
			}
			break;
		case "mailing" :
			$mailing = new PluginOrderConfigMailing;
			$mailing->showMailingForm($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.setup.mailing.php");
			break;
		default :
			if (in_array($type, $ORDER_AVAILABLE_TYPES) && !$withtemplate)
				plugin_order_showOrderInfoByDeviceID($type, $ID);
			break;
	}
}
?>