<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

commonHeader($LANG['plugin_order']['reference'][1], '', "plugins", "order", "reference");

$PluginOrderReference=new PluginOrderReference();
if ($PluginOrderReference->canView() || haveRight("config","w")) {
   
   echo "<div align='center'><script type='text/javascript'>";
   echo "cleanhide('modal_reference_content');";
   echo "var order_window=new Ext.Window({
      layout:'fit',
      width:800,
      height:400,
      closeAction:'hide',
      modal: true,
      autoScroll: true,
      title: \"".$LANG['plugin_order']['reference'][11]."\",
      autoLoad: '".$CFG_GLPI['root_doc']."/plugins/order/ajax/referencetree.php'
   });";
   echo "</script>";

   echo "<a onclick='order_window.show();' href='#modal_reference_content' title='".
          $LANG['plugin_order']['reference'][11]."'>".
          $LANG['plugin_order']['reference'][11]."</a>";
   echo "</div>";        
   Search::show("PluginOrderReference");
   
} else {
   echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] . 
      "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . $LANG['login'][5] . "</b></div>";
}

commonFooter();

?>