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
   
$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","user","search","tracking");
define('GLPI_ROOT', '../..'); 
include (GLPI_ROOT."/inc/includes.php");

commonHeader($LANG['plugin_order'][4],$_SERVER["PHP_SELF"],"plugins","order","order");

if(plugin_order_haveRight("order","r") && plugin_order_haveRight("reference","r"))
{
	echo "<div class='center'>";
	echo "<table class='tab_cadre'>";
	echo "<tr><th>".$LANG['plugin_order'][4]."</th></tr>";
	echo "<tr class='tab_bg_1' align='center'><td><a href='./front/plugin_order.order.php'>".$LANG['plugin_order']['menu'][1]."</a></td></tr>";
	echo "<tr class='tab_bg_1' align='center'><td><a href='./front/plugin_order.reference.php'>".$LANG['plugin_order']['menu'][2]."</a></td></tr>";
	echo "</table></div>";
} 
elseif (plugin_order_haveRight("order","r"))
{
	glpi_header("./front/plugin_order.order.php");	
}else{
	echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
	echo "<b>".$LANG['login'][5]."</b></div>";
}
commonFooter();
?>