<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinegg extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinegg',
            'name' => 'CoinEgg',
            'countries' => array ( 'CN', 'UK' ),
            'has' => array (
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => 'emulated',
                'fetchMyTrades' => true,
                'fetchTickers' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/36770310-adfa764e-1c5a-11e8-8e09-449daac3d2fb.jpg',
                'api' => array (
                    'web' => 'https://www.coinegg.com/coin',
                    'rest' => 'https://api.coinegg.com/api/v1',
                ),
                'www' => 'https://www.coinegg.com',
                'doc' => 'https://www.coinegg.com/explain.api.html',
                'fees' => 'https://www.coinegg.com/fee.html',
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        '{quote}/allcoin',
                        '{quote}/trends',
                        '{quote}/{base}/order',
                        '{quote}/{base}/trades',
                        '{quote}/{base}/depth.js',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'ticker/{quote}',
                        'depth/{quote}',
                        'orders/{quote}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balance',
                        'trade_add/{quote}',
                        'trade_cancel/{quote}',
                        'trade_view/{quote}',
                        'trade_list/{quote}',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.1 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BTC' => 0.008,
                        'BCH' => 0.002,
                        'LTC' => 0.001,
                        'ETH' => 0.01,
                        'ETC' => 0.01,
                        'NEO' => 0,
                        'QTUM' => '1%',
                        'XRP' => '1%',
                        'DOGE' => '1%',
                        'LSK' => '1%',
                        'XAS' => '1%',
                        'BTS' => '1%',
                        'GAME' => '1%',
                        'GOOC' => '1%',
                        'NXT' => '1%',
                        'IFC' => '1%',
                        'DNC' => '1%',
                        'BLK' => '1%',
                        'VRC' => '1%',
                        'XPM' => '1%',
                        'VTC' => '1%',
                        'TFC' => '1%',
                        'PLC' => '1%',
                        'EAC' => '1%',
                        'PPC' => '1%',
                        'FZ' => '1%',
                        'ZET' => '1%',
                        'RSS' => '1%',
                        'PGC' => '1%',
                        'SKT' => '1%',
                        'JBC' => '1%',
                        'RIO' => '1%',
                        'LKC' => '1%',
                        'ZCC' => '1%',
                        'MCC' => '1%',
                        'QEC' => '1%',
                        'MET' => '1%',
                        'YTC' => '1%',
                        'HLB' => '1%',
                        'MRYC' => '1%',
                        'MTC' => '1%',
                        'KTC' => 0,
                    ),
                ),
            ),
            'exceptions' => array (
                '103' => '\\ccxt\\AuthenticationError',
                '104' => '\\ccxt\\AuthenticationError',
                '105' => '\\ccxt\\AuthenticationError',
                '106' => '\\ccxt\\InvalidNonce',
                '200' => '\\ccxt\\InsufficientFunds',
                '201' => '\\ccxt\\InvalidOrder',
                '202' => '\\ccxt\\InvalidOrder',
                '203' => '\\ccxt\\OrderNotFound',
                '402' => '\\ccxt\\DDoSProtection',
            ),
            'errorMessages' => array (
                '100' => 'Required parameters can not be empty',
                '101' => 'Illegal parameter',
                '102' => 'coin does not exist',
                '103' => 'Key does not exist',
                '104' => 'Signature does not match',
                '105' => 'Insufficient permissions',
                '106' => 'Request expired(nonce error)',
                '200' => 'Lack of balance',
                '201' => 'Too small for the number of trading',
                '202' => 'Price must be in 0 - 1000000',
                '203' => 'Order does not exist',
                '204' => 'Pending order amount must be above 0.001 BTC',
                '205' => 'Restrict pending order prices',
                '206' => 'Decimal place error',
                '401' => 'System error',
                '402' => 'Requests are too frequent',
                '403' => 'Non-open API',
                '404' => 'IP restriction does not request the resource',
                '405' => 'Currency transactions are temporarily closed',
            ),
            'options' => array (
                'quoteIds' => array ( 'btc', 'eth', 'usc' ),
            ),
        ));
    }

    public function fetch_markets () {
        $quoteIds = $this->options['quoteIds'];
        $result = array ();
        for ($b = 0; $b < count ($quoteIds); $b++) {
            $quoteId = $quoteIds[$b];
            $bases = $this->webGetQuoteAllcoin (array (
                'quote' => $quoteId,
            ));
            if ($bases === null)
                throw new ExchangeNotAvailable ($this->id . ' fetchMarkets() for "' . $quoteId . '" returned => "' . $this->json ($bases) . '"');
            $baseIds = is_array ($bases) ? array_keys ($bases) : array ();
            $numBaseIds = is_array ($baseIds) ? count ($baseIds) : 0;
            if ($numBaseIds < 1)
                throw new ExchangeNotAvailable ($this->id . ' fetchMarkets() for "' . $quoteId . '" returned => "' . $this->json ($bases) . '"');
            for ($i = 0; $i < count ($baseIds); $i++) {
                $baseId = $baseIds[$i];
                $market = $bases[$baseId];
                $base = strtoupper ($baseId);
                $quote = strtoupper ($quoteId);
                $base = $this->common_currency_code($base);
                $quote = $this->common_currency_code($quote);
                $id = $baseId . $quoteId;
                $symbol = $base . '/' . $quote;
                $precision = array (
                    'amount' => 8,
                    'price' => 8,
                );
                $lot = pow (10, -$precision['amount']);
                $result[] = array (
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'active' => true,
                    'lot' => $lot,
                    'precision' => $precision,
                    'limits' => array (
                        'amount' => array (
                            'min' => $lot,
                            'max' => pow (10, $precision['amount']),
                        ),
                        'price' => array (
                            'min' => pow (10, -$precision['price']),
                            'max' => pow (10, $precision['price']),
                        ),
                        'cost' => array (
                            'min' => null,
                            'max' => null,
                        ),
                    ),
                    'info' => $market,
                );
            }
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = $this->milliseconds ();
        $last = $this->safe_float($ticker, 'last');
        $percentage = $this->safe_float($ticker, 'change');
        $open = null;
        $change = null;
        $average = null;
        if ($percentage !== null) {
            $relativeChange = $percentage / 100;
            $open = $last / $this->sum (1, $relativeChange);
            $change = $last - $open;
            $average = $this->sum ($last, $open) / 2;
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => $this->safe_float($ticker, 'quoteVol'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetTickerQuote (array_merge (array (
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $quoteIds = $this->options['quoteIds'];
        $result = array ();
        for ($b = 0; $b < count ($quoteIds); $b++) {
            $quoteId = $quoteIds[$b];
            $tickers = $this->webGetQuoteAllcoin (array (
                'quote' => $quoteId,
            ));
            $baseIds = is_array ($tickers) ? array_keys ($tickers) : array ();
            if (strlen (!$baseIds)) {
                throw new ExchangeError ('fetchTickers failed');
            }
            for ($i = 0; $i < count ($baseIds); $i++) {
                $baseId = $baseIds[$i];
                $ticker = $tickers[$baseId];
                $id = $baseId . $quoteId;
                if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                    $market = $this->marketsById[$id];
                    $symbol = $market['symbol'];
                    $result[$symbol] = $this->parse_ticker(array (
                        'high' => $ticker[4],
                        'low' => $ticker[5],
                        'buy' => $ticker[2],
                        'sell' => $ticker[3],
                        'last' => $ticker[1],
                        'change' => $ticker[8],
                        'vol' => $ticker[6],
                        'quoteVol' => $ticker[7],
                    ), $market);
                }
            }
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $orderbook = $this->publicGetDepthQuote (array_merge (array (
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = intval ($trade['date']) * 1000;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $symbol = $market['symbol'];
        $cost = $this->cost_to_precision($symbol, $price * $amount);
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'tid'),
            'order' => null,
            'type' => 'limit',
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $trades = $this->publicGetOrdersQuote (array_merge (array (
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        ), $params));
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostBalance ($params);
        $result = array ();
        $balances = $this->omit ($response['data'], 'uid');
        $keys = is_array ($balances) ? array_keys ($balances) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            list ($currencyId, $accountType) = explode ('_', $key);
            $code = $currencyId;
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            }
            if (!(is_array ($result) && array_key_exists ($code, $result))) {
                $result[$code] = array (
                    'free' => null,
                    'used' => null,
                    'total' => null,
                );
            }
            $accountType = ($accountType === 'lock') ? 'used' : 'free';
            $result[$code][$accountType] = floatval ($balances[$key]);
        }
        $currencies = is_array ($result) ? array_keys ($result) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $result[$currency]['total'] = $this->sum ($result[$currency]['free'], $result[$currency]['used']);
        }
        return $this->parse_balance(array_merge (array ( 'info' => $response ), $result));
    }

    public function parse_order ($order, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = $this->parse8601 ($order['datetime']);
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount_original');
        $remaining = $this->safe_float($order, 'amount_outstanding');
        $filled = $amount - $remaining;
        $status = $this->safe_string($order, 'status');
        if ($status === 'cancelled') {
            $status = 'canceled';
        } else {
            $status = $remaining ? 'open' : 'closed';
        }
        $info = $this->safe_value($order, 'info', $order);
        return array (
            'id' => $this->safe_string($order, 'id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $order['type'],
            'price' => $price,
            'cost' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => null,
            'info' => $info,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostTradeAddQuote (array_merge (array (
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
            'type' => $side,
            'amount' => $amount,
            'price' => $price,
        ), $params));
        $id = (string) $response['id'];
        $order = $this->parse_order(array (
            'id' => $id,
            'datetime' => $this->ymdhms ($this->milliseconds ()),
            'amount_original' => $amount,
            'amount_outstanding' => $amount,
            'price' => $price,
            'type' => $side,
            'info' => $response,
        ), $market);
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostTradeCancelQuote (array_merge (array (
            'id' => $id,
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        ), $params));
        return $response;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostTradeViewQuote (array_merge (array (
            'id' => $id,
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        ), $params));
        return $this->parse_order($response['data'], $market);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'coin' => $market['baseId'],
            'quote' => $market['quoteId'],
        );
        if ($since !== null)
            $request['since'] = $since / 1000;
        $orders = $this->privatePostTradeListQuote (array_merge ($request, $params));
        return $this->parse_orders($orders['data'], $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'type' => 'open',
        ), $params));
        return $result;
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $apiType = 'rest';
        if ($api === 'web') {
            $apiType = $api;
        }
        $url = $this->urls['api'][$apiType] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public' || $api === 'web') {
            if ($api === 'web')
                $query['t'] = $this->nonce ();
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $query = $this->urlencode (array_merge (array (
                'key' => $this->apiKey,
                'nonce' => $this->nonce (),
            ), $query));
            $secret = $this->hash ($this->encode ($this->secret));
            $signature = $this->hmac ($this->encode ($query), $this->encode ($secret));
            $query .= '&' . 'signature=' . $signature;
            if ($method === 'GET') {
                $url .= '?' . $query;
            } else {
                $headers = array (
                    'Content-type' => 'application/x-www-form-urlencoded',
                );
                $body = $query;
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        // checks against error codes
        if (gettype ($body) != 'string')
            return;
        if (strlen ($body) === 0)
            return;
        if ($body[0] !== '{')
            return;
        $response = json_decode ($body, $as_associative_array = true);
        // private endpoints return the following structure:
        // array ("$result":true,"data":{...)} - success
        // array ("$result":false,"$code":"103") - failure
        // array ("$code":0,"msg":"Suceess","data":{"uid":"2716039","btc_balance":"0.00000000","btc_lock":"0.00000000","xrp_balance":"0.00000000","xrp_lock":"0.00000000")}
        $result = $this->safe_value($response, 'result');
        if ($result === null)
            // public endpoint ← this comment left here by the contributor, in fact a missing $result does not necessarily mean a public endpoint...
            // we should just check the $code and don't rely on the $result at all here...
            return;
        if ($result === true)
            // success
            return;
        $errorCode = $this->safe_string($response, 'code');
        $errorMessages = $this->errorMessages;
        $message = $this->safe_string($errorMessages, $errorCode, 'Unknown Error');
        if (is_array ($this->exceptions) && array_key_exists ($errorCode, $this->exceptions)) {
            throw new $this->exceptions[$errorCode] ($this->id . ' ' . $message);
        } else {
            throw new ExchangeError ($this->id . ' ' . $message);
        }
    }
}
