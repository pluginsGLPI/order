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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginOrderReception extends CommonDBChild
{
    public static $rightname          = 'plugin_order_order';

    public $dohistory                 = true;

    public static $itemtype           = 'PluginOrderOrder';

    public static $items_id           = 'plugin_order_orders_id';

    public static $checkParentRights  = self::DONT_CHECK_ITEM_RIGHTS;


    public static function getTable($classname = null)
    {
        return "glpi_plugin_order_orders_items";
    }


    public static function getTypeName($nb = 0)
    {
        return __("Delivery", "order");
    }


    public function canUpdateItem()
    {
        return Session::haveRight('plugin_order_order', PluginOrderOrder::RIGHT_DELIVERY);
    }


    public function canViewItem()
    {
        return Session::haveRight('plugin_order_order', PluginOrderOrder::RIGHT_DELIVERY)
         && Session::haveRight('plugin_order_order', READ);
    }


    public function getOrdersID()
    {
        return $this->fields["plugin_order_orders_id"];
    }


    public function getFromDBByOrder($plugin_order_orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $query = [
            'FROM' => self::getTable(),
            'WHERE' => ['plugin_order_orders_id' => $plugin_order_orders_id],
        ];
        $result = $DB->request($query);

        foreach ($result as $fields) {
            $this->fields = $fields;
            if (is_array($this->fields) && count($this->fields)) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }


    public function checkThisItemStatus($detailID, $states_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $query = "SELECT `states_id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `id` = '$detailID' ";
        $result = $DB->query($query);
        if ($DB->result($result, 0, "states_id") == $states_id) {
            return true;
        } else {
            return false;
        }
    }


    public function checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, $states_id)
    {
        return countElementsInTable(
            "glpi_plugin_order_orders_items",
            [
                'plugin_order_orders_id' => $plugin_order_orders_id,
                'plugin_order_references_id' => $plugin_order_references_id,
                'states_id' => $states_id,
            ]
        );
    }


    public function deleteDelivery($detailID)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $detail = new PluginOrderOrder_Item();
        $detail->getFromDB($detailID);

        if ($detail->fields["itemtype"] == 'SoftwareLicense') {
            $iterator = $detail->queryRef(
                $_POST["plugin_order_orders_id"],
                $detail->fields["plugin_order_references_id"],
                $detail->fields["price_taxfree"],
                $detail->fields["discount"],
                PluginOrderOrder::ORDER_DEVICE_DELIVRED
            );
            $nb = count($iterator);

            if ($nb) {
                foreach ($iterator as $data) {
                    $detailID = $data['id'];
                    $detail->update([
                        "id"                             => $detailID,
                        "delivery_date"                  => 'NULL',
                        "states_id"                      => PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED,
                        "delivery_number"                => "",
                        "plugin_order_deliverystates_id" => 0,
                        "delivery_comment"               => "",
                    ]);
                }
            }
        } else {
            $detail->update([
                "id"                             => $detailID,
                "date"                           => 0,
                "states_id"                      => PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED,
                "delivery_number"                => "",
                "plugin_order_deliverystates_id" => 0,
                "delivery_comment"               => "",
            ]);
        }
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        $order_order = new PluginOrderOrder();
        $order_order->getFromDB($this->getOrdersID());

        $order_reference = new PluginOrderReference();
        $order_reference->getFromDB($this->fields["plugin_order_references_id"]);

        $canedit = $order_order->can($this->getOrdersID(), UPDATE)
                 && !$order_order->canUpdateOrder()
                 && !$order_order->isCanceled();

        echo Html::hidden('plugin_order_orders_id', ['value' => $this->getOrdersID()]);

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __("Reference") . ": </td>";
        echo "<td>";
        if ($this->fields['itemtype'] == 'PluginOrderReferenceFree') {
            echo $order_reference->fields["name"];
        } else {
            $data         = [];
            $data["id"]   = $this->fields["plugin_order_references_id"];
            $data["name"] = $order_reference->fields["name"];
            echo $order_reference->getReceptionReferenceLink($data);
        }
        echo "</td>";

        echo "<td>" . __("Taken delivery", "order") . "</td>";
        echo "<td>";
        Dropdown::showYesNo('states_id', $this->fields['states_id']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __("Delivery form") . ": </td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input(
                'delivery_number',
                [
                    'value' => $this->fields['delivery_number'],
                ]
            );
        } else {
            echo $this->fields["delivery_number"];
        }
        echo "</td>";

        echo "<td>" . __("Delivery date") . ": </td>";
        echo "<td>";
        if ($canedit) {
            Html::showDateField("delivery_date", [
                'value'      => $this->fields["delivery_date"],
                'maybeempty' => true,
                'canedit'    => true
            ]);
        } else {
            echo Html::convDate($this->fields["delivery_date"]);
        }
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __("Delivery status", "order") . ": </td>";
        echo "<td>";
        if ($canedit) {
            PluginOrderDeliveryState::Dropdown([
                'name'  => "plugin_order_deliverystates_id",
                'value' => $this->fields["plugin_order_deliverystates_id"]
            ]);
        } else {
            echo Dropdown::getDropdownName(
                "glpi_plugin_order_deliverystates",
                $this->fields["plugin_order_deliverystates_id"]
            );
        }
        echo "</td>";

        echo "<td>" . __("Bill", "order") . "</td>";
        echo "<td>";
        if (Session::haveRight("plugin_order_bill", UPDATE)) {
            PluginOrderBill::Dropdown([
                'name'  => "plugin_order_bills_id",
                'value' => $this->fields["plugin_order_bills_id"]
            ]);
        } else if (Session::haveRight("plugin_order_bill", READ)) {
            echo Dropdown::getDropdownName(
                "glpi_plugin_order_bills",
                $this->fields["plugin_order_bills_id"]
            );
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>";
       //comments of order
        echo __("Comments") . ": </td>";
        echo "<td colspan='3'>";
        if ($canedit) {
            echo "<textarea cols='100' rows='4' name='delivery_comment'>" .
             $this->fields["delivery_comment"] . "</textarea>";
        } else {
            echo $this->fields["delivery_comment"];
        }
        echo "</td>";
        echo "</tr>";
        $options['candel'] = false;
        $this->showFormButtons($options);

        return true;
    }


    public function showOrderReception($orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $order_order = new PluginOrderOrder();
        $order_item  = new PluginOrderOrder_Item();
        $reference   = new PluginOrderReference();
        $order_order->getFromDB($orders_id);

        Session::initNavigateListItems(
            $this->getType(),
            __("Order", "order") . " = " . $order_order->fields["name"]
        );

        $canedit = self::canCreate()
                 && !$order_order->canUpdateOrder()
                 && !$order_order->isCanceled();

        $result_ref = $order_item->queryDetail($orders_id, 'glpi_plugin_order_references');
        $numref     = count($result_ref);

        foreach ($result_ref as $data_ref) {
            self::showOrderReceptionItem(
                $data_ref,
                $numref,
                $canedit,
                $reference,
                $order_item,
                $orders_id,
                $order_order,
                'glpi_plugin_order_references'
            );
        }

        $result_reffree = $order_item->queryDetail($orders_id, 'glpi_plugin_order_referencefrees');
        $numreffree     = count($result_reffree);

        foreach ($result_reffree as $data_reffree) {
            self::showOrderReceptionItem(
                $data_reffree,
                $numreffree,
                $canedit,
                $reference,
                $order_item,
                $orders_id,
                $order_order,
                'glpi_plugin_order_referencefrees'
            );
        }
    }

    public function showOrderReceptionItem($data_ref, $numref, $canedit, $reference, $order_item, $orders_id, $order_order, $table)
    {
        /** @var \DBmysql $DB */
        /** @var array $CFG_GLPI */
        global $DB, $CFG_GLPI;

        echo "<table class='tab_cadre_fixe'>";

        $references_id  = $data_ref["id"];
        $typeRef        = $data_ref["itemtype"];

        if (!$numref) {
            echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";
        } else {
            $price_taxfree  = $data_ref["price_taxfree"];
            $discount       = $data_ref["discount"];
            $canmassive     = $order_order->canDeliver()
                           && $this->checkItemStatus(
                               $orders_id,
                               $references_id,
                               PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED
                           );

            $massiveactionparams = [
                'extraparams' => [
                    'massive_action_fields' => [
                        'plugin_order_orders_id',
                        'plugin_order_references_id',
                    ]
                ]
            ];

            $item = new $typeRef();
            $rand = mt_rand();
            echo "<tr><th><ul class='list-unstyled'><li>";
            echo "<a href=\"javascript:showHideDiv('reception$rand','reception_img$rand', '" .
              $CFG_GLPI['root_doc'] . "/pics/plus.png','" . $CFG_GLPI['root_doc'] . "/pics/moins.png');\">";
            echo "<img alt='' name='reception_img$rand' src=\"" . $CFG_GLPI['root_doc'] . "/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . __("Assets") . "</th>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "<th>" . __("Delivered items", "order") . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";
            echo "<td align='center'>" . Dropdown::getDropdownName(
                "glpi_manufacturers",
                $data_ref["manufacturers_id"]
            ) . "</td>";
            if ($table == 'glpi_plugin_order_referencefrees') {
                echo "<td>" . $data_ref['name'] . "</td>";
            } else {
                echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
            }
            $total = $order_item->getTotalQuantityByRefAndDiscount(
                $orders_id,
                $references_id,
                $data_ref["price_taxfree"],
                $data_ref["discount"]
            );
            echo "<td>" . $order_item->getDeliveredQuantity(
                $orders_id,
                $references_id,
                $data_ref["price_taxfree"],
                $data_ref["discount"]
            )
              . " / " . $total . "</td>";
            echo "</tr></table>";

            echo "<div id='reception$rand' style='display:none'>";

            $this->displayBulkReceptionForm(
                $order_order,
                $orders_id,
                $references_id,
                $typeRef,
                '_top',
                'top'
            );

            $criteria = [
                'SELECT' => [
                    'items.id AS IDD',
                    'ref.id AS id',
                    'ref.templates_id',
                    'items.states_id',
                    'items.entities_id',
                    'items.comment',
                    'items.plugin_order_deliverystates_id',
                    'items.delivery_date',
                    'items.delivery_number',
                    'ref.name',
                    'ref.itemtype',
                    'items.items_id'
                ],
                'FROM' => 'glpi_plugin_order_orders_items AS items',
                'INNER JOIN' => [
                    "$table AS ref" => [
                        'ON' => [
                            'items' => 'plugin_order_references_id',
                            'ref' => 'id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'items.plugin_order_orders_id' => $orders_id,
                    'items.plugin_order_references_id' => $references_id,
                    'items.discount' => ['LIKE', $discount],
                    'items.price_taxfree' => ['LIKE', $price_taxfree]
                ],
                'ORDER' => 'ref.name'
            ];

            if ($typeRef == 'SoftwareLicense') {
                $criteria['GROUPBY'] = 'ref.name';
            }

            $result = $DB->request($criteria);
            $num    = count($result);

            $all_data = [];
            foreach ($result as $data) {
                $all_data[] = $data;
            }

            if ($canmassive && $num) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams['container']   = 'mass' . __CLASS__ . $rand;
                $massiveactionparams['item']        = $order_order;
                $massiveactionparams['rand']        = $rand;
                $massiveactionparams['extraparams']['plugin_order_orders_id']     = $orders_id;
                $massiveactionparams['extraparams']['plugin_order_references_id'] = $references_id;
                Html::showMassiveActions($massiveactionparams);

                echo "<span style='margin-left: 10px;'>";
                Dropdown::showNumber(
                    'nb_items_to_check_top_',
                    [
                        'value' => '',
                        'min'   => 1,
                        'max'   => $num,
                        'rand' => $rand,
                    ]
                );
                echo "&nbsp;<button type='button' class='btn btn-secondary btn-sm' onclick='selectNItems(\"mass" . __CLASS__ . "$rand\", \"dropdown_nb_items_to_check_top_$rand\")'>" . __("Select") . "</button>";
                echo "</span>";
            }

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr>";
            if ($order_order->canDeliver()) {
                echo "<th width='15'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            }
            if ($typeRef != 'SoftwareLicense') {
                echo "<th>" . __("ID") . "</th>";
            }
            echo "<th>" . __("Reference") . "</th>";
            echo "<th>" . __("Status") . "</th>";
            echo "<th>" . __("Entity") . "</th>";
            echo "<th>" . __("Delivery date") . "</th>";
            echo "<th>" . __("Delivery form") . "</th>";
            echo "<th>" . __("Delivery status", "order") . "</th>";
            echo "</tr>";

            foreach ($all_data as $data) {
                $detailID = $data["IDD"];
                Session::addToNavigateListItems($this->getType(), (int) $detailID);
                echo "<tr class='tab_bg_2'>";
                $status    = 1;
                if ($typeRef != 'SoftwareLicense') {
                    $status = $this->checkThisItemStatus(
                        $detailID,
                        PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED
                    );
                }

                if ($order_order->canDeliver() && $status) {
                    echo "<td width='15' align='left'>";
                    Html::showMassiveActionCheckBox(__CLASS__, (int) $detailID);
                    echo "</td>";
                } else {
                    echo "<td width='15' align='left'></td>";
                }

                if ($typeRef != 'SoftwareLicense') {
                    echo "<td align='center'>" . $data["IDD"] . "&nbsp;";
                    Html::showTooltip($data['comment']);
                    echo "</td>";
                }
                if ($table == 'glpi_plugin_order_referencefrees') {
                    echo "<td align='center'>" . $data['name'] . "</td>";
                } else {
                    echo "<td align='center'>" . $reference->getReceptionReferenceLink($data) . "</td>";
                }
                echo "<td align='center'>";
                $link = Toolbox::getItemTypeFormURL($this->getType());
                if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
                    echo "<a href=\"" . $link . "?id=" . $data["IDD"] . "\">";
                }
                echo $this->getReceptionStatus($detailID);
                if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
                    echo "</a>";
                }
                echo "</td>";

                echo "<td align='center'>" . Dropdown::getDropdownName(getTableForItemType(Entity::class), (int) $data["entities_id"]) . "</td>";
                echo "<td align='center'>" . Html::convDate($data["delivery_date"]) . "</td>";
                echo "<td align='center'>" . $data["delivery_number"] . "</td>";
                echo "<td align='center'>" .
                 Dropdown::getDropdownName(
                     "glpi_plugin_order_deliverystates",
                     (int) $data["plugin_order_deliverystates_id"]
                 ) . "</td>";
                echo Html::hidden(
                    "id[$detailID]",
                    ['value' => $detailID]
                );
                echo Html::hidden(
                    "name[$detailID]",
                    ['value' => $data["name"]]
                );
                echo Html::hidden(
                    "plugin_order_references_id[$detailID]",
                    ['value' => $data["id"]]
                );
                echo Html::hidden(
                    "itemtype[$detailID]",
                    ['value' => $data["itemtype"]]
                );
                echo Html::hidden(
                    "templates_id[$detailID]",
                    ['value' => $data["templates_id"]]
                );
                echo Html::hidden(
                    "states_id[$detailID]",
                    ['value' => $data["states_id"]]
                );
            }
            echo "</table>";

            if ($canmassive) {
                if ($num > 10) {
                    $massiveactionparams['ontop'] = false;
                    Html::showMassiveActions($massiveactionparams);

                    echo "<span style='margin-left: 10px;'>";
                    Dropdown::showNumber(
                        'nb_items_to_check_bottom_',
                        [
                            'value' => '',
                            'min'   => 1,
                            'max'   => $num,
                            'rand' => $rand,
                        ]
                    );
                    echo "&nbsp;<button type='button' class='btn btn-secondary btn-sm' onclick='selectNItems(\"mass" . __CLASS__ . "$rand\", \"dropdown_nb_items_to_check_bottom_$rand\")'>" . __("Select") . "</button>";
                    echo "</span>";
                }
                Html::closeForm();

                $script = <<<JAVASCRIPT
                    function selectNItems(container_id, select_id) {
                        var n = parseInt(document.getElementById(select_id).value);
                        var checked = 0;
                        var checkboxes = document.querySelectorAll("#" + container_id + " input[type='checkbox']");

                        checkboxes.forEach(function(checkbox) {
                            if (checkbox.name && checkbox.name.indexOf('item') === 0) {
                                checkbox.checked = false;
                            }
                        });

                        checkboxes.forEach(function(checkbox) {
                            if (checkbox.name && checkbox.name.indexOf('item') === 0 && checked < n) {
                                checkbox.checked = true;
                                checked++;
                            }
                        });
                    }
JAVASCRIPT;
                echo Html::scriptBlock($script);
            }
        }

        $this->displayBulkReceptionForm(
            $order_order,
            $orders_id,
            $references_id,
            $typeRef
        );

        echo "</div>";
        echo "<br>";
    }

    /**
     * Display bulk reception form button
     *
     * @param PluginOrderOrder $order_order   Order object
     * @param int              $orders_id     Order ID
     * @param int              $references_id Reference ID
     * @param string           $typeRef       Reference type
     * @param string           $suffix        Suffix for unique IDs
     * @param string           $position      Position the button
     *
     * @return void
     */
    private function displayBulkReceptionForm($order_order, $orders_id, $references_id, $typeRef, $suffix = '', $position = 'bottom')
    {
        if (
            $order_order->canDeliver()
            && $this->checkItemStatus(
                $orders_id,
                $references_id,
                PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED
            )
        ) {
            if ($typeRef != 'SoftwareLicense') {
                $bulk_rand = mt_rand();

                $button = "<p><a href='javascript:viewmassreception$suffix" . $orders_id . "$bulk_rand();'>";
                $button .= __("Take item delivery (bulk)", "order") . "</a></p>";

                echo "<form method='post' name='order_reception_form$suffix$bulk_rand'
                          action='" . Toolbox::getItemTypeFormURL("PluginOrderReception") . "'>";
                if ($position == 'top') {
                    echo "<br>";
                    echo $button;
                }
                echo "<div id='massreception$suffix$orders_id$bulk_rand'></div>";
                echo Html::scriptBlock("function viewmassreception$suffix" . $orders_id . "$bulk_rand() {" .
                    Ajax::updateItemJsCode(
                        "massreception$suffix" . $orders_id . $bulk_rand,
                        Plugin::getWebDir('order') . "/ajax/massreception.php",
                        [
                            'plugin_order_orders_id'     => $orders_id,
                            'plugin_order_references_id' => $references_id,
                        ],
                        '',
                        false
                    ) . "
                }");
                if ($position == 'bottom') {
                    echo $button;
                    echo "<br>";
                }

                Html::closeForm();
            }
        }
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

       //remove native transfer action
        unset($actions[MassiveAction::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_transfer_list']);
        $sep     = __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR;

        $actions[$sep . 'reception'] = __("Take item delivery", "order");
        $actions[$sep . 'transfer_order_item'] = _x('button', 'Add to transfer list');

        return $actions;
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        $reception = new self();
        switch ($ma->getAction()) {
            case 'reception':
                $reception->showReceptionForm($ma->POST);
                break;
            case 'transfer_order_item':
                $reception->showTransferForm($ma->POST);
                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        $reception  = new PluginOrderReception();
        switch ($ma->getAction()) {
            case 'transfer_order_item':
                foreach ($ids as $id) {
                    $order_item = new PluginOrderOrder_Item();
                    $input = [
                        'id' => $id,
                        'entities_id' => $ma->getInput()['entities_id']
                    ];

                    if ($order_item->update($input)) {
                        $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    }
                }
                break;

            case 'reception':
                $reception->updateReceptionStatus($ma);
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    public function showTransferForm($params = [])
    {
        $plugin_order_orders_id = $params['plugin_order_orders_id'];
        $order = new PluginOrderOrder();
        $order->getFromDB($plugin_order_orders_id);

        echo "<label class='order_ma'>" . __("Destination entity", "order") . "</label>";
        Entity::Dropdown([
            'name'        => "entities_id",
            'entity'      => $order->fields['entities_id'],
            'entity_sons' => $order->fields['is_recursive']
        ]);
        echo "<br/><br/>";
    }


    public function showReceptionForm($params = [])
    {
        echo "<label class='order_ma'>" . __("Delivery date") . "</label>";
        Html::showDateField("delivery_date", ['value'      => date("Y-m-d"),
            'maybeempty' => true,
            'canedit'    => true
        ]);

        echo "<label class='order_ma'>" . __("Delivery form") . "</label>";
        echo "<input type='text' name='delivery_number' size='20'>";

        echo "<label class='order_ma'>" . __("Delivery status", "order") . "</label>";
        PluginOrderDeliveryState::Dropdown(['name' => "plugin_order_deliverystates_id"]);

        $config = PluginOrderConfig::getConfig();
        if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
            echo "<label class='order_ma'>" . __("Enable automatic generation", "order") . "</label>";
            Dropdown::showFromArray('manual_generate', [
                PluginOrderConfig::CONFIG_NEVER => __('No'),
                PluginOrderConfig::CONFIG_YES   => __('Yes')
            ]);

            echo "<label class='order_ma'>" . __("Default name", "order") . "</label>";
            echo Html::input(
                'generated_name',
                [
                    'value' => $config->fields['generated_name'],
                ]
            );

            echo "<label class='order_ma'>" . __("Default serial number", "order") . "</label>";
            echo Html::input(
                'generated_serial',
                [
                    'value' => $config->fields['generated_serial'],
                ]
            );

            echo "<label class='order_ma'>" . __("Default inventory number", "order") . "<label>";
            echo Html::input(
                'generated_otherserial',
                [
                    'value' => $config->fields['generated_otherserial'],
                ]
            );
        }
    }

    public function getReceptionStatus($ID)
    {
        $detail = new PluginOrderOrder_Item();
        $detail->getFromDB($ID);

        switch ($detail->fields["states_id"]) {
            case PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED:
                return __("Waiting for delivery", "order");

            case PluginOrderOrder::ORDER_DEVICE_DELIVRED:
                return __("Taken delivery", "order");

            default:
                return "";
        }
    }


    public function updateBulkReceptionStatus($params)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $criteria = [
            'SELECT' => ['id', 'itemtype', 'entities_id'],
            'FROM' => 'glpi_plugin_order_orders_items',
            'WHERE' => [
                'plugin_order_orders_id' => $params["plugin_order_orders_id"],
                'plugin_order_references_id' => $params["plugin_order_references_id"],
                'states_id' => 0
            ]
        ];

        $result  = $DB->request($criteria);
        $nb      = count($result);

        if ($nb < $params['number_reception']) {
            Session::addMessageAfterRedirect(__("Not enough items to deliver", "order"), true, ERROR);
        } else {
            $i = 0;
            foreach ($result as $row) {
                if ($i >= $params['number_reception']) {
                    break;
                }
                $this->receptionOneItem(
                    $row['id'],
                    $params['plugin_order_orders_id'],
                    $params["delivery_date"],
                    $params["delivery_number"],
                    $params["plugin_order_deliverystates_id"]
                );

               // Automatic generate asset
                $options = [
                    "itemtype"                   => $row["itemtype"],
                    "items_id"                   => $row["id"],
                    "entities_id"                => $_SESSION['glpiactive_entity'],
                    "plugin_order_orders_id"     => $params['plugin_order_orders_id'],
                    "plugin_order_references_id" => $params["plugin_order_references_id"],
                ];

                $config = PluginOrderConfig::getConfig();
                if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
                    $options['manual_generate'] = $params['manual_generate'];
                    if ($params['manual_generate'] == 1) {
                        $options['name']            = $params['generated_name'];
                        $options['serial']          = $params['generated_serial'];
                        $options['otherserial']     = $params['generated_otherserial'];
                        $options['generate_assets'] = $params['generate_assets'];
                    }
                }
                self::generateAsset($options);
                $i++;
            }
            self::updateDelivryStatus($params['plugin_order_orders_id']);
        }
    }


    public function receptionOneItem($detailID, $orders_id, $delivery_date, $delivery_nb, $state_id)
    {
        $detail = new PluginOrderOrder_Item();
        $detail->update([
            "id"                             => $detailID,
            "delivery_date"                  => $delivery_date,
            "states_id"                      => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
            "delivery_number"                => $delivery_nb,
            "plugin_order_deliverystates_id" => $state_id,
        ]);

        Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
    }


    public function receptionAllItem($detailID, $ref_id, $orders_id, $delivery_date, $delivery_nb, $state_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $detail = new PluginOrderOrder_Item();
        $detail->getFromDB($detailID);
        $result = $detail->queryRef(
            $_POST["plugin_order_orders_id"],
            $ref_id,
            $detail->fields["price_taxfree"],
            $detail->fields["discount"],
            PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED
        );
        $nb = count($result);

        if ($nb) {
            foreach ($result as $row) {
                $detailID = $row['id'];
                $detail->update([
                    "id"                             => $detailID,
                    "delivery_date"                  => $delivery_date,
                    "states_id"                      => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
                    "delivery_number"                => $delivery_nb,
                    "plugin_order_deliverystates_id" => $state_id,
                ]);
            }
        }
        Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
    }


    public function updateReceptionStatus($params)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $detail                 = new PluginOrderOrder_Item();
        $plugin_order_orders_id = 0;
        $ma                     = false;
        $params2                = [];

       // from MassiveAction process, we get ma object, so convert it into array
        if (is_object($params)) {
            $ma      = $params;
            $params2 = [
                'items' => $ma->getItems(),
                'POST' => $ma->getInput(),
            ];
        }

        if (isset($params2['items'][__CLASS__])) {
            $additional_data_ite = $DB->request([
                'SELECT' => [
                    'glpi_plugin_order_orders_items.id',
                    'glpi_plugin_order_references.id AS plugin_order_references_id',
                    'glpi_plugin_order_references.itemtype',
                ],
                'FROM' => [
                    'glpi_plugin_order_orders_items'
                ],
                'LEFT JOIN' => [
                    'glpi_plugin_order_references' => [
                        'FKEY' => [
                            'glpi_plugin_order_orders_items' => 'plugin_order_references_id',
                            'glpi_plugin_order_references'   => 'id',
                        ]
                    ]
                ],
                'WHERE' => [
                    'glpi_plugin_order_orders_items.id' => array_keys($params2['items'][__CLASS__])
                ]
            ]);
            $additional_data = [];
            foreach ($additional_data_ite as $add_values) {
                $additional_data[$add_values['id']] = $add_values;
            }

            foreach ($params2['items'][__CLASS__] as $key => $val) {
                if ($val < 1) {
                    continue;
                }
                $add_data = $additional_data[$key];
                if ($add_data["itemtype"] == 'SoftwareLicense') {
                    $this->receptionAllItem(
                        $key,
                        $add_data["plugin_order_references_id"],
                        $params2['POST']["plugin_order_orders_id"],
                        $params2['POST']["delivery_date"],
                        $params2['POST']["delivery_number"],
                        $params2['POST']["plugin_order_deliverystates_id"]
                    );

                    $plugin_order_orders_id = $params2['POST']["plugin_order_orders_id"];
                } else {
                    if ($detail->getFromDB($key)) {
                        if (!$plugin_order_orders_id) {
                             $plugin_order_orders_id = $detail->fields["plugin_order_orders_id"];
                        }

                        if ($detail->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                            $this->receptionOneItem(
                                $key,
                                $plugin_order_orders_id,
                                $params2['POST']["delivery_date"],
                                $params2['POST']["delivery_number"],
                                $params2['POST']["plugin_order_deliverystates_id"]
                            );
                            if ($ma !== false) {
                                 $ma->itemDone(__CLASS__, $key, MassiveAction::ACTION_OK);
                            }
                        } else {
                            Session::addMessageAfterRedirect(__("Item already taken delivery", "order"), true, ERROR);
                            if ($ma !== false) {
                                $ma->itemDone(__CLASS__, $key, MassiveAction::ACTION_KO);
                            }
                        }

                       // Automatic generate asset
                        $options = [
                            "itemtype"                   => $add_data["itemtype"],
                            "items_id"                   => $key,
                            'entities_id'                => $detail->getEntityID(),
                            "plugin_order_orders_id"     => $detail->fields["plugin_order_orders_id"],
                            "plugin_order_references_id" => $add_data["plugin_order_references_id"],
                        ];

                        $config = PluginOrderConfig::getConfig(true);
                        if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
                            $options['manual_generate'] = $params2['POST']['manual_generate'];
                            if ($params2['POST']['manual_generate'] == 1) {
                                $options['name']            = $params2['POST']['generated_name'];
                                $options['serial']          = $params2['POST']['generated_serial'];
                                $options['otherserial']     = $params2['POST']['generated_otherserial'];
                            }
                        }
                        self::generateAsset($options);
                    }
                }
            }

            self::updateDelivryStatus($plugin_order_orders_id);
        } else {
            Session::addMessageAfterRedirect(__("No item selected", "order"), false, ERROR);
        }
    }


    public static function updateDelivryStatus($orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $config = PluginOrderConfig::getConfig();
        $order  = new PluginOrderOrder();

        $order->getFromDB($orders_id);

        $criteria = [
            'SELECT' => 'states_id',
            'FROM' => 'glpi_plugin_order_orders_items',
            'WHERE' => [
                'plugin_order_orders_id' => $orders_id
            ]
        ];
        $result = $DB->request($criteria);
        $number = count($result);

        $delivery_status = 0;
        $is_delivered    = 1; //Except order to be totally delivered
        if ($number) {
            foreach ($result as $data) {
                if ($data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
                    $delivery_status = 1;
                } else {
                    $is_delivered    = 0;
                }
            }
        }

       //Are all items delivered ?
        if ($is_delivered && !$order->isDelivered()) {
            $order->updateOrderStatus($orders_id, $config->getDeliveredState());
           //At least one item is delivered
        } else {
            if ($delivery_status) {
                $order->updateOrderStatus(
                    $orders_id,
                    $config->getPartiallyDeliveredState()
                );
            }
        }
    }


    public function prepareInputForUpdate($input)
    {
        if (isset($input['states_id']) && !$input['states_id']) {
            $input['delivery_date']                  = null;
            $input['delivery_number']                = '';
            $input['plugin_order_deliverystates_id'] = 0;
        }
        return $input;
    }


    public function post_updateItem($history = 1)
    {
        self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
    }


    public function post_purgeItem()
    {
        self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
    }


   /**
   *
   * @param $options
   *
   * @return void
   */
    public static function generateAsset($options = [])
    {
       // No asset should be generated for PluginOrderOther and PluginOrderReferenceFree items.
        if (
            array_key_exists('itemtype', $options)
            && (in_array($options['itemtype'], [PluginOrderOther::class, PluginOrderReferenceFree::class]))
        ) {
            return;
        }

       // Retrieve configuration for generate assets feature
        $config = PluginOrderConfig::getConfig();
        if (
            $config->canGenerateAsset() == PluginOrderConfig::CONFIG_YES
            || ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK
              && $options['manual_generate'] == 1)
        ) {
           // Automatic generate assets on delivery
            $rand = mt_rand();
            $item = [
                "name"                   => $config->getGeneratedAssetName() . $rand,
                "serial"                 => $config->getGeneratedAssetSerial() . $rand,
                "otherserial"            => $config->getGeneratedAssetOtherserial() . $rand,
                "entities_id"            => $options['entities_id'],
                "locations_id"           => '0',
                "groups_id"              => '0',
                "states_id"              => '0',
                "itemtype"               => $options["itemtype"],
                "id"                     => $options["items_id"],
                "plugin_order_orders_id" => $options["plugin_order_orders_id"],
            ];

            if (
                $config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK
                && ($options['manual_generate'] == 1)
            ) {
                $item['name']        = $options['name'] . $rand;
                $item['serial']      = $options['serial'] . $rand;
                $item['otherserial'] = $options['otherserial'] . $rand;
            }

            $options_gen = [
                "plugin_order_orders_id"     => $options["plugin_order_orders_id"],
                "plugin_order_references_id" => $options["plugin_order_references_id"],
                "id"                         => [$item],
                'itemtype'                   => $options['itemtype'],
            ];

            if ($config->canGenerateTicket()) {
                $options_gen["generate_ticket"] = [
                    "entities_id"        => $options['entities_id'],
                    "tickettemplates_id" => $config->fields['tickettemplates_id_delivery'],
                ];
            }

            $link = new PluginOrderLink();
            $link->generateNewItem($options_gen);
        }
    }


    public static function countForOrder(PluginOrderOrder $item)
    {
        return countElementsInTable(
            'glpi_plugin_order_orders_items',
            ['plugin_order_orders_id' => $item->getID()]
        );
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof PluginOrderOrder
            && PluginOrderOrder::canView()
            && $item->getState() > PluginOrderOrderState::WAITING_FOR_APPROVAL
        ) {
            return self::createTabEntry(__("Item delivered", "order"), self::countForOrder($item));
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof PluginOrderOrder) {
            $reception = new self();
            $reception->showOrderReception($item->getID());
        }

        return true;
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'            => '2',
            'table'         => $this->getTable(),
            'field'         => 'delivery_number',
            'name'          => __('Delivery form'),
            'autocomplete'  => true,
        ];

        return $tab;
    }
}
