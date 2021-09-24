<?php

// rex_sql_table::get(rex::getTable('pagestats_dump'))
//     ->ensureColumn(new rex_sql_column('browser', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('os', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('browsertype', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('brand', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('model', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('date', 'date'))
//     ->ensureColumn(new rex_sql_column('hour', 'int'))
//     ->ensureColumn(new rex_sql_column('weekday', 'int'))
//     ->ensure();

rex_sql_table::get(rex::getTablePrefix() . 'article_slice')
    ->ensureColumn(new rex_sql_column('slice_size', 'varchar(255)'))
    ->ensure();
