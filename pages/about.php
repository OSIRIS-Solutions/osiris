

<h1>OSIRIS</h1>


<h4 class="text-muted font-weight-normal mt-0">The Open, Simple and Integrated Research Information System</h4>

<p>

    <?=lang('OSIRIS is my attempt to create a light-weight and open-source research information system; made-to-measure the needs of the DSMZ members.', 'OSIRIS ist mein Ansatz, ein leichtgewichtiges Open-Source Forschungsinformationssystem zu schaffen; maßgeschneidert auf die Bedürfnisse der DSMZ.')?>
</p>

<hr>

<style>
    .check-list, .check-list ul {
        margin: 0;
        padding-left: 1.2rem;
      }
      
      .check-list li {
        position: relative;
        list-style-type: none;
        padding-left: 2.5rem;
        margin-bottom: 0.5rem;
      }
      
      .check-list li:before {
          content: '\f0c8';
          font-family: 'Font Awesome 6 Pro';
          font-weight: 400;
          display: block;
          position: absolute;
          color: var(--muted-color);
          left: 0;
          top: 0;
      }
      .check-list li.checked::before {
        content: '\f14a';
        color: var(--success-color);
      }
      .check-list li.moved::before {
        content: '\f32b';
        color: var(--signal-color);
      }
</style>

<h2>To-Do and Roadmap</h2>

<h3>Grundlegende Funktionen der Applikation</h3>

<ul class="check-list">
    <li>Alle Datenfelder implementieren (Datenbank und Interface)
        <ul>
            <li class="checked">Publikationen</li>
            <li class="checked">Bücher und Zeitschriften</li>
            <li class="checked">Poster</li>
            <li class="checked">Vorträge</li>
            <li class="checked">Reviews und Editorials</li>
            <li class="checked">Gäste</li>
            <li class="checked">Doktoranden-Betreuung</li>
            <li class="moved">Drittmittel und Projekte?</li>
            <li class="moved">Habilitationen und Preise?</li>
            <li class="checked">Sonstige Aktivitäten</li>
        </ul>
    </li>
    <li class="checked">Alle Datenfelder können über ein Interface zur DB hinzugefügt werden</li>
    <li class="checked">Alle Datenfelder können bearbeitet und entfernt werden</li>
    <li class="checked">Übersichtsseite eines Wissenschaftlers</li>
    <li class="checked">Import der DSMZ-Bibliographie</li>
    <li class="checked">Import der Wissenschaftler und Journale</li>
    <li class="checked">Import der Impact Faktoren</li>
    <li class="checked">Such- und Filterfunktionen für die Übersichtsseiten</li>
    <li>Vermeidung von Datendoppelungen</li>
    <li class="checked">Quartal-Auswahl</li>
    <li>Knopf auf der Wissenschaftlerseite, um Daten zu bestätigen</li>
    <li class="checked">Übersetzung der Seite in zwei Sprachen</li>
    <li class="checked">Nutzerrollen zufügen (Admin, Controller, Group leader, Scientist)</li>
    <li>Zugriff auf verschiedene Seiten sind rollenspezifisch @Controlling: wer darf was?</li>
</ul>

<h3>Erweiterte Funktionen (Auswertung und Analyse)</h3>

<ul class="check-list">
    <li>Export-Funktionen:
        <ul>
            <li>Mgl. Formate: Word, PDF, CSV, BibTeX</li>
            <li>Alle Aktivitäten nur durch Controller</li>
            <li>Einzelaktivitäten für Forschende</li>
        </ul>
    </li>
    <li>Service: Impact factor-Abfrage (https://developer.clarivate.com/apis/wos-journal); zurzeit ein manueller Import erforderlich</li>
    <li>Implementation einer API, um anderen Services der DSMZ Abfragen zu erlauben</li>
    <li>LOM-Punktesystem</li>
    <li>Ko-Autoren-Netzwerk (chord diagram)</li>
    <li>Impact Factor-Verteilung</li>
    <li>Abteilungszusammenarbeit (als Netzwerk)</li>
</ul>