# REDAXO-AddOn: SliceColumns

Das REDAXO-AddOn ermöglicht die flexible Darstellung von Slices in responsive Spalten-Layouts. Mit intelligenter Button-Steuerung und nahtloser bloecks-Integration.

![Screenshot](https://github.com/FriendsOfREDAXO/slice_columns/blob/assets/screenshot.png)
Screenshot mit AddOn Backend Tools 

## ✨ Features

- **Responsive Spalten**: Flexible Breitenanpassung für Slices (1-12 Spalten)
- **Intelligente Steuerung**: Dynamische Button-Icons je nach Zustand
- **Smart Reset**: Bei maximaler Breite wird der Wrapper automatisch entfernt
- **CSS-Framework-Support**: Kompatibel mit Bootstrap, UIKit, Foundation etc.
- **Modul-/Template-Filter**: Gezielter Ausschluss bestimmter Module oder Templates
- **bloecks-Integration**: Moderne Drag & Drop-Funktionalität
- **Tastenkürzel**: Shift+Breiter = sofort 100% 

## 🆕 Was ist neu in Version 2.0.0

### Verbesserungen
- **🎯 Intelligente Button-Steuerung**: Icons ändern sich dynamisch je nach Zustand
- **🔄 Smart Reset**: Bei maximaler Breite wird automatisch der Wrapper entfernt  
- **🎨 Bessere UX**: Keine Extra-Buttons mehr, alles über zwei intuitive Buttons
- **🏗️ Backend-Konsistenz**: Reset-Zustand sieht aus wie volle Breite
- **⚡ Optimierte Logik**: Bessere Behandlung von Edge-Cases

### Architektur-Änderungen
- **❌ Drag & Drop entfernt**: Wird jetzt vollständig von bloecks übernommen
- **🔗 bloecks-Abhängigkeit**: bloecks ^5.0 ist jetzt zwingend erforderlich  
- **🎯 Fokus auf Spalten**: AddOn konzentriert sich ausschließlich auf Spalten-Management
- **🧹 Code-Bereinigung**: Entfernung von Legacy-Drag&Drop-Code

### Kompatibilität
- **✅ Vollständig kompatibel** mit bloecks für moderne Slice-Verwaltung
- **✅ Backwards-kompatibel** für bestehende CSS-Konfigurationen
- **✅ Framework-agnostisch** - funktioniert mit allen CSS-Frameworks


## 🔧 Voraussetzungen

- **REDAXO**: ^5.12
- **bloecks**: ^5.0 (erforderliche Abhängigkeit - wird automatisch installiert)
- **PHP**: ^7.4

> **Wichtig**: Das bloecks-AddOn ist **zwingend erforderlich** und übernimmt die Drag & Drop-Funktionalität für Slices.

## 🎮 Bedienung im Backend

### Standard-Buttons
Jeder Slice erhält zwei Buttons zur Breitensteuerung:

- **↙ Schmaler** (Compress): Reduziert die Breite um einen Schritt
- **↗ Breiter** (Expand): Erhöht die Breite um einen Schritt

### Intelligente Button-Logik

Die Button-Icons ändern sich dynamisch je nach aktuellem Zustand:

#### Normal-Modus (1-11 Spalten)
- **↙ Schmaler**: Compress-Icon → Spalte schmaler machen
- **↗ Breiter**: Expand-Icon → Spalte breiter machen

#### Maximum erreicht (12 Spalten) 
- **↙ Schmaler**: Compress-Icon → Spalte schmaler machen
- **❌ Reset**: Times-Icon → **Wrapper komplett entfernen**

#### Reset-Zustand (0 = kein Wrapper)
- **↙ Zurück**: Compress-Icon → Zur maximalen Breite zurückkehren
- **↗ Zurück**: Expand-Icon → Zur Standard-Breite zurückkehren

### Backend-Darstellung
- **Standard (1-11)**: Entsprechende prozentuale Breite
- **Maximum (12)**: Volle Breite (100%)
- **Reset (0)**: Volle Breite (100%) - *sieht aus wie Maximum*

### Frontend-Ausgabe
- **Standard (1-12)**: `<div class="col-sm-X">...</div>`
- **Reset (0)**: **Kein Wrapper** → direkter Slice-Inhalt
- **Ausgeschlossene Module**: **Kein Wrapper** → direkter Slice-Inhalt 

## ⚙️ CSS-Mapping Konfiguration

### Grundprinzip
Die Spalten werden über JSON-Definitionen an CSS-Framework-Klassen gemappt. Standardmäßig ist Bootstrap-Support vorkonfiguriert.

### Standard-Konfiguration (Bootstrap)
```json
{
  "0": "reset",
  "1": "col-sm-1",
  "2": "col-sm-2",
  "3": "col-sm-3",
  "4": "col-sm-4",
  "5": "col-sm-5",
  "6": "col-sm-6",
  "7": "col-sm-7",
  "8": "col-sm-8",
  "9": "col-sm-9",
  "10": "col-sm-10",
  "11": "col-sm-11",
  "12": "col-sm-12"
}
```

### Reset-Funktion aktivieren
```json
{
  "0": "reset",  ← Aktiviert die Reset-Funktionalität
  "1": "col-sm-1",
  ...
}
```

**Alternativ auch möglich:**
- `"0": ""` (leerer String)
- `"0": "no-wrapper"`

### Beispiel: UIKit 3.x
```json
{
  "0": "reset",
  "1": "uk-width-1-12",
  "2": "uk-width-1-6", 
  "3": "uk-width-1-4",
  "4": "uk-width-1-3",
  "5": "uk-width-5-12",
  "6": "uk-width-1-2",
  "7": "uk-width-7-12",
  "8": "uk-width-2-3",
  "9": "uk-width-3-4",
  "10": "uk-width-5-6",
  "11": "uk-width-11-12",
  "12": "uk-width-1-1"
}
```

### Beispiel: Foundation 6
```json
{
  "0": "reset",
  "1": "small-1 columns",
  "2": "small-2 columns",
  "3": "small-3 columns",
  "4": "small-4 columns",
  "5": "small-5 columns",
  "6": "small-6 columns",
  "7": "small-7 columns", 
  "8": "small-8 columns",
  "9": "small-9 columns",
  "10": "small-10 columns",
  "11": "small-11 columns",
  "12": "small-12 columns"
}
```

> **💡 Tipp**: Definieren Sie alle Spalten von 1 bis zur maximalen Anzahl. Die `"0": "reset"` Definition ist optional, aber empfohlen für maximale Flexibilität. 


## 🛠️ Konfigurationsmöglichkeiten

### AddOn-Einstellungen
- **Spalten-Anzahl**: Standard 12 (anpassbar)
- **Schrittweite**: Standard 1 (anpassbar)
- **Minimale Breite**: Standard 1 (anpassbar)
- **Ausgeschlossene Templates**: Komma-getrennte Template-IDs
- **Ausgeschlossene Module**: Komma-getrennte Modul-IDs

### Template-/Modul-Ausschluss
Module oder Templates können vom Spalten-System ausgeschlossen werden:

**Ausgeschlossene Templates:**
- Slices erhalten keine Spalten-Buttons
- Keine Wrapper im Frontend

**Ausgeschlossene Module:**
- Slices erhalten keine Spalten-Buttons  
- Keine Wrapper im Frontend
- Ideal für Hero-Bereiche, Full-Width-Content, etc.

## 🎯 Anwendungsfälle

### Standard Content-Layout
```html
<!-- Backend: 4-8-Spalten Layout -->
<div class="col-sm-4">Sidebar Content</div>
<div class="col-sm-8">Main Content</div>
```

### Hero-Bereiche ohne Wrapper
```html
<!-- Backend: Reset-Button geklickt -->
<!-- Frontend: Kein Wrapper -->
<section class="hero-area">...</section>
```

### Responsive Layouts
```html
<!-- Backend: 6-6-Spalten Layout -->
<div class="col-sm-6">Left Column</div>
<div class="col-sm-6">Right Column</div>
```



## 🆘 Support & Hilfe

- **GitHub Issues**: https://github.com/FriendsOfREDAXO/slice_columns
- **REDAXO Forum**: https://www.redaxo.org/de/forum/
- **Dokumentation**: Diese README + Inline-Hilfe im Backend

## Credits
Ursprüngliche Idee von: Thomas Göllner](https://github.com/tgoellner). 

https://github.com/FriendsOfREDAXO/bloecks/tree/previous_alpha/plugins/columns

**Lead:** Andreas Lenhardt https://github.com/andileni

**Testing and further development:** Thomas Skerbis https://github.com/skerbis
