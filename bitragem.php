<?php

/**
 * Bitragem's official php lib
 * v 1.1.1
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
    public function get_url_contents($input)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $input,
            CURLOPT_REFERER => $input,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
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
    public function get_id($input)
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
    public static function getMarkets()
    {
        return self::$markets;
    }
}

class allcoinwallet extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'ETH', 'LTC', 'BCH', 'XRP'));
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
        $book = self::get_url_contents('https://public-api.allcoinwallet.com/api/v1/' . $asset . $market . '/orderbook');

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
        $data = self::get_url_contents('https://public-api.allcoinwallet.com/api/v1/' . $asset . $market . '/ticker');
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

        switch ($asset) {
            case 'BTC':
                $asset = 1;
                break;
            case 'ETH':
                $asset = 2;
                break;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
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
        $book = self::get_url_contents('https://broker.batexchange.com.br/api/v3/' . $asset . 'BRL/orderbook');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i]['price']),
                'volume' => floatval($book['ask'][$i]['quantity']),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i]['price']),
                'volume' => floatval($book['bid'][$i]['quantity']),
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

class batexchange extends Bitragem
{
    private static $markets = array('BRL' => array('ETH'));
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
        $book = self::get_url_contents('https://broker.batexchange.com.br/api/v3/' . $asset . 'BRL/orderbook');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i]['price']),
                'volume' => floatval($book['ask'][$i]['quantity']),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i]['price']),
                'volume' => floatval($book['bid'][$i]['quantity']),
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
        $data = self::get_url_contents('https://broker.batexchange.com.br/api/v3/' . $asset . 'BRL/ticker');
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
        $ticker['vol'] = $data['24h_volume'];
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
        $book = self::get_url_contents('https://www.bitcointoyou.com/api/orderbook.aspx');
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
        $data = self::get_url_contents('https://www.bitcointoyou.com/API/ticker.aspx')['ticker'];
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
    private static $markets = array('BRL' => array('BTC', 'BCH', 'BTG', 'ETH', 'LTC', 'DASH'));
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
        $book = self::get_url_contents('https://braziliex.com/api/v1/public/orderbook/' . strtolower($asset) . '_brl');
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
    public static function getMarkets()
    {
        return self::$markets;
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
        for ($i = count($book['data']['asks']) - 1; $i > 0; $i--) {
            $newBook['asks'][] = array(
                'price' => $book['data']['asks'][$i]['price_unity'],
                'volume' => $book['data']['asks'][$i]['amount'],
            );
        }
        for ($i = 0; $i < count($book['data']['bids']); $i++) {
            $newBook['bids'][] = array(
                'price' => $book['data']['bids'][$i]['price_unity'],
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

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
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
            case 'TUSD':
                $asset = 2;
                break;
        }

        $newBook['id'] = self::get_id(__CLASS__);
        $newBook['asset'] = $asset;
        $newBook['base'] = $market;
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

class intertradec extends Bitragem
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
        $book = self::get_url_contents('https://app.intertradec.com.br/api/v1/public/booking/' . $asset . '_BRL');

        for ($i = 0; $i < count($book); $i++) {
            if ($book[$i]['type'] == 'SELL') {
                $newBook['asks'][] = array(
                    'price' => floatval($book[$i]['rate']),
                    'volume' => floatval($book[$i]['amount']),
                );
            }
            if ($book[$i]['type'] == 'BUY') {
                $newBook['bids'][] = array(
                    'price' => floatval($book[$i]['rate']),
                    'volume' => floatval($book[$i]['amount']),
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
        $data = self::get_url_contents('https://app.intertradec.com/api/v1/public/ticker')[$asset . '_BRL'];
        $ticker['last'] = floatval($data['last']);
        $ticker['ask'] = null;
        $ticker['bid'] = null;
        $ticker['vol'] = null;
        $ticker['id'] = self::get_id(__CLASS__);
        return $ticker;
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

class negociecoins extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BTG', 'BCH', 'LTC', 'DASH', 'DOGE'));
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
        $book = self::get_url_contents('https://broker.negociecoins.com.br/api/v3/' . $asset . 'BRL/orderbook');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i]['price']),
                'volume' => floatval($book['ask'][$i]['quantity']),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i]['price']),
                'volume' => floatval($book['bid'][$i]['quantity']),
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
        $data = self::get_url_contents('https://broker.negociecoins.com.br/api/v3/' . $asset . 'BRL/ticker');
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
        $book = self::get_url_contents('https://api.satoshitango.com/v3/ticker/BRL')['data']['ticker'][$asset];

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
        $data = self::get_url_contents('https://api.satoshitango.com/v3/ticker/BRL')['data']['ticker'][$asset];
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

class tembtc extends Bitragem
{
    private static $markets = array('BRL' => array('BTC', 'BCH', 'ETH', 'LTC', 'DASH'));
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
        $book = self::get_url_contents('https://broker.tembtc.com.br/api/v3/' . $asset . 'BRL/orderbook');

        for ($i = 0; $i < count($book['ask']); $i++) {
            $newBook['asks'][] = array(
                'price' => floatval($book['ask'][$i]['price']),
                'volume' => floatval($book['ask'][$i]['quantity']),
            );
        }
        for ($i = 0; $i < count($book['bid']); $i++) {
            $newBook['bids'][] = array(
                'price' => floatval($book['bid'][$i]['price']),
                'volume' => floatval($book['bid'][$i]['quantity']),
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
        $data = self::get_url_contents('https://broker.tembtc.com.br/api/v3/' . $asset . 'BRL/ticker');
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
