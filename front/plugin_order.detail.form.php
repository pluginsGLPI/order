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

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

useplugin('order',true);

if(!isset($_GET["ID"])) $_GET["ID"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$plugin_order_detail=new plugin_order_detail();

/* add detail */
if (isset($_POST["add"]))
{
	if(plugin_order_HaveRight("order","w")) 
        {
                if (!isset($_POST["type"]) || !isset($_POST["manufacturer"]) || !isset($_POST["quantity"]) || empty($_POST["quantity"]) || !isset($_POST["unitprice"]) || empty($_POST["unitprice"]) || !isset($_POST["taxes"]) || empty($_POST["taxes"]))
                {
                        commonHeader($LANG['plugin_order'][4],$_SERVER['PHP_SELF'],"plugins","order");
                        echo "<div align='center'><br><br><img src='".$CFG_GLPI["root_doc"]."/pics/warning.png' alt='warning'><br><br>";
                        echo "<b>".$LANG['plugin_order'][33]."</b></div>";
                        commonFooter();
                }else{
			$DB = new DB;
			$newID=$plugin_order_detail->add($_POST);
			$i=0;
			while($i<$_POST["quantity"]){
				$query="	INSERT INTO glpi_plugin_order_deliver (FK_order, FK_detail)
						values (".$_POST["FK_order"].",$newID)";
				$DB->query($query);
				$i++;
			}
			$query=" SELECT * FROM glpi_plugin_order
					WHERE ID=".$_POST["FK_order"]."";
			$result=$DB->query($query);
			$price=$DB->result($result,0,"price");
			$query=" UPDATE glpi_plugin_order
					SET price = $price+".$_POST["unitprice"]*$_POST["quantity"]."
					WHERE ID=".$_POST["FK_order"]."";
			$result=$DB->query($query);
			$query=" SELECT value FROM glpi_dropdown_plugin_order_taxes
					WHERE ID=".$_POST["taxes"]."";
			$result=$DB->query($query);
			$taxes=$DB->result($result,0,'value');
			$query=" UPDATE glpi_plugin_order_detail
					SET totalpricetaxes = ".$_POST["unitprice"]*$_POST["quantity"]."*$taxes
					WHERE ID=$newID";
			$result=$DB->query($query);
			plugin_order_update_status($_POST["FK_order"]);
                }
        }
	glpi_header($_SERVER['HTTP_REFERER']);
	
}
/* delete detail */
if (isset($_POST["delete"]))
{	
        if(plugin_order_HaveRight("order","w"))
                        foreach ($_POST["item"] as $key => $val){
				if ($val==1) {
					 $DB = new DB;
					$query=" 	SELECT * FROM glpi_plugin_order
							WHERE ID=".$_POST["FK_order"]."";
					$result=$DB->query($query);
					$price=$DB->result($result,0,"price");
					$query=" 	SELECT * FROM glpi_plugin_order_detail
							WHERE ID=".$key."";
					$result=$DB->query($query);
					$unitprice=$DB->result($result,0,"unitprice");
					$quantity=$DB->result($result,0,"quantity");
					$query=" 	UPDATE glpi_plugin_order
							SET price = $price-$unitprice*$quantity
							WHERE ID=".$_POST["FK_order"]."";
					$result=$DB->query($query);
					plugin_order_delete_detail($key);
					plugin_order_update_status($_POST["FK_order"]);
				}
                        }
                glpi_header($_SERVER['HTTP_REFERER']);
}
/* delivery */
if (isset($_POST["delivery"]))
{	
        if(plugin_order_HaveRight("order","w")){
		$DB=new DB;
		$i=0;
		while ($i<$_POST["num"]) {
			$query="	SELECT * FROM glpi_plugin_order_detail
					WHERE ID=".$_POST["ID"][$i]."";
			$result=$DB->query($query);
			$qreceived=$_POST["qreceived"][$i];
			settype($_POST["qreceived"][$i], "int");
			if($_POST["qreceived"][$i]>($DB->result($result,0,'quantity')-$DB->result($result,0,'delivredquantity')) || $_POST["qreceived"][$i]==0 || $_POST["qreceived"][$i]!=$qreceived){
				commonHeader($LANG['plugin_order'][4],$_SERVER['PHP_SELF'],"plugins","order");
				echo "<div align='center'><br><br>";
				echo "<img src='".$CFG_GLPI["root_doc"]."/pics/warning.png' alt='warning'><br><br>";
				echo "<b>".$LANG['plugin_order'][36]."</b></div>";
				commonFooter();
			}
			elseif(isset($_POST["delivery"][$i]) && !isset($_POST["generate"][$i]))
				plugin_order_update_delivery($_POST["ID"][$i], $_POST["qreceived"][$i]);
			elseif(isset($_POST["delivery"][$i]) && isset($_POST["generate"][$i]))
				showDelivery($_POST["FK_order"], $_POST["qreceived"][$i]);
			$i++;
		}
		plugin_order_update_status($_POST["FK_order"]);
        }
	glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
	plugin_order_checkRight("order","r");
	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) 
		$_SESSION['glpi_tab']=$_GET['onglet'];
	commonHeader($LANG['plugin_order'][4],$_SERVER["PHP_SELF"],"plugins","order","order");
	commonFooter();
}
?>