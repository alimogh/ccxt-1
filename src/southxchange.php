<?php

namespace ccxt;

use Exception; // a common import

class southxchange extends Exchange {

    public function describe() {
        return array_replace_recursive(parent::describe (), array(
            'id' => 'southxchange',
            'name' => 'SouthXchange',
            'countries' => array( 'AR' ), // Argentina
            'rateLimit' => 1000,
            'has' => array(
                'CORS' => true,
                'createDepositAddress' => true,
                'fetchOpenOrders' => true,
                'fetchTickers' => true,
                'withdraw' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/27838912-4f94ec8a-60f6-11e7-9e5d-bbf9bd50a559.jpg',
                'api' => 'https://www.southxchange.com/api',
                'www' => 'https://www.southxchange.com',
                'doc' => 'https://www.southxchange.com/Home/Api',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'markets',
                        'price/{symbol}',
                        'prices',
                        'book/{symbol}',
                        'trades/{symbol}',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'cancelMarketOrders',
                        'cancelOrder',
                        'getOrder',
                        'generatenewaddress',
                        'listOrders',
                        'listBalances',
                        'listTransactions',
                        'placeOrder',
                        'withdraw',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.1 / 100,
                    'taker' => 0.3 / 100,
                ),
            ),
            'commonCurrencies' => array(
                'SMT' => 'SmartNode',
                'MTC' => 'Marinecoin',
                'BHD' => 'Bithold',
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $markets = $this->publicGetMarkets ($params);
        $result = array();
        for ($i = 0; $i < count($markets); $i++) {
            $market = $markets[$i];
            $baseId = $market[0];
            $quoteId = $market[1];
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $id = $baseId . '/' . $quoteId;
            $result[] = array(
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => null,
                'info' => $market,
                'precision' => $this->precision,
                'limits' => $this->limits,
            );
        }
        return $result;
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostListBalances ($params);
        $result = array( 'info' => $response );
        for ($i = 0; $i < count($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'Currency');
            $code = $this->safe_currency_code($currencyId);
            $deposited = $this->safe_float($balance, 'Deposited');
            $unconfirmed = $this->safe_float($balance, 'Unconfirmed');
            $account = $this->account();
            $account['free'] = $this->safe_float($balance, 'Available');
            $account['total'] = $this->sum($deposited, $unconfirmed);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'symbol' => $this->market_id($symbol),
        );
        $response = $this->publicGetBookSymbol (array_merge($request, $params));
        return $this->parse_order_book($response, null, 'BuyOrders', 'SellOrders', 'Price', 'Amount');
    }

    public function parse_ticker($ticker, $market = null) {
        $timestamp = $this->milliseconds();
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'Last');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'Bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'Ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => $this->safe_float($ticker, 'Variation24Hr'),
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'Volume24Hr'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetPrices ($params);
        $tickers = $this->index_by($response, 'Market');
        $ids = is_array($tickers) ? array_keys($tickers) : array();
        $result = array();
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            $symbol = $id;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            }
            $ticker = $tickers[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'symbol' => $market['id'],
        );
        $response = $this->publicGetPriceSymbol (array_merge($request, $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_trade($trade, $market) {
        $timestamp = $this->safe_timestamp($trade, 'At');
        $price = $this->safe_float($trade, 'Price');
        $amount = $this->safe_float($trade, 'Amount');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        $side = $this->safe_string($trade, 'Type');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array(
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'id' => null,
            'order' => null,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'takerOrMaker' => null,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'symbol' => $market['id'],
        );
        $response = $this->publicGetTradesSymbol (array_merge($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_order($order, $market = null) {
        $status = 'open';
        $baseId = $this->safe_string($order, 'ListingCurrency');
        $quoteId = $this->safe_string($order, 'ReferenceCurrency');
        $base = $this->safe_currency_code($baseId);
        $quote = $this->safe_currency_code($quoteId);
        $symbol = $base . '/' . $quote;
        $timestamp = null;
        $price = $this->safe_float($order, 'LimitPrice');
        $amount = $this->safe_float($order, 'OriginalAmount');
        $remaining = $this->safe_float($order, 'Amount');
        $filled = null;
        $cost = null;
        if ($amount !== null) {
            $cost = $price * $amount;
            if ($remaining !== null) {
                $filled = $amount - $remaining;
            }
        }
        $type = 'limit';
        $side = $this->safe_string_lower($order, 'Type');
        $id = $this->safe_string($order, 'Code');
        $result = array(
            'info' => $order,
            'id' => $id,
            'clientOrderId' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
            'average' => null,
            'trades' => null,
        );
        return $result;
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market($symbol);
        }
        $response = $this->privatePostListOrders ($params);
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'listingCurrency' => $market['base'],
            'referenceCurrency' => $market['quote'],
            'type' => $side,
            'amount' => $amount,
        );
        if ($type === 'limit') {
            $request['limitPrice'] = $price;
        }
        $response = $this->privatePostPlaceOrder (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => (string) $response,
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'orderCode' => $id,
        );
        return $this->privatePostCancelOrder (array_merge($request, $params));
    }

    public function create_deposit_address($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency($code);
        $request = array(
            'currency' => $currency['id'],
        );
        $response = $this->privatePostGeneratenewaddress (array_merge($request, $params));
        $parts = explode('|', $response);
        $numParts = is_array($parts) ? count($parts) : 0;
        $address = $parts[0];
        $this->check_address($address);
        $tag = null;
        if ($numParts > 1) {
            $tag = $parts[1];
        }
        return array(
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency($code);
        $request = array(
            'currency' => $currency['id'],
            'address' => $address,
            'amount' => $amount,
        );
        if ($tag !== null) {
            $request['address'] = $address . '|' . $tag;
        }
        $response = $this->privatePostWithdraw (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => null,
        );
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit($params, $this->extract_params($path));
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = $this->nonce();
            $query = array_merge(array(
                'key' => $this->apiKey,
                'nonce' => $nonce,
            ), $query);
            $body = $this->json($query);
            $headers = array(
                'Content-Type' => 'application/json',
                'Hash' => $this->hmac($this->encode($body), $this->encode($this->secret), 'sha512'),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
