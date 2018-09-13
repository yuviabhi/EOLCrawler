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


$url = 'http://eol.org/api/pages/1.0.json?batch=true&id=1045608%2C5471391&images_per_page=1&images_page=1&videos_per_page=1&videos_page=1&sounds_per_page=1&sounds_page=1&maps_per_page=1&maps_page=1&texts_per_page=2&texts_page=1&subjects=overview&licenses=all&details=true&common_names=true&synonyms=true&references=true&taxonomy=true&vetted=0&cache_ttl=&language=en';

print_r($url);

?>