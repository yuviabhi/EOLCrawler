<?php
// set project path
$project_path = dirname ( __DIR__ );

// import libraries
require_once $project_path . '/classes/utilities-common.php';

// setting up the data source
$src_id = "theancientweb";
$data_src = "/mnt/8A38B87C38B868B9/crawled-data/saptarshi/theancientweb";

// remove existing data
$source_details = new DataSource ( $src_id );
$source_details->remove_all_douments ();
// exit ( 0 );

// define source specific class
class TheAncientWebImportHandler extends ImportHandler {
	private $coll_id = null;
	private $coll_name = null;
	public function set_collection($id, $name) {
		$this->coll_id = $id;
		$this->coll_name = $name;
	}
	public function process_src_document($src_document) {
		$ndli_doc_id = $this->generate_ndli_document_id ( $src_document );
		$document = new NDLIDocument ( $ndli_doc_id );
		$document->add_metadata ( "title", ucwords ( $ndli_doc_id, "_" ) );
		// /
		// $document->set_collection ( $this->coll_id, $this->coll_name );
		// /
		$dom_doc = HTMLParser::parse_html ( file_get_contents ( $src_document ) );
		$xpath = new DOMXPath ( $dom_doc );
		// get meta tag information
		$nodes = $xpath->query ( "//div[@class='page-banner-text']" );
		if ($nodes->length) {
			$document->add_metadata ( "banner-text", trim ( $nodes->item ( 0 )->textContent ) );
		}
		$document->add_parts ( "123" );
		$this->add_document ( $document, $this->coll_id, $this->coll_name );
	}
	protected function generate_ndli_document_id(string $filename) {
		$temp = explode ( "/", $filename );
		return str_replace ( ".html", "", implode ( "_", array_slice ( $temp, count ( $temp ) - 2 ) ) );
	}
}
// ////////////////////////////////////////////////////////////////////////////////////////////////

// processing
$import_handler = new TheAncientWebImportHandler ( $src_id );
$structure = new NDLIStructure ( $src_id );
$structure->add_community ( "abc123", "test-comm" );
$collections = FileSystemUtils::get_filelist ( $data_src . "/explore" );

foreach ( $collections as $collection ) {
	$documents = FileSystemUtils::get_filelist ( $data_src . "/explore/" . $collection );
	$col_id = $collection;
	$col_name = ucwords ( $collection );
	$structure->add_collection ( $col_id, $col_name, "abc123" );
	foreach ( $documents as $src_document ) {
		$import_handler->set_collection ( $col_id, $col_name );
		$import_handler->process_src_document ( $data_src . "/explore/" . $collection . "/" . $src_document );
	}
}
// print_r ( $import_handler->get_documents () );
// store
$source_details = new DataSource ( $src_id );
$source_details->store_documents ( $import_handler->get_documents () ); // store a set of data
$source_details->index_all_documents (); // commit
$source_details->set_structure ( $structure );
