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
            $article_id = rex_request('article', 'int', 0);
            $clang_id = rex_request('clang', 'int', 0);
            $width = rex_request('width', 'string', '');

            $sql = rex_sql::factory();
            $sql->setQuery('update rex_article_slice set slice_size = :size where id = :id', ['size' => $width, 'id' => $slice]);

            rex_article_cache::delete($article_id);

            rex_article_slice_history::makeSnapshot($article_id, $clang_id, 'slice_columns_updatewidth');

            echo json_encode([$function, $slice, $width, 'article_id' => $article_id]);
            exit;
        }

        if ($function === 'updateorder') {
            $article_id = rex_request('article', 'int', 0);
            $clang_id = rex_request('clang', 'int', 0);
            $order = rex_request('order', 'string', '');

            $order = json_decode($order);
            array_pop($order);

            $sql = rex_sql::factory();
            foreach ($order as $key => $value) {
                $sql->setQuery('update rex_article_slice set priority = :prio where id = :id', ['prio' => $key, 'id' => $value]);
            }

            rex_article_slice_history::makeSnapshot($article_id, $clang_id, 'slice_columns_updateorder');

            rex_article_cache::delete($article_id);

            // $sql = rex_sql::factory();
            // $sql->setQuery('update rex_article_slice set slice_size = :size where id = :id', ['size' => $width , 'id' => $slice]);

            echo json_encode([$function, $order, $article_id]);
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
