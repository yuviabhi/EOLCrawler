<?php

// SANITIZE THE DATA
function sanitize($data){

	$dtype = gettype($data);
	switch($dtype) {
	
		case 'integer':
			//echo $dtype , " -> ", $data , "\n";
			if ($data == '')
				{
					$data = null;	
					//echo $data , ' check';
				}
			break;
			
		case 'double':
			//echo $dtype , " -> ", $data , "\n";
			if ($data == '')
				{
					$data = null;	
					//echo $data , ' check';
				}
			break;
			
		case 'string':
			//echo $dtype , " -> ", $data , "\n";
			if ($data == '')
				{
					$data = null;	
					//echo $data , ' check';
				}
			break;
			
		case 'array':
			//echo $dtype , " -> ", json_encode($data) , "\n";
			if ($data == '')
				{
					$data = '[]';	
					//echo $data , ' check';
				}
			
			// replaces \" from the array
			$new_data = array();
			foreach ($data as $d) {
				if(is_array($d)){
					array_push($new_data, sanitize($d));
				} else {
					$d = preg_replace("/\"/","'",$d); // escape double quote
					array_push($new_data, $d); 
				}
			}
			$data = $new_data;
			
			break;
			
		default:
			//echo $dtype , " -> " , $data , "\n";
			break;		
	}
		
	return $data;	
}

// CONVERT PHP ARRAY TO POSTGRESQL ARRAY
function to_pg_array($set) {
    settype($set, 'array'); // can be called with a scalar or array
    $result = array();
    foreach ($set as $t) {
        if (is_array($t))
        	$result[] = to_pg_array($t); 
        else {
            $t = str_replace('"', '\\"', $t); // escape double quote
            if (! is_numeric($t)) // quote only non-numeric values
                $t = '"' . $t . '"';
            $result[] = $t;
        }
    }
    return '{' . implode(",", $result) . '}'; // format
}


?>
