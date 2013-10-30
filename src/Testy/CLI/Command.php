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
namespace Testy\CLI;

use Symfony\Component\Console\Command\Command as AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base-Command class to handle arguments and start processing
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Command extends AbstractCommand {

    /**
     * Default config-file name
     *
     * @var string
     */
    const CONFIG_FILE = 'testy.json';

    /**
     * Configure the current command
     *
     * @return void
     */
    protected function configure() {
        $this->setName('testy')
             ->setDescription('Run testy')
             ->addOption(
                 'config',
                 'c',
                 InputOption::VALUE_REQUIRED,
                 'Set the config file',
                 self::CONFIG_FILE
            );
    }

    /**
     * Executes the current command.
     *
     * @param  InputInterface $input An InputInterface instance
     * @param  OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $oConfig = new \Testy\Config($input->getOption('config'));
            $oConfig->validate();

            $bFirstRun = true;
            $oWatch = new \Testy\Watch();
            while (true) {
                $oRawConfig = $oConfig->get();
                if ($bFirstRun === true or $oConfig->wasUpdated() === true) {
                    $aNotifiers = array();
                    foreach ($oRawConfig->setup->notifiers as $sNotifier => $oNotifierConfig) {
                        if (isset($oNotifierConfig->enabled) === true and $oNotifierConfig->enabled == true) {
                            $aNotifiers[$sNotifier] = \notifyy\Builder::build($sNotifier, $oNotifierConfig);
                        }
                    }

                    foreach ($oRawConfig->projects as $sProject => $oProjectConfig) {
                        $oWatch->add(\Testy\Project\Builder::build($sProject, $oProjectConfig, $aNotifiers));
                    }
                }

                $oWatch->loop($oRawConfig->setup->parallel, $oRawConfig->setup->sleep);
                sleep($oRawConfig->setup->sleep);
                $bFirstRun = false;
            }
        }
        catch (\Testy\Exception $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        return null;
    }
}