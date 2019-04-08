# Bitragem PHP Lib
Exchanges:

3xbit
A Casa do Bitcoin
Bitblue
Bitinka
Bitcambio
Bitcointoyou
Bitnuvem
Bitpreco
Bitrecife
BitcoinTrade
Brabex
BrasilBitcoin
Braziliex
BTCBolsa
Coinbene
Coin2001
Coinext
Citcoin
CryptoMarket
FlowBTC
Foxbit
Intertradec
Mercado Bitcoin
Modiax
Negocie Coins
Omnitrade
PagCripto
Pitaiatrade
Profitfy
SatoshiTango
TemBTC
Walltime
Welcoin

## To use

```
<?php

include 'bitragem.php';

// get assets integrated into the library
echo json_encode(bitragem\bitinka::getAssets());

// get orders
//echo json_encode(bitragem\bitinka::getBook());

?>

```