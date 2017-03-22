/**
 * This file contains all the JS related to the dashboard's Attacks page.
 */

/* CONSTANTS */
TITLE 		= 'TRAFFIC EVENTS';
SUBTITLE	= 'In the last 30 minutes';

/**
 * Initialize the current JS file.
 */
function initDashboardAttacks() {
	// Set the active content navigation bar item
	contentSetActive('Attacks');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
	
	// Create the attacks graph
	createAttacksGraph('dashbaord-attacks-canvas');
}

$(function () {
	// Call the initializer function
	initDashboardAttacks();
});