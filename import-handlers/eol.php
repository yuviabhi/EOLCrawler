<?php
// set project path
$project_path = dirname(__DIR__);

// import libraries
require_once $project_path . '/classes/utilities-common.php';

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
        $document->add_metadata("page_id", ucwords($ndli_doc_id, "_"));
        // /
        // $document->set_collection ( $this->coll_id, $this->coll_name );
        // /

        $document->add_metadata("scientificName", json_decode($src_document)->scientificName);

        // print_r("\n".$ndli_doc_id ." ". json_decode($src_document)->scientificName ).PHP_EOL; // exit(0);

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

foreach ($collections as $collection) {
    print_r($collection . "\n");
    $chunks = FileSystemUtils::get_large_files($data_src . "/pages/" . $collection, 500);
    $col_id = $collection;
    $col_name = ucwords(getCollectionName($col_id, $collectionJSON));
    // print_r($col_id . "\r". $col_name); exit(0);

    $structure->add_collection($col_id, $col_name);
    foreach ($chunks as $chunk) {
        // print_r($chunk."\n"); exit(0);
        $documents = json_decode(file_get_contents($chunk), TRUE);

        try {
            foreach ((array) $documents as $value) {
                if (! empty($value) and is_array($value)) {
                    $import_handler->set_collection($col_id, $col_name);
                    $import_handler->process_src_document(json_encode($value));
                }
            }
        } catch (Exception $ex) {
            print_r($data_src . "/pages/" . $collection . "/" . $chunk . "\n");
        }
    }
}

// print_r ( $import_handler->get_documents () );
// store
$source_details = new DataSource($src_id);
$source_details->store_documents($import_handler->get_documents()); // store a set of data
$source_details->index_all_documents(); // commit
$source_details->set_structure($structure);
