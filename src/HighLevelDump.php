<?php

namespace inzh\elasticsearch\dump;

/**
 * HighLevelDump Elasticsearch Cluster.
 * Class contains all code for dump Elasticsearch cluster
 *
 * @author Jean-Raffi Nazareth <jr-nazareth@inzh.fr>
 * @copyright Copyright © 2011-2022 [InZH] Studio.
 */
class HighLevelDump extends HighLevelProcess
{
    /**
     * @param Elasticsearch\Client $c Instance of Elasticsearch Client
     * @param \stdClass $options  Options of this process
     */
    public function __construct($c, $options)
    {
        parent::__construct($c, $options);
    }

    protected function extractData($c, $o)
    {
        $message = "Dump in progress: %s (%s s)\r";

        $docs = $c->search([
            "search_type" => "scan",
            "scroll" => "60s",
            "size" => $this->options->buffer,
            "index" => "*",
            "body" => [
                "query" => [
                    "match_all" => []
                ]
            ]
        ]);

        if (!isset($docs['_scroll_id'])) {
            return;
        }

        $sid = $docs['_scroll_id'];
        $cpt = 0;
        while (true) {
            $start = time();

            $re = $c->scroll(
                array(
                    "scroll_id" => $sid,
                    "scroll" => "30s"
                )
            );

            $put = false;
            $ccpt = count($re['hits']['hits']);
            if ($ccpt > 0) {
                foreach ($re['hits']['hits'] as $hit) {
                    $this->write($o, $hit);
                }
                $sid = $re['_scroll_id'];
                $put = true;
            }

            $d = time() - $start;
            $cpt += $ccpt;
            $this->onProgress($cpt, $d, $message);

            if ($put === false) {
                break;
            }
        }
    }

    protected function write($o, $re)
    {
        $str = json_encode($re) . "\n";
        gzwrite($o, $str);
    }

    protected function onProgress($cpt, $time, $message = null)
    {
        $withProgress = $this->options->output !== "php://stdout";
        if ($withProgress && isset($message)) {
            echo sprintf($message, $cpt, $time);
        }
    }

    /**
     * Launch dump process with constructor options
     *
     * @return void
     */
    public function dump()
    {
        $c = $this->c;

        $path = $this->options->output;
        $ext = $this->options->gzip === true ? ".json.gz" : ".json";
        if (is_null($this->options->output)) {
            $path = __DIR__ . DIRECTORY_SEPARATOR . $c->cluster()->state()["cluster_name"] . $ext;
        }

        $o = $this->options->gzip === true ? gzopen($path, 'w') : fopen($path, "w");

        $this->write($o, $c->cluster()->state());
        $this->write($o, $c->indices()->getMapping());
        $this->write($o, $c->indices()->getSettings());
        $this->extractData($c, $o);

        $this->options->gzip === true ? gzclose($o) : fclose($o);
    }
}
