<?php
class NDLIStructure extends NDLIConstraints {
	const TYPE_COLLECTION = "COLLECTION";
	const TYPE_COMMUNITY = "COMMUNITY";
	private $root_nodes = array ();
	private $src_id = null;
	public function __construct(string $src_id) {
		$this->src_id = $src_id;
	}
	public function add_community(string $com_id, string $com_name, string $parent_com_id = null) {
		if ($parent_com_id) {
			$parent_node = $this->find_node ( $parent_com_id, $this->root_nodes );
			if ($parent_node->type == self::TYPE_COLLECTION) {
				return false;
			}
			array_push ( $parent_node->child, $this->create_node ( self::TYPE_COMMUNITY, $com_id, $com_name ) );
		} else {
			array_push ( $this->root_nodes, $this->create_node ( self::TYPE_COMMUNITY, $com_id, $com_name ) );
		}
		return true;
	}
	public function add_collection(string $col_id, string $col_name, string $parent_com_id = null) {
		if ($parent_com_id) {
			$parent_node = $this->find_node ( $parent_com_id, $this->root_nodes );
			if ($parent_node->type == self::TYPE_COLLECTION) {
				return false;
			}
			array_push ( $parent_node->child, $this->create_node ( self::TYPE_COLLECTION, $col_id, $col_name ) );
		} else {
			array_push ( $this->root_nodes, $this->create_node ( self::TYPE_COLLECTION, $col_id, $col_name ) );
		}
		return true;
	}
	public function get_child_nodes(string $parent_com_id = null) {
		$parent_node = $this->find_node ( $parent_com_id, $this->root_nodes );
		return $parent_node->child;
	}
	public function get_structure(string $parent_com_id = null) {
		return $this->root_nodes;
	}
	public function _move_node(string $current_parent_com_id, string $new_parent_com_id) {
	}
	public function _remove_node(string $node_id) {
	}
	private function find_node(string $id, array $nodes) {
		foreach ( $nodes as $node ) {
			if ($node->id == $this->src_id . "/" . $this->normalize_id ( $id )) {
				return $node;
			}
			if ($node->child) {
				return $this->find_node ( $id, $node->child );
			}
		}
	}
	private function create_node(string $type, string $id, string $name) {
		$obj = new stdClass ();
		$obj->type = $type;
		$obj->id = $this->src_id . "/" . $this->normalize_id ( $id );
		$obj->name = $name;
		$obj->child = array ();
		return $obj;
	}
}
class NDLIConstraints {
	/**
	 *
	 * @param string $field
	 * @return string
	 */
	protected function normalize_field_name(string $field) {
		$field = preg_replace ( "/\[.*?\]/", "", $field );
		return trim ( preg_replace ( "/(\W|\_)+/", "_", strtolower ( trim ( $field ) ) ), "_" );
	}

	/**
	 *
	 * @param string $id
	 * @return mixed
	 */
	protected function normalize_id(string $id) {
		return preg_replace ( "/(\W|\_)+/", "_", strtolower ( trim ( $id ) ) );
	}
}
/**
 *
 * @author subhayan
 *        
 */
class NDLIAsset extends NDLIConstraints {
	private $src_asset_id = null;
	private $ndli_asset_id = null;
	private $metadata = array ();

	/**
	 *
	 * @param string $src_id
	 * @param string $src_asset_id
	 */
	function __construct(string $src_id, string $src_asset_id) {
		$this->src_asset_id = $src_asset_id;
		$this->ndli_asset_id = $this->normalize_id ( $src_id . "_" . $src_asset_id );
	}
	public function set_asset_url(string $url) {
		$this->set_asset_metadata ( "ndli_asset_url", $url );
	}
	public function set_asset_thumbnail_url(string $url) {
		$this->set_asset_metadata ( "ndli_asset_thumbnail_url", $url );
	}
	public function set_asset_filesize(string $size) {
		$this->set_asset_metadata ( "ndli_asset_filesize", $size );
	}
	public function set_asset_filename(string $name) {
		$this->set_asset_metadata ( "ndli_asset_filename", $name );
	}
	public function set_asset_mimetype(string $mime) {
		$this->set_asset_metadata ( "ndli_asset_mimetype", $mime );
	}
	public function set_asset_accessiblity(string $accessiblity) {
		$this->set_asset_metadata ( "ndli_asset_accessiblity", $accessiblity );
	}

	/**
	 *
	 * @param string $field
	 * @param string $value
	 */
	public function set_asset_metadata(string $field, string $value) {
		$field = $this->normalize_field_name ( $field );
		$this->metadata [$field] = trim ( $value );
	}

	/**
	 *
	 * @param string $field
	 * @return boolean
	 */
	public function remove_asset_metadata_field(string $field) {
		$field = $this->normalize_field_name ( $field );
		if (array_key_exists ( $field, $this->metadata )) {
			unset ( $this->metadata [$field] );
			return true;
		}
		return false;
	}
	public function get_asset() {
		$obj = new stdClass ();
		$obj->src_asset_id = $this->src_asset_id;
		$obj->ndli_asset_id = $this->ndli_asset_id;
		$obj->metadata = $this->metadata;
		return $obj;
	}
}

/**
 *
 * @author subhayan
 *        
 */
class NDLIDocument extends NDLIConstraints {
	private $src_document_id = null;
	private $ndli_document_id = null;
	private $collection_id = null;
	private $metadata = array ();
	private $assets = array ();
	private $url_thumbnail = array ();
	private function set_metadata(string $name, $values) {
		if ($values) {
			$this->metadata [$name] = is_array ( $values ) ? array_map ( "trim", $values ) : array (
					trim ( $values )
			);
			return true;
		}
		return false;
	}
	/**
	 *
	 * @param string $id
	 */
	public function __construct(string $id) {
		$this->src_document_id = trim ( $id );
		$this->ndli_document_id = $this->normalize_id ( trim ( $id ) );
	}

	/**
	 *
	 * @param string $field
	 * @return boolean
	 */
	public function remove_metadata_field(string $field) {
		$field = $this->normalize_field_name ( $field );
		if (array_key_exists ( $field, $this->metadata )) {
			unset ( $this->metadata [$field] );
			return true;
		}
		return false;
	}
	/**
	 *
	 * @todo
	 * @param string $field
	 * @param mixed $values
	 * @return boolean
	 */
	public function _remove_metadata_field_values(string $field, $values) {
		$field = $this->normalize_field_name ( $field );
		// $old_count = count ( $values );
		if (array_key_exists ( $field, $this->metadata )) {
			unset ( $this->metadata [$field] ); // FIXME
			$this->metadata [$field] = array_values ( $this->metadata [$field] );
		}
		if (! $this->metadata [$field]) {
		}
		return false;
	}
	/**
	 * add metadata to the document
	 *
	 * @param string $field
	 *        	name of the field
	 * @param mixed $values
	 *        	the value(s) of the field
	 * @param string $language
	 *        	[incomplete] language code of the value(s)
	 */
	public function add_metadata(string $field, $values, string $language = null) {
		$field = $this->normalize_field_name ( $field );

		if (! $field) {
			$field = "ndli_undefined_field";
		}
		if (is_object ( $values )) {
			$values = json_encode ( $values, JSON_UNESCAPED_UNICODE );
		}
		if (! is_array ( $values )) {
			$values = array (
					$values
			);
		}
		foreach ( $values as $i => $value ) {
			if (is_iterable ( $value )) {
				$values [$i] = json_encode ( $value );
			}
		}
		if ($values = array_values ( array_filter ( array_map ( "trim", $values ) ) )) {
			$this->metadata [$field] = array_key_exists ( $field, $this->metadata ) ? array_merge ( $this->metadata [$field], $values ) : $values;
		}
		return true;
	}

	/**
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function add_thumbnail_url(string $url) {
		if ($url = trim ( $url )) {
			array_push ( $this->url_thumbnail, $url );
			$this->url_thumbnail = array_unique ( $this->url_thumbnail );
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param NDLIAsset $asset
	 */
	public function add_asset(NDLIAsset $asset) {
		array_push ( $this->assets, $asset->get_asset () );
		return true;
	}

	/**
	 *
	 * @param string $src_id
	 * @param mixed $src_document_ids
	 * @return boolean
	 */
	public function add_parts(string $src_id, $src_document_ids) {
		if (! is_array ( $src_document_ids )) {
			$src_document_ids = array (
					$src_document_ids
			);
		}
		foreach ( $src_document_ids as &$id ) {
			$id = $src_id . "/" . $this->normalize_id ( $id );
		}
		return $this->add_metadata ( "ndli_relation_parts", $src_document_ids );
	}

	/**
	 *
	 * @param string $src_id
	 * @param mixed $src_document_ids
	 * @param string $relation_name
	 * @return boolean
	 */
	public function add_related_items(string $src_id, $src_document_ids, string $relation_name) {
		if (! is_array ( $src_document_ids )) {
			$src_document_ids = array (
					$src_document_ids
			);
		}
		foreach ( $src_document_ids as &$id ) {
			$id = $src_id . "/" . $this->normalize_id ( $id );
		}
		return $this->add_metadata ( $this->normalize_field_name ( "ndli_relation_" . $relation_name ), $src_document_ids );
	}

	/**
	 *
	 * @param string $collection_id
	 * @param string $collection_name
	 * @return bool
	 */
	public function set_collection(string $collection_id, string $collection_name = null) {
		if ($collection_id) {
			$this->collection_id = $this->normalize_id ( $collection_id );
			if ($collection_name) {
				$this->set_metadata ( "ndli_collection_name", trim ( $collection_name ) );
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param string $webpage_url
	 * @param array $breadcrumbs
	 * @return boolean
	 */
	public function set_webpage_info(string $webpage_url, array $breadcrumbs = array()) {
		if ($webpage_url) {
			$this->set_metadata ( "ndli_webpage_url", $webpage_url );
			if ($breadcrumbs) {
				$this->set_metadata ( "ndli_relation_breadcrumbs", implode ( "/", $breadcrumbs ) );
			}
			return true;
		}
		return false;
	}

	// GET ////////////////////////////////////////////////////////////////////////////////////////
	public function get_src_document_id() {
		return $this->src_document_id;
	}
	public function get_ndli_document_id() {
		return $this->ndli_document_id;
	}
	public function get_collection_id() {
		return $this->collection_id;
	}
	public function get_collection_name() {
		$values = $this->get_metadata ( "ndli_collection_name" );
		return $values ? current ( $values ) : null;
	}
	public function get_metadata($key = null) {
		if ($key) {
			return array_key_exists ( $key, $this->metadata ) ? $this->metadata [$key] : null;
		}
		return $this->metadata;
	}
	public function get_thumbnail_url() {
		return array_unique ( $this->url_thumbnail );
	}
	public function get_assets() {
		return ($this->assets);
	}
}
class NDLIDocumentSet {
	private $documents = array ();
	public function add_document_to_set(NDLIDocument $document) {
		return array_push ( $this->documents, $document );
	}
	public function get_documents_from_set() {
		return $this->documents;
	}
	public function size() {
		return count ( $this->documents );
	}
}
?>
