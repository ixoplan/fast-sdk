<?php


namespace Ixolit\CDE\Interfaces;

/**
 * The sitemap renderer generates a Google sitemap.
 */
interface SitemapRenderer {
	/**
	 * Renders a sitemap.xml file for the given vhost. Always uses the default layout.
	 *
	 * @param null|string $vhost defaults to current effective vhost
	 * @param array       $languages defaults to all available if empty
	 * @param array       $excludePatterns regular expressions matched against the page ID excluding any matching
	 *
	 * @return string
	 */
	function render($vhost = null, $languages = [], $excludePatterns = []);
}