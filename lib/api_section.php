<?php

class rex_api_section extends rex_api_function
{
    protected $published = false;  // Aufruf nur aus dem Backend

    public function execute()
    {
        $function = rex_request('function', 'string', '');
        $sliceId = rex_request('slice_id', 'int', 0);
        $articleId = rex_request('article_id', 'int', 0);
        $clangId = rex_request('clang', 'int', 1);
        $sectionId = rex_request('section_id', 'int', 0);
        
        if ($function === 'get_sections') {
            // Hole alle Sections für diesen Artikel
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT DISTINCT section_id FROM ' . rex::getTablePrefix() . 'article_slice 
                            WHERE article_id = :article_id AND clang_id = :clang_id AND section_id IS NOT NULL AND section_id > 0', 
                          ['article_id' => $articleId, 'clang_id' => $clangId]);
            
            $sections = [];
            for ($i = 0; $i < $sql->getRows(); $i++) {
                $sections[$sql->getValue('section_id')] = 'Section ' . $sql->getValue('section_id');
                $sql->next();
            }
            
            echo json_encode($sections);
            exit;
        }
        elseif ($function === 'add_to_section') {
            // Wenn keine section_id übergeben wurde, erstelle eine neue
            if ($sectionId === 0) {
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT MAX(section_id) as max_section FROM ' . rex::getTablePrefix() . 'article_slice');
                $sectionId = (int)$sql->getValue('max_section') + 1;
            }
            
            // Füge den Slice zur Section hinzu
            $sql = rex_sql::factory();
            $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article_slice SET section_id = :section_id WHERE id = :id', 
                ['section_id' => $sectionId, 'id' => $sliceId]);
            
            // Cache löschen
            rex_article_cache::delete($articleId, $clangId);
            
            if (rex_plugin::get('structure','history')->isAvailable()) {
                rex_article_slice_history::makeSnapshot($articleId, $clangId, 'slice_columns_section_add');
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Slice zur Section hinzugefügt', 'section_id' => $sectionId]);
            exit;
        } 
        elseif ($function === 'remove_from_section') {
            // Slice aus Section entfernen
            $sql = rex_sql::factory();
            $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article_slice SET section_id = NULL WHERE id = :id',
                ['id' => $sliceId]);
            
            // Cache löschen
            rex_article_cache::delete($articleId, $clangId);
            
            if (rex_plugin::get('structure','history')->isAvailable()) {
                rex_article_slice_history::makeSnapshot($articleId, $clangId, 'slice_columns_section_remove');
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Slice aus Section entfernt']);
            exit;
        }
        elseif ($function === 'get_section_settings') {
            $sectionId = rex_request('section_id', 'int', 0);
            
            $addon = rex_addon::get('slice_columns');
            $sectionSettings = $addon->getConfig('section_settings', []);
            
            $settings = isset($sectionSettings[$sectionId]) ? $sectionSettings[$sectionId] : [];
            
            echo json_encode($settings);
            exit;
        }
        elseif ($function === 'save_section_settings') {
            $sectionId = rex_request('section_id', 'int', 0);
            $sectionClass = rex_request('section_class', 'string', '');
            $sectionBackground = rex_request('section_background', 'string', '');
            
            $addon = rex_addon::get('slice_columns');
            $sectionSettings = $addon->getConfig('section_settings', []);
            
            $sectionSettings[$sectionId] = [
                'class' => $sectionClass,
                'background' => $sectionBackground
            ];
            
            $addon->setConfig('section_settings', $sectionSettings);
            
            echo json_encode(['status' => 'success', 'message' => 'Section-Einstellungen gespeichert']);
            exit;
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Unbekannte Funktion']);
        exit;
    }
}
