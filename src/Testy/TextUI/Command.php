<?php
/**
 * testy
 *
 * Copyright (c) 2011-2013, Hans-Peter Buniat <hpbuniat@googlemail.com>.
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
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace Testy\TextUI;


/**
 * Base-Command class to handle arguments and start processing
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Command {

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
        'config::' => null,
        'version' => null
    );

    /**
     * The config-builder
     *
     * @var \Testy\Config
     */
    protected $_oConfig;

    /**
     * Main entry
     *
     * @codeCoverageIgnore
     */
    public static function main() {
        $command = new Command();
        $command->run($_SERVER['argv']);
    }

    /**
     * Run Testy
     *
     * @param  array $aArguments
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    public function run(array $aArguments) {
        try {
            if ($this->handleArguments($aArguments) === false) {
                return self::SUCCESS_EXIT;
            }

            $oWatch = new \Testy\Watch();
            while (true) {
                $oConfig = $this->_oConfig->get();
                if ($this->_oConfig->wasUpdated() === true) {
                    $aNotifiers = array();
                    foreach ($oConfig->setup->notifiers as $sNotifier => $oNotifierConfig) {
                        if (isset($oNotifierConfig->enabled) === true and $oNotifierConfig->enabled == true) {
                            $aNotifiers[$sNotifier] = \notifyy\Builder::build($sNotifier, $oNotifierConfig);
                        }
                    }

                    foreach ($oConfig->projects as $sProject => $oProjectConfig) {
                        $oWatch->add(\Testy\Project\Builder::build($sProject, $oProjectConfig, $aNotifiers));
                    }
                }

                $sTransport = (isset($oConfig->setup->parallel) === true) ? $oConfig->setup->parallel : \Testy\Util\Parallel\Transport\Builder::TRANSPORT_DEFAULT;
                $oWatch->loop(\Testy\Util\Parallel\Transport\Builder::build($sTransport), $oConfig->setup->sleep);
                sleep($oConfig->setup->sleep);
            }
        }
        catch (\Testy\Exception $e) {
            \Testy\TextUI\Output::error($e->getMessage());
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
     * @throws \InvalidArgumentException If there a no valid arguments given
     */
    public function handleArguments(array $aParameters = array()) {
        try {
            $oOpts = new \GetOptionKit\GetOptionKit();
            $oOpts->add('v|verbose', 'Toggle verbose mode');
            $oOpts->add('h|help', 'Show help');
            $oOpts->add('version', 'Show the version');
            $oOpts->add('c|config?', 'Set the config file');

            $oResult = $oOpts->parse($aParameters);

            if (empty($oResult) !== true) {
                foreach ($oResult as  $sOption => $mValue) {
                    switch ($sOption) {
                        case 'verbose':
                            define('VERBOSE', true);
                            break;

                        case 'config':
                            $this->_aArguments['config'] = $mValue;
                            break;

                        case 'help':
                            self::showHelp();
                            return false;

                        case 'version':
                            self::printVersionString();
                            return false;

                        default:
                            throw new \InvalidArgumentException('Unknown option');
                            break;
                    }
                }
            }
        }
        catch (\InvalidArgumentException $e) {
            \Testy\TextUI\Output::info($e->getMessage());
        }

        $sConfig = self::CONFIG_FILE;
        if (isset($this->_aArguments['config']) === true) {
            $sConfig = $this->_aArguments['config'];
        }

        $this->_oConfig = new \Testy\Config($sConfig);
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
        \Testy\TextUI\Output::info(self::USAGE);
    }

    /**
     * Print the version string
     *
     * @return void
     */
    public static function printVersionString() {
        \Testy\TextUI\Output::info(self::VERSION);
    }
}
