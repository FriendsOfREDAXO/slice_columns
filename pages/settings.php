<?php

$addon = rex_addon::get('slice_columns');

$form = rex_config_form::factory($addon->name);

$field = $form->addCheckboxField('sidebar_switch');
$field->setLabel($addon->i18n('sidebar_switch'));
$field->addOption($addon->i18n('yes'), '1');

$field = $form->addTextField('number_columns');
$field->setLabel($addon->i18n('number_columns'));
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice($addon->i18n('number_columns_notice'));
$field->getValidator()->add('type', $addon->i18n('val_numbers'), 'int');

$field = $form->addTextField('number_steps');
$field->setLabel($addon->i18n('number_steps'));
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice($addon->i18n('number_steps_notice'));
$field->getValidator()->add('type', $addon->i18n('val_numbers'), 'int');



$field = $form->addTextField('min_width_column');
$field->setLabel($addon->i18n('min_width'));
$field->setAttribute('type', 'number');
$field->setAttribute('required', 'required');
$field->setNotice($addon->i18n('min_width_notice'));
$field->getValidator()->add('type', $addon->i18n('val_numbers'), 'int');


$field = $form->addTextAreaField('definitions');
$field->setAttribute('class', 'codemirror form-control');
$field->setAttribute('data-codemirror-mode', 'json');
$field->setLabel($addon->i18n('definitions'));
$field->setNotice($addon->i18n('definitions_notice'));


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



$field = $form->addSelectField('templates', null, ['class' => 'form-control']);
$field->setAttribute('multiple', 'multiple');
$field->setAttribute('class', 'form-control');
$field->setLabel($addon->i18n('templates'));
$select = $field->getSelect();
$select->setSize(5);

$mSql = rex_sql::factory();
        foreach ($mSql->getArray('SELECT id, name FROM ' . rex::getTablePrefix() . 'template ORDER BY name') as $m) {
            $select->addOption(rex_i18n::translate((string) $m['name']), (int) $m['id']);
		}
$field->setNotice($addon->i18n('templates_notice'));


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Einstellungen", false);
$fragment->setVar('body', $form->get(), false);

echo $fragment->parse('core/page/section.php');


