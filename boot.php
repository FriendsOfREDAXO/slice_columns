<?php

rex_perm::register('slice_columns[edit]');
if (rex::isBackend() && rex::getUser() && \rex_be_controller::getCurrentPagePart(1) ==='content') {   

    $addon = rex_addon::get('slice_columns');

    rex_view::setJsProperty('slicesteps', (int)$addon->getConfig('number_steps'));
    rex_view::setJsProperty('min_width_column', (int)$addon->getConfig('min_width_column'));
    rex_view::setJsProperty('number_columns', (int)$addon->getConfig('number_columns'));

    // Load assets only on content pages - bloecks handles drag & drop (required dependency)
     switch (\rex_be_controller::getCurrentPagePart(1)) {
         case 'content':
             rex_view::addCssFile($addon->getAssetsUrl('columns.css'));
             rex_view::addJsFile($addon->getAssetsUrl('columns_sortablejs.js'));
         default:
             break;
     }
 
    // templates ausschließen	
    $templates = [];
    $templates = explode("|", $addon->getConfig('templates',''));
      if (rex_article::getCurrent() && in_array(rex_article::getCurrent()->getTemplateId(), $templates)) {
    } else {
        // add buttons to slice menu
        rex_extension::register('SLICE_MENU', ['columns', 'addButtons']);
    }

    // Register with LATE priority to run after bloecks
    rex_extension::register('SLICE_SHOW', array('columns', 'show'), rex_extension::LATE);
} elseif(rex::isFrontend()) {
    // rex_extension::register('ART_CONTENT', array('columns', 'frontend'));
    // rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', array('columns', 'frontend'));
    // rex_extension::register('STRUCTURE_CONTENT_AFTER_SLICES', array('columns', 'frontend'));
    // rex_extension::register('SLICE_OUTPUT', array('columns', 'frontend'));
    rex_extension::register('SLICE_SHOW', array('columns', 'frontend'), rex_extension::LATE);
}

