# Changelog

All notable changes to **AMQP Agent** will be documented in this file.


## [Unreleased]


<br />

## [[1.0.0] - 2020-06-15](https://github.com/MarwanAlsoltany/amqp-agent/commits/v1.0.0)
- Initial release.


<br />

## [[1.0.1] - 2020-06-23](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.0.0...v1.0.1)
- Fix issue with Logger class:
    - Fix additional line breaks when writing to log file.


<br />

## [[1.1.0] - 2020-08-10](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.0.1...v1.1.0)
- Add the possibility to open multiple connection by a worker.
- Update `AbstractWorker` class:
    - Add connections array and channels array.
    - Add `setConnection()`, `getNewConnection()`, and `setChannel()` methods.
    - Modify old methods to make use of the newly created methods internally.
    - Add new tests to the newly created methods.
    - Update methods signature on the corresponding interface (`AbstractWorkerInterface`)
    - Update DocBlocks of other classes to reference the newly created methods.
    - Rebuild documentation.


<br />

## [[1.1.1] - 2020-09-14](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.0.1...v1.1.1)
- Update `composer.json`:
    - Pump minimum **php-amqplib** version.
    - Downgrade minimum php version.
    - Update dev requirements versions to match php version.
    - Update branch-alias.
    - Update scripts field.
    - Add conflict field.
- Fix **php < 7.4** type hinting incompatibility:
    - Remove return type "self" from methods signature in all interfaces.
- Fix **php-amqplib** v2.12.0 deprecations:
    - Fix references to deprecated properties in **php-amqplib** v2.12.0.
- Change `AbstractWorker` arguments method to a static method.
- Refactor some internal functionalities in different classes.
- Update tests so that they run faster.
- Update Travis config.
- Rebuild documentation.


<br />

## [[1.2.0] - 2020-09-26](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.1.1...v1.2.0)
- Update `composer.json`:
    - Add a link for the documentation.
    - Add some suggestions.
    - Update `dev-autoload` namespace.
- Fix typos and update DocBlocks:
    - Fix some typos in DocBlocks and other parts of the codebase.
    - Add examples to major classes DocBlocks.
- Add `Utility` class to contain some miscellaneous reusable functions.
- Refactor `Logger` class:
    - Internal changes to make use of the `Utility` class.
    - Better writing directory guessing when no path is specified.
- Update `AmqpAgentException` class:
    - Fix issue of wrong fully-qualified name when casting the class to a string.
    - Change default message of `rethrowException` method to a more useful one.
    - Add a new parameter to change the wrapping thrown exception class.
    - Rename the method `rethrowException()` to `rethrow()` and add the old name as an alias.
- Add `MagicMethodsExceptionsTrait` to unify error messages of calls to magic methods.
- Add `AbstractParameters` class to simplify working with parameters:
    - Add `AmqpAgentParameters` as global class for all parameters.
    - Add worker specific parameters class (`AbstractWorkerParameters`, `PublisherParameters`, `ConsumerParameters`).
- Update configuration file (`maks-amqp-agent-config.php`) to make use of the newly created `AmqpAgentParameters` class.
- Refactor workers classes (`AbstractWorker`, `Publisher`, `Consumer`):
    - Make use of the newly created `*Parameters` class.
    - Make use of the newly created `MagicMethodsExceptionsTrait`.
    - Remove `@codeCoverageIgnore` annotations from workers classes.
    - Remove constants from the corresponding `*Interface` as they are available now via `*Parameters`.
    - Update the classes in different places to make use of the new additions.
- Update `WorkerCommandTrait` to make use of the newly created `AmqpAgentParameters` class.
- Remove protected method `mutateClassConst()` from `WorkerMutationTrait` as it is not used anymore (usage replaced with `*Parameters::patchWith()`).
- Update old tests to cover the new changes.
- Update tests
  - Add new tests for the newly created classes and functions.
  - Update `phpunit.xml.dist` to run the new tests.
  - Update namespace across all test classes.
  - Remove `*Mock` classes from `*Test` classes and move them to their own namespace.
- Rebuild documentation.
- Update formatting of `CHANGELOG.md`.


<br />

## [[1.2.1] - 2020-09-30](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.2.0...v1.2.1)
- Update `composer.json`:
    - Update `branch-alias` version.
- Update `Utility` class:
    - Add `collapse()` method.
- Update `Client` class:
    - Add `gettable()` method.
    - Refactor `get()` method.
- Refactor `Logger` class:
    - Add `getFallbackFilename()` method.
    - Add `getFallbackDirectory()` method.
    - Add `getNormalizedPath()` method.
    - Refactor `log()` method to make use of the newly created methods.
- Update `MagicMethodsExceptionsTrait`:
  - Update exceptions messages to prevent notices when passing an array as an argument to magic methods.
- Fix coding style issues in different places.
- Rebuild documentation.


<br />

## [[1.2.2] - 2020-11-29](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.2.1...v1.2.2)
- Update `Config` class:
    - Remove deprecated method `get()`.
    - Remove `$configFlat` property and all of its references.
    - Update `$configPath` property to be a realpath.
    - Add `has()` method to quickly check if a config value exists.
    - Add `get()` method to quickly get a config value (new functionality, should be backwards compatible).
    - Add `set()` method to quickly set a config value.
- Update `Serializer` class:
    - Add serializations types as class constants.
    - Add methods to assert for PHP und JSON (un)serializations errors.
    - Refactor `serialize()` and `unserialize()` methods to use assertions.
    - Refactor `setType()` method to check if the type is supported.
    - Add a new `$strict` property to determine serialization strictness and its corresponding `getStrict()` and `setStrict()` methods.
    - Add `$strict` parameter to `serialize()` and `unserialize()` methods.
    - Add `deserialize()` method as an alias for `unserialize()`.
    - Refactor different methods to make use of available setters.
    - Update DocBlocks und Exceptions Messages of different methods.
- Update `Utility` class:
    - Add `objectToArray()` method.
    - Add `arrayToObject()` method.
    - Add `getArrayValueByKey()` method.
    - Add `setArrayValueByKey()` method.
- Update `Logger` class:
    - Fix an issue with `log()` method when checking for file size.
- Update tests
  - Add new tests to the newly created methods.
  - Update old tests to cover the new changes.
- Fix coding style issues in different places.
- Rebuild documentation.


<br />

## [[2.0.0] - 2020-12-03](https://github.com/MarwanAlsoltany/amqp-agent/compare/v1.2.2...v2.0.0)
- Update `composer.json`:
    - Update `branch-alias` version.
- Add RPC endpoints interfaces:
    - Add `AbstractEndpointInterface`.
    - Add `ClientEndpointInterface`.
    - Add `ServerEndpointInterface`.
- Add RPC endpoints classes:
    - Add `AbstractEndpoint` class.
    - Add `ClientEndpoint` class.
    - Add `ServerEndpoint` class.
    - Add `RPCEndpointParameters` class.
    - Add `RPCEndpointException` class.
- Add `IDGenerator` class for generating unique IDs and Tokens.
- Add `EventTrait` and its corresponding `Event` class to expose a simplified API for handling events.
- Add `ClassProxyTrait` and its corresponding `ClassProxy` class to expose a simplified API for manipulating objects.
- Add `ArrayProxyTrait` and its corresponding `ArrayProxy` class to expose a simplified API for manipulating arrays.
- Update `Utility` class:
    - Add `execute()` method.
    - Remove `collapse()` method (extracted to `ArrayProxy`).
    - Remove `objectToArray()` method (extracted to `ArrayProxy`).
    - Remove `arrayToObject()` method (extracted to `ArrayProxy`).
    - Remove `getArrayValueByKey()` method (extracted to `ArrayProxy`).
    - Remove `setArrayValueByKey()` method (extracted to `ArrayProxy`).
- Update `Client` class:
    - Add `$clientEndpoint` property.
    - Add `$serverEndpoint` property.
    - Add `getClientEndpoint()` method.
    - Add `getServerEndpoint()` method.
- Update `AmqpAgentParameters` class:
    - Add parameters for RPC endpoints.
- Update `Config` class:
    - Add references to RPC endpoints properties (`$rpcConnectionOptions` and `$rpcQueueName`).
- Update configuration file (`maks-amqp-agent-config.php`):
    - Add references to RPC endpoints options.
- Update tests
  - Add new tests to the newly created methods and classes.
  - Add new mocks to help with classes testing.
  - Add `bin/endpoint` executable to help with endpoints testing.
  - Update old tests to cover the new changes.
  - Update `phpunit.xml.dist` to run the new tests.
- Rebuild documentation.
