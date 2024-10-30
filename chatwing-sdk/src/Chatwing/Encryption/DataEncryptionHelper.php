<?php
/**
 * @author Chatwing <dev@chatwing.com>
 * @package Chatwing\SDK\Encryption
 */

namespace Chatwing\Encryption;

class DataEncryptionHelper
{
    protected static $encryptionKey = null;

    /**
     * Set encryption key
     * @param string $key
     */
    public static function setEncryptionKey($key)
    {
        static::$encryptionKey = $key;
    }

    /**
     * Get current encryption key
     * @return string
     */
    public static function getEncryptionKey()
    {
        return static::$encryptionKey;
    }

    /**
     * Generate encryption key
     * @param int $resultLength
     * @return string
     */
    public static function generateKey($resultLength = 16)
    {
        $key = '';
        $characterSpace = 'abcdeFVWDEXYfghijkG908765HIJKvwLQRSTUlmnopqrsMNOPtuxyzABCZ4321';
        $len = strlen($characterSpace);
        for ($i = 0; $i < $resultLength; $i++) {
            $randomPos = mt_rand(0, $len - 1);
            $key .= $characterSpace[$randomPos];
        }

        return $key;
    }

    public static function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    public static function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * Encrypt a text using current encryption key
     * @param  string $text
     * @throws \ErrorException If no encryption key was set
     * @return string
     */
    public static function encrypt($text)
    {
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $encryptedText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::getEncryptionKey(), $text, MCRYPT_MODE_ECB, $iv);
        return trim(self::safe_b64encode($encryptedText));
    }

    /**
     * Decrypt encrypted text
     * @param  string $text
     * @throws \ErrorException If no encryption key was set
     * @return string
     */
    public static function decrypt($text)
    {
        $encryptedText = self::safe_b64decode($text);
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $decryptedText = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::getEncryptionKey(), $encryptedText, MCRYPT_MODE_ECB, $iv);
        return trim($decryptedText);
    }
}
