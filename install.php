<?php

rex_sql_table::get(rex::getTablePrefix() . 'article_slice')
    ->ensureColumn(new rex_sql_column('slice_size', 'varchar(255)'))
    ->ensure();
