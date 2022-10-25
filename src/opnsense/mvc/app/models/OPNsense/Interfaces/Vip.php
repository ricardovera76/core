<?php

/*
 * Copyright (C) 2022 Deciso B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Interfaces;

use Phalcon\Messages\Message;
use OPNsense\Base\BaseModel;
use OPNsense\Core\Config;
use OPNsense\Firewall\Util;

class Vip extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function performValidation($validateFullModel = false)
    {
        $messages = parent::performValidation($validateFullModel);
        $vips = [];
        $carp_vhids = [];

        // collect chaned VIP entries
        $vip_fields = ['mode', 'subnet', 'subnet_bits', 'password', 'vhid', 'interface'];
        foreach ($this->getFlatNodes() as $key => $node) {
            $tagName = $node->getInternalXMLTagName();
            $parentNode = $node->getParentNode();
            if ($validateFullModel || $node->isFieldChanged()) {
                if ($parentNode->getInternalXMLTagName() === 'vip' && in_array($tagName, $vip_fields)) {
                    $parentKey = $parentNode->__reference;
                    $vips[$parentKey] = $parentNode;
                }
            }
            if ((string)$parentNode->mode == 'carp' && !isset($carp_vhids[(string)$parentNode->vhid])) {
                $carp_vhids[(string)$parentNode->vhid] = $parentNode;
            }
        }

        // validate all changed VIPs
        foreach ($vips as $key => $node) {
            $subnet_bits = (string)$node->subnet_bits;
            $subnet = (string)$node->subnet;
            if (in_array((string)$node->mode, ['carp', 'ipalias'])) {
                if (Util::isSubnet($subnet . "/" . $subnet_bits) && strpos($subnet, ':') === false) {
                    $sm = 0;
                    for ($i = 0; $i < $subnet_bits; $i++) {
                        $sm >>= 1;
                        $sm |= 0x80000000;
                    }
                    $network_addr = long2ip(ip2long($subnet) & $sm);
                    $broadcast_addr = long2ip((ip2long($subnet) & 0xFFFFFFFF) | $sm);
                    if ($subnet == $network_addr && $subnet_bits != '32') {
                        $messages->appendMessage(
                            new Message(
                                gettext("You cannot use the network address for this VIP"),
                                $key . ".subnet"
                            )
                        );
                    } elseif ($subnet == $broadcast_addr && $subnet_bits != '32') {
                        $messages->appendMessage(
                            new Message(
                              gettext("You cannot use the broadcast address for this VIP"),
                              $key . ".subnet"
                            )
                        );
                    }
                }
                $configHandle = Config::getInstance()->object();
                if (!empty($configHandle->interfaces) && !empty((string)$node->vhid)) {
                    foreach ($configHandle->interfaces->children() as $ifname => $ifnode) {
                        if ($ifname === (string)$node->interface && substr($ifnode->if, 0, 2) === 'lo') {
                            $messages->appendMessage(
                                new Message(
                                  gettext('For this type of VIP loopback is not allowed.'),
                                  $key . ".interface"
                                )
                            );
                            break;
                        }
                    }
                }
            }
            if ((string)$node->mode == 'carp') {
                if (empty((string)$node->password)) {
                    $messages->appendMessage(
                        new Message(
                          gettext("You must specify a CARP password that is shared between the two VHID members."),
                          $key . ".password"
                        )
                    );
                }
                if (empty((string)$node->vhid)) {
                    $messages->appendMessage(
                        new Message(
                          gettext('A VHID must be selected for this CARP VIP.'),
                          $key . ".vhid"
                        )
                    );
                } elseif (
                  isset($carp_vhids[(string)$node->vhid]) &&
                  $carp_vhids[(string)$node->vhid]->__reference != $node->__reference
                ) {
                    $errmsg = gettext(
                        "VHID %s is already in use on interface %s. Pick a unique number on this interface."
                    );
                    $messages->appendMessage(
                        new Message(
                          sprintf($errmsg, (string)$node->vhid, (string)$carp_vhids[(string)$node->vhid]->interface),
                          $key . ".vhid"
                        )
                    );
                }
            } elseif (
              (string)$node->mode == 'ipalias' &&
              !empty((string)$node->vhid) && (
                  !isset($carp_vhids[(string)$node->vhid]) ||
                  (string)$carp_vhids[(string)$node->vhid]->interface != (string)$node->interface
              )
            ) {
                $errmsg = gettext("VHID %s must be defined on interface %s as a CARP VIP first.");
                $messages->appendMessage(
                    new Message(
                      sprintf($errmsg, (string)$node->vhid, (string)$node->interface),
                      $key . ".vhid"
                    )
                );
            }
        }

        return $messages;
    }
}
