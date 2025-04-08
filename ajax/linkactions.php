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

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
    die("Can not acces directly to this file");
}

$reference = new PluginOrderReference();

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "generation":
            echo Html::hidden(
                'plugin_order_references_id',
                ['value' => $_POST["plugin_order_references_id"]]
            );
            echo "&nbsp;";
            echo Html::submit(_sx('button', 'Post'), ['name' => 'generation']);
            break;

        case "createLink":
            echo Html::hidden('itemtype', ['value' => $_POST["itemtype"]]);
            echo Html::hidden(
                'plugin_order_orders_id',
                ['value' => $_POST["plugin_order_orders_id"]]
            );

            $reference->getFromDB($_POST["plugin_order_references_id"]);
            $reference->dropdownAllItemsByType(
                "items_id",
                $_POST["itemtype"],
                $_SESSION["glpiactiveentities"],
                $reference->fields["types_id"],
                $reference->fields["models_id"]
            );
            echo "&nbsp;";
            echo Html::submit(_sx('button', 'Post'), ['name' => 'createLinkWithItem']);

            break;

        case "deleteLink":
            echo "&nbsp;";
            echo Html::submit(_sx('button', 'Post'), ['name' => 'deleteLinkWithItem']);
            break;

        case "show_location_by_entity":
            Location::dropdown(['name'   => "id[" . $_POST['id'] . "][locations_id]",
                'value'  => $_POST['value'] ?? 0,
                'entity' => $_POST['entities']
            ]);

            if (isset($_POST['massaction'])) {
                $order_web_dir = Plugin::getWebDir('order');
                Ajax::updateItemOnSelectEvent(
                    $_POST['id'] . "[entities_id]",
                    "show_location_by_entity_id_" . $_POST['id'],
                    "$order_web_dir/ajax/linkactions.php",
                    ['entities' => '__VALUE__',
                        'action'   => 'show_location_by_entity',
                        'id'       => $_POST['id']
                    ]
                );
            }
            break;

        case "show_group_by_entity":
            Group::dropdown(['name'      => "id[" . $_POST['id'] . "][groups_id]",
                'entity'    => $_POST['entities'],
                'value'  => $_POST['value'] ?? 0,
                'condition' => ['is_assign' => 1],
            ]);

            if (isset($_POST['massaction'])) {
                $order_web_dir = Plugin::getWebDir('order');
                Ajax::updateItemOnSelectEvent(
                    $_POST['id'] . "[entities_id]",
                    "show_group_by_entity_id_" . $_POST['id'],
                    "$order_web_dir/ajax/linkactions.php",
                    ['entities' => '__VALUE__',
                        'action'   => 'show_group_by_entity',
                        'id'       => $_POST['id']
                    ]
                );
            }
            break;

        case "show_state_by_entity":
            $condition = PluginOrderLink::getCondition($_POST["itemtype"] ?? "");
            State::dropdown(['name'      => "id[" . $_POST['id'] . "][states_id]",
                'entity'    => $_POST['entities'],
                'value'  => $_POST['value'] ?? 0,
                'condition' => $condition
            ]);

            if (isset($_POST['massaction'])) {
                $order_web_dir = Plugin::getWebDir('order');
                Ajax::updateItemOnSelectEvent(
                    $_POST['id'] . "[entities_id]",
                    "show_state_by_entity_id_" . $_POST['id'],
                    "$order_web_dir/ajax/linkactions.php",
                    ['entities' => '__VALUE__',
                        'action'   => 'show_state_by_entity',
                        'id'       => $_POST['id']
                    ]
                );
            }
            break;

        case "update_entity":
            Entity::dropdown(['name'      => "id[" . $_POST['id'] . "][entities_id]",
                'value'     => $_POST['value'],
                'on_change' => "plugin_order_adaptOnSelectedEntity(" . $_POST['id'] . ", this.value);",
            ]);

            $order_web_dir = Plugin::getWebDir('order');
            Ajax::updateItemOnSelectEvent(
                "dropdown_id[" . $_POST['id'] . "][entities_id]",
                "show_location_by_entity_id_" . $_POST['id'],
                "$order_web_dir/ajax/linkactions.php",
                ['entities' => '__VALUE__',
                    'action'   => 'show_location_by_entity',
                    'id'       => $_POST['id']
                ]
            );
            Ajax::updateItemOnSelectEvent(
                "dropdown_id[" . $_POST['id'] . "][entities_id]",
                "show_group_by_entity_id_" . $_POST['id'],
                "$order_web_dir/ajax/linkactions.php",
                ['entities' => '__VALUE__',
                    'action'   => 'show_group_by_entity',
                    'id'       => $_POST['id']
                ]
            );
            Ajax::updateItemOnSelectEvent(
                "dropdown_id[" . $_POST['id'] . "][entities_id]",
                "show_state_by_entity_id_" . $_POST['id'],
                "$order_web_dir/ajax/linkactions.php",
                ['entities' => '__VALUE__',
                    'action'   => 'show_state_by_entity',
                    'id'       => $_POST['id']
                ]
            );
            break;

        case "check_unicity":
            $itemtype = new $_POST['itemtype']();
            if (count($itemtype->find([$_POST['field'] => $_POST['field_value'], ])) > 0) {
                echo "false";
            } else {
                echo "true";
            }
    }
}
