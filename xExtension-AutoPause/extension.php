<?php

class AutoPauseExtension extends Minz_Extension {

    public function init() {       
        Minz_View::appendScript($this->getFileUrl('script.js', 'js'),'','','');   
    }

}
