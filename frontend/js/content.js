/* CONSTANTS */
const MAX_CONTENT_NAVBAR_LINK_WIDTH = 15;

/* GLOBALS */
content_focused_navbar_item = null;

/**
 * Called when the content is loaded (using the content's onLoad event).
 * Responsible for all content-related initialization procedures.
 */
function contentStart() {
}

/**
 * Reloads the content DIV HTML.
 * 
 * @param new_html The HTML to reload.
 */
function contentReload(new_html) {
	// Refresh the main content DIV
	$('#divContentContent').html(new_html);
	
	// Slide down the DIV
	elementSlideDown($('#divContentContent'));
	
	// Fade in the DIV
	elementFadeIn($('#divContentContent'));
}

/**
 * Reloads the content navigation bar links.
 * 
 * @param links The links to reload.
 */
function contentReloadNavbar(links) {
	// Calculate the amount of width each link block should take
	// (according to the number of links there are), to a maximum of 25%.
	links_width = Math.min(100 / Object.keys(links).length, MAX_CONTENT_NAVBAR_LINK_WIDTH) + '%';
	
	// Reset the current navigation bar
	$('#divContentNavbar').html('');
	
	// Loops through the navigation bar links array
	for(name in links) {
		// Create the link DIV
		link_div = document.createElement('div');
		
		// Set its CSS class to the navigation bar DIV class
		$(link_div).addClass('divContentNavbarDIV')
			.width(links_width) // Set the DIV width
			.click(function() { // Add the onClick event
				$(this).find('a').click();
			});

		// Create a link
		link_a = document.createElement('a');
		
		// Set its CSS class to the navigation bar links class
		$(link_a).addClass('divContentNavbarLink')
			.addClass('vertical-center') // The link should be vertically centered as well
			.text(name) // Set its text according to the links dictionary
			.attr('href', system_root + links[name]) // Set its HREF according to the links dictionary
			.attr('id', 'content-navbar-' + name.toLowerCase().replace(' ', '-'))
			.on( 'click', headerLinkClick) // Add the onClick event
			.appendTo($(link_div)); // Add the link to our crafted link's DIV

		// Append the DIV to the main navigation bar DIV
		$('#divContentNavbar').append(link_div);
	}
}

/**
 * Set the active navigation bar link.
 * 
 * @param name String The name of the link to focus.
 */
function contentSetActive(name) {
	// If there's an active menu item, un-focus it
	if( content_focused_navbar_item ) {
		// Reset its background color back to normal
		$(content_focused_navbar_item).removeClass('active');
	}
	
	// Get the activated navigation bar item DIV
	navbar_item = $('#content-navbar-' + name.toLowerCase().replace(' ', '-'));
	
	// Focus it
	$(navbar_item).addClass('active');

	// Set the focused element
	content_focused_navbar_item = navbar_item;
}

/**
 * Set the active navigation bar link.
 * 
 * @param title 	String The main page title.
 * @param subtitle 	String Optional. The page subtitle.
 */
function contentSetTitle(title, subtitle = '') {
	// Set the title
	$('#spanContentTitleText').text(title);
	
	// Set the subtitle
	$('#spanContentSubtitleText').text(subtitle);
}