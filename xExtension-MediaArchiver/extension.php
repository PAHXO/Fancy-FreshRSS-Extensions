<?php

class MediaArchiverExtension extends Minz_Extension {
	// Defaults
	const PROXY_URL = 'nothing used'; // needs to be reimplemented
	const SCHEME_HTTP = '1';
	const SCHEME_HTTPS = '1';
	const SCHEME_DEFAULT = 'auto';
	const SCHEME_INCLUDE = '';
	const URL_ENCODE = '1';

	public function init() {
		$this->registerHook('entry_before_display',
		                    array('MediaArchiverExtension', 'setMediaArchiverHook'));
		// Defaults
		$save = false;
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_url)) {
			FreshRSS_Context::$user_conf->media_achiver_url = self::PROXY_URL;
			$save = true;
		}
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_scheme_http)) {
			FreshRSS_Context::$user_conf->media_achiver_scheme_http = self::SCHEME_HTTP;
			$save = true;
		}
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_scheme_https)) {
			FreshRSS_Context::$user_conf->media_achiver_scheme_https = self::SCHEME_HTTPS;
			// Legacy
			if (!is_null(FreshRSS_Context::$user_conf->media_achiver_force)) {
				FreshRSS_Context::$user_conf->media_achiver_scheme_https = FreshRSS_Context::$user_conf->media_achiver_force;
				FreshRSS_Context::$user_conf->media_achiver_force = null;  // Minz -> unset
			}
			$save = true;
		}
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_scheme_default)) {
			FreshRSS_Context::$user_conf->media_achiver_scheme_default = self::SCHEME_DEFAULT;
			$save = true;
		}
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_scheme_include)) {
			FreshRSS_Context::$user_conf->media_achiver_scheme_include = self::SCHEME_INCLUDE;
			$save = true;
		}
		if (is_null(FreshRSS_Context::$user_conf->media_achiver_url_encode)) {
			FreshRSS_Context::$user_conf->media_achiver_url_encode = self::URL_ENCODE;
			$save = true;
		}
		if ($save) {
			FreshRSS_Context::$user_conf->save();
		}
	}

	public function handleConfigureAction() {
		$this->registerTranslates();

		if (Minz_Request::isPost()) {
			FreshRSS_Context::$user_conf->media_achiver_url = Minz_Request::param('media_achiver_url', self::PROXY_URL, true);
			FreshRSS_Context::$user_conf->media_achiver_scheme_http = Minz_Request::param('media_achiver_scheme_http', '');
			FreshRSS_Context::$user_conf->media_achiver_scheme_https = Minz_Request::param('media_achiver_scheme_https', '');
			FreshRSS_Context::$user_conf->media_achiver_scheme_default = Minz_Request::param('media_achiver_scheme_default', self::SCHEME_DEFAULT);
			FreshRSS_Context::$user_conf->media_achiver_scheme_include = Minz_Request::param('media_achiver_scheme_include', '');
			FreshRSS_Context::$user_conf->media_achiver_url_encode = Minz_Request::param('media_achiver_url_encode', '');
			FreshRSS_Context::$user_conf->save();
		}
	}

	public static function downloadImage($url) {
		// PROXY IS NOT USED 
		
		
		$original_url = $url;
		$parsed_url = parse_url($url);
		$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] : null;
		if ($scheme === 'http') {
			if (!FreshRSS_Context::$user_conf->media_achiver_scheme_http) return $url;
			if (!FreshRSS_Context::$user_conf->media_achiver_scheme_include) {
				$url = substr($url, 7);  // http://
			}
		}
		else if ($scheme === 'https') {
			if (false) return $url;
			if (!FreshRSS_Context::$user_conf->media_achiver_scheme_include) {
				$url = substr($url, 8);  // https://
			}
		}
		else if (empty($scheme)) {
			if (FreshRSS_Context::$user_conf->media_achiver_scheme_default === 'auto') {
				if (FreshRSS_Context::$user_conf->media_achiver_scheme_include) {
					$url = ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!== 'off') ? 'https:' : 'http:') . $url;
				}
			}
			else if (substr(FreshRSS_Context::$user_conf->media_achiver_scheme_default, 0, 4) === 'http') {
				if (FreshRSS_Context::$user_conf->media_achiver_scheme_include) {
					$url = FreshRSS_Context::$user_conf->media_achiver_scheme_default . ':' . $url;
				}
			}
			else {  // do not proxy unschemed ("//path/...") URLs
				return $url;
			}
		}
		else {  // unknown/unsupported (non-http) scheme
			return $url;
		}
		if (FreshRSS_Context::$user_conf->media_achiver_url_encode) {
			$url = rawurlencode($url);
		}
		
		// You can do stuff here
		if (!is_dir ('../i/ArchivedMedia/'))
		{
			exec('mkdir ../i/ArchivedMedia/');
			
			//Please, give write access to this folder
			// #note IT IS AN EXPOSED FOLDER SO DO NOT USE ON A PUBLIC SERVER!
		}
		
		$filePath = "../i/ArchivedMedia/" . md5($url);

		if (!file_exists($filePath))
		{
			//file_put_contents($filePath, file_get_contents($original_url));
			$cmdd = 'wget ';
			$cmdd .= '-U "Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.84 Mobile Safari/537.36" ';
			$cmdd .= '"'.$original_url .'"';
			$cmdd .= ' -O ';
			$cmdd .= $filePath;
			exec($cmdd);
			return $original_url;

			
		}
		
		return "../i/ArchivedMedia/" . md5($url);
	}

	public static function getSrcSetUris($matches) {
		return str_replace($matches[1], self::downloadImage($matches[1]), $matches[0]);
	}

	public static function swapUris($content) {
		if (empty($content)) {
			return $content;
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors(true); // prevent tag soup errors from showing
		$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		$imgs = $doc->getElementsByTagName('img');
		foreach ($imgs as $img) {
			if ($img->hasAttribute('src')) {
				$newSrc = self::downloadImage($img->getAttribute('src'));
				$img->setAttribute('src', $newSrc);
			}
			if ($img->hasAttribute('srcset')) {
				$newSrcSet = preg_replace_callback('/(?:([^\s,]+)(\s*(?:\s+\d+[wx])(?:,\s*)?))/', 'self::getSrcSetUris', $img->getAttribute('srcset'));
				$img->setAttribute('srcset', $newSrcSet);
			}
		}
		
		// for instagram videos
				$videos = $doc->getElementsByTagName('source');
		foreach ($videos as $video) {
			if ($video->hasAttribute('src')) {
				$newSrc = self::downloadImage($video->getAttribute('src'));
				$video->setAttribute('src', $newSrc);
			}

		}
		//
		
		//for twitter videos
				$videosTWT = $doc->getElementsByTagName('video');
		foreach ($videosTWT as $videoTWT) {
			if ($videoTWT->hasAttribute('src')) {
				$newSrc = self::downloadImage($videoTWT->getAttribute('src'));
				$videoTWT->setAttribute('src', $newSrc);
			}

		}
		//

		return $doc->saveHTML();
	}

	public static function setMediaArchiverHook($entry) {
		$entry->_content(
			self::swapUris($entry->content())
		);

		return $entry;
	}
}
