<?php
/**
 * testy
 *
 * Copyright (c)2011-2012, Hans-Peter Buniat <hpbuniat@googlemail.com>.
 * All rights reserved.
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
 * @package Testy
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Base-Command class to handle arguments and start processing
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_TextUI_Command {

    /**
     * Exit code on success
     *
     * @var int
     */
    const SUCCESS_EXIT = 0;

    /**
     * Exit code on failure
     *
     * @var int
     */
    const ERROR_EXIT = 1;

    /**
     * Default config-file name
     *
     * @var string
     */
    const CONFIG_FILE = 'testy.json';

    /**
     * The application name
     *
     * @var string
     */
    const NAME = 'testy';

    /**
     * The version
     *
     * @var string
     */
    const VERSION = 'testy - a continuous test-runner (Version: @package_version@)';

    /**
     * Usage info
     *
     * @var string
     */
    const USAGE = 'Usage: testy.php [--config=testy.json]';

    /**
     * Default-Arguments
     *
     * @var array
     */
    protected $_aArguments = array();

    /**
     * Long-Options
     *
     * @var array
     */
    protected $_aLongOptions = array(
        'help' => null,
        'verbose' => null,
        'config=' => null,
        'version' => null
    );

    /**
     * Short-Options
     *
     * @var array
     */
    protected $_aOptions = array();

    /**
     * The config-builder
     *
     * @var Testy_Config
     */
    protected $_oConfig;

    /**
     * Main entry
     *
     * @codeCoverageIgnore
     */
    public static function main() {
        $command = new Testy_TextUI_Command();
        $command->run($_SERVER['argv']);
    }

    /**
     * Run Testy
     *
     * @param  array $argv
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    public function run(array $argv) {
        try {
            if ($this->handleArguments($argv) === false) {
                return self::SUCCESS_EXIT;
            }

            $oWatch = new Testy_Watch();
            while (true) {
                $oConfig = $this->_oConfig->get();
                if ($this->_oConfig->wasUpdated() === true) {
                    $aNotifiers = array();
                    foreach ($oConfig->setup->notifiers as $sNotifier => $oNotifierConfig) {
                        if (isset($oNotifierConfig->enabled) === true and $oNotifierConfig->enabled == true) {
                            $sNotifier = 'Testy_Notifier_' . ucfirst($sNotifier);
                            $aNotifiers[$sNotifier] = new $sNotifier($oNotifierConfig);
                        }
                    }

                    foreach ($oConfig->projects as $sProject => $oProjectConfig) {
                        $oWatch->add(Testy_Project_Builder::build($sProject, $oProjectConfig, $aNotifiers));
                    }
                }

                $sTransport = (isset($oConfig->setup->parallel) === true) ? $oConfig->setup->parallel : Testy_Util_Parallel_Transport_Builder::TRANSPORT_DEFAULT;
                $oWatch->loop(Testy_Util_Parallel_Transport_Builder::build($sTransport), $oConfig->setup->sleep);
                sleep($oConfig->setup->sleep);
            }
        }
        catch (Testy_Exception $e) {
            Testy_TextUI_Output::error($e->getMessage());
        }

        return true;
    }

    /**
     * Handle passed arguments
     *
     * @param  array $aParameters
     *
     * @return boolean
     *
     * @throws InvalidArgumentException If there a no valid arguments given
     */
    public function handleArguments(array $aParameters = array()) {
        $oConsole = new Console_Getopt();
        try {
            $this->_aOptions = @$oConsole->getopt($aParameters, '', array_keys($this->_aLongOptions));
            if ($this->_aOptions instanceof PEAR_Error) {
                throw new InvalidArgumentException($this->_aOptions->getMessage());
            }

            if (empty($this->_aOptions[0]) !== true) {
                foreach ($this->_aOptions[0] as $option) {
                    switch ($option[0]) {
                        case '--verbose':
                            define('VERBOSE', true);
                            break;

                        case '--config':
                            $this->_aArguments['config'] = $option[1];
                            break;

                        case '--help':
                            self::showHelp();
                            return false;

                        case '--version':
                            self::printVersionString();
                            return false;

                        default:
                            throw new InvalidArgumentException('Unknown option');
                            break;
                    }
                }
            }
        }
        catch (InvalidArgumentException $e) {
            Testy_TextUI_Output::info($e->getMessage());
        }

        unset($oConsole);

        $sConfig = self::CONFIG_FILE;
        if (isset($this->_aArguments['config']) === true) {
            $sConfig = $this->_aArguments['config'];
        }

        $this->_oConfig = new Testy_Config($sConfig);
        return true;
    }

    /**
     * Get the parsed arguments
     *
     * @return array
     */
    public function getArguments() {
        return $this->_aArguments;
    }

    /**
     * Show the help message
     *
     * @return void
     */
    public static function showHelp() {
        Testy_TextUI_Output::info(self::USAGE);
    }

    /**
     * Print the version string
     *
     * @return void
     */
    public static function printVersionString() {
        Testy_TextUI_Output::info(self::VERSION);
    }
}
