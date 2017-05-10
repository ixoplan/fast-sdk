<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\WorkingObjects\Page;

class SitemapRenderer implements Interfaces\SitemapRenderer {
	/**
	 * @var PagesAPI
	 */
	private $pagesApi;

	/**
	 * @var CDERequestAPI
	 */
	private $requestApi;

	public function __construct(PagesAPI $pagesApi, RequestAPI $requestApi) {
		$this->pagesApi = $pagesApi;
		$this->requestApi = $requestApi;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($vhost = null, $languages = [], $excludePatterns = []) {
		$output = '';
		$output .= '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= '<urlset'.
			' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
			' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
			' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' .
				' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"' .
			'>';

		if (empty($languages)) {
			$languages = $this->pagesApi->getLanguages();
		}
		foreach ($languages as $lang) {
			// TODO: ksort ?
			$output .= $this->renderSitemapFragment($this->pagesApi->getAll($vhost, $lang, 'default'), $excludePatterns);
		}

		$output .= '</urlset>';

		return $output;
	}

	/**
	 * Renders an XML sitemap fragment.
	 *
	 * @param Page[] $pages
	 * @param array $excludePatterns
	 *
	 * @return string
	 *
	 * @internal
	 */
	private function renderSitemapFragment($pages, $excludePatterns) {
		$output = '';
		foreach ($pages as $id => $page) {

			foreach ($excludePatterns as $pattern) {
				if (preg_match($pattern, $id)) {
					continue 2;
				}
			}

			$output  .= '<url>';
			$output  .= '<loc>' . \xml($page->getPageUrl()) . '</loc>';
			$output  .= '<changefreq>daily</changefreq>';
			$level    = \substr_count($page->getPagePath(), '/');
			$priority = \round(1 - \min($level - 1, 9)/10, 2);
			$output  .= '<priority>' . \xml($priority) . '</priority>';
			$output  .= '</url>';
		}
		return $output;
	}
}
