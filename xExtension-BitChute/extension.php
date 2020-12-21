<?php

/**
 * Class bitchuteExtension
 *
 *
 *
 */
class BitChuteExtension extends Minz_Extension
{
    /**
     * Video player width
     * @var int
     */
    protected $width = 560;
    /**
     * Video player height
     * @var int
     */
    protected $height = 315;
    /**
     * Whether we display the original feed content
     * @var bool
     */
    protected $showContent = false;
    /**
     * Switch to enable the bitchute No-Cookie domain
     * @var bool
     */
    protected $useNoCookie = false;

    /**
     * Initialize this extension
     */
    public function init()
    {
        $this->registerHook('entry_before_display', array($this, 'embedbitchuteVideo'));
        $this->registerTranslates();
    }


    /**
     * Initializes the extension configuration, if the user context is available.
     * Do not call that in your extensions init() method, it can't be used there.
     */
    public function loadConfigValues()
    {
        if (!class_exists('FreshRSS_Context', false) || null === FreshRSS_Context::$user_conf) {
            return;
        }

        if (FreshRSS_Context::$user_conf->yt_player_width != '') {
            $this->width = FreshRSS_Context::$user_conf->yt_player_width;
        }
        if (FreshRSS_Context::$user_conf->yt_player_height != '') {
            $this->height = FreshRSS_Context::$user_conf->yt_player_height;
        }
        if (FreshRSS_Context::$user_conf->yt_show_content != '') {
            $this->showContent = (bool)FreshRSS_Context::$user_conf->yt_show_content;
        }
        if (FreshRSS_Context::$user_conf->yt_nocookie != '') {
            $this->useNoCookie = (bool)FreshRSS_Context::$user_conf->yt_nocookie;
        }
    }

    /**
     * Returns the width in pixel for the bitchute player iframe.
     * You have to call loadConfigValues() before this one, otherwise you get default values.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height in pixel for the bitchute player iframe.
     * You have to call loadConfigValues() before this one, otherwise you get default values.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns whether this extensions displays the content of the bitchute feed.
     * You have to call loadConfigValues() before this one, otherwise you get default values.
     *
     * @return bool
     */
    public function isShowContent()
    {
        return $this->showContent;
    }
    
    /**
     * Returns if this extension should use bitchute-nocookie.com instead of bitchute.com.
     * You have to call loadConfigValues() before this one, otherwise you get default values.
     *
     * @return bool
     */
    public function isUseNoCookieDomain()
    {
        return $this->useNoCookie;
    }

    /**
     * Inserts the bitchute video iframe into the content of an entry, if the entries link points to a bitchute watch URL.
     *
     * @param FreshRSS_Entry $entry
     * @return mixed
     */
    public function embedbitchuteVideo($entry)
    {
        $link = $entry->link();

        if (preg_match('#^https?://www\.bitchute\.com|/embed/watch/[0-9a-f-]{36}$#', $link) !== 1) {
            return $entry;
        }

        $this->loadConfigValues();

        if (stripos($entry->content(), '<iframe class="bitchute-plugin-video"') !== false) {
            return $entry;
        }
        if (stripos($link, 'www.bitchute.com/embed/') !== false) {
            $html = $this->getHtmlContentForLink($entry, $link);
        }
        else { //peertube
            $html = $this->getHtmlPeerTubeContentForLink($entry, $link);
        }

        $entry->_content($html);
		$domain = 'www.bitchute.com';
		$url = str_replace('//www.bitchute.com/embed/', '//'.$domain.'/video/', $link);
		$entry->_link($url);
        return $entry;
    }

    /**
     * Returns an HTML <iframe> for a given bitchute watch URL (www.bitchute.com/watch?v=)
     *
     * @param string $link
     * @return string
     */
    public function getHtmlContentForLink($entry, $link)
    {
        $domain = 'www.bitchute.com';
        $url = str_replace('//www.bitchute.com/embed/', '//'.$domain.'/embed/', $link);
        $url = str_replace('http://', 'https://', $url);

        $html = $this->getHtml($entry, $url);

        return $html;
    }

    /**
    * Returns an HTML <iframe> for a given PeerTube watch URL
    *
    * @param string $link
    * @return string
    */
    public function getHtmlPeerTubeContentForLink($entry, $link)
    {
        $url = str_replace('/watch', '/embed', $link);
        $html = $this->getHtml($entry, $url);
        
        return $html;
    }

    /**
     * Returns an HTML <iframe> for a given URL for the configured width and height, with content ignored, appended or formated.
     *
     * @param string $url
     * @return string
     */
    public function getHtml($entry, $url)
    {
        $content = '';
        
        $iframe = '<iframe class="bitchute-plugin-video"
                style="height: ' . $this->height . 'px; width: ' . $this->width . 'px;" 
                width="' . $this->width . '" 
                height="' . $this->height . '" 
                src="' . $url . '" 
                frameborder="0" 
                allowFullScreen></iframe>';

        if ($this->showContent) {
            $doc = new DOMDocument();
            $doc->encoding = 'UTF-8';
            $doc->recover = true;
            $doc->strictErrorChecking = false;

            if ($doc->loadHTML('<?xml encoding="utf-8" ?>' . $entry->content()))
            {
                $xpath = new DOMXpath($doc);

                $titles = $xpath->evaluate("//*[@class='enclosure-title']");
                $thumbnails = $xpath->evaluate("//*[@class='enclosure-thumbnail']/@src");
                $descriptions = $xpath->evaluate("//*[@class='enclosure-description']");

                $content = '<div class="enclosure">';

                // We hide the title so it doesn't appear in the final article, which would be redundant with the RSS article title,
                // but we keep it in the content anyway, so RSS clients can extract it if needed.
                if ($titles->length > 0) {
                    $content .= '<p class="enclosure-title" hidden>' . $titles[0]->nodeValue . '</p>';
                }

                // We hide the thumbnail so it doesn't appear in the final article, which would be redundant with the bitchute player preview,
                // but we keep it in the content anyway, so RSS clients can extract it to display a preview where it wants (in article listing,
                // by example, like with Reeder).
                if ($thumbnails->length > 0) {
                    $content .= '<p hidden><img class="enclosure-thumbnail" src="' . $thumbnails[0]->nodeValue . '" alt=""/></p>';
                }

                $content .= $iframe;

                if ($descriptions->length > 0) {
                    $content .= '<p class="enclosure-description">' . nl2br(htmlentities($descriptions[0]->nodeValue)) . '</p>';
                }

                $content .= "</div>\n";
            }
            else {
                $content = $iframe . $entry->content();
            }
        }
        else {
            $content = $iframe;
        }

        return $content;
    }

    /**
     * This function is called by FreshRSS when the configuration page is loaded, and when configuration is saved.
     *  - We save configuration in case of a post.
     *  - We (re)load configuration in all case, so they are in-sync after a save and before a page load.
     */
    public function handleConfigureAction()
    {
        $this->registerTranslates();

        if (Minz_Request::isPost()) {
            FreshRSS_Context::$user_conf->yt_player_height = (int)Minz_Request::param('yt_height', '');
            FreshRSS_Context::$user_conf->yt_player_width = (int)Minz_Request::param('yt_width', '');
            FreshRSS_Context::$user_conf->yt_show_content = (bool)Minz_Request::param('yt_show_content', 0);
            FreshRSS_Context::$user_conf->yt_nocookie = (int)Minz_Request::param('yt_nocookie', 0);
            FreshRSS_Context::$user_conf->save();
        }

        $this->loadConfigValues();
    }
}