<?php

$addon = rex_addon::get('slice_columns');

$form = rex_config_form::factory($addon->name);

$field = $form->addSelectField('modules', null, ['class' => 'form-control']);
$field->setAttribute('multiple', 'multiple');
$field->setAttribute('class', 'form-control');
$field->setLabel($addon->i18n('modules'));
$select = $field->getSelect();
$select->setSize(5);
$mSql = rex_sql::factory();
        foreach ($mSql->getArray('SELECT id, name FROM ' . rex::getTablePrefix() . 'module ORDER BY name') as $m) {
            $select->addOption(rex_i18n::translate((string) $m['name']), (int) $m['id']);
}
$field->setNotice($addon->i18n('modules_notice'));

$field = $form->addTextField('number_columns');
$field->setLabel('Anzahl Spalten');
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice('Wie viele Spalten unterstützt das Frontend Framework?');
$field->getValidator()->add('type', 'Muss eine Zahl sein', 'int');

$field = $form->addTextField('number_steps');
$field->setLabel('Schritte pro Click');
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice('Hier definiert man die Spaltenanzahl die je Click erweitert werden soll');
$field->getValidator()->add('type', 'Muss eine Zahl sein', 'int');



$field = $form->addTextField('min_width_column');
$field->setLabel('Spalten-Minimum');
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice('Kleiner als dieser Wert darf eine Spalte nicht werden.');
$field->getValidator()->add('type', 'Muss eine Zahl sein', 'int');


$field = $form->addTextAreaField('definitions');
$field->setAttribute('class', 'codemirror form-control');
$field->setAttribute('data-codemirror-mode', 'json');
$field->setLabel('Mappings für Breiten zu CSS-Klassen');
$field->setNotice('');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Einstellungen", false);
$fragment->setVar('body', $form->get(), false);

echo $fragment->parse('core/page/section.php');
