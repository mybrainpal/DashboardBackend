/**
 * This file contains all the JS related to the user login template.
 */

/* CONSTANTS */
TITLE 		= 'MY PROFILE';
SUBTITLE	= '';

/**
 * Initialize the current JS file.
 */
function initUserProfile() {
	// Set the active content navigation bar item
	contentSetActive('Profile');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
}

$(function () {
	// Call the initializer function
	initUserProfile();
});