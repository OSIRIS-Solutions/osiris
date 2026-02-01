# Neuigkeiten


<time datetime="2026-01-18">01.02.2026</time>
<a class="anchor" href="#version-1.8.0" id="version-1.8.0"></a>

## Version 1.8.0

Es gibt viele neue Funktionen und Verbesserungen, insbesondere im Bereich Customization, Portfolio und Organisationen. Hier die Highlights:

### Design & Customization
Bislang war es nur möglich, das Logo und die Farben von OSIRIS anzupassen. Mit diesem Update haben wir die Customization-Funktionen deutlich erweitert:
- **Eigene Schriftarten**: Ihr könnt jetzt eigene Schriftarten in OSIRIS verwenden, um das Erscheinungsbild noch besser an eure CI anzupassen.
- **Icons**: wählt aus drei verschiedenen Icon-Stilen (Standard, Gefüllt oder Zweifarbig).
- **Viele Elemente anpassen**: z.B. die Dicke und Farbe von Rahmen, die Rundung von Ecken, Schatten, Icons und vieles mehr.
- **Kopfzeile**: Ihr könnt jetzt die Kopfzeile anpassen, z.B. die Höhe verändern, die Logos komplett in den Footer verschieben oder das OSIRIS-Logo einfärben.


### Portfolio

OSIRIS Portfolio wurde um einige nützliche Funktionen erweitert:

- Es gibt jetzt die Möglichkeit, ein öffentliches Portfolio direkt in OSIRIS zu aktivieren. Diese Funktion kann im Admin-Bereich unter "Generelle Einstellungen" > "Funktionen" aktiviert werden. Das öffentliche Portfolio ist auch ohne Login von der Startseite aus zugänglich.
- Die Forschungsbereiche wurden hinzugefügt und können jetzt im Portfolio und in der API angezeigt werden. Sie haben eine schnell zugängliche Übersichtsseite und werden auch in allen verknüpften Entitäten angezeigt (Aktivitäten, Projekte, Personen, Gruppen).
- Infrastrukturen wurden hinzugefügt und können jetzt im Portfolio und in der API angezeigt werden. Sie haben eine eigene Seite im Portfolio und werden auch in verknüpften Aktivitäten und auf der persönlichen Seite vom Betriebspersonal angezeigt.
- Es wurde ein neuer Tab mit Kooperationspartnern hinzugefügt, der eine Weltkarte mit den Standorten der Partner anzeigt. Diese Karte wurde verbessert, um den verfügbaren Platz besser zu nutzen und auf großen Bildschirmen ansprechender auszusehen. Außerdem unterstützt die Karte jetzt die Aggregation von Partnern je nach Zoomstufe, um die Übersichtlichkeit zu erhöhen.
- Es wurden weitere Einstellungen für persönliche Profile hinzugefügt, z.B. die Möglichkeit, die Anzeige von weiteren Aktivitäten oder Lehraktivitäten zu deaktivieren.
- Es wurden weitere Vorschauoptionen hinzugefügt.

Es ist nun außerdem möglich, die **URL zum externen Portfolio** anzupassen. So könnt ihr z.B. eine eigene Domain verwenden oder das Portfolio einfacher in eine bestehende Webseite integrieren. Dazu gibt es im Admin-Bereich unter "Generelle Einstellungen" > "Portfolio" ein neues Feld "Portfolio Basis-URL". Wenn dieses ausgefüllt ist, wird diese URL für alle Links verwendet, die in Portfolio generiert werden. Andernfalls wird die OSIRIS versuchen, eine relative URL zu verwenden.

### Organisationen und Projekt-Kollaborationen

- Es ist nun möglich, Logos für Organisationen hochzuladen, die dann in verschiedenen Bereichen von OSIRIS angezeigt werden (z.B. in Projekten, auf der Organisationsseite, in der erweiterten Suche).
- Für eine bessere User Experience beim manuellen Anlegen von Organisationen, können die Koordinaten jetzt automatisch über den angegebenen Standort (wahlweise inkl. des Landes) bezogen werden. Dabei wird die aktuelle UI-Sprache verwendet, um die bestmöglichen Ergebnisse zu erzielen.
- Auf der Übersichtsseite einer Organisation wird neben einem überarbeiteten Layout jetzt auch eine Karte mit den Standort der Organisation angezeigt.
- Synonyme wurden für Organisationen hinzugefügt zur besseren Auffindbarkeit, z.B. kann dann die Deutsche Forschungsgemeinschaft auch über "DFG" gefunden werden. Die Synonyme werden ebenfalls über ROR bezogen.
- Der Kollaborationsbereich in Projekten grundlegend überarbeitet und fügt sich damit nahtlos in das neue Organisationsmanagement ein. Es kann zuerst in bereits bestehende Organisationen gesucht und diese dann als Kollaborationspartner hinzugefügt werden. Alternativ können auch neue Organisationen direkt im Projekt angelegt werden.

### Lehrveranstaltungen

Lehrveranstaltungen wurden grundsätzlich überarbeitet und bieten nun mehr Möglichkeiten
- Die Übersichtsseite zeigt jetzt alle Lehrveranstaltungen in einer Tabelle an, die deutlich übersichtlicher ist und mehr Informationen bietet.
- Jede Lehrveranstaltung hat jetzt eine eigene Seite, auf der alle Details angezeigt werden, inkl. aller verknüpften Aktivitäten. Dabei werden Aktivitäten mit dem Modul `supervisors` auch mit ihren Semesterwochenstunden angezeigt.
- Es gibt auch eine neue Seite, um existierende Lehrveranstaltungen zu bearbeiten. Dazu wurde ein neues Recht hinzugefügt (Lehrveranstaltungen bearbeiten), das standardmäßig an alle Nutzenden vergeben wird (wie es zuvor auch war).
- Verknüpfung mit Organisationen:
  - Anstatt Organisationen händisch einzugeben, können diese jetzt aus dem Organisationsmanagement ausgewählt werden. Alternativ gibt es einen Link, um neue Organisationen anzulegen. 
  - Es gibt auch eine Vorschlagsliste, die die 5 am häufigsten zu Lehrveranstaltungen hinzugefügten Organisationen anzeigt, um die Bearbeitung zu vereinfachen.
  - Auf der Seite der Organisationen werden alle verknüpften Lehrveranstaltungen angezeigt.
  - Die Organisation wird auch auf der Statistik-Seite der Lehrveranstaltungen angezeigt.

> Bitte beachte, dass wir die Organisationen nicht automatisch hinzufügen können, wenn ihr bereits Lehrveranstaltungen habt. Diese müsst ihr manuell bearbeiten, um die Organisationen zu verknüpfen. Allerdings werden bestehende Organisationen beibehalten und weiterhin angezeigt.

### Projekte und Anträge

**Finanzen** 
*(Aufgrund der Sensibilität dieser Daten sind sie ausschließlich im Antrag zu sehen und nicht im Projekt selbst)*

- Die Drittmitteleinnahmen wurden in ein neues Format überführt, das nun neben den geplanten auch die tatsächlich verausgabten Mittel pro Jahr erfasst.
- Es gibt jetzt eine Übersicht über die Finanzen eines Projekts, die geplante und verausgabte Mittel pro Jahr anzeigt und diese analysiert und visualisiert.
- In einer neuen Finanzübersicht können Admins und Projektverantwortliche verschiedene Finanzberichte generieren, z.B. geplante vs. verausgabte Mittel. Es gibt ein neues Zugriffsrecht dafür (Finanzinformationen sehen), das standardmäßig nicht vergeben ist.

**Formulare & Eingaben**

- Die maximale Länge für Projekt-Kurzbezeichnungen von 30 auf 100 Zeichen erhöht
- Dafür wurde ein neues Akronym-Feld ergänzt: dieses wird mit dem Kurztitel kombiniert, falls vorhanden
- Fehler behoben: Projekttyp wurde im Projektformular nicht angezeigt
- Es wurde die Möglichkeit hinzugefügt, Projekte und Anträge als Verbundvorhaben zu kennzeichnen. Dabei wurde sich am KDSF-Datenmodell orientiert. Bei Verbundvorhaben wird nun ebenfalls nach dem Identifier und dem Titel des Verbunds, sowie nach der Sprecherrolle gefragt. Wenn ihr das Datenmodul "Verbundvorhaben" nutzen wollt, müsst ihr es zunächst in der Konfiguration aktivieren. Geht dazu auf Inhalte > Projekte und wählt die entsprechende Projektkategorie aus. Auf der zweiten Seite könnt ihr das Modul für alle Phasen aktivieren, bei denen es eine Rolle spielen soll (z.B. Beantragt, Bewilligt, Projektphase).
- Es gibt nun auch eine Möglichkeit, ein Förderprogramm-Feld zu nutzen, das eine Liste statt eines Freifeldes verwendet. Dabei werden vordefinierte Förderprogramme aus einer Vokabularliste verwendet, die im Admin-Bereich verwaltet werden kann.

### Dokumente von Aktivitäten

Die Dokumentenverwaltung bei Aktivitäten wurde auf das neue System migriert und dadurch deutlich verbessert:
- Der Bereich "Dokumente" in Aktivitäten wurde komplett überarbeitet und bietet jetzt eine deutlich bessere Übersicht und Handhabung.
- Es ist nun möglich, die Art der Dokumente anzugeben und eine Beschreibung hinzuzufügen.
- Für die Arten von Dokumenten gibt es jetzt eine Vokabularliste, die im Admin-Bereich verwaltet werden kann.
- Es gibt einen neuen Bereich "Dokumente", in dem alle Dokumente aus Aktivitäten und Projekten zentral angezeigt werden. Außerdem kann in dieser Liste nach Dokumentarten und Dateitypen gefiltert werden. Dazu wurde ein neues Zugriffsrecht (Dokumente sehen) dem Bereich "Ansichten" hinzugefügt, das standardmäßig nicht vergeben ist.

### Aktivitäten verknüpfen

- Es wurde die Möglichkeit hinzugefügt, Aktivitäten miteinander zu verknüpfen, um Beziehungen wie "ist eine Übersetzung von", "ist eine erweiterte Version von" oder "ist ein Preprint von" abzubilden. Dabei können beliebige Aktivitätstypen miteinander verknüpft werden.
- Die Verknüpfungen können in beide Richtungen angezeigt werden und sind in der Aktivität unter einem eigenen Abschnitt zu finden.
- Die Arten von Verknüpfungen sind im Moment noch nicht konfigurierbar, aber es sind bereits einige gängige Beziehungen vordefiniert. Wir möchten gern erst weiteres Feedback sammeln, bevor wir hier mehr Flexibilität einbauen.

### Organisationseinheiten

- Die Forschungsfeldklassifikation des KDSF wurde nun auch den Einheiten in OSIRIS hinzugefügt. Dadurch können Einheiten bestimmten Forschungsfeldern zugeordnet werden, was die KDSF-Konformität weiter verbessert.
- Ein Fehler wurde behoben, durch den Klammersetzung in Einheitennamen die Darstellung als Organigramm verhindert hat. Außerdem unterstützt das Organigramm jetzt auch die deutschen Namen von Einheiten.

### Journale

- Es wurde die Möglichkeit hinzugefügt, Journale zu löschen. Dazu gibt es ein neues Zugriffsrecht (Journale löschen), das standardmäßig nicht vergeben ist. Man kann nur Journale löschen, die nicht mit Aktivitäten verknüpft sind.

### Visualisierungen

Die Visualisierung "Abteilungs-Netzwerk" wurde überarbeitet und in "Aktivitäten-Netzwerk" umbenannt, um besser die neue Flexibilität widerzuspiegeln.
- Dabei kann nach verschiedenen Entitäten gruppiert werden. Im Moment werden Organisationen (1. und 2. Level) und Forschungsbereiche unterstützt, weitere folgen eventuell in zukünftigen Versionen. Vorschläge sind willkommen!
- Es können nun Aktivitäten verschiedener Kategorien (z.B. Publikationen, Vorträge, Medienbeiträge) visualisiert werden
- Das Startjahr kann jetzt frei gewählt werden (default: 5 Jahre zurück)
- Das fertige Netzwerk kann jetzt als PNG oder SVG heruntergeladen werden.

### Weitere Verbesserungen & Bugfixes
- Auf der Seite eines Custom-Fields wird jetzt angezeigt, welche Aktivitäts- und Projekttypen dieses Feld verwenden und ob es mit Infrastrukturen oder Personen verknüpft ist.
- Die Abfrage der Erst- und Letztautoren wurde aus dem normalen "Authors"-Modul entfernt
- Wenn die Edition (ein Datenfeld in Aktivitäten) leer oder keine Nummer ist, wird jetzt kein Suffix mehr angezeigt (z.B. 8th, 2nd, 1st).
- Es ist jetzt nicht mehr möglich, leere Autoren oder Herausgeber hinzuzufügen. Die Fehlermeldungen werden jetzt auch auf Deutsch übersetzt.
- Das Layout der News-Seite wurde verbessert.

----

<time datetime="2025-12-17">17.12.2025</time>
<a class="anchor" href="#version-1.7.1" id="version-1.7.1"></a>

## Version 1.7.1

In dieser Version wurden im Wesentlichen Fehler behoben und kleinere Verbesserungen vorgenommen:

- Die Module `supervisors` und `supervisor-thesis` speichern jetzt nicht mehr in die Autorenliste. Dadurch lassen sich jetzt bei einer Aktivität sowohl Betreuer:innen als auch Autor:innen unabhängig voneinander erfassen. Dazu wurden im gesamten Code überall Optimierungen vorgenommen, um diese Trennung zu gewährleisten. Nach Betreuenden kann jetzt auch in der erweiterten Suche gefiltert werden.
- Es wurde ein Fehler behoben durch den Aktivitätstypen, die nicht öffentlich sichtbar sein sollten, trotzdem über die exakte ID aufgerufen werden konnten.
- Für Aktivitätstypen, die nicht im Portfolio angezeigt werden sollen, gibt es jetzt auch nicht mehr die Option, sie auszublenden. Dies verhindert Verwirrung, da sie ohnehin nicht sichtbar sind.
- Die **Templates** wurden erweitert:
  - Die Conditional Templates unterstützen jetzt auch mehrere Bedingungen mit `&` (und) bzw. `|` (oder).
  - Es gibt nun die Möglichkeit, im Falle das ein Feld leer ist ein anderes Feld zu nutzen (z.B. `{field1|field2}`). Sollte field1 leer sein, wird field2 genutzt. Sollte field2 kein Name eines Feldes sein, wird der Text so ausgegeben. Die Textausgabe kann mit Anführungszeichen erzwungen werden: `{field1| "Default Text"}`
- In der Personen-Übersicht kann jetzt auch nach Rollen gefiltert werden.
- Die Historie wurde verbessert und sollte jetzt zwei unterschiedlich leere Werte nicht mehr als Unterschied erkennen (z.B. "" und []). Außerdem können einige Werte jetzt besser dargestellt werden (z.B. Open Access), da hier keine Icons mehr für die Darstellung in Listen verwendet werden. Des Weiteren können jetzt auch boolsche Werte in der Historie angezeigt werden.


---

<time datetime="2025-12-08">08.12.2025</time>
<a class="anchor" href="#version-1.7.0" id="version-1.7.0"></a>

## Version 1.7.0

### Neues Modul: Nagoya / ABS Compliance

OSIRIS unterstützt jetzt vollständig den Prozess rund um das Nagoya-Protokoll und nationale ABS-Regelungen.
Das neue Modul hilft Forschenden und ABS-Beauftragten, alle relevanten Schritte nachvollziehbar, vollständig und revisionssicher abzubilden.

Hier die Highlights:

- **Country Review:** Bewertung aller beteiligten Länder (Nagoya-Party, eigene ABS-Maßnahmen, Kommentare).
- **Scope-Erfassung für Forschende:** Geographischer, zeitlicher, materieller und nutzungsbezogener Scope — inklusive Unterstützung fürmehrere Probensammlungen pro Land.
- **ABS-Evaluation (A/B/C):** Klassifikation pro Land sowie automatische Projektklassifikation.
- **Permits & Dokumente:** Erfassung von PIC, MAT, Community Consent und weiteren Genehmigungen. Upload & Verwaltung von Dokumenten direkt in OSIRIS.
- **Geteilte Notizen:** Für Kommunikation zwischen Projektteam und ABS-Compliance-Team.
- **Dashboard für ABS-Teams:** Überblick über offene Bewertungen, fehlende Scopes, offene Permits und eingereichte Unterlagen.

Mehr Infos zum Nagoya-Modul findet ihr in unserem [Wiki](https://wiki.osiris-app.de/topics/nagoya/).


### Erweiterte Suche für fast alles

Die erweiterte Suche wurde erweitert und verbessert:

- Die gespeicherten Suchen lassen sich jetzt mit einzelnen Rollen oder Global teilen (Achtung: nur mit der neuen Berechtigung `queries.global`). Geteilte Queries sind mit einem kleinen Flag gekennzeichnet, werden nach unten sortiert und können nicht bearbeitet werden. Um bei so vielen Queries den Überblick zu behalten, gibt es jetzt eine Suchfunktion.
- Es wurden ein paar Probleme mit verschachtelten Queries behoben, die nun auch in der Vorschau korrekt angezeigt werden.
- Der Bereich für die Auswahl der Spalten lässt sich jetzt ebenfalls durchsuchen, um schneller die gewünschten Spalten zu finden.
- Es wurden ein paar kleinere Verbesserungen vorgenommen, durch die es jetzt noch stabiler laufen sollte.

Und weil das so gut funktioniert, wurde die erweiterte Suche jetzt auch auf folgende Bereiche ausgeweitet:
- **Projekte und Anträge**
- **Events**
- **Journals**
- **Nutzende**

Ihr findet dazu neu gestaltete Suchseiten, indem ihr auf die Lupe mit dem Plus in der jeweiligen Übersicht klickt.

### DOI-Mappings für Publikationstypen

- Admins können jetzt im Admin-Bereich unter Inhalte > Aktivitäten > DOI-Zuordnung eigene Mappings für Publikationstypen aus CrossRef und DataCite definieren.
- Diese Mappings werden verwendet, wenn eine Aktivität über eine DOI importiert wird, um den entsprechenden Aktivitätstyp in OSIRIS zu bestimmen.
- Dadurch können spezifische Publikationstypen besser abgebildet werden, z.B. "preprint" oder "conference-paper".

> Achtung: Beta-Feature. Bitte testet es ausgiebig und gebt uns Feedback, damit wir es weiter verbessern können!

### Verbesserungen & Bugfixes

- Einige Verbesserungen und Bugfixes bei den neuen Gästeaccounts (z.B. Suchfunktion in der Nutzerübersicht, Markierung in Tabellen, werden beim Synchronisieren nicht mehr deaktiviert).
- Organisationen-Feld: korrektes Layout + Möglichkeit, gesetzte Werte zu löschen
- ISSN-Feld im Aktivitäten-Formular bricht nicht mehr das ganze Formular ab
- Tags funktionieren wieder zuverlässig
- „Online ahead of print“ kann wieder deaktiviert werden
- Letzter Social-Link ist wirklich löschbar
- Ampersands & Quotes verursachen keine Fehler mehr in Journal-/Aktivitätsfeldern
- Projekt-/Personen-Dropdowns liefern keine leeren Auswahlen mehr
- Wichtiger Fix im Activity-Type-Handling
- Gruppen-Synonyme nutzen jetzt Semikolon statt Komma (Kollisionsvermeidung)
- Portfolio: Keine Hervorhebung mehr, wenn Einträge ausgeblendet sind
- LDAP-Sync: zuverlässige Aktualisierung von Units, inkl. Einheiten-Synonyme
- Fix für ungültige Gastaccounts, wenn „gültig bis“ leer ist
- Array-Dokument-Fehler beim Rendering behoben
- Diverse Verbesserungen an der JSON-Ausgabe
- Fix für kaputten Link zur Attribute-Preview
- u.v.m.

Eine Übersicht über alle geschlossenen Issues findet ihr [hier](https://github.com/OSIRIS-Solutions/osiris/milestone/7)

---

<time datetime="2025-11-16">16.11.2025</time>
<a class="anchor" href="#version-1.6.2" id="version-1.6.2"></a>

## Version 1.6.2

Dieses Update bringt viele Verbesserungen in Performance, Formularlogik, LDAP-Synchronisation, Journals, Events, Reports und UI-Design. Hier die wichtigsten Änderungen:

### Verbesserte Infrastruktur-Statistiken

In diesem Update haben wir die Handhabung der Infrastruktur-Statistiken überarbeitet und ein Migrationsskript hinzugefügt, um bestehende Statistiken in ein neues Format zu überführen.

Infrastruktur-Statistiken sind jetzt deutlich flexibler. Man konnte zuvor bereits verschiedene Statistikfelder definieren, aber diese wurden immer auf alle Infrastrukturen angewendet. Jetzt können Statistiken individuell für jede Infrastruktur definiert und verwaltet werden.

Außerdem ist es möglich, den Zeitraum für die Erhebung der Statistiken festzulegen, z.B. jährlich, vierteljährlich, monatlich oder unregelmäßig. Dies ermöglicht eine genauere und kontextbezogenere Erfassung der Daten. Bei der unregelmäßigen Erfassung wird das Datum der Erhebung gespeichert, sodass man im Nachhinein nachvollziehen kann, wann die Daten erfasst wurden. Wenn der Zeitpunkt der Erhebung erneut verwendet wird, dann werden die bestehenden Daten überschrieben.

Diese neue Flexibilität ermöglicht es, die Statistiken besser an die spezifischen Anforderungen und Gegebenheiten der jeweiligen Infrastruktur anzupassen. Die Abbildungen wurden entsprechend angepasst, um die neuen Möglichkeiten zu unterstützen.

Diese neuen Statistiken reflektieren sich auch in den Benachrichtigungen, die Reporter erhalten, wenn sie ihre Statistiken aktualisieren müssen. Irreguläre Statistiken werden dabei nicht in den Benachrichtigungen angezeigt, es wurde jedoch ein Shortcut zum Hinzufügen-Menü hinzugefügt, um die Erfassung zu erleichtern.


### Tabellen & Downloads
- Verbesserte Tabellenfunktionalität:
  - bessere Stabilität
  - mehr Download-Optionen (z.B. PDF), Excel mehr verbreitet (z.B. bei Journalen, Events, ...)
  - Teilweise Drucken-Funktionalität hinzugefügt
  - Seitenlänge kann angepasst werden
  - Statistik-Tabellen haben jetzt ebenfalls Download- und Druck-Buttons
- Viele Abbildungen lassen sich jetzt ebenfalls als PNG oder SVG herunterladen (z.B. Konfetti-Timelines, Koautoren-Netzwerke, Wordclouds), weitere folgen in kommenden Versionen


### LDAP, Nutzer & Rollen

**Guest Accounts**

Neu: Vollständiges Management für Gastkonten
- Anlegen, Bearbeiten & Löschen von Gastaccounts
- Neue UI-Seiten zur Verwaltung
- Gäste können sich anmelden, wenn LDAP fehlschlägt
- Gastkonten können ein Ablaufdatum haben
- Gastkonten bekommen automatisch die "guest"-Rolle zugewiesen. Diese wurde angelegt, hat aber per Default keine Rechte.

**LDAP-Synchronisation**

- UI der Synchronisation verbessert
- Ein LDAP-Timeout führt dazu, dass die Synchronisation abgebrochen wird, sollte LDAP nicht erreichbar sein
- Synchronisation von Einheiten wurde verbessert: 
  - Synonyme für Einheiten hinzugefügt und berücksichtigt
  - Wiederaktivierte Einheiten werden nicht gelöscht
  - Bei neuen Einheiten wird jetzt nicht mehr angenommen, dass der aktuelle Tag der Start der Zugehörigkeit ist
  - In der Synchronisations-Vorschau gibt es jetzt eine Warnung, wenn Einheiten nicht gefunden werden
  - Neue Sync-Zeitstempel in der UI


### Events
- Events können jetzt zentral deaktiviert werden (Einstellungen > Features)
- Länder wurden zu Events hinzugefügt
- Event-Handling in "Aktivität hinzufügen" wurde verbessert: 
  - Duplikate werden jetzt anhand von Titel und Startdatum erkannt und verhindert
  - Fehlermeldungen werden jetzt korrekt ausgegeben (#253)
  - Typauswahl und Länder wurden ebenfalls hinzugefügt
  - Der Event-Select wurde deutlich verbessert, zeigt jetzt alle verfügbaren Events in einem durchsuchbaren Dropdown an
  - Beim Auswählen eines Events werden automatisch Titel, Datum, Ort und Land vorausgefüllt. Dies wird jetzt im UI deutlicher kommuniziert mit einem Hinweistext und einer kurzzeitigen Markierung der Datenfelder


### Formulare & Eingaben
- Checkboxen in Personen-Feldern funktionieren wieder korrekt (#240)
- Hilfe-Texte werden jetzt per Hover angezeigt und erscheinen links (#288)
- Bei Forschungsinteressen werden jetzt auch deutsche Vorschläge korrekt gezeigt (#286)
- Nutzer:innen können Aktivitäten nicht mehr highlighten, wenn sie keine Autor:innen sind (#290)
- Wenn eine hervorgehobene Aktivität versteckt wird, wird sie nicht mehr über die Portfolio-API ausgeliefert
- Das Label für Forschungsreisen erscheint jetzt auch, wenn Forschungsbereiche deaktiviert sind (#282)
- Man kann jetzt auch Zeitschriften ohne ISSN hinzufügen
- Magazine (nicht-standardisierte Journals) haben eine Vorschlagsliste bekommen
- Journale können jetzt zentral umbenannt werden (Einstellungen > Features)
- Bei Projektanträgen werden jetzt die Formularfelder von "Bewilligt" durch die Felder aus "Beantragt" vorausgefüllt
- Wenn eine Aktivität über eine DOI geladen wird, deren Typ es aber nicht gibt, wird stattdessen jetzt eine Warnmeldung angezeigt, die den Nutzenden auffordert, einen passenden Typ auszuwählen
- Affiliation-Check berücksichtigt nun auch affiliierte Herausgeber:innen (#292)
- Das Default-Icon von Aktivitäten wurde angepasst
- Der volle Link ist nun der Standard in Templates, der abgekürzte Link kann als "link-short" verwendet werden und ist Standard in der Tabellen-Ansicht


### Warteschlange
- Problem behoben: persönliche Warteschlange war nicht für alle Nutzer:innen sichtbar
- Warteschlange wird nicht mehr versteckt, wenn Nutzer:innen eine Warteschlange-Warnung in ihren Benachrichtigungen haben
- Gesamte Warteschlange nur noch für Editor-Berechtigte sichtbar
- Editoren können jetzt trotzdem ihre eigene Warteschlange sehen

### Reports & Report Builder
- Neue Standardvariablen für Zeitfilter im Report Builder
- Tabellen im Report Builder können jetzt sortiert werden
- Zeitlimit im Report Builder verbessert, um langfristige Aktivitäten zu erfassen


### Einstellungen, UI & Design
- Rendering-Sprache als globale Einstellung hinzugefügt (#267)
- Layout der Feature-Einstellung wurde angepasst und verbessert
- Icons auf Duotone umgestellt
- Typografie vereinheitlicht, Header mit Icons versehen

### Weitere Verbesserungen
- Man kann Forschungsbereiche jetzt inaktivieren
- Backend-Admin erhält garantiert Admin-Rechte
- Wenn Journal-Namen Klammern enthalten, bricht das Rendern nicht mehr ab
- Icons in Hinweis- und Fehlermeldungen werden jetzt in allen Browsern korrekt angezeigt
- Die Portfolio-Vorschau funktioniert jetzt auch mit HTTPS

---

<time datetime="2025-11-02">02.11.2025</time>
<a class="anchor" href="#version-1.6.1" id="version-1.6.1"></a>

## Version 1.6.1

Wir haben viele kleinere Verbesserungen vorgenommen:

- Die Benutzeroberfläche für das Feature-Management wurde verbessert, indem die Features in Kategorien gruppiert und mit Beschreibungen versehen wurden. Dies erleichtert es Administratoren, die verfügbaren Funktionen zu verstehen und zu verwalten.
- Es wurde eine Möglichkeit hinzugefügt, Events zentral auszuschalten. Dies kann im Admin-Bereich unter "Generelle Einstellungen" > "Funktionen" konfiguriert werden. Wenn diese Option deaktiviert ist, werden Events in der Navigation und auf der Startseite nicht mehr angezeigt.
- Im Bereich News der eigenen Profilseite können jetzt auch neue Nutzer:innen angezeigt werden. Dies kann im Admin-Bereich unter "Generelle Einstellungen" > "Funktionen" eingestellt werden.
- Es wird nun deutlicher auf Benachrichtigungen hingewiesen, indem der Button im Header rot hervorgehoben wird, wenn neue Benachrichtigungen vorhanden sind und auf der Startseite des Profils eine Meldung angezeigt wird.

### Bug Fixes

**Formulare & Eingaben**
- Autoren und Herausgeber werden nur hinzugefügt, wenn das Formular leer ist; alle können nun gelöscht werden, falls nicht benötigt (#254)
- Pflichtfelder in Projektformularen korrekt markiert (#276)
- Im Formularbaukasten können jetzt auch Felder gespeichert werden, die nur Herausgeber:innen enthalten
- Float-Felder in Custom Fields akzeptieren jetzt auch Dezimalzahlen statt nur ganze Zahlen
- Einige Formular-Labels wurden nicht korrekt durch Nutzereinstellungen überschrieben – dies wurde behoben. Autoren- und Herausgeberüberschriften werden nun korrekt angezeigt
- Es ist nicht mehr möglich, ein Custom Field language zu nennen (verursachte Probleme mit MongoDB)
- Ein Problem wurde behoben, durch das einige Seiten unter bestimmten PHP-Einstellungen nicht geladen werden konnten
- Tippfehler („Ablehnung“) und kleinere Schreibfehler korrigiert (#275, #277)

**Aktivitäten & Darstellung**
- Rendering-Fehler bei Journalnamen mit Klammern behoben
- Der „keine Personen“-Hinweis erscheint nicht mehr, wenn Herausgeber:innen vorhanden sind
- Links zu Einheiten in Aktivitäten korrigiert
- Der Link auf der Startseite zum Sperren von Aktivitäten funktioniert wieder
- Länder von Kollaboratoren werden jetzt in der UI-Sprache angezeigt (#273)
- Assoziierte Partner sind jetzt auf der Kollaborationskarte sichtbar und farblich hervorgehoben (#274)
- Rohdaten werden nicht mehr als HTML interpretiert (#259)
- Spaltennamen in Statistiken und im Feld supervisor-thesis korrigiert (#270)
- SWS können im Autoren-Editor wieder bearbeitet werden

**Benutzer & Organisationseinheiten**
- UX beim Hinzufügen von Organisationseinheiten verbessert
- Einheiten im Autoren-Editor erscheinen nur noch einmal (#268)
- Unterschiedliche Mitarbeiterzahlen auf Gruppenseiten behoben (#258)
- Werte für Expertise, CV und Forschungsinteressen können im Personen-Editor jetzt auch leer gesetzt werden

**Projekte & Vorschläge**
- Projekt-Typen werden nach Aktualisierungsdatum sortiert
- "Projektantrag hinzufügen" ist nun der neue Standard, wenn verfügbar
- Konfetti-Timeline in Projekten lässt sich korrekt filtern

**APIs & Dokumentation**
- Englische API-Dokumentation für Portfolio hinzugefügt (#266)

---



<time datetime="2025-10-12">12.10.2025</time>
<a class="anchor" href="#version-1.6.0" id="version-1.6.0"></a>

## Version 1.6.0

#### <i class="ph ph-seal-check"></i> Neue Qualitäts-Workflows für Aktivitäten

OSIRIS 1.6.0 führt ein vollständig neues System für Qualitäts- und Prüf-Workflows ein – flexibel, offen und direkt in der Aktivität integriert:
- Admins können eigene Workflows definieren und beliebigen Aktivitätskategorien zuordnen.
- Jeder Workflow besteht aus frei konfigurierbaren Schritten, denen Rollen und Organisationseinheiten zugewiesen werden können.
- Schritte können parallel oder sequenziell ablaufen – ideal für Freigaben durch Abteilungsleitungen und Bibliothek.
- Workflow-Schritte lassen sich bei Bedarf auf die eigene Organisationseinheit beschränken.
- Fertige Workflows können nachträglich bestehenden Aktivitäten zugewiesen („migriert“) werden.
- Der Abschluss aller Schritte führt automatisch zum Status „verified“, über den zentral gefiltert werden kann.
- Aktivitäten können optional nach bestimmten Schritten gesperrt werden.

👉 Damit lassen sich interne Prüf- und Freigabeprozesse direkt in OSIRIS abbilden – von der Datenerfassung bis zur Veröffentlichung.

**Neues Workflow-UI in der Aktivität**

Ein neues Workflow-Widget wurde zu Aktivitäten hinzugefügt, die Teil eines Workflows sind:

- Kompakte Schritt-Leiste mit visuellem Fortschritt, Icons und Tooltips.
- Aktuell laufende Schritte werden hervorgehoben, abgeschlossene nach vorne sortiert.
- Ein schmaler, fixierter Fortschrittsbalken oben im Interface zeigt den Workflow-Status jederzeit an.
- Bei abgeschlossenen Workflows färbt sich die Leiste grün, bei Zurückweisungen rot.
- Klick auf die Leiste öffnet das vollständige Workflow-Widget mit allen Details.
- Dieses zeigt alle Schritte, Zuständige, Status und eventuelle Kommentare an.
- Die einzelnen Schritte des Workflows werden in der Historie der Aktivität protokolliert.

**Neues Reviewer-Interface**

Für Rollen mit Prüfaufgaben (z. B. Abteilungsleitungen, Bibliothek) gibt es ein übersichtliches neues Reviewer-Dashboard:
- Zeigt alle aktuell zu prüfenden Aktivitäten auf einen Blick.
- Filter nach Kategorie, Rolle, Organisationseinheit oder Status.
- Schnell-Aktionen zum Freigeben oder Zurückweisen direkt aus der Liste.
- Benachrichtigungen erscheinen automatisch in der Seitenleiste und in der Navigation.

**Möglichkeit, Aktivitäten zurückzuweisen**

Prüfer:innen können Aktivitäten zurückweisen, wenn z. B. Angaben fehlen oder unklar sind. Wird eine Aktivität zurückgewiesen, passiert Folgendes:
- Der Workflow-Status wechselt auf „rejected“.
- Prüfer:innen können einen Kommentar hinterlassen und damit die Aktivität zur Bearbeitung freigeben.
- Autor:innen erhalten eine Nachricht in ihren Benachrichtigungen mit Kommentar.
- Nach Überarbeitung können sie die Aktivität per Klick erneut zur Prüfung einreichen. Dabei können sie ebenfalls einen Kommentar hinterlassen.
- Alle bisherigen Freigaben bleiben erhalten – kein Datenverlust, kein Neustart des Workflows.

**Filter für Workflow-Status**

- Damit Filter und Übersichten korrekt funktionieren, gibt es ein neues Recht zum Anzeigen von Workflows, das standardmäßig nicht zugewiesen ist. Nur wer dieses Recht hat, sieht alle Workflows und kann sie nutzen.
- In der Aktivitäten-Übersicht gibt es jetzt einen neuen Filter für den Workflow-Status (pending, in-progress, verified, rejected). Außerdem gibt es im Quartalfeld einen Indikator, wenn eine Aktivität Teil eines Workflows ist, der den Status anzeigt.
- In der erweiterten Suche gibt es ebenfalls einen Filter für den Workflow-Status.
- Im Download-Bereich gibt es einen neuen Filter für den Workflow-Status.
- In den allgemeinen Einstellungen gibt es jetzt einen Neuen Tab für Portfolio (wenn eingeschaltet), in dem Admins festlegen können, welche Aktivitäten im Portfolio angezeigt werden. Es gibt drei Optionen:
  - Alle Aktivitäten (Standard)
  - Nur genehmigte Aktivitäten (nur Aktivitäten mit dem Status verified)
  - Genehmigte Aktivitäten und Aktivitäten ohne Workflow (Aktivitäten mit dem Status verified und Aktivitäten, die keinem Workflow zugeordnet sind)

**Migration & Datenmodell**

- Alte Workflows können per Migration entfernt oder zurückgesetzt werden (über das Workflow-Interface).
- Automatische Initialisierung bei neu erstellten Aktivitäten.


#### <i class="ph ph-envelope"></i> Neuer Mail-Digest (Benachrichtigungs-Zusammenfassung)

OSIRIS versendet nun auf Wunsch automatische Digest-E-Mails – übersichtlich, zweisprachig und im OSIRIS-Design:

- Enthält eine Übersicht offener Aktivitäten, Nachrichten und Aufgaben (alles was man in den Benachrichtigungen sieht).
- Frequenz pro Nutzer einstellbar: none, daily, weekly, monthly.
- Standardverhalten kann global von Admins festgelegt werden.
- Versand erfolgt automatisch über den Cron-Job `/cron/digest`.
- Mails sind zweisprachig (DE/EN), vollständig UTF-8-kodiert und nutzen das OSIRIS-Design.

#### <i class="ph ph-file-text"></i> Entwürfe für Aktivitäten
- Nutzer:innen können Aktivitäten als Entwurf speichern und später fertigstellen.
- Entwürfe sind nur für die erstellende Person sichtbar.
- Entwürfe können jederzeit geladen, bearbeitet und gespeichert werden.
- Wenn ein Entwurf gespeichert wird, wird automatisch eine neue Aktivität erstellt und der Entwurf gelöscht.
- Dieses Feature kann im Admin-Bereich unter "Funktionen" ein- und ausgeschaltet werden.

#### <i class="ph ph-tag"></i> Schlagworte für Aktivitäten, Projekte und Events

- Aktivitäten können nun mit Schlagworten versehen werden, um sie besser zu kategorisieren und zu finden.
- Es kann in alle Aktivitäten nach Schlagworten gefiltert werden.
- Schlagworte lassen sich in der Detailansicht von Aktivitäten hinzufügen. Sie können aber auch bei der Erstellung oder Bearbeitung von Aktivitäten hinzugefügt werden. Dazu wurde ein neues Datenfeld `tags` hinzugefügt.
- Dem Rechte-Management wurde ein neues Recht `activities.tags` hinzugefügt, das standardmäßig nicht vergeben ist. Nur wer dieses Recht hat, kann Schlagworte zu Aktivitäten hinzufügen oder entfernen. Dies gilt auch immer nur dann, wenn die Aktivität selbst auch bearbeitet werden darf. 
- Schlagworte lassen sich zentral im Admin-Bereich unter "Inhalte" > "Schlagworte" verwalten. Dort können Admins neue Schlagworte hinzufügen, bestehende bearbeiten oder löschen.
- Dieses Feature kann im Admin-Bereich unter "Funktionen" ein- und ausgeschaltet werden.
- Schlagworte können zu Aktivitäten, Projekten, und Events hinzugefügt werden. 

#### <i class="ph ph-file-text"></i> Verbesserter Report-Builder

Der Report-Builder wurde überarbeitet und bietet nun eine verbesserte Benutzeroberfläche sowie zusätzliche Funktionen zur Anpassung von Berichten.

- Es wurden neue **Variablen für Berichte** hinzugefügt, die im Report-Template verwendet werden können. Diese Variablen können im Report-Builder definiert werden und ermöglichen es, dynamische Inhalte in den Bericht einzufügen. Sowohl die Vorschau als auch der generierte Bericht unterstützen diese Variablen.
- Es wurde ein neuer Baustein hinzugefügt, mit dem man eine Tabelle mit Aktivitäten und einem beliebigen weiteren Datenfeld erstellen kann. Dies ermöglicht es, Berichte zu erstellen, die spezifische Informationen zu Aktivitäten enthalten, die für den Bericht relevant sind, zum Beispiel Impact-Faktoren, Publikationsarten oder andere benutzerdefinierte Felder.
- Die Unterstützung für **Textbausteine** wurde verbessert. Textbausteine können jetzt formatierten Text enthalten, der im Bericht korrekt dargestellt wird. Dies ermöglicht es, Berichte mit ansprechenderen und besser strukturierten Textinhalten zu erstellen.
- Sowohl der Aktivitäten- als auch der Impact-Faktor-Baustein unterstützen jetzt **Sortierungen** (gewünscht von IfL). Es wird dabei zwischen aufsteigender und absteigender Sortierung unterschieden.
- Die Zeitbegrenzung ist jetzt standardmäßig ausgewählt, um Verwirrung zu vermeiden.
- Die Benutzeroberfläche wurde verbessert, indem Klapp- und Kopierfunktionen hinzugefügt wurden. Außerdem gibt es jetzt Dropdown-Menüs für Aggregationen und eine bessere Beschreibung der Bausteine.
- Die Aggregations-Tabellen wurden verbessert, indem korrekte Namen für Felder und Werte verwendet werden. Auch leere Felder werden jetzt besser dargestellt. Außerdem sorgt das "Entwirren" von Array-Feldern dazu, dass diese einzeln gezählt werden können. Man kann also jetzt zum Beispiel nach Einheiten oder Forschungsbereichen aggregieren, auch wenn diese als Mehrfachauswahl definiert sind.


#### <i class="ph ph-textbox"></i> Neue Datenfelder
- Es wurden neue Datenfelder für Aktivitäten hinzugefügt:
  - **Funding Type**: Ein Dropdown-Feld, das es ermöglicht, den Typ der Finanzierung auszuwählen. Die möglichen Werte können im Vokabular unter "funding-type" definiert werden und sind die gleichen wie bei Projekten.
  - **Schlagworte**: Ein Mehrfachauswahl-Feld, das es ermöglicht, Schlagworte zu Aktivitäten hinzuzufügen. Die möglichen Werte können im Admin-Bereich unter "Inhalte" > "Schlagworte" verwaltet werden.
  - **Projekte**: Ein Mehrfachauswahl-Feld, das es ermöglicht, Projekte mit Aktivitäten zu verknüpfen. Es können mehrere Projekte ausgewählt werden und die über die DOI zur Verfügung gestellten Funding-Nummern werden automatisch mit den Projekten abgeglichen und verknüpft. Es gibt ein neues Recht, um Projekte mit Aktivitäten zu verknüpfen, ohne sie bearbeiten zu können.


#### <i class="ph ph-lock"></i> Auth-Token für Nutzerregistrierung
- Es wurde ein Auth-Token für die Auth-Nutzerregistrierung eingeführt. Dieser Token kann verwendet werden, um die Registrierung neuer Nutzer zu validieren und sicherzustellen, dass nur autorisierte Anfragen bearbeitet werden.
- Der Token kann in der URL zur Registrierungsseite übergeben werden und wird dort überprüft. Bei erfolgreicher Validierung kann der Nutzer mit der Registrierung fortfahren.
- Dies erhöht die Sicherheit des Registrierungsprozesses und verhindert Missbrauch durch unautorisierte Zugriffe.


#### <i class="ph ph-list-checks"></i> Erweiterung für Conditional Templates

Die Vorlagenlogik wurde erweitert, um bedingtes Rendern basierend auf **mehreren Feldern** zu unterstützen:

- Verwende `&`, um zu verlangen, dass **alle** Felder vorhanden sind.
- Verwende `|`, um zu rendern, wenn **ein beliebiges** Feld vorhanden ist.

**Beispiele:**
- `%title Titel: %` → wird nur gerendert, wenn `title` gesetzt ist.  
- `%title&authors by %` → wird nur gerendert, wenn **sowohl** `title` als auch `authors` ausgefüllt sind  
- `%journal|conference In: %` → wird gerendert, wenn **entweder** `journal` oder `conference` vorhanden ist

Dadurch werden flexiblere und kontextsensitive Vorlagen möglich, ohne dass zusätzliche Codelogik hinzugefügt werden muss.


#### Bug Fixes und Verbesserungen
- Es wurde die Visualisierung der Netzwerke von Organisationseinheiten gefixt
- Autor:innen mit mehreren Vornamen werden jetzt korrekt abgekürzt
- Es wurde ein Problem behoben, durch das man beim Kopieren einer Aktivität eine deaktivierte Kategorie auswählen konnte
- Die Dokumentation wurde durch das neue Wiki unter https://wiki.osiris-app.de/ ersetzt. Die alten Links zu den Hilfeseiten wurden aktualisiert.
- Die Darstellung der Weltkarte von Kooperationspartnern wurde verbessert, indem das Layout-Styling angepasst und die Größe der Karte automatisch an den verfügbaren Platz angepasst wird. Dadurch sieht die Karte jetzt besonders auf großen Bildschirmen besser aus und nutzt den verfügbaren Platz optimal.
- Es wurde ein Problem behoben, durch das Custom Fields ohne Wert immer '-' ausgegeben haben.
  - Die Logik ist, dass ein Custom Field das explizit leer gelassen wurde, auch als leer gerendert wird. Wenn das Feld jedoch gar nicht existiert, z.B. bei neuen Aktivitäten oder weil das Feld später dazugekommen ist, dann wird der angegebene Default-Wert verwendet.


---


<time datetime="2025-09-30">30.09.2025</time>
<a class="anchor" href="#version-1.5.2" id="version-1.5.2"></a>

## Version 1.5.2

- **Neue Conditional Templates**: mit `%field text%` wird der Text jetzt nur noch gezeigt, wenn das entsprechende Feld nicht leer ist. Beispiel: `%details Details:%` rendert "Details:" nur, wenn das Details-Feld nicht leer ist.
- **Weiteres bei Listen**: Custom Fields mit dem Typ "Liste" können jetzt so eingestellt werden, dass man "Weiteres" auswählen und spezifizieren kann (über Freitextfeld). Dies funktioniert im Moment noch nicht in Kombination mit Mehrfachauswahl.
- **Distribute Roles**: die Vergabe von Rollen ist jetzt viel einfacher geworden
- **Password zurücksetzen**: bei der OSIRIS Auth Nutzerverwaltung ist es ist jetzt für Admins mit entsprechenden Rechten möglich, zentral ein Passwort zurückzusetzen und der Person dann einen erhaltenen Link zuzuschicken
- **Verbesserte ID-Bereinigung**: OSIRIS ersetzt verbotene Zeichen in IDs (zum Beispiel Leerzeichen und Punkte) jetzt automatisch bei der Eingabe. Dies gilt jetzt auch für Custom Fields, da es hier zuvor zu Problemen kommen konnte, insbesondere wenn diese IDs Leerzeichen oder Punkte enthielten. Besonders Punkte haben in der verwendeten MongoDB-Datenbank zu Problemen geführt.
- **Formularbaukasten** wurde weiter verbessert:
  - Für die Vorschau muss nicht mehr gespeichert werden
  - Die überschriebenen Labels werden auch in der Detail-Tabelle der Aktivität für die Überschriften verwendet
- Neues Datenfeld **Projekte**: hier können Projekte jetzt direkt im Aktivitätsformular verknüpft werden
  - Es können mehrere Projekte ausgewählt werden
  - Die über die DOI zur Verfügung gestellten Funding-Nummern werden automatisch mit den Projekten abgeglichen und verknüpft
  - Es gibt ein neues Recht, um Projekte mit Aktivitäten zu verknüpfen, ohne sie bearbeiten zu können
  - Ihr könnt das neue Datenfeld im Formularbaukasten zu euren Aktivitäts-Typen hinzufügen
- In Aktivitäten: **leere Felder** werden jetzt nicht mehr in der Tabelle sondern als Liste am Fuß der Tabelle dargestellt, um übersichtlicher zu sein
- Die Maintenance-Message wurde verbessert
- Der "Aktivität hinzuf."-Knopf ist jetzt auch bei Meine Aktivitäten zu finden
- Die Printdarstellung wird jetzt auch über die Portfolio-API ausgeliefert
- Es gibt ein neues Recht, um Projekte mit Aktivitäten zu verknüpfen, ohne sie bearbeiten zu können. Dieses wird automatisch mit dem Recht zum Bearbeiten von Projekten mitgegeben.

#### Fehlerbehebungen:
- Ein Fehler wurde behoben, durch den Aktivitäten nicht bestätigt werden konnten
- Ein Fehler wurde behoben, durch den beim Synchronisieren von LDAP-Attributen diese einem zufälligen Nutzer zugeschrieben wurden
- Aktivität hinzufügen: eine Kategorie auszuwählen wählt jetzt korrekt den ersten Typ aus
- Emails können jetzt auch ohne SMTP Authentifikation verschickt werden
- Der Link zu Aktivitäten in Events wurde gefixt
- Der SWS-Rechner wurde aus dem `supervisor-thesis`-Modul entfernt
- Die Erweiterte Suche wurde verbessert und sollte nicht mehr so fehleranfällig sein
- Es wurden ein paar Probleme mit den neuen Autoren-Templates behoben
- Einige Achievement-Texte wurden verbessert oder auf englisch korrigiert
- Ein paar weitere Übersetzungs- und Textfehler wurden korrigiert
- NPM JSCDN wurde entfernt, um die Sicherheit zu verbessern
- Es werden jetzt nicht zusätzlich volle Adminrechte benötigt, um Nutzer zu synchronisieren
- Die Karte der Kooperationspartner wurde nicht geladen, wenn Latitude und Longitude des Instituts nicht gesetzt waren. Jetzt wird eine Standard-Position verwendet, um dem vorzubeugen.

---

<time datetime="2025-08-24">24.08.2025</time>
<a class="anchor" href="#version-1.5.1" id="version-1.5.1"></a>

## Version 1.5.1

#### <i class="ph ph-toolbox"></i> Neuer Formular-Baukasten

Mit dem neuen Formbuilder kannst du Formulare für Aktivitäten noch flexibler gestalten:

- Freie Gestaltung mit Überschriften, Textabsätzen und Trennlinien
- Eigene Bezeichnungen für Felder, Hilfetexte und individuelle Feldbreiten
- Vorschau von einzelnen Feldern und des gesamten Formulars direkt beim Bearbeiten
- Möglichkeit, ein bestehendes Formular zu kopieren und als Vorlage zu verwenden
- Im Bereich der Aktivitäts-Typen werden Datenfelder jetzt als Badges mit Icons dargestellt und es gibt Direkt-Links zum neuen Formbuilder

👉 Damit wird die Konfiguration von Aktivitätsformularen so einfach wie nie!

*Vielen Dank an Jochen Knaus, der dieses Feature auf Herz und Nieren getestet hat und uns wertvolles Feedback gegeben hat! 🙏*

#### <i class="ph ph-selection-background"></i> Verbesserte Auswahlfelder (Multi-Select)

- Auswahlfelder mit mehreren Optionen funktionieren jetzt viel intuitiver: statt gedrückter Strg-Taste gibt es einfache Checkboxen.
- Das Feld verbraucht jetzt auch deutlich weniger Platz
- Es wurde ein Bug behoben, durch den die Spracheinstellungen des Interfaces zu unterschiedlichen Werten in der Datenbank führten. Es wird jetzt korrekt immer der englische Wert gespeichert. Außerdem werden Multiselect-Felder im deutschen Interface jetzt korrekt übersetzt und angezeigt.

#### <i class="ph ph-graduation-cap"></i> Bessere Unterstützung für Abschlussarbeiten

- Es gibt jetzt ein neues Modul `supervisor-thesis`, das als Autorenfeld dient (speichert in Autoren). Es ist komplementär zum `supervisor`-Feld, das Semesterwochenstunden benötigt. Das neue Feld speichert stattdessen die Rolle des Betreuenden.
- Auch im Autoren-/Editor-Interface werden Betreuende korrekt angezeigt.


#### <i class="ph ph-highlighter"></i> Herausgeber-Probleme behoben

- Der Autoren-Editor (Aktivität > Autoren bearbeiten) funktionierte bislang nicht bei Herausgebern. Stattdessen wurden die Autoren überschrieben! Das wurde jetzt behoben.
- Herausgeber konnten zuvor ihre Aktivitäten nicht bestätigen. Das Problem ist jetzt behoben, sodass sie ihre Aktivitäten in den Benachrichtigungen korrekt gezeigt bekommen und bestätigen können.
- Sie werden nun korrekt bei Aktivitäten und Einheiten mitgerechnet.
- Semesterwochenstunden (SWS) und Betreuer-Rollen lassen sich nun auch im Autoreneditor-Interface anpassen.

#### <i class="ph ph-paint-brush"></i> UI/UX-Optimierungen

- Verbesserte Darstellung von gruppierten Listen mit neuem CSS
- Neue Tooltip-Hilfen in Aktivitäts-Formularen


---


<time datetime="2025-07-31">31.07.2025</time>
<a class="anchor" href="#version-1.5.0" id="version-1.5.0"></a>

## Version 1.5.0

In diesem Update wurden die Projekte komplett überarbeitet und deutlich flexibler gestaltet. Datenfelder können nun sowohl bei Projekten als auch bei Personen vollständig definiert werden. Auch Custom Fields sind jetzt bei Projekten, Personen und Infrastrukturen möglich.

#### <i class="ph ph-stack"></i> Verbesserung im Admin-Bereich

Es gibt eine neue Seite im Admin-Bereich **"Inhalte"**, in der alle bearbeitbaren Inhalte von OSIRIS aufgelistet sind. Hier findet ihr jetzt die Personen-, Aktivitäts-, Projekt- und Infrastrukturkategorien, die Custom Fields und das Vokabular. Außerdem gibt es Links zu Helfertools, wie der Liste mit den Datenfeldern und den Templates. Bitte beachtet, dass einige Features aus den Generellen Einstellungen und Funktionen hierher verschoben wurden.

Der Bereich **Funktionen** wurde in die allgemeinen Einstellungen verschoben. Hier gibt es jetzt auch deutlich mehr Einstellungsmöglichkeiten, zum Beispiel kann das quartalsweise Controlling deaktiviert, die automatische Abfrage von Journal-Metriken unterbunden, der Kalender und die Lehrveranstaltungen aus der Navigation verbannt und auch der Import von OpenAlex oder GoogleScholar deaktiviert werden.

Es wurde eine Möglichkeit hinzugefügt, den **Footer** der Seite anzupassen. Die Inhalte von Impressum und Datenschutzerklärung können (und sollten!) bearbeitet werden und Links zu externen Ressourcen (z.B. Betriebsvereinbarungen) können hinzugefügt werden.

#### <i class="ph ph-chat-circle-dots"></i> Benachrichtigungen und Nachrichten

- Die Navigation wurde überarbeitet: Die Benachrichtigungen sind jetzt im Header und nicht mehr auf der Startseite.
- Neben den bisherigen System-Benachrichtigungen können nun gezielt Nachrichten an bestimmte Nutzer:innen oder Rollen verschickt werden.
- Eingegangene Nachrichten können als gelesen markiert oder gelöscht werden.
- Die Darstellung der Nachrichten wurde verbessert und das Styling modernisiert.
- OSIRIS verschickt jetzt optional **E-Mail-Benachrichtigungen**, z.B. bei Erstellung oder Bearbeitung von Projekten. Im Admin-Interface kann dies konfiguriert werden.

#### <i class="ph ph-users-three"></i> Personen-Einstellungen

- Die Personeneinstellungen sind jetzt unter "Inhalte" zu finden.
- Datenfelder für Personen können angepasst und Custom Fields hinzugefügt werden.
- Positionen werden nun ebenfalls in diesem neuen Bereich verwaltet (zuvor in Generelle Einstellungen), genau wie die Einstellungen zu Coins und Achievements (zuvor in Funktionen)
- Es wurde ein neues Datenfeld "Schlagworte" hinzugefügt, das ähnlich wie Expertise verwendet werden kann, allerdings mit einem definierten Vokabular arbeitet. Wie genau dieses Datenfeld heißen soll, kann ebenfalls im Admin-Bereich definiert werden.
- Es kann festgelegt werden, ob sich Nutzer:innen selbst registrieren dürfen oder ob dies nur Admins möglich ist.
- Die Auswahl von Organisationseinheiten bei der Zuordnung von Personen wurde verbessert.
- Beim Anlegen einer Person wird jetzt nicht mehr nach dem Passwort gefragt, wenn als User-Management nicht das `AUTH`-Addon ausgewählt ist, z.B. wenn LDAP oder OAUTH2 verwendet wird.

#### <i class="ph ph-cube-transparent"></i> Forschungsinfrastrukturen

- Die Datenfelder für Infrastrukturen sind jetzt konfigurierbar. Diese Einstellungen sind unter "Inhalte" zu finden.
- Custom Fields sind auch hier möglich.
- Die Übersichtstabellen für Infrastrukturen unterstützen jetzt Filter und Suchfunktionen.
- Die Darstellung von Verbundinfrastrukturen und Kooperationspartnern wurde verbessert.
- Der Begriff "Infrastrukturen" kann jetzt im Admin-Bereich angepasst werden.
- Forschungsbereiche können jetzt auch bei Infrastrukturen hinzugefügt und zum Filtern verwendet werden.
- Die Jahresstatistiken für Infrastrukturen sind jetzt konfigurierbar und können über das Vokabular angepasst werden.
- Die Statistiken für Infrastrukturen wurden ebenfalls verbessert, lassen sich jetzt nach dem Jahr filtern und nehmen als Startdatum den 31.12. des Vorjahres an. Außerdem werden selbst definierte Statistikfelder unterstützt, die über das Vokabular definiert werden können.

#### <i class="ph ph-tree-structure"></i> Projekte und Projektanträge

Die Projekte wurden komplett überarbeitet und bilden jetzt den gesamten Projektlebenszyklus ab:

- **Projekttypen** sind konfigurierbar (Inhalte > Projekte), inklusive der zugehörigen Datenfelder.
- Vokabulare wie Fördererkategorien sind frei definierbar.
- Förderer, Universitäten und Kooperationspartner können über ROR-IDs verknüpft werden.
- Custom Fields sind auch bei Projekten möglich.
- Bessere Unterstützung für mehrsprachige Eingabefelder (z.B. Projekttitel, Abstracts).
- Teilprojekte wurden ebenfalls verbessert und zeigen nun die Kooperationspartner des Elternprojekts an. Die automatische Vererbung wurde entfernt, um die Flexibilität zu erhöhen.

**Projektanträge**:

- Neue Entität "Projektantrag" eingeführt, inklusive Statusverwaltung (Beantragt, Bewilligt, Abgelehnt).
- In Formularen werden nun nur die relevanten Felder angezeigt, abhängig vom Status des Antrags.
- Neue Antragsübersicht mit Filtermöglichkeiten.
- Bewilligte Anträge können in Projekte überführt werden.
- Neue Felder zum Tracken von Antrags- und Bewilligungsdaten wurden hinzugefügt.
- Finanzierungsinformationen (z.B. Drittmitteleinnahmen) können bei Anträgen gepflegt werden.
- Das Berechtigungssystem für die Bearbeitung und Ansicht von Anträgen wurde verfeinert.
- Man kann Dokumente zu Anträgen hochladen und verwalten. Es gibt neue Rechte für das Hochladen und globale Bearbeiten von Dokumenten.

**Weitere Verbesserungen**:

- Verbesserte Darstellung und Verwaltung von Projektbeteiligten, inkl. einer Erweiterung des Rollenvokabulars.
- Optimiertes Layout und übersichtlichere Struktur auf den Bearbeitungsseiten.
- Viele Filter- und Statistikfunktionen (z.B. Suche nach Projekttypen, Förderstatus) wurden erweitert.
- Es gibt die Möglichkeit, bei Erstellung oder Bearbeitung eines Projektes bestimmte Nutzer oder Rollen zu benachrichtigen, entweder direkt in OSIRIS über das neue Benachrichtigungssystem oder per E-Mail.

<!-- - Projekte: Hier können mehrere Projekte ausgewählt werden, die mit der Aktivität verknüpft sind.
- KDSF-FKK: Hier kann die KDSF-Forschungsfeldklassifikation ausgewählt werden, die mit der Aktivität verknüpft ist. -->

#### <i class="ph ph-book-bookmark"></i> Darstellung und Sichtbarkeit von Aktivitäten

- Neue **Templates**:
  - Es wurden neue Templates für die Formatierung von Aktivitäten hinzugefügt, insbesondere für Autor:innen und Herausgeberschaften. Dadurch lassen sich auch Autorenschaften im APA-Format und andere Stile einfacher darstellen. Im Bereich Inhalte > Template-Baukasten findet ihr eine Anleitung.
  - Es wurden auch weitere Möglichkeiten hinzugefügt, DOIs auszugeben, z.B. als Link oder nur als Text. Diese können ebenfalls im Template-Baukasten gefunden werden.
- Es ist nun möglich, die Formatierung der affiliierten Autor:innen anzupassen. In den allgemeinen Einstellungen gibt es dazu ein Dropdown-Menü, mit dem ihr anpassen könnt, ob affiliierte Autor:innen fett, kursiv oder unterstrichen dargestellt werden sollen, oder eine Mischung davon.
- Es ist nun möglich, die Sichtbarkeit einzelner Aktivitätskategorien anzupassen. Dazu geht ihr auf Inhalte > Aktivitäten, wählt eine Kategorie (z.B. Publikationen) und klickt auf "Bearbeiten". Dort könnt ihr die Rolle einstellen, die Aktivitäten dieser Kategorie sehen darf. Standardmäßig ist dies auf "Alle" gesetzt, sodass alle Aktivitäten dieser Kategorie für alle Nutzer:innen sichtbar sind. Bitte beachtet, dass Nutzende ihre eigenen Aktivitäten immer sehen können, unabhängig von der Sichtbarkeitseinstellung. Diese Einstellung ist nur auf Kategorie-Ebene möglich, nicht auf Typen-Ebene.
- Es ist nun möglich, den Upload von Dokumenten für einzelne Aktivitäts-Kategorien zu deaktivieren. Diese Einstellung findet ihr ebenfalls unter Inhalte > Aktivitäten, wenn ihr eine Kategorie bearbeitet. Standardmäßig ist der Upload für alle Kategorien aktiviert. Wenn er deaktiviert ist, wird der Upload-Button in der Detailansicht der Aktivität nicht mehr angezeigt und es können keine Dokumente hochgeladen werden. Vorhandene Dokumente werden nicht gelöscht, werden aber auch nicht mehr angezeigt.
- Neues Widget für die Auswertung von Aktivitäten: **Konfetti-Timeline**. Das Widget ist im Moment bei Forschungsbereichen und Organisationseinheiten verfügbar, wird aber in Zukunft auch bei anderen Entitäten verfügbar sein. Es zeigt die Aktivitäten in einer Zeitachse an und ermöglicht es, nach Kategorien zu filtern. Der Filter wirkt sich auch auf die darunter liegende Tabelle mit den Aktivitäten aus.
- Es wurde ein neuer Knopf hinzugefügt, mit dem der formattierte Eintrag der Aktivität in die Zwischenablage kopiert werden kann. Dies ist besonders nützlich, um schnell Informationen zu teilen.
- Des Weiteren wurden weitere Datenfelder für Aktivitäten hinzugefügt:
  - Organisation: Hier kann via ROR eine Organisation ausgewählt werden, die an der Aktivität beteiligt ist. Es gibt auch eine Möglichkeit, nur den Ort der Organisation als Template auszulesen.
  - Organisationen: Das gleiche wie oben, aber hier können mehrere Organisationen ausgewählt werden.

#### <i class="ph ph-calendar"></i> Verbessertes Event-Management

- Es wurde eine Möglichkeit hinzugefügt, Events zu bearbeiten.
- Das User Interface bei der Anzeige von Events wurde verbessert.
- Es gibt nun eine Möglichkeit, direkt auf der Event-Seite eine neue Aktivität anzulegen
- Es wurde neu eine Beschreibung (mit Formatierungsmöglichkeiten) und ein Eventtyp hinzugefügt. Mögliche Typen lassen sich über das Vokabular definieren. Der Typ kann auf der Übersichtsseite zum Filtern verwendet werden.
- Forschungsbereiche können jetzt auch bei Events hinzugefügt werden. Ein entsprechender Filter wurde ebenfalls hinzugefügt.
- Es gibt jetzt einen Filter für das Jahr, in dem das Event stattfindet.
- Über den Events gibt es jetzt eine neue Visualisierung, die eine Timeline der Events anzeigt. Diese kann nach Jahr gefiltert werden. Die Timeline ist interaktiv und ermöglicht es, direkt zu den Events zu springen.

#### <i class="ph ph-puzzle-piece"></i> Forschungsbereiche

- Forschungsbereiche können jetzt auch zu Organisationseinheiten hinzugefügt werden.
- Das Web-Interface für die Darstellung von Forschungsbereichen wurde verbessert und folgt jetzt dem Tab-basierten Ansatz, der auch an vielen anderen Stellen verwendet wird.
- Bei den Forschungsbereichen wurden neue Abbildungen hinzugefügt: es gibt jetzt eine Netzwerkansicht mit allen verknüpften Autor:innen, eine Wordmap und eine Konfetti-Timeline.
- Bei den mit Bereichen verknüpften Entitäten (z.B. Aktivitäten): Wenn keine Forschungsbereiche existieren, wird das Forschungsbereich-Filterwidget nicht mehr angezeigt.

#### <i class="ph ph-map"></i> Forschungsreisen

- Es wurde ein neues Addon hinzugefügt, das es ermöglicht, Forschungsreisen zu analysieren.
- Um dieses Addon zu aktivieren, müsst ihr folgende Schritte durchführen:
  1. Im Admin-Bereich "Inhalte" einen Aktivitätstyp anlegen, der die ID `travel` hat. Dieser Typ wird dann für die Forschungsreisen verwendet. Der Name des Aktivitätstyps kann frei gewählt werden und bestimmt auch, wie die Visualisierung in der Seitennavigation heißt.
  2. Diesem Typen müssen die folgenden Felder zugeordnet werden:
     - `date-range`
     - `countries` oder `country` (je nachdem, ob ihr mehrere Länder oder nur ein Land pro Reise haben wollt)
     - natürlich `authors` oder `scientist`, je nachdem ob eine Person oder eine Gruppe von Personen die Reisen unternimmt
  3. In den generellen Einstellungen unter "Features" das Addon "Forschungsreisen" aktivieren.

#### <i class="ph ph-code"></i> Weitere Verbesserungen und Bugfixes

- Es wurde eine neue Seite zu den Einstellungen hinzugefügt, die es ermöglicht, den Footer der Seite anzupassen. Hier können Impressum, Datenschutzerklärung und Links zu externen Ressourcen hinzugefügt werden.
- Sperren von Aktivitäten wurde verbessert:
  - das Sperren von Aktivitäten ist jetzt einfacher zu finden (alle Aktivitäten > Sperren)
  - Du kannst jetzt eine einzelne Aktivität sperren und entsperren (auf der Aktivitätsseite; nur Benutzer mit dem Recht, Aktivitäten zu sperren)
- Die Vorschau für Portfolios wurde überarbeitet und spiegelt jetzt mehr wieder, wie die Portfolio-Seite tatsächlich aussehen würde. Außerdem greift die Vorschau jetzt auf die tatsächlich von der Portfolio-API ausgelieferten Daten zurück, weshalb sie nun genauer ist und nicht mehr Dinge zeigt, die nicht dargestellt werden sollen.
- Wenn man eine Aktivität über eine DOI abgerufen hat und den Typ ändert, werden die Daten aus der DOI jetzt korrekt übernommen.
- die Controlling-Seite wurde entfernt, da sie teilweise veraltete Informationen enthielt
- Email-Einstellungen können jetzt korrekt gespeichert werden und SMTP wird vollumfänglich unterstützt.
- Der visuelle Stil von allen Mitteilungen wurde verbessert
- Globale Steuerung der quartalsweisen Berichtspflicht (Controlling) möglich (standardmäßig aktiviert).
- Diverse kleinere Layout- und Darstellungsoptimierungen (u.a. Profile, Notifications, Listenansichten).
- Verbesserungen bei der Filterung und Sortierung von Projekten, Anträgen, Aktivitäten und Infrastrukturen.
- In der Übersicht über Aktivitäten, Infrastrukturen, Projekten und Anträgen zeigen kleine Zahlen an den Filtern nun an, wie viele Einträge es in den jeweiligen Kategorien gibt.
- Probleme mit der Sichtbarkeit bei der Online-Stellung von Aktivitäten korrigiert.
- Wenn nur die englische Bezeichnung für Features gesetzt wird, wird auch auf deutsch nun die englische Bezeichnung angezeigt.
- Fehlerhafte Anzeigen von Organisationen und Partnern in Listen behoben.
- Rechteverwaltung für Infrastruktur-Bearbeitungen verbessert, inklusive "edit-own"-Recht.
- Verbesserte Verwaltung von Organisationen (Löschen und Bearbeiten).
- Inaktive Nutzer:innen werden bei Auswahllisten nun nach unten sortiert.
- Mehr Felder für die erweiterte Suche hinzugefügt.
- Sidebar-Navigation im Admin-Bereich verbessert.
- Ein Problem wurde gefixt, durch das die Jahre in Metriken dupliziert wurde, wenn man aktualisiert hat
- In der Liste der Journale wird jetzt der neuste JCR-Impact factor gezeigt
- Im CV wird nun die Korrekte Sprache für die Überschriften verwendet. Außerdem wurden die teils hard-gecodeten Kategorien durch die flexiblen Kategorien ersetzt.
- Der Name der Aktivitätskategorie in "Mein Jahr" wird nun korrekt angezeigt
- In "Mein Jahr" werden die Aktivitätskategorien nun korrekt sortiert
- Typen lassen sich ebenfalls sortieren
- Es wurde ein Problem behoben, durch das Journale nicht in der Tabelle aufgelistet wurden, wenn sie keine Impact Faktoren hatten
- Ein Problem wurde behoben, durch das bei Gruppen nicht die korrekten Aktivitäten angezeigt wurden
- Im Profil werden die bestätigten Quartale jetzt in der Tabelle mit den Details angezeigt
- Man wird auf der Profilseite eines geteilten Profils jetzt nicht mehr nach Passwort-Anpassungen gefragt
- Wenn Organisationseinheiten keine Farbe (bzw. schwarz) zugewiesen wurde, wird für Abb. jetzt eine Auswahl an Standardfarben verwendet
- Ein Bug wurde behoben, durch den man die Leitung einer Organisationseinheit nicht löschen konnte
- Es wurde ein Bug behoben, wodurch die Tabellensuche beim Neuladen einer Aktivität nicht mehr funktionierte, wenn Leerzeichen im Suchbegriff waren
- Die Organisationseinheiten sollten jetzt überall in der korrekten Reihenfolge angezeigt werden, falls sie sortiert wurden
- Es wurde ein Fehler behoben, durch den gewisse DOIs von DataCite nicht korrekt verarbeitet wurden

---

<time datetime="2025-04-13">13.04.2025</time>
<a class="anchor" href="#version-1.4.3" id="version-1.4.3"></a>

## Version 1.4.3

#### <i class="ph ph-stack"></i> Journale und Impact Faktoren

- Die automatische Synchronisation der Metriken kann nun deaktiviert werden. Ihr findet eine Einstellung im Admin-Bereich unter "Features".
- Es wurde eine neue Seite hinzugefügt, auf der die Metriken eines Jahres für alle Journale aktualisiert werden können. Ihr findet sie mit den Bearbeitsrechten der Journale in der Übersicht der Journale.
- Die Metriken für das Jahr 2024 sind nun über unsere OSIRIS-API verfügbar und können automatisch aktualisiert werden.
- Die Quartile können nun auch manuell hinzugefügt werden, für den Fall, dass ihr sie nicht über die API aktualisieren wollt.
- Es wurde ein Graph hinzugefügt, der die Quartile der letzten Jahre anzeigt. Dieser ist in der Detailansicht des Journals zu finden.
- Die Kategorien der Journale können nun ebenfalls manuell bearbeitet werden. Dies kann in der Detailansicht des Journals gefunden werden.

---

<time datetime="2025-04-03">03.04.2025</time>
<a class="anchor" href="#version-1.4.2" id="version-1.4.2"></a>

## Version 1.4.2

#### <i class="ph ph-chart-line-up"></i> Statistiken

Es wurden neue Statistiken hinzugefügt, die ähnlich wie die Statistik der Infrastrukturen funktionieren. Statistiken wurden für Aktivitäten und Publikationen, Projekte, und Lehrveranstaltungen hinzugefügt

- Die Statistiken sind in den jeweiligen Übersichtseiten zu finden
- Eine ganze Reihe vorgefertigter Statistiken sind bereits vorhanden, inklusive einiger cooler Abbildungen

#### <i class="ph ph-lock-key-open"></i> Module: Open Access und politische Beratung

- Dem Open Access-Modul wurde "Diamond" als weitere Option hinzugefügt
  - Diamond wird auch über de DOI ausgeliefert und somit automatisch ausgefüllt
- Außerdem wurde ein neues Feld für politische und soziale Beratungsbeiträge hinzugefügt
  - Dieses Feld ist für die Paktabfrage der GWK relevant

#### <i class="ph ph-globe"></i> Länder

- Länder können jetzt semi-automatisch aktualisiert werden
- Dafür wird eine aktuelle Liste der Länder [heruntergeladen](https://stefangabos.github.io/world_countries/) und in OSIRIS importiert
- Um die Liste zu aktualisieren und einen Überblick über die aktuell vorhandenen Länder zu bekommen, gibt es eine neue Seite im Admin-Bereich "Generelle Einstellungen"

#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

- Es wurde ein seltsames Verhalten mit dem Login beim Auth-Addon behoben
- In der Autorenliste wird jetzt kein Link mehr gezeigt, wenn kein Nutzer verknüpft ist

---

<time datetime="2025-04-03">03.04.2025</time>
<a class="anchor" href="#version-1.4.1" id="version-1.4.1"></a>

## Version 1.4.1

#### <i class="ph ph-building-office"></i> Organisationen

- Es wurden externe Organisationen als eigene Entität hinzugefügt
- Diese sind mit **ROR** verknüpft und können über die ROR-ID automatisch angelegt werden
- Die Organisationen können mit Projekten und Verbundinfrastrukturen verknüpft werden
- Es gibt eine neue Übersichtsseite für Organisationen, auf der alle Informationen und verlinkte Details dargestellt sind
- Wenn ihr das Feature neu hinzufügt, werden alle Organisationen, die in OSIRIS angelegt sind, automatisch als externe Organisationen angelegt. Diese können dann bearbeitet werden. <i class="ph ph-warning text-signal"></i> Vergesst bitte nicht, dass auch neue Rechte für die Bearbeitung von Organisationen vergeben werden müssen.

#### <i class="ph ph-cube-transparent"></i> Forschungsinfrastrukturen

- Es können nun Forschungsinfrastrukturen angelegt und bearbeitet werden
- Datenfelder basieren auf dem brandneuen **KDSF 2.0**
- Aktivitäten und Personen lassen sich verknüpfen, bei Personen können auch Zeiträume, Rollen und VZÄs angegeben werden
- Statistiken können pro Jahr angelegt werden, um die Entwicklung der Infrastruktur zu zeigen
- Als "Reporter" markierte Personen werden auf ihrer Profilseite darauf hingewiesen, die Daten einmal jährlich zu aktualisieren
- Eine Übersichtsseite mit allen Statistiken der Infrastrukturen wurde hinzugefügt. Damit lassen sich alle Beispielabfragen des KDSF durchführen.
- Die Forschungsinfrastruktur wird im Profil der Person angezeigt
- Eine Infrastruktur kann als Verbundinfrastruktur angelegt werden
  - Eine Verbundinfrastruktur kann mit den kooperierenden Einrichtungen verknüpft werden
  - Auf der Statistik-Seite wird dann eine Übersicht über alle kooperierenden Einrichtungen angezeigt
- Infrastrukturen werden über die API ausgeliefert
- <i class="ph ph-warning text-signal"></i> Vergesst bitte nicht, dass auch neue Rechte für die Bearbeitung von Forschungsinfrastrukturen vergeben werden müssen.

#### <i class="ph ph-table"></i> Pivot-Tabellen und Diagramme

- Es wurde ein neues Modul hinzugefügt, mit dem Pivot-Tabellen und Diagramme erstellt werden können
- Die Daten können nach verschiedenen Kategorien gruppiert und aggregiert werden, um Trends und Muster zu erkennen
- Die Daten können in verschiedenen Diagrammen dargestellt werden, z.B. Balken-, Linien- und Tortendiagramme

#### <i class="ph ph-gear"></i> Kleinere Verbesserungen

- In der erweiterten Suche können jetzt auch Spalten angezeigt werden, die eigentlich eine Liste sind, wie zum Beispiel die SWS von Autor:innen
- Wenn eine neue Aktivitätskategorie angelegt wird, übernimmt der erste Typ automatisch einige Infos der Kategorie
- Das Datenfeld "Status" wurde überarbeitet, sodass es jetzt universeller eingesetzt wird
  - Es wurde der Status "in Vorbereitung" hinzugefügt
  - Es wurde eine Überprüfung/Fehlermeldung hinzugefügt, wenn der Status "in Vorbereitung" ist, das Startdatum aber in der Vergangenheit liegt
  - Fehlermeldungen und Übersichten wurden so überarbeitet, dass sich das Feld "Status" auch für andere Aktivitäten verwenden lässt und nicht nur für Abschlussarbeiten
- Es wurde den Custom Fields jetzt auch die Möglichkeit hinzugefügt, eine URL anzulegen, die dann auch als Link dargestellt wird
- Die Rolle "Admin" wird im Rollen und Rechte-Bereich jetzt immer als letzte Spalte angezeigt

#### <i class="ph ph-bug"></i> Bug Fixes

- Es wurde ein Fehler behoben, durch den man eine Person nicht mehr inaktiv setzen konnte
- Die Darstellung von Forschungsbereichen wurde im Profil und in der Aktivitätsübersicht verbessert
- Es wurde ein Bug in der Portfolio-API behoben, durch den die Daten nicht korrekt ausgeliefert wurden
- Es wurde ein Fehler behoben, durch den das Profilbild einer Person nicht gelöscht werden konnte
- Es wurde ein Fehler behoben, durch den einige Filter in der erweiterten Suche nicht korrekt funktionierten (insbesondere Open Access und Open Access Status)
- Es wurde ein Fehler behoben, durch den man auf der Seite "Alle Aktivitäten" nicht mehr nach Einheiten filtern konnte
- Namen in den Brotkrumen werden jetzt abgekürzt, wenn sie zu lang sind
- Wenn ein Event keinen Link hat, wird jetzt auch kein Link mehr angezeigt
- Die Liste von Aktivitäten im Download-Bereich passt sich jetzt euren Einstellungen an
- Beim Klick auf den dunklen Hintergrund hinter einem Popup scrollt die Seite nicht mehr nach oben
- In der Hierarchie-Ansicht der Organisationseinheiten wird jetzt im englischen Interface auch der englische Name der Einheit angezeigt

### Für Administrator:innen und Systembetreiber:innen

<div class="alert danger">
<h5 class="title">
<i class="ph ph-warning"></i> Wichtige Änderungen für Administrator:innen
</h5>
In diesem Update werden neue Einstellungen für die LDAP-Synchronisation eingeführt und für Maintenance und Testsysteme hinzugefügt.
Diese können nur in der PHP-Konfiguration gesetzt werden und sind nicht im Admin-Bereich zu finden. Die neuen Einstellungen sind weiter unten beschrieben.
</div>

#### <i class="ph ph-lock-key-open"></i> LDAP Sync

- Es wurde ein neues LDAP-Interface hinzugefügt, das einige Verbesserungen in der Nutzer-Authentifizierung bietet und die Synchronisation von Nutzern mit LDAP ermöglicht
- Es können jetzt auch Attribute aus LDAP in OSIRIS gespeichert werden, die dann nicht mehr manuell bearbeitet werden können. Diese Attribute werden im Admin-Bereich definiert und sind dann nur noch lesbar.
- Es wurde ein Skript hinzugefügt, das die Synchronisation von Nutzern mit LDAP ermöglicht. Es kann manuell ausgeführt oder als CRON-Job gestartet werden. Im Adminbereich kann der Zeitstempel der letzten Synchronisation eingesehen werden.
- Außerdem wurde ein Problem (vorläufig) behoben, durch das beim Ändern des Usernamens in LDAP ein neuer Nutzeraccount angelegt wurde. Die einzigartige Nutzer-ID wird jetzt ebenfalls gespeichert und verwendet, um den Nutzer zu identifizieren. Im Moment wird in OSIRIS so getan, als ob der Nutzername noch immer der alte Nutzername ist. Das wird in einer späteren Version behoben, indem der Nutzername in OSIRIS ebenfalls geändert wird. Zurzeit ist dies schwierig, da der Nutzername in vielen Stellen verwendet wird.

#### <i class="ph ph-code"></i> Admin-Einstellungen für den Betrieb und LDAP

- <code class="code">LIVE</code>: true/false (default: false) - Wenn LIVE auf false gesetzt wird, wird unterhalb des OSIRIS-Logos ein Hinweis angezeigt, dass es sich um ein Testsystem handelt. Dies ist wichtig, damit Nutzer:innen nicht versehentlich ihre Daten in einem Testsystem eingeben.
- <code class="code">MAINTENANCE</code>: true/false (default: false) - Wenn MAINTENANCE auf true gesetzt wird, wird anstelle der Brotkrumen im Header ein Hinweis angezeigt, dass sich das System in Wartung befindet und keine Daten eingegeben werden sollen.
- <code class="code">OPEN_LDAP</code>: true/false (default: false) - Falls ihr statt Microsoft AD einen OpenLDAP-Server verwendet, könnt ihr hier das System auf OpenLDAP umstellen. Dies ist wichtig, da viele Felder anders benannt sind und die Authentifizierung wie bei über Microsoft AD funktioniert.
- <code class="code">LDAP_USE_SSL</code>: true/false (default: false) - Wenn ihr LDAP_USE_SSL auf true setzt, wird die Verbindung zu LDAP über SSL hergestellt.
- <code class="code">LDAP_USE_TLS</code>: true/false (default: false) - Wenn ihr LDAP_USE_TLS auf true setzt, wird die Verbindung zu LDAP über TLS hergestellt.

#### <i class="ph ph-shipping-container"></i> Docker Support

- Danke an unseren Contributor, Paul Gaida, der OSIRIS in Docker verpackt hat
- Eine komplette Anleitung zum Einrichten von OSIRIS in Docker findet ihr [hier](https://github.com/OSIRIS-Solutions/osiris/blob/master/docker.md)

---

<time datetime="2025-02-12">12.02.2025</time>
<a class="anchor" href="#version-1.4.0" id="version-1.4.0"></a>

## Version 1.4.0

#### <i class="ph ph-siren"></i> Wichtige Änderungen am Interface

- Das Menü wurde etwas umstrukturiert, um jetzt mehr Sinn zu ergeben
- Die erweiterte Suche wurde als inline-Element in die jeweilige Navigation integriert
- Der Menüpunkt "Meine Aktivitäten" ist zurück (nur Wissenschaftler-Ansicht)
- Die User Experience wurde verbessert, wenn man versucht hat, eine Seite zu erreichen, obwohl man nicht eingeloggt ist
- gaaaaaanz viele kleine Verbesserungen am Interface, z.B. in der Expertise-Suche

#### <i class="ph ph-puzzle-piece"></i> Forschungsbereiche

- Forschungsbereiche wurden als neue Entität hinzugefügt
- Forschungsbereiche können zentral eingeschaltet, definiert und danach zu anderen Entitäten hinzugefügt werden, z.B. zu Aktivitäten, Projekten und Personen
- Es wurden Detailseiten für alle Forschungsbereiche hinzugefügt, auf denen alle Informationen und verlinkte Details dargestellt sind
- Es wurden zu allen verlinkbaren Aktivitäten Filter hinzugefügt
- Wie genau die Forschungsbereiche genannt werden, kann ebenfalls im Admin-Bereich definiert werden
- <i class="ph ph-warnung"></i> Bitte beachte, dass die Forschungsbereiche zurzeit noch nicht über die API ausgeliefert werden. Dies ist für den kommenden Release 1.5.0 geplant.

#### <i class="ph ph-users-three"></i> Organisationseinheiten

- Einheiten können jetzt als inaktiv markiert werden und werden dann nicht mehr in Filtern etc. angezeigt
- Es wurden neue Felder angelegt, beispielsweise kann nun eine interne ID vergeben werden
- Das Interface für die Bearbeitung von Einheiten wurde verbessert; insbesondere die Zuordnung von Personen zu Einheiten
- Der Graph für die Zusammenarbeit innerhalb einer Einheit wurde deutlich verbessert und zeigt jetzt auch individuelle Arbeiten an

<i class="ph ph-clock"></i> **Zeitaufgelöste Organisationseinheiten**

- Organisationseinheiten können jetzt zeitlich aufgelöst werden, d.h. es können Start- und Enddaten für die Zugehörigkeit von Personen zu Organisationseinheiten angegeben werden
- Die Zugehörigkeit zur Einheit wird auch bei den Aktivitäten gespeichert, wenn eine Person also die Einheit wechselt, bleiben die zuvor angelegten Aktivitäten weiterhin der ursprünglichen Einheit zugeordnet
- Aktivitäten und Projekte werden automatisch der Einheit zugeordnet, die die Person zum Zeitpunkt der Aktivität hatte. Dies lässt sich aber auch manuell ändern und wird dann nicht mehr automatisch aktualisiert
- Es wurde eine neue Seite hinzugefügt, auf der die zeitliche Entwicklung der Einheiten dargestellt wird und auf der die Zugehörigkeit von Personen zu Einheiten bearbeitet werden kann
- Die Darstellung der Einheiten wurde global verbessert, beispielweise im Profil, in der Aktivitäten- und Projekte-Übersicht

#### <i class="ph ph-magnifying-glass-plus"></i> Verbesserungen bei der Suche und Darstellung Aktivitäten

**Die Detailansicht von Aktivitäten**

- Eine Reihe von wichtigen Meta-Informationen wird jetzt ganz oben angezeigt, darunter das Datum/Zeitraum, die Metriken (Impact und Quartil), Information über die Zusammenarbeit und Affiliation, sowie ggf. die Forschungsbereiche
- Das Interface wurde generell verbessert und ist jetzt übersichtlicher

**Alle Aktivitäten**

- Im Filter lassen sich Kategorien lassen nach dem Auswählen jetzt noch weiter als Typen differenziert filtern

**Erweiterte Aktivitäten-Suche**

- Das Interface wurde deutlich verbessert und ist jetzt übersichtlicher. Die gespeicherten Abfragen sind jetzt in einem Popup zu finden und zeigen nun mehr Informationen an. Der Filter ist ebenfalls in ein eigenes Fenster gewandert. Dadurch wird die Suche deutlich intuitiver und einfacher zu bedienen.
- **Neu**: Es können jetzt Spalten für die Anzeige ausgewählt werden.
  - Die Liste enthält dabei alle Felder und jeweils Icons, um zu kennzeichnen, in welchen Aktivitätstypen diese vorkommen.
  - Die Tabelle lässt sich inklusive der Spalten sortieren und filtern
  - Die Tabelle lässt sich inklusive aller gewählten Spalten als Excel-Datei herunterladen
- Filter:
  - Es wurde ein neuer Filter für das Start- und Enddatum hinzugefügt, durch den sich Zeiträume jetzt auch leicht mit "größer" und "kleiner" definieren lassen
  - Es wurde ein Filter für die Forschungsbereiche hinzugefügt
  - Der Filter für die Organisationseinheiten wurde verbessert
- Ein Fehler wurde behoben, durch den die Experten-Suche nicht gespeichert werden konnte
- Die Suche ist jetzt auch über die Seite "Alle Aktivitäten" erreichbar

#### <i class="ph ph-calendar-dots"></i> Kalender

- Es wurde ein Kalender hinzugefügt, der alle Aktivitäten, Gastaufenthalte und Events anzeigt
- Es kann auch in der Organisationsstruktur nach oben navigiert werden, um die Aktivitäten der übergeordneten Einheiten zu sehen

#### <i class="ph ph-text-aa"></i> Rich Text-Editoren und mehr Übersetzungen

- Mehr Felder lassen sich jetzt zweisprachig definieren:
  - Forschungsinteressen von Personen
  - Forschungsinteressen von Gruppen
  - Titel, Untertitel und Public Abstract von Projekten
- Es wurde ein Rich-Text Editor hinzugefügt, der das bisherige Markdown ersetzt
  - Bei der Beschreibung von Einheiten, ihren Forschungsbereichen und bei der öffentlichen Beschreibung von Projekten
  - Bilder können darüber direkt eingefügt werden (bitte auf die Größe achten)
  - Links und Formatierungen lassen sich jetzt einfacher bearbeiten

#### <i class="ph ph-newspaper-clipping"></i> Metriken von Journalen

- Die Metriken (z.B. 2-Jahres-Impact Faktor) können jetzt über die neue zentrale OSIRIS-API abgerufen werden
- Es gibt auch eine Funktion, mit der alle Metriken auf einmal abgerufen werden können
- Um Metriken für ein Journal zu aktualisieren, gehe auf die Seite des Journals und drucke auf _Metriken aktualisieren_

#### <i class="ph ph-tree-structure"></i> Verbesserung von Projekten

- Beantragte Projekte sind jetzt auf dem eigenen Profil zu sehen

**Projektliste**

- In der Projektliste sieht man jetzt nicht nur die eigenen sondern auch noch alle anderen bewilligten Projekte
- Die Ansicht ist komplett überarbeitet worden
- Filtermöglichkeiten wurden deutlich verbessert und erweitert
- Forschungsbereiche wurden hinzugefügt
- Eine Liste aller Projekte lässt sich jetzt als Excel-Datei herunterladen
- Ein Link zur erweiterten Suche wurde hinzugefügt

**Projektübersicht**

- Die Dauer und öffentliche Sichtbarkeit werden jetzt bei den Metadaten dargestellt
- Eine Übersicht über alle beteiligten Orga-Einheiten wurde hinzugefügt
- Das Projekt kann jetzt **als Word-Datei exportiert** werden. Wie man als Admin das Word-Template anpasst, findet ihr in den Konfigurationsanweisungen auf der OSIRIS-Webseite.

**Kooperationspartner**

- Es wurden zwei weitere Möglichkeiten hinzugefügt, um Partner anzulegen: entweder direkt über die ROR-ID oder durch den Upload von CSV-Dateien

**Erweiterte Suche**

- Eine komplett neue erweiterte Suche wurde hinzugefügt
- Unterstützung für alle Funktionen, die die Aktivitäten-Suche ebenfalls bietet, inkl. Speichern von Abfragen und der Experten-Suche
- <i class="ph ph-warnung"></i> Bitte beachte, dass du hier noch keine Spalten auswählen kannst. Dies wird in einer nächsten Version hinzugefügt.

#### <i class="ph ph-gear"></i> Verbesserte Nutzereinstellungen

Die Nutzereinstellungen sind in den letzten Monaten gewachsen und sehr unübersichtlich geworden. Deshalb wude das ganze Interface stark überarbeitet.

- Die einzelnen Abschnitte sind jetzt in einzelne Reiter unterteilt
- Hier ist jetzt auch die Biographie (CV) und die Forschungsinteressen zu finden, die vorher etwas ausgegliedert waren.
- Das Interface zur Bearbeitung der Organisationseinheiten wurde nun wie bereits oben erwähnt in die neue Einheitsstruktur ausgelagert
- Die Forschungsbereiche wurden im Reiter "Organisation" hinzugefügt
- Im Reiter "Account" kann jetzt zudem das Password geändert werden (nur bei Auth-Addon)
- Es wurden neue Felder hinzugefügt, zum Beispiel eine Biographie, Forschungsinteressen (Freitext), Raumnummer, und eine interne ID
- Die Position einer Person kann nun entweder wie zuvor ein Freitext sein oder aus einer Liste ausgewählt werden. Die Liste kann im Admin-Bereich definiert werden.
- Personen können jetzt gelöscht werden. Dazu wurden neue Rechte im Admin-Bereich hinzugefügt. Bitte beachte, dass das Löschen von Personen nicht rückgängig gemacht werden kann und alle Daten der Person gelöscht werden, genau wie die Verknüpfung zu allen Aktivitäten, Projekten, Infrastrukturen, etc. Es gibt keine Möglichkeit, die Person wiederherzustellen.

#### <i class="ph ph-presentation-chart"></i> Events

- Es wurde eine eigene Seite für Events (zuvor Konferenzen) hinzugefügt
- Events können jetzt mit einem neuen Modul zu Aktivitäten hinzugefügt werden. Das Modul heißt `event-select` und wird bei allen Aktivitäten, die zu Events verknüpfen können, empfohlen. Wird ein Event aus dem Modul ausgewählt, werden folgende Werte vorausgefüllt:
  - Eventname (inkl. Verknüpfung zum Event)
  - Ort des Events
  - Start und Enddatum
- Es könne auch direkt in meine Aktivitäten neue Events angelegt werden, ohne die Seite zu verlassen. Die Aktivität wird dann automatisch damit verknüpft. An dieser Stelle kann man auch direkt als Teilnehmer des Events registriert werden.
- Events im eigenen Profil können jetzt ausgeblendet werden wenn kein Interesse besteht. Das dient der besseren Übersicht

#### <i class="ph ph-textbox"></i> Custom Fields

- Custom Fields können jetzt auch auf Englisch übersetzt werden
- Es ist nun möglich, Custom Fields im Nachhinein zu bearbeiten
- Es wurde eine Möglichkeit hinzugefügt, ein Select-Feld als Multi-Select zu definieren

#### <i class="ph ph-lock"></i> Sicherheit

- Für den Nutzen des User-Auth Addons (das ursprünglich nicht für Live gedacht war, aber doch von einigen genutzt wird), wird das Password jetzt getrennt von den Nutzerinfos gespeichert. Das führt zu höherer Sicherheit, da diese Collection für keine anderen Seiten sonst verwendet werden und auch nicht API-Reachable sind.
- Das Password wird jetzt auch nicht mehr im Klartext in der Datenbank gespeichert, sondern gehasht.
- Passwort zurücksetzen wurde ebenfalls deutlich verbessert und ist jetzt sicherer, da es nur noch über einen Link in der Email möglich ist.

**Wichtg:** Nutzer des Auth-Addons werden unbedingt aufgefordert auf 1.4.0 zu migrieren.

#### <i class="ph ph-clipboard-text"></i> Report Templates

- Die Berichte-Templates wurden weiter ausgebaut
- Die Vorschau wurde verbessert und das Vorschau-Jahr lässt sich jetzt auswählen
- Der Export als Word-Dokumente wurde gefixt
- TODO: Das Interface braucht immer noch ein bisschen Liebe.

#### <i class="ph ph-hand ph-fw"></i> Erweitertes Claimen von Aktivitäten

- Auf der eigenen Profilseite gibt es jetzt einen Knopf über den der "Claim"-Bereich verfügbar ist.
- Hier können ahand der eigenen Namen alle Aktivitäten, die noch einem nicht zugeordnet sind, zugeordnet werden

#### <i class="ph ph-gear"></i> Admin-Bereich

- Die generellen Einstellungen wurden überarbeitet und sind jetzt übersichtlicher
- Es ist jetzt möglich, die Farben des UIs anzupassen
- Es ist jetzt möglich, eigene Rollen anzulegen
- Es ist jetzt möglich, eine Liste mit möglichen Positionen anzulegen (für Personen)
- Es ist jetzt möglich, Nutzende zentral anzulegen (nur Auth-Addon)
- Im Bereich Rollen und Rechte ist der Header der Tabelle nun fixiert, sodass er beim Scrollen stehts zu sehen ist
<!-- - Es können nun über LDAP synchronisierte Attribute ausgelesen und in OSIRIS gespeichert werden. Diese werden ebenfalls in den generellen Einstellungen definiert. Auf diese Art und Weise festgelegte Attribute (zum Beispiel Raumnummer) können dann nicht mehr manuell in den Nutzereinstellungen bearbeitet werden. -->
- Es wurde ein neuer Template-Editor hinzugefügt (Beta), der bei der Formatierung von Templates hilft
- Es wurde eine Übersicht über alle vorhandenen Formularfelder hinzugefügt (Beta), die sowohl den Namen des Feldes enthält als auch das Aussehen und die entsprechenden gespeicherten Informationen.

#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

- Es werden jetzt alle Namen einer Person bei der Suche in der Personenliste berücksichtigt
- Ein Fehler wurde behoben, durch den bei der Pubmed-Suche immer die nächste Publikation hinzugefügt wurde
- Die Navigation auf der Profilseite wurde verbessert, sodass beim Nutzen der "Zurück"-Taste des Browsers automatisch das zuvor ausgewählte Fenster ausgewählt wird
- Ein Fehler wurde behoben, durch den Projekte nicht korrekt nach Laufzeit sortiert wurden
- Ein Fehler wurde behoben, durch den einige Infos (z.B. Forschungsinteressen und Links von Projekten) nicht zu Portfolio ausgeliefert wurden
- Es wurde das Coin Icon gefixt
- Es wurde ein Fehler behoben, durch den Template-Bausteine im Berichte-Editor nicht löschbar waren
- Die Bezeichnung "Epub" wurde überall einheitlich in "Online ahead of Print" umbenannt
- Untereinheiten können jetzt sortiert werden
- Bei der Eingabe der Google Scholar-ID wird diese jetzt auf Fehler überprüft, da dies immer wieder zu Problemen beim Importieren führte
- Es wurde ein Fehler behoben, durch den beim Ändern der ID einer Aktivitätskategorie alle Typen verloren gingen
- Es wurde ein Problem behoben, durch das Autor:innen mit diakritischen Zeichen nicht korrekt verknüpft wurden

---

<time datetime="2024-08-15">15.08.2024</time>
<a class="anchor" href="#version-1.3.6" id="version-1.3.6"></a>

## Version 1.3.6

#### <i class="ph ph-tree-structure"></i> Teilprojekte wurden hinzugefügt

- Einem Projekt können jetzt Teilprojekte hinzugefügt werden
- Teilprojekte erben einige Eigenschaften (Fördermittelgeber, Kooperationspartner, etc.) vom Elternprojekt
- Die Beziehungen werden in OSIRIS und in Portfolio dargestellt

#### <i class="ph ph-tree-structure"></i> Erste Schritte für Nagoya-Compliance

Es wurden erste Schritte unternommen, um Compliance mit dem [Nagoya-Protokoll](https://de.wikipedia.org/wiki/Nagoya-Protokoll) in OSIRIS einzufügen.

- Das Feature kann im Feature-Bereich eingeschaltet werden.
- Beim Eintragen eines Projekt können Herkunftsländer für biologisches Material angegeben werden
- Es gibt einen neuen Rechte-Bereich für Nagoya-Compliance
- Personen mit entsprechenden Rechten haben Zugriff auf den Nagoya-Bereich, in dem alle relevanten Projekte mit Ländern aufgeführt sind
- Weitere Entwicklungen (Status, Dokumente, etc.) folgen

#### <i class="ph ph-users"></i> Verbesserung im LDAP_Interface

- Es wurde ein LDAP-Filter für den Sync hinzugefügt
- Fehlermeldungen beim Login wurden verbessert
- Es wurde eine Möglichkeit hinzugefügt, mehr als 1000 Nutzende zu synchronisieren

#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

- Wenn man die Seite "Alle Aktivitäten" filtert, die Seite verlässt und über den Zurück-Knopf des Browsers zurückkehrt, wird der letzte Status der Suche/Filter/Seitenauswahl wiederhergestellt
- Probleme mit Stipendien wurden behoben
- Probleme mit dem Level und der Farbe von Organisationseinheiten wurden behoben
- Ein Problem wurde behoben, durch das Gruppenleiter ihre Gruppen nicht mehr bearbeiten konnten
- Es wurde ein Problem mit nicht öffentlich gezeigten Gruppen im Portfolio behoben
- Das Laden der Profilseite wurde deutlich verbessert.
- Dem DOI-Import wurde besseres Error-Handling hinzugefügt
- Es wurden Fehler behoben, die die Darstellung von Listen, z.B. ISSNs betrafen
- Diverse Bugs Portofolio betreffend wurden gefixt
  - Die Karte zeigt nun korrekt die Kooperationspartner
  - Beantragte Projekte werden nicht mehr gezeigt
  - Die ungleichmäßige Darstellung der Namen wurde gefixt
  - OSIRIS liefert keine sensiblen Nutzerdaten mehr aus (betraf Version 1.3.5)
- Es wurde ein Portfolio-FAQ hinzugefügt
- Aus Gründen der Datensicherheit wurden Nutzernamen an einigen Stellen unkenntlich gemacht.

---

<time datetime="2024-06-23">23.06.2024</time>
<a class="anchor" href="#version-1.3.5" id="version-1.3.5"></a>

## Version 1.3.5

#### <i class="ph ph-presentation-chart"></i> Überarbeitete Startseite und Konferenzen

Die Startseite wurde überarbeitet

- Auf der eigenen Profilseite gibt es jetzt den Reiter **News**
- Hier findet ihr alle wichtigen Neuigkeiten und Benachrichtigungen
- Warnmeldungen und Hinweise wurden hierher verschoben und verbessert
- Die Neuesten Publikationen des Instituts werden hier angezeigt

Der Startseite wurde der neue Bereich "**Konferenzen**" hinzugefügt.

- Man kann Konferenzen anlegen und so die Zusammenarbeit im Institut fördern
- Bei zukünftigen Konferenzen kann Interesse und Teilnahme bekundet werden und das Event kann für den Kalender als iCal exportiert werden
- Bei vergangenen Konferenzen kann nun ganz einfach ein neuer Beitrag (z.B. Poster oder Vortrag) hinzugefügt werden.

#### <i class="ph ph-code"></i> Anpassungen für OSIRIS Portfolio

- Eine neue API-Schnittstelle, die speziell auf Portfolio zugeschnitten ist
- Eine neue API-Dokumentation
- Profilbild ist jetzt per Default nicht mehr angezeigt
- Portfolio-Preview wurde verbessert

#### <i class="ph ph-student"></i> Neues Feature: Claim Authorship

Es wurde ein neues Feature hinzugefügt, mit dem Autor:innen ihre Autorenschaft beanspruchen können. Dazu gibt es in der Übersicht der Aktivität jetzt einen neuen Knopf bei Autor:innen, mit denen kein Nutzer verknüpft ist. Wenn eine Person eine Autorenschaft beansprucht, wird sie ihrem Profil hinzugefügt und der in der Autorenschaft verwendete Name wird ggf. der Liste der alternativen Namen hinzugefügt.

#### <i class="ph ph-code"></i> Besserer LDAP-Sync

Die Synchronisation zu LDAP hat jetzt ein Interface spendiert bekommen, über das man einzelne Nutzer:innen auswählen und ggf. blacklisten kann.

#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

- Bessere Erkennung von Artikeln, die mittels DOI hinzugefügt werden
  - Bessere Erkennung vom Veröffentlichungsdatum
  - Wenn die Seitenzahl nicht angegeben ist, wird alternativ die Artikelnummer verwendet (falls vorhanden)
- Die Filter in "Alle Aktivitäten" lassen sich nun wegklappen und sind auch auf mobilen Endgeräten besser verfügbar
- Style-Fixes und Verbesserungen
- Berechtigungen zur Bearbeitung von Gruppen wurden gefixt
- Neue Version von Phosphoricons <i class="ph ph-heart" title="Danke an die Autoren dieser fantastischen Bibliothek"></i>
- Die Ersteller von Projekten können sie jetzt auch bearbeiten
- Bei Projekten wurden Stipendien hinzugefügt

---

<time datetime="2024-05-23">23.05.2024</time>
<a class="anchor" href="#version-1.3.4" id="version-1.3.4"></a>

## Version 1.3.4

#### <i class="ph ph-users-three"></i> Seite für Organisationseinheiten

Organisationseinheiten haben jetzt eine überarbeitete Seite, die eine Übersicht über die folgenden Punkte bietet:

- Eine Übersicht, gepflegt von den leitenden Personen, die neben einem allgemeinen Beschreibungstext auch Forschungsinteressen enthält
- Mitarbeitenden Personen werden als Liste und als Interaktionsgraph dargestellt
- Publikationen und Tabellen der Organisationseinheit werden als Tabellen dargestellt
- Alle laufenden und abgeschlossenen Projekte der Organisationseinheit
- Eine Word cloud, die die Forschungsinteressen verschlagwortet
- Verknüpfung mit anderen Einheiten, z.B. über- und untergeordnete Einheiten, aber auch Zusammenarbeit als Chord-Diagramm

Bitte beachtet, dass sich diese Seite noch immer im Aufbau befindet und sich noch weiter ändern und (hoffentlich) verbessern wird.

#### <i class="ph ph-user-plus"></i> Personen zu Organisationseinheiten hinzufügen

Personen können jetzt (mit entsprechenden Rechten) direkt über die Seite der Organisationseinheit zur Einheit hinzugefügt werden.

#### <i class="ph ph-plus-circle"></i> Aktivitäten über die Projektseite verknüpfen

Um eine Aktivität mit einem Projekt zu verknüpfen, musste man bislang immer jede Aktivität einzeln auswählen und dann das Projekt. Weil das für später hinzugefügte Projekte sehr umständlich war, wurde ein Mechanismus hinzugefügt, um Aktivitäten schnell zu Projekten hinzuzufügen.

Dazu wählt ihr ein Projekt aus, klickt auf Aktivitäten und dann auf <span class="btn secondary small"><i class="ph ph-plus"></i> Aktivitäten verknüpfen</span>. Es öffnet sich ein Fenster, indem ihr Aktivitäten einfach durchsuchen könnt. Klickt die gewünschte Aktivität an und klickt dann auf Bestätigen.

#### <i class="ph ph-magnifying-glass-plus"></i> Überarbeitung der Erweiterten Suche

Die Erweiterte Suche wurde überarbeitet und bietet jetzt mehr Filtermöglichkeiten und eine verbesserte Benutzeroberfläche.

1. **Aggregationen**<br>
   Es wurden Aggregationen hinzugefügt, um Ihnen einen besseren Überblick über die Daten zu geben. Sie können jetzt Daten nach verschiedenen Kategorien gruppieren und aggregieren, um Trends und Muster zu erkennen.

2. **Suchen können jetzt gespeichert werden**<br>
   Sie können jetzt Ihre Suchanfragen speichern, um sie später wieder abzurufen. Dadurch sparen Sie Zeit und können häufig verwendete Suchen schnell wiederholen.

3. **Hinzufügen von Docs**<br>
   Es wurden eine Dokumentationen hinzugefügt, um dir bei der Nutzung des Systems zu helfen. Du kannst jetzt auf detaillierte Anleitungen und Informationen zu den verschiedenen Funktionen zugreifen. Die Dokumentation findest du [hier](docs/search).

#### <i class="ph ph-book"></i> Documentation in English

Parts of the documentation has been translated into English. You can now access the English version of the documentation to learn more about the different features of the system, e.g. on how to add activities, how to search and what the warnings mean.

#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

Es wurde ein Problem behoben, durch das bei der Nutzung der LDAP-Schnittstelle ein neuer Nutzeraccount angelegt wurde, wenn der Nutzername in Groß- und Kleinschreibung vom gespeicherten Nutzernamen abgewichen ist. Es sollte nun immer der im LDAP hinterlegte Accountname für die Verifizierung gespeichert und verwendet werden.

Es wurde ein Problem behoben, durch das Aktivitäten mit einer rein numerischen ID nicht bearbeitet werden können.

Es gab außerdem einige UI-Improvements, z.B. beim Festlegen der Leitung einer Organisationseinheit.

---

<time datetime="2024-04-15">15.04.2024</time>
<a class="anchor" href="#version-1.3.3" id="version-1.3.3"></a>

## Version 1.3.3

#### <i class="ph ph-clock-counter-clockwise"></i> Historie

Es wurde eine überarbeitete Historie zu Aktivitäten hinzugefügt, die jetzt alle Ereignisse aufnimmt (zuvor nur jeweils das letzte Ereignis) und auch Änderungen speichert.
Sie ist auf der Detailseite einer Aktivität unter dem Reiter _Historie_ verfügbar.

---

<time datetime="2024-02-22">22.02.2024</time>
<a class="anchor" href="#version-1.3.2" id="version-1.3.2"></a>

## Version 1.3.2

#### <i class="ph ph-code"></i> API Changes und API-Key

Es gibt ein paar kleinere Änderungen an der API, hauptsächlich wurden mehr Filtermöglichkeiten hinzugefügt.

Außerdem gibt es jetzt **API-Keys** mit denen sich die API nach außen hin absichern lässt. Erstellt euch einen einzigartigen API-Key und sichert damit eure Daten vor unbefugtem Zugriff. Die Einstellungen dazu findet ihr in den allgemeinen Einstellungen im Admin-Bereich.

#### <i class="ph ph-book"></i> API Docs

In der Dokumentation ist ein neuer Reiter API Docs, unter dem die wichtigsten Endpunkte beschrieben sind.

---

<time datetime="2024-02-21">21.02.2024</time>
<a class="anchor" href="#version-1.3.1" id="version-1.3.1"></a>

## Version 1.3.1

In dieser Version sind ein paar Fixes zur vorherigen Version, sowie neue Custom fields enthalten.

#### <i class="ph ph-textbox"></i> Custom Fields

- definiert **Custom fields** im Admin-Bereich
- wählt den Typ des Feldes, legt Default-Werte fest und definiert Listen mit Auswahlmöglichkeiten
- Benutzt die "ID" des neuen Feldes, um es zu den Formularen hinzuzufügen. Geht dazu in den Aktivitäten-Konfigurator im Admin-Bereich und wählt eure eigenen Module aus (custom fields erscheinen ganz oben in der Sortierung)
- Benutzt die "ID" des neuen Feldes, um den Wert in Templates auszugeben
- Eure Custom Fields werden ebenfalls in der Erweiterten Suche angezeigt

---

<time datetime="2024-02-09">09.02.2024</time>
<a class="anchor" href="#version-1.3.0" id="version-1.3.0"></a>

## Version 1.3.0

#### <i class="ph ph-sparkle"></i> Neues Design

- OSIRIS erstrahlt im neuen Design, das jetzt noch moderner (und etwas weniger orange-lastig) ist.

#### <i class="ph ph-user-gear"></i> Biographie wurde hinzugefügt

- Position kann erfasst werden
- Forschungsinteressen können angegeben werden
- CV kann hinzugefügt werden (mit Download-Funktion)

#### <i class="ph ph-users-three"></i> Organisationseinheiten wurden hinzugefügt

- Die Hierarchie des Organigramms kann abgebildet werden
- Anzeige als
  - Karten
  - Hierarchie
  - Organigramm
- Einheiten anzeigen, hinzufügen, bearbeiten, löschen
- Eine Einheit kann einen oder mehrere Leiter:innen haben
- Eine Person kann mehreren Einheiten zugeordnet werden
- Übersichtsseite:
  - Beschreibung auf deutsch und englisch
  - Forschungsinteressen
  - Lister der Mitarbeitenden Personen
  - Übersicht aller Publikationen und anderer Aktivitäten
  - Projekte der Gruppe
  - Word Cloud
  - Zusammenarbeit mit anderen Gruppen (falls vorhanden)
- Zugangsbeschränkung und Rechte wurden hinzugefügt

#### <i class="ph ph-tree-structure"></i> Projekte wurden weiter ausgebaut

- Projekte lassen sich anzeigen, hinzufügen, bearbeiten, löschen, Personen lassen sich mit ihrer Rolle im Projekt verknüpfen
- Kooperationspartner verknüpfen (semi-automatisch dank ROR-Intergration)
  - Visualisierung auf einer Karte
  - Gesamtübersicht auf Karte
- Aktivitäten verknüpfen wurde verbessert:
  - Wenn in Crossref der Funder hinterlegt ist und die Fundingnummer existiert, wird das Projekt automatisch verknüpft
  - Download aller verknüpften Aktivitäten
  <!-- - *Metriken und Visualisierung* -->

#### <i class="ph ph-circles-three-plus"></i> Verschlagwortung von Aktivitäten ist möglich

- Rudimentäre Verknüpfung von Schlagwörtern (mit Links und Kategorien)
- Gruppierung nach Entität
- **Achtung**: Es handelt sich um einen sehr rudimentären Prototypen, der später noch weiter ausgebaut werden soll

#### <i class="ph ph-lightbulb"></i> Concepts wurden eingeführt

- Konzepte werden von [OpenAlex](https://docs.openalex.org/api-entities/concepts) bezogen
- Werden _automatisch_ zu Publikationen hinzugefügt
- Ansicht in der Aktivitätenseite
- Aggregation für Nutzer:innen in der Profilseite
- Gesamttabelle mit Suchfunktion
- Detailseiten mit Expert:innen und allen verknüpften Aktivitäten
  - Weitere Konzepte können zum Graph hinzugefügt werden

#### <i class="ph ph-user-switch"></i> Anmeldung von Gästen und externe Gästeformulare

- Anmeldung von neuen Gästen in OSIRIS
- Externes Gäste-Formular zur Ergänzung der Details und Kenntnisnahme der Belehrungen
- Verknüpfung weiterer Daten, z.B. Nutzer:in, Dokumente
  <!-- - *Emails werden an Betreuer und Verantwortliche geschickt* -->
  <!-- - *Im Profil des Betreuers finden sich Infos zu momentanen Gästen* -->
- Die Gästeliste ist zugangsbeschränkt
- **Achtung**: die Gästeliste ist noch nicht 100%ig fertig. Es fehlen noch Email-Benachrichtigungen und es können noch keine Dokumente und keine Schlüsselcodes hinterlegt werden.

#### <i class="ph ph-eye"></i> Port*folio*: das Forschungs-Portal

- Die Darstellung von Forschungsinformationen nach außen soll durch das Port*folio* möglich werden
- Vorschau-Seiten wurden hinzugefügt, um die Außendarstellung zu sehen
- Übersichtstabellen wurden hinzugefügt
- Es wurde begonnen, Port*folio* zu implementieren

#### <i class="ph ph-gear"></i> Verbessertes Admin-Dashboard

- Die Einstellungen werden jetzt in der Datenbank gespeichert
- Das Institutslogo wird nun in der Datenbank gespeichert, was in einigen Fällen Probleme mit Schreibrechten umgeht
- Features
  - Einzelne Features (z.B. Projekte) können jetzt zentral deaktiviert werden
  - Es gibt nun die Möglichkeit, Profilbilder in die Datenbank zu verlagern, was in vielen Fällen Probleme mit Schreibrechten umgeht
- Aktivitäten können jetzt besser und übersichtlicher aktualisiert werden
  - Die Menge Coins kann für jede Aktivität definiert werden
  - Automatisch generierte Beispiele wurden hinzugefügt
- Rechte und Rollensystem: Detaillierte Definition von Rollen und dazugehörigen Rechten

#### <i class="ph ph-student"></i> Verbesserung der Profilseite

- Bessere Struktur durch Navigation
- Tabellen und Graphen werden jetzt dynamisch geladen
- Tabellen enthalten jetzt alle Daten (paginiert und durchsuchbar)
- Neue Verknüpfungen und Graphen
  - Das Koautoren-Netzwerk ist hier zu finden
  - »Andere Aktivitäten« wurde umstrukturiert
  - Zeitstrahl für Projekte
  - Wordcloud basierend auf Abstract und Titel von Publikationen
- Neue Daten: Projekte und Konzepte
- Inaktivierung von Nutzer:innen jetzt detaillierter möglich

#### <i class="ph ph-folders"></i> Aktivitätenseite überarbeitet

- Bessere Struktur durch Navigation
- Organisationseinheiten eingepflegt
- Koautoren-Visualisierung
- Verknüpfte Projekte
- Verknüpfte Forschungsdaten
- Verknüpfte Konzepte
- Link zur Vorschau-Seite

#### <i class="ph ph-suitcase"></i> Aktivitätenübersicht überarbeitet

- Neue Darstellung mit besseren Filtermöglichkeiten
- Download der Tabelle als Excel oder CSV möglich
- Kopieren der Tabelle möglich (Print-Version aller Aktivitäten wird in den Zwischenspeicher geladen)

#### <i class="ph ph-users"></i> Personenübersicht verbessert

- Neue Darstellung mit Karten statt Tabelle
- Neue Filtermöglichkeiten
- Inaktive Mitarbeiter:innen lassen sich jetzt einfacher finden

#### <i class="ph ph-sync"></i> Verbesserung der LDAP-Schnittstelle

- ldaps wurde hinzugefügt (über Port identifiziert)
- Nutzer, die nicht in der BASEDN sind, wurden vom Log-In ausgeschlossen.
- Es wurde eine User-Whitelist und eine Blacklist hinzugefügt (in Feature Settings)
- Aktive LDAP-Synchronisation wurde hinzugefügt (manuell, Knopf findet sich ebenfalls in Feature Settings)

#### <i class="ph ph-star"></i> Weiteres

- Das Ergebnis der Erweiterten Suche lässt sich ebenfalls in die Zwischenablage kopieren und als Excel bzw. CSV herunterladen
- Name in MyActivities wurde gefixt
- Ein Problem wurde behoben, wegen dem der Institutsname in Autorenlisten nicht geändert werden konnte.
- Es wurden sehr viele weitere kleine Probleme behoben.
<!-- - *Neue Achievements* -->
- Der Code wurde umstrukturiert, sodass die Routes jetzt in mehrere Dateien gesplittet wurden
  - Die `index.php` ist jetzt weniger überladen
  - Die routes sind nun nach Thema sortiert
  - POST routes wurden besser benannt und mit `/crud` geprefixt und finden sich nun ebenfalls in den thematischen routes
  - Die verschiedenen JavaScript-Funktionen wurden in osiris.js ausgegliedert und verallgemeinert.

#### ⚠ Breaking Changes

Es kann sein, dass einige wenige Einstellungen von Euch nicht in das neue Format übertragen werden. Dies betrifft z.B. das Institutslogo, das durch die Umstellung auf die Datenbank erneut hochgeladen werden muss.

---

<time datetime="2023-10-25">25.10.2023</time>
<a class="anchor" href="#version-1.2.2" id="version-1.2.2"></a>

## Version 1.2.2

#### <i class="ph ph-tree-structure"></i> Projekte

Projekte wurden eingeführt.

- Eine Übersicht über alle Projekte wird als Tabelle dargestellt. Diese ist durchsuchbar und kann nach Status und Rolle des Instituts gefiltert werden.
- Durch Klick auf den Kurznamen gelangt man zur Übersichtsseite eines Projektes. Hier sind die folgenden Details dargestellt:
  - Eine Übersicht über alle Projektinformationen ist als Tabelle dargestellt
  - Ein Knopf führt zur Bearbeitungsseite
  - Ein Überblick über alle beteiligten Personen ist dargestellt. Ansprechpartner werden bei der Erstellung eines Projektes automatisch als Projektleiter hinzugefügt
  - Über einen Knopf lassen sich die Personen bearbeiten
  - Alle verknüpfte Aktivitäten werden dargestellt
  - Über einen Download-Knopf lassen sich alle zu dem Projekt gehörige Aktivitäten als Word oder BibTeX herunterladen.
- Auf der Profilseite einer Person wurde ein neues Widget hinzugefügt. Dies zeigt alle Projekte, an denen eine Person beteiligt ist.
- Auf der Übersichtsseite einer Aktivität können nun Projekte verknüpft werden. Dazu wurde auch hier ein neues Widget hinzugefügt. ![Projekte auf der Übersichtsseite](img/news/activity-projects.png)
- Alle Projektseiten sind zurzeit noch mit dem Flag <span class="badge danger text-normal font-size-16" data-toggle="tooltip" data-title="Nicht für den Produktions-einsatz">BETA</span> versehen. Sobald das Feature ausreichend getestet wurde, wird es entfernt. Feedback dazu wie immer an <a href="mailto:julia.koblitz@dsmz.de"><i class="ph ph-envelope"></i>&nbsp;Julia</a>.

#### <i class="ph ph-person-arms-spread"></i> Accessibility

Es wurden neue Features für die verbesserte Accessibility hinzugefügt, insbesondere ein Modus mit erhöhtem Kontrast, verringerte Animationen, sowie eine Schriftart speziell für Personen mit Dyslexie. All diese Features findest du in einem neuen Dropdown-Menü im Header.

#### <i class="ph ph-user-switch"></i> Gäste (Prototyp)

Das Gästeaddon wurde weiterentwickelt (**immer noch im Prototyp-Stadium**)

- Gäste können jetzt über das Modul angemeldet werden
  - Einige Details sind dabei bei Anmeldung vom Betreuer auszufüllen
  - Andere Details sind bei Anmeldung optional
- Nach Anmeldung wird ein Link und ein QR-Code generiert. Diese können an den Gast weitergeleitet werden.
- Der Gast kann nun das extern freigeschaltete Formular verwenden, um alle Daten zu ergänzen, alle Belehrungen zur Kenntnis zu nehmen und sich anzumelden.
- Die Daten werden dann an OSIRIS übermittelt.
- Wenn der Gast fertig angelegt ist, kann auch ein Nutzeraccount verknüpft werden. Dieser wird beim Gast angezeigt und im Profil des Gastes wird der Gaststatus dargestellt. ![Gaststatus](img/news/guest-status.png)

<div class="alert signal">
  <h5 class="title">
      <i class="ph ph-warning"></i>
      Work in Progress
  </h5>
  Bitte beachtet, dass es sich hierbei um Work in Progress handelt. Es fehlen noch wichtige Funktionen, die für den Live-Betrieb unerlässlich sind. 
  Dies sind u.a. Email-Benachrichtigungen, Sichtbarkeit und Zugriffsrechte.
  Außerdem funktionieren einige Knöpfe noch nicht, z.B. die Verknüpfung von Chips und der Upload von Dokumenten. Des Weiteren soll auch eine Lösung für Universitätspraktika geschaffen werden, bei denen viele Studierende an die Einrichtung kommen. 
</div>

#### <i class="ph ph-gear"></i> Generell

Es wurden einige generelle Verbesserungen durchgeführt:

- Im Zuge der Einführung von Projekten wurde die visuelle Darstellung der Aktivitätsseite weiter verbessert. Die wichtigsten Knöpfe sind jetzt zusätzlich in einer Toolbar ganz oben zu finden.
- Der generelle Look aller Tabellen wurde überarbeitet.

---

<time datetime="2023-10-18">18.10.2023</time>
<a class="anchor" href="#version-1.2.1" id="version-1.2.1"></a>

## Version 1.2.1

Diese Version beinhaltet vornehmlich Bug Fixes und Optimierungen im Hintergrund. Außerdem wurde das Layout einiger Seiten verbessert, sowie die Schriftart für Überschriften verändert.

---

<time datetime="2023-10-04">04.10.2023</time>
<a class="anchor" href="#version-1.2.0" id="version-1.2.0"></a>

## Version 1.2.0

#### <i class="ph ph-users"></i> Rollensystem

- Ein ausgeklügeltes Rollensystem wurde hinzugefügt
- Einem Nutzer können jetzt mehrere Rollen zugewiesen werden (im Nutzer-Editor)
- Welche Rechte eine Rolle hat, kann jetzt feingranular im Admin-Panel eingstellt werden (neuer Reiter "Rollen")
- Die Einstellungen wurden im Hintergund verbessert

#### <i class="ph ph-user-minus"></i> Verbessertes Inaktivieren von Nutzern

- Nutzer können jetzt leichter als "Inaktiv" markiert werden
- Dazu wurde ein Knopf zur Toolbar im Nutzerprofil hinzugefügt (nur für Nutzer mit entsprechenden Rechten sichtbar)
- Persönliche Daten werden (abgesehen von Namen und Abteilung) gelöscht, sobald ein Nutzer inaktiviert wird
- Um einen Nutzer wieder zu aktivieren, kann man in "Nutzerprofil bearbeiten" einen entsprechenden Haken setzen.

#### <i class="ph ph-circles-three-plus"></i> Forschungsdaten

- Nein, wir fügen zu OSIRIS keine Forschungsdaten hinzu. Niemals.
- Stattdessen kann man Foschungsdaten mit Aktivitäten verknüpfen. Das geht über Entität (z.B. Genomsequenz), Freitext und Link. Geht dazu auf die Übersichtsseite einer Aktivität und klickt auf "Verknüpfen".
- Eine umfassende Suche für Forschungsdaten wurde hinzugefügt. Ihr müsst wissen, wie viele Genomsequenzen ihr im Jahr 2022 veröffentlicht habt? Mit OSIRIS ist das jetzt möglich (solange ihr die Daten eingepflegt habt).

#### <i class="ph ph-gear"></i> Mehr Einstellungen im Admin-Panel

- Coins und Achievements lassen sich jetzt global ausstellen
- Fremde Nutzermetriken lassen sich jetzt im Profil ausstellen. Daraufhin sind die Graphen nur noch für einen selbst sichtbar.

#### <i class="ph ph-copy"></i> Verbesserungen bei der Dubletten-Erkennung

- Die beste Lösung für Dubletten ist zu verhindern, dass sie entstehen. OSIRIS warnt euch, falls ihr dabei seid, gerade eine Dublette anzulegen.

#### <i class="ph ph-chalkboard-simple"></i> Verbesserung der Lehrveranstaltungen

- Lehrveranstaltungen wurden optisch überarbeitet
- Ein Filter wurde hinzugefügt
- Es wurde eine Möglichkeit hinzugefügt, um Lehrveranstaltungen zu löschen (nur wenn keine Aktivitäten verknüpft sind)

#### <i class="ph ph-textbox"></i> Neue Module

- Open Access Status
- Abstract
- Gender
- Country (nach ISO-Standard)

#### <i class="ph ph-star"></i> Kleinere Features und Bug Fixes

- Im Header ist nun ein Suchfeld für Aktivitäten zu finden
- Nutzer können ihre Profilbilder jetzt selbst bearbeiten.
- Zuletzt besuchte Konferenzen werden im "Conference"-Modul jetzt vorgeschlagen. Das führt hoffentlich zu mehr Konsistenz.
- Dem Profil von Berichterstattern wurden neue Elemente hinzugefügt
- Einige Interfaces wurden angepasst, z.B. ist die Übersichtsseite einer Aktivität jetzt noch nutzerfreundlicher.
- OSIRIS-Seiten sollten sich jetzt sehr viel besser ausdrucken lassen.
- Der Style von Buttons und Badges wurde angepasst.
- Es gibt jetzt eine neue 404 Seite. Die ist schön, schaut sie euch gern mal an.

#### <i class="ph ph-code"></i> Hintergrundverbesserungen

- Es gibt außerdem ein paar Verbesserungen hinter den Szenen. Das wird in Zukunft zu noch schnellerer und konsistenterer Entwicklung führen.
- Eine neue Datenbank-Klasse war längst überfällig.
- Nutzerdaten wurden in Personen und Accounts geteilt. Dadurch können auch Personen angelegt werden, die keine Nutzer sind und Accountdaten sind von Personendaten getrennt. Das war ein wichtiger Schritt für die Gästeformulare.

**10.07.23**

#### <i class="ph ph-briefcase"></i> Altdaten-Import

Es können jetzt auch Altdaten importiert werden. Wie genau das funktioniert, liest du am besten in der [Anleitung](https://osiris-app.de/install#import).

**06.07.23**

#### <i class="ph ph-swap"></i> IDA-Integration

Mit einer IDA-Integration wurde begonnen. Um das Feature zu aktivieren, muss in der CONFIG-Datei der folgende Wert auf true gesetzt werden:

```php
// activate IDA integration here
define("IDA_INTEGRATION", true);
```

Zurzeit wird nur Formular 18.3 als Proof of Concept unterstützt. An weiteren Formularen wird gearbeitet, sobald der Fragenkatalog für das folgende Berichtsjahr feststeht.

Außerdem wurden einige neue Module hinzugefügt, die für die IDA-Abfrage relevant sind, z.B. gender, nationality, open access status.

#### <i class="ph ph-tree-structure"></i> Schema.org Integration

OSIRIS-Aktivitäten sind jetzt auch als Schema.org hinterlegt und exportierbar. Falls ihr nicht wisst, was das ist, könnt ihr es auf der [offiziellen Seite von Schema.org](https://schema.org/) nachlesen. Und falls ihr wissen wollt, wie so ein maschinen-lesbares Format aussieht, habe ich es euch hier mal für eine meiner Publikationen dargestellt:

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@id": "#issue",
      "@type": "PublicationIssue",
      "issueNumber": 5,
      "isPartOf": {
        "@id": "#volume"
      }
    },
    {
      "@id": "#volume",
      "@type": "PublicationVolume",
      "volumeNumber": 19,
      "datePublished": "2023-05"
    },
    {
      "@type": "Periodical",
      "@id": "#journal",
      "name": "Nature Chemical Biology",
      "issn": ["1552-4469", "1552-4450"],
      "publisher": "Nature Pub. Group",
      "hasPart": {
        "@id": "#volume"
      }
    },
    {
      "@id": "#record",
      "@type": "ScholarlyArticle",
      "name": "Metabolism from the magic angle",
      "author": {
        "@type": "Person",
        "givenName": "Koblitz",
        "familyName": "Julia"
      },
      "datePublished": "2023-05",
      "identifier": [
        {
          "@type": "PropertyValue",
          "name": "DOI",
          "value": "10.1038/s41589-023-01317-2"
        }
      ],
      "pagination": "538-539",
      "isPartOf": [
        {
          "@id": "#issue"
        },
        {
          "@id": "#volume"
        },
        {
          "@id": "#journal"
        }
      ]
    }
  ]
}
```

**05.06.23**

#### <i class="ph ph-queue"></i> Neues Feature: Warteschlange

OSIRIS prüft jetzt selbstständig einmal in der Woche (sonntags um 2 Uhr), ob es neue Publikationen für die Institution gibt. Wenn für dich neue Publikationen gefunden wurden, erhältst du einen Hinweis auf deiner Startseite.

---

<time datetime="23-05-12">12.05.23</time>
<a class="anchor" href="#version-1.1.0" id="version-1.1.0"></a>

## Version 1.1.0

Ich habe mehrere kleinere Releases in einem großen zusammengeführt.

#### <i class="ph ph-cake"></i> Neue Icons wurden eingeführt

Aufgrund von Lizenzproblemen wurden die Icons auf der Seite vollständig durch [Phosphor-Icons](https://phosphoricons.com/) ersetzt. Die sind (genau wie OSIRIS) unter der Open Source-Lizenz MIT veröffentlicht.

Dadurch sieht die Seite auf den ersten Blick vielleicht etwas ungewohnt aus, ihr werdet euch aber sicher schnell daran gewöhnen.

#### <i class="ph ph-plus-circle"></i> Verbesserungen beim Hinzufügen der Aktivitäten

- Viele Module wurden umstrukturiert und verbessert
- Ein neuer Date-Picker für Zeiträume erleichtert (hoffentlich) die Bedienung
- Journale und Lehrveranstaltungen haben ein komplett neues UI/UX und lassen sich jetzt (hoffentlich) besser bedienen
- Wenn eine DOI oder Pubmed-ID eingegeben wird, wird nun sofort überprüft, ob diese bereits in der Datenbank vorhanden ist (danke an Markus für das Feedback)
- Bearbeitungsnotizen sind nur noch verfügbar, wenn mehr als ein Autor beteiligt ist
- Preprints brauchen jetzt kein Journal mehr (was quatsch war). Stattdessen kann der Veröffentlichungsort (z.B. BioRxiv) in ein Freitextfeld eingetragen werden.
- Ein Fehler wurde behoben, durch den sich das Interface aufhängen konnte, wenn die DOI nicht gefunden wurde
- Ein Fehler wurde behoben, durch den OSIRIS sich 'verschluckt' hat, wenn Autoren-Vornamen mit einem Sonderzeichen beginnen

#### <i class="ph ph-chalkboard-simple"></i> Umstrukturierung der Lehre

- Lehrveranstaltungen sind jetzt standardisiert
- Jedes Modul hat eine einzigartige Modulnummer, über die es leicht gefunden werden kann
- Es kann für jede Person einzeln der Anteil der SWS angegeben werden (nur bei affiliierten Personen notwendig)
- Ein SWS-Rechner wurde hinzugefügt
- Einem Modul können Lehrveranstaltungen (z.B. Praktika, Vorlesungen, Seminare) hinzugefügt werden
- Gäste, die wegen einer Lehrveranstaltung hier sind (i.e. Studenten), können ebenfalls mit dem Modul verknüpft werden

#### <i class="ph ph-book-open-text"></i> Neue Pubmed-Suche

- Pubmed kann nun nach Autor(en), Titel, Jahr und Affiliation [durchsucht werden](activities/online-search?authors=Koblitz&year=2023)
- Mittels der Levenshtein-Distanz wird die Wahrscheinlichkeit berechnet, ob es sich um ein Duplikat handelt oder nicht (nur basierend auf dem Titel)
- Die neue Suche kann z.B. bei Publikationen in [Mein Jahr](scientist) gefunden werden

#### <i class="ph ph-newspaper-clipping"></i> Verbesserungen bei Journalen

- Das UI/UX-Design der Journale wurde verbessert
- Es wird nun eine bessere API verwendet, um Journale abzufragen. [OpenAlex](https://docs.openalex.org/api-entities/venues) ist nicht nur deutlich schneller als NLM, es enthält auch mehr Journale und mehr Datenfelder. So muss OpenAccess jetzt nicht mehr zusätzlich abgefragt werden.

#### <i class="ph ph-sparkle"></i> Verbesserungen bei den Aktivitäten

- Als Beta-Feature wurden Awards eingeführt. Falls ihr dort Datenfelder vermisst, meldet euch bitte bei mir.
- Die Filter-Funktionen in Alle Aktivitäten wurden verbessert
  - Laufende Aktivitäten werden jetzt auch bei der Zeitraum-Suche berücksichtigt
  - Autoren, die sich hinter et al. verstecken, werden jetzt auch bei der Suche berücksichtigt
  - Der mittlerweile sehr viel Platz fressende Aktivitätenfilter wurde in ein Dropdown gepackt
  - Es wurde die Möglichkeit hinzugefügt, nach Abteilung zu filtern und Epubs (Online ahead of print) auszuschließen

#### <i class="ph ph-shapes"></i> Sonstiges

- Nicht aktive Nutzer werden in der Expertise-Suche nicht mehr berücksichtigt
- Widgets auf der Profilseite sind nur noch sichtbar, wenn sie Daten enthalten
- Coins müssen jetzt aktiv eingeschaltet werden (drei Zustände: nicht sichtbar, für mich sichtbar, für alle sichtbar)
- Die Seitennavigation wurde überarbeitet, sodass die wachsende Menge an Inhalten besser strukturiert ist.
- Viele Verbesserungen am Report (geschützte Leerzeichen, Bindestrich-Abk., Software und versch. Reviews berücksichtigt, Alphabetische Sortierung)
- Es wurden weitere Filter und Links bei der Abteilungsvisualisierung hinzugefügt
- Man erhält nur noch Erfolge für Aktivitäten, die man bereits bestätigt hat

#### <i class="ph ph-hammer"></i> Maximale Flexibilität

- durch einige umfangreiche Umstellungen ist es nun kinderleicht, neue Aktivitätsarten hinzuzufügen, zu konfigurieren, formatieren und zu bearbeiten. Dafür sind jetzt nicht mal mehr Programmierkenntnisse erforderlich.
- Die Konfiguration bei anderen Instituten wird auch bei neuen Updates nicht überschrieben, wodurch sie die maximale Flexibilität haben, OSIRIS nach ihren Wünschen zu gestalten.

<div class="alert danger">
  <h3 class="title">
    <i class="ph ph-warning"></i>
    Achtung für alle anderen Institute! Breaking Changes!!!
  </h3>
  <ul class="list">
    <li>Die Struktur der Einstellungen wurde verändert!</li>
    <li>Für alle Einstellungen, die zuvor an <code class="code">settings.json</code> vorgenommen wurden, gibt es jetzt ein Admin-Dashboard.</li>
    <li>Da die Datei <code class="code">settings.json</code> aus technischen Gründen jetzt extern sichtbar ist, wurden sicherheitsrelevante Einstellungen in die <code class="code">CONFIG.php</code> transferiert. Bitte schau dir die Datei <code class="code">CONFIG.default.php</code> an, um zu sehen, wie die neue Datei auszusehen hat. </li>
    <li>Dafür sind Aktivitäten jetzt komplett konfigurierbar. Tutorials und Beispiele folgen demnächst auf <a href="https://osiris-app.de" target="_blank">der offiziellen Webseite</a>.</li>
  </ul>
</div>

**31.01.23**

- Die Datenbank wurde aufgeräumt: nicht mehr benötigte/gepflegte/gezeigte Datenfelder wurden entfernt.
- Beim Hinzufügen von Publikationen via DOI oder PM-ID wird nun das Journal anhand der ISSN gesucht. Dadurch wird eine falsche Journalzuordnung durch unterschiedliche Namen vermieden.
- Ich habe alle Journale, die mindestens ein verknüpftes Paper hatten, mit JCR verknüpft
  - Über die API von Web of Science wurden MetaInformationen hinzugefügt.
  - Auf der Übersichtsseite eines Journals findet sich bei betroffenen Journalen jetzt ein Link zum Journal Citation Report.
  - Mittels eines selbstgeschriebenen Web Scrapers wurden Impact Factoren von JCR gezogen und gespei

**25.01.23**

- Feedback zum Hinzufügen von Editorenschaften wurde eingepflegt (Danke an Andrey)
- Auf der Profilseite gibt es (wenn vorh.) eine Übersicht mit allen laufenden Mitgliedschaften (Gremien & Editorial Boards)
- Es gibt jetzt eine Liste mit Namen, die für das Autoren-Matching verwendet werden. Diese enthält standardmäßig den vollen Namen und den abgekürzten Namen. Letzterer kann jedoch auch entfernt werden (falls er zu viele Treffer verursacht). Außerdem können weitere Namen (Mädchenname, Pseudonyme, optionale Vornamen) hinzugefügt werden, die für das Matching relevant sind. Bearbeiten kann man die Liste im Profil.
- Ein Bug wurde gefixt, bei dem eine Publikation ohne Impact Factor keine Coins gab.
- Ein Bug wurde gefixt, wegen dem Autoren, deren Vornamen mit einem Sonderzeichen beginnen, das System gebrochen haben.

**23.01.23**

- Der Autoreneditor auf der Seite "Aktivität hinzufügen" wurde verbessert
- Wenn ein Journal nicht in OSIRIS gefunden wurde, wird automatisch eine Suche in NLM durchgeführt.

**11.01.23**

- Es wurde eine Möglichkeit hinzugefügt, den Typ einer Aktivität nachträglich zu bearbeiten
- In die neue Web-Ansicht wurde das Datum der Aktivität integriert
- Es wurde ein Bug gefixt, durch den beim Filtern nach Vorträgen in allen Aktivitäten auch bestimmte Studierende gezeigt wurden

---

<time datetime="23-01-02">02.01.23</time>

## Version 1.0

Über die Feiertage habe ich noch ein paar Features hinzugefügt und (nicht aufgeführt) ein paar Bugs entfernt:

**Download von Aktivitäten**

- Einzelne Aktivitäten können jetzt auf der Übersichtsseite heruntergeladen werden. Dafür gehst du rechts oben auf Download und kannst dann auswählen, ob und wer fett hervorgehoben wird und in welchem Format du herunterladen möchtest
- Der "Einkaufswagen" wurde hinzugefügt. Damit können einzelne Aktivitäten gesammelt werden (sowohl auf der Übersichtsseite als auch bei Alle Aktivitäten). Alle ausgewählten Aktivitäten können dann gesammelt heruntergeladen werden.

**Import von Aktivitäten**

- Der Import von Publikationen aus Google Scholar wurde hinzugefügt
- Dazu muss im Nutzerprofil der Google Scholar-Account hinterlegt sein
- Um Duplikate zu vermeiden, wird sowohl nach Titelübereinstimmung (Levenshtein-Distanz) als auch nach DOIs gesucht
- Bitte beachtet, dass oftmals eine Überprüfung der Publikation notwendig ist, da Google Scholar-Infos leider oft inkorrekt oder unvollständig sind

**Achievements:**

- Errungenschaften wurden eingeführt
- für den Anfang gibt es 16 Errungenschaften mit eigenen Icons, verschiedenen Leveln und Beschreibungen
- Errungenschaften werden auf der Profilseite angezeigt
- Es gibt eine eigene Seite, um detailliert die Errungenschaften einer Person anzuschauen

**Profileinstellungen**

- Im Menü "Profil bearbeiten" können jetzt Präferenzen festgelegt werden
- Beispielsweise können Coins und Errungenschaften ausgeblendet werden. Sie werden dann weder für dich selbst noch für andere Nutzer auf deinem Profil gezeigt.

**Neue Aktivitätsansicht**

- Es gibt jetzt eine neue Ansicht für Aktivitäten, die besser fürs Web optimiert ist
- Der Titel wird dabei größer dargestellt, Autoren und weitere Infos sind je in einer eigenen Zeile
- Die neue Ansicht ist jetzt der Standard bei allen Tabellen und auf Profil- und Jahresseiten
- Falls euch die alte Ansicht besser gefallen hat, könnt ihr in euren Profileinstellungen im Punkt "Aktivitäten-Anzeige" auf Print stellen.

**Journale**

- Ein Journal kann nun als Open Access markiert werden
- Dazu kann entweder angegeben werden, dass das Journal ausschließlich open access ist, oder ab welchem Jahr
- Neu hinzugefügte Publikationen, die nach dem angegebenen Jahr publiziert wurden, werden automatisch als Open Access markiert
- Wenn das Open Access Jahr eines Journals neu gesetzt wird, werden alle Publikationen die _nach_ dem Jahr publiziert wurden, automatisch als Open Access markiert. Publikationen _im_ angegebenen Jahr müssen manuell überprüft werden
- Journale können jetzt manuell hinzugefügt werden (für Admins)

**Dokumentation**

- Eine erste Dokumentation wurde bereitgestellt
- Es werden zurzeit bereits Themen wie zum Beispiel das Hinzufügen von Aktivitäten, mein Jahr und Warnungen abgebildet
- Weitere Themen sind in Arbeit und werden sukzessiv hinzugefügt

OSIRIS geht damit in die Version 1.0 über und verlässt die Betaphase. Wir werden natürlich trotzdem weiterhin Feedback einsammeln und an dem Tool weiterentwickeln. Danke an alle, die an der Betaphase beteiligt waren!

**18.12.22**

- Neues "Experten-Tool": mit der [erweiterten Suche](activities/search) können jetzt alle Aktivitäten detailliert durchsucht werden. 45 Datenfelder sind mit unterschieldichen Optionen durchsuchbar. Ein Anleitungsvideo folgt in Kürze.
- In der Übersicht einer Aktivität sind nun alle Autoren aufgeführt
- Bei Autoren ohne Vornamen (z.B. "The Microbiome Consortium") wird nun kein Komma mehr angezeigt
- Bug Fixes im Report:
  - Impact Faktoren werden korrekt angezeigt
  - Hoch- und tiefgestellte Zeichen werden jetzt korrekt übernommen
  - Leerzeile zwischen der Publikation und der Bemerkung "Shared authors" wurde entfernt
  - Wenn in einer Aktivität kein Autor mit DSMZ-Affiliation gefunden wurde, wird sie nicht aufgeführt

**13.12.22**

- Aktivitäten:
  - Zu allen Aktivitäten können nun optional Kommentare hinzugefügt werden
  - Kommentare sind nur für Autoren der Aktivität (und Admins, a.k.a. Julia und das Controlling) sichtbar
  - Dadurch können einerseits "private" Notizen zu den eigenen Aktivitäten hinzugefügt werden, aber auch Bemerkungen für das Controlling können hinterlassen werden.
- Mein Jahr:
  - Die Seite "Mein Jahr" wurde etwas überarbeitet: unwichtige Sachen wurden entfernt und wichtige bekommen zentralere Positionen.
  - Eine Timeline wurde hinzugefügt, um eine visuelle Übersicht über das Jahr zu geben.
  - Der Prozess, mit dem ein Quartal für das Controlling freigegeben werden kann, wurde verbessert.
  - Wenn das letzte Quartal noch nicht freigegeben wurde, erscheint eine Nachricht im persönlichen Profil.
- Journale:
  - Die Tabelle mit allen Journalen wurde verbessert und enthält nun eine Anzahl von Aktivitäten in OSIRIS, nach der auch standardmäßig sortiert wird.
  - Auf der Übersicht eines Journals werden jetzt alle Publikationen, sowie Reviewer- und Editortätigkeiten in dem jeweiligen Journal gezeigt.

**02.12.22**

- Beim Hinzufügen von Publikationen werden Journale jetzt standardisiert und verknüpft
- Neue Journale können anhand des NLM-Katalogs hinzugefügt werden
- Journale können bearbeitet und um neue Impact Factoren erweitert werden

**29.11.22**

- Neue Visualisierungen wurden hinzugefügt (Abteilungs-Netzwerk und -Übersicht) und die vorhandene (Coautoren-Netzwerk) wurde verbessert

**24.11.22**

- Das Menü wurde umstrukturiert und farbig etwas einfacher gehalten
- Neue Icons für Aktivitäten wurden eingeführt
- Der Knopf zum Hinzufügen von Aktivitäten war anscheinend zu fancy, um ihn wahrzunehmen. Er wurde vereinfacht.
- Die neue Primärfarbe ist "Osiris"-Orange
- Die Profilseite wurde überarbeitet:
  - Es wurde eine Grafik zur Rolle in Publikationen hinzugefügt
  - Publikationen und andere Aktivitäten werden jetzt getrennt voneinander aufgeführt
  - Viele Graphen haben nun Achsenbeschriftungen bekommen
  - Der Polar-Chart zu Impact Faktoren wurde durch ein Histogramm ersetzt
  - Die neuen Aktivitätstypen (Software & Teaching) wurden der Grafik über alle Aktivitäten hinzugefügt
- Bei den zu überprüfenden Autorenschaften wurde die Information hinzugefügt, wer die Aktivität hinzugefügt bzw. zuletzt bearbeitet hat
- Das Löschen von Aktivitäten ist nach ausdrücklichem Wunsch des Controllings für vergangene Quartale nicht mehr möglich. Fehlerhafte oder doppelte Aktivitäten können vom Controlling gelöscht werden, ein Knopf für schnellen Kontakt wurde hinzugefügt.
- "Open Access" ist jetzt ein Radio Button statt einer Checkbox
- Ein Icon für "Closed Access" wurde eingeführt
- Die Übersichtsseite der Aktivitäten wurde grundsätzlich überarbeitet und ist jetzt deutlich übersichtlicher gestaltet
- Der Upload von Dokumenten zu Aktivitäten wurde überarbeitet
  - Jetzt getrennt vom Erstellen einer Publikation
  - Es können mehrere Dokumente für eine Aktivität hochgeladen werden
  - Es können jetzt auch andere Dokumente als PDF hochgeladen werden (z.B. PPTX, Word, usw.)
  - Dokumente können jetzt wieder gelöscht werden
  - Ein kleines Datei-Icon mit Link zum Dokument erscheint in der formatierten Aktivität.
- Bug Fixes:
  - Anzahl der Erst- und Letztautoren werden beim Bearbeiten einer Publikation nicht mehr versehentlich überschrieben
  - Eine Aktivität zu kopieren war nicht möglich
  - Viele kleinere Bug Fixes

**18.11.22**

- Ich habe zusätzlich zu Crossref auch DataCite hinzugefügt. Dadurch können jetzt auch **DOIs von anderen Aktivitäten** als nur Publikationen hinzugefügt werden, beispielsweise Software, Datensätze, Poster, Präsentationen, usw. Um eine DOI für eine solche Aktivität zu bekommen, empfehle ich die Aktivität auf einem Datenarchiv wie beispielsweise [Zenodo](https://zenodo.org/) hochzuladen.
- Die Seite "Mein Jahr" wurde für die neuen Aktivitäten aktualisiert
- Die Einstellungen im Downloadbereich wurden erweitert:
  - Es kann nun eine Abteilung ausgewählt werden
  - Ein genauer Zeitraum (Monat/Jahr) kann ausgewählt werden
  - Man kann nun einstellen, ob und was fett markiert werden soll
  - BibTex-Export funktioniert jetzt

**17.11.22**

Ich habe sehr viel User-Feedback aus der Beta-Phase eingearbeitet, u.a. folgendes:

- Neue Aktivitäten wurden hinzugefügt:

  - Software
  - Lehre (Vorlesungen und Praktika)
  - Reviews von Grant Proposals und Abschlussarbeiten

- Das Hinzufügen von Aktivitäten wurde verbessert:
  - Die Aktivitäten Publikation, Reviews, Misc und Studierende haben jetzt Unterkategorien, die ausgewählt werden können, nachdem eine Kategorie ausgewählt wurde. Dadurch sollten viele offene Fragen geklärt werden, da die Datenfelder nun etwas flexibler reagieren.
  - Beispiele wurden hinzugefügt. Wenn man nun auf Beispiele klickt, werden ausgewählte Aktivitäten oberhalb des Formulars angezeigt. Dabei sind Datenfelder farbig gekennzeichnet und beim Hovern wird der Name des Datenfelds angezeigt. Die Datenfelder im Formular sind in der gleichen Farbe gekennzeichnet.
  - Eine Hilfe-Funktion erklärt nun, wie der Autoreneditor funktioniert.
  - Lectures: Es kann jetzt angegeben werden, ob es sich um eine _Invited lecture_ handelt

**15.11.22**

- Das Controlling-Dashboard wurde komplett überarbeitet. Neue Metriken werden in verbesserten Graphen dargestellt.

**07.11.22**

- Ich habe auf Anfrage eine Nutzerprofilseite hinzugefügt. Die Seite "Mein Jahr", auf die bisher von der Nutzertabelle verwiesen wurde, hat offenbar zu einiger Verwirrung geführt. "Mein Jahr" soll ausschließlich der Übersicht des aktuellen Jahrs/Quartals dienen und die bekannten Excel-Listen ablösen.
- Das neue Profil wurde mit Metriken und Graphen ausgestattet. Außerdem findet sich hier eine kurze Übersicht über die neuesten Aktivitäten sowie ein paar Nutzerinfos.
- Da das Quartal-Auswahlfeld (vorher zu finden oben rechts im Header) nur noch auf der Seite "mein Jahr" verwendet wurde, wurde es dorthin verschoben, um Verwirrung zu vermeiden.
- Das Dashboard wurde durch das neue Profil abgelöst

**06.11.22**

- Man kann sich nun ein Koautoren-Netzwerk anzeigen lassen. Dort sind alle DSMZ-Wissenschaftler dargestellt, mit denen man zusammen publiziert hat. Die Verknüpfungen sind dabei nach Abteilung gefärbt.
- Achievements wurden hinzugefügt (im Moment noch low level, sprich nur für erstellte/bearbeitete Datensätze)

**31.10.22**

- Journale wurden um alle NLM Journale ergänzt
- Falls vorhanden wurde der Impact Factor hinzugefügt
- Der Journal- und Nutzerbrowser wurde optimiert und ist nun komplett durchsuch- und sortierbar
- Quartale können nicht mehr bestätigt werden, wenn sie noch nicht zu Ende sind
- Es wird nun am Ende eines Quartals auf weitere mgl. Probleme hingewiesen (z.B. noch laufende Aktivitäten ohne Enddatum)

**07.10.22**

- Nutzer können jetzt bearbeitet werden

**03.10.22**

Der erste Report wurde eingeführt (Research report)

- Die Zeitspanne kann genau angepasst werden
- Der Export richtet sich ungefähr nach dem bisherigen Report, Header, Footer und Tabellen müssen manuell angepasst werden

**25.09.22**

- Die Übersichtsseite für Aktivitäten wurde verbessert (trotzdem noch im Alpha-Status).
- Man kann nun PDF-Dokumente an Aktivitäten anhängen. Diese können auf der Übersichtsseite heruntergeladen werden.
- Eine Übersichtsseite mit allen gefundenen Problemen ermöglicht Wissenschaftlern ihre Aktivitäten zu bereinigen. Folgende "Probleme" gibt es:
  - Eine Autorenschaft wurde noch nicht bestätigt
  - Eine Publikation ist noch als Epub hinterlegt
  - Eine Abschlussarbeit ist noch "in Progress", aber das Abschlussdatum liegt in der Vergangenheit
- Im Titel-Editor können jetzt auch ein paar Sonderzeichen hinzugefügt werden.
- Autoren können nun im Detail-Editor bearbeitet werden. So ist es möglich, z.B. Nutzernamen anzupassen, damit die Aktivitäten auch den richtigen Autoren zugeordnet werden können.

**24.09.22**

- Dashboard hinzugefügt: Das Dashboard ist die neue Startseite. Sie wurde sowohl für Wissenschaftler als auch für das Controlling verbessert und zeigt jetzt eine Übersicht über die Aktivitäten (beim Wissenschaftler nur über eigene).

**23.09.22**

- Die verschiedenen Aktivitäten wurden alle in einer Tabelle zusammengefasst, die übersichtlicher gestaltet ist
- Die Aktivitäten lassen sich nach Art der Aktivität und Datum filtern und durchsuchen
- Die Aktivitäten lassen sich anschauen (über die Lupe) und bearbeiten (über den Stift). Bitte beachten, dass die Ansicht sehr rudimentär ist (die Rohdaten werden gezeigt) und noch verbessert wird.
- Die Aktivitäten lassen sich jetzt alle über ein gemeinsames Formular hinzufügen
- Die Aktivitäten lassen sich bearbeiten und löschen
- Geteilte Autorenschaften werden dargestellt
- Für Open-Access-Publikationen wurde ein Icon hinzugefügt

**21.09.22**

- Eine Schnittstelle wurde eingeführt, über die Nutzer aus LDAP bezogen werden können. Die Nutzer wurden aktualisiert und in Abteilungen unterteilt

**11.09.22**

- Titel von Aktivitäten können nun formatiert werden. Dabei ist es möglich, fett, kursiv und unterstrichen zu formatieren. Die Formatierungen sind auch bei den Bearbeitungen möglich und können mit exportiert werden.
- Bilder der Nutzer werden jetzt von Gravatar importiert
- Ich habe angefangen, das Confirmation-System einzuführen. Funktioniert soweit ganz gut.
- Bei den Publikationen funktionieren jetzt die Knöpfe "nicht Autor" und Affiliation.
- Bei dem LOM-System werden nun keine Punkte vergeben, wenn der Autor als Affiliation nicht die DSMZ hat.
- Wenn bei dem Hinzufügen einer Aktivität keiner der Autoren der DSMZ zugehörig ist, wird eine Warnmeldung gezeigt.

**02.09.22**

Ich habe ein ganz simples Punkte-System aufgesetzt.

- Die Punkte werden in einer von mir definierten Matrix gespeichert, die leicht anzupassen ist.
- Über ein Punkte-Portal kann das Controlling einfach die Punkte für die einzelnen Bereiche anpassen. Siehe [hier](lom).
- Auf der Übersichtsseite eines Wissenschaftlers werden oben die Punkte aufsummiert. Hinter jeder Aktivität stehen die dafür erhaltenen Punkte. Die Punkte für refereed journals errechnen sich mit dem Impact Factor (falls vorhanden)

**01.09.22**

Dieses Update bezieht sich ausschließlich auf die Ansicht des Controllings

- Auf der Startseite wird nun eine Übersicht aller Wissenschaftler gezeigt, die das aktuelle Quartal "approved" haben
- Die Inhaltsseiten (Publikationen, Poster, Vorträge, Reviewsm Misc, Students) zeigen nun alle Aktivitäten des ausgewählten Jahres
- Inhaltsseiten sind jetzt filterbar (Achtung! Groß- und Kleinschreibung beachten.)

**31.07.22**

- Publikationen können jetzt hinzugefügt werden. Sowohl Journal-Artikel als auch Bücher funktionieren.
- Auf das Löschen von Publikationen wurde bewusst verzichtet. Vielleicht fügt man die Funktion ein, ermöglicht es aber nur begrenzten User-Gruppen (z.B. Admin und Controlling)
- Export von Publikationen in Word und BibTex wurde hinzugefügt.

**29.07.22**

Ich habe mal wieder alles auf den Kopf gestellt. Die Datenbank zum Beispiel. Dort läuft jetzt MongoDB anstatt MySQL. Warum? Weil das für mehr Flexibilität, schnellere Ladezeiten und geringere Entwicklungszeit führt. Key-Value for the win :)

Dadurch war jetzt in kürzester Zeit folgender Fortschritt möglich:

- Alle CRUD-Funktionen wurden hinzugefügt, was bedeutet, dass sich jetzt alle dargestellten Aktivitäten erstellen, anzeigen, bearbeiten und löschen lassen. Einzige Ausnahme ist das Hinzufügen/Bearbeiten von Publikationen.
- Bei vielen Aktivitätstypen gibt es Sonderfunktionen:
  - Abschlussarbeiten können einfach verlängert werden.
  - Bereits gehaltenen Vorträgen kann einfach eine Wiederholung hinzugefügt werden
  - Reviews können weitere Reviewaktivitäten hinzugefügt werden
  - Die Zeitspanne von Editorials kann angepasst werden
  - Sonstige Forschungsaktivitäten (Misc) können weitere Termine hinzugefügt werden (wenn einmalig) bzw. die Zeitspanne angepasst werden (wenn annual)
- Auf der Übersichtsseite eines Wissenschaftlers können neue Aktivitäten über Popups hinzugefügt werden
- Man kann bei allen Aktivitäten den Nutzer auswählen, sodass z.B. auch das Controlling für andere Nutzer Aktivitäten hinzufügen kann.
- Die Übersicht der Nutzer ist nun durchsuchbar (Vorname, Nachname, Kürzel) und kann nach Wissenschaftler gefiltert werden.
- Die Übersicht der Journale ist nun nach Name des Journals und ISSN durchsuchbar.

**24.07.22**

- Dieses Changelog wurde hinzugefügt, um den Überblick über die Entwicklung zu behalten
- Neues Auswahlmenü zur Navigation hinzugefügt: jetzt können Jahr und Quartal präziser ausgewählt werden.
- Auf der Übersichtsseite werden jetzt alle Aktivitäten des aktuell gewählten **Jahres** gezeigt. Aktivitäten, die nicht im ausgewählten **Quartal** stattfanden werden ausgegraut
- Es wurden mit Students & Guests Doktoranden, Studenten und Gäste hinzugefügt.
  - Es gibt ein Interface, um neue Datensätze hinzuzufügen
  - Verantwortliche Wissenschaftler haben in diesem Jahr gelaufene Betreuungen in ihrer Übersicht.
  - Der verantw. Wissenschaftler bekommt einen Hinweis, wenn die Zeit eines Gast/Student/Doktorand abgelaufen ist und kann das Ende bestätigen oder verschieben
  - Abschlussarbeiten können auch abgebrochen werden
- Auf der Übersichtsseite werden jetzt erste Fehlermeldungen angezeigt.
