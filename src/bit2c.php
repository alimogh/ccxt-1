<?php

namespace ccxt;

use Exception as Exception; // a common import

class bit2c extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bit2c',
            'name' => 'Bit2C',
            'countries' => array ( 'IL' ), // Israel
            'rateLimit' => 3000,
            'has' => array (
                'CORS' => false,
                'fetchOpenOrders' => true,
                'fetchMyTrades' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766119-3593220e-5ece-11e7-8b3a-5a041f6bcc3f.jpg',
                'api' => 'https://bit2c.co.il',
                'www' => 'https://www.bit2c.co.il',
                'doc' => array (
                    'https://www.bit2c.co.il/home/api',
                    'https://github.com/OferE/bit2c',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'Exchanges/{pair}/Ticker',
                        'Exchanges/{pair}/orderbook',
                        'Exchanges/{pair}/trades',
                        'Exchanges/{pair}/lasttrades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'Merchant/CreateCheckout',
                        'Order/AddCoinFundsRequest',
                        'Order/AddFund',
                        'Order/AddOrder',
                        'Order/AddOrderMarketPriceBuy',
                        'Order/AddOrderMarketPriceSell',
                        'Order/CancelOrder',
                        'Order/AddCoinFundsRequest',
                        'Order/AddStopOrder',
                        'Payment/GetMyId',
                        'Payment/Send',
                        'Payment/Pay',
                    ),
                    'get' => array (
                        'Account/Balance',
                        'Account/Balance/v2',
                        'Order/MyOrders',
                        'Order/GetById',
                        'Order/AccountHistory',
                        'Order/OrderHistory',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/NIS' => array ( 'id' => 'BtcNis', 'symbol' => 'BTC/NIS', 'base' => 'BTC', 'quote' => 'NIS' ),
                'ETH/NIS' => array ( 'id' => 'EthNis', 'symbol' => 'ETH/NIS', 'base' => 'ETH', 'quote' => 'NIS' ),
                'BCH/NIS' => array ( 'id' => 'BchAbcNis', 'symbol' => 'BCH/NIS', 'base' => 'BCH', 'quote' => 'NIS' ),
                'LTC/NIS' => array ( 'id' => 'LtcNis', 'symbol' => 'LTC/NIS', 'base' => 'LTC', 'quote' => 'NIS' ),
                'ETC/NIS' => array ( 'id' => 'EtcNis', 'symbol' => 'ETC/NIS', 'base' => 'ETC', 'quote' => 'NIS' ),
                'BTG/NIS' => array ( 'id' => 'BtgNis', 'symbol' => 'BTG/NIS', 'base' => 'BTG', 'quote' => 'NIS' ),
                'BSV/NIS' => array ( 'id' => 'BchSvNis', 'symbol' => 'BSV/NIS', 'base' => 'BSV', 'quote' => 'NIS' ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.5 / 100,
                    'taker' => 0.5 / 100,
                ),
            ),
            'options' => array (
                'fetchTradesMethod' => 'public_get_exchanges_pair_lasttrades',
            ),
        ));
    }

    public function fetch_balance ($params = array ()) {
        $balance = $this->privateGetAccountBalanceV2 ();
        $result = array ( 'info' => $balance );
        $currencies = is_array ($this->currencies) ? array_keys ($this->currencies) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $account = $this->account ();
            if (is_array ($balance) && array_key_exists ($currency, $balance)) {
                $available = 'AVAILABLE_' . $currency;
                $account['free'] = $balance[$available];
                $account['total'] = $balance[$currency];
                $account['used'] = $account['total'] - $account['free'];
            }
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $orderbook = $this->publicGetExchangesPairOrderbook (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $ticker = $this->publicGetExchangesPairTicker (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $timestamp = $this->milliseconds ();
        $averagePrice = $this->safe_float($ticker, 'av');
        $baseVolume = $this->safe_float($ticker, 'a');
        $quoteVolume = null;
        if ($baseVolume !== null && $averagePrice !== null)
            $quoteVolume = $baseVolume * $averagePrice;
        $last = $this->safe_float($ticker, 'll');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'h'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'l'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => $averagePrice,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $market = $this->market ($symbol);
        $method = $this->options['fetchTradesMethod'];
        $response = $this->$method (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        if (gettype ($response) === 'string') {
            throw new ExchangeError ($response);
        }
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $method = 'privatePostOrderAddOrder';
        $order = array (
            'Amount' => $amount,
            'Pair' => $this->market_id($symbol),
        );
        if ($type === 'market') {
            $method .= 'MarketPrice' . $this->capitalize ($side);
        } else {
            $order['Price'] = $price;
            $order['Total'] = $amount * $price;
            $order['IsBid'] = ($side === 'buy');
        }
        $result = $this->$method (array_merge ($order, $params));
        return array (
            'info' => $result,
            'id' => $result['NewOrder']['id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostOrderCancelOrder (array ( 'id' => $id ));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        if ($api === 'public') {
            // lasttrades is the only endpoint that doesn't require the .json extension/suffix
            if (mb_strpos ($path, 'lasttrades') < 0) {
                $url .= '.json';
            }
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $query = array_merge (array ( 'nonce' => $nonce ), $params);
            $body = $this->urlencode ($query);
            $signature = $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512', 'base64');
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'key' => $this->apiKey,
                'sign' => $this->decode ($signature),
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($symbol === null)
            throw new ArgumentsRequired ($this->id . ' fetchOpenOrders() requires a $symbol argument');
        $market = $this->market ($symbol);
        $response = $this->privateGetOrderMyOrders (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        $orders = $this->safe_value($response, $market['id'], array ());
        $asks = $this->safe_value($orders, 'ask');
        $bids = $this->safe_value($orders, 'bid');
        return $this->parse_orders($this->array_concat($asks, $bids), $market, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        $timestamp = $order['created'];
        $price = $order['price'];
        $amount = $order['amount'];
        $cost = $price * $amount;
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        $side = $this->safe_value($order, 'type');
        if ($side === 0) {
            $side = 'buy';
        } else if ($side === 1) {
            $side = 'sell';
        }
        $id = $this->safe_string($order, 'id');
        $status = $this->safe_string($order, 'status');
        return array (
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'cost' => $cost,
            'trades' => null,
            'fee' => null,
            'info' => $order,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $method = 'privateGetOrderOrderhistory';
        $request = array ();
        if ($limit !== null)
            $request['take'] = $limit;
        $request['take'] = $limit;
        if ($since !== null) {
            $request['toTime'] = $this->ymd ($this->milliseconds (), '.');
            $request['fromTime'] = $this->ymd ($since, '.');
        }
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
        }
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = null;
        $id = null;
        $price = null;
        $amount = null;
        $orderId = null;
        $feeCost = null;
        $side = null;
        $reference = $this->safe_string($trade, 'reference');
        if ($reference !== null) {
            $timestamp = $this->safe_integer($trade, 'ticks') * 1000;
            $price = $this->safe_float($trade, 'price');
            $amount = $this->safe_float($trade, 'firstAmount');
            $reference_parts = explode ('|', $reference); // $reference contains => 'pair|$orderId|tradeId'
            if ($market === null) {
                $marketId = $this->safe_string($trade, 'pair');
                if (is_array ($this->markets_by_id[$marketId]) && array_key_exists ($marketId, $this->markets_by_id[$marketId])) {
                    $market = $this->markets_by_id[$marketId];
                } else if (is_array ($this->markets_by_id) && array_key_exists ($reference_parts[0], $this->markets_by_id)) {
                    $market = $this->markets_by_id[$reference_parts[0]];
                }
            }
            $orderId = $reference_parts[1];
            $id = $reference_parts[2];
            $side = $this->safe_integer($trade, 'action');
            if ($side === 0) {
                $side = 'buy';
            } else if ($side === 1) {
                $side = 'sell';
            }
            $feeCost = $this->safe_float($trade, 'feeAmount');
        } else {
            $timestamp = $this->safe_integer($trade, 'date') * 1000;
            $id = $this->safe_string($trade, 'tid');
            $price = $this->safe_float($trade, 'price');
            $amount = $this->safe_float($trade, 'amount');
            $side = $this->safe_value($trade, 'isBid');
            if ($side !== null) {
                if ($side) {
                    $side = 'buy';
                } else {
                    $side = 'sell';
                }
            }
        }
        $symbol = null;
        if ($market !== null)
            $symbol = $market['symbol'];
        return array (
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $price * $amount,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => 'NIS',
                'rate' => null,
            ),
        );
    }
}
