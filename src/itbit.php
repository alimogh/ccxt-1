<?php

namespace ccxt;

use Exception as Exception; // a common import

class itbit extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'itbit',
            'name' => 'itBit',
            'countries' => array ( 'US' ),
            'rateLimit' => 2000,
            'version' => 'v1',
            'has' => array (
                'CORS' => true,
                'createMarketOrder' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27822159-66153620-60ad-11e7-89e7-005f6d7f3de0.jpg',
                'api' => 'https://api.itbit.com',
                'www' => 'https://www.itbit.com',
                'doc' => array (
                    'https://api.itbit.com/docs',
                    'https://www.itbit.com/api',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets/{symbol}/ticker',
                        'markets/{symbol}/order_book',
                        'markets/{symbol}/trades',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'wallets',
                        'wallets/{walletId}',
                        'wallets/{walletId}/balances/{currencyCode}',
                        'wallets/{walletId}/funding_history',
                        'wallets/{walletId}/trades',
                        'wallets/{walletId}/orders',
                        'wallets/{walletId}/orders/{id}',
                    ),
                    'post' => array (
                        'wallet_transfers',
                        'wallets',
                        'wallets/{walletId}/cryptocurrency_deposits',
                        'wallets/{walletId}/cryptocurrency_withdrawals',
                        'wallets/{walletId}/orders',
                        'wire_withdrawal',
                    ),
                    'delete' => array (
                        'wallets/{walletId}/orders/{id}',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/USD' => array ( 'id' => 'XBTUSD', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD' ),
                'BTC/SGD' => array ( 'id' => 'XBTSGD', 'symbol' => 'BTC/SGD', 'base' => 'BTC', 'quote' => 'SGD' ),
                'BTC/EUR' => array ( 'id' => 'XBTEUR', 'symbol' => 'BTC/EUR', 'base' => 'BTC', 'quote' => 'EUR' ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0,
                    'taker' => 0.2 / 100,
                ),
            ),
            'commonCurrencies' => array (
                'XBT' => 'BTC',
            ),
        ));
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetMarketsSymbolOrderBook (array_merge (array (
            'symbol' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $ticker = $this->publicGetMarketsSymbolTicker (array_merge (array (
            'symbol' => $this->market_id($symbol),
        ), $params));
        $serverTimeUTC = $this->safe_string($ticker, 'serverTimeUTC');
        if (!$serverTimeUTC)
            throw new ExchangeError ($this->id . ' fetchTicker returned a bad response => ' . $this->json ($ticker));
        $timestamp = $this->parse8601 ($serverTimeUTC);
        $vwap = $this->safe_float($ticker, 'vwap24h');
        $baseVolume = $this->safe_float($ticker, 'volume24h');
        $quoteVolume = null;
        if ($baseVolume !== null && $vwap !== null)
            $quoteVolume = $baseVolume * $vwap;
        $last = $this->safe_float($ticker, 'lastPrice');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high24h'),
            'low' => $this->safe_float($ticker, 'low24h'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $this->safe_float($ticker, 'openToday'),
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

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     {
        //         $timestamp => "2015-05-22T17:45:34.7570000Z",
        //         matchNumber => "5CR1JEUBBM8J",
        //         $price => "351.45000000",
        //         $amount => "0.00010000"
        //     }
        //
        // fetchMyTrades (private)
        //
        //     {
        //         "$orderId" => "248ffda4-83a0-4033-a5bb-8929d523f59f",
        //         "$timestamp" => "2015-05-11T14:48:01.9870000Z",
        //         "instrument" => "XBTUSD",
        //         "direction" => "buy",                      // buy or sell
        //         "currency1" => "XBT",                      // $base currency
        //         "currency1Amount" => "0.00010000",         // order $amount in $base currency
        //         "currency2" => "USD",                      // $quote currency
        //         "currency2Amount" => "0.0250530000000000", // order $cost in $quote currency
        //         "rate" => "250.53000000",
        //         "commissionPaid" => "0.00000000",   // net $trade $fee paid after using any available rebate balance
        //         "commissionCurrency" => "USD",
        //         "rebatesApplied" => "-0.000125265", // negative values represent $amount of rebate balance used for trades removing liquidity from order book; positive values represent $amount of rebate balance earned from trades adding liquidity to order book
        //         "rebateCurrency" => "USD",
        //         "executionId" => "23132"
        //     }
        //
        $id = $this->safe_string_2($trade, 'executionId', 'matchNumber');
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'timestamp'));
        $side = $this->safe_string($trade, 'direction');
        $orderId = $this->safe_string($trade, 'orderId');
        $feeCost = $this->safe_float($trade, 'commissionPaid');
        $feeCurrencyId = $this->safe_string($trade, 'commissionCurrency');
        $feeCurrency = $this->common_currency_code($feeCurrencyId);
        $fee = null;
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $price = $this->safe_float_2($trade, 'price', 'rate');
        $amount = $this->safe_float_2($trade, 'currency1Amount', 'amount');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        $symbol = null;
        $marketId = $this->safe_string($trade, 'instrument');
        if ($marketId !== null) {
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                $baseId = $this->safe_string($trade, 'currency1');
                $quoteId = $this->safe_string($trade, 'currency2');
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        return array (
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $walletId = $this->safe_string($params, 'walletId');
        if ($walletId === null) {
            throw new ExchangeError ($this->id . ' fetchMyTrades requires a $walletId parameter');
        }
        $this->load_markets();
        $request = array (
            'walletId' => $walletId,
        );
        if ($since !== null) {
            $request['rangeStart'] = $this->ymdhms ($since, 'T');
        }
        if ($limit !== null) {
            $request['perPage'] = $limit; // default 50, max 50
        }
        $response = $this->privateGetWalletsWalletIdTrades (array_merge ($request, $params));
        //
        //     {
        //         "totalNumberOfRecords" => "2",
        //         "currentPageNumber" => "1",
        //         "latestExecutionId" => "332", // most recent execution at time of $response
        //         "recordsPerPage" => "50",
        //         "tradingHistory" => array (
        //             {
        //                 "orderId" => "248ffda4-83a0-4033-a5bb-8929d523f59f",
        //                 "timestamp" => "2015-05-11T14:48:01.9870000Z",
        //                 "instrument" => "XBTUSD",
        //                 "direction" => "buy",                      // buy or sell
        //                 "currency1" => "XBT",                      // base currency
        //                 "currency1Amount" => "0.00010000",         // order amount in base currency
        //                 "currency2" => "USD",                      // quote currency
        //                 "currency2Amount" => "0.0250530000000000", // order cost in quote currency
        //                 "rate" => "250.53000000",
        //                 "commissionPaid" => "0.00000000",   // net trade fee paid after using any available rebate balance
        //                 "commissionCurrency" => "USD",
        //                 "rebatesApplied" => "-0.000125265", // negative values represent amount of rebate balance used for $trades removing liquidity from order book; positive values represent amount of rebate balance earned from $trades adding liquidity to order book
        //                 "rebateCurrency" => "USD",
        //                 "executionId" => "23132"
        //             },
        //         ),
        //     }
        //
        $trades = $this->safe_value($response, 'tradingHistory', array ());
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->publicGetMarketsSymbolTrades (array_merge ($request, $params));
        //
        //     {
        //         count => 3,
        //         recentTrades => array (
        //             array (
        //                 timestamp => "2015-05-22T17:45:34.7570000Z",
        //                 matchNumber => "5CR1JEUBBM8J",
        //                 price => "351.45000000",
        //                 amount => "0.00010000"
        //             ),
        //         )
        //     }
        //
        $trades = $this->safe_value($response, 'recentTrades', array ());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->fetch_wallets ($params);
        $balances = $response[0]['balances'];
        $result = array ( 'info' => $response );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $this->safe_string($balance, 'currency');
            $code = $this->common_currency_code($currencyId);
            $account = array (
                'free' => $this->safe_float($balance, 'availableBalance'),
                'used' => 0.0,
                'total' => $this->safe_float($balance, 'totalBalance'),
            );
            $account['used'] = $account['total'] - $account['free'];
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_wallets ($params = array ()) {
        if (!$this->uid)
            throw new AuthenticationError ($this->id . ' fetchWallets requires uid API credential');
        $request = array (
            'userId' => $this->uid,
        );
        return $this->privateGetWallets (array_merge ($request, $params));
    }

    public function fetch_wallet ($walletId, $params = array ()) {
        $wallet = array (
            'walletId' => $walletId,
        );
        return $this->privateGetWalletsWalletId (array_merge ($wallet, $params));
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'status' => 'open',
        ), $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'status' => 'filled',
        ), $params));
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $walletIdInParams = (is_array ($params) && array_key_exists ('walletId', $params));
        if (!$walletIdInParams)
            throw new ExchangeError ($this->id . ' fetchOrders requires a $walletId parameter');
        $walletId = $params['walletId'];
        $response = $this->privateGetWalletsWalletIdOrders (array_merge (array (
            'walletId' => $walletId,
        ), $params));
        $orders = $this->parse_orders($response, null, $since, $limit);
        return $orders;
    }

    public function parse_order ($order, $market = null) {
        $side = $order['side'];
        $type = $order['type'];
        $symbol = $this->markets_by_id[$order['instrument']]['symbol'];
        $timestamp = $this->parse8601 ($order['createdTime']);
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'amountFilled');
        $remaining = $amount - $filled;
        $fee = null;
        $price = $this->safe_float($order, 'price');
        $cost = $price * $this->safe_float($order, 'volumeWeightedAveragePrice');
        return array (
            'id' => $order['id'],
            'info' => $order,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $order['status'],
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'fee' => $fee,
            // 'trades' => $this->parse_trades($order['trades'], $market),
        );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market')
            throw new ExchangeError ($this->id . ' allows limit orders only');
        $walletIdInParams = (is_array ($params) && array_key_exists ('walletId', $params));
        if (!$walletIdInParams)
            throw new ExchangeError ($this->id . ' createOrder requires a walletId parameter');
        $amount = (string) $amount;
        $price = (string) $price;
        $market = $this->market ($symbol);
        $order = array (
            'side' => $side,
            'type' => $type,
            'currency' => str_replace ($market['quote'], '', $market['id']),
            'amount' => $amount,
            'display' => $amount,
            'price' => $price,
            'instrument' => $market['id'],
        );
        $response = $this->privatePostWalletsWalletIdOrders (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $walletIdInParams = (is_array ($params) && array_key_exists ('walletId', $params));
        if (!$walletIdInParams) {
            throw new ExchangeError ($this->id . ' fetchOrder requires a walletId parameter');
        }
        $request = array ( 'id' => $id );
        $response = $this->privateGetWalletsWalletIdOrdersId (array_merge ($request, $params));
        return $this->parse_order($response);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $walletIdInParams = (is_array ($params) && array_key_exists ('walletId', $params));
        if (!$walletIdInParams)
            throw new ExchangeError ($this->id . ' cancelOrder requires a walletId parameter');
        return $this->privateDeleteWalletsWalletIdOrdersId (array_merge (array (
            'id' => $id,
        ), $params));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET' && $query)
            $url .= '?' . $this->urlencode ($query);
        if ($method === 'POST' && $query)
            $body = $this->json ($query);
        else
            $body = '';
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $timestamp = $nonce;
            $auth = array ( $method, $url, $body, $nonce, $timestamp );
            $message = $nonce . str_replace ('\\/', '/', $this->json ($auth));
            $hash = $this->hash ($this->encode ($message), 'sha256', 'binary');
            $binaryUrl = $this->encode ($url);
            $binhash = $this->binary_concat($binaryUrl, $hash);
            $signature = $this->hmac ($binhash, $this->encode ($this->secret), 'sha512', 'base64');
            $headers = array (
                'Authorization' => $this->apiKey . ':' . $signature,
                'Content-Type' => 'application/json',
                'X-Auth-Timestamp' => $timestamp,
                'X-Auth-Nonce' => $nonce,
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('code', $response))
            throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        return $response;
    }
}
