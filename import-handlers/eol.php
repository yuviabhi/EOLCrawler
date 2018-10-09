<?php
// set project path
$project_path = dirname(__DIR__);

// import libraries
require_once $project_path . '/classes/utilities-common.php';
require_once $project_path . '/eol-utils.php';

// setting up the data source
$src_id = "eol";
$data_src = $project_path . '/data';

// remove existing data
$source_details = new DataSource($src_id);
$source_details->remove_all_douments();
// exit ( 0 );

// define source specific class
class EOLImportHandler extends ImportHandler
{

    private $coll_id = null;

    private $coll_name = null;

    public function set_collection($id, $name)
    {
        $this->coll_id = $id;
        $this->coll_name = $name;
    }

    public function process_src_document($src_document)
    {
        $ndli_doc_id = $this->generate_ndli_document_id($src_document);

        $document = new NDLIDocument($ndli_doc_id);

        // ***** traverse each ndli item and save ***** //
        foreach (json_decode($src_document, false) as $key => $value) {

            switch ($key) {

                case "dataObjects":
                    foreach ($value as $val) {
                        foreach ($val as $k => $v) {
                            if ($k == "agents") {
                                foreach ($val as $obj) {
                                    if (isset($obj->role)) {
                                        $metaname = $key . "_" . $k . "_" . $obj->role;
                                        unset($obj->role);
                                        $document->add_metadata($metaname, $obj);
                                    }
                                }
                            } else {
                                $document->add_metadata($key . "_" . $k, $v);
                            }
                        }
                    }
                    break;

                case "synonyms":
                    foreach ($value as $val) {
                        $document->add_metadata($key, $val);
                    }
                    break;

                case "vernacularNames":
                    foreach ($value as $val) {
                        $document->add_metadata($key, $val);
                    }
                    break;

                case "taxonConcepts":
                    foreach ($value as $val) {
                        $document->add_metadata($key, $val);
                    }
                    break;

                case "default":
                    $document->add_metadata($key, $value);
                    break;
            }
        }

        $this->add_document($document, $this->coll_id, $this->coll_name);
    }

    protected function generate_ndli_document_id(string $filename)
    {
        $id = json_decode($filename)->identifier;
        return $id;
    }
}
// ////////////////////////////////////////////////////////////////////////////////////////////////

// processing
$import_handler = new EOLImportHandler($src_id);
$structure = new NDLIStructure($src_id);

function getCollectionName($col_id, $col_JSON)
{
    $id = explode("_", $col_id);
    foreach ($col_JSON as $c) {
        if ($c->id == $id[1]) {
            return $c->label;
        }
    }
}

$collections = FileSystemUtils::get_filelist($data_src . "/pages");
$collectionJSON = json_decode(file_get_contents($data_src . "/eol_providers.json"));

$ndli_item_count = 0;
$ndli_item_save_count = 0;
$ndli_item_ignore_count = 0;
foreach ($collections as $collection) {
    print_r($collection . "\n");
    $chunks = FileSystemUtils::get_large_files($data_src . "/pages/" . $collection, 500);
    $col_id = $collection;
    $col_name = ucwords(getCollectionName($col_id, $collectionJSON));
    // print_r($col_id . "\r". $col_name); exit(0);

    $structure->add_collection($col_id, $col_name);
    foreach ($chunks as $chunk) {
        // print_r($chunk."\n"); exit(0);
        $documents_of_25_eol_pages = json_decode(file_get_contents($chunk), TRUE);

        try {
            foreach ((array) $documents_of_25_eol_pages as $eol_page) {
                if (! empty($eol_page) and is_array($eol_page)) {

                    if (! empty((array) ($eol_page['dataObjects']))) {
                        $import_handler->set_collection($col_id, $col_name);
                        $import_handler->process_src_document(json_encode($eol_page));
                        $ndli_item_count++;
                        $ndli_item_save_count++;
                    } else{
                        print_r("IGNORED " . $chunk . " :: No data objects found " . PHP_EOL);
                        $ndli_item_ignore_count++;
                    }
                }
                
            }
        } catch (Exception $ex) {
            print_r($data_src . "/pages/" . $collection . "/" . $chunk . "\n");
        }

        if($ndli_item_count > 5000){
            $ndli_item_count = 0;
            // store
            $source_details = new DataSource($src_id);
            $source_details->store_documents($import_handler->get_documents()); // store a set of data
            $import_handler = new EOLImportHandler($src_id);
            
        }
         //break; // for a single chunk in a provider
    }
     //break; // for a single provider
}

// store
$source_details = new DataSource($src_id);
$source_details->store_documents($import_handler->get_documents()); // store a set of data
$source_details->index_all_documents(); // commit
$source_details->set_structure($structure);

print_r($ndli_item_save_count. " items saved" . PHP_EOL);
print_r($ndli_item_ignore_count. " items ignored" . PHP_EOL);
