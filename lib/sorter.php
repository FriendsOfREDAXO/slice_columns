<?php

class rex_api_sorter extends rex_api_function
{

    protected $published = false;  // Aufruf nur aus dem Backend

    public function execute()
    {

        $order = rex_request('order', 'string', '');
        $function = rex_request('function', 'string', '');

        if ($function === 'updatewidth') {
            $slice = rex_request('slice', 'int', 0);
            $article_id = rex_request('article', 'int', 0);
			$article_clang = rex_request('clang', 'int', 1);
            $width = rex_request('width', 'string', '');
            
            $sql = rex_sql::factory();
            $sql->setQuery('update rex_article_slice set slice_size = :size where id = :id', ['size' => $width, 'id' => $slice]);
            
			if (rex_plugin::get('structure','history')->isAvailable()) {
            rex_article_slice_history::makeSnapshot($article_id, $article_clang,'slice_columns_updatewidth');
			}
            rex_article_cache::delete($article_id);
            
            echo json_encode([$function, $slice, $width, 'article_id' => $article_id]);
            exit;
        }

        if ($function === 'updateorder') {
            // Drag & Drop ordering is handled by bloecks addon (required dependency)
            echo json_encode(['status' => 'disabled', 'message' => 'Drag & Drop ordering is handled by bloecks addon (required dependency)']);
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

