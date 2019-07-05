<?php

namespace ccxt;

use Exception as Exception; // a common import

class lykke extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'lykke',
            'name' => 'Lykke',
            'countries' => array ( 'CH' ),
            'version' => 'v1',
            'rateLimit' => 200,
            'has' => array (
                'CORS' => false,
                'fetchOHLCV' => false,
                'fetchTrades' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/34487620-3139a7b0-efe6-11e7-90f5-e520cef74451.jpg',
                'api' => array (
                    'mobile' => 'https://public-api.lykke.com/api',
                    'public' => 'https://hft-api.lykke.com/api',
                    'private' => 'https://hft-api.lykke.com/api',
                    'test' => array (
                        'mobile' => 'https://public-api.lykke.com/api',
                        'public' => 'https://hft-service-dev.lykkex.net/api',
                        'private' => 'https://hft-service-dev.lykkex.net/api',
                    ),
                ),
                'www' => 'https://www.lykke.com',
                'doc' => array (
                    'https://hft-api.lykke.com/swagger/ui/',
                    'https://www.lykke.com/lykke_api',
                ),
                'fees' => 'https://www.lykke.com/trading-conditions',
            ),
            'api' => array (
                'mobile' => array (
                    'get' => array (
                        'Market/{market}',
                        'Trades/{AssetPairId}',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'AssetPairs',
                        'AssetPairs/{id}',
                        'IsAlive',
                        'OrderBooks',
                        'OrderBooks/{AssetPairId}',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'Orders',
                        'Orders/{id}',
                        'Wallets',
                    ),
                    'post' => array (
                        'Orders/limit',
                        'Orders/market',
                        'Orders/{id}/Cancel',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.0, // as of 7 Feb 2018, see https://github.com/ccxt/ccxt/issues/1863
                    'taker' => 0.0, // https://www.lykke.com/cp/wallet-fees-and-limits
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'BTC' => 0.001,
                    ),
                    'deposit' => array (
                        'BTC' => 0,
                    ),
                ),
            ),
        ));
    }

    public function parse_trade ($trade, $market) {
        //
        //  public fetchTrades
        //
        //   {
        //     "$id" => "d5983ab8-e9ec-48c9-bdd0-1b18f8e80a71",
        //     "assetPairId" => "BTCUSD",
        //     "dateTime" => "2019-05-15T06:52:02.147Z",
        //     "volume" => 0.00019681,
        //     "index" => 0,
        //     "$price" => 8023.333,
        //     "action" => "Buy"
        //   }
        //
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($trade, 'AssetPairId');
            $market = $this->safe_value($this->markets_by_id, $marketId);
        }
        if ($market) {
            $symbol = $market['symbol'];
        }
        $id = $this->safe_string($trade, 'id');
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'dateTime'));
        $side = $this->safe_string($trade, 'action');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'volume');
        $cost = $price * $amount;
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'order' => null,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null) {
            $limit = 100;
        }
        $request = array (
            'AssetPairId' => $market['id'],
            'skip' => 0,
            'take' => $limit,
        );
        $response = $this->mobileGetTradesAssetPairId (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetWallets ($params);
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'AssetId');
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account ();
            $account['total'] = $this->safe_float($balance, 'Balance');
            $account['used'] = $this->safe_float($balance, 'Reserved');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostOrdersIdCancel (array( 'id' => $id ));
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $query = array (
            'AssetPairId' => $market['id'],
            'OrderAction' => $this->capitalize ($side),
            'Volume' => $amount,
        );
        if ($type === 'market') {
            $query['Asset'] = ($side === 'buy') ? $market['base'] : $market['quote'];
        } else if ($type === 'limit') {
            $query['Price'] = $price;
        }
        $method = 'privatePostOrders' . $this->capitalize ($type);
        $result = $this->$method (array_merge ($query, $params));
        return array (
            'id' => null,
            'info' => $result,
        );
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetAssetPairs ();
        //
        //     array ( array (                Id => "AEBTC",
        //                      Name => "AE/BTC",
        //                  Accuracy =>  6,
        //          InvertedAccuracy =>  8,
        //               BaseAssetId => "6f75280b-a005-4016-a3d8-03dc644e8912",
        //            QuotingAssetId => "BTC",
        //                 MinVolume =>  0.4,
        //         MinInvertedVolume =>  0.0001                                 ),
        //       {                Id => "AEETH",
        //                      Name => "AE/ETH",
        //                  Accuracy =>  6,
        //          InvertedAccuracy =>  8,
        //               BaseAssetId => "6f75280b-a005-4016-a3d8-03dc644e8912",
        //            QuotingAssetId => "ETH",
        //                 MinVolume =>  0.4,
        //         MinInvertedVolume =>  0.001                                  } )
        //
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'Id');
            $name = $this->safe_string($market, 'Name');
            list($baseId, $quoteId) = explode('/', $name);
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $this->safe_integer($market, 'Accuracy'),
                'price' => $this->safe_integer($market, 'InvertedAccuracy'),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'active' => true,
                'info' => $market,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow(10, -$precision['amount']),
                        'max' => pow(10, $precision['amount']),
                    ),
                    'price' => array (
                        'min' => pow(10, -$precision['price']),
                        'max' => pow(10, $precision['price']),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        $close = $this->safe_float($ticker, 'lastPrice');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $close,
            'last' => $close,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => $this->safe_float($ticker, 'volume24H'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        $ticker = $this->mobileGetMarketMarket (array_merge ($request, $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'Pending' => 'open',
            'InOrderBook' => 'open',
            'Processing' => 'open',
            'Matched' => 'closed',
            'Cancelled' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $status = $this->parse_order_status($this->safe_string($order, 'Status'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'AssetPairId');
            $market = $this->safe_value($this->markets_by_id, $marketId);
        }
        if ($market) {
            $symbol = $market['symbol'];
        }
        $lastTradeTimestamp = $this->parse8601 ($this->safe_string($order, 'LastMatchTime'));
        $timestamp = null;
        if ((is_array($order) && array_key_exists('Registered', $order)) && ($order['Registered'])) {
            $timestamp = $this->parse8601 ($order['Registered']);
        } else if ((is_array($order) && array_key_exists('CreatedAt', $order)) && ($order['CreatedAt'])) {
            $timestamp = $this->parse8601 ($order['CreatedAt']);
        }
        $price = $this->safe_float($order, 'Price');
        $amount = $this->safe_float($order, 'Volume');
        $remaining = $this->safe_float($order, 'RemainingVolume');
        $filled = $amount - $remaining;
        $cost = $filled * $price;
        $id = $this->safe_string($order, 'Id');
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => null,
            'side' => null,
            'price' => $price,
            'cost' => $cost,
            'average' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $id,
        );
        $response = $this->privateGetOrdersId (array_merge ($request, $params));
        return $this->parse_order($response);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetOrders ($params);
        return $this->parse_orders($response, null, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'status' => 'InOrderBook',
        );
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response, null, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'status' => 'Matched',
        );
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response, null, $since, $limit);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetOrderBooksAssetPairId (array_merge (array (
            'AssetPairId' => $this->market_id($symbol),
        ), $params));
        $orderbook = array (
            'timestamp' => null,
            'bids' => array(),
            'asks' => array(),
        );
        $timestamp = null;
        for ($i = 0; $i < count ($response); $i++) {
            $side = $response[$i];
            if ($side['IsBuy']) {
                $orderbook['bids'] = $this->array_concat($orderbook['bids'], $side['Prices']);
            } else {
                $orderbook['asks'] = $this->array_concat($orderbook['asks'], $side['Prices']);
            }
            $sideTimestamp = $this->parse8601 ($side['Timestamp']);
            $timestamp = ($timestamp === null) ? $sideTimestamp : max ($timestamp, $sideTimestamp);
        }
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'Price', 'Volume');
    }

    public function parse_bid_ask ($bidask, $priceKey = 0, $amountKey = 1) {
        $price = $this->safe_float($bidask, $priceKey);
        $amount = $this->safe_float($bidask, $amountKey);
        if ($amount < 0) {
            $amount = -$amount;
        }
        return array ( $price, $amount );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'mobile') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else if ($api === 'private') {
            if ($method === 'GET') {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
            }
            $this->check_required_credentials();
            $headers = array (
                'api-key' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            );
            if ($method === 'POST') {
                if ($params) {
                    $body = $this->json ($params);
                }
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
