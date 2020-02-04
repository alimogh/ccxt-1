<?php

namespace ccxt;

use Exception; // a common import

class coinbaseprime extends coinbasepro {

    public function describe () {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'coinbaseprime',
            'name' => 'Coinbase Prime',
            'urls' => array(
                'test' => array(
                    'public' => 'https://api-public.sandbox.prime.coinbase.com',
                    'private' => 'https://api-public.sandbox.prime.coinbase.com',
                ),
                'logo' => 'https://user-images.githubusercontent.com/1294454/44539184-29f26e00-a70c-11e8-868f-e907fc236a7c.jpg',
                'api' => array(
                    'public' => 'https://api.prime.coinbase.com',
                    'private' => 'https://api.prime.coinbase.com',
                ),
                'www' => 'https://prime.coinbase.com',
                'doc' => 'https://docs.prime.coinbase.com',
                'fees' => 'https://support.prime.coinbase.com/customer/en/portal/articles/2945629-fees?b_id=17475',
            ),
        ));
    }
}
