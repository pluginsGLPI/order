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

class PluginOrderOrder_Item extends CommonDBRelation // phpcs:ignore
{
    public static $rightname              = 'plugin_order_order';

    public $dohistory                     = true;

    // From CommonDBRelation
    public static $itemtype_1             = "PluginOrderOrder";

    public static $items_id_1             = 'plugin_order_orders_id';

    public static $checkItem_1_Rights     = self::DONT_CHECK_ITEM_RIGHTS;

    public static $itemtype_2             = 'itemtype';

    public static $items_id_2             = 'items_id';

    public static $checkItem_2_Rights     = self::DONT_CHECK_ITEM_RIGHTS;

    public static $check_entity_coherency = false;

    //TODO better right and entity menber (ex Computer_Item)


    public static function canCreate(): bool
    {
        return true;
    }


    public static function canPurge(): bool
    {
        return true;
    }


    public static function canDelete(): bool
    {
        return true;
    }


    public function canCreateItem(): bool
    {
        return true;
    }


    public function canUpdateItem(): bool
    {
        return true;
    }


    public function canDeleteItem(): bool
    {
        return true;
    }


    public function canPurgeItem(): bool
    {
        return true;
    }


    public function canViewItem(): bool
    {
        return true;
    }


    public static function getTypeName($nb = 0)
    {
        return __("Order item", "order");
    }

    public static function getIcon()
    {
        return 'ti ti-clipboard-list';
    }

    public function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id'            => 'common',
            'name'          => __('Orders management', 'order'),
        ];

        $tab[] = [
            'id'            => 1,
            'table'         => self::getTable(),
            'field'         => 'price_ati',
            'name'          => __('Unit price tax free', 'order'),
            'datatype'      => 'decimal',
        ];

        $tab[] = [
            'id'            => 2,
            'table'         => self::getTable(),
            'field'         => 'discount',
            'name'          => __('Discount (%)', 'order'),
            'datatype'      => 'decimal',
        ];

        $tab[] = [
            'id'            => 3,
            'table'         => self::getTable(),
            'field'         => 'price_taxfree',
            'name'          => __('Discount (%)', 'order'),
            'datatype'      => 'decimal',
        ];

        $tab[] = [
            'id'            => 4,
            'table'         => self::getTable(),
            'field'         => 'delivery_number',
            'name'          => __('Delivery form'),
            'checktype'     => 'text',
            'displaytype'   => 'text',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 5,
            'table'         => self::getTable(),
            'field'         => 'delivery_date',
            'name'          => __('Delivery date'),
            'datatype'      => 'date',
            'checktype'     => 'date',
            'displaytype'   => 'date',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 6,
            'table'         => self::getTable(),
            'field'         => 'name',
            'name'          => __('Product name', 'order'),
            'autocomplete'  => true,
        ];

        $tab[] = [
            'id'            => 7,
            'table'         => self::getTable(),
            'field'         => 'reference_code',
            'name'          => __("Manufacturer's product reference", 'order'),
            'autocomplete'  => true,
        ];

        $tab[] = [
            'id'            => 16,
            'table'         => self::getTable(),
            'field'         => 'comment',
            'name'          => __('Description'),
            'datatype'      => 'text',
            'checktype'     => 'text',
            'displaytype'   => 'multiline_text',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 86,
            'table'         => 'glpi_plugin_order_deliverystates',
            'field'         => 'name',
            'name'          => __('Delivery status', 'order'),
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 87,
            'table'         => 'glpi_plugin_order_analyticnatures',
            'field'         => 'name',
            'name'          => __("Analytic Nature", "order"),
            'datatype'      => 'dropdown',
            'checktype'     => 'text',
            'displaytype'   => 'dropdown',
            'injectable'    => true,
            'massiveaction' => false,
        ];

        return $tab;
    }


    public static function updateItem(CommonDBTM $item)
    {

        //TO DO : Must do check same values or update infocom
        $plugin = new Plugin();
        if ($plugin->isActivated("order")) {
            if (isset($item->fields["id"])) {
                $item->getFromDB($item->input["id"]);

                if (isset($item->fields["itemtype"]) & isset($item->fields["items_id"])) {
                    $orderlink      = new PluginOrderLink();
                    $order          = new PluginOrderOrder();
                    $orderitem      = new self();
                    $order_supplier = new PluginOrderOrder_Supplier();

                    $detail_id      = $orderlink->isItemLinkedToOrder(
                        $item->fields["itemtype"],
                        $item->fields["items_id"],
                    );
                    if ($detail_id > 0) {
                        switch ($item->fields["itemtype"]) {
                            default:
                                $field_set    = false;
                                $unset_fields = ["order_number", "delivery_number", "budgets_id",
                                    "suppliers_id", "value",
                                ];
                                $orderitem->getFromDB($detail_id);
                                $order->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                                $order_supplier->getFromDBByOrder($orderitem->fields["plugin_order_orders_id"]);

                                $value["order_number"]    = $order->fields["num_order"];
                                $value["delivery_number"] = $orderitem->fields["delivery_number"];
                                $value["budgets_id"]      = $order->fields["budgets_id"];
                                $value["suppliers_id"]    = $order->fields["suppliers_id"];
                                $value["value"]           = $orderitem->fields["price_discounted"];
                                if (
                                    isset($order_supplier->fields["num_bill"])
                                    && !empty($order_supplier->fields["num_bill"])
                                ) {
                                    $unset_fields[]        = "bill";
                                    $value["bill"]         = $order_supplier->fields["num_bill"];
                                }

                                foreach ($unset_fields as $field) {
                                    if (
                                        isset($item->input[$field])
                                         && $item->input[$field] != $value[$field]
                                    ) {
                                        $field_set           = true;
                                        $item->input[$field] = $value[$field];
                                    }
                                }
                                if ($field_set && !isset($item->input['_no_warning'])) {
                                    Session::addMessageAfterRedirect(__("Some fields cannont be modified because they belong to an order", "order"), true, ERROR);
                                }
                                break;
                            case 'Contract':
                                $orderitem->getFromDB($detail_id);
                                $order->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                                $item->input['cost'] = $orderitem->fields["price_discounted"];
                                break;
                        }
                    }
                }
            }
        }
    }

    public static function getClasses($all = false)
    {
        /** @var array $ORDER_TYPES */
        global $ORDER_TYPES;

        if ($all) {
            return $ORDER_TYPES;
        }

        $types = $ORDER_TYPES;
        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }
            if (!$type::canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }


    public function getPricesATI($priceHT, $taxes)
    {
        return (!$priceHT ? 0 : $priceHT + (($priceHT * $taxes) / 100));
    }

    /**
     * Calculate the total ecotax from all ordered items with their quantities
     *
     * @param int $orders_id Order ID
     * @return float Total ecotax amount
     */
    public function getEcotaxTotal($orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $total_ecotax = 0;

        // Create subqueries for standard and free references
        $standard_subquery = new \Glpi\DBAL\QuerySubQuery([
            'SELECT' => [
                'plugin_order_references_id',
                new \Glpi\DBAL\QueryExpression('COUNT(' . $DB->quoteName('id') . ') AS quantity'),
                new \Glpi\DBAL\QueryExpression("'standard' AS reftype"),
            ],
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_order_orders_id' => $orders_id,
                'NOT' => ['itemtype' => 'PluginOrderReferenceFree'],
            ],
            'GROUP' => 'plugin_order_references_id',
        ]);

        $free_subquery = new \Glpi\DBAL\QuerySubQuery([
            'SELECT' => [
                'plugin_order_references_id',
                new \Glpi\DBAL\QueryExpression('COUNT(' . $DB->quoteName('id') . ') AS quantity'),
                new \Glpi\DBAL\QueryExpression("'free' AS reftype"),
            ],
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_order_orders_id' => $orders_id,
                'itemtype' => 'PluginOrderReferenceFree',
            ],
            'GROUP' => 'plugin_order_references_id',
        ]);

        // Create union query
        $union = new \Glpi\DBAL\QueryUnion([$standard_subquery, $free_subquery]);
        $iterator = $DB->request([
            'FROM' => $union,
        ]);

        foreach ($iterator as $data) {
            $ecotax_price = 0;

            if ($data['reftype'] === 'standard') {
                $reference = new PluginOrderReference();
                if ($reference->getFromDB((int) $data['plugin_order_references_id'])) {
                    $ecotax_price = $reference->getEcotaxPrice();
                }
            } else {
                $reference = new PluginOrderReferenceFree();
                if ($reference->getFromDB((int) $data['plugin_order_references_id'])) {
                    $ecotax_price = $reference->getEcotaxPrice();
                }
            }

            // Add to total (quantity * unit ecotax price)
            $total_ecotax += ((float) $data['quantity'] * (float) $ecotax_price);
        }

        return $total_ecotax;
    }

    public function addDetails($ref_id, $itemtype, $orders_id, $quantity, $price, $discounted_price, $taxes_id, $analytic_nature_id)
    {

        $order = new PluginOrderOrder();
        if ($quantity > 0 && $order->getFromDB($orders_id)) {
            $tax = new PluginOrderOrderTax();
            $tax->getFromDB($taxes_id);

            // Get ecotax price from reference
            $ecotax_price = 0;
            if ($itemtype == 'PluginOrderReferenceFree') {
                $reference = new PluginOrderReferenceFree();
                if ($reference->getFromDB($ref_id)) {
                    $ecotax_price = $reference->getEcotaxPrice();
                }
            } else {
                $reference = new PluginOrderReference();
                if ($reference->getFromDB($ref_id)) {
                    $ecotax_price = $reference->getEcotaxPrice();
                }
            }

            for ($i = 0; $i < $quantity; $i++) {
                $input["plugin_order_orders_id"]          = $orders_id;
                $input["plugin_order_references_id"]      = $ref_id;
                $input["plugin_order_ordertaxes_id"]      = $taxes_id;
                $input["plugin_order_analyticnatures_id"] = $analytic_nature_id;
                $input["itemtype"]                        = $itemtype;
                $input["entities_id"]                     = $order->getEntityID();
                $input["is_recursive"]                    = $order->isRecursive();
                $input["price_taxfree"]                   = $price;
                $input["price_discounted"]                = $price - ($price * ($discounted_price / 100));
                $input["states_id"]                       = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;
                $input["price_ati"]                       = $this->getPricesATI(
                    $input["price_discounted"],
                    $tax->getRate(),
                );
                $input["discount"]                        = $discounted_price;

                $this->add($input);
            }
        }
    }


    /* show details of orders */
    public function showItem($ID)
    {
        $this->showFormDetail($ID);
        $this->showAddForm($ID);
    }


    public function showAddForm($plugin_order_orders_id)
    {
        /**
         * @var \DBmysql $DB
         * @var array $CFG_GLPI
         */
        global $DB, $CFG_GLPI;

        $order     = new PluginOrderOrder();
        $reference = new PluginOrderReference();
        $config    = PluginOrderConfig::getConfig();

        if (
            $order->getFromDB($plugin_order_orders_id)
            && $order->canUpdateOrder()
        ) {
            if ($order->can($plugin_order_orders_id, UPDATE)) {
                echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"" .
                Toolbox::getItemTypeFormURL('PluginOrderOrder') . "\">";
                echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
                echo Html::hidden('entities_id', ['value' => $order->fields['entities_id']]);
                echo "<div class='center'>";
                echo "<hr>";
                echo "<h3>" . __("Add to the order from the catalog", "order") . "</h3>";
                echo "<table class='tab_cadre_fixe tab_order_fixed tab_order_add_items'>";

                if ($order->fields["suppliers_id"]) {
                    echo "<tr align='center'>";
                    echo "<th>" . __("Type") . "</th>";
                    echo "<th>" . __("Product reference", "order") . "</th>";
                    echo "<th " . (!$config->isAnalyticNatureDisplayed() ? 'style="display:none;"' : '') . ">";
                    echo __("Analytic nature", "order");
                    echo "</th>";
                    echo "<th>" . __("Quantity", "order") . "</th>";
                    echo "<th>" . __("Unit price tax free", "order") . "</th>";
                    echo "<th>" . __("VAT", "order") . "</th>";
                    echo "<th>" . __("Discount (%)", "order") . "</th>";
                    echo "<th></th>";
                    echo"</tr>";

                    echo "<tr align='center'>";
                    echo "<td class='tab_bg_1'>";

                    $criteria = [
                        'SELECT' => ['r.itemtype'],
                        'DISTINCT' => true,
                        'FROM' => 'glpi_plugin_order_references AS r',
                        'LEFT JOIN' => [
                            'glpi_plugin_order_references_suppliers AS s' => [
                                'ON' => [
                                    's' => 'plugin_order_references_id',
                                    'r' => 'id',
                                ],
                            ],
                        ],
                        'WHERE' => [
                            's.suppliers_id' => $order->fields["suppliers_id"],
                        ],
                    ];
                    $result = $DB->request($criteria);

                    $itemtypeArray = ['' => Dropdown::EMPTY_VALUE];
                    foreach ($result as $row) {
                        $itemtype = $row['itemtype'];
                        $type = getItemForItemtype($itemtype);
                        $itemtypeArray[$itemtype] = $type->getTypeName();
                    }
                    asort($itemtypeArray);

                    $rand = mt_rand();
                    Dropdown::showFromArray('itemtype', $itemtypeArray, [
                        'rand' => $rand,
                        'width' => '100%',
                    ]);

                    Ajax::updateItemOnSelectEvent(
                        'dropdown_itemtype' . $rand,
                        'show_reference',
                        '../ajax/dropdownReference.php',
                        [
                            'itemtype'     => '__VALUE__',
                            'fieldname'    => 'plugin_order_references_id',
                            'suppliers_id' => $order->fields["suppliers_id"],
                            'entities_id'  => $order->fields["entities_id"],
                            'is_recursive' => $order->fields["is_recursive"],
                            'rand'         => $rand,
                        ],
                    );
                    echo "</td>";
                    echo "<td class='tab_bg_1'><span id='show_reference'>";
                    Dropdown::showFromArray('plugin_order_references_id', [0 => Dropdown::EMPTY_VALUE], [
                        'rand'  => $rand,
                        'width' => '100%',
                    ]);
                    echo "</span></td>";

                    echo "<td " . (!$config->isAnalyticNatureDisplayed() ? 'style="display:none;"' : '') . ">";
                    PluginOrderAnalyticNature::Dropdown(['name'  => "plugin_order_analyticnatures_id"]);

                    if ($config->isAnalyticNatureMandatory()) {
                        echo " <span class='red'>*</span>";
                    }
                    echo "</td>";

                    echo "<td class='tab_bg_1'><span id='show_quantity'>";
                    echo "<input type='number' class='form-control' min='0' name='quantity' value='0' class='quantity' />";
                    echo "</span></td>";

                    echo "<td class='tab_bg_1'><span id='show_priceht'>";
                    echo "<input type='number' class='form-control' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='price' value='0.00' class='decimal' />";
                    echo "</span></td>";

                    echo "<td class='tab_bg_1'><span id='show_taxe'>";

                    PluginOrderOrderTax::Dropdown([
                        'name'                => "plugin_order_ordertaxes_id",
                        'value'               => $config->getDefaultTaxes(),
                        'display_emptychoice' => true,
                        'emptylabel'          => __("No VAT", "order"),
                    ]);
                    echo "</span></td>";

                    echo "<td class='tab_bg_1'><span id='show_pricediscounted'>";
                    echo "<input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='discount'
                            value='" . $order->fields['global_discount'] . "' class='smalldecimal' />";
                    echo "</span></td>";

                    echo "<td class='tab_bg_1'><span id='show_validate'>";
                    echo "<input type='submit' name='add_item' value=\"" . __("Add") . "\" class='submit'>";
                    echo "</span></td>";
                    echo "</tr>";
                } else {
                    echo "<tr class='tab_bg_1'><td align='center'>" . __("Please select a supplier", "order") . "</td></tr>";
                }

                echo "</table></div>";
                Html::closeForm();

                if ($config->useFreeReference()) {
                    echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\""
                    . Toolbox::getItemTypeFormURL('PluginOrderOrder') . "\">";
                    echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
                    echo Html::hidden('entities_id', ['value' => $order->fields['entities_id']]);
                    echo "<div class='center'>";
                    echo "<hr>";
                    echo "<h3>" . __("Add to the order free items", "order") . "</h3>";
                    echo "<table class='tab_cadre_fixe tab_order_fixed tab_order_add_items'>";

                    if ($order->fields["suppliers_id"]) {
                        echo "<tr align='center'>";
                        echo "<th>" . __("Product name", "order") . "</th>";
                        echo "<th>" . __("Manufacturer") . "</th>";
                        echo "<th " . (!$config->isAnalyticNatureDisplayed() ? 'style="display:none;"' : '') . ">";
                        echo __("Analytic nature", "order");
                        echo "</th>";
                        echo "<th>" . __("Quantity", "order") . "</th>";
                        echo "<th>" . __("Unit price tax free", "order") . "</th>";
                        echo "<th>" . __("VAT", "order") . "</th>";
                        echo "<th>" . __("Discount (%)", "order") . "</th>";
                        echo "<th>" . __("Add the reference", "order") . "</th>";
                        echo "<th name='add_reference' style='display: none;'>" . __("Item type") . "</th>";
                        echo "<th name='add_reference' style='display: none;'>" . __("Type") . "</th>";
                        echo "<th name='add_reference' style='display: none;'>" . __("Manufacturer's product reference", "order") . "</th>";
                        echo "<th></th>";
                        echo "</tr>";

                        echo "<tr align='center'>";
                        echo "<td class='tab_bg_1'>";
                        echo Html::input('name');
                        echo "</td>";

                        echo "<td class='tab_bg_1'>";
                        $rand = mt_rand();
                        Manufacturer::dropdown(['rand' => $rand]);
                        echo "</td>";

                        echo "<td " . (!$config->isAnalyticNatureDisplayed() ? 'style="display:none;"' : '') . ">";
                        PluginOrderAnalyticNature::Dropdown(['name'  => "plugin_order_analyticnatures_id"]);

                        if ($config->isAnalyticNatureMandatory()) {
                            echo " <span class='red'>*</span>";
                        }
                        echo "</td>";

                        echo "<td class='tab_bg_1'><span id='show_quantity'>";
                        echo "<input type='number' class='form-control' min='0' name='quantity' value='0' class='quantity' />";
                        echo "</span></td>";

                        echo "<td class='tab_bg_1'><span id='show_priceht'>";
                        echo "<input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='price' value='0.00' class='decimal' />";
                        echo "</span></td>";

                        echo "<td class='tab_bg_1'><span id='show_taxe'>";
                        $config = PluginOrderConfig::getConfig();
                        PluginOrderOrderTax::Dropdown([
                            'name'                => "plugin_order_ordertaxes_id",
                            'value'               => $config->getDefaultTaxes(),
                            'display_emptychoice' => true,
                            'emptylabel'          => __("No VAT", "order"),
                        ]);
                        echo "</span></td>";

                        echo "<td class='tab_bg_1'><span id='show_pricediscounted'>";
                        echo "<input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='discount'
                               value='" . $order->fields['global_discount'] . "' class='smalldecimal' />";
                        echo "</span></td>";

                        echo "<td class='tab_bg_1'><span id='show_addreference'>";

                        echo Html::scriptBlock("function plugin_order_checkboxAction() {
                                if ($('#addreference').is(':checked')) {
                                    $(\"td[name='add_reference']\").each(function () {
                                        $(this).show();
                                    });
                                    $(\"th[name='add_reference']\").each(function () {
                                        $(this).show();
                                    });
                                } else {
                                    $(\"td[name='add_reference']\").each(function () {
                                        $(this).hide();
                                    });
                                    $(\"th[name='add_reference']\").each(function () {
                                        $(this).hide();
                                    });
                                }

                        };");
                        echo "<input type='checkbox' id='addreference' onclick='plugin_order_checkboxAction()' name='addreference' value='0' />";
                        echo "</span></td>";

                        echo "<td class='tab_bg_1' name='add_reference' style='display: none;'><span id='show_addreference'>";
                        $params    = [
                            'myname'    => 'itemtype',
                            'value'     => 'PluginOrderOther',
                            'entity'    => $_SESSION["glpiactive_entity"],
                            'ajax_page' => $CFG_GLPI['root_doc'] . '/plugins/order/ajax/referencespecifications.php',
                            //                     'class'     => __CLASS__,
                        ];
                        $reference = new PluginOrderReference();
                        $reference->dropdownAllItems($params);

                        echo "</span></td>";

                        echo "<td class='tab_bg_1' name='add_reference' style='display: none;'>";
                        echo "<span id='show_types_id'>";
                        $file = 'other';

                        $core_typefilename   = GLPI_ROOT . "/src/" . $file . "Type.php";
                        $plugin_typefilename = PLUGIN_ORDER_DIR . "/inc/" . strtolower($file) . "type.class.php";
                        $itemtypeclass       = "PluginOrderOtherType";

                        if (
                            file_exists($core_typefilename)
                            || file_exists($plugin_typefilename)
                        ) {
                            Dropdown::show(
                                $itemtypeclass,
                                ['name' => "types_id"],
                            );
                        }
                        echo "</span>";
                        echo "</td>";

                        echo "<td class='tab_bg_1' name='add_reference' style='display: none;'>";
                        echo Html::input('reference_code');
                        echo "</td>";

                        echo "<td class='tab_bg_1'><span id='show_validate'>";
                        echo "<input type='submit' name='add_itemfree' value=\"" . __("Add") . "\" class='submit'>";
                        echo "</span></td>";
                        echo "</tr>";
                    } else {
                        echo "<tr class='tab_bg_1'><td align='center'>" . __("Please select a supplier", "order") . "</td></tr>";
                    }

                    echo "</table></div>";
                    Html::closeForm();
                }
            }
        }
    }

    public function prepareInputForAdd($input)
    {
        $config = PluginOrderConfig::getConfig();

        if (isset($input["id"]) && $input["id"] > 0) {
            $input["_oldID"] = $input["id"];
            unset($input['id']);
            unset($input['withtemplate']);
        } else {
            if (empty($input["plugin_order_references_id"])) {
                Session::addMessageAfterRedirect(__("You must select a reference", "order"), false, ERROR);
                return [];
            }
            if (
                $config->isAnalyticNatureDisplayed()
                && $config->isAnalyticNatureMandatory()
                && $input["plugin_order_analyticnatures_id"] == 0
            ) {
                Session::addMessageAfterRedirect(__("An analytic nature is mandatory !", "order"), false, ERROR);
                return [];
            }
        }

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $config = PluginOrderConfig::getConfig();
        if (
            $config->isAnalyticNatureDisplayed()
            && $config->isAnalyticNatureMandatory()
            && ($input["plugin_order_analyticnatures_id"] ?? $this->fields["plugin_order_analyticnatures_id"]) == 0
        ) {
            Session::addMessageAfterRedirect(__("An analytic nature is mandatory !", "order"), false, ERROR);
            return [];
        }

        return $input;
    }

    public function queryDetail($ID, $tableRef = 'glpi_plugin_order_references')
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();
        if ($tableRef == 'glpi_plugin_order_references') {
            $criteria = [
                'SELECT' => [
                    'item.id AS IDD',
                    'ref.id',
                    'ref.itemtype',
                    'othertype.name as othertypename',
                    'ref.types_id',
                    'ref.models_id',
                    'ref.manufacturers_id',
                    'ref.name',
                    'item.price_taxfree',
                    'item.price_ati',
                    'item.price_discounted',
                    'item.discount',
                    'item.plugin_order_ordertaxes_id',
                    'item.plugin_order_analyticnatures_id',
                ],
                'FROM' => "$table AS item",
                'LEFT JOIN' => [
                    'glpi_plugin_order_references AS ref' => [
                        'ON' => [
                            'item' => 'plugin_order_references_id',
                            'ref' => 'id',
                        ],
                    ],
                    'glpi_plugin_order_othertypes AS othertype' => [
                        'ON' => [
                            'ref' => 'types_id',
                            'othertype' => 'id', [
                                'AND' => [
                                    'ref.itemtype' => ['LIKE', 'PluginOrderOther'],
                                ],
                            ],
                        ],
                    ],
                ],
                'WHERE' => [
                    'item.plugin_order_orders_id' => $ID,
                    ['NOT' => ['item.itemtype' => ['LIKE', 'PluginOrderReferenceFree']]],
                ],
                'GROUPBY' => ['ref.id', 'item.price_taxfree', 'item.discount'],
                'ORDER' => 'ref.name',
            ];
            return $DB->request($criteria);
        } else {
            $criteria = [
                'SELECT' => [
                    'item.id AS IDD',
                    'ref.id',
                    'ref.itemtype',
                    'ref.manufacturers_id',
                    'ref.name',
                    'item.price_taxfree',
                    'item.price_ati',
                    'item.price_discounted',
                    'item.discount',
                    'item.plugin_order_ordertaxes_id',
                    'item.plugin_order_analyticnatures_id',
                ],
                'FROM' => "$table AS item",
                'INNER JOIN' => [
                    "$tableRef AS ref" => [
                        'ON' => [
                            'item' => 'plugin_order_references_id',
                            'ref' => 'id',
                        ],
                    ],
                ],
                'WHERE' => [
                    'item.plugin_order_orders_id' => $ID,
                    ['item.itemtype' => ['LIKE', 'PluginOrderReferenceFree']],
                ],
                'GROUPBY' => ['ref.id', 'item.price_taxfree', 'item.discount'],
                'ORDER' => 'ref.name',
            ];
            return $DB->request($criteria);
        }
    }


    public function queryBills($orders_id, $references_id, $tabRef)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();

        if ($tabRef == 'glpi_plugin_order_references') {
            $criteria = [
                'SELECT' => [
                    'item.id AS IDD',
                    'ref.id',
                    'ref.itemtype',
                    'ref.types_id',
                    'ref.models_id',
                    'ref.manufacturers_id',
                    'ref.name',
                    'item.plugin_order_bills_id',
                    'item.plugin_order_billstates_id',
                ],
                'FROM' => ["$table AS item", 'glpi_plugin_order_references AS ref'],
                'WHERE' => [
                    'item.plugin_order_references_id' => new \Glpi\DBAL\QueryExpression('ref.id'),
                    'item.plugin_order_orders_id' => $orders_id,
                    'ref.id' => $references_id,
                    ['NOT' => ['item.itemtype' => ['LIKE', 'PluginOrderReferenceFree']]],
                ],
                'ORDER' => 'ref.name',
            ];
        } else {
            $criteria = [
                'SELECT' => [
                    'item.id AS IDD',
                    'ref.id',
                    'ref.itemtype',
                    'ref.manufacturers_id',
                    'ref.name',
                    'item.plugin_order_bills_id',
                    'item.plugin_order_billstates_id',
                ],
                'FROM' => ["$table AS item", "$tabRef AS ref"],
                'WHERE' => [
                    'item.plugin_order_references_id' => new \Glpi\DBAL\QueryExpression('ref.id'),
                    'item.plugin_order_orders_id' => $orders_id,
                    'ref.id' => $references_id,
                    ['item.itemtype' => ['LIKE', 'PluginOrderReferenceFree']],
                ],
                'ORDER' => 'ref.name',
            ];
        }

        return $DB->request($criteria);
    }


    public function queryRef($orders_id, $ref_id, $price_taxfree, $discount, $states_id = false)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $criteria = [
            'SELECT' => ['id', 'items_id'],
            'FROM' => 'glpi_plugin_order_orders_items',
            'WHERE' => [
                'plugin_order_orders_id' => $orders_id,
                'plugin_order_references_id' => $ref_id,
                new \Glpi\DBAL\QueryExpression("CAST(" . $DB->quoteName('price_taxfree') . " AS CHAR) = " . $DB->quote($price_taxfree)),
                ['NOT' => ['itemtype' => ['LIKE', 'PluginOrderReferenceFree']]],
                new \Glpi\DBAL\QueryExpression("CAST(" . $DB->quoteName('discount') . " AS CHAR) = " . $DB->quote($discount)),
            ],
        ];

        if ($states_id) {
            $criteria['WHERE']['states_id'] = $states_id;
        }

        return $DB->request($criteria);
    }


    public function showFormDetail($plugin_order_orders_id)
    {
        /** @var \DBmysql $DB */
        global  $DB;

        $order                = new PluginOrderOrder();
        $reference            = new PluginOrderReference();
        $reception            = new PluginOrderReception();
        $result_ref           = $this->queryDetail($plugin_order_orders_id, 'glpi_plugin_order_references');
        $numref               = count($result_ref);
        $rand                 = mt_rand();
        $canedit              = $order->can($plugin_order_orders_id, UPDATE)
                              && $order->canUpdateOrder();
        Session::initNavigateListItems(
            $this->getType(),
            __("Order", "order") . " = " . $order->getName(),
        );
        foreach ($result_ref as $data_ref) {
            self::getItems(
                $rand,
                $data_ref,
                $plugin_order_orders_id,
                $numref,
                $canedit,
                $reference,
                $reception,
                'glpi_plugin_order_references',
            );
        }

        $result_ref_free       = $this->queryDetail($plugin_order_orders_id, 'glpi_plugin_order_referencefrees');
        $numref_free           = count($result_ref_free);
        foreach ($result_ref_free as $data_ref_free) {
            self::getItems(
                $rand,
                $data_ref_free,
                $plugin_order_orders_id,
                $numref_free,
                $canedit,
                $reference,
                $reception,
                'glpi_plugin_order_referencefrees',
            );
        }
    }

    public function getItems($rand, $data_ref, $plugin_order_orders_id, $numref, $canedit, $reference, $reception, $table_ref)
    {
        /** @var array $CFG_GLPI */
        /** @var \DBmysql $DB */
        global  $CFG_GLPI,$DB;

        $config      = new PluginOrderConfig();
        $hidden_fields = [
            'plugin_order_orders_id' => $plugin_order_orders_id,
            'plugin_order_order_items_id' => $data_ref['IDD'],
        ];

        $refID         = $data_ref["id"];
        $price_taxfree = $data_ref["price_taxfree"];
        $discount      = $data_ref["discount"];
        $rand          = mt_rand();
        $countainer_name = 'countainer' . $plugin_order_orders_id . "_" . $refID;

        $columns = [
            'quantity' => __("Quantity", "order"),
        ];
        if ($config->isAnalyticNatureDisplayed()) {
            $columns['analytic_nature'] = __("Analytic nature", "order");
        }
        $columns = array_merge($columns, [
            'assets' => __("Assets"),
            'manufacturer' => __("Manufacturer"),
            'reference' => __("Reference"),
            'type' => __("Type"),
            'model' => __("Model"),
            'manufacturer_reference' => __("Manufacturer reference", "order"),
            'unit_price' => __("Unit price tax free", "order"),
            'discount' => __("Discount (%)", "order"),
        ]);

        /* quantity */
        $quantity = $this->getTotalQuantityByRefAndDiscount(
            $plugin_order_orders_id,
            $refID,
            $price_taxfree,
            $discount,
        );
        $entrie['quantity'] = $quantity;

        $config = new PluginOrderConfig();

        if ($config->isAnalyticNatureDisplayed()) {
            $entrie['analytic_nature'] = Dropdown::getDropdownName(
                "glpi_plugin_order_analyticnatures",
                $data_ref["plugin_order_analyticnatures_id"],
            );
        }

        /* type */
        if (!is_null($data_ref["itemtype"]) && class_exists($data_ref["itemtype"])) {
            $item = getItemForItemtype($data_ref["itemtype"]);
            if (is_a($item, CommonGLPI::class)) {
                $entrie['assets'] = $item->getTypeName();
            }
        } else {
            $entrie['assets'] = '';
        }
        /* manufacturer */
        $entrie['manufacturer'] = Dropdown::getDropdownName(
            "glpi_manufacturers",
            $data_ref["manufacturers_id"],
        );
        /* reference */
        $hidden_fields = array_merge($hidden_fields, [
            'old_plugin_order_references_id' => $refID,
            'old_price_taxfree' => $price_taxfree,
            'old_discount' => $discount,
        ]);

        if ($table_ref == 'glpi_plugin_order_referencefrees') {
            $entrie['reference'] = $data_ref['name'];
        } else {
            $entrie['reference'] = $reference->getReceptionReferenceLink($data_ref);
        }

        /* type */
        if (file_exists(GLPI_ROOT . "/src/" . $data_ref["itemtype"] . "Type.php")) {
            $entrie['type'] = Dropdown::getDropdownName(
                getTableForItemType($data_ref["itemtype"] . "Type"),
                $data_ref["types_id"],
            );
        } elseif ($data_ref["itemtype"] == "PluginOrderOther") {
            $entrie['type'] =  $data_ref['othertypename'];
        } else {
            $entrie['type'] = '';
        }

        /* modele */
        if (file_exists(GLPI_ROOT . "/src/" . $data_ref["itemtype"] . "Model.php")) {
            $entrie['model'] = Dropdown::getDropdownName(
                getTableForItemType($data_ref["itemtype"] . "Model"),
                $data_ref["models_id"],
            );
        } else {
            $entrie['model'] = '';
        }

        /* Manufacturer Reference*/
        $entrie['manufacturer_reference'] = $this->getManufacturersReference($refID);

        /* unit price */
        $entrie['unit_price'] = Html::formatNumber($price_taxfree);

        /* reduction */
        $entrie['discount'] = Html::formatNumber($discount);

        $countainer_name = 'countainer' . $plugin_order_orders_id . "_" . $refID;

        TemplateRenderer::getInstance()->display('@order/order_getitems.html.twig', [
            'nopager' => true,
            'nofilter' => true,
            'is_tab' => true,
            'nosort' => false,
            'table_visible' => true,
            'items_id' => $plugin_order_orders_id,
            'columns' => $columns,
            'formatters' => [
                'reference' => 'raw_html',
                'manufacturer' => 'raw_html',
                'type' => 'raw_html',
                'model' => 'raw_html',
            ],
            'columns_values' => [],
            'entries' => [$entrie],
            'total_number' => $numref,
            'filtered_number' => $numref,
            'showmassiveactions' => false,
            'hidden_fields' => $hidden_fields,
        ]);

        // Initialize columns array based on needed headers
        $columns = [];

        if ($data_ref["itemtype"] != 'SoftwareLicense') {
            $columns['id_showed'] = __("ID");
        }
        $columns['reference'] = __("Reference");
        $columns['price_taxfree'] = __("Unit price tax free", "order");
        $columns['vat'] = __("VAT", "order");
        $columns['discount'] = __("Discount (%)", "order");
        $columns['price_discounted'] = __("Discounted price tax free", "order");
        $columns['price_ati'] = __("Price ATI", "order");
        $columns['status'] = __("Status");

        // Prepare to collect entries
        $entries = [];

        $table = self::getTable();

        $start = (int) ($_GET['start'] ?? 0);
        $limit = (int) ($_GET['glpilist_limit'] ?? 15);

        $criteria_count = [
            'COUNT' => 'total',
            'FROM' => $table,
            'JOIN' => [
                $table_ref => [
                    'ON' => [
                        "$table.plugin_order_references_id",
                        "$table_ref.id",
                    ],
                ],
            ],
            'WHERE' => [
                "$table.plugin_order_references_id" => "$table_ref.id",
                "$table.plugin_order_references_id" => $refID,
                "$table.price_taxfree" => ['LIKE', $price_taxfree],
                "$table.discount" => ['LIKE', $discount],
                "$table.plugin_order_orders_id" => $plugin_order_orders_id,
            ],
        ];

        if ($data_ref["itemtype"] == 'SoftwareLicense') {
            $criteria_count['GROUPBY'] = "$table_ref.name";
        }

        $iterator_count = $DB->request($criteria_count);
        $total_number = ($data_ref["itemtype"] == 'SoftwareLicense')
            ? count($iterator_count)
            : $iterator_count->current()['total'];

        $criteria = [
            'SELECT' => [
                "$table.id AS IDD",
                "$table_ref.id",
                "$table_ref.name",
                "$table.comment",
                "$table.price_taxfree",
                "$table.price_discounted",
                "$table.discount",
                "$table.plugin_order_ordertaxes_id",
                "$table.price_ati",
            ],
            'FROM' => $table,
            'JOIN' => [
                $table_ref => [
                    'ON' => [
                        "$table.plugin_order_references_id",
                        "$table_ref.id",
                    ],
                ],
            ],
            'WHERE' => [
                "$table.plugin_order_references_id" => $refID,
                "$table.price_taxfree" => ['LIKE', $price_taxfree],
                "$table.discount" => ['LIKE', $discount],
                "$table.plugin_order_orders_id" => $plugin_order_orders_id,
            ],
            'ORDER' => "$table_ref.name",
            'LIMIT' => $limit,
            'START' => $start,
        ];

        if ($data_ref["itemtype"] == 'SoftwareLicense') {
            $criteria['GROUPBY'] = "$table_ref.name";
        }

        $iterator = $DB->request($criteria);
        $displayed_number = count($iterator);

        $sort = $_GET[$countainer_name . 'sort'] ?? 'id_showed';
        $order = $_GET[$countainer_name . 'order'] ?? 'ASC';
        $visible = $_GET[$countainer_name . 'visible'] ?? false;

        foreach ($iterator as $data) {
            Session::addToNavigateListItems($this->getType(), (int) $data['IDD']);

            // Build entry for this row
            $entry = [];

            $entry['id'] = $data['IDD'];
            $entry['id_showed'] = "<a href='" . Toolbox::getItemTypeFormURL('PluginOrderOrder_Item') .
                    "?id=" . $data['IDD'] . "'>" . $data['IDD'] . "</a>&nbsp;" .
                    Html::showToolTip($data['comment'], ['display' => false]);

            // Reference
            $entry['reference'] = Html::hidden(
                "detail_old_plugin_order_references_id[" . $data["IDD"] . "]",
                ['value' => $data["id"]],
            );

            if ($table_ref == 'glpi_plugin_order_referencefrees') {
                $entry['reference'] .= $data_ref['name'];
            } else {
                $entry['reference'] .= $reference->getReceptionReferenceLink($data);
            }

            // Price tax free
            $entry['price_taxfree'] = Html::formatNumber((float) $data["price_taxfree"]);

            // VAT
            $entry['vat'] = Dropdown::getDropdownName(
                getTableForItemType("PluginOrderOrderTax"),
                (int) $data["plugin_order_ordertaxes_id"],
            );

            // Discount
            $entry['discount'] = Html::formatNumber((float) $data["discount"]);

            // Price with reduction
            $entry['price_discounted'] = Html::formatNumber((float) $data["price_discounted"]);

            // Price ATI
            $entry['price_ati'] = Html::formatNumber((float) $data["price_ati"]);

            // Status
            $entry['status'] = $reception->getReceptionStatus($data["IDD"]);

            // Add entry require for massive actions
            $entry['itemtype'] = PluginOrderOrder_Item::class;

            $entries[] = $entry;
        }

        if (!empty($entries) && isset($columns[$sort])) {
            usort($entries, function ($a, $b) use ($sort, $order) {
                // Handle different data types appropriately
                $val_a = $a[$sort] ?? null;
                $val_b = $b[$sort] ?? null;

                if (is_numeric($val_a) && is_numeric($val_b)) {
                    $cmp = $val_a <=> $val_b;
                } else {
                    if (is_string($val_a)) {
                        if (preg_match('/<a[^>]*>(\d+)<\/a>/', $val_a, $matches)) {
                            $val_a = (int) $matches[1];
                        } else {
                            $val_a = strip_tags($val_a);
                        }
                    }

                    if (is_string($val_b)) {
                        if (preg_match('/<a[^>]*>(\d+)<\/a>/', $val_b, $matches)) {
                            $val_b = (int) $matches[1];
                        } else {
                            $val_b = strip_tags($val_b);
                        }
                    }

                    if (is_numeric($val_a) && is_numeric($val_b)) {
                        $cmp = (float) $val_a <=> (float) $val_b;
                    } else {
                        $cmp = strcasecmp((string) $val_a, (string) $val_b);
                    }
                }

                return $order === 'DESC' ? -$cmp : $cmp;
            });
        }

        // Render the table using the template
        TemplateRenderer::getInstance()->display('@order/order_getitems.html.twig', [
            'rand' => $rand,
            'ID' => $plugin_order_orders_id,
            'countainer_name' => $countainer_name,
            'hide_and_show' => true,
            'table_visible' => $visible,
            'sub_table' => true,
            'nopager' => false,
            'nofilter' => true,
            'is_tab' => true,
            'sort' => $sort,
            'order' => $order,
            'columns' => $columns,
            'formatters' => [
                'id_showed' => 'raw_html',
                'reference' => 'raw_html',
                'vat' => 'raw_html',
                'price_taxfree' => 'raw_html',
                'discount' => 'raw_html',
            ],
            'entries' => $entries,
            'canedit' => $canedit,
            'total_number' => $total_number,
            'filtered_number' => $total_number,
            'displayed_count' => $displayed_number,
            'start' => $start,
            'limit' => $limit,
            'massiveactionparams' => [
                'container'        => 'mass' . __CLASS__ . $rand,
                'itemtype'         => PluginOrderOrder_Item::class,
                'specific_actions' => [
                    'purge'     => _x('button', 'Delete permanently'),
                ],
            ],
        ]);
    }

    public function getTotalQuantityByRefAndDiscount($orders_id, $references_id, $price_taxfree, $discount)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $criteria = [
            'COUNT' => 'quantity',
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_order_orders_id' => $orders_id,
                'plugin_order_references_id' => $references_id,
                'price_taxfree' => ['LIKE', $price_taxfree],
                'discount' => ['LIKE', $discount],
            ],
        ];
        $iterator = $DB->request($criteria);
        return $iterator->current()['quantity'];
    }


    public function getDeliveredQuantity($orders_id, $references_id, $price_taxfree, $discount)
    {
        return countElementsInTable(
            self::getTable(),
            [
                'plugin_order_orders_id'     => $orders_id,
                'plugin_order_references_id' => $references_id,
                'price_taxfree'              => ['LIKE', $price_taxfree],
                'discount'                   => ['LIKE', $discount],
                'states_id'                  => ['<>', 0],
            ],
        );
    }


    public function getAllPrices($orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $criteria = [
            'SELECT' => [
                'SUM' => [
                    'price_ati AS priceTTC',
                    'price_discounted AS priceHT',
                    'price_ati` - `price_discounted AS priceTVA',
                ],
            ],
            'FROM' => self::getTable(),
            'WHERE' => ['plugin_order_orders_id' => $orders_id],
        ];
        $iterator = $DB->request($criteria);
        return $iterator->current();
    }


    public function getOrderInfosByItem($itemtype, $items_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'SELECT' => 'glpi_plugin_order_orders.*',
            'FROM' => ['glpi_plugin_order_orders', $table],
            'WHERE' => [
                "glpi_plugin_order_orders.id" => "$table.plugin_order_orders_id",
                "$table.itemtype" => $itemtype,
                "$table.items_id" => $items_id,
            ],
        ];
        $iterator = $DB->request($criteria);
        if (count($iterator)) {
            return $iterator->current();
        } else {
            return false;
        }
    }


    public function showPluginFromItems($itemtype, $ID)
    {
        $infos = $this->getOrderInfosByItem($itemtype, $ID);

        if ($infos) {
            $twig_option = [];
            $order = new PluginOrderOrder();
            $order->getFromDB($infos['id']);
            $twig_option['order_link'] = $order->getLink();

            $result = getAllDataFromTable(
                self::getTable(),
                [
                    'plugin_order_orders_id' => $infos['id'],
                    'itemtype' => $itemtype,
                    'items_id' => $ID,
                ],
            );
            if (!empty($result)) {
                $link = array_shift($result);
                $reference = new PluginOrderReference();
                $reference->getFromDB($link['plugin_order_references_id']);
                if (Session::haveRight('plugin_order_reference', READ)) {
                    $twig_option['reference_link'] = $reference->getLink();
                }
                $twig_option['delivery_date'] = Html::convDate($link["delivery_date"]);
            }

            TemplateRenderer::getInstance()->display('@order/order_infocom.html.twig', $twig_option);
        }
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        $order_order = new PluginOrderOrder();
        $order_order->getFromDB($this->fields['plugin_order_orders_id']);

        if ($this->fields['itemtype'] == 'PluginOrderReferenceFree') {
            $order_reference = new PluginOrderReferenceFree();
            $order_reference->getFromDB($this->fields["plugin_order_references_id"]);
        } else {
            $order_reference = new PluginOrderReference();
            $order_reference->getFromDB($this->fields["plugin_order_references_id"]);
        }

        $canedit         = $order_order->can($this->fields['plugin_order_orders_id'], UPDATE)
                          && $order_order->canUpdateOrder() && !$order_order->isCanceled();
        $canedit_comment = $order_order->can($this->fields['plugin_order_orders_id'], UPDATE)
                          && !$order_order->isCanceled();

        echo Html::hidden(
            'plugin_order_orders_id',
            ['value' => $this->fields['plugin_order_orders_id']],
        );

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __("Order", "order") . ": </td>";
        echo "<td>";
        echo $order_order->getLink();
        echo "</td>";

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

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Unit price tax free", "order") . ": </td>";
        if ($canedit) {
            echo "<td><input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='price_taxfree' value='" . $this->fields['price_taxfree'] . "' class='decimal'>";
        } else {
            echo "<td>" . Html::formatNumber($this->fields['price_taxfree']) . "</td>";
        }

        echo "<td>" . __("VAT", "order") . ": </td>";
        echo "<td>";
        if ($canedit) {
            PluginOrderOrderTax::Dropdown(['value' => $this->fields['plugin_order_ordertaxes_id']]);
        } else {
            echo Dropdown::getDropdownName(
                'glpi_plugin_order_ordertaxes',
                $this->fields['plugin_order_ordertaxes_id'],
            );
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Discount (%)", "order") . ": </td>";
        if ($canedit) {
            echo "<td><input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='discount'
                          value='" . $this->fields['discount'] . "' class='decimal'>";
        } else {
            echo "<td>" . Html::formatNumber($this->fields['discount']) . "</td>";
        }

        echo "<td>" . __("Discounted price tax free", "order") . ": </td>";
        echo "<td>" . Html::formatNumber($this->fields['price_discounted']) . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Price ATI", "order") . ": </td>";
        echo "<td>" . Html::formatNumber($this->fields['price_ati']) . "</td>";

        echo "<td>" . __("Status") . ": </td>";
        echo "<td>";
        echo Dropdown::getDropdownName(
            'glpi_plugin_order_deliverystates',
            $this->fields['plugin_order_deliverystates_id'],
        );
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>";

        //comments of order
        echo __("Description") . ":  </td>";
        echo "<td colspan='3'>";
        if ($canedit_comment) {
            echo "<textarea cols='50' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
        } else {
            echo $this->fields['comment'];
        }
        echo "</td></tr>";

        $this->showFormButtons([
            'canedit' => ($canedit || $canedit_comment),
            'candel' => $canedit,
        ]);

        return true;
    }


    public function updatePrices($order_items_id)
    {
        /** @var \DBmysql $DB */
        global $DB;
        $this->getFromDB($order_items_id);
        if (
            isset($this->input['price_taxfree'])
            || isset($this->input['plugin_order_ordertaxes_id'])
        ) {
            $iterator = $this->queryRef(
                $this->fields['plugin_order_orders_id'],
                $this->fields['plugin_order_references_id'],
                $this->fields['price_taxfree'],
                $this->fields['discount'],
            );
            foreach ($iterator as $item) {
                $this->updatePrice_taxfree([
                    'item_id'       => $item['id'],
                    'price_taxfree'  => $this->fields['price_taxfree'],
                ]);
            }
        }
    }


    public function showBillsItems(PluginOrderOrder $order)
    {
        /** @var \DBmysql $DB */
        global $DB;

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th colspan='2'>" . __("Bills", "order") . "</th></tr>";
        echo "<tr class='tab_bg_1'><td class='center'>" . __("Payment status", "order") . ": </td>";
        echo "<td>";
        echo PluginOrderBillState::getState($order->fields['plugin_order_billstates_id']);
        echo "</td></tr></table>";

        $table = self::getTable();
        if (
            countElementsInTable(
                $table,
                ['WHERE' => ['plugin_order_orders_id' => $order->getID()],
                    'GROUPBY' => 'plugin_order_bills_id',
                ],
            )
        ) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th>" . __("Name") . "</th>";
            echo "<th>" . __("Status") . "</th>";
            echo "<th>" . __("Value") . "</th>";
            echo "<th>" . __("Paid value", "order") . "</th></tr>";

            $bill = new PluginOrderBill();

            $query = [
                'FROM' => $table,
                'WHERE' => [
                    'plugin_order_orders_id' => $order->getID(),
                ],
                'GROUPBY' => 'plugin_order_bills_id',
            ];

            foreach ($DB->request($query) as $item) {
                if (
                    isset($item['plugin_order_bills_id'])
                    && $item['plugin_order_bills_id']
                ) {
                    echo "<tr class='tab_bg_1'><td class='center'>";
                    if ($bill->can($item['plugin_order_bills_id'], READ)) {
                        echo "<td><a href='" . $bill->getLinkURL() . "'>" . $bill->getName() . "</a></td>";
                    } else {
                        echo "<td>" . $bill->getName() . "</td>";
                    }

                    echo "</td>";
                    echo "<td>";
                    echo Dropdown::getDropdownName(
                        PluginOrderBillState::getTable(),
                        $bill->fields['plugin_order_billstates_id'],
                    );
                    echo "</td></tr>";
                }
            }

            echo "</tr></table>";
        }

        //Can write orders, and order is not already paid
        $canedit = $order->can($order->getID(), UPDATE)
                 && !$order->isPaid() && !$order->isCanceled();

        $iterator_ref = self::queryBillsItems($order->getID(), 'glpi_plugin_order_references');
        foreach ($iterator_ref as $data_ref) {
            self::showBillsItemsDetail($data_ref, $iterator_ref, $canedit, $order, 'glpi_plugin_order_references');
        }

        $iterator_reffree = self::queryBillsItems($order->getID(), 'glpi_plugin_order_referencefrees');
        foreach ($iterator_reffree as $data_reffree) {
            self::showBillsItemsDetail($data_reffree, $iterator_reffree, $canedit, $order, 'glpi_plugin_order_referencefrees');
        }
        echo "<br>";
    }

    public static function queryBillsItems($ID, $table)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $criteria = [
            'SELECT' => [
                'glpi_plugin_order_orders_items.id AS IDD',
                'glpi_plugin_order_orders_items.plugin_order_references_id AS id',
                'ref.name',
                'ref.itemtype',
                'ref.manufacturers_id',
            ],
            'FROM' => ['glpi_plugin_order_orders_items', "$table AS ref"],
            'WHERE' => [
                'glpi_plugin_order_orders_items.plugin_order_orders_id' => $ID,
                'glpi_plugin_order_orders_items.plugin_order_references_id' => new \Glpi\DBAL\QueryExpression('ref.id'),
            ],
            'GROUPBY' => 'glpi_plugin_order_orders_items.plugin_order_references_id',
            'ORDER' => 'ref.name',
        ];

        if ($table == 'glpi_plugin_order_references') {
            $criteria['WHERE'][] = ['NOT' => ['glpi_plugin_order_orders_items.itemtype' => ['LIKE', 'PluginOrderReferenceFree']]];
        } else {
            $criteria['WHERE']['glpi_plugin_order_orders_items.itemtype'] = ['LIKE', 'PluginOrderReferenceFree'];
        }

        return $DB->request($criteria);
    }

    public function showBillsItemsDetail($data_ref, $result_ref, $canedit, $order, $table)
    {
        /** @var \DBmysql $DB */
        /** @var array $CFG_GLPI */
        global $DB, $CFG_GLPI;

        $reference = new PluginOrderReference();
        $rand      = mt_rand();

        echo "<table class='tab_cadre_fixe'>";
        if (!count($result_ref)) {
            echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";
        } else {
            $itemtype = $data_ref["itemtype"];
            $item     = getItemForItemtype($itemtype);
            echo "<tr><th><ul class='list-unstyled'><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation_img$rand', '" .
              $CFG_GLPI['root_doc'] . "/pics/plus.png','" . $CFG_GLPI['root_doc'] . "/pics/moins.png');\">";
            echo "<img alt='' name='generation_img$rand' src=\"" . $CFG_GLPI['root_doc'] . "/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . __("Assets") . "</th>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";

            //Entity
            echo "<td align='center'>";
            echo Dropdown::getDropdownName('glpi_entities', $this->getEntityID());
            echo "</td>";
            if ($table == 'glpi_plugin_order_referencefrees') {
                echo "<td>" . $data_ref['name'] . "</td>";
            } else {
                echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
            }
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='bills_form$rand' id='bills_form$rand'  " .
              "action='" . Toolbox::getItemTypeFormURL('PluginOrderBill') . "'>";

            echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
            echo "<table class='tab_cadre_fixe'>";

            echo "<th></th>";
            echo "<th>" . __("Reference") . "</th>";
            echo "<th>" . __("Type") . "</th>";
            echo "<th>" . __("Model") . "</th>";
            echo "<th>" . __("Bill", "order") . "</th>";
            echo "<th>" . __("Bill status", "order") . "</th>";
            echo "</tr>";

            $iterator = $this->queryBills($order->getID(), $data_ref['id'], $table);
            foreach ($iterator as $data) {
                echo "<tr class='tab_bg_1'>";
                if ($canedit) {
                    echo "<td width='10'>";
                    $sel = "";
                    if (
                        isset($_POST['select'])
                        && (
                            is_string($_POST['select'])
                            && $_POST['select'] == 'all'
                            || (
                                is_array($_POST['select'])
                                && isset($_POST['select']['all'])
                            )
                        )
                    ) {
                        $sel = "checked";
                    }
                    echo "<input type='checkbox' name='item[" . $data["IDD"] . "]' value='1' $sel>";
                    echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
                    echo "</td>";
                }

                //Reference
                echo "<td align='center'>";
                if ($table == 'glpi_plugin_order_referencefrees') {
                    echo $data['name'];
                } else {
                    echo $reference->getReceptionReferenceLink($data);
                }
                echo "</td>";

                //Type
                echo "<td align='center'>";
                if (file_exists(GLPI_ROOT . "/src/" . $data["itemtype"] . "Type.php")) {
                    echo Dropdown::getDropdownName(
                        getTableForItemType($data["itemtype"] . "Type"),
                        (int) $data["types_id"],
                    );
                }
                echo "</td>";
                //Model
                echo "<td align='center'>";
                if (file_exists(GLPI_ROOT . "/src/" . $data["itemtype"] . "Model.php")) {
                    echo Dropdown::getDropdownName(
                        getTableForItemType($data["itemtype"] . "Model"),
                        (int) $data["models_id"],
                    );
                }
                $bill = new PluginOrderBill();
                echo "<td align='center'>";
                if ($data["plugin_order_bills_id"] > 0) {
                    if ($bill->can((int) $data['plugin_order_bills_id'], READ)) {
                        echo "<a href='" . $bill->getLinkURL() . "'>" . $bill->getName() . "</a>";
                    } else {
                        echo $bill->getName();
                    }
                }
                echo "</td>";
                echo "<td align='center'>";
                echo Dropdown::getDropdownName(
                    getTableForItemType('PluginOrderBillState'),
                    (int) $data['plugin_order_billstates_id'],
                );
                echo "</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        if ($canedit) {
            echo "<div class='center'>";
            echo "<table width='950px' class='tab_cadre_fixe left'>";
            echo "<tr><td><i class='fas fa-level-up-alt fa-flip-horizontal fa-lg mx-2'></i></td><td class='center'>";
            echo "<a onclick= \"if ( markCheckboxes('bills_form$rand') ) " .
              "return false;\" href='#'>" . __("Check all") . "</a></td>";

            echo "<td>/</td><td class='center'>";
            echo "<a onclick= \"if ( unMarkCheckboxes('bills_form$rand') ) " .
              "return false;\" href='#'>" . __("Uncheck all") . "</a>";
            echo "</td><td align='left' width='80%'>";
            echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
            $this->dropdownBillItemsActions($order->getID());
            echo "</td>";
            echo "</table>";
            echo "</div>";
        }
        Html::closeForm();
    }

    public function dropdownBillItemsActions($orders_id)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $action['']     = Dropdown::EMPTY_VALUE;
        $action['bill'] = __("Bill", "order");
        $rand           = Dropdown::showFromArray('chooseAction', $action);

        Ajax::updateItemOnSelectEvent(
            "dropdown_chooseAction$rand",
            "show_billsActions$rand",
            $CFG_GLPI['root_doc'] . "/plugins/order/ajax/billactions.php",
            [
                'action'                 => '__VALUE__',
                'plugin_order_orders_id' => $orders_id,
            ],
        );
        echo "<span id='show_billsActions$rand'>&nbsp;</span>";
    }


    public function updateQuantity($post)
    {
        /** @var \DBmysql $DB */
        global $DB;
        $quantity = $this->getTotalQuantityByRefAndDiscount(
            $post['plugin_order_orders_id'],
            $post['old_plugin_order_references_id'],
            $post['old_price_taxfree'],
            $post['old_discount'],
        );

        if ($post['quantity'] > $quantity) {
            $iterator = $this->queryRef(
                $post['plugin_order_orders_id'],
                $post['old_plugin_order_references_id'],
                $post['old_price_taxfree'],
                $post['old_discount'],
            );
            $item = $iterator->current();

            $this->getFromDB((int) $item['id']);
            $to_add  = $post['quantity'] - $quantity;
            $this->addDetails(
                $this->fields['plugin_order_references_id'],
                $this->fields['itemtype'],
                $this->fields['plugin_order_orders_id'],
                $to_add,
                $this->fields['price_taxfree'],
                $this->fields['discount'],
                $this->fields['plugin_order_ordertaxes_id'],
                $this->fields['plugin_order_analyticnatures_id'],
            );
        }
    }

    public function updateAnalyticNature($post)
    {
        $this->getFromDB($post['item_id']);

        $input = $this->fields;
        $input['plugin_order_analyticnatures_id'] = $post['plugin_order_analyticnatures_id'];
        $this->update($input);
    }

    public function updatePrice_taxfree($post)
    {
        $this->getFromDB($post['item_id']);

        $input = $this->fields;
        $discount                   = $input['discount'];
        $plugin_order_ordertaxes_id = $input['plugin_order_ordertaxes_id'];

        $input["price_taxfree"]     = $post['price_taxfree'];
        $input["price_discounted"]  = $input["price_taxfree"] - ($input["price_taxfree"] * ($discount / 100));

        $tax = new PluginOrderOrderTax();
        $tax->getFromDB($plugin_order_ordertaxes_id);

        $input["price_ati"]         = $this->getPricesATI($input["price_discounted"], $tax->getRate());

        $this->update($input);
    }


    public function updateDiscount($post)
    {
        $this->getFromDB($post['item_id']);

        $input                        = $this->fields;
        $plugin_order_ordertaxes_id   = $input['plugin_order_ordertaxes_id'];

        $input["discount"]            = $post['discount'];
        $input["price_discounted"]    = $post['price'] - ($post['price'] * ($input['discount'] / 100));

        $tax = new PluginOrderOrderTax();
        $tax->getFromDB($plugin_order_ordertaxes_id);

        $input["price_ati"]  = $this->getPricesATI($input["price_discounted"], $tax->getRate());

        $this->update($input);
    }


    public static function install(Migration $migration)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();

        if (!$DB->tableExists($table) && !$DB->tableExists("glpi_plugin_order_detail")) {
            $migration->displayMessage("Installing $table");

            //install
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
               `id` int {$default_key_sign} NOT NULL auto_increment,
               `entities_id` int {$default_key_sign} NOT NULL default '0',
               `is_recursive` tinyint NOT NULL default '0',
               `plugin_order_orders_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
               `itemtype` varchar(100) NOT NULL COMMENT 'see .class.php file',
               `items_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `plugin_order_references_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
               `plugin_order_deliverystates_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)',
               `plugin_order_ordertaxes_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
               `plugin_order_analyticnatures_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to plugin_order_analyticnatures (id)',
               `delivery_number` varchar(255) default NULL,
               `delivery_comment` text,
               `price_taxfree` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_discounted` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_ati` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `states_id` int {$default_key_sign} NOT NULL default 1,
               `delivery_date` date default NULL,
               `plugin_order_bills_id` INT {$default_key_sign} NOT NULL DEFAULT '0',
               `plugin_order_billstates_id` INT {$default_key_sign} NOT NULL DEFAULT '0',
               `comment` text,
               PRIMARY KEY  (`id`),
               KEY `FK_device` (`items_id`,`itemtype`),
               KEY `entities_id` (`entities_id`),
               KEY `item` (`itemtype`,`items_id`),
               KEY `plugin_order_references_id` (`plugin_order_references_id`),
               KEY `plugin_order_deliverystates_id` (`plugin_order_deliverystates_id`),
               KEY `plugin_order_analyticnatures_id` (`plugin_order_analyticnatures_id`),
               KEY `states_id` (`states_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.1.2
            if ($DB->tableExists("glpi_plugin_order_detail")) {
                $migration->addField(
                    "glpi_plugin_order_detail",
                    "delivery_status",
                    "int NOT NULL default '0'",
                );
                $migration->addField("glpi_plugin_order_detail", "delivery_comments", "TEXT");
                $migration->migrationOneTable("glpi_plugin_order_detail");
            }

            //1.2.0
            $migration->renameTable("glpi_plugin_order_detail", $table);

            $migration->changeField($table, "ID", "id", "int {$default_key_sign} NOT NULL AUTO_INCREMENT");
            $migration->changeField(
                $table,
                "FK_order",
                "plugin_order_orders_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'",
            );
            $migration->changeField(
                $table,
                "device_type",
                "itemtype",
                "varchar(100) NOT NULL COMMENT 'see .class.php file'",
            );
            $migration->changeField(
                $table,
                "FK_device",
                "items_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)'",
            );
            $migration->changeField(
                $table,
                "FK_reference",
                "plugin_order_references_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)'",
            );
            $migration->changeField(
                $table,
                "delivery_status",
                "plugin_order_deliverystates_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)'",
            );
            $migration->changeField(
                $table,
                "deliverynum",
                "delivery_number",
                "varchar(255) default NULL",
            );
            $migration->changeField(
                $table,
                "delivery_comments",
                "delivery_comment",
                "text",
            );
            $migration->changeField($table, "status", "states_id", "int {$default_key_sign} NOT NULL default 1");
            $migration->changeField($table, "date", "delivery_date", "date default NULL");
            $migration->addKey($table, ["items_id", "itemtype"], "FK_device");
            $migration->addKey($table, ["itemtype", "items_id"], "item");
            $migration->addKey($table, "plugin_order_references_id");
            $migration->addKey($table, "plugin_order_deliverystates_id");
            $migration->addKey($table, "states_id");
            $migration->migrationOneTable($table);

            //1.4.0
            $migration->addField(
                $table,
                "plugin_order_ordertaxes_id",
                "INT {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)'",
            );
            $migration->migrationOneTable($table);

            /* Migrate VAT */
            foreach ($DB->request(['FROM' => 'glpi_plugin_order_orders']) as $data) {
                $query  = "UPDATE `glpi_plugin_order_orders_items`
                       SET `plugin_order_ordertaxes_id` = '" . $data["plugin_order_ordertaxes_id"] . "'
                       WHERE `plugin_order_orders_id` = '" . $data["id"] . "'";
                $DB->doQuery($query);
            }
            //1.5.0
            $migration->addField($table, "entities_id", "INT {$default_key_sign} NOT NULL DEFAULT '0'");
            $migration->addField($table, "is_recursive", "TINYINT NOT NULL DEFAULT '0'");
            $migration->addField($table, "plugin_order_bills_id", "INT {$default_key_sign} NOT NULL DEFAULT '0'");
            $migration->addField($table, "plugin_order_billstates_id", "INT {$default_key_sign} NOT NULL DEFAULT '0'");
            $migration->addKey($table, "entities_id");
            $migration->addKey($table, "plugin_order_bills_id");
            $migration->addKey($table, "plugin_order_billstates_id");
            $migration->addField($table, "comment", "text");
            $migration->migrationOneTable($table);

            //Change format for prices : from float to decimal
            $migration->changeField(
                $table,
                "price_taxfree",
                "price_taxfree",
                "decimal(20,6) NOT NULL DEFAULT '0.000000'",
            );
            $migration->changeField(
                $table,
                "price_discounted",
                "price_discounted",
                "decimal(20,6) NOT NULL DEFAULT '0.000000'",
            );
            $migration->changeField(
                $table,
                "price_ati",
                "price_ati",
                "decimal(20,6) NOT NULL DEFAULT '0.000000'",
            );
            $migration->changeField(
                $table,
                "discount",
                "discount",
                "decimal(20,6) NOT NULL DEFAULT '0.000000'",
            );

            //Drop unused fields from previous migration
            $migration->dropField($table, "price_taxfree2");
            $migration->dropField($table, "price_discounted2");
            $migration->migrationOneTable($table);

            //Forward entities_id and is_recursive into table glpi_plugin_order_orders_items
            $query = [
                'SELECT' => [
                    'go.entities_id as entities_id',
                    'go.is_recursive as is_recursive',
                    'goi.id as items_id',
                ],
                'FROM' => [
                    'glpi_plugin_order_orders as go',
                    "$table as goi",
                ],
                'WHERE' => [
                    'goi.plugin_order_orders_id' => 'go.id',
                ],
            ];
            foreach ($DB->request($query) as $data) {
                $update = "UPDATE `$table`
                       SET `entities_id`='" . $data['entities_id'] . "'
                          AND `is_recursive`='" . $data['is_recursive'] . "'
                       WHERE `id`='" . $data['items_id'] . "'";
                $DB->doQuery($update);
            }

            if (!$DB->fieldExists($table, 'plugin_order_analyticnatures_id')) {
                $migration->addField($table, 'plugin_order_analyticnatures_id', "INT {$default_key_sign} NOT NULL DEFAULT '0'", ['after' => 'plugin_order_ordertaxes_id']);
                $migration->migrationOneTable($table);
            }
            if (!$DB->fieldExists($table, 'immo_number')) {
                $migration->addField($table, "immo_number", "varchar(255) default NULL");
            }

            $migration->executeMigration();
        }
    }


    public static function uninstall()
    {
        /** @var \DBmysql $DB */
        global $DB;

        //Old table name
        $DB->doQuery("DROP TABLE IF EXISTS `glpi_plugin_order_detail`");
        //Current table name
        $DB->doQuery("DROP TABLE IF EXISTS  `" . self::getTable() . "`");
        self::uninstallOrderItemNotification();
    }


    public static function countForOrder(PluginOrderOrder $item)
    {
        return countElementsInTable(
            'glpi_plugin_order_orders_items',
            ['plugin_order_orders_id' => $item->getID()],
        );
    }


    public static function countForItem(CommonDBTM $item)
    {
        return countElementsInTable(
            'glpi_plugin_order_orders_items',
            [
                'itemtype' => $item->getType(),
                'items_id' => $item->getID(),
            ],
        );
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        /** @var CommonDBTM $item */
        if (in_array(get_class($item), PluginOrderOrder_Item::getClasses(true))) {
            $orderlink = new PluginOrderLink();
            if (!$orderlink->isItemLinkedToOrder(get_class($item), $item->getField('id'))) {
                return '';
            }

            if ($item->getField('id') && !$withtemplate) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        __("Orders", "order"),
                        self::countForItem($item),
                    );
                }
                return __("Orders", "order");
            }
        } elseif (get_class($item) == 'PluginOrderOrder') {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return self::createTabEntry(
                    __("Order item", "order"),
                    self::countForOrder($item),
                    null,
                    self::getIcon(),
                );
            }
            return __("Order", "order");
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof PluginOrderOrder) {
            if (!$item->fields['is_template']) {
                $order_item = new self();
                $order_item->showItem($item->getID());
            }
        } elseif (
            is_subclass_of($item, CommonDBTM::class)
            && in_array($item->getType(), PluginOrderOrder_Item::getClasses(true))
        ) {
            $order_item = new self();
            $order_item->showPluginFromItems(get_class($item), $item->fields['id']);
        }
        return true;
    }


    public static function showForInfocom(CommonDBTM $item)
    {
        $order_item = new self();
        $order_item->showPluginFromItems(get_class($item), $item->fields['id']);

        return $item;
    }


    public static function uninstallOrderItemNotification()
    {
        /** @var \DBmysql $DB */
        global $DB;

        $notif   = new Notification();
        $options = [
            'itemtype' => 'PluginOrderOrder_Item',
            'event'    => 'delivered',
            'FIELDS'   => 'id',
        ];
        foreach ($DB->request(['FROM' => 'glpi_notifications'], $options) as $data) {
            $notif->delete($data);
        }

        $template    = new NotificationTemplate();
        $translation = new NotificationTemplateTranslation();

        //templates
        $options = [
            'itemtype' => 'PluginOrderOrder_Item',
            'FIELDS'   => 'id',
        ];
        foreach ($DB->request(['FROM' => 'glpi_notificationtemplates'], $options) as $data) {
            $options_template = [
                'notificationtemplates_id' => $data['id'],
                'FIELDS'                   => 'id',
            ];
            foreach (
                $DB->request(
                    ['FROM' => 'glpi_notificationtemplatetranslations'],
                    $options_template,
                ) as $data_template
            ) {
                $translation->delete($data_template);
            }
            $template->delete($data);
        }
    }

    /**
     * Returns manufacturer's reference number.
     *
     * @param integer $reference_id
     * @return string
     */
    protected function getManufacturersReference($reference_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        if (empty($reference_id)) {
            return '';
        }

        $result = $DB->request([
            'SELECT' => 'manufacturers_reference',
            'FROM'   => 'glpi_plugin_order_references',
            'WHERE'  => ['id' => $reference_id],
        ]);

        $data = $result->current();
        return $data['manufacturers_reference'] ?? '';
    }
}
