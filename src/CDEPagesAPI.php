<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\WorkingObjects\BreadcrumbEntry;
use Ixolit\CDE\WorkingObjects\Page;

/**
 * This API implements the pages API using the CDE API calls.
 */
class CDEPagesAPI implements PagesAPI {
	/**
	 * {@inheritdoc}
	 */
	public function getAll($vhost = null, $lang = null, $layout = null, $scheme = null) {
		if (!\function_exists('getAllPages')) {
			throw new CDEFeatureNotSupportedException('getAllPages');
		}
		$pages = \getAllPages($vhost, $lang, $layout, $scheme);

		$result = [];
		foreach ($pages as $id => $page) {
			$result[$id] = new Page($page->pageUrl, $page->pagePath);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLanguages() {
		if (!\function_exists('getAllLanguages')) {
			throw new CDEFeatureNotSupportedException('getAllLanguages');
		}
		return \getAllLanguages();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBreadcrumb($page = null, $lang = null, $layout = null) {
		if (!\function_exists('getBreadcrumb')) {
			throw new CDEFeatureNotSupportedException('getBreadcrumb');
		}
		$breadcrumb = getBreadcrumb($page, $lang, $layout);
		$result = [];
		if (\is_array($breadcrumb)) {
			foreach ($breadcrumb as $entry) {
				$result[] = new BreadcrumbEntry(
					$entry->pageId,
					$entry->url,
					$entry->title
				);
			}
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($meta, $lang = null, $pagePath = null, $layout = null) {
		if (!\function_exists('getMeta')) {
			throw new CDEFeatureNotSupportedException('getBreadcrumb');
		}

		$data = \getMeta($meta, $lang, $pagePath, $layout);

		if ($data === null) {
			throw new MetadataNotAvailableException($meta);
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllMetadata($lang = null, $pagePath = null, $layout = null) {
		if (!\function_exists('getMeta')) {
			throw new CDEFeatureNotSupportedException('getBreadcrumb');
		}

		return \getMeta(null, $lang, $pagePath, $layout);
	}
}
