<?php

namespace ccxt;

use Exception as Exception; // a common import

class okex3 extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'okex3',
            'name' => 'OKEX',
            'countries' => array ( 'CN', 'US' ),
            'version' => 'v3',
            'rateLimit' => 1000, // up to 3000 requests per 5 minutes ≈ 600 requests per minute ≈ 10 requests per second ≈ 100 ms
            'has' => array (
                'CORS' => false,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchCurrencies' => false, // see below
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
                'fetchTransactions' => false,
                'fetchMyTrades' => false, // they don't have it
                'fetchDepositAddress' => true,
                'fetchOrderTrades' => true,
                'fetchTickers' => true,
                'fetchLedger' => true,
                'withdraw' => true,
                'futures' => true,
            ),
            'timeframes' => array (
                '1m' => '60',
                '3m' => '180',
                '5m' => '300',
                '15m' => '900',
                '30m' => '1800',
                '1h' => '3600',
                '2h' => '7200',
                '4h' => '14400',
                '6h' => '21600',
                '12h' => '43200',
                '1d' => '86400',
                '1w' => '604800',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/32552768-0d6dd3c6-c4a6-11e7-90f8-c043b64756a7.jpg',
                'api' => 'https://www.okex.com',
                'www' => 'https://www.okex.com',
                'doc' => 'https://www.okex.com/docs/en/',
                'fees' => 'https://www.okex.com/pages/products/fees.html',
            ),
            'api' => array (
                'general' => array (
                    'get' => array (
                        'time',
                    ),
                ),
                'account' => array (
                    'get' => array (
                        'currencies',
                        'wallet',
                        'wallet/{currency}',
                        'withdrawal/fee',
                        'withdrawal/history',
                        'withdrawal/history/{currency}',
                        'ledger',
                        'deposit/address',
                        'deposit/history',
                        'deposit/history/{currency}',
                    ),
                    'post' => array (
                        'transfer',
                        'withdrawal',
                    ),
                ),
                'spot' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{currency}',
                        'accounts/{currency}/ledger',
                        'orders',
                        'orders_pending',
                        'orders/{order_id}',
                        'orders/{client_oid}',
                        'fills',
                        // public
                        'instruments',
                        'instruments/{instrument_id}/book',
                        'instruments/ticker',
                        'instruments/{instrument_id}/ticker',
                        'instruments/{instrument_id}/trades',
                        'instruments/{instrument_id}/candles',
                    ),
                    'post' => array (
                        'orders',
                        'batch_orders',
                        'cancel_orders/{order_id}',
                        'cancel_orders/{client_oid}',
                        'cancel_batch_orders',
                    ),
                ),
                'margin' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{instrument_id}',
                        'accounts/{instrument_id}/ledger',
                        'accounts/availability',
                        'accounts/{instrument_id}/availability',
                        'accounts/borrowed',
                        'accounts/{instrument_id}/borrowed',
                        'orders',
                        'orders/{order_id}',
                        'orders/{client_oid}',
                        'orders_pending',
                        'fills',
                    ),
                    'post' => array (
                        'accounts/borrow',
                        'accounts/repayment',
                        'orders',
                        'batch_orders',
                        'cancel_orders',
                        'cancel_orders/{order_id}',
                        'cancel_orders/{client_oid}',
                        'cancel_batch_orders',
                    ),
                ),
                'futures' => array (
                    'get' => array (
                        'position',
                        '{instrument_id}/position',
                        'accounts',
                        'accounts/{currency}',
                        'accounts/{currency}/leverage',
                        'accounts/{currency}/ledger',
                        'orders/{instrument_id}',
                        'orders/{instrument_id}/{order_id}',
                        'orders/{instrument_id}/{client_oid}',
                        'fills',
                        // public
                        'instruments',
                        'instruments/{instrument_id}/book',
                        'instruments/ticker',
                        'instruments/{instrument_id}/ticker',
                        'instruments/{instrument_id}/trades',
                        'instruments/{instrument_id}/candles',
                        'accounts/{instrument_id}/holds',
                        'instruments/{instrument_id}/index',
                        'rate',
                        'instruments/{instrument_id}/estimated_price',
                        'instruments/{instrument_id}/open_interest',
                        'instruments/{instrument_id}/price_limit',
                        'instruments/{instrument_id}/liquidation',
                        'instruments/{instrument_id}/mark_price',
                    ),
                    'post' => array (
                        'accounts/{currency}/leverage',
                        'order',
                        'orders',
                        'cancel_order/{instrument_id}/{order_id}',
                        'cancel_order/{instrument_id}/{client_oid}',
                        'cancel_batch_orders/{instrument_id}',
                    ),
                ),
                'swap' => array (
                    'get' => array (
                        'position',
                        '{instrument_id}/position',
                        'accounts',
                        '{instrument_id}/accounts',
                        'accounts/{instrument_id}/settings',
                        'accounts/{instrument_id}/ledger',
                        'accounts/{instrument_id}/holds',
                        'orders/{instrument_id}',
                        'orders/{instrument_id}/{order_id}',
                        'orders/{instrument_id}/{client_oid}',
                        'fills',
                        // public
                        'instruments',
                        'instruments/{instrument_id}/depth',
                        'instruments/ticker',
                        'instruments/{instrument_id}/ticker',
                        'instruments/{instrument_id}/trades',
                        'instruments/{instrument_id}/candles',
                        'instruments/{instrument_id}/index',
                        'rate',
                        'instruments/{instrument_id}/open_interest',
                        'instruments/{instrument_id}/price_limit',
                        'instruments/{instrument_id}/liquidation',
                        'instruments/{instrument_id}/funding_time',
                        'instruments/{instrument_id}/mark_price',
                        'instruments/{instrument_id}/historical_funding_rate',
                    ),
                    'post' => array (
                        'accounts/{instrument_id}/leverage',
                        'order',
                        'orders',
                        'cancel_order/{instrument_id}/{order_id}',
                        'cancel_order/{instrument_id}/{client_oid}',
                        'cancel_batch_orders/{instrument_id}',
                    ),
                ),
                // they have removed this part from public
                'ett' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{currency}',
                        'accounts/{currency}/ledger',
                        'orders', // fetchOrder, fetchOrders
                        // public
                        'constituents/{ett}',
                        'define-price/{ett}',
                    ),
                    'post' => array (
                        'orders',
                        'orders/{order_id}',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.0015,
                    'maker' => 0.0010,
                ),
                'spot' => array (
                    'taker' => 0.0015,
                    'maker' => 0.0010,
                ),
                'futures' => array (
                    'taker' => 0.0030,
                    'maker' => 0.0020,
                ),
                'swap' => array (
                    'taker' => 0.0070,
                    'maker' => 0.0020,
                ),
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'password' => true,
            ),
            'exceptions' => array (
                // http error codes
                // 400 Bad Request — Invalid request format
                // 401 Unauthorized — Invalid API Key
                // 403 Forbidden — You do not have access to the requested resource
                // 404 Not Found
                // 500 Internal Server Error — We had a problem with our server
                'exact' => array (
                    '1' => '\\ccxt\\ExchangeError', // array( "code" => 1, "message" => "System error" )
                    // undocumented
                    'failure to get a peer from the ring-balancer' => '\\ccxt\\ExchangeError', // array( "message" => "failure to get a peer from the ring-balancer" )
                    '4010' => '\\ccxt\\PermissionDenied', // array( "code" => 4010, "message" => "For the security of your funds, withdrawals are not permitted within 24 hours after changing fund password  / mobile number / Google Authenticator settings " )
                    // common
                    '30001' => '\\ccxt\\AuthenticationError', // array( "code" => 30001, "message" => 'request header "OK_ACCESS_KEY" cannot be blank')
                    '30002' => '\\ccxt\\AuthenticationError', // array( "code" => 30002, "message" => 'request header "OK_ACCESS_SIGN" cannot be blank')
                    '30003' => '\\ccxt\\AuthenticationError', // array( "code" => 30003, "message" => 'request header "OK_ACCESS_TIMESTAMP" cannot be blank')
                    '30004' => '\\ccxt\\AuthenticationError', // array( "code" => 30004, "message" => 'request header "OK_ACCESS_PASSPHRASE" cannot be blank')
                    '30005' => '\\ccxt\\InvalidNonce', // array( "code" => 30005, "message" => "invalid OK_ACCESS_TIMESTAMP" )
                    '30006' => '\\ccxt\\AuthenticationError', // array( "code" => 30006, "message" => "invalid OK_ACCESS_KEY" )
                    '30007' => '\\ccxt\\BadRequest', // array( "code" => 30007, "message" => 'invalid Content_Type, please use "application/json" format')
                    '30008' => '\\ccxt\\RequestTimeout', // array( "code" => 30008, "message" => "timestamp request expired" )
                    '30009' => '\\ccxt\\ExchangeError', // array( "code" => 30009, "message" => "system error" )
                    '30010' => '\\ccxt\\AuthenticationError', // array( "code" => 30010, "message" => "API validation failed" )
                    '30011' => '\\ccxt\\PermissionDenied', // array( "code" => 30011, "message" => "invalid IP" )
                    '30012' => '\\ccxt\\AuthenticationError', // array( "code" => 30012, "message" => "invalid authorization" )
                    '30013' => '\\ccxt\\AuthenticationError', // array( "code" => 30013, "message" => "invalid sign" )
                    '30014' => '\\ccxt\\DDoSProtection', // array( "code" => 30014, "message" => "request too frequent" )
                    '30015' => '\\ccxt\\AuthenticationError', // array( "code" => 30015, "message" => 'request header "OK_ACCESS_PASSPHRASE" incorrect')
                    '30016' => '\\ccxt\\ExchangeError', // array( "code" => 30015, "message" => "you are using v1 apiKey, please use v1 endpoint. If you would like to use v3 endpoint, please subscribe to v3 apiKey" )
                    '30017' => '\\ccxt\\ExchangeError', // array( "code" => 30017, "message" => "apikey's broker id does not match" )
                    '30018' => '\\ccxt\\ExchangeError', // array( "code" => 30018, "message" => "apikey's domain does not match" )
                    '30019' => '\\ccxt\\ExchangeNotAvailable', // array( "code" => 30019, "message" => "Api is offline or unavailable" )
                    '30020' => '\\ccxt\\BadRequest', // array( "code" => 30020, "message" => "body cannot be blank" )
                    '30021' => '\\ccxt\\BadRequest', // array( "code" => 30021, "message" => "Json data format error" ), array( "code" => 30021, "message" => "json data format error" )
                    '30022' => '\\ccxt\\PermissionDenied', // array( "code" => 30022, "message" => "Api has been frozen" )
                    '30023' => '\\ccxt\\BadRequest', // array( "code" => 30023, "message" => "{0} parameter cannot be blank" )
                    '30024' => '\\ccxt\\BadRequest', // array( "code" => 30024, "message" => "{0} parameter value error" )
                    '30025' => '\\ccxt\\BadRequest', // array( "code" => 30025, "message" => "{0} parameter category error" )
                    '30026' => '\\ccxt\\DDoSProtection', // array( "code" => 30026, "message" => "requested too frequent" )
                    '30027' => '\\ccxt\\AuthenticationError', // array( "code" => 30027, "message" => "login failure" )
                    '30028' => '\\ccxt\\PermissionDenied', // array( "code" => 30028, "message" => "unauthorized execution" )
                    '30029' => '\\ccxt\\AccountSuspended', // array( "code" => 30029, "message" => "account suspended" )
                    '30030' => '\\ccxt\\ExchangeError', // array( "code" => 30030, "message" => "endpoint request failed. Please try again" )
                    '30031' => '\\ccxt\\BadRequest', // array( "code" => 30031, "message" => "token does not exist" )
                    '30032' => '\\ccxt\\ExchangeError', // array( "code" => 30032, "message" => "pair does not exist" )
                    '30033' => '\\ccxt\\BadRequest', // array( "code" => 30033, "message" => "exchange domain does not exist" )
                    '30034' => '\\ccxt\\ExchangeError', // array( "code" => 30034, "message" => "exchange ID does not exist" )
                    '30035' => '\\ccxt\\ExchangeError', // array( "code" => 30035, "message" => "trading is not supported in this website" )
                    '30036' => '\\ccxt\\ExchangeError', // array( "code" => 30036, "message" => "no relevant data" )
                    '30038' => '\\ccxt\\AuthenticationError', // array( "code" => 30038, "message" => "user does not exist" )
                    '30037' => '\\ccxt\\ExchangeNotAvailable', // array( "code" => 30037, "message" => "endpoint is offline or unavailable" )
                    // futures
                    '32001' => '\\ccxt\\AccountSuspended', // array( "code" => 32001, "message" => "futures account suspended" )
                    '32002' => '\\ccxt\\PermissionDenied', // array( "code" => 32002, "message" => "futures account does not exist" )
                    '32003' => '\\ccxt\\CancelPending', // array( "code" => 32003, "message" => "canceling, please wait" )
                    '32004' => '\\ccxt\\ExchangeError', // array( "code" => 32004, "message" => "you have no unfilled orders" )
                    '32005' => '\\ccxt\\InvalidOrder', // array( "code" => 32005, "message" => "max order quantity" )
                    '32006' => '\\ccxt\\InvalidOrder', // array( "code" => 32006, "message" => "the order price or trigger price exceeds USD 1 million" )
                    '32007' => '\\ccxt\\InvalidOrder', // array( "code" => 32007, "message" => "leverage level must be the same for orders on the same side of the contract" )
                    '32008' => '\\ccxt\\InvalidOrder', // array( "code" => 32008, "message" => "Max. positions to open (cross margin)" )
                    '32009' => '\\ccxt\\InvalidOrder', // array( "code" => 32009, "message" => "Max. positions to open (fixed margin)" )
                    '32010' => '\\ccxt\\ExchangeError', // array( "code" => 32010, "message" => "leverage cannot be changed with open positions" )
                    '32011' => '\\ccxt\\ExchangeError', // array( "code" => 32011, "message" => "futures status error" )
                    '32012' => '\\ccxt\\ExchangeError', // array( "code" => 32012, "message" => "futures order update error" )
                    '32013' => '\\ccxt\\ExchangeError', // array( "code" => 32013, "message" => "token type is blank" )
                    '32014' => '\\ccxt\\ExchangeError', // array( "code" => 32014, "message" => "your number of contracts closing is larger than the number of contracts available" )
                    '32015' => '\\ccxt\\ExchangeError', // array( "code" => 32015, "message" => "margin ratio is lower than 100% before opening positions" )
                    '32016' => '\\ccxt\\ExchangeError', // array( "code" => 32016, "message" => "margin ratio is lower than 100% after opening position" )
                    '32017' => '\\ccxt\\ExchangeError', // array( "code" => 32017, "message" => "no BBO" )
                    '32018' => '\\ccxt\\ExchangeError', // array( "code" => 32018, "message" => "the order quantity is less than 1, please try again" )
                    '32019' => '\\ccxt\\ExchangeError', // array( "code" => 32019, "message" => "the order price deviates from the price of the previous minute by more than 3%" )
                    '32020' => '\\ccxt\\ExchangeError', // array( "code" => 32020, "message" => "the price is not in the range of the price limit" )
                    '32021' => '\\ccxt\\ExchangeError', // array( "code" => 32021, "message" => "leverage error" )
                    '32022' => '\\ccxt\\ExchangeError', // array( "code" => 32022, "message" => "this function is not supported in your country or region according to the regulations" )
                    '32023' => '\\ccxt\\ExchangeError', // array( "code" => 32023, "message" => "this account has outstanding loan" )
                    '32024' => '\\ccxt\\ExchangeError', // array( "code" => 32024, "message" => "order cannot be placed during delivery" )
                    '32025' => '\\ccxt\\ExchangeError', // array( "code" => 32025, "message" => "order cannot be placed during settlement" )
                    '32026' => '\\ccxt\\ExchangeError', // array( "code" => 32026, "message" => "your account is restricted from opening positions" )
                    '32029' => '\\ccxt\\ExchangeError', // array( "code" => 32029, "message" => "order info does not exist" )
                    '32028' => '\\ccxt\\ExchangeError', // array( "code" => 32028, "message" => "account is suspended and liquidated" )
                    '32027' => '\\ccxt\\ExchangeError', // array( "code" => 32027, "message" => "cancelled over 20 orders" )
                    '32044' => '\\ccxt\\ExchangeError', // array( "code" => 32044, "message" => "The margin ratio after submitting this order is lower than the minimum requirement ({0}) for your tier." )
                    // token and margin trading
                    '33001' => '\\ccxt\\PermissionDenied', // array( "code" => 33001, "message" => "margin account for this pair is not enabled yet" )
                    '33002' => '\\ccxt\\AccountSuspended', // array( "code" => 33002, "message" => "margin account for this pair is suspended" )
                    '33003' => '\\ccxt\\InsufficientFunds', // array( "code" => 33003, "message" => "no loan balance" )
                    '33004' => '\\ccxt\\ExchangeError', // array( "code" => 33004, "message" => "loan amount cannot be smaller than the minimum limit" )
                    '33005' => '\\ccxt\\ExchangeError', // array( "code" => 33005, "message" => "repayment amount must exceed 0" )
                    '33006' => '\\ccxt\\ExchangeError', // array( "code" => 33006, "message" => "loan order not found" )
                    '33007' => '\\ccxt\\ExchangeError', // array( "code" => 33007, "message" => "status not found" )
                    '33008' => '\\ccxt\\ExchangeError', // array( "code" => 33008, "message" => "loan amount cannot exceed the maximum limit" )
                    '33009' => '\\ccxt\\ExchangeError', // array( "code" => 33009, "message" => "user ID is blank" )
                    '33010' => '\\ccxt\\ExchangeError', // array( "code" => 33010, "message" => "you cannot cancel an order during session 2 of call auction" )
                    '33011' => '\\ccxt\\ExchangeError', // array( "code" => 33011, "message" => "no new market data" )
                    '33012' => '\\ccxt\\ExchangeError', // array( "code" => 33012, "message" => "order cancellation failed" )
                    '33013' => '\\ccxt\\InvalidOrder', // array( "code" => 33013, "message" => "order placement failed" )
                    '33014' => '\\ccxt\\OrderNotFound', // array( "code" => 33014, "message" => "order does not exist" )
                    '33015' => '\\ccxt\\InvalidOrder', // array( "code" => 33015, "message" => "exceeded maximum limit" )
                    '33016' => '\\ccxt\\ExchangeError', // array( "code" => 33016, "message" => "margin trading is not open for this token" )
                    '33017' => '\\ccxt\\InsufficientFunds', // array( "code" => 33017, "message" => "insufficient balance" )
                    '33018' => '\\ccxt\\ExchangeError', // array( "code" => 33018, "message" => "this parameter must be smaller than 1" )
                    '33020' => '\\ccxt\\ExchangeError', // array( "code" => 33020, "message" => "request not supported" )
                    '33021' => '\\ccxt\\BadRequest', // array( "code" => 33021, "message" => "token and the pair do not match" )
                    '33022' => '\\ccxt\\InvalidOrder', // array( "code" => 33022, "message" => "pair and the order do not match" )
                    '33023' => '\\ccxt\\ExchangeError', // array( "code" => 33023, "message" => "you can only place market orders during call auction" )
                    '33024' => '\\ccxt\\InvalidOrder', // array( "code" => 33024, "message" => "trading amount too small" )
                    '33025' => '\\ccxt\\InvalidOrder', // array( "code" => 33025, "message" => "base token amount is blank" )
                    '33026' => '\\ccxt\\ExchangeError', // array( "code" => 33026, "message" => "transaction completed" )
                    '33027' => '\\ccxt\\InvalidOrder', // array( "code" => 33027, "message" => "cancelled order or order cancelling" )
                    '33028' => '\\ccxt\\InvalidOrder', // array( "code" => 33028, "message" => "the decimal places of the trading price exceeded the limit" )
                    '33029' => '\\ccxt\\InvalidOrder', // array( "code" => 33029, "message" => "the decimal places of the trading size exceeded the limit" )
                    '33034' => '\\ccxt\\ExchangeError', // array( "code" => 33034, "message" => "You can only place limit order after Call Auction has started" )
                    '33059' => '\\ccxt\\BadRequest', // array( "code" => 33059, "message" => "client_oid or order_id is required" )
                    '33060' => '\\ccxt\\BadRequest', // array( "code" => 33060, "message" => "Only fill in either parameter client_oid or order_id" )
                    // account
                    '34001' => '\\ccxt\\PermissionDenied', // array( "code" => 34001, "message" => "withdrawal suspended" )
                    '34002' => '\\ccxt\\InvalidAddress', // array( "code" => 34002, "message" => "please add a withdrawal address" )
                    '34003' => '\\ccxt\\ExchangeError', // array( "code" => 34003, "message" => "sorry, this token cannot be withdrawn to xx at the moment" )
                    '34004' => '\\ccxt\\ExchangeError', // array( "code" => 34004, "message" => "withdrawal fee is smaller than minimum limit" )
                    '34005' => '\\ccxt\\ExchangeError', // array( "code" => 34005, "message" => "withdrawal fee exceeds the maximum limit" )
                    '34006' => '\\ccxt\\ExchangeError', // array( "code" => 34006, "message" => "withdrawal amount is lower than the minimum limit" )
                    '34007' => '\\ccxt\\ExchangeError', // array( "code" => 34007, "message" => "withdrawal amount exceeds the maximum limit" )
                    '34008' => '\\ccxt\\InsufficientFunds', // array( "code" => 34008, "message" => "insufficient balance" )
                    '34009' => '\\ccxt\\ExchangeError', // array( "code" => 34009, "message" => "your withdrawal amount exceeds the daily limit" )
                    '34010' => '\\ccxt\\ExchangeError', // array( "code" => 34010, "message" => "transfer amount must be larger than 0" )
                    '34011' => '\\ccxt\\ExchangeError', // array( "code" => 34011, "message" => "conditions not met" )
                    '34012' => '\\ccxt\\ExchangeError', // array( "code" => 34012, "message" => "the minimum withdrawal amount for NEO is 1, and the amount must be an integer" )
                    '34013' => '\\ccxt\\ExchangeError', // array( "code" => 34013, "message" => "please transfer" )
                    '34014' => '\\ccxt\\ExchangeError', // array( "code" => 34014, "message" => "transfer limited" )
                    '34015' => '\\ccxt\\ExchangeError', // array( "code" => 34015, "message" => "subaccount does not exist" )
                    '34016' => '\\ccxt\\PermissionDenied', // array( "code" => 34016, "message" => "transfer suspended" )
                    '34017' => '\\ccxt\\AccountSuspended', // array( "code" => 34017, "message" => "account suspended" )
                    '34018' => '\\ccxt\\AuthenticationError', // array( "code" => 34018, "message" => "incorrect trades password" )
                    '34019' => '\\ccxt\\PermissionDenied', // array( "code" => 34019, "message" => "please bind your email before withdrawal" )
                    '34020' => '\\ccxt\\PermissionDenied', // array( "code" => 34020, "message" => "please bind your funds password before withdrawal" )
                    '34021' => '\\ccxt\\InvalidAddress', // array( "code" => 34021, "message" => "Not verified address" )
                    '34022' => '\\ccxt\\ExchangeError', // array( "code" => 34022, "message" => "Withdrawals are not available for sub accounts" )
                    '34023' => '\\ccxt\\PermissionDenied', // array( "code" => 34023, "message" => "Please enable futures trading before transferring your funds" )
                    // swap
                    '35001' => '\\ccxt\\ExchangeError', // array( "code" => 35001, "message" => "Contract does not exist" )
                    '35002' => '\\ccxt\\ExchangeError', // array( "code" => 35002, "message" => "Contract settling" )
                    '35003' => '\\ccxt\\ExchangeError', // array( "code" => 35003, "message" => "Contract paused" )
                    '35004' => '\\ccxt\\ExchangeError', // array( "code" => 35004, "message" => "Contract pending settlement" )
                    '35005' => '\\ccxt\\AuthenticationError', // array( "code" => 35005, "message" => "User does not exist" )
                    '35008' => '\\ccxt\\InvalidOrder', // array( "code" => 35008, "message" => "Risk ratio too high" )
                    '35010' => '\\ccxt\\InvalidOrder', // array( "code" => 35010, "message" => "Position closing too large" )
                    '35012' => '\\ccxt\\InvalidOrder', // array( "code" => 35012, "message" => "Incorrect order size" )
                    '35014' => '\\ccxt\\InvalidOrder', // array( "code" => 35014, "message" => "Order price is not within limit" )
                    '35015' => '\\ccxt\\InvalidOrder', // array( "code" => 35015, "message" => "Invalid leverage level" )
                    '35017' => '\\ccxt\\ExchangeError', // array( "code" => 35017, "message" => "Open orders exist" )
                    '35019' => '\\ccxt\\InvalidOrder', // array( "code" => 35019, "message" => "Order size too large" )
                    '35020' => '\\ccxt\\InvalidOrder', // array( "code" => 35020, "message" => "Order price too high" )
                    '35021' => '\\ccxt\\InvalidOrder', // array( "code" => 35021, "message" => "Order size exceeded current tier limit" )
                    '35022' => '\\ccxt\\ExchangeError', // array( "code" => 35022, "message" => "Contract status error" )
                    '35024' => '\\ccxt\\ExchangeError', // array( "code" => 35024, "message" => "Contract not initialized" )
                    '35025' => '\\ccxt\\InsufficientFunds', // array( "code" => 35025, "message" => "No account balance" )
                    '35026' => '\\ccxt\\ExchangeError', // array( "code" => 35026, "message" => "Contract settings not initialized" )
                    '35029' => '\\ccxt\\OrderNotFound', // array( "code" => 35029, "message" => "Order does not exist" )
                    '35030' => '\\ccxt\\InvalidOrder', // array( "code" => 35030, "message" => "Order size too large" )
                    '35031' => '\\ccxt\\InvalidOrder', // array( "code" => 35031, "message" => "Cancel order size too large" )
                    '35032' => '\\ccxt\\ExchangeError', // array( "code" => 35032, "message" => "Invalid user status" )
                    '35039' => '\\ccxt\\ExchangeError', // array( "code" => 35039, "message" => "Open order quantity exceeds limit" )
                    '35040' => '\\ccxt\\InvalidOrder', // array("error_message":"Invalid order type","result":"true","error_code":"35040","order_id":"-1")
                    '35044' => '\\ccxt\\ExchangeError', // array( "code" => 35044, "message" => "Invalid order status" )
                    '35046' => '\\ccxt\\InsufficientFunds', // array( "code" => 35046, "message" => "Negative account balance" )
                    '35047' => '\\ccxt\\InsufficientFunds', // array( "code" => 35047, "message" => "Insufficient account balance" )
                    '35048' => '\\ccxt\\ExchangeError', // array( "code" => 35048, "message" => "User contract is frozen and liquidating" )
                    '35049' => '\\ccxt\\InvalidOrder', // array( "code" => 35049, "message" => "Invalid order type" )
                    '35050' => '\\ccxt\\InvalidOrder', // array( "code" => 35050, "message" => "Position settings are blank" )
                    '35052' => '\\ccxt\\InsufficientFunds', // array( "code" => 35052, "message" => "Insufficient cross margin" )
                    '35053' => '\\ccxt\\ExchangeError', // array( "code" => 35053, "message" => "Account risk too high" )
                    '35055' => '\\ccxt\\InsufficientFunds', // array( "code" => 35055, "message" => "Insufficient account balance" )
                    '35057' => '\\ccxt\\ExchangeError', // array( "code" => 35057, "message" => "No last traded price" )
                    '35058' => '\\ccxt\\ExchangeError', // array( "code" => 35058, "message" => "No limit" )
                    '35059' => '\\ccxt\\BadRequest', // array( "code" => 35059, "message" => "client_oid or order_id is required" )
                    '35060' => '\\ccxt\\BadRequest', // array( "code" => 35060, "message" => "Only fill in either parameter client_oid or order_id" )
                    '35061' => '\\ccxt\\BadRequest', // array( "code" => 35061, "message" => "Invalid instrument_id" )
                    '35062' => '\\ccxt\\InvalidOrder', // array( "code" => 35062, "message" => "Invalid match_price" )
                    '35063' => '\\ccxt\\InvalidOrder', // array( "code" => 35063, "message" => "Invalid order_size" )
                    '35064' => '\\ccxt\\InvalidOrder', // array( "code" => 35064, "message" => "Invalid client_oid" )
                ),
                'broad' => array (
                ),
            ),
            'options' => array (
                'fetchMarkets' => array ( 'spot', 'futures', 'swap' ),
                'defaultType' => 'spot', // 'account', 'spot', 'margin', 'futures', 'swap'
                'auth' => array (
                    'time' => 'public',
                    'currencies' => 'private',
                    'instruments' => 'public',
                    'rate' => 'public',
                    'constituents/{ett}' => 'public',
                    'define-price/{ett}' => 'public',
                ),
            ),
            'commonCurrencies' => array (
                // OKEX refers to ERC20 version of Aeternity (AEToken)
                'AE' => 'AET', // https://github.com/ccxt/ccxt/issues/4981
                'FAIR' => 'FairGame',
                'HOT' => 'Hydro Protocol',
                'HSR' => 'HC',
                'MAG' => 'Maggie',
                'YOYO' => 'YOYOW',
                'WIN' => 'WinToken', // https://github.com/ccxt/ccxt/issues/5701
            ),
        ));
    }

    public function fetch_time ($params = array ()) {
        $response = $this->generalGetTime ($params);
        //
        //     {
        //         "iso" => "2015-01-07T23:47:25.201Z",
        //         "epoch" => 1420674445.201
        //     }
        //
        return $this->parse8601 ($this->safe_string($response, 'iso'));
    }

    public function fetch_markets ($params = array ()) {
        $types = $this->safe_value($this->options, 'fetchMarkets');
        $result = array();
        for ($i = 0; $i < count ($types); $i++) {
            $markets = $this->fetch_markets_by_type ($types[$i], $params);
            $result = $this->array_concat($result, $markets);
        }
        return $result;
    }

    public function parse_markets ($markets) {
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $result[] = $this->parse_market ($markets[$i]);
        }
        return $result;
    }

    public function parse_market ($market) {
        //
        // $spot markets
        //
        //     array ( array (   base_currency => "EOS",
        //           instrument_id => "EOS-OKB",
        //                min_size => "0.01",
        //              product_id => "EOS-OKB",
        //          quote_currency => "OKB",
        //          size_increment => "0.000001",
        //               tick_size => "0.0001"        ),
        //
        //       ..., // the $spot endpoint also returns ETT instruments
        //
        //       {   base_currency => "OK06ETT",
        //          base_increment => "0.00000001",
        //           base_min_size => "0.01",
        //           instrument_id => "OK06ETT-USDT",
        //                min_size => "0.01",
        //              product_id => "OK06ETT-USDT",
        //          quote_currency => "USDT",
        //         quote_increment => "0.0001",
        //          size_increment => "0.00000001",
        //               tick_size => "0.0001"        } )
        //
        // futures markets
        //
        //     array ( {    instrument_id => "BTG-USD-190329",
        //         underlying_index => "BTG",
        //           quote_currency => "USD",
        //                tick_size => "0.01",
        //             contract_val => "10",
        //                  listing => "2018-12-14",
        //                 delivery => "2019-03-29",
        //          trade_increment => "1"               }  )
        //
        // $swap markets
        //
        //     array ( {    instrument_id => "BTC-USD-SWAP",
        //         underlying_index => "BTC",
        //           quote_currency => "USD",
        //                     coin => "BTC",
        //             contract_val => "100",
        //                  listing => "2018-10-23T20:11:00.443Z",
        //                 delivery => "2018-10-24T20:11:00.443Z",
        //           size_increment => "4",
        //                tick_size => "4"                         }  )
        //
        $id = $this->safe_string($market, 'instrument_id');
        $marketType = 'spot';
        $spot = true;
        $future = false;
        $swap = false;
        $baseId = $this->safe_string($market, 'base_currency');
        if ($baseId === null) {
            $marketType = 'swap';
            $spot = false;
            $swap = true;
            $baseId = $this->safe_string($market, 'coin');
            if ($baseId === null) {
                $swap = false;
                $future = true;
                $marketType = 'futures';
                $baseId = $this->safe_string($market, 'underlying_index');
            }
        }
        $quoteId = $this->safe_string($market, 'quote_currency');
        $base = $this->safe_currency_code($baseId);
        $quote = $this->safe_currency_code($quoteId);
        $symbol = $spot ? ($base . '/' . $quote) : $id;
        $amountPrecision = $this->safe_string($market, 'size_increment');
        if ($amountPrecision !== null) {
            $amountPrecision = $this->precision_from_string($amountPrecision);
        }
        $pricePrecision = $this->safe_string($market, 'tick_size');
        if ($pricePrecision !== null) {
            $pricePrecision = $this->precision_from_string($pricePrecision);
        }
        $precision = array (
            'amount' => $amountPrecision,
            'price' => $pricePrecision,
        );
        $minAmount = $this->safe_float_2($market, 'min_size', 'base_min_size');
        $minPrice = $this->safe_float($market, 'tick_size');
        if ($precision['price'] !== null) {
            $minPrice = pow(10, -$precision['price']);
        }
        $minCost = null;
        if ($minAmount !== null && $minPrice !== null) {
            $minCost = $minAmount * $minPrice;
        }
        $active = true;
        $fees = $this->safe_value_2($this->fees, $marketType, 'trading', array());
        return array_merge ($fees, array (
            'id' => $id,
            'symbol' => $symbol,
            'base' => $base,
            'quote' => $quote,
            'baseId' => $baseId,
            'quoteId' => $quoteId,
            'info' => $market,
            'type' => $marketType,
            'spot' => $spot,
            'futures' => $future,
            'swap' => $swap,
            'active' => $active,
            'precision' => $precision,
            'limits' => array (
                'amount' => array (
                    'min' => $minAmount,
                    'max' => null,
                ),
                'price' => array (
                    'min' => $minPrice,
                    'max' => null,
                ),
                'cost' => array (
                    'min' => $minCost,
                    'max' => null,
                ),
            ),
        ));
    }

    public function fetch_markets_by_type ($type, $params = array ()) {
        $method = $type . 'GetInstruments';
        $response = $this->$method ($params);
        //
        // spot markets
        //
        //     array ( {   base_currency => "EOS",
        //          base_increment => "0.000001",
        //           base_min_size => "0.01",
        //           instrument_id => "EOS-OKB",
        //                min_size => "0.01",
        //              product_id => "EOS-OKB",
        //          quote_currency => "OKB",
        //         quote_increment => "0.0001",
        //          size_increment => "0.000001",
        //               tick_size => "0.0001"    }      )
        //
        // futures markets
        //
        //     array ( {    instrument_id => "BTG-USD-190329",
        //         underlying_index => "BTG",
        //           quote_currency => "USD",
        //                tick_size => "0.01",
        //             contract_val => "10",
        //                  listing => "2018-12-14",
        //                 delivery => "2019-03-29",
        //          trade_increment => "1"               }  )
        //
        // swap markets
        //
        //     array ( {    instrument_id => "BTC-USD-SWAP",
        //         underlying_index => "BTC",
        //           quote_currency => "USD",
        //                     coin => "BTC",
        //             contract_val => "100",
        //                  listing => "2018-10-23T20:11:00.443Z",
        //                 delivery => "2018-10-24T20:11:00.443Z",
        //           size_increment => "4",
        //                tick_size => "4"                         }  )
        //
        return $this->parse_markets ($response);
    }

    public function fetch_currencies ($params = array ()) {
        // has['fetchCurrencies'] is currently set to false
        // despite that their docs say these endpoints are public:
        //     https://www.okex.com/api/account/v3/withdrawal/fee
        //     https://www.okex.com/api/account/v3/currencies
        // it will still reply with array( "$code":30001, "message" => "OK-ACCESS-KEY header is required" )
        // if you attempt to access it without authentication
        $response = $this->accountGetCurrencies ($params);
        //
        //     array (
        //         array (
        //             $name => '',
        //             $currency => 'BTC',
        //             can_withdraw => '1',
        //             can_deposit => '1',
        //             min_withdrawal => '0.0100000000000000'
        //         ),
        //     )
        //
        $result = array();
        for ($i = 0; $i < count ($response); $i++) {
            $currency = $response[$i];
            $id = $this->safe_string($currency, 'currency');
            $code = $this->safe_currency_code($id);
            $precision = 8; // default $precision, todo => fix "magic constants"
            $name = $this->safe_string($currency, 'name');
            $canDeposit = $this->safe_integer($currency, 'can_deposit');
            $canWithdraw = $this->safe_integer($currency, 'can_withdraw');
            $active = $canDeposit && $canWithdraw;
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'type' => null,
                'name' => $name,
                'active' => $active,
                'fee' => null, // todo => redesign
                'precision' => $precision,
                'limits' => array (
                    'amount' => array( 'min' => null, 'max' => null ),
                    'price' => array( 'min' => null, 'max' => null ),
                    'cost' => array( 'min' => null, 'max' => null ),
                    'withdraw' => array (
                        'min' => $this->safe_float($currency, 'min_withdrawal'),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['type'] . 'GetInstrumentsInstrumentId';
        $method .= ($market['type'] === 'swap') ? 'Depth' : 'Book';
        $request = array (
            'instrument_id' => $market['id'],
        );
        if ($limit !== null) {
            $request['size'] = $limit; // max 200
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {      asks => [ ["0.02685268", "0.242571", "1"],
        //                    ["0.02685493", "0.164085", "1"],
        //                    ...
        //                    ["0.02779", "1.039", "1"],
        //                    ["0.027813", "0.0876", "1"]        ],
        //            bids => [ ["0.02684052", "10.371849", "1"],
        //                    ["0.02684051", "3.707", "4"],
        //                    ...
        //                    ["0.02634963", "0.132934", "1"],
        //                    ["0.02634962", "0.264838", "2"]    ],
        //       $timestamp =>   "2018-12-17T20:24:16.159Z"            }
        //
        $timestamp = $this->parse8601 ($this->safe_string($response, 'timestamp'));
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {         best_ask => "0.02665472",
        //               best_bid => "0.02665221",
        //          instrument_id => "ETH-BTC",
        //             product_id => "ETH-BTC",
        //                   $last => "0.02665472",
        //                    ask => "0.02665472", // missing in the docs
        //                    bid => "0.02665221", // not mentioned in the docs
        //               open_24h => "0.02645482",
        //               high_24h => "0.02714633",
        //                low_24h => "0.02614109",
        //        base_volume_24h => "572298.901923",
        //              $timestamp => "2018-12-17T21:20:07.856Z",
        //       quote_volume_24h => "15094.86831261"            }
        //
        $timestamp = $this->parse8601 ($this->safe_string($ticker, 'timestamp'));
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'instrument_id');
        if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
        } else if ($marketId !== null) {
            $parts = explode('-', $marketId);
            $numParts = is_array ($parts) ? count ($parts) : 0;
            if ($numParts === 2) {
                list($baseId, $quoteId) = $parts;
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            } else {
                $symbol = $marketId;
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'last');
        $open = $this->safe_float($ticker, 'open_24h');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high_24h'),
            'low' => $this->safe_float($ticker, 'low_24h'),
            'bid' => $this->safe_float($ticker, 'best_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'best_ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'base_volume_24h'),
            'quoteVolume' => $this->safe_float($ticker, 'quote_volume_24h'),
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['type'] . 'GetInstrumentsInstrumentIdTicker';
        $request = array (
            'instrument_id' => $market['id'],
        );
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {         best_ask => "0.02665472",
        //               best_bid => "0.02665221",
        //          instrument_id => "ETH-BTC",
        //             product_id => "ETH-BTC",
        //                   last => "0.02665472",
        //                    ask => "0.02665472",
        //                    bid => "0.02665221",
        //               open_24h => "0.02645482",
        //               high_24h => "0.02714633",
        //                low_24h => "0.02614109",
        //        base_volume_24h => "572298.901923",
        //              timestamp => "2018-12-17T21:20:07.856Z",
        //       quote_volume_24h => "15094.86831261"            }
        //
        return $this->parse_ticker($response);
    }

    public function fetch_tickers_by_type ($type, $symbols = null, $params = array ()) {
        $this->load_markets();
        $method = $type . 'GetInstrumentsTicker';
        $response = $this->$method ($params);
        $result = array();
        for ($i = 0; $i < count ($response); $i++) {
            $ticker = $this->parse_ticker($response[$i]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $defaultType = $this->safe_string_2($this->options, 'fetchTickers', 'defaultType');
        $type = $this->safe_string($params, 'type', $defaultType);
        return $this->fetch_tickers_by_type ($type, $symbols, $this->omit ($params, 'type'));
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     spot trades
        //
        //         {
        //             time => "2018-12-17T23:31:08.268Z",
        //             $timestamp => "2018-12-17T23:31:08.268Z",
        //             trade_id => "409687906",
        //             $price => "0.02677805",
        //             size => "0.923467",
        //             $side => "sell"
        //         }
        //
        //     futures trades, swap trades
        //
        //         {
        //             trade_id => "1989230840021013",
        //             $side => "buy",
        //             $price => "92.42",
        //             qty => "184", // missing in swap markets
        //             size => "5", // missing in futures markets
        //             $timestamp => "2018-12-17T23:26:04.613Z"
        //         }
        //
        // fetchOrderTrades (private)
        //
        //     spot trades, margin trades
        //
        //         array (
        //             "created_at":"2019-03-15T02:52:56.000Z",
        //             "exec_type":"T", // whether the order is taker or maker
        //             "$fee":"0.00000082",
        //             "instrument_id":"BTC-USDT",
        //             "ledger_id":"3963052721",
        //             "liquidity":"T", // whether the order is taker or maker
        //             "order_id":"2482659399697408",
        //             "$price":"3888.6",
        //             "product_id":"BTC-USDT",
        //             "$side":"buy",
        //             "size":"0.00055306",
        //             "$timestamp":"2019-03-15T02:52:56.000Z"
        //         ),
        //
        //     futures trades, swap trades
        //
        //         {
        //             "trade_id":"197429674631450625",
        //             "instrument_id":"EOS-USD-SWAP",
        //             "order_id":"6a-7-54d663a28-0",
        //             "$price":"3.633",
        //             "order_qty":"1.0000",
        //             "$fee":"-0.000551",
        //             "created_at":"2019-03-21T04:41:58.0Z", // missing in swap trades
        //             "$timestamp":"2019-03-25T05:56:31.287Z", // missing in futures trades
        //             "exec_type":"M", // whether the order is taker or maker
        //             "$side":"short", // "buy" in futures trades
        //         }
        //
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->parse8601 ($this->safe_string_2($trade, 'timestamp', 'created_at'));
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float_2($trade, 'size', 'qty');
        $amount = $this->safe_float($trade, 'order_qty', $amount);
        $takerOrMaker = $this->safe_string_2($trade, 'exec_type', 'liquidity');
        if ($takerOrMaker === 'M') {
            $takerOrMaker = 'maker';
        } else if ($takerOrMaker === 'T') {
            $takerOrMaker = 'taker';
        }
        $side = $this->safe_string($trade, 'side');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $feeCost = $this->safe_float($trade, 'fee');
        $fee = null;
        if ($feeCost !== null) {
            $feeCurrency = null;
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        $orderId = $this->safe_string($trade, 'order_id');
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'trade_id'),
            'order' => $orderId,
            'type' => null,
            'takerOrMaker' => $takerOrMaker,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['type'] . 'GetInstrumentsInstrumentIdTrades';
        if (($limit === null) || ($limit > 100)) {
            $limit = 100; // maximum = default = 100
        }
        $request = array (
            'instrument_id' => $market['id'],
            'limit' => $limit,
            // from => 'id',
            // to => 'id',
        );
        $response = $this->$method (array_merge ($request, $params));
        //
        // spot markets
        //
        //     array (
        //         {
        //             time => "2018-12-17T23:31:08.268Z",
        //             timestamp => "2018-12-17T23:31:08.268Z",
        //             trade_id => "409687906",
        //             price => "0.02677805",
        //             size => "0.923467",
        //             side => "sell"
        //         }
        //     )
        //
        // futures markets, swap markets
        //
        //     array (
        //         {
        //             trade_id => "1989230840021013",
        //             side => "buy",
        //             price => "92.42",
        //             qty => "184", // missing in swap markets
        //             size => "5", // missing in futures markets
        //             timestamp => "2018-12-17T23:26:04.613Z"
        //         }
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        //
        // spot markets
        //
        //     {
        //         close => "0.02684545",
        //         high => "0.02685084",
        //         low => "0.02683312",
        //         open => "0.02683894",
        //         time => "2018-12-17T20:28:00.000Z",
        //         volume => "101.457222"
        //     }
        //
        // futures markets
        //
        //     array (
        //         1545072720000,
        //         0.3159,
        //         0.3161,
        //         0.3144,
        //         0.3149,
        //         22886,
        //         725179.26172331,
        //     )
        //
        if (gettype ($ohlcv) === 'array' && count (array_filter (array_keys ($ohlcv), 'is_string')) == 0) {
            $numElements = is_array ($ohlcv) ? count ($ohlcv) : 0;
            $volumeIndex = ($numElements > 6) ? 6 : 5;
            $timestamp = $ohlcv[0];
            if (gettype ($timestamp) === 'string') {
                $timestamp = $this->parse8601 ($timestamp);
            }
            return [
                $timestamp, // $timestamp
                floatval ($ohlcv[1]),            // Open
                floatval ($ohlcv[2]),            // High
                floatval ($ohlcv[3]),            // Low
                floatval ($ohlcv[4]),            // Close
                // floatval ($ohlcv[5]),         // Quote Volume
                // floatval ($ohlcv[6]),         // Base Volume
                floatval ($ohlcv[$volumeIndex]),  // Volume, okex will return base volume in the 7th element for future markets
            ];
        } else {
            return array (
                $this->parse8601 ($this->safe_string($ohlcv, 'time')),
                $this->safe_float($ohlcv, 'open'),    // Open
                $this->safe_float($ohlcv, 'high'),    // High
                $this->safe_float($ohlcv, 'low'),     // Low
                $this->safe_float($ohlcv, 'close'),   // Close
                $this->safe_float($ohlcv, 'volume'),  // Base Volume
            );
        }
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['type'] . 'GetInstrumentsInstrumentIdCandles';
        $request = array (
            'instrument_id' => $market['id'],
            'granularity' => $this->timeframes[$timeframe],
        );
        if ($since !== null) {
            $request['start'] = $this->iso8601 ($since);
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        // spot markets
        //
        //     array ( array (  close => "0.02683401",
        //           high => "0.02683401",
        //            low => "0.02683401",
        //           open => "0.02683401",
        //           time => "2018-12-17T23:47:00.000Z",
        //         volume => "0"                         ),
        //       ...
        //       {  close => "0.02684545",
        //           high => "0.02685084",
        //            low => "0.02683312",
        //           open => "0.02683894",
        //           time => "2018-12-17T20:28:00.000Z",
        //         volume => "101.457222"                }  )
        //
        // futures
        //
        //     array ( array ( 1545090660000,
        //         0.3171,
        //         0.3174,
        //         0.3171,
        //         0.3173,
        //         1648,
        //         51930.38579450868 ),
        //       ...
        //       array ( 1545072720000,
        //         0.3159,
        //         0.3161,
        //         0.3144,
        //         0.3149,
        //         22886,
        //         725179.26172331 )    )
        //
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function parse_account_balance ($response) {
        //
        // $account
        //
        //     array (
        //         array (
        //             $balance =>  0,
        //             available =>  0,
        //             currency => "BTC",
        //             hold =>  0
        //         ),
        //         {
        //             $balance =>  0,
        //             available =>  0,
        //             currency => "ETH",
        //             hold =>  0
        //         }
        //     )
        //
        // spot
        //
        //     array (
        //         array (
        //             frozen => "0",
        //             hold => "0",
        //             id => "2149632",
        //             currency => "BTC",
        //             $balance => "0.0000000497717339",
        //             available => "0.0000000497717339",
        //             holds => "0"
        //         ),
        //         {
        //             frozen => "0",
        //             hold => "0",
        //             id => "2149632",
        //             currency => "ICN",
        //             $balance => "0.00000000925",
        //             available => "0.00000000925",
        //             holds => "0"
        //         }
        //     )
        //
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($response); $i++) {
            $balance = $response[$i];
            $currencyId = $this->safe_string($balance, 'currency');
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account ();
            $account['total'] = $this->safe_float($balance, 'balance');
            $account['used'] = $this->safe_float($balance, 'hold');
            $account['free'] = $this->safe_float($balance, 'available');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_margin_balance ($response) {
        //
        //     array (
        //         array (
        //             "currency:BTC" => array (
        //                 "available":"0",
        //                 "$balance":"0",
        //                 "borrowed":"0",
        //                 "can_withdraw":"0",
        //                 "frozen":"0",
        //                 "hold":"0",
        //                 "holds":"0",
        //                 "lending_fee":"0"
        //             ),
        //             "currency:USDT" => array (
        //                 "available":"100",
        //                 "$balance":"100",
        //                 "borrowed":"0",
        //                 "can_withdraw":"100",
        //                 "frozen":"0",
        //                 "hold":"0",
        //                 "holds":"0",
        //                 "lending_fee":"0"
        //             ),
        //             "instrument_id":"BTC-USDT",
        //             "liquidation_price":"0",
        //             "product_id":"BTC-USDT",
        //             "risk_rate":""
        //         ),
        //     )
        //
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($response); $i++) {
            $balance = $response[$i];
            $marketId = $this->safe_string($balance, 'instrument_id');
            $market = $this->safe_value($this->markets_by_id, $marketId);
            $symbol = null;
            if ($market === null) {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            } else {
                $symbol = $market['symbol'];
            }
            $omittedBalance = $this->omit ($balance, array (
                'instrument_id',
                'liquidation_price',
                'product_id',
                'risk_rate',
                'margin_ratio',
            ));
            $keys = is_array($omittedBalance) ? array_keys($omittedBalance) : array();
            $accounts = array();
            for ($k = 0; $k < count ($keys); $k++) {
                $key = $keys[$k];
                $marketBalance = $balance[$key];
                if (mb_strpos($key, ':') !== false) {
                    $parts = explode(':', $key);
                    $currencyId = $parts[1];
                    $code = $this->safe_currency_code($currencyId);
                    $account = $this->account ();
                    $account['total'] = $this->safe_float($marketBalance, 'balance');
                    $account['used'] = $this->safe_float($marketBalance, 'hold');
                    $account['free'] = $this->safe_float($marketBalance, 'available');
                    $accounts[$code] = $account;
                } else {
                    throw new NotSupported($this->id . ' margin $balance $response format has changed!');
                }
            }
            $result[$symbol] = $this->parse_balance($accounts);
        }
        return $result;
    }

    public function parse_futures_balance ($response) {
        //
        //     {
        //         "$info":{
        //             "eos":array (
        //                 "auto_margin":"0",
        //                 "contracts" => array (
        //                     array (
        //                         "available_qty":"40.37069445",
        //                         "fixed_balance":"0",
        //                         "instrument_id":"EOS-USD-190329",
        //                         "margin_for_unfilled":"0",
        //                         "margin_frozen":"0",
        //                         "realized_pnl":"0",
        //                         "unrealized_pnl":"0"
        //                     ),
        //                     array (
        //                         "available_qty":"40.37069445",
        //                         "fixed_balance":"14.54895721",
        //                         "instrument_id":"EOS-USD-190628",
        //                         "margin_for_unfilled":"0",
        //                         "margin_frozen":"10.64042157",
        //                         "realized_pnl":"-3.90853564",
        //                         "unrealized_pnl":"-0.259"
        //                     ),
        //                 ),
        //                 "equity":"50.75220665",
        //                 "margin_mode":"fixed",
        //                 "total_avail_balance":"40.37069445"
        //             ),
        //         }
        //     }
        //
        // their root field name is "$info", so our $info will contain their $info
        $result = array( 'info' => $response );
        $info = $this->safe_value($response, 'info', array());
        $ids = is_array($info) ? array_keys($info) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $code = $this->safe_currency_code($id);
            $balance = $this->safe_value($info, $id, array());
            $account = $this->account ();
            // it may be incorrect to use total, free and used for swap accounts
            $account['total'] = $this->safe_float($balance, 'equity');
            $account['free'] = $this->safe_float($balance, 'total_avail_balance');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_swap_balance ($response) {
        //
        //     {
        //         "$info" => array (
        //             {
        //                 "equity":"3.0139",
        //                 "fixed_balance":"0.0000",
        //                 "instrument_id":"EOS-USD-SWAP",
        //                 "margin":"0.5523",
        //                 "margin_frozen":"0.0000",
        //                 "margin_mode":"crossed",
        //                 "margin_ratio":"1.0913",
        //                 "realized_pnl":"-0.0006",
        //                 "timestamp":"2019-03-25T03:46:10.336Z",
        //                 "total_avail_balance":"3.0000",
        //                 "unrealized_pnl":"0.0145"
        //             }
        //         )
        //     }
        //
        // their root field name is "$info", so our $info will contain their $info
        $result = array( 'info' => $response );
        $info = $this->safe_value($response, 'info', array());
        for ($i = 0; $i < count ($info); $i++) {
            $balance = $info[$i];
            $marketId = $this->safe_string($balance, 'instrument_id');
            $symbol = $marketId;
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $symbol = $this->markets_by_id[$marketId]['symbol'];
            }
            $account = $this->account ();
            // it may be incorrect to use total, free and used for swap accounts
            $account['total'] = $this->safe_float($balance, 'equity');
            $account['free'] = $this->safe_float($balance, 'total_avail_balance');
            $result[$symbol] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $defaultType = $this->safe_string_2($this->options, 'fetchBalance', 'defaultType');
        $type = $this->safe_string($params, 'type', $defaultType);
        if ($type === null) {
            throw new ArgumentsRequired($this->id . " fetchBalance requires a $type parameter (one of 'account', 'spot', 'margin', 'futures', 'swap')");
        }
        $suffix = ($type === 'account') ? 'Wallet' : 'Accounts';
        $method = $type . 'Get' . $suffix;
        $query = $this->omit ($params, 'type');
        $response = $this->$method ($query);
        //
        // account
        //
        //     array (
        //         array (
        //             balance =>  0,
        //             available =>  0,
        //             currency => "BTC",
        //             hold =>  0
        //         ),
        //         {
        //             balance =>  0,
        //             available =>  0,
        //             currency => "ETH",
        //             hold =>  0
        //         }
        //     )
        //
        // spot
        //
        //     array (
        //         array (
        //             frozen => "0",
        //             hold => "0",
        //             id => "2149632",
        //             currency => "BTC",
        //             balance => "0.0000000497717339",
        //             available => "0.0000000497717339",
        //             holds => "0"
        //         ),
        //         {
        //             frozen => "0",
        //             hold => "0",
        //             id => "2149632",
        //             currency => "ICN",
        //             balance => "0.00000000925",
        //             available => "0.00000000925",
        //             holds => "0"
        //         }
        //     )
        //
        // margin
        //
        //     array (
        //         array (
        //             "currency:BTC" => array (
        //                 "available":"0",
        //                 "balance":"0",
        //                 "borrowed":"0",
        //                 "can_withdraw":"0",
        //                 "frozen":"0",
        //                 "hold":"0",
        //                 "holds":"0",
        //                 "lending_fee":"0"
        //             ),
        //             "currency:USDT" => array (
        //                 "available":"100",
        //                 "balance":"100",
        //                 "borrowed":"0",
        //                 "can_withdraw":"100",
        //                 "frozen":"0",
        //                 "hold":"0",
        //                 "holds":"0",
        //                 "lending_fee":"0"
        //             ),
        //             "instrument_id":"BTC-USDT",
        //             "liquidation_price":"0",
        //             "product_id":"BTC-USDT",
        //             "risk_rate":""
        //         ),
        //     )
        //
        // futures
        //
        //     {
        //         "info":{
        //             "eos":array (
        //                 "auto_margin":"0",
        //                 "contracts" => array (
        //                     array (
        //                         "available_qty":"40.37069445",
        //                         "fixed_balance":"0",
        //                         "instrument_id":"EOS-USD-190329",
        //                         "margin_for_unfilled":"0",
        //                         "margin_frozen":"0",
        //                         "realized_pnl":"0",
        //                         "unrealized_pnl":"0"
        //                     ),
        //                     array (
        //                         "available_qty":"40.37069445",
        //                         "fixed_balance":"14.54895721",
        //                         "instrument_id":"EOS-USD-190628",
        //                         "margin_for_unfilled":"0",
        //                         "margin_frozen":"10.64042157",
        //                         "realized_pnl":"-3.90853564",
        //                         "unrealized_pnl":"-0.259"
        //                     ),
        //                 ),
        //                 "equity":"50.75220665",
        //                 "margin_mode":"fixed",
        //                 "total_avail_balance":"40.37069445"
        //             ),
        //         }
        //     }
        //
        // swap
        //
        //     {
        //         "info" => array (
        //             {
        //                 "equity":"3.0139",
        //                 "fixed_balance":"0.0000",
        //                 "instrument_id":"EOS-USD-SWAP",
        //                 "margin":"0.5523",
        //                 "margin_frozen":"0.0000",
        //                 "margin_mode":"crossed",
        //                 "margin_ratio":"1.0913",
        //                 "realized_pnl":"-0.0006",
        //                 "timestamp":"2019-03-25T03:46:10.336Z",
        //                 "total_avail_balance":"3.0000",
        //                 "unrealized_pnl":"0.0145"
        //             }
        //         )
        //     }
        //
        if (($type === 'account') || ($type === 'spot')) {
            return $this->parse_account_balance ($response);
        } else if ($type === 'margin') {
            return $this->parse_margin_balance ($response);
        } else if ($type === 'futures') {
            return $this->parse_futures_balance ($response);
        } else if ($type === 'swap') {
            return $this->parse_swap_balance ($response);
        }
        throw new NotSupported($this->id . " fetchBalance does not support the '" . $type . "' $type (the $type must be one of 'account', 'spot', 'margin', 'futures', 'swap')");
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'instrument_id' => $market['id'],
            // 'client_oid' => 'abcdef1234567890', // [a-z0-9]array(1,32)
            // 'order_type' => '0', // 0 => Normal limit order (Unfilled and 0 represent normal limit order) 1 => Post only 2 => Fill Or Kill 3 => Immediatel Or Cancel
        );
        $method = null;
        if ($market['futures'] || $market['swap']) {
            $size = $market['futures'] ? $this->number_to_string($amount) : $this->amount_to_precision($symbol, $amount);
            $request = array_merge ($request, array (
                'type' => $type, // 1:open long 2:open short 3:close long 4:close short for futures
                'size' => $size,
                'price' => $this->price_to_precision($symbol, $price),
                // 'match_price' => '0', // Order at best counter party $price? (0:no 1:yes). The default is 0. If it is set as 1, the $price parameter will be ignored. When posting orders at best bid $price, order_type can only be 0 (regular order).
            ));
            if ($market['futures']) {
                $request['leverage'] = '10'; // or '20'
            }
            $method = $market['type'] . 'PostOrder';
        } else {
            $marginTrading = $this->safe_string($params, 'margin_trading', '1');  // 1 = spot, 2 = margin
            $request = array_merge ($request, array (
                'side' => $side,
                'type' => $type, // limit/market
                'margin_trading' => $marginTrading, // 1 = spot, 2 = margin
            ));
            if ($type === 'limit') {
                $request['price'] = $this->price_to_precision($symbol, $price);
                $request['size'] = $this->amount_to_precision($symbol, $amount);
            } else if ($type === 'market') {
                // for $market buy it requires the $amount of quote currency to spend
                if ($side === 'buy') {
                    $notional = $this->safe_float($params, 'notional');
                    $createMarketBuyOrderRequiresPrice = $this->safe_value($this->options, 'createMarketBuyOrderRequiresPrice', true);
                    if ($createMarketBuyOrderRequiresPrice) {
                        if ($price !== null) {
                            if ($notional === null) {
                                $notional = $amount * $price;
                            }
                        } else if ($notional === null) {
                            throw new InvalidOrder($this->id . " createOrder() requires the $price argument with $market buy orders to calculate total order cost ($amount to spend), where cost = $amount * $price-> Supply a $price argument to createOrder() call if you want the cost to be calculated for you from $price and $amount, or, alternatively, add .options['createMarketBuyOrderRequiresPrice'] = false and supply the total cost value in the 'notional' extra parameter (the exchange-specific behaviour)");
                        }
                    }
                    $request['notional'] = $this->cost_to_precision($symbol, $notional);
                } else {
                    $request['size'] = $this->amount_to_precision($symbol, $amount);
                }
            }
            $method = ($marginTrading === '2') ? 'marginPostOrders' : 'spotPostOrders';
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {
        //         "client_oid":"oktspot79",
        //         "error_code":"",
        //         "error_message":"",
        //         "order_id":"2510789768709120",
        //         "result":true
        //     }
        //
        $timestamp = $this->milliseconds ();
        $id = $this->safe_string($response, 'order_id');
        return array (
            'info' => $response,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'status' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'cost' => null,
            'trades' => null,
            'fee' => null,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder() requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $type = $market['type'];
        $method = $type . 'PostCancelOrder';
        $request = array (
            'instrument_id' => $market['id'],
        );
        if ($market['futures'] || $market['swap']) {
            $method .= 'InstrumentId';
        } else {
            $method .= 's';
        }
        $clientOid = $this->safe_string($params, 'client_oid');
        if ($clientOid !== null) {
            $method .= 'ClientOid';
            $request['client_oid'] = $clientOid;
        } else {
            $method .= 'OrderId';
            $request['order_id'] = $id;
        }
        $query = $this->omit ($params, 'type');
        $response = $this->$method (array_merge ($request, $query));
        $result = (is_array($response) && array_key_exists('result', $response)) ? $response : $this->safe_value($response, $market['id'], array());
        //
        // spot, margin
        //
        //     {
        //         "btc-usdt" => array (
        //             {
        //                 "$result":true,
        //                 "client_oid":"a123",
        //                 "order_id" => "2510832677225473"
        //             }
        //         )
        //     }
        //
        // futures, swap
        //
        //     {
        //         "$result" => true,
        //         "client_oid" => "oktfuture10", // missing if requested by order_id
        //         "order_id" => "2517535534836736",
        //         "instrument_id" => "EOS-USD-190628"
        //     }
        //
        return $this->parse_order($result, $market);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '-2' => 'failed',
            '-1' => 'canceled',
            '0' => 'open',
            '1' => 'open',
            '2' => 'closed',
            '3' => 'open',
            '4' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order_side ($side) {
        $sides = array (
            '1' => 'buy', // open long
            '2' => 'sell', // open short
            '3' => 'sell', // close long
            '4' => 'buy', // close short
        );
        return $this->safe_string($sides, $side, $side);
    }

    public function parse_order ($order, $market = null) {
        //
        // createOrder
        //
        //     {
        //         "client_oid":"oktspot79",
        //         "error_code":"",
        //         "error_message":"",
        //         "order_id":"2510789768709120",
        //         "result":true
        //     }
        //
        // cancelOrder
        //
        //     {
        //         "result" => true,
        //         "client_oid" => "oktfuture10", // missing if requested by order_id
        //         "order_id" => "2517535534836736",
        //         // instrument_id is missing for spot/margin orders
        //         // available in futures and swap orders only
        //         "instrument_id" => "EOS-USD-190628",
        //     }
        //
        // fetchOrder, fetchOrdersByState, fetchOpenOrders, fetchClosedOrders
        //
        //     // spot and margin orders
        //
        //     {
        //         "client_oid":"oktspot76",
        //         "created_at":"2019-03-18T07:26:49.000Z",
        //         "filled_notional":"3.9734",
        //         "filled_size":"0.001", // filled_qty in futures and swap orders
        //         "funds":"", // this is most likely the same as notional
        //         "instrument_id":"BTC-USDT",
        //         "notional":"",
        //         "order_id":"2500723297813504",
        //         "order_type":"0",
        //         "$price":"4013",
        //         "product_id":"BTC-USDT", // missing in futures and swap orders
        //         "$side":"buy",
        //         "size":"0.001",
        //         "$status":"$filled",
        //         "state" => "2",
        //         "$timestamp":"2019-03-18T07:26:49.000Z",
        //         "$type":"limit"
        //     }
        //
        //     // futures and swap orders
        //
        //     {
        //         "instrument_id":"EOS-USD-190628",
        //         "size":"10",
        //         "$timestamp":"2019-03-20T10:04:55.000Z",
        //         "filled_qty":"10", // filled_size in spot and margin orders
        //         "$fee":"-0.00841043",
        //         "order_id":"2512669605501952",
        //         "$price":"3.668",
        //         "price_avg":"3.567", // missing in spot and margin orders
        //         "$status":"2",
        //         "state" => "2",
        //         "$type":"4",
        //         "contract_val":"10",
        //         "leverage":"10", // missing in swap, spot and margin orders
        //         "client_oid":"",
        //         "pnl":"1.09510794", // missing in swap, spo and margin orders
        //         "order_type":"0"
        //     }
        //
        $id = $this->safe_string($order, 'order_id');
        $timestamp = $this->parse8601 ($this->safe_string($order, 'timestamp'));
        $side = $this->safe_string($order, 'side');
        $type = $this->safe_string($order, 'type');
        if (($side !== 'buy') && ($side !== 'sell')) {
            $side = $this->parse_order_side ($type);
        }
        if (($type !== 'limit') && ($type !== 'market')) {
            if (is_array($order) && array_key_exists('pnl', $order)) {
                $type = 'futures';
            } else {
                $type = 'swap';
            }
        }
        $symbol = null;
        $marketId = $this->safe_string($order, 'instrument_id');
        if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
            $symbol = $market['symbol'];
        } else {
            $symbol = $marketId;
        }
        if ($market !== null) {
            if ($symbol === null) {
                $symbol = $market['symbol'];
            }
        }
        $amount = $this->safe_float($order, 'size');
        $filled = $this->safe_float_2($order, 'filled_size', 'filled_qty');
        $remaining = null;
        if ($amount !== null) {
            if ($filled !== null) {
                $amount = max ($amount, $filled);
                $remaining = max (0, $amount - $filled);
            }
        }
        if ($type === 'market') {
            $remaining = 0;
        }
        $cost = $this->safe_float_2($order, 'filled_notional', 'funds');
        $price = $this->safe_float($order, 'price');
        $average = $this->safe_float($order, 'price_avg');
        if ($cost === null) {
            if ($filled !== null && $average !== null) {
                $cost = $average * $filled;
            }
        } else {
            if (($average === null) && ($filled !== null) && ($filled > 0)) {
                $average = $cost / $filled;
            }
        }
        $status = $this->parse_order_status($this->safe_string($order, 'state'));
        $feeCost = $this->safe_float($order, 'fee');
        $fee = null;
        if ($feeCost !== null) {
            $feeCurrency = null;
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            );
        }
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'average' => $average,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrder requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $defaultType = $this->safe_string_2($this->options, 'fetchOrder', 'defaultType', $market['type']);
        $type = $this->safe_string($params, 'type', $defaultType);
        if ($type === null) {
            throw new ArgumentsRequired($this->id . " fetchOrder requires a $type parameter (one of 'spot', 'margin', 'futures', 'swap').");
        }
        $instrumentId = ($market['futures'] || $market['swap']) ? 'InstrumentId' : '';
        $method = $type . 'GetOrders' . $instrumentId;
        $request = array (
            'instrument_id' => $market['id'],
            // 'client_oid' => 'abcdef12345', // optional, [a-z0-9]array(1,32)
            // 'order_id' => $id,
        );
        $clientOid = $this->safe_string($params, 'client_oid');
        if ($clientOid !== null) {
            $method .= 'ClientOid';
            $request['client_oid'] = $clientOid;
        } else {
            $method .= 'OrderId';
            $request['order_id'] = $id;
        }
        $query = $this->omit ($params, 'type');
        $response = $this->$method (array_merge ($request, $query));
        //
        // spot, margin
        //
        //     {
        //         "client_oid":"oktspot70",
        //         "created_at":"2019-03-15T02:52:56.000Z",
        //         "filled_notional":"3.8886",
        //         "filled_size":"0.001",
        //         "funds":"",
        //         "instrument_id":"BTC-USDT",
        //         "notional":"",
        //         "order_id":"2482659399697408",
        //         "order_type":"0",
        //         "price":"3927.3",
        //         "product_id":"BTC-USDT",
        //         "side":"buy",
        //         "size":"0.001",
        //         "status":"filled",
        //         "state" => "2",
        //         "timestamp":"2019-03-15T02:52:56.000Z",
        //         "$type":"limit"
        //     }
        //
        // futures, swap
        //
        //     {
        //         "instrument_id":"EOS-USD-190628",
        //         "size":"10",
        //         "timestamp":"2019-03-20T02:46:38.000Z",
        //         "filled_qty":"10",
        //         "fee":"-0.0080819",
        //         "order_id":"2510946213248000",
        //         "price":"3.712",
        //         "price_avg":"3.712",
        //         "status":"2",
        //         "state" => "2",
        //         "$type":"2",
        //         "contract_val":"10",
        //         "leverage":"10",
        //         "client_oid":"", // missing in swap orders
        //         "pnl":"0", // missing in swap orders
        //         "order_type":"0"
        //     }
        //
        return $this->parse_order($response);
    }

    public function fetch_orders_by_state ($state, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrdersByState requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $type = $market['type'];
        $request = array (
            'instrument_id' => $market['id'],
            // '-2' => failed,
            // '-1' => cancelled,
            //  '0' => open ,
            //  '1' => partially filled,
            //  '2' => fully filled,
            //  '3' => submitting,
            //  '4' => cancelling,
            //  '6' => incomplete（open+partially filled),
            //  '7' => complete（cancelled+fully filled),
            'state' => $state,
        );
        $method = $type . 'GetOrders';
        if ($market['futures'] || $market['swap']) {
            $method .= 'InstrumentId';
        }
        $query = $this->omit ($params, 'type');
        $response = $this->$method (array_merge ($request, $query));
        //
        // spot, margin
        //
        //     array (
        //         // in fact, this documented API $response does not correspond
        //         // to their actual API $response for spot markets
        //         // OKEX v3 API returns a plain array of $orders (see below)
        //         array (
        //             array (
        //                 "client_oid":"oktspot76",
        //                 "created_at":"2019-03-18T07:26:49.000Z",
        //                 "filled_notional":"3.9734",
        //                 "filled_size":"0.001",
        //                 "funds":"",
        //                 "instrument_id":"BTC-USDT",
        //                 "notional":"",
        //                 "order_id":"2500723297813504",
        //                 "order_type":"0",
        //                 "price":"4013",
        //                 "product_id":"BTC-USDT",
        //                 "side":"buy",
        //                 "size":"0.001",
        //                 "status":"filled",
        //                 "$state" => "2",
        //                 "timestamp":"2019-03-18T07:26:49.000Z",
        //                 "$type":"$limit"
        //             ),
        //         ),
        //         {
        //             "$before":"2500723297813504",
        //             "after":"2500650881647616"
        //         }
        //     )
        //
        // futures, swap
        //
        //     {
        //         "result":true,  // missing in swap $orders
        //         "order_info" => array (
        //             array (
        //                 "instrument_id":"EOS-USD-190628",
        //                 "size":"10",
        //                 "timestamp":"2019-03-20T10:04:55.000Z",
        //                 "filled_qty":"10",
        //                 "fee":"-0.00841043",
        //                 "order_id":"2512669605501952",
        //                 "price":"3.668",
        //                 "price_avg":"3.567",
        //                 "status":"2",
        //                 "$state" => "2",
        //                 "$type":"4",
        //                 "contract_val":"10",
        //                 "leverage":"10", // missing in swap $orders
        //                 "client_oid":"",
        //                 "pnl":"1.09510794", // missing in swap $orders
        //                 "order_type":"0"
        //             ),
        //         )
        //     }
        //
        $orders = null;
        if ($market['type'] === 'swap' || $market['type'] === 'futures') {
            $orders = $this->safe_value($response, 'order_info', array());
        } else {
            $orders = $response;
            $responseLength = is_array ($response) ? count ($response) : 0;
            if ($responseLength < 1) {
                return array();
            }
            // in fact, this documented API $response does not correspond
            // to their actual API $response for spot markets
            // OKEX v3 API returns a plain array of $orders
            if ($responseLength > 1) {
                $before = $this->safe_value($response[1], 'before');
                if ($before !== null) {
                    $orders = $response[0];
                }
            }
        }
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // '-2' => failed,
        // '-1' => cancelled,
        //  '0' => open ,
        //  '1' => partially filled,
        //  '2' => fully filled,
        //  '3' => submitting,
        //  '4' => cancelling,
        //  '6' => incomplete（open+partially filled),
        //  '7' => complete（cancelled+fully filled),
        return $this->fetch_orders_by_state ('6', $symbol, $since, $limit, $params);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // '-2' => failed,
        // '-1' => cancelled,
        //  '0' => open ,
        //  '1' => partially filled,
        //  '2' => fully filled,
        //  '3' => submitting,
        //  '4' => cancelling,
        //  '6' => incomplete（open+partially filled),
        //  '7' => complete（cancelled+fully filled),
        return $this->fetch_orders_by_state ('7', $symbol, $since, $limit, $params);
    }

    public function parse_deposit_addresses ($addresses) {
        $result = array();
        for ($i = 0; $i < count ($addresses); $i++) {
            $result[] = $this->parse_deposit_address ($addresses[$i]);
        }
        return $result;
    }

    public function parse_deposit_address ($depositAddress, $currency = null) {
        //
        //     {
        //         $address => '0x696abb81974a8793352cbd33aadcf78eda3cfdfa',
        //         $currency => 'eth'
        //         $tag => 'abcde12345', // will be missing if the token does not require a deposit $tag
        //         payment_id => 'abcde12345', // will not be returned if the token does not require a payment_id
        //         // can_deposit => 1, // 0 or 1, documented but missing
        //         // can_withdraw => 1, // 0 or 1, documented but missing
        //     }
        //
        $address = $this->safe_string($depositAddress, 'address');
        $tag = $this->safe_string_2($depositAddress, 'tag', 'payment_id');
        $tag = $this->safe_string($depositAddress, 'memo', $tag);
        $currencyId = $this->safe_string($depositAddress, 'currency');
        $code = $this->safe_currency_code($currencyId);
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $depositAddress,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->accountGetDepositAddress (array_merge ($request, $params));
        //
        //     array (
        //         {
        //             address => '0x696abb81974a8793352cbd33aadcf78eda3cfdfa',
        //             $currency => 'eth'
        //         }
        //     )
        //
        $addresses = $this->parse_deposit_addresses ($response);
        $numAddresses = is_array ($addresses) ? count ($addresses) : 0;
        if ($numAddresses < 1) {
            throw new InvalidAddress($this->id . ' fetchDepositAddress cannot return nonexistent $addresses, you should create withdrawal $addresses with the exchange website first');
        }
        return $addresses[0];
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        if ($tag) {
            $address = $address . ':' . $tag;
        }
        $fee = $this->safe_string($params, 'fee');
        if ($fee === null) {
            throw new ExchangeError($this->id . " withdraw() requires a `$fee` string parameter, network transaction $fee must be ≥ 0. Withdrawals to OKCoin or OKEx are $fee-free, please set '0'. Withdrawing to external digital asset $address requires network transaction $fee->");
        }
        $request = array (
            'currency' => $currency['id'],
            'to_address' => $address,
            'destination' => '4', // 2 = OKCoin International, 3 = OKEx 4 = others
            'amount' => $this->number_to_string($amount),
            'fee' => $fee, // String. Network transaction $fee ≥ 0. Withdrawals to OKCoin or OKEx are $fee-free, please set as 0. Withdrawal to external digital asset $address requires network transaction $fee->
        );
        if ($this->password) {
            $request['trade_pwd'] = $this->password;
        } else if (is_array($params) && array_key_exists('password', $params)) {
            $request['trade_pwd'] = $params['password'];
        } else if (is_array($params) && array_key_exists('trade_pwd', $params)) {
            $request['trade_pwd'] = $params['trade_pwd'];
        }
        $query = $this->omit ($params, array ( 'fee', 'password', 'trade_pwd' ));
        if (!(is_array($request) && array_key_exists('trade_pwd', $request))) {
            throw new ExchangeError($this->id . ' withdraw() requires $this->password set on the exchange instance or a password / trade_pwd parameter');
        }
        $response = $this->accountPostWithdrawal (array_merge ($request, $query));
        //
        //     {
        //         "$amount":"0.1",
        //         "withdrawal_id":"67485",
        //         "$currency":"btc",
        //         "result":true
        //     }
        //
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'withdrawal_id'),
        );
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $method = 'accountGetDepositHistory';
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['code'] = $currency['code'];
            $method .= 'Currency';
        }
        $response = $this->$method (array_merge ($request, $params));
        return $this->parseTransactions ($response, $currency, $since, $limit, $params);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $method = 'accountGetWithdrawalHistory';
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['code'] = $currency['code'];
            $method .= 'Currency';
        }
        $response = $this->$method (array_merge ($request, $params));
        return $this->parseTransactions ($response, $currency, $since, $limit, $params);
    }

    public function parse_transaction_status ($status) {
        //
        // deposit $statuses
        //
        //     {
        //         '0' => 'waiting for confirmation',
        //         '1' => 'confirmation account',
        //         '2' => 'recharge success'
        //     }
        //
        // withdrawal statues
        //
        //     {
        //        '-3' => 'pending cancel',
        //        '-2' => 'cancelled',
        //        '-1' => 'failed',
        //         '0' => 'pending',
        //         '1' => 'sending',
        //         '2' => 'sent',
        //         '3' => 'email confirmation',
        //         '4' => 'manual confirmation',
        //         '5' => 'awaiting identity confirmation'
        //     }
        //
        $statuses = array (
            '-3' => 'pending',
            '-2' => 'pending',
            '-1' => 'failed',
            '0' => 'pending',
            '1' => 'pending',
            '2' => 'ok',
            '3' => 'pending',
            '4' => 'pending',
            '5' => 'pending',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // withdraw
        //
        //     {
        //         "$amount":"0.1",
        //         "withdrawal_id":"67485",
        //         "$currency":"btc",
        //         "result":true
        //     }
        //
        // fetchWithdrawals
        //
        //     {
        //         $amount => "4.72100000",
        //         withdrawal_id => "1729116",
        //         fee => "0.01000000eth",
        //         $txid => "0xf653125bbf090bcfe4b5e8e7b8f586a9d87aa7de94598702758c0802b…",
        //         $currency => "ETH",
        //         from => "7147338839",
        //         to => "0x26a3CB49578F07000575405a57888681249c35Fd",
        //         $timestamp => "2018-08-17T07:03:42.000Z",
        //         $status => "2"
        //     }
        //
        // fetchDeposits
        //
        //     {
        //         $amount => "0.47847546",
        //         $txid => "1723573_3_0_0_WALLET",
        //         $currency => "BTC",
        //         to => "",
        //         $timestamp => "2018-08-16T03:41:10.000Z",
        //         $status => "2"
        //     }
        //
        $type = null;
        $id = null;
        $address = null;
        $withdrawalId = $this->safe_string($transaction, 'withdrawal_id');
        $addressFrom = $this->safe_string($transaction, 'from');
        $addressTo = $this->safe_string($transaction, 'to');
        if ($withdrawalId !== null) {
            $type = 'withdrawal';
            $id = $withdrawalId;
            $address = $addressTo;
        } else {
            $type = 'deposit';
            $address = $addressFrom;
        }
        $currencyId = $this->safe_string($transaction, 'currency');
        $code = $this->safe_currency_code($currencyId);
        $amount = $this->safe_float($transaction, 'amount');
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'status'));
        $txid = $this->safe_string($transaction, 'txid');
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'timestamp'));
        $feeCost = null;
        if ($type === 'deposit') {
            $feeCost = 0;
        } else {
            if ($currencyId !== null) {
                $feeWithCurrencyId = $this->safe_string($transaction, 'fee');
                if ($feeWithCurrencyId !== null) {
                    // https://github.com/ccxt/ccxt/pull/5748
                    $lowercaseCurrencyId = strtolower($currencyId);
                    $feeWithoutCurrencyId = str_replace($lowercaseCurrencyId, '', $feeWithCurrencyId);
                    $feeCost = floatval ($feeWithoutCurrencyId);
                }
            }
        }
        // todo parse tags
        return array (
            'info' => $transaction,
            'id' => $id,
            'currency' => $code,
            'amount' => $amount,
            'addressFrom' => $addressFrom,
            'addressTo' => $addressTo,
            'address' => $address,
            'tag' => null,
            'status' => $status,
            'type' => $type,
            'updated' => null,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => array (
                'currency' => $code,
                'cost' => $feeCost,
            ),
        );
    }

    public function fetch_order_trades ($id, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrderTrades requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        if (($limit === null) || ($limit > 100)) {
            $limit = 100;
        }
        $request = array (
            'instrument_id' => $market['id'],
            'order_id' => $id,
            // from => '1', // return the page after the specified page number
            // to => '1', // return the page before the specified page number
            'limit' => $limit, // optional, number of results per $request, default = maximum = 100
        );
        $defaultType = $this->safe_string_2($this->options, 'fetchMyTrades', 'defaultType');
        $type = $this->safe_string($params, 'type', $defaultType);
        $query = $this->omit ($params, 'type');
        $method = $type . 'GetFills';
        $response = $this->$method (array_merge ($request, $query));
        //
        // spot $trades, margin $trades
        //
        //     array (
        //         array (
        //             array (
        //                 "created_at":"2019-03-15T02:52:56.000Z",
        //                 "exec_type":"T", // whether the order is taker or maker
        //                 "fee":"0.00000082",
        //                 "instrument_id":"BTC-USDT",
        //                 "ledger_id":"3963052721",
        //                 "liquidity":"T", // whether the order is taker or maker
        //                 "order_id":"2482659399697408",
        //                 "price":"3888.6",
        //                 "product_id":"BTC-USDT",
        //                 "side":"buy",
        //                 "size":"0.00055306",
        //                 "timestamp":"2019-03-15T02:52:56.000Z"
        //             ),
        //         ),
        //         {
        //             "before":"3963052722",
        //             "after":"3963052718"
        //         }
        //     )
        //
        // futures $trades, swap $trades
        //
        //     array (
        //         {
        //             "trade_id":"197429674631450625",
        //             "instrument_id":"EOS-USD-SWAP",
        //             "order_id":"6a-7-54d663a28-0",
        //             "price":"3.633",
        //             "order_qty":"1.0000",
        //             "fee":"-0.000551",
        //             "created_at":"2019-03-21T04:41:58.0Z", // missing in swap $trades
        //             "timestamp":"2019-03-25T05:56:31.287Z", // missing in futures $trades
        //             "exec_type":"M", // whether the order is taker or maker
        //             "side":"short", // "buy" in futures $trades
        //         }
        //     )
        //
        $trades = null;
        if ($market['type'] === 'swap' || $market['type'] === 'futures') {
            $trades = $response;
        } else {
            $responseLength = is_array ($response) ? count ($response) : 0;
            if ($responseLength < 1) {
                return array();
            }
            $trades = $response[0];
        }
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_ledger ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $defaultType = $this->safe_string_2($this->options, 'fetchLedger', 'defaultType');
        $type = $this->safe_string($params, 'type', $defaultType);
        $query = $this->omit ($params, 'type');
        $suffix = ($type === 'account') ? '' : 'Accounts';
        $argument = '';
        $request = array (
            // 'from' => 'id',
            // 'to' => 'id',
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $currency = null;
        if (($type === 'spot') || ($type === 'futures')) {
            if ($code === null) {
                throw new ArgumentsRequired($this->id . " fetchLedger requires a $currency $code $argument for '" . $type . "' markets");
            }
            $argument = 'Currency';
            $currency = $this->currency ($code);
            $request['currency'] = $currency['id'];
        } else if (($type === 'margin') || ($type === 'swap')) {
            if ($code === null) {
                throw new ArgumentsRequired($this->id . " fetchLedger requires a $code $argument (a $market symbol) for '" . $type . "' markets");
            }
            $argument = 'InstrumentId';
            $market = $this->market ($code); // we intentionally put a $market inside here for the margin and swap ledgers
            $currency = $this->currency ($market['base']);
            $request['instrument_id'] = $market['id'];
            //
            //     if ($type === 'margin') {
            //         //
            //         //      3. Borrow
            //         //      4. Repayment
            //         //      5. Interest
            //         //      7. Buy
            //         //      8. Sell
            //         //      9. From capital account
            //         //     10. From C2C
            //         //     11. From Futures
            //         //     12. From Spot
            //         //     13. From ETT
            //         //     14. To capital account
            //         //     15. To C2C
            //         //     16. To Spot
            //         //     17. To Futures
            //         //     18. To ETT
            //         //     19. Mandatory Repayment
            //         //     20. From Piggybank
            //         //     21. To Piggybank
            //         //     22. From Perpetual
            //         //     23. To Perpetual
            //         //     24. Liquidation Fee
            //         //     54. Clawback
            //         //     59. Airdrop Return.
            //         //
            //         $request['type'] = 'number'; // All types will be returned if this filed is left blank
            //     }
            //
        } else if ($type === 'account') {
            if ($code !== null) {
                $currency = $this->currency ($code);
                $request['currency'] = $currency['id'];
            }
            //
            //     //
            //     //      1. deposit
            //     //      2. withdrawal
            //     //     13. cancel withdrawal
            //     //     18. into futures account
            //     //     19. out of futures account
            //     //     20. into sub account
            //     //     21. out of sub account
            //     //     28. claim
            //     //     29. into ETT account
            //     //     30. out of ETT account
            //     //     31. into C2C account
            //     //     32. out of C2C account
            //     //     33. into margin account
            //     //     34. out of margin account
            //     //     37. into spot account
            //     //     38. out of spot account
            //     //
            //     $request['type'] = 'number';
            //
        } else {
            throw new NotSupported($this->id . " fetchLedger does not support the '" . $type . "' $type (the $type must be one of 'account', 'spot', 'margin', 'futures', 'swap')");
        }
        $method = $type . 'Get' . $suffix . $argument . 'Ledger';
        $response = $this->$method (array_merge ($request, $query));
        //
        // transfer     funds transfer in/out
        // trade        funds moved as a result of a trade, spot and margin accounts only
        // rebate       fee rebate as per fee schedule, spot and margin accounts only
        // match        open long/open short/close long/close short (futures) or a change in the amount because of trades (swap)
        // fee          fee, futures only
        // settlement   settlement/clawback/settle long/settle short
        // liquidation  force close long/force close short/deliver close long/deliver close short
        // funding      funding fee, swap only
        // margin       a change in the amount after adjusting margin, swap only
        //
        // account
        //
        //     array (
        //         {
        //             "amount":0.00051843,
        //             "balance":0.00100941,
        //             "$currency":"BTC",
        //             "fee":0,
        //             "ledger_id":8987285,
        //             "timestamp":"2018-10-12T11:01:14.000Z",
        //             "typename":"Get from activity"
        //         }
        //     )
        //
        // spot
        //
        //     array (
        //         {
        //             "timestamp":"2019-03-18T07:08:25.000Z",
        //             "ledger_id":"3995334780",
        //             "created_at":"2019-03-18T07:08:25.000Z",
        //             "$currency":"BTC",
        //             "amount":"0.0009985",
        //             "balance":"0.0029955",
        //             "$type":"trade",
        //             "details":{
        //                 "instrument_id":"BTC-USDT",
        //                 "order_id":"2500650881647616",
        //                 "product_id":"BTC-USDT"
        //             }
        //         }
        //     )
        //
        // margin
        //
        //     array (
        //         array (
        //             {
        //                 "created_at":"2019-03-20T03:45:05.000Z",
        //                 "ledger_id":"78918186",
        //                 "timestamp":"2019-03-20T03:45:05.000Z",
        //                 "$currency":"EOS",
        //                 "amount":"0", // ?
        //                 "balance":"0.59957711",
        //                 "$type":"transfer",
        //                 "details":{
        //                     "instrument_id":"EOS-USDT",
        //                     "order_id":"787057",
        //                     "product_id":"EOS-USDT"
        //                 }
        //             }
        //         ),
        //         {
        //             "before":"78965766",
        //             "after":"78918186"
        //         }
        //     )
        //
        // futures
        //
        //     array (
        //         {
        //             "ledger_id":"2508090544914461",
        //             "timestamp":"2019-03-19T14:40:24.000Z",
        //             "amount":"-0.00529521",
        //             "balance":"0",
        //             "$currency":"EOS",
        //             "$type":"fee",
        //             "details":{
        //                 "order_id":"2506982456445952",
        //                 "instrument_id":"EOS-USD-190628"
        //             }
        //         }
        //     )
        //
        // swap
        //
        //     array (
        //         array (
        //             "amount":"0.004742",
        //             "fee":"-0.000551",
        //             "$type":"match",
        //             "instrument_id":"EOS-USD-SWAP",
        //             "ledger_id":"197429674941902848",
        //             "timestamp":"2019-03-25T05:56:31.286Z"
        //         ),
        //     )
        //
        $entries = ($type === 'margin') ? $response[0] : $response;
        return $this->parse_ledger($entries, $currency, $since, $limit);
    }

    public function parse_ledger_entry_type ($type) {
        $types = array (
            'transfer' => 'transfer', // // funds transfer in/out
            'trade' => 'trade', // funds moved as a result of a trade, spot and margin accounts only
            'rebate' => 'rebate', // fee rebate as per fee schedule, spot and margin accounts only
            'match' => 'trade', // open long/open short/close long/close short (futures) or a change in the amount because of trades (swap)
            'fee' => 'fee', // fee, futures only
            'settlement' => 'trade', // settlement/clawback/settle long/settle short
            'liquidation' => 'trade', // force close long/force close short/deliver close long/deliver close short
            'funding' => 'fee', // funding fee, swap only
            'margin' => 'margin', // a change in the amount after adjusting margin, swap only
        );
        return $this->safe_string($types, $type, $type);
    }

    public function parse_ledger_entry ($item, $currency = null) {
        //
        //
        // $account
        //
        //     {
        //         "$amount":0.00051843,
        //         "balance":0.00100941,
        //         "$currency":"BTC",
        //         "$fee":0,
        //         "ledger_id":8987285,
        //         "$timestamp":"2018-10-12T11:01:14.000Z",
        //         "typename":"Get from activity"
        //     }
        //
        // spot
        //
        //     {
        //         "$timestamp":"2019-03-18T07:08:25.000Z",
        //         "ledger_id":"3995334780",
        //         "created_at":"2019-03-18T07:08:25.000Z",
        //         "$currency":"BTC",
        //         "$amount":"0.0009985",
        //         "balance":"0.0029955",
        //         "$type":"trade",
        //         "$details":{
        //             "instrument_id":"BTC-USDT",
        //             "order_id":"2500650881647616",
        //             "product_id":"BTC-USDT"
        //         }
        //     }
        //
        // margin
        //
        //     {
        //         "created_at":"2019-03-20T03:45:05.000Z",
        //         "ledger_id":"78918186",
        //         "$timestamp":"2019-03-20T03:45:05.000Z",
        //         "$currency":"EOS",
        //         "$amount":"0", // ?
        //         "balance":"0.59957711",
        //         "$type":"transfer",
        //         "$details":{
        //             "instrument_id":"EOS-USDT",
        //             "order_id":"787057",
        //             "product_id":"EOS-USDT"
        //         }
        //     }
        //
        // futures
        //
        //     {
        //         "ledger_id":"2508090544914461",
        //         "$timestamp":"2019-03-19T14:40:24.000Z",
        //         "$amount":"-0.00529521",
        //         "balance":"0",
        //         "$currency":"EOS",
        //         "$type":"$fee",
        //         "$details":{
        //             "order_id":"2506982456445952",
        //             "instrument_id":"EOS-USD-190628"
        //         }
        //     }
        //
        // swap
        //
        //     array (
        //         "$amount":"0.004742",
        //         "$fee":"-0.000551",
        //         "$type":"match",
        //         "instrument_id":"EOS-USD-SWAP",
        //         "ledger_id":"197429674941902848",
        //         "$timestamp":"2019-03-25T05:56:31.286Z"
        //     ),
        //
        $id = $this->safe_string($item, 'ledger_id');
        $account = null;
        $details = $this->safe_value($item, 'details', array());
        $referenceId = $this->safe_string($details, 'order_id');
        $referenceAccount = null;
        $type = $this->parse_ledger_entry_type ($this->safe_string($item, 'type'));
        $code = $this->safe_currency_code($this->safe_string($item, 'currency'), $currency);
        $amount = $this->safe_float($item, 'amount');
        $timestamp = $this->parse8601 ($this->safe_string($item, 'timestamp'));
        $fee = array (
            'cost' => $this->safe_float($item, 'fee'),
            'currency' => $code,
        );
        $before = null;
        $after = $this->safe_float($item, 'balance');
        $status = 'ok';
        return array (
            'info' => $item,
            'id' => $id,
            'account' => $account,
            'referenceId' => $referenceId,
            'referenceAccount' => $referenceAccount,
            'type' => $type,
            'currency' => $code,
            'amount' => $amount,
            'before' => $before, // balance $before
            'after' => $after, // balance $after
            'status' => $status,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => $fee,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $isArray = gettype ($params) === 'array' && count (array_filter (array_keys ($params), 'is_string')) == 0;
        $request = '/api/' . $api . '/' . $this->version . '/';
        $request .= $isArray ? $path : $this->implode_params($path, $params);
        $query = $isArray ? $params : $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . $request;
        $type = $this->get_path_authentication_type ($path);
        if ($type === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else if ($type === 'private') {
            $this->check_required_credentials();
            $timestamp = $this->iso8601 ($this->milliseconds ());
            $headers = array (
                'OK-ACCESS-KEY' => $this->apiKey,
                'OK-ACCESS-PASSPHRASE' => $this->password,
                'OK-ACCESS-TIMESTAMP' => $timestamp,
                // 'OK-FROM' => '',
                // 'OK-TO' => '',
                // 'OK-LIMIT' => '',
            );
            $auth = $timestamp . $method . $request;
            if ($method === 'GET') {
                if ($query) {
                    $urlencodedQuery = '?' . $this->urlencode ($query);
                    $url .= $urlencodedQuery;
                    $auth .= $urlencodedQuery;
                }
            } else {
                if ($isArray || $query) {
                    $body = $this->json ($query);
                    $auth .= $body;
                }
                $headers['Content-Type'] = 'application/json';
            }
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha256', 'base64');
            $headers['OK-ACCESS-SIGN'] = $this->decode ($signature);
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function get_path_authentication_type ($path) {
        $auth = $this->safe_value($this->options, 'auth', array());
        $key = $this->findBroadlyMatchedKey ($auth, $path);
        return $this->safe_string($auth, $key, 'private');
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        $feedback = $this->id . ' ' . $body;
        if ($code === 503) {
            throw new ExchangeError($feedback);
        }
        if (!$response) {
            return; // fallback to default error handler
        }
        $exact = $this->exceptions['exact'];
        $message = $this->safe_string($response, 'message');
        $errorCode = $this->safe_string_2($response, 'code', 'error_code');
        if (is_array($exact) && array_key_exists($errorCode, $exact)) {
            throw new $exact[$errorCode]($feedback);
        }
        if ($message !== null) {
            if (is_array($exact) && array_key_exists($message, $exact)) {
                throw new $exact[$message]($feedback);
            }
            $broad = $this->exceptions['broad'];
            $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
            if ($broadKey !== null) {
                throw new $broad[$broadKey]($feedback);
            }
            throw new ExchangeError($feedback); // unknown $message
        }
    }
}
