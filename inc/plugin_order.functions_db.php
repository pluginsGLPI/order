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
/* link order to items of glpi */
function plugin_order_linkdevice($conID,$ID,$type){

	$DB = new DB;
	$query="INSERT INTO glpi_plugin_order_device (FK_order,FK_device, device_type ) 
			VALUES ('$conID','$ID','$type');";
	$result = $DB->query($query);
}

/* unlink order to items of glpi */
function plugin_order_unlinkdevice($ID){

	$DB = new DB;
	$query="DELETE 
			FROM glpi_plugin_order_device 
			WHERE ID= '$ID';";
	$result = $DB->query($query);
}
/* delete detail from an order */
function plugin_order_delete_detail($ID){

	$DB = new DB;
	$query="DELETE 
			FROM glpi_plugin_order_detail 
			WHERE ID= '$ID';";
	$result = $DB->query($query);
}

/* update order status */
function plugin_order_update_status($ID){

        $DB=new DB;
        $query="SELECT sum(quantity) AS somme FROM glpi_plugin_order_detail WHERE FK_order=$ID";
        $result=$DB->query($query);
        $quantity=$DB->result($result,0, "somme");
        
        $query="SELECT sum(delivredquantity) AS somme FROM glpi_plugin_order_detail WHERE FK_order=$ID";
        $result=$DB->query($query);
        $dquantity=$DB->result($result,0, "somme");
	
	$query_status=" SELECT * FROM glpi_plugin_order_config
				 WHERE ID=1";
	$result_status=$DB->query($query_status);
	$status1=$DB->result($result_status,0,"status_delivered");
	$status2=$DB->result($result_status,0,"status_nodelivered");
       
        if($quantity==$dquantity)
        {
                $query="UPDATE glpi_plugin_order SET status=$status1 WHERE ID=$ID";
                $result=$DB->query($query);
                 
        }else{
                $query="UPDATE glpi_plugin_order SET status=$status2 WHERE ID=$ID";
                $result=$DB->query($query);
        }
}

/* update order delivery */
function plugin_order_update_delivery($ID, $qreceived){
	$DB=new DB;
	$query="SELECT * FROM glpi_plugin_order_detail WHERE ID=$ID";
	$result=$DB->query($query);
	$dquantity=$DB->result($result,0,"delivredquantity");
	$query="UPDATE glpi_plugin_order_detail SET delivredquantity=$dquantity+$qreceived WHERE ID=$ID";
	$result=$DB->query($query);
}

