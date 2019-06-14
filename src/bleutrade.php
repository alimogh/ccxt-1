<?php

namespace ccxt;

use Exception as Exception; // a common import

class bleutrade extends bittrex {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bleutrade',
            'name' => 'Bleutrade',
            'countries' => array ( 'BR' ), // Brazil
            'rateLimit' => 1000,
            'version' => 'v2',
            'certified' => false,
            'has' => array (
                'CORS' => true,
                'fetchTickers' => true,
                'fetchOrders' => true,
                'fetchClosedOrders' => true,
                'fetchOrderTrades' => true,
            ),
            'timeframes' => array (
                '15m' => '15m',
                '20m' => '20m',
                '30m' => '30m',
                '1h' => '1h',
                '2h' => '2h',
                '3h' => '3h',
                '4h' => '4h',
                '6h' => '6h',
                '8h' => '8h',
                '12h' => '12h',
                '1d' => '1d',
            ),
            'hostname' => 'bleutrade.com',
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/30303000-b602dbe6-976d-11e7-956d-36c5049c01e7.jpg',
                'api' => array (
                    'public' => 'https://{hostname}/api',
                    'account' => 'https://{hostname}/api',
                    'market' => 'https://{hostname}/api',
                ),
                'www' => 'https://bleutrade.com',
                'doc' => 'https://bleutrade.com/help/API',
                'fees' => 'https://bleutrade.com/help/fees_and_deadlines',
            ),
            'api' => array (
                'account' => array (
                    'get' => array (
                        'balance',
                        'balances',
                        'depositaddress',
                        'deposithistory',
                        'order',
                        'orders',
                        'orderhistory',
                        'withdrawhistory',
                        'withdraw',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'candles',
                        'currencies',
                        'markethistory',
                        'markets',
                        'marketsummaries',
                        'marketsummary',
                        'orderbook',
                        'ticker',
                    ),
                ),
            ),
            'fees' => array (
                'funding' => array (
                    'withdraw' => array (
                        'ADC' => 0.1,
                        'BTA' => 0.1,
                        'BITB' => 0.1,
                        'BTC' => 0.001,
                        'BCC' => 0.001,
                        'BTCD' => 0.001,
                        'BTG' => 0.001,
                        'BLK' => 0.1,
                        'CDN' => 0.1,
                        'CLAM' => 0.01,
                        'DASH' => 0.001,
                        'DCR' => 0.05,
                        'DGC' => 0.1,
                        'DP' => 0.1,
                        'DPC' => 0.1,
                        'DOGE' => 10.0,
                        'EFL' => 0.1,
                        'ETH' => 0.01,
                        'EXP' => 0.1,
                        'FJC' => 0.1,
                        'BSTY' => 0.001,
                        'GB' => 0.1,
                        'NLG' => 0.1,
                        'HTML' => 1.0,
                        'LTC' => 0.001,
                        'MONA' => 0.01,
                        'MOON' => 1.0,
                        'NMC' => 0.015,
                        'NEOS' => 0.1,
                        'NVC' => 0.05,
                        'OK' => 0.1,
                        'PPC' => 0.1,
                        'POT' => 0.1,
                        'XPM' => 0.001,
                        'QTUM' => 0.1,
                        'RDD' => 0.1,
                        'SLR' => 0.1,
                        'START' => 0.1,
                        'SLG' => 0.1,
                        'TROLL' => 0.1,
                        'UNO' => 0.01,
                        'VRC' => 0.1,
                        'VTC' => 0.1,
                        'XVP' => 0.1,
                        'WDC' => 0.001,
                        'ZET' => 0.1,
                    ),
                ),
            ),
            'commonCurrencies' => array (
                'EPC' => 'Epacoin',
            ),
            'exceptions' => array (
                'Insufficient funds!' => '\\ccxt\\InsufficientFunds',
                'Invalid Order ID' => '\\ccxt\\InvalidOrder',
                'Invalid apikey or apisecret' => '\\ccxt\\AuthenticationError',
            ),
            'options' => array (
                'parseOrderStatus' => true,
                'disableNonce' => false,
                'symbolSeparator' => '_',
            ),
        ));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'OK' => 'closed',
            'OPEN' => 'open',
            'CANCELED' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // Possible $params
        // orderstatus (ALL, OK, OPEN, CANCELED)
        // ordertype (ALL, BUY, SELL)
        // depth (optional, default is 500, max is 20000)
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        } else {
            $market = null;
        }
        $request = array (
            'market' => 'ALL',
            'orderstatus' => 'ALL',
        );
        $response = $this->accountGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response['result'], $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $response = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($response, 'status', 'closed');
    }

    public function get_order_id_field () {
        return 'orderid';
    }

    public function parse_symbol ($id) {
        list($base, $quote) = explode($this->options['symbolSeparator'], $id);
        $base = $this->common_currency_code($base);
        $quote = $this->common_currency_code($quote);
        return $base . '/' . $quote;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'market' => $this->market_id($symbol),
            'type' => 'ALL',
        );
        if ($limit !== null) {
            $request['depth'] = $limit; // 50
        }
        $response = $this->publicGetOrderbook (array_merge ($request, $params));
        $orderbook = $this->safe_value($response, 'result');
        if (!$orderbook) {
            throw new ExchangeError($this->id . ' publicGetOrderbook() returneded no result ' . $this->json ($response));
        }
        return $this->parse_order_book($orderbook, null, 'buy', 'sell', 'Rate', 'Quantity');
    }

    public function fetch_order_trades ($id, $symbol = null, $since = null, $limit = null, $params = array ()) {
        // Currently we can't set the makerOrTaker field, but if the user knows the order side then it can be
        // determined (if the side of the trade is different to the side of the order, then the trade is maker).
        // Similarly, the correct 'side' for the trade is that of the order.
        // The trade fee can be set by the user, it is always 0.25% and is taken in the quote currency.
        $this->load_markets();
        $request = array (
            'orderid' => $id,
        );
        $response = $this->accountGetOrderhistory (array_merge ($request, $params));
        return $this->parse_trades($response['result'], null, $since, $limit, array (
            'order' => $id,
        ));
    }

    public function fetch_transactions_by_type ($type, $code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $method = ($type === 'deposit') ? 'accountGetDeposithistory' : 'accountGetWithdrawhistory';
        $response = $this->$method ($params);
        $result = $this->parseTransactions ($response['result']);
        return $this->filterByCurrencySinceLimit ($result, $code, $since, $limit);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('deposit', $code, $since, $limit, $params);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('withdrawal', $code, $since, $limit, $params);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1d', $since = null, $limit = null) {
        $timestamp = $this->parse8601 ($ohlcv['TimeStamp'] . '+00:00');
        return [
            $timestamp,
            $ohlcv['Open'],
            $ohlcv['High'],
            $ohlcv['Low'],
            $ohlcv['Close'],
            $ohlcv['Volume'],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '15m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'period' => $this->timeframes[$timeframe],
            'market' => $market['id'],
            'count' => $limit,
        );
        $response = $this->publicGetCandles (array_merge ($request, $params));
        if (is_array($response) && array_key_exists('result', $response)) {
            if ($response['result']) {
                return $this->parse_ohlcvs($response['result'], $market, $timeframe, $since, $limit);
            }
        }
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['TimeStamp'] . '+00:00');
        $side = null;
        if ($trade['OrderType'] === 'BUY') {
            $side = 'buy';
        } else if ($trade['OrderType'] === 'SELL') {
            $side = 'sell';
        }
        $id = $this->safe_string($trade, 'TradeID');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $cost = null;
        $price = $this->safe_float($trade, 'Price');
        $amount = $this->safe_float($trade, 'Quantity');
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $price * $amount;
            }
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function parse_order ($order, $market = null) {
        $side = $this->safe_string_2($order, 'OrderType', 'Type');
        $isBuyOrder = ($side === 'LIMIT_BUY') || ($side === 'BUY');
        $isSellOrder = ($side === 'LIMIT_SELL') || ($side === 'SELL');
        if ($isBuyOrder) {
            $side = 'buy';
        }
        if ($isSellOrder) {
            $side = 'sell';
        }
        // We parse different fields in a very specific $order->
        // Order might well be closed and then canceled.
        $status = null;
        if ((is_array($order) && array_key_exists('Opened', $order)) && $order['Opened']) {
            $status = 'open';
        }
        if ((is_array($order) && array_key_exists('Closed', $order)) && $order['Closed']) {
            $status = 'closed';
        }
        if ((is_array($order) && array_key_exists('CancelInitiated', $order)) && $order['CancelInitiated']) {
            $status = 'canceled';
        }
        if ((is_array($order) && array_key_exists('Status', $order)) && $this->options['parseOrderStatus']) {
            $status = $this->parse_order_status($this->safe_string($order, 'Status'));
        }
        $symbol = null;
        $marketId = $this->safe_string($order, 'Exchange');
        if ($marketId === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        } else {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                $symbol = $this->parse_symbol ($marketId);
            }
        }
        $timestamp = null;
        if (is_array($order) && array_key_exists('Opened', $order)) {
            $timestamp = $this->parse8601 ($order['Opened'] . '+00:00');
        }
        if (is_array($order) && array_key_exists('Created', $order)) {
            $timestamp = $this->parse8601 ($order['Created'] . '+00:00');
        }
        $lastTradeTimestamp = null;
        if ((is_array($order) && array_key_exists('TimeStamp', $order)) && ($order['TimeStamp'] !== null)) {
            $lastTradeTimestamp = $this->parse8601 ($order['TimeStamp'] . '+00:00');
        }
        if ((is_array($order) && array_key_exists('Closed', $order)) && ($order['Closed'] !== null)) {
            $lastTradeTimestamp = $this->parse8601 ($order['Closed'] . '+00:00');
        }
        if ($timestamp === null) {
            $timestamp = $lastTradeTimestamp;
        }
        $fee = null;
        $commission = null;
        if (is_array($order) && array_key_exists('Commission', $order)) {
            $commission = 'Commission';
        } else if (is_array($order) && array_key_exists('CommissionPaid', $order)) {
            $commission = 'CommissionPaid';
        }
        if ($commission) {
            $fee = array (
                'cost' => $this->safe_float($order, $commission),
            );
            if ($market !== null) {
                $fee['currency'] = $market['quote'];
            } else if ($symbol !== null) {
                $currencyIds = explode('/', $symbol);
                $quoteCurrencyId = $currencyIds[1];
                if (is_array($this->currencies_by_id) && array_key_exists($quoteCurrencyId, $this->currencies_by_id)) {
                    $fee['currency'] = $this->currencies_by_id[$quoteCurrencyId]['code'];
                } else {
                    $fee['currency'] = $this->common_currency_code($quoteCurrencyId);
                }
            }
        }
        $price = $this->safe_float($order, 'Price');
        $cost = null;
        $amount = $this->safe_float($order, 'Quantity');
        $remaining = $this->safe_float($order, 'QuantityRemaining');
        $filled = null;
        if ($amount !== null && $remaining !== null) {
            $filled = $amount - $remaining;
        }
        if (!$cost) {
            if ($price && $filled) {
                $cost = $price * $filled;
            }
        }
        if (!$price) {
            if ($cost && $filled) {
                $price = $cost / $filled;
            }
        }
        $average = $this->safe_float($order, 'PricePerUnit');
        $id = $this->safe_string_2($order, 'OrderUuid', 'OrderId');
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => 'limit',
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

    public function parse_transaction ($transaction, $currency = null) {
        //
        //  deposit:
        //
        //     {
        //         Id => '96974373',
        //         Coin => 'DOGE',
        //         Amount => '12.05752192',
        //         TimeStamp => '2017-09-29 08:10:09',
        //         Label => 'DQqSjjhzCm3ozT4vAevMUHgv4vsi9LBkoE',
        //     }
        //
        // withdrawal:
        //
        //     {
        //         Id => '98009125',
        //         Coin => 'DOGE',
        //         Amount => '-483858.64312050',
        //         TimeStamp => '2017-11-22 22:29:05',
        //         Label => '483848.64312050;DJVJZ58tJC8UeUv9Tqcdtn6uhWobouxFLT;10.00000000',
        //         TransactionId => '8563105276cf798385fee7e5a563c620fea639ab132b089ea880d4d1f4309432',
        //     }
        //
        //     {
        //         "Id" => "95820181",
        //         "Coin" => "BTC",
        //         "Amount" => "-0.71300000",
        //         "TimeStamp" => "2017-07-19 17:14:24",
        //         "Label" => "0.71200000;PER9VM2txt4BTdfyWgvv3GziECRdVEPN63;0.00100000",
        //         "TransactionId" => "CANCELED"
        //     }
        //
        $id = $this->safe_string($transaction, 'Id');
        $amount = $this->safe_float($transaction, 'Amount');
        $type = 'deposit';
        if ($amount < 0) {
            $amount = abs ($amount);
            $type = 'withdrawal';
        }
        $currencyId = $this->safe_string($transaction, 'Coin');
        $code = null;
        $currency = $this->safe_value($this->currencies_by_id, $currencyId);
        if ($currency !== null) {
            $code = $currency['code'];
        } else {
            $code = $this->common_currency_code($currencyId);
        }
        $label = $this->safe_string($transaction, 'Label');
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'TimeStamp'));
        $txid = $this->safe_string($transaction, 'TransactionId');
        $address = null;
        $feeCost = null;
        $labelParts = explode(';', $label);
        if (strlen ($labelParts) === 3) {
            $amount = $labelParts[0];
            $address = $labelParts[1];
            $feeCost = $labelParts[2];
        } else {
            $address = $label;
        }
        $fee = null;
        if ($feeCost !== null) {
            $fee = array (
                'currency' => $code,
                'cost' => $feeCost,
            );
        }
        $status = 'ok';
        if ($txid === 'CANCELED') {
            $txid = null;
            $status = 'canceled';
        }
        return array (
            'info' => $transaction,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'id' => $id,
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => null,
            'status' => $status,
            'type' => $type,
            'updated' => null,
            'txid' => $txid,
            'fee' => $fee,
        );
    }
}
