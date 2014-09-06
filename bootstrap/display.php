<?php

class Display
{
    public $data = array();
    public $list;
    private $message;

    public function __construct()
    {
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function showParsed()
    {
        $result = '<html>
        <head>
                <title>test</title>
        </head>
        <body>
        <span>' .
            $this->message . "</span><br/>";
        $batch = $this->data['data']['batch'];
        foreach ($batch as $date => $value) {
            $result .= "<div>";
            $result .= sprintf('%s total hits %s unique %s', $date, $value['hit'], $value['total_unique']);
            $result .= "</div><br/>";
        }
        $result .= "<div>";
        $result .= sprintf('total hits: %s, unique: %s, average hits: %s, average unique hits: %s',
            $this->data['data']['total_hits'],
            $this->data['data']['total_unique'],
            $this->data['data']['average_hits'],
            $this->data['data']['average_unique_hits']
        );
        $result .= "</div><br/>";
        $result .= '
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="file">Filename:</label>
                    <input type="file" name="file" id="file"><br>
                    <input type="submit" name="submit" value="Submit">
                </form>
                <h1>
                </h1>
        </body>
        </html>';
        return $result;
    }

    public function showDefault()
    {
        // obviously would be better to use smarty/twig-like structure, but time is running out
        return '<html>
        <head>
                <title>test</title>
        </head>
        <body>' .
            $this->message
            . '<form action="" method="post" enctype="multipart/form-data">
                    <label for="file">Filename:</label>
                    <input type="file" name="file" id="file"><br>
                    <input type="submit" name="submit" value="Submit">
                </form>
                <h1>
                </h1>
        </body>
        </html>';
    }
}
