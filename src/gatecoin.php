<?php

namespace ccxt;

use Exception as Exception; // a common import

class gatecoin extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'gatecoin',
            'name' => 'Gatecoin',
            'rateLimit' => 2000,
            'countries' => array ( 'HK' ), // Hong Kong
            'comment' => 'a regulated/licensed exchange',
            'has' => array (
                'CORS' => false,
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchTickers' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '15m' => '15m',
                '1h' => '1h',
                '6h' => '6h',
                '1d' => '24h',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/28646817-508457f2-726c-11e7-9eeb-3528d2413a58.jpg',
                'api' => 'https://api.gatecoin.com',
                'www' => 'https://gatecoin.com',
                'doc' => array (
                    'https://gatecoin.com/api',
                    'https://github.com/Gatecoin/RESTful-API-Implementation',
                    'https://api.gatecoin.com/swagger-ui/index.html',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'Public/ExchangeRate', // Get the exchange rates
                        'Public/LiveTicker', // Get live ticker for all currency
                        'Public/LiveTicker/{CurrencyPair}', // Get live ticker by currency
                        'Public/LiveTickers', // Get live ticker for all currency
                        'Public/MarketDepth/{CurrencyPair}', // Gets prices and market depth for the currency pair.
                        'Public/NetworkStatistics/{DigiCurrency}', // Get the network status of a specific digital currency
                        'Public/StatisticHistory/{DigiCurrency}/{Typeofdata}', // Get the historical data of a specific digital currency
                        'Public/TickerHistory/{CurrencyPair}/{Timeframe}', // Get ticker history
                        'Public/Transactions/{CurrencyPair}', // Gets recent transactions
                        'Public/TransactionsHistory/{CurrencyPair}', // Gets all transactions
                        'Reference/BusinessNatureList', // Get the business nature list.
                        'Reference/Countries', // Get the country list.
                        'Reference/Currencies', // Get the currency list.
                        'Reference/CurrencyPairs', // Get the currency pair list.
                        'Reference/CurrentStatusList', // Get the current status list.
                        'Reference/IdentydocumentTypes', // Get the different types of identity documents possible.
                        'Reference/IncomeRangeList', // Get the income range list.
                        'Reference/IncomeSourceList', // Get the income source list.
                        'Reference/VerificationLevelList', // Get the verif level list.
                        'Stream/PublicChannel', // Get the public pubnub channel list
                    ),
                    'post' => array (
                        'Export/Transactions', // Request a export of all trades from based on currencypair, start date and end date
                        'Ping', // Post a string, then get it back.
                        'Public/Unsubscribe/{EmailCode}', // Lets the user unsubscribe from emails
                        'RegisterUser', // Initial trader registration.
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'Account/CorporateData', // Get corporate account data
                        'Account/DocumentAddress', // Check if residence proof uploaded
                        'Account/DocumentCorporation', // Check if registered document uploaded
                        'Account/DocumentID', // Check if ID document copy uploaded
                        'Account/DocumentInformation', // Get Step3 Data
                        'Account/Email', // Get user email
                        'Account/FeeRate', // Get fee rate of logged in user
                        'Account/Level', // Get verif level of logged in user
                        'Account/PersonalInformation', // Get Step1 Data
                        'Account/Phone', // Get user phone number
                        'Account/Profile', // Get trader profile
                        'Account/Questionnaire', // Fill the questionnaire
                        'Account/Referral', // Get referral information
                        'Account/ReferralCode', // Get the referral code of the logged in user
                        'Account/ReferralNames', // Get names of referred traders
                        'Account/ReferralReward', // Get referral reward information
                        'Account/ReferredCode', // Get referral code
                        'Account/ResidentInformation', // Get Step2 Data
                        'Account/SecuritySettings', // Get verif details of logged in user
                        'Account/User', // Get all user info
                        'APIKey/APIKey', // Get API Key for logged in user
                        'Auth/ConnectionHistory', // Gets connection history of logged in user
                        'Balance/Balances', // Gets the available balance for each currency for the logged in account.
                        'Balance/Balances/{Currency}', // Gets the available balance for s currency for the logged in account.
                        'Balance/Deposits', // Get all account deposits, including wire and digital currency, of the logged in user
                        'Balance/Withdrawals', // Get all account withdrawals, including wire and digital currency, of the logged in user
                        'Bank/Accounts/{Currency}/{Location}', // Get internal bank account for deposit
                        'Bank/Transactions', // Get all account transactions of the logged in user
                        'Bank/UserAccounts', // Gets all the bank accounts related to the logged in user.
                        'Bank/UserAccounts/{Currency}', // Gets all the bank accounts related to the logged in user.
                        'ElectronicWallet/DepositWallets', // Gets all crypto currency addresses related deposits to the logged in user.
                        'ElectronicWallet/DepositWallets/{DigiCurrency}', // Gets all crypto currency addresses related deposits to the logged in user by currency.
                        'ElectronicWallet/Transactions', // Get all digital currency transactions of the logged in user
                        'ElectronicWallet/Transactions/{DigiCurrency}', // Get all digital currency transactions of the logged in user
                        'ElectronicWallet/UserWallets', // Gets all external digital currency addresses related to the logged in user.
                        'ElectronicWallet/UserWallets/{DigiCurrency}', // Gets all external digital currency addresses related to the logged in user by currency.
                        'Info/ReferenceCurrency', // Get user's reference currency
                        'Info/ReferenceLanguage', // Get user's reference language
                        'Notification/Messages', // Get from oldest unread . 3 read message to newest messages
                        'Trade/Orders', // Gets open orders for the logged in trader.
                        'Trade/Orders/{OrderID}', // Gets an order for the logged in trader.
                        'Trade/StopOrders', // Gets all stop orders for the logged in trader. Max 1000 record.
                        'Trade/StopOrdersHistory', // Gets all stop orders for the logged in trader. Max 1000 record.
                        'Trade/Trades', // Gets all transactions of logged in user
                        'Trade/UserTrades', // Gets all transactions of logged in user
                    ),
                    'post' => array (
                        'Account/DocumentAddress', // Upload address proof document
                        'Account/DocumentCorporation', // Upload registered document document
                        'Account/DocumentID', // Upload ID document copy
                        'Account/Email/RequestVerify', // Request for verification email
                        'Account/Email/Verify', // Verification email
                        'Account/GoogleAuth', // Enable google auth
                        'Account/Level', // Request verif level of logged in user
                        'Account/Questionnaire', // Fill the questionnaire
                        'Account/Referral', // Post a referral email
                        'APIKey/APIKey', // Create a new API key for logged in user
                        'Auth/ChangePassword', // Change password.
                        'Auth/ForgotPassword', // Request reset password
                        'Auth/ForgotUserID', // Request user id
                        'Auth/Login', // Trader session log in.
                        'Auth/Logout', // Logout from the current session.
                        'Auth/LogoutOtherSessions', // Logout other sessions.
                        'Auth/ResetPassword', // Reset password
                        'Bank/Transactions', // Request a transfer from the traders account of the logged in user. This is only available for bank account
                        'Bank/UserAccounts', // Add an account the logged in user
                        'ElectronicWallet/DepositWallets/{DigiCurrency}', // Add an digital currency addresses to the logged in user.
                        'ElectronicWallet/Transactions/Deposits/{DigiCurrency}', // Get all internal digital currency transactions of the logged in user
                        'ElectronicWallet/Transactions/Withdrawals/{DigiCurrency}', // Get all external digital currency transactions of the logged in user
                        'ElectronicWallet/UserWallets/{DigiCurrency}', // Add an external digital currency addresses to the logged in user.
                        'ElectronicWallet/Withdrawals/{DigiCurrency}', // Request a transfer from the traders account to an external address. This is only available for crypto currencies.
                        'Notification/Messages', // Mark all as read
                        'Notification/Messages/{ID}', // Mark as read
                        'Trade/Orders', // Place an order at the exchange.
                        'Trade/StopOrders', // Place a stop order at the exchange.
                    ),
                    'put' => array (
                        'Account/CorporateData', // Update user company data for corporate account
                        'Account/DocumentID', // Update ID document meta data
                        'Account/DocumentInformation', // Update Step3 Data
                        'Account/Email', // Update user email
                        'Account/PersonalInformation', // Update Step1 Data
                        'Account/Phone', // Update user phone number
                        'Account/Questionnaire', // update the questionnaire
                        'Account/ReferredCode', // Update referral code
                        'Account/ResidentInformation', // Update Step2 Data
                        'Account/SecuritySettings', // Update verif details of logged in user
                        'Account/User', // Update all user info
                        'Bank/UserAccounts', // Update the label of existing user bank accounnt
                        'ElectronicWallet/DepositWallets/{DigiCurrency}/{AddressName}', // Update the name of an address
                        'ElectronicWallet/UserWallets/{DigiCurrency}', // Update the name of an external address
                        'Info/ReferenceCurrency', // User's reference currency
                        'Info/ReferenceLanguage', // Update user's reference language
                    ),
                    'delete' => array (
                        'APIKey/APIKey/{PublicKey}', // Remove an API key
                        'Bank/Transactions/{RequestID}', // Delete pending account withdraw of the logged in user
                        'Bank/UserAccounts/{Currency}/{Label}', // Delete an account of the logged in user
                        'ElectronicWallet/DepositWallets/{DigiCurrency}/{AddressName}', // Delete an digital currency addresses related to the logged in user.
                        'ElectronicWallet/UserWallets/{DigiCurrency}/{AddressName}', // Delete an external digital currency addresses related to the logged in user.
                        'Trade/Orders', // Cancels all existing order
                        'Trade/Orders/{OrderID}', // Cancels an existing order
                        'Trade/StopOrders', // Cancels all existing stop orders
                        'Trade/StopOrders/{ID}', // Cancels an existing stop order
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.0025,
                    'taker' => 0.0035,
                ),
            ),
            'commonCurrencies' => array (
                'BCP' => 'BCPT',
                'FLI' => 'FLIXX',
                'MAN' => 'MANA',
                'SLT' => 'SALT',
                'TRA' => 'TRAC',
                'WGS' => 'WINGS',
            ),
            'exceptions' => array (
                '1005' => '\\ccxt\\InsufficientFunds',
                '1008' => '\\ccxt\\OrderNotFound',
                '1057' => '\\ccxt\\InvalidOrder',
                '1044' => '\\ccxt\\OrderNotFound', // already canceled,
                '1054' => '\\ccxt\\OrderNotFound', // already executed
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetReferenceCurrencyPairs ();
        $markets = $response['currencyPairs'];
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['tradingCode'];
            $baseId = $market['baseCurrency'];
            $quoteId = $market['quoteCurrency'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => 8,
                'price' => $market['priceDecimalPlaces'],
            );
            $limits = array (
                'amount' => array (
                    'min' => pow (10, -$precision['amount']),
                    'max' => null,
                ),
                'price' => array (
                    'min' => pow (10, -$precision['amount']),
                    'max' => null,
                ),
                'cost' => array (
                    'min' => null,
                    'max' => null,
                ),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalanceBalances ();
        $balances = $response['balances'];
        $result = array ( 'info' => $balances );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currencyId = $balance['currency'];
            $code = $currencyId;
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id))
                $code = $this->currencies_by_id[$currencyId]['code'];
            $account = array (
                'free' => $balance['availableBalance'],
                'used' => $this->sum (
                    $balance['pendingIncoming'],
                    $balance['pendingOutgoing'],
                    $balance['openOrder']
                ),
                'total' => $balance['balance'],
            );
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $orderbook = $this->publicGetPublicMarketDepthCurrencyPair (array_merge (array (
            'CurrencyPair' => $market['id'],
        ), $params));
        return $this->parse_order_book($orderbook, null, 'bids', 'asks', 'price', 'volume');
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetTradeOrdersOrderID (array_merge (array (
            'OrderID' => $id,
        ), $params));
        return $this->parse_order($response->order);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = intval ($ticker['createDateTime']) * 1000;
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $baseVolume = $this->safe_float($ticker, 'volume');
        $vwap = $this->safe_float($ticker, 'vwap');
        $quoteVolume = $baseVolume * $vwap;
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $this->safe_float($ticker, 'open'),
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

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetPublicLiveTickers ($params);
        $tickers = $response['tickers'];
        $result = array ();
        for ($t = 0; $t < count ($tickers); $t++) {
            $ticker = $tickers[$t];
            $id = $ticker['currencyPair'];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetPublicLiveTickerCurrencyPair (array_merge (array (
            'CurrencyPair' => $market['id'],
        ), $params));
        $ticker = $response['ticker'];
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $side = null;
        $orderId = null;
        if (is_array ($trade) && array_key_exists ('way', $trade)) {
            $side = ($trade['way'] === 'bid') ? 'buy' : 'sell';
            $orderIdField = $trade['way'] . 'OrderId';
            $orderId = $this->safe_string($trade, $orderIdField);
        }
        $timestamp = intval ($trade['transactionTime']) * 1000;
        if ($market === null) {
            $marketId = $this->safe_string($trade, 'currencyPair');
            if ($marketId !== null)
                $market = $this->find_market($marketId);
        }
        $fee = null;
        $feeCost = $this->safe_float($trade, 'feeAmount');
        $price = $trade['price'];
        $amount = $trade['quantity'];
        $cost = $price * $amount;
        $feeCurrency = null;
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = $market['quote'];
        }
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
                'rate' => $this->safe_float($trade, 'feeRate'),
            );
        }
        return array (
            'info' => $trade,
            'id' => $this->safe_string($trade, 'transactionId'),
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
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
        $response = $this->publicGetPublicTransactionsCurrencyPair (array_merge (array (
            'CurrencyPair' => $market['id'],
        ), $params));
        return $this->parse_trades($response['transactions'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            intval ($ohlcv['createDateTime']) * 1000,
            $ohlcv['open'],
            $ohlcv['high'],
            $ohlcv['low'],
            $ohlcv['last'],
            $ohlcv['volume'],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'CurrencyPair' => $market['id'],
            'Timeframe' => $this->timeframes[$timeframe],
        );
        if ($limit !== null)
            $request['Count'] = $limit;
        $request = array_merge ($request, $params);
        $response = $this->publicGetPublicTickerHistoryCurrencyPairTimeframe ($request);
        $ohlcvs = $this->parse_ohlcvs($response['tickers'], $market, $timeframe, $since, $limit);
        return $this->sort_by($ohlcvs, 0);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'Code' => $this->market_id($symbol),
            'Way' => ($side === 'buy') ? 'Bid' : 'Ask',
            'Amount' => $amount,
        );
        if ($type === 'limit')
            $order['Price'] = $price;
        if ($this->twofa) {
            if (is_array ($params) && array_key_exists ('ValidationCode', $params))
                $order['ValidationCode'] = $params['ValidationCode'];
            else
                throw new AuthenticationError ($this->id . ' two-factor authentication requires a missing ValidationCode parameter');
        }
        $response = $this->privatePostTradeOrders (array_merge ($order, $params));
        // At this point $response['responseStatus']['message'] has been verified in handleErrors ()
        // to be == 'OK', so we assume the $order has indeed been opened
        return array (
            'info' => $response,
            'status' => 'open',
            'id' => $this->safe_string($response, 'clOrderId'), // $response['clOrderId'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateDeleteTradeOrdersOrderID (array ( 'OrderID' => $id ));
        return $response;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '1' => 'open', // New
            '2' => 'open', // Filling
            '4' => 'canceled',
            '6' => 'closed',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses))
            return $statuses[$status];
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $side = ($order['side'] === 0) ? 'buy' : 'sell';
        $type = ($order['type'] === 0) ? 'limit' : 'market';
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'code');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                $market = $this->markets_by_id[$marketId];
        }
        if ($market !== null)
            $symbol = $market['symbol'];
        $timestamp = intval ($order['date']) * 1000;
        $amount = $order['initialQuantity'];
        $remaining = $order['remainingQuantity'];
        $filled = $amount - $remaining;
        $price = $order['price'];
        $cost = $price * $filled;
        $id = $order['clOrderId'];
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $trades = null;
        $fee = null;
        if ($status === 'closed') {
            $tradesFilled = null;
            $tradesCost = null;
            $trades = array ();
            $transactions = $this->safe_value($order, 'trades');
            $feeCost = null;
            $feeCurrency = null;
            $feeRate = null;
            if ($transactions !== null) {
                if (gettype ($transactions) === 'array' && count (array_filter (array_keys ($transactions), 'is_string')) == 0) {
                    for ($i = 0; $i < count ($transactions); $i++) {
                        $trade = $this->parse_trade($transactions[$i]);
                        if ($tradesFilled === null)
                            $tradesFilled = 0.0;
                        if ($tradesCost === null)
                            $tradesCost = 0.0;
                        $tradesFilled .= $trade['amount'];
                        $tradesCost .= $trade['amount'] * $trade['price'];
                        if (is_array ($trade) && array_key_exists ('fee', $trade)) {
                            if ($trade['fee']['cost'] !== null) {
                                if ($feeCost === null)
                                    $feeCost = 0.0;
                                $feeCost .= $trade['fee']['cost'];
                            }
                            $feeCurrency = $trade['fee']['currency'];
                            if ($trade['fee']['rate'] !== null) {
                                if ($feeRate === null)
                                    $feeRate = 0.0;
                                $feeRate .= $trade['fee']['rate'];
                            }
                        }
                        $trades[] = $trade;
                    }
                    if (($tradesFilled !== null) && ($tradesFilled > 0))
                        $price = $tradesCost / $tradesFilled;
                    if ($feeRate !== null) {
                        $numTrades = is_array ($trades) ? count ($trades) : 0;
                        if ($numTrades > 0)
                            $feeRate = $feeRate / $numTrades;
                    }
                    if ($feeCost !== null) {
                        $fee = array (
                            'cost' => $feeCost,
                            'currency' => $feeCurrency,
                            'rate' => $feeRate,
                        );
                    }
                }
            }
        }
        $result = array (
            'id' => $id,
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'cost' => $cost,
            'trades' => $trades,
            'fee' => $fee,
            'info' => $order,
        );
        return $result;
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetTradeOrders ();
        $orders = $this->parse_orders($response['orders'], null, $since, $limit);
        if ($symbol !== null)
            return $this->filter_by_symbol($orders, $symbol);
        return $orders;
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $nonceString = (string) $nonce;
            $contentType = ($method === 'GET') ? '' : 'application/json';
            $auth = $method . $url . $contentType . $nonceString;
            $auth = strtolower ($auth);
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha256', 'base64');
            $headers = array (
                'API_PUBLIC_KEY' => $this->apiKey,
                'API_REQUEST_SIGNATURE' => $this->decode ($signature),
                'API_REQUEST_DATE' => $nonceString,
            );
            if ($method !== 'GET') {
                $headers['Content-Type'] = $contentType;
                $body = $this->json (array_merge (array ( 'nonce' => $nonce ), $params));
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'DigiCurrency' => $currency['id'],
            'Address' => $address,
            'Amount' => $amount,
        );
        $response = $this->privatePostElectronicWalletWithdrawalsDigiCurrency (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'id'),
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'DigiCurrency' => $currency['id'],
        );
        $response = $this->privateGetElectronicWalletDepositWalletsDigiCurrency (array_merge ($request, $params));
        $result = $response['addresses'];
        $numResults = is_array ($result) ? count ($result) : 0;
        if ($numResults < 1)
            throw new InvalidAddress ($this->id . ' privateGetElectronicWalletDepositWalletsDigiCurrency() returned no addresses');
        $address = $this->safe_string($result[0], 'address');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'info' => $response,
        );
    }

    public function create_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'DigiCurrency' => $currency['id'],
        );
        $response = $this->privatePostElectronicWalletDepositWalletsDigiCurrency (array_merge ($request, $params));
        $address = $response['address'];
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'info' => $response,
        );
    }

    public function create_user_wallet ($code, $address, $name, $password, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'DigiCurrency' => $currency['id'],
            'AddressName' => $name,
            'Address' => $address,
            'Password' => $password,
        );
        // not unified yet
        return $this->privatePostElectronicWalletUserWalletsDigiCurrency (array_merge ($request, $params));
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (mb_strpos ($body, 'You are not authorized') !== false) {
            throw new PermissionDenied ($body);
        }
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('responseStatus', $response)) {
                $errorCode = $this->safe_string($response['responseStatus'], 'errorCode');
                $message = $this->safe_string($response['responseStatus'], 'message');
                $feedback = $this->id . ' ' . $body;
                if ($errorCode !== null) {
                    $exceptions = $this->exceptions;
                    if (is_array ($exceptions) && array_key_exists ($errorCode, $exceptions)) {
                        throw new $exceptions[$errorCode] ($feedback);
                    }
                    throw new ExchangeError ($feedback);
                // Sometimes there isn't 'errorCode' but 'message' is present and is not 'OK'
                } else if ($message !== null && $message !== 'OK') {
                    throw new ExchangeError ($feedback);
                }
            }
        }
    }
}
