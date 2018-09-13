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

function crawl_recursively($taxonID) {
    $url_hierarchy_entries = 'http://eol.org/api/hierarchy_entries/1.0/' . $taxonID . '.json';
    $hierarchy_entries = json_decode(getData($url_hierarchy_entries));
    
    $children = $hierarchy_entries->children;
    if(sizeof($children)==0){
        return ;
    }
    else {
        //recursion
        foreach ($children as $child) {
            $taxonID = $child->taxonID;
            $pageID = $child->taxonConceptID;
            $parentID = $child->parentNameUsageID;
            print_r($taxonID . ", " . $parentID . ", " . $pageID . "\n");
            crawl_recursively($taxonID);
            
            //insert into table
        }        
    }
    
}

// manual run
// crawl_recursively("62852883");



$url_providers = 'http://eol.org/api/provider_hierarchies/1.0.json';
$providers = json_decode(getData($url_providers));

foreach ($providers as $p) {
    $id = $p->id;
    $label = $p->label;
    //print_r($id . " " . $label . "\n");

    $url_hierarchy = 'http://eol.org/api/hierarchies/1.0/' . $id . '.json??&language=en';
    $hierarchy = json_decode(getData($url_hierarchy));
    
    //print_r($hierarchy->title);
    $roots = $hierarchy->roots;
    
    foreach ($roots as $root) {
        $taxonID = $root->taxonID;
        $pageID = $root->taxonConceptID;
        $parentID = $root->parentNameUsageID;
        //insert into table
        print_r($taxonID . ", " . $parentID . ", " . $pageID . "\n");
        
        crawl_recursively($taxonID);

        
    }
}


?>
