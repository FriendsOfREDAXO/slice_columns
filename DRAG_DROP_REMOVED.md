# Drag & Drop Funktionalität entfernt

## Überblick
Die Drag-and-Drop-Funktionalität wurde aus dem slice_columns Addon entfernt. Slices können nicht mehr per Drag-and-Drop neu sortiert werden.

# Drag & Drop Funktionalität entfernt

## Überblick
Die Drag-and-Drop-Funktionalität wurde aus dem slice_columns Addon entfernt. Slices können nicht mehr per Drag-and-Drop neu sortiert werden.

## Entfernte Dateien:
- `assets/sortable.min.js` - SortableJS-Bibliothek
- `assets/section_functions.js` - Separate Section-API (in columns_static.js integriert)
- `lib/api_helper.php` - Unnötige Config-API

## Geänderte Dateien:

### 1. `assets/columns_static.js` (umbenannt von columns_sortablejs.js)
- **SortableJS-Initialisierung:** Komplett entfernt
- **CSS-Klassen:** `dragdrop` durch `slice-column` ersetzt
- **Drag-Handler:** Entfernt
- **Log-Meldung:** Angepasst auf "initialising columns without drag & drop"
- **Section-Funktionalität:** Integriert und von section_functions.js übernommen

### 2. `boot.php`
- **SortableJS-Bibliothek:** Komplett entfernt
- **JavaScript-Datei:** Auf `columns_static.js` aktualisiert

### 3. `lib/columns.php`
- **HTML-Struktur:** `<li class="dragdrop">` durch `<div class="slice-column">` ersetzt
- **Drag-Handler:** Entfernt
- **CSS-Klassen:** Angepasst für statische Darstellung

### 4. `lib/sorter.php`
- **updateorder-Funktion:** Vollständig deaktiviert
- **Alle Drag & Drop bezogenen Codes:** Entfernt

### 5. `assets/columns.css`
- **Komplett bereinigt:** Alle Drag & Drop-Styles entfernt
- **Optimiert:** Nur noch notwendige Styles für statische Spalten

## Was funktioniert noch:

✅ **Spaltenbreite ändern:** Buttons zum Vergrößern/Verkleinern der Slices  
✅ **Section-Funktionalität:** Slices zu Sections hinzufügen/entfernen  
✅ **Frontend-Rendering:** Korrekte Darstellung im Frontend  
✅ **Konfiguration:** Alle Einstellungen bleiben erhalten  

## Was nicht mehr funktioniert:

❌ **Drag & Drop:** Slices können nicht mehr gezogen werden  
❌ **Reihenfolge ändern:** Slices müssen über REDAXO-Standard-Funktionen sortiert werden  
❌ **SortableJS-Features:** Alle sortable-spezifischen Funktionen sind deaktiviert  

## Wiederherstellung

Die Drag-and-Drop-Funktionalität kann nicht mehr einfach wiederhergestellt werden, da die entsprechenden Dateien und Codes vollständig entfernt wurden. Für eine Wiederherstellung wären folgende Schritte notwendig:

1. SortableJS-Bibliothek wieder hinzufügen
2. Komplette Neuimplementierung der Drag & Drop-Logik
3. HTML-Struktur auf `<li class="dragdrop">` zurücksetzen
4. CSS-Styles für Drag & Drop wieder hinzufügen
5. API-Funktionen für Reihenfolge-Updates wieder implementieren

**Empfehlung:** Verwenden Sie ein Git-Backup oder eine frühere Version des Addons für die Wiederherstellung.

## Verbleibende Assets:
- `columns_static.js` - Enthält nur Spaltenbreite-Funktionen und Section-Management
- `columns.css` - Bereinigt, nur statische Styles
- `uikit_columns.scss` - Unverändert für UIKit-Styling

## Datum der Änderung
11. August 2025
