<?php

include 'bitragem.php';

echo json_encode(bitragem\bitinka::getAssets());

//echo json_encode(bitragem\bitinka::getBook());

?>