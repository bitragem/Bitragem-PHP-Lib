<?php

include 'bitragem.php';

echo json_encode(bitragem\pagcripto::getAssets());

//echo json_encode(bitragem\pagcripto::getBook());

?>