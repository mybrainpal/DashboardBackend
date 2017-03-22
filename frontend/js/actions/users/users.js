/**
 * This file contains all the JS related to the users pages.
 */

/* CONSTANTS */
USERS_CATEGORY_NAME = 'Users';
USERS_NAVBAR_LINKS = {
		'LIST USERS': 'Users/ListUsers',
		'CREATE USER': 'Users/CreateUser',
		'IMPERSONATE': 'Users/Impersonate',
}

/**
 * Initialize the current JS file.
 */
function initUsers() {
	// If the user switched category
	if(header_active_category != USERS_CATEGORY_NAME) {
		// Set the navigation bar links
		contentReloadNavbar(USERS_NAVBAR_LINKS);
		
		// Active the appropriate navigation bar links
		headerSetActive(USERS_CATEGORY_NAME);
	}
}

$(function () {
	// Call the initializer function
	initUsers();
});