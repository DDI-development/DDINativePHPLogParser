<?php

if (file_exists('config.php')) {
    require_once('config.php');
}

if (file_exists('bootstrap/bootstrap.php')) {
    require_once('bootstrap/bootstrap.php');
}


class Parser
{
    private $_request_params = array();
    private $params = array();
    private $type = null;
    private $upload_contents = array();
    private $path_to_file;


    public function __construct($params)
    {
        $this->_request_params = new Request();
        if (is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = array();
        }
    }

    /**
     * parse each line to pieces;
     * @param $line
     */
    protected function parseLine($line)
    {
        // 10.1.1.150 - - [29/September/2011:14:21:49 -0400] "GET /info/ HTTP/1.1" 200 9955 "http://www.domain.com/download/" "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1"
        // 192.168.1.1 - - [16/Feb/2014:06:44:00 +0000] "GET /images/icon.png HTTP/1.1" 200 1331 "http://www.example.com/index.html" "Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53"
        $pattern = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")$/';
        preg_match($pattern, $line, $matches);
        return $matches;
    }

    /**
     * Log file handler
     * @return array
     */
    private function parse()
    {
        $ok = false;
        $data = [];
        $message = $this->params['generic_message'];

        $total_hits = 0;
        $total_unique = 0;

        $batch = [
            'unique' => array()
        ];
        if (!is_file($this->path_to_file)) {
            $message = 'file unavailable';
        } else {
            $file_handler = fopen($this->path_to_file, "r");
            if ($file_handler) {
                while (($line = fgets($file_handler)) !== false) {
                    $hit = $this->parseLine($line);
                    // split by time
                    $date_str = $hit[4];
                    if (!$date_str) {
                        continue;
                    }
                    // $date_str = DateTime::createFromFormat('d/M/Y', $hit[4]);
                    $daily = $batch[$date_str];

                    // hits per day
                    if (isset($daily['hit'])) {
                        $daily['hit'] += 1;
                    } else {
                        $daily['hit'] = 1;
                    }

                    // unique per day
                    if (!isset($daily['unique'][$hit[1]])) {
                        $daily['unique'][$hit[1]] = 1;
                    } else {
                        $daily['unique'][$hit[1]] += 1;
                    }
                    $daily['total_unique'] = count($daily['unique']);

                    $batch[$date_str] = $daily;
                    $total_hits += 1;

                    // total unique hits
                    if (!isset($batch['unique'][$hit[1]])) {
                        $batch['unique'][$hit[1]] = 1;
                    } else {
                        $batch['unique'][$hit[1]] += 1;
                    }
                }
            }
            $total_unique = count($batch['unique']);
            unset($batch['unique']);
            $ok = true;
            $message = $this->params['parsed_successfully_message'];
            fclose($file_handler);
        }
        return array(
            'ok' => $ok,
            'data' => array(
                'batch' => $batch,
                'total_hits' => $total_hits,
                'total_unique' => $total_unique,
                'average_hits' => floor($total_hits / count($batch)),
                'average_unique_hits' => floor($total_unique / count($batch))
            ),
            'message' => $message
        );
    }

    /**
     * Main dispatcher
     */
    public function run()
    {
        $display = new Display();
        $upload_path = $this->params['upload_path'];

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $dir_handler = opendir($upload_path);
        while (false !== ($entry = readdir($dir_handler))) {
            array_push($this->upload_contents, $entry);
        }
        $display->data['uploaded'] = $this->upload_contents;

        switch ($this->_request_params->type) {
            case 'POST':
                if (empty($this->_request_params->_files)) {
                    echo $display->showDefault();
                }
                $tmp_name = $this->_request_params->_files['file']['tmp_name'];
                $filename = uniqid() . '_' . $this->_request_params->_files['file']['name'];
                $this->path_to_file = $upload_path . '/' . $filename;
                if (!move_uploaded_file($tmp_name, $this->path_to_file)) {
                    $display->setMessage('upload failed');
                    echo $display->showDefault();
                } else {
                    $display->setMessage('all ok');
                    $data = $this->parse();
                    $display->setData($data);
                    $display->setMessage($data['message']);
                    echo $display->showParsed();
                }

                break;
            case 'GET':
            default:
                echo $display->showDefault();
                break;
        }
    }
}

$parser = new Parser($config);
$parser->run();
