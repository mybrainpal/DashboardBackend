<?php

/**
 * This class is responsible for everything tracker related - such as:
 * - Clients Retrieval
 * - Conversion rates
 * - Experiments
 * - Other tracker related activity
 * 
 * @author Nati
 */
class Experiment {
	
	/**
	 * The tracker ID.
	 * 
	 * @var int
	 */
	private $id;
	
	/**
	 * The user ID of the tracker owner.
	 *
	 * @var int
	 */
	private $owner;
	
	/**
	 * The name of the tracker.
	 * 
	 * @var string
	 */
	private $name;
	
	/**
	 * The url of the tracker.
	 *
	 * @var string
	 */
	private $url;

    /**
	 * Initialize the default properties for the tracker.
	 * 
	 * @param int $tracker_id Optional - The tracker ID to load.
	 * @param bool $init Optional - Should the class load the information of the specified tracker?
	 */
	public function __construct($tracker_id = 0, $init = true) {
		// Set the tracker ID
		$this->id = intval($tracker_id);
		
		// If we should load the tracker information from the DB
		if( $this->id && $init ) {
    		// Load it
    		$this->loadTracker();
		}
	}
	
	/**
	 * Load a tracker into the object.
	 * In case no tracker ID is entered, the function takes the tracker ID from the ID property instead.
	 * 
	 * @param int $tracker_id Optional. The tracker ID to load.
	 */
	public function loadTracker($tracker_id = 0) {
		global $app;
        
		// If no username is provided, attempt to get one from the session
		if( empty($tracker_id) && !empty( $this->id ) ) {
			// There's a user logged in, use it
			$tracker_id = $this->id;
		}
		
		// Make sure the tracker ID is an integer
		$tracker_id = intval($tracker_id);
		
		// Make sure the tracker ID is a valid number
		if($tracker_id <= 0) {
		    // It isn't, throw an exception
		    error('Invalid tracker ID at Tracker::loadTracker()!');
		}
		
		// Now that we have a valid tracker ID, we can grab its info from the DB
		$result = $app->db->select('*')->from('`trackers`')->where(
		    '`id` = :id', array(
		        ':id' => $tracker_id
		    ))->execute();
		
		// If a user has returned from the database
		if( count($result) === 1 ) {
			// Use it
			$tracker = $result[0];

			// Set the tracker ID
			$this->id = intval($tracker['id']);

			// The user ID of the tracker owner
			$this->owner = intval($tracker['user_id']);
			
			// The tracker name
			$this->name = $tracker['name'];
			
			// The tracker URL
			$this->url = $tracker['url'];
		}
		// Otherwise raise an error
		else {
			error('Tracker does not exists at Tracker::loadTracker()!');
		}
	}
	
	/**
	 * Returns the clients information for the loaded tracker.
	 * 
	 * @param bool $data Optional. Should the function return the clients data or just their amount?
	 * @param int $days Optional. The amount of days to query. Defaults to DEFAULT_QUERY_DAYS.
	 * @param int $limit Optional. The query limit. Defaults to DEFAULT_QUERY_LIMIT.
	 * @return array A two dimensional array containing the clients and their information.
	 */
	public function getClients($data = true, $days = DEFAULT_QUERY_DAYS, $limit = DEFAULT_QUERY_LIMIT) {
	    global $app;
	    
	    // Make sure the tracker ID is a valid number
	    if($this->id <= 0) {
	        // It isn't, throw an exception
	        error('Invalid tracker ID at Tracker::getClients()!');
	    }
	    
	    // Make sure $limit is a valid query limit
	    $limit = intval($limit);
	    if($limit <= 0) {
	        // It isn't, throw an exception
	        error('Invalid limit at Tracker::getClients()!');
	    }
	    
	    // Check if we should return the data or just the clients amount
	    $select = ($data) ? '*' : 'count(id)';
	    
	    // Get all the unique clients for that tracker
	    $result = $app->db->select($select)->from('`clients`')->where(
	        '`tracker_id`=:tracker_id AND
            (`created` BETWEEN DATE_SUB(NOW(), INTERVAL :days DAY) AND NOW())', array(
                ':tracker_id' => $this->id,
                ':days' => $days,
	    ))->limit($limit)->execute();
        
        // Success! Return the clients information
        return $result;
	}
	
	/**
	 * Get the tracker ID.
	 * 
	 * @return int The tracker ID.
	 */
	public function getId()
	{
	    return $this->id;
	}
	
	/**
	 * Get the tracker owner.
	 * 
	 * @return int The user ID of the tracker.
	 */
	public function getOwner()
	{
	    return $this->owner;
	}
	
	/**
	 * Get the tracker name.
	 * 
	 * @return string The tracker name.
	 */
	public function getName()
	{
	    return $this->name;
	}
	
	/**
	 * Get the tracker url.
	 * 
	 * @return string The tracker's URL.
	 */
	public function getUrl()
	{
	    return $this->url;
	}
	
	/**
	 * Set the tracker owner.
	 * 
	 * @param int The user ID of tracker owner.
	 */
	public function setOwner($owner)
	{
	    $this->owner = $owner;
	}
	
	/**
	 * Set the tracker name.
	 * 
	 * @param string The tracker name.
	 */
	public function setName($name)
	{
	    $this->name = $name;
	}
	
	/**
	 * Set the tracker url.
	 * 
	 * @param string The tracker's URL.
	 */
	public function setUrl($url)
	{
	    $this->url = $url;
	}
}

?>