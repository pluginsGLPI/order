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
    
function getQuantity($FK_order, $FK_ref) {
	global $CFG_GLPI, $DB;
		$query="	SELECT count(*) AS quantity FROM glpi_plugin_order_detail
							WHERE FK_order=$FK_order
							AND FK_ref=$FK_ref";
		$result=$DB->query($query);
		return($DB->result($result,0,'quantity'));
}
function getDelivredQuantity($FK_order, $FK_ref) {
	global  $CFG_GLPI, $DB;
		$query="	SELECT count(*) AS delivredquantity FROM glpi_plugin_order_detail
							WHERE FK_order=$FK_order
							AND FK_ref=$FK_ref
							AND status='1'";
		$result=$DB->query($query);
		return($DB->result($result,0,'delivredquantity'));
}
function getTaxes($FK_order, $FK_ref) {
	global  $CFG_GLPI, $DB;
		$query="	SELECT price, taxesprice FROM glpi_plugin_order_detail
							WHERE FK_order=$FK_order
							AND FK_ref=$FK_ref";
		$result=$DB->query($query);
		$taxes=$DB->result($result,0,'taxesprice')/$DB->result($result,0,'price');
		return($taxes);
}

function addDetails($referenceID,$orderID,$quantity,$price,$discounted_price,$taxes)
{
	if ($quantity > 0)
	{
		$detail = new plugin_order_detail;
		for ($i=0;$i<$quantity;$i++)
		{
			$input["FK_order"] = $orderID;
			$input["FK_ref"] = $referenceID;
			$input["price"] = $price;
			$input["taxesprice"] = (($price*getDropdownName("glpi_dropdown_plugin_order_taxes",$taxes))/100)+$price;
			$input["reductedprice"] = $discounted_price;
			$input["status"] = ORDER_STATUS_NOT_DELIVERED;
			$detail->add($input);
		}
	}   	
}

function deleteDetails($referenceID,$orderID)
{
	global $DB;
	$query=" DELETE FROM glpi_plugin_order_detail
			WHERE FK_order=$orderID 
			AND FK_ref=$referenceID";
	$DB->query($query);
}

?>