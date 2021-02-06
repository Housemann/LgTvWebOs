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

* Ermöglicht das Auslesen der eingenen Plex Mediatheken
* Dazu kann man pro Mediathek das einlesen seiner Plex Filme starten
* Darstellung und Bedienung via WebFront und mobilen Apps

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

Hiermit kann man eine Nachricht an den Fernseher für ein PopUp senden.
```php
LGTV_message($Instance,"Das ist eine Test Nachricht")
```

Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

**Beispiel:**

Variable ID Status: 12345
```php
RequestAction(12345, true); //Einschalten
RequestAction(12345, false); //Ausschalten
```