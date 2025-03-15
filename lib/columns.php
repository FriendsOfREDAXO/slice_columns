<?php

class Columns
{
    // Neue Eigenschaften für Section Management
    private static $sectionOpen = false;
    private static $currentSectionId = null;
    private static $allowedModulesInSection = [];

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
        
        // Prüfen ob es sich um einen Section-Slice handelt
        $isSection = self::isSection($ep->getSliceId());
        
        // Breiten-Buttons nur anzeigen, wenn es kein Section-Slice ist
        if (!$isSection) {
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
    }

    // Prüft, ob ein Slice ein Section-Slice ist
    private static function isSection($sliceId)
    {
        if (!$sliceId) return false;
        
        // Abfrage, ob der Slice ein Section-Modul verwendet
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT m.id, m.name FROM ' . rex::getTable('article_slice') . ' AS s 
                      LEFT JOIN ' . rex::getTable('module') . ' AS m ON s.module_id = m.id 
                      WHERE s.id = :id', ['id' => $sliceId]);
        
        if ($sql->getRows() > 0) {
            $moduleName = $sql->getValue('m.name');
            // Prüfen Sie, ob der Name des Moduls auf "section" endet oder beginnt
            // Oder verwenden Sie eine andere Kennzeichnung für Section-Module
            return (strpos(strtolower($moduleName), 'section_') === 0);
        }
        
        return false;
    }

    public static function show($ep)
    {
        $subject = $ep->getSubject();
        $sliceId = $ep->getParam('slice_id');

        $attributes = [];

        if (rex::isBackend()) {
            if (!preg_match('/id="REX_FORM"/', $subject)) {

                $addon = rex_addon::get('slice_columns');
                $number_columns = $addon->getConfig('number_columns');

                $sql = rex_sql::factory();
                $res = $sql->setQuery('SELECT slice_size, module_id FROM rex_article_slice WHERE id = :id', ['id' => $sliceId]);

                $width = $res->getValue('slice_size');
                $moduleId = $res->getValue('module_id');

                if ($width == '') {
                    $width = $number_columns;
                }

                $css_width = 100 * ($width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);

                // Prüfen, ob es sich um einen Section-Slice handelt
                $isSection = self::isSection($sliceId);
                $sectionClass = $isSection ? 'section-slice' : '';

                // Wenn es ein Section-Slice ist, Section öffnen
                if ($isSection) {
                    self::$sectionOpen = true;
                    self::$currentSectionId = $sliceId;
                    
                    // Erlaubte Module für diese Section auslesen
                    self::loadAllowedModules($sliceId);
                    
                    // Section-Start-Markup hinzufügen
                    $subject = '<li class="dragdrop section-container" data-section-id="' . $sliceId . '" data-width="' . $width . '" data-slice-id="' . $sliceId . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul class="section-content">' . $subject;
                } 
                // Wenn der Slice nicht in einer Section ist oder die Section endet, normales Markup verwenden
                else {
                    $subject = '<li class="dragdrop ' . $sectionClass . '" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $sliceId . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '"><ul>' . $subject . '</ul></li>';
                }
                
                // Section schließen, wenn der nächste Slice ein Section-Slice ist oder es der letzte Slice im Artikel ist
                if (self::shouldCloseSection($sliceId, $ep->getParam('article_id'), $ep->getParam('clang'))) {
                    $subject .= '</ul></li>';
                    self::$sectionOpen = false;
                    self::$currentSectionId = null;
                }
            }
        } else {
        }

        return $subject;
    }
    
    // Lädt die erlaubten Module für eine Section
    private static function loadAllowedModules($sectionSliceId)
    {
        // Hier die Logik, um die erlaubten Module aus dem Section-Slice zu laden
        // Beispiel: Aus einem REX_VALUE des Section-Slices
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('article_slice') . ' WHERE id = :id', ['id' => $sectionSliceId]);
        
        if ($sql->getRows() > 0) {
            // Beispiel: Erlaubte Module aus REX_VALUE[3] als kommaseparierte Liste
            $allowedModulesString = $sql->getValue('value3');
            if ($allowedModulesString) {
                self::$allowedModulesInSection = explode(',', $allowedModulesString);
            } else {
                // Standardmäßig alle Module erlauben, wenn nichts definiert ist
                self::$allowedModulesInSection = [];
            }
        }
    }
    
    // Prüft, ob eine Section geschlossen werden sollte
    private static function shouldCloseSection($currentSliceId, $articleId, $clang)
    {
        if (!self::$sectionOpen) {
            return false;
        }
        
        // Nächsten Slice finden
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, module_id FROM ' . rex::getTable('article_slice') . ' 
                      WHERE article_id = :article_id AND clang_id = :clang 
                      AND priority > (SELECT priority FROM ' . rex::getTable('article_slice') . ' WHERE id = :id) 
                      ORDER BY priority ASC LIMIT 1', 
                      ['article_id' => $articleId, 'clang' => $clang, 'id' => $currentSliceId]);
        
        // Section schließen, wenn es keinen nachfolgenden Slice gibt oder der nächste Slice ein Section-Slice ist
        if ($sql->getRows() == 0) {
            return true;
        }
        
        $nextSliceId = $sql->getValue('id');
        return self::isSection($nextSliceId);
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

        // Prüfen, ob es sich um einen Section-Slice handelt
        $isSection = self::isSection($ep->getParam('slice_id'));

        if (rex_request('rex_history_date') || rex_request('rex_version') ) {
            if ($isSection) {
                // Für Section-Slices einfach den Inhalt zurückgeben
                $subject = '<div class="' . $definitions[$size] . '">' . $subject . '</div>';
            } else {
                $subject = '<div class="' . $definitions[$size] . '">' . $subject . '</div>';
            }
        } 
        else {
            if ($isSection) {
                // Für Section-Slices
                $subject = "\n" .
                    $subject .
                    "\n";
            } else {
                // Für reguläre Slices
                $subject =  "\n" .
                    "echo '<div class=\"" . $definitions[$size] . "\">'; // column wrapper" .
                    "\n\n" .
                    $subject .
                    "\n" .
                    "echo '</div>'; // column wrapper" .
                    "\n";
            }
            
            // Am Ende des Artikels Section schließen
            if (self::shouldCloseSection($ep->getParam('slice_id'), $ep->getParam('article_id'), $ep->getParam('clang'))) {
                $subject .= "\n" . "echo '</div></section>'; // section end" . "\n";
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
