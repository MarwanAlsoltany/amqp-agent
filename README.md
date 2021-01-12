<h1 align="center"><a href="https://marwanalsoltany.github.io/amqp-agent/" title="Documentation" target="_blank">AMQP Agent</a></h1>

<div align="center">

An elegant wrapper around the famous php-amqplib for 90% use case.


[![PHP Version][php-icon]][php-href]
[![Latest Version on Packagist][version-icon]][version-href]
[![License][license-icon]][license-href]
[![Maintenance][maintenance-icon]][maintenance-href]
[![Documentation][documentation-icon]][documentation-href]
[![Total Downloads][downloads-icon]][downloads-href]
[![Scrutinizer Build Status][scrutinizer-icon]][scrutinizer-href]
[![Scrutinizer Code Coverage][scrutinizer-coverage-icon]][scrutinizer-coverage-href]
[![Scrutinizer Code Quality][scrutinizer-quality-icon]][scrutinizer-quality-href]
[![Travis Build Status][travis-icon]][travis-href]
[![StyleCI Code Style][styleci-icon]][styleci-href]

<details>
<summary>Table of Contents</summary>
<p>

[Installation](#installation)<br/>
[About AMQP Agent](#about-amqp-agent)<br/>
[API](#api)<br/>
<a href="https://marwanalsoltany.github.io/amqp-agent/" target="_blank">Documentation</a><br/>
[Configuration](#configuration)<br/>
[Examples](#examples)<br/>
[Links](#links)<br/>
[License](#license)<br/>
[Changelog](./CHANGELOG.md)

</p>
</details>

<a href="https://twitter.com/intent/tweet?url=&text=Working%20with%20%23RabbitMQ%20in%20%23PHP%20has%20never%20been%20so%20easy%20and%20fun%2C%20check%20out%20AMQP%20Agent%20and%20stop%20wasting%20your%20time!%20https%3A%2F%2Fgithub.com%2FMarwanAlsoltany%2Famqp-agent%20" title="Tweet" target="_blank"><img src="https://img.shields.io/twitter/url/http/shields.io.svg?style=social" alt="Tweet"></a>
</div>




---


## Key Features

1. Framework agnostic, integrates easily in any codebase
2. An intuitive and tested API with out-of-the-box support for **Publishers**, **Consumers**, and **RPC Endpoints**
3. Contains tons of helpers to get you up and running in no time without deep knowledge of the topic
4. Unlimited flexibility when it comes to customizing it to your exact needs
5. Actively maintained, well documented and all about syntactic sugar.

---


## Installation

Try AMQP Agent out now:

#### Composer using Packagist:

```sh
composer require marwanalsoltany/amqp-agent
```

#### Composer using GitHub Repo (unstable):

Copy this configuration in your `composer.json`:
```json
"repositories": {
    "amqp-agent-repo": {
        "type": "vcs",
        "url": "https://github.com/MarwanAlsoltany/amqp-agent.git"
    }
},
"require": {
    "marwanalsoltany/amqp-agent": "dev-dev"
},
"minimum-stability": "dev"
```

Run:

```sh
composer update
```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *AMQP Agent supports now PHP 7.1 by default starting from version v1.1.1, if you used the `php7.1-compatibility` branch in older versions, update your composer.json!*

---


## About AMQP Agent

AMQP Agent tries to simplify the implementation of a message-broker in a PHP project. It takes away the entire overhead of building and configuring objects or creating classes that you would need in order to talk with RabbitMQ server (through *php-amqplib*) and exposes a tested, fully configurable, and flexible API that fits almost any project.

The *php-amqplib* library is awesome and works very well. The one and only problem is, it's pretty bare-bone to be used in a project, without remaking your own wrapper classes, it's almost impossible to not write spaghetti code. Plus the enormous amount of functions, methods, and configurations (parameters) that come with it make it really hard to implement a reasonable API to be used. AMQP Agent solves this problem by making as much abstraction as possible without losing control over the workers and by bringing back the terminology associated with message-brokers, a Publisher and a Consumer is all that you need to deal with if you are a newcomer.

According to this motto, AMQP Agent makes working with RabbitMQ as fun and elegant as possible by exposing some fluent interfaces that are cleverly implemented, fit modern PHP development, nice to work with and very simple to use; yet very powerful and can overwrite the smallest quirks at any point of working with the worker. With AMQP Agent you can start publishing and consuming messages with just a few lines of code!

AMQP Agent does not overwrite anything of *php-amqplib* nor it does change the terminology associated with its functions. It only simplifies it; takes out the noise of functions' names and extends it in some places. It also adds some nice features like workers-commands, dynamic channel-waiting, and facilitation methods.

AMQP Agent does also offer a powerful event-based RPC Client and RPC Server for your IoT projects.

Working with AMQP Agent can be as easy as:

```php
// Publisher
$publisher = new Publisher();
$publisher->work($messages);

// Consumer
$consumer = new Consumer();
$consumer->work($callback);

// RPC Client
$rpcClient = new ClientEndpoint();
$rpcClient->connect();
$response = $rpcClient->request($request);
$rpcClient->disconnect();

// RPC Server
$rpcServer = new ServerEndpoint();
$rpcServer->connect();
$request = $rpcServer->respond($callback);
$rpcServer->disconnect();
```


---


## API

AMQP Agent exposes a number of concrete classes that can be directly used and other abstract classes that can be extended. These two class-variants also have a helper sub-division.


#### AMQP Agent Classes

| Class | Description | API |
| --- | --- | --- |
| [AbstractWorker](./src/Worker/AbstractWorker.php) <sup><code>*A</code></sup> | An abstract class implementing the basic functionality of a worker. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_AbstractWorker.html) |
| [Publisher](./src/Worker/Publisher.php) <sup><code>*C\*S</code></sup> | A class specialized in publishing. Implementing only the methods needed for a publisher. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_Publisher.html) |
| [Consumer](./src/Worker/Consumer.php) <sup><code>*C\*S</code></sup> | A class specialized in consuming. Implementing only the methods needed for a consumer. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_Consumer.html) |
| [AbstractEndpoint](./src/RPC/AbstractEndpoint.php) <sup><code>*A</code></sup> | An abstract class implementing the basic functionality of an endpoint. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_RPC_AbstractEndpoint.html) |
| [ClientEndpoint](./src/RPC/ClientEndpoint.php) <sup><code>*C</code></sup> | A class specialized in requesting. Implementing only the methods needed for a client. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_RPC_ClientEndpoint.html) |
| [ServerEndpoint](./src/RPC/ServerEndpoint.php) <sup><code>*C</code></sup> | A class specialized in responding. Implementing only the methods needed for a server. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_RPC_ServerEndpoint.html) |
| [AmqpAgentParameters](./src/Config/Utility.php) <sup><code>*C\*H</code></sup> | A class that contains all AMQP Agent parameters as constants. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Config_AmqpAgentParameters.html) |
| [Utility](./src/Helper/Utility.php) <sup><code>*C\*H</code></sup> | A class containing miscellaneous helper functions. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Utility.html) |
| [Event](./src/Helper/Event.php) <sup><code>*C\*H</code></sup> | A simple class for handling events (dispatching and listening). | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Event.html) |
| [ArrayProxy](./src/Helper/ArrayProxy.php) <sup><code>*C\*H</code></sup> | A class containing methods for for manipulating and working arrays. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_ArrayProxy.html) |
| [ClassProxy](./src/Helper/ClassProxy.php) <sup><code>*C\*H</code></sup> | A class containing methods for proxy methods calling, properties manipulation, and class utilities. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_ClassProxy.html) |
| [IDGenerator](./src/Helper/IDGenerator.php) <sup><code>*C\*H</code></sup> | A class containing functions for generating unique IDs and Tokens | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_IDGenerator.html) |
| [Serializer](./src/Helper/Serializer.php) <sup><code>*C\*H</code></sup> | A flexible serializer to be used in conjunction with the workers. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Serializer.html) |
| [Logger](./src/Helper/Logger.php) <sup><code>*C\*H</code></sup> | A class to write logs, exposing methods that work statically and on instantiation. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Logger.html) |
| [Singleton](./src/Helper/Singleton.php) <sup><code>*A\*H</code></sup> | An abstract class implementing the fundamental functionality of a singleton. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Singleton.html) |
| [Config](./src/Config.php) <sup><code>*C\*R</code></sup> | A class that turns the configuration file into an object. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Config.html) |
| [Client](./src/Client.php) <sup><code>*C\*R</code></sup> | A class returns everything AMQP Agent has to offer. A simple service container so to say. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Client.html) |
| [Example](./src/Helper/Example.php) <sup><code>*A\*H</code></sup> | An abstract class used as a default callback for the consumer. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Example.html) |

> See also: [AbstractWorkerSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_AbstractWorkerSingleton.html), [PublisherSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_PublisherSingleton.html), [ConsumerSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_ConsumerSingleton.html), [AbstractWorkerInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_AbstractWorkerInterface.html), [PublisherInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_PublisherInterface.html), [ConsumerInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_ConsumerInterface.html), [WorkerFacilitationInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_WorkerFacilitationInterface.html), [WorkerMutationTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Worker_WorkerMutationTrait.html), [WorkerCommandTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Worker_WorkerCommandTrait.html), [AbstractEndpointInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_RPC_AbstractEndpointInterface.html), [ClientEndpointInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_RPC_ClientEndpointInterface.html), [ServerEndpointInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_RPC_ServerEndpointInterface.html), [EventTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Helper_EventTrait.html), [ArrayProxyTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Helper_ArrayProxyTrait.html), [ClassProxyTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Helper_ClassProxyTrait.html), [AbstractParameters](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Config_AbstractParameters.html).

#### Bibliography
* <code>*C</code> **Concrete:** This class is a concrete class and can be instantiated directly.
* <code>*A</code> **Abstract:** This class is an abstract class and cannot be instantiated directly.
* <code>*H</code> **Helper:** This class is a helper class. Third-party alternatives can be freely used instead.
* <code>*R</code> **Recommended:** This class is recommended to be used when working with AMQP Agent (best practice).
* <code>*S</code> **Singleton:** This class has a singleton version available via suffixing the class name with `Singleton` and can be retrieved via `*Singleton::getInstance()`, i.e. `Publisher` -> `PublisherSingleton`.

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Singleton is considered an anti-pattern, try avoiding it as much as possible, though there are use-cases for it. Use singletons only if you know what you are doing.*


---


## Configuration

If you just quickly want to publish and consume messages, everything is ready and configured already, AMQP Agent is shipped with a tested configuration that follows best practices. You can simply import `Publisher` class and/or `Consumer` class in your file and overwrite the parameters you want (RabbitMQ credentials for example) later on the instance.

If you want to fine-tune and tweak AMQP Agent configuration to your exact needs, there is a bit of work to do. You have to supply a config file (see: [maks-amqp-agent-config.php](./src/Config/maks-amqp-agent-config.php) and pay attention to the comments). You don't have to supply everything, you can simply only write the parameters you want to overwrite, AMQP Agent is smart enough to append the deficiency. These parameters can also be overwritten later through public assignment notation or per method call.

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *AMQP Agent uses the same parameter names as php-amqplib in the config file and in the parameters array passed on the method call.*

#### Here is an example of a config file

```php

<?php return [
    // Global
    'connectionOptions' => [
        'host'     => 'your-rabbitmq-server.com',
        'port'     => 5672,
        'user'     => 'your-username',
        'password' => 'your-password',
        'vhost'    => '/'
    ],
    'queueOptions' => [
        'queue'   => 'your.queue.name',
        'durable' => true,
        'nowait'  => false
    ],
    // Publisher
    'exchangeOptions' => [
        'exchange' => 'your.exchange.name',
        'type'     => 'direct'
    ],
    'bindOptions' => [
        'queue'    => 'your.queue.name',
        'exchange' => 'your.exchange.name'
    ],
    'messageOptions' => [
        'properties' => [
            'content_type'     => 'application/json',
            'content_encoding' => 'UTF-8',
            'delivery_mode'    => 2
        ]
    ],
    'publishOptions' => [
        'exchange'    => 'your.exchange.name',
        'routing_key' => 'your.route.name'
    ],
    // Consumer
    'qosOptions' => [
        'prefetch_count' => 25
    ],
    'waitOptions' => [
        'timeout' => 3600
    ],
    'consumeOptions' => [
        'queue'        => 'your.queue.name',
        'consumer_tag' => 'your.consumer.name',
        'callback'     => 'YourNamespace\YourClass::yourCallback'
    ]
    // RPC Endpoints
    'rpcQueueName' => 'your.rpc.queue.name'
];

```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Array first-level key names (suffixed with `Options`) are specific to AMQP Agent.*


---


## Examples

Before we start with examples, we have to clarify a few things. It's worth mentioning from the beginning that with AMQP Agent there are multiple ways to how you can retrieve a worker, there is the simple way, the recommended way, and the more advanced ways. After you retrieve a worker, it's like clay, you can form it the way you want. This modular design gracefully accommodates your needs, drives to a scalable codebase, and simply makes everyone happy.

#### The ways a worker can be retrieved

1. The simplest way is to instantiate a worker directly i.e. using `new` keyword. This way requires passing parameters via the constructor, method calls, or public property assignment.
2. The more advanced way is retrieving a singleton worker i.e `PublisherSingleton::getInstance()`. This way requires passing parameters via `getInstance()` method, method calls, or public property assignment.
3. The more advanced but recommended way is to use an instance of the `Client` class. This way also makes code more readable as the parameters are retrieved from the passed config.


```php
// Instantiating Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\PublisherSingleton;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Worker\ConsumerSingleton;
use MAKS\AmqpAgent\RPC\ClientEndpoint;
use MAKS\AmqpAgent\RPC\ServerEndpoint;

$publisher1 = new Publisher(/* parameters can be passed here */);
$publisher2 = PublisherSingleton::getInstance(/* parameters can be passed here */);

$consumer1 = new Consumer(/* parameters can be passed here */);
$consumer2 = ConsumerSingleton::getInstance(/* parameters can be passed here */);

$rpcClientA = new ClientEndpoint(/* parameters can be passed here */);
$rpcServerA = new ServerEndpoint(/* parameters can be passed here */);

// the parameters from this Config object will be passed to the workers.
$config = new Config('path/to/your/config-file.php');
$client = new Client($config); // path can also be passed directly to Client

$publisher3 = $client->getPublisher(); // or $client->get('publisher');
$consumer3 = $client->getConsumer(); // or $client->get('consumer');

$rpcClientB = $client->getClientEndpoint(); // or $client->get('client.endpoint');
$rpcServerB = $client->getServerEndpoint(); // or $client->get('server.endpoint');

// Use $client->gettable() to get an array of all available services.

```

#### Here are some examples of a publisher

1. **Variant I:** Passing parameters in worker's constructor.

```php
// Publisher Demo 1

$messages = [
    'This is an example message. ID [1].',
    'This is an example message. ID [2].',
    'This is an example message. ID [3].'
];


$publisher = new Publisher(
    [
        // connectionOptions
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    [
        // channelOptions
    ],
    [
        // queueOptions
        'queue' => 'test.messages.queue'
    ],
    [
        // exchangeOptions
        'exchange' => 'test.messages.exchange'
    ],
    [
        // bindOptions
        'queue' => 'test.messages.queue',
        'exchange' => 'test.messages.exchange'
    ],
    [
        // messageOptions
        'properties' => [
            'content_type' => 'text/plain',
        ]
    ],
    [
        // publishOptions
        'exchange' => 'test.messages.exchange'
    ]
);

// Variant I (1)
$publisher->connect()->queue()->exchange()->bind();
foreach ($messages as $message) {
    $publisher->publish($message);
}
$publisher->disconnect();

// Variant I (2)
$publisher->prepare();
foreach ($messages as $message) {
    $publisher->publish($message);
}
$publisher->disconnect();

// Variant I (3)
$publisher->work($messages);

```

2. **Variant II:** Overwriting parameters per method call.

```php
// Publisher Demo 2

$messages = [
    'This is an example message. ID [1].',
    'This is an example message. ID [2].',
    'This is an example message. ID [3].'
];


$publisher = new Publisher();

// connect() method does not take any parameters.
// Public assignment notation is used instead.
// Starting from v1.1.0, you can use getNewConnection(),
// setConnection(), getNewChannel, and setChannel() instead.
$publisher->connectionOptions = [
    'host' => 'localhost',
    'user' => 'guest',
    'password' => 'guest'
];
$publisher->connect();
$publisher->queue([
    'queue' => 'test.messages.queue'
]);
$publisher->exchange([
    'exchange' => 'test.messages.exchange'
]);
$publisher->bind([
    'queue' => 'test.messages.queue',
    'exchange' => 'test.messages.exchange'
]);
foreach ($messages as $message) {
    $publisher->publish(
        [
            'body' => $message,
            'properties' => [
                'content_type' => 'text/plain',
            ]
        ],
        [
            'exchange' => 'test.messages.exchange'
        ]
    );
}
$publisher->disconnect();

```

#### Here are some examples of a consumer

1. **Variant I:** Passing parameters in worker's constructor.

```php
// Consumer Demo 1

$consumer = new Consumer(
    [
        // connectionOptions
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    [
        // channelOptions
    ],
    [
        // queueOptions
        'queue' => 'test.messages.queue'
    ],
    [
        // qosOptions
        'exchange' => 'test.messages.exchange'
    ],
    [
        // waitOptions
    ],
    [
        // consumeOptions
        'queue' => 'test.messages.queue',
        'callback' => 'YourNamespace\YourClass::yourCallback',
    ],
    [
        // publishOptions
        'exchange' => 'test.messages.exchange'
    ]
);

// Variant I (1)
$consumer->connect();
$consumer->queue();
$consumer->qos();
$consumer->consume();
$consumer->wait();
$consumer->disconnect();

// Variant I (2)
$consumer->prepare()->consume()->wait()->disconnect();

// Variant I (3)
$consumer->work('YourNamespace\YourClass::yourCallback');

```

2. **Variant II:** Overwriting parameters per method call.

```php
// Consumer Demo 2

$variable = 'This variable is needed in your callback. It will be the second, the first is always the message!';


$consumer = new Consumer();

// connect() method does not take any parameters.
// Public assignment notation is used instead.
// Starting from v1.1.0, you can use getNewConnection(),
// setConnection(), getNewChannel, and setChannel() instead.
$consumer->connectionOptions = [
    'host' => 'localhost',
    'user' => 'guest',
    'password' => 'guest'
];
$consumer->connect();
$consumer->queue([
    'queue' => 'test.messages.queue'
]);
$consumer->qos([
    'prefetch_count' => 10
]);
$consumer->consume(
    [
        'YourNamespace\YourClass',
        'yourCallback'
    ],
    [
        $variable
    ],
    [
        'queue' => 'test.messages.queue'
    ]
);
$consumer->wait();
$consumer->disconnect();

```

#### Here are some examples of an RPC client

1. **Variant I:** Passing parameters in client's constructor.
```php
// RPC Client Demo 1

$rpcClient = new ClientEndpoint(
    // connectionOptions
    [
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    // queueName
    'your.rpc.queue.name'
);
$rpcClient->connect();
$response = $rpcClient->request('{"command":"some-command","parameter":"some-parameter"}');
$rpcClient->disconnect();

```

2. **Variant II:** Overwriting parameters per method call.
```php
// RPC Client Demo 2

$rpcClient = new ClientEndpoint();
$rpcClient->connect(
    // connectionOptions
    [
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    // queueName
    'your.rpc.queue.name'
);
$response = $rpcClient->request(
    '{"command":"some-command","parameter":"some-parameter"}',
    'your.rpc.queue.name'
);
$rpcClient->disconnect();

```

#### Here are some examples of an RPC server

1. **Variant I:** Passing parameters in server's constructor.
```php
// RPC Server Demo 1

$rpcServer = new ServerEndpoint(
    // connectionOptions
    [
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    // queueName
    'your.rpc.queue.name'
);
$rpcServer->connect();
$request = $rpcServer->respond('YourNamespace\YourClass::yourCallback');
$rpcServer->disconnect();

```

2. **Variant II:** Overwriting parameters per method call.
```php
// RPC Server Demo 2

$rpcServer = new ServerEndpoint();
$rpcServer->connect(
    // connectionOptions
    [
        'host' => 'localhost',
        'user' => 'guest',
        'password' => 'guest'
    ],
    // queueName
    'your.rpc.queue.name'
);
$request = $rpcServer->respond(
    'YourNamespace\YourClass::yourCallback',
    'your.rpc.queue.name'
);
$rpcServer->disconnect();

```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *When supplying parameters provide only the parameters you need. AMQP Agent is smart enough to append the deficiency.*

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *You can simplify the heavy constructors written in the examples above if you use `get($className)` on an instance of the `Client` class after providing a config file with the parameters you want.*

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Refer to [AMQP Agent Docs](https://marwanalsoltany.github.io/amqp-agent/) for the full explanation of the methods. Refer to [RabbitMQ Documentation](https://www.rabbitmq.com/documentation.html) and [php-amqplib](https://github.com/php-amqplib/php-amqplib) for the full explanation of the parameters.*

### Advanced Examples

In these examples, you will see how you would work with AMQP Agent in a real-world scenario.

* **Publisher Example:**
    You will see here how you would publish messages with priority to a queue. Use workers-commands to start additional consumers (sub-processes/threads) for redundancy in case a consumer fails and publish channel-closing commands to close consumers' channels after they finish.

```php
// Advanced Publisher Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Helper\Serializer;

// Preparing some data to work with.
$data = [];
for ($i = 0; $i < 10000; $i++) {
    $data[] = [
        'id' => $i,
        'importance' => $i % 3 == 0 ? 'high' : 'low', // Tag 1/3 of the messages with high importance.
        'text' => 'Test message with ID ' . $i
    ];
}

// Instantiating a config object.
// Note that not passing a config file path falls back to the default config.
// Starting from v1.2.2, you can use has(), get(), set() methods to modify config values.
$config = new Config();

// Instantiating a client.
$client = new Client($config);

// Retrieving a serializer from the client.
/** @var \MAKS\AmqpAgent\Helper\Serializer */
$serializer = $client->get('serializer');

// Retrieving a publisher from the client.
/** @var \MAKS\AmqpAgent\Worker\Publisher */
$publisher = $client->get('publisher');

// Connecting to RabbitMQ server using the default config.
// host: localhost, port: 5672, username: guest, password: guest.
$publisher->connect();

// Declaring high and low importance messages queue.
// Note that this queue is lazy and accept priority messages.
$publisher->queue([
    'queue' => 'high.and.low.importance.queue',
    'arguments' => $publisher->arguments([
        'x-max-priority' => 2,
        'x-queue-mode' => 'lazy'
    ])
]);

// Declaring a direct exchange to publish messages to.
$publisher->exchange([
    'exchange' => 'high.and.low.importance.exchange',
    'type' => 'direct'
]);

// Binding the queue with the exchange.
$publisher->bind([
    'queue' => 'high.and.low.importance.queue',
    'exchange' => 'high.and.low.importance.exchange'
]);

// Publishing messages according to their priority.
foreach ($data as $item) {
    $payload = $serializer->serialize($item, 'JSON');
    if ($item['importance'] == 'high') {
        $publisher->publish(
            [
                'body' => $payload,
                'properties' => [
                    'priority' => 2
                ],
            ],
            [
                'exchange' => 'high.and.low.importance.exchange'
            ]
        );
        continue;
    }
    $publisher->publish(
        $payload, // Not providing priority will fall back to 0
        [
            'exchange' => 'high.and.low.importance.exchange'
        ]
    );
}

// Starting a new consumer after messages with high importance are consumed.
// Pay attention to the priority, this message will be placed just after
// high importance messages but before low importance messages.
$publisher->publish(
    [
        'body' => $serializer->serialize(
            Publisher::makeCommand('start', 'consumer'),
            'JSON'
        ),
        'properties' => [
            'priority' => 1
        ],
    ],
    [
        'exchange' => 'high.and.low.importance.exchange'
    ]
);

// Since we have two consumers now, one from the original worker
// and the other gets started later in the callback. We have
// to publish two channel-closing commands to stop the consumers.
// These will be added at the end after low importance messages.
$iterator = 2;
do {
    $publisher->publish(
        [
            'body' => $serializer->serialize(
                Publisher::makeCommand('close', 'channel'),
                'JSON'
            ),
            'properties' => [
                'priority' => 0
            ],
        ],
        [
            'exchange' => 'high.and.low.importance.exchange'
        ]
    );
    $iterator--;
} while ($iterator != 0);

// Close the connection with RabbitMQ server.
$publisher->disconnect();

```

* **Consumer Example:**
    You will see here how you would consume messages. Read workers-commands to start additional consumers (sub-processes/threads) and close consumers' channels.

```php
// Advanced Consumer Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Helper\Logger;

$config = new Config();
$client = new Client($config);

// Retrieving a logger from the client.
// And setting its write directory and filename.
/** @var \MAKS\AmqpAgent\Helper\Logger */
$logger = $client->get('logger');
$logger->setDirectory(__DIR__);
$logger->setFilename('high-and-low-importance-messages');

// Retrieving a serializer from the client.
/** @var \MAKS\AmqpAgent\Helper\Serializer */
$serializer = $client->get('serializer');

// Retrieving a consumer from the client.
/** @var \MAKS\AmqpAgent\Worker\Consumer */
$consumer = $client->get('consumer');

$consumer->connect();

// Declaring high and low importance messages queue for the consumer.
// The declaration here must match the one on the publisher. This step
// can also be omitted if you're sure that the queue exists on the server.
$consumer->queue([
    'queue' => 'high.and.low.importance.queue',
    'arguments' => $consumer->arguments([
        'x-max-priority' => 2,
        'x-queue-mode' => 'lazy'
    ])
]);

// Overwriting the default quality of service.
$consumer->qos([
    'prefetch_count' => 1,
]);

// The callback is defined here for demonstration purposes
// Normally you should separate this in its own class.
$callback = function($message, &$client, $callback) {
    $data = $client->getSerializer()->unserialize($message->body, 'JSON');

    if (Consumer::isCommand($data)) {
        Consumer::ack($message);
        if (Consumer::hasCommand($data, 'close', 'channel')) {
            // Giving time for acknowledgements to take effect,
            // because the channel will be closed shortly
            sleep(5);
            // Close the channel using the delivery info of the message.
            Consumer::shutdown($message);
        } elseif (Consumer::hasCommand($data, 'start', 'consumer')) {
            $consumer = $client->getConsumer();
            // Getting a new channel on the same connection.
            $channel = $consumer->getNewChannel();
            $consumer->queue(
                [
                    'queue' => 'high.and.low.importance.queue',
                    'arguments' => $consumer->arguments([
                        'x-max-priority' => 2,
                        'x-queue-mode' => 'lazy'
                    ])
                ],
                $channel
            );
            $consumer->qos(
                [
                    'prefetch_count' => 1,
                ],
                $channel
            );
            $consumer->consume(
                $callback,
                [
                    &$client,
                    $callback
                ],
                [
                    'queue' => 'high.and.low.importance.queue',
                    'consumer_tag' => 'callback.consumer-' . uniqid()
                ],
                $channel
            );
        }
        return;
    }

    $client->getLogger()->write("({$data['importance']}) - {$data['text']}");
    // Sleep for 50ms to mimic some processing.
    usleep(50000);

    // The final step is acknowledgment so that no data is lost.
    Consumer::ack($message);
};

$consumer->consume(
    $callback,
    [
        &$client, // Is used to refetch the consumer, serializer, and logger.
        $callback // This gets passed to the consumer that get started by the callback.
    ],
    [
        'queue' => 'high.and.low.importance.queue'
    ]
);

// Here we have to wait using waitForAll() method
// because we have consumers that start dynamically.
$consumer->waitForAll();

// Close the connection with RabbitMQ server.
$consumer->disconnect();

```

* **RPC Client Example:**
    You will see here how you would send request to the RPC Server and add additional functionality to the endpoint by using the events it offers.

```php
// Advanced RPC Client Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\RPC\ClientEndpoint;

$config = new Config();
$client = new Client($config);

// Retrieving an RPC client endpoint from the client.
/** @var \MAKS\AmqpAgent\RPC\ClientEndpoint */
$rpcClient = $client->getClientEndpoint();

// Attaching some additional functionality based on events emitted by the endpoint.
// See $rpcClient->on() and $rpcClient->getEvents() methods for more info.
$rpcClient
    ->on('connection.after.open', function ($connection, $rpcClient, $eventName) {
        printf('%s has emitted [%s] event and is now connected!', get_class($rpcClient), $eventName);
        if ($connection instanceof AMQPStreamConnection) {
            printf('  The connection has currently %d channel(s).', count($connection->channels) - 1);
        }
    })->on('request.before.send', function ($request, $rpcClient, $eventName) {
        printf('%s has emitted [%s] event and is about to send a request!', get_class($rpcClient), $eventName);
        if ($request instanceof AMQPMessage) {
            $request->set('content_type', 'application/json')
            printf('  The request content_type header has been set to: %s', $request->get('content_type'));
        }
    });

// Optionally, you can ping the RabbitMQ server to see if a connection can be established.
$roundtrip = $rpcClient->ping();

$rpcClient->connect();
$response = $rpcClient->request('{"command":"some-command","parameter":"some-parameter"}');
$rpcClient->disconnect();

```

* **RPC Server Example:**
    You will see here how you would respond to request from the RPC Client and add additional functionality to the endpoint by using the events it offers.

```php
// Advanced RPC Server Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\RPC\ServerEndpoint;

$config = new Config();
$client = new Client($config);

// Retrieving an RPC server from the client.
/** @var \MAKS\AmqpAgent\RPC\ServerEndpoint */
$rpcServer = $client->getServerEndpoint();

// Attaching some additional functionality based on events emitted by the endpoint.
// See $rpcServer->on() and $rpcServer->getEvents() methods for more info.
$rpcServer
    ->on('request.on.get', function ($request, $rpcServer, $eventName) {
        printf('%s has emitted [%s] event and has just got a request!', get_class($rpcServer), $eventName);
        if ($request instanceof AMQPMessage) {
            printf('  The request has the following body: %s', $request->body;
        }
    });

$rpcServer->connect();
$request = $rpcServer->respond('YourNamespace\YourClass::yourCallback');
$rpcServer->disconnect();

```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can make the code in Publisher/Consumer Advanced Examples way more easer if you make all parameters' changes in a config file and pass it to the client instead of the default.*

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *AMQP Agent code-base is well documented, please refer to [this link](https://marwanalsoltany.github.io/amqp-agent/classes.html) to have a look over all classes and methods.*


---


## Links
* Documentation: [Full API](https://marwanalsoltany.github.io/amqp-agent/)
* Dependency: [php-amqplib](https://github.com/php-amqplib/php-amqplib)


---


## License

AMQP Agent is an open-sourced package licensed under the [**GNU LGPL v2.1**](./LICENSE) due to [php-amqplib](https://github.com/php-amqplib/php-amqplib) license.
<br/>
Copyright (c) 2020 Marwan Al-Soltany. All rights reserved.
<br/>




[php-icon]: https://img.shields.io/badge/php-%5E7.1-yellow?style=flat-square
[version-icon]: https://img.shields.io/packagist/v/marwanalsoltany/amqp-agent.svg?style=flat-square
[license-icon]: https://img.shields.io/badge/license-LGPL_2.1_or_later-red.svg?style=flat-square
[maintenance-icon]: https://img.shields.io/badge/maintained-yes-orange.svg?style=flat-square
[documentation-icon]: https://img.shields.io/website-up-down-blue-red/http/marwanalsoltany.github.io/amqp-agent.svg?style=flat-square
[downloads-icon]: https://img.shields.io/packagist/dt/marwanalsoltany/amqp-agent.svg?style=flat-square
[travis-icon]: https://img.shields.io/travis/MarwanAlsoltany/amqp-agent/master.svg?style=flat-square
[scrutinizer-icon]: https://img.shields.io/scrutinizer/build/g/MarwanAlsoltany/amqp-agent/master?style=flat-square
[scrutinizer-coverage-icon]: https://img.shields.io/scrutinizer/coverage/g/MarwanAlsoltany/amqp-agent.svg?style=flat-square
[scrutinizer-quality-icon]: https://img.shields.io/scrutinizer/g/MarwanAlsoltany/amqp-agent.svg?style=flat-square
[styleci-icon]: https://github.styleci.io/repos/271944962/shield?branch=master

[php-href]: https://github.com/MarwanAlsoltany/amqp-agent/search?l=php
[version-href]: https://packagist.org/packages/marwanalsoltany/amqp-agent
[license-href]: ./LICENSE
[maintenance-href]: https://github.com/MarwanAlsoltany/amqp-agent/graphs/commit-activity
[documentation-href]: http://marwanalsoltany.github.io/amqp-agent
[downloads-href]: https://packagist.org/packages/marwanalsoltany/amqp-agent/stats
[travis-href]: https://travis-ci.com/MarwanAlsoltany/amqp-agent
[scrutinizer-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/build-status/master
[scrutinizer-coverage-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/?branch=master
[scrutinizer-quality-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/?branch=maste
[styleci-href]: https://github.styleci.io/repos/271944962
