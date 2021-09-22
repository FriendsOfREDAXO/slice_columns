<?php

class rex_api_slice_columns_helper extends rex_api_function
{

    protected $published = true;

    public function execute()
    {

        $function = rex_request('function', 'string', '');

        if ($function == 'get_number_columns') {
            $addon = rex_addon::get('slice_columns');
            $number_columns = $addon->getConfig('slice_columns_number_columns');

            echo json_encode(['number_columns' => $number_columns]);
            exit;
        }
    }
}
