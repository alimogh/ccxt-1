<?php

namespace ccxt;

use Exception as Exception; // a common import

class zb extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'zb',
            'name' => 'ZB',
            'countries' => 'CN',
            'rateLimit' => 1000,
            'version' => 'v1',
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1min',
                '3m' => '3min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '2h' => '2hour',
                '4h' => '4hour',
                '6h' => '6hour',
                '12h' => '12hour',
                '1d' => '1day',
                '3d' => '3day',
                '1w' => '1week',
            ),
            'exceptions' => array (
                // '1000' => 'Successful operation',
                '1001' => '\\ccxt\\ExchangeError', // 'General error message',
                '1002' => '\\ccxt\\ExchangeError', // 'Internal error',
                '1003' => '\\ccxt\\AuthenticationError', // 'Verification does not pass',
                '1004' => '\\ccxt\\AuthenticationError', // 'Funding security password lock',
                '1005' => '\\ccxt\\AuthenticationError', // 'Funds security password is incorrect, please confirm and re-enter.',
                '1006' => '\\ccxt\\AuthenticationError', // 'Real-name certification pending approval or audit does not pass',
                '1009' => '\\ccxt\\ExchangeNotAvailable', // 'This interface is under maintenance',
                '2001' => '\\ccxt\\InsufficientFunds', // 'Insufficient CNY Balance',
                '2002' => '\\ccxt\\InsufficientFunds', // 'Insufficient BTC Balance',
                '2003' => '\\ccxt\\InsufficientFunds', // 'Insufficient LTC Balance',
                '2005' => '\\ccxt\\InsufficientFunds', // 'Insufficient ETH Balance',
                '2006' => '\\ccxt\\InsufficientFunds', // 'Insufficient ETC Balance',
                '2007' => '\\ccxt\\InsufficientFunds', // 'Insufficient BTS Balance',
                '2009' => '\\ccxt\\InsufficientFunds', // 'Account balance is not enough',
                '3001' => '\\ccxt\\OrderNotFound', // 'Pending orders not found',
                '3002' => '\\ccxt\\InvalidOrder', // 'Invalid price',
                '3003' => '\\ccxt\\InvalidOrder', // 'Invalid amount',
                '3004' => '\\ccxt\\AuthenticationError', // 'User does not exist',
                '3005' => '\\ccxt\\ExchangeError', // 'Invalid parameter',
                '3006' => '\\ccxt\\AuthenticationError', // 'Invalid IP or inconsistent with the bound IP',
                '3007' => '\\ccxt\\AuthenticationError', // 'The request time has expired',
                '3008' => '\\ccxt\\OrderNotFound', // 'Transaction records not found',
                '4001' => '\\ccxt\\ExchangeNotAvailable', // 'API interface is locked or not enabled',
                '4002' => '\\ccxt\\DDoSProtection', // 'Request too often',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/32859187-cd5214f0-ca5e-11e7-967d-96568e2e2bd1.jpg',
                'api' => array (
                    'public' => 'http://api.zb.com/data', // no https for public API
                    'private' => 'https://trade.zb.com/api',
                ),
                'www' => 'https://www.zb.com',
                'doc' => 'https://www.zb.com/i/developer',
                'fees' => 'https://www.zb.com/i/rate',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets',
                        'ticker',
                        'depth',
                        'trades',
                        'kline',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        // spot API
                        'order',
                        'cancelOrder',
                        'getOrder',
                        'getOrders',
                        'getOrdersNew',
                        'getOrdersIgnoreTradeType',
                        'getUnfinishedOrdersIgnoreTradeType',
                        'getAccountInfo',
                        'getUserAddress',
                        'getWithdrawAddress',
                        'getWithdrawRecord',
                        'getChargeRecord',
                        'getCnyWithdrawRecord',
                        'getCnyChargeRecord',
                        'withdraw',
                        // leverage API
                        'getLeverAssetsInfo',
                        'getLeverBills',
                        'transferInLever',
                        'transferOutLever',
                        'loan',
                        'cancelLoan',
                        'getLoans',
                        'getLoanRecords',
                        'borrow',
                        'repay',
                        'getRepayments',
                    ),
                ),
            ),
            'fees' => array (
                'funding' => array (
                    'withdraw' => array (
                        'BTC' => 0.0001,
                        'BCH' => 0.0006,
                        'LTC' => 0.005,
                        'ETH' => 0.01,
                        'ETC' => 0.01,
                        'BTS' => 3,
                        'EOS' => 1,
                        'QTUM' => 0.01,
                        'HSR' => 0.001,
                        'XRP' => 0.1,
                        'USDT' => '0.1%',
                        'QCASH' => 5,
                        'DASH' => 0.002,
                        'BCD' => 0,
                        'UBTC' => 0,
                        'SBTC' => 0,
                        'INK' => 20,
                        'TV' => 0.1,
                        'BTH' => 0,
                        'BCX' => 0,
                        'LBTC' => 0,
                        'CHAT' => 20,
                        'bitCNY' => 20,
                        'HLC' => 20,
                        'BTP' => 0,
                        'BCW' => 0,
                    ),
                ),
                'trading' => array (
                    'maker' => 0.2 / 100,
                    'taker' => 0.2 / 100,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetMarkets ();
        $keys = is_array ($markets) ? array_keys ($markets) : array ();
        $result = array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $id = $keys[$i];
            $market = $markets[$id];
            list ($baseId, $quoteId) = explode ('_', $id);
            $base = $this->common_currency_code(strtoupper ($baseId));
            $quote = $this->common_currency_code(strtoupper ($quoteId));
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $market['amountScale'],
                'price' => $market['priceScale'],
            );
            $lot = pow (10, -$precision['amount']);
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'base' => $base,
                'quote' => $quote,
                'lot' => $lot,
                'active' => true,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $lot,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision['price']),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => 0,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetGetAccountInfo ($params);
        // todo => use this somehow
        // $permissions = $response['result']['base'];
        $balances = $response['result']['coins'];
        $result = array ( 'info' => $response );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            //     {        enName => "BTC",
            //               freez => "0.00000000",
            //         unitDecimal =>  8, // always 8
            //              cnName => "BTC",
            //       isCanRecharge =>  true, // TODO => should use this
            //             unitTag => "฿",
            //       isCanWithdraw =>  true,  // TODO => should use this
            //           available => "0.00000000",
            //                 key => "btc"         }
            $account = $this->account ();
            $currency = $balance['key'];
            if (is_array ($this->currencies_by_id) && array_key_exists ($currency, $this->currencies_by_id))
                $currency = $this->currencies_by_id[$currency]['code'];
            else
                $currency = $this->common_currency_code($balance['enName']);
            $account['free'] = floatval ($balance['available']);
            $account['used'] = floatval ($balance['freez']);
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function get_market_field_name () {
        return 'market';
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $marketFieldName = $this->get_market_field_name ();
        $request = array ();
        $request[$marketFieldName] = $market['id'];
        $orderbook = $this->publicGetDepth (array_merge ($request, $params));
        return $this->parse_order_book($orderbook);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $marketFieldName = $this->get_market_field_name ();
        $request = array ();
        $request[$marketFieldName] = $market['id'];
        $response = $this->publicGetTicker (array_merge ($request, $params));
        $ticker = $response['ticker'];
        $timestamp = $this->milliseconds ();
        $last = floatval ($ticker['last']);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['high']),
            'low' => floatval ($ticker['low']),
            'bid' => floatval ($ticker['buy']),
            'bidVolume' => null,
            'ask' => floatval ($ticker['sell']),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => floatval ($ticker['vol']),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null)
            $limit = 1000;
        $request = array (
            'market' => $market['id'],
            'type' => $this->timeframes[$timeframe],
            'limit' => $limit,
        );
        if ($since !== null)
            $request['since'] = $since;
        $response = $this->publicGetKline (array_merge ($request, $params));
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $trade['date'] * 1000;
        $side = ($trade['trade_type'] === 'bid') ? 'buy' : 'sell';
        return array (
            'info' => $trade,
            'id' => (string) $trade['tid'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $side,
            'price' => floatval ($trade['price']),
            'amount' => floatval ($trade['amount']),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $marketFieldName = $this->get_market_field_name ();
        $request = array ();
        $request[$marketFieldName] = $market['id'];
        $response = $this->publicGetTrades (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type !== 'limit')
            throw new InvalidOrder ($this->id . ' allows limit orders only');
        $this->load_markets();
        $order = array (
            'price' => $this->price_to_precision($symbol, $price),
            'amount' => $this->amount_to_string($symbol, $amount),
            'tradeType' => ($side === 'buy') ? '1' : '0',
            'currency' => $this->market_id($symbol),
        );
        $response = $this->privateGetOrder (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'id' => (string) $id,
            'currency' => $this->market_id($symbol),
        );
        $order = array_merge ($order, $params);
        return $this->privateGetCancelOrder ($order);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null)
            throw new ExchangeError ($this->id . ' fetchOrder() requires a $symbol argument');
        $this->load_markets();
        $order = array (
            'id' => (string) $id,
            'currency' => $this->market_id($symbol),
        );
        $order = array_merge ($order, $params);
        $response = $this->privateGetGetOrder ($order);
        return $this->parse_order($response, null, true);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = 50, $params = array ()) {
        if (!$symbol)
            throw new ExchangeError ($this->id . 'fetchOrders requires a $symbol parameter');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currency' => $market['id'],
            'pageIndex' => 1, // default pageIndex is 1
            'pageSize' => $limit, // default pageSize is 50
        );
        $method = 'privateGetGetOrdersIgnoreTradeType';
        // tradeType 交易类型1/0[buy/sell]
        if (is_array ($params) && array_key_exists ('tradeType', $params))
            $method = 'privateGetGetOrdersNew';
        $response = null;
        try {
            $response = $this->$method (array_merge ($request, $params));
        } catch (Exception $e) {
            if ($e instanceof OrderNotFound) {
                return array ();
            }
            throw $e;
        }
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = 10, $params = array ()) {
        if (!$symbol)
            throw new ExchangeError ($this->id . 'fetchOpenOrders requires a $symbol parameter');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currency' => $market['id'],
            'pageIndex' => 1, // default pageIndex is 1
            'pageSize' => $limit, // default pageSize is 10
        );
        $method = 'privateGetGetUnfinishedOrdersIgnoreTradeType';
        // tradeType 交易类型1/0[buy/sell]
        if (is_array ($params) && array_key_exists ('tradeType', $params))
            $method = 'privateGetGetOrdersNew';
        $response = null;
        try {
            $response = $this->$method (array_merge ($request, $params));
        } catch (Exception $e) {
            if ($e instanceof OrderNotFound) {
                return array ();
            }
            throw $e;
        }
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        $side = $order['type'] === 1 ? 'buy' : 'sell';
        $type = 'limit'; // $market $order is not availalbe in ZB
        $timestamp = null;
        $createDateField = $this->get_create_date_field ();
        if (is_array ($order) && array_key_exists ($createDateField, $order))
            $timestamp = $order[$createDateField];
        $symbol = null;
        if (is_array ($order) && array_key_exists ('currency', $order)) {
            // get $symbol from currency
            $market = $this->marketsById[$order['currency']];
        }
        if ($market)
            $symbol = $market['symbol'];
        $price = $order['price'];
        $average = $order['trade_price'];
        $filled = $order['trade_amount'];
        $amount = $order['total_amount'];
        $remaining = $amount - $filled;
        $cost = $order['trade_money'];
        $status = $this->safe_string($order, 'status');
        if ($status !== null)
            $status = $this->parse_order_status($status);
        $result = array (
            'info' => $order,
            'id' => $order['id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'average' => $average,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open',
            '1' => 'canceled',
            '2' => 'closed',
            '3' => 'open', // partial
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function get_create_date_field () {
        return 'trade_date';
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api];
        if ($api === 'public') {
            $url .= '/' . $this->version . '/' . $path;
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        } else {
            $query = $this->keysort (array_merge (array (
                'method' => $path,
                'accesskey' => $this->apiKey,
            ), $params));
            $nonce = $this->nonce ();
            $query = $this->keysort ($query);
            $auth = $this->rawencode ($query);
            $secret = $this->hash ($this->encode ($this->secret), 'sha1');
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($secret), 'md5');
            $suffix = 'sign=' . $signature . '&reqTime=' . (string) $nonce;
            $url .= '/' . $path . '?' . $auth . '&' . $suffix;
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) != 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('code', $response)) {
                $code = $this->safe_string($response, 'code');
                $message = $this->id . ' ' . $this->json ($response);
                if (is_array ($this->exceptions) && array_key_exists ($code, $this->exceptions)) {
                    $ExceptionClass = $this->exceptions[$code];
                    throw new $ExceptionClass ($message);
                } else if ($code !== '1000') {
                    throw new ExchangeError ($message);
                }
            }
        }
    }
}
