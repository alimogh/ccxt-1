<?php

namespace ccxt;

use Exception as Exception; // a common import

class independentreserve extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'independentreserve',
            'name' => 'Independent Reserve',
            'countries' => array ( 'AU', 'NZ' ), // Australia, New Zealand
            'rateLimit' => 1000,
            'has' => array (
                'CORS' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/30521662-cf3f477c-9bcb-11e7-89bc-d1ac85012eda.jpg',
                'api' => array (
                    'public' => 'https://api.independentreserve.com/Public',
                    'private' => 'https://api.independentreserve.com/Private',
                ),
                'www' => 'https://www.independentreserve.com',
                'doc' => 'https://www.independentreserve.com/API',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'GetValidPrimaryCurrencyCodes',
                        'GetValidSecondaryCurrencyCodes',
                        'GetValidLimitOrderTypes',
                        'GetValidMarketOrderTypes',
                        'GetValidOrderTypes',
                        'GetValidTransactionTypes',
                        'GetMarketSummary',
                        'GetOrderBook',
                        'GetTradeHistorySummary',
                        'GetRecentTrades',
                        'GetFxRates',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'PlaceLimitOrder',
                        'PlaceMarketOrder',
                        'CancelOrder',
                        'GetOpenOrders',
                        'GetClosedOrders',
                        'GetClosedFilledOrders',
                        'GetOrderDetails',
                        'GetAccounts',
                        'GetTransactions',
                        'GetDigitalCurrencyDepositAddress',
                        'GetDigitalCurrencyDepositAddresses',
                        'SynchDigitalCurrencyDepositAddressWithBlockchain',
                        'WithdrawDigitalCurrency',
                        'RequestFiatWithdrawal',
                        'GetTrades',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.5 / 100,
                    'maker' => 0.5 / 100,
                    'percentage' => true,
                    'tierBased' => false,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $baseCurrencies = $this->publicGetGetValidPrimaryCurrencyCodes ();
        $quoteCurrencies = $this->publicGetGetValidSecondaryCurrencyCodes ();
        $result = array ();
        for ($i = 0; $i < count ($baseCurrencies); $i++) {
            $baseId = $baseCurrencies[$i];
            $baseIdUppercase = strtoupper ($baseId);
            $base = $this->common_currency_code($baseIdUppercase);
            for ($j = 0; $j < count ($quoteCurrencies); $j++) {
                $quoteId = $quoteCurrencies[$j];
                $quoteIdUppercase = strtoupper ($quoteId);
                $quote = $this->common_currency_code($quoteIdUppercase);
                $id = $baseId . '/' . $quoteId;
                $symbol = $base . '/' . $quote;
                $result[] = array (
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'info' => $id,
                );
            }
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privatePostGetAccounts ();
        $result = array ( 'info' => $balances );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyCode = $balance['CurrencyCode'];
            $uppercase = strtoupper ($currencyCode);
            $currency = $this->common_currency_code($uppercase);
            $account = $this->account ();
            $account['free'] = $balance['AvailableBalance'];
            $account['total'] = $balance['TotalBalance'];
            $account['used'] = $account['total'] - $account['free'];
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetGetOrderBook (array_merge (array (
            'primaryCurrencyCode' => $market['baseId'],
            'secondaryCurrencyCode' => $market['quoteId'],
        ), $params));
        $timestamp = $this->parse8601 ($response['CreatedTimestampUtc']);
        return $this->parse_order_book($response, $timestamp, 'BuyOrders', 'SellOrders', 'Price', 'Volume');
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->parse8601 ($ticker['CreatedTimestampUtc']);
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $last = $ticker['LastPrice'];
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $ticker['DayHighestPrice'],
            'low' => $ticker['DayLowestPrice'],
            'bid' => $ticker['CurrentHighestBidPrice'],
            'bidVolume' => null,
            'ask' => $ticker['CurrentLowestOfferPrice'],
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => $ticker['DayAvgPrice'],
            'baseVolume' => $ticker['DayVolumeXbtInSecondaryCurrrency'],
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetGetMarketSummary (array_merge (array (
            'primaryCurrencyCode' => $market['baseId'],
            'secondaryCurrencyCode' => $market['quoteId'],
        ), $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_order ($order, $market = null) {
        $symbol = null;
        if ($market === null) {
            $symbol = $market['symbol'];
        } else {
            $market = $this->find_market($order['PrimaryCurrencyCode'] . '/' . $order['SecondaryCurrencyCode']);
        }
        $orderType = $this->safe_value($order, 'Type');
        if (mb_strpos ($orderType, 'Market') !== false)
            $orderType = 'market';
        else if (mb_strpos ($orderType, 'Limit') !== false)
            $orderType = 'limit';
        $side = null;
        if (mb_strpos ($orderType, 'Bid') !== false)
            $side = 'buy';
        else if (mb_strpos ($orderType, 'Offer') !== false)
            $side = 'sell';
        $timestamp = $this->parse8601 ($order['CreatedTimestampUtc']);
        $amount = $this->safe_float($order, 'VolumeOrdered');
        if ($amount === null)
            $amount = $this->safe_float($order, 'Volume');
        $filled = $this->safe_float($order, 'VolumeFilled');
        $remaining = null;
        $feeRate = $this->safe_float($order, 'FeePercent');
        $feeCost = null;
        if ($amount !== null) {
            if ($filled !== null) {
                $remaining = $amount - $filled;
                if ($feeRate !== null)
                    $feeCost = $feeRate * $filled;
            }
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['base'];
        }
        $fee = array (
            'rate' => $feeRate,
            'cost' => $feeCost,
            'currency' => $feeCurrency,
        );
        $id = $order['OrderGuid'];
        $status = $this->parse_order_status($order['Status']);
        $cost = $this->safe_float($order, 'Value');
        $average = $this->safe_float($order, 'AvgPrice');
        $price = $this->safe_float($order, 'Price', $average);
        return array (
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
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'Open' => 'open',
            'PartiallyFilled' => 'open',
            'Filled' => 'closed',
            'PartiallyFilledAndCancelled' => 'canceled',
            'Cancelled' => 'canceled',
            'PartiallyFilledAndExpired' => 'canceled',
            'Expired' => 'canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetOrderDetails (array_merge (array (
            'orderGuid' => $id,
        ), $params));
        $market = null;
        if ($symbol !== null)
            $market = $this->market ($symbol);
        return $this->parse_order($response, $market);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = 50, $params = array ()) {
        $this->load_markets();
        $pageIndex = $this->safe_integer($params, 'pageIndex', 1);
        $request = $this->ordered (array (
            'pageIndex' => $pageIndex,
            'pageSize' => $limit,
        ));
        $response = $this->privatePostGetTrades (array_merge ($request, $params));
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        return $this->parse_trades($response['Data'], $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['TradeTimestampUtc']);
        $id = $this->safe_string($trade, 'TradeGuid');
        $orderId = $this->safe_string($trade, 'OrderGuid');
        $price = $this->safe_float($trade, 'Price');
        if ($price === null) {
            $price = $this->safe_float($trade, 'SecondaryCurrencyTradePrice');
        }
        $amount = $this->safe_float($trade, 'VolumeTraded');
        if ($amount === null) {
            $amount = $this->safe_float($trade, 'PrimaryCurrencyAmount');
        }
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        $side = $this->safe_string($trade, 'OrderType');
        if ($side !== null) {
            if (mb_strpos ($side, 'Bid') !== false)
                $side = 'buy';
            else if (mb_strpos ($side, 'Offer') !== false)
                $side = 'sell';
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
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetGetRecentTrades (array_merge (array (
            'primaryCurrencyCode' => $market['baseId'],
            'secondaryCurrencyCode' => $market['quoteId'],
            'numberOfRecentTradesToRetrieve' => 50, // max = 50
        ), $params));
        return $this->parse_trades($response['Trades'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $capitalizedOrderType = $this->capitalize ($type);
        $method = 'privatePostPlace' . $capitalizedOrderType . 'Order';
        $orderType = $capitalizedOrderType;
        $orderType .= ($side === 'sell') ? 'Offer' : 'Bid';
        $order = $this->ordered (array (
            'primaryCurrencyCode' => $market['baseId'],
            'secondaryCurrencyCode' => $market['quoteId'],
            'orderType' => $orderType,
        ));
        if ($type === 'limit')
            $order['price'] = $price;
        $order['volume'] = $amount;
        $response = $this->$method (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['OrderGuid'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privatePostCancelOrder (array ( 'orderGuid' => $id ));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $path;
        if ($api === 'public') {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $auth = array (
                $url,
                'apiKey=' . $this->apiKey,
                'nonce=' . (string) $nonce,
            );
            // remove this crap
            $keys = is_array ($params) ? array_keys ($params) : array ();
            $payload = array ();
            for ($i = 0; $i < count ($keys); $i++) {
                $key = $keys[$i];
                $payload[] = $key . '=' . $params[$key];
            }
            $auth = $this->array_concat($auth, $payload);
            $message = implode (',', $auth);
            $signature = $this->hmac ($this->encode ($message), $this->encode ($this->secret));
            $body = $this->json (array (
                'apiKey' => $this->apiKey,
                'nonce' => $nonce,
                'signature' => $signature,
            ));
            $headers = array ( 'Content-Type' => 'application/json' );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
