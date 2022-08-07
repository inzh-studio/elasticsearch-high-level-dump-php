<?php

namespace inzh\elasticsearch\dump;

/**
 * HighLevelProcess abstraction for Dump/Restore action.
 *
 * @author Jean-Raffi Nazareth <jr-nazareth@inzh.fr>
 * @copyright Copyright © 2011-2022 [InZH] Studio.
 */
abstract class HighLevelProcess
{
    protected $c;
    protected $options;

    /**
     * @param Elasticsearch\Client $c Instance of Elasticsearch Client
     * @param \stdClass $options Options of this process
     */
    public function __construct($c, $options)
    {
        $this->c = $c;
        $this->options = $options;
    }

    abstract protected function onProgress($cpt, $time, $message = null);
}
