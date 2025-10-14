# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [UNRELEASED]

## [2.12.1] - 2025-10-14

### Fixed

- SQL expression in supplier survey total calculation
- Add `users_id_tech` field field for tech assignment compatibility

## [2.12.0] - 2025-10-01

### Added

- GLPI 11 compatibility

## [2.11.3] - 2025-10-14

### Fixed

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
