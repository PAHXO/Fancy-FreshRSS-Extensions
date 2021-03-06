/**
 * jquery.detectSwipe v3.0.1
 * jQuery Plugin to obtain touch gestures from iPhone, iPod Touch, iPad and Android
 * original: http://github.com/marcandre/detect_swipe
 * former fork: https://github.com/supachris28/detect_swipe
 * THIS FORK: https://github.com/PAHXO/FreshRSS-Extensions
 * Based on touchwipe by Andreas Waltl, netCU Internetagentur (http://www.netcu.de)
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function($) {

  $.detectSwipe = {
    version: '2.1.2',
    enabled: 'ontouchstart' in document.documentElement,
    preventDefault: true,
    threshold: 20,
    edgethreshold: $(window).width() / 15,
    debug: false
  };

  var startX,
    startY,
    isMoving = false;
	var dir;

  function onTouchEnd() {
    this.removeEventListener('touchmove', onTouchMove);
    this.removeEventListener('touchend', onTouchEnd);
    isMoving = false;
	$(this).trigger('swipe', dir).trigger('swipe' + dir);
	dir = "null";
  }

  function onTouchMove(e) {
    if ($.detectSwipe.preventDefault) { }
    if(isMoving) {
      var x = e.touches[0].clientX;
      var y = e.touches[0].clientY;
      var dx = startX - x;
      var dy = startY - y;

      
       //Detect anywhere hoirzontal swipes; you may want to change 85
      if(Math.abs(dx) >= 85) {
        dir = dx > 0 ? 'left' : 'right';
          
		  // TO TO DETECT EDGE SWIPE ; you may want to change 50
        if (startX < 50 && dir == 'right') {
            dir = 'fromleft';
        } else if (startX > ($(window).width() - 50) && dir == 'left') {
            dir = 'fromright';
        }
			
      }
	  
	  //Cancels horizontal swipes or initiates verical swipes; you may want to change 90
       if(Math.abs(dy) >= 90) {
        dir = dy > 0 ? 'up' : 'down';
		//dir = "null"; I don't use these two vertical swipes 
        if (startY < $.detectSwipe.edgethreshold && dir == 'down') {
            dir = 'fromtop';
        } else if (startY > ($(window).height() - $.detectSwipe.edgethreshold) && dir == 'up') {
            dir = 'frombottom';
        }
		
				  

      }
	  
	  
	  if(Math.abs(dx) <= $.detectSwipe.threshold)
	  {
		  dir = "null";
	  }
	  	  //console.log(dx);
		//console.log($.detectSwipe.threshold);
      if(dir) {
        //onTouchEnd.call(this);
        if ($.detectSwipe.debug) {
            console.log(dir);
        }
		 //console.log(dir);
        
      }
    }
  }

  function onTouchStart(e) {
    if (e.touches.length == 1) {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      isMoving = true;
      this.addEventListener('touchmove', onTouchMove, false);
      this.addEventListener('touchend', onTouchEnd, false);
    }
  }

  function setup() {
    this.addEventListener && this.addEventListener('touchstart', onTouchStart, false);
  }

  function teardown() {
    this.removeEventListener('touchstart', onTouchStart);
  }

  $.event.special.swipe = { setup: setup };

  $.each(['left', 'up', 'down', 'right'], function () {
    $.event.special['swipe' + this] = { 
        setup: function(){
            $(this).on('swipe', $.noop);
        } 
    };
  });
  $.each(['top', 'bottom', 'left', 'right'], function () {
    $.event.special['swipefrom' + this] = { 
        setup: function(){
            $(this).on('swipe', $.noop);
        } 
    };
  });
}));