<?php
/**
 * testy
 *
 * Copyright (c) 2011, Hans-Peter Buniat <hpbuniat@googlemail.com>.
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
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Base-Command class to handle arguments and start processing
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
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
     * Main entry
     *
     * @param boolean $exit
     */
    public static function main($exit = true) {
        $command = new Testy_TextUI_Command();
        $command->run($_SERVER['argv'], $exit);
    }

    /**
     * Run Testy
     *
     * @param  array   $argv
     * @param  boolean $exit
     *
     * @return Testy_TextUI_Command
     */
    public function run(array $argv, $exit = true) {
        $this->_handleArguments($argv);

        $aNotifiers = array(
            new Testy_Notifier_Growl($this->_aArguments['config']->setup->notifiers->growl),
            new Testy_Notifier_Stdout(),
            new Testy_Notifier_Dbus()
        );

        $oWatch = new Testy_Watch();
        foreach ($this->_aArguments['config']->projects as $sProject => $oConfig) {
            $oWatch->add(Testy_Project_Builder::build($sProject, $oConfig, $aNotifiers));
        }

        while(true ) {
            $oWatch->loop();
            sleep($this->_aArguments['config']->setup->sleep);
        }

        return $this;
    }

    /**
     * Handle passed arguments
     *
     * @param array $aParameters
     *
     * @return Testy_TextUI_Command
     */
    protected function _handleArguments(array $aParameters) {
        self::printVersionString();

        $oConsole = new Console_Getopt();
        try {
            $this->_aOptions = @$oConsole->getopt($aParameters, '', array_keys($this->_aLongOptions));
        }
        catch (RuntimeException $e) {
            Testy_TextUI_Output::info($e->getMessage());
        }

        if ($this->_aOptions instanceof PEAR_Error) {
            Testy_TextUI_Output::error($this->_aOptions->getMessage());
        }

        if (empty($this->_aOptions[0]) !== true) {
            foreach ($this->_aOptions[0] as $option) {
                switch ($option[0]) {
                    case '--config' :
                        $this->_aArguments['config'] = $option[1];
                        break;

                    case '--help' :
                    case '--version' :
                        self::showHelp();
                        exit(self::SUCCESS_EXIT);
                        break;
                }
            }
        }

        unset($oConsole);

        $sConfig = self::CONFIG_FILE;
        if (isset($this->_aArguments['config']) === true) {
            $sConfig = $this->_aArguments['config'];
        }

        $this->_aArguments['config'] = json_decode(file_get_contents($sConfig));
        if (empty($this->_aArguments['config']) === true) {
            Testy_TextUI_Output::error(self::CONFIG_ERROR);
            exit();
        }

        return $this;
    }

    /**
     * Show the help message
     *
     * @return void
     */
    public static function showHelp() {
        self::printVersionString();
        Testy_TextUI_Output::info('Usage: Testy [--config=testy.json]');
    }

    /**
     * Print the version string
     *
     * @return void
     */
    public static function printVersionString() {
        Testy_TextUI_Output::info('Testy - a continuous test-runner (Version: @package_version@)');
    }
}