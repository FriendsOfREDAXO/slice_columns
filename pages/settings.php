<?php

$addon = rex_addon::get('slice_columns');

$form = rex_config_form::factory($addon->name);


$field = $form->addTextField('slice_columns_number_columns');
$field->setLabel('Anzahl Spalten');
$field->setNotice('Wie viele Spalten unterstützt das Frontend Framework?');
$field->getValidator()->add('type', 'Muss eine Zahl sein', 'int');


$field = $form->addTextAreaField('definitions');
$field->setLabel('Mappings für Breiten zu CSS-Klassen');
$field->setNotice('');



$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Einstellungen", false);
$fragment->setVar('body', $form->get(), false);

echo $fragment->parse('core/page/section.php');
