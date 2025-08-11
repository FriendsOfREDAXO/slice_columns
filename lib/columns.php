<?php

class Columns
{

    public static function addButtonm(rex_extension_point $ep, array $btn)
    {
        $items = (array) $ep->getSubject();
        $items[] = $btn;
        #dump($items);
        $ep->setSubject($items);
    }

    public static function addButtons(rex_extension_point $ep)
    {
        $addon = rex_addon::get('slice_columns');

        // Module ausschließen	
        $modules = [];
        $modules = explode("|", $addon->getConfig('modules',''));
        if (in_array($ep->getModuleId(), $modules) || !rex::getUser()->hasPerm('slice_columns[edit]') || false === rex::getUser()->getComplexPerm('modules')->hasPerm($ep->getModuleId())) {
            return;
        }

        $expand = rex_addon::get('slice_columns')->getAssetsUrl('outline_expand_black_24dp.png');
        $compress = rex_addon::get('slice_columns')->getAssetsUrl('outline_compress_black_24dp.png');

        $ep->addAdditionalActions([
            'smallerButton' => [
                'label' => '',
				'icon' => 'fa fa-lg fa-compress slice_columns_icon ',
                'attributes' => [
                    "class" => ['btn btn-default btn_smaller']
                ]
            ]
        ]);

        $ep->addAdditionalActions([
            'widerButton' => [
                'label' => '',
				'icon' => 'fa fa-lg fa-expand slice_columns_icon ',
                'attributes' => [
                    "class" => ['btn btn-default btn_wider']
                ]
            ]
        ]);
        

    }

    public static function show($ep)
    {
        $subject = $ep->getSubject();

        $attributes = [];

        if (rex::isBackend()) {
            // Prüfe, ob der Subject bereits von bloecks geworfen wurde (bloecks ist Voraussetzung)
            if (strpos($subject, 'bloecks-dragdrop') !== false) {
                // Bloecks hat bereits den Slice geworfen, ändere nur die Breite
                return static::addWidthToBloecksWrapper($ep);
            }
            
            if (!preg_match('/id="REX_FORM"/', $subject)) {

                $addon = rex_addon::get('slice_columns');
                $number_columns = $addon->getConfig('number_columns');

                $sql = rex_sql::factory();
                
                // Einfache Abfrage nur für slice_size
                $res = $sql->setQuery('SELECT slice_size FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
                $width = $res->getValue('slice_size');

                if ($width == '') {
                    $width = $number_columns;
                }

                // Im Backend: Reset-Zustand (0) soll wie der größte Wert aussehen
                $display_width = ($width == 0) ? $number_columns : $width;
                $css_width = 100 * ($display_width / $number_columns) . '%';
                $css_width = str_replace(",", ".", $css_width);

                // HTML-Struktur angepasst für bloecks (div statt li) - ohne Section-Funktionalität
                // data-width behält den echten Wert (auch 0), aber style verwendet display_width
                $subject = '<div class="slice-column" style="width:' . $css_width . '" data-width="' . $width . '" data-slice-id="' . $ep->getParam('slice_id') . '" data-clang-id="' . $ep->getParam('clang') . '" data-article-id="' . $ep->getParam('article_id') . '">' . $subject . '</div>';
            }
        } else {
            // Frontend-Rendering wird in frontend() behandelt
        }

        return $subject;
    }
    
    /**
     * Fügt Breiten-Styles zu bloecks-Wrappern hinzu, ohne sie zu zerstören
     */
    private static function addWidthToBloecksWrapper($ep)
    {
        $subject = $ep->getSubject();
        $addon = rex_addon::get('slice_columns');
        $number_columns = $addon->getConfig('number_columns');

        $sql = rex_sql::factory();
        $res = $sql->setQuery('SELECT slice_size FROM ' . rex::getTablePrefix() . 'article_slice WHERE id = :id', ['id' => $ep->getParam('slice_id')]);
        $width = $res->getValue('slice_size');

        if ($width == '') {
            $width = $number_columns;
        }

        // Im Backend: Reset-Zustand (0) soll wie der größte Wert aussehen
        $display_width = ($width == 0) ? $number_columns : $width;
        $css_width = 100 * ($display_width / $number_columns) . '%';
        $css_width = str_replace(",", ".", $css_width);

        // Füge Breiten-Styles und zusätzliche Daten zu existierendem bloecks-Wrapper hinzu
        // data-width behält den echten Wert (auch 0), aber style verwendet display_width
        $subject = str_replace(
            'class="bloecks-dragdrop"',
            'class="bloecks-dragdrop slice-column" style="width:' . $css_width . '" data-width="' . $width . '"',
            $subject
        );

        return $subject;
    }

    public static function frontend($ep)
    {
        $subject = $ep->getSubject();
        $addon = rex_addon::get('slice_columns');
        // Module ausschließen	
        $modules = [];
        $modulesConfig = $addon->getConfig('modules','');
        if (!empty($modulesConfig)) {
            $modules = explode("|", $modulesConfig);
        }

        // Ausgeschlossene Module erhalten keinen Wrapper
        if (!empty($modules) && in_array($ep->getParam('module_id'), $modules)) {
            return $subject;
        }

        $size = static::getSize($ep->getParam('slice_id'));

        if ($size == '') {
            $addon = rex_addon::get('slice_columns');
            $size = $addon->getConfig('number_columns');
        }

        $addon = rex_addon::get('slice_columns');
        $definitions = $addon->getConfig('definitions');
        $definitions = json_decode($definitions, true);

        // Prüfe auf "0":"reset" oder "0":"" Definition - kein Wrapper
        if (isset($definitions['0']) && ($definitions['0'] === 'reset' || $definitions['0'] === '' || $size == '0')) {
            if ($size == '0') {
                return $subject; // Keine Wrapper für reset/0-Wert
            }
        }

        // Sicherheitscheck: Definition muss existieren
        if (!isset($definitions[$size])) {
            return $subject; // Kein Wrapper wenn Definition fehlt
        }

        $cssClass = $definitions[$size];
        
        // Prüfe ob CSS-Klasse "reset" ist - dann kein Wrapper
        if ($cssClass === 'reset' || $cssClass === '') {
            return $subject;
        }

        // Einfache Ausgabe ohne Section-Logik
        if (rex_request('rex_history_date') || rex_request('rex_version')) {
            $subject = '<div class="' . $cssClass . '">' . $subject . '</div>';
        } else {
            $subject =  "\n" .
                "echo '<div class=\"" . $cssClass . "\">'; // column wrapper" .
                "\n\n" .
                $subject .
                "\n" .
                "echo '</div>'; // column wrapper" .
                "\n";
        }

        return $subject;
    }

    private static function getSize($sliceID)
    {
        $sql = rex_sql::factory();
        if (rex_request('rex_history_date')) {
            $res = $sql->setQuery('select slice_size from rex_article_slice_history where id = :id', ['id' => $sliceID]);
        } else {
            $res = $sql->setQuery('select slice_size from rex_article_slice where id = :id', ['id' => $sliceID]);
        }
        $width = $res->getValue('slice_size');

        return $width;
    }
}
