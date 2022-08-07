# Elasticsearch high level dump

Elasticsearch high level dump is tool for create or restore dump from/to cluster elasticsearch with search/scroll method.

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?logo=php)](https://php.net/)
[![Minimum Elasticsearch Version](https://img.shields.io/badge/elasticsearch-%3E%3D%201.4-yellowgreen?logo=elasticsearch)](https://www.elastic.co/)

## Installation

You can install this tool in your PHP project using composer:

    composer require inzh/elasticsearch-high-level-dump

## Usage

> :warning: **All indexs is read with search/scrool method, and write with bulk method**, use with caution for preserve data integrity.

### Bash:

You can use this tool in cli context.

For dump process :

```bash
./vendor/bin/edump
```

For restore process :

```bash
./vendor/bin/erestore 
```

All options :

1. The `--gzip` option
Use GZip compression for restore or dump.

2. The `-b`, `--buffer` option
Set buffer for read on write, big buffer need more memory: 1000

3. The `-o`, `--output` option
Set output path file or stream, default on standart output: /var/dir/file

4. The `-i`, `--input` option
Set input path file or stream, default on standart input: /var/dir/file

5. The `-es-host` option
Set Elasticsearch service hostname or ip: localhost

6. The `-es-port` option
Set Elasticsearch service port: 9200

Exemple :

```bash
./vendor/bin/edump --gzip -b 1000 -es-host localhost -es-port 9200 > output.json.gz
cat output.json.gz | ./vendor/bin/erestore --gzip -b 1000 -es-host localhost -es-port 9200

./vendor/bin/edump --gzip -b 1000 -es-host localhost -es-port 9200 -o output.json.gz
./vendor/bin/erestore --gzip -b 1000 -es-host localhost -es-port 9200 -i output.json.gz
```

### Development:

You can use directly process class in your code.

For dump process :

```php
use inzh\elasticsearch\dump\HighLevelDump;

$client = $yourInstanceOfClient; // Your elasticsearch client
$options = new \stdClass; // .... See all options in next section

$process = new HighLevelDump($client, $options);
$process->dump();
```

For restore process :

```php
use inzh\elasticsearch\dump\HighLevelRestore;

$client = $yourInstanceOfClient; // Your elasticsearch client
$options = new \stdClass; // .... See all options in next section

$process = new HighLevelRestore($client, $options);
$process->restore();
```

All options :

```php
// All options parameters
$options = new \stdClass;
$options->buffer = 1000;
$options->gzip = false;
$options->output = "php://stdout"; // Stream or filePath
$options->input = "php://stdin"; // Stream or filePath
$options->host = "localhost";
$options->port = 9200;
```
#

[© 2011-2022 [InZH] Studio.](https://www.inzh.fr/)