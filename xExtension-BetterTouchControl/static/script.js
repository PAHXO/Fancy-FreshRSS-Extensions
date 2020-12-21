$(document).ready(function ($) {

    // Available actions:
    // bottom_nav_toggle()  // Toggle bottom navigation
    // menu_open()          // Display folder list
    // menu_close()         // Hide folder list
    // entry_next()         // Next entry
    // entry_previous()     // Previous entry
    // entry_close()        // Close entry
    
    // Available checks:
    // is_small_display()
    // is_menu_open()
    // is_entry_open


    // Init config
    $.detectSwipe.preventDefault = true;
    $.detectSwipe.threshold = 60;
    bottom_nav_toggle(); // Hide bottom menu on pageload

    // Swipe from right edge
    $('body').on('swipefromright', function () {
			
		if (document.dir != "rtl")
			{
			menu_close();
			}else{
			menu_open();
			}
		
    });

    // Swipe from left edge
    $('body').on('swipefromleft', function () {
		
        if (document.dir != "rtl")
			{
			menu_open();
			}else{
			menu_close();
			}
		
    });

    // Swipe from bottom edge
    $('body').on('swipefrombottom', function () {
        bottom_nav_toggle();
    });

    // Swipe left
    $('body').on('swipeleft', function () {

	
			if (document.dir != "rtl")
			{
				
				 // If menu is open, close it - otherwise next entry
				if (is_menu_open()) 
					{
						menu_close();
					}else 
						{
							entry_next();
						}
					
			}else{
			    // If entry is open switch to previous entry - otherwise open menu
				if (is_entry_open()) 
				{
					entry_previous();
				} else 
					{
						menu_open();
					}
			
			}
		
    });

    // Swipe right
    $('body').on('swiperight', function () {


			if (document.dir != "rtl")
			{
				
				// If entry is open switch to previous entry - otherwise open menu
				if (is_entry_open()) 
				{
					entry_previous();
					
				} else 
					{
						menu_open();
					}
						
			}else{
			    // If entry is open switch to previous entry - otherwise open menu
        // If menu is open, close it - otherwise next entry
				if (is_menu_open()) 
				{
					menu_close();
				} else 
					{
						entry_next();
					}
			
			}
    });

    // Doubletap (supports delegate) on article
    $('body').on('doubletap', function () {
        entry_close();
    });

});

// Control functions

// Hide folder list
function menu_close() {
    $('.toggle_aside')[0].click();
}

// Display folder list
function menu_open() {
    $('.btn.toggle_aside')[0].click();
}

// Previous entry
function entry_previous() {
		//alert("hello");

    $('.previous_entry')[0].click();
}

// Next entry
function entry_next() {
		//	alert("hello");

    $('.next_entry')[0].click();
}

// Next entry
function entry_close() {
    if ($('div.flux.current.active').length) {

        // Slide to prevent an accidental click on another article.
        $('div.flux.current.active>.flux_content').slideUp('slow', function () {
            $('div.flux.current.active').removeClass('active');

            // Reset display state to enable re-display
            $(this).css('display', '');
        });
    }
}

// Bottom Navigation toggle
function bottom_nav_toggle() {
    $('#nav_entries').slideToggle();
}

// Check functions

// Is an article expanded?
function is_entry_open() {
    if ($('div.flux.current.active').length) {
        return true;
    } else {
        return false;
    }
}

// Is the folder list visible?
function is_menu_open() {
    if ($('div.aside.aside_feed').width() > 10) {
        return true;
    } else {
        return false;
    }
}

// Display on mobile phone / small device?
function is_small_display() {
    var width = $(window).width();
    if (width <= 768) {
        return true;
    } else {
        return false;
    }
}