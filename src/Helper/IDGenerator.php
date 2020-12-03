<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

/**
 * A class containing miscellaneous helper functions.
 * @since 2.0.0
 */
final class IDGenerator
{
    /**
     * Generates an md5 hash from microtime and uniqid.
     * @param string $entropy [optional] Additional entropy.
     * @return string
     */
    public static function generateHash(string $entropy = 'maks-amqp-agent-id'): string
    {
        $prefix = sprintf('-%s-[%d]-', $entropy, rand());
        $symbol = microtime(true) . uniqid($prefix, true);

        return md5($symbol);
    }

    /**
     * Generates a crypto safe unique token. Note that this function is pretty expensive.
     * @param int $length The length of the token. If the token is hashed this will not be the length of the returned string.
     * @param string $charset [optional] A string of characters to generate the token from. Defaults to alphanumeric.
     * @param string $hashing [optional] A name of hashing algorithm to hash the generated token with. Defaults to no hashing.
     * @return string
     */
    public static function generateToken(int $length = 32, ?string $charset = null, ?string $hashing = null): string
    {
        $token = '';
        $charset = $charset ?? (
            implode(range('A', 'Z')) .
            implode(range('a', 'z')) .
            implode(range(0, 9))
        );
        $max = strlen($charset);

        for ($i = 0; $i < $length; $i++) {
            $token .= $charset[
                self::generateCryptoSecureRandom(0, $max - 1)
            ];
        }

        return $hashing ? hash($hashing, $token) : $token;
    }

    /**
     * Generates a crypto secure random number.
     * @param int $min
     * @param int $max
     * @return int
     */
    protected static function generateCryptoSecureRandom(int $min, int $max): int
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }

        $log = ceil(log($range, 2));
        $bytes = (int)(($log / 8) + 1); // length in bytes
        $bits = (int)($log + 1); // length in bits
        $filter = (int)((1 << $bits) - 1); // set all lower bits to 1

        do {
            $random = PHP_VERSION >= 7
                ? random_bytes($bytes)
                : openssl_random_pseudo_bytes($bytes);
            $random = hexdec(bin2hex($random));
            $random = $random & $filter; // discard irrelevant bits
        } while ($random > $range);

        return $min + $random;
    }
}
