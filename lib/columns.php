<?php

class Columns
{
    // Bestehende Methoden beibehalten...

    public static function addButtons(rex_extension_point $ep)
    {
        $addon = rex_addon::get('slice_columns');

        // Module ausschließen	
        $modules = [];
        $modules = explode("|", $addon->getConfig('modules',''));
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
        
        // Section-Buttons hinzufügen
        $ep->addAdditionalActions([
            'addToSectionButton' => [
                'label' => '',
                'icon' => 'fa fa-lg fa-object-group',
                'attributes' => [
                    "class" => ['btn btn-default btn_add_to_section'],
                    "data-slice-id" => $ep->getSliceId()
                ]
            ]
        ]);
    }

    public static function show($ep)
    {
        $subject = $ep->getSubject();

        $attributes = [];

        if (rex::isBackend()) {
            if (!preg_match('/id="REX_FORM"/', $subject)) {

                $addon = rex_addon::get('slice_columns');
                $number_columns = $addon->getConfig('number_columns');

                $sql = rex_sql::factory();
                $res = $sql->setQuery('select slice_size, section_id from rex_article_slice where id = :id', ['id' => $ep->getParam('slice_id')]);

                $width = $res->getValue('slice_size');
                $sectionId = $res->getValue('section_id') ?? 0;

                if ($width == '') {
                    $width = $number_columns;
                }

                $css_width = 100 * ($width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);


                $handler = '
                <span class="fa fa-arrows slice_columns_handler">handle</span>
                ';
                
                // Wenn der Slice Teil einer Section ist, füge die Section-Klasse hinzu
                $sectionClass = $sectionId > 0 ? ' in-section section-' . $sectionId : '';
                
                // sortablejs
                $subject = '<li class="dragdrop' . $sectionClass . '" style="width:' . $css_width . '" data-width="' . $width . '" data-section-id="' . $sectionId . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul>' . $subject . '</ul></li>';
            }
        } else {
            // Frontend-Rendering anpassen, um Sections zu unterstützen
        }

        return $subject;
    }
    
    // Weitere bestehende Methoden...
    
    public static function frontend($ep)
    {
        $subject = $ep->getSubject();
        $addon = rex_addon::get('slice_columns');
        // Module ausschließen	
        $modules = [];
        $modules = explode("|", $addon->getConfig('modules',''));
        #dump($ep);

        if (in_array($ep->getParam('module_id'), $modules)) {
            return $subject;
        }

        $size = static::getSize($ep->getParam('slice_id'));

        if ($size == '') {
            $addon = rex_addon::get('slice_columns');
            $size = $addon->getConfig('number_columns');
        }

        $addon = rex_addon::get('slice_columns');
        $definitions = $addon->getConfig('definitions');
        $definitions = json_decode($definitions, true);
        
        // Hole die section_id des aktuellen Slices
        $sliceId = $ep->getParam('slice_id');
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $sliceId]);
        $sectionId = $sql->getValue('section_id');

        // Wenn der Slice Teil einer Section ist, prüfe, ob es der erste in der Section ist
        if ($sectionId) {
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id ORDER BY priority LIMIT 1', ['section_id' => $sectionId]);
            $firstSliceInSection = $sql->getValue('id');
            
            if ($sliceId == $firstSliceInSection) {
                // Wenn es der erste Slice in der Section ist, beginne eine Section
                if (rex_request('rex_history_date') || rex_request('rex_version') ) {
                    $subject = '<div class="slice-section section-' . $sectionId . '"><div class="' . $definitions[$size] . '">' . $subject . '</div>';
                } else {
                    $subject =  "\n" .
                        "echo '<div class=\"slice-section section-" . $sectionId . "\">'; // section wrapper" .
                        "\n" .
                        "echo '<div class=\"" . $definitions[$size] . "\">'; // column wrapper" .
                        "\n\n" .
                        $subject .
                        "\n" .
                        "echo '</div>'; // column wrapper" .
                        "\n";
                }
            } else {
                // Normaler Slice innerhalb einer Section
                if (rex_request('rex_history_date') || rex_request('rex_version') ) {
                    $subject = '<div class="' . $definitions[$size] . '">' . $subject . '</div>';
                } else {
                    $subject =  "\n" .
                        "echo '<div class=\"" . $definitions[$size] . "\">'; // column wrapper" .
                        "\n\n" .
                        $subject .
                        "\n" .
                        "echo '</div>'; // column wrapper" .
                        "\n";
                }
            }
            
            // Prüfe, ob es der letzte Slice in der Section ist
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id ORDER BY priority DESC LIMIT 1', ['section_id' => $sectionId]);
            $lastSliceInSection = $sql->getValue('id');
            
            if ($sliceId == $lastSliceInSection) {
                // Wenn es der letzte Slice in der Section ist, schließe die Section
                if (rex_request('rex_history_date') || rex_request('rex_version') ) {
                    $subject .= '</div><!-- end of section -->';
                } else {
                    $subject .= "\necho '</div>'; // end of section wrapper\n";
                }
            }
        } else {
            // Normaler Slice ohne Section
            if (rex_request('rex_history_date') || rex_request('rex_version') ) {
                $subject = '<div class="' . $definitions[$size] . '">' . $subject . '</div>';
            } else {
                $subject =  "\n" .
                    "echo '<div class=\"" . $definitions[$size] . "\">'; // column wrapper" .
                    "\n\n" .
                    $subject .
                    "\n" .
                    "echo '</div>'; // column wrapper" .
                    "\n";
            }
        }

        return $subject;
    }
}
