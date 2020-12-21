# FreshRSS TouchControl
Add touch gestures to FreshRSS

If an article is expanded:
- next article (swipe to left), 
- previous article (swipe to right)
- collapse article (double tap on the article)

If no article is expanded: 
- close folder-menu (swipe to left), 
- open folder-menu (swipe to right)

Always:
- open folder-menu (swipe from left-edge)
- close folder-menu (swipe from right-edge)

The bottom-navigation is hidden by default, to show:
- show bottom-navigation (swipe from bottom-edge up)

# Installation
To use it, upload the *xExtension-TouchControl* folder in your ./extensions directory and enable it on the extension panel in FreshRSS.

# Changelog
0.35
-Added RTL support
-Better Responsiveness and ignore possible miss swipes
-Easier cancellation of a swipe
-Action is done only when the finger is lifted.

0.2 
- switched to a fork of detectswipe with support of edge detection
- changed gestures to news+ style
- created understandable functions for customization (with some checks)

0.1
- first release