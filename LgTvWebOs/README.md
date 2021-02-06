# Lg Tv Web Os
Das Plex Mediathek Update ließt die Plex Mediatheken aus und speichert diese in einer HTML Box. Mit dem Aktualisierungsbutton kann man dann das Einlesen von neuem Content der entsprechenden Mediatheken starten.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Funktionen](#2-funktionen)

### 1. Funktionsumfang

* Ermöglicht das Auslesen der Lautstärke vom Fernseher und das setzten einer Lautstärke.
* Nachrichten an den Fernseher senden.
* Hinterlegte Apps umschalten.

### 2. Voraussetzungen

- IP-Symcon ab Version 5.5

### 3. Software-Installation

* Über das Module Control folgende URL hinzufügen:
    `https://github.com/Housemann/LgTvWebOs`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" kann das 'Lg Tv Web Os'-Modul mithilfe des Schnellfilters gefunden werden.
    - Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)
__Konfigurationsseite__:

Name           | VariableTyp | Beschreibung
-------------- | ----------- | ---------------------
Ausschalten    | (bool)      | Zum Ausschalten des Fernsehers (Einschalten nur über WOL möglich)
Lautlos        | (bool)      | Stummschalten des Fernsehers
Lautstärke +-  | (bool)      | Lautstärke raus / runter
Lautstärke     | (integer)   | Lautstärke slider
Lg App         | (integer)   | App auswahl zum wechseln der HDMI eingänge oder zum starten einer App (z.B. Netflix)
Play Pause     | (bool)      | Play Pause Button

### 5. Statusvariablen und Profile

Die Statusvariablen werden bei bedarf über die Checkboxen im Modul automatisch angelegt.

### 6. Funktionen

Mit dieser Funktion kann man eine Nachricht an den Fernseher für ein PopUp senden.
```php
LGTV_message($Instance,"Das ist eine Test Nachricht");
```

Mit dieser Funktion kann der Fernseher auf Stumm geschaltet werden.
```php
LGTV_mute($Instance,true);  // Stummschalten
LGTV_mute($Instance,false); // Stummschaltung aufheben 
```

Mit dieser Funktion kann man die Lautstärke rauf und runter stellen.
```php
LGTV_volumeDown($Instance);  // Lautstärke runter
LGTV_volumeUp($Instance);    // Lautstärke raus 
```

Mit dieser Funktion kann man die aktuelle Lautstärke auslesen.
```php
print_r(LGTV_getVolume($Instance)); // Return der aktuellen Lautstärke
```

Mit dieser Funktion setzt man eine Lautstärke.
```php
LGTV_setVolume($Instance,13); // Lautstärke auf z.B. 13 setzen
```

Mit dieser Funktion schaltet man den Fernseher aus.
```php
LGTV_turnOff($Instance);  // Ausschalten
```

Mit dieser Funktion kann man eine Aufnahme stoppen und wiedergehen (ungetestet).
```php
LGTV_pause($Instance);  
LGTV_play($Instance);
```

Mit dieser Funktion kann man eine App starten oder die HDMI eingang wechseln.
```php
LGTV_startApp($Instance,"com.webos.app.hdmi2");  // Auf HDMI-2 wechseln
```
Apps die bisher funktionieren
```php
"com.webos.app.hdmi1"
"com.webos.app.hdmi2"
"com.webos.app.hdmi3"
"com.webos.app.hdmi4"
"com.webos.app.browser"
"com.webos.app.connectionwizard"
"com.webos.app.miracast"
"com.webos.app.notificationcenter"
"com.palm.app.settings"
"com.webos.app.softwareupdate"
"com.webos.app.livetv"
"com.webos.app.tvguide"
```

Mit dieser Funktion kann man ein eigenes Request für das WebOs absetzten.
```php
$command = '{"id":"volumeUp","type":"request","uri":"ssap://audio/volumeUp"}';
print_r(LGTV_ownCommand($Instance,$command)); // Return Json
```

