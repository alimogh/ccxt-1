<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinbasepro extends gdax {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinbasepro',
            'name' => 'Coinbase Pro',
            'urls' => array (
                'test' => 'https://api-public.sandbox.pro.coinbase.com',
                'logo' => 'https://user-images.githubusercontent.com/1294454/41764625-63b7ffde-760a-11e8-996d-a6328fa9347a.jpg',
                'api' => 'https://api.pro.coinbase.com',
                'www' => 'https://pro.coinbase.com/',
                'doc' => 'https://docs.pro.coinbase.com/',
                'fees' => array (
                    'https://docs.pro.coinbase.com/#fees',
                    'https://support.pro.coinbase.com/customer/en/portal/articles/2945310-fees',
                ),
            ),
        ));
    }
}
