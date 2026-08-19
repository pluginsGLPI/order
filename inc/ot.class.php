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

/**
 * Generates OT (fixed assets protocol) PDF documents from order data.
 */
class PluginOrderOt
{
    /**
     * Marks a superseded OT document. Kept in ASCII so re-runs recognise it
     * whatever language the interface is in.
     */
    public const ARCHIVED_PREFIX = '[ARCHIVED] ';

    /**
     * Show the input sub-form for the "Generate OT" massive action popup.
     */
    public static function showMassiveActionSubForm(): void
    {
        self::showInvoiceNumberField();
        echo "<br><br>";
        echo "<label for='cost_center'>" . __s("Cost Center", "order") . " (MPK):&nbsp;</label>";
        echo Html::input('cost_center', [
            'id'    => 'cost_center',
            'value' => '',
            'size'  => 20,
        ]);
        echo "<br><br>";
        echo "<label for='ot_num_order'>" . __s("Order number", "order") . ":&nbsp;</label>";
        echo Html::input('ot_num_order', [
            'id'    => 'ot_num_order',
            'value' => '',
            'size'  => 30,
        ]);
        echo "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Leave empty to use the order's own number", "order")
           . "</span>";
        echo "<br><br>";
        echo "<label for='date_usage'>" . __s("Commissioning date", "order") . ":&nbsp;</label>";
        Html::showDateField('date_usage', ['value' => '', 'maybeempty' => true]);
        echo "<br><br>";
        echo "<label for='date_warehouse'>" . __s("Warehouse deposit date", "order") . ":&nbsp;</label>";
        Html::showDateField('date_warehouse', ['value' => '', 'maybeempty' => true]);
        echo "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Both dates are optional - fill in only the one that applies", "order")
           . "</span>";
        echo "<br><br>";
        echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
    }


    /**
     * Invoice number input, shared by the "Generate OT" and "Invoicing" sub-forms.
     *
     * @param bool $required Whether the field must be filled in
     */
    public static function showInvoiceNumberField(bool $required = false): void
    {
        $options = [
            'id'    => 'invoice_number',
            'value' => '',
            'size'  => 30,
        ];
        if ($required) {
            $options['required'] = 'required';
        }

        echo "<label for='invoice_number'>" . __s("Invoice Number", "order") . ":&nbsp;</label>";
        echo Html::input('invoice_number', $options);
    }


    /**
     * Let the user narrow a bill down to some positions of a single order.
     *
     * A correcting bill often covers only part of an order, so the positions are
     * offered with everything ticked: leaving them alone bills the whole order.
     * The picker is skipped for a multi-order selection, where one list of
     * positions would not mean anything.
     *
     * @param array $selected_orders Order ids picked in the list
     */
    public static function showInvoiceItemsPicker(array $selected_orders): void
    {
        /** @var DBmysql $DB */
        global $DB;

        if (count($selected_orders) !== 1) {
            echo "<br><span class='text-muted' style='font-size:0.85em;'>"
               . __s("Several orders are selected: the bill will cover all of their positions.", "order")
               . "</span>";
            return;
        }

        $order_id = (int) reset($selected_orders);
        $iterator = $DB->request([
            'FROM'  => PluginOrderOrder_Item::getTable(),
            'WHERE' => ['plugin_order_orders_id' => $order_id],
            'ORDER' => 'id ASC',
        ]);

        if (count($iterator) === 0) {
            return;
        }

        $reference = new PluginOrderReference();
        $bill      = new PluginOrderBill();

        echo "<br><br><div style='text-align:left;display:inline-block;'>";
        // Unchecked boxes are absent from the POST, so this marker is how the
        // server tells "picker shown, everything unticked" apart from "no
        // picker at all" - the two must not both mean "bill the whole order".
        echo Html::hidden('invoice_items_shown', ['value' => 1]);
        echo "<strong>" . __s("Positions covered by this bill", "order") . "</strong>";
        echo "<br><span class='text-muted' style='font-size:0.85em;'>"
           . __s("Untick the positions this bill does not cover.", "order")
           . "</span>";
        echo "<table class='tab_cadre' style='margin-top:6px;'>";

        foreach ($iterator as $item) {
            // Orders regularly repeat the same product, so the position id is
            // what tells two otherwise identical rows apart.
            $label = sprintf(__('Position %s', 'order'), $item['id']);
            if ((int) $item['plugin_order_references_id'] > 0
                && $reference->getFromDB($item['plugin_order_references_id'])
                && $reference->fields['name'] !== ''
            ) {
                $label .= ' - ' . $reference->fields['name'];
            }

            $current = '';
            if ((int) $item['plugin_order_bills_id'] > 0
                && $bill->getFromDB($item['plugin_order_bills_id'])
            ) {
                $current = ' <span class="text-muted">('
                    . sprintf(
                        __s('currently on bill %s', 'order'),
                        htmlescape($bill->fields['number'] !== '' ? $bill->fields['number'] : $bill->fields['name']),
                    )
                    . ')</span>';
            }

            echo "<tr class='tab_bg_1'><td>";
            echo "<input type='checkbox' class='form-check-input' name='invoice_items[]' value='"
               . (int) $item['id'] . "' checked id='invoice_item_" . (int) $item['id'] . "'>";
            echo "&nbsp;<label for='invoice_item_" . (int) $item['id'] . "'>"
               . htmlescape($label) . $current . "</label>";
            echo "</td></tr>";
        }

        echo "</table></div>";
    }


    /**
     * Normalize the sub-form input into the parameter set used to build an OT.
     *
     * @param array $input Raw massive action input
     * @return array{cost_center: string, invoice_number: string, num_order: string, date_usage: string, date_warehouse: string}
     */
    public static function extractParams(array $input): array
    {
        return [
            'cost_center'    => trim((string) ($input['cost_center'] ?? '')),
            'invoice_number' => trim((string) ($input['invoice_number'] ?? '')),
            'num_order'      => trim((string) ($input['ot_num_order'] ?? '')),
            'date_usage'     => trim((string) ($input['date_usage'] ?? '')),
            'date_warehouse' => trim((string) ($input['date_warehouse'] ?? '')),
        ];
    }


    /**
     * Full orchestration: generate HTML -> PDF -> save as Document -> create Bill -> return result.
     *
     * @param int   $order_id The order ID
     * @param array $params   Values from the sub-form, see self::extractParams()
     * @return array|false ['doc_id' => int, 'bill_id' => int|false] on success, false on failure
     */
    public function processAction(int $order_id, array $params)
    {
        $order = new PluginOrderOrder();
        if (!$order->getFromDB($order_id)) {
            return false;
        }

        $invoice_number = $params['invoice_number'] ?? '';

        $html = $this->generateOtHtml($order, $params);
        $num_order = ($params['num_order'] ?? '') !== ''
            ? $params['num_order']
            : ($order->fields['num_order'] ?: $order_id);
        $base_name = 'OT_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $num_order);

        $pdf_path = $this->generatePdf($html, $base_name);
        if ($pdf_path === false) {
            return false;
        }

        $is_pdf  = str_ends_with($pdf_path, '.pdf');
        $ext     = $is_pdf ? 'pdf' : 'html';
        $mime    = $is_pdf ? 'application/pdf' : 'text/html';

        $doc_id = $this->saveAsDocument($order, $pdf_path, $base_name . '.' . $ext, $mime);

        // Regenerating an OT for a corrected order supersedes the earlier ones.
        if ($doc_id) {
            $archived = self::archivePreviousDocuments($order, (int) $doc_id);
            if ($archived > 0) {
                Session::addMessageAfterRedirect(
                    sprintf(
                        _sn(
                            "%s previous OT document was archived and stays attached to the order",
                            "%s previous OT documents were archived and stay attached to the order",
                            $archived,
                            "order",
                        ),
                        $archived,
                    ),
                    true,
                    INFO,
                );
            }
        }

        // Auto-create bill if invoice number provided
        $bill_id = false;
        if ($invoice_number !== '') {
            $bill_id = $this->createBill($order, $invoice_number);
        }

        return [
            'doc_id'  => $doc_id,
            'bill_id' => $bill_id,
        ];
    }


    /**
     * Create and link the bill without producing an OT document.
     *
     * @param int    $order_id       The order ID
     * @param string $invoice_number Invoice number entered by user
     * @return int|false Bill ID on success, false on failure
     */
    public function processInvoiceOnly(int $order_id, string $invoice_number, ?array $item_ids = null)
    {
        if ($invoice_number === '') {
            return false;
        }

        $order = new PluginOrderOrder();
        if (!$order->getFromDB($order_id)) {
            return false;
        }

        return $this->createBill($order, $invoice_number, $item_ids);
    }


    /**
     * Create a PluginOrderBill record for the order.
     *
     * @param PluginOrderOrder $order          The order (already loaded)
     * @param string           $invoice_number Invoice number entered by user
     * @param int[]|null       $item_ids       Order items the bill covers; null means the whole order
     * @return int|false Bill ID on success, false on failure
     */
    private function createBill(PluginOrderOrder $order, string $invoice_number, ?array $item_ids = null)
    {
        /** @var DBmysql $DB */
        global $DB;

        $order_id = $order->getID();

        if ($item_ids !== null) {
            $item_ids = self::filterOrderItems($order_id, $item_ids);
            // A selection that no longer matches anything (rows deleted between
            // popup and submit, or nothing ticked) must never widen into "bill
            // the whole order".
            if ($item_ids === []) {
                return false;
            }
        }

        // The bill is worth what it actually covers, so a correcting bill for a
        // few positions does not carry the whole order's value.
        if ($item_ids === null) {
            $order_item = new PluginOrderOrder_Item();
            $prices = $order_item->getAllPrices($order_id);
        } else {
            $prices = $DB->request([
                'SELECT' => ['SUM' => ['price_discounted AS priceHT']],
                'FROM'   => PluginOrderOrder_Item::getTable(),
                'WHERE'  => ['id' => $item_ids],
            ])->current();
        }
        $value = (float) ($prices['priceHT'] ?? 0);

        $today = date('Y-m-d');

        $bill = new PluginOrderBill();
        $bill_id = $bill->add([
            'name'                       => $invoice_number,
            'number'                     => $invoice_number,
            'suppliers_id'               => (int) $order->fields['suppliers_id'],
            'value'                      => $value,
            'plugin_order_orders_id'     => $order_id,
            'plugin_order_billstates_id' => PluginOrderBillState::PAID,
            'billdate'                   => $today,
            'validationdate'             => $today,
            'users_id_validation'        => Session::getLoginUserID(),
            'entities_id'                => (int) $order->fields['entities_id'],
            'is_recursive'               => (int) ($order->fields['is_recursive'] ?? 0),
        ]);

        if (!$bill_id) {
            return false;
        }

        $this->linkBillToOrderItems($order, $bill_id, $bill, $item_ids);

        // A bill is superseded once nothing points at it any more. A correcting
        // bill covering part of the order therefore leaves the earlier one in
        // place for the positions it still covers.
        $archived = PluginOrderBill::archiveUncoveredForOrder($order_id, (int) $bill_id);
        if ($archived > 0) {
            Session::addMessageAfterRedirect(
                sprintf(
                    _sn(
                        "%s previous bill no longer covers any position and was archived",
                        "%s previous bills no longer cover any position and were archived",
                        $archived,
                        "order",
                    ),
                    $archived,
                ),
                true,
                INFO,
            );
        }

        return $bill_id;
    }


    /**
     * Keep only ids that really belong to this order.
     *
     * @param int[] $item_ids
     * @return int[]|null null when the selection covers the whole order,
     *                    [] when nothing valid remains (the caller must abort)
     */
    private static function filterOrderItems(int $order_id, array $item_ids): ?array
    {
        /** @var DBmysql $DB */
        global $DB;

        $item_ids = array_filter(array_map('intval', $item_ids));
        if ($item_ids === []) {
            return [];
        }

        $valid = [];
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => PluginOrderOrder_Item::getTable(),
            'WHERE'  => ['plugin_order_orders_id' => $order_id, 'id' => $item_ids],
        ]);
        foreach ($iterator as $row) {
            $valid[] = (int) $row['id'];
        }

        if ($valid === []) {
            return [];
        }

        // Selecting everything is the same as not selecting at all.
        $total = countElementsInTable(
            PluginOrderOrder_Item::getTable(),
            ['plugin_order_orders_id' => $order_id],
        );

        return count($valid) === $total ? null : $valid;
    }


    /**
     * Mark the OT documents generated earlier for this order as archived.
     *
     * They keep their link to the order so the paper trail stays complete; the
     * marker only tells readers which one is superseded.
     *
     * @param int $keep_document_id Document that must stay current
     * @return int Number of documents archived
     */
    public static function archivePreviousDocuments(PluginOrderOrder $order, int $keep_document_id): int
    {
        /** @var DBmysql $DB */
        global $DB;

        $iterator = $DB->request([
            'SELECT'    => ['doc.id', 'doc.name', 'doc.comment'],
            'FROM'      => 'glpi_documents_items AS di',
            'INNER JOIN' => [
                'glpi_documents AS doc' => [
                    'ON' => ['di' => 'documents_id', 'doc' => 'id'],
                ],
            ],
            'WHERE'     => [
                'di.itemtype' => PluginOrderOrder::class,
                'di.items_id' => $order->getID(),
                'doc.id'      => ['!=', $keep_document_id],
                'doc.filepath' => ['LIKE', '_plugins/order/ot/%'],
            ],
        ]);

        $document = new Document();
        $archived = 0;

        foreach ($iterator as $row) {
            if (str_starts_with((string) $row['name'], self::ARCHIVED_PREFIX)) {
                continue;
            }

            $note = sprintf(
                __("Archived on %s: replaced by a newer OT document", "order"),
                Html::convDateTime($_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s')),
            );
            $comment = trim((string) $row['comment']);

            $document->update([
                'id'      => $row['id'],
                'name'    => self::ARCHIVED_PREFIX . $row['name'],
                'comment' => $comment === '' ? $note : $comment . "\n" . $note,
            ]);
            $archived++;
        }

        return $archived;
    }


    /**
     * Link a bill to all order items and update their Infocom records.
     *
     * @param PluginOrderOrder $order   The order (already loaded)
     * @param int              $bill_id The newly created bill ID
     * @param PluginOrderBill  $bill    The bill object (already loaded after add)
     */
    private function linkBillToOrderItems(
        PluginOrderOrder $order,
        int $bill_id,
        PluginOrderBill $bill,
        ?array $item_ids = null,
    ): void {
        /** @var DBmysql $DB */
        global $DB;

        $order_id = $order->getID();
        $bill->getFromDB($bill_id);

        $where = ['plugin_order_orders_id' => $order_id];
        if ($item_ids !== null) {
            $where['id'] = $item_ids;
        }

        $items_result = $DB->request([
            'FROM'  => 'glpi_plugin_order_orders_items',
            'WHERE' => $where,
        ]);

        $order_item = new PluginOrderOrder_Item();
        $config = PluginOrderConfig::getConfig();

        foreach ($items_result as $item_data) {
            // Update order item with bill ID and PAID state. Item form rules
            // (e.g. a mandatory analytic nature on legacy rows) must not block
            // this internal bookkeeping, so fall back to a direct write when
            // the ORM update is rejected.
            $updated = $order_item->update([
                'id'                         => $item_data['id'],
                'plugin_order_bills_id'      => $bill_id,
                'plugin_order_billstates_id' => PluginOrderBillState::PAID,
            ]);
            if (!$updated) {
                $DB->update('glpi_plugin_order_orders_items', [
                    'plugin_order_bills_id'      => $bill_id,
                    'plugin_order_billstates_id' => PluginOrderBillState::PAID,
                ], ['id' => $item_data['id']]);
            }

            // Update Infocom on the linked asset (if delivered and config allows)
            if ($config->canAddBillDetails()
                && !empty($item_data['itemtype'])
                && (int) $item_data['items_id'] > 0
            ) {
                $ic = new Infocom();
                if ($ic->getFromDBforDevice($item_data['itemtype'], (int) $item_data['items_id'])) {
                    $ic->update([
                        'id'            => $ic->fields['id'],
                        'bill'          => $bill->fields['number'],
                        'warranty_date' => $bill->fields['billdate'],
                    ]);
                }
            }
        }

        // Update order's aggregate bill state
        PluginOrderOrder::updateBillState($order_id);
    }


    /**
     * Build the full HTML document matching the OT.xlsx template layout.
     *
     * @param PluginOrderOrder $order          The order object (already loaded)
     * @param string           $cost_center    Cost Center value
     * @param string           $invoice_number Invoice number
     * @return string Complete HTML document
     */
    public function generateOtHtml(PluginOrderOrder $order, array $params): string
    {
        /** @var DBmysql $DB */
        global $DB;

        $cost_center    = $params['cost_center'] ?? '';
        $invoice_number = $params['invoice_number'] ?? '';
        $date_usage     = $params['date_usage'] ?? '';
        $date_warehouse = $params['date_warehouse'] ?? '';

        $order_id  = $order->getID();
        $num_order = ($params['num_order'] ?? '') !== ''
            ? $params['num_order']
            : ($order->fields['num_order'] ?? '');

        // The two dates are mutually exclusive in practice (an item either goes
        // into the warehouse or straight into use), so filling in one of them
        // takes over both columns and an empty field stays empty. When neither
        // is given, each item keeps showing its own delivery date as before.
        $dates_given     = $date_usage !== '' || $date_warehouse !== '';
        $usage_column     = $date_usage !== '' ? Html::convDate($date_usage) : '';
        $warehouse_column = $date_warehouse !== '' ? Html::convDate($date_warehouse) : '';

        // Get supplier name
        $supplier_name = '';
        $supplier = new Supplier();
        if ($supplier->getFromDB($order->fields['suppliers_id'])) {
            $supplier_name = $supplier->fields['name'];
        }

        // Query delivered items (items_id != 0 means delivered and linked to an asset)
        $items_result = $DB->request([
            'FROM'  => 'glpi_plugin_order_orders_items',
            'WHERE' => [
                'plugin_order_orders_id' => $order_id,
                'items_id'              => ['!=', 0],
            ],
            'ORDER' => 'id ASC',
        ]);

        $rows = [];
        $total_value = 0.0;

        foreach ($items_result as $item_data) {
            $asset_serial = '';
            $itemtype     = $item_data['itemtype'] ?? '';
            $items_id     = (int) ($item_data['items_id'] ?? 0);

            // Get serial number from the delivered asset in GLPI
            if ($itemtype && $items_id > 0) {
                $asset = getItemForItemtype($itemtype);
                if ($asset !== false && $asset->getFromDB($items_id)) {
                    $asset_serial = $asset->fields['serial'] ?? '';
                }
            }

            // Get product reference name from the order's reference
            $ref_name = '';
            $ref_id = (int) ($item_data['plugin_order_references_id'] ?? 0);
            if ($ref_id > 0) {
                $reference = new PluginOrderReference();
                if ($reference->getFromDB($ref_id)) {
                    $ref_name = $reference->fields['name'] ?? '';
                }
            }

            $price = (float) ($item_data['price_taxfree'] ?? 0);
            $total_value += $price;

            $delivery_date = '';
            if (!empty($item_data['delivery_date'])) {
                $delivery_date = Html::convDate($item_data['delivery_date']);
            }

            $rows[] = [
                'name'           => htmlspecialchars($ref_name, ENT_QUOTES, 'UTF-8'),
                'serial'         => htmlspecialchars($asset_serial, ENT_QUOTES, 'UTF-8'),
                'price'          => number_format($price, 2, ',', ' '),
                'date_usage'     => htmlspecialchars(
                    $dates_given ? $usage_column : $delivery_date,
                    ENT_QUOTES,
                    'UTF-8',
                ),
                'date_warehouse' => htmlspecialchars($warehouse_column, ENT_QUOTES, 'UTF-8'),
            ];
        }

        $supplier_esc    = htmlspecialchars($supplier_name, ENT_QUOTES, 'UTF-8');
        $cost_center_esc = htmlspecialchars($cost_center, ENT_QUOTES, 'UTF-8');
        $num_order_esc   = htmlspecialchars($num_order, ENT_QUOTES, 'UTF-8');
        $invoice_display = $invoice_number !== '' ? htmlspecialchars($invoice_number, ENT_QUOTES, 'UTF-8') : '_______________';
        $total_formatted = number_format($total_value, 2, ',', ' ');

        // Build item rows HTML
        $items_html = '';
        $pos = 1;
        foreach ($rows as $row) {
            $items_html .= "<tr>
                <td style='border:1px solid #000;text-align:center;padding:3px;'>{$pos}</td>
                <td style='border:1px solid #000;text-align:center;padding:3px;'>1</td>
                <td style='border:1px solid #000;padding:3px;'>{$row['name']}</td>
                <td style='border:1px solid #000;padding:3px;'>{$row['serial']}</td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;text-align:right;padding:3px;'>{$row['price']}</td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'>{$cost_center_esc}</td>
                <td style='border:1px solid #000;padding:3px;'>{$num_order_esc}</td>
                <td style='border:1px solid #000;padding:3px;'>{$row['date_usage']}</td>
                <td style='border:1px solid #000;padding:3px;'>{$row['date_warehouse']}</td>
            </tr>\n";
            $pos++;
        }

        // Fill empty rows up to 20 lines to match the template
        for ($i = $pos; $i <= 20; $i++) {
            $items_html .= "<tr>
                <td style='border:1px solid #000;text-align:center;padding:3px;'>{$i}</td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
                <td style='border:1px solid #000;padding:3px;'></td>
            </tr>\n";
        }

        $th_style = "border:1px solid #000;padding:3px;text-align:center;font-weight:bold;font-size:7px;background:#f0f0f0;";

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>OT - {$num_order_esc}</title>
<style>
    @page { size: A4 portrait; margin: 10mm 8mm; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; margin: 0; padding: 0; }
    table { border-collapse: collapse; width: 100%; }
    td, th { font-size: 8px; }
</style>
</head>
<body>

<table>
    <tr>
        <td colspan="11" style="text-align:center;font-size:22px;font-weight:bold;padding:8px 0 2px 0;">OT</td>
    </tr>
    <tr>
        <td colspan="11" style="text-align:center;font-size:11px;padding:2px 0;">
            Potwierdzenie w&#322;&#261;czenia do u&#380;ytku / Lokalizacja &#347;rodk&oacute;w trwa&#322;ych
        </td>
    </tr>
    <tr>
        <td colspan="11" style="text-align:center;font-size:9px;font-style:italic;padding:0 0 10px 0;">
            (Nachweis der Inbetriebnahme / Verteilung der Sachanlagen)
        </td>
    </tr>
</table>

<table>
    <tr>
        <td style="width:40%;padding:4px 0;">
            <strong>Numer faktury:</strong> {$invoice_display}
        </td>
        <td style="width:60%;padding:4px 0;">
            <strong>Dostawca / Producent:</strong> {$supplier_esc}
        </td>
    </tr>
</table>

<br>

<table>
    <thead>
        <tr>
            <th style="{$th_style}width:4%;">Poz</th>
            <th style="{$th_style}width:5%;">Ilo&#347;&#263;/<br>Menge</th>
            <th style="{$th_style}width:18%;">Nazwa &#347;rodka trwa&#322;ego /<br>Bezeichnung</th>
            <th style="{$th_style}width:12%;">Nr seryjny /<br>Seriennummer</th>
            <th style="{$th_style}width:10%;">Nr &#347;rodka trwa&#322;ego /<br>Anlagennumm.</th>
            <th style="{$th_style}width:10%;">Warto&#347;&#263; /<br>Betrag</th>
            <th style="{$th_style}width:8%;">Lokaliz. /<br>Standort</th>
            <th style="{$th_style}width:9%;">Cost<br>Center</th>
            <th style="{$th_style}width:8%;">Order</th>
            <th style="{$th_style}width:10%;">Data w&#322;&#261;cz. do u&#380;ytku /<br>Datum Inbetriebnahme</th>
            <th style="{$th_style}width:10%;">Data zdep. w magazynie /<br>Datum ins Lager</th>
        </tr>
    </thead>
    <tbody>
        {$items_html}
        <tr>
            <td colspan="5" style="border:1px solid #000;text-align:right;padding:4px;font-weight:bold;">SUMA / Summe:</td>
            <td style="border:1px solid #000;text-align:right;padding:4px;font-weight:bold;">{$total_formatted}</td>
            <td colspan="5" style="border:1px solid #000;padding:3px;"></td>
        </tr>
    </tbody>
</table>

<br>
<table>
    <tr><td style="padding:15px 0 5px 40px;">____________________________</td></tr>
    <tr><td style="padding:0 0 0 60px;font-size:9px;">czytelny podpis</td></tr>
</table>

<br>
<table>
    <tr><td style="padding:5px 0;"><strong>Uwagi:</strong></td></tr>
    <tr><td style="border-bottom:1px solid #999;padding:10px 0;">&nbsp;</td></tr>
    <tr><td style="border-bottom:1px solid #999;padding:10px 0;">&nbsp;</td></tr>
</table>

<br><br>

<table>
    <tr>
        <td colspan="11" style="font-size:10px;font-weight:bold;padding:8px 0 4px 0;">
            Zmiany lokalizacji &#347;rodk&oacute;w trwa&#322;ych / (Verschiebung von Anlagenverm&ouml;gen)
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th style="{$th_style}width:4%;">Poz</th>
            <th style="{$th_style}width:5%;">Ilo&#347;&#263;/<br>Menge</th>
            <th style="{$th_style}width:18%;">Nazwa &#347;rodka trwa&#322;ego /<br>Bezeichnung</th>
            <th style="{$th_style}width:12%;">Nr seryjny /<br>Seriennummer</th>
            <th style="{$th_style}width:10%;">Nr &#347;rodka trwa&#322;ego /<br>Anlagennumm.</th>
            <th style="{$th_style}width:10%;">Lokaliz. /<br>Standort</th>
            <th style="{$th_style}width:9%;">Cost<br>Center</th>
            <th style="{$th_style}width:8%;">Order</th>
            <th style="{$th_style}width:12%;">Data przes. /<br>Datum von Verschiebung</th>
            <th style="{$th_style}width:12%;">Data w&#322;&#261;cz. do u&#380;ytku /<br>Datum Inbetriebnahme</th>
        </tr>
    </thead>
    <tbody>
HTML;

        // 10 empty rows for the relocation section
        for ($i = 1; $i <= 10; $i++) {
            $html .= "<tr>";
            for ($c = 0; $c < 10; $c++) {
                $html .= "<td style='border:1px solid #000;padding:3px;'>&nbsp;</td>";
            }
            $html .= "</tr>\n";
        }

        $html .= <<<HTML
    </tbody>
</table>

<br>
<table>
    <tr><td style="padding:15px 0 5px 40px;">____________________________</td></tr>
    <tr><td style="padding:0 0 0 60px;font-size:9px;">czytelny podpis</td></tr>
    <tr>
        <td style="padding:5px 0 0 200px;font-size:8px;font-style:italic;">
            * nach Bearbeitung durch Rechnungspr&uuml;fer Kopie an LZ
        </td>
    </tr>
</table>

</body>
</html>
HTML;

        return $html;
    }


    /**
     * Generate PDF from HTML using fallback chain: wkhtmltopdf -> Chromium -> mPDF -> HTML file.
     *
     * @param string $html      Full HTML document
     * @param string $base_name Base filename (without extension)
     * @return string|false Path to generated file, or false on failure
     */
    public function generatePdf(string $html, string $base_name)
    {
        $tmp_dir   = GLPI_TMP_DIR;
        $html_path = $tmp_dir . '/' . $base_name . '_' . uniqid() . '.html';
        $pdf_path  = $tmp_dir . '/' . $base_name . '_' . uniqid() . '.pdf';

        file_put_contents($html_path, $html);

        // 1. Try wkhtmltopdf
        $wk_path = $this->findBinary('wkhtmltopdf');
        if ($wk_path) {
            $cmd = sprintf(
                '%s --quiet --page-size A4 --orientation Portrait --encoding utf-8 --margin-top 10 --margin-bottom 10 --margin-left 8 --margin-right 8 %s %s 2>&1',
                escapeshellarg($wk_path),
                escapeshellarg($html_path),
                escapeshellarg($pdf_path),
            );
            exec($cmd, $output, $exit_code);
            @unlink($html_path);
            if ($exit_code === 0 && file_exists($pdf_path)) {
                return $pdf_path;
            }
        }

        // 2. Try Chromium headless
        $chrome_path = $this->findBinary('chromium-browser') ?: $this->findBinary('chromium') ?: $this->findBinary('google-chrome');
        if ($chrome_path) {
            $cmd = sprintf(
                '%s --headless --disable-gpu --no-sandbox --print-to-pdf=%s --no-pdf-header-footer file://%s 2>&1',
                escapeshellarg($chrome_path),
                escapeshellarg($pdf_path),
                escapeshellarg($html_path),
            );
            exec($cmd, $output, $exit_code);
            @unlink($html_path);
            if ($exit_code === 0 && file_exists($pdf_path)) {
                return $pdf_path;
            }
        }

        // 3. Try mPDF
        if (class_exists('\\Mpdf\\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode'          => 'utf-8',
                    'format'        => 'A4',
                    'margin_left'   => 8,
                    'margin_right'  => 8,
                    'margin_top'    => 10,
                    'margin_bottom' => 10,
                    'tempDir'       => $tmp_dir,
                ]);
                // Adapt for mPDF limitations
                $adapted_html = str_replace("'DejaVu Sans'", "'dejavusans'", $html);
                $mpdf->WriteHTML($adapted_html);
                $mpdf->Output($pdf_path, \Mpdf\Output\Destination::FILE);
                @unlink($html_path);
                if (file_exists($pdf_path)) {
                    return $pdf_path;
                }
            } catch (\Throwable $e) {
                // mPDF failed, continue to fallback
            }
        }

        // 4. Fallback: save as HTML
        @unlink($pdf_path);
        // Return the HTML file path directly
        return $html_path;
    }


    /**
     * Find a binary in common system paths.
     *
     * @param string $name Binary name
     * @return string|null Full path if found, null otherwise
     */
    private function findBinary(string $name): ?string
    {
        $search_paths = [
            '/usr/bin/',
            '/usr/local/bin/',
            '/snap/bin/',
        ];

        foreach ($search_paths as $dir) {
            $path = $dir . $name;
            if (is_executable($path)) {
                return $path;
            }
        }

        // Try `which` as last resort
        $result = trim((string) shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null'));
        if ($result && is_executable($result)) {
            return $result;
        }

        return null;
    }


    /**
     * Save the generated file as a GLPI Document linked to the order.
     *
     * @param PluginOrderOrder $order    The order (already loaded)
     * @param string           $filepath Absolute path to the generated file
     * @param string           $filename Document filename (e.g. "OT_PO123.pdf")
     * @param string           $mime     MIME type
     * @return int|false Document ID on success, false on failure
     */
    public function saveAsDocument(PluginOrderOrder $order, string $filepath, string $filename, string $mime)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        // Move file to GLPI document storage
        $doc_dir = GLPI_DOC_DIR . '/_plugins/order/ot/';
        @mkdir($doc_dir, 0755, true);

        $dest_path = $doc_dir . $filename;
        // If the name is taken, keep suffixing until it is not: a bulk run with
        // a shared order-number override can collide several times within the
        // same second, and rename() would silently overwrite an earlier OT.
        if (file_exists($dest_path)) {
            $info   = pathinfo($filename);
            $stamp  = date('YmdHis');
            $suffix = '';
            $i      = 1;
            do {
                $dest_path = $doc_dir . $info['filename'] . '_' . $stamp . $suffix . '.' . $info['extension'];
                $suffix    = '_' . ++$i;
            } while (file_exists($dest_path));
            $filename = basename($dest_path);
        }

        if (!rename($filepath, $dest_path)) {
            if (!copy($filepath, $dest_path)) {
                return false;
            }
            @unlink($filepath);
        }

        // Relative path from GLPI_DOC_DIR
        $relative_path = '_plugins/order/ot/' . $filename;

        // Create GLPI Document record
        $doc = new Document();
        $doc_id = $doc->add([
            'name'          => $filename,
            'filename'      => $filename,
            'filepath'      => $relative_path,
            'mime'          => $mime,
            'entities_id'   => $order->fields['entities_id'],
            'is_recursive'  => $order->fields['is_recursive'] ?? 0,
        ]);

        if (!$doc_id) {
            return false;
        }

        // Link document to the order
        $doc_item = new Document_Item();
        $doc_item->add([
            'documents_id' => $doc_id,
            'itemtype'     => PluginOrderOrder::class,
            'items_id'     => $order->getID(),
            'entities_id'  => $order->fields['entities_id'],
        ]);

        return $doc_id;
    }


    /**
     * Stream a document file as a download response.
     *
     * @param int $doc_id GLPI Document ID
     * @return void
     */
    public static function downloadDocument(int $doc_id): void
    {
        $doc = new Document();
        if (!$doc->getFromDB($doc_id)) {
            return;
        }

        $filepath = GLPI_DOC_DIR . '/' . $doc->fields['filepath'];
        if (!file_exists($filepath)) {
            return;
        }

        $filename = $doc->fields['filename'];
        $mime     = $doc->fields['mime'] ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filepath);
    }
}
