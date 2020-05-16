<?php

namespace ccxt;

use Exception; // a common import

class binanceje extends binance {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'binanceje',
            'name' => 'Binance Jersey',
            'countries' => array( 'JE' ), // Jersey
            'certified' => false,
            'pro' => true,
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/54874009-d526eb00-4df3-11e9-928c-ce6a2b914cd1.jpg',
                'api' => array(
                    'web' => 'https://www.binance.je',
                    'wapi' => 'https://api.binance.je/wapi/v3',
                    'public' => 'https://api.binance.je/api/v1',
                    'private' => 'https://api.binance.je/api/v3',
                    'v3' => 'https://api.binance.je/api/v3',
                    'v1' => 'https://api.binance.je/api/v1',
                ),
                'www' => 'https://www.binance.je',
                'referral' => 'https://www.binance.je/?ref=35047921',
                'doc' => 'https://github.com/binance-exchange/binance-official-api-docs/blob/master/rest-api.md',
                'fees' => 'https://www.binance.je/fees.html',
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'taker' => 0.0005,
                    'maker' => 0.0005,
                ),
                // should be deleted, these are outdated and inaccurate
                'funding' => array(
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(
                        'BTC' => 0.0005,
                        'ETH' => 0.01,
                    ),
                    'deposit' => array(),
                ),
            ),
            'options' => array(
                'quoteOrderQty' => false, // whether market orders support amounts in quote currency
            ),
        ));
    }
}
