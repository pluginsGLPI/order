# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased]

### Added

- Orders list: default sort by creation date, newest first
- Orders list: "Invoiced" column available in the column view settings
- OT generation popup: order number and optional commissioning / warehouse deposit dates
- New "Invoicing" massive action: create and link a bill without generating the OT file
- Configurable e-mail reminders (multiple day thresholds) for orders not yet invoiced
- Reopening an invoiced order keeps its bill, and the validation tab asks whether that bill is still correct
- A correcting bill can cover only part of an order, and a regenerated OT or a fully superseded bill is archived while staying attached to the order
- Dedicated profile rights for "Generate OT" and "Invoicing"; profiles holding UPDATE on orders receive both on upgrade
- Plugin setup can hold an uploaded header image, prepended to every e-mail the plugin sends (native notifications included)
- Administrator-mapped extra asset fields (e.g. an IMEI) as additional columns on the item generation form, per item type, custom assets included
- The OT popup can use a mapped extra field (e.g. IMEI) as the document's serial-number source, falling back per item

### Fixed

- OT/invoicing massive actions require their dedicated right server-side, instead of being runnable by read-only users
- The reminder cron no longer consumes thresholds while notifications are disabled or the reminder notification is inactive
- Unticking every position in the invoicing picker aborts with a message instead of billing the whole order
- Confirming the bill of a reopened order re-validates the order state, so a stale tab cannot re-close it
- Bill-to-item linking and the invoiced aggregate are no longer silently blocked by mandatory analytic-nature / account-section rules on legacy rows
- OT file names deduplicate in a loop, so same-second bulk runs cannot overwrite an earlier document
- Purging an order removes its reminder-ledger rows
- Uploading the mail header now confirms with a message and shows the preview through a root-relative URL (independent of url_base); a stale schema is reported instead of silently ignoring the upload
- Fix fatal error when opening the "Generate item" massive action form for
  assignable assets (including GLPI 11 custom assets): an array value reaching a
  single-select dropdown triggered `strlen(): ... array given`
- Fix generate associated item massive action
- Update locales

### Changed

- Order item rows are now expanded by default
- Polish translation for the OT and invoicing actions
- The OT popup field feeding the document's Order column is labeled "ORDER number"
- Plugin buttons use semantic Tabler classes, so custom themes cannot leave them unreadable


## [2.12.6] - 2026-02-23

### Fixed

- Fix SQL query error when displaying deliveries
- Fix error during generate associated material action
- Fix SQL warning during installation

## [2.12.5] - 2026-01-08

### Fixed

- Fix `Validation` tab from `Order`
- Fix invalid relations declared
- Fix uninstall method
- Fix action to link an existing item

## [2.12.4] - 2025-12-02

### Fixed

- Fix `order` search option from `Bill`
- Fix SQL error : Truncated incorrect DOUBLE value: `glpi_plugin_order_orders_items.plugin_order_orders_id`
- Fix missing PDF generation menu
- Fix warning: Invalid relations declared between `glpi_plugin_order_accountsections` and `glpi_plugin_order_accountsections` table.

### Added

- Add missing `value` / `num_order` from `Bill` search option

## [2.12.3] - 2025-11-25

### Fixed

- Fix SQL syntax errors in cronComputeLateOrders cron task

## [2.12.2] - 2025-11-13

### Fixed

- Restore inline editing functionality in items tab
- Fix the 'Missing table name' error message on the order.
- Add missing mass input field for immobilization number.

## [2.12.1] - 2025-10-14

### Fixed

- SQL expression in supplier survey total calculation
- Add `users_id_tech` field field for tech assignment compatibility

## [2.12.0] - 2025-10-01

### Added

- GLPI 11 compatibility

## [2.11.3] - 2025-10-14

### Fixed

- Add missing mass input field for immobilization number
- SQL expression in supplier survey total calculation

## [2.11.2] - 2025-08-07

- Update exemple.odt file to include ecotax tags
- Fix icon for `transfer` action
- Fix reference form error when ecotax_price field is not set
- Fix expanding all rows in table shows incorrect reference

## [2.11.1] - 2025-07-11

### Fixed

- Fix ODT export generation

## [2.11.0] - 2025-07-10

### Fixed

- Improved access control checks when updating user preferences
- Access checks improved for ODT export generation.
- Added missing access control.


### Added

- Add eco responsibility fees
- Addition of a massive action “Cancel receipt” (in the “attachments” tab)
- Batch data entry for item generation
- Add massive selection for delivered items
- Duplicate the delivery button at the top of the reception list

### Changed

- Implement `Twig` template for order items list and associated items

## [2.10.7] - 2025-03-19

- Fixed the cumulative total order price (TTC) calculation in budgets.
- Paid invoice changes status to Paid

## [2.10.6] - 2024-03-08

### Changed

- Fix error message during plugin update
- Fix few errors messages in debug mode


## [2.10.5] - 2024-02-23

### Changed

- Restores the ability to clone an order


## [1.9.6] - 2017-03-03

**Compatible with GLPI 0.85 and above**

### Added

- add parameter in config (Ask) to allow change of these 3 fields during each reception

### Changed

- fix bill creation on non default GLPI display method
- fix error in supplier notation
- fix various minor issues on ODT generation
- fix wrong call to CONFIG_ASK constant
- missing getTable function preventing data injection
