# Changelog

All notable changes to AMQP Agent will be documented in this file.

## [Unreleased]

## [1.0.0] - 2020-06-15
- Initial release.

## [1.0.1] - 2020-06-23
- Fix issue with Logger class
  - Fix addtional line breaks when writing to log file.

## [1.1.0] - 2020-08-10
- Add the possiblity to open multiple connection by a worker
- Update AbstractWorker class
  - Add connections array and channels array.
  - Add setConnection(), getNewConnection() and setChannel() methods.
  - Modify old methods to make use of the newly created methods internally.
  - Add new tests to the newly created methods.
  - Update methods signature on the corresponding interface (AbstractWorkerInterface)
  - Update DocBlocks of other classes to reference the newly created methods.
  - Rebuild documentation.

## [1.1.1] - 2020-09-14
- Update composer.json
    - Pump minimum php-amqplib version.
    - Downgrade minimum php version.
    - Update dev requirements versions to match php version.
    - Update branch-alias.
    - Update scripts field.
    - Add conflict field.
- Fix php < 7.4 type hinting incompatibility
    - remove return type "self" from methods signature in all interfaces.
- Fix php-amqplib v2.12.0 deprecations
    - Fix references to deprecated properties in php-amqplib v2.12.0.
- Change AbstractWorker arguments method to a static method
- Refactor some internal functionalities in different classes
- Update tests so that they run faster
- Update Travis config
- Rebuild documentation