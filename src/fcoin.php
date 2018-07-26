<?php

namespace ccxt;

use Exception as Exception; // a common import

class fcoin extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'fcoin',
            'name' => 'FCoin',
            'countries' => 'CN',
            'rateLimit' => 2000,
            'userAgent' => $this->userAgents['chrome39'],
            'version' => 'v2',
            'accounts' => null,
            'accountsById' => null,
            'hostname' => 'api.fcoin.com',
            'has' => array (
                'CORS' => false,
                'fetchDepositAddress' => false,
                'fetchOHCLV' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOrderBook' => true,
                'fetchOrderBooks' => false,
                'fetchTradingLimits' => false,
                'withdraw' => false,
                'fetchCurrencies' => false,
            ),
            'timeframes' => array (
                '1m' => 'M1',
                '3m' => 'M3',
                '5m' => 'M5',
                '15m' => 'M15',
                '30m' => 'M30',
                '1h' => 'H1',
                '1d' => 'D1',
                '1w' => 'W1',
                '1M' => 'MN',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/42244210-c8c42e1e-7f1c-11e8-8710-a5fb63b165c4.jpg',
                'api' => 'https://api.fcoin.com',
                'www' => 'https://www.fcoin.com',
                'referral' => 'https://www.fcoin.com/i/Z5P7V',
                'doc' => 'https://developer.fcoin.com',
                'fees' => 'https://support.fcoin.com/hc/en-us/articles/360003715514-Trading-Rules',
            ),
            'api' => array (
                'market' => array (
                    'get' => array (
                        'ticker/{symbol}',
                        'depth/{level}/{symbol}',
                        'trades/{symbol}',
                        'candles/{timeframe}/{symbol}',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'symbols',
                        'currencies',
                        'server-time',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts/balance',
                        'orders',
                        'orders/{order_id}',
                        'orders/{order_id}/match-results', // check order result
                    ),
                    'post' => array (
                        'orders',
                        'orders/{order_id}/submit-cancel', // cancel order
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.001,
                    'taker' => 0.001,
                ),
            ),
            'limits' => array (
                'amount' => array ( 'min' => 0.01, 'max' => 100000 ),
            ),
            'options' => array (
                'limits' => array (
                    'BTM/USDT' => array ( 'amount' => array ( 'min' => 0.1, 'max' => 10000000 )),
                    'ETC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 400000 )),
                    'ETH/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 10000 )),
                    'LTC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 40000 )),
                    'BCH/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 5000 )),
                    'BTC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 1000 )),
                    'ICX/ETH' => array ( 'amount' => array ( 'min' => 0.01, 'max' => 3000000 )),
                    'OMG/ETH' => array ( 'amount' => array ( 'min' => 0.01, 'max' => 500000 )),
                    'FT/USDT' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'ZIL/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'ZIP/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'FT/BTC' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'FT/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                ),
            ),
            'exceptions' => array (
                '400' => '\\ccxt\\NotSupported', // Bad Request
                '401' => '\\ccxt\\AuthenticationError',
                '405' => '\\ccxt\\NotSupported',
                '429' => '\\ccxt\\DDoSProtection', // Too Many Requests, exceed api request limit
                '1002' => '\\ccxt\\ExchangeNotAvailable', // System busy
                '1016' => '\\ccxt\\InsufficientFunds',
                '3008' => '\\ccxt\\InvalidOrder',
                '6004' => '\\ccxt\\InvalidNonce',
                '6005' => '\\ccxt\\AuthenticationError', // Illegal API Signature
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetSymbols ();
        $result = array ();
        $markets = $response['data'];
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['name'];
            $baseId = $market['base_currency'];
            $quoteId = $market['quote_currency'];
            $base = strtoupper ($baseId);
            $base = $this->common_currency_code($base);
            $quote = strtoupper ($quoteId);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'price' => $market['price_decimal'],
                'amount' => $market['amount_decimal'],
            );
            $limits = array (
                'price' => array (
                    'min' => pow (10, -$precision['price']),
                    'max' => pow (10, $precision['price']),
                ),
            );
            if (is_array ($this->options['limits']) && array_key_exists ($symbol, $this->options['limits'])) {
                $limits = array_merge ($this->options['limits'][$symbol], $limits);
            }
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetAccountsBalance ($params);
        $result = array ( 'info' => $response );
        $balances = $response['data'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $balance['currency'];
            $code = strtoupper ($currencyId);
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            } else {
                $code = $this->common_currency_code($code);
            }
            $account = $this->account ();
            $account['free'] = floatval ($balance['available']);
            $account['total'] = floatval ($balance['balance']);
            $account['used'] = floatval ($balance['frozen']);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_bids_asks ($orders, $priceKey = 0, $amountKey = 1) {
        $result = array ();
        $length = is_array ($orders) ? count ($orders) : 0;
        $halfLength = intval ($length / 2);
        // .= 2 in the for loop below won't transpile
        for ($i = 0; $i < $halfLength; $i++) {
            $index = $i * 2;
            $priceField = $this->sum ($index, $priceKey);
            $amountField = $this->sum ($index, $amountKey);
            $result[] = [
                $orders[$priceField],
                $orders[$amountField],
            ];
        }
        return $result;
    }

    public function fetch_order_book ($symbol = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($limit !== null) {
            if (($limit === 20) || ($limit === 100)) {
                $limit = 'L' . (string) $limit;
            } else {
                throw new ExchangeError ($this->id . ' fetchOrderBook supports $limit of 20, 100 or no $limit-> Other values are not accepted');
            }
        } else {
            $limit = 'full';
        }
        $request = array_merge (array (
            'symbol' => $this->market_id($symbol),
            'level' => $limit, // L20, L100, full
        ), $params);
        $response = $this->marketGetDepthLevelSymbol ($request);
        $orderbook = $response['data'];
        return $this->parse_order_book($orderbook, $orderbook['ts'], 'bids', 'asks', 0, 1);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->marketGetTickerSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker['data'], $market);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = null;
        $symbol = null;
        if ($market === null) {
            $tickerType = $this->safe_string($ticker, 'type');
            if ($tickerType !== null) {
                $parts = explode ('.', $tickerType);
                $id = $parts[1];
                if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                    $market = $this->markets_by_id[$id];
                }
            }
        }
        $values = $ticker['ticker'];
        $last = $values[0];
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $values[7],
            'low' => $values[8],
            'bid' => $values[2],
            'bidVolume' => $values[3],
            'ask' => $values[4],
            'askVolume' => $values[5],
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $values[9],
            'quoteVolume' => $values[10],
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = intval ($trade['ts']);
        $side = strtolower ($trade['side']);
        $orderId = $this->safe_string($trade, 'id');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = $price * $amount;
        $fee = null;
        return array (
            'id' => $orderId,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'order' => $orderId,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = 50, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'limit' => $limit,
        );
        if ($since !== null) {
            $request['timestamp'] = intval ($since / 1000);
        }
        $response = $this->marketGetTradesSymbol (array_merge ($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $orderType = $type;
        $amount = $this->amount_to_precision($symbol, $amount);
        $order = array (
            'symbol' => $this->market_id($symbol),
            'amount' => $amount,
            'side' => $side,
            'type' => $orderType,
        );
        if ($type === 'limit') {
            $order['price'] = $this->price_to_precision($symbol, $price);
        }
        $result = $this->privatePostOrders (array_merge ($order, $params));
        return array (
            'info' => $result,
            'id' => $result['data'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostOrdersOrderIdSubmitCancel (array_merge (array (
            'order_id' => $id,
        ), $params));
        $order = $this->parse_order($response);
        return array_merge ($order, array (
            'id' => $id,
            'status' => 'canceled',
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'submitted' => 'open',
            'canceled' => 'canceled',
            'partial_filled' => 'open',
            'partial_canceled' => 'canceled',
            'filled' => 'closed',
            'pending_cancel' => 'canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $id = $this->safe_string($order, 'id');
        $side = $this->safe_string($order, 'side');
        $status = $this->parse_order_status($this->safe_string($order, 'state'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'symbol');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        $orderType = $this->safe_string($order, 'type');
        $timestamp = $this->safe_integer($order, 'created_at');
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'filled_amount');
        $remaining = null;
        $price = $this->safe_float($order, 'price');
        $cost = $this->safe_float($order, 'executed_value');
        if ($filled !== null) {
            if ($amount !== null) {
                $remaining = $amount - $filled;
            }
            if ($cost === null) {
                if ($price !== null) {
                    $cost = $price * $filled;
                }
            } else if (($cost > 0) && ($filled > 0)) {
                $price = $cost / $filled;
            }
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = ($side === 'buy') ? $market['base'] : $market['quote'];
        }
        $feeCost = $this->safe_float($order, 'fill_fees');
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $orderType,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'average' => null,
            'status' => $status,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            ),
            'trades' => null,
        );
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array_merge (array (
            'order_id' => $id,
        ), $params);
        $response = $this->privateGetOrdersOrderId ($request);
        return $this->parse_order($response['data']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array ( 'states' => 'submitted' ));
        return $result;
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array ( 'states' => 'filled' ));
        return $result;
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'states' => 'submitted',
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response['data'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv['id'] * 1000,
            $ohlcv['open'],
            $ohlcv['high'],
            $ohlcv['low'],
            $ohlcv['close'],
            $ohlcv['base_vol'],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 100, $params = array ()) {
        $this->load_markets();
        if ($limit === null) {
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $limit argument');
        }
        $market = $this->market ($symbol);
        $request = array_merge (array (
            'symbol' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
            'limit' => $limit,
        ), $params);
        $response = $this->marketGetCandlesTimeframeSymbol ($request);
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->version . '/';
        $request .= ($api === 'private') ? '' : ($api . '/');
        $request .= $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . $request;
        if (($api === 'public') || ($api === 'market')) {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else if ($api === 'private') {
            $this->check_required_credentials();
            $timestamp = (string) $this->nonce ();
            $query = $this->keysort ($query);
            if ($method === 'GET') {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
            }
            // HTTP_METHOD . HTTP_REQUEST_URI . TIMESTAMP . POST_BODY
            $auth = $method . $url . $timestamp;
            if ($method === 'POST') {
                if ($query) {
                    $body = $this->json ($query);
                    $auth .= $this->urlencode ($query);
                }
            }
            $payload = base64_encode ($this->encode ($auth));
            $signature = $this->hmac ($payload, $this->encode ($this->secret), 'sha1', 'binary');
            $signature = $this->decode (base64_encode ($signature));
            $headers = array (
                'FC-ACCESS-KEY' => $this->apiKey,
                'FC-ACCESS-SIGNATURE' => $signature,
                'FC-ACCESS-TIMESTAMP' => $timestamp,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            $status = $this->safe_string($response, 'status');
            if ($status !== '0') {
                $feedback = $this->id . ' ' . $body;
                if (is_array ($this->exceptions) && array_key_exists ($status, $this->exceptions)) {
                    $exceptions = $this->exceptions;
                    throw new $exceptions[$status] ($feedback);
                }
                throw new ExchangeError ($feedback);
            }
        }
    }
}
