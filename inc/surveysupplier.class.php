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

class PluginOrderSurveySupplier extends CommonDBChild
{
    public static $rightname = 'plugin_order_order';

    public static $itemtype  = 'PluginOrderOrder';

    public static $items_id  = 'plugin_order_orders_id';


    public static function getTypeName($nb = 0)
    {
        return __("Supplier quality", "order");
    }


    public function prepareInputForAdd($input)
    {
       // Not attached to reference -> not added
        if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
            return false;
        }
        return $input;
    }


    public function getFromDBByOrder($plugin_order_orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'FROM' => $table,
            'WHERE' => ['plugin_order_orders_id' => $plugin_order_orders_id]
        ];

        $iterator = $DB->request($criteria);
        if (count($iterator) != 1) {
            return false;
        }
        $this->fields = $iterator->current();
        if (is_array($this->fields) && count($this->fields)) {
            return true;
        } else {
            return false;
        }
    }


    public function addNotation($field, $value)
    {

        $rand = mt_rand();

        echo "<table style='font-size:0.9em; width:50%' class='tab_format'>";
        echo "<tr>";
        echo "<td>";
        echo "<select id='$field$rand' name='$field'>";
        for ($i = 0; $i <= 5; $i++) {
            echo "<option value='$i' " . (($i == $value) ? 'selected' : '') .
            ">$i</option>";
        }
        echo "</select>";
        echo "<div class='rateit' id='notation$rand'></div>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";

        echo  "<script type='text/javascript'>\n";
        echo "$('#notation$rand').rateit({value: '" . $value . "',
                                min : 0,
                                max : 5,
                                step: 1,
                                backingfld: '#$field$rand',
                                ispreset: true,
                                resetable: false});";
        echo "</script>";
    }


    public function getTotalNotation($plugin_order_orders_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'SELECT' => ['(answer1 + answer2 + answer3 + answer4 + answer5) AS total'],
            'FROM' => $table,
            'WHERE' => ['plugin_order_orders_id' => $plugin_order_orders_id]
        ];
        $iterator = $DB->request($criteria);
        if (count($iterator)) {
            return $iterator->current()["total"] / 5;
        } else {
            return 0;
        }
    }


    public function getNotation($suppliers_id, $field)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table = self::getTable();
        $criteria = [
            'SELECT' => [
                "SUM(survey.$field) AS total",
                "COUNT(survey.id) AS nb"
            ],
            'FROM' => ["glpi_plugin_order_orders AS orders", "$table AS survey"],
            'WHERE' => [
                'survey.suppliers_id' => 'orders.suppliers_id',
                'survey.plugin_order_orders_id' => 'orders.id',
                'orders.suppliers_id' => $suppliers_id
            ] + getEntitiesRestrictCriteria('orders', 'entities_id', '', true)
        ];
        $iterator = $DB->request($criteria);

        if (count($iterator)) {
            $result = $iterator->current();
            return $result["total"] / $result["nb"];
        } else {
            return 0;
        }
    }


    public static function showGlobalNotation($suppliers_id)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $config = PluginOrderConfig::getConfig();
        if (!$config->canUseSupplierSatisfaction()) {
            return;
        }

        $survey       = new self();
        $survey_table = $survey->getTable();

        $restrict = getEntitiesRestrictRequest(" AND ", "orders", "entities_id", '', true);

        $criteria = [
            'SELECT' => [
                'orders.id',
                'orders.entities_id',
                'orders.name',
                'survey.comment'
            ],
            'FROM' => [
                'glpi_plugin_order_orders AS orders',
                "$survey_table AS survey"
            ],
            'WHERE' => [
                'survey.suppliers_id' => 'orders.suppliers_id',
                'survey.plugin_order_orders_id' => 'orders.id',
                'orders.suppliers_id' => $suppliers_id
            ] + getEntitiesRestrictCriteria('orders', 'entities_id', '', true),
            'GROUPBY' => 'survey.id'
        ];
        $iterator = $DB->request($criteria);
        $nb       = count($iterator);
        $total    = 0;
        $nb_order = 0;

        echo "<br>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr>";
        echo "<th colspan='4'>" . __("Supplier quality", "order") . "</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<th>" . __("Entity") . "</th>";
        echo "<th>" . __("Order name", "order") . "</th>";
        echo "<th>" . __("Note", "order") . "</th>";
        echo "<th>" . __("Comment on survey", "order") . "</th>";
        echo "</tr>";

        if ($nb) {
            foreach ($iterator as $row) {
                $name        = $row["name"];
                $ID          = $row["id"];
                $comment     = $row["comment"];
                $entities_id = $row["entities_id"];
                $note        = $survey->getTotalNotation($ID);
                echo "<tr class='tab_bg_1'>";
                echo "<td>";
                echo Dropdown::getDropdownName("glpi_entities", $entities_id);
                echo "</td>";
                $link = Toolbox::getItemTypeFormURL('PluginOrderOrder');
                echo "<td><a href=\"" . $link . "?id=" . $ID . "\">" . $name . "</a></td>";
                echo "<td>" . $note . " / 5</td>";
                echo "<td>" . nl2br($comment) . "</td>";
                echo "</tr>";
                $total += $survey->getTotalNotation($ID);
                $nb_order++;
            }
            echo "<tr>";
            echo "<th colspan='4'>&nbsp;</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" .
              __("Administrative followup quality (contracts, bills, mail, etc.)", "order") .
              "</div></td>";
            echo "<td><div align='left'>" .
              Html::formatNumber($survey->getNotation($suppliers_id, "answer1")) .
              "&nbsp;/ 5</div></td>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" .
              __("Commercial followup quality, visits, responseness", "order") . "</div></td>";
            echo "<td><div align='left'>" .
              Html::formatNumber($survey->getNotation($suppliers_id, "answer2")) .
              "&nbsp;/ 5</div></td>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" . __("Contacts availability", "order") . "</div></td>";
            echo "<td><div align='left'>" .
              Html::formatNumber($survey->getNotation($suppliers_id, "answer3")) .
              "&nbsp;/ 5</div></td>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" .
              __("Quality of supplier intervention", "order") . "</div></td>";
            echo "<td><div align='left'>" .
              Html::formatNumber($survey->getNotation($suppliers_id, "answer4")) .
              "&nbsp;/ 5</div></td>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" . __("Reliability about annouced delays", "order") .
              "</div></td>";
            echo "<td><div align='left'>" .
              Html::formatNumber($survey->getNotation($suppliers_id, "answer5")) .
              "&nbsp;/ 5</div></td>";

            echo "<tr>";
            echo "<th colspan='4'>&nbsp;</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1 b'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" . __("Final supplier note", "order") . "</div></td>";
            echo "<td><div align='left'>" . Html::formatNumber($total / $nb_order) . "&nbsp;/ 5</div></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }


    public function showForm($ID, $options = [])
    {
        if (!self::canView()) {
            return false;
        }

        $plugin_order_orders_id = -1;
        if (isset($options['plugin_order_orders_id'])) {
            $plugin_order_orders_id = $options['plugin_order_orders_id'];
        }

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
           // Create item
            $input = [
                'plugin_order_orders_id' => $options['plugin_order_orders_id']
            ];
            $this->check(-1, UPDATE, $input);
        }

        $this->initForm($ID, $options);

        $options['colspan'] = 1;
        $this->showFormHeader($options);

        $order = new PluginOrderOrder();
        $order->getFromDB($plugin_order_orders_id);
        echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
        echo Html::hidden('entities_id', ['value' => $order->getEntityID()]);
        echo Html::hidden('is_recursive', ['value' => $order->isRecursive()]);

        echo "<tr class='tab_bg_1'><td>" . __("Supplier") . ": </td><td>";
        $suppliers_id = $order->fields["suppliers_id"];
        if ($ID > 0) {
            $suppliers_id = $this->fields["suppliers_id"];
        }
        $link = Toolbox::getItemTypeFormURL('Supplier');
        echo "<a href=\"" . $link . "?id=" . $suppliers_id . "\">" .
         Dropdown::getDropdownName("glpi_suppliers", $suppliers_id) . "</a>";
        echo Html::hidden('suppliers_id', ['value' => $suppliers_id]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>" .
           __("Administrative followup quality (contracts, bills, mail, etc.)", "order") .
           ": </td><td>";
        $this->addNotation("answer1", $this->fields["answer1"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>" .
           __("Commercial followup quality, visits, responseness", "order") . ": </td><td>";
        $this->addNotation("answer2", $this->fields["answer2"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>" . __("Contacts availability", "order") . ": </td><td>";
        $this->addNotation("answer3", $this->fields["answer3"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>" .
           __("Quality of supplier intervention", "order") . ": </td><td>";
        $this->addNotation("answer4", $this->fields["answer4"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>" .
           __("Reliability about annouced delays", "order") . ": </td><td>";
        $this->addNotation("answer5", $this->fields["answer5"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'><td>";
       //comments of order
        echo __("Comments") . ": </td>";
        echo "<td>";
        echo "<textarea cols='80' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
        echo "</td>";
        echo "</tr>";

        if ($ID > 0) {
            echo "<tr><th><div align='left'>" . __("Average mark up to 5 (X points / 5)", "order")
            . ": </div></th><th><div align='left'>";
            $total = $this->getTotalNotation($this->fields["plugin_order_orders_id"]);
            echo Html::formatNumber($total) . " / 5";
            echo "</div></th>";
            echo "</tr>";
        }

        $this->showFormButtons($options);
        return true;
    }


    public static function showOrderSupplierSurvey($ID)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $order = new PluginOrderOrder();
        $order->getFromDB($ID);

        $survey = new self();

        $table = self::getTable();
        Session::initNavigateListItems(__CLASS__, __("Order", "order") . " = " . $order->fields["name"]);

        $candelete = $order->can($ID, DELETE);
        $criteria = [
            'FROM' => $table,
            'WHERE' => ['plugin_order_orders_id' => $ID]
        ];
        $iterator = $DB->request($criteria);
        $rand      = mt_rand();
        echo "<div class='center'>";
        echo "<form method='post' name='show_suppliersurvey$rand' id='show_suppliersurvey$rand' " .
            " action=\"" . Toolbox::getItemTypeFormURL(__CLASS__) . "\">";
        echo Html::hidden('plugin_order_orders_id', ['value' => $ID]);
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='5'>" . __("Supplier quality", "order") . "</th></tr>";
        echo "<tr><th>&nbsp;</th>";
        echo "<th>" . __("Supplier") . "</th>";
        echo "<th>" . __("Note", "order") . "</th>";
        echo "<th>" . __("Comment on survey", "order") . "</th>";
        echo "</tr>";

        if (count($iterator) > 0) {
            foreach ($iterator as $data) {
                Session::addToNavigateListItems(__CLASS__, (int) $data['id']);
                echo Html::hidden("item[" . $data["id"] . "]", ['value' => $ID]);
                echo "<tr class='tab_bg_1 center'>";
                echo "<td>";
                if ($candelete) {
                    echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
                    if (isset($_POST['check']) && $_POST['check'] == 'all') {
                        echo " checked ";
                    }
                    echo ">";
                }
                echo "</td>";
                $link = Toolbox::getItemTypeFormURL(__CLASS__);
                echo "<td><a href='" . $link . "?id=" . $data["id"] . "&plugin_order_orders_id=" . $ID . "'>"
                . Dropdown::getDropdownName("glpi_suppliers", (int) $data["suppliers_id"]) . "</a></td>";
                echo "<td>";
                $total = $survey->getTotalNotation($ID);
                echo $total . " / 5";
                echo "</td>";
                echo "<td>";
                echo $data["comment"];
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";

            if ($candelete) {
                echo "<div class='center'>";
                $formname = 'show_suppliersurvey' . $rand;
                echo "<table width='950px'>";
                $arrow = "fas fa-level-up-alt";

                echo "<tr>";
                echo "<td><i class='$arrow fa-flip-horizontal fa-lg mx-2'></i></td>";
                echo "<td class='center' style='white-space:nowrap;'>";
                echo "<a onclick= \"if ( markCheckboxes('$formname') ) return false;\" href='#'>" . __('Check all') . "</a></td>";
                echo "<td>/</td>";
                echo "<td class='center' style='white-space:nowrap;'>";
                echo "<a onclick= \"if ( unMarkCheckboxes('$formname') ) return false;\" href='#'>" . __('Uncheck all') . "</a></td>";
                echo "<td class='left' width='80%'>";

                echo "<input type='submit' name='delete' ";
                echo "value=\"" . addslashes(_sx('button', 'Delete permanently')) . "\" class='btn btn-primary'>&nbsp;";
                echo "</td></tr>";
                echo "</table>";
                echo "</div>";
            }
        } else {
            echo "</table>";
        }

        Html::closeForm();
        echo "</div>";
    }


    public function checkIfSupplierSurveyExists($orders_id)
    {
        if ($orders_id) {
            return (countElementsInTable(self::getTable(), ['plugin_order_orders_id' => $orders_id]));
        } else {
            return false;
        }
    }


    public static function install(Migration $migration)
    {
        /** @var \DBmysql $DB */
        global $DB;
       //Only avaiable since 1.3.0

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();
        if (!$DB->tableExists("glpi_plugin_order_surveysuppliers")) {
            $migration->displayMessage("Installing $table");

           //Installation
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_surveysuppliers` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `entities_id` int {$default_key_sign} NOT NULL default '0',
                  `is_recursive` tinyint NOT NULL default '0',
                  `plugin_order_orders_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
                  `suppliers_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                  `answer1` int NOT NULL default 0,
                  `answer2` int NOT NULL default 0,
                  `answer3` int NOT NULL default 0,
                  `answer4` int NOT NULL default 0,
                  `answer5` int NOT NULL default 0,
                  `comment` text,
                  PRIMARY KEY  (`id`),
                  KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
                  KEY `entities_id` (`entities_id`),
                  KEY `suppliers_id` (`suppliers_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query) or die($DB->error());
        } else {
           //upgrade
            $migration->displayMessage("Upgrading $table");

           //1.2.0
            $migration->changeField($table, "ID", "id", "int {$default_key_sign} NOT NULL auto_increment");
            $migration->changeField(
                $table,
                "FK_order",
                "plugin_order_orders_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'"
            );
            $migration->changeField(
                $table,
                "FK_enterprise",
                "suppliers_id",
                "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'"
            );
            $migration->changeField(
                $table,
                "comment",
                "comment",
                "text"
            );
            $migration->addField($table, "entities_id", "int {$default_key_sign} NOT NULL default '0'");
            $migration->addField($table, "is_recursive", "tinyint NOT NULL default '0'");
            $migration->addKey($table, "plugin_order_orders_id");
            $migration->addKey($table, "suppliers_id");
            $migration->migrationOneTable($table);

            $query = "SELECT `suppliers_id`, `entities_id`,`is_recursive`,`id`
                   FROM `glpi_plugin_order_orders` ";
            foreach ($DB->request($query) as $data) {
                $query = "UPDATE `glpi_plugin_order_surveysuppliers` SET
                        `entities_id` = '{$data["entities_id"]}',
                        `is_recursive` = '{$data["is_recursive"]}'
                      WHERE `plugin_order_orders_id` = '{$data["id"]}' ";
                $DB->doQuery($query) or die($DB->error());
            }
        }
    }


    public static function uninstall()
    {
        /** @var \DBmysql $DB */
        global $DB;

       //Current table name
        $DB->doQuery("DROP TABLE IF EXISTS  `" . self::getTable() . "`") or die($DB->error());
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof PluginOrderOrder) {
            $config = PluginOrderConfig::getConfig();
            if (
                $config->canUseSupplierSatisfaction()
                && $item->getState() == PluginOrderOrderState::DELIVERED
            ) {
                return __("Supplier quality", "order");
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof PluginOrderOrder) {
            $survey = new self();
            self::showOrderSupplierSurvey($item->getID());
            if (
                !$survey->checkIfSupplierSurveyExists($item->getID())
                && $item->can($item->getID(), UPDATE)
            ) {
                $survey->showForm(-1, ['plugin_order_orders_id' => $item->getID()]);
            }
        }

        return true;
    }
}
