<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\File;

use Zend\Validator\Exception;

/**
 * Validator for the sha1 hash of given files
 *
 * @category  Zend
 * @package   Zend_Validator
 */
class Sha1 extends Hash
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileSha1DoesNotMatch';
    const NOT_DETECTED   = 'fileSha1NotDetected';
    const NOT_FOUND      = 'fileSha1NotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_NOT_MATCH => "File does not match the given sha1 hashes",
        self::NOT_DETECTED   => "A sha1 hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File is not readable or does not exist",
    );

    /**
     * Options for this validator
     *
     * @var string
     */
    protected $options = array(
        'algorithm' => 'sha1',
        'hash'      => null,
    );

    /**
     * Returns all set sha1 hashes
     *
     * @return array
     */
    public function getSha1()
    {
        return $this->getHash();
    }

    /**
     * Sets the sha1 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     */
    public function setSha1($options)
    {
        $this->setHash($options);
        return $this;
    }

    /**
     * Adds the sha1 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     */
    public function addSha1($options)
    {
        $this->addHash($options);
        return $this;
    }

    /**
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string $value|array Filename to check for hash
     * @return bool
     */
    public function isValid($value)
    {
        if (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
        } else {
            $file     = $value;
            $filename = basename($file);
        }
        $this->setValue($filename);

        // Is file readable ?
        if (false === stream_resolve_include_path($file)) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $hashes = array_unique(array_keys($this->getHash()));
        $filehash = hash_file('sha1', $file);
        if ($filehash === false) {
            $this->error(self::NOT_DETECTED);
            return false;
        }

        foreach ($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        $this->error(self::DOES_NOT_MATCH);
        return false;
    }
}