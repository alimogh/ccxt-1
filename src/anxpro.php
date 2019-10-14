<?php

namespace ccxt;

use Exception as Exception; // a common import

class anxpro extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'anxpro',
            'name' => 'ANXPro',
            'countries' => array ( 'JP', 'SG', 'HK', 'NZ' ),
            'rateLimit' => 1500,
            'userAgent' => $this->userAgents['chrome'],
            'has' => array (
                'CORS' => false,
                'fetchCurrencies' => true,
                'fetchOHLCV' => false,
                'fetchTrades' => false,
                'fetchOpenOrders' => true,
                'fetchDepositAddress' => true,
                'fetchTransactions' => true,
                'fetchMyTrades' => true,
                'createDepositAddress' => false,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27765983-fd8595da-5ec9-11e7-82e3-adb3ab8c2612.jpg',
                'api' => array (
                    'public' => 'https://anxpro.com/api/2',
                    'private' => 'https://anxpro.com/api/2',
                    'v3public' => 'https://anxpro.com/api/3',
                    'v3private' => 'https://anxpro.com/api/3',
                ),
                'www' => 'https://anxpro.com',
                'doc' => array (
                    'https://anxv2.docs.apiary.io',
                    'https://anxv3.docs.apiary.io',
                    'https://anxpro.com/pages/api',
                ),
            ),
            'api' => array (
                'v3public' => array (
                    'get' => array (
                        'currencyStatic',
                    ),
                ),
                'v3private' => array (
                    'post' => array (
                        'register/register',
                        'register/verifyRegistration',
                        'register/resendVerification',
                        'register/autoRegister',
                        'account',
                        'subaccount/new',
                        'transaction/list',
                        'order/list',
                        'trade/list',
                        'send',
                        'receive',
                        'receive/create',
                        'batch/new',
                        'batch/add',
                        'batch/list',
                        'batch/info',
                        'batch/closeForSend',
                        'order/new',
                        'order/info',
                        'order/cancel',
                        'retail/quote',
                        'retail/trade',
                        'validateAddress',
                        'address/check',
                        'alert/create',
                        'alert/delete',
                        'alert/list',
                        'kyc/personal',
                        'kyc/document',
                        'kyc/status',
                        'kyc/verifyCode',
                        'news/list',
                        'press/list',
                        'announcements/list',
                        'apiDoc/list',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        '{currency_pair}/money/ticker',
                        '{currency_pair}/money/depth/full',
                        '{currency_pair}/money/trade/fetch', // disabled by ANXPro
                    ),
                ),
                'private' => array (
                    'post' => array (
                        '{currency_pair}/money/order/add',
                        '{currency_pair}/money/order/cancel',
                        '{currency_pair}/money/order/quote',
                        '{currency_pair}/money/order/result',
                        '{currency_pair}/money/orders',
                        'money/{currency}/address',
                        'money/{currency}/send_simple',
                        'money/info',
                        'money/trade/list',
                        'money/wallet/history',
                    ),
                ),
            ),
            'httpExceptions' => array (
                '403' => '\\ccxt\\AuthenticationError',
            ),
            'exceptions' => array (
                'exact' => array (
                    // v2
                    'Insufficient Funds' => '\\ccxt\\InsufficientFunds',
                    'Trade value too small' => '\\ccxt\\InvalidOrder',
                    'The currency pair is not supported' => '\\ccxt\\BadRequest',
                    'Order amount is too low' => '\\ccxt\\InvalidOrder',
                    'Order amount is too high' => '\\ccxt\\InvalidOrder',
                    'order rate is too low' => '\\ccxt\\InvalidOrder',
                    'order rate is too high' => '\\ccxt\\InvalidOrder',
                    'Too many open orders' => '\\ccxt\\InvalidOrder',
                    'Unexpected error' => '\\ccxt\\ExchangeError',
                    'Order Engine is offline' => '\\ccxt\\ExchangeNotAvailable',
                    'No executed order with that identifer found' => '\\ccxt\\OrderNotFound',
                    'Unknown server error, please contact support.' => '\\ccxt\\ExchangeError',
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.1 / 100,
                    'taker' => 0.2 / 100,
                ),
            ),
            'options' => array (
                'fetchMyTradesMethod' => 'private_post_money_trade_list', // or 'v3private_post_trade_list'
            ),
        ));
    }

    public function fetch_transactions ($code = null, $since = null, $limit = null, $params = array ()) {
        // todo => migrate this to fetchLedger
        $this->load_markets();
        $request = array();
        if ($since !== null) {
            $request['from'] = $since;
        }
        if ($limit !== null) {
            $request['max'] = $limit;
        }
        $currency = ($code === null) ? null : $this->currency ($code);
        if ($currency !== null) {
            $request['ccy'] = $currency['id'];
        }
        $response = $this->v3privatePostTransactionList (array_merge ($request, $params));
        //
        //     {
        //         $transactions => array (
        //             array (
        //                 transactionClass => 'COIN',
        //                 uuid => '7896857b-2ed6-4c62-ba4c-619837438d9c',
        //                 userUuid => '82027ee9-cb59-4f29-80d6-f7e793f39ad4',
        //                 amount => -17865.72689976,
        //                 fee => 1,
        //                 balanceBefore => 17865.72689976,
        //                 balanceAfter => 17865.72689976,
        //                 ccy => 'XRP',
        //                 transactionState => 'PROCESSED',
        //                 transactionType => 'WITHDRAWAL',
        //                 received => '1551357946000',
        //                 processed => '1551357946000',
        //                 timestampMillis => '1557441435932',
        //                 displayTitle => 'Coin Withdrawal',
        //                 displayDescription => 'Withdraw to => rw2ciyaNshpHe7bCHo4bRWq6pqqynnWKQg?dt=3750180345',
        //                 coinAddress => 'rw2ciyaNshpHe7bCHo4bRWq6pqqynnWKQg?dt=3750180345',
        //                 coinTransactionId => '68444611753E9D8F5C33DCBBF43F01391070F79CAFCF7625397D1CEFA519064A',
        //                 subAccount => array (
        //                     Object
        //                 )
        //             ),
        //             {
        //                 transactionClass => 'FILL',
        //                 uuid => 'a5ae54de-c14a-4ef8-842d-56000c9dc7ab',
        //                 userUuid => '82027ee9-cb59-4f29-80d6-f7e793f39ad4',
        //                 amount => 0.09006364,
        //                 fee => 0.00018013,
        //                 balanceBefore => 0.3190001,
        //                 balanceAfter => 0.40888361,
        //                 ccy => 'BTC',
        //                 transactionState => 'PROCESSED',
        //                 transactionType => 'FILL_CREDIT',
        //                 received => '1551357057000',
        //                 processed => '1551357057000',
        //                 timestampMillis => '1557441435956',
        //                 displayTitle => 'Order Fill',
        //                 displayDescription => 'Buy BTC @ 3008.53930 EUR/BTC'
        //             }
        //         ),
        //         count => ...,
        //         timestamp => '1557441435971',
        //         resultCode => 'OK'
        //     }
        //
        $transactions = $this->safe_value($response, 'transactions', array());
        $grouped = $this->group_by($transactions, 'transactionType', array());
        $depositsAndWithdrawals = $this->array_concat($this->safe_value($grouped, 'DEPOSIT', array()), $this->safe_value($grouped, 'WITHDRAWAL', array()));
        return $this->parse_transactions($depositsAndWithdrawals, $currency, $since, $limit);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // withdrawal
        //
        //     {
        //         transactionClass => 'COIN',
        //         uuid => 'bff91938-4dad-4c48-9db6-468324ce96c1',
        //         userUuid => '82027ee9-cb59-4f29-80d6-f7e793f39ad4',
        //         $amount => -0.40888361,
        //         fee => 0.002,
        //         balanceBefore => 0.40888361,
        //         balanceAfter => 0.40888361,
        //         ccy => 'BTC',
        //         $transactionState => 'PROCESSED',
        //         $transactionType => 'WITHDRAWAL',
        //         received => '1551357156000',
        //         processed => '1551357156000',
        //         timestampMillis => '1557441846213',
        //         displayTitle => 'Coin Withdrawal',
        //         $displayDescription => 'Withdraw to => 1AHnhqbvbYx3rnZx8uC7NbFZaTe4tafFHX',
        //         coinAddress => '1AHnhqbvbYx3rnZx8uC7NbFZaTe4tafFHX',
        //         coinTransactionId:
        //         'ab80abcb62bf6261ebc827c73dd59a4ce15d740b6ba734af6542f43b6485b923',
        //         subAccount => {
        //             uuid => '652e1add-0d0b-462c-a03c-d6197c825c1a',
        //             name => 'DEFAULT'
        //         }
        //     }
        //
        // deposit
        //
        //     {
        //         "transactionClass" => "COIN",
        //         "uuid" => "eb65576f-c1a8-423c-8e2f-fa50109b2eab",
        //         "userUuid" => "82027ee9-cb59-4f29-80d6-f7e793f39ad4",
        //         "$amount" => 3.99287184,
        //         "fee" => 0,
        //         "balanceBefore" => 8.39666034,
        //         "balanceAfter" => 12.38953218,
        //         "ccy" => "ETH",
        //         "$transactionState" => "PROCESSED",
        //         "$transactionType" => "DEPOSIT",
        //         "received" => "1529420056000",
        //         "processed" => "1529420766000",
        //         "timestampMillis" => "1557442743854",
        //         "displayTitle" => "Coin Deposit",
        //         "$displayDescription" => "Deposit to => 0xf123aa44fadea913a7da99cc2ee202db684ce0e3",
        //         "coinTransactionId" => "0x33a3e5ea7c034dc5324a88aa313962df0a5d571ab4bcc3cb00b876b1bdfc54f7",
        //         "coinConfirmations" => 51,
        //         "coinConfirmationsRequired" => 45,
        //         "subAccount" => array("uuid" => "aba1de05-c7c6-49d7-84ab-a6aca0e827b6", "name" => "DEFAULT")
        //     }
        //
        $timestamp = $this->safe_integer($transaction, 'received');
        $updated = $this->safe_integer($transaction, 'processed');
        $transactionType = $this->safe_string($transaction, 'transactionType');
        $type = null;
        $amount = $this->safe_float($transaction, 'amount');
        $address = $this->safe_string($transaction, 'coinAddress');
        $tag = null;
        if ($transactionType === 'WITHDRAWAL') {
            $type = 'withdrawal';
            $amount = -$amount;
            if ($address) {
                //  xrp => "coinAddress" => "rw2ciyaNshpHe7bCHo4bRWq6pqqynnWKQg?dt=3750180345",
                if (mb_strpos($address, '?dt=') !== false) {
                    $parts = explode('?dt=', $address);
                    $address = $parts[0];
                    $tag = $parts[1];
                }
            }
        } else if ($transactionType === 'DEPOSIT') {
            if (!$address) {
                $displayDescription = $this->safe_string($transaction, 'displayDescription');
                $addressText = str_replace('Deposit to => ', '', $displayDescription);
                if (strlen ($addressText) > 0) {
                    //  eth => "$displayDescription" => "Deposit to => 0xf123aa44fadea913a7da99cc2ee202db684ce0e3",
                    //  xrp => "$displayDescription" => "Deposit to => rUjxty1WWLwX1evhKf3C2XNZDMcXEZ9ToJ?dt=504562345",
                    if (mb_strpos($addressText, '?dt=') !== false) {
                        $parts = explode('?dt=', $addressText);
                        $address = $parts[0];
                        $tag = $parts[1];
                    } else {
                        $address = $addressText;
                    }
                }
            }
            $type = 'deposit';
        }
        $currencyId = $this->safe_string($transaction, 'ccy');
        $code = $this->safe_currency_code($currencyId);
        $transactionState = $this->safe_string($transaction, 'transactionState');
        $status = $this->parse_transaction_status ($transactionState);
        $feeCost = $this->safe_float($transaction, 'fee');
        $netAmount = $amount - $feeCost;
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'id' => $this->safe_string($transaction, 'uuid'),
            'currency' => $code,
            'amount' => $netAmount,
            'address' => $address,
            'tag' => $tag,
            'status' => $status,
            'type' => $type,
            'updated' => $updated,
            'txid' => $this->safe_string($transaction, 'coinTransactionId'),
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $code,
            ),
            'info' => $transaction,
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'PROCESSED' => 'ok',
            'REVERSED' => 'canceled',
            'CANCELLED_INSUFFICIENT_FUNDS' => 'canceled',
            'CANCELLED_LIMIT_BREACH' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        //
        // v2
        //
        //     {
        //         result => 'success',
        //         data => array (
        //             array (
        //                 tradeId => 'c2ed821d-717a-4b7e-beb0-a9ba60e8f5a0',
        //                 orderId => '5a65ae21-c7a8-4009-b3af-306c2ad21a02',
        //                 timestamp => '1551357057000',
        //                 tradedCurrencyFillAmount => '0.09006364',
        //                 settlementCurrencyFillAmount => '270.96',
        //                 settlementCurrencyFillAmountUnrounded => '270.96000000',
        //                 price => '3008.53930',
        //                 ccyPair => 'BTCEUR',
        //                 side => 'BUY' // missing in v3
        //             ),
        //             array (
        //                 tradeId => 'fc0d3a9d-8b0b-4dff-b2e9-edd160785210',
        //                 orderId => '8161ae6e-251a-4eed-a56f-d3d6555730c1',
        //                 timestamp => '1551357033000',
        //                 tradedCurrencyFillAmount => '0.06521746',
        //                 settlementCurrencyFillAmount => '224.09',
        //                 settlementCurrencyFillAmountUnrounded => '224.09000000',
        //                 price => '3436.04305',
        //                 ccyPair => 'BTCUSD',
        //                 side => 'BUY' // missing in v3
        //             ),
        //         )
        //     }
        //
        // v3
        //
        //     {
        //         $trades => array (
        //             array (
        //                 tradeId => 'c2ed821d-717a-4b7e-beb0-a9ba60e8f5a0',
        //                 orderId => '5a65ae21-c7a8-4009-b3af-306c2ad21a02',
        //                 timestamp => '1551357057000',
        //                 tradedCurrencyFillAmount => '0.09006364',
        //                 settlementCurrencyFillAmount => '270.96',
        //                 settlementCurrencyFillAmountUnrounded => '270.96000000',
        //                 price => '3008.53930',
        //                 ccyPair => 'BTCEUR'
        //             ),
        //             array (
        //                 tradeId => 'fc0d3a9d-8b0b-4dff-b2e9-edd160785210',
        //                 orderId => '8161ae6e-251a-4eed-a56f-d3d6555730c1',
        //                 timestamp => '1551357033000',
        //                 tradedCurrencyFillAmount => '0.06521746',
        //                 settlementCurrencyFillAmount => '224.09',
        //                 settlementCurrencyFillAmountUnrounded => '224.09000000',
        //                 price => '3436.04305',
        //                 ccyPair => 'BTCUSD'
        //             ),
        //         ),
        //         count => 3,
        //         timestamp => '1557438456732',
        //         resultCode => 'OK'
        //     }
        //
        $request = array();
        if ($limit !== null) {
            $request['max'] = $limit;
        }
        $method = $this->safe_string($this->options, 'fetchMyTradesMethod', 'private_post_money_trade_list');
        $response = $this->$method (array_merge ($request, $params));
        $trades = $this->safe_value_2($response, 'trades', 'data', array());
        $market = ($symbol === null) ? null : $this->market ($symbol);
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // v2
        //
        //     {
        //         tradeId => 'fc0d3a9d-8b0b-4dff-b2e9-edd160785210',
        //         $orderId => '8161ae6e-251a-4eed-a56f-d3d6555730c1',
        //         $timestamp => '1551357033000',
        //         tradedCurrencyFillAmount => '0.06521746',
        //         settlementCurrencyFillAmount => '224.09',
        //         settlementCurrencyFillAmountUnrounded => '224.09000000',
        //         $price => '3436.04305',
        //         ccyPair => 'BTCUSD',
        //         $side => 'BUY', // missing in v3
        //     }
        //
        // v3
        //
        //     {
        //         tradeId => 'fc0d3a9d-8b0b-4dff-b2e9-edd160785210',
        //         $orderId => '8161ae6e-251a-4eed-a56f-d3d6555730c1',
        //         $timestamp => '1551357033000',
        //         tradedCurrencyFillAmount => '0.06521746',
        //         settlementCurrencyFillAmount => '224.09',
        //         settlementCurrencyFillAmountUnrounded => '224.09000000',
        //         $price => '3436.04305',
        //         ccyPair => 'BTCUSD'
        //     }
        //
        $id = $this->safe_string($trade, 'tradeId');
        $orderId = $this->safe_string($trade, 'orderId');
        $timestamp = $this->safe_integer($trade, 'timestamp');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'tradedCurrencyFillAmount');
        $cost = $this->safe_float($trade, 'settlementCurrencyFillAmount');
        $side = $this->safe_string_lower($trade, 'side');
        return array (
            'id' => $id,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $this->find_symbol($this->safe_string($trade, 'ccyPair')),
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->v3publicGetCurrencyStatic ($params);
        //
        //   {
        //     "$currencyStatic" => array (
        //       "$currencies" => array (
        //         "HKD" => array (
        //           "decimals" => 2,
        //           "minOrderSize" => 1.00000000,
        //           "maxOrderSize" => 10000000000.00000000,
        //           "displayDenominator" => 1,
        //           "summaryDecimals" => 0,
        //           "displayUnit" => "HKD",
        //           "symbol" => "$",
        //           "$type" => "FIAT",
        //           "$engineSettings" => array (
        //             "$depositsEnabled" => false,
        //             "$withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 1.00000000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 36000.00000000,
        //           "maxMarketOrderSize" => 36000.00000000,
        //           "assetDivisibility" => 0
        //         ),
        //         "ETH" => array (
        //           "decimals" => 8,
        //           "minOrderSize" => 0.00010000,
        //           "maxOrderSize" => 1000000000.00000000,
        //           "$type" => "CRYPTO",
        //           "confirmationThresholds" => array (
        //             array( "confosRequired" => 30, "threshold" => 0.50000000 ),
        //             array( "confosRequired" => 45, "threshold" => 10.00000000 ),
        //             array( "confosRequired" => 70 )
        //           ),
        //           "networkFee" => 0.00500000,
        //           "$engineSettings" => array (
        //             "$depositsEnabled" => true,
        //             "$withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 0.00010000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderSize" => 1000000000.00000000,
        //           "digitalCurrencyType" => "ETHEREUM",
        //           "assetDivisibility" => 0,
        //           "assetIcon" => "/images/currencies/crypto/ETH.svg"
        //         ),
        //       ),
        //       "currencyPairs" => array (
        //         "ETHUSD" => array (
        //           "priceDecimals" => 5,
        //           "$engineSettings" => array (
        //             "tradingEnabled" => true,
        //             "$displayEnabled" => true,
        //             "cancelOnly" => true,
        //             "verifyRequired" => false,
        //             "restrictedBuy" => false,
        //             "restrictedSell" => false
        //           ),
        //           "minOrderRate" => 10.00000000,
        //           "maxOrderRate" => 10000.00000000,
        //           "displayPriceDecimals" => 5,
        //           "tradedCcy" => "ETH",
        //           "settlementCcy" => "USD",
        //           "preferredMarket" => "ANX",
        //           "chartEnabled" => true,
        //           "simpleTradeEnabled" => false
        //         ),
        //       ),
        //     ),
        //     "timestamp" => "1549840691039",
        //     "resultCode" => "OK"
        //   }
        //
        $currencyStatic = $this->safe_value($response, 'currencyStatic', array());
        $currencies = $this->safe_value($currencyStatic, 'currencies', array());
        $result = array();
        $ids = is_array($currencies) ? array_keys($currencies) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $currency = $currencies[$id];
            $code = $this->safe_currency_code($id);
            $engineSettings = $this->safe_value($currency, 'engineSettings');
            $depositsEnabled = $this->safe_value($engineSettings, 'depositsEnabled');
            $withdrawalsEnabled = $this->safe_value($engineSettings, 'withdrawalsEnabled');
            $displayEnabled = $this->safe_value($engineSettings, 'displayEnabled');
            $active = $depositsEnabled && $withdrawalsEnabled && $displayEnabled;
            $precision = $this->safe_integer($currency, 'decimals');
            $fee = $this->safe_float($currency, 'networkFee');
            $type = $this->safe_string_lower($currency, 'type');
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $code,
                'type' => $type,
                'active' => $active,
                'precision' => $precision,
                'fee' => $fee,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($currency, 'minOrderSize'),
                        'max' => $this->safe_float($currency, 'maxOrderSize'),
                    ),
                    'price' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => $this->safe_float($currency, 'minOrderValue'),
                        'max' => $this->safe_float($currency, 'maxOrderValue'),
                    ),
                    'withdraw' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->v3publicGetCurrencyStatic ($params);
        //
        //   {
        //     "$currencyStatic" => array (
        //       "$currencies" => array (
        //         "HKD" => array (
        //           "decimals" => 2,
        //           "minOrderSize" => 1.00000000,
        //           "maxOrderSize" => 10000000000.00000000,
        //           "displayDenominator" => 1,
        //           "summaryDecimals" => 0,
        //           "displayUnit" => "HKD",
        //           "$symbol" => "$",
        //           "type" => "FIAT",
        //           "$engineSettings" => array (
        //             "depositsEnabled" => false,
        //             "withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 1.00000000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 36000.00000000,
        //           "maxMarketOrderSize" => 36000.00000000,
        //           "assetDivisibility" => 0
        //         ),
        //         "ETH" => array (
        //           "decimals" => 8,
        //           "minOrderSize" => 0.00010000,
        //           "maxOrderSize" => 1000000000.00000000,
        //           "type" => "CRYPTO",
        //           "confirmationThresholds" => array (
        //             array( "confosRequired" => 30, "threshold" => 0.50000000 ),
        //             array( "confosRequired" => 45, "threshold" => 10.00000000 ),
        //             array( "confosRequired" => 70 )
        //           ),
        //           "networkFee" => 0.00500000,
        //           "$engineSettings" => array (
        //             "depositsEnabled" => true,
        //             "withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 0.00010000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderSize" => 1000000000.00000000,
        //           "digitalCurrencyType" => "ETHEREUM",
        //           "assetDivisibility" => 0,
        //           "assetIcon" => "/images/currencies/crypto/ETH.svg"
        //         ),
        //       ),
        //       "$currencyPairs" => array (
        //         "ETHUSD" => array (
        //           "priceDecimals" => 5,
        //           "$engineSettings" => array (
        //             "$tradingEnabled" => true,
        //             "$displayEnabled" => true,
        //             "cancelOnly" => true,
        //             "verifyRequired" => false,
        //             "restrictedBuy" => false,
        //             "restrictedSell" => false
        //           ),
        //           "minOrderRate" => 10.00000000,
        //           "maxOrderRate" => 10000.00000000,
        //           "displayPriceDecimals" => 5,
        //           "tradedCcy" => "ETH",
        //           "settlementCcy" => "USD",
        //           "preferredMarket" => "ANX",
        //           "chartEnabled" => true,
        //           "simpleTradeEnabled" => false
        //         ),
        //       ),
        //     ),
        //     "timestamp" => "1549840691039",
        //     "resultCode" => "OK"
        //   }
        //
        $currencyStatic = $this->safe_value($response, 'currencyStatic', array());
        $currencies = $this->safe_value($currencyStatic, 'currencies', array());
        $currencyPairs = $this->safe_value($currencyStatic, 'currencyPairs', array());
        $result = array();
        $ids = is_array($currencyPairs) ? array_keys($currencyPairs) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $currencyPairs[$id];
            //
            //     "ETHUSD" => array (
            //       "priceDecimals" => 5,
            //       "$engineSettings" => array (
            //         "$tradingEnabled" => true,
            //         "$displayEnabled" => true,
            //         "cancelOnly" => true,
            //         "verifyRequired" => false,
            //         "restrictedBuy" => false,
            //         "restrictedSell" => false
            //       ),
            //       "minOrderRate" => 10.00000000,
            //       "maxOrderRate" => 10000.00000000,
            //       "displayPriceDecimals" => 5,
            //       "tradedCcy" => "ETH",
            //       "settlementCcy" => "USD",
            //       "preferredMarket" => "ANX",
            //       "chartEnabled" => true,
            //       "simpleTradeEnabled" => false
            //     ),
            //
            $baseId = $this->safe_string($market, 'tradedCcy');
            $quoteId = $this->safe_string($market, 'settlementCcy');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $baseCurrency = $this->safe_value($currencies, $baseId, array());
            $quoteCurrency = $this->safe_value($currencies, $quoteId, array());
            $precision = array (
                'price' => $this->safe_integer($market, 'priceDecimals'),
                'amount' => $this->safe_integer($baseCurrency, 'decimals'),
            );
            $engineSettings = $this->safe_value($market, 'engineSettings');
            $displayEnabled = $this->safe_value($engineSettings, 'displayEnabled');
            $tradingEnabled = $this->safe_value($engineSettings, 'tradingEnabled');
            $active = $displayEnabled && $tradingEnabled;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'precision' => $precision,
                'active' => $active,
                'limits' => array (
                    'price' => array (
                        'min' => $this->safe_float($market, 'minOrderRate'),
                        'max' => $this->safe_float($market, 'maxOrderRate'),
                    ),
                    'amount' => array (
                        'min' => $this->safe_float($baseCurrency, 'minOrderSize'),
                        'max' => $this->safe_float($baseCurrency, 'maxOrderSize'),
                    ),
                    'cost' => array (
                        'min' => $this->safe_float($quoteCurrency, 'minOrderValue'),
                        'max' => $this->safe_float($quoteCurrency, 'maxOrderValue'),
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostMoneyInfo ($params);
        $balance = $this->safe_value($response, 'data', array());
        $wallets = $this->safe_value($balance, 'Wallets', array());
        $currencyIds = is_array($wallets) ? array_keys($wallets) : array();
        $result = array( 'info' => $balance );
        for ($c = 0; $c < count ($currencyIds); $c++) {
            $currencyId = $currencyIds[$c];
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account ();
            $wallet = $this->safe_value($wallets, $currencyId);
            $account['free'] = $this->safe_float($wallet['Available_Balance'], 'value');
            $account['total'] = $this->safe_float($wallet['Balance'], 'value');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'currency_pair' => $this->market_id($symbol),
        );
        $response = $this->publicGetCurrencyPairMoneyDepthFull (array_merge ($request, $params));
        $orderbook = $this->safe_value($response, 'data', array());
        $timestamp = $this->safe_integer_product($orderbook, 'dataUpdateTime', 0.001);
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $request = array (
            'currency_pair' => $this->market_id($symbol),
        );
        $response = $this->publicGetCurrencyPairMoneyTicker (array_merge ($request, $params));
        $ticker = $this->safe_value($response, 'data', array());
        $timestamp = $this->safe_integer_product($ticker, 'dataUpdateTime', 0.001);
        $bid = $this->safe_float($ticker['buy'], 'value');
        $ask = $this->safe_float($ticker['sell'], 'value');
        $baseVolume = $this->safe_float($ticker['vol'], 'value');
        $last = $this->safe_float($ticker['last'], 'value');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker['high'], 'value'),
            'low' => $this->safe_float($ticker['low'], 'value'),
            'bid' => $bid,
            'bidVolume' => null,
            'ask' => $ask,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => $this->safe_float($ticker['avg'], 'value'),
            'baseVolume' => $baseVolume,
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' switched off the trades endpoint, see their docs at https://docs.anxv2.apiary.io');
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        if ($limit !== null) {
            $request['max'] = $limit;
        }
        $response = $this->v3privatePostOrderList (array_merge ($request, $params));
        $orders = $this->safe_value($response, 'orders', array());
        $market = ($symbol === null) ? null : $this->market ($symbol);
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currency_pair' => $market['id'],
        );
        // ANXPro will return all $symbol pairs regardless of what is specified in $request
        $response = $this->privatePostCurrencyPairMoneyOrders (array_merge ($request, $params));
        //
        //     {
        //         "result" => "success",
        //         "data" => array (
        //             array (
        //                 "oid" => "e74305c7-c424-4fbc-a8a2-b41d8329deb0",
        //                 "currency" => "HKD",
        //                 "item" => "BTC",
        //                 "type" => "offer",
        //                 "amount" => array (
        //                     "currency" => "BTC",
        //                     "display" => "10.00000000 BTC",
        //                     "display_short" => "10.00 BTC",
        //                     "value" => "10.00000000",
        //                     "value_int" => "1000000000"
        //                 ),
        //                 "effective_amount" => array (
        //                     "currency" => "BTC",
        //                     "display" => "10.00000000 BTC",
        //                     "display_short" => "10.00 BTC",
        //                     "value" => "10.00000000",
        //                     "value_int" => "1000000000"
        //                 ),
        //                 "price" => array (
        //                     "currency" => "HKD",
        //                     "display" => "412.34567 HKD",
        //                     "display_short" => "412.35 HKD",
        //                     "value" => "412.34567",
        //                     "value_int" => "41234567"
        //                 ),
        //                 "status" => "open",
        //                 "date" => 1393411075000,
        //                 "priority" => 1393411075000000,
        //                 "actions" => array()
        //             ),
        //            ...
        //         )
        //     }
        //
        return $this->parse_orders($this->safe_value($response, 'data', array()), $market, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        if (is_array($order) && array_key_exists('orderId', $order)) {
            return $this->parse_order_v3 ($order, $market);
        } else {
            return $this->parse_order_v2 ($order, $market);
        }
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'ACTIVE' => 'open',
            'FULL_FILL' => 'closed',
            'CANCEL' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order_v3 ($order, $market = null) {
        //
        // v3
        //
        //     {
        //         orderType => 'LIMIT',
        //         $tradedCurrency => 'XRP',
        //         $settlementCurrency => 'BTC',
        //         tradedCurrencyAmount => '400.00000000',
        //         $buyTradedCurrency => true,
        //         limitPriceInSettlementCurrency => '0.00007129',
        //         $timestamp => '1522547850000',
        //         orderId => '62a8be4d-73c6-4469-90cd-28b4726effe0',
        //         tradedCurrencyAmountOutstanding => '0.00000000',
        //         $orderStatus => 'FULL_FILL',
        //         $executedAverageRate => '0.00007127',
        //         $trades => array (
        //             array (
        //                 tradeId => 'fe16b796-df57-41a2-b6d9-3489f189749e',
        //                 orderId => '62a8be4d-73c6-4469-90cd-28b4726effe0',
        //                 $timestamp => '1522547850000',
        //                 tradedCurrencyFillAmount => '107.91298639',
        //                 settlementCurrencyFillAmount => '0.00768772',
        //                 settlementCurrencyFillAmountUnrounded => '0.00768772',
        //                 $price => '0.00007124',
        //                 ccyPair => 'XRPBTC'
        //             ),
        //             {
        //                 tradeId => 'e2962f67-c094-4243-8b88-0cdc70a1b1c7',
        //                 orderId => '62a8be4d-73c6-4469-90cd-28b4726effe0',
        //                 $timestamp => '1522547851000',
        //                 tradedCurrencyFillAmount => '292.08701361',
        //                 settlementCurrencyFillAmount => '0.02082288',
        //                 settlementCurrencyFillAmountUnrounded => '0.02082288',
        //                 $price => '0.00007129',
        //                 ccyPair => 'XRPBTC'
        //             }
        //         )
        //     }
        //
        $tradedCurrency = $this->safe_string($order, 'tradedCurrency');
        $orderStatus = $this->safe_string($order, 'orderStatus');
        $status = $this->parse_order_status($orderStatus);
        $settlementCurrency = $this->safe_string($order, 'settlementCurrency');
        $symbol = $this->find_symbol($tradedCurrency . '/' . $settlementCurrency);
        $buyTradedCurrency = $this->safe_string($order, 'buyTradedCurrency');
        $side = ($buyTradedCurrency === 'true') ? 'buy' : 'sell';
        $timestamp = $this->safe_integer($order, 'timestamp');
        $lastTradeTimestamp = null;
        $trades = array();
        $filled = 0;
        $type = $this->safe_string_lower($order, 'orderType');
        for ($i = 0; $i < count ($order['trades']); $i++) {
            $trade = $order['trades'][$i];
            $tradeTimestamp = $this->safe_integer($trade, 'timestamp');
            if (!$lastTradeTimestamp || $lastTradeTimestamp < $tradeTimestamp) {
                $lastTradeTimestamp = $tradeTimestamp;
            }
            $parsedTrade = array_merge ($this->parse_trade($trade), array( 'side' => $side, 'type' => $type ));
            $trades[] = $parsedTrade;
            $filled = $this->sum ($filled, $parsedTrade['amount']);
        }
        $price = $this->safe_float($order, 'limitPriceInSettlementCurrency');
        $executedAverageRate = $this->safe_float($order, 'executedAverageRate');
        $remaining = ($type === 'market') ? 0 : $this->safe_float($order, 'tradedCurrencyAmountOutstanding');
        $amount = $this->safe_float($order, 'tradedCurrencyAmount');
        if (!$amount) {
            $settlementCurrencyAmount = $this->safe_float($order, 'settlementCurrencyAmount');
            $amount = $settlementCurrencyAmount / $executedAverageRate;
        }
        $cost = $executedAverageRate * $filled;
        return array (
            'id' => $this->safe_string($order, 'orderId'),
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'status' => $status,
            'fee' => null,
            'trades' => $trades,
            'info' => $order,
        );
    }

    public function parse_order_v2 ($order, $market = null) {
        //
        // v2
        //
        //     {
        //         "oid" => "e74305c7-c424-4fbc-a8a2-b41d8329deb0",
        //         "currency" => "HKD",
        //         "item" => "BTC",
        //         "type" => "offer",  <-- bid/offer
        //         "$amount" => array (
        //             "currency" => "BTC",
        //             "display" => "10.00000000 BTC",
        //             "display_short" => "10.00 BTC",
        //             "value" => "10.00000000",
        //             "value_int" => "1000000000"
        //         ),
        //         "effective_amount" => array (
        //             "currency" => "BTC",
        //             "display" => "10.00000000 BTC",
        //             "display_short" => "10.00 BTC",
        //             "value" => "10.00000000",
        //             "value_int" => "1000000000"
        //         ),
        //         "$price" => array (
        //             "currency" => "HKD",
        //             "display" => "412.34567 HKD",
        //             "display_short" => "412.35 HKD",
        //             "value" => "412.34567",
        //             "value_int" => "41234567"
        //         ),
        //         "$status" => "open",
        //         "date" => 1393411075000,
        //         "priority" => 1393411075000000,
        //         "actions" => array()
        //     }
        //
        $id = $this->safe_string($order, 'oid');
        $status = $this->safe_string($order, 'status');
        $timestamp = $this->safe_integer($order, 'date');
        $baseId = $this->safe_string($order, 'item');
        $quoteId = $this->safe_string($order, 'currency');
        $marketId = $baseId . '/' . $quoteId;
        $market = $this->safe_value($this->markets_by_id, $marketId);
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $amount_info = $this->safe_value($order, 'amount', array());
        $effective_info = $this->safe_value($order, 'effective_amount', array());
        $price_info = $this->safe_value($order, 'price', array());
        $remaining = $this->safe_float($effective_info, 'value');
        $amount = $this->safe_float($amount_info, 'volume');
        $price = $this->safe_float($price_info, 'value');
        $filled = null;
        $cost = null;
        if ($amount !== null) {
            if ($remaining !== null) {
                $filled = $amount - $remaining;
                $cost = $price * $filled;
            }
        }
        $orderType = 'limit';
        $side = $this->safe_string($order, 'type');
        if ($side === 'offer') {
            $side = 'sell';
        } else {
            $side = 'buy';
        }
        $fee = null;
        $trades = null; // todo parse $trades
        $lastTradeTimestamp = null;
        return array (
            'info' => $order,
            'id' => $id,
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'type' => $orderType,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'status' => $status,
            'fee' => $fee,
            'trades' => $trades,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $amountMultiplier = pow(10, $market['precision']['amount']);
        $request = array (
            'currency_pair' => $market['id'],
            'amount_int' => intval ($amount * $amountMultiplier), // 10^8
        );
        if ($type === 'limit') {
            $priceMultiplier = pow(10, $market['precision']['price']);
            $request['price_int'] = intval ($price * $priceMultiplier); // 10^5 or 10^8
        }
        $request['type'] = ($side === 'buy') ? 'bid' : 'ask';
        $response = $this->privatePostCurrencyPairMoneyOrderAdd (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['data'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostCurrencyPairMoneyOrderCancel (array( 'oid' => $id ));
    }

    public function get_amount_multiplier ($code) {
        $multipliers = array (
            'BTC' => 100000000,
            'LTC' => 100000000,
            'STR' => 100000000,
            'XRP' => 100000000,
            'DOGE' => 100000000,
        );
        $defaultValue = 100;
        return $this->safe_integer($multipliers, $code, $defaultValue);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $multiplier = $this->get_amount_multiplier ($code);
        $request = array (
            'currency' => $currency,
            'amount_int' => intval ($amount * $multiplier),
            'address' => $address,
        );
        if ($tag !== null) {
            $request['destinationTag'] = $tag;
        }
        $response = $this->privatePostMoneyCurrencySendSimple (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['data']['transactionId'],
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privatePostMoneyCurrencyAddress (array_merge ($request, $params));
        $data = $this->safe_value($response, 'data', array());
        $address = $this->safe_string($data, 'addr');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'][$api] . '/' . $request;
        if ($api === 'public' || $api === 'v3public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $auth = null;
            $contentType = null;
            if ($api === 'v3private') {
                $body = $this->json (array_merge (array( 'tonce' => $nonce * 1000 ), $query));
                $path = str_replace('https://anxpro.com/', '', $url);
                $auth = $path . '\0' . $body;
                $contentType = 'application/json';
            } else {
                $body = $this->urlencode (array_merge (array( 'nonce' => $nonce ), $query));
                // eslint-disable-next-line quotes
                $auth = $request . "\0" . $body;
                $contentType = 'application/x-www-form-urlencoded';
            }
            $secret = base64_decode ($this->secret);
            $signature = $this->hmac ($this->encode ($auth), $secret, 'sha512', 'base64');
            $headers = array (
                'Content-Type' => $contentType,
                'Rest-Key' => $this->apiKey,
                'Rest-Sign' => $this->decode ($signature),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null || $response === '') {
            return;
        }
        $result = $this->safe_string($response, 'result');
        $code = $this->safe_string($response, 'resultCode');
        if ((($result !== null) && ($result !== 'success')) || (($code !== null) && ($code !== 'OK'))) {
            $message = $this->safe_string($response, 'error');
            $feedback = $this->id . ' ' . $body;
            $exact = $this->exceptions['exact'];
            if (is_array($exact) && array_key_exists($code, $exact)) {
                throw new $exact[$code]($feedback);
            } else if (is_array($exact) && array_key_exists($message, $exact)) {
                throw new $exact[$message]($feedback);
            }
            $broad = $this->safe_value($this->exceptions, 'broad', array());
            $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
            if ($broadKey !== null) {
                throw new $broad[$broadKey]($feedback);
            }
            throw new ExchangeError($feedback); // unknown $message
        }
    }
}
