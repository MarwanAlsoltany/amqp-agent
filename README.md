<h1 align="center"><a href="https://marwanalsoltany.github.io/amqp-agent/" title="Documentation" target="_blank">AMQP Agent</a></h1>

<div align="center">

An elegant wrapper around the famous php-amqplib for 90% use case.

[![PHP](https://img.shields.io/badge/PHP-%5E7.4-yellow)](https://github.com/MarwanAlsoltany/amqp-agent/search?l=php) [![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://travis-ci.org/MarwanAlsoltany/amqp-agent/graphs/commit-activity) [![Doc](https://img.shields.io/website-up-down-blue-red/http/marwanalsoltany.github.io/amqp-agent.svg)](http://marwanalsoltany.github.io/amqp-agent/) [![Licence()](https://img.shields.io/github/license/MarwanAlsoltany/amqp-agent.svg)](./LICENCE)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/?branch=master) [![Code Intelligence Status](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence) [![Code Coverage](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/badges/build.png?b=master)](https://scrutinizer-ci.com/g/MarwanAlsoltany/amqp-agent/build-status/master) [![Build Status](https://travis-ci.org/MarwanAlsoltany/amqp-agent.svg?branch=master)](https://travis-ci.org/MarwanAlsoltany/amqp-agent) [![StyleCI](https://github.styleci.io/repos/271944962/shield?branch=master)](https://github.styleci.io/repos/271944962)

<details><summary>Table of Contents</summary>
<p>

[Installtion](#installtion)<br/> [About AMQP Agent](#about-amqp-agent)<br/> [API](#api)<br/> <a href="https://marwanalsoltany.github.io/amqp-agent/" target="_blank">Documentation</a><br/> [Configuration](#configuration)<br/> [Examples](#examples)<br/> [Links](#links)<br/> [License](#license)<br/> [Changelog](./CHANGELOG.md)

</p>
</details>

</div>




---


## Installtion

Try AMQP Agent out now:

#### Composer using Packagist:

```sh
composer require maks/amqp-agent
```

#### Composer using GitHub Repo:

Copy this configuration in your `composer.json`:
```json
"minimum-stability": "dev",
"repositories": {
    "amqp-agent-repo": {
        "type": "vcs",
        "url": "https://github.com/MarwanAlsoltany/amqp-agent.git"
    }
},
"require": {
    "maks/amqp-agent": "dev-master"
}
```

Run:

```sh
composer update
```

---


## About AMQP Agent

AMQP Agent tries to simplify the implementation of a message-broker in a PHP project. It takes away the entire overhead of building and configuring objects that you would need in order to talk with RabbitMQ server (through php-amqplib) and exposes a tested, fully configurable, and flexible API that fits almost any project.

The php-amqplib library is awesome and works very well. The one and only problem is, it's pretty bare-bone to be used in a project. Without remaking your own wrapper classes, it's almost impossible to not write spaghetti code. Plus the enormous amount of functions, methods, and configurations (parameters) that come with it make it really hard to implement a reasonable API to be used. AMQP Agent tries to solve this problem by making as much abstraction as possible without losing control over the workers and by bringing back the terminology associated with message-brokers, a Publisher and a Consumer is all that you need to deal with if you are a newcomer.

According to this motto, AMQP Agent makes working with RabbitMQ as fun and elegant as possible by exposing some fluent interfaces that are cleverly implemented, fit modern PHP development, nice to work with and very simple to use; yet very powerful and can overwrite the smallest quirks at any point of working with the worker. With AMQP Agent you can start publishing and consuming messages with just a few lines of code!

AMQP Agent does not overwrite anything of php-amqplib nor it does change the terminology associated with the functions. It only simplifies it and takes out the noise of function names and extend it in some places. It also adds some nice features like workers-commands, dynamic channel-waiting, and facilitation methods.


---


## API

AMQP Agent exposes a number of concrete classes that can be directly used and other abstract classes that can be extended. These two class-variants also have a helper sub-division.


#### AMQP Agent Classes

| Class | Description | API |
| --- | --- | --- |
| [Publisher](./src/Worker/Publisher.php) <sup><code>*C\*S</code></sup> | A class specialized in publishing. Implementing only the methods needed for a publisher. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_Publisher.html) |
| [Consumer](./src/Worker/Consumer.php) <sup><code>*C\*S</code></sup> | A class specialized in consuming. Implementing only the methods needed for a consumer. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_Consumer.html) |
| [Serializer](./src/Helper/Serializer.php) <sup><code>*C\*H</code></sup> | A flexible serializer to be used in conjuction with the workers. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Serializer.html) |
| [Logger](./src/Helper/Logger.php) <sup><code>*C\*H</code></sup> | A class to write logs, exposing methods that work statically and on instantiation. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Logger.html) |
| [Config](./src/Config.php) <sup><code>*C\*R</code></sup> | A class that turns the configuration file into an object. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Config.html) |
| [Client](./src/Client.php) <sup><code>*C\*R</code></sup> | A class returns everything AMQP Agent has to offer. A simple service container so to say. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Client.html) |
| [AbstractWorker](./src/Worker/AbstractWorker.php) <sup><code>*A</code></sup> | An abstract class implementing the basic functionality of a worker. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_AbstractWorker.html) |
| [Singleton](./src/Helper/Singleton.php) <sup><code>*A\*H</code></sup> | An abstract class implementing the fundamental functionality of a singleton. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Singleton.html) |
| [Example](./src/Helper/Example.php) <sup><code>*A\*H</code></sup> | An abstract class used as a default callback for the consumer. | [Doc](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Helper_Example.html) |

> See also: [AbstractWorkerSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_AbstractWorkerSingleton.html), [PublisherSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_PublisherSingleton.html), [ConsumerSingleton](https://marwanalsoltany.github.io/amqp-agent/classes/MAKS_AmqpAgent_Worker_ConsumerSingleton.html), [AbstractWorkerInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_AbstractWorkerInterface.html), [PublisherInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_PublisherInterface.html), [ConsumerInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_ConsumerInterface.html), [WorkerFacilitationInterface](https://marwanalsoltany.github.io/amqp-agent/interfaces/MAKS_AmqpAgent_Worker_WorkerFacilitationInterface.html), [WorkerMutationTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Worker_WorkerMutationTrait.html), [WorkerCommandTrait](https://marwanalsoltany.github.io/amqp-agent/traits/MAKS_AmqpAgent_Worker_WorkerCommandTrait.html).

#### Bibliography
* <code>*C</code> **Concrete:** This class is a concrete class and can be instantiated directly.
* <code>*A</code> **Abstract:** This class is an abstract class and cannot be instantiated directly.
* <code>*H</code> **Helper:** This class is a helper class. Third-party alternatives can be freely used instead.
* <code>*R</code> **Recommended:** This class is recommended to be used when working with AMQP Agent (best practice).
* <code>*S</code> **Singleton:** This class has a singleton version available via suffixing the class name with `Singleton` i.e. `PublisherSingleton` and can be retrieved via `*Singleton::getInstance()`.

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Singleton is considered anti-pattern, try avoiding them as much as possible, though there are use-cases for it. Use singletons only if you know what you are doing.*


---


## Configuration

If you just quickly want to publish and consume messages, everything is ready and configured already, AMQP Agent is shipped with a tested configuration that follows best practices. You can simply import `Publisher` class and/or `Consumer` class in your file and overwrite the parameters you want (RabbitMQ credentials for example) later on the instance.

If you want to fine-tune and tweak AMQP Agent configuration to your exact needs, there is a bit of work to do. You have to supply a config file (see: [maks-amqp-agent-config.php](./src/Config/maks-amqp-agent-config.php) and pay attention to the comments). You don't have to supply everything, you can simply only write the parameters you want to overwrite, AMQP Agent is smart enough to append the deficiency. These parameters can also be overwritten later through public assignment notation or per method call.

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *AMQP Agent uses the same parameter names as php-amqplib in the config file and in the overrides array passed on the method call.*

#### Here is an example of a config file

```php
<?php

return [
    // Global
    'connectionOptions' => [
        'host'                   =>    'your-rabbitmq-server.com',
        'port'                   =>    5672,
        'user'                   =>    'your-username',
        'password'               =>    'your-password',
        'vhost'                  =>    '/'
    ],
    'queueOptions' => [
        'queue'          =>    'your.queue.name',
        'durable'        =>    true,
        'nowait'         =>    false
    ],
    // Publisher
    'exchangeOptions' => [
        'exchange'       =>    'your.exchange.name',
        'type'           =>    'direct'
    ],
    'bindOptions' => [
        'queue'          =>    'your.queue.name',
        'exchange'       =>    'your.exchange.name'
    ],
    'messageOptions' => [
        'properties'    =>    [
            'content_type'        =>    'application/json',
            'content_encoding'    =>    'UTF-8',
            'delivery_mode'       =>    2
        ]
    ],
    'publishOptions' => [
        'exchange'       =>    'your.exchange.name',
        'routing_key'    =>    'your.route.name'
    ],
    // Consumer
    'qosOptions' => [
        'prefetch_count'    =>    25
    ],
    'waitOptions' => [
        'timeout'            =>    3600
    ],
    'consumeOptions' => [
        'queue'           =>    'your.queue.name',
        'consumer_tag'    =>    'your.consumer.name',
        'callback'        =>    'YourNamespace\YourClass::yourCallback'
    ]
];

```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Array key names suffixed with `Option` are specific to AMQP Agent.*


---


## Examples

Before we start with examples, we have to clarify a few things. It's worth mentioning from the beginning that with AMQP Agent there are multiple ways to how you can retrieve a worker, there is the simple way, the recommended way, and the more advanced ways. After you retrieve a worker, it's like clay, you can form it the way you want. This modular design gracefully accommodates your needs, drives to a scalable code-base, and simply makes everyone happy.

#### The ways a worker can be retrieved

1. The simplest way is to instantiate a worker directly i.e. using `new` keyword. This way requires passing parameters via constructor, method calls, or public property assginment.
2. The more advanced way is retriving a singleton worker i.e `*Singleton::getInstance()`. This way requires passing parameters via `getInstance()` method, method calls, or public property assginment.
3. The more advanced but recommended way is to use an instance of the `Client` class. This way also makes code more readable as the parameters are retrived from the passed config.


```php
// Instantiating Demo

use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\PublisherSingleton;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Worker\ConsumerSingleton;

$publisher1 = new Publisher(/* parameters can be passed here */);
$publisher2 = PublisherSingleton::getInstance(/* parameters can be passed here */);

$consumer1 = new Consumer(/* parameters can be passed here */);
$consumer2 = ConsumerSingleton::getInstance(/* parameters can be passed here */);

// the parameters from this Config object will be passed to the workers.
$config = new Config('path/to/your/config-file.php');
$client = new Client($config); // path can also be passed directly to Client

$publisher3 = $client->getPublisher(); // or $client->get('publisher');
$consumer3 = $client->getConsumer(); // or $client->get('consumer');

```

#### Here are some examples of a publisher

1. **Variant I:** Passing parameters in workers constructor

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

2. **Variant II:** Overwriting parameters per method call

```php
// Publisher Demo 2

$messages = [
    'This is an example message. ID [1].',
    'This is an example message. ID [2].',
    'This is an example message. ID [3].'
];

// connect() method does not take any parameters.
// Public assginment notation is used instead.
$publisher->connectionOptions = [
    'host' => 'localhost',
    'user' => 'guest',
    'password' => 'guest'
];
$consumer->connect();
$consumer->queue([
    'queue' => 'test.messages.queue'
]);
$consumer->exchange([
    'exchange' => 'test.messages.exchange'
]);
$consumer->bind([
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

1. **Variant I:** Passing parameters in workers constructor

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

2. **Variant II:** Overwriting parameters per method call

```php
// Consumer Demo 2

$consumer = new Consumer();
$variable = 'This variable is needed in your callback. It will be the second, the first is always the message!';

// connect() method does not take any parameters.
// Public assginment notation is used instead.
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

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *When supplying parameters provide only the parameters you need. AMQP Agent is smart enough to append the deficiency.*

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *You can simplifiy the heavy constructors written in the examples above if you use `getPublisher()` on an instance of the `Client` class after providing a config file with the parameters you want.*

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Refere to [AMQP Agent Docs](https://marwanalsoltany.github.io/amqp-agent/) for the full explanation of the methods. Refere to [RabbitMQ Documentation](https://www.rabbitmq.com/documentation.html) and [php-amqplib](https://github.com/php-amqplib/php-amqplib) for the full explanation of the parameters.*


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