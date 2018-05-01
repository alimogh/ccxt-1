<?php

namespace ccxt;

use Exception as Exception; // a common import

class yobit extends liqui {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'yobit',
            'name' => 'YoBit',
            'countries' => 'RU',
            'rateLimit' => 3000, // responses are cached every 2 seconds
            'version' => '3',
            'has' => array (
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
                'CORS' => false,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766910-cdcbfdae-5eea-11e7-9859-03fea873272d.jpg',
                'api' => array (
                    'public' => 'https://yobit.net/api',
                    'private' => 'https://yobit.net/tapi',
                ),
                'www' => 'https://www.yobit.net',
                'doc' => 'https://www.yobit.net/en/api/',
                'fees' => 'https://www.yobit.net/en/fees/',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'depth/{pair}',
                        'info',
                        'ticker/{pair}',
                        'trades/{pair}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'ActiveOrders',
                        'CancelOrder',
                        'GetDepositAddress',
                        'getInfo',
                        'OrderInfo',
                        'Trade',
                        'TradeHistory',
                        'WithdrawCoinsToAddress',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.002,
                    'taker' => 0.002,
                ),
                'funding' => array (
                    'withdraw' => array (),
                ),
            ),
            'commonCurrencies' => array (
                'AIR' => 'AirCoin',
                'ANI' => 'ANICoin',
                'ANT' => 'AntsCoin',
                'AST' => 'Astral',
                'ATM' => 'Autumncoin',
                'BCC' => 'BCH',
                'BCS' => 'BitcoinStake',
                'BLN' => 'Bulleon',
                'BTS' => 'Bitshares2',
                'CAT' => 'BitClave',
                'COV' => 'Coven Coin',
                'CPC' => 'Capricoin',
                'CS' => 'CryptoSpots',
                'DCT' => 'Discount',
                'DGD' => 'DarkGoldCoin',
                'DROP' => 'FaucetCoin',
                'ERT' => 'Eristica Token',
                'ICN' => 'iCoin',
                'KNC' => 'KingN Coin',
                'LIZI' => 'LiZi',
                'LOC' => 'LocoCoin',
                'LOCX' => 'LOC',
                'LUN' => 'LunarCoin',
                'MDT' => 'Midnight',
                'NAV' => 'NavajoCoin',
                'OMG' => 'OMGame',
                'STK' => 'StakeCoin',
                'PAY' => 'EPAY',
                'PLC' => 'Platin Coin',
                'REP' => 'Republicoin',
                'RUR' => 'RUB',
                'XIN' => 'XINCoin',
            ),
            'options' => array (
                'fetchOrdersRequiresSymbol' => true,
            ),
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open',
            '1' => 'closed',
            '2' => 'canceled',
            '3' => 'open', // or partially-filled and closed? https://github.com/ccxt/ccxt/issues/1594
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ();
        $balances = $response['return'];
        $result = array ( 'info' => $balances );
        $sides = array ( 'free' => 'funds', 'total' => 'funds_incl_orders' );
        $keys = is_array ($sides) ? array_keys ($sides) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $side = $sides[$key];
            if (is_array ($balances) && array_key_exists ($side, $balances)) {
                $currencies = is_array ($balances[$side]) ? array_keys ($balances[$side]) : array ();
                for ($j = 0; $j < count ($currencies); $j++) {
                    $lowercase = $currencies[$j];
                    $uppercase = strtoupper ($lowercase);
                    $currency = $this->common_currency_code($uppercase);
                    $account = null;
                    if (is_array ($result) && array_key_exists ($currency, $result)) {
                        $account = $result[$currency];
                    } else {
                        $account = $this->account ();
                    }
                    $account[$key] = $balances[$side][$lowercase];
                    if ($account['total'] && $account['free'])
                        $account['used'] = $account['total'] - $account['free'];
                    $result[$currency] = $account;
                }
            }
        }
        return $this->parse_balance($result);
    }

    public function create_deposit_address ($code, $params = array ()) {
        $response = $this->fetch_deposit_address ($code, array_merge (array (
            'need_new' => 1,
        ), $params));
        $address = $this->safe_string($response, 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'status' => 'ok',
            'info' => $response['info'],
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'coinName' => $currency['id'],
            'need_new' => 0,
        );
        $response = $this->privatePostGetDepositAddress (array_merge ($request, $params));
        $address = $this->safe_string($response['return'], 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'status' => 'ok',
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privatePostWithdrawCoinsToAddress (array_merge (array (
            'coinName' => $currency['id'],
            'amount' => $amount,
            'address' => $address,
        ), $params));
        return array (
            'info' => $response,
            'id' => null,
        );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('success', $response)) {
                if (!$response['success']) {
                    if (is_array ($response) && array_key_exists ('error_log', $response)) {
                        if (mb_strpos ($response['error_log'], 'Insufficient funds') !== false) { // not enougTh is a typo inside Liqui's own API...
                            throw new InsufficientFunds ($this->id . ' ' . $this->json ($response));
                        } else if ($response['error_log'] === 'Requests too often') {
                            throw new DDoSProtection ($this->id . ' ' . $this->json ($response));
                        } else if (($response['error_log'] === 'not available') || ($response['error_log'] === 'external service unavailable')) {
                            throw new DDoSProtection ($this->id . ' ' . $this->json ($response));
                        }
                    }
                    throw new ExchangeError ($this->id . ' ' . $this->json ($response));
                }
            }
        }
    }
}
