<?php




if (rex::isBackend()) {
    $addon = rex_addon::get('slice_columns');

    // rex_view::addCssFile($addon->getAssetsUrl('gridstack.min.css'));
    rex_view::addCssFile($addon->getAssetsUrl('columns.css'));

    rex_view::addJsFile($addon->getAssetsUrl('sortable.min.js'));
    // rex_view::addJsFile($addon->getAssetsUrl('gridstack-h5.js'));
    rex_view::addJsFile($addon->getAssetsUrl('columns_sortablejs.js'));
    // rex_view::addJsFile($addon->getAssetsUrl('columns_gridstack.js'));

    // add buttons to slice menu
    rex_extension::register('SLICE_MENU', ['columns', 'addButtons']);

    rex_extension::register('SLICE_SHOW', array('columns', 'show'));
} else {
    // rex_extension::register('ART_CONTENT', array('columns', 'frontend'));
    // rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', array('columns', 'frontend'));
    // rex_extension::register('STRUCTURE_CONTENT_AFTER_SLICES', array('columns', 'frontend'));
    // rex_extension::register('SLICE_OUTPUT', array('columns', 'frontend'));
    rex_extension::register('SLICE_SHOW', array('columns', 'frontend'), rex_extension::LATE);

}
