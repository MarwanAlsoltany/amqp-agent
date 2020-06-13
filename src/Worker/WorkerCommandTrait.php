<?php
/**
 * @since 1.0.0
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace MAKS\AmqpAgent\Worker;

/**
 * A trait containing the implementation of the workers command interface/functions.
 * @since 1.0.0
 */
trait WorkerCommandTrait
{
    /**
     * The prefix that should be used to define an array as a command.
     * @var string
     */
    public static $commandPrefix = '__COMMAND__';

    /**
     * The recommended way of defining a command array.
     * @var array
     */
    public static $commandSyntax = [
        '__COMMAND__'    =>    [
            'ACTION'    =>    'OBJECT',
            'PARAMS'    =>    [
                'NAME'    =>    'VALUE'
            ]
        ]
    ];


    /**
     * Constructs a command from passed data to a command array following the recommended pattern.
     * @param string $name The name of the command.
     * @param string $value The vlaue of the command.
     * @param mixed $parameters [optional] Additional parameters to add to the command.
     * @param string $argument [optional] The key to use to store the parameters under.
     * @return array
     */
    public static function makeCommand(string $name, string $value, $parameters = null, string $argument = 'params'): array
    {
        $prefix = static::$commandPrefix;
        $result = [
            $prefix => []
        ];

        if ($name && $value) {
            $result[$prefix] = [
                $name => $value
            ];
            if ($parameters) {
                $result[$prefix][$argument] = $parameters;
            }
        }

        return $result;
    }

    /**
     * Checks wether an array is a command following the recommended pattern.
     * @param mixed $data The data that should be checked.
     * @return bool
     */
    public static function isCommand($data): bool
    {
        $prefix = static::$commandPrefix;

        $result = ($data && is_array($data) && array_key_exists($prefix, $data))
            ? true
            : false;

        return $result;
    }

    /**
     * Checks wether a specific command (command name) exists in the command array.
     * @param array $data The array that should be checked.
     * @param string $name The name of the command.
     * @param string $value The value of the command.
     * @return bool
     */
    public static function hasCommand(array $data, string $name = null, ?string $value = null): bool
    {
        $prefix = static::$commandPrefix;
        $result = static::isCommand($data);

        $result = ($result && $name && array_key_exists($name, $data[$prefix]))
            ? true
            : $result;

        if ($result && $name && $value) {
            $result = (isset($data[$prefix][$name]) && $data[$prefix][$name] === $value)
                ? true
                : false;
        }

        return $result;
    }

    /**
     * Returns the content of a specific key in the command array, used for example to get the additional parameters.
     * @param array $data The array that should be checked.
     * @param string $key [optional] The array key name.
     * @param string $sub [optional] The array nested array key name.
     * @return mixed
     */
    public static function getCommand(array $data, string $key = 'params', ?string $sub = null)
    {
        $prefix = static::$commandPrefix;
        $result = static::isCommand($data);

        if ($result) {
            $result = $data[$prefix];
        }
        if ($result && $key) {
            $result = array_key_exists($key, $data[$prefix])
                ? $data[$prefix][$key]
                : null;
        }
        if ($result && $sub) {
            $result = array_key_exists($sub, $data[$prefix][$key])
                ? $data[$prefix][$key][$sub]
                : null;
        }

        return $result;
    }
}
