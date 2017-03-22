/**
 * This file contains all the JS related to the user creation page.
 */

/* CONSTANTS */
TITLE 		= 'CREATE USER';
SUBTITLE	= '';

/**
 * Initialize the current JS file.
 */
function initUsersCreate() {
	// Set the active content navigation bar item
	contentSetActive('Create User');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
}

$(function () {
	// Call the initializer function
	initUsersCreate();
});