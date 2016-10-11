<?php

namespace ECL\Command\Elasticsearch;

/**
 * Elasticsearch Builder
 * Initializes Elasticsearch Commands
 */
class Builder {
    /**
     * @var array[] Mapping of ES sources, keyed on the name of the source.
     * Each entry has the following values:
     * host - The source host
     * index_host - The lookup table host
     * index - The source index
     * type - The source type
     * date_based - Whether the source index is date based.
     * date_field - Field to apply date ranges to.
     */
    protected $sources = [];
    /** @var array[] A list of default settings to merge in. */
    protected $settings = [];

    /**
     * Apply a new search
     */
    public function setSources(array $sources) {
        $this->sources = $sources;
    }

    public function setSettings(array $settings) {
        $this->settings = $settings;
    }

    public function build($source, array $query=[], $agg=null, array $settings=[]) {
        return new \ECL\Command\Elasticsearch(
            $query, $agg,
            array_merge(\ECL\Util::get($this->sources, $source, []), $this->settings, $settings)
        );
    }
}
