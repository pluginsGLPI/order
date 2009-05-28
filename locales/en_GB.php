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

$title = "Orders";

$LANG['plugin_order'][0] = "Order number";
$LANG['plugin_order'][1] = "Order date";
$LANG['plugin_order'][2] = "Comments";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "".$title."";
$LANG['plugin_order'][11] = "No order found";
$LANG['plugin_order'][12] = "Delivery number";
$LANG['plugin_order'][13] = "Total price";
$LANG['plugin_order'][14] = "Prix Total (TTC)";
$LANG['plugin_order'][21] = "";
$LANG['plugin_order'][26] = "Total HT";
$LANG['plugin_order'][27] = "Total TTC";
$LANG['plugin_order'][28] = "N° Facture";

$LANG['plugin_order']['status'][0]="Status";
$LANG['plugin_order']['status'][1]="In progress";
$LANG['plugin_order']['status'][2]="Finished";
$LANG['plugin_order']['status'][3]="Partialy delivred";

$LANG['plugin_order']['item'][0]="Dependant material";

$LANG['plugin_order']['detail'][0]="Detail(s)";
$LANG['plugin_order']['detail'][1]="Type";
$LANG['plugin_order']['detail'][2]="Model";
$LANG['plugin_order']['detail'][3]="Ordered quantity";
$LANG['plugin_order']['detail'][4]="Delivred quantity";
$LANG['plugin_order']['detail'][5]="Unit price";	

$LANG['plugin_order']['profile'][0] = "Rights management"; 
$LANG['plugin_order']['profile'][1] = "$title"; 

$LANG['plugin_order']['setup'][1] = "Category";
$LANG['plugin_order']['setup'][2] = "You cannot use this plugin on helpdesk";
$LANG['plugin_order']['setup'][11] = "Server";
$LANG['plugin_order']['setup'][12] = "Language";
$LANG['plugin_order']['setup'][14] = "Supplier";
$LANG['plugin_order']['setup'][15] = "Associated item(s)";
$LANG['plugin_order']['setup'][23] = "Associate";
$LANG['plugin_order']['setup'][24] = "Dissociate";
$LANG['plugin_order']['setup'][25] = "Associate to web application";
$LANG['plugin_order']['setup'][28] = "Editor";
?>