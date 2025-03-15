<?php
// Bestehende Funktionalität beibehalten
rex_sql_table::get(rex::getTablePrefix() . 'article_slice')
    ->ensureColumn(new rex_sql_column('slice_size', 'varchar(255)'))
    ->ensure();

// Neue Spalte für Sections hinzufügen
rex_sql_table::get(rex::getTablePrefix() . 'article_slice')
    ->ensureColumn(new rex_sql_column('section_id', 'int(10)', true))
    ->ensure();
