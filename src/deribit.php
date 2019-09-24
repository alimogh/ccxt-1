<?php

namespace ccxt;

use Exception as Exception; // a common import

class deribit extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'deribit',
            'name' => 'Deribit',
            'countries' => array ( 'NL' ), // Netherlands
            'version' => 'v1',
            'userAgent' => null,
            'rateLimit' => 2000,
            'has' => array (
                'CORS' => true,
                'editOrder' => true,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchMyTrades' => true,
                'fetchTickers' => false,
            ),
            'urls' => array (
                'test' => 'https://test.deribit.com',
                'logo' => 'https://user-images.githubusercontent.com/1294454/41933112-9e2dd65a-798b-11e8-8440-5bab2959fcb8.jpg',
                'api' => 'https://www.deribit.com',
                'www' => 'https://www.deribit.com',
                'doc' => array (
                    'https://docs.deribit.com',
                    'https://github.com/deribit',
                ),
                'fees' => 'https://www.deribit.com/pages/information/fees',
                'referral' => 'https://www.deribit.com/reg-1189.4038',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'ping',
                        'test',
                        'getinstruments',
                        'index',
                        'getcurrencies',
                        'getorderbook',
                        'getlasttrades',
                        'getsummary',
                        'stats',
                        'getannouncments',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'account',
                        'getopenorders',
                        'positions',
                        'orderhistory',
                        'orderstate',
                        'tradehistory',
                        'newannouncements',
                    ),
                    'post' => array (
                        'buy',
                        'sell',
                        'edit',
                        'cancel',
                        'cancelall',
                    ),
                ),
            ),
            'exceptions' => array (
                // 0 or absent Success, No error
                '9999' => '\\ccxt\\PermissionDenied',   // "api_not_enabled" User didn't enable API for the Account
                '10000' => '\\ccxt\\AuthenticationError',  // "authorization_required" Authorization issue, invalid or absent signature etc
                '10001' => '\\ccxt\\ExchangeError',     // "error" Some general failure, no public information available
                '10002' => '\\ccxt\\InvalidOrder',      // "qty_too_low" Order quantity is too low
                '10003' => '\\ccxt\\InvalidOrder',      // "order_overlap" Rejection, order overlap is found and self-trading is not enabled
                '10004' => '\\ccxt\\OrderNotFound',     // "order_not_found" Attempt to operate with order that can't be found by specified id
                '10005' => '\\ccxt\\InvalidOrder',      // "price_too_low {Limit}" Price is too low, {Limit} defines current limit for the operation
                '10006' => '\\ccxt\\InvalidOrder',      // "price_too_low4idx {Limit}" Price is too low for current index, {Limit} defines current bottom limit for the operation
                '10007' => '\\ccxt\\InvalidOrder', // "price_too_high {Limit}" Price is too high, {Limit} defines current up limit for the operation
                '10008' => '\\ccxt\\InvalidOrder', // "price_too_high4idx {Limit}" Price is too high for current index, {Limit} defines current up limit for the operation
                '10009' => '\\ccxt\\InsufficientFunds', // "not_enough_funds" Account has not enough funds for the operation
                '10010' => '\\ccxt\\OrderNotFound', // "already_closed" Attempt of doing something with closed order
                '10011' => '\\ccxt\\InvalidOrder', // "price_not_allowed" This price is not allowed for some reason
                '10012' => '\\ccxt\\InvalidOrder', // "book_closed" Operation for instrument which order book had been closed
                '10013' => '\\ccxt\\PermissionDenied', // "pme_max_total_open_orders {Limit}" Total limit of open orders has been exceeded, it is applicable for PME users
                '10014' => '\\ccxt\\PermissionDenied', // "pme_max_future_open_orders {Limit}" Limit of count of futures' open orders has been exceeded, it is applicable for PME users
                '10015' => '\\ccxt\\PermissionDenied', // "pme_max_option_open_orders {Limit}" Limit of count of options' open orders has been exceeded, it is applicable for PME users
                '10016' => '\\ccxt\\PermissionDenied', // "pme_max_future_open_orders_size {Limit}" Limit of size for futures has been exceeded, it is applicable for PME users
                '10017' => '\\ccxt\\PermissionDenied', // "pme_max_option_open_orders_size {Limit}" Limit of size for options has been exceeded, it is applicable for PME users
                '10019' => '\\ccxt\\PermissionDenied', // "locked_by_admin" Trading is temporary locked by admin
                '10020' => '\\ccxt\\ExchangeError', // "invalid_or_unsupported_instrument" Instrument name is not valid
                '10022' => '\\ccxt\\InvalidOrder', // "invalid_quantity" quantity was not recognized as a valid number
                '10023' => '\\ccxt\\InvalidOrder', // "invalid_price" price was not recognized as a valid number
                '10024' => '\\ccxt\\InvalidOrder', // "invalid_max_show" max_show parameter was not recognized as a valid number
                '10025' => '\\ccxt\\InvalidOrder', // "invalid_order_id" Order id is missing or its format was not recognized as valid
                '10026' => '\\ccxt\\InvalidOrder', // "price_precision_exceeded" Extra precision of the price is not supported
                '10027' => '\\ccxt\\InvalidOrder', // "non_integer_contract_amount" Futures contract amount was not recognized as integer
                '10028' => '\\ccxt\\DDoSProtection', // "too_many_requests" Allowed request rate has been exceeded
                '10029' => '\\ccxt\\OrderNotFound', // "not_owner_of_order" Attempt to operate with not own order
                '10030' => '\\ccxt\\ExchangeError', // "must_be_websocket_request" REST request where Websocket is expected
                '10031' => '\\ccxt\\ExchangeError', // "invalid_args_for_instrument" Some of arguments are not recognized as valid
                '10032' => '\\ccxt\\InvalidOrder', // "whole_cost_too_low" Total cost is too low
                '10033' => '\\ccxt\\NotSupported', // "not_implemented" Method is not implemented yet
                '10034' => '\\ccxt\\InvalidOrder', // "stop_price_too_high" Stop price is too high
                '10035' => '\\ccxt\\InvalidOrder', // "stop_price_too_low" Stop price is too low
                '11035' => '\\ccxt\\InvalidOrder', // "no_more_stops {Limit}" Allowed amount of stop orders has been exceeded
                '11036' => '\\ccxt\\InvalidOrder', // "invalid_stoppx_for_index_or_last" Invalid StopPx (too high or too low) as to current index or market
                '11037' => '\\ccxt\\InvalidOrder', // "outdated_instrument_for_IV_order" Instrument already not available for trading
                '11038' => '\\ccxt\\InvalidOrder', // "no_adv_for_futures" Advanced orders are not available for futures
                '11039' => '\\ccxt\\InvalidOrder', // "no_adv_postonly" Advanced post-only orders are not supported yet
                '11040' => '\\ccxt\\InvalidOrder', // "impv_not_in_range 0..499%" Implied volatility is out of allowed range
                '11041' => '\\ccxt\\InvalidOrder', // "not_adv_order" Advanced order properties can't be set if the order is not advanced
                '11042' => '\\ccxt\\PermissionDenied', // "permission_denied" Permission for the operation has been denied
                '11044' => '\\ccxt\\OrderNotFound', // "not_open_order" Attempt to do open order operations with the not open order
                '11045' => '\\ccxt\\ExchangeError', // "invalid_event" Event name has not been recognized
                '11046' => '\\ccxt\\ExchangeError', // "outdated_instrument" At several minutes to instrument expiration, corresponding advanced implied volatility orders are not allowed
                '11047' => '\\ccxt\\ExchangeError', // "unsupported_arg_combination" The specified combination of arguments is not supported
                '11048' => '\\ccxt\\ExchangeError', // "not_on_this_server" The requested operation is not available on this server.
                '11050' => '\\ccxt\\ExchangeError', // "invalid_request" Request has not been parsed properly
                '11051' => '\\ccxt\\ExchangeNotAvailable', // "system_maintenance" System is under maintenance
                '11030' => '\\ccxt\\ExchangeError', // "other_reject {Reason}" Some rejects which are not considered as very often, more info may be specified in {Reason}
                '11031' => '\\ccxt\\ExchangeError', // "other_error {Error}" Some errors which are not considered as very often, more info may be specified in {Error}
            ),
            'precisionMode' => TICK_SIZE,
            'options' => array (
                'fetchTickerQuotes' => true,
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetGetinstruments ($params);
        $markets = $this->safe_value($response, 'result');
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'instrumentName');
            $baseId = $this->safe_string($market, 'baseCurrency');
            $quoteId = $this->safe_string($market, 'currency');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $type = $this->safe_string($market, 'kind');
            $future = ($type === 'future');
            $option = ($type === 'option');
            $active = $this->safe_value($market, 'isActive');
            $precision = array (
                'amount' => $this->safe_float($market, 'minTradeAmount'),
                'price' => $this->safe_float($market, 'tickSize'),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $id,
                'base' => $base,
                'quote' => $quote,
                'active' => $active,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($market, 'minTradeAmount'),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => $this->safe_float($market, 'tickSize'),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'type' => $type,
                'spot' => false,
                'future' => $future,
                'option' => $option,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetAccount ($params);
        //
        //     {
        //         "usOut":1569048827943520,
        //         "usIn":1569048827943020,
        //         "usDiff":500,
        //         "testnet":false,
        //         "success":true,
        //         "$result":array (
        //             "equity":2e-9,
        //             "maintenanceMargin":0.0,
        //             "initialMargin":0.0,
        //             "availableFunds":0.0,
        //             "$balance":0.0,
        //             "marginBalance":0.0,
        //             "SUPL":0.0,
        //             "SRPL":0.0,
        //             "PNL":0.0,
        //             "optionsPNL":0.0,
        //             "optionsSUPL":0.0,
        //             "optionsSRPL":0.0,
        //             "optionsD":0.0,
        //             "optionsG":0.0,
        //             "optionsV":0.0,
        //             "optionsTh":0.0,
        //             "futuresPNL":0.0,
        //             "futuresSUPL":0.0,
        //             "futuresSRPL":0.0,
        //             "deltaTotal":0.0,
        //             "sessionFunding":0.0,
        //             "depositAddress":"13tUtNsJSZa1F5GeCmwBywVrymHpZispzw",
        //             "currency":"BTC"
        //         ),
        //         "message":""
        //     }
        //
        $result = array (
            'info' => $response,
        );
        $balance = $this->safe_value($response, 'result', array());
        $currencyId = $this->safe_string($balance, 'currency');
        $code = $this->safe_currency_code($currencyId);
        $account = $this->account ();
        $account['free'] = $this->safe_float($balance, 'availableFunds');
        $account['used'] = $this->safe_float($balance, 'maintenanceMargin');
        $account['total'] = $this->safe_float($balance, 'equity');
        $result[$code] = $account;
        return $this->parse_balance($result);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privateGetAccount (array_merge ($request, $params));
        $result = $this->safe_value($response, 'result', array());
        $address = $this->safe_string($result, 'depositAddress');
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->safe_integer($ticker, 'created');
        $symbol = $this->find_symbol($this->safe_string($ticker, 'instrumentName'), $market);
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bidPrice'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'askPrice'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => $this->safe_float($ticker, 'volume'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        $response = $this->publicGetGetsummary (array_merge ($request, $params));
        return $this->parse_ticker($response['result'], $market);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     {
        //         "tradeId":23197559,
        //         "instrument":"BTC-28JUN19",
        //         "timeStamp":1559643011379,
        //         "tradeSeq":1997200,
        //         "quantity":2,
        //         "$amount":20.0,
        //         "$price":8010.0,
        //         "direction":"sell",
        //         "tickDirection":2,
        //         "indexPrice":7969.01
        //     }
        //
        // fetchMyTrades (private)
        //
        //     {
        //         "quantity":54,
        //         "$amount":540.0,
        //         "tradeId":23087297,
        //         "instrument":"BTC-PERPETUAL",
        //         "timeStamp":1559604178803,
        //         "tradeSeq":8265011,
        //         "$price":8213.0,
        //         "$side":"sell",
        //         "$orderId":12373631800,
        //         "matchingId":0,
        //         "liquidity":"T",
        //         "$fee":0.000049312,
        //         "feeCurrency":"BTC",
        //         "tickDirection":3,
        //         "indexPrice":8251.94,
        //         "selfTrade":false
        //     }
        //
        $id = $this->safe_string($trade, 'tradeId');
        $orderId = $this->safe_string($trade, 'orderId');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($trade, 'timeStamp');
        $side = $this->safe_string_2($trade, 'side', 'direction');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'quantity');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $fee = null;
        $feeCost = $this->safe_float($trade, 'fee');
        if ($feeCost !== null) {
            $feeCurrencyId = $this->safe_string($trade, 'feeCurrency');
            $feeCurrencyCode = $this->safe_currency_code($feeCurrencyId);
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrencyCode,
            );
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
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        } else {
            $request['limit'] = 10000;
        }
        $response = $this->publicGetGetlasttrades (array_merge ($request, $params));
        //
        //     {
        //         "usOut":1559643108984527,
        //         "usIn":1559643108984470,
        //         "usDiff":57,
        //         "testnet":false,
        //         "success":true,
        //         "$result" => array (
        //             {
        //                 "tradeId":23197559,
        //                 "instrument":"BTC-28JUN19",
        //                 "timeStamp":1559643011379,
        //                 "tradeSeq":1997200,
        //                 "quantity":2,
        //                 "amount":20.0,
        //                 "price":8010.0,
        //                 "direction":"sell",
        //                 "tickDirection":2,
        //                 "indexPrice":7969.01
        //             }
        //         ),
        //         "message":""
        //     }
        //
        $result = $this->safe_value($response, 'result', array());
        return $this->parse_trades($result, $market, $since, $limit);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        $response = $this->publicGetGetorderbook (array_merge ($request, $params));
        $timestamp = $this->safe_integer($response, 'usOut') / 1000;
        $orderbook = $this->parse_order_book($response['result'], $timestamp, 'bids', 'asks', 'price', 'quantity');
        return array_merge ($orderbook, array (
            'nonce' => $this->safe_integer($response, 'tstamp'),
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'open' => 'open',
            'cancelled' => 'canceled',
            'filled' => 'closed',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        //
        //     {
        //         "orderId" => 5258039,          // ID of the $order
        //         "$type" => "limit",             // not documented, but present in the actual response
        //         "instrument" => "BTC-26MAY17", // instrument name ($market $id)
        //         "direction" => "sell",         // $order direction, "buy" or "sell"
        //         "$price" => 1860,               // float, USD for futures, BTC for options
        //         "label" => "",                 // label set by the owner, up to 32 chars
        //         "quantity" => 10,              // quantity, in contracts ($10 per contract for futures, ฿1 — for options)
        //         "filledQuantity" => 3,         // $filled quantity, in contracts ($10 per contract for futures, ฿1 — for options)
        //         "avgPrice" => 1860,            // $average fill $price of the $order
        //         "commission" => -0.000001613,  // in BTC units
        //         "created" => 1494491899308,    // creation $timestamp
        //         "state" => "open",             // open, cancelled, etc
        //         "postOnly" => false            // true for post-only orders only
        // open orders --------------------------------------------------------
        //         "$lastUpdate" => 1494491988754, // $timestamp of the last $order state change (before this cancelorder of course)
        // closed orders ------------------------------------------------------
        //         "tstamp" => 1494492913288,     // $timestamp of the last $order state change, documented, but may be missing in the actual response
        //         "modified" => 1494492913289,   // $timestamp of the last db write operation, e.g. trade that doesn't change $order $status, documented, but may missing in the actual response
        //         "adv" => false                 // advanced $type (false, or "usd" or "implv")
        //         "trades" => array(),                // not documented, injected from the outside of the parseOrder method into the $order
        //     }
        //
        $timestamp = $this->safe_integer($order, 'created');
        $lastUpdate = $this->safe_integer($order, 'lastUpdate');
        $lastTradeTimestamp = $this->safe_integer_2($order, 'tstamp', 'modified');
        $id = $this->safe_string($order, 'orderId');
        $price = $this->safe_float($order, 'price');
        $average = $this->safe_float($order, 'avgPrice');
        $amount = $this->safe_float($order, 'quantity');
        $filled = $this->safe_float($order, 'filledQuantity');
        if ($lastTradeTimestamp === null) {
            if ($filled !== null) {
                if ($filled > 0) {
                    $lastTradeTimestamp = $lastUpdate;
                }
            }
        }
        $remaining = null;
        $cost = null;
        if ($filled !== null) {
            if ($amount !== null) {
                $remaining = $amount - $filled;
            }
            if ($price !== null) {
                $cost = $price * $filled;
            }
        }
        $status = $this->parse_order_status($this->safe_string($order, 'state'));
        $side = $this->safe_string_lower($order, 'direction');
        $feeCost = $this->safe_float($order, 'commission');
        if ($feeCost !== null) {
            $feeCost = abs ($feeCost);
        }
        $fee = array (
            'cost' => $feeCost,
            'currency' => 'BTC',
        );
        $type = $this->safe_string($order, 'type');
        $marketId = $this->safe_string($order, 'instrument');
        $symbol = null;
        if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
            $symbol = $market['symbol'];
        }
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'average' => $average,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
            'trades' => null, // todo => parse trades
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderId' => $id,
        );
        $response = $this->privateGetOrderstate (array_merge ($request, $params));
        $result = $this->safe_value($response, 'result');
        if ($result === null) {
            throw new OrderNotFound($this->id . ' fetchOrder() ' . $this->json ($response));
        }
        return $this->parse_order($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'instrument' => $this->market_id($symbol),
            'quantity' => $amount,
            'type' => $type,
            // 'post_only' => 'false' or 'true', https://github.com/ccxt/ccxt/issues/5159
        );
        if ($price !== null) {
            $request['price'] = $price;
        }
        $method = 'privatePost' . $this->capitalize ($side);
        $response = $this->$method (array_merge ($request, $params));
        $order = $this->safe_value($response['result'], 'order');
        if ($order === null) {
            return $response;
        }
        return $this->parse_order($order);
    }

    public function edit_order ($id, $symbol, $type, $side, $amount = null, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderId' => $id,
        );
        if ($amount !== null) {
            $request['quantity'] = $amount;
        }
        if ($price !== null) {
            $request['price'] = $price;
        }
        $response = $this->privatePostEdit (array_merge ($request, $params));
        return $this->parse_order($response['result']['order']);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderId' => $id,
        );
        $response = $this->privatePostCancel (array_merge ($request, $params));
        return $this->parse_order($response['result']['order']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchClosedOrders() requires a `$symbol` argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        $response = $this->privateGetGetopenorders (array_merge ($request, $params));
        return $this->parse_orders($response['result'], $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchClosedOrders() requires a `$symbol` argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        $response = $this->privateGetOrderhistory (array_merge ($request, $params));
        return $this->parse_orders($response['result'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument' => $market['id'],
        );
        if ($limit !== null) {
            $request['count'] = $limit; // default = 20
        }
        $response = $this->privateGetTradehistory (array_merge ($request, $params));
        //
        //     {
        //         "usOut":1559611553394836,
        //         "usIn":1559611553394000,
        //         "usDiff":836,
        //         "testnet":false,
        //         "success":true,
        //         "result" => array (
        //             {
        //                 "quantity":54,
        //                 "amount":540.0,
        //                 "tradeId":23087297,
        //                 "instrument":"BTC-PERPETUAL",
        //                 "timeStamp":1559604178803,
        //                 "tradeSeq":8265011,
        //                 "price":8213.0,
        //                 "side":"sell",
        //                 "orderId":12373631800,
        //                 "matchingId":0,
        //                 "liquidity":"T",
        //                 "fee":0.000049312,
        //                 "feeCurrency":"BTC",
        //                 "tickDirection":3,
        //                 "indexPrice":8251.94,
        //                 "selfTrade":false
        //             }
        //         ),
        //         "message":"",
        //         "has_more":true
        //     }
        //
        $trades = $this->safe_value($response, 'result', array());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = '/' . 'api/' . $this->version . '/' . $api . '/' . $path;
        $url = $this->urls['api'] . $query;
        if ($api === 'public') {
            if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = '_=' . $nonce . '&_ackey=' . $this->apiKey . '&_acsec=' . $this->secret . '&_action=' . $query;
            if ($params) {
                $params = $this->keysort ($params);
                $auth .= '&' . $this->urlencode ($params);
            }
            $hash = $this->hash ($this->encode ($auth), 'sha256', 'base64');
            $signature = $this->apiKey . '.' . $nonce . '.' . $this->decode ($hash);
            $headers = array (
                'x-deribit-sig' => $signature,
            );
            if ($method !== 'GET') {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                $body = $this->urlencode ($params);
            } else if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if (!$response) {
            return; // fallback to default $error handler
        }
        //
        //     array("usOut":1535877098645376,"usIn":1535877098643364,"usDiff":2012,"testnet":false,"success":false,"message":"order_not_found","$error":10004)
        //
        $error = $this->safe_string($response, 'error');
        if (($error !== null) && ($error !== '0')) {
            $feedback = $this->id . ' ' . $body;
            $exceptions = $this->exceptions;
            if (is_array($exceptions) && array_key_exists($error, $exceptions)) {
                throw new $exceptions[$error]($feedback);
            }
            throw new ExchangeError($feedback); // unknown message
        }
    }
}
