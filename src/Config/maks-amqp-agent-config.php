<?php

namespace MAKS\AmqpAgent\Config;

use MAKS\AmqpAgent\Config\AmqpAgentParameters;

return [
    // Global
    // Start of static/constant class properties. specific to AMQP Agent.
    // Only for reference, modifying them won't change anything.
    'prefix' => AmqpAgentParameters::PREFIX, // default
    // If you want to modify command* use ClassName::$variableName.
    'commandPrefix' => AmqpAgentParameters::COMMAND_PREFIX,
    'commandSyntax' => AmqpAgentParameters::COMMAND_SYNTAX,
    // End of static/constant class properties. specific to AMQP Agent.

    // AbstractWorker
    'connectionOptions' => AmqpAgentParameters::CONNECTION_OPTIONS,
    'channelOptions'    => AmqpAgentParameters::CHANNEL_OPTIONS,
    'queueOptions'      => AmqpAgentParameters::QUEUE_OPTIONS,

    // Publisher
    'exchangeOptions' => AmqpAgentParameters::EXCHANGE_OPTIONS,
    'bindOptions'     => AmqpAgentParameters::BIND_OPTIONS,
    'messageOptions'  => AmqpAgentParameters::MESSAGE_OPTIONS,
    'publishOptions'  => AmqpAgentParameters::PUBLISH_OPTIONS,

    // Consumer
    'qosOptions'     => AmqpAgentParameters::QOS_OPTIONS,
    'waitOptions'    => AmqpAgentParameters::WAIT_OPTIONS,
    'consumeOptions' => AmqpAgentParameters::CONSUME_OPTIONS,

    // Start of constant class properties.
    // Only for reference, modifying them won't change anything
    'ackOptions'     => AmqpAgentParameters::ACK_OPTIONS,
    'nackOptions'    => AmqpAgentParameters::NACK_OPTIONS,
    'getOptions'     => AmqpAgentParameters::GET_OPTIONS,
    'cancelOptions'  => AmqpAgentParameters::CANCEL_OPTIONS,
    'recoverOptions' => AmqpAgentParameters::RECOVER_OPTIONS,
    'rejectOptions'  => AmqpAgentParameters::REJECT_OPTIONS,
    // End of constant class properties.
];
