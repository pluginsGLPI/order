<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderPreference extends CommonDBTM {

   function checkIfPreferenceExists($user_id) {
      global $DB;
      
      $result = $DB->query("SELECT `id` 
                        FROM `".$this->getTable()."` 
                        WHERE `user_id` = '".$user_id."' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"id");
      else
         return 0;   
   }
   
   function addDefaultPreference($user_id) {
   
      $input["user_id"]=$user_id;
      $input["template"]="";
      $input["sign"]="";
      return $this->add($input);
   }
   
   function checkPreferenceSignatureValue($user_id) {
      global $DB;
      
      $result = $DB->query("SELECT * 
                        FROM `".$this->getTable()."` 
                        WHERE `user_id` = '".$user_id."' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"sign");
      else
         return 0;   
   }
   
   function checkPreferenceTemplateValue($user_id) {
      global $DB;
      
      $result = $DB->query("SELECT * 
                        FROM `".$this->getTable()."` 
                        WHERE `user_id` = '".$user_id."' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"template");
      else
         return 0;   
   }

   function showForm($target,$ID){
      global $LANG,$CFG_GLPI;
      
      $data=plugin_version_order();
      $this->getFromDB($ID);
      
      $dir_template = GLPI_ROOT."/plugins/order/templates/";
      $array_template = $this->getFiles($dir_template,"odt",$this->fields["template"]);
      
      $dir_sign = GLPI_ROOT."/plugins/order/signatures/";
      $array_sign = $this->getFiles($dir_sign,"png",$this->fields["sign"]);
      
      if (!empty($array_template)) {
      
         echo "<div align='center'><form method='post' action='".$target."'>";
         echo "<table class='tab_cadre_fixe' cellpadding='5'>";
         echo "<tr><th colspan='2'>" . $data['name'] . " - ". $data['version'] . "</th></tr>";
         echo "<tr class='tab_bg_2'><td align='center'>".$LANG['plugin_order']['parser'][1]."</td>";
         echo "<td align='center'>";
         
         echo "<select name='template'>";
         echo "<option value=''>".DROPDOWN_EMPTY_VALUE."</option>";
         foreach ($array_template as $item)
            echo "<option value='".$item[0]."' ".($item[0]==$this->fields["template"]?" selected ":"").">".$item[0]." - ".$item[1]."</option>";
            
         echo "</select></td></tr>";
         echo "<tr class='tab_bg_2'><td align='center'>".$LANG['plugin_order']['parser'][3]."</td>";
         echo "<td align='center'>";
         
         echo "<select name='sign'>";
         echo "<option value=''>".DROPDOWN_EMPTY_VALUE."</option>";
         foreach ($array_sign as $item)
            echo "<option value='".$item[0]."' ".($item[0]==$this->fields["sign"]?" selected ":"").">".$item[0]." - ".$item[1]."</option>";
            
         echo "</select></td></tr>";
         
         if (isset($this->fields["sign"]) && !empty($this->fields["sign"])) {
            echo "<tr class='tab_bg_2'><td align='center' colspan='2'>";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/order/signatures/".$this->fields["sign"]."'>";
            echo "</td></tr>";
         }
         
         echo "<tr class='tab_bg_2'><td align='center' colspan='2'><input type='hidden' name='id' value='".$ID."'>";
         echo "<input type='submit' name='update' value='".$LANG['buttons'][2]."' class='submit' ></td>";
         echo "</tr>";
         
         echo "</table></form></div>";
      } else {
         echo "<div align='center'><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
         echo "<b>".$LANG['plugin_order']['parser'][2]."</b></div>";
      }
   }
   
   function getFiles($dir,$ext,$select=-1) {
      
      //$file_select = $dir.$select;
      $array_dir  = array();
      $array_file = array();
      
      if (is_dir($dir)) {
         if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false)
            {
            $filename = $file;
            $filetype = filetype($dir . $file);
            $filedate = convdate(date ("Y-m-d", filemtime($dir . $file)));
            $basename=explode('.', basename($filename));
            $extension = array_pop($basename);
            if ($filename == ".." OR $filename == ".")
            {
            echo "";
            }
            else
               {
               if ($filetype == 'file' && $extension ==$ext)
                  {
                  if ($ext == 'png') {
                     $name = array_shift($basename);
                     if (strtolower($name) == strtolower($_SESSION["glpiname"])) {
                        $array_file[] = array($filename,$filedate,$extension);
                     }
                  }
                  else
                  {
                  $array_file[] = array($filename,$filedate,$extension);
                  }
                  
               }
               elseif ($filetype == "dir")
                  {
                  $array_dir[] = $filename;
                  }
               }
            }
            closedir($dh);
         }
      }

      rsort($array_file);
      
      return $array_file;
   }
}

?>