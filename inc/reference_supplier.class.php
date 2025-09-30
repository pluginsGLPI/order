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



class PluginOrderReference_Supplier extends CommonDBChild // phpcs:ignore
{
    public static $rightname = 'plugin_order_reference';

    public static $itemtype  = 'PluginOrderReference';

    public static $items_id  = 'plugin_order_references_id';

    public $dohistory        = true;


    public static function getTypeName($nb = 0)
    {
        return __("Supplier for the reference", "order");
    }

    protected function computeFriendlyName()
    {
        $ref = new PluginOrderReference();
        $ref->getFromDB($this->fields['plugin_order_references_id']);
        return sprintf(__('Supplier for the reference "%1$s"'), $ref->getName());
    }


    public function getFromDBByReference($plugin_order_references_id)
    {
        /** @var DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'FROM' => $table,
            'WHERE' => [
                'plugin_order_references_id' => $plugin_order_references_id,
            ],
        ];
        $result = $DB->request($criteria);

        if (count($result) != 1) {
            return false;
        }
        $this->fields = $result->current();
        if (is_array($this->fields) && count($this->fields)) {
            return true;
        } else {
            return false;
        }
    }

    public function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id'            => 'common',
            'name'          => __('Supplier for the reference', 'order'),
        ];

        $tab[] = [
            'id'            => 1,
            'table'         => self::getTable(),
            'field'         => 'reference_code',
            'name'          => __('Manufacturer\'s product reference', 'order'),
            'datatype'      => 'text',
            'autocomplete'  => true,
        ];

        $tab[] = [
            'id'            => 2,
            'table'         => self::getTable(),
            'field'         => 'price_taxfree',
            'name'          => __('Unit price tax free', 'order'),
            'datatype'      => 'decimal',
        ];

        $tab[] = [
            'id'            => 3,
            'table'         => 'glpi_suppliers',
            'field'         => 'name',
            'name'          => __('Supplier'),
            'datatype'      => 'itemlink',
            'itemlink_type' => 'Supplier',
            'forcegroupby'  => true,
        ];

        $tab[] = [
            'id'            => 30,
            'table'         => self::getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
        ];

        $tab[] = [
            'id'            => 80,
            'table'         => 'glpi_entities',
            'field'         => 'completename',
            'name'          => __('Entity'),
        ];

        return $tab;
    }


    public function prepareInputForAdd($input)
    {
        // Not attached to reference -> not added
        if (
            !isset($input['plugin_order_references_id'])
            || $input['plugin_order_references_id'] <= 0
        ) {
            return false;
        }
        return $input;
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    /**
     * @return array|string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (get_class($item) == self::class) {
            return [1 => __("Main")];
        } elseif ($item instanceof PluginOrderReference) {
            return self::createTabEntry(
                __("Supplier Detail", "order"),
                0,
                null,
                self::getIcon(),
            );
        }
        return '';
    }

    public static function getIcon()
    {
        return 'ti ti-trolley';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $reference_supplier = new self();
        if ($item instanceof PluginOrderReference) {
            if ($item->can($item->getID(), UPDATE)) {
                $reference_supplier->showForm(0, ['plugin_order_references_id' => $item->getID()]);
            }
            $reference_supplier->showReferenceManufacturers($item->getID());
        }

        return true;
    }


    public function showForm($ID, $options = [])
    {
        /** @var DBmysql $DB */
        global $DB;

        $plugin_order_references_id = -1;
        if (isset($options['plugin_order_references_id'])) {
            $plugin_order_references_id = $options['plugin_order_references_id'];
        }

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        $PluginOrderReference = new PluginOrderReference();
        $PluginOrderReference->getFromDB($plugin_order_references_id);
        echo Html::hidden('plugin_order_references_id', ['value' => $plugin_order_references_id]);
        echo Html::hidden('entities_id', ['value' => $PluginOrderReference->getEntityID()]);
        echo Html::hidden('is_recursive', ['value' => (int) $PluginOrderReference->isRecursive()]);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Supplier") . ": </td>";
        echo "<td>";

        if ($ID > 0) {
            $supplier = new Supplier();
            $supplier->getFromDB($this->fields['suppliers_id']);
            echo $supplier->getLink();
        } else {
            $suppliers = [];
            $criteria = [
                'SELECT' => 'suppliers_id',
                'FROM' => self::getTable(),
                'WHERE' => [
                    'plugin_order_references_id' => $plugin_order_references_id,
                ],
            ];
            $result = $DB->request($criteria);
            foreach ($result as $data) {
                $suppliers[] = $data["suppliers_id"];
            }

            Supplier::Dropdown([
                'name'   => 'suppliers_id',
                'used'   => $suppliers,
                'entity' => $PluginOrderReference->getEntityID(),
            ]);
        }
        echo "</td>";

        echo "<td>" . __("Manufacturer's product reference", "order") . ": </td>";
        echo "<td>";
        echo Html::input(
            'reference_code',
            [
                'value' => $this->fields['reference_code'],
            ],
        );
        echo "</td></tr>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __("Unit price tax free", "order") . ": </td>";
        echo "<td>";
        echo "<input type='number' class='form-control' min='0' step='" . PLUGIN_ORDER_NUMBER_STEP . "' name='price_taxfree' value=\""
        . Html::formatNumber($this->fields["price_taxfree"], true) . "\" class='decimal'>";
        echo "</td>";

        echo "<td></td>";
        echo "<td></td>";

        echo "</tr>";

        $options['candel'] = false;
        $this->showFormButtons($options);
        return true;
    }


    public function showReferenceManufacturers($ID)
    {
        /** @var DBmysql $DB */
        global $DB;

        $ref = new PluginOrderReference();
        $ref->getFromDB($ID);

        $target = Toolbox::getItemTypeFormURL(self::class);
        Session::initNavigateListItems(
            $this->getType(),
            __("Product reference", "order") . " = " . $ref->fields["name"],
        );

        $candelete = $ref->can($ID, DELETE);
        $criteria  = [
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_order_references_id' => $ID,
            ] + getEntitiesRestrictCriteria(
                self::getTable(),
                "entities_id",
                $ref->fields['entities_id'],
                $ref->fields['is_recursive'],
            ),
        ];
        $result    = $DB->request($criteria);
        $rand      = mt_rand();
        echo "<div class='center'>";
        echo "<form method='post' name='show_supplierref$rand' id='show_supplierref$rand' action=\"$target\">";
        echo Html::hidden('plugin_order_references_id', ['value' => $ID]);
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='5'>" . __("Supplier Detail", "order") . "</th></tr>";
        echo "<tr><th>&nbsp;</th>";
        echo "<th>" . __("Supplier") . "</th>";
        echo "<th>" . __("Product reference", "order") . "</th>";
        echo "<th>" . __("Unit price tax free", "order") . "</th>";
        echo "</tr>";

        if (count($result) > 0) {
            echo "<form method='post' name='show_ref_manu' action=\"$target\">";
            echo Html::hidden('plugin_order_references_id', ['value' => $ID]);

            foreach ($result as $data) {
                Session::addToNavigateListItems($this->getType(), (int) $data['id']);
                echo Html::hidden("item[" . $data["id"] . "]", ['value' => $ID]);
                echo "<tr class='tab_bg_1 center'>";
                echo "<td>";
                if ($candelete) {
                    echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
                    if (
                        isset($_POST['check'])
                        && (
                            is_string($_POST['check'])
                            && $_POST['check'] == 'all'
                            || (
                                is_array($_POST['check']) && isset($_POST['check']['all'])
                            )
                        )
                    ) {
                        echo " checked ";
                    }
                    echo ">";
                }
                echo "</td>";

                $link = Toolbox::getItemTypeFormURL($this->getType());
                echo "<td><a href='" . $link . "?id=" . $data["id"] . "&plugin_order_references_id=" . $ID . "'>"
                . Dropdown::getDropdownName("glpi_suppliers", (int) $data["suppliers_id"]) . "</a></td>";
                echo "<td>";
                echo $data["reference_code"];
                echo "</td>";
                echo "<td>";
                echo Html::formatNumber((float) $data["price_taxfree"]);
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";

            if ($candelete) {
                echo "<div class='center'>";
                echo "<table width='900px' class='tab_glpi'>";
                echo "<tr><td><i class='fas fa-level-up-alt fa-flip-horizontal fa-lg mx-2'></i></td>";
                echo "<td class='center'><a onclick= \"if ( markCheckboxes('show_supplierref$rand') ) "
                . "return false;\" href='#'>" . __("Check all") . "</a></td>";

                echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('show_supplierref$rand') ) "
                . "return false;\" href='#'>" . __("Uncheck all") . "</a>";
                echo "</td><td align='left' width='80%'>";
                echo "<input type='submit' name='delete' value=\"" . __("Delete permanently")
                . "\" class='submit' >";
                echo "</td>";
                echo "</table>";
                echo "</div>";
            }
        } else {
            echo "</table>";
        }

        Html::closeForm();
        echo "</div>";
    }


    public function getReferenceCodeByReferenceAndSupplier($plugin_order_references_id, $suppliers_id)
    {
        /** @var DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'SELECT' => 'reference_code',
            'FROM' => $table,
            'WHERE' => [
                'plugin_order_references_id' => $plugin_order_references_id,
                'suppliers_id' => $suppliers_id,
            ],
        ];
        $result = $DB->request($criteria);

        if (count($result) > 0) {
            $row = $result->current();
            return $row["reference_code"];
        } else {
            return 0;
        }
    }


    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();
        if (!$DB->tableExists($table) && !$DB->tableExists("glpi_plugin_order_references_manufacturers")) {
            $migration->displayMessage("Installing $table");

            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_suppliers` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `entities_id` int {$default_key_sign} NOT NULL default '0',
                     `is_recursive` tinyint NOT NULL default '0',
                     `plugin_order_references_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
                     `suppliers_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                     `price_taxfree` decimal(20,6) NOT NULL DEFAULT '0.000000',
                     `reference_code` varchar(255) default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `entities_id` (`entities_id`),
                     KEY `plugin_order_references_id` (`plugin_order_references_id`),
                     KEY `suppliers_id` (`suppliers_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        } else {
            $migration->displayMessage("Upgrading $table");

            //1.1.0
            if ($DB->tableExists("glpi_plugin_order_references_manufacturers")) {
                $migration->addField(
                    "glpi_plugin_order_references_manufacturers",
                    "reference_code",
                    "varchar(255) NOT NULL default ''",
                );
                $migration->migrationOneTable("glpi_plugin_order_references_manufacturers");
            }

            //1.2.0
            $migration->renameTable("glpi_plugin_order_references_manufacturers", $table);
            $migration->addField($table, "is_recursive", "int {$default_key_sign} NOT NULL default '0'");
            $migration->addKey($table, "suppliers_id");
            $migration->addKey($table, "plugin_order_references_id");
            $migration->changeField(
                $table,
                "ID",
                "id",
                "int {$default_key_sign} NOT NULL auto_increment",
            );
            $migration->changeField(
                $table,
                "FK_entities",
                "entities_id",
                "int {$default_key_sign} NOT NULL default '0'",
            );
            $migration->changeField(
                $table,
                "FK_reference",
                "plugin_order_references_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)'",
            );
            $migration->changeField(
                $table,
                "FK_enterprise",
                "suppliers_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'",
            );
            $migration->changeField(
                $table,
                "reference_code",
                "reference_code",
                "varchar(255) default NULL",
            );
            $migration->changeField(
                $table,
                "price_taxfree",
                "price_taxfree",
                "decimal(20,6) NOT NULL DEFAULT '0.000000'",
            );
            $migration->migrationOneTable($table);

            //1.5.0
            $query = [
                'SELECT' => [
                    'entities_id',
                    'is_recursive',
                    'id',
                ],
                'FROM' => 'glpi_plugin_order_references',
            ];
            foreach ($DB->request($query) as $data) {
                $migration->addPostQuery(
                    $DB->buildUpdate(
                        'glpi_plugin_order_references_suppliers',
                        [
                            'entities_id' => $data['entities_id'],
                            'is_recursive' => $data['is_recursive'],
                        ],
                        ['plugin_order_references_id' => $data['id']],
                    ),
                );
            }
        }
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        //Old table name
        $DB->doQuery("DROP TABLE IF EXISTS `glpi_plugin_order_references_manufacturers`");

        //Current table name
        $DB->doQuery("DROP TABLE IF EXISTS  `" . self::getTable() . "`");
    }


    public static function showReferencesFromSupplier($ID)
    {
        /** @var DBmysql $DB */
        global $DB;

        $start = isset($_POST["start"]) ? (int) $_POST["start"] : 0;

        $criteria = [
            'SELECT' => [
                'gr.id',
                'gr.manufacturers_id',
                'gr.entities_id',
                'gr.itemtype',
                'gr.name',
                'grm.price_taxfree',
                'grm.reference_code',
            ],
            'FROM' => 'glpi_plugin_order_references_suppliers AS grm',
            'INNER JOIN' => [
                'glpi_plugin_order_references AS gr' => [
                    'ON' => [
                        'grm' => 'plugin_order_references_id',
                        'gr' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'grm.suppliers_id' => $ID,
            ] + getEntitiesRestrictCriteria("gr", '', '', false, true),
        ];

        $result = $DB->request($criteria);
        $nb     = count($result);
        echo "<div class='center'>";

        if ($nb !== 0) {
            // Add pagination
            $criteria_limit = $criteria;
            $criteria_limit['START'] = $start;
            $criteria_limit['LIMIT'] = (int) $_SESSION['glpilist_limit'];
            $result_limited = $DB->request($criteria_limit);

            Html::printAjaxPager(__("List references", "order"), $start, $nb);

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr>";
            echo "<th>" . __("Entity") . "</th>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "<th>" . __("Reference") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "<th>" . __("Unit price tax free", "order") . "</th></tr>";

            foreach ($result_limited as $data) {
                echo "<tr class='tab_bg_1' align='center'>";
                echo "<td>";
                echo Dropdown::getDropdownName("glpi_entities", (int) $data["entities_id"]);
                echo "</td>";

                echo "<td>";
                echo Dropdown::getDropdownName("glpi_manufacturers", (int) $data["manufacturers_id"]);
                echo "</td>";

                echo "<td>";
                $PluginOrderReference = new PluginOrderReference();
                echo $PluginOrderReference->getReceptionReferenceLink($data);
                echo "</td>";

                echo "<td>";
                $item = getItemForItemtype($data["itemtype"]);
                if ($item !== false) {
                    echo $item->getTypeName();
                } else {
                    echo $data["itemtype"];
                }
                echo "</td>";

                echo "<td>";
                echo $data['reference_code'];
                echo "</td>";

                echo "<td>";
                echo number_format((float) $data["price_taxfree"], 2);
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        echo "</div>";
    }
}
