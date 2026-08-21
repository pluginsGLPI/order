# Ankieta QA — wtyczka Order 2.14.0 (dla testera)

> **Środowisko:** GLPI 11.0.x z zainstalowaną i aktywną wtyczką Order 2.14.0.
> **Konto:** Super-Admin (chyba że krok mówi inaczej). Po instalacji/aktualizacji wtyczki wyczyść cache: `php bin/console cache:clear`.
> **Jak wypełniać:** wykonaj kroki po kolei, porównaj z „Oczekiwany rezultat", zaznacz `[x]` przy PASS albo FAIL i dopisz uwagi.

**Tester:** ______________________  **Data:** ____________  **Wersja GLPI:** ____________

---

## 1. Lista zamówień — sortowanie i kolumna „Zafakturowane"

**Kroki:**
1. Otwórz *Zarządzanie → Zamówienia* (świeża sesja albo po kliknięciu gumki czyszczącej kryteria).
2. Spójrz na kolejność wierszy i na etykietę sortowania nad tabelą.
3. Otwórz ustawienia widoku kolumn (ikona kolumn) i odszukaj pozycje **„Zafakturowane"** i **„Data utworzenia"**.
4. Dodaj kolumnę „Zafakturowane" do widoku.
5. Ustaw kryterium wyszukiwania: *Zafakturowane = Tak*.

**Oczekiwany rezultat:**
- Lista domyślnie posortowana **od najnowszych** (po dacie utworzenia, malejąco); ręczna zmiana sortowania działa i jest pamiętana w sesji.
- Obie kolumny dostępne w ustawieniach widoku; „Zafakturowane" pokazuje **Tak/Nie**.
- Filtr *Tak* zwraca wyłącznie zamówienia zakończone fakturą.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 2. Popup „Generuj OT"

**Kroki:**
1. Na liście zamówień zaznacz jedno **dostarczone** zamówienie → *Akcje → Generuj OT*.
2. Obejrzyj pola popupu.
3. Wypełnij: *Numer faktury*, *Centrum kosztów (MPK)*, **zostaw pole „Numer ORDER" puste**, wpisz tylko *Data zdep. w magazynie*. Zatwierdź.
4. Powtórz na innym zamówieniu, tym razem **wpisując własny** „Numer ORDER" (np. `TEST-ORDER-1`) i *Datę włącz. do użytku*.
5. Otwórz wygenerowane dokumenty z zakładki *Dokumenty* zamówienia.

**Oczekiwany rezultat:**
- Popup ma pola: Numer faktury, Centrum kosztów (MPK), **Numer ORDER** (puste, bez podpowiedzi, z dopiskiem o pustym polu), dwie opcjonalne daty.
- Puste pole ORDER → dokument i nazwa pliku używają numeru zamówienia; wpisany `TEST-ORDER-1` → widnieje w kolumnie **Order** każdego wiersza dokumentu i w nazwie pliku.
- Wpisana data trafia do właściwej kolumny dokumentu; druga kolumna zostaje pusta.
- Po akcji zamówienie ma status *Zapłacone*, faktura widoczna w zakładce *Faktura*.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 3. Akcja „Fakturowanie" (bez pliku OT) + korekta częściowa

**Kroki:**
1. Zaznacz jedno niezafakturowane zamówienie z **min. 2 pozycjami** → *Akcje → Fakturowanie*.
2. Sprawdź listę pozycji w popupie (wszystkie zaznaczone), **odznacz wszystkie** i spróbuj zatwierdzić z wpisanym numerem.
3. Ponów akcję: zostaw zaznaczoną **tylko jedną** pozycję, wpisz numer `FV-KOR-TEST`, zatwierdź.
4. Sprawdź zakładkę *Faktura* zamówienia i wartość nowej faktury.
5. Ponów akcję na **wszystkie** pozycje z numerem `FV-PELNA-TEST`.

**Oczekiwany rezultat:**
- Krok 2: odmowa z komunikatem „Nie wybrano żadnej pozycji dla tej faktury", **żadna faktura nie powstaje**.
- Krok 3: faktura obejmuje tylko wybraną pozycję, jej **wartość = cena tej pozycji**; poprzednia faktura pozostaje aktywna dla reszty.
- Krok 5: poprzednie faktury oznaczone jako **archiwalne** (pozostają powiązane), pozycje wskazują nową fakturę; **żaden plik OT nie powstał** w całym scenariuszu.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 4. Korekta zamówienia zamkniętego (reopen)

**Kroki:**
1. Otwórz zafakturowane zamówienie → zakładka *Zatwierdzenie* → **Edytuj zamówienie** (potwierdź).
2. Przeczytaj komunikaty po cofnięciu; sprawdź zakładkę *Faktura* i *Dokumenty*.
3. Dodaj nową pozycję do zamówienia.
4. Wróć na *Zatwierdzenie* — panel „Faktura zamówienia otwartego ponownie" → kliknij **„Faktura jest nadal poprawna"**.
5. Sprawdź stan pozycji i status zamówienia.

**Oczekiwany rezultat:**
- Po cofnięciu: faktury i dokumenty OT **nietknięte**; komunikat, że faktura pozostaje powiązana.
- Panel pyta o poprawność faktury i pokazuje jej numer oraz datę.
- Po potwierdzeniu: dopisana pozycja **podpięta do tej samej faktury**, zamówienie wraca do *Zapłacone*, panel znika.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 5. Uprawnienia „Generuj OT" i „Fakturowanie"

**Kroki:**
1. *Administracja → Profile → [profil testowy z prawem aktualizacji zamówień] → zakładka Zamówienia*.
2. Sprawdź kolumny matrycy: **Generate OT** i **Invoicing**; odznacz „Generate OT", zapisz.
3. Zaloguj się użytkownikiem z tym profilem, zaznacz zamówienie → *Akcje*.
4. Wróć na konto administratora i sprawdź to samo menu.

**Oczekiwany rezultat:**
- Matryca profilu zawiera oba nowe prawa i zapamiętuje zmiany.
- Użytkownik bez prawa OT **nie widzi** akcji *Generuj OT*, widzi *Fakturowanie* (i odwrotnie przy odwrotnej konfiguracji).
- Administrator widzi obie akcje.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 6. Przypomnienia o niezafakturowanych zamówieniach

**Przygotowanie:** *Ustawienia → Powiadomienia* — powiadomienia i tryb e-mail włączone. W konfiguracji wtyczki ustaw *Progi przypomnień w dniach* = `1,3` oraz wpisz 1-2 dodatkowe adresy. Utwórz zamówienie z datą zamówienia sprzed ~5 dni, niezafakturowane.

**Kroki:**
1. W konfiguracji wtyczki wpisz w progi wartość ` 3, 1, abc, -2, 3 ` i zapisz — sprawdź, co zostało zapisane.
2. Uruchom zadanie automatyczne **notInvoicedReminder** (*Ustawienia → Automatyczne akcje* → wykonaj).
3. Sprawdź *Administracja → Kolejka powiadomień* (lub skrzynki odbiorców).
4. Uruchom zadanie **ponownie**.
5. Zafakturuj zamówienie i uruchom zadanie po raz trzeci.

**Oczekiwany rezultat:**
- Krok 1: progi znormalizowane do `1,3`; błędne wpisy odrzucone (nieprawidłowe adresy e-mail odrzucane z komunikatem).
- Krok 2: **po jednym mailu na próg** (2) do autora zamówienia **i** do adresów dodatkowych; treść zawiera numer, nazwę, datę, liczbę dni i działający link do zamówienia.
- Krok 4: **zero** nowych maili (progi już obsłużone).
- Krok 5: zero maili (zamówienie zafakturowane).

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 7. Nagłówek graficzny wiadomości

**Kroki:**
1. *Konfiguracja wtyczki (Zarządzanie zamówieniami)* → sekcja **Wygląd wiadomości e-mail** → pole **„Wgraj nagłówek wiadomości"**: wgraj obraz PNG (np. logo 600×150), zapisz.
2. Sprawdź podgląd nagłówka w konfiguracji.
3. Wywołaj dowolny mail wtyczki (np. przypomnienie jak w sekcji 6 **albo** natywne powiadomienie — cofnięcie zatwierdzonego zamówienia przy aktywnym powiadomieniu „Cancel Order Validation").
4. Obejrzyj treść maila (kolejka powiadomień → podgląd HTML, lub skrzynka).
5. Spróbuj wgrać plik niebędący obrazem (np. .txt) i plik > 2 MB.
6. Zaznacz „Usuń obecny nagłówek", zapisz, wyślij kolejny mail.

**Oczekiwany rezultat:**
- Podgląd wgranego obrazu widoczny w konfiguracji; obraz dostępny bez logowania pod adresem `…/plugins/order/front/mailheader.php`.
- **Każdy** mail wtyczki (przypomnienia i powiadomienia natywne) ma obraz nagłówka **na samej górze treści**.
- Błędne pliki odrzucone z komunikatem; po usunięciu nagłówka maile przychodzą bez obrazu.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 8. Edytowalny szablon przypomnienia

**Kroki:**
1. *Ustawienia → Powiadomienia → Szablony powiadomień → „Order not invoiced"* → tłumaczenie.
2. Zmień treść (np. dodaj zdanie) i zapisz.
3. Wywołaj przypomnienie (jak w sekcji 6, nowe zamówienie/próg).

**Oczekiwany rezultat:** mail zawiera zmienioną treść; aktualizacja wtyczki **nie nadpisuje** edycji.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 9. Pozycje zamówienia — rozwinięcie i przyciski

**Kroki:**
1. Otwórz zamówienie → zakładka *Pozycje zamówienia*, potem *Dostarczone pozycje*.
2. Kliknij „Zwiń wszystko" / „Rozwiń wszystko".
3. Wejdź na zakładkę *Zatwierdzenie* i obejrzyj przyciski.

**Oczekiwany rezultat:**
- Obie zakładki otwierają się **rozwinięte**; przełącznik działa.
- Przyciski są czytelne w Twoim motywie: **Anuluj zamówienie = czerwony**, akcje „do przodu" (Zatwierdź / Edytuj / Poproś o zatwierdzenie / potwierdzenie faktury) = **zielone**, wycofanie prośby = **żółty**; biały tekst na czerwonym/zielonym.

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## 10. Kasowanie zamówienia

**Kroki:**
1. Utwórz zamówienie testowe, przenieś do kosza, a następnie **usuń trwale**.

**Oczekiwany rezultat:** operacja kończy się bez błędu (biała strona/500 = FAIL).

- [ ] PASS  - [ ] FAIL — uwagi: ______________________________________________

---

## Podsumowanie

| Sekcja | Wynik |
|---|---|
| 1. Lista zamówień | |
| 2. Popup Generuj OT | |
| 3. Fakturowanie + korekta częściowa | |
| 4. Reopen | |
| 5. Uprawnienia | |
| 6. Przypomnienia | |
| 7. Nagłówek maili | |
| 8. Szablon przypomnienia | |
| 9. Rozwinięcie i przyciski | |
| 10. Kasowanie | |

**Werdykt końcowy:**  - [ ] ZALICZONE  - [ ] NIEZALICZONE

**Uwagi ogólne:** _________________________________________________________
