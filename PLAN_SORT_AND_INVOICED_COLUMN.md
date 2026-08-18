# Plan wykonawczy: pakiet zmian w pluginie Order (sortowanie, zafakturowanie, OT, przypomnienia)

> **Wykonawca:** sesja Claude (Opus) na tym repozytorium.
> **Gałąź robocza:** nowa gałąź od `main` (to funkcje, nie bugfix — nie mieszać z gałęzią `claude/peaceful-cori-8go1w`).
> **Cel GLPI:** 11.0.x (`setup.php`: MIN `11.0.0`, MAX `11.0.99`). Wersja pluginu obecnie: `2.13.1` (`setup.php:33`) → po tym pakiecie **2.14.0**.
> **Locale:** `locales/pl_PL.po` + `pl_PL.mo` istnieją — każdy nowy string wymaga wpisu + `msgfmt locales/pl_PL.po -o locales/pl_PL.mo`.
>
> Odwołania plik:linia dotyczą stanu repo na 2026-08-18 (commit `db5a16f`). Na `main` numery mogą się różnić o kilka linii — dopasuj po treści. Wszystkie mechanizmy rdzenia GLPI zweryfikowano w źródłach 11.0.4.

## Zakres (6 funkcji)

| # | Funkcja | Rozmiar |
|---|---------|---------|
| A | Domyślne sortowanie listy zamówień: najnowsze na górze | S |
| B | Kolumna „Zafakturowane" w ustawieniach widoku kolumn | S |
| C | Popup przy „Generuj OT": Invoice Number, MPK, Order, 2 daty (opcjonalne) | M |
| D | Nowa akcja „Fakturowanie" (faktura bez pliku OT) + PL tłumaczenie „Generate OT" | M |
| E | Cron: przypomnienia e-mail o niezafakturowanych zamówieniach (wiele progów dniowych, adresy z konfiguracji) | L |
| F | Wiersze pozycji zamówienia domyślnie rozwinięte | XS |

Zalecana kolejność implementacji i commitów: **F → A → B → C → D → E** (od najprostszych; C przed D, bo D reuse'uje refaktor z C).

---

## A — domyślne sortowanie: najnowsze na górze

### Fakty (zweryfikowane)
- Lista = `Search::show("PluginOrderOrder")` (`front/order.php:46`).
- `glpi_plugin_order_orders` **nie ma** `date_creation`; ma `date_mod` (indeks), `order_date`, `duedate`, `deliverydate` (schemat: `inc/order.class.php:2671-2721`).
- Search options: `rawSearchOptions()` `inc/order.class.php:329-594`. Zajęte ID: 1-17, 24-28, 31, 35 (date_mod), 80, 86, 87.
- GLPI 11: domyślne parametry listy = itemtype implementuje **`Glpi\Search\DefaultSearchRequestInterface`** z `public static function getDefaultSearchRequest(): array`. Konsumpcja w `Glpi\Search\Input\QueryBuilder::manageParams()` przez **`instanceof`** (NIE `method_exists`!). Klucze: `criteria`, `sort`, `order`. Wzorzec: `src/Ticket.php` (zwraca `['criteria'=>[...], 'sort'=>19, 'order'=>'DESC']`). Preferencje użytkownika (sesja/zapisane wyszukiwania) nadpisują default — pożądane.
- `CommonDBTM::add()` auto-wypełnia `date_creation`/`date_mod`, jeśli kolumny istnieją i nie podano ich w input — zero zmian w `prepareInputForAdd`.

### Kroki
1. **CREATE TABLE** (`inc/order.class.php` ~2705, obok `date_mod`): dodaj `` `date_creation` timestamp NULL default NULL, `` + `KEY \`date_creation\` (\`date_creation\`)`.
2. **Upgrade** — na końcu `PluginOrderOrder::install()` (po bloku `plugin_order_accountsections_id`, ~3096-3100):
   ```php
   if (!$DB->fieldExists($table, 'date_creation')) {
       $migration->addField($table, 'date_creation', 'timestamp NULL DEFAULT NULL', ['after' => 'date_mod']);
       $migration->addKey($table, 'date_creation');
       $migration->migrationOneTable($table);
       $DB->doQuery("UPDATE `glpi_plugin_order_orders`
                     SET `date_creation` = COALESCE(`order_date`, `date_mod`)
                     WHERE `date_creation` IS NULL");
   }
   ```
   (`order_date` jest typu `date` — rzutowanie na timestamp OK; rekordy z oboma NULL zostają NULL i w DESC lądują na końcu.)
3. **Search option ID 121** (konwencja rdzenia dla date_creation; wolne w pluginie):
   ```php
   ['id' => 121, 'table' => self::getTable(), 'field' => 'date_creation',
    'name' => __s('Creation date'), 'datatype' => 'datetime', 'massiveaction' => false],
   ```
4. **Interfejs + metoda** w `PluginOrderOrder`:
   `class PluginOrderOrder extends CommonDBTM implements \Glpi\Search\DefaultSearchRequestInterface` oraz
   ```php
   public static function getDefaultSearchRequest(): array
   {
       return ['sort' => 121, 'order' => 'DESC'];
   }
   ```
   Bez klucza `criteria` (nie chcemy domyślnego filtra). Sygnatura musi być identyczna z interfejsem.
5. Komunikacja: użytkownicy z zapamiętanym sortowaniem zobaczą nowe domyślne po resecie listy (gumka/relogin).

---

## B — kolumna „Zafakturowane"

### Fakty (zweryfikowane) — stan fakturowania JUŻ istnieje w modelu
- `glpi_plugin_order_orders.plugin_order_billstates_id` (schemat :2691, migracja :2994), wartości wyłącznie 0/1: `PluginOrderBillState::NOTPAID=0`, `PAID=1` (`inc/billstate.class.php:35-37`).
- Agregat utrzymuje `PluginOrderOrder::updateBillState()` (`inc/order.class.php:2257-2293`): PAID ⇔ **każda** pozycja ma stan ≠ NOTPAID (pozycja bez faktury = 0), czyli PAID = „w całości zafakturowane". Wołane z `front/bill.form.php:92` i `inc/ot.class.php:195`.
- Model: `glpi_plugin_order_bills.plugin_order_orders_id`; `glpi_plugin_order_orders_items.plugin_order_bills_id` + `plugin_order_billstates_id`.
- Zamówienie nie ma dziś ŻADNEJ search option dot. faktur. Każda search option automatycznie pojawia się w oknie „ustawienia widoku kolumn" — nic więcej nie trzeba rejestrować.

### Kroki
1. Search option **ID 88** (pierwsze wolne po 87) w `rawSearchOptions()`:
   ```php
   ['id' => 88, 'table' => self::getTable(), 'field' => 'plugin_order_billstates_id',
    'name' => __s('Invoiced', 'order'), 'datatype' => 'bool', 'massiveaction' => false],
   ```
   **NIE** `datatype => 'dropdown'` po tabeli billstates — 0/1 to stałe klasowe (0 nie ma wiersza w dropdownie; wyświetlanie agregatu w kodzie idzie przez `PluginOrderBillState::getState()`, np. `inc/order_item.class.php:1622`).
2. `pl_PL.po`: `msgid "Invoiced"` → `msgstr "Zafakturowane"`.
3. Znane ograniczenia (opisz w PR, nie koduj): (a) zamówienie bez pozycji ma default 0 → „Nie" dopóki ktoś nie uruchomi updateBillState; (b) updateBillState nie jest wołane przy dodaniu/usunięciu pozycji → możliwy stale „Tak" do następnej operacji fakturowej. Utwardzenie (wywołanie updateBillState w post_add/post_purge pozycji) zmienia też `plugin_order_orderstates_id` (config `order_status_paid`) — osobna decyzja użytkownika, poza zakresem.

---

## C — popup „Generuj OT" z polami: Invoice Number, MPK, Order, 2 daty

### Fakty (zweryfikowane)
- Akcja masowa `PluginOrderOrder:generate_ot` (label `__s('Generate OT','order')`, `inc/order.class.php:2585`); subform → `PluginOrderOt::showMassiveActionSubForm()` (`inc/order.class.php:2565-2568` → `inc/ot.class.php:39-56`) — **popup już istnieje** i ma pola `invoice_number` + `cost_center` (MPK).
- Przetwarzanie: `processMassiveActionsForOneItemtype()` case `generate_ot` (`inc/order.class.php:2616-2649`) → `PluginOrderOt::processAction(int $order_id, string $cost_center, string $invoice_number)` (`inc/ot.class.php:67-99`): generuje HTML (`generateOtHtml`), PDF, zapisuje Document, przy niepustym invoice_number tworzy Bill (`createBill` :109) i linkuje pozycje (`linkBillToOrderItems` :153) + `updateBillState`.
- Numer zamówienia w dokumencie OT: `generateOtHtml` bierze `$order->fields['num_order']` (ot.class.php, względna linia 7 od :207, abs ~213) i wstawia w komórki dokumentu.
- Dokument OT **ma już kolumny na te daty** (dziś puste): „Data włącz. do użytku / Datum Inbetriebnahme" i „Data zdep. w magazynie / Datum ins Lager" — nagłówki abs ~375-376 oraz drugi blok ~423-424.

### Kroki
1. **Subform** (`PluginOrderOt::showMassiveActionSubForm()`): dodaj pola:
   - `ot_num_order` (text, label `__s('Order number', 'order')`): jeśli w akcji wybrano **dokładnie jedno** zamówienie — prefill jego `num_order`; przy wielu zostaw puste (puste = użyj `num_order` danego zamówienia). Selekcja jest dostępna w `$ma->POST['items']['PluginOrderOrder']` — wzorzec odczytu: `PluginOrderLink::showMassiveActionsSubForm()` (`inc/link.class.php:559-580`) i sygnatura przekazania `$ma` z `inc/order.class.php:2565` (trzeba przekazać `$ma` do subformu — zmień sygnaturę na `showMassiveActionSubForm(MassiveAction $ma)`).
   - `date_warehouse` (label `__s('Warehouse deposit date', 'order')` / PL „Data zdep. w magazynie") — `Html::showDateField('date_warehouse', ['value' => '', 'required' => false])` (lub `type=date` input, spójnie z resztą).
   - `date_usage` (label `__s('Commissioning date', 'order')` / PL „Data włącz. do użytku") — jw.
   - **Obie daty opcjonalne niezależnie** (magazyn vs od razu do użytku). Bez twardej walidacji „min. jedna" — dopuszczamy obie puste (zachowanie jak dziś).
2. **Refaktor `processAction`** na tablicę parametrów: `processAction(int $order_id, array $params)` z kluczami `cost_center`, `invoice_number`, `num_order` (override, '' = z zamówienia), `date_warehouse`, `date_usage`. Zaktualizuj jedyne wywołanie (`inc/order.class.php:2627`).
3. **`generateOtHtml`**: przyjmij i wykorzystaj: (a) override numeru zamówienia zamiast bezwarunkowego `$order->fields['num_order']`; (b) wpisz `date_warehouse`/`date_usage` (format `Html::convDate`) w istniejące kolumny dat — w OBU tabelach dokumentu (~375-376 i ~423-424); puste → komórka pusta jak dziś. Nazwa pliku (`OT_...`, `processAction` :75-76) też powinna użyć override'u.
4. **Handler MA** (`inc/order.class.php:2620-2622`): odczytaj nowe pola z `$ma->getInput()` i przekaż w `$params`.
5. `pl_PL.po`: nowe stringi (labels dat, ewentualnie „Order number" już ma tłumaczenie w rdzeniu).

---

## D — akcja „Fakturowanie" (bez pliku OT) + PL dla „Generate OT"

### Kroki
1. **Wydziel ścieżkę fakturowania** w `PluginOrderOt`: nowa publiczna metoda:
   ```php
   public function processInvoiceOnly(int $order_id, string $invoice_number)
   ```
   = `getFromDB` + `createBill()` + (w środku) `linkBillToOrderItems()` + `updateBillState` — czyli dokładnie `processAction` MINUS generateOtHtml/PDF/Document. Prywatne `createBill`/`linkBillToOrderItems` zostają bez zmian (`inc/ot.class.php:109-196`).
2. **Rejestracja akcji** w `getSpecificMassiveActions()` (`inc/order.class.php:2584-2586`):
   ```php
   $actions['PluginOrderOrder:invoice'] = __s('Invoicing', 'order');
   ```
3. **Subform** w `showMassiveActionsSubForm()` (:2556-2571): case `invoice` → pole `invoice_number` (**wymagane** — atrybut `required` + walidacja serwerowa: puste ⇒ `ACTION_KO` z komunikatem) + submit. Można reużyć fragmentu subformu OT (wydziel wspólny helper w `PluginOrderOt`).
4. **Przetwarzanie** w `processMassiveActionsForOneItemtype()` (:2597-2650): case `invoice` → pętla po `$ids` → `processInvoiceOnly()`; komunikaty jak w OT („Bill #%s created", :2634), bez redirectu do pobrania dokumentu.
5. **Tłumaczenia** w `pl_PL.po` (+ `msgfmt`):
   - `Generate OT` → `Generuj OT` (string istnieje, brakuje tłumaczenia),
   - `Invoicing` → `Fakturowanie`,
   - `Invoice Number` → `Numer faktury`, `Cost Center` → `Centrum kosztów (MPK)` (jeśli nieprzetłumaczone).

---

## E — cron: przypomnienia o niezafakturowanych zamówieniach

### Fakty (zweryfikowane)
- Wzorzec crona pluginu: `cronComputeLateOrders($task)` (`inc/order.class.php:2382`) + `CronTask::Register(self::class, 'computeLateOrders', HOUR_TIMESTAMP, ['mode' => CronTask::MODE_EXTERNAL, ...])` przy instalacji (:2724) i guard przy upgrade (:3001).
- System powiadomień pluginu istnieje: `PluginOrderNotificationTargetOrder` (`inc/notificationtargetorder.class.php`, rejestrowany w `hook.php:61,126`). Eventy: ask, validation, cancel, undovalidation, **duedate**, delivered (:52-63). Tagi szablonu m.in. `##order.item.name##`, `##order.item.numorder##`, **`##order.item.url##`** (link!, :80), `##order.item.orderdate##` (:78-90). Seeding szablonów+notyfikacji przy instalacji: :221-462 (wzorzec `countElementsInTable("glpi_notifications", ...)` → idempotentny).
- Przykład raise: `NotificationEvent::raiseEvent('duedate', new PluginOrderOrder(), $options)` (`inc/order.class.php:2458`) — event „zbiorczy" z listą zamówień w `$options`, konsumowaną w `addDataForTemplate` (:65-95).
- Config: tabela `glpi_plugin_order_configs`, wzorzec migracji pól `inc/config.class.php:770+` (`$migration->addField(...)`), formularz `showForm()` :100-459, dostęp `PluginOrderConfig::getConfig()`.
- „Niezafakturowane" = `plugin_order_billstates_id != PluginOrderBillState::PAID` (patrz funkcja B).

### Decyzje projektowe
- **Jeden e-mail na zamówienie na próg** (nie digest) — bo odbiorcą jest autor KONKRETNEGO zamówienia + stałe adresy z konfiguracji.
- **Progi**: pole tekstowe CSV w konfiguracji, np. `14,30,60` (dni od `order_date`; gdy `order_date` NULL → `date_creation` z funkcji A). Puste pole = funkcja wyłączona.
- **Anty-duplikacja**: tabela-ledger `glpi_plugin_order_orders_reminders` (`id`, `plugin_order_orders_id`, `threshold_days` int, `date_creation` timestamp, UNIQUE(`plugin_order_orders_id`,`threshold_days`)). Wysłano → wpis; cron pomija istniejące pary.

### Kroki
1. **Config — migracja** (wzorzec :770+):
   - `not_invoiced_reminder_days` varchar(255) NOT NULL default '' (CSV progów),
   - `not_invoiced_reminder_emails` text (CSV dodatkowych adresów).
2. **Config — UI** w `showForm()`: sekcja „Przypomnienia o niezafakturowanych zamówieniach": dwa pola tekstowe + opis formatu (CSV). Walidacja przy zapisie: progi → tylko dodatnie inty, sort rosnąco, dedup; e-maile → `filter_var(..., FILTER_VALIDATE_EMAIL)`, odrzucone zgłoś komunikatem.
3. **Ledger** — CREATE TABLE w `PluginOrderOrder::install()` (albo osobna klasa wzorem innych tabel) + DROP w uninstall (lista tabel: `inc/order.class.php:3107`).
4. **Event** `'not_invoiced' => __s("Order not invoiced", "order")` w `getEvents()` (:52-63) + branch w `addDataForTemplate` (dane pojedynczego zamówienia: name, numorder, orderdate, **url**, entity, wartość, liczba dni po terminie — nowy tag np. `##order.item.dayselapsed##`) + **seeding** szablonu (temat: „Zamówienie ##order.item.numorder## nie zostało zafakturowane") i notyfikacji w `install()` klasy target (wzorzec :221-462, idempotentny). Szablon dwujęzyczny EN + tłumaczenie PL (NotificationTemplateTranslation — wzorzec w istniejącym seedingu).
5. **Odbiorcy**: autor zamówienia (`users_id`) — w klasie target obsłuż target „Author" (`$this->addUserByField('users_id')` w odpowiednim hooku targetów; zbadaj, jak istniejące eventy pluginu dodają odbiorców — sekcja :462+ — i zrób analogicznie) + dodatkowe adresy z configu przez `$this->addToRecipientsList(['email' => $addr, 'language' => $CFG_GLPI["language"]])` dla eventu `not_invoiced`.
6. **Cron** `cronNotInvoicedReminder($task)` w `PluginOrderOrder` (wzorzec :2382):
   - wczytaj progi z configu; puste → `return 0`;
   - kandydaci: `is_template=0 AND is_deleted=0 AND plugin_order_billstates_id != 1` oraz stan ≠ anulowane (`PluginOrderConfig::getCanceledState()`, patrz `inc/config.class.php:495`);
   - `age = DATEDIFF(NOW(), COALESCE(order_date, date_creation))`;
   - dla każdego progu `D` (rosnąco): jeżeli `age >= D` i brak wpisu w ledger → `NotificationEvent::raiseEvent('not_invoiced', $order, ['reminder_days' => D, ...])` → insert do ledger; `$task->addVolume(1)`;
   - rejestracja: `CronTask::Register(self::class, 'notInvoicedReminder', DAY_TIMESTAMP, ['mode' => CronTask::MODE_EXTERNAL])` w install + guard w upgrade (wzorzec :3001-3004).
7. **Wymagania środowiskowe** (opisz w PR): powiadomienia GLPI muszą być włączone (tryb mail skonfigurowany), cron systemowy dla MODE_EXTERNAL. Wyłączenie funkcji = puste pole progów.

---

## F — wiersze pozycji domyślnie rozwinięte

### Fakty (zweryfikowane)
- Mechanizm: szablony `templates/order_getitems.html.twig` (przycisk „Collapse all"/„Expand all" :43-59, JS :640+) sterowane zmienną `table_visible`; stan per kontener w `$_GET[$countainer_name . 'visible']`.
- Domyślnie ZWINIĘTE w dwóch miejscach: `inc/order_item.class.php:1208` i `inc/link.class.php:455` — `$visible = $_GET[...] ?? false;`. (Trzecie użycie, `inc/order_item.class.php:1085`, już ma `table_visible => true`.)

### Kroki
1. `inc/order_item.class.php:1208`: `?? false` → `?? true`.
2. `inc/link.class.php:455`: `?? false` → `?? true`.
3. Uwaga: `$_GET[...]'visible'` przychodzi jako string `'false'`/`'true'` — sprawdź, jak wartość jest dalej porównywana (Twig `table_visible` + JS `countainer_name.val()`); jeżeli porównanie jest luźne, string `'false'` jest truthy — w razie potrzeby znormalizuj: `filter_var($_GET[...] ?? true, FILTER_VALIDATE_BOOLEAN)`. Przetestuj toggle po zmianie w obu zakładkach (pozycje + dostawa/link).

---

## Zmiany wspólne

1. **Wersja**: `setup.php:33` → `2.14.0` (są migracje: date_creation, config, ledger; bump inwaliduje cache search options).
2. **CHANGELOG.md** — `[unreleased]`:
   ```
   ### Added
   - Orders list: default sort by creation date, newest first
   - Orders list: "Invoiced" column available in the column view settings
   - OT generation popup: editable order number and optional warehouse/commissioning dates
   - New "Invoicing" massive action: create and link a bill without generating the OT file
   - Configurable e-mail reminders (multiple day thresholds) for orders not yet invoiced
   ### Changed
   - Order item rows are now expanded by default
   - Polish translation for OT/invoicing actions
   ```
3. **Commity**: osobny commit na funkcję (F, A, B, C, D, E), na końcu wersja+changelog+locale rebuild. Push → **draft PR** z sekcją „known limitations" (B: staleness, E: wymagania środowiskowe).

## QA (ręczne, instancja dev)

1. **Upgrade** 2.13.1→2.14.0: kolumna `date_creation` istnieje i zbackfillowana; tabela ledger istnieje; nowe pola configu istnieją; cron `notInvoicedReminder` widoczny w Ustawienia→Automatyczne akcje.
2. **A**: świeża lista → najnowsze na górze; ręczne sortowanie działa i przeżywa nawigację; reset wraca do date_creation DESC; nowe zamówienie ma date_creation.
3. **B**: kolumna w ustawieniach widoku; Nie/Tak wg scenariuszy: bez faktury / OT z fakturą (wszystkie pozycje) / faktura częściowa; filtr Tak/Nie.
4. **C**: popup ma 5 pól; prefill Order przy 1 zamówieniu, pusty przy wielu; daty trafiają do OBU tabel dokumentu OT; puste daty = puste komórki; nazwa pliku używa wpisanego numeru.
5. **D**: akcja „Fakturowanie" widoczna; wymaga numeru; tworzy Bill + linkuje pozycje + ustawia PAID; NIE tworzy dokumentu OT; „Generuj OT" po polsku.
6. **E**: ustaw progi `1,2`, utwórz zamówienie z order_date sprzed 3 dni → ręczne odpalenie crona (`php bin/console glpi:cron` lub front cron) wysyła DWA maile (po jednym na próg), z danymi + działającym linkiem; drugi bieg crona nie wysyła nic (ledger); zamówienie zafakturowane/anulowane — brak maili; adresy dodatkowe z configu dostają kopię.
7. **F**: obie zakładki domyślnie rozwinięte; toggle działa; stan z GET respektowany.
8. `php -l` na zmienionych plikach; phpstan/cs-fixer jeśli w CI (`.php-cs-fixer.php`, `phpstan.neon` są w repo).
9. Wydajność listy (prod ma 22k+ pozycji): kolumna B bez JOIN-ów, sort A po indeksie.

## Pułapki (nie pomiń)

- `DefaultSearchRequestInterface` sprawdzany przez `instanceof` — sama metoda bez `implements` NIE zadziała.
- ID search options to kontrakt (zapisane wyszukiwania/preferencje) — nie ruszaj istniejących; nowe: 121, 88.
- Wartości `plugin_order_billstates_id` to stałe klasowe, nie ID dropdownu — datatype `bool`, nie `dropdown`.
- Subform akcji masowej dostaje selekcję przez `$ma->POST['items']['PluginOrderOrder']` — wzorzec `inc/link.class.php:559+`.
- Seeding notyfikacji musi być idempotentny (wzorzec `countElementsInTable`) — instalacja może być uruchamiana wielokrotnie.
- Nie loguj/nie commituj `.mo` bez odpowiadającej zmiany `.po`.
- Po wdrożeniu na prod: `php bin/console cache:clear` — na tej instalacji przeterminowany cache Twig już raz dał 500 (incydent „Generuj pozycję").
