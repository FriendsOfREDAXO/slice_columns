# REDAXO-AddOn: SliceColumns
Das REDAXO-AddOn erlaubt die Anordnung der Slices in Spalten. Drag & Drop wird durch das bloecks-AddOn bereitgestellt (erforderliche Abhängigkeit).

![Screenshot](https://github.com/FriendsOfREDAXO/slice_columns/blob/assets/screenshot.png)
Screenshot mit AddOn Backend Tools 

## Features

- Blöcke vergrößern / verkleinern
- Intelligente Reset-Funktion: Bei maximaler Breite entfernt "größer" den Wrapper
- Mapping der Breiten zu eigenen CSS oder passend zu CSS-Frameworks
- Ausschluss von Modulen 
- Ausschluss von Templates 
- Drag & Drop über bloecks-AddOn (erforderliche Abhängigkeit)
- Shift+Breiter = 100% 

## Voraussetzungen

- REDAXO ^5.12
- **bloecks ^5.0** (wird automatisch als Abhängigkeit installiert)
- PHP ^7.4

Das bloecks-AddOn ist **erforderlich** und übernimmt die Drag & Drop-Funktionalität für Slices. 

## CSS Mapping 

Die Spalten können mit dem eigenen CSS oder Framework gemappt werden. 
Ein Beispiel dazu steht direkt nach Installation für Bootstrap bereit. 

**Wichtig**
Es sollten alle Spalten definiert sein, die am Ende verwendet werden. 
Vor allem aber die kleinste und die größte Breite, die durch die Definition möglich sind. 

### Reset-Funktion

Die Reset-Funktionalität ist intelligent in die normalen Buttons integriert:

- **Bei maximaler Breite**: Der "größer"-Button (↗) wird zum Reset-Button (❌) und entfernt den Wrapper
- **Im Reset-Zustand**: Der "kleiner"-Button führt zurück zur normalen Größe
- **Visueller Indikator**: Icons ändern sich je nach Zustand
- **Frontend**: Kein Wrapper-Div bei Reset-Zustand (Breite = 0)
- **Ausgeschlossene Module**: Erhalten automatisch keinen Wrapper

**Button-Verhalten:**
- **Normal → Maximum**: ↗ (Expand-Icon) 
- **Maximum**: ❌ (Times-Icon) → Reset
- **Reset**: ↗ (Expand-Icon) → zurück zur normalen Größe

Wenn Sie eine Definition `"0": "reset"` in Ihrem CSS-Mapping haben, wird dies für die Wrapper-Entfernung genutzt. 


## Beispiel CSS Mapping für UiKit3.x mit erweiterten Styles

Hier bei 12 Spalten

Ein geeignetes SCSS liegt im Assets-Ordner des AddOns uikit_columns.css

```json
{
   "1":"uk-width-1-12",
   "2":"uk-width-1-6",
   "3":"uk-width-1-4",
   "4":"uk-width-1-3",
   "5":"uk-width-5-12",
   "6":"uk-width-1-2",
   "7":"uk-width-7-12",
   "8":"uk-width-2-3",
   "9":"uk-width-3-4",
   "10":"uk-width-5-6",
   "11":"uk-width-11-12",
   "12":"uk-width-1-1"
}
```

## Support

https://github.com/FriendsOfREDAXO/slice_columns

## Änderungen in Version 1.0.2

- **Drag & Drop entfernt**: Die Drag & Drop-Funktionalität wurde aus slice_columns entfernt
- **bloecks-Abhängigkeit**: bloecks ^5.0 ist jetzt eine erforderliche Abhängigkeit und übernimmt das Drag & Drop
- **Kompatibilität**: Vollständige Kompatibilität mit bloecks für moderne Slice-Verwaltung
- **Fokus auf Spalten**: Das AddOn konzentriert sich jetzt ausschließlich auf die Spalten-Funktionalität

## Credits
Ursprüngliche Idee von: Thomas Göllner](https://github.com/tgoellner). 

https://github.com/FriendsOfREDAXO/bloecks/tree/previous_alpha/plugins/columns

**Lead:** Andreas Lenhardt https://github.com/andileni

**Testing and further development:** Thomas Skerbis https://github.com/skerbis
