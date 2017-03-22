/**
 * This file contains all the JS related to the dashboard's home page.
 */

/* CONSTANTS */
TITLE 		= 'NETWORK STATUS';
SUBTITLE	= '';

/**
 * Initialize the current JS file.
 */
function initDashboardHome() {
	// Set the active content navigation bar item
	contentSetActive('Status');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
	
	// Create the attacks graph
	createAttacksGraph('dashbaord-home-attacks-canvas');
	
	// Create the meters graph
	createMetersGraph('dashbaord-home-meters-canvas');
}

$(function () {
	// Call the initializer function
	initDashboardHome();
});