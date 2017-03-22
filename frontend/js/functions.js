/* CONSTANTS */
const FADE_IN_SPEED = 750 // Elements fade in animation duration in microseconds
const SLIDE_DOWN_SPEED = 500 // Elements scroll down animation duration in microseconds
const SLIDE_DOWN_GAP = "5%" // Elements scroll down distance

/* GLOBALS */


/**
 * Slides down the specified element.
 * 
 * @param element The element to slide.
 */
function elementSlideDown(element) {	
	/* Slide required elements */
	$(element).css({ top: "-=" + SLIDE_DOWN_GAP }); // Set the position of the element higher than it is
											 // (so it can slide down)
	$(element).animate({ 
        top: "+=" + SLIDE_DOWN_GAP }, {
        	queue: false, 
        	duration: SLIDE_DOWN_SPEED
        	}); // Slide the elements
}

/**
 * Slides up the specified element.
 * 
 * @param element The element to slide.
 */
function elementSlideUp(element) {	
	/* Slide required elements */
	$(element).css({ top: "+=" + SLIDE_DOWN_GAP }); // Set the position of the element higher than it is
											 // (so it can slide down)
	$(element).animate({ 
        top: "-=" + SLIDE_DOWN_GAP }, {
        	queue: false, 
        	duration: SLIDE_DOWN_SPEED
        	}); // Slide the elements
}

/**
 * Fades in the specified element.
 * 
 * @param element The element to fade in.
 */
function elementFadeIn(element) {	
	/* Fade in required elements */
	$(element).css({ opacity: 0.1 }); // Set the opacity of all elements to near invisible
	$(element).animate({
		opacity: 1 }, {
			queue: false,
			duration: FADE_IN_SPEED
			}); // Fade in the elements
}

/**
 * Implement format() for javascript strings.
 */
//First, checks if it isn't implemented yet.
if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}