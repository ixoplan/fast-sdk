<?php

namespace Ixolit\CDE\View;


/**
 * Class OutputBuffer
 *
 * @package Ixolit\CDE\View
 */
class OutputBuffer {

    const OUTPUT_IDENTIFIER_SCRIPTS = 'scripts';
    const OUTPUT_IDENTIFIER_STYLES = 'styles';

    /** @var OutputBuffer|null */
    private static $instance = null;

    /** @var array */
    private $output;

    private function __construct() {
        $this->output = [];
    }

    private function __clone() {

    }

    /**
     * @return OutputBuffer
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $outputIdentifier
     * @param string $output
     *
     * @return $this
     */
    public function appendOutput($outputIdentifier, $output) {
        if (!isset($this->output[$outputIdentifier])) {
            $this->output[$outputIdentifier] = [];
        }

        $this->output[$outputIdentifier][] = $output;

        return $this;
    }

    /**
     * @param string $outputIdentifier
     *
     * @return string
     */
    public function renderOutput($outputIdentifier) {
        return isset($this->output[$outputIdentifier]) ? \implode('\\n', $this->output[$outputIdentifier]) : '';
    }

}