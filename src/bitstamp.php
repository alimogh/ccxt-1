<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitstamp extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitstamp',
            'name' => 'Bitstamp',
            'countries' => array ( 'GB' ),
            'rateLimit' => 1000,
            'version' => 'v2',
            'has' => array (
                'CORS' => true,
                'fetchDepositAddress' => true,
                'fetchOrder' => 'emulated',
                'fetchOpenOrders' => true,
                'fetchMyTrades' => true,
                'fetchTransactions' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27786377-8c8ab57e-5fe9-11e7-8ea4-2b05b6bcceec.jpg',
                'api' => 'https://www.bitstamp.net/api',
                'www' => 'https://www.bitstamp.net',
                'doc' => 'https://www.bitstamp.net/api',
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'uid' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'order_book/{pair}/',
                        'ticker_hour/{pair}/',
                        'ticker/{pair}/',
                        'transactions/{pair}/',
                        'trading-pairs-info/',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balance/',
                        'balance/{pair}/',
                        'bch_withdrawal/',
                        'bch_address/',
                        'user_transactions/',
                        'user_transactions/{pair}/',
                        'open_orders/all/',
                        'open_orders/{pair}/',
                        'order_status/',
                        'cancel_order/',
                        'buy/{pair}/',
                        'buy/market/{pair}/',
                        'buy/instant/{pair}/',
                        'sell/{pair}/',
                        'sell/market/{pair}/',
                        'sell/instant/{pair}/',
                        'ltc_withdrawal/',
                        'ltc_address/',
                        'eth_withdrawal/',
                        'eth_address/',
                        'xrp_withdrawal/',
                        'xrp_address/',
                        'transfer-to-main/',
                        'transfer-from-main/',
                        'withdrawal-requests/',
                        'withdrawal/open/',
                        'withdrawal/status/',
                        'withdrawal/cancel/',
                        'liquidation_address/new/',
                        'liquidation_address/info/',
                    ),
                ),
                'v1' => array (
                    'post' => array (
                        'bitcoin_deposit_address/',
                        'unconfirmed_btc/',
                        'bitcoin_withdrawal/',
                        'ripple_withdrawal/',
                        'ripple_address/',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => 0.25 / 100,
                    'maker' => 0.25 / 100,
                    'tiers' => array (
                        'taker' => [
                            [0, 0.25 / 100],
                            [20000, 0.24 / 100],
                            [100000, 0.22 / 100],
                            [400000, 0.20 / 100],
                            [600000, 0.15 / 100],
                            [1000000, 0.14 / 100],
                            [2000000, 0.13 / 100],
                            [4000000, 0.12 / 100],
                            [20000000, 0.11 / 100],
                            [20000001, 0.10 / 100],
                        ],
                        'maker' => [
                            [0, 0.25 / 100],
                            [20000, 0.24 / 100],
                            [100000, 0.22 / 100],
                            [400000, 0.20 / 100],
                            [600000, 0.15 / 100],
                            [1000000, 0.14 / 100],
                            [2000000, 0.13 / 100],
                            [4000000, 0.12 / 100],
                            [20000000, 0.11 / 100],
                            [20000001, 0.10 / 100],
                        ],
                    ),
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'BTC' => 0,
                        'BCH' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'XRP' => 0,
                        'USD' => 25,
                        'EUR' => 0.90,
                    ),
                    'deposit' => array (
                        'BTC' => 0,
                        'BCH' => 0,
                        'LTC' => 0,
                        'ETH' => 0,
                        'XRP' => 0,
                        'USD' => 25,
                        'EUR' => 0,
                    ),
                ),
            ),
            'exceptions' => array (
                'No permission found' => '\\ccxt\\PermissionDenied',
                'API key not found' => '\\ccxt\\AuthenticationError',
                'IP address not allowed' => '\\ccxt\\PermissionDenied',
                'Invalid nonce' => '\\ccxt\\InvalidNonce',
                'Invalid signature' => '\\ccxt\\AuthenticationError',
                'Authentication failed' => '\\ccxt\\AuthenticationError',
                'Missing key, signature and nonce parameters' => '\\ccxt\\AuthenticationError',
                'Your account is frozen' => '\\ccxt\\PermissionDenied',
                'Please update your profile with your FATCA information, before using API.' => '\\ccxt\\PermissionDenied',
                'Order not found' => '\\ccxt\\OrderNotFound',
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetTradingPairsInfo ();
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $symbol = $market['name'];
            list ($base, $quote) = explode ('/', $symbol);
            $baseId = strtolower ($base);
            $quoteId = strtolower ($quote);
            $symbolId = $baseId . '_' . $quoteId;
            $id = $market['url_symbol'];
            $precision = array (
                'amount' => $market['base_decimals'],
                'price' => $market['counter_decimals'],
            );
            $parts = explode (' ', $market['minimum_order']);
            $cost = $parts[0];
            // list ($cost, $currency) = explode (' ', $market['minimum_order']);
            $active = ($market['trading'] === 'Enabled');
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'symbolId' => $symbolId,
                'info' => $market,
                'active' => $active,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow (10, -$precision['amount']),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision['price']),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => floatval ($cost),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetOrderBookPair (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $timestamp = intval ($orderbook['timestamp']) * 1000;
        return $this->parse_order_book($orderbook, $timestamp);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $ticker = $this->publicGetTickerPair (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $timestamp = intval ($ticker['timestamp']) * 1000;
        $vwap = $this->safe_float($ticker, 'vwap');
        $baseVolume = $this->safe_float($ticker, 'volume');
        $quoteVolume = null;
        if ($baseVolume !== null && $vwap !== null)
            $quoteVolume = $baseVolume * $vwap;
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function get_currency_id_from_transaction ($transaction) {
        //
        //     {
        //         "fee" => "0.00000000",
        //         "btc_usd" => "0.00",
        //         "datetime" => XXX,
        //         "usd" => 0.0,
        //         "btc" => 0.0,
        //         "eth" => "0.05000000",
        //         "type" => "0",
        //         "$id" => XXX,
        //         "eur" => 0.0
        //     }
        //
        if (is_array ($transaction) && array_key_exists ('currency', $transaction)) {
            return strtolower ($transaction['currency']);
        }
        $transaction = $this->omit ($transaction, array (
            'fee',
            'price',
            'datetime',
            'type',
            'status',
            'id',
        ));
        $ids = is_array ($transaction) ? array_keys ($transaction) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            if (mb_strpos ($id, '_') < 0) {
                $value = $this->safe_float($transaction, $id);
                if (($value !== null) && ($value !== 0)) {
                    return $id;
                }
            }
        }
        return null;
    }

    public function get_market_from_trade ($trade) {
        $trade = $this->omit ($trade, array (
            'fee',
            'price',
            'datetime',
            'tid',
            'type',
            'order_id',
            'side',
        ));
        $currencyIds = is_array ($trade) ? array_keys ($trade) : array ();
        $numCurrencyIds = is_array ($currencyIds) ? count ($currencyIds) : 0;
        if ($numCurrencyIds > 2) {
            throw new ExchangeError ($this->id . ' getMarketFromTrade too many keys => ' . $this->json ($currencyIds) . ' in the $trade => ' . $this->json ($trade));
        }
        if ($numCurrencyIds === 2) {
            $marketId = $currencyIds[0] . $currencyIds[1];
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                return $this->markets_by_id[$marketId];
            $marketId = $currencyIds[1] . $currencyIds[0];
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                return $this->markets_by_id[$marketId];
        }
        return null;
    }

    public function get_market_from_trades ($trades) {
        $tradesBySymbol = $this->index_by($trades, 'symbol');
        $symbols = is_array ($tradesBySymbol) ? array_keys ($tradesBySymbol) : array ();
        $numSymbols = is_array ($symbols) ? count ($symbols) : 0;
        if ($numSymbols === 1) {
            return $this->markets[$symbols[0]];
        }
        return null;
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = null;
        $symbol = null;
        if (is_array ($trade) && array_key_exists ('date', $trade)) {
            $timestamp = intval ($trade['date']) * 1000;
        } else if (is_array ($trade) && array_key_exists ('datetime', $trade)) {
            $timestamp = $this->parse8601 ($trade['datetime']);
        }
        // only if overrided externally
        $side = $this->safe_string($trade, 'side');
        $orderId = $this->safe_string($trade, 'order_id');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = $this->safe_float($trade, 'cost');
        $id = $this->safe_string_2($trade, 'tid', 'id');
        if ($market === null) {
            $keys = is_array ($trade) ? array_keys ($trade) : array ();
            for ($i = 0; $i < count ($keys); $i++) {
                if (mb_strpos ($keys[$i], '_') !== false) {
                    $marketId = str_replace ('_', '', $keys[$i]);
                    if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                        $market = $this->markets_by_id[$marketId];
                }
            }
            // if the $market is still not defined
            // try to deduce it from used $keys
            if ($market === null) {
                $market = $this->get_market_from_trade ($trade);
            }
        }
        $feeCost = $this->safe_float($trade, 'fee');
        $feeCurrency = null;
        if ($market !== null) {
            $price = $this->safe_float($trade, $market['symbolId'], $price);
            $amount = $this->safe_float($trade, $market['baseId'], $amount);
            $cost = $this->safe_float($trade, $market['quoteId'], $cost);
            $feeCurrency = $market['quote'];
            $symbol = $market['symbol'];
        }
        if ($amount !== null) {
            if ($amount < 0) {
                $side = 'sell';
            } else {
                $side = 'buy';
            }
            $amount = abs ($amount);
        }
        if ($cost === null) {
            if ($price !== null) {
                if ($amount !== null) {
                    $cost = $price * $amount;
                }
            }
        }
        if ($cost !== null) {
            $cost = abs ($cost);
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            ),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetTransactionsPair (array_merge (array (
            'pair' => $market['id'],
            'time' => 'hour',
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balance = $this->privatePostBalance ();
        $result = array ( 'info' => $balance );
        $currencies = is_array ($this->currencies) ? array_keys ($this->currencies) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $lowercase = strtolower ($currency);
            $total = $lowercase . '_balance';
            $free = $lowercase . '_available';
            $used = $lowercase . '_reserved';
            $account = $this->account ();
            if (is_array ($balance) && array_key_exists ($free, $balance))
                $account['free'] = floatval ($balance[$free]);
            if (is_array ($balance) && array_key_exists ($used, $balance))
                $account['used'] = floatval ($balance[$used]);
            if (is_array ($balance) && array_key_exists ($total, $balance))
                $account['total'] = floatval ($balance[$total]);
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'privatePost' . $this->capitalize ($side);
        $request = array (
            'pair' => $market['id'],
            'amount' => $this->amount_to_precision($symbol, $amount),
        );
        if ($type === 'market') {
            $method .= 'Market';
        } else {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $method .= 'Pair';
        $response = $this->$method (array_merge ($request, $params));
        $order = $this->parse_order($response, $market);
        return array_merge ($order, array (
            'type' => $type,
        ));
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privatePostCancelOrder (array ( 'id' => $id ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'In Queue' => 'open',
            'Open' => 'open',
            'Finished' => 'closed',
            'Canceled' => 'canceled',
        );
        return (is_array ($statuses) && array_key_exists ($status, $statuses)) ? $statuses[$status] : $status;
    }

    public function fetch_order_status ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostOrderStatus (array_merge (array ( 'id' => $id ), $params));
        return $this->parse_order_status($this->safe_string($response, 'status'));
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $response = $this->privatePostOrderStatus (array_merge (array ( 'id' => $id ), $params));
        return $this->parse_order($response, $market);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        $method = 'privatePostUserTransactions';
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
            $method .= 'Pair';
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->$method (array_merge ($request, $params));
        $result = $this->filter_by($response, 'type', '2');
        return $this->parse_trades($result, $market, $since, $limit);
    }

    public function fetch_transactions ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privatePostUserTransactions (array_merge ($request, $params));
        //
        //     array (
        //         array (
        //             "fee" => "0.00000000",
        //             "btc_usd" => "0.00",
        //             "id" => 1234567894,
        //             "usd" => 0,
        //             "btc" => 0,
        //             "datetime" => "2018-09-08 09:00:31",
        //             "type" => "1",
        //             "xrp" => "-20.00000000",
        //             "eur" => 0,
        //         ),
        //         array (
        //             "fee" => "0.00000000",
        //             "btc_usd" => "0.00",
        //             "id" => 1134567891,
        //             "usd" => 0,
        //             "btc" => 0,
        //             "datetime" => "2018-09-07 18:47:52",
        //             "type" => "0",
        //             "xrp" => "20.00000000",
        //             "eur" => 0,
        //         ),
        //     )
        //
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
        }
        $transactions = $this->filter_by_array($response, 'type', array ( '0', '1' ), false);
        return $this->parseTransactions ($transactions, $currency, $since, $limit);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($since !== null) {
            $request['timedelta'] = $this->milliseconds () - $since;
        }
        $response = $this->privatePostWithdrawalRequests (array_merge ($request, $params));
        //
        //     array (
        //         array (
        //             status => 2,
        //             datetime => '2018-10-17 10:58:13',
        //             currency => 'BTC',
        //             amount => '0.29669259',
        //             address => 'aaaaa',
        //             type => 1,
        //             id => 111111,
        //             transaction_id => 'xxxx',
        //         ),
        //         array (
        //             status => 2,
        //             datetime => '2018-10-17 10:55:17',
        //             currency => 'ETH',
        //             amount => '1.11010664',
        //             address => 'aaaa',
        //             type => 16,
        //             id => 222222,
        //             transaction_id => 'xxxxx',
        //         ),
        //     )
        //
        return $this->parseTransactions ($response, null, $since, $limit);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // fetchTransactions
        //
        //     {
        //         "fee" => "0.00000000",
        //         "btc_usd" => "0.00",
        //         "$id" => 1234567894,
        //         "usd" => 0,
        //         "btc" => 0,
        //         "datetime" => "2018-09-08 09:00:31",
        //         "$type" => "1",
        //         "xrp" => "-20.00000000",
        //         "eur" => 0,
        //     }
        //
        // fetchWithdrawals
        //
        //     {
        //         $status => 2,
        //         datetime => '2018-10-17 10:58:13',
        //         $currency => 'BTC',
        //         $amount => '0.29669259',
        //         $address => 'aaaaa',
        //         $type => 1,
        //         $id => 111111,
        //         transaction_id => 'xxxx',
        //     }
        //
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'datetime'));
        $code = null;
        $id = $this->safe_string($transaction, 'id');
        $currencyId = $this->get_currency_id_from_transaction ($transaction);
        if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
            $currency = $this->currencies_by_id[$currencyId];
        } else if ($currencyId !== null) {
            $code = strtoupper ($currencyId);
            $code = $this->common_currency_code($code);
        }
        $feeCost = $this->safe_float($transaction, 'fee');
        $feeCurrency = null;
        $amount = null;
        if ($currency !== null) {
            $amount = $this->safe_float($transaction, $currency['id'], $amount);
            $feeCurrency = $currency['code'];
            $code = $currency['code'];
        } else if (($code !== null) && ($currencyId !== null)) {
            $amount = $this->safe_float($transaction, $currencyId, $amount);
            $feeCurrency = $code;
        }
        if ($amount !== null) {
            // withdrawals have a negative $amount
            $amount = abs ($amount);
        }
        $status = $this->parse_transaction_status_by_type ($this->safe_string($transaction, 'status'));
        $type = $this->safe_string($transaction, 'type');
        if ($status === null) {
            if ($type === '0') {
                $type = 'deposit';
            } else if ($type === '1') {
                $type = 'withdrawal';
            }
        } else {
            $type = 'withdrawal';
        }
        $txid = $this->safe_string($transaction, 'transaction_id');
        $address = $this->safe_string($transaction, 'address');
        $tag = null; // not documented
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'address' => $address,
            'tag' => $tag,
            'type' => $type,
            'amount' => $amount,
            'currency' => $code,
            'status' => $status,
            'updated' => null,
            'fee' => array (
                'currency' => $feeCurrency,
                'cost' => $feeCost,
                'rate' => null,
            ),
        );
    }

    public function parse_transaction_status_by_type ($status) {
        // withdrawals:
        // 0 (open), 1 (in process), 2 (finished), 3 (canceled) or 4 (failed).
        $statuses = array (
            '0' => 'pending', // Open
            '1' => 'pending', // In process
            '2' => 'ok', // Finished
            '3' => 'canceled', // Canceled
            '4' => 'failed', // Failed
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        //
        //     {
        //         $price => '0.00008012',
        //         currency_pair => 'XRP/BTC',
        //         datetime => '2019-01-31 21:23:36',
        //         $amount => '15.00000000',
        //         type => '0',
        //         $id => '2814205012'
        //     }
        //
        $id = $this->safe_string($order, 'id');
        $side = $this->safe_string($order, 'type');
        if ($side !== null) {
            $side = ($side === '1') ? 'sell' : 'buy';
        }
        $timestamp = $this->parse8601 ($this->safe_string($order, 'datetime'));
        $symbol = null;
        $marketId = $this->safe_string($order, 'currency_pair');
        $marketId = str_replace ('/', '', $marketId);
        $marketId = strtolower ($marketId);
        if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
        }
        $amount = $this->safe_float($order, 'amount');
        $filled = 0.0;
        $trades = array ();
        $transactions = $this->safe_value($order, 'transactions');
        $feeCost = null;
        $cost = null;
        if ($transactions !== null) {
            if (gettype ($transactions) === 'array' && count (array_filter (array_keys ($transactions), 'is_string')) == 0) {
                $feeCost = 0.0;
                for ($i = 0; $i < count ($transactions); $i++) {
                    $trade = $this->parse_trade(array_merge (array (
                        'order_id' => $id,
                        'side' => $side,
                    ), $transactions[$i]), $market);
                    $filled .= $trade['amount'];
                    $feeCost .= $trade['fee']['cost'];
                    if ($cost === null)
                        $cost = 0.0;
                    $cost .= $trade['cost'];
                    $trades[] = $trade;
                }
            }
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        if (($status === 'closed') && ($amount === null)) {
            $amount = $filled;
        }
        $remaining = null;
        if ($amount !== null) {
            $remaining = $amount - $filled;
        }
        $price = $this->safe_float($order, 'price');
        if ($market === null) {
            $market = $this->get_market_from_trades ($trades);
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        }
        if ($cost === null) {
            if ($price !== null) {
                $cost = $price * $filled;
            }
        } else if ($price === null) {
            if ($filled > 0) {
                $price = $cost / $filled;
            }
        }
        $fee = null;
        if ($feeCost !== null) {
            if ($feeCurrency !== null) {
                $fee = array (
                    'cost' => $feeCost,
                    'currency' => $feeCurrency,
                );
            }
        }
        return array (
            'id' => $id,
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => $trades,
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $market = null;
        $this->load_markets();
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $response = $this->privatePostOpenOrdersAll ($params);
        //     array (
        //         {
        //             price => '0.00008012',
        //             currency_pair => 'XRP/BTC',
        //             datetime => '2019-01-31 21:23:36',
        //             amount => '15.00000000',
        //             type => '0',
        //             id => '2814205012',
        //         }
        //     )
        //
        $result = array ();
        for ($i = 0; $i < count ($response); $i++) {
            $order = $this->parse_order($response[$i], $market);
            $result[] = array_merge ($order, array (
                'status' => 'open',
                'type' => 'limit',
            ));
        }
        if ($symbol === null) {
            return $this->filter_by_since_limit($result, $since, $limit);
        }
        return $this->filter_by_symbol_since_limit($result, $symbol, $since, $limit);
    }

    public function get_currency_name ($code) {
        if ($code === 'BTC')
            return 'bitcoin';
        return strtolower ($code);
    }

    public function is_fiat ($code) {
        if ($code === 'USD')
            return true;
        if ($code === 'EUR')
            return true;
        return false;
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        if ($this->is_fiat ($code))
            throw new NotSupported ($this->id . ' fiat fetchDepositAddress() for ' . $code . ' is not implemented yet');
        $name = $this->get_currency_name ($code);
        $v1 = ($code === 'BTC');
        $method = $v1 ? 'v1' : 'private'; // $v1 or v2
        $method .= 'Post' . $this->capitalize ($name);
        $method .= $v1 ? 'Deposit' : '';
        $method .= 'Address';
        $response = $this->$method ($params);
        $address = $v1 ? $response : $this->safe_string($response, 'address');
        $tag = $v1 ? null : $this->safe_string($response, 'destination_tag');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        if ($this->is_fiat ($code))
            throw new NotSupported ($this->id . ' fiat withdraw() for ' . $code . ' is not implemented yet');
        $name = $this->get_currency_name ($code);
        $request = array (
            'amount' => $amount,
            'address' => $address,
        );
        $v1 = ($code === 'BTC');
        $method = $v1 ? 'v1' : 'private'; // $v1 or v2
        $method .= 'Post' . $this->capitalize ($name) . 'Withdrawal';
        $query = $params;
        if ($code === 'XRP') {
            if ($tag !== null) {
                $request['destination_tag'] = $tag;
                $query = $this->omit ($params, 'destination_tag');
            } else {
                throw new ExchangeError ($this->id . ' withdraw() requires a destination_tag param for ' . $code);
            }
        }
        $response = $this->$method (array_merge ($request, $query));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/';
        if ($api !== 'v1')
            $url .= $this->version . '/';
        $url .= $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = $nonce . $this->uid . $this->apiKey;
            $signature = $this->encode ($this->hmac ($this->encode ($auth), $this->encode ($this->secret)));
            $query = array_merge (array (
                'key' => $this->apiKey,
                'signature' => strtoupper ($signature),
                'nonce' => $nonce,
            ), $query);
            $body = $this->urlencode ($query);
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if (gettype ($body) !== 'string')
            return; // fallback to default $error handler
        if (strlen ($body) < 2)
            return; // fallback to default $error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            // fetchDepositAddress returns array ("$error" => "No permission found") on apiKeys that don't have the permission required
            $error = $this->safe_string($response, 'error');
            $exceptions = $this->exceptions;
            if (is_array ($exceptions) && array_key_exists ($error, $exceptions)) {
                throw new $exceptions[$error] ($this->id . ' ' . $body);
            }
            $status = $this->safe_string($response, 'status');
            if ($status === 'error') {
                $code = $this->safe_string($response, 'code');
                if ($code !== null) {
                    if ($code === 'API0005')
                        throw new AuthenticationError ($this->id . ' invalid signature, use the uid for the main account if you have subaccounts');
                }
                throw new ExchangeError ($this->id . ' ' . $body);
            }
        }
    }
}
