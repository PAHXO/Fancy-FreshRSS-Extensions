# FreshRSS-Extensions

* [AutoPause](https://github.com/PAHXO/FreshRSS-Extensions/tree/main/xExtension-AutoPause): Plays videos when in view and stops them if out of view.

* [BetterTouchControl](https://github.com/PAHXO/FreshRSS-Extensions/tree/main/xExtension-BetterTouchControl): Based on [Touch Control](https://github.com/oyox/FreshRSS-extensions/tree/master/xExtension-TouchControl)
     - Added RTL support
     - Better Responsiveness and ignore possible miss swipes
     - Easier cancellation of a swipe
     - Action is done only when the finger is lifted.

* [MediaArchiver](https://github.com/PAHXO/FreshRSS-Extensions/tree/main/xExtension-MediaArchiver): Downloads Images and Videos for the feeds to be used offline.  Based on [Image Proxy](https://github.com/FreshRSS/Extensions/tree/master/xExtension-ImageProxy)
     - **WARNNING IT ARCIVES THEM INTO /p/i/ArchivedMedia which is an exposed directory**
     * Needs "Wget" to function which can be installed using the container's CLI commands, or  
       * apt update
       * apt install wget
     - Or you can uncomment "file_put_contents" but it has very poor performance
     - Give right permissions to /p/i/ArchivedMedia.
     
* [BitChute](https://github.com/PAHXO/FreshRSS-Extensions/tree/main/xExtension-BitChute): Based on [Youtube/Peertube](https://github.com/kevinpapst/freshrss-youtube)
     - Embeds BitChute videos
