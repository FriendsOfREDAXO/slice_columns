<?php

class rex_api_slice_columns_helper extends rex_api_function
{

    protected $published = true;

    public function execute()
    {

        $function = rex_request('function', 'string', '');

        if ($function == 'get_config') {
            $addon = rex_addon::get('slice_columns');
            
            $number_columns = $addon->getConfig('number_columns');
            $min_width_column = $addon->getConfig('min_width_column');

            echo json_encode(['number_columns' => $number_columns, 'min_width_column' => $min_width_column]);
            exit;
        }
    }
}
