<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;

class PluginOrderLink extends CommonDBChild
{
    public static $rightname         = 'plugin_order_order';

    public $dohistory                = true;

    public static $itemtype          = 'PluginOrderOrder';

    public static $items_id          = 'plugin_order_orders_id';

    public static $checkParentRights = self::DONT_CHECK_ITEM_RIGHTS;


    public static function getTypeName($nb = 0)
    {
        return __s("Generation", "order");
    }


    public static function getTable($classname = null)
    {
        return "glpi_plugin_order_orders_items";
    }

    public static function getIcon()
    {
        return 'ti ti-packages';
    }

    public static function getTypesThanCannotBeGenerated()
    {
        return [
            'CartridgeItem',
            'SoftwareLicense',
            'Contract',
            '', // Items without references show up as an empty itemtype
        ];
    }


    public function showItemGenerationForm($params)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        // Retrieve configuration for generate assets feature
        $config = PluginOrderConfig::getConfig();

        // Calculate colspan
        $colspan = 9;
        if (Session::isMultiEntitiesMode()) {
            $colspan++;
        }

        if ($config->canAddImmobilizationNumber()) {
            $colspan++;
        }

        $order = new PluginOrderOrder();
        $order->getFromDB($params["plugin_order_orders_id"]);

        $reference = new PluginOrderReference();
        $i = 0;
        $item_rows = [];
        $found = false;
        $order_web_dir = $CFG_GLPI['root_doc'] . '/plugins/order';

        foreach ($params["items"][self::class] as $key => $val) {
            $detail = new PluginOrderOrder_Item();
            $detail->getFromDB($key);
            $reference->getFromDB($detail->getField('plugin_order_references_id'));

            if (!$detail->fields["items_id"]) {
                $itemtype = $detail->getField('itemtype');
                $templateID = $reference->checkIfTemplateExistsInEntity(
                    $val,
                    $detail->getField('itemtype'),
                    $order->fields["entities_id"],
                );

                $row = [
                    'i' => $i,
                    'key' => $key,
                    'reference_name' => $reference->getField('name'),
                    'templateID' => $templateID,
                    'entity' => $order->fields["entities_id"],
                    'order_entity_id' => $order->fields["entities_id"],
                    'entity_scope' => $order->fields["is_recursive"]
                        ? getSonsOf('glpi_entities', $order->fields["entities_id"])
                        : $order->fields["entities_id"],
                    'condition' => self::getCondition($itemtype),
                    'itemtype' => $itemtype,
                ];

                if ($templateID) {
                    $item = getItemForItemtype($itemtype);
                    $item->getFromDB($templateID);

                    $row['name'] = $item->fields["name"] ?? "";
                    $row['otherserial'] = $item->fields["otherserial"] ?? "";
                    $row['states_id'] = $item->fields["states_id"] ?? "";
                    $row['locations_id'] = $item->fields["locations_id"] ?? "";
                    $row['groups_id'] = $item->fields["groups_id"] ?? "";
                    $row['immo_number'] = $item->fields["immo_number"] ?? "";
                    $row['template_name'] = $reference->getTemplateName($itemtype, $templateID);
                } else {
                    $row['name'] = false;
                    $row['otherserial'] = false;
                    $row['states_id'] = false;
                    $row['locations_id'] = false;
                    $row['groups_id'] = false;
                    $row['immo_number'] = false;
                    $row['template_name'] = "";
                }

                $item_rows[] = $row;
                $found = true;
            }

            $i++;
        }

        if (!$found) {
            return false;
        }

        // Render the template with all prepared data
        TemplateRenderer::getInstance()->display('@order/generate_item.html.twig', [
            'config' => $config,
            'colspan' => $colspan,
            'is_multi_entities_mode' => Session::isMultiEntitiesMode(),
            'active_entities' => $_SESSION['glpiactiveentities'] ?? [],
            'item_rows' => $item_rows,
            'order_web_dir' => $order_web_dir,
        ]);
        return null;
    }

    public function queryRef($ID, $table)
    {
        /** @var DBmysql $DB */
        global $DB;

        $condition_itemtype = ($table == 'glpi_plugin_order_references')
            ? ['NOT LIKE', 'PluginOrderReferenceFree']
            : ['LIKE', 'PluginOrderReferenceFree'];

        $criteria = [
            'SELECT' => [
                'glpi_plugin_order_orders_items.id AS IDD',
                'glpi_plugin_order_orders_items.plugin_order_references_id AS id',
                'ref.name',
                'ref.itemtype',
                'ref.manufacturers_id',
                'glpi_plugin_order_orders_items.price_taxfree',
                'glpi_plugin_order_orders_items.discount',
            ],
            'FROM' => 'glpi_plugin_order_orders_items',
            'INNER JOIN' => [
                $table . ' AS ref' => [
                    'ON' => [
                        'glpi_plugin_order_orders_items' => 'plugin_order_references_id',
                        'ref' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'glpi_plugin_order_orders_items.plugin_order_orders_id' => $ID,
                'glpi_plugin_order_orders_items.states_id' => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
                'glpi_plugin_order_orders_items.itemtype' => $condition_itemtype,
            ],
            'GROUPBY' => 'glpi_plugin_order_orders_items.plugin_order_references_id',
            'ORDER' => 'ref.name',
        ];

        return $DB->request($criteria);
    }


    public static function getCondition($itemtype)
    {
        switch ($itemtype) {
            case 'Computer':
                return ['is_visible_computer' => 1];
            case 'Monitor':
                return ['is_visible_monitor' => 1];
            case 'Printer':
                return ['is_visible_printer' => 1];
            case 'Phone':
                return ['is_visible_phone' => 1];
            case 'NetworkEquipment':
                return ['is_visible_networkequipment' => 1];
            case 'Peripheral':
                return ['is_visible_peripheral' => 1];
            case 'SoftwareLicense':
                return ['is_visible_softwareversion' => 1];
        }

        return null;
    }


    public function showOrderLink($plugin_order_orders_id)
    {
        /** @var DBmysql $DB */
        global $DB;

        $PluginOrderOrder      = new PluginOrderOrder();

        $PluginOrderOrder->getFromDB($plugin_order_orders_id);

        $canedit = $PluginOrderOrder->canDeliver()
                  && !$PluginOrderOrder->canUpdateOrder()
                  && !$PluginOrderOrder->isCanceled();

        $result_ref = $this->queryRef($plugin_order_orders_id, 'glpi_plugin_order_references');
        $numref     = count($result_ref);
        foreach ($result_ref as $data_ref) {
            $link = new self();
            $link->showOrderLinkItem(
                $numref,
                $data_ref,
                $canedit,
                $plugin_order_orders_id,
                $PluginOrderOrder,
                'glpi_plugin_order_references',
            );
        }

        $result_reffree = $this->queryRef($plugin_order_orders_id, 'glpi_plugin_order_referencefrees');
        $numreffree     = count($result_reffree);
        foreach ($result_reffree as $data_reffree) {
            $link = new self();
            $link->showOrderLinkItem(
                $numreffree,
                $data_reffree,
                $canedit,
                $plugin_order_orders_id,
                $PluginOrderOrder,
                'glpi_plugin_order_referencefrees',
            );
        }
    }

    public function showOrderLinkItem($numref, $data_ref, $canedit, $plugin_order_orders_id, $PluginOrderOrder, $table)
    {
        /** @var DBmysql $DB */
        global $DB;

        $PluginOrderOrder_Item = new PluginOrderOrder_Item();
        $PluginOrderReference  = new PluginOrderReference();
        $PluginOrderReception  = new PluginOrderReception();

        $plugin_order_references_id = $data_ref["id"];
        $itemtype                   = $data_ref["itemtype"];
        $canuse                     = !in_array($itemtype, ['PluginOrderOther', 'PluginOrderReferenceFree']);
        $item                       = getItemForItemtype($itemtype);
        $rand                       = mt_rand();
        $countainer_name            = 'orderlink' . $plugin_order_orders_id . "_" . $plugin_order_references_id;

        $start = (int) ($_GET['start'] ?? 0);
        $limit = (int) ($_GET['glpilist_limit'] ?? 15);

        $massiveactionparams = [
            'container'   => 'mass' . self::class . $rand,
            'itemtype'    => self::class,
            'item'        => $PluginOrderOrder,
            'extraparams' => [
                'plugin_order_orders_id'     => $plugin_order_orders_id,
                'plugin_order_references_id' => $plugin_order_references_id,
                'massive_action_fields' => [
                    'plugin_order_orders_id',
                    'plugin_order_references_id',
                ],
            ],
            'specific_actions' => $this->getSpecificMassiveActions(),
        ];

        $condition_itemtype = ($table == 'glpi_plugin_order_referencefrees')
            ? ['LIKE', 'PluginOrderReferenceFree']
            : ['NOT LIKE', 'PluginOrderReferenceFree'];

        $criteria = [
            'SELECT' => [
                'items.id AS IDD',
                'ref.id AS id',
                'ref.templates_id',
                'items.states_id',
                'items.entities_id',
                'items.delivery_date',
                'items.delivery_number',
                'ref.name',
                'ref.itemtype',
                'items.items_id',
                'items.price_taxfree',
                'items.discount',
            ],
            'FROM' => 'glpi_plugin_order_orders_items AS items',
            'INNER JOIN' => [
                $table . ' AS ref' => [
                    'ON' => [
                        'items' => 'plugin_order_references_id',
                        'ref' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'items.plugin_order_orders_id' => $plugin_order_orders_id,
                'items.plugin_order_references_id' => $plugin_order_references_id,
                'items.states_id' => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
                'items.itemtype' => $condition_itemtype,
            ],
            'ORDER' => 'ref.name',
        ];

        if ($itemtype == 'SoftwareLicense') {
            $criteria['GROUPBY'] = ['items.price_taxfree', 'items.discount'];
        }

        // Count total for pagination
        $result_count = $DB->request($criteria);
        $total_number = count($result_count);

        // Add pagination
        $criteria['START'] = $start;
        $criteria['LIMIT'] = $limit;

        $result = $DB->request($criteria);
        $num = count($result);
        $all_data = [];
        foreach ($result as $data) {
            Session::addToNavigateListItems(self::class, (int) $data['IDD']);
            $all_data[] = $data;
        }

        $columns = [];
        if ($itemtype != 'SoftwareLicense') {
            $columns['id_showed'] = __s("ID");
        } else {
            $columns['quantity'] = __s("Quantity", "order");
        }

        $columns['reference'] = __s("Reference");
        $columns['status'] = __s("Status");
        $columns['entity'] = __s("Entity");
        $columns['delivery_date'] = __s("Delivery date");
        $columns['associated_item'] = _sn("Associated item", "Associated items", 2);
        $columns['serial_number'] = __s("Serial number");

        // Prepare entries for the Twig template
        $entries = [];
        foreach ($all_data as $data) {
            $detailID = (int) $data["IDD"];
            $entry = [];
            $entry['id'] = $detailID;
            $entry['itemtype'] = self::class;

            if ($itemtype != 'SoftwareLicense') {
                $entry['id_showed'] = $detailID;
            } else {
                $entry['quantity'] = $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount(
                    $plugin_order_orders_id,
                    $plugin_order_references_id,
                    $data["price_taxfree"],
                    $data["discount"],
                );
            }

            if ($table == 'glpi_plugin_order_referencefrees') {
                $entry['reference'] = $data['name'];
            } else {
                $entry['reference'] = $PluginOrderReference->getReceptionReferenceLink($data);
            }

            $entry['status'] = $PluginOrderReception->getReceptionStatus($detailID);
            $entry['entity'] = Dropdown::getDropdownName(getTableForItemType(Entity::class), (int) $data["entities_id"]);
            $entry['delivery_date'] = Html::convDate($data["delivery_date"]);
            $entry['associated_item'] = $this->getReceptionItemName($data["items_id"], $data["itemtype"]);
            $entry['serial_number'] = $this->getItemSerialNumber((int) $data["items_id"], $data["itemtype"]);

            $entries[] = $entry;
        }

        $sort = $_GET[$countainer_name . 'sort'] ?? 'id_showed';
        if ($itemtype == 'SoftwareLicense' && $sort == 'id_showed') {
            $sort = 'quantity';
        }

        $order = $_GET[$countainer_name . 'order'] ?? 'ASC';
        $visible = $_GET[$countainer_name . 'visible'] ?? false;

        if ($entries !== [] && isset($columns[$sort])) {
            usort($entries, function ($a, $b) use ($sort, $order) {
                $val_a = $a[$sort] ?? null;
                $val_b = $b[$sort] ?? null;

                if (is_numeric($val_a) && is_numeric($val_b)) {
                    $cmp = $val_a <=> $val_b;
                } elseif (is_string($val_a) && is_string($val_b)) {
                    $val_a_clean = strip_tags($val_a);
                    $val_b_clean = strip_tags($val_b);
                    $cmp = strcasecmp($val_a_clean, $val_b_clean);
                } else {
                    $cmp = strcasecmp((string) $val_a, (string) $val_b);
                }

                return $order === 'DESC' ? -$cmp : $cmp;
            });
        }

        $reference_header_data = [
            'item_type_name' => $item->getTypeName(),
            'manufacturer_name' => Dropdown::getDropdownName("glpi_manufacturers", $data_ref["manufacturers_id"]),
            'reference_name' => ($table == 'glpi_plugin_order_referencefrees') ? $data_ref['name'] : $PluginOrderReference->getReceptionReferenceLink($data_ref),
            'item_count' => $total_number,
        ];

        $formatters = [
            'reference' => 'raw_html',
            'status' => 'raw_html',
            'associated_item' => 'raw_html',
            'serial_number' => 'raw_html',
        ];

        $massive_select_params = [
            'name' => 'nb_items_to_check_top_',
            'params' => [
                'value' => '',
                'min'   => 1,
                'max'   => ($start + $num > $total_number ? $total_number : $start + $num),
                'rand' => $rand,
            ],
        ];

        TemplateRenderer::getInstance()->display('@order/order_link_item.html.twig', [
            'classname' => self::class,
            'rand' => $rand,
            'ID' => $plugin_order_orders_id,
            'entries' => $entries,
            'columns' => $columns,
            'formatters' => $formatters,
            'showmassiveactions' => $canedit && $canuse && $num > 0,
            'massiveactionparams' => $massiveactionparams,
            'massive_select_params' => $massive_select_params,
            'datatable_id' => 'datatable_link_' . $rand,
            'numref' => $numref,
            'table_visible' => $visible,
            'hide_and_show' => true,
            'countainer_name' => $countainer_name,
            'sort' => $sort,
            'order' => $order,
            'nosort' => false,
            'nopager' => false,
            'total_count' => $total_number,
            'displayed_count' => $num,
            'start' => $start,
            'limit' => $limit,
            'reference_header_data' => $reference_header_data,
        ]);
    }

    /**
     * Returns serial number of associated item.
     *
     * @param integer $items_id
     * @param string  $itemtype
     * @return string
     */
    protected function getItemSerialNumber($items_id, $itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        if ($itemtype == 'PluginOrderOther' || $itemtype == 'PluginOrderReferenceFree') {
            return '';
        }

        $result = $DB->request([
            'FROM'   => $itemtype::getTable(),
            'WHERE'  => ['id' => $items_id],
        ]);
        $data = $result->current();
        return $data['serial'] ?? $data['otherserial'] ?? '';
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        $forbidden[] = 'purge';
        $forbidden[] = 'ObjectLock:unlock';
        return $forbidden;
    }


    public function getSpecificMassiveActions($checkitem = null)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        foreach ($actions as $action_key => $action_label) {
            // Remove HTML tags from action labels (like the transfer list action)
            $actions[$action_key] = strip_tags((string) $action_label);
        }

        $sep     = self::class . MassiveAction::CLASS_ACTION_SEPARATOR;

        $actions[$sep . 'generation']       = __s("Generate item", "order");
        $actions[$sep . 'createLink']       = __s("Link to an existing item", "order");
        $actions[$sep . 'deleteLink']       = __s("Delete item link", "order");
        $actions[$sep . 'cancelReceipt']    = __s("Cancel reception", "order");

        return $actions;
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        $link = new self();
        $reference = new PluginOrderReference();

        switch ($ma->getAction()) {
            case 'generation':
                return $link->showItemGenerationForm($ma->POST);
            case 'createLink':
                $reference->getFromDB($ma->POST["plugin_order_references_id"]);
                $reference->dropdownAllItemsByType(
                    "items_id",
                    $reference->fields["itemtype"],
                    $_SESSION["glpiactiveentities"],
                    $reference->fields["types_id"],
                    $reference->fields["models_id"],
                );
                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids,
    ) {
        /** @var DBmysql $DB */
        global $DB;

        // retrieve additional informations for each items
        $ma->POST['add_items'] = [];
        if (isset($ma->items[self::class])) {
            $additional_data_ite = $DB->request([
                'SELECT' => [
                    'glpi_plugin_order_orders_items.id',
                    'glpi_plugin_order_references.id AS plugin_order_references_id',
                    'glpi_plugin_order_references.itemtype',
                ],
                'FROM' => [
                    'glpi_plugin_order_orders_items',
                ],
                'LEFT JOIN' => [
                    'glpi_plugin_order_references' => [
                        'FKEY' => [
                            'glpi_plugin_order_orders_items' => 'plugin_order_references_id',
                            'glpi_plugin_order_references'   => 'id',
                        ],
                    ],
                ],
                'WHERE' => [
                    'glpi_plugin_order_orders_items.id' => array_keys($ma->getItems()[self::class]),
                ],
            ]);
            foreach ($additional_data_ite as $add_values) {
                $ma->POST['add_items'][$add_values['id']] = $add_values;
            }
        }

        $link = new self();
        switch ($ma->getAction()) {
            case 'generation':
                $newIDs = $link->generateNewItem($ma->POST);
                foreach ($ma->getItems()[self::class] as $key => $val) {
                    if (isset($newIDs[$key]) && $newIDs[$key]) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    }
                }

                break;

            case 'createLink':
                if (count($ids) > 1) {
                    $ma->addMessage(__s("Cannot link several items to one detail line", "order"));
                    foreach ($ids as $id) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    }

                    break;
                }

                $order_item = new PluginOrderOrder_Item();
                foreach ($ma->getItems()[self::class] as $key => $val) {
                    $order_item->getFromDB($val);
                    if ($order_item->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                        $ma->addMessage(__s("Cannot link items not delivered", "order"));
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    } else {
                        $link->createLinkWithItem(
                            $key,
                            $ma->POST["items_id"],
                            $ma->POST['add_items'][$key]['itemtype'],
                            $ma->POST['plugin_order_orders_id'],
                        );
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                    }
                }

                break;

            case 'deleteLink':
                foreach ($ma->getItems()[self::class] as $key => $val) {
                    $link->deleteLinkWithItem(
                        $key,
                        $ma->POST['add_items'][$key]['itemtype'],
                        $ma->POST['plugin_order_orders_id'],
                    );
                    $ma->itemDone($item->getType(), $val, MassiveAction::ACTION_OK);
                }

                break;

            case 'cancelReceipt':
                foreach ($ma->getItems()[self::class] as $key => $val) {
                    $order_item = new PluginOrderOrder_Item();
                    $order_item->getFromDB($val);
                    if ($order_item->fields["items_id"] != 0) {
                        $ma->addMessage(__s("Unable to cancel reception when items are already linked, please unlink them before trying again.", "order"));
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    } elseif (!$link->cancelReception($key)) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    } else {
                        $ma->itemDone($item->getType(), $val, MassiveAction::ACTION_OK);
                    }
                }

                break;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public function cancelReception($id)
    {
        $order_item = new PluginOrderOrder_Item();
        $order_item->getFromDB($id);

        return $order_item->update([
            'id'            => $id,
            'states_id'     => PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED,
            'delivery_date' => null,
        ]);
    }


    public function getReceptionItemName($items_id, $itemtype)
    {
        if ($items_id == 0) {
            return (__s("No associated item", "order"));
        } else {
            switch ($itemtype) {
                case 'ConsumableItem':
                case 'CartridgeItem':
                    $table = $itemtype::getTable();
                    $item = $itemtype == 'ConsumableItem' ? new Consumable() : new Cartridge();
                    $item->getFromDB($items_id);
                    $item_type = getItemForItemtype($itemtype);
                    $item_type->getFromDB($item->fields[getForeignKeyFieldForTable($table)]);
                    return $item_type->getLink(['comments' => 1]);
                default:
                    $item = getItemForItemtype($itemtype);
                    $item->getFromDB($items_id);
                    return $item->getLink(['comments' => 1]);
            }
        }
    }


    public function itemAlreadyLinkedToAnOrder(
        $itemtype,
        $items_id,
        $plugin_order_orders_id,
        $detailID = 0,
    ) {
        if (!in_array($itemtype, self::getTypesThanCannotBeGenerated())) {
            $cpt = countElementsInTable(
                'glpi_plugin_order_orders_items',
                [
                    'plugin_order_orders_id' => $plugin_order_orders_id,
                    'items_id'               => $items_id,
                    'itemtype'               => $itemtype,
                ],
            );

            return ($cpt > 0);
        } else {
            $detail = new PluginOrderOrder_Item();
            $detail->getFromDB($detailID);

            if (!$detail->fields['items_id']) {
                return false;
            } else {
                return true;
            }
        }
    }


    public function isItemLinkedToOrder($itemtype, $items_id)
    {
        /** @var DBmysql $DB */
        global $DB;

        $criteria = [
            'SELECT' => 'id',
            'FROM' => 'glpi_plugin_order_orders_items',
            'WHERE' => [
                'itemtype' => $itemtype,
                'items_id' => $items_id,
            ],
        ];
        $result = $DB->request($criteria);

        if (count($result) > 0) {
            $row = $result->current();
            return $row['id'];
        } else {
            return 0;
        }
    }


    public function generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id, $templateID = 0)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        //Do not try to generate infocoms if itemtype doesn't support it (ie contracts...)
        if (in_array($itemtype, $CFG_GLPI["infocom_types"])) {
            // Retrieve configuration for generate assets feature
            $config = PluginOrderConfig::getConfig();

            $fields = [];

            //Create empty infocom, in order to forward entities_id and is_recursive
            $ic = new Infocom();
            $infocomID = $ic->getFromDBforDevice($itemtype, $items_id) ? $ic->fields["id"] : false;

            $detail = new PluginOrderOrder_Item();
            $detail->getFromDB($detailID);

            $order  = new PluginOrderOrder();
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);

            $order_supplier = new PluginOrderOrder_Supplier();
            $order_supplier->getFromDBByOrder($detail->fields["plugin_order_orders_id"]);

            if ($templateID && $ic->getFromDBforDevice($itemtype, $templateID)) {
                $fields = $ic->fields;
                unset($fields["id"]);
                if (isset($fields["immo_number"])) {
                    $fields["immo_number"] = autoName(
                        $fields["immo_number"],
                        "immo_number",
                        true,
                        'Infocom',
                        $entity,
                    );
                }

                if (empty($fields['buy_date'])) {
                    unset($fields['buy_date']);
                }
            }

            $fields["entities_id"]     = $entity;
            $fields["itemtype"]        = $itemtype;
            $fields["items_id"]        = $items_id;
            $fields["order_number"]    = $order->fields["num_order"];
            $fields["delivery_number"] = $detail->fields["delivery_number"];
            $fields["budgets_id"]      = $order->fields["budgets_id"];
            $fields["suppliers_id"]    = $order->fields["suppliers_id"];
            $fields["value"]           = $detail->fields["price_discounted"];
            $fields["order_date"]      = $order->fields["order_date"];
            $fields["buy_date"]        = $order->fields["order_date"];

            if (!isset($fields["immo_number"]) && isset($detail->fields["immo_number"])) {
                $fields["immo_number"] = $detail->fields["immo_number"];
            }

            if (!is_null($detail->fields["delivery_date"])) {
                $fields["delivery_date"] = $detail->fields["delivery_date"];
            }

            // Get bill data
            if ($config->canAddBillDetails()) {
                $bill = new PluginOrderBill();
                if ($bill->getFromDB($detail->fields["plugin_order_bills_id"])) {
                    $fields['bill']          = $bill->fields['number'];
                    $fields['warranty_date'] = $bill->fields['billdate'];
                }
            }

            foreach (['warranty_date', 'buy_date', 'inventory_date'] as $date) {
                if (!isset($fields[$date])) {
                    $fields[$date] = 'NULL';
                }
            }

            $fields['_no_warning'] = true;

            if ($infocomID) {
                $fields['id'] = $infocomID;
                $ic->update($fields);
            } else {
                $ic->add($fields);
            }
        }
    }


    public function removeInfoComRelatedToOrder($itemtype, $items_id)
    {
        $infocom = new Infocom();
        $infocom->getFromDBforDevice($itemtype, $items_id);
        $infocom->update([
            "id"              => $infocom->fields["id"],
            "order_number"    => "",
            "delivery_number" => "",
            "budgets_id"      => 0,
            "suppliers_id"    => 0,
            "bill"            => "",
            "value"           => 0,
            "order_date"      => null,
            "delivery_date"   => null,
        ]);
    }


    public function createLinkWithItem(
        $detailID = 0,
        $items_id = 0,
        $itemtype = 0,
        $plugin_order_orders_id = 0,
        $entity = 0,
        $templateID = 0,
        $history = true,
        $check_link = true,
    ) {
        /** @var DBmysql $DB */
        global $DB;

        if (
            !$check_link
            || !$this->itemAlreadyLinkedToAnOrder(
                $itemtype,
                $items_id,
                $plugin_order_orders_id,
                $detailID,
            )
        ) {
            $detail     = new PluginOrderOrder_Item();
            $restricted = ['ConsumableItem', 'CartridgeItem'];

            if ($itemtype == 'SoftwareLicense') {
                $detail->getFromDB($detailID);
                $criteria = [
                    'SELECT' => 'id',
                    'FROM' => 'glpi_plugin_order_orders_items',
                    'WHERE' => [
                        'plugin_order_orders_id' => $plugin_order_orders_id,
                        'plugin_order_references_id' => $detail->fields["plugin_order_references_id"],
                        'price_taxfree' => ['LIKE', $detail->fields["price_taxfree"]],
                        'discount' => ['LIKE', $detail->fields["discount"]],
                        'states_id' => 1,
                    ],
                ];
                $result = $DB->request($criteria);
                $nb     = count($result);

                if ($nb !== 0) {
                    $lic = new SoftwareLicense();
                    foreach ($result as $row) {
                        $ID                = $row['id'];
                        $input["id"]       = $ID;
                        $input["items_id"] = $items_id;
                        $detail->update($input);

                        $this->generateInfoComRelatedToOrder($entity, $ID, $itemtype, $items_id, 0);

                        $lic              = new SoftwareLicense();
                        $lic->getFromDB($items_id);
                        $values["id"]     = $lic->fields["id"];
                        $values["number"] = $lic->fields["number"] + 1;
                        $lic->update($values);
                    }

                    if ($history) {
                        $order     = new PluginOrderOrder();
                        $new_value = __s("Item linked to order", "order") . ' : ' . $lic->getField("name");
                        $order->addHistory('PluginOrderOrder', '', $new_value, $plugin_order_orders_id);
                    }
                }
            } elseif (in_array($itemtype, $restricted)) {
                if ($itemtype == 'ConsumableItem') {
                    $item = new Consumable();
                    $type = 'Consumable';
                    $pkey = 'consumableitems_id';
                } elseif ($itemtype == 'CartridgeItem') {
                    $item = new Cartridge();
                    $type = 'Cartridge';
                    $pkey = 'cartridgeitems_id';
                } else {
                    return false;
                }

                $detail->getFromDB($detailID);
                $input[$pkey]     = $items_id;
                $input["date_in"] = $detail->fields["delivery_date"];
                $newID            = $item->add($input);

                $input["id"]       = $detailID;
                $input["items_id"] = $newID;
                $input["itemtype"] = $itemtype;
                if ($detail->update($input)) {
                    $this->generateInfoComRelatedToOrder($entity, $detailID, $type, $newID, 0);
                }
            } elseif ($itemtype == 'Contract') {
                $input = [
                    "id"       => $detailID,
                    "items_id" => $items_id,
                    "itemtype" => $itemtype,
                ];
                if ($detail->update($input)) {
                    $detail->getFromDB($detailID);
                    $item = new Contract();

                    if (
                        $item->update(['id'   => $items_id,
                            'cost' => $detail->fields["price_discounted"],
                        ])
                    ) {
                        $order = new PluginOrderOrder();
                        $order->getFromDB($plugin_order_orders_id);
                        if (
                            !countElementsInTable(
                                'glpi_contracts_suppliers',
                                ['contracts_id' => $items_id, 'suppliers_id' => $order->fields['suppliers_id']],
                            )
                        ) {
                            $contract_supplier = new Contract_Supplier();
                            $contract_supplier->add([
                                'contracts_id' => $items_id,
                                'suppliers_id'  => $order->fields['suppliers_id'],
                            ]);
                        }
                    }
                }
            } else {
                $input = [
                    "id"       => $detailID,
                    "items_id" => $items_id,
                    "itemtype" => $itemtype,
                ];
                if ($detail->update($input)) {
                    $this->generateInfoComRelatedToOrder(
                        $entity,
                        $detailID,
                        $itemtype,
                        $items_id,
                        $templateID,
                    );

                    if ($history) {
                        $order = new PluginOrderOrder();
                        $order->getFromDB($detail->fields["plugin_order_orders_id"]);

                        $item  = getItemForItemtype($itemtype);
                        $item->getFromDB($items_id);

                        $new_value = __s("Item linked to order", "order") . ' : ' . $item->getField("name");
                        $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
                    }
                }
            }

            if ($history) {
                $order = new PluginOrderOrder();
                $order->getFromDB($detail->fields["plugin_order_orders_id"]);
                $new_value = __s("Item linked to order", "order") . ' : ' . $order->fields["name"];
                $order->addHistory($itemtype, '', $new_value, $items_id);
            }

            Session::addMessageAfterRedirect(__s("Item linked to order", "order"), true);
        } else {
            Session::addMessageAfterRedirect(__s("Item already linked to another one", "order"), true, ERROR);
        }

        return null;
    }


    public function deleteLinkWithItem($detailID, $itemtype, $plugin_order_orders_id)
    {
        /** @var DBmysql $DB */
        global $DB;

        if ($itemtype == 'SoftWareLicense') {
            $detail  = new PluginOrderOrder_Item();
            $detail->getFromDB($detailID);
            $license = $detail->fields["items_id"];

            $this->removeInfoComRelatedToOrder($itemtype, $license);
            $result = $detail->queryRef(
                $detail->fields["plugin_order_orders_id"],
                $detail->fields["plugin_order_references_id"],
                $detail->fields["price_taxfree"],
                $detail->fields["discount"],
                PluginOrderOrder::ORDER_DEVICE_DELIVRED,
            );
            if (($nb = count($result)) !== 0) {
                foreach ($result as $row) {
                    $ID = $row['id'];
                    $detail->update([
                        "id"       => $ID,
                        "items_id" => 0,
                    ]);

                    $lic = new SoftwareLicense();
                    $lic->getFromDB($license);
                    $values["id"]     = $lic->fields["id"];
                    $values["number"] = $lic->fields["number"] - 1;
                    $lic->update($values);
                }

                $order = new PluginOrderOrder();
                $order->getFromDB($detail->fields["plugin_order_orders_id"]);
                $new_value = __s("Item unlink form order", "order") . ' : ' . $order->fields["name"];
                $order->addHistory($itemtype, '', $new_value, $license);

                $item = getItemForItemtype($itemtype);
                $item->getFromDB($license);
                $new_value = __s("Item unlink form order", "order") . ' : ' . $item->getField("name");
                $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
            }
        } else {
            $order = new PluginOrderOrder();
            $order->getFromDB($plugin_order_orders_id);

            $detail = new PluginOrderOrder_Item();
            $detail->getFromDB($detailID);
            $items_id = $detail->fields["items_id"];

            $this->removeInfoComRelatedToOrder($itemtype, $items_id);

            if ($items_id != 0) {
                $input = $detail->fields;
                $input["items_id"] = 0;
                $detail->update($input);
            } else {
                Session::addMessageAfterRedirect(__s("One or several selected rows haven't linked items", "order"), true, ERROR);
            }

            $new_value = __s("Item unlink form order", "order") . ' : ' . $order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $items_id);

            $item = getItemForItemtype($itemtype);
            $item->getFromDB($items_id);
            $new_value = __s("Item unlink form order", "order") . ' : ' . $item->getField("name");
            $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
        }
    }


    public function generateNewItem($params)
    {
        $newIDs = [];

        // Retrieve plugin configuration
        $config    = new PluginOrderConfig();
        $reference = new PluginOrderReference();

        foreach ($params["id"] as $key => $values) {
            $add_item = $values;
            if (
                array_key_exists('add_items', $params)
                && array_key_exists($values['id'], $params['add_items'])
            ) {
                $add_item = array_merge($params['add_items'][$values['id']], $add_item);
            }

            //retrieve plugin_order_references_id from param if needed
            if (!isset($add_item["plugin_order_references_id"])) {
                $add_item["plugin_order_references_id"] = $params['plugin_order_references_id'];
            }

            //If itemtype cannot be generated, go to the new occurence
            if (in_array($add_item['itemtype'], self::getTypesThanCannotBeGenerated())) {
                continue;
            }

            $entity = $values["entities_id"];
            //------------- Template management -----------------------//
            //Look for a template in the entity
            $templateID = $reference->checkIfTemplateExistsInEntity(
                $values["id"],
                $add_item["itemtype"],
                $entity,
            );

            $item  = getItemForItemtype($add_item["itemtype"]);
            if ($add_item['itemtype']) {
                $order = new PluginOrderOrder();
            } else {
                return false;
            }

            $order->getFromDB($params["plugin_order_orders_id"]);
            $reference->getFromDB($add_item["plugin_order_references_id"]);

            //Update immo_number in details to fill Infocom later
            if ($config->canAddImmobilizationNumber()) {
                $detail = new PluginOrderOrder_Item();
                $detail->update(['id' => $add_item["id"], 'immo_number' => $values["immo_number"]]);
            }

            if ($templateID) {
                $item->getFromDB($templateID);
                unset($item->fields["is_template"]);
                unset($item->fields["date_mod"]);

                $input  = [];
                foreach ($item->fields as $key => $value) {
                    if ($value != '') {
                        $input[$key] = $value;
                    }
                }

                if (isset($values["states_id"]) && $values["states_id"] != 0) {
                    $input['states_id'] = $values['states_id'];
                } elseif ($config->getGeneratedAssetState()) {
                    $input["states_id"] = $config->getGeneratedAssetState();
                }

                $input['groups_id'] = $values['groups_id'];
                if (isset($values["locations_id"]) && $values["locations_id"] != 0) {
                    $input['locations_id'] = $values['locations_id'];
                } elseif ($config->canAddLocation()) {
                    // Get bill data
                    $input['locations_id'] = $order->fields['locations_id'];
                }

                $input["entities_id"] = $entity;
                $input["serial"]      = $values["serial"];

                if (isset($item->fields['name']) && $item->fields['name']) {
                    $input["name"] = autoName(
                        $item->fields["name"],
                        "name",
                        $templateID,
                        $add_item["itemtype"],
                        $entity,
                    );
                } else {
                    $input["name"] = $values["name"];
                }

                if (isset($item->fields['otherserial']) && $item->fields['otherserial']) {
                    $input["otherserial"] = autoName(
                        $item->fields["otherserial"],
                        "otherserial",
                        $templateID,
                        $add_item["itemtype"],
                        $entity,
                    );
                } else {
                    $input["otherserial"] = $values["otherserial"];
                }
            } elseif ($add_item["itemtype"] == 'Contract') {
                $input["name"]             = $values["name"];
                $input["entities_id"]      = $entity;
                $input['contracttypes_id'] = $reference->fields['types_id'];
            } else {
                if (isset($values["states_id"]) && $values["states_id"] != 0) {
                    $input['states_id']     = $values['states_id'];
                } elseif ($config->getGeneratedAssetState()) {
                    $input["states_id"]  = $config->getGeneratedAssetState();
                } else {
                    $input["states_id"]  = 0;
                }

                $input['groups_id']        = $values['groups_id'];
                if (isset($values["locations_id"]) && $values["locations_id"] != 0) {
                    $input['locations_id'] = $values["locations_id"];
                } elseif ($config->canAddLocation()) {
                    // Get bill data
                    $input['locations_id'] = $order->fields['locations_id'];
                }

                $input["entities_id"]      = $entity;
                $input["serial"]           = $values["serial"];
                $input["otherserial"]      = $values["otherserial"];
                $input["name"]             = $values["name"];
            }

            if (!array_key_exists('manufacturers_id', $input) || $input["manufacturers_id"] == 0) {
                $input["manufacturers_id"] = $reference->fields["manufacturers_id"];
            }

            $typefield = getForeignKeyFieldForTable(getTableForItemType($add_item["itemtype"] . "Type"));
            if (!array_key_exists($typefield, $input) || $input[$typefield] == 0) {
                $input[$typefield] = $reference->fields["types_id"];
            }

            $modelfield = getForeignKeyFieldForTable(getTableForItemType($add_item["itemtype"] . "Model"));
            if (!array_key_exists($modelfield, $input) || $input[$modelfield] == 0) {
                $input[$modelfield] = $reference->fields["models_id"];
            }

            $newID = $item->add($input);
            $newIDs[$values["id"]] = $newID;

            // Attach new ticket if option is on
            if (isset($params['generate_ticket'])) {
                $tkt = new TicketTemplate();
                if ($tkt->getFromDB($params['generate_ticket']['tickettemplates_id'])) {
                    $input = Ticket::getDefaultValues($entity);
                    $ttp        = new TicketTemplatePredefinedField();
                    $predefined = $ttp->getPredefinedFields($params['generate_ticket']['tickettemplates_id'], true);
                    if (count($predefined)) {
                        foreach ($predefined as $predeffield => $predefvalue) {
                            $input[$predeffield] = $predefvalue;
                        }
                    }

                    $input['entities_id']         = $entity;
                    $input['_users_id_requester'] = empty($order->fields['users_id']) ? Session::getLoginUserID() : $order->fields['users_id'];
                    $input['items_id']            = $newID;
                    $input['itemtype']            = $add_item["itemtype"];

                    $ticket = new Ticket();
                    $ticket->add($input);
                }
            }

            //-------------- End template management ---------------------------------//
            $this->createLinkWithItem(
                $values["id"],
                $newID,
                $add_item["itemtype"],
                $params["plugin_order_orders_id"],
                $entity,
                $templateID,
                false,
                false,
            );

            //Add item's history
            $new_value = __s("Item generated by using order", "order") . ' : ' . $order->fields["name"];
            $order->addHistory($add_item["itemtype"], '', $new_value, $newID);

            //Add order's history
            $new_value  = __s("Item generated by using order", "order") . ' : ';
            $new_value .= $item->getTypeName() . " -> " . $item->getField("name");
            $order->addHistory('PluginOrderOrder', '', $new_value, $params["plugin_order_orders_id"]);

            //Copy order documents if needed
            self::copyDocuments($add_item['itemtype'], $newID, $params["plugin_order_orders_id"], $entity);

            Session::addMessageAfterRedirect(__s("Item successfully selected", "order"), true);
        }

        return $newIDs;
    }


    public static function countForOrder(PluginOrderOrder $item)
    {
        return countElementsInTable(
            'glpi_plugin_order_orders_items',
            [
                'plugin_order_orders_id' => $item->getID(),
                'states_id' => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
            ],
        );
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof PluginOrderOrder
            && $item->checkIfDetailExists($item->getID(), true)
            && Session::haveRight('plugin_order_order', READ)
        ) {
            return self::createTabEntry(
                _sn("Associated item", "Associated items", 2),
                self::countForOrder($item),
                null,
                self::getIcon(),
            );
        }

        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof PluginOrderOrder) {
            $link = new self();
            $link->showOrderLink($item->getID());
        }

        return true;
    }


    /**
     * Copy order documents into the newly generated item
     * @since 1.5.3
     * @param $itemtype
     * @param $items_id
     * @param $orders_id
     * @param $entity
     */
    public static function copyDocuments($itemtype, $items_id, $orders_id, $entity)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $config = PluginOrderConfig::getConfig();

        if ($config->canCopyDocuments() && in_array($itemtype, $CFG_GLPI["document_types"])) {
            $document = new Document();
            $docitem  = new Document_Item();

            $item = getItemForItemtype($itemtype);
            $item->getFromDB($items_id);
            $is_recursive = 0;

            foreach (
                getAllDataFromTable(
                    'glpi_documents_items',
                    ['itemtype' => 'PluginOrderOrder',
                        'items_id' => $orders_id,
                    ],
                ) as $doc
            ) {
                //Create a new document
                $document->getFromDB($doc['documents_id']);
                if (
                    ($document->getEntityID() != $entity && !$document->fields['is_recursive'])
                    || !in_array($entity, getSonsOf('glpi_entities', $document->getEntityID()))
                ) {
                    $found_docs = getAllDataFromTable(
                        'glpi_documents',
                        [
                            'entities_id' => $entity,
                            'sha1sum' => $document->fields['sha1sum'],
                        ],
                    );
                    if (empty($found_docs)) {
                        $tmpdoc                = $document->fields;
                        $tmpdoc['entities_id'] = $entity;
                        unset($tmpdoc['id']);
                        $documents_id = $document->add($tmpdoc);
                        $is_recursive = $document->fields['is_recursive'];
                    } else {
                        $found_doc = array_pop($found_docs);
                        $documents_id = $found_doc['id'];
                        $is_recursive = $found_doc['is_recursive'];
                    }
                } else {
                    $documents_id = $document->getID();
                    $is_recursive = $document->fields['is_recursive'];
                }

                //Link the document to the newly generated item
                $docitem->add([
                    'documents_id' => $documents_id,
                    'entities_id'  => $entity,
                    'items_id'     => $items_id,
                    'itemtype'     => $itemtype,
                    'is_recursive' => $is_recursive,
                ]);
            }
        }
    }
}
