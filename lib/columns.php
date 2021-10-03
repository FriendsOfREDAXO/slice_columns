<?php

class Columns
{

    public static function addButtonm(rex_extension_point $ep, array $btn)
    {
        $items = (array) $ep->getSubject();
        $items[] = $btn;
        #dump($items);
        $ep->setSubject($items);
    }

    public static function addButtons(rex_extension_point $ep)
    {
        $addon = rex_addon::get('slice_columns');

        // Module ausschließen	
        $modules = [];
        $modules = explode("|", $addon->getConfig('modules'));
        if (in_array($ep->getModuleId(), $modules) || !rex::getUser()->hasPerm('slice_columns[edit]') || false === rex::getUser()->getComplexPerm('modules')->hasPerm($ep->getModuleId())) {
            return;
        }

        $expand = rex_addon::get('slice_columns')->getAssetsUrl('outline_expand_black_24dp.png');
        $compress = rex_addon::get('slice_columns')->getAssetsUrl('outline_compress_black_24dp.png');

        $ep->addAdditionalActions([
            'smallerButton' => [
                'label' => '',
                'icon' => 'fa fa-lg fa-compress slice_columns_icon ',
                'attributes' => [
                    "class" => ['btn btn-default btn_smaller']
                ]
            ]
        ]);

        $ep->addAdditionalActions([
            'widerButton' => [
                'label' => '',
				'icon' => 'fa fa-lg fa-expand slice_columns_icon ',
                'attributes' => [
                    "class" => ['btn btn-default btn_wider']
                ]
            ]
        ]);
    }

    public static function show($ep)
    {
        $subject = $ep->getSubject();

        $attributes = [];

        if (rex::isBackend()) {
            if (!preg_match('/<form/', $subject)) {

                $addon = rex_addon::get('slice_columns');
                $number_columns = $addon->getConfig('number_columns');

                $sql = rex_sql::factory();
                $res = $sql->setQuery('select slice_size from rex_article_slice where id = :id', ['id' => $ep->getParam('slice_id')]);

                $width = $res->getValue('slice_size');

                if ($width == '') {
                    $width = $number_columns;
                }

                $css_width = 100 * ($width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);


                $handler = '
                <span class="fa fa-arrows slice_columns_handler">handle</span>
                ';
                // sortablejs
                $subject = '<li class="dragdrop" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul>' . $subject . '</ul></li>';
            }
        } else {
        }

        return $subject;
    }

    public static function frontend($ep)
    {
        $subject = $ep->getSubject();
        $addon = rex_addon::get('slice_columns');
        // Module ausschließen	
        $modules = [];
        $modules = explode("|", $addon->getConfig('modules'));
        #dump($ep);

        $definitions = $addon->getConfig('definitions');
        $definitions = json_decode($definitions, true);  
	$size = static::getSize($ep->getParam('slice_id'));    
	    
        if (in_array($ep->getParam('module_id'), $modules) || !$definitions[$size]) {
            return $subject;
        }

        if ($size === '') {
            $size = $addon->getConfig('number_columns');
        }

        if (!rex_request('rex_history_date') ) {
            $subject =  "\n" .
                "echo '<div class=\"" . $definitions[$size] . "\">'; // column wrapper" .
                "\n\n" .
                $subject .
                "\n" .
                "echo '</div>'; // column wrapper" .
                "\n";
        } else {
            $subject = '<div class="' . $definitions[$size] . '">' . $subject . '</div>';
        }

        return $subject;
    }

    private static function getSize($sliceID)
    {
        $sql = rex_sql::factory();
        if (rex_request('rex_history_date')) {
            $res = $sql->setQuery('select slice_size from rex_article_slice_history where id = :id', ['id' => $sliceID]);
        } else {
            $res = $sql->setQuery('select slice_size from rex_article_slice where id = :id', ['id' => $sliceID]);
        }
        $width = $res->getValue('slice_size');

        return $width;
    }
}
