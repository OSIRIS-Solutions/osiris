# Neuigkeiten


<span class="badge float-right">04.05.2025</span>
<a class="anchor" href="#version-1.5.0" id="version-1.5.0"></a>

## Version 1.5.0 (Beta)

In diesem Update wurden die Projekte komplett überarbeitet und deutlich flexibler gestaltet. Datenfelder können nun sowohl bei Projekten als auch bei Personen vollständig definiert werden. Auch Custom Fields sind jetzt bei Projekten, Personen und Infrastrukturen möglich.

<i class="ph ph-gear"></i> Es gibt eine neue Seite im Admin-Bereich **"Inhalte"**, in der alle bearbeitbaren Inhalte von OSIRIS aufgelistet sind. Hier findet ihr jetzt die Personen-, Aktivitäts-, Projekt- und Infrastrukturkategorien, die Custom Fields und das Vokabular. Außerdem gibt es einen Link zu Helfertools, wie der Liste mit den Datenfeldern und den Templates. Bitte beachtet, dass einige Features aus den Generellen Einstellungen und Funktionen hierher verschoben wurden.


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


#### <i class="ph ph-cube-transparent"></i> Forschungsinfrastrukturen

- Die Datenfelder für Infrastrukturen sind jetzt konfigurierbar. Diese Einstellungen sind unter "Inhalte" zu finden.
- Custom Fields sind auch hier möglich.
- Die Übersichtstabellen für Infrastrukturen unterstützen jetzt Filter und Suchfunktionen.
- Die Darstellung von Verbundinfrastrukturen und Kooperationspartnern wurde verbessert.
- Der Begriff "Infrastrukturen" kann jetzt im Admin-Bereich angepasst werden.


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

**Weitere Verbesserungen**:

- Verbesserte Darstellung und Verwaltung von Projektbeteiligten, inkl. einer Erweiterung des Rollenvokabulars.
- Optimiertes Layout und übersichtlichere Struktur auf den Bearbeitungsseiten.
- Viele Filter- und Statistikfunktionen (z.B. Suche nach Projekttypen, Förderstatus) wurden erweitert.
- Es gibt die Möglichkeit, bei Erstellung oder Bearbeitung eines Projektes bestimmte Nutzer oder Rollen zu benachrichtigen, entweder direkt in OSIRIS über das neue Benachrichtigungssystem oder per E-Mail.


#### <i class="ph ph-clipboard-text"></i> Neue Datenfelder für Aktivitäten
Es wurden weitere Datenfelder für Aktivitäten hinzugefügt:
- Organisation: Hier kann via ROR eine Organisation ausgewählt werden, die an der Aktivität beteiligt ist. Es gibt auch eine Möglichkeit, nur den Ort der Organisation als Template auszulesen.
- Organisationen: Das gleiche wie oben, aber hier können mehrere Organisationen ausgewählt werden.

<!-- - Projekte: Hier können mehrere Projekte ausgewählt werden, die mit der Aktivität verknüpft sind.
- KDSF-FKK: Hier kann die KDSF-Forschungsfeldklassifikation ausgewählt werden, die mit der Aktivität verknüpft ist. -->



#### <i class="ph ph-book-bookmark"></i> Darstellung und Sichtbarkeit von Aktivitäten
- Neue Templates: Es wurden neue Templates für die Formatierung von Aktivitäten hinzugefügt, insbesondere für Autor:innen und Herausgeberschaften. Dadurch lassen sich auch Autorenschaften im APA-Format und andere Stile einfacher darstellen. Im Bereich Inhalte > Template-Baukasten findet ihr eine Anleitung.
- Es ist nun möglich, die Formatierung der affilierten Autor:innen anzupassen. In den allgemeinen Einstellungen gibt es dazu ein Dropdown-Menü, mit dem ihr anpassen könnt, ob affilierte Autor:innen fett, kursiv oder unterstrichen dargestellt werden sollen, oder eine Mischung davon.
- Es ist nun möglich, die Sichtbarkeit einzelner Aktivitätskategorien anzupassen. Dazu geht ihr auf Inhalte > Aktivitäten, wählt eine Kategorie (z.B. Publikationen) und klickt auf "Bearbeiten". Dort könnt ihr die Rolle einstellen, die Aktivitäten dieser Kategorie sehen darf. Standardmäßig ist dies auf "Alle" gesetzt, sodass alle Aktivitäten dieser Kategorie für alle Nutzer:innen sichtbar sind. Bitte beachtet, dass Nutzende ihre eigenen Aktivitäten immer sehen können, unabhängig von der Sichtbarkeitseinstellung. Diese Einstellung ist nur auf Kategorie-Ebene möglich, nicht auf Typen-Ebene.


#### <i class="ph ph-calendar"></i> Verbessertes Event-Management
- Es wurde eine Möglichkeit hinzugefügt, Events zu bearbeiten
- Das User Interface bei der Anzeige von Events wurde verbessert
- Es gibt nun eine Möglichkeit, direkt auf der Event-Seite eine neue Aktivität anzulegen
- Es wurde neu eine Beschreibung (mit Formatierungsmöglichkeiten) und ein Eventtyp hinzugefügt. Mögliche Typen lassen sich über das Vokabular definieren. Der Typ kann auf der Übersichtsseite zum Filtern verwendet werden.




#### <i class="ph ph-code"></i> Bug Fixes und Verbesserungen

- Wenn man eine Aktivität über eine DOI abgerufen hat und den Typ ändert, werden die Daten aus der DOI jetzt korrekt übernommen.
- Email-Einstellungen können jetzt korrekt gespeichert werden und SMTP wird vollumfänglich unterstützt.
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
- Wenn keine Topics existieren, wird das Topic-Filterwidget nicht mehr angezeigt
- Es wurde ein Problem behoben, durch das Journale nicht in der Tabelle aufgelistet wurden, wenn sie keine Impact Faktoren hatten
- Ein Problem wurde behoben, durch das bei Gruppen nicht die korrekten Aktivitäten angezeigt wurden

---

<span class="badge float-right">13.04.2025</span>
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

<span class="badge float-right">03.04.2025</span>
<a class="anchor" href="#version-1.4.2" id="version-1.4.2"></a>

## Version 1.4.2

####  <i class="ph ph-chart-line-up"></i> Statistiken

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

----

<span class="badge float-right">03.04.2025</span>
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

<span class="badge float-right">12.02.2025</span>
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

<span class="badge float-right">15.08.2024</span>
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

<span class="badge float-right">23.06.2024</span>
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

<span class="badge float-right">23.05.2024</span>
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

<span class="badge float-right">15.04.2024</span>
<a class="anchor" href="#version-1.3.3" id="version-1.3.3"></a>

## Version 1.3.3

#### <i class="ph ph-clock-counter-clockwise"></i> Historie

Es wurde eine überarbeitete Historie zu Aktivitäten hinzugefügt, die jetzt alle Ereignisse aufnimmt (zuvor nur jeweils das letzte Ereignis) und auch Änderungen speichert.
Sie ist auf der Detailseite einer Aktivität unter dem Reiter _Historie_ verfügbar.

---

<span class="badge float-right">22.02.2024</span>
<a class="anchor" href="#version-1.3.2" id="version-1.3.2"></a>

## Version 1.3.2

#### <i class="ph ph-code"></i> API Changes und API-Key

Es gibt ein paar kleinere Änderungen an der API, hauptsächlich wurden mehr Filtermöglichkeiten hinzugefügt.

Außerdem gibt es jetzt **API-Keys** mit denen sich die API nach außen hin absichern lässt. Erstellt euch einen einzigartigen API-Key und sichert damit eure Daten vor unbefugtem Zugriff. Die Einstellungen dazu findet ihr in den allgemeinen Einstellungen im Admin-Bereich.

#### <i class="ph ph-book"></i> API Docs

In der Dokumentation ist ein neuer Reiter API Docs, unter dem die wichtigsten Endpunkte beschrieben sind.

---

<span class="badge float-right">21.02.2024</span>
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

<span class="badge float-right">09.02.2024</span>
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

<span class="badge float-right">25.10.2023</span>
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

<span class="badge float-right">18.10.2023</span>
<a class="anchor" href="#version-1.2.1" id="version-1.2.1"></a>

## Version 1.2.1

Diese Version beinhaltet vornehmlich Bug Fixes und Optimierungen im Hintergrund. Außerdem wurde das Layout einiger Seiten verbessert, sowie die Schriftart für Überschriften verändert.

---

<span class="badge float-right">04.10.2023</span>
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

<span class="badge float-right">12.05.23</span>
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
- Es kann für jede Person einzeln der Anteil der SWS angegeben werden (nur bei affilierten Personen notwendig)
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

<span class="badge float-right">02.01.23</span>

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

- Das Menü wurde umstrukturiert und farblich etwas einfacher gehalten
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
  - Beispiele wurden hinzugefügt. Wenn man nun auf Beispiele klickt, werden ausgewählte Aktivitäten oberhalb des Formulars angezeigt. Dabei sind Datenfelder farblich gekennzeichnet und beim Hovern wird der Name des Datenfelds angezeigt. Die Datenfelder im Formular sind in der gleichen Farbe gekennzeichnet.
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
