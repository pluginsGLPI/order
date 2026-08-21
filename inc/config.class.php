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



class PluginOrderConfig extends CommonDBTM
{
    public static $rightname = 'config';

    public const CONFIG_NEVER   = 0;

    public const CONFIG_YES     = 1;

    public const CONFIG_ASK     = 2;


    public function __construct()
    {
        /** @var DBmysql $DB */
        global $DB;
        if ($DB->tableExists(self::getTable())) {
            $this->getFromDB(1);
        }
    }


    public static function canView(): bool
    {
        return Session::haveRight('config', READ);
    }


    public static function canCreate(): bool
    {
        return Session::haveRight('config', UPDATE);
    }


    public static function getConfig($update = false)
    {
        static $config = null;

        if (is_null($config)) {
            $config = new self();
        }

        if ($update) {
            $config->getFromDB(1);
        }

        return $config;
    }


    public static function getTypeName($nb = 0)
    {
        return __s("Orders management", "order");
    }


    public static function getMenuContent()
    {
        $menu  = parent::getMenuContent();
        $menu['page']   = PluginOrderMenu::getSearchURL(false);
        $menu['links']['add']    = null;
        $menu['links']['search'] = null;
        $menu['links']['config'] = self::getFormURL(false);

        return $menu;
    }


    public function showForm($ID, array $options = [])
    {
        $this->getFromDB($ID);

        echo "<div class='center'>";
        echo "<form name='form' method='post' action='" . $this->getFormURL() . "' enctype='multipart/form-data'>";

        echo Html::hidden('id', ['value' => 1]);

        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='2'>" . __s("Plugin configuration", "order") . "</th></tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Default VAT", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderTax::Dropdown([
            'name'                => "default_taxes",
            'value'               => $this->fields["default_taxes"],
            'display_emptychoice' => true,
            'emptylabel'          => __s("No VAT", "order"),
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Use validation process", "order") . "</td>";
        echo "<td>";
        Dropdown::showYesNo("use_validation", $this->fields["use_validation"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Order generation in ODT", "order") . "</td><td>";
        Dropdown::showYesNo("generate_order_pdf", $this->fields["generate_order_pdf"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Activate suppliers quality satisfaction", "order") . "</td>";
        echo "<td>";
        Dropdown::showYesNo("use_supplier_satisfaction", $this->fields["use_supplier_satisfaction"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Display order's suppliers informations", "order") . "</td><td>";
        Dropdown::showYesNo("use_supplier_informations", $this->fields["use_supplier_informations"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Color to be displayed when order due date is overtaken", "order") . "</td>";
        echo "<td>";
        echo "<input type='color' name='shoudbedelivered_color'
               value='" . $this->fields['shoudbedelivered_color'] . "'>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Copy order documents when a new item is created", "order") . "</td>";
        echo "<td>";
        Dropdown::showYesNo("copy_documents", $this->fields["copy_documents"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Default heading when adding a document to an order", "order") . "</td>";
        echo "<td>";
        DocumentCategory::Dropdown(['value' => $this->fields["documentcategories_id"]]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Author group", "order") . ' (' . __s("Default values") . ")</td>";
        echo "<td>";
        Group::Dropdown([
            'value' => $this->fields["groups_id_author"],
            'name'  => 'groups_id_author',
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Recipient group", "order") . ' (' . __s("Default values") . ")</td>";
        echo "<td>";
        Group::Dropdown([
            'value' => $this->fields["groups_id_recipient"],
            'name'  => 'groups_id_recipient',
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Recipient") . ' (' . __s("Default values") . ")</td>";
        echo "<td>";
        User::Dropdown([
            'name'   => 'users_id_recipient',
            'value'  => $this->fields["users_id_recipient"],
            'right'  => 'all',
            'entity' => 0,
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Hide inactive budgets", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("hide_inactive_budgets", $this->fields["hide_inactive_budgets"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Transmit budget change to linked assets", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("transmit_budget_change", $this->fields["transmit_budget_change"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Display account section on order form", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("order_accountsection_display", $this->fields["order_accountsection_display"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Set account section as mandatory on order form", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("order_accountsection_mandatory", $this->fields["order_accountsection_mandatory"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Use free references", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("use_free_reference", $this->fields["use_free_reference"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Rename documents added in order", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("rename_documents", $this->fields["rename_documents"]);
        echo "</td>";
        echo "</tr>";

        // Automatic actions
        echo "<tr class='tab_bg_1' align='center'>";
        echo "<th colspan='2'>" . __s("Automatic actions when delivery", "order") . "</th>";
        echo "</tr>";

        // ASSETS
        echo "<tr class='tab_bg_1' align='center'>";
        echo "<th colspan='2'>" . __s('Item') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Display analytic nature on item form", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("order_analyticnature_display", $this->fields["order_analyticnature_display"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Set analytic nature as mandatory on item form", 'order') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("order_analyticnature_mandatory", $this->fields["order_analyticnature_mandatory"]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1 center'>";
        echo "<td>" . __s("Enable automatic generation", "order") . "</td>";
        echo "<td>";
        Dropdown::showFromArray(
            'generate_assets',
            [self::CONFIG_NEVER => __s('No'),
                self::CONFIG_YES   => __s('Yes'),
                self::CONFIG_ASK   => __s('Asked', 'order'),
            ],
            ['value' => $this->canGenerateAsset()],
        );
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Default state", "order") . "</td>";
        echo "<td>";
        State::Dropdown([
            'name'   => 'default_asset_states_id',
            'value'  => $this->fields["default_asset_states_id"],
            'entity' => $_SESSION["glpiactiveentities"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Add order location to item", "order") . "</td>";
        echo "<td>";
        Dropdown::showYesNo("add_location", $this->canAddLocation());
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Add billing details to item", "order") . "</td><td>";
        Dropdown::showYesNo("add_bill_details", $this->canAddBillDetails());
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Add immobilization number to item", "order") . "</td><td>";
        Dropdown::showYesNo("add_immobilization_number", $this->canAddImmobilizationNumber());
        echo "</td>";
        echo "</tr>";

        if ($this->canGenerateAsset()) {
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>" . __s("Default name", "order") . "</td>";
            echo "<td>";
            echo Html::input(
                'generated_name',
                [
                    'value' => $this->fields['generated_name'],
                ],
            );
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>" . __s("Default serial number", "order") . "</td>";
            echo "<td>";
            echo Html::input(
                'generated_serial',
                [
                    'value' => $this->fields['generated_serial'],
                ],
            );
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>" . __s("Default inventory number", "order") . "</td>";
            echo "<td>";
            echo Html::input(
                'generated_otherserial',
                [
                    'value' => $this->fields['generated_otherserial'],
                ],
            );
            echo "</td>";
            echo "</tr>";

            // TICKETS
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<th colspan='2'>" . __s("Ticket") . "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>" . TicketTemplate::getTypeName(1) . "</td>";
            echo "<td>";
            Dropdown::show('TicketTemplate', [
                'name'  => 'tickettemplates_id_delivery',
                'value' => $this->fields['tickettemplates_id_delivery'],
            ]);
            echo "</td>";
            echo "</tr>";
        }

        /* Workflow */
        echo "<tr class='tab_bg_1' align='center'>";
        echo "<th colspan='2'>" . __s("Order lifecycle", "order") . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("State before validation", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_draft',
            'value'  => $this->fields["order_status_draft"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Waiting for validation state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_waiting_approval',
            'value'  => $this->fields["order_status_waiting_approval"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Validated order state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_approved',
            'value'  => $this->fields["order_status_approved"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Order being delivered state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_partially_delivred',
            'value'  => $this->fields["order_status_partially_delivred"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Order delivered state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_completly_delivered',
            'value'  => $this->fields["order_status_completly_delivered"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Order paied state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_paid',
            'value'  => $this->fields["order_status_paid"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Canceled order state", "order") . "</td>";
        echo "<td>";
        PluginOrderOrderState::Dropdown([
            'name'   => 'order_status_canceled',
            'value'  => $this->fields["order_status_canceled"],
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr><th colspan='2'>" . __s("Reminders for orders not invoiced", "order") . "</th></tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Reminder thresholds in days", "order")
           . "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Comma separated, for example 14,30,60 - leave empty to disable", "order")
           . "</span></td>";
        echo "<td>";
        echo Html::input('not_invoiced_reminder_days', [
            'value' => $this->fields['not_invoiced_reminder_days'] ?? '',
            'size'  => 30,
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Additional reminder recipients", "order")
           . "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Comma separated addresses, notified on top of the order author", "order")
           . "</span></td>";
        echo "<td>";
        echo Html::input('not_invoiced_reminder_emails', [
            'value' => $this->fields['not_invoiced_reminder_emails'] ?? '',
            'size'  => 50,
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr><th colspan='2'>" . __s("E-mail appearance", "order") . "</th></tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td>" . __s("Upload message header", "order")
           . "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Image prepended to every e-mail sent by the Order plugin", "order")
           . "</span></td>";
        echo "<td>";
        $header_path = $this->getMailHeaderPath();
        if ($header_path !== null) {
            echo "<img src='" . htmlescape($this->getMailHeaderWebPath())
               . "' alt='' style='max-width:320px;max-height:90px;display:block;margin:0 auto 6px;border:1px solid rgba(0,0,0,.15);padding:2px;background:#fff;'>";
            $size = @getimagesize($header_path);
            echo "<span class='text-muted' style='font-size:0.85em;display:block;margin-bottom:8px;'>"
               . htmlescape(sprintf(
                   '%s - %s - %s',
                   (string) $this->fields['mail_header_filename'],
                   $size !== false ? $size[0] . 'x' . $size[1] . ' px' : '?',
                   Html::convDateTime(date('Y-m-d H:i:s', filemtime($header_path))),
               ))
               . "</span>";
        } else {
            echo "<span class='text-muted' style='font-size:0.85em;display:block;margin-bottom:8px;'>"
               . __s("No header uploaded yet", "order")
               . "</span>";
        }
        echo "<input type='file' name='mail_header_file' accept='image/png,image/jpeg,image/gif,image/webp' class='form-control' style='max-width:320px;display:inline-block;'>";
        if ($header_path !== null) {
            echo "<br><label style='margin-top:6px;display:inline-block;'>"
               . "<input type='checkbox' name='_drop_mail_header' value='1' class='form-check-input'>&nbsp;"
               . __s("Remove current header", "order")
               . "</label>";
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' align='center'>";
        echo "<td colspan='2' align='center'>";
        echo "<input type='submit' name='update' value=\"" . _sx("button", "Post") . "\" class='btn btn-primary' >";
        echo"</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();

        $this->showGenerationFieldsSection();

        echo "</div>";

        return true;
    }


    /**
     * Mapping manager for the extra fields of the item generation form.
     *
     * Rendered outside the main configuration form: each mapping row and the
     * add row are small forms of their own (nested forms are invalid HTML).
     */
    private function showGenerationFieldsSection(): void
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $target = Toolbox::getItemTypeFormURL('PluginOrderGenerationField');
        $ajax   = $CFG_GLPI['root_doc'] . '/plugins/order/ajax/generationfields.php';

        echo "<table class='tab_cadre_fixe' style='margin-top:14px;'>";
        echo "<tr><th colspan='3'>" . __s("Generation extra fields", "order") . "</th></tr>";
        echo "<tr class='tab_bg_1'><td colspan='3' class='center'>"
           . "<span class='text-muted' style='font-size:0.9em;'>"
           . __s("Extra asset fields (for example an IMEI) shown as additional columns on the item generation form, per item type. Custom asset fields are supported.", "order")
           . "</span></td></tr>";

        $mappings = PluginOrderGenerationField::getAllMappings();
        if ($mappings === []) {
            echo "<tr class='tab_bg_1'><td colspan='3' class='center'>"
               . "<span class='text-muted'>" . __s("No extra field mapped yet", "order") . "</span>"
               . "</td></tr>";
        } else {
            echo "<tr class='tab_bg_2'>"
               . "<th>" . __s("Item type", "order") . "</th>"
               . "<th>" . __s("Field", "order") . "</th>"
               . "<th></th></tr>";
            foreach ($mappings as $row) {
                $type_label = is_a($row['itemtype'], CommonDBTM::class, true)
                    ? $row['itemtype']::getTypeName(1)
                    : $row['itemtype'];
                echo "<tr class='tab_bg_1'>";
                echo "<td>" . htmlescape($type_label)
                   . " <span class='text-muted' style='font-size:0.85em;'>(" . htmlescape($row['itemtype']) . ")</span></td>";
                echo "<td>" . htmlescape($row['label'])
                   . " <span class='text-muted' style='font-size:0.85em;'>(" . htmlescape($row['field']) . ")</span></td>";
                echo "<td class='center'>";
                echo "<form method='post' action='" . $target . "' style='display:inline;'>";
                echo Html::hidden('id', ['value' => $row['id']]);
                echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
                echo "<input type='submit' name='purge' value=\"" . _sx('button', 'Delete permanently') . "\" class='btn btn-sm btn-outline-danger'>";
                Html::closeForm();
                echo "</td></tr>";
            }
        }

        echo "<tr class='tab_bg_1'><td colspan='3'>";
        echo "<form method='post' action='" . $target . "' class='d-flex align-items-center justify-content-center' style='gap:10px;flex-wrap:wrap;'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<label>" . __s("Item type", "order") . "&nbsp;";
        echo "<select name='itemtype' id='generation_field_itemtype' class='form-select' style='display:inline-block;width:auto;'>";
        echo "<option value=''>-----</option>";
        foreach ($CFG_GLPI['plugin_order_types'] ?? [] as $order_type) {
            if (!is_a($order_type, CommonDBTM::class, true)) {
                continue;
            }
            echo "<option value='" . htmlescape($order_type) . "'>"
               . htmlescape($order_type::getTypeName(1)) . "</option>";
        }
        echo "</select></label>";
        echo "<label>" . __s("Field", "order") . "&nbsp;";
        echo "<select name='field' id='generation_field_field' class='form-select' style='display:inline-block;width:auto;min-width:220px;'>";
        echo "<option value=''>-----</option>";
        echo "</select></label>";
        echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
        Html::closeForm();
        echo "</td></tr>";
        echo "</table>";

        echo Html::scriptBlock("
            $('#generation_field_itemtype').on('change', function() {
                var field = $('#generation_field_field');
                field.html(\"<option value=''>-----</option>\");
                if (this.value === '') {
                    return;
                }
                $.get('" . $ajax . "', {itemtype: this.value}, function(html) {
                    field.html(html);
                });
            });
        ");
    }


    /**
     * Normalize the reminder settings so the stored value is always a clean,
     * ordered list and invalid entries are reported instead of silently kept.
     */
    public function prepareInputForUpdate($input)
    {
        if (isset($input['not_invoiced_reminder_days'])) {
            $input['not_invoiced_reminder_days'] = implode(
                ',',
                self::parseReminderDays((string) $input['not_invoiced_reminder_days']),
            );
        }

        if (isset($input['not_invoiced_reminder_emails'])) {
            $submitted = (string) $input['not_invoiced_reminder_emails'];
            $valid     = self::parseReminderEmails($submitted);

            $rejected = [];
            foreach (preg_split('/[,;]/', $submitted) ?: [] as $email) {
                $email = trim($email);
                if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rejected[] = $email;
                }
            }

            if ($rejected !== []) {
                Session::addMessageAfterRedirect(
                    sprintf(
                        __s("These addresses are not valid and were ignored: %s", "order"),
                        htmlescape(implode(', ', $rejected)),
                    ),
                    true,
                    ERROR,
                );
            }

            $input['not_invoiced_reminder_emails'] = implode(', ', $valid);
        }

        $input = $this->handleMailHeaderInput($input);

        return $input;
    }


    /**
     * Store, replace or drop the uploaded e-mail header image.
     *
     * The file lives under GLPI's files directory (not the plugin directory,
     * which is replaced on upgrade) and only its bare name is kept in the
     * configuration row.
     */
    private function handleMailHeaderInput(array $input): array
    {
        /** @var DBmysql $DB */
        global $DB;

        $dir = self::getMailHeaderDir();

        if (!empty($input['_drop_mail_header'])) {
            $current = $this->getMailHeaderPath();
            if ($current !== null) {
                @unlink($current);
            }
            $input['mail_header_filename'] = '';
            Session::addMessageAfterRedirect(__s("Message header removed", "order"), true, INFO);
        }

        $upload = $_FILES['mail_header_file'] ?? null;
        if ($upload === null || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $input;
        }

        // A stale schema would silently drop the filename from the update and
        // leave the upload invisible - refuse loudly instead.
        if (!$DB->fieldExists(self::getTable(), 'mail_header_filename')) {
            Session::addMessageAfterRedirect(
                __s("The plugin database is outdated: run the plugin update, then upload the header again", "order"),
                true,
                ERROR,
            );
            return $input;
        }

        if ($upload['error'] !== UPLOAD_ERR_OK) {
            Session::addMessageAfterRedirect(__s("The header image could not be uploaded", "order"), true, ERROR);
            return $input;
        }

        if ((int) $upload['size'] > 2 * 1024 * 1024) {
            Session::addMessageAfterRedirect(__s("The header image is too large (max 2 MB)", "order"), true, ERROR);
            return $input;
        }

        $mime_to_ext = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $info = @getimagesize($upload['tmp_name']);
        $ext  = $info !== false ? ($mime_to_ext[$info['mime']] ?? null) : null;
        if ($ext === null) {
            Session::addMessageAfterRedirect(__s("The header must be a PNG, JPG, GIF or WebP image", "order"), true, ERROR);
            return $input;
        }

        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            Session::addMessageAfterRedirect(__s("The header image could not be uploaded", "order"), true, ERROR);
            return $input;
        }

        // One header at a time: drop whatever was there before.
        foreach (glob($dir . '/header.*') ?: [] as $old) {
            @unlink($old);
        }

        $filename = 'header.' . $ext;
        $moved = is_uploaded_file($upload['tmp_name'])
            ? move_uploaded_file($upload['tmp_name'], $dir . '/' . $filename)
            : @rename($upload['tmp_name'], $dir . '/' . $filename);
        if (!$moved) {
            Session::addMessageAfterRedirect(__s("The header image could not be uploaded", "order"), true, ERROR);
            return $input;
        }

        $input['mail_header_filename'] = $filename;
        Session::addMessageAfterRedirect(__s("Message header uploaded", "order"), true, INFO);

        return $input;
    }


    //----------------- Getters and setters -------------------//

    public function useValidation()
    {
        return $this->fields['use_validation'];
    }


    public function getDraftState()
    {
        return $this->fields['order_status_draft'];
    }


    public function getWaitingForApprovalState()
    {
        return $this->fields['order_status_waiting_approval'];
    }


    public function getApprovedState()
    {
        return $this->fields['order_status_approved'];
    }


    public function getPartiallyDeliveredState()
    {
        return $this->fields['order_status_partially_delivred'];
    }


    public function getDeliveredState()
    {
        return $this->fields['order_status_completly_delivered'];
    }


    public function getCanceledState()
    {
        return $this->fields['order_status_canceled'];
    }


    public function getPaidState()
    {
        return $this->fields['order_status_paid'];
    }

    public function isAccountSectionDisplayed()
    {

        return $this->fields['order_accountsection_display'];
    }

    public function isAccountSectionMandatory()
    {

        return $this->fields['order_accountsection_mandatory'];
    }

    public function isAnalyticNatureDisplayed()
    {

        return $this->fields['order_analyticnature_display'];
    }

    public function isAnalyticNatureMandatory()
    {

        return $this->fields['order_analyticnature_mandatory'];
    }

    public function isConfigured()
    {
        return ($this->fields['order_status_draft']
        && $this->fields['order_status_waiting_approval']
        && $this->fields['order_status_approved']
        && $this->fields['order_status_partially_delivred']
        && $this->fields['order_status_completly_delivered']
        && $this->fields['order_status_canceled']
        && $this->fields['order_status_paid']);
    }

    public function getDefaultTaxes()
    {
        return $this->fields['default_taxes'];
    }


    public function canGenerateAsset()
    {
        return $this->fields['generate_assets'];
    }


    public function canGenerateTicket()
    {
        return ($this->fields['tickettemplates_id_delivery'] > 0);
    }


    public function canAddLocation()
    {
        return $this->fields['add_location'];
    }


    public function canAddBillDetails()
    {
        return $this->fields['add_bill_details'];
    }


    public function canAddImmobilizationNumber()
    {
        return $this->fields['add_immobilization_number'];
    }


    public function getGeneratedAssetName()
    {
        return $this->fields['generated_name'];
    }


    public function getGeneratedAssetSerial()
    {
        return $this->fields['generated_serial'];
    }


    public function getGeneratedAssetState()
    {
        return $this->fields['default_asset_states_id'];
    }


    public function getGeneratedAssetOtherserial()
    {
        return $this->fields['generated_otherserial'];
    }


    public function canUseSupplierSatisfaction()
    {
        return $this->fields['use_supplier_satisfaction'];
    }


    public function canUseSupplierInformations()
    {
        return $this->fields['use_supplier_informations'];
    }


    public function canGenerateOrderPDF()
    {
        return $this->fields['generate_order_pdf'];
    }


    public function canCopyDocuments()
    {
        return $this->fields['copy_documents'];
    }


    public function getShouldBeDevileredColor()
    {
        return $this->fields['shoudbedelivered_color'];
    }


    public function getDefaultDocumentCategory()
    {
        return $this->fields['documentcategories_id'];
    }


    public function getDefaultAuthorGroup()
    {
        return $this->fields['groups_id_author'];
    }


    public function getDefaultRecipientGroup()
    {
        return $this->fields['groups_id_recipient'];
    }


    public function getDefaultRecipient()
    {
        return $this->fields['users_id_recipient'];
    }


    public function canHideInactiveBudgets()
    {
        return $this->fields['hide_inactive_budgets'];
    }

    public function useFreeReference()
    {
        return $this->fields['use_free_reference'];
    }

    public function canRenameDocuments()
    {
        return $this->fields['rename_documents'];
    }


    /**
     * Day thresholds after which a reminder is sent for an order that has not
     * been invoiced yet. An empty list disables the reminders altogether.
     *
     * @return int[] Positive thresholds, deduplicated, in ascending order
     */
    public function getNotInvoicedReminderDays(): array
    {
        return self::parseReminderDays($this->fields['not_invoiced_reminder_days'] ?? '');
    }


    /**
     * Extra recipients notified on top of the order's author.
     *
     * @return string[] Valid e-mail addresses
     */
    public function getNotInvoicedReminderEmails(): array
    {
        return self::parseReminderEmails($this->fields['not_invoiced_reminder_emails'] ?? '');
    }


    /**
     * @param string $value Comma separated list of day thresholds
     * @return int[]
     */
    public static function parseReminderDays(string $value): array
    {
        $days = [];
        foreach (explode(',', $value) as $threshold) {
            $threshold = (int) trim($threshold);
            if ($threshold > 0) {
                $days[$threshold] = $threshold;
            }
        }
        sort($days);

        return $days;
    }


    /**
     * Directory holding the uploaded e-mail header image.
     */
    public static function getMailHeaderDir(): string
    {
        return GLPI_DOC_DIR . '/_plugins/order/mailheader';
    }


    /**
     * Absolute path of the configured header image, or null when unset/missing.
     */
    public function getMailHeaderPath(): ?string
    {
        $filename = (string) ($this->fields['mail_header_filename'] ?? '');
        if ($filename === '' || basename($filename) !== $filename) {
            return null;
        }

        $path = self::getMailHeaderDir() . '/' . $filename;

        return is_readable($path) ? $path : null;
    }


    /**
     * Public URL of the header image (cache-busted), or null when unset.
     *
     * Mail clients fetch this without a GLPI session, so it points at the
     * plugin's unauthenticated image endpoint, absolute via url_base.
     */
    public function getMailHeaderUrl(): ?string
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $path = $this->getMailHeaderPath();
        if ($path === null) {
            return null;
        }

        return $CFG_GLPI['url_base'] . '/plugins/order/front/mailheader.php?ts=' . filemtime($path);
    }


    /**
     * Same endpoint as a root-relative path, for in-app previews.
     *
     * The configuration page must show the image even when url_base does not
     * match the address the administrator is browsing from.
     */
    public function getMailHeaderWebPath(): ?string
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $path = $this->getMailHeaderPath();
        if ($path === null) {
            return null;
        }

        return $CFG_GLPI['root_doc'] . '/plugins/order/front/mailheader.php?ts=' . filemtime($path);
    }


    /**
     * @param string $value Comma (or semicolon) separated list of addresses
     * @return string[]
     */
    public static function parseReminderEmails(string $value): array
    {
        $emails = [];
        foreach (preg_split('/[,;]/', $value) ?: [] as $email) {
            $email = trim($email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[strtolower($email)] = $email;
            }
        }

        return array_values($emails);
    }


    //----------------- Install & uninstall -------------------//
    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table  = self::getTable();
        $config = new self();

        //This class is available since version 1.3.0
        if (
            !$DB->tableExists($table)
            && !$DB->tableExists("glpi_plugin_order_config")
        ) {
            $migration->displayMessage('Installing ' . $table);

            //Install
            $query = "CREATE TABLE `{$table}` (
                        `id` int {$default_key_sign} NOT NULL auto_increment,
                        `use_validation` tinyint NOT NULL default '0',
                        `use_supplier_satisfaction` tinyint NOT NULL default '0',
                        `use_supplier_informations` tinyint NOT NULL default '0',
                        `use_supplier_infos` tinyint NOT NULL default '1',
                        `generate_order_pdf` tinyint NOT NULL default '0',
                        `copy_documents` tinyint NOT NULL default '0',
                        `default_taxes` int NOT NULL default '0',
                        `generate_assets` int NOT NULL default '0',
                        `generated_name` varchar(255) default NULL,
                        `generated_serial` varchar(255) default NULL,
                        `generated_otherserial` varchar(255) default NULL,
                        `default_asset_states_id` int {$default_key_sign} NOT NULL default '0',
                        `tickettemplates_id_delivery` int {$default_key_sign} NOT NULL default '0',
                        `order_status_draft` int NOT NULL default '1',
                        `order_status_waiting_approval` int NOT NULL default '2',
                        `order_status_approved` int NOT NULL default '3',
                        `order_status_partially_delivred` int NOT NULL default '4',
                        `order_status_completly_delivered` int NOT NULL default '5',
                        `order_status_canceled` int NOT NULL default '6',
                        `order_status_paid` int NOT NULL default '7',
                        `order_analyticnature_display` int NOT NULL default '0',
                        `order_analyticnature_mandatory` int NOT NULL default '0',
                        `order_accountsection_display` int NOT NULL default '0',
                        `order_accountsection_mandatory` int NOT NULL default '0',
                        `shoudbedelivered_color` char(20) default '#ff5555',
                        `documentcategories_id` int {$default_key_sign} NOT NULL default '0',
                        `groups_id_author` int {$default_key_sign} NOT NULL default '0',
                        `groups_id_recipient` int {$default_key_sign} NOT NULL default '0',
                        `users_id_recipient` int {$default_key_sign} NOT NULL default '0',
                        `add_location` tinyint NOT NULL default '0',
                        `add_bill_details` tinyint NOT NULL default '0',
                        `add_immobilization_number` tinyint NOT NULL default '0',
                        `hide_inactive_budgets` tinyint NOT NULL default '0',
                        `rename_documents` tinyint NOT NULL default '0',
                        `transmit_budget_change` tinyint NOT NULL default '0',
                        `use_free_reference` tinyint NOT NULL default '0',
                        `not_invoiced_reminder_days` varchar(255) NOT NULL default '',
                        `not_invoiced_reminder_emails` text,
                        `mail_header_filename` varchar(255) NOT NULL default '',
                        PRIMARY KEY  (`id`)
                     ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);

            $tobefilled = "TOBEFILLED";
            $config->add([
                'id'                          => 1,
                'use_validation'              => 0,
                'default_taxes'               => 0,
                'generate_assets'             => 0,
                'generated_name'              => $tobefilled,
                'generated_serial'            => $tobefilled,
                'generated_otherserial'       => $tobefilled,
                'default_asset_states_id'     => 0,
                'generated_title'             => $tobefilled,
                'generated_content'           => $tobefilled,
                'default_ticketcategories_id' => 0,
                'shoudbedelivered_color'      => '#ff5555',
            ]);
        } else {
            //Upgrade
            $migration->displayMessage('Upgrading ' . $table);

            //1.2.0
            $migration->renameTable("glpi_plugin_order_config", $table);

            if (!countElementsInTable("glpi_plugin_order_configs")) {
                $migration->insertInTable(
                    'glpi_plugin_order_configs',
                    [
                        'id'                          => 1,
                        'use_validation'              => 0,
                        'default_taxes'               => 0,
                    ],
                );
            }

            $migration->changeField($table, "ID", "id", sprintf('int %s NOT NULL auto_increment', $default_key_sign));

            //1.3.0
            $migration->addField($table, "generate_assets", "tinyint NOT NULL default '0'");
            $migration->addField($table, "generated_name", "varchar(255) default NULL");
            $migration->addField($table, "generated_serial", "varchar(255) default NULL");
            $migration->addField($table, "generated_otherserial", "varchar(255) default NULL");
            $migration->addField($table, "default_asset_entities_id", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "default_asset_states_id", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "generated_title", "varchar(255) default NULL");
            $migration->addField($table, "generated_content", "text");
            $migration->addField($table, "default_ticketcategories_id", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "use_supplier_satisfaction", "tinyint NOT NULL default '0'");
            $migration->addField($table, "generate_order_pdf", "tinyint NOT NULL default '0'");
            $migration->addField($table, "use_supplier_informations", "tinyint NOT NULL default '1'");
            $migration->addField($table, "shoudbedelivered_color", "char(20) default '#ff5555'");
            $migration->addField($table, "copy_documents", "tinyint NOT NULL DEFAULT '0'");
            $migration->addField($table, "documentcategories_id", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "groups_id_author", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "groups_id_recipient", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->addField($table, "users_id_recipient", sprintf("int %s NOT NULL default '0'", $default_key_sign));

            $migration->changeField(
                $table,
                "default_ticketcategories_id",
                "default_itilcategories_id",
                sprintf("int %s NOT NULL default '0'", $default_key_sign),
            );

            //1.9.0
            $migration->addField($table, "add_location", "TINYINT NOT NULL DEFAULT '0'");
            $migration->addField($table, "add_bill_details", "TINYINT NOT NULL DEFAULT '0'");

            $config = new self();
            $config->getFromDB(1);

            $migration->addField($table, "tickettemplates_id_delivery", sprintf("int %s NOT NULL default '0'", $default_key_sign));
            $migration->migrationOneTable($table);

            $migration->dropField($table, "generated_title");
            $migration->dropField($table, "generated_content");
            $migration->dropField($table, "default_itilcategories_id");

            $migration->addField($table, "hide_inactive_budgets", "bool");
            $migration->addField($table, "rename_documents", "bool");

            //0.85+1.2
            $migration->addField($table, "transmit_budget_change", "bool");

            $migration->migrationOneTable($table);

            //version 2.0.1
            $migration->addField($table, "use_free_reference", "bool");
        }

        $migration->displayMessage("Add default order state workflow");
        $new_states = [
            'order_status_draft'               => '1',
            'order_status_waiting_approval'    => '2',
            'order_status_approved'            => '3',
            'order_status_partially_delivred'  => '4',
            'order_status_completly_delivered' => '5',
            'order_status_canceled'            => '6',
            'order_status_paid'                => '7',
        ];

        foreach ($new_states as $field => $value) {
            $migration->addField($table, $field, sprintf("int NOT NULL default '%s'", $value), ['update' => $value]);
        }

        if (!$DB->fieldExists($table, 'order_analyticnature_display')) {
            $migration->addField($table, 'order_analyticnature_display', 'integer');
        }

        if (!$DB->fieldExists($table, 'order_accountsection_display')) {
            $migration->addField($table, 'order_accountsection_display', 'integer');
        }

        if (!$DB->fieldExists($table, 'order_analyticnature_mandatory')) {
            $migration->addField($table, 'order_analyticnature_mandatory', 'integer');
        }

        if (!$DB->fieldExists($table, 'order_accountsection_mandatory')) {
            $migration->addField($table, 'order_accountsection_mandatory', 'integer');
        }

        if (!$DB->fieldExists($table, 'add_immobilization_number')) {
            $migration->addField($table, "add_immobilization_number", "TINYINT NOT NULL DEFAULT '0'");
        }

        if (!$DB->fieldExists($table, 'not_invoiced_reminder_days')) {
            $migration->addField($table, 'not_invoiced_reminder_days', "VARCHAR(255) NOT NULL DEFAULT ''");
        }

        if (!$DB->fieldExists($table, 'not_invoiced_reminder_emails')) {
            $migration->addField($table, 'not_invoiced_reminder_emails', 'text');
        }

        if (!$DB->fieldExists($table, 'mail_header_filename')) {
            $migration->addField($table, 'mail_header_filename', "VARCHAR(255) NOT NULL DEFAULT ''");
        }

        $migration->migrationOneTable($table);
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        //Old table
        $DB->doQuery("DROP TABLE IF EXISTS `glpi_plugin_order_config`");

        //New table
        $DB->doQuery("DROP TABLE IF EXISTS `" . self::getTable() . "`");

        foreach (glob(self::getMailHeaderDir() . '/header.*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir(self::getMailHeaderDir());
    }


    public function rawSearchOptions()
    {
        return [[
            'id'            => '2',
            'table'         => $this->getTable(),
            'field'         => 'generated_name',
            'name'          => __s('Default name', 'order'),
            'autocomplete'  => true,
        ], [
            'id'            => '3',
            'table'         => $this->getTable(),
            'field'         => 'generated_serial',
            'name'          => __s('Default serial number', 'order'),
            'autocomplete'  => true,
        ], [
            'id'            => '4',
            'table'         => $this->getTable(),
            'field'         => 'generated_otherserial',
            'name'          => __s('Default inventory number', 'order'),
            'autocomplete'  => true,
        ]];
    }
}
