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
    Original Author of file:
    Purpose of file:
    ----------------------------------------------------------------------*/
    
function getPrice($FK_order) {
	global $DB;
		$query=" SELECT sum(reductedprice) AS result from glpi_plugin_order_detail
						WHERE FK_order=$FK_order";
		$result=$DB->query($query);
		if ($DB->result($result,0,'result') != NULL)
			return(sprintf("%01.2f", $DB->result($result,0,'result')));
		else
			return(-1);
}

function getTaxesPrice($FK_order) {
	global $DB;
		$query=" SELECT  SUM(reductedprice*(taxesprice/price)) AS result from glpi_plugin_order_detail
						WHERE FK_order=$FK_order";
		$result=$DB->query($query);
		if ($DB->result($result,0,'result') != NULL)
			return(sprintf("%01.2f", $DB->result($result,0,'result')));
		else 
			return(-1);
}
?>