<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginOrderPurchaseRequest
 */
class PluginOrderPurchaseRequest extends CommonDBTM {
   public static $rightname  = 'plugin_order_purchaserequest';
   public $dohistory         = true;


   // additionnals rights
   const RIGHT_VALIDATION  = 32768;

   /**
    * @param int $nb
    *
    * @return string|\translated
    */
   public static function getTypeName($nb = 0) {
      return __("Purchase request", "order");
   }

   /**
    * @return bool
    */
   public static function canValidation() {
      return Session::haveRight("plugin_order_purchaserequest", self::RIGHT_VALIDATION);
   }

   /**
    * @param array $options
    * @return array
    */
   function defineTabs($options = array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return string|\translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == "PluginOrderPurchaseRequest") {
         return __('Approval');
      } else if ($item->getType() == "Ticket") {
         return self::getTypeName();
      } else if ($item->getType() == "PluginOrderOrder") {
         return self::getTypeName();
      }

      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == "PluginOrderPurchaseRequest") {
         self::showValidation($item);
      } else if ($item->getType() == "Ticket") {
         self::showForTicket($item);
      } else if ($item->getType() == "PluginOrderOrder") {
         self::showForOrder($item);
      }

      return true;
   }

   /**
    * @param array|\datas $input
    *
    * @return array|bool|\datas
    */
   public function prepareInputForAdd($input) {
      if(!$this->checkMandatoryFields($input)){
         return false;
      }

      $input['status'] = CommonITILValidation::WAITING;


      return $input;
   }

   /**
    * Prepare input datas for updating the item
    *
    * @param $input datas used to update the item
    *
    * @return the modified $input array
    **/
   public function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      if (isset($input['update_status'])) {

         if ($CFG_GLPI["use_mailing"]) {
            $purchase_request = new PluginOrderPurchaseRequest();
            $purchase_request->getFromDB($input['id']);
            $purchase_request->fields['status']             = $input['status'];
            $purchase_request->fields['comment_validation'] = $input['comment_validation'];

            if(isset($input['status'])
               && $input['status'] == CommonITILValidation::ACCEPTED) {
               NotificationEvent::raiseEvent('validation_purchaserequest', $purchase_request);
            } else if(isset($input['status'])
               && $input['status']== CommonITILValidation::REFUSED) {
               NotificationEvent::raiseEvent('no_validation_purchaserequest', $purchase_request);
            }
         }

      } else {
         if (!$this->checkMandatoryFields($input)) {
            return false;
         }
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return nothing
    **/
   public function post_addItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent('ask_purchaserequest', $this);
      }
   }


   /**
    * @param $input
    *
    * @return bool
    */
   function checkMandatoryFields($input){
      $msg     = array();
      $checkKo = false;

      $mandatory_fields = array('users_id'          => __('Requester'),
                                'comment'           => __('Description'),
                                'itemtype'          => __('Item type'),
                                'types_id'          => __('Type'),
                                'users_id_validate' => __('To be validated by', 'order'));

      foreach($input as $key => $value){
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               if(($key == 'item' && $input['type'] == 'dropdown')
                  || ($key == 'label2' && $input['type'] == 'datetime_interval')){

                  $msg[] = $mandatory_fields[$key];
                  $checkKo = true;
               } elseif($key != 'item' && $key != 'label2') {
                  $msg[] = $mandatory_fields[$key];
                  $checkKo = true;
               }
            }
         }
         $_SESSION['glpi_plugin_orders_fields'][$key] = $value;
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
         return false;
      }
      return true;
   }

   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = array();

      if ($interface == 'central') {
         $values                         = parent::getRights();
         $values[self::RIGHT_VALIDATION] = __("Purchase request validation", "order");
      }

      return $values;
   }

   /**
    * Get the Search options for the given Type
    *
    * This should be overloaded in Class
    *
    * @return an array of search options
    * More information on https://forge.indepnet.net/wiki/glpi/SearchEngine
    **/
   public function getSearchOptions() {
      $tab = array();

      $tab['common']            = self::getTypeName();

      $tab[1]['table']          = $this->getTable();
      $tab[1]['field']          = 'name';
      $tab[1]['name']           = __("Name");
      $tab[1]['datatype']       = 'itemlink';

      $tab[2]['table']          = getTableForItemType('User');
      $tab[2]['field']          = 'name';
      $tab[2]['name']           = __("Requester");
      $tab[2]['linkfield']      = 'users_id';

      $tab[3]['table']          = getTableForItemType('Group');
      $tab[3]['field']          = 'name';
      $tab[3]['name']           = __("Requester group");
      $tab[3]['linkfield']      = 'groups_id';

      $tab[4]['table']          = $this->getTable();
      $tab[4]['field']          = 'itemtype';
      $tab[4]['name']           = __("Item type");
      $tab[4]['datatype']       = 'itemtypename';
      $tab[4]['massiveaction']  = false;
      $tab[4]['itemtype_list']  = 'plugin_order_types';
      $tab[4]['checktype']      = 'itemtype';
      $tab[4]['searchtype']     = array('equals');
      $tab[4]['injectable']     = true;

      $tab[5]['table']          = getTableForItemType('User');
      $tab[5]['field']          = 'name';
      $tab[5]['linkfield']      = 'users_id_validate';
      $tab[5]['name']           = __("Approver");

      $tab[6]['table']         = $this->getTable();
      $tab[6]['field']         = 'due_date';
      $tab[6]['massiveaction'] = false;
      $tab[6]['name']          = __("Due date", "order");
      $tab[6]['datatype']      = 'datetime';

      $tab[7]['table']          = $this->getTable();
      $tab[7]['field']          = 'types_id';
      $tab[7]['name']           = __("Type");
      $tab[7]['massiveaction']  = false;
      $tab[7]['checktype']      = 'text';
      $tab[7]['injectable']     = true;
      $tab[7]['searchtype']     = array('equals');
      $tab[7]['nosearch']       = true;

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'status';
      $tab[8]['name']            = __('Status');
      $tab[8]['searchtype']      = 'equals';
      $tab[8]['datatype']        = 'specific';

      $tab[9]['table']          = $this->getTable();
      $tab[9]['field']          = 'plugin_order_orders_id';
      $tab[9]['datatype']       = 'itemlink';
      $tab[9]['massiveaction']  = false;
      $tab[9]['name']           = PluginOrderOrder::getTypeName();

      $tab[10]['table']         = 'glpi_tickets';
      $tab[10]['field']         = 'name';
      $tab[10]['datatype']      = 'itemlink';
      $tab[10]['massiveaction'] = false;
      $tab[10]['name']          = Ticket::getTypeName(Session::getPluralNumber());
      $tab[10]['joinparams']    = array('beforejoin'
                                        => array('table'      => $this->getTable(),
                                                 'joinparams' => array('jointype' => 'child')));

      /* comments */
      $tab[16]['table']         = $this->getTable();
      $tab[16]['field']         = 'comment';
      $tab[16]['name']          = __("Description");
      $tab[16]['datatype']      = 'text';

      /* ID */
      $tab[30]['table']         = $this->getTable();
      $tab[30]['field']         = 'id';
      $tab[30]['name']          = __("ID");

      /* entity */
      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = __("Entity");

      $tab[86]['table']         = $this->getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = __("Child entities");
      $tab[86]['datatype']      = 'bool';
      $tab[86]['massiveaction'] = false;

      return $tab;
   }

   /**
    * @param $field
    * @param $values
    * @param $options   array
    **/
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'status':
            return CommonITILValidation::getStatus($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @param $field
    * @param $name              (default '')
    * @param $values            (default '')
    * @param $options   array
    **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $options['value'] = $values[$field];
            return CommonITILValidation::dropdownStatus($name, $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   public function showForm ($ID, $options=array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $canedit            = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      // Data saved in session
      if(isset($_SESSION['glpi_plugin_orders_fields'])){
         foreach($_SESSION['glpi_plugin_orders_fields'] as $key => $value){
            $this->fields[$key] = $value;
         }
         unset($_SESSION['glpi_plugin_orders_fields']);
      }

      /* title */
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Name") . ":</td><td>";
      if ($canedit) {
         Html::autocompletionTextField($this, "name");
      } else {
         echo $this->fields["name"];
      }
      echo "</td><td colspan='2'></td></tr>";

      echo "</td></tr>";
      /* requester */
      echo "<tr class='tab_bg_1'><td>" . __("Requester") . "&nbsp;<span class='red'>*</span></td><td>";
      if ($canedit) {
         User::dropdown(array('name' => "users_id",
                              'value' => $this->fields["users_id"],
                              'entity' => $this->fields["entities_id"],
                              'right' => 'interface'));
      } else {
         echo Dropdown::getDropdownName(getTableForItemType('User'));
      }
      echo "</td>";

      /* requester group */
      echo "<td>" . __("Requester group");
      echo "</td><td>";
      if ($canedit) {
         Group::dropdown(array('name' => "groups_id",
                              'value' => $this->fields["groups_id"],
                              'entity' => $this->fields["entities_id"],
                              'right' => 'interface'));
      } else {
         echo Dropdown::getDropdownName(getTableForItemType('Group'));
      }
      echo "</td></tr>";

      /* description */
      echo "<tr class='tab_bg_1'><td>" . __("Description") . "&nbsp;<span class='red'>*</span></td>";
      echo "<td colspan='3'>";
      echo "<textarea id='comment' name='comment' rows='4' cols='100'>" . stripslashes($this->fields['comment']) . "</textarea>";

      echo "</td></tr>";

      /* type */
      $reference = new PluginOrderReference();
      echo "<tr class='tab_bg_1'><td>" . __("Item type");
      echo "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      $params = array(
         'myname'    => 'itemtype',
         'value'     => $this->fields["itemtype"],
         'entity'    => $_SESSION["glpiactive_entity"],
         'ajax_page' => $CFG_GLPI["root_doc"] . '/plugins/order/ajax/referencespecifications.php',
         'class'     => __CLASS__,
      );

      $reference->dropdownAllItems($params);
      echo "</td>";

      echo "<td>" . __("Type") . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      echo "<span id='show_types_id'>";
      if ($this->fields['itemtype']) {
         if ($this->fields['itemtype'] == 'PluginOrderOther') {
            $file = 'other';
         } else {
            $file = $this->fields['itemtype'];
         }
         $core_typefilename   = GLPI_ROOT . "/inc/" . strtolower($file) . "type.class.php";
         $plugin_typefilename = GLPI_ROOT . "/plugins/order/inc/" . strtolower($file) . "type.class.php";
         $itemtypeclass       = $this->fields['itemtype'] . "Type";

         if (file_exists($core_typefilename)
             || file_exists($plugin_typefilename)) {
               Dropdown::show($itemtypeclass,
                              array(
                                 'name'  => "types_id",
                                 'value' => $this->fields["types_id"],
                              ));

         }
      }
      echo "</span>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __("Due date", "order")."</td>";
      echo "<td>";
      Html::showDateFormItem("due_date", $this->fields["due_date"], true, true);
      echo "</td>";

      echo "<td>" . __("To be validated by", "order")."&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      User::dropdown(array('name'   => "users_id_validate",
                           'value'  => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'plugin_order_purchaserequest'));
      echo "</td></tr>";

      if((isset($this->fields['plugin_order_orders_id'])
         && $this->fields['plugin_order_orders_id'])
            || (isset($this->fields['tickets_id'])
                && $this->fields['tickets_id'])) {
         echo "<tr class='tab_bg_1'>";
         $order = new PluginOrderOrder();
         if($order->getFromDB($this->fields['plugin_order_orders_id'])) {
            echo "<tr class='tab_bg_1'><td>" . __("Linked to the order", "order") . "</td>";
            echo "<td>";
            echo $order->getLink();
            echo "</td>";

            echo "<td colspan='2'>";
            echo "</td></tr>";
         }
      }

      echo "<input type='hidden' name='users_id_creator' value='".$_SESSION['glpiID']."'/>";

      if ($canedit) {
         $this->showFormButtons($options);
      } else {
         echo "</table></div>";
         Html::closeForm();
      }

      return true;
   }

   /**
    * @param $item
    */
   static function showForTicket($item){

      $purchaserequest = new self();

      $canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));
      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }

      $datas = $purchaserequest->getItems($item->fields['id'], array('start' => $start, 'addLimit' => true));
      $rows = count($purchaserequest->getItems($item->fields['id'], array('addLimit' => false)));

      //form
      if ($canedit) {
         $purchaserequest = new PluginOrderPurchaseRequest();
         $purchaserequest->showFormPurchase($item->fields['id']);
      }

      //Purchase request linked to the ticket
      if (!empty($datas) || count($datas) > 0) {
         $purchaserequest->listItems($datas, $canedit, $start, $rows);
      } else {
         __('No item to display');
      }
   }

   /**
    * @param $tickets_id
    */
   static function showFormPurchase($tickets_id) {
      global $CFG_GLPI;

      $purchaserequest = new self();
      $purchaserequest->getEmpty();

      $ticket = new Ticket();
      $ticket->getFromDB($tickets_id);

      $purchaserequest->fields['entities_id'] = $ticket->fields['entities_id'];

      $ticket_user = new Ticket_User();
      if($ticket_user->getFromDBByQuery("WHERE `tickets_id` = $tickets_id AND `type` = ".CommonITILActor::REQUESTER)) {
         $purchaserequest->fields['users_id'] = $ticket_user->fields['users_id'];
      }

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL('PluginOrderPurchaseRequest') . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='6'>" . __('Add a purchase request', 'order') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Name") . ":</td><td>";
      Html::autocompletionTextField($purchaserequest, "name");

      //Ticket validator
      $ticket_validation = new TicketValidation();
      $ticket_validations = $ticket_validation->find("`tickets_id` = $tickets_id 
                                                      AND `status` = ".CommonITILValidation::ACCEPTED);
      $users_validations = array();
      foreach ($ticket_validations as $validation) {
         $users_validations[] = getUserName($validation['users_id_validate']);
      }

      echo "</td><td>" . __("Validated by", "order") . ":</td><td>";
      echo implode(', ', $users_validations);
      echo "</td></tr>";

      /* requester */
      echo "<tr class='tab_bg_1'><td>" . __("Requester") . "&nbsp;<span class='red'>*</span></td><td>";
      $rand_user = User::dropdown(array('name'   => "users_id",
                           'value'  => $purchaserequest->fields["users_id"],
                           'entity' => $purchaserequest->fields["entities_id"],
                           'on_change' => "pluginOrderLoadGroups();",
                           'right'  => 'interface'));

      echo "</td>";

      /* requester group */
      echo "<td>" . __("Requester group");
      echo "</td><td id='plugin_order_group'>";

      
      if($purchaserequest->fields['users_id']) {
         self::displayGroup($purchaserequest->fields['users_id']);
      }

      $JS  = "function pluginOrderLoadGroups(){";
      $params = array('users_id' => '__VALUE__',
                      'entity' => $purchaserequest->fields["entities_id"]);
      $JS .= Ajax::updateItemJsCode("plugin_order_group",
                                    $CFG_GLPI["root_doc"]."/plugins/order/ajax/dropdownGroup.php", $params, 'dropdown_users_id'.$rand_user, false);
      $JS .= "}";
      echo Html::scriptBlock($JS);


      echo "</td></tr>";

      /* description */
      echo "<tr class='tab_bg_1'><td>" . __("Description") . "&nbsp;<span class='red'>*</span></td>";
      echo "<td colspan='3'>";
      echo "<textarea id='comment' name='comment' rows='4' cols='100'>";
      echo stripslashes($purchaserequest->fields['comment']) . "</textarea>";

      echo "</td></tr>";

      /* type */
      $reference = new PluginOrderReference();
      echo "<tr class='tab_bg_1'><td>" . __("Item type");
      echo "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      $params = array(
         'myname'    => 'itemtype',
         'value'     => $purchaserequest->fields["itemtype"],
         'entity'    => $_SESSION["glpiactive_entity"],
         'ajax_page' => $CFG_GLPI["root_doc"] . '/plugins/order/ajax/referencespecifications.php',
         'class'     => __CLASS__,
      );

      $reference->dropdownAllItems($params);
      echo "</td>";

      echo "<td>" . __("Type") . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      echo "<span id='show_types_id'>";
      if ($purchaserequest->fields['itemtype']) {
         if ($purchaserequest->fields['itemtype'] == 'PluginOrderOther') {
            $file = 'other';
         } else {
            $file = $purchaserequest->fields['itemtype'];
         }
         $core_typefilename   = GLPI_ROOT . "/inc/" . strtolower($file) . "type.class.php";
         $plugin_typefilename = GLPI_ROOT . "/plugins/order/inc/" . strtolower($file) . "type.class.php";
         $itemtypeclass       = $purchaserequest->fields['itemtype'] . "Type";

         if (file_exists($core_typefilename)
             || file_exists($plugin_typefilename)
         ) {
            Dropdown::show($itemtypeclass,
                           array(
                              'name'  => "types_id",
                              'value' => $purchaserequest->fields["types_id"],
                           ));

         }
      }
      echo "</span>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __("Due date", "order") . "</td>";
      echo "<td>";
      Html::showDateFormItem("due_date", $purchaserequest->fields["due_date"], true, true);
      echo "</td>";

      echo "<td>" . __("To be validated by", "order") . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      User::dropdown(array('name'   => "users_id_validate",
                           'value'  => $purchaserequest->fields["users_id"],
                           'entity' => $purchaserequest->fields["entities_id"],
                           'right'  => 'plugin_order_purchaserequest'));
      echo "</td>";
      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='6'>";
      echo "<input type='submit' name='add_tickets' class='submit' value='"._sx('button', 'Add')."' >";
      echo "<input type='hidden' name='tickets_id' value='".$tickets_id."' >";
      echo "<input type='hidden' name='users_id_creator' value='".$_SESSION['glpiID']."'>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
   }

   /**
    * listItems
    *
    * @param array $data
    * @param bool $canedit
    * @param int $start
    */
   private function listItems($data, $canedit, $start, $rows){

      $rand = mt_rand();

      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand);
         Html::showMassiveActions($massiveactionparams);
      }

      Html::printAjaxPager(PluginOrderPurchaseRequest::getTypeName(2), $start, $rows);
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Requester')."</th>";
      echo "<th>".__('Requester group')."</th>";
      echo "<th>".__('Item type')."</th>";
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Due date')."</th>";
      echo "<th>".__('Approver')."</th>";
      echo "<th>".__('Status')."</th>";
      echo "<th>".PluginOrderOrder::getTypeName()."</th>";
      echo "</tr>";

      foreach($data as $field){
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         // Name
         $purchase_request = new PluginOrderPurchaseRequest();
         $purchase_request->getFromDB($field['id']);
         echo "<td>".$purchase_request->getLink()."</td>";
         // requester
         echo "<td>".getUserName($field['users_id'])."</td>";
         // requester group
         echo "<td>".Dropdown::getDropdownName('glpi_groups', $field['groups_id'])."</td>";
         // item type
         $item     = new $field["itemtype"]();
         echo "<td>". $item->getTypeName()."</td>";
         // Model name
         $itemtypeclass       = $field['itemtype'] . "Type";
         echo "<td>". Dropdown::getDropdownName(getTableForItemType($itemtypeclass), $field["types_id"])."</td>";
          //due date
         echo "<td>".Html::convDate($field['due_date'])."</td>";
         // validation
         echo "<td>".getUserName($field['users_id_validate'])."</td>";
         //status
         echo "<td>".CommonITILValidation::getStatus($field['status'])."</td>";
         //link with order
         $order = new PluginOrderOrder();
         $order->getFromDB($field['plugin_order_orders_id']);
         echo "<td>".$order->getLink()."</td>";
         echo "</tr>";
      }

      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</table>";
      echo "</div>";
   }

   /**
    * @param int   $tickets_id
    * @param array $options
    *
    * @return \all
    */
   function getItems($tickets_id = 0, $options = array()) {
      global $DB;

      $params['start']    = 0;
      $params['limit']    = $_SESSION['glpilist_limit'];
      $params['addLimit'] = true;

      if (!empty($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $output = array();

      $query = "SELECT *
          FROM ".$this->getTable()."
          WHERE `".$this->getTable()."`.`tickets_id` = $tickets_id";

            if ($params['addLimit']) {
               $query .= " LIMIT " . intval($params['start']) . "," . intval($params['limit']);
            }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Display list of purchase request linked to the order
    *
    * @param $item
    */
   static function showForOrder($item) {
      global $CFG_GLPI;

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }

      $purchase_request = new PluginOrderPurchaseRequest();
      $data = $purchase_request->find("`plugin_order_orders_id` = ".$item->fields['id']);

      $rows = count($data);

      if(!$rows) {
         echo __('No item to display');
      } else {
         $canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));
         $rand    = mt_rand();

         echo "<div class='center'>";

         Html::printAjaxPager(PluginOrderPurchaseRequest::getTypeName(2), $start, $rows);
         echo "<form method='post' name='purchaseresquet_form$rand' id='purchaseresquet_form$rand'  " .
              "action='" . Toolbox::getItemTypeFormURL('PluginOrderPurchaseRequest') . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th></th>";
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Requester') . "</th>";
         echo "<th>" . __('Requester group') . "</th>";
         echo "<th>" . __('Item type') . "</th>";
         echo "<th>" . __('Type') . "</th>";
         echo "<th>" . __('Due date') . "</th>";
         echo "<th>" . __('Approver') . "</th>";
         echo "<th>" . __('Status') . "</th>";
         echo "<th>" . PluginOrderOrder::getTypeName() . "</th>";
         echo "</tr>";

         foreach ($data as $field) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               $sel = "";
               if (isset($_GET["select"]) && $_GET["select"] == "all") {
                  $sel = "checked";
               }
               echo "<input type='checkbox' name='item[" . $field["id"] . "]' value='1' $sel>";
               echo "<input type='hidden' name='plugin_order_orders_id' value='" .
                    $item->getID() . "'>";
               echo "</td>";
            }
            // Name
            $purchase_request = new PluginOrderPurchaseRequest();
            $purchase_request->getFromDB($field['id']);
            echo "<td>" . $purchase_request->getLink() . "</td>";
            // requester
            echo "<td>" . getUserName($field['users_id']) . "</td>";
            // requester group
            echo "<td>" . Dropdown::getDropdownName('glpi_groups', $field['groups_id']) . "</td>";
            // item type
            $item = new $field["itemtype"]();
            echo "<td>" . $item->getTypeName() . "</td>";
            // Model name
            $itemtypeclass = $field['itemtype'] . "Type";
            echo "<td>" . Dropdown::getDropdownName(getTableForItemType($itemtypeclass), $field["types_id"]) . "</td>";
            //due date
            echo "<td>" . Html::convDate($field['due_date']) . "</td>";
            // validation
            echo "<td>" . getUserName($field['users_id_validate']) . "</td>";
            //status
            echo "<td>" . CommonITILValidation::getStatus($field['status']) . "</td>";
            //link with order
            $order = new PluginOrderOrder();
            $order->getFromDB($field['plugin_order_orders_id']);
            echo "<td>" . $order->getLink() . "</td>";
            echo "</tr>";
         }

         if ($canedit) {
            echo "<div class='center'>";
            echo "<table width='950px' class='tab_glpi'>";
            echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"]
                 . "/pics/arrow-left.png\" alt=''></td><td class='center'>";
            echo "<a onclick= \"if ( markCheckboxes('purchaseresquet_form$rand') ) "
                 . "return false;\" href='#'>" . __("Check all") . "</a></td>";

            echo "<td>/</td><td class='center'>";
            echo "<a onclick= \"if ( unMarkCheckboxes('purchaseresquet_form$rand') ) "
                 . "return false;\" href='#'>" . __("Uncheck all") . "</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='hidden' name='plugin_order_orders_id' value='" . $item->getID() . "'>";
            $purchase_request->dropdownPurchaseRequestItemsActions();
            echo "</td>";
            echo "</table>";
            echo "</div>";
            Html::closeForm();
         }
         echo "</table>";
         echo "</div>";
      }
   }

   public function dropdownPurchaseRequestItemsActions(){
      
      $action['delete_link'] = __("Delete link with order", "order");
      Dropdown::showFromArray('chooseAction', $action);

      echo "&nbsp;<input type='submit' name='action' class='submit' value='" . _sx('button', 'Post') . "'>";
   }

   /**
    * @param $item
    */
   static function showValidation($item) {

      $validator = ($item->fields["users_id_validate"] == Session::getLoginUserID());

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL('PluginOrderPurchaseRequest') . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>" . __('Validation') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Approval requester') . "</td>";
      echo "<td>" . getUserName($item->fields["users_id"]) . "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Approver') . "</td>";
      echo "<td>" . getUserName($item->fields["users_id_validate"]) . "</td></tr>";
      echo "</td></tr>";


      if ($validator) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Status of my validation') . "</td>";
         echo "<td>";
         CommonITILValidation::dropdownStatus("status", array('value' => $item->fields["status"]));
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Approval comments') . "</td>";
         echo "<td><textarea cols='60' rows='3' name='comment_validation'>" .
              $item->fields["comment_validation"] . "</textarea>";
         echo "</td></tr>";
         echo "<tr><th colspan='2'>";
         echo "<input type='hidden' name='id' value='".$item->fields['id']."'>";
         echo "<input type='submit' name='update_status' value='".__("Save")."' class='submit'>";
         echo "</th></tr>";
      } else {
         echo "<tr class='tab_bg_2'><td colspan='2'>&nbsp;</td></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Status of the approval request') . "</td>";
         $bgcolor = CommonITILValidation::getStatusColor($item->fields['status']);
         echo "<td><span style='background-color:" . $bgcolor . ";'>" .
              CommonITILValidation::getStatus($item->fields["status"]) . "</span></td></tr>";

         $status = array(CommonITILValidation::REFUSED, CommonITILValidation::ACCEPTED);
         if (in_array($item->fields["status"], $status)) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Approval comments') . "</td>";
            echo "<td>" . $item->fields["comment_validation"] . "</td></tr>";
         }
      }
      echo "</table></div>";
      echo "</form>";

   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'link':
            PluginOrderOrder::dropdown();
            echo "&nbsp;".
                 Html::submit(_x('button', 'Post'), array('name' => 'massiveaction'));
            return true;
      }
      return "";
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    *
    * This should be overloaded in Class
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **/
   function getSpecificMassiveActions($checkitem=NULL) {

      $actions['PluginOrderPurchaseRequest:link'] = __("Linked to an order", "order");

      return $actions;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      switch ($ma->getAction()) {
         case "link":
            $input = $ma->getInput();
            $order_id = $input['plugin_order_orders_id'];

            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  //Possible connection with an order if purchase request is validated
                  if($item->fields['status'] == CommonITILValidation::ACCEPTED) {
                     $item->update(array(
                                      "id"                     => $id,
                                      "plugin_order_orders_id" => $order_id,
                                      "update"                 => __('Update'),
                                   ));
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  }
               }
            }
            return;
            break;
      }
      return;
   }

   /**
    * Users groups dropdown list
    * 
    * @param $users_id
    */
   static function displayGroup($users_id) {
      //list of groups
      $group_users = Group_User::getUserGroups($users_id);
      $groups = array();

      foreach ($group_users as $item) {
         $groups[] = $item['id'];
      }

      if(count($groups) > 0) {
         Group::dropdown(array('condition' => "`id` IN (" . implode(",", $groups) . ")"));
      } else {
         echo __('No groups for this user', 'order');
      }
   }

   /**
    * @param \Migration $migration
    */
   public static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_purchaserequests` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `entities_id` int(11) NOT NULL DEFAULT '0',
                    `is_recursive` int(11) NOT NULL DEFAULT '0',
                    `name` varchar(255) collate utf8_unicode_ci default NULL,
                    `users_id` int(11) NOT NULL DEFAULT '0',
                    `groups_id` int(11) NOT NULL DEFAULT '0',
                    `comment` text COLLATE utf8_unicode_ci,
                    `itemtype` varchar(255) NOT NULL,
                    `types_id` int(11) NOT NULL DEFAULT '0',
                    `due_date` datetime default NULL,
                    `users_id_validate` int(11) NOT NULL DEFAULT '0',
                    `users_id_creator` int(11) NOT NULL DEFAULT '0',
                    `status` int(11) NOT NULL DEFAULT '0',
                    `comment_validation` text COLLATE utf8_unicode_ci,
                    `tickets_id` int(11) NOT NULL DEFAULT '0',
                    `plugin_order_orders_id` int(11) NOT NULL DEFAULT '0',
                    `date_mod` datetime default NULL,
                    PRIMARY KEY (`id`),
                    KEY `users_id` (`users_id`),
                    KEY `groups_id` (`groups_id`),
                    KEY `users_id_validate` (`users_id_validate`),
                    KEY `users_id_creator` (`users_id_creator`),
                    KEY `tickets_id` (`tickets_id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());

         $DB->query($query) or die ($DB->error());
      }

   }

   public static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      foreach (array("displaypreferences", "documents_items", "bookmarks", "logs") as $t) {
         $query = "DELETE FROM `glpi_$t` WHERE `itemtype` = '" . __CLASS__ . "'";
         $DB->query($query);
      }
      $DB->query("DROP TABLE IF EXISTS`" . $table . "`") or die ($DB->error());
   }

}
