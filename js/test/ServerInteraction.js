'use strict';
require('../common');

describe('ServerInteraction', function () {
    describe('prepare', function () {
        afterEach(async function () {
            // pause to let async functions conclude
            await new Promise(resolve => setTimeout(resolve, 1900));
        });
        this.timeout(30000);
        it('can prepare an encrypted document', function () {
            jsc.assert(jsc.forall(
                'string',
                'string',
                'string',
                async function (key, password, message) {
                    // pause to let async functions conclude
                    await new Promise(resolve => setTimeout(resolve, 300));
                    let clean = jsdom();
                    window.crypto = new WebCrypto();
                    message = message.trim();

                    $.Zbin.ServerInteraction.prepare();
                    $.Zbin.ServerInteraction.setCryptParameters(password, key);
                    $.Zbin.ServerInteraction.setUnencryptedData('adata', [
                        // encryption parameters defined by CryptTool, format, discussion, burn after reading
                        null, 'plaintext', 0, 0
                    ]);
                    $.Zbin.ServerInteraction.setUnencryptedData('meta', {'expire': '5min'});
                    await $.Zbin.ServerInteraction.setCipherMessage({'paste': message});
                    //console.log($.Zbin.ServerInteraction.getData());
                    clean();
                    // TODO currently not testing anything and just used to generate v2 pastes for starting development of server side v2 implementation
                    return true;
                }
            ),
            {tests: 3});
        });
    });
});
