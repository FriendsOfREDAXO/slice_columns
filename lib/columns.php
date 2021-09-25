<?php

class Columns
{


    public static function addButtonm(rex_extension_point $ep, array $btn)
    {
        $items = (array) $ep->getSubject();
        $items[] = $btn;
        dump($items);
        $ep->setSubject($items);
    }


    public static function addButtons(rex_extension_point $ep)
    {
	$addon = rex_addon::get('slice_columns');
    
	// Module ausschließen	
	$modules = [];
    $modules = explode("|", $addon->getConfig('modules'));
    if (in_array($ep->getModuleId(), $modules)) {
	return;}
	
	/*	
	// templates ausschließen	
   	$artId = $ep->getArticleId();
	$rexArticle = rex_article::get($artId);
	$templates = [];
	$templates = explode("|", $addon->getConfig('templates'));	
	if (in_array($rexArticle->getTemplateId(), $templates)) {
	return;}	
	*/
		
        $expand = rex_addon::get('slice_columns')->getAssetsUrl('outline_expand_black_24dp.png');
        $compress = rex_addon::get('slice_columns')->getAssetsUrl('outline_compress_black_24dp.png');

        $ep->addAdditionalActions([
            'smallerButton' => [
                'label' => '<img height="22px" src="' . $compress . '">',
                'attributes' => [
                    "class" => ['btn-default btn_smaller slice_columns_no_padding']
                ]
            ]
        ]);

        $ep->addAdditionalActions([
            'widerButton' => [
                'label' => '<img height="22px" src="' . $expand . '">',
                'attributes' => [
                    "class" => ['btn-default btn_wider slice_columns_no_padding']
                ]
            ]
        ]);



        // foreach (['copy', 'cut'] as $type) {
        //     static::addButtonm($ep, [
        //         'hidden_label' => 'HIDDEN LABEL',
        //         // 'url' => rex_url::backendController([
        //         //     'page' => 'content/edit',
        //         //     'article_id' => $ep->getParam('article_id'),
        //         //     'bloecks' => 'cutncopy',
        //         //     'module_id' => $ep->getParam('module_id'),
        //         //     'slice_id' => $ep->getParam('slice_id'),
        //         //     'clang' => $ep->getParam('clang'),
        //         //     'ctype' => $ep->getParam('ctype'),
        //         //     'revision' => 1,
        //         //     'cuc_action' => $type,
        //         // ]),
        //         'attributes' => [
        //             'class' => ['btn-' . $type],
        //             'title' => 'TITLE',
        //             'data-bloecks-cutncopy-iscopied' => 0 && ('edit' === $type) ? 'true' : 'false',
        //             'data-pjax-no-history' => 'true',
        //         ],
        //         'icon' => '',
        //     ]);
        // }
    }

    public static function show($ep)
    {
        $subject = $ep->getSubject();

        $attributes = [];

        if (rex::isBackend()) {
            if (!preg_match('/<form/', $subject)) {
                // $subject = '<li class="rex-slice rex-slice-bloecks-item rex-slice-output"' . (!empty($attributes) ? ' ' . join(' ', $attributes) : '') . '><ul>' . $subject . '</ul></li>';
                // dump($ep);
                // dump($subject);

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
                // $subject = '<li class="dragdrop" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-article-id="' . $ep->getParam('article_id') . '">' . $handler . '<ul>' . $subject . '</ul></li>';
                $subject = '<li class="dragdrop" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul>' . $subject . '</ul></li>';


                // gridstack
                // $subject = '<li class="grid-stack-item" data-id="' . $ep->getParam('slice_id') . '"><ul><div class="grid-stack-item-content">' . $subject . '</div></ul></li>';

                //     $subject .= '<div class="grid-stack">
                //     <div class="grid-stack-item">
                //       <div class="grid-stack-item-content">Item 1</div>
                //     </div>
                //     <div class="grid-stack-item" gs-w="2">
                //       <div class="grid-stack-item-content">Item 2 wider</div>
                //     </div>
                //   </div>';


                // dump($subject);
            }
        } else {

            // $subject = 'HELLOOOO';
            // echo 'HHHHHHHHHHHHHHHHHHHHH';
        }

        return $subject;
    }

    public static function frontend($ep)
    {
        $subject = $ep->getSubject();
        $find = '{{bloecks_columns_css}}';

        $size = static::getSize($ep->getParam('slice_id'));

        if ($size == '') {
			$addon = rex_addon::get('slice_columns');
            $size = $addon->getConfig('number_columns');
        }

        // dump($size);
        // $subject = $ep->getSubject();

        // $subject = '<div class="col-sm-3"><hr><hr><hr>' . $subject . '</div>';

        // return $subject;

        $addon = rex_addon::get('slice_columns');
        $definitions = $addon->getConfig('definitions');
        $definitions = json_decode($definitions, true);


        if (($p = strpos($subject, $find)) !== false) {
            $subject = substr($subject, 0, $p) . substr($subject, $p + strlen($find));
        } else {
            $subject =  "\n" .
                "echo '<div class=\"" . $definitions[$size] . "\">'; // bloecks_columns" .
                "\n\n" .
                $subject .
                "\n" .
                "echo '</div>'; // bloecks_columns wrapper" .
                "\n";
        }
        // dump($subject);
        return $subject;
    }

    private static function getSize($sliceID)
    {
        $sql = rex_sql::factory();
        $res = $sql->setQuery('select slice_size from rex_article_slice where id = :id', ['id' => $sliceID]);

        $width = $res->getValue('slice_size');

        return $width;
    }
}


