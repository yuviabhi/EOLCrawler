<?php

function getData($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, "http://172.16.2.30:8080");
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}




$url_providers = 'http://eol.org/api/provider_hierarchies/1.0.json';
$providers = json_decode(getData($url_providers));

foreach ($providers as $p) {
    $id = $p->id;
    $label = $p->label;
    print_r($id. "\r\n");

}


?>
