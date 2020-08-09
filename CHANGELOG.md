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
