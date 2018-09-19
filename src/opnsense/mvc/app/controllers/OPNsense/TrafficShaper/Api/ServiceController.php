<?php
/**
 *    Copyright (C) 2015-2016 Deciso B.V.
 *    Copyright (C) 2018 Michael Muenz <m.muenz@gmail.com>
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace OPNsense\TrafficShaper\Api;

use \OPNsense\Base\ApiControllerBase;
use \OPNsense\Core\Backend;

/**
 * Class ServiceController
 * @package OPNsense\TrafficShaper
 */
class ServiceController extends ApiControllerBase
{

    /**
     * reconfigure ipfw, generate config and reload
     */
    public function reconfigureAction()
    {
        if ($this->request->isPost()) {
            // close session for long running action
            $this->sessionClose();

            $backend = new Backend();
            $backend->configdRun('template reload OPNsense/IPFW');
            $bckresult = trim($backend->configdRun("ipfw reload"));
            if ($bckresult == "OK") {
                $status = "ok";
            } else {
                $status = "error reloading shaper (".$bckresult.")";
            }

            return array("status" => $status);
        } else {
            return array("status" => "failed");
        }
    }

    /**
     * flush all ipfw rules
     */
    public function flushreloadAction()
    {
        if ($this->request->isPost()) {
            // close session for long running action
            $this->sessionClose();

            $backend = new Backend();
            $status = trim($backend->configdRun("ipfw flush"));
            $status = trim($backend->configdRun("ipfw reload"));
            return array("status" => $status);
        } else {
            return array("status" => "failed");
        }
    }

    /**
     * show scheduler statistics
     * @return array
     */
    public function showschedAction()
    {
        $backend = new Backend();
        $response = $backend->configdRun("ipfw showsched");
        return array("response" => $response);
    }

    /**
     * show queue statistics
     * @return array
     */
    public function showqueueAction()
    {
        $backend = new Backend();
        $response = $backend->configdRun("ipfw showqueue");
        return array("response" => $response);
    }
}
