# Order Plugin v2.13.1 - QA Test Plan

## Prerequisites

- GLPI 11.0.x instance with the Order plugin v2.13.1 installed
- At least one user-defined custom asset type created in **Setup > Asset Definitions** (e.g. "Laptop", "Mobile Phone")
- At least one Supplier configured
- At least one Budget configured
- Super-Admin access for testing

---

## 1. Plugin Installation & Activation

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 1.1 | Fresh install | Upload plugin to `plugins/order/`, go to Setup > Plugins, click Install then Enable | Plugin installs without errors, status shows "Enabled", version shows 2.13.1 | [ ] |
| 1.2 | Upgrade from 2.12.4 | Replace plugin files, go to Setup > Plugins, click Update then Enable | Plugin updates without errors, no data loss, version shows 2.13.1 | [ ] |
| 1.3 | Plugin page loads | Navigate to Management > Orders | Orders list loads without errors | [ ] |

---

## 2. Standard Asset Product References (Regression)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 2.1 | Create reference for Computer | Management > Orders > References tab > Add, select "Computer" as item type | Type, Model, Template dropdowns appear and populate correctly | [ ] |
| 2.2 | Create reference for Monitor | Same as above with "Monitor" | Dropdowns work correctly | [ ] |
| 2.3 | Create reference for Peripheral | Same as above with "Peripheral" | Dropdowns work correctly | [ ] |
| 2.4 | Create reference for Printer | Same as above with "Printer" | Dropdowns work correctly | [ ] |
| 2.5 | Create reference for Network Equipment | Same as above with "NetworkEquipment" | Dropdowns work correctly | [ ] |
| 2.6 | Create reference for Phone | Same as above with "Phone" | Dropdowns work correctly | [ ] |
| 2.7 | Create reference for Other | Same as above with "Other" | Type dropdown shows custom "Other types", no errors | [ ] |
| 2.8 | Create reference for Software License | Same as above with "SoftwareLicense" | Dropdowns work correctly | [ ] |
| 2.9 | Create reference for Contract | Same as above with "Contract" | Dropdowns work correctly | [ ] |

---

## 3. Custom Asset Product References (New Feature)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 3.1 | Custom assets visible in dropdown | Add new product reference, open item type dropdown | User-defined custom asset types appear with their labels (e.g. "Laptop", "Mobile Phone") | [ ] |
| 3.2 | Create reference for custom asset | Select a custom asset type, fill in name and details | Reference is created, item type shows correctly | [ ] |
| 3.3 | Type dropdown for custom asset | After selecting custom asset type, check Type dropdown | Type dropdown loads (may be empty if no types defined for that asset) | [ ] |
| 3.4 | Model dropdown for custom asset | After selecting custom asset type, check Model dropdown | Model dropdown loads (may be empty if no models defined) | [ ] |
| 3.5 | Template dropdown for custom asset | After selecting custom asset type, check Template dropdown | Template dropdown loads if templates exist for that asset type | [ ] |
| 3.6 | View existing custom asset reference | Open a previously created custom asset reference | Item type label displays correctly (not raw class name) | [ ] |
| 3.7 | Multiple custom asset types | Create references for different custom asset types | Each type appears correctly and independently in the dropdown | [ ] |
| 3.8 | Search by custom asset type | Use the search/filter on references list, filter by item type | Custom asset types appear in the filter dropdown and filtering works | [ ] |

---

## 4. Order Creation & Management (Regression)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 4.1 | Create new order | Management > Orders > Add, fill required fields (name, supplier, etc.) | Order is created successfully | [ ] |
| 4.2 | Add standard reference to order | Open order > Items tab > Add a Computer reference | Reference added with correct quantity, price, type, model displayed | [ ] |
| 4.3 | Add custom asset reference to order | Open order > Items tab > Add a custom asset reference | Reference added, type and model columns display correctly | [ ] |
| 4.4 | Order validation workflow | Submit order for validation, approve it | Workflow proceeds normally | [ ] |
| 4.5 | Order item display | View order items list | Type and Model columns show correct values for all item types | [ ] |

---

## 5. Delivery / Reception (Regression)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 5.1 | Receive standard item | On an approved order, go to Reception tab, select items, click "Take delivery" | Items marked as delivered, delivery date set | [ ] |
| 5.2 | Generate standard asset | After delivery, select items > Actions > "Generate item" | New GLPI asset created with correct name, serial, type, model | [ ] |
| 5.3 | Link to existing item | After delivery, select items > Actions > "Link to an existing item" | Asset linked correctly to order item | [ ] |
| 5.4 | Delete item link | Select linked item > Actions > "Delete item link" | Link removed, asset unlinked | [ ] |
| 5.5 | Cancel reception | Select unlinked delivered item > Actions > "Cancel reception" | Item reverts to "Not delivered" status | [ ] |
| 5.6 | Receive custom asset item | Same as 5.1 but for a custom asset reference | Delivery works without errors | [ ] |
| 5.7 | Generate custom asset | Same as 5.2 but for a custom asset reference | Custom asset created in glpi_assets_assets with correct definition ID | [ ] |

---

## 6. OT Protocol Generation (New Feature)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 6.1 | OT action visible | Open an order with delivered items, click Actions dropdown | "Generate OT" option appears in the list | [ ] |
| 6.2 | Cost Center prompt | Select order(s), click "Generate OT" | Popup shows with "Cost Center (MPK)" input field and submit button | [ ] |
| 6.3 | Generate OT document | Enter a Cost Center value, click submit | Document generated, download starts (PDF or HTML depending on server) | [ ] |
| 6.4 | OT content - header | Open the generated document | Title "OT" centered at top, Polish/German subtitle present | [ ] |
| 6.5 | OT content - supplier | Check "Dostawca / Producent" field | Shows the order's supplier name | [ ] |
| 6.6 | OT content - items | Check data rows | Each delivered item has its own row with: Poz (sequential), Ilość=1, Nazwa=product reference name, Serial from GLPI asset | [ ] |
| 6.7 | OT content - price | Check "Wartość" column | Shows unit price tax free for each item | [ ] |
| 6.8 | OT content - cost center | Check "Cost Center" column | Shows the value entered in the popup for all rows | [ ] |
| 6.9 | OT content - order number | Check "Order" column | Shows the order number | [ ] |
| 6.10 | OT content - delivery date | Check "Data włącz. do użytku" column | Shows delivery date for each item | [ ] |
| 6.11 | OT content - sum | Check sum row at bottom | Sum of all item values is correct | [ ] |
| 6.12 | OT content - orientation | Check page layout | Document is in **portrait** A4 format | [ ] |
| 6.13 | OT content - empty fields | Check Nr środka trwałego, Lokaliz., Data zdep. | These fields are empty (for manual filling) | [ ] |
| 6.14 | OT content - relocation section | Check bottom section | Empty relocation table with 10 rows present | [ ] |
| 6.15 | OT saved as document | Go to order's Documents tab | Generated OT document is linked to the order | [ ] |
| 6.16 | OT with no delivered items | Try generating OT for order with no deliveries | Graceful failure, error message shown | [ ] |
| 6.17 | OT multiple orders | Select multiple orders, generate OT | Each order gets its own document, last one downloads | [ ] |
| 6.18 | Invoice Number prompt | Select order(s), click "Generate OT" | Popup shows "Invoice Number" input field alongside "Cost Center (MPK)" | [ ] |
| 6.19 | OT content - invoice number | Enter invoice number "FV/2026/001", generate OT | "Numer faktury:" field shows "FV/2026/001" | [ ] |
| 6.20 | OT content - empty invoice | Generate OT with empty invoice number field | "Numer faktury:" field shows underscores (fallback) | [ ] |
| 6.21 | Bill auto-created | Enter invoice number, generate OT, go to Management > Orders > Bill | New bill exists with name and number = invoice number | [ ] |
| 6.22 | Bill supplier | Check the auto-created bill's Supplier field | Matches the order's supplier | [ ] |
| 6.23 | Bill value | Check the auto-created bill's Value field | Matches order's total price (tax-free / priceHT) | [ ] |
| 6.24 | Bill status | Check the auto-created bill's Status | Status = "Paid" (Zapłacony) | [ ] |
| 6.25 | Bill dates | Check bill's Date and Approval date fields | Both set to the date of OT generation | [ ] |
| 6.26 | Bill approver | Check the auto-created bill's Approver | Set to the user who generated the OT | [ ] |
| 6.27 | Bill order link | Check the auto-created bill's Order field | Points to the correct order | [ ] |
| 6.28 | Bill entity | Check the auto-created bill's entity | Matches the order's entity | [ ] |
| 6.29 | No bill without invoice | Generate OT without entering invoice number | No bill record is created | [ ] |
| 6.30 | Bill success message | Generate OT with invoice number | Success message includes bill creation confirmation | [ ] |
| 6.31 | Special chars in invoice | Enter invoice with special chars "FV/2026/001 & test" | Bill created correctly, OT PDF shows escaped characters | [ ] |
| 6.32 | Bill linked to order items | Generate OT with invoice number, go to order's Faktura tab | Each order item shows the bill number in "Faktura" column | [ ] |
| 6.33 | Item bill status set to Paid | Check "Status faktury" column for each item in Faktura tab | All items show "Zapłacony" (Paid) status | [ ] |
| 6.34 | Order aggregate bill state | After OT+bill generation, check order's bill payment status | Order shows "Zapłacony" (Paid) overall payment status | [ ] |
| 6.35 | Infocom updated with bill | Go to a delivered asset > Management tab > check Infocom | Bill number and warranty date are populated from the auto-created bill | [ ] |
| 6.36 | All items linked (multi-item order) | Generate OT with invoice for order with 5+ items, check Faktura tab | All items (not just some) have the bill number and Paid status | [ ] |
| 6.37 | No item linking without invoice | Generate OT without entering invoice number, check Faktura tab | Order items have no bill linked, bill columns remain empty | [ ] |

---

## 7. Billing Display (Regression)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 7.1 | Bill item display | Open a bill linked to an order | Type and Model columns show correct values | [ ] |
| 7.2 | Bill with custom asset | View bill for order containing custom asset references | Type and model display correctly (or empty if not set) | [ ] |

---

## 8. Search & Display (Regression)

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 8.1 | Order search results | Search orders in global search | Type and Model columns in results show correct values | [ ] |
| 8.2 | Reference list display | View references list | All references show correct item type, type, model | [ ] |
| 8.3 | Custom asset in search | Search for references with custom asset types | Results display correctly with proper labels | [ ] |

---

## 9. PDF Generation Backends

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 9.1 | wkhtmltopdf | Install wkhtmltopdf, generate OT | PDF file generated | [ ] |
| 9.2 | Chromium headless | Install chromium, remove wkhtmltopdf, generate OT | PDF file generated | [ ] |
| 9.3 | No PDF binary | Remove all PDF binaries, generate OT | HTML file generated as fallback, download works | [ ] |

---

## 10. Edge Cases & Error Handling

| # | Test | Steps | Expected Result | Pass |
|---|------|-------|-----------------|------|
| 10.1 | Empty Cost Center | Generate OT with empty Cost Center field | Document generated with empty Cost Center column | [ ] |
| 10.2 | Special characters | Create order/reference with special chars (& < > " '), generate OT | Characters properly escaped in output, no errors | [ ] |
| 10.3 | Large order (20+ items) | Generate OT for order with more than 20 delivered items | All items appear in document (rows extend beyond the template's 20) | [ ] |
| 10.4 | Custom asset definition deactivated | Deactivate a custom asset definition in GLPI, reload plugin | Deactivated type no longer appears in dropdown, existing references unaffected | [ ] |
| 10.5 | Multi-entity | Test with sub-entities and recursive orders | References, orders, and OT generation respect entity boundaries | [ ] |
| 10.6 | Non-admin user | Login as user with Order read-only rights | Generate OT visible but standard item generation restricted per profile | [ ] |

---

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Tester | | | |
| Reviewer | | | |
| Approver | | | |

---

*Generated for Order Plugin v2.13.1 - QA before production deployment*
