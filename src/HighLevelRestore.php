<?php

namespace inzh\elasticsearch\dump;

/**
 * HighLevelRestore Elasticsearch Cluster.
 * Class contains all code for restore Elasticsearch cluster
 *
 * @author Jean-Raffi Nazareth <jr-nazareth@inzh.fr>
 * @copyright Copyright © 2011-2022 [InZH] Studio.
 */
class HighLevelRestore extends HighLevelProcess
{
    /**
     * @param Elasticsearch\Client $c Instance of Elasticsearch Client
     * @param \stdClass $options Options of this process
     */
    public function __construct($c, $options)
    {
        parent::__construct($c, $options);
    }

    protected function importData($c, $o, $buffer = 1000)
    {
        $message = "Restore in progress: %s (%s s)\r";

        $es = [];
        $cpt = 0;
        while ($l = fgets($o)) {
            $start = time();
            $e = json_decode($l, true);
            $es[] = $e;
            $cpt++;

            if (count($es) == $buffer) {
                $this->index($c, $es);
                $es = [];
                $d = time() - $start;
                $this->onProgress($cpt, $d, $message);
            }
        }

        if (!empty($es)) {
            $this->index($c, $es);
            $d = time() - $start;
            $this->onProgress($cpt, $d, $message);
        }
    }

    protected function index($c, $es)
    {
        $body = [];
        foreach ($es as $e) {
            $body[] = ["index" => ["_index" => $e["_index"], "_type" => $e["_type"], "_id" => $e["_id"]]];
            $body[] = $e["_source"];
        }
        $req = ["body" => $body];

        $c->bulk($req);
    }

    protected function onProgress($cpt, $time, $message = null)
    {
        if (isset($message)) {
            echo sprintf($message, $cpt, $time);
        }
    }

    /**
     * Launch delete and restore process with constructor options
     *
     * @return void
     */
    public function restore()
    {
        $c = $this->c;

        // Delete if exist
        foreach ($c->indices()->getMapping() as $index => $def) {
            $c->indices()->delete(["index" => $index]);
        }

        // Restore
        $o = $this->options->gzip === true ? gzopen($this->options->input, 'r') : fopen($this->options->input, "r");
        $state = json_decode(fgets($o), true);
        $mapping = json_decode(fgets($o), true);
        $settings = json_decode(fgets($o), true);

        foreach ($mapping as $index => $m) {
            $req = [
                'index' => $index,
                'body' => [
                    'settings' => $settings[$index]["settings"],
                    'mappings' => $m["mappings"]
                ]
            ];
            $c->indices()->create($req);
        }

        $this->importData($c, $o, $this->options->buffer);

        $this->options->gzip === true ? gzclose($o) : fclose($o);
    }
}
