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
 
$LANG['plugin_order']['title'][1] = "Gestione Ordini";

$LANG['plugin_order'][0] = "Numero Ordine";
$LANG['plugin_order'][1] = "Data Ordine";
$LANG['plugin_order'][2] = "Descrizione";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "Info Fornitori";
$LANG['plugin_order'][5] = "Validazione";
$LANG['plugin_order'][6] = "Consegna";
$LANG['plugin_order'][7] = "Ordine";
$LANG['plugin_order'][8] = "Altre attrezzature";
$LANG['plugin_order'][9] = "Tipo altre attrezzature";
$LANG['plugin_order'][10] = "Quality";
$LANG['plugin_order'][11] = "Ordini collegati";
$LANG['plugin_order'][12] = "Budget gia' utilizzato";
$LANG['plugin_order'][13] = "Importo senza IVA";
$LANG['plugin_order'][14] = "Importo (iva + sconto)";
$LANG['plugin_order'][15] = "Importo Totale + Spedizione";
$LANG['plugin_order'][25] = "IVA";
$LANG['plugin_order'][26] = "Spedizione";
$LANG['plugin_order'][28] = "Numero Fattura";
$LANG['plugin_order'][30] = "Numero Interno";
$LANG['plugin_order'][31] = "Numero Ordine";
$LANG['plugin_order'][32] = "Condizioni di pagamento";
$LANG['plugin_order'][39] = "Ordine";
$LANG['plugin_order'][40] = "Indirizzo Spedizione";
$LANG['plugin_order'][42] = "Non possibile collegare più dettagli articoli in una sola linea";
$LANG['plugin_order'][44] = "Numero di Ordine OBBLIGATORIO!";
$LANG['plugin_order'][45] = "NON posso INVENTARIARE Articoli non spediti";
$LANG['plugin_order'][46] = "NON posso ASSOCIARE Articoli non spediti";
$LANG['plugin_order'][47] = "Informazioni Ordine";
$LANG['plugin_order'][48] = "Uno o più righe selezionate non hanno materiali associati";
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

$LANG['plugin_order']['config'][0] = "Configurazione Plugin";
$LANG['plugin_order']['config'][1] = "Default IVA";
$LANG['plugin_order']['config'][2] = "Usare Processo Verifica";
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
$LANG['plugin_order']['config'][23] = "Activate suppliers quality satisfaction";
$LANG['plugin_order']['config'][24] = "Display order's suppliers informations";
$LANG['plugin_order']['config'][25] = "Color to be displayed when order due date is overtaken";
$LANG['plugin_order']['config'][26] = "Copy order documents when a new item is created";
$LANG['plugin_order']['config'][27] = "Default heading when adding a document to an order";

$LANG['plugin_order']['delivery'][1] = "Ricezione Articoli";
$LANG['plugin_order']['delivery'][2] = "Articolo preso in consegna";
$LANG['plugin_order']['delivery'][3] = "Carica in inventario";
$LANG['plugin_order']['delivery'][4] = "Ricezione in massa di Articoli";
$LANG['plugin_order']['delivery'][5] = "Consegna Articoli";
$LANG['plugin_order']['delivery'][6] = "Numero Consegna";
$LANG['plugin_order']['delivery'][9] = "Generato";
$LANG['plugin_order']['delivery'][11] = "Collegamento a Entità esistente";
$LANG['plugin_order']['delivery'][12] = "Cancellazione collegamento articolo";
$LANG['plugin_order']['delivery'][13] = "Articolo generato da ordine";
$LANG['plugin_order']['delivery'][14] = "Articolo collegato a Ordine";
$LANG['plugin_order']['delivery'][15] = "Articolo scollegato a ordine";
$LANG['plugin_order']['delivery'][16] = "Articolo collegato ad altro Ordine";
$LANG['plugin_order']['delivery'][17] = "Nessun Articolo Generato";

$LANG['plugin_order']['detail'][1] = "Attrezzature";
$LANG['plugin_order']['detail'][2] = "Riferimento";
$LANG['plugin_order']['detail'][4] = "Prezzo Unitario senza IVA";
$LANG['plugin_order']['detail'][5] = "Aggiunto all'ordine";
$LANG['plugin_order']['detail'][6] = "Tipo";
$LANG['plugin_order']['detail'][7] = "Quantità";
$LANG['plugin_order']['detail'][18] = "Importo scontato unit. senza IVA";
$LANG['plugin_order']['detail'][19] = "Status";
$LANG['plugin_order']['detail'][20] = "Nessun  articolo da prendere in consegna";
$LANG['plugin_order']['detail'][21] = "Data Spedizione";
$LANG['plugin_order']['detail'][25] = "Sconto in %";
$LANG['plugin_order']['detail'][27] = "Selezionare Fornitore";
$LANG['plugin_order']['detail'][29] = "Nessun Articolo selezionato";
$LANG['plugin_order']['detail'][30] = "Articolo selezionato";
$LANG['plugin_order']['detail'][31] = "Articolo ricevuto";
$LANG['plugin_order']['detail'][32] = "Articolo GIA ricevuto";
$LANG['plugin_order']['detail'][33] = "La % sconto deve essere compresa tra 0 e 100";
$LANG['plugin_order']['detail'][34] = "Aggiungi riferimento";
$LANG['plugin_order']['detail'][35] = "Rimuovi Riferimento";
$LANG['plugin_order']['detail'][36] = "Vuoi cancellare questo(i) dettaglio(i)? Articolo(i) in consegna sarà(nno) CANCELLATO(I)! ";
$LANG['plugin_order']['detail'][37] = "Nessun articolo in consegna";
$LANG['plugin_order']['detail'][38] = "Vuoi CANCELLARE questo Ordine  ? Questa opzione è IRREVERSIBILE !";
$LANG['plugin_order']['detail'][39] = "Vuoi CANCELLARE il processo di Validazione ?";
$LANG['plugin_order']['detail'][40] = "Vuoi modificare l'ORDINE ? ";
$LANG['plugin_order']['detail'][41] = "Do you really want to update this item ? ";

$LANG['plugin_order']['generation'][0] = "Generazione";
$LANG['plugin_order']['generation'][1] = "Generazione Ordine";
$LANG['plugin_order']['generation'][2] = "Ordine";
$LANG['plugin_order']['generation'][3] = "Indirizzo Fatturazione";
$LANG['plugin_order']['generation'][4] = "Indirizzo Spedizione";
$LANG['plugin_order']['generation'][5] = "il";
$LANG['plugin_order']['generation'][6] = "Quantità";
$LANG['plugin_order']['generation'][7] = "Denominazione";
$LANG['plugin_order']['generation'][8] = "Importo unitario";
$LANG['plugin_order']['generation'][9] = "Importo Totale (senza tasse)";
$LANG['plugin_order']['generation'][10] = "Emissione Ordine";
$LANG['plugin_order']['generation'][11] = "Destinatario";
$LANG['plugin_order']['generation'][12] = "N° Ordine";
$LANG['plugin_order']['generation'][13] = "% Sconto";
$LANG['plugin_order']['generation'][14] = "Totale (no IVA)";
$LANG['plugin_order']['generation'][15] = "Totale con IVA";
$LANG['plugin_order']['generation'][16] = "Firma emettitore ordine";
$LANG['plugin_order']['generation'][17] = "€";

$LANG['plugin_order']['history'][2] = "Aggiungere";
$LANG['plugin_order']['history'][4] = "Cancellare";

$LANG['plugin_order']['item'][0] = "Elementi Associati";
$LANG['plugin_order']['item'][2] = "Nessun elemento Associato";

$LANG['plugin_order']['infocom'][1] = "Alcuni elementi non possono essere cancellati, fanno parte di un ordine";

$LANG['plugin_order']['mailing'][2] = "da";

$LANG['plugin_order']['menu'][0] = "Menu";
$LANG['plugin_order']['menu'][1] = "Gestione Ordini";
$LANG['plugin_order']['menu'][2] = "Gestione Riferimenti Prodotti";
$LANG['plugin_order']['menu'][4] = "Ordini";
$LANG['plugin_order']['menu'][5] = "Riferimenti";
$LANG['plugin_order']['menu'][6] = "Bills";

$LANG['plugin_order']['parser'][1] = "Usa questo modello";
$LANG['plugin_order']['parser'][2] = "Nessun file nel folder";
$LANG['plugin_order']['parser'][3] = "Usa questa firma";
$LANG['plugin_order']['parser'][4] = "Grazie per aver selezionato il modello preferito";

$LANG['plugin_order']['profile'][0] = "Gestione Privilegi";
$LANG['plugin_order']['profile'][1] = "Validazione Ordine";
$LANG['plugin_order']['profile'][2] = "Cancellazione Ordine";
$LANG['plugin_order']['profile'][3] = "Modifica Ordine verificato";
$LANG['plugin_order']['profile'][4] = "Link order to a ticket";

$LANG['plugin_order']['reference'][1] = "Riferimento Prodotto";
$LANG['plugin_order']['reference'][2] = "Aggiungi un Fornitore";
$LANG['plugin_order']['reference'][3] = "Lista dei Riferimenti";
$LANG['plugin_order']['reference'][5] = "Riferimento Fornitore";
$LANG['plugin_order']['reference'][6] = "Gia esiste un riferimento con lo stesso nome";
$LANG['plugin_order']['reference'][7] = "Riferimento(i) già utilizzato(i)";
$LANG['plugin_order']['reference'][8] = "Non posso creare riferimenti senza Nome";
$LANG['plugin_order']['reference'][9] = "Non posso creare riferimenti senza Tipo";
$LANG['plugin_order']['reference'][10] = "Riferimento prodotti per Produttore";
$LANG['plugin_order']['reference'][11] = "Vista per tipo";
$LANG['plugin_order']['reference'][12] = "Seleziona il tipo voluto";
$LANG['plugin_order']['reference'][13] = "Copy reference";
$LANG['plugin_order']['reference'][14] = "Copy of";

$LANG['plugin_order']['status'][0] = "Status Ordine";
$LANG['plugin_order']['status'][1] = "In corso di consegna";
$LANG['plugin_order']['status'][2] = "Consegnato";
$LANG['plugin_order']['status'][3] = "Status spedizione";
$LANG['plugin_order']['status'][4] = "Nessun status specificato";
$LANG['plugin_order']['status'][7] = "In approvazione";
$LANG['plugin_order']['status'][8] = "Ricevuta";
$LANG['plugin_order']['status'][9] = "In compilazione";
$LANG['plugin_order']['status'][10] = "Annullato";
$LANG['plugin_order']['status'][11] = "In Attesa Spedizione";
$LANG['plugin_order']['status'][12] = "Approvato";
$LANG['plugin_order']['status'][13] = "Statistiche spedizione";
$LANG['plugin_order']['status'][14] = "the order is validated, any update is forbidden";
$LANG['plugin_order']['status'][15] = "You cannot remove this status";
$LANG['plugin_order']['status'][16] = "Paid";
$LANG['plugin_order']['status'][17] = "Not paid";
$LANG['plugin_order']['status'][18] = "Paid value";
$LANG['plugin_order']['status'][19] = "Billing summary";
$LANG['plugin_order']['status'][20] = "Order is late";

$LANG['plugin_order']['survey'][0] = "Qualità Fornitori";
$LANG['plugin_order']['survey'][1] = "Qualità monitoraggio amministrativo (contratti, fatture, corrieri...)";
$LANG['plugin_order']['survey'][2] = "Qualità monitoraggio commerciale, frequenza di visita, reattività";
$LANG['plugin_order']['survey'][3] = "Disponibilité des interlocuteurs fournisseur";
$LANG['plugin_order']['survey'][4] = "Qualità delle prestazioni dei collaboratori dei fornitori";
$LANG['plugin_order']['survey'][5] = "Affidabilità sulle disponibilità indicate";
$LANG['plugin_order']['survey'][6] = "Molto insoddisfatto";
$LANG['plugin_order']['survey'][7] = "Molto soddisfatto";
$LANG['plugin_order']['survey'][8] = "Voto medio su 10 (X punti / 5)";
$LANG['plugin_order']['survey'][9] = "Voto globale sul fornitore";
$LANG['plugin_order']['survey'][10] = "Note";
$LANG['plugin_order']['survey'][11] = "Commenti sul sondaggio";

$LANG['plugin_order']['validation'][0] = "Grazie per aver aggiunto attrezzature all ordine";
$LANG['plugin_order']['validation'][1] = "Richiesta Accettazione Ordine";
$LANG['plugin_order']['validation'][2] = "Ordine Accettato";
$LANG['plugin_order']['validation'][3] = "Ordine in attesa di spedizione";
$LANG['plugin_order']['validation'][4] = "Ordine Spedito";
$LANG['plugin_order']['validation'][5] = "Ordine Cancellato";
$LANG['plugin_order']['validation'][6] = "Processo di Verifica";
$LANG['plugin_order']['validation'][7] = "Verifica Ordine eseguita";
$LANG['plugin_order']['validation'][8] = "Commande en cours d'édition";
$LANG['plugin_order']['validation'][9] = "Verifica Ordine";
$LANG['plugin_order']['validation'][10] = "Ordine Verificato";
$LANG['plugin_order']['validation'][11] = "Richiesta Verifica";
$LANG['plugin_order']['validation'][12] = "Cancellazione Ordine";
$LANG['plugin_order']['validation'][13] = "Cancellazione domanda validazione";
$LANG['plugin_order']['validation'][14] = "Domanda di validazione cancellata";
$LANG['plugin_order']['validation'][15] = "Ordine in compilazione";
$LANG['plugin_order']['validation'][16] = "Annullamento validazione effettuata";
$LANG['plugin_order']['validation'][17] = "Modifica Ordine";
$LANG['plugin_order']['validation'][18] = "Commento sulla validazione";
$LANG['plugin_order']['validation'][19] = "Editor of validation";

$LANG['plugin_order']['budget_over'][0] = "Total orders related with this budget is greater than its value.";
$LANG['plugin_order']['budget_over'][1] = "Total orders related with this budget is equal to its value.";

$LANG['plugin_order']['install'][0] = "Plugin installation or upgrade";
?>