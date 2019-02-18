<?php

namespace Ixolit\CDE\Context;


use Ixolit\CDE\CDEInit;
use Ixolit\CDE\Controller\ControllerLogic;
use Ixolit\CDE\Exceptions\InvalidInterfacedObjectException;
use Ixolit\CDE\Exceptions\InvalidOperationException;
use Ixolit\CDE\Exceptions\InvalidValueException;
use Ixolit\CDE\Exceptions\KVSKeyNotFoundException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Exceptions\PageContextAlreadySetException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
use Ixolit\CDE\Interfaces\ControllerLogicInterface;
use Ixolit\CDE\Interfaces\FilesystemAPI;
use Ixolit\CDE\Interfaces\GeoLookupAPI;
use Ixolit\CDE\Interfaces\KVSAPI;
use Ixolit\CDE\Interfaces\MetaAPI;
use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResourceAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;
use Ixolit\CDE\Interfaces\SitemapRenderer as SitemapRendererInterface;
use Ixolit\CDE\PSR7\Uri;
use Ixolit\CDE\SitemapRenderer;
use Ixolit\CDE\WorkingObjects\Layout;
use Ixolit\CDE\WorkingObjects\VersionInfo;
use Psr\Http\Message\UriInterface;

/**
 * Context instance to be initialized once, providing utilities related to pages requests
 *
 * @package Ixolit\CDE\Context
 */
class Page {

	const KVS_KEY_APP_CFG = 'app.cfg';
	const APP_CFG_KEY_ENV = 'env';
	const APP_CFG_VAL_ENV_PROD = 'production';
	const APP_CFG_VAL_ENV_DEVL = 'development';
	const APP_CFG_KEY_HTTPS = 'https';

	/** @var static */
	private static $instance;

    /** @var array */
	private $config;

	/** @var RequestAPI */
	private $requestAPI;

	/** @var ResponseAPI */
	private $responseAPI;

	/** @var ResourceAPI */
	private $resourceAPI;

	/** @var FilesystemAPI */
	private $filesystemAPI;

	/** @var PagesAPI */
	private $pagesAPI;

	/** @var MetaAPI */
	private $metaAPI;

	/** @var GeoLookupAPI */
	private $geoLookupApi;

	/** @var KVSAPI */
	private $kvsAPI;

	/** @var ControllerLogicInterface */
	private $controllerLogic;

	/** @var SitemapRendererInterface */
	private $sitemapRenderer;

	/** @var PageTemporaryStorage */
	private $temporaryStorage;

	/** @var string */
	private $temporaryStorageDomain = null;

	/** @var VersionInfo */
	private $versionInfo;

	/** @var string */
	private $url;

	/** @var string */
	private $scheme;

	/** @var string */
	private $vhost;

	/** @var string */
	private $language;

	/** @var Layout */
	private $layout;

	/** @var string */
	private $path;

	/** @var array */
	private $query;

	/** @var array */
	private $request;

	/** @var string[] */
	private $languages;

	protected function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return static
	 *
	 * @throws PageContextAlreadySetException
	 */
	public static function get() {

		if (!isset(self::$instance)) {
			self::set(new static());
		}

		return self::$instance;
	}

	/**
	 * @param static $instance
	 *
	 * @throws PageContextAlreadySetException
	 */
	protected static function set($instance) {

		if (isset(self::$instance)) {
			throw new PageContextAlreadySetException('Page context already set');
		}

		self::$instance = $instance;
	}

    /**
     * @return void
     * @deprecated call various methods as needed in project specific context
     */
    public static function start() {
	    self::set(new self());
	    self::get()
            ->prepare()
            ->execute();
    }

    /**
     * @return $this
     * @deprecated call various methods as needed in project specific context
     */
    protected function prepare() {
        if ($this->getConfigEnforceHttps()) {
            $this->doEnforceHttps();
        }

        return $this;
    }

    /**
     * @return void
     * @deprecated call various methods as needed in project specific context
     */
    protected function execute() {
        $this->getControllerLogic()->execute();
    }

	/**
	 * @param self $instance
     *
     * @deprecated call various methods as needed in project specific context
	 */
	public static function run($instance) {
		self::set($instance);
		self::get()->doRun();
	}

	/**
	 * @deprecated call various methods as needed in project specific context
	 */
	public function doRun() {
		$this->doPrepare();
		$this->doExecute();
	}

    /**
     * @return void
     *
     * @deprecated call various methods as needed in project specific context
     */
    protected function doPrepare() {
		if ($this->getConfigEnforceHttps()) {
			$this->doEnforceHttps();
		}
	}

    /**
     * @return void
     *
     * @deprecated call various methods as needed in project specific context
     */
    protected function doExecute() {
		// call CDE controller logic
		CDEInit::execute();
	}

	// region factory methods

    /**
     * @param string $class
     * @param string $interface
     *
     * @return mixed
     *
     * @throws InvalidInterfacedObjectException
     */
    protected function getInterfacedObject($class, $interface) {
        if (! \class_exists($class)) {
            throw new InvalidInterfacedObjectException($class . ' doesn\'t exist.');
        }

        $object = new $class();

        if (! ($object instanceof $interface)) {
            throw new InvalidInterfacedObjectException($class . ' is no instance of ' . $interface);
        }

        return $object;
    }

    /**
     * @param string $className
     * @param string $interface
     *
     * @return mixed
     */
    protected function newApiObject($className, $interface) {
        // TODO: move API classes to separate namespace?
        $class = 'Ixolit\\CDE\\' . $className;

        return $this->getInterfacedObject($class, $interface);
    }

	/**
	 * @return RequestAPI
	 */
	protected function newRequestAPI() {
        return $this->newApiObject('CDERequestAPI', 'Ixolit\\CDE\\Interfaces\\RequestAPI');
	}

	/**
	 * @return ResponseAPI
	 */
	protected function newResponseAPI() {
        return $this->newApiObject('CDEResponseAPI', 'Ixolit\\CDE\\Interfaces\\ResponseAPI');
	}

	/**
	 * @return ResourceAPI
	 */
	protected function newResourceAPI() {
        return $this->newApiObject('CDEResourceAPI', 'Ixolit\\CDE\\Interfaces\\ResourceAPI');
	}

	/**
	 * @return FilesystemAPI
	 */
	protected function newFilesystemAPI() {
	    return $this->newApiObject('CDEFilesystemAPI', 'Ixolit\\CDE\\Interfaces\\FilesystemAPI');
	}

	/**
	 * @return PagesAPI
	 */
	protected function newPagesAPI() {
        return $this->newApiObject('CDEPagesAPI', 'Ixolit\\CDE\\Interfaces\\PagesAPI');
	}

	/**
	 * @return MetaAPI
	 */
	protected function newMetaAPI() {
        return $this->newApiObject('CDEMetaAPI', 'Ixolit\\CDE\\Interfaces\\MetaAPI');
	}

	/**
	 * @return GeoLookupAPI
	 */
	protected function newGeoLookupApi() {
        return $this->newApiObject('CDEGeoLookupAPI', 'Ixolit\\CDE\\Interfaces\\GeoLookupAPI');
	}

	/**
	 * @return KVSAPI
	 */
	protected function newKvsAPI() {
	    return $this->newApiObject('CDEKVSAPI', 'Ixolit\\CDE\\Interfaces\\KVSAPI');
	}

	/**
	 * @return ControllerLogic
	 */
	protected function newControllerLogic() {
    	return new ControllerLogic(
    		$this->getRequestAPI(),
			$this->getResponseAPI(),
			$this->getFilesystemAPI(),
			$this
		);
	}


	/**
	 * @return SitemapRendererInterface
	 */
	protected function newSitemapRenderer() {
		return new SitemapRenderer(
			$this->getPagesAPI(),
			$this->getRequestAPI()
		);
	}

	/**
	 * @return PageTemporaryStorage
	 */
	protected function newTemporaryStorage() {
		return new PageTemporaryStorage(
			$this->getTemporaryStorageName(),
			$this->getTemporaryStorageTimeout(),
			$this->getTemporaryStoragePath(),
			$this->getTemporaryStorageDomain()
		);
	}

	// endregion

	/**
	 * Returns CDE version info
	 *
	 * @return VersionInfo
	 */
	public function getCdeVersionInfo() {
		if (!isset($this->versionInfo)) {
			if (\function_exists('getVersion') && $versionInfo = getVersion()) {
				$this->versionInfo = new VersionInfo(
					$versionInfo->major,
					$versionInfo->minor,
					$versionInfo->tag,
					$versionInfo->version
				);
			}
			else {
				$this->versionInfo = new VersionInfo(0, 0, 'unknown', '0.0-unknown');
			}
		}
		return $this->versionInfo;
	}

    /**
     * @param RequestAPI $requestAPI
     *
     * @return $this
     */
    public function setRequestAPI(RequestAPI $requestAPI) {
        $this->requestAPI = $requestAPI;

        return $this;
    }

	/**
	 * @return RequestAPI
	 */
	public function getRequestAPI() {

		if (!isset($this->requestAPI)) {
			$this->requestAPI = $this->newRequestAPI();
		}

		return $this->requestAPI;
	}

	public function setResponseAPI(ResponseAPI $responseAPI) {
	    $this->responseAPI = $responseAPI;

	    return $this;
    }

	/**
	 * @return ResponseAPI
	 */
	public function getResponseAPI() {

		if (!isset($this->responseAPI)) {
			$this->responseAPI = $this->newResponseAPI();
		}

		return $this->responseAPI;
	}

    /**
     * @param ResourceAPI $resourceAPI
     *
     * @return $this
     */
	public function setResourceAPI(ResourceAPI $resourceAPI) {
	    $this->resourceAPI = $resourceAPI;

	    return $this;
    }

	/**
	 * @return ResourceAPI
	 */
	public function getResourceAPI() {

		if (!isset($this->resourceAPI)) {
			$this->resourceAPI = $this->newResourceAPI();
		}

		return $this->resourceAPI;
	}

    /**
     * @param FilesystemAPI $filesystemAPI
     *
     * @return FilesystemAPI
     */
	public function setFilesystemAPI(FilesystemAPI $filesystemAPI) {
	    $this->filesystemAPI = $filesystemAPI;

	    return $this->filesystemAPI;
    }

	/**
	 * @return FilesystemAPI
	 */
	public function getFilesystemAPI() {

		if (!isset($this->filesystemAPI)) {
			$this->filesystemAPI = $this->newFilesystemAPI();
		}

		return $this->filesystemAPI;
	}

    /**
     * @param PagesAPI $pagesAPI
     *
     * @return $this
     */
	public function setPagesAPI(PagesAPI $pagesAPI) {
	    $this->pagesAPI = $pagesAPI;

	    return $this;
    }

	/**
	 * @return PagesAPI
	 */
	public function getPagesAPI() {

		if (!isset($this->pagesAPI)) {
			$this->pagesAPI = $this->newPagesAPI();
		}

		return $this->pagesAPI;
	}

    /**
     * @param MetaAPI $metaAPI
     *
     * @return $this
     */
	public function setMetaAPI(MetaAPI $metaAPI) {
	    $this->metaAPI = $metaAPI;

	    return $this;
    }

	/**
	 * @return MetaAPI
	 */
	public function getMetaAPI() {

		if (!isset($this->metaAPI)) {
			$this->metaAPI = $this->newMetaAPI();
		}

		return $this->metaAPI;
	}

    /**
     * @param GeoLookupAPI $geoLookupAPI
     *
     * @return $this
     */
	public function setGeoLookupAPI(GeoLookupAPI $geoLookupAPI) {
	    $this->geoLookupApi = $geoLookupAPI;

	    return $this;
    }

	/**
	 * @return GeoLookupAPI
	 */
	public function getGeoLookupApi() {
		if (!isset($this->geoLookupApi)) {
			$this->geoLookupApi = $this->newGeoLookupApi();
		}

		return $this->geoLookupApi;
	}

    /**
     * @param KVSAPI $kvsAPI
     *
     * @return $this
     */
	public function setKvsAPI(KVSAPI $kvsAPI) {
	    $this->kvsAPI = $kvsAPI;

	    return $this;
    }

	/**
	 * @return KVSAPI
	 */
	public function getKvsAPI() {
		if (!isset($this->kvsAPI)) {
			$this->kvsAPI = $this->newKvsAPI();
		}

		return $this->kvsAPI;
	}

	/**
	 * @return ControllerLogicInterface
	 */
	public function getControllerLogic() {
		if (!isset($this->controllerLogic)) {
			$this->controllerLogic = $this->newControllerLogic();
		}

		return $this->controllerLogic;
	}

	/**
	 * @return SitemapRendererInterface
	 */
	protected function getSitemapRenderer() {
		if (!isset($this->sitemapRenderer)) {
			$this->sitemapRenderer = $this->newSitemapRenderer();
		}

		return $this->sitemapRenderer;
	}

	/**
	 * @return PageTemporaryStorage
	 */
	public function getTemporaryStorage() {
		if (!isset($this->temporaryStorage)) {
			$this->temporaryStorage = $this->newTemporaryStorage();
		}
		return $this->temporaryStorage;
	}

	protected function getTemporaryStorageName() {
		return PageTemporaryStorage::COOKIE_NAME;
	}

	protected function getTemporaryStorageTimeout() {
		return PageTemporaryStorage::COOKIE_TIMEOUT;
	}

	protected function getTemporaryStoragePath() {
		return null;
	}

	/**
	 * @return string
	 */
	public function getTemporaryStorageDomain() {
		return $this->temporaryStorageDomain;
	}

	/**
	 * @param string $temporaryStorageDomain
	 * @return Page
	 * @throws InvalidOperationException
	 */
	public function setTemporaryStorageDomain($temporaryStorageDomain) {

		if (isset($this->temporaryStorage)) {
			throw new InvalidOperationException('PageTemporaryStorage already set');
		}

		$this->temporaryStorageDomain = $temporaryStorageDomain;
		return $this;
	}

	/**
	 * Returns an URI instance for the given string
	 *
	 * @param string $uri
	 *
	 * @return Uri
	 *
	 * @throws InvalidValueException
	 */
	// TODO: move to \Ixolit\CDE\PSR7\Uri ?
	private static function parseUri($uri) {
		if (\preg_match('~^(?:(.*?):)(?://(?:(.*?)(?:\:(.*?))?@)?(.*?)(?:\:(\d+))?(?=[/?#]|$))?((?:.*?)?)(?:\?(.*?))?(?:\#(.*?))?$~', $uri, $matches)) {
			return new Uri(
				!empty($matches[1]) ? $matches[1] : null,
				!empty($matches[4]) ? $matches[4] : null,
				!empty($matches[5]) ? $matches[5] : null,
				!empty($matches[6]) ? $matches[6] : null,
				!empty($matches[7]) ? $matches[7] : null,
				!empty($matches[8]) ? $matches[8] : null
			);
		}
		throw new InvalidValueException($uri);
	}

	/**
	 * Build a path from it's elements by removing empty ones and redundant slashes
	 *
	 * @param ...
	 *
	 * @return string
	 */
	private static function buildPath() {
		return implode('/', array_filter(array_map(function ($i) {return \trim($i, '/');}, func_get_args())));
	}

	/**
	 * Build a query string from name value pairs or return the passed value as is
	 *
	 * @param mixed $query
	 *
	 * @return string
	 */
	private static function buildQueryString($query) {
		if (\is_array($query)) {
			$params = [];
			foreach ($query as $key => $value) {
				$params[] = \urlencode($key) . '=' . \urlencode($value);
			}
			return \implode('&', $params);
		}
		return $query;
	}

	/**
	 * Returns a valid language for the given one, defaults to the request's language
	 *
	 * @param string|null $lang
	 *
	 * @return string
	 */
	private function getValidLanguage($lang = null) {
		if (!empty($lang)) {
			foreach ($this->getLanguages() as $item) {
				if (\strtolower($item) === \strtolower($lang)) {
					return $item;
				}
			}
		}
		return $this->getLanguage();
	}

	/**
	 * Load environment from CDE's key value store (KVS)
	 */
	protected function loadEnvironment() {
		try {
			$_ENV = array_merge($_ENV, $this->getKvsAPI()->get('cde.php.env'));
		}
		catch (KVSKeyNotFoundException $e) {
			// ignore
		}
	}

	/**
	 * Get configuration (key value pairs)
	 *
	 * @return array
	 */
	protected function getConfig() {

		// try to load from CDE's key value store (KVS)
		if (!isset($this->config)) {
			try {
				$this->config = $this->getKvsAPI()->get(self::KVS_KEY_APP_CFG);
			}
			catch (KVSKeyNotFoundException $e) {
				$this->config = [];
			}
		}

		return $this->config;
	}

	/**
	 * Get configuration value
	 *
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public function getConfigValue($name, $default = null) {
		$config = $this->getConfig();
		return isset($config[$name]) ? $config[$name] : $default;
	}

	/**
	 * Returns true if HTTPS is enforced
	 *
	 * @return mixed|null
	 */
	public function getConfigEnforceHttps() {
		return $this->getConfigValue(self::APP_CFG_KEY_HTTPS, true);
	}

	/**
	 * Returns the application environment
	 *
	 * @return mixed
	 */
	public function getAppEnv() {
		return $this->getConfigValue(self::APP_CFG_KEY_ENV, self::APP_CFG_VAL_ENV_PROD);
	}

	/**
	 * Returns true in development environment
	 *
	 * @return bool
	 */
	public function getDevEnv() {
		return ($this->getAppEnv() === self::APP_CFG_VAL_ENV_DEVL);
	}

	/**
	 * Returns true in preview sessions
	 *
	 * @return bool
	 */
	public function getPreview() {
		if ($this->getCdeVersionInfo()->getMajor() < 4) {
			return ($this->getPagesAPI()->getPreviewInfo() != null);
		}
		return $this->getPagesAPI()->isPreview();
	}

	/**
	 * Returns the request's url
	 *
	 * @return string
	 */
	public function getUrl() {
		if (!isset($this->url)) {
			$this->url = $this->getRequestAPI()->getPageLink();
		}
		return $this->url;
	}

	/**
	 * Returns the request's url scheme
	 *
	 * @return string
	 */
	public function getScheme() {
		if (!isset($this->scheme)) {
			$this->scheme = $this->getRequestAPI()->getScheme();
		}
		return $this->scheme;
	}

	/**
	 * Returns the request's virtual host name
	 *
	 * @return string
	 */
	public function getVhost() {
		if (!isset($this->vhost)) {
			$this->vhost = $this->getRequestAPI()->getVhost();
		}
		return $this->vhost;
	}

	/**
	 * Returns the request's language code
	 *
	 * @return string
	 */
	public function getLanguage() {
		if (!isset($this->language)) {
			$this->language = $this->getRequestAPI()->getLanguage();
		}
		return $this->language;
	}

	/**
	 * Returns the request's layout data
	 *
	 * @return Layout
	 */
	public function getLayout() {
		if (!isset($this->layout)) {
			$this->layout = $this->getRequestAPI()->getLayout();
		}
		return $this->layout;
	}

	/**
	 * Returns the request's path
	 *
	 * @return string
	 */
	public function getPath() {
		if (!isset($this->path)) {
			$this->path = $this->getRequestAPI()->getPagePath();
		}
		return $this->path;
	}

	public function getFullPath() {
		return '/' . $this->getLanguage() . $this->getPath();
	}

	/**
	 * @return array
	 */
	public function getQuery() {
		// TODO: refactor to return query parameters only as soon as CDE supports it ...
		if (!isset($this->query)) {
			$this->query = $this->getRequestAPI()->getRequestParameters();
		}
		return $this->query;
	}

	/**
	 * @return string
	 */
	public function getQueryString() {
		return self::buildQueryString($this->getQuery());
	}

	/**
	 * @return array
	 */
	public function getRequestParameters() {
		if (!isset($this->request)) {
			$this->request = $this->getRequestAPI()->getRequestParameters();
		}
		return $this->request;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public function getRequestParameter($name, $default = null) {
		$request = $this->getRequestParameters();
		return isset($request[$name]) ? $request[$name] : $default;
	}

	/**
	 * Returns the languages supported by the current host
	 *
	 * @return string[]
	 */
	public function getLanguages() {
		if (!isset($this->languages)) {
			$this->languages = $this->getPagesAPI()->getLanguages();
		}
		return $this->languages;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public function getTemporaryVariable($name, $default = null) {
		$value = $this->getTemporaryStorage()->getVariable($name);
		if ($value === null){
			return $default;
		}
		return $value;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function setTemporaryVariable($name, $value) {
		$this->getTemporaryStorage()->setVariable($name, $value);
		return $this;
	}

	/**
	 * Returns the meta data value for the given name, language, page and layout
	 *
	 * @param string $name
	 * @param string|null $default
	 * @param string|null $lang
	 * @param string|null $page
	 * @param string|null $layout
	 *
	 * @return null|string
	 */
	public function getMeta($name, $default = null, $lang = null, $page = null, $layout = null) {
		try {
			return $this->getMetaAPI()->getMeta($name, $lang, $page, $layout);
		}
		catch (MetadataNotAvailableException $e) {
			return $default;
		}
	}

	/**
	 * Returns the translation for the given text and language
	 *
	 * @param string $text
	 * @param string|null $lang
	 * @param string|null $default
	 *
	 * @return null|string
	 */
	public function getTranslation($text, $lang = null, $default = null) {
		return $this->getMeta('t-' . $text, isset($default) ? $default : $text, $lang);
	}

	/**
	 * Returns the translation for the given text, most specific keys and language
	 *
	 * @param string $name
	 * @param string[] $keys
	 * @param string|null $lang
	 * @param string|null $default

	 * @return null|string
	 */
	public function getTranslations($name, $keys = [], $lang = null, $default = null) {

		if (\is_array($keys)) {
			$keys = array_filter($keys);
		}
		else {
			$keys = [];
		}

		do {
			$text = \implode('-', array_merge([$name], $keys));
			if (!isset($default)) {
				$default = $text;
			}

			$trans = $this->getTranslation($text, $lang, false);
			if ($trans !== false) {
				return $trans;
			}
		} while (\array_shift($keys) !== null);

		return $default;
	}

	/**
	 * Returns the path for the given page and language, based on the current request
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 *
	 * @return string
	 */
	public function getPagePath($page = null, $lang = null) {
		return '/' . self::buildPath(
			$this->getValidLanguage($lang),
			$page === null ? self::getPath() : $page
		);
	}

	/**
	 * Returns the URL for the given page, language, query, host and scheme, based on the current request
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 *
	 * @return UriInterface
	 */
	public function getPageUri($page = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {
		return $this->getPathUri($this->getPagePath($page, $lang), $query, $host, $scheme, $port);
	}

	/**
	 * Returns the URL for the given path, query, host and scheme, based on the current request
	 *
	 * @param string|null $path
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 *
	 * @return UriInterface
	 */
	public function getPathUri($path = null, $query = null, $host = null, $scheme = null, $port = null) {

		/** @var UriInterface $uri */
		$uri = self::parseUri($this->getUrl());

		$uri = $uri->withPath($path === null ? $this->getFullPath() : $path);

		$uri = $uri->withQuery(self::buildQueryString($query === null ? $this->getQuery() : $query));

		if ($host !== null) {
			$uri = $uri->withHost($host);
		}

		if ($scheme !== null) {
			$uri = $uri->withScheme($scheme);
		}

		if ($port !== null) {
			$uri = $uri->withPort($port);
		}

		// remove scheme if host is missing since we are dealing with hierarchical URLs like HTTP(S) here ...
		if (empty($uri->getHost())) {
			$uri = $uri->withScheme(null);
		}

		return $uri;
	}

	/**
	 * Returns the URL for the given static path
	 *
	 * @param string $path
	 *
	 * @return null|string
	 */
	public function getStaticUrl($path) {
		try {
			return $this->getResourceAPI()->getStaticUrl($path);
		}
		catch (ResourceNotFoundException $e) {
			return null;
		}
	}

	/**
	 * Returns the URL for the given static path prefixed by the request's layout name
	 *
	 * @param string $path
	 *
	 * @return null|string
	 */
	public function getStaticLayoutUrl($path) {
		return $this->getStaticUrl(self::buildPath($this->getLayout()->getName(), $path));
	}

	/**
	 * Returns the content of the current page
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->getPagesAPI()->getContent();
	}

	/**
	 * Sends a redirect response and exits
	 *
	 * @param string $location
	 * @param bool $permanent
	 */
	public function doRedirectTo($location, $permanent = false) {
		$this->getResponseAPI()->redirectTo($location, $permanent);
		exit;
	}

	/**
	 * Sends a redirect response to the URL for the given page, language, query, host and scheme, based on the current
	 * request and exits
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 * @param bool $permanent
	 */
	public function doRedirectToUri($page, $lang = null, $query = null, $host = null, $scheme = null, $port = null, $permanent = false) {
		$this->doRedirectTo((string) $this->getPageUri($page, $lang, $query, $host, $scheme, $port), $permanent);
	}

	/**
	 * Sends a redirect response to the URL for the given page, language and query, based on the current request and
	 * exits
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param bool $permanent
	 */
	public function doRedirectToPage($page, $lang = null, $query = null, $permanent = false) {
		$this->doRedirectToUri($page, $lang, $query, null, null, null, $permanent);
	}

	/**
	 * Compares the current scheme to the given, redirects if different
	 *
	 * @param string $scheme
	 */
	public function doEnforceScheme($scheme) {
		$scheme = strtolower($scheme);
		if (strtolower($this->getScheme()) != $scheme) {
			$this->doRedirectTo($this->getPageUri()->withScheme($scheme));
		}
	}

	/**
	 * Checks for HTTPS, redirects if not
	 */
	public function doEnforceHttps() {
		$this->doEnforceScheme('https');
	}

	// region static shortcuts

	public static function cdeVersionInfo() {
		return self::get()->getCdeVersionInfo();
	}

	/** @see getRequestAPI */
	public static function requestAPI() {
		return self::get()->getRequestAPI();
	}

	/** @see getResponseAPI */
	public static function responseAPI() {
		return self::get()->getResponseAPI();
	}

	/** @see getResourceAPI */
	public static function resourceAPI() {
		return self::get()->getResourceAPI();
	}

	/** @see getFilesystemAPI */
	public static function filesystemAPI() {
		return self::get()->getFilesystemAPI();
	}

	/** @see getPagesAPI */
	public static function pagesAPI() {
		return self::get()->getPagesAPI();
	}

	/** @see getMetaAPI */
	public static function metaAPI() {
		return self::get()->getMetaAPI();
	}

	/** @see getGeoLookupApi */
	public static function geoLookupApi() {
		return self::get()->getGeoLookupApi();
	}

	/** @see getKvsAPI */
	public static function kvsAPI() {
		return self::get()->getKvsAPI();
	}

	/** @see getSitemapRenderer */
	public static function sitemapRenderer() {
		return self::get()->getSitemapRenderer();
	}

	/** @see getAppEnv */
	public static function appEnv() {
		return self::get()->getAppEnv();
	}

	/** @see getDevEnv */
	public static function isDevEnv() {
		return self::get()->getDevEnv();
	}

	/** @see getPreview */
	public static function isPreview() {
		return self::get()->getPreview();
	}

	/** @see getUrl */
	public static function url() {
		return self::get()->getUrl();
	}

	/** @see getScheme */
	public static function scheme() {
		return self::get()->getScheme();
	}

	/** @see getVhost */
	public static function vhost() {
		return self::get()->getVhost();
	}

	/** @see getLanguage */
	public static function language() {
		return self::get()->getLanguage();
	}

	/** @see getLayout */
	public static function layout() {
		return self::get()->getLayout();
	}

	/** @see getPath */
	public static function path() {
		return self::get()->getPath();
	}

	/** @see getFullPath */
	public static function fullPath() {
		return self::get()->getFullPath();
	}

	/** @see getQuery */
	public static function query() {
		return self::get()->getQuery();
	}

	/** @see getQueryString */
	public static function queryString() {
		return self::get()->getQueryString();
	}

	/** @see getRequestParameters */
	public static function requestParameters() {
		return self::get()->getRequestParameters();
	}

	/**
	 * @see getRequestParameter
	 * @param string $name
	 * @param mixed $default
	 * @return null|string
	 */
	public static function requestParameter($name, $default = null) {
		return self::get()->getRequestParameter($name, $default);
	}

	/** @see getLanguages */
	public static function languages() {
		return self::get()->getLanguages();
	}

	/**
	 * @see getTemporaryVariable
	 * @param string $name
	 * @param mixed $default
	 * @return mixed|null
	 */
	public static function temporaryVariable($name, $default = null) {
		return self::get()->getTemporaryVariable($name, $default);
	}

	/**
	 * @see getMeta
	 * @param string $name
	 * @param string|null $default
	 * @param string|null $lang
	 * @param string|null $page
	 * @param string|null $layout
	 * @return null|string
	 */
	public static function meta($name, $default = null, $lang = null, $page = null, $layout = null) {
		return self::get()->getMeta($name, $default, $lang, $page, $layout);
	}

	/**
	 * @see getTranslation
	 * @param string $text
	 * @param string|null $lang
	 * @param string|null $default
	 * @return null|string
	 */
	public static function translation($text, $lang = null, $default = null) {
		return self::get()->getTranslation($text, $lang, $default);
	}

	/**
	 * @see getTranslation
	 * @param string $name
	 * @param string[] $keys
	 * @param string|null $lang
	 * @param string|null $default
	 * @return null|string
	 */
	public static function translations($name, $keys = null, $lang = null, $default = null) {
		return self::get()->getTranslations($name, $keys, $lang, $default);
	}

	/**
	 * @see getPagePath
	 * @param string|null $page
	 * @param string|null $lang
	 * @return string
	 */
	public static function pagePath($page = null, $lang = null) {
		return self::get()->getPagePath($page, $lang);
	}

	/**
	 * @see getPageUri
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 * @return UriInterface
	 */
	public static function pageUri($page = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {
		return self::get()->getPageUri($page, $lang, $query, $host, $scheme, $port);
	}

	/**
	 * @see getPathUri
	 * @param string|null $path
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 * @return UriInterface
	 */
	public static function pathUri($path = null, $query = null, $host = null, $scheme = null, $port = null) {
		return self::get()->getPathUri($path, $query, $host, $scheme, $port);
	}

	/**
	 * @see getStaticUrl
	 * @param string $path
	 * @return null|string
	 */
	public static function staticUrl($path) {
		return self::get()->getStaticUrl($path);
	}

	/**
	 * @see getStaticLayoutUrl
	 * @param string $path
	 * @return null|string
	 */
	public static function staticLayoutUrl($path) {
		return self::get()->getStaticLayoutUrl($path);
	}

	/** @see getContent */
	public static function content() {
		return self::get()->getContent();
	}

	/**
	 * @see doRedirectTo
	 * @param string $location
	 * @param bool $permanent
	 */
	public static function redirectTo($location, $permanent = false) {
		self::get()->doRedirectTo($location, $permanent);
	}

	/**
	 * @see doRedirectToUri
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 * @param bool $permanent
	 */
	public static function redirectToUri($page, $lang = null, $query = null, $host = null, $scheme = null, $port = null, $permanent = false) {
		self::get()->doRedirectToUri($page, $lang, $query, $host, $scheme, $port, $permanent);
	}

	/**
	 * @see doRedirectToPage
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param bool $permanent
	 */
	public static function redirectToPage($page, $lang = null, $query = null, $permanent = false) {
		self::get()->doRedirectToPage($page, $lang, $query, $permanent);
	}

	/**
	 * @see doEnforceScheme
	 * @param string $scheme
	 */
	public static function enforceScheme($scheme) {
		self::get()->doEnforceScheme($scheme);
	}

	/** @see doEnforceHttps */
	public static function enforceHttps() {
		self::get()->doEnforceHttps();
	}

	public static function processEnforceHttps() {
		if (self::get()->getConfigEnforceHttps()) {
			self::get()->doEnforceHttps();
		}
	}

	public static function runController() {
		self::get()->getControllerLogic()->execute();
	}

	// endregion

}