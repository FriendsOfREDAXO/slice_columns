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
        
        if ($function === 'add_to_section') {
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
        
        echo json_encode(['status' => 'error', 'message' => 'Unbekannte Funktion']);
        exit;
    }
}
