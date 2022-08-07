<?php

namespace inzh\elasticsearch\dump\cli;

use Exception;
use ReflectionClass;
use inzh\elasticsearch\dump\HighLevelDump;
use inzh\elasticsearch\dump\HighLevelRestore;

/**
 * HighLevelProcessLauncher
 * Tool for simple launch cli action.
 *
 * @author Jean-Raffi Nazareth <jr-nazareth@inzh.fr>
 * @copyright Copyright © 2011-2022 [InZH] Studio.
 */
class HighLevelProcessLauncher
{
    /**
     * @param string $action Action 'dump' or 'restore'
     * @param array $argv Array of options, cli format
     */
    public static function execute($action, $argv = null)
    {
        $options = new \stdClass;
        $options->buffer = 1000;
        $options->gzip = false;
        $options->output = "php://stdout";
        $options->input = "php://stdin";
        $options->host = "localhost";
        $options->port = 9200;

        for ($i = 1; $i < count($argv); $i++) {
            $c = $argv[$i];
            $v = isset($argv[$i + 1]) ? $argv[$i + 1] : null;
            switch ($c) {
                case "--gzip":
                    $options->gzip = true;
                    break;
                case "-b":
                case "--buffer":
                    $options->buffer = intval($v);
                    break;
                case "-o":
                case "--output":
                    $options->output = $v;
                    break;
                case "-i":
                case "--input":
                    $options->input = $v;
                    break;
                case "--es-host":
                    $options->host = $v;
                    break;
                case "--es-port":
                    $options->port = intval($v);
                    break;
            }
        }

        try {
            // Configure Elasticsearch client
            $hosts = [
                $options->host . ":" . $options->port
            ];

            if (class_exists("\Elasticsearch\ClientBuilder")) {
                $rc = new ReflectionClass("\Elasticsearch\ClientBuilder");
                $m = $rc->getMethod('create');
                $builder = $m->invoke(null);
                $c = $builder->setHosts($hosts)->build();
            } elseif (!isset($c) && class_exists("\Elasticsearch\Client")) {
                $rc = new ReflectionClass("\Elasticsearch\Client");
                $c = $rc->newInstanceArgs([["hosts" => $hosts]]);
            } else {
                throw new Exception("Missing valid Elasticsearch client");
            }

            // Launch action
            switch ($action) {
                default:
                    echo "[Failed] -> No valid action. Please read help.\n";
                    break;

                case "dump":
                    $e = new HighLevelDump($c, $options);
                    $e->dump();
                    break;
                case "restore":
                    $e = new HighLevelRestore($c, $options);
                    $e->restore();
                    break;
            }
        } catch (Exception $ex) {
            echo "[Failed] -> " . $ex->getMessage() . "\n";
        }
    }
}
