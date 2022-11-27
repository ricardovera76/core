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

namespace OPNsense\IPsec\FieldTypes;

use OPNsense\Base\FieldTypes\BaseListField;

/**
 * @package OPNsense\Base\FieldTypes
 */
class IPsecProposalField extends BaseListField
{
    private static $internalCacheOptionList = [];

    protected function actionPostLoadingEvent()
    {
        if (empty(self::$internalCacheOptionList)) {
            self::$internalCacheOptionList['default'] = 'default';
            foreach (['aes128', 'aes192', 'aes256', 'aes128gcm16', 'aes192gcm16', 'aes256gcm16',
                      'chacha20poly1305'] as $encalg
            ) {
                foreach (['sha256', 'sha384', 'sha512', 'aesxcbc'] as $intalg) {
                    foreach ([
                        'modp2048', 'modp3072', 'modp4096', 'modp6144', 'modp8192', 'ecp224',
                        'ecp256', 'ecp384', 'ecp521', 'ecp224bp', 'ecp256bp', 'ecp384bp', 'ecp512bp',
                        'x25519', 'x448'] as $dhgroup
                    ) {
                        $cipher = "{$encalg}-{$intalg}-{$dhgroup}";
                        self::$internalCacheOptionList[$cipher] = $cipher;
                    }
                }
            }
            natcasesort(self::$internalCacheOptionList);
        }
        $this->internalOptionList = self::$internalCacheOptionList;
    }
}
