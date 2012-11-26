<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
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
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */
 $LANG['plugin_order']['title'][1] = "Bestellverwaltung";

$LANG['plugin_order'][0] = "Bestellnummer";
$LANG['plugin_order'][1] = "Bestelldatum";
$LANG['plugin_order'][2] = "Beschreibung";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "Detail Lieferant";
$LANG['plugin_order'][5] = "Freigabe";
$LANG['plugin_order'][6] = "Lieferung";
$LANG['plugin_order'][7] = "Bestellung";
$LANG['plugin_order'][8] = "Other item";
$LANG['plugin_order'][9] = "Other type of item";
$LANG['plugin_order'][10] = "Quality";
$LANG['plugin_order'][11] = "Verbundene Bestellungen";
$LANG['plugin_order'][12] = "Verbrauchtes Budget";
$LANG['plugin_order'][13] = "Total Netto";
$LANG['plugin_order'][14] = "Total Brutto";
$LANG['plugin_order'][15] = "Total Netto + Porto";
$LANG['plugin_order'][25] = "MwSt";
$LANG['plugin_order'][26] = "Porto";
$LANG['plugin_order'][28] = "Rechnung";
$LANG['plugin_order'][30] = "Angebot";
$LANG['plugin_order'][31] = "Auftragsbestätigung";
$LANG['plugin_order'][32] = "Zahlungskonditionen";
$LANG['plugin_order'][39] = "Name der Bestellung";
$LANG['plugin_order'][40] = "Lieferort";
$LANG['plugin_order'][42] = "Ein Gerät kann nicht mehreren Positionen zugewiesen werden";
$LANG['plugin_order'][44] = "Die Bestellnummer ist Pflicht!";
$LANG['plugin_order'][45] = "Noch nicht empfangene Geräte können nicht erzeugt werden";
$LANG['plugin_order'][46] = "Noch nicht empfangene Geräte können nicht verbunden werden";
$LANG['plugin_order'][47] = "Informationen über die Bestellung";
$LANG['plugin_order'][48] = "Eine oder mehrere ausgewählte Positionen wurden noch keinem Gerät zugeordnet";
$LANG['plugin_order'][49] = "The order date must be within the dates entered for the selected budget.";
$LANG['plugin_order'][50] = "Estimated due date";
$LANG['plugin_order'][51] = "Due date overtaken";
$LANG['plugin_order'][52] = "Unlink";
$LANG['plugin_order'][53] = "Delivery date";
$LANG['plugin_order'][54] = "Due date overtake";
$LANG['plugin_order'][55] = "Late orders";
$LANG['plugin_order'][56] = "Author";
$LANG['plugin_order'][57] = "Author group";
$LANG['plugin_order'][58] = "Recipient";
$LANG['plugin_order'][59] = "Recipient group";
$LANG['plugin_order'][60] = "Order item";

$LANG['plugin_order']['bill'][0] = "Bill";
$LANG['plugin_order']['bill'][1] = "Bill type";
$LANG['plugin_order']['bill'][2] = "Bill status";
$LANG['plugin_order']['bill'][3] = "A bill number is mandatory";
$LANG['plugin_order']['bill'][4] = "Bills";
$LANG['plugin_order']['bill'][5] = "Payment status";
$LANG['plugin_order']['bill'][6] = "Paid";
$LANG['plugin_order']['bill'][7] = "Being paid";

$LANG['plugin_order']['config'][0] = "Plugin-Einstellungen";
$LANG['plugin_order']['config'][1] = "Standard-MwSt";
$LANG['plugin_order']['config'][2] = "Freigabeprozedur verwenden";
$LANG['plugin_order']['config'][2] = "Use validation process";
$LANG['plugin_order']['config'][3] = "Automatic actions when delivery";
$LANG['plugin_order']['config'][4] = "Enable automatic generation";
$LANG['plugin_order']['config'][5] = "Default name";
$LANG['plugin_order']['config'][6] = "Default serial number";
$LANG['plugin_order']['config'][7] = "Default inventory number";
$LANG['plugin_order']['config'][8] = "Default entity";
$LANG['plugin_order']['config'][9] = "Default category";
$LANG['plugin_order']['config'][10] = "Default title";
$LANG['plugin_order']['config'][11] = "Default description";
$LANG['plugin_order']['config'][12] = "Default state";
$LANG['plugin_order']['config'][13] = "Order lifecycle";
$LANG['plugin_order']['config'][14] = "State before validation";
$LANG['plugin_order']['config'][15] = "Waiting for validation state";
$LANG['plugin_order']['config'][16] = "Validated order state";
$LANG['plugin_order']['config'][17] = "Order being delivered state";
$LANG['plugin_order']['config'][18] = "Order delivered state";
$LANG['plugin_order']['config'][19] = "Canceled order state";
$LANG['plugin_order']['config'][20] = "No VAT";
$LANG['plugin_order']['config'][21] = "Order paied state";
$LANG['plugin_order']['config'][22] = "Order generation in ODT";
$LANG['plugin_order']['config'][23] = "Activate suppliers quality satisfaction";
$LANG['plugin_order']['config'][24] = "Display order's suppliers informations";
$LANG['plugin_order']['config'][25] = "Color to be displayed when order due date is overtaken";
$LANG['plugin_order']['config'][26] = "Copy order documents when a new item is created";
$LANG['plugin_order']['config'][27] = "Default heading when adding a document to an order";

$LANG['plugin_order']['delivery'][1] = "Warenempfang";
$LANG['plugin_order']['delivery'][2] = "Geräte empfangen";
$LANG['plugin_order']['delivery'][3] = "Verbundene Geräte erzeugen";
$LANG['plugin_order']['delivery'][4] = "Geräte Batch-Empfang";
$LANG['plugin_order']['delivery'][5] = "Empfangene Geräte";
$LANG['plugin_order']['delivery'][6] = "Zu empfangende Geräte";
$LANG['plugin_order']['delivery'][9] = "Erzeugen";
$LANG['plugin_order']['delivery'][11] = "Mit bereits existierendem Gerät verbinden";
$LANG['plugin_order']['delivery'][12] = "Verbindung mit Gerät entfernen";
$LANG['plugin_order']['delivery'][13] = "Aus Bestellung erzeugte Geräte";
$LANG['plugin_order']['delivery'][14] = "Mit Bestellung verbundene Geräte";
$LANG['plugin_order']['delivery'][15] = "Von Bestellung gelöste Geräte";
$LANG['plugin_order']['delivery'][16] = "Bereits im Inventar aufgenommene Geräte";
$LANG['plugin_order']['delivery'][17] = "Es kann kein Gerät erzeugt werden";

$LANG['plugin_order']['detail'][1] = "Gerätetyp";
$LANG['plugin_order']['detail'][2] = "Referenz";
$LANG['plugin_order']['detail'][4] = "Nettostückpreis";
$LANG['plugin_order']['detail'][5] = "Der Bestellung hinzufügen";
$LANG['plugin_order']['detail'][6] = "Typ";
$LANG['plugin_order']['detail'][7] = "Menge";
$LANG['plugin_order']['detail'][18] = "Nettostückpreis mit Rabatt";
$LANG['plugin_order']['detail'][19] = "Status";
$LANG['plugin_order']['detail'][20] = "Kein Gerät kann empfangen werden";
$LANG['plugin_order']['detail'][21] = "Lieferdatum";
$LANG['plugin_order']['detail'][25] = "Rabatt in %";
$LANG['plugin_order']['detail'][27] = "Lieferanten wählen";
$LANG['plugin_order']['detail'][29] = "Kein Gerät wurde ausgewählt";
$LANG['plugin_order']['detail'][30] = "Geräte erfolgreich erzeugt";
$LANG['plugin_order']['detail'][31] = "Geräte erfolgreich empfangen";
$LANG['plugin_order']['detail'][32] = "Bereits empfangene Geräte";
$LANG['plugin_order']['detail'][33] = "Der Rabattsatz muss zwischen 0 und 100 liegen";
$LANG['plugin_order']['detail'][34] = "Referenz hinzufügen";
$LANG['plugin_order']['detail'][35] = "Referenz entfernen";
$LANG['plugin_order']['detail'][36] = "Wollen Sie wirklich diese Positionen entfernen? Bereits gelieferte Geräte werden von dieser Bestellung getrennt!";
$LANG['plugin_order']['detail'][37] = "Es gibt nicht genügend zu empfangene Geräte";
$LANG['plugin_order']['detail'][38] = "Wollen Sie diese Bestellung wirklich stornieren? Dies kann nicht rückgängig gemacht werden!";
$LANG['plugin_order']['detail'][39] = "Wollen Sie den Freigabeantrag wirklich rückgängig machen?";
$LANG['plugin_order']['detail'][40] = "Wollen Sie die Bestellung wirklich Bearbeiten?";
$LANG['plugin_order']['detail'][41] = "Do you really want to update this item ? ";

$LANG['plugin_order']['generation'][0] = "Erzeugen";
$LANG['plugin_order']['generation'][1] = "Lieferschein erzeugen";
$LANG['plugin_order']['generation'][2] = "Lieferschein";
$LANG['plugin_order']['generation'][3] = "Rechnungsadresse";
$LANG['plugin_order']['generation'][4] = "Lieferadresse";
$LANG['plugin_order']['generation'][5] = "Der";
$LANG['plugin_order']['generation'][6] = "Menge";
$LANG['plugin_order']['generation'][7] = "Beschreibung";
$LANG['plugin_order']['generation'][8] = "Stückpreis";
$LANG['plugin_order']['generation'][9] = "Nettobetrag";
$LANG['plugin_order']['generation'][10] = "Auftraggeber";
$LANG['plugin_order']['generation'][11] = "Empfänger";
$LANG['plugin_order']['generation'][12] = "Bestellnummer";
$LANG['plugin_order']['generation'][13] = "Rabatt";
$LANG['plugin_order']['generation'][14] = "TOTAL netto";
$LANG['plugin_order']['generation'][15] = "TOTAL brutto";
$LANG['plugin_order']['generation'][16] = "Unterschrift des Auftraggebers";
$LANG['plugin_order']['generation'][17] = "€";

$LANG['plugin_order']['history'][2] = "Hinzugefügt";
$LANG['plugin_order']['history'][4] = "Gelöscht";

$LANG['plugin_order']['infocom'][1] = "Einige Felder können nicht verändert werden: sie stammen aus einer Bestellung";

$LANG['plugin_order']['item'][0] = "Verbundene Geräte";
$LANG['plugin_order']['item'][2] = "Keine verbundene Geräte";

$LANG['plugin_order']['mailing'][2] = "von";

$LANG['plugin_order']['menu'][0] = "Menü";
$LANG['plugin_order']['menu'][1] = "Bestellungen verwalten";
$LANG['plugin_order']['menu'][2] = "Liste der Produktreferenzen verwalten";
$LANG['plugin_order']['menu'][4] = "Bestellungen";
$LANG['plugin_order']['menu'][5] = "Referenzen";
$LANG['plugin_order']['menu'][6] = "Bills";

$LANG['plugin_order']['parser'][1] = "Use this model";
$LANG['plugin_order']['parser'][2] = "No file found into the folder";
$LANG['plugin_order']['parser'][3] = "Use this sign";
$LANG['plugin_order']['parser'][4] = "Thanks to select a model into your preferences";

$LANG['plugin_order']['profile'][0] = "Berechtigungsverwaltung";
$LANG['plugin_order']['profile'][1] = "Bestellung freigeben";
$LANG['plugin_order']['profile'][2] = "Bestellung stornieren";
$LANG['plugin_order']['profile'][3] = "Freigegebene Bestellung ändern";
$LANG['plugin_order']['profile'][4] = "Link order to a ticket";

$LANG['plugin_order']['reference'][1] = "Produktreferenz";
$LANG['plugin_order']['reference'][2] = "Produktreferenz hinzufügen";
$LANG['plugin_order']['reference'][3] = "Liste der Referenzen";
$LANG['plugin_order']['reference'][5] = "Lieferant für eine Referenz";
$LANG['plugin_order']['reference'][6] = "Eine Referenz mit gleichem Namen existiert bereits";
$LANG['plugin_order']['reference'][7] = "Aktuell benutzte Referenzen";
$LANG['plugin_order']['reference'][8] = "Eine Referenz ohne Namen kann nicht erzeugt werden";
$LANG['plugin_order']['reference'][9] = "Eine Referenz ohne Typ kann nicht erzeugt werden";
$LANG['plugin_order']['reference'][10] = "Produktreferenz des Lieferanten";
$LANG['plugin_order']['reference'][11] = "View by item type";
$LANG['plugin_order']['reference'][12] = "Select the wanted item type";
$LANG['plugin_order']['reference'][13] = "Copy reference";
$LANG['plugin_order']['reference'][14] = "Copy of";

$LANG['plugin_order']['status'][0] = "Status";
$LANG['plugin_order']['status'][1] = "Wird geliefert";
$LANG['plugin_order']['status'][2] = "Geliefert";
$LANG['plugin_order']['status'][3] = "Delivery status";
$LANG['plugin_order']['status'][4] = "No specified status";
$LANG['plugin_order']['status'][7] = "Freigabe pendent";
$LANG['plugin_order']['status'][8] = "Empfangen";
$LANG['plugin_order']['status'][9] = "In Bearbeitung";
$LANG['plugin_order']['status'][10] = "Storniert";
$LANG['plugin_order']['status'][11] = "Lieferung pendent";
$LANG['plugin_order']['status'][12] = "Genehmigt";
$LANG['plugin_order']['status'][13] = "Delivery statistics";
$LANG['plugin_order']['status'][14] = "the order is validated, any update is forbidden";
$LANG['plugin_order']['status'][15] = "You cannot remove this status";
$LANG['plugin_order']['status'][16] = "Paid";
$LANG['plugin_order']['status'][17] = "Not paid";
$LANG['plugin_order']['status'][18] = "Paid value";
$LANG['plugin_order']['status'][19] = "Billing summary";
$LANG['plugin_order']['status'][20] = "Order is late";

$LANG['plugin_order']['survey'][0] = "Supplier quality";
$LANG['plugin_order']['survey'][1] = "Administrative followup quality (contracts, bills, mail, etc.)";
$LANG['plugin_order']['survey'][2] = "Commercial followup quality, visits, responseness";
$LANG['plugin_order']['survey'][3] = "Contacts availability";
$LANG['plugin_order']['survey'][4] = "Quality of supplier intervention";
$LANG['plugin_order']['survey'][5] = "Reliability about annouced delays";
$LANG['plugin_order']['survey'][6] = "Really unsatisfied";
$LANG['plugin_order']['survey'][7] = "Really satisfied";
$LANG['plugin_order']['survey'][8] = "Average mark up to 10 (X points / 5)";
$LANG['plugin_order']['survey'][9] = "Final supplier note";
$LANG['plugin_order']['survey'][10] = "Note";
$LANG['plugin_order']['survey'][11] = "Comment on survey";

$LANG['plugin_order']['validation'][0] = "Mindestens eine Bestellposition wird benötigt.";
$LANG['plugin_order']['validation'][1] = "Freigabeantrag der Bestellung";
$LANG['plugin_order']['validation'][2] = "Freigabe der Bestellung";
$LANG['plugin_order']['validation'][3] = "Bestellung wird geliefert";
$LANG['plugin_order']['validation'][4] = "Bestellung wurde komplett geliefert";
$LANG['plugin_order']['validation'][5] = "Bestellung wurde storniert";
$LANG['plugin_order']['validation'][6] = "Freigabeprozedur einer Bestellung";
$LANG['plugin_order']['validation'][7] = "Freigabeantrag der Bestellung wurde gestellt";
$LANG['plugin_order']['validation'][8] = "Commande en cours d'édition";
$LANG['plugin_order']['validation'][9] = "Bestellung freigeben";
$LANG['plugin_order']['validation'][10] = "Bestellung ist freigegeben worden";
$LANG['plugin_order']['validation'][11] = "Bestellung freigeben lassen";
$LANG['plugin_order']['validation'][12] = "Bestellung stornieren";
$LANG['plugin_order']['validation'][13] = "Freigabeantrag rückgängig machen";
$LANG['plugin_order']['validation'][14] = "Freigabeantrag wurde rückgängig gemacht";
$LANG['plugin_order']['validation'][15] = "Bestellung in Bearbeitung";
$LANG['plugin_order']['validation'][16] = "Freigabe wurde rückgängig gemacht";
$LANG['plugin_order']['validation'][17] = "Bestellung bearbeiten";
$LANG['plugin_order']['validation'][18] = "Comment of validation";
$LANG['plugin_order']['validation'][19] = "Editor of validation";

$LANG['plugin_order']['budget_over'][0] = "Total orders related with this budget is greater than its value.";
$LANG['plugin_order']['budget_over'][1] = "Total orders related with this budget is equal to its value.";

$LANG['plugin_order']['install'][0] = "Plugin installation or upgrade";
?>