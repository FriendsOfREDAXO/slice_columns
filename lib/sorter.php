<?php

class rex_api_sorter extends rex_api_function
{

    protected $published = true;  // Aufruf aus dem Frontend erlaubt

    public function execute()
    {

        $order = rex_request('order', 'string', '');
        $function = rex_request('function', 'string', '');

        if ($function === 'updatewidth') {
            $slice = rex_request('slice', 'int', 0);
            $width = rex_request('width', 'string', '');

            $sql = rex_sql::factory();
            $sql->setQuery('update rex_article_slice set slice_size = :size where id = :id', ['size' => $width, 'id' => $slice]);

            echo json_encode([$function, $slice, $width]);
            exit;
        }

        if ($function === 'updateorder') {
            $order = rex_request('order', 'string', '');
            $order = json_decode($order);
            array_pop($order);

            $sql = rex_sql::factory();
            foreach ($order as $key => $value) {
                $sql->setQuery('update rex_article_slice set priority = :prio where id = :id', ['prio' => $key, 'id' => $value]);
            }


            // $sql = rex_sql::factory();
            // $sql->setQuery('update rex_article_slice set slice_size = :size where id = :id', ['size' => $width , 'id' => $slice]);

            echo json_encode([$function, $order]);
            exit;
        }

        // if ($order != '') {
        //     $sql = rex_sql::factory();
        //     // $sql->setQuery('SELECT name, id FROM rex_article WHERE parent_id = :pid', ['pid'=>5]);

        //     $log = rex_logger::getPath();
        //     file_put_contents($log, $order . PHP_EOL);
        // }

        echo json_encode($order);
        exit;
    }
}
