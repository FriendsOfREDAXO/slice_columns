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
        $article_id = $ep->getParam('article_id');
        $clang = $ep->getParam('clang');
        $slice_id = $ep->getParam('slice_id');

        // Prüfen, ob der Slice bereits in einer Section ist
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $slice_id]);
        $current_section_id = $sql->getValue('section_id');

        if ($current_section_id) {
            // Button zum Entfernen aus der Section
            $ep->addAdditionalActions([
                'removeFromSectionButton' => [
                    'label' => 'Aus Abschnitt entfernen',
                    'icon' => 'fa fa-lg fa-sign-out',
                    'attributes' => [
                        "class" => ['btn btn-default remove-from-section'],
                        "data-section-id" => $current_section_id,
                        "data-slice-id" => $slice_id,
                        "onclick" => "removeFromSection(this)"
                    ]
                ]
            ]);
        } else {
            // Button zum Erstellen einer neuen Section
            $ep->addAdditionalActions([
                'createSectionButton' => [
                    'label' => 'Neuen Abschnitt erstellen',
                    'icon' => 'fa fa-lg fa-object-group',
                    'attributes' => [
                        "class" => ['btn btn-default create-section'],
                        "data-article-id" => $article_id,
                        "data-clang-id" => $clang,
                        "data-slice-id" => $slice_id,
                        "onclick" => "createSection(this)"
                    ]
                ]
            ]);
            
            // Vorhandene Sections anzeigen, falls vorhanden
            $sections = SliceColumns_Sections::getSectionsForArticle($article_id, $clang);
            if (!empty($sections)) {
                $ep->addAdditionalActions([
                    'addToSectionDropdown' => [
                        'label' => 'Zu Abschnitt hinzufügen',
                        'icon' => 'fa fa-lg fa-plus-square',
                        'attributes' => [
                            "class" => ['btn btn-default add-to-section-dropdown'],
                            "data-toggle" => "dropdown"
                        ]
                    ]
                ]);
                
                // Dropdown mit vorhandenen Sections
                foreach ($sections as $section) {
                    $ep->addAdditionalActions([
                        'addToSection_' . $section['id'] => [
                            'label' => $section['section_name'],
                            'icon' => 'fa fa-lg fa-folder-open',
                            'attributes' => [
                                "class" => ['dropdown-item add-to-section'],
                                "data-section-id" => $section['id'],
                                "data-slice-id" => $slice_id,
                                "onclick" => "addToSection(this)"
                            ]
                        ]
                    ]);
                }
            }
        }
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
                $res = $sql->setQuery('select slice_size from rex_article_slice where id = :id', ['id' => $ep->getParam('slice_id')]);

                $width = $res->getValue('slice_size');

                if ($width == '') {
                    $width = $number_columns;
                }

                $css_width = 100 * ($width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);

                // Hole section_id des aktuellen Slices
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
                $section_id = $sql->getValue('section_id');

                $section_class = '';
                if ($section_id) {
                    $section_class = ' section-member section-' . $section_id;
                    
                    // Prüfen, ob es der erste Slice in dieser Section ist
                    $sql->setQuery('SELECT MIN(priority) as first_prio FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id', 
                        ['section_id' => $section_id]);
                    $first_prio = $sql->getValue('first_prio');
                    
                    $sql->setQuery('SELECT priority FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
                    $current_prio = $sql->getValue('priority');
                    
                    if ($current_prio == $first_prio) {
                        $section_class .= ' section-first';
                    }
                    
                    // Prüfen, ob es der letzte Slice in dieser Section ist
                    $sql->setQuery('SELECT MAX(priority) as last_prio FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id', 
                        ['section_id' => $section_id]);
                    $last_prio = $sql->getValue('last_prio');
                    
                    if ($current_prio == $last_prio) {
                        $section_class .= ' section-last';
                    }
                }

                $handler = '
                <span class="fa fa-arrows slice_columns_handler">handle</span>
                ';
                // sortablejs
                $subject = '<li class="dragdrop' . $section_class . '" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul>' . $subject . '</ul></li>';
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

        // Section Handling
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
        $current_section_id = $sql->getValue('section_id');

        if ($current_section_id) {
            // Prüfen, ob es der erste Slice in dieser Section ist
            $sql->setQuery('SELECT MIN(priority) as first_prio FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id', 
                ['section_id' => $current_section_id]);
            $first_prio = $sql->getValue('first_prio');
            
            $sql->setQuery('SELECT priority FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
            $current_prio = $sql->getValue('priority');
            
            if ($current_prio == $first_prio) {
                // Hole Section-Einstellungen
                $sql->setQuery('SELECT section_settings FROM ' . rex::getTablePrefix() . 'slice_sections WHERE id = :id', 
                    ['id' => $current_section_id]);
                $settings = json_decode($sql->getValue('section_settings'), true);
                
                // Erstelle Section-Start
                $style = '';
                if (!empty($settings['background'])) {
                    $style .= "background: {$settings['background']};";
                }
                if (!empty($settings['padding'])) {
                    $style .= "padding: {$settings['padding']};";
                }
                if (!empty($settings['margin'])) {
                    $style .= "margin: {$settings['margin']};";
                }
                
                if (rex_request('rex_history_date') || rex_request('rex_version')) {
                    $subject = '<div class="slice-section" style="' . $style . '">' . $subject;
                } else {
                    $subject = "\n" .
                        "echo '<div class=\"slice-section\" style=\"{$style}\">'; // section wrapper start" .
                        "\n\n" .
                        $subject;
                }
            }
            
            // Prüfen, ob es der letzte Slice in dieser Section ist
            $sql->setQuery('SELECT MAX(priority) as last_prio FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id', 
                ['section_id' => $current_section_id]);
            $last_prio = $sql->getValue('last_prio');
            
            if ($current_prio == $last_prio) {
                // Erstelle Section-Ende
                if (rex_request('rex_history_date') || rex_request('rex_version')) {
                    $subject .= '</div>';
                } else {
                    $subject .= "\n" .
                        "echo '</div>'; // section wrapper end" .
                        "\n";
                }
            }
        } else {
            // Normales Slice ohne Section
            if (rex_request('rex_history_date') || rex_request('rex_version')) {
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
