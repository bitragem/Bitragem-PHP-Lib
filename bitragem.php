<?php

/**
 * Bitragem's official php lib
 * v 1.1.2
 * https://bitragem.com/
 */

namespace bitragem;

abstract class Bitragem
{

    /**
     * Get Array from JSON URL
     * @param String $input
     * @return Array
     * Ex.:
     * get_url_contents('https://bitragem.com/api/v1/');
     */
    public static function get_url_contents($input)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $input,
            CURLOPT_REFERER => $input,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => "gzip",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));

        $response  = curl_exec($curl);
        curl_close($curl);
        return (json_decode($response, true));
    }
    /**
     * Get Array from JSON File
     * @param String $input
     * @return Array
     * Ex.:
     * get_file_contents('config.json');
     */
    public function get_file_contents($input)
    {
        $json = file_get_contents($input);
        return (json_decode($json, true));
    }
    /**
     * Get Class Name
     * @param String $input
     * @return String
     * Ex.:
     * get_id(__CLASS__);
     */
    public static function get_id($input)
    {
        return substr(strrchr($input, "\\"), 1);
    }
}

class _3xbit extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'DASH', 'DOGE', 'ETH', 'LTC', 'SMART'));
    /**
     * Get Array of orders
     * @param String $asset
     * @return Array
     * Ex.:
     * getBook('BTC');
     */
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.exchange.3xbit.com.br/v1/orderbook/credit/' . $asset);
        $ticker = self::get_url_contents('https://api.exchange.3xbit.com.br/ticker/brl/')['CREDIT_' . $asset];

        for ($i = 0; $i < count($book['sell_orders']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['sell_orders'][$i]['unit_price'] * $ticker['dollar_brl'],
                'volume' => floatval($book['sell_orders'][$i]['quantity']),
            );
        }
        for ($i = 0; $i < count($book['buy_orders']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['buy_orders'][$i]['unit_price'] * $ticker['dollar_brl'],
                'volume' => floatval($book['buy_orders'][$i]['quantity']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://api.exchange.3xbit.com.br/ticker/brl/')['CREDIT_' . $asset];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://api.exchange.3xbit.com.br/ticker/');
        $summaries['id'] = self::get_id(__CLASS__);
        foreach ($api as $key) {
            if ($key['market'] == 'BTC') {
                $summaries['tickers']['BTC'][] = array(
                    'symbol' => $key['symbol'],
                    'ask' => floatval($key['ask']),
                    'bid' => floatval($key['bid']),
                    'vol' => $key['volume'],
                );
            }
        }

        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class acasadobitcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;

        switch ($asset) {
            case 'BTC':
                $asset = 1;
                break;
            case 'ETH':
                $asset = 2;
                break;
        }
        $book = self::get_url_contents('https://apicasabitcoinprod.alphapoint.com:8443/AP/GetL2Snapshot?OMSId=1&InstrumentId=' . $asset . '&Depth=1');

        for ($i = 0; $i < count($book); $i++) {
            if ($book[$i][9] == 1) {
                $newBook['asks'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            } else {
                $newBook['bids'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            }
        }

        return $newBook;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class acessobitcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH'));
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://www.acessobitcoin.com/api/' . strtolower($asset) . '/market/');
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitblue extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'DASH'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://bitblue.com/api/order-book/' . strtolower($asset))['order-book'];
        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['ask'][$i]['price'],
                'volume' => $book['ask'][$i]['order_amount'],
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bid'][$i]['price'],
                'volume' => $book['bid'][$i]['order_amount'],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://bitblue.com/api/stats?market=' . $asset)['stats'];
        $ticker['last'] = $data['last_price'];
        $ticker['ask'] = $data['ask'];
        $ticker['bid'] = $data['bid'];
        $ticker['vol'] = 0;
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitinka extends Bitragem
{
    private static $markets = array(
        'BRL' => array('BTC', 'ETH', 'DASH'),
        'COP' => array('BTC', 'ETH', 'DASH'),
        'EUR' => array('BTC', 'ETH', 'DASH'),
        'USD' => array('BTC', 'ETH', 'DASH')
    );
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://www.bitinka.com/api/apinka/order_book/' . $asset . '_BRL?format=json')['orders'];

        for ($i = 0; $i < count($book['purchases']['BRL']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['purchases']['BRL'][$i]['Price'],
                'volume' => $book['purchases']['BRL'][$i]['Amount'],
            );
        }
        for ($i = 0; $i < count($book['sales']['BRL']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['sales']['BRL'][$i]['Price'],
                'volume' => $book['sales']['BRL'][$i]['Amount'],
            );
        }

        return $newBook;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitcambio extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://bitcambio_api.blinktrade.com/api/v1/BRL/orderbook');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['asks'][$i][0],
                'volume' => $book['asks'][$i][1],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i][0],
                'volume' => $book['bids'][$i][1],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://bitcambio_api.blinktrade.com/api/v1/BRL/ticker?crypto_currency=' . $asset);
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['vol'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitcointoyou extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api_v1.bitcointoyou.com/orderbook.aspx');
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['asks'][$i][0],
                'volume' => $book['asks'][$i][1],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i][0],
                'volume' => $book['bids'][$i][1],
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api_v1.bitcointoyou.com/ticker.aspx')['ticker'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}
/* nao existe mais */
class bitja extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'DASH', 'DCR', 'XLM'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.bitja.com.br/api/coins/ticker/' . strtolower($asset) . 'brl');

        $newBook['asks'][] = array(
            'price' => floatval($book['price_sell']),
            'volume' => 1,
        );

        $newBook['bids'][] = array(
            'price' => floatval($book['price_buy']),
            'volume' => 1,
        );

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.bitja.com.br/api/coins/ticker/' . strtolower($asset) . 'brl');
        $ticker['last'] = null;
        $ticker['ask'] = floatval($data['price_sell']);
        $ticker['bid'] = floatval($data['price_buy']);
        $ticker['vol'] = floatval($data['volume24h']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitnuvem extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://bitnuvem.com/api/BTC/orderbook');
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://bitnuvem.com/api/BTC/ticker')['ticker'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitpreco extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.bitpreco.com/btc-brl/orderbook');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i]['price']),
                'volume' => floatval($book['asks'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i]['price']),
                'volume' => floatval($book['bids'][$i]['amount']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.bitpreco.com/btc-brl/ticker');
        $ticker['last'] = $data['last'];
        $ticker['ask'] = null;
        $ticker['bid'] = null;
        $ticker['vol'] = $data['vol'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitrecife extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'SMART'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://exchange.bitrecife.com.br/api/v3/public/getorderbook?market=' . $asset . '_BRL');

        for ($i = 0; $i < count($book['result']['sell']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['result']['sell'][$i]['Rate']),
                'volume' => floatval($book['result']['sell'][$i]['Quantity']),
            );
        }
        for ($i = 0; $i < count($book['result']['buy']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['result']['buy'][$i]['Rate']),
                'volume' => floatval($book['result']['buy'][$i]['Quantity']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://exchange.bitrecife.com.br/api/v3/public/getmarketsummary?market=' . $asset . '_BRL')['result'][0];
        $ticker['last'] = $data['Last'];
        $ticker['ask'] = $data['Ask'];
        $ticker['bid'] = $data['Bid'];
        $ticker['vol'] = $data['Volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitcointrade extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'ETH', 'LTC', 'XRP'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.bitcointrade.com.br/v2/public/BRL' . $asset . '/orders');
        for ($i = 0; $i < count($book['data']['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['data']['asks'][$i]['unit_price'],
                'volume' => $book['data']['asks'][$i]['amount'],
            );
        }
        for ($i = 0; $i < count($book['data']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['data']['bids'][$i]['unit_price'],
                'volume' => $book['data']['bids'][$i]['amount'],
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.bitcointrade.com.br/v2/public/BRL' . $asset . '/ticker')['data'];
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}
/* não existe mais */
class brabex extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://exchange.brabex.com.br/api/v1/BRL/orderbook?crypto_currency=BTC');
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://exchange.brabex.com.br/api/v1/BRL/ticker?crypto_currency=BTC');
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['vol'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class brasilbitcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://brasilbitcoin.com.br/API/orderbook/BTC');
        for ($i = 0; $i < count($book['sell']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['sell'][$i]['preco']),
                'volume' => floatval($book['sell'][$i]['quantidade']),
            );
        }
        for ($i = 0; $i < count($book['buy']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['buy'][$i]['preco']),
                'volume' => floatval($book['buy'][$i]['quantidade']),
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://brasilbitcoin.com.br/API/prices/BTC');
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class braziliex extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'BTG', 'ETH', 'LTC', 'DASH', 'XRP'));
    private static $entryPoint = 'https://braziliex.com/api/v1/public';
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents(self::$entryPoint . '/orderbook/' . strtolower($asset) . '_brl');
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['asks'][$i]['price'],
                'volume' => $book['asks'][$i]['amount'],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i]['price'],
                'volume' => $book['bids'][$i]['amount'],
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://braziliex.com/api/v1/public/ticker/' . strtolower($asset) . '_brl');
        $ticker['last'] = $data['lowestAsk'];
        $ticker['ask'] = $data['lowestAsk'];
        $ticker['bid'] = $data['highestBid'];
        $ticker['vol'] = $data['baseVolume24'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://braziliex.com/api/v1/public/ticker');
        $summaries['id'] = self::get_id(__CLASS__);
        foreach ($api as $key => $value) {
            $splited = explode('_', strtoupper($key));
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => strtoupper($splited[0]),
                'ask' => $value['lowestAsk'],
                'bid' => $value['highestBid'],
                'vol' => $value['quoteVolume'],
            );
        }
        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/currencies');
        $walletsStatus['id'] = self::get_id(__CLASS__);
        foreach ($res as $currencies => $currency) {
            if ($currency['is_withdrawal_active'] == 1 && $currency['is_deposit_active'] == 1) {
                $walletsStatus['online'][] = $currency['name'];
            } else {
                $walletsStatus['offline'][] = $currency['name'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = get_url_contents(self::$entryPoint . '/currencies');
        $walletsStatus['id'] = self::get_id(__CLASS__);
        foreach ($res as $currencies => $currency) {
            $withdrawalFees['id'] = self::get_id(__CLASS__);
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => strtoupper($currencies),
                'name' => $currency['name'],
                'withdrawalFee' => $currency['txWithdrawalFee']
            );
            return $withdrawalFees;
        }
        return $walletsStatus;
    }
}

class btcbolsa extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'LTC'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://apiv2.btcbolsa.com/v2/orders/order-book/' . $asset . '_BRL');
        for ($i =  0; $i < count($book['data']['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['data']['asks'][$i]['price_unity']),
                'volume' => floatval($book['data']['asks'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($book['data']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['data']['bids'][$i]['price_unity']),
                'volume' => floatval($book['data']['bids'][$i]['amount']),
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://apiv2.btcbolsa.com/v2/orders/ticker/' . $asset . '_BRL')['data'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['buy']);
        $ticker['bid'] = floatval($data['sell']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class coinbene extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'DASH', 'ETH', 'LTC', 'SMART', 'EOS', 'USDT', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('http://api.coinbene.com/v1/market/orderbook?symbol=' . $asset . 'brl');

        for ($i = 0; $i < count($book['orderbook']['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['orderbook']['asks'][$i]['price'],
                'volume' => $book['orderbook']['asks'][$i]['quantity'],
            );
        }
        for ($i = 0; $i < count($book['orderbook']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['orderbook']['bids'][$i]['price'],
                'volume' => $book['orderbook']['bids'][$i]['quantity'],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('http://api.coinbene.com/v1/market/ticker?symbol=' . $asset . 'brl')['ticker'][0];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['24hrVol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('http://api.coinbene.com/v1/market/ticker?symbol=all')['ticker'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $currencyName = $api[$i]['symbol'] . '-';
            if (substr_count($currencyName, 'BTC-') == 1) {
                $currencyName = str_replace('BTC-', '', $currencyName);

                $summaries['tickers']['BTC'][] = array(
                    'symbol' => $currencyName,
                    'ask' => floatval($api[$i]['ask']),
                    'bid' => floatval($api[$i]['bid']),
                    'vol' => floatval($api[$i]['24hrVol']),
                );
            }
        }

        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class coinext extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;

        switch ($asset) {
            case 'BTC':
                $asset = 1;
                break;
            case 'ETH':
                $asset = 4;
                break;
            case 'LTC':
                $asset = 2;
                break;
            case 'XRP':
                $asset = 6;
                break;
        }

        $book = self::get_url_contents('https://api.coinext.com.br:8443/AP/GetL2Snapshot?OMSId=1&InstrumentId=' . $asset . '&Depth=1');

        for ($i = 0; $i < count($book); $i++) {
            if ($book[$i][9] == 1) {
                $newBook['asks'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            } else {
                $newBook['bids'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            }
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        switch ($asset) {
            case 'BTC':
                $asset = 1;
                break;
            case 'ETH':
                $asset = 4;
                break;
            case 'LTC':
                $asset = 2;
                break;
            case 'XRP':
                $asset = 6;
                break;
        }

        $data = self::get_url_contents('http://apex.coinext.com.br/tickers')[$asset];
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class citcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.citcoin.com.br/v1/btc/orderbook/');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i]['btc_price']),
                'volume' => floatval($book['ask'][$i]['btc']),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i]['btc_price']),
                'volume' => floatval($book['bid'][$i]['btc']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.citcoin.com.br/v1/btc/ticker/');
        $ticker['last'] = null;
        $ticker['ask'] = null;
        $ticker['bid'] = null;
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class cryptomarket extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'EOS'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $bookAsk = self::get_url_contents('https://api.cryptomkt.com/v1/book?market=' . $asset . 'BRL&type=sell&page=0');
        $bookBid = self::get_url_contents('https://api.cryptomkt.com/v1/book?market=' . $asset . 'BRL&type=buy&page=0');

        for ($i = 0; $i < count($bookAsk['data']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($bookAsk['data'][$i]['price']),
                'volume' => floatval($bookAsk['data'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($bookBid['data']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($bookBid['data'][$i]['price']),
                'volume' => floatval($bookBid['data'][$i]['amount']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.cryptomkt.com/v1/ticker?market=' . $asset . 'BRL')['data'][0];
        $ticker['last'] = floatval($data['last_price']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class flowbtc extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'ETH', 'EOS', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://publicapi.flowbtc.com.br/v1/book/' . $asset . 'BRL?depth=3000');

        for ($i = 0; $i < count($book['data']['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['data']['asks'][$i]['Price']),
                'volume' => floatval($book['data']['asks'][$i]['Quantity']),
            );
        }
        for ($i = 0; $i < count($book['data']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['data']['bids'][$i]['Price']),
                'volume' => floatval($book['data']['bids'][$i]['Quantity']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://publicapi.flowbtc.com.br/ticker/' . $asset . 'BRL')['data'];
        $ticker['last'] = floatval($data['LastTradedPx']);
        $ticker['ask'] = floatval($data['BestOffer']);
        $ticker['bid'] = floatval($data['BestBid']);
        $ticker['vol'] = floatval($data['Rolling24HrVolume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class foxbit extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'TUSD'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;

        switch ($asset) {
            case 'BTC':
                $asset = 1;
                break;
            case 'LTC':
                $asset = 2;
                break;
            case 'ETH':
                $asset = 4;
                break;
            case 'TUSD':
                $asset = 6;
                break;
        }

        $book = self::get_url_contents('https://apifoxbitprodlb.alphapoint.com:8443/AP/GetL2Snapshot?OMSId=1&InstrumentId=' . $asset . '&Depth=1');

        for ($i = 0; $i < count($book); $i++) {
            if ($book[$i][9] == 1) {
                $newBook['asks'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            } else {
                $newBook['bids'][] = array(
                    'price' => $book[$i][6],
                    'volume' => $book[$i][8],
                );
            }
        }

        return $newBook;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class mercadobitcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'BTG', 'ETH', 'LTC', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://www.mercadobitcoin.net/api/' . $asset . '/orderbook/');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://www.mercadobitcoin.net/api/' . $asset . '/ticker/')['ticker'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class modiax extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://app.modiax.com/api/v2/order_book?market=' . strtolower($asset) . 'brl');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i]['price']),
                'volume' => floatval($book['asks'][$i]['volume']),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i]['price']),
                'volume' => floatval($book['bids'][$i]['volume']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://app.modiax.com/api/v2/tickers/' . strtolower($asset) . 'brl')['ticker'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class newcash extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BTG', 'LTC', 'DASH', 'DOGE'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://newcash.exchange/apiv2/ordens/' . $asset . ':BRL');
        for ($i = 0; $i < count($book['venda']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['venda'][$i]['preco']),
                'volume' => floatval($book['venda'][$i]['volume']),
            );
        }
        for ($i = 0; $i < count($book['compra']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['compra'][$i]['preco']),
                'volume' => floatval($book['compra'][$i]['volume']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://newcash.exchange/apiv2/ticket/market/' . $asset . ':BRL')['market'];
        $ticker['last'] = floatval($data['lastPrice']);
        $ticker['ask'] = floatval($data['sellPrice']);
        $ticker['bid'] = floatval($data['buyPrice']);
        $ticker['vol'] = floatval($data['volumeCurrency']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class omnitrade extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'BTG', 'DCR', 'DASH', 'ETH', 'LTC', 'XRP'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://omnitrade.io/api/v2/order_book?market=' . strtolower($asset) . 'brl&asks_limit=200&bids_limit=200');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i]['price']),
                'volume' => floatval($book['asks'][$i]['volume']),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i]['price']),
                'volume' => floatval($book['bids'][$i]['volume']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://omnitrade.io/api/v2/tickers/' . strtolower($asset) . 'brl')['ticker'];
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class pagcripto extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.pagcripto.com.br/v1/BTC/orders');

        for ($i = 0; $i < count($book['data']['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['data']['asks'][$i]['cot']),
                'volume' => floatval($book['data']['asks'][$i]['qtd']),
            );
        }
        for ($i = 0; $i < count($book['data']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['data']['bids'][$i]['cot']),
                'volume' => floatval($book['data']['bids'][$i]['qtd']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.pagcripto.com.br/v1/BTC/ticker')['data'];
        $ticker['last'] = $data['last'];
        $ticker['ask'] = floatval($data['sell']);
        $ticker['bid'] = floatval($data['buy']);
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class pitaiatrade extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.pitaiatrade.com/v1/orderbook');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['asks'][$i][1],
                'volume' => $book['asks'][$i][0],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i][1],
                'volume' => $book['bids'][$i][0],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.pitaiatrade.com/v1/ticker')['ticker'];
        $ticker['last'] = null;
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class profitfy extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'DCR', 'LTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://profitfy.trade/api/v1/public/orderbook/BRL/' . strtolower($asset));

        for ($i = 0; $i < count($book[0]['sell']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book[0]['sell'][$i]['price'],
                'volume' => $book[0]['sell'][$i]['amount'],
            );
        }
        for ($i = 0; $i < count($book[0]['buy']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book[0]['buy'][$i]['price'],
                'volume' => $book[0]['buy'][$i]['amount'],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://profitfy.trade/api/v1/public/ticker/BTC/BRL')[0];
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class satoshitango extends Bitragem
{
    private static $markets = array(
        'BRL' => array('BTC', 'BCH', 'ETH', 'LTC', 'XRP'),
        'EUR' => array('BTC', 'BCH', 'ETH', 'LTC', 'XRP'),
        'USD' => array('BTC', 'BCH', 'ETH', 'LTC', 'XRP')
    );

    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.satoshitango.com/v3/ticker/' + $market)['data']['ticker'][$asset];

        $newBook['asks'][] = array(
            'price' => $book['ask'],
            'volume' => 100,
        );

        $newBook['bids'][] = array(
            'price' => $book['bid'],
            'volume' => 100,
        );

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.satoshitango.com/v3/ticker/' + $market)['data']['ticker'][$asset];
        $ticker['last'] = null;
        $ticker['ask'] = $data['ask'];
        $ticker['bid'] = $data['bid'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class walltime extends Bitragem
{
    private static $markets = array('BRL' => array('BTC'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $data = self::get_url_contents('https://s3.amazonaws.com/data-production-walltime-info/production/dynamic/meta.json?now=1517922306634.319625.92');
        $book = self::get_url_contents('https://s3.amazonaws.com/data-production-walltime-info/production/dynamic/order-book/v8878cb_r' . $data["current_round"] . '_p0.json?now=1517922306795.319787.02');

        for ($i = 0; $i < count($book['xbt-brl']); $i++) {
            $newBook['asks'][] = array(
                'price' => eval('return ' . $book['xbt-brl'][$i][1] . ';') / eval('return ' . $book['xbt-brl'][$i][0] . ';'),
                'volume' => eval('return ' . $book['xbt-brl'][$i][0] . ';'),
            );
        }
        for ($i = 0; $i < count($book['brl-xbt']); $i++) {
            $newBook['bids'][] = array(
                'price' => eval('return ' . $book['brl-xbt'][$i][0] . ';') / eval('return ' . $book['brl-xbt'][$i][1] . ';'),
                'volume' => eval('return ' . $book['brl-xbt'][$i][1] . ';'),
            );
        }

        $book = self::get_url_contents('https://s3.amazonaws.com/data-production-walltime-info/production/dynamic/order-book/v8878cb_r' . $data["current_round"] . '_p1.json?now=1517922306795.319787.02');

        for ($i = 0; $i < count($book['xbt-brl']); $i++) {
            $newBook['asks'][] = array(
                'price' => eval('return ' . $book['xbt-brl'][$i][1] . ';') / eval('return ' . $book['xbt-brl'][$i][0] . ';'),
                'volume' => eval('return ' . $book['xbt-brl'][$i][0] . ';'),
            );
        }
        for ($i = 0; $i < count($book['brl-xbt']); $i++) {
            $newBook['bids'][] = array(
                'price' => eval('return ' . $book['brl-xbt'][$i][0] . ';') / eval('return ' . $book['brl-xbt'][$i][1] . ';'),
                'volume' => eval('return ' . $book['brl-xbt'][$i][1] . ';'),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://s3.amazonaws.com/data-production-walltime-info/production/dynamic/walltime-info.json')['BRL_XBT'];
        $ticker['last'] = floatval($data['last_inexact']);
        $ticker['ask'] = floatval($data['lowest_ask_inexact']);
        $ticker['bid'] = floatval($data['highest_bid_inexact']);
        $ticker['vol'] = floatval($data['quote_volume24h_inexact']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class welcoin extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH'));
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://broker.welcoin.com.br/api/v3/' . $asset . 'BRL/orderbook');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['ask'][$i]['price'],
                'volume' => $book['ask'][$i]['quantity'],
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bid'][$i]['price'],
                'volume' => $book['bid'][$i]['quantity'],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://broker.welcoin.com.br/api/v3/' . $asset . 'BRL/ticker');
        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['sell'];
        $ticker['bid'] = $data['buy'];
        $ticker['vol'] = $data['vol'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bisq extends Bitragem
{
    private static $markets = array(
        'BRL' => array('BTC'),
        'USD' => array('BTC'),
        'GBP' => array('BTC'),
        'EUR' => array('BTC'),
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://markets.bisq.network/api/offers?market=' . strtolower($asset) . '_' . strtolower($market))[strtolower($asset) . '_' . strtolower($market)];

        for ($i = 0; $i < count($book['sells']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['sells'][$i]['price']),
                'volume' => floatval($book['sells'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($book['buys']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['buys'][$i]['price']),
                'volume' => floatval($book['buys'][$i]['amount']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://markets.bisq.network/api/ticker?market=' . strtolower($asset) . '_' . strtolower($market));
        $ticker['last'] = floatval($data[0]['last']);
        $ticker['ask'] = floatval($data[0]['buy']);
        $ticker['bid'] = floatval($data[0]['sell']);
        $ticker['vol'] = floatval($data[0]['volume_left']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class criptohub extends Bitragem
{
    private static $markets = array(
        'BRL' => array(
            'BTC', 'ETH', 'LTC'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.criptohub.com.br/market/depth?symbol=' . $market . '_' . $asset . '&limit=100')['data'];
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://api.criptohub.com.br/market/get-market-summary/' . $market . '_' . $asset)['data'];
        $ticker['last'] = $data['Last'];
        $ticker['ask'] = $data['LowestAsk'];
        $ticker['bid'] = $data['HeighestBid'];
        $ticker['vol'] = $data['QuoteVolume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitbay extends Bitragem
{
    private static $markets = array(
        'EUR' => array(
            'BTC', 'BTG', 'DASH', 'ETH', 'LTC', 'XRP'
        ),
        'GBP' => array(
            'BTC', 'BTG', 'ETH', 'LTC', 'XRP'
        ),
        'USD' => array(
            'BTC', 'BTG', 'DASH', 'ETH', 'LTC', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.bitbay.net/rest/trading/orderbook/' . $asset  . '-' . $market);
        for ($i = 0; $i < count($book['sell']); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($book['sell'][$i]['ra']),
                'volume' => floatval($book['sell'][$i]['ca']),
            );
        }
        for ($i = 0; $i < count($book['buy']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['buy'][$i]['ra']),
                'volume' => floatval($book['buy'][$i]['ca']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://api.bitbay.net/rest/trading/ticker/' . $asset  . '-' . $market)['ticker'];
        $ticker['last'] = floatval($data['rate']);
        $ticker['ask'] = floatval($data['lowestAsk']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = 0;
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bitstamp extends Bitragem
{
    private static $markets = array(
        'BTC' => array(
            'BCH', 'ETH', 'LTC', 'XRP'
        ),
        'EUR' => array(
            'BCH', 'BTC', 'ETH', 'LTC', 'XRP'
        ),
        'USD' => array(
            'BCH', 'BTC', 'ETH', 'LTC', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://www.bitstamp.net/api/v2/order_book/' . strtolower($asset)  . strtolower($market) . '?group=1');
        for ($i = 0; $i < count($book['asks']); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://www.bitstamp.net/api/v2/ticker/' . strtolower($asset)  . strtolower($market));
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class coinsbit extends Bitragem
{
    private static $markets = array(
        'USD' => array(
            'BTC', 'ETH'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $bookAsk = self::get_url_contents('https://coinsbit.io/api/v1/public/book?market=' . $asset  . '_' . $market . '&side=buy&limit=200')['result']['orders'];
        for ($i = 0; $i < count($bookAsk); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($bookAsk[$i]['price']),
                'volume' => floatval($bookAsk[$i]['amount']),
            );
        }
        $bookBid = self::get_url_contents('https://coinsbit.io/api/v1/public/book?market=' . $asset  . '_' . $market . '&side=sell&limit=200')['result']['orders'];
        for ($i = 0; $i < count($bookBid); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($bookBid[$i]['price']),
                'volume' => floatval($bookBid[$i]['amount']),
            );
        }

        return $newBook;
    }
    // public static function getTicker($asset, $market)
    // {
    //     if (isset(self::$markets[$market])) {
    //         if (!in_array($asset, self::$markets[$market])) {
    //             return null;
    //         }
    //     } else {
    //         return null;
    //     }

    //     $data = self::get_url_contents('https://www.bitstamp.net/api/v2/ticker/' . strtolower($asset)  . strtolower($market));
    //     $ticker['last'] = floatval($data['last']);
    //     $ticker['ask'] = floatval($data['ask']);
    //     $ticker['bid'] = floatval($data['bid']);
    //     $ticker['vol'] = floatval($data['volume']);
    //     $ticker['id'] = self::get_id(__CLASS__);
    //     return $ticker;
    // }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://coinsbit.io/api/v1/public/tickers')['result'];
        $summaries['id'] = self::get_id(__CLASS__);
        foreach ($api as $key => $value) {
            $splited = explode('_', $key);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => strtoupper($splited[0]),
                'last' => floatval($value['ticker']['last']),
                'ask' => floatval($value['ticker']['ask']),
                'bid' => floatval($value['ticker']['bid']),
                'vol' => floatval($value['ticker']['vol']),
            );
        }
        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class cexio extends Bitragem
{
    private static $markets = array(
        'BTC' => array(
            'BCH', 'QASH', 'BTG', 'ETH', 'DASH', 'OMG', 'GUSD', 'ZEC', 'XLM', 'XRP'
        ),
        'ETH' => array(
            'QASH'
        ),
        'GBP' => array(
            'BCH', 'BTC', 'ETH', 'DASH', 'ZEC'
        ),
        'RUB' => array(
            'BTC'
        ),
        'EUR' => array(
            'BCH', 'BTC', 'BTG', 'ETH', 'DASH', 'OMG', 'GUSD', 'ZEC', 'XLM', 'XRP'
        ),
        'USD' => array(
            'BCH', 'BTC', 'BTG', 'ETH', 'DASH', 'OMG', 'GUSD', 'ZEC', 'XLM', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://cex.io/api/order_book/' . $asset . '/' . $market);

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => $book['asks'][$i][0],
                'volume' => $book['asks'][$i][1],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i][0],
                'volume' => $book['bids'][$i][1],
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://cex.io/api/ticker/' . $asset . '/' . $market);
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://cex.io/api/tickers/GBP/USD/EUR/RUB/BTC/ETH')['data'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $splited = explode(':', $api[$i]['pair']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => $api[$i]['ask'],
                'bid' => $api[$i]['bid'],
                'vol' => floatval($api[$i]['volume']),
            );
        }

        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class coinbase_pro extends Bitragem
{
    private static $markets = array(
        'BTC' => array(
            'ETH', 'XRP', 'LTC', 'BCH', 'EOS', 'XLM', 'ETC', 'MKR', 'ZEC', 'REP', 'ZRX'
        ),
        'ETH' => array(
            'BAT'
        ),
        'GBP' => array(
            'BTC', 'ETH', 'LTC', 'BCH', 'ETC'
        ),
        'EUR' => array(
            'BTC', 'ETH', 'XRP', 'LTC', 'BCH', 'EOS', 'XLM', 'ETC', 'ZRX'
        ),
        'USD' => array(
            'BTC', 'ETH', 'XRP', 'LTC', 'BCH', 'EOS', 'XLM', 'ETC', 'REP', 'ZRX'
        ),
        'USDC' => array(
            'BTC', 'ETH', 'MKR', 'ZEC', 'BAT', 'DAI', 'GNT', 'MANA', 'LOOM', 'CVC', 'DNT'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.pro.coinbase.com/products/' . $asset . '-' . $market . '/book?level=3');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://api.pro.coinbase.com/products/' . $asset . '-' . $market . '/ticker');
        $ticker['last'] = floatval($data['price']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class exmo extends Bitragem
{
    private static $markets = array(
        'EUR' => array(
            'BTC', 'ETH', 'LTC', 'XRP'
        ),
        'RUB' => array(
            'BTC', 'DASH', 'ETH', 'LTC', 'XRP'
        ),
        'USD' => array(
            'BTC', 'DASH', 'ETH', 'LTC', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.exmo.com/v1/order_book/?pair=' . $asset  . '_' . $market)[$asset  . '_' . $market];
        for ($i = 0; $i < count($book['ask']); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i][0]),
                'volume' => floatval($book['ask'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i][0]),
                'volume' => floatval($book['bid'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://api.exmo.com/v1/ticker/')[$asset  . '_' . $market];
        $ticker['last'] = floatval($data['last_trade']);
        $ticker['ask'] = floatval($data['sell_price']);
        $ticker['bid'] = floatval($data['buy_price']);
        $ticker['vol'] = floatval($data['vol']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class gemini extends Bitragem
{
    private static $markets = array(
        'USD' => array(
            'BTC', 'BCH', 'ETH', 'LTC'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.gemini.com/v1/book/' . strtolower($asset)   .  strtolower($market));
        for ($i = 0; $i < count($book['asks']); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i]['price']),
                'volume' => floatval($book['asks'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i]['price']),
                'volume' => floatval($book['bids'][$i]['amount']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $data = self::get_url_contents('https://api.exmo.com/v1/ticker/' . strtolower($asset)   .  strtolower($market));
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume']['BTC']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class kraken extends Bitragem
{
    private static $markets = array(
        'BTC' => array(
            'BCH', 'ETH', 'LTC', 'DASH', 'XRP'
        ),
        'ETH' => array(
            'BCH', 'BTC', 'LTC', 'ETH', 'DASH', 'XRP'
        ),
        'CAD' => array(
            'BCH', 'BTC', 'LTC', 'ETH', 'DASH', 'ZEC'
        ),
        'JPY' => array(
            'BCH', 'BTC', 'LTC', 'ETH', 'DASH', 'XRP'
        ),
        'EUR' => array(
            'BCH', 'BTC', 'LTC', 'ETH', 'DASH', 'XRP'
        ),
        'USD' => array(
            'BCH', 'BTC', 'LTC', 'ETH', 'DASH', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        // normalizando o asset
        $asset = $asset == 'BTC' ? 'XBT' : $asset;
        $book = self::get_url_contents('https://api.kraken.com/0/public/Depth?pair=' . strtolower($asset) . strtolower($market))['result']['X' . $asset . 'Z' . $market];

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        // normalizando o asset
        $asset = $asset == 'BTC' ? 'XBT' : $asset;
        $data = self::get_url_contents('https://api.kraken.com/0/public/Ticker?pair=' . strtolower($asset) . strtolower($market))['result']['X' . $asset . 'Z' . $market];
        $ticker['last'] = floatval($data['c'][0]);
        $ticker['ask'] = floatval($data['a'][0]);
        $ticker['bid'] = floatval($data['b'][0]);
        $ticker['vol'] = floatval($data['v'][1]);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class itbit extends Bitragem
{
    private static $markets = array(
        'EUR' => array(
            'ETH', 'BTC'
        ),
        'USD' => array(
            'ETH', 'BTC'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        // normalizando o asset
        $asset = $asset == 'BTC' ? 'XBT' : $asset;
        $book = self::get_url_contents('https://api.itbit.com/v1/markets/' . $asset . $market . '/order_book');

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        // normalizando o asset
        $asset = $asset == 'BTC' ? 'XBT' : $asset;
        $data = self::get_url_contents('https://api.itbit.com/v1/markets/' . $asset . $market . '/ticker');
        $ticker['last'] = floatval($data['lastPrice']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['volume24h']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class coinsbank extends Bitragem
{
    private static $markets = array(
        'GBP' => array(
            'BTC', 'LTC', 'XRP'
        ),
        'EUR' => array(
            'BTC', 'ETH', 'LTC', 'XRP'
        ),
        'USD' => array(
            'BTC', 'ETH', 'LTC', 'XRP'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://coinsbank.com/api/bitcoincharts/orderbook/' . $asset  . $market);
        for ($i = 0; $i < count($book['asks']); $i++) {

            $newBook['asks'][] = array(
                'price' => $book['asks'][$i][0],
                'volume' => $book['asks'][$i][1],
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['bids'][$i][0],
                'volume' => $book['bids'][$i][1],
            );
        }

        return $newBook;
    }
    // public static function getTicker($asset, $market)
    // {
    //     if (isset(self::$markets[$market])) {
    //         if (!in_array($asset, self::$markets[$market])) {
    //             return null;
    //         }
    //     } else {
    //         return null;
    //     }

    //     $data = self::get_url_contents('https://www.bitstamp.net/api/v2/ticker/' . strtolower($asset)  . strtolower($market));
    //     $ticker['last'] = floatval($data['last']);
    //     $ticker['ask'] = floatval($data['ask']);
    //     $ticker['bid'] = floatval($data['bid']);
    //     $ticker['vol'] = floatval($data['volume']);
    //     $ticker['id'] = self::get_id(__CLASS__);
    //     return $ticker;
    // }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class bittrex extends Bitragem
{
    private static $entryPoint = 'https://bittrex.com/api/v1.1/public';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/getmarketsummaries')['result'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $splited = explode('-', $api[$i]['MarketName']);
            $summaries['tickers'][$splited[0]][] = array(
                'symbol' => $splited[1],
                'ask' => $api[$i]['Ask'],
                'bid' => $api[$i]['Bid'],
                'vol' => $api[$i]['Volume'],
            );
        }

        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/getcurrencies')['result'];
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if (!$res[$i]['IsRestricted'] && $res[$i]['IsActive']) {
                $walletsStatus['online'][] = $res[$i]['CurrencyLong'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['CurrencyLong'];
            }
        }
        return $walletsStatus;
    }
}

class binance extends Bitragem
{
    private static $markets = array(
        'BTC' => array(
            'ETH', 'LTC', 'ADA', 'BTG', 'EOS', 'DASH'
        ),
        'ETH' => array(
            'ETH', 'LTC', 'ADA', 'BTG', 'EOS', 'DASH'
        )
    );
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://api.binance.com/api/v1/depth?symbol=' . $asset . $market . '&limit=10');

        for ($i = 0; $i < count($book['asks']); $i++) {

            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://api.binance.com/api/v1/ticker/24hr');
        $summaries['id'] = self::get_id(__CLASS__);

        // mercado BTC
        for ($i = 0; $i < count($api); $i++) {
            $currencyName = $api[$i]['symbol'] . '-';
            if (substr_count($currencyName, 'BTC-') == 1) {
                $currencyName = str_replace('BTC-', '', $currencyName);
                $summaries['tickers']['BTC'][] = array(
                    'symbol' => $currencyName,
                    'ask' => floatval($api[$i]['askPrice']),
                    'bid' => floatval($api[$i]['bidPrice']),
                    'vol' => floatval($api[$i]['volume']),
                );
            } else if (substr_count($currencyName, 'ETH-') == 1) {
                $currencyName = str_replace('ETH-', '', $currencyName);
                $summaries['tickers']['ETH'][] = array(
                    'symbol' => $currencyName,
                    'ask' => floatval($api[$i]['askPrice']),
                    'bid' => floatval($api[$i]['bidPrice']),
                    'vol' => floatval($api[$i]['volume']),
                );
            }
        }

        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents('https://www.binance.com/assetWithdraw/getAllAsset.html');
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i]['enableWithdraw'] && $res[$i]['enableCharge']) {
                $walletsStatus['online'][] = $res[$i]['assetName'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['assetName'];
            }
        }
        return $walletsStatus;
    }
}

class gateio extends Bitragem
{
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://data.gateio.co/api2/1/tickers');
        $summaries['id'] = self::get_id(__CLASS__);
        foreach ($api as $key => $value) {
            $splited = explode('_', strtoupper($key));
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => strtoupper($splited[0]),
                'ask' => floatval($value['lowestAsk']),
                'bid' => floatval($value['highestBid']),
                'vol' => floatval($value['quoteVolume']),
            );
        }
        return $summaries;
    }
}

class bleutrade extends Bitragem
{
    private static $entryPoint = 'https://bleutrade.com/api/v3/public';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/getmarketsummaries')['result'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $summaries['tickers'][$api[$i]['BaseAsset']][] = array(
                'symbol' => $api[$i]['MarketAsset'],
                'ask' => $api[$i]['Ask'],
                'bid' => $api[$i]['Bid'],
                'vol' => $api[$i]['Volume'],
            );
        }
        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/getassets')['result'];
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if (!$res[$i]['MaintenanceMode'] && $res[$i]['IsActive']) {
                $walletsStatus['online'][] = $res[$i]['AssetLong'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['AssetLong'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = get_url_contents(self::$entryPoint . '/getassets')['result'];
        $withdrawalFees['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => $res[$i]['Asset'],
                'name' => $res[$i]['AssetLong'],
                'withdrawalFee' => $res[$i]['WithdrawTxFee'],
            );
        }
        return $withdrawalFees;
    }
}

class exccripto extends Bitragem
{
    private static $entryPoint = 'https://trade.exccripto.com/api/v3/public';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/getmarketsummaries')['result'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $summaries['tickers'][$api[$i]['BaseAsset']][] = array(
                'symbol' => $api[$i]['MarketAsset'],
                'ask' => $api[$i]['Ask'],
                'bid' => $api[$i]['Bid'],
                'vol' => $api[$i]['Volume'],
            );
        }
        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/getassets')['result'];
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if (!$res[$i]['MaintenanceMode'] && $res[$i]['IsActive']) {
                $walletsStatus['online'][] = $res[$i]['AssetLong'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['AssetLong'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = get_url_contents(self::$entryPoint . '/getassets')['result'];
        $withdrawalFees['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => $res[$i]['Asset'],
                'name' => $res[$i]['AssetLong'],
                'withdrawalFee' => $res[$i]['WithdrawTxFee'],
            );
        }
        return $withdrawalFees;
    }
}

class hitbtc extends Bitragem
{
    private static $entryPoint = 'https://api.hitbtc.com/api/2/public';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/ticker');
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $currencyName = $api[$i]['symbol'] . '-';
            if (substr_count($currencyName, 'BTC-') == 1) {
                $currencyName = str_replace('BTC-', '', $currencyName);

                $summaries['tickers']['BTC'][] = array(
                    'symbol' => $currencyName,
                    'ask' => floatval($api[$i]['ask']),
                    'bid' => floatval($api[$i]['bid']),
                    'vol' => floatval($api[$i]['volume']),
                );
            }
        }

        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/currency');
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i]['payinEnabled'] && $res[$i]['payoutEnabled']) {
                $walletsStatus['online'][] = $res[$i]['fullName'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['fullName'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = get_url_contents(self::$entryPoint . '/currency');
        $withdrawalFees['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => $res[$i]['id'],
                'name' => $res[$i]['fullName'],
                'withdrawalFee' => isset($res[$i]['payoutFee']) ? $res[$i]['payoutFee'] : 0,
            );
        }
        return $withdrawalFees;
    }
}

class poloniex extends Bitragem
{
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://poloniex.com/public?command=returnTicker');
        $summaries['id'] = self::get_id(__CLASS__);
        foreach ($api as $key => $value) {
            $splited = explode('_', $key);
            $summaries['tickers'][$splited[0]][] = array(
                'symbol' => strtoupper($splited[1]),
                'ask' => floatval($value['lowestAsk']),
                'bid' => floatval($value['highestBid']),
                'vol' => floatval($value['quoteVolume']),
            );
        }
        return $summaries;
    }
}

class kucoin extends Bitragem
{
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://api.kucoin.com/api/v1/market/allTickers')['data']['ticker'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $splited = explode('-', $api[$i]['symbol']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => floatval($api[$i]['sell']),
                'bid' => floatval($api[$i]['buy']),
                'vol' => floatval($api[$i]['vol']),
            );
        }

        return $summaries;
    }
}

class okex extends Bitragem
{
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents('https://www.okex.com/api/spot/v3/instruments/ticker');
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $splited = explode('-', $api[$i]['instrument_id']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => floatval($api[$i]['best_ask']),
                'bid' => floatval($api[$i]['best_bid']),
                'vol' => floatval($api[$i]['base_volume_24h']),
            );
        }

        return $summaries;
    }
}

class crex24 extends Bitragem
{
    private static $entryPoint = 'https://api.crex24.com/v2/public';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/tickers');
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($api); $i++) {
            $splited = explode('-', $api[$i]['instrument']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => floatval($api[$i]['ask']),
                'bid' => floatval($api[$i]['bid']),
                'vol' => floatval($api[$i]['baseVolume']),
            );
        }

        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/currencies');
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i]['depositsAllowed'] && $res[$i]['withdrawalsAllowed']) {
                $walletsStatus['online'][] = $res[$i]['name'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['name'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = get_url_contents(self::$entryPoint . '/currencies');
        $withdrawalFees['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => $res[$i]['symbol'],
                'name' => $res[$i]['name'],
                'withdrawalFee' => $res[$i]['flatWithdrawalFee']
            );
        }
        return $withdrawalFees;
    }
}

class coinexchange extends Bitragem
{
    private static $entryPoint = 'https://www.coinexchange.io/api/v1';
    public static function getMarketSummaries()
    {
        $api = self::get_url_contents(self::$entryPoint . '/getmarketsummaries')['result'];
        $markets = self::get_url_contents(self::$entryPoint . '/getmarkets')['result'];

        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($markets); $i++) {
            if ($markets[$i]['BaseCurrencyCode'] == 'BTC') {
                $ids[] = $markets[$i]['MarketID'];
            }
        }
        if (!empty($ids)) {
            for ($i = 0; $i < count($api); $i++) {
                $id = $api[$i]['MarketID'];
                if (in_array($id, $ids)) {

                    $summaries['tickers']['BTC'][] = array(
                        'symbol' => $api[$i]['MarketAssetCode'],
                        'ask' => floatval($api[$i]['AskPrice']),
                        'bid' => floatval($api[$i]['BidPrice']),
                        'vol' => floatval($api[$i]['Volume']),
                    );
                }
            }
            return $summaries;
        }
    }
    public static function getWalletStatus()
    {
        $res = get_url_contents(self::$entryPoint . '/getcurrencies')['result'];
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i]['WalletStatus'] == 'online') {
                $walletsStatus['online'][] = $res[$i]['Name'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['Name'];
            }
        }
        return $walletsStatus;
    }
}

class cointrade extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'DASH'));
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://broker.cointrade.cx/apiv2/ordens/' . $asset . ':' . $market);

        for ($i = 0; $i < count($book['venda']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['venda'][$i]['preco']),
                'volume' => floatval($book['venda'][$i]['volume']),
            );
        }
        for ($i = 0; $i < count($book['compra']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['compra'][$i]['preco']),
                'volume' => floatval($book['compra'][$i]['volume']),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://broker.cointrade.cx/apiv2/ticket/market/' . $asset . ':' . $market)['market'];
        $ticker['last'] = floatval($data['lastPrice']);
        $ticker['ask'] = floatval($data['sellPrice']);
        $ticker['bid'] = floatval($data['buyPrice']);
        $ticker['vol'] = floatval($data['volume']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class cbx extends Bitragem
{
    private static $markets = array('USDT' => array('REALT', 'BTC', 'ETH', 'LTC'));
    private static $entryPoint = 'https://www.cbx.one/api/v2';
    public static function getBook($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents(self::$entryPoint . '/markets/' . $asset . '-' . $market . '/depth');
        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i]['price']),
                'volume' => floatval($book['asks'][$i]['amount']),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i]['price']),
                'volume' => floatval($book['bids'][$i]['amount']),
            );
        }
        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents(self::$entryPoint . '/markets/' . $asset . '-' . $market . '/ticker');
        $ticker['last'] = $data['ask']['price'];
        $ticker['ask'] = $data['ask']['price'];
        $ticker['bid'] = $data['bid']['price'];
        $ticker['vol'] = $data['volume'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarketSummaries()
    {
        $res = self::get_url_contents(self::$entryPoint . '/tickers')['data'];
        $summaries['id'] = self::get_id(__CLASS__);

        for ($i = 0; $i < count($res); $i++) {
            $splited = explode('-', $res[$i]['market_id']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => floatval($res[$i]['ask']['price']),
                'bid' => floatval($res[$i]['bid']['price']),
                'vol' => floatval($res[$i]['volume']),
            );
        }
        return $summaries;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class stratum extends Bitragem
{
    private static $markets = array(
        'BRL' => array('BTC', 'BCH', 'ETH', 'DASH', 'XRP')
    );

    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;


        if ($asset == 'DASH') {
            $asset = 'DSH';
        }


        $book = self::get_url_contents('https://stratum.hk/api/ticker?country=br')['data'];

        for ($i = 0; $i < count($book); $i++) {
            if ($book[$i]['symbol'] == $asset) {

                $newBook['asks'][] = array(
                    'price' => $book[$i]['ask'],
                    'volume' => 1000,
                );

                $newBook['bids'][] = array(
                    'price' => $book[$i]['offer'],
                    'volume' => 1000,
                );
            }
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        if ($asset == 'DASH') {
            $asset = 'DSH';
        }

        $data = self::get_url_contents('https://stratum.hk/api/ticker?country=br')['data'];
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['symbol'] == $asset) {
                $ticker['last'] = $data[$i]['ask'];
                $ticker['ask'] = $data[$i]['ask'];
                $ticker['bid'] = $data[$i]['offer'];
                $ticker['vol'] = null;
                $ticker['id'] = self::get_id(__CLASS__);
                return $ticker;
            }
        }
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class cobinhood extends Bitragem
{
    private static $entryPoint = 'https://api.cobinhood.com/v1/market';
    public static function getMarketSummaries()
    {
        $res = self::get_url_contents(self::$entryPoint . '/tickers')['result']['tickers'];
        $summaries['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $splited = explode('-', $res[$i]['trading_pair_id']);
            $summaries['tickers'][$splited[1]][] = array(
                'symbol' => $splited[0],
                'ask' => floatval($res[$i]['lowest_ask']),
                'bid' => floatval($res[$i]['highest_bid']),
                'vol' => floatval($res[$i]['24h_volume']),
            );
        }

        return $summaries;
    }
    public static function getWalletStatus()
    {
        $res = self::get_url_contents(self::$entryPoint . '/currencies')['result']['currencies'];
        $walletsStatus['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            if (!$res[$i]['deposit_frozen'] && !$res[$i]['withdrawal_frozen']) {
                $walletsStatus['online'][] = $res[$i]['name'];
            } else {
                $walletsStatus['offline'][] = $res[$i]['name'];
            }
        }
        return $walletsStatus;
    }
    public static function getWithdrawalFees()
    {
        $res = self::get_url_contents(self::$entryPoint . '/currencies')['result']['currencies'];
        $withdrawalFees['id'] = self::get_id(__CLASS__);
        for ($i = 0; $i < count($res); $i++) {
            $withdrawalFees['withdrawalFees'][] = array(
                'symbol' => $res[$i]['currency'],
                'name' => $res[$i]['name'],
                'withdrawalFee' => $res[$i]['withdrawal_fee']
            );
        }
        return $withdrawalFees;
    }
}

class biscoint extends Bitragem
{
    private static $markets = array(
        'BRL' => array('BTC'),
    );

    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents('https://biscoint.io/api/ticker?base=BTC&quote=BRL');

        $newBook['asks'][] = array(
            'price' => $book['ask'],
            'volume' => $book['askBaseAmountRef'],
        );

        $newBook['bids'][] = array(
            'price' => $book['bid'],
            'volume' => $book['bidBaseAmountRef'],
        );

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents('https://biscoint.io/api/ticker?base=BTC&quote=BRL');

        $ticker['last'] = $data['last'];
        $ticker['ask'] = $data['ask'];
        $ticker['bid'] = $data['bid'];
        $ticker['vol'] = $data['vol'];
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class novadax extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'BCH', 'XRP', 'DASH'));
    private static $entryPoint = 'https://api.novadax.com/v1/market';
    public static function getBook($asset, $market)
    {

        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
        $book = self::get_url_contents(self::$entryPoint . '/depth?symbol=' . $asset . '_' . $market . '&limit=60')['data'];

        for ($i = 0; $i < count($book['asks']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['asks'][$i][0]),
                'volume' => floatval($book['asks'][$i][1]),
            );
        }
        for ($i = 0; $i < count($book['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bids'][$i][0]),
                'volume' => floatval($book['bids'][$i][1]),
            );
        }

        return $newBook;
    }
    public static function getTicker($asset, $market)
    {
        if (isset(self::$markets[$market])) {
            if (!in_array($asset, self::$markets[$market])) {
                return null;
            }
        } else {
            return null;
        }
        $data = self::get_url_contents(self::$entryPoint . '/ticker?symbol=' . $asset . '_' . $market)['data'];
        $ticker['last'] = floatval($data['lastPrice']);
        $ticker['ask'] = floatval($data['ask']);
        $ticker['bid'] = floatval($data['bid']);
        $ticker['vol'] = floatval($data['baseVolume24h']);
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
    }
    public static function getMarkets()
    {
        return self::$markets;
    }
}
