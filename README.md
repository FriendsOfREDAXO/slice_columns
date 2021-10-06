# REDAXO-AddOn: SliceColumns
Das REDAXO-AddOn erlaubt die Anordnung der Slices in Spalten und das Verschieben von Blöcken per Drag & Drop. 

![Screenshot](https://github.com/FriendsOfREDAXO/slice_columns/blob/assets/screenshot.png)
Screenshot mit AddOn Backend Tools 

## Features

- Blöcke vergrößern / verkleinern
- Mapping der Breiten zu eigenen CSS oder passend zu CSS-Frameworks
- Ausschluss von Modulen 
- Ausschluss von Templates 
- Drag & Drop für Blöcke
- Shift+Breiter = 100% 

## CSS Mapping 

Die Spalten können mit dem eigenen CSS oder Framework gemappt werden. 
Ein Beispiel dazu steht direkt nach Installation für Bootstrap bereit. 

**Wichtig**
Es sollten alle Spalten definiert sein, die am Ende verwendet werden. 
Vor allem aber die kleinste und die größte Breite, die durch die Definition möglich sind. 


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

## Credits
Ursprüngliche Idee von: Thomas Göllner](https://github.com/tgoellner). 

https://github.com/FriendsOfREDAXO/bloecks/tree/previous_alpha/plugins/columns

**Lead:** Andreas Lenhardt https://github.com/andileni

**Testing and further development:** Thomas Skerbis https://github.com/skerbis
