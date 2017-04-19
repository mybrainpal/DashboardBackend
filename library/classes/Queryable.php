<?php

/**
 * This is a mother class for all classes that handle DB objects (customers, trackers, experiments, etc.).
 * It features results organizing functions, as well as security checks and other quality-of-life improvements.
 * 
 * @author Nati
 */
abstract class Queryable {
	
	/**
	 * The instance DB ID.
	 * 
	 * @var int
	 */
	protected $id;
	
	/**
	 * The table name for the object.
	 *
	 * @var string
	 */
	protected $table;
	
	/**
	 * The fields the object can update in the DB.
	 * 
	 * @var array
	 */
	protected $update_fields;
	
	/**
	 * Initialize the default properties for this object.
	 *
	 * @param int $id Optional - The DB instance ID to load.
	 * @param bool $init Optional - Should the class load the information of the specified object from the DB?
	 */
	public function __construct($id = 0, $init = true) {
	    // Set the instance ID
	    $this->id = intval($id);
	    
	    // Check that we have a defined table name
	    if( empty($this->table) ) {
	        error('No table name specified for object');
	    }
	
	    // If we should load the instance information from the DB
	    if( $this->id && $init ) {
	        // Load it
	        $this->load();
	    }
	    
	    
	}
	
	/**
	 * Load data from the DB into the object.
	 * In case no specific ID is entered, the function takes the DB ID from the ID property instead.
	 *
	 * @param int $id Optional. The SB ID to load.
	 */
	public function load($id = 0) {
	    global $app;
	
	    // If no username is provided, attempt to get one from the session
	    if( empty($id) && !empty( $this->id ) ) {
	        // There's a user logged in, use it
	        $id = $this->id;
	    }
	
	    // Make sure the tracker ID is an integer
	    $id = intval($id);
	
	    // Make sure the tracker ID is a valid number
	    if($id <= 0) {
	        // It isn't, throw an exception
	        error('Invalid ID!');
	    }
	    
	    // Now that we have a valid tracker ID, we can grab its info from the DB
	    $result = $app->db->select('*')->from($this->table)->where(
	        '`id` = :id', array(
	            ':id' => $id
        ))->execute();
        
        // If the instance was found in the DB
        if( count($result) === 1 ) {
            // Use it
            $instance = $result[0];
            
            // Loop through the different columns and set each of them to the matching instance property
            foreach($instance as $property => $value) {
                // Make sure we want this property
                if( property_exists($this, $property) ) {
                    // Set the property according to the data in the DB
                    $this->$property = is_numeric($value) ? intval($value) : $value;
                }
            }
        }
        // Otherwise raise an error
        else {
            error('Instance does not exists!');
        }
        
	}
	
	/**
	 * Set instance data in the DB.
	 * This function is using the 'update_fields' property in order to determine which field it can update.
	 * 
	 * @see Queryable::update_fields
	 */
	public function save() {
	    global $app;
	    
	    // If we don't have an ID, raise an exception
        if (empty($this->id)) {
            error('Invalid user ID');
        }
	
	    // Generate the query
	    $query = ''; // The query string variable
	    foreach($this->update_fields as $field) {
	        // Add the key and quoted value to the query from the instance
	        $query .= "`$field` = {$app->db->quote($this->$field)}, ";
	    }
	
	    // Remove the last comma
	    $query = substr($query, 0, -2);
	
	    // Set the additional information row in the DB
	    $result = $app->db->update($this->table)->set("($query)")->where('`id`=:id', array(
	        ':id' => $this->id
	    ))->execute();
	}
	
	/**
	 * Creates a new DB row for the current object.
	 * If the function fails it prints an error, otherwise it returns the new object ID.
	 *
	 * @return int The new object's ID.
	 */
	public function create() {
	    global $app;
	    
	    // Make sure we don't have an ID
	    if( !empty($this->id) ) {
	        // We do, the user should use the save() method instead
	        error('Current object has an ID, please use the save() method');
	    }
	
        // Generate the query
        $fields = $values = ''; // The query string variable
        foreach($this->update_fields as $field) {
            // Add the key and quoted value to the query from the instance
            $fields .= "`$field`, ";
            $values .= $app->db->quote($this->$field) . ', ';
        }
        
        // Remove the last commas
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);
	
	    // And insert everything to the DB
	    $app->db->insert_into($this->table)->_("($fields)")->values("($values)")->execute();
	
	    // If we've passed the last query we can safely assume that we have an ID to use, so get it
	    $id = $app->db->lastInsertId;
	    
	    // Set it in the object
	    $this->id = $id;

	    // Return the new object's ID
	    return $id;
	}
	
	/**
	 * Deletes the DB record for this object.
	 * If the function fails it prints an error, otherwise it returns boolean true.
	 *
	 * @return boolean Whether the function succeeded or not.
	 */
	public function delete() {
	    global $app;
	    
	    // If we don't have an ID, raise an exception
        if (empty($this->id)) {
            error('Invalid user ID');
        }
	
	    // And insert everything to the DB
	    $app->db->delete_from($this->table)->where('`id`=:id', array(
	        ':id' => $this->id
	    ))->execute();
	
	    // If we've reached here, everything is OK
	    return true;
	}
	
	/**
	 * Makes sure the current instance can support DB queries.
	 * It validates the customer ID, the amount of days to query, and the query limit.
	 *
	 * @param int $days The amount of days to query. Has to be an integer larger than 0.
	 * @param int $limit The maximum amount of rows to return. Has to be an integer larger than 0.
	 */
	protected function validateQueryInput(&$days, &$limit) {
	    // Make sure the customer ID is a valid number
	    if($this->id <= 0) {
	        // It isn't, throw an exception
	        error('Invalid ID!');
	    }
	
	    // Make sure $limit is a valid query limit
	    if($limit <= 0 || !is_numeric($limit)) {
	        // It isn't, throw an exception
	        error('Invalid limit!');
	    }
	
	    // Make sure $days is a valid amount of days
	    $days = intval($days);
	    if($days <= 0) {
	        // It isn't, throw an exception
	        error('Invalid days!');
	    }
	}
	
	/**
	 * Parse and organize query results for better usage.
	 * 
	 * @param array $query_result The DB query result.
	 * @return array An organized and better looking query result array.
	 */
	protected function organizeResult($query_result) {
	    // Group the result by dates
	    $query_result = $this->organizeResultDate($query_result);
	    
	    // Remove count(id) (if necessary)
	    $query_result = $this->organizeResultCount($query_result);
	    
	    // Return the organized query result
	    return $query_result;
	}
	
	/**
	 * Groups data returned from the DB by the row's date.
	 * 
	 * @param array $query_result The DB query result.
	 * @return array An organized array where the date is the index and the rows are the values.
	 */
	protected function organizeResultDate($query_result) {
	    // Split the returned data based on the day
	    $result = array(); // The results final array
	    foreach($query_result as $row) {
	        // Use the row creation date as the array key, and a reference to the row as the returned value
	        $result[$row['DATE(created)']] = $row;
	         
	        // Remove the creation date from the row (as we added it for internal use)
	        unset($result[$row['DATE(created)']]['DATE(created)']);
	    }
	    
	    // Return the final result array
	    return $result;
	}
	
	/**
	 * Replace the ugly 'count(*)' keys with the better looking 'count' keys.
	 *
	 * @param array $query_result The DB query result.
	 * @return array An organized array where 'count(id)' keys are replace with 'count' keys.
	 */
	protected function organizeResultCount($query_result) {
	    // Loop through the query result in order to remove count(id)
	    foreach($query_result as &$row) {
	        // Loop through the columns and search for a count() row
	        foreach( $row as $field => $value ) {
	            // Check that we are organizing a count() row
	            if( preg_match('/count\((\w+)\)/', $field) ) {
	                // Move count(id) to its new key
	                $row['count'] = $row[$field];
	            
	                // Remove it from the array
	                unset($row[$field]);
	            }
	        }
	    }
	     
	    // Return the organized query result
	    return $query_result;
	}
	
	/**
	 * This function is called when the user tries to get a property that does not exist or is protected.
	 * The function returns the requested property if it exists in this object.
	 * If the property could not be found, an error is being thrown.
	 *
	 * @param string $name The name of the property to fetch.
	 * @return mixed The value of the property if it exists.
	 */
	public function __get($name) {
	    // Check if $name is a property in the object
	    if( property_exists($this, $name) ) {
	        // It is! Return it
	        return $this->$name;
	    } else {
	        // We couldn't find the property, raise an error
	        error("Could not find property '$name' in this object!");
	    }
	}
	
	/**
	 * This function is called when the user tries to set a property that does not exist or is protected.
	 * The function sets the requested property if it exist either in this object.
	 * If the property could not be found, an error is being thrown.
	 *
	 * @param string $name The name of the property to set.
	 * @param mixed $value The value of the property to set.
	 */
	public function __set($name, $value) {
	    // Check if the property exist in the object and it's not the instance ID
	    if( property_exists($this, $name) && $name !== 'id') {
	        // It is! Set it
	        $this->$name = $value;
	    } else {
	        // We couldn't find the property, raise an error
	        error("Could not set property '$name' in this object!");
	    }
	}
}

?>