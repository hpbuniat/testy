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
namespace Testy\Util\Parallel\Transport;


/**
 * Builder for Transports
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/Testy
 */
class Builder {

    /**
     * Class for file-transport
     *
     * @var string
     */
    const TRANSPORT_FILE = '\Testy\Util\Parallel\Transport\File';

    /**
     * Class for shared-memory-transport
     *
     * @var string
     */
    const TRANSPORT_SHARED = '\Testy\Util\Parallel\Transport\SharedMemory';

    /**
     * Class for memcache-transport
     *
     * @var string
     */
    const TRANSPORT_MEMCACHE = '\Testy\Util\Parallel\Transport\Memcache';

    /**
     * The default transport
     *
     * @var string
     */
    const TRANSPORT_DEFAULT = 'SharedMemory';

    /**
     * Build a transport
     *
     * @param  string $sType
     *
     * @return \Testy\Util\Parallel\TransportInterface
     *
     * @throws \Testy\Util\Parallel\Transport\Exception If a unknown transport is provided
     */
    public static function build($sTransport) {
        $sBuild = strtolower($sTransport);
        switch ($sBuild) {
            case 'file':
                $sBuild = self::TRANSPORT_FILE;
                break;

            case 'memcache':
                $sBuild = self::TRANSPORT_MEMCACHE;
                break;

            case 'shared':
            case 'sharedmemory':
                $sBuild = self::TRANSPORT_SHARED;
                break;

            default:
                throw new \Testy\Util\Parallel\Transport\Exception(\Testy\Util\Parallel\Transport\Exception::UNKNOWN_TRANSPORT);
        }

        return new $sBuild();
    }
}
