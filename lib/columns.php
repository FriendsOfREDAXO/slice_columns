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
        $ep->addAdditionalActions([
            'addToSectionButton' => [
                'label' => '',
                'icon' => 'fa fa-lg fa-object-group',
                'attributes' => [
                    "class" => ['btn btn-default btn_add_to_section'],
                    "title" => 'Zu Section hinzufügen',
                    "data-slice-id" => $ep->getSliceId()
                ]
            ]
        ]);
        
        // Wenn der Slice bereits in einer Section ist, auch einen Button zum Entfernen anbieten
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getSliceId()]);
        if ($sql->getRows() > 0) {
            $sectionId = $sql->getValue('section_id');
            
            if ($sectionId) {
                $ep->addAdditionalActions([
                    'removeFromSectionButton' => [
                        'label' => '',
                        'icon' => 'fa fa-lg fa-object-ungroup',
                        'attributes' => [
                            "class" => ['btn btn-default btn_remove_from_section'],
                            "title" => 'Aus Section entfernen',
                            "data-slice-id" => $ep->getSliceId(),
                            "data-section-id" => $sectionId
                        ]
                    ]
                ]);
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
                $res = $sql->setQuery('SELECT slice_size, section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);

                $width = $res->getValue('slice_size');
                $sectionId = $res->getValue('section_id');

                if ($width == '') {
                    $width = $number_columns;
                }

                $css_width = 100 * ($width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);
                
                // Wenn der Slice Teil einer Section ist, füge die Section-Klasse hinzu
                $sectionClass = $sectionId > 0 ? ' in-section section-' . $sectionId : '';
                
                // Attribute für die Section
                $sectionAttributes = $sectionId > 0 ? ' data-section-id="' . $sectionId . '"' : '';
                
                // Static column structure without drag & drop functionality
                $subject = '<div class="slice-column' . $sectionClass . '" style="width:' . $css_width . '" data-width="' . $width . '"' . $sectionAttributes . ' data-slice-id="' . $ep->getParam('slice_id') . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '">' . $subject . '</div>';
            }
        } else {
            // Frontend-Rendering wird in frontend() behandelt
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
        
        // Hole Section-Informationen des aktuellen Slices
        $sliceId = $ep->getParam('slice_id');
        $sql = rex_sql::factory();
        if (rex_request('rex_history_date')) {
            $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice_history WHERE id = :id', ['id' => $sliceId]);
        } else {
            $sql->setQuery('SELECT section_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $sliceId]);
        }
        $sectionId = $sql->getValue('section_id');

        // Wenn der Slice Teil einer Section ist
        if ($sectionId) {
            // Prüfe, ob es der erste Slice in der Section ist
            if (rex_request('rex_history_date')) {
                $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice_history WHERE section_id = :section_id AND article_id = :article_id AND clang_id = :clang_id ORDER BY priority LIMIT 1', 
                    ['section_id' => $sectionId, 'article_id' => $ep->getParam('article_id'), 'clang_id' => $ep->getParam('clang')]);
            } else {
                $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id AND article_id = :article_id AND clang_id = :clang_id ORDER BY priority LIMIT 1', 
                    ['section_id' => $sectionId, 'article_id' => $ep->getParam('article_id'), 'clang_id' => $ep->getParam('clang')]);
            }
            $firstSliceInSection = $sql->getValue('id');
            
            // Hole die Section-Einstellungen
            $sectionSettings = static::getSectionSettings($sectionId);
            $sectionClass = !empty($sectionSettings['class']) ? ' ' . $sectionSettings['class'] : '';
            $sectionStyle = !empty($sectionSettings['background']) ? ' style="background-color: ' . $sectionSettings['background'] . ';"' : '';
            
            if ($sliceId == $firstSliceInSection) {
                // Wenn es der erste Slice in der Section ist, beginne eine Section
                if (rex_request('rex_history_date') || rex_request('rex_version')) {
                    $subject = '<div class="slice-section section-' . $sectionId . $sectionClass . '"' . $sectionStyle . '><div class="' . $definitions[$size] . '">' . $subject . '</div>';
                } else {
                    $sectionClassPhp = !empty($sectionSettings['class']) ? ' ' . $sectionSettings['class'] : '';
                    $sectionStylePhp = !empty($sectionSettings['background']) ? ' style=\"background-color: ' . $sectionSettings['background'] . ';\"' : '';
                    
                    $subject =  "\n" .
                        "echo '<div class=\"slice-section section-" . $sectionId . $sectionClassPhp . "\"" . $sectionStylePhp . ">'; // section wrapper" .
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
            
            // Prüfe, ob es der letzte Slice in der Section ist
            if (rex_request('rex_history_date')) {
                $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice_history WHERE section_id = :section_id AND article_id = :article_id AND clang_id = :clang_id ORDER BY priority DESC LIMIT 1', 
                    ['section_id' => $sectionId, 'article_id' => $ep->getParam('article_id'), 'clang_id' => $ep->getParam('clang')]);
            } else {
                $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article_slice WHERE section_id = :section_id AND article_id = :article_id AND clang_id = :clang_id ORDER BY priority DESC LIMIT 1', 
                    ['section_id' => $sectionId, 'article_id' => $ep->getParam('article_id'), 'clang_id' => $ep->getParam('clang')]);
            }
            $lastSliceInSection = $sql->getValue('id');
            
            if ($sliceId == $lastSliceInSection) {
                // Wenn es der letzte Slice in der Section ist, schließe die Section
                if (rex_request('rex_history_date') || rex_request('rex_version')) {
                    $subject .= '</div><!-- end of section -->';
                } else {
                    $subject .= "\necho '</div>'; // end of section wrapper\n";
                }
            }
        } else {
            // Normaler Slice ohne Section
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

    /**
     * Holt die Einstellungen einer Section
     * 
     * @param int $sectionId Die ID der Section
     * @return array Die Section-Einstellungen
     */
    private static function getSectionSettings($sectionId)
    {
        $settings = [];
        
        if (!$sectionId) {
            return $settings;
        }
        
        $addon = rex_addon::get('slice_columns');
        $sectionSettings = $addon->getConfig('section_settings', []);
        
        if (isset($sectionSettings[$sectionId])) {
            $settings = $sectionSettings[$sectionId];
        }
        
        return $settings;
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
