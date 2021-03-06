<?php
date_default_timezone_set ( 'UTC' );
require_once dirname ( __DIR__ ) . '/classes/document.php';

/**
 *
 * @author subhayan
 *        
 */
class Configuration {
	private function get_config() {
		return json_decode ( file_get_contents ( dirname ( __DIR__ ) . "/config/config.json" ) );
	}
	public static function get_database_configuration() {
		return self::get_config ()->{"database"};
	}
	public static function get_proxy() {
		return self::get_config ()->{"httpProxy"};
	}
	public static function get_data_service_endpoint() {
		return self::get_config ()->{"dataServiceEndpoint"};
	}
	public static function get_view_name_item(string $src_id) {
		return $src_id . "_raw_item";
	}
	public static function get_view_name_asset(string $src_id) {
		return $src_id . "_raw_asset";
	}
	public static function get_dspace_configuration() {
		return self::get_config ()->{"dspace"};
	}
}

/**
 * String operations
 *
 * @author subhayan
 *        
 */
class StringUtils {
	public static function convert_to_utf8(string $text) {
		$text = preg_replace ( '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' . '|[\x00-\x7F][\x80-\xBF]+' . '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' . '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' . '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S', '?', $text );
		return utf8_encode ( preg_replace_callback ( '/\\\\u([0-9a-fA-F]{4})/', function ($match) {
			return mb_convert_encoding ( pack ( 'H*', $match [1] ), 'UTF-8', 'UCS-2BE' );
		}, $text ) );
		// return iconv ( mb_detect_encoding ( $text, mb_detect_order (), true ), 'UTF-8//TRANSLIT', $text );
		// return mb_convert_encoding ( $text, "UTF-8" );
	}
}

/**
 * File System relaated operations
 *
 * @author subhayan
 *        
 */
class FileSystemUtils {

	/**
	 * Creates a directory/sub-directory
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public static function create_dir(string $dir) {
		if (! file_exists ( $dir )) {
			return mkdir ( $dir, 0777, true );
		}
		return true;
	}

	/**
	 * Get the list of files/folders for given directory
	 *
	 * @param string $path
	 * @return array
	 */
	public static function get_filelist(string $path) {
		return array_values ( array_diff ( scandir ( $path ), array (
				".",
				".."
		) ) );
	}

	/**
	 * Find duplicate files in RECURSIVE mode
	 *
	 * @param string $dir
	 * @return array
	 */
	public static function _get_duplicate_files(string $dir) {
		return array ();
	}

	/**
	 * Find empty directories in RECURSIVE mode
	 *
	 * @param string $dir
	 * @return array
	 */
	public static function _get_empty_directories(string $dir) {
		"find $dir -type d -empty -print";
		return array ();
	}

	/**
	 * Remove empty directories in RECURSIVE mode
	 *
	 * @param string $dir
	 * @return array
	 */
	public static function _remove_empty_directories(string $dir) {
		"find $dir -type d -empty -delete";
		return array ();
	}

	/**
	 * Find empty files in RECURSIVE mode
	 *
	 * @param string $dir
	 * @return array
	 */
	public static function get_empty_files(string $dir) {
		$op = array ();
		if (file_exists ( $dir )) {
			exec ( "find $dir -empty -type f", $op );
		}
		return $op;
	}

	/**
	 * Remove all empty files in RECURSIVE mode
	 *
	 * @param string $dir
	 * @return array
	 */
	public static function remove_empty_files(string $dir) {
		$op = array ();
		if (file_exists ( $dir )) {
			exec ( "find $dir -size 0 -delete", $op );
		}
		return $op;
	}

	/**
	 * Find matched files for given name-pattern
	 *
	 * @param string $dir
	 * @param string $pattern
	 * @example $pattern=*.html
	 * @return array
	 */
	public static function get_files_by_pattern(string $dir, string $pattern) {
		$op = array ();
		if (file_exists ( $dir )) {
			exec ( "find $dir -name $pattern", $op );
		}
		return $op;
	}

	/**
	 * Upper limit; list will contain the files having size smaller than $size_in_bytes
	 *
	 * @param int $size_in_bytes
	 * @return array
	 */
	public static function get_small_files(string $dir, int $size_in_bytes) {
		$op = array ();
		if (file_exists ( $dir )) {
			exec ( "find $dir -type f -size -" . $size_in_bytes . "c", $op );
		}
		return $op;
	}

	/**
	 * Lower limit; list will contain the files having size larger than $size_in_bytes
	 *
	 * @param int $size_in_bytes
	 * @return array
	 */
	public static function get_large_files(string $dir, int $size_in_bytes) {
		$op = array ();
		if (file_exists ( $dir )) {
			exec ( "find $dir -type f -size +" . $size_in_bytes . "c", $op );
		}
		return $op;
	}
}

/**
 * HTTP GET/POST related operations
 *
 * @author subhayan
 *        
 */
class HTTP {
	public static function _fetch_response_GET(string $url, bool $local_network = false, array $get_params = array(), array $headers = array()) {
		$curl_options = array (
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_PROXY => $local_network ? null : Configuration::get_proxy ()
		);
		// FIXME URL conatins GET params
		$curl = curl_init ( $url );
		curl_setopt_array ( $curl, $curl_options );
		$response = curl_exec ( $curl );
		curl_close ( $curl );
		if ($response === false) {
			return null;
		}
		return $response;
	}
	public static function _fetch_header_GET(string $url, bool $local_network = false, array $get_params = array(), array $headers = array()) {
	}
	public static function _fetch_file_GET(string $url, bool $local_network = false, array $get_params = array(), array $headers = array()) {
		set_time_limit ( 0 );
		$fp = fopen ( dirname ( __FILE__ ) . '/localfile.tmp', 'w+' );

		$ch = curl_init ( str_replace ( " ", "%20", $url ) ); // file we are downloading, replace spaces with %20
		$curl_options = array (
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_PROXY => $local_network ? null : Configuration::get_proxy (),
				CURLOPT_FILE => $fp // write curl response to file
		);
		curl_setopt_array ( $ch, $curl_options );
		curl_exec ( $ch );
		curl_close ( $ch );
		fclose ( $fp );
	}
	// /
	public static function _fetch_response_POST(string $url, bool $local_network = false, array $post_params = array(), array $headers = array()) {
	}
	public static function _fetch_header_POST(string $url, bool $local_network = false, array $post_params = array(), array $headers = array()) {
	}
	/**
	 *
	 * @param string $url
	 * @param boolean $local_network
	 */
	public static function get_remote_file(string $url, $local_network = false) {
		set_time_limit ( 0 );
		$fp = fopen ( dirname ( __FILE__ ) . '/localfile.tmp', 'w+' );

		$ch = curl_init ( str_replace ( " ", "%20", $url ) ); // file we are downloading, replace spaces with %20
		$curl_options = array (
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_PROXY => $local_network ? null : Configuration::get_proxy (),
				CURLOPT_FILE => $fp // write curl response to file
		);
		curl_setopt_array ( $ch, $curl_options );
		curl_exec ( $ch );
		curl_close ( $ch );
		fclose ( $fp );
	}

	/**
	 *
	 * @param string $url
	 * @param boolean $local_network
	 * @return null|string
	 */
	public static function get_remote_data(string $url, $local_network = false) {
		$curl_options = array (
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_PROXY => $local_network ? null : Configuration::get_proxy ()
		);
		$curl = curl_init ( $url );
		curl_setopt_array ( $curl, $curl_options );
		$response = curl_exec ( $curl );
		curl_close ( $curl );
		if ($response === false)
			return null;
		return $response;
	}

	/**
	 *
	 * @param string $url
	 * @param boolean $local_network
	 * @return array
	 */
	public static function get_remote_file_header(string $url, $local_network = false) {
		$curl_options = array (
				CURLOPT_HEADER => true,
				CURLOPT_NOBODY => true,
				CURLOPT_BINARYTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FAILONERROR => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_PROXY => $local_network ? null : Configuration::get_proxy ()
		);
		$ch = curl_init ( $url );
		curl_setopt_array ( $ch, $curl_options );
		curl_exec ( $ch );
		$headers = curl_getinfo ( $ch );
		curl_close ( $ch );
		return ($headers);
	}
}
class HTMLParser {

	/**
	 *
	 * @param string $html
	 * @param bool $ignore_errors
	 * @return DOMDocument
	 */
	public static function parse_html(string $html, bool $ignore_errors = true) {
		libxml_use_internal_errors ( $ignore_errors );
		$doc = new DOMDocument ();
		$doc->loadHTML ( $html, LIBXML_BIGLINES | LIBXML_NOWARNING | LIBXML_ERR_NONE );
		libxml_clear_errors ();
		return $doc;
	}

	/**
	 *
	 * @param DOMDocument $dom
	 * @param NDLIDocument $document
	 * @return int
	 */
	public static function extract_metadata_from_headers(DOMDocument $dom_document, NDLIDocument $document) {
		$tags_found = 0;

		$exceptions_name = array (
				"viewport",
				"robots",
				"twitter:card",
				"twitter:site",
				"twitter:description",
				"twitter:creator",
				"twitter:image",
				"fb:admins",
				"google-site-verification",
				"apple-itunes-app"
		);
		$exceptions_property = array (
				"fb:pages"
		);

		$xpath = new DOMXPath ( $dom_document );

		// fetch from <meta>
		$nodes = $xpath->query ( "//meta[@property or @name]", $dom_document );
		foreach ( $nodes as $node ) {
			$attributes = $node->attributes;
			if (isset ( $attributes->getNamedItem ( "property" )->nodeValue )) {
				$metadata_name = trim ( $attributes->getNamedItem ( "property" )->nodeValue );
				if (in_array ( strtolower ( $metadata_name ), $exceptions_property )) {
					continue;
				}
				$metadata_value = trim ( $attributes->getNamedItem ( "content" )->nodeValue );
				$document->add_metadata ( "header_meta_" . $metadata_name, $metadata_value );
				$tags_found ++;
			}
			if (isset ( $attributes->getNamedItem ( "name" )->nodeValue )) {
				$metadata_name = trim ( $attributes->getNamedItem ( "name" )->nodeValue );
				if (in_array ( strtolower ( $metadata_name ), $exceptions_name )) {
					continue;
				}
				if (isset ( $attributes->getNamedItem ( "content" )->nodeValue )) {
					$document->add_metadata ( "header_meta_" . $metadata_name, trim ( $attributes->getNamedItem ( "content" )->nodeValue ) );
					$tags_found ++;
				}
			}
		}

		// fetch from <title>
		$title_node = $xpath->query ( "//title", $dom_document );
		if (isset ( $title_node [0]->nodeValue )) {
			$document->add_metadata ( "header_title", trim ( $title_node [0]->nodeValue ) );
			$tags_found ++;
		}

		return $tags_found;
	}

	/**
	 *
	 * @param DOMDocument $dom_document
	 * @param NDLIDocument $document
	 * @return int
	 */
	public static function extract_metadata_from_jsonLD(DOMDocument $dom_document, NDLIDocument $document) {
		$tags_found = 0;
		$xpath = new DOMXPath ( $dom_document );
		$nodes = $xpath->query ( "//script[@type='application/ld+json']" );
		foreach ( $nodes as $node ) {
			$json = json_decode ( $node->nodeValue );
			if (json_last_error () == JSON_ERROR_NONE) {
				foreach ( $json as $key => $values ) {
					if ($key == "@context") {
						continue;
					}
					$document->add_metadata ( "jsonld_" . $key, $values );
					$tags_found ++;
				}
			}
		}
		return $tags_found;
	}

	/**
	 *
	 * @param DOMElement $table_element
	 * @param NDLIDocument $document
	 * @param int $key_position
	 * @param int $value_position
	 * @return number
	 */
	public static function extract_metadata_from_table(DOMElement $table_element, NDLIDocument $document, int $key_position = 0, int $value_position = 1) {
		$key_count = 0;
		$xpath = new DOMXPath ( $table_element->ownerDocument );
		$tr_elements = $xpath->query ( "./tr", $table_element );

		for($i = 0; $i < $tr_elements->length; $i ++) {
			$td_elements = $xpath->query ( "./td", $tr_elements->item ( $i ) );
			if ($td_elements->length >= 2) {
				if ($td_elements->item ( $key_position ) != null && strlen ( $td_elements->item ( $key_position )->textContent ) > 0) {
					if ($td_elements->item ( $value_position ) != null && strlen ( $td_elements->item ( $value_position )->textContent ) > 0) {
						$field = $td_elements->item ( $key_position )->textContent;
						$value = $td_elements->item ( $value_position )->textContent;
						$document->add_metadata ( $field, explode ( PHP_EOL, $value ) );
						$key_count ++;
					}
				}
			}
		}
		return $key_count;
	}
	/**
	 *
	 * @param string $html_string
	 * @return string
	 */
	public static function _remove_html_tags(string $html_string) {
		return $html_string;
	}
}

/**
 *
 * @author subhayan
 *        
 */
class Database {
	private $db_conn = null;

	/**
	 *
	 * @param resource $db_conn
	 */
	public function __construct($db_conn = null) {
		$this->db_conn = $db_conn ?: self::create_database_connection ();
	}
	private static function create_database_connection() {
		$config = Configuration::get_database_configuration ();
		$connection_string = implode ( " ", array (
				"host=" . $config->host,
				"dbname=" . $config->name,
				"user=" . $config->user,
				"password=" . $config->pass
		) );
		for($i = 0; $i < 1; $i ++) {
			$db = @pg_connect ( $connection_string, PGSQL_CONNECT_FORCE_NEW );
			if ($db !== false && pg_connection_status ( $db ) == PGSQL_CONNECTION_OK) {
				return $db;
			}
		}
		return null;
	}
	public function get_connection() {
		return $this->db_conn;
	}
	public function close_database() {
		pg_close ( $this->db_conn );
	}
}

/**
 *
 * @author subhayan
 *        
 */
class DataSource {
	protected $src_id = null;
	public function __construct(string $src_id) {
		$this->src_id = $src_id;
	}

	/**
	 *
	 * @param array $documents
	 * @return int
	 */
	public function store_documents(array $documents) {
		$success_count = 0;
		if ($documents) {
			$db = new Database ();
			foreach ( $documents as $document ) {
				$this->store_document ( $document, $db->get_connection () ) ? $success_count ++ : null;
			}
			$db->close_database ();
		}
		return $success_count;
	}

	/**
	 *
	 * @return boolean
	 */
	public function index_all_documents() {
		return $this->generate_views ();
	}

	/**
	 * Removes all documents under the source
	 *
	 * @return boolean
	 */
	public function remove_all_douments() {
		$db = new Database ();
		$queries = array (
				"DELETE FROM items WHERE src_id = '" . $this->src_id . "'",
				"DROP MATERIALIZED VIEW IF EXISTS public." . Configuration::get_view_name_item ( $this->src_id ),
				"DROP MATERIALIZED VIEW IF EXISTS public." . Configuration::get_view_name_asset ( $this->src_id )
		);
		$status = pg_query ( $db->get_connection (), implode ( ";", $queries ) );
		$db->close_database ();
		return $status ? true : false;
	}

	/**
	 *
	 * @param NDLIDocument $document
	 * @param resource $db_conn
	 * @return boolean
	 */
	private function store_document(NDLIDocument $document, $db_conn) {
		$ndli_uniq_id = $this->src_id . "/" . $document->get_ndli_document_id ();
		$ndli_collection_id = $document->get_collection_id () ? $this->src_id . "/" . $document->get_collection_id () : null;

		pg_flush ( $db_conn );
		$status = pg_query ( $db_conn, "BEGIN" ) ? true : false;
		// pg_query ( $db, "DEALLOCATE ALL" );

		// INSERT INTO items
		if ($status) {
			$query_items_params = array (
					"src_id" => $this->src_id,
					"src_uniq_id" => $document->get_src_document_id (),
					"src_data_hash" => md5 ( json_encode ( $document ) ),
					"ndli_uniq_id" => $ndli_uniq_id,
					"ndli_collection_id" => $ndli_collection_id
			);
			$status = @pg_insert ( $db_conn, "items", $query_items_params, PGSQL_DML_ESCAPE | PGSQL_DML_EXEC );
			if (! $status) {
				echo $ndli_uniq_id . "::items::" . pg_errormessage ( $db_conn ) . PHP_EOL;
			}
		}

		// INSERT INTO metadata
		$metadata = $document->get_metadata ();
		if ($thumbnails = $document->get_thumbnail_url ()) {
			$metadata ["ndli_document_thumbnail"] = $thumbnails;
		}
		foreach ( $metadata as $key => $values ) {
			foreach ( $values as $value ) {
				if (! $status) {
					break;
				}

				$query_metadata_params = array (
						"ndli_uniq_id" => $ndli_uniq_id
				);
				if (is_iterable ( $value ) || is_object ( $value )) {
					$query_metadata_params ["meta_field"] = $key . "_OBJECT";
					$value = json_encode ( $value, JSON_UNESCAPED_UNICODE );
				} else {
					$query_metadata_params ["meta_field"] = $key;
				}
				$query_metadata_params ["meta_value"] = StringUtils::convert_to_utf8 ( $value );
				mb_convert_encoding ( $value, "UTF-8" );
				$status = @pg_insert ( $db_conn, "metadata_raw", $query_metadata_params, PGSQL_DML_ESCAPE | PGSQL_DML_EXEC );
			}
			if (! $status) {
				echo $ndli_uniq_id . "::metadata_raw" . $key . "::" . $value . "::" . pg_errormessage ( $db_conn ) . PHP_EOL;
				break;
			}
		}

		// INSERT INTO assets
		foreach ( $document->get_assets () as $seq => $asset ) {
			if (! $status) {
				break;
			}
			$query_asset_params = array (
					"ndli_uniq_id" => $ndli_uniq_id,
					"src_asset_id" => $asset->src_asset_id,
					"ndli_asset_id" => $asset->ndli_asset_id,
					"asset_sequence" => $seq
			);
			$status = @pg_insert ( $db_conn, "assets", $query_asset_params, PGSQL_DML_ESCAPE | PGSQL_DML_EXEC );

			// INSERT INTO assets_metadata
			foreach ( $asset->metadata as $field => $value ) {
				if (! $status) {
					break;
				}
				$query_asset_metadata_params = array (
						"ndli_asset_id" => $asset->ndli_asset_id,
						"asset_metadata_field" => $field,
						"asset_metadata_value" => StringUtils::convert_to_utf8 ( $value )
				);
				$status = @pg_insert ( $db_conn, "assets_metadata", $query_asset_metadata_params, PGSQL_DML_ESCAPE | PGSQL_DML_EXEC );
			}
		}

		if (pg_query ( $db_conn, ($status ? "COMMIT" : "ROLLBACK") ) && $status) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param string $src_id
	 */
	private function generate_views() {
		$src_id = $this->src_id;
		$view_item = Configuration::get_view_name_item ( $src_id );
		$view_asset = Configuration::get_view_name_asset ( $src_id );

		$queries = array (
				"DROP MATERIALIZED VIEW IF EXISTS $view_item",
				"CREATE MATERIALIZED VIEW $view_item AS (
					SELECT DISTINCT(meta_field) field,
					COUNT(DISTINCT(ndli_uniq_id)) count_covered,
					COUNT(DISTINCT meta_value) count_values,
					max(char_length(meta_value)) max_len,
					min(char_length(meta_value)) min_len,
					CASE WHEN COUNT(DISTINCT(metadata_raw.ndli_uniq_id))=(SELECT COUNT(*)
						FROM items
						WHERE src_id = '$src_id') THEN true ELSE false END mandatory,
					CASE WHEN (meta_field IN (SELECT DISTINCT(meta_field) field
						FROM metadata_raw
						WHERE ndli_uniq_id IN ( SELECT ndli_uniq_id FROM items WHERE src_id = '$src_id' )
						GROUP BY ndli_uniq_id, meta_field
						HAVING COUNT(meta_value)>1
						ORDER BY meta_field))
					THEN true ELSE false END multivalued
					FROM metadata_raw
					WHERE ndli_uniq_id IN ( SELECT ndli_uniq_id FROM items WHERE src_id = '$src_id' )
					GROUP BY meta_field
				)",
				"DROP MATERIALIZED VIEW IF EXISTS $view_asset",
				"CREATE MATERIALIZED VIEW $view_asset AS (
					SELECT DISTINCT(asset_metadata_field) field,
					COUNT(DISTINCT(assets.ndli_uniq_id)) count_covered,
					COUNT(DISTINCT(asset_metadata_value)) count_values,
					max(char_length(asset_metadata_value)) max_len,
					min(char_length(asset_metadata_value)) min_len,
					CASE WHEN COUNT(DISTINCT(assets.ndli_uniq_id))=(SELECT COUNT(*)
						FROM items
						WHERE src_id = '$src_id') THEN true ELSE false END mandatory
					FROM assets_metadata
					LEFT JOIN assets ON assets.ndli_asset_id = assets_metadata.ndli_asset_id
					WHERE ndli_uniq_id IN ( SELECT ndli_uniq_id FROM items WHERE src_id = '$src_id' )
					GROUP BY asset_metadata_field
				)"
		);
		$db = new Database ();
		$status = pg_query ( $db->get_connection (), implode ( ";", $queries ) );
		$db->close_database ();

		// $this->set_structure ( $this->collections );
		return $status ? true : false;
	}

	/**
	 *
	 * @param NDLIStructure $structure
	 * @return boolean
	 */
	public function set_structure(NDLIStructure $structure) {
		$structure = json_encode ( $structure->get_structure (), JSON_UNESCAPED_UNICODE );
		$db = new Database ();
		$status = pg_update ( $db->get_connection (), "sources", array (
				"structure" => $structure
		), array (
				"src_id" => $this->src_id
		) );
		$db->close_database ();
		return $status ? true : false;
	}

	/**
	 *
	 * @return stdClass FIXME
	 */
	public function get_report_structure() {
		$obj = new stdClass ();
		$obj->missed_collections_in_struct = array (); // ERROR:
		$obj->missed_collections_in_items = array (); // WARN: empty collections
		$obj->duplicate_collections_in_struct = array (); // ERROR
		$obj->duplicate_colmmunities_in_struct = array (); // ERROR
		$obj->duplicate_cols_coms_in_struct = array (); // ERROR
		/**
		 *
		 * @param array $nodes
		 * @return array
		 */
		function get_collection_communities(array $nodes) {
			$obj = array (
					"col" => array (),
					"com" => array ()
			);
			foreach ( $nodes as $node ) {
				if ($node->type == "COMMUNITY") {
					$nested = get_collection_communities ( $node->child );
					array_push ( $obj ["com"], $node->id );
					$obj ["com"] = array_merge ( $obj ["com"], $nested ["com"] );
					$obj ["col"] = array_merge ( $obj ["col"], $nested ["col"] );
				}
				if ($node->type == "COLLECTION") {
					array_push ( $obj ["col"], $node->id );
				}
			}
			return $obj;
		}
		$structure = array ();
		$covered_collections = array ();
		$db = new Database ();
		$query = "SELECT structure FROM sources WHERE src_id='" . pg_escape_string ( $this->src_id ) . "'";
		$structure = json_decode ( current ( pg_fetch_all_columns ( pg_query ( $db->get_connection (), $query ) ) ) );
		$query = "SELECT COALESCE(ndli_collection_id, 'NO_COLLECTION') ndli_collection_id, COUNT(ndli_uniq_id) num_items
					FROM items
					WHERE src_id= '" . pg_escape_string ( $this->src_id ) . "'
					GROUP BY ndli_collection_id
					ORDER BY num_items DESC";
		$result = pg_query ( $db->get_connection (), $query );
		while ( $row = pg_fetch_assoc ( $result ) ) {
			$covered_collections [$row ["ndli_collection_id"]] = $row ["num_items"];
		}
		$db->close_database ();

		$structure = get_collection_communities ( $structure );
		$id_map_col = array_count_values ( $structure ["col"] );
		$id_map_com = array_count_values ( $structure ["com"] );

		$obj->duplicate_collections_in_struct = array_filter ( $id_map_col, function ($v, $k) {
			return $v > 1;
		}, ARRAY_FILTER_USE_BOTH );
		$obj->duplicate_colmmunities_in_struct = array_filter ( $id_map_com, function ($v, $k) {
			return $v > 1;
		}, ARRAY_FILTER_USE_BOTH );
		$obj->duplicate_cols_coms_in_struct = array_intersect_key ( $id_map_col, $id_map_com );
		// FIXME calculate count
		// $obj->duplicate_cols_coms_in_struct = array_filter ( array_count_values ( array_merge ( $structure ["col"], $structure ["com"] ) ), function ($v, $k) {
		// return $v > 1;
		// }, ARRAY_FILTER_USE_BOTH );
		$obj->missed_collections_in_items = array_values ( array_diff ( array_keys ( $id_map_col ), array_keys ( $covered_collections ) ) );
		$obj->missed_collections_in_struct = array_values ( array_diff ( array_keys ( $covered_collections ), array_keys ( $id_map_col ) ) );

		return $obj;
	}

	/**
	 * Automatically generates structure from field-value
	 *
	 * @todo incomplete
	 *      
	 * @param string $field
	 */
	public function auto_generate_structure_from_field(string $field = "ndli_collection_name") {
		$query = "SELECT col_id, item_covered, to_json(col_names) col_names, array_length(col_names, 1) col_len
					FROM (
						SELECT ndli_collection_id col_id, COUNT(items.ndli_uniq_id) item_covered, array_agg(DISTINCT(meta_value)) col_names
						FROM items, metadata_raw
						WHERE items.src_id = '" . pg_escape_string ( $this->src_id ) . "'
							AND items.ndli_uniq_id = metadata_raw.ndli_uniq_id
							AND meta_field = '" . pg_escape_string ( $field ) . "'
						GROUP BY ndli_collection_id
					) virtual_relation
					ORDER BY col_len DESC, col_id ASC";

		$db = new Database ();
		$items = pg_fetch_all ( pg_query ( $db->get_connection (), $query ) );
		$db->close_database ();
		print_r ( $items );
	}
}
/**
 *
 * @author subhayan
 *        
 */
abstract class ImportHandler {
	protected $src_id;
	private $documents = null;
	private $errors = array ();
	/**
	 *
	 * @param string $src_id
	 */
	public function __construct(string $src_id) {
		$this->src_id = $src_id;
		$this->documents = new NDLIDocumentSet ();
	}
	public function get_documents() {
		return $this->documents->get_documents_from_set ();
	}

	/**
	 *
	 * @param NDLIDocument $document
	 * @return number
	 */
	protected function add_document(NDLIDocument $document) {
		return $this->documents->add_document_to_set ( $document );
	}
	public function _release_documents() {
		// $this->documents = new NDLIDocumentSet ();
	}
	/**
	 *
	 * @param string $input
	 */
	abstract protected function generate_ndli_document_id(string $input);
	/**
	 *
	 * @param mixed $src_document
	 */
	abstract public function process_src_document($src_document);
}

/**
 *
 * @author subhayan
 *        
 */
abstract class IncrementHandler {
}
?>
