/**
 * This file contains all the JS related to the user listing page.
 */

/* CONSTANTS */
TITLE 		= 'SYSTEM USERS';
SUBTITLE	= '';

/**
 * Initialize the current JS file.
 */
function initUsersList() {
	// Set the active content navigation bar item
	contentSetActive('List Users');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
}

$(function () {
	// Call the initializer function
	initUsersList();
});