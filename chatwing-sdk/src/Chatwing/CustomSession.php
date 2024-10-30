<?php
/**
 * @author chatwing
 * @package Chatwing\SDK
 */

namespace Chatwing;

use Chatwing\Exception\ChatwingException;

class CustomSession extends Object
{
    const BLOCK_SIZE = 16;

    /**
     * secret key to encrypt the session
     * @var string
     */
    protected $secret = '';

    /**
     * the encrypted session
     * @var string
     */
    protected $encryptedSession = null;

    public function __construct($secret = '', $params = array())
    {
        if ($secret) {
            $this->setSecret($secret);
        }

        if (!empty($params)) {
            parent::__construct($params);
        }

        $this->parseData();
    }

    public function setSecret($str)
    {
        $this->secret = $str;
        $this->dataChanged()->parseData();

        return $this;
    }

    /**
     * Return current secret
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->hasData('expire') && ($this->getData('expire') < time());
    }

    /**
     * @return mixed
     */
    public function renew()
    {
        $this->setData('expire', round(microtime(true) * 1000) + 60 * 60 * 1000);
        return $this->dataChanged()->parseData();
    }

    /**
     * @return $this
     */
    protected function parseData()
    {
        if ($this->getSecret() && $this->hasData()) {
            // check if data has been changed, then re-encrypt the session
            if ($this->isDirty()) {
                $this->encryptedSession = null;
                if (!$this->hasData('expire')) {
                    $this->setData('expire', round(microtime(true) * 1000) + 60 * 60 * 1000);
                }
                $this->toEncryptedSession();
            }
        }

        return $this->dataChanged(false);
    }

    /**
     * @return array
     * @throws \Chatwing\Exception\ChatwingException
     */
    protected function getKeyAndIv()
    {
        $secret = $this->getSecret();
        if (!$secret) {
            throw new ChatwingException(array('message' => __('Secret has not been set !!', CHATWING_TEXTDOMAIN)));
        }
        $md5Secret = md5($this->getSecret());
        $encryptionKey = substr($md5Secret, 0, 16);
        $iv = substr($md5Secret, 16, 16);

        return array($encryptionKey, $iv);
    }

    /**
     * @return string
     */
    public function toEncryptedSession()
    {

        if (is_null($this->encryptedSession)) {
            list($encryptionKey, $iv) = $this->getKeyAndIv();
            $data = json_encode($this->getData());
            $pad = self::BLOCK_SIZE - (strlen($data) % self::BLOCK_SIZE);
            $data .= str_repeat(chr($pad), $pad);

            $this->encryptedSession = bin2hex(
                mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryptionKey, $data, MCRYPT_MODE_CBC, $iv)
            );
        }

        return $this->encryptedSession;
    }

    /**
     * @param string $encryptedSession
     *
     * @return array|mixed
     */
    public function toOriginalData($encryptedSession = '')
    {
        list($encryptionKey, $iv) = $this->getKeyAndIv();
        $result = array();
        if (!$encryptedSession) {
            return $result;
        }

        $data = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $encryptionKey,
            hex2bin($encryptedSession),
            MCRYPT_MODE_CBC,
            $iv
        );

        return json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data), true);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toEncryptedSession();
    }

    /**
     * Factory method to create a CustomString object
     * from an encrypted session string
     * @param  string $sessionString
     * @param  string $secret
     * @return CustomSession
     */
    public static function createFromString($sessionString, $secret)
    {
        $cs = new self($secret);
        $data = $cs->toOriginalData($sessionString);
        if (is_array($data) && !empty($data)) {
            $cs->setData($data);
            $cs->dataChanged(false);
            return $cs;
        } else {
            return null;
        }
    }
}
