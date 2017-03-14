<?php

namespace Ixolit\CDE\PSR7;

use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Psr\Http\Message\UriInterface;

/**
 * {@inheritdoc}
 */
class Uri implements UriInterface {
	/**
	 * @var string
	 */
	private $userInfo;

	/**
	 * @var string
	 */
	private $scheme;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @var string
	 */
	private $fragment;

	public function __construct($scheme, $host, $port, $path, $query, $fragment) {
		$this->scheme = $scheme;
		$this->host = $host;
		$this->port = $port;
		$this->path = $path;
		$this->query = $query;
		$this->fragment = $fragment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAuthority() {
		$authority = '';
		if ($this->getUserInfo()) {
			$authority .= $this->getUserInfo() . '@';
		}
		$authority .= $this->getHost();
		if (!($this->getScheme() == 'http' && $this->getPort() == 80) &&
			!($this->getScheme() == 'https' && $this->getPort() == 443)) {
			$authority .= ':' . $this->getPort();
		}
		return $authority;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserInfo() {
		return $this->userInfo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withScheme($scheme) {
		return new Uri(
			$scheme,
			$this->getHost(),
			$this->getPort(),
			$this->getPath(),
			$this->getQuery(),
			$this->getFragment()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withUserInfo($user, $password = null) {
		throw new CDEFeatureNotSupportedException('user info in PSR-7 objects');
	}

	/**
	 * {@inheritdoc}
	 */
	public function withHost($host) {
		return new Uri(
			$this->getScheme(),
			$host,
			$this->getPort(),
			$this->getPath(),
			$this->getQuery(),
			$this->getFragment()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPort($port) {
		return new Uri(
			$this->getScheme(),
			$this->getHost(),
			$port,
			$this->getPath(),
			$this->getQuery(),
			$this->getFragment()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPath($path) {
		return new Uri(
			$this->getScheme(),
			$this->getHost(),
			$this->getPort(),
			$path,
			$this->getQuery(),
			$this->getFragment()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withQuery($query) {
		return new Uri(
			$this->getScheme(),
			$this->getHost(),
			$this->getPort(),
			$this->getPath(),
			$query,
			$this->getFragment()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withFragment($fragment) {
		return new Uri(
			$this->getScheme(),
			$this->getHost(),
			$this->getPort(),
			$this->getPath(),
			$this->getQuery(),
			$fragment
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return $this->getScheme() . '://' .
		$this->getAuthority() .
		$this->getPath() .
		($this->getQuery()?'?' . $this->getQuery():'') .
		($this->getFragment()?'#' . $this->getFragment():'');
	}
}