<?php
/**
 * Registry for testy - taken from Zend Framework
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Hans-Peter Buniat nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Generic storage class helps to manage global data.
 *
 * @category   Zend
 * @package    Zend_Registry
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Testy_Util_Registry extends ArrayObject {

    /**
     * Registry object provides storage for shared objects.
     *
     * @var Testy_Util_Registry
     */
    private static $_registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return Testy_Util_Registry
     */
    public static function getInstance() {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Testy_Util_Registry $registry An object instance of type Testy_Util_Registry, or a subclass.
     *
     * @return void
     *
     * @throws Zend_Exception if registry is already initialized.
     */
    public static function setInstance(Testy_Util_Registry $registry) {
        if (self::$_registry !== null) {
            throw new Exception('Registry is already initialized');
        }

        self::$_registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init() {
        self::setInstance(new self());
    }

    /**
     * Getter method, basically same as offsetGet().
     *
     * @param string $index - get the value associated with $index
     *
     * @return mixed
     *
     * @throws Exception if no entry is registerd for $index.
     */
    public static function get($index) {
        $instance = self::getInstance();
        if (! $instance->offsetExists($index)) {
            throw new Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    /**
     * Setter method, basically same as offsetSet().
     *
     * @param string $index The location in the ArrayObject in which to store the value.
     * @param mixed $value The object to store in the ArrayObject.
     *
     * @return void
     */
    public static function set($index, $value) {
        self::getInstance()->offsetSet($index, $value);
    }

    /**
     * Returns true if the $index is a named value in the registry,
     * or false if $index was not found in the registry.
     *
     * @param  string $index
     *
     * @return boolean
     */
    public static function isRegistered($index) {
        if (self::$_registry === null) {
            return false;
        }

        return self::$_registry->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS) {
        parent::__construct($array, $flags);
    }

    /**
     * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
     *
     * @param string $index
     *
     * @returns mixed
     */
    public function offsetExists($index) {
        return array_key_exists($index, $this);
    }
}
