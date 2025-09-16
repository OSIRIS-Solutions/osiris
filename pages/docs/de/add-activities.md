

# <i class="icon-activity-plus text-osiris"></i>    Aktivitäten hinzufügen


##  Eine Aktivität mittels einer DOI hinzufügen

Am einfachsten ist es, eine Aktivität über eine DOI oder einer Pubmed-ID hinzuzufügen.
    Dafür trägst du die DOI bzw. Pubmed-ID in den Suchschlitz ein:

<div class="demo">
    <div class="form-group">
        <label for="doi">Suche über die DOI oder Pubmed-ID:</label>
        <div class="input-group">
            <input type="text" class="form-control" value="10.1093/nar/gkab961" name="doi"
                id="search-doi">
            <div class="input-group-append">
                <button class="btn secondary" type="submit"><i class="ph ph-search"></i></button>
            </div>
        </div>
    </div>
</div>

OSIRIS sucht die ID in einem von drei verschiedenen Services (CrossRef, DataCite, Pubmed) und erkennt automatisch, um welche Art von Aktivität es sich handelt. Aus CrossRef und Pubmed werden ausschließlich Publikationen abgefragt. Über DataCite können aber auch andere Aktivitäten abgefragt werden, beispielsweise Poster, Präsentationen oder Software-Pakete. Um eine DOI für eine solche Aktivität zu bekommen, kann man die Dateien bei einem Anbieter wie [Zenodo](https://zenodo.org/) hochladen. Wichtig ist dabei, dass die Metadaten korrekt hinterlegt werden (z.B. Autoren).

Wenn OSIRIS die Aktivität im externen Service gefunden hat, wird das Formular so gut es geht vorausgefüllt. Dies klappt mit DOIs meist besser als bei Pubmed, da bei letzterem Autoreninformationen oft nur unzureichend hinterlegt sind. In jedem Fall sollen <b>alle Informationen noch einmal manuell überprüft</b> und ggf. korrigiert werden. Beim Titel ist auch auf eine korrekte Rechtschreibung und Formatierung zu achten. Die Formatierung sollte nach Möglichkeit der Formatierung im Orginaltitel entsprechen.

Einige Felder können nicht automatisch ausgefüllt werden, insbesondere wenn der Publisher die Informationen nicht in den Metadaten hinterlegt hat. Automatisch ausgefüllte Felder werden deshalb grün unterlegt, auch wenn die Daten in der öffentlichen Datenbank leer waren (beispielsweise bei fehlenden Seitenzahlen). Nicht markierte Felder müssen ggf. nachgetragen werden.

Falls eine Publikation abgerufen wird, deren Journal zurzeit noch nicht in der Datenbank vorhanden ist, öffnet sich das Fenster zur Journal-Auswahl. Mehr Infos dazu gibt es [weiter unten](#das-Journal-bearbeiten).

## Eine Aktivität manuell hinzufügen

Selbstverständlich kann eine Aktivität auch manuell hinzugefügt werden.

### Eine Kategorie auswählen

Beim manuellen Hinzufügen muss zuerst ausgewählt werden, um welche Art von Aktivität es sich handelt. 
Welche Kategorien zu finden sind, wurde von deiner Einrichtung konfiguriert. 
Hier findest du ein Beispiel, wie das aussehen kann. Um eine Aktivität hinzuzufügen, klickst du auf die Schaltfläche.

<div class="demo">
    <div class="select-btns" id="select-btns">
        <button class="btn btn-select text-publication" id="publication-btn"><i class="ph text-publication ph-book-bookmark"></i>Publikation</button>
        <button class="btn btn-select text-poster" id="poster-btn"><i class="ph text-poster ph-presentation-chart"></i>Poster</button>
        <button class="btn btn-select text-lecture" id="lecture-btn"><i class="ph text-lecture ph-chalkboard-teacher"></i>Vorträge</button>
        <button class="btn btn-select text-review" id="review-btn"><i class="ph text-review ph-article"></i>Reviews &amp; Editorials</button>
        <button class="btn btn-select text-teaching" id="teaching-btn"><i class="ph text-teaching ph-chalkboard-simple"></i>Lehre</button>
        <button class="btn btn-select text-students" id="students-btn"><i class="ph text-students ph-student"></i>Studierende &amp; Gäste</button>
        <button class="btn btn-select text-software" id="software-btn"><i class="ph text-software ph-desktop-tower"></i>Software &amp; Data</button>
        <button class="btn btn-select text-misc" id="misc-btn"><i class="ph text-misc ph-shapes"></i>Misc</button>
    </div>
</div>

Nachdem eine Aktivität ausgewählt wurde, öffnet sich ein Formular mit allen Datenfeldern, die für die Aktivität relevant sein können. Benötigte Datenfelder sind mit <span class="text-danger">*</span> markiert und können nicht leer gelassen werden.

Bei einigen Aktivitäten gibt es Unterkategorien, die einen Einfluss auf die Datenfelder haben. So kann man bei <span class="text-publication">Publikationen</span> noch den Publikationstyp auswählen, beispielsweise Journalartikel, Buch oder Dissertation. Die Unterkategorien werden mit ähnlichen Knöpfen wie oben zu sehen sind gesteuert und können erst ausgewählt werden, nachdem die Oberkategorie ausgewählt wurde. 


### Beispiele nutzen

Ganz oben im Formular gibt es einen kleinen Knopf, mit dem die <button class="btn secondary btn-sm">Beispiele</button> umgeschaltet werden können. Bei aktivierten Beispielen erscheint ein formatierter Text über dem Formular. Dieser Text ist abhängig, von der ausgewählten Aktivitätskategorie (und eventuell Unterkategorie). Hier als Beispiel ein Journalartikel:

<div class="demo">
    <span class="element-author" data-element="Autor(en)">Spring, S., Rohde, M., Bunk, B., Spröer, C., Will, S. E. and Neumann-Schaal, M. </span>
    (<span class="element-time" data-element="Jahr">2022</span>)
    <span class="element-title" data-element="Titel">New insights into the energy metabolism and taxonomy of Deferribacteres revealed by the characterization of a new isolate from a hypersaline microbial mat</span>.
    <span class="element-cat" data-element="Journal">Environmental microbiology </span>
    <span data-element="Issue, Volume, Pages">24(5):2543-2575</span>.
    DOI: http://dx.doi.org/<span class="element-link" data-element="DOI">10.1111/1462-2920.15999</span>
</div>


### Den Titel formatieren

Der Titel kann über einen Rich-Text-Editor bearbeitet und formatiert werden. Dabei ist es wichtig, dass Rechtschreibung und Formatierung mit der Originalarbeit übereinstimmen. Unterstützt werden dabei folgende Formatierungen: kursiv, unterstrichen, hochgestellt und tiefgestellt. Außerdem gibt es Shortcuts für die gängigsten griechischen Sonderzeichen. Hier kann mit dem Editor etwas herumgespielt werden:

<div class="demo">
    <label for="title" class="required element-title">
        <span style="">Titel</span>
    </label>
    <div class="form-group" id="title-editor"></div>
    <input type="text" class="form-control hidden" name="values[title]" id="title" required="" value="">  
    <script>
        initQuill(document.getElementById('title-editor'));
    </script> 
</div>

### Autoren bearbeiten

Um die Autorenliste zu bearbeiten, steht ein einfacher Autoreneditor zur Verfügung. Hier ist ein funktionierender Editor zum Ausprobieren:

<div class="demo">
    <label for="author" class="element-author">
        Autor(en) (in korrekter Reihenfolge, Format: Nachname, Vorname)
    </label>
    <div class="author-widget" id="author-widget">
        <div class="author-list p-10" id="author-list">
            <div class="author author-aoi ui-sortable-handle" ondblclick="toggleAffiliation(this);">
                Koblitz, Julia<input type="hidden" name="values[authors][]" value="Koblitz;Julia;1">
                <a onclick="removeAuthor(event, this);">×</a>
            </div>
        </div>
        <div class="footer">
            <div class="input-group small d-inline-flex w-auto">
                <input type="text" placeholder="Füge Autor hinzu ..." onkeypress="addAuthor(event);" id="add-author" list="scientist-list">
                <div class="input-group-append">
                    <button class="btn secondary h-full" type="button" onclick="addAuthor(event);">
                        <i class="ph ph-plus"></i>
                    </button>
                </div>
            </div>
            <div class="ml-auto" data-visible="article,preprint" id="author-numbers">
                <label for="first-authors">Anzahl der Erstautoren:</label>
                <input type="number" name="values[first_authors]" id="first-authors" value="1" class="form-control form-control-sm w-50 d-inline-block mr-10" autocomplete="off">
                <label for="last-authors">Letztautoren:</label>
                <input type="number" name="values[last_authors]" id="last-authors" value="1" class="form-control form-control-sm w-50 d-inline-block" autocomplete="off">
            </div>
        </div>
    </div>
</div>


Um einen **Autor hinzuzufügen**, musst du ihn in das Feld eintragen, das mit "Add author ..." gekennzeichnet ist. Nutze dafür bitte das Format <code>Nachname, Vorname</code>, damit OSIRIS die Autoren korrekt zuordnen kann. Bestätigen kannst du durch Drücken von <kbd>Enter</kbd> oder den <i class="ph ph-plus"></i>-Knopf. Autoren deines Instituts werden in einer Liste vorgeschlagen. Ein Autor aus der Liste wird automatisch zum Institut zugeordnet.

Um einen **Autor zu entfernen**, musst du auf das &times; hinter seinem Namen klicken.

Um die **Autorenreihenfolge zu ändern**, kannst du einen Autoren nehmen und ihn mittels Drag & Drop an die gewünschte Position ziehen.

Um einen **Autor zum Institut zugehörig zu markieren**, kannst du ihn einfach mit Doppelklick anklicken. Der Name wird dann blau markiert und das Kürzel des Instituts (oder ein *) taucht davor auf. Es ist wichtig für die Berichterstattung, dass alle Autoren ihrer Zugehörigkeit nach markiert sind! Wenn Autoren zwar Beschäftigte deines Instituts sind, es aber zum Zeitpunkt der Aktivität nicht waren, dürfen sie nicht entsprechend markiert werden!

Verschrieben? Ein Autor wird nicht korrekt einem Nutzer zugeordnet? Nachdem du den Datensatz hinzugefügt hast, kannst du die Autorenliste im Detail noch einmal bearbeiten. Lies dazu den [folgenden Abschnitt](#der-autoren-editor).

### Der Autoren-Editor

Nachdem eine Aktivität hinzugefügt wurde, steht ein detaillierter Autoren-Editor zur Verfügung. Dazu klickt man auf der Übersichtsseite der Aktivität bei den Autoren auf *Bearbeiten*.

Bei editierten Büchern gibt es das gleiche auch für Editoren.

Im Autoreneditor öffnet sich nun eine Tabelle, mit allen Details zu den Autoren einer Aktivität. Diese Tabelle kann folgendermaßen aussehen:

<div class="demo">
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>Last name</th>
                <th>First name</th>
                <th>Position</th>
                <th>*</th>
                <th>Username</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="author-detail-editor" class="ui-sortable">
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[0][last]" type="text" class="form-control" value="Becker">
                </td>
                <td>
                    <input name="authors[0][first]" type="text" class="form-control" value="Patrick">
                </td>
                <td>
                    <select name="authors[0][position]" class="form-control">
                        <option value="first" selected="">first</option>
                        <option value="middle">middle</option>
                        <option value="corresponding">corresponding</option>
                        <option value="last">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-0" name="authors[0][aoi]" value="1">
                        <label for="checkbox-0" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[0][user]" type="text" class="form-control" list="user-list" value="">
                    <input name="authors[0][approved]" type="hidden" class="form-control" value="">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[1][last]" type="text" class="form-control" value="Kirstein">
                </td>
                <td>
                    <input name="authors[1][first]" type="text" class="form-control" value="Sarah">
                </td>
                <td>
                    <select name="authors[1][position]" class="form-control">
                        <option value="first" selected="">first</option>
                        <option value="middle">middle</option>
                        <option value="corresponding">corresponding</option>
                        <option value="last">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-1" name="authors[1][aoi]" value="1">
                        <label for="checkbox-1" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[1][user]" type="text" class="form-control" list="user-list" value="sak20">
                    <input name="authors[1][approved]" type="hidden" class="form-control" value="">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[3][last]" type="text" class="form-control" value="Koblitz">
                </td>
                <td>
                    <input name="authors[3][first]" type="text" class="form-control" value="Julia">
                </td>
                <td>
                    <select name="authors[3][position]" class="form-control">
                        <option value="first">first</option>
                        <option value="middle" selected="">middle</option>
                        <option value="corresponding">corresponding</option>
                        <option value="last">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-3" name="authors[3][aoi]" value="1" checked="">
                        <label for="checkbox-3" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[3][user]" type="text" class="form-control" list="user-list" value="juk20">
                    <input name="authors[3][approved]" type="hidden" class="form-control" value="1">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[4][last]" type="text" class="form-control" value="Buschen">
                </td>
                <td>
                    <input name="authors[4][first]" type="text" class="form-control" value="Ramona">
                </td>
                <td>
                    <select name="authors[4][position]" class="form-control">
                        <option value="first">first</option>
                        <option value="middle" selected="">middle</option>
                        <option value="corresponding">corresponding</option>
                        <option value="last">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-4" name="authors[4][aoi]" value="1">
                        <label for="checkbox-4" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[4][user]" type="text" class="form-control" list="user-list" value="">
                    <input name="authors[4][approved]" type="hidden" class="form-control" value="">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[20][last]" type="text" class="form-control" value="Neumann-Schaal">
                </td>
                <td>
                    <input name="authors[20][first]" type="text" class="form-control" value="Meina">
                </td>
                <td>
                    <select name="authors[20][position]" class="form-control">
                        <option value="first">first</option>
                        <option value="middle">middle</option>
                        <option value="corresponding" selected="">corresponding</option>
                        <option value="last">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-20" name="authors[20][aoi]" value="1" checked="">
                        <label for="checkbox-20" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[20][user]" type="text" class="form-control" list="user-list" value="men17">
                    <input name="authors[20][approved]" type="hidden" class="form-control" value="1">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <i class="ph ph-dots-six-vertical text-muted handle ui-sortable-handle"></i>
                </td>
                <td>
                    <input name="authors[21][last]" type="text" class="form-control" value="Rabus">
                </td>
                <td>
                    <input name="authors[21][first]" type="text" class="form-control" value="Ralf">
                </td>
                <td>
                    <select name="authors[21][position]" class="form-control">
                        <option value="first">first</option>
                        <option value="middle">middle</option>
                        <option value="corresponding">corresponding</option>
                        <option value="last" selected="">last</option>
                    </select>
                </td>
                <td>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="checkbox-21" name="authors[21][aoi]" value="1">
                        <label for="checkbox-21" class="blank"></label>
                    </div>
                </td>
                <td>
                    <input name="authors[21][user]" type="text" class="form-control" list="user-list" value="">
                    <input name="authors[21][approved]" type="hidden" class="form-control" value="">
                </td>
                <td>
                    <button class="btn" type="button" onclick="$(this).closest('tr').remove()"><i
                            class="ph ph-trash"></i></button>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr id="last-row">
                <td></td>
                <td colspan="6">
                    <button class="btn" type="button" onclick="addAuthorRow()"><i class="ph ph-plus"></i> Autor
                        hinzufügen</button>
                </td>
            </tr>
        </tfoot>
    </table>
    <script>
        var counter = 10;
        function addAuthorRow() {
            counter++;
            var tr = $('<tr>')
            tr.append('<td><i class="ph ph-dots-six-vertical text-muted handle"></i></td>')
            tr.append('<td><input name="authors[' + counter + '][last]" type="text" class="form-control"></td>')
            tr.append('<td><input name="authors[' + counter + '][first]" type="text" class="form-control"></td>')
            tr.append('<td><select name="authors[' + counter + '][position]" class="form-control"><option value="first">first</option><option value="middle" selected>middle</option><option value="corresponding">corresponding</option><option value="last">last</option></select></td>')
            tr.append('<td><div class="custom-checkbox"><input type="checkbox" id="checkbox-' + counter + '" name="authors[' + counter + '][aoi]" value="1"><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
            tr.append('<td> <input name="authors[' + counter + '][user]" type="text" class="form-control" list="user-list"></td>')
            var btn = $('<button class="btn" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
                $(this).closest('tr').remove();
            });
            tr.append($('<td>').append(btn))
            $('#author-detail-editor').append(tr)
        }
        $(document).ready(function() {
            $('#author-detail-editor').sortable({
                handle: ".handle",
            });
        })
    </script>
</div>


Gleich am Anfang jeder Zeile gibt es einen sog. Handle (<i class="ph ph-dots-six-vertical text-muted"></i>), mit dem die Reihenfolge der Autoren durch Drag & Drop verändert werden kann. Es folgen Vor- und Nachname des Autoren. Der Nachname ist ein Pflichtfeld, der Vorname kann (beispielsweise bei Konsortien) weggelassen werden. 

Die Position des Autors kann durch ein Dropdown-Menü ausgewählt werden. Auf diese Weise können mehrere Autoren als Erst- bzw. Letztautoren festgelegt werden (bei geteilter Autorenschaft). Hier kann auch ein Corresponding author festgelegt werden, falls dieser weder Erst- noch Letztautor ist. 

Es folgt eine Checkbox, in der angegeben wird, ob der Autor beim Zeitpunkt der Veröffentlichung Angehöriger deines Instituts war oder nicht. Im nächsten Feld kann der Nutzeraccount hinterlegt werden, mit dem dieser Autor verknüpft ist. Im oben gezeigten Beispiel ist zu sehen, dass man nicht dem Institut zugehörig sein muss, um die Aktivitäten mit dem Nutzerprofil zu verknüpfen. Die zweite Erstautorin ist einem Nutzerkonto zugeordnet, war aber zum Zeitpunkt der Arbeit nicht mit dem Institut affiliiert. Die Aktivität ist mit ihrem Account verknüpft und wird in ihrem Profil dargestellt, sie wird jedoch im Report nicht als affiliierte Autorin markiert und bekommt für die Aktivität keine Coins.

Zu guter Letzt ist eine Schaltfläche zu sehen (<i class="ph ph-trash"></i>), mit der ein Autor komplett gelöscht werden kann.

Am Fuß der Tabelle können über die Schaltfläche <span class="btn btn-sm">+ Autor hinzufügen</span> weitere Autoren der Tabelle hinzugefügt werden. Neue Zeilen mit Autoren haben die gleichen Funktionen wie oben gezeigt.


### Das Journal bearbeiten

Journale sind bei <span class="text-publication">Journalartikeln</span> und <span class="text-review">Reviews &amp; Editorials</span> relevant. 

Aus Gründen der Standardisierung kann ein Journal nicht als Freitext-Feld eingetragen werden. Stattdessen ist folgendes Modul zu finden:

<div class="demo">
    <div class="data-module col-12" data-module="journal">
        <a href="/osiris/docs/add-activities#das-journal-bearbeiten" target="_blank" class="required float-right">
            <i class="ph ph-question"></i> Hilfe                    </a>
        <label for="journal" class="element-cat required">Journal</label>
        <a href="#journal-select" id="journal-field" class="module">
            <span class="float-right text-secondary"><i class="ph ph-edit"></i></span>
            <div id="selected-journal">
                <span class="title">Kein Journal ausgewählt</span>
                                        </div>
            <input type="hidden" class="form-control hidden" name="values[journal]" value="" id="journal" list="journal-list" required="" readonly="">
            <input type="hidden" class="form-control hidden" name="values[journal_id]" value="" id="journal_id" required="" readonly="">
        </a>
    </div>
</div>

Um das Journal zu bearbeiten, reicht ein Klick in dieses Feld. Dadurch öffnet sich folgendes Fenster, indem man mittels Namen oder (bevorzugt) ISSN nach einem Journal in OSIRIS suchen kann. 
Im folgenden Beispiel sind in OSIRIS zwei Journale vorhanden, die dem Suchterm entsprechen:

<div class="demo" id="journal-select">
    <div class="modal-content">
        <label for="journal-search">Suche Journal nach Name oder ISSN</label>
        <div class="input-group">
            <input type="text" class="form-control is-valid" list="journal-list" id="journal-search" value="Nucleic acid" data-value="Nucleic acid">
            <div class="input-group-append">
                <button class="btn"><i class="ph ph-search"></i></button>
            </div>
        </div>
        <table class="table table-simple">
            <tbody id="journal-suggest"><tr><td class="w-50"><button class="btn" title="select"><i class="ph ph-check"></i></button></td><td><h5 class="m-0">Nucleic acid therapeutics</h5><span class="float-right">Mary Ann Liebert, Inc.</span><span class="text-muted">2159-3345, 2159-3337, 1545-4576</span></td></tr><tr><td class="w-50"><button class="btn" title="select"><i class="ph ph-check"></i></button></td><td><h5 class="m-0">Nucleic acids research</h5><span class="float-right">Oxford University Press</span><span class="text-muted">1362-4962, 0305-1048</span></td></tr><tr><td><button class="btn">Suche im NLM-Katalog</button></td></tr></tbody>
        </table>
    </div>
<div class="text-muted text-center mt-10">Dieses Fenster ist aus technischen Gründen nicht funktional.</div>
</div>

Ein Journal kann ausgewählt werden, indem man auf den Haken <span class="btn btn-sm"><i class="ph ph-check"></i></span> klickt. Das Fenster schließt sich automatisch und das Modul wird ausgefüllt. Das sieht dann wie folgt aus:

<div class="demo">
    <div class="data-module col-12" data-module="journal">
        <a href="/osiris/docs/add-activities#das-journal-bearbeiten" target="_blank" class="required float-right">
            <i class="ph ph-question"></i> Hilfe                    </a>
        <label for="journal" class="element-cat required">Journal</label>
        <a href="#journal-select" id="journal-field" class="module">
            <span class="float-right text-secondary"><i class="ph ph-edit"></i></span>
            <div id="selected-journal"><h5 class="m-0">Nucleic acids research</h5><span class="float-right">Oxford University Press</span><span class="text-muted">ISSN: 1362-4962, 0305-1048</span></div>
            <input type="hidden" class="form-control hidden" name="values[journal]" value="Nucleic acids research" id="journal" list="journal-list" required="" readonly="">
            <input type="hidden" class="form-control hidden" name="values[journal_id]" value="6364d154f7323cdc82531a01" id="journal_id" required="" readonly="">
        </a>
    </div>
</div>

Sollte das gesuchte Journal nicht in OSIRIS gefunden werden, kann man durch Klick auf <span class="btn btn-sm">Suche im NLM-Katalog</span> eine erweiterte Suche starten. Dabei werden alle bei NLM indizierten Journale durchsucht und vorgeschlagen. Hier wird ebenfalls wieder über den Haken bestätigt. Sollte das ausgewählte Journal bereits in OSIRIS vorhanden sein (möglicherweise unter einem etwas anderen Namen), wird das bereits vorhandene Journal ausgewählt. Der entsprechende Abgleich findet über die ISSN statt und du wirst darüber durch ein Pop-Up informiert. Sollte das Journal noch unbekannt sein, fragt OSIRIS alle Informationen dazu von externen Services ab. **Dieser Prozess kann einen Moment dauern**, bitte warte also, bis das Modul ausgefüllt wurde.

## Erweiterte Funktionen

### Dokumente hinterlegen
Nachdem eine Aktivität hinzugefügt wurde, können Dokumente hinterlegt werden. Idealerweise werden Publikationen, Poster und Vorträge mit einem PDF-Anhang supplementiert. Dafür geht man auf der Detailseite einer Aktivität (auf der man nach dem Erstellen landet), auf den Knopf **Datei hochladen**.

Auf der folgenden Seite findet sich ganz oben eine Übersicht zu der Aktivität, die man gerade bearbeitet. Es folgt eine Liste mit eventuell bereits vorhandenen Dateien, die an dieser Stelle auch heruntergeladen (<i class="ph ph-download text-secondary"></i>) bzw. gelöscht  (<i class="ph ph-trash text-danger"></i>) werden können. 

Weiter unten findet sich ein Formular, mit dem neue Dokumente hochgeladen werden können. Diese dürfen eine Maximalgröße von 16 MB nicht überschreiten und sollten sich nach Möglichkeit in einem Standardformat befinden. Gute Beispiele sind PDF (bevorzugt), PPTX, XLSX, DOCX. 

Bitte bemerke, dass sich PPTX und DOCX über die Exportieren-Funktion in Microsoft Office ganz einfach in PDF umwandeln lassen (Datei > Exportieren > PDF/XPS-Dokument erstellen). **Aus den folgenden Gründen empfehlen wir, PDF-Dokumente hochzuladen**:
- PDF kann oftmals direkt im Browser geöffnet werden
- PDF wird auf allen Geräten gleich dargestellt (unabhängig vom Betriebssystem, veknüpften Bildern und installierten Schriftarten)
- PDF hat durch die Komprimierung oftmals eine geringere Dateigröße als andere Formate
- PDF-Dokumente lassen sich nicht so leicht versehentlich bearbeiten
- PDF ist weniger anfällig für Viren-Befall



### Autorennotizen

Es ist möglich, in OSIRIS eigene Notizen zu einer Aktivität zu hinterlassen. 
Diese Notiz ist nur den Autoren der Aktivität und den Admins (a.k.a Julia und das Controlling) sichtbar. 
Um eine Notiz zu hinterlassen, muss beim Hinzufügen bzw. Bearbeiten einer Aktivität die folgende Box ausgeklappt werden (ganz unten vor dem Knopf zum Abschicken des Formulars):

<div class="demo">
    <a onclick="$(this).next().toggleClass('hidden')">
        <label onclick="$(this).next().toggleClass('hidden')" for="comment" class="cursor-pointer">
            <i class="ph ph-plus"></i> Notiz (Nur sichtbar für Autoren und Admins)
        </label>
    </a>
    <textarea name="values[comment]" id="comment" cols="30" rows="2" class="form-control hidden"></textarea>

</div>

Beispiele für Notizen ist der Titel von Publikationen, die gereviewed wurden, Kommentare zum vorraussichtlichen Veröffentlichungstermin von Online ahead of print-Artikeln, o.ä.

### Editorkommentare

Wenn du eine Aktivität abänderst, werden alle deine Koautoren darüber benachrichtigt und müssen eventuell erneut bestätigen, dass sie Autoren sind und die Aktivität überprüft haben. Um ihnen diesen Prozess zu vereinfachen, kannst du mitteilen, was genau du geändert hast. Dafür gibt es einen Bearbeitungs-Bereich im Formular, der nur sichtbar wird, wenn du eine vorhandene Aktivität bearbeitest. In folgendem Beispiel wurde nur die Rechtschreibung des Titels verändert:

<div class="demo">
    <div class="alert signal p-10 mb-10">
        <div class="title">
            Bearbeitungs-Bereich 
        </div>
        <label for="editor-comment">Editor-Kommentar (teile deinen Ko-Autoren mit, was du geändert hast)</label>
        <textarea name="values[editor-comment]" id="editor-comment" cols="30" rows="2" class="form-control">Als Open Access markiert.</textarea>
        <div class="mt-10">
            <div class="custom-checkbox" id="minor-div">
                <input type="checkbox" id="minor" value="1" name="minor" data-value="1">
                <label for="minor">Änderungen sind minimal und Koautoren müssen nicht benachrichtigt werden.</label>
            </div>
            <small class="text-muted">
                Bitte beachte, dass Änderungen an den Autoren ignoriert werden, wenn dieser Haken gesetzt ist.
            </small>
        </div>
    </div>
</div>

So sieht diese Information jetzt auf der Prüfseite deiner Koautoren aus:
<div class="demo">
    <div class="row py-10 px-20">
        <div class="col-md-6">
            <p class="mt-0">
                <b class="text-lecture">
                    <span data-toggle="tooltip" data-title="Vortrag"><i
                            class="ph text-lecture ph-chalkboard-teacher"></i></span> Vortrag </b> <br>
                <a class="colorless" href="/osiris/activities/view/650449e74430390609471786">Open-Source CRIS am
                    Beispiel von OSIRIS</a><br><small class="text-muted d-block"><a
                        href="/osiris/profile/juk20">Koblitz,&nbsp;J.</a> and <a
                        href="/osiris/profile/dok21">Koblitz,&nbsp;D.</a><br> Workshop-Reihe "Stärkung von CRIS",
                    Online. 07.09.2023, short <a
                        href="/uploads/650449e74430390609471786/OSIRIS_Leibniz-CRIS_Open-Source.pdf" target="_blank"
                        data-toggle="tooltip" data-title="pdf: OSIRIS_Leibniz-CRIS_Open-Source.pdf"
                        class="file-link"><i class="ph ph-file ph-file-pdf"></i></a></small>
            </p>
            <div class="" id="approve-650449e74430390609471786">
                Ist dies deine Aktivität? <br>
                <div class="btn-group mr-10">
                    <button class="btn small text-success" onclick="_approve('650449e74430390609471786', 1)"
                        data-toggle="tooltip" data-title="Ja, und ich war der DSMZ angehörig">
                        <i class="ph ph-check ph-fw"></i>
                    </button>
                    <button class="btn small text-signal" onclick="_approve('650449e74430390609471786', 2)"
                        data-toggle="tooltip" data-title="Ja, aber ich war nicht der DSMZ angehörig">
                        <i class="ph ph-push-pin-slash ph-fw"></i>
                    </button>
                    <button class="btn small text-danger" onclick="_approve('650449e74430390609471786', 3)"
                        data-toggle="tooltip" data-title="Nein, das bin ich nicht">
                        <i class="ph ph-x ph-fw"></i>
                    </button>
                </div>
                <a target="_blank" href="/osiris/activities/view/650449e74430390609471786"
                    class="btn small text-secondary" data-toggle="tooltip" data-title="Aktivität ansehen">
                    <i class="ph ph-arrow-fat-line-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-6">
            <span class="badge primary float-md-right">27.05.2024</span>
            <h5 class="m-0">
                Bearbeitet von Dominic Koblitz </h5>
            <blockquote class="signal">
                <div class="title">
                    Kommentar </div>
                Ort wurde aktualisiert.
            </blockquote>
            <div class="font-weight-bold mt-10">Änderungen an der Aktivität:</div>
            <table class="table simple w-auto small border px-10">
                <tbody>
                    <tr>
                        <td class="pl-0">
                            <span class="key">Ort</span>
                            <span class="del text-danger">-</span>
                            <i class="ph ph-arrow-right mx-10"></i>
                            <span class="ins text-success">Online</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


Falls du der Meinung sind, dass deine Änderungen zu minimal sind, um eine Kontrolle durch die Koautoren erforderlich zu machen, kannst du auch den entsprechenden Haken setzen. In den meisten Fällen gehört es aber zum guten Ton, seine Koautoren über etwaige Änderungen zu informieren.


## Eine Aktivität kopieren

Bei den folgenden Aktivitäten ist es zurzeit möglich, eine Kopie anzulegen: Poster, Lecture, Review, Misc, Students.
Die Idee ist, dass man eine Aktivität wiederholt, wobei nur kleine Änderungen vorzunehmen sind, beispielsweise am Datum.

Ein Beispiel:

Ich halte einen Vortrag auf einer Konferenz. Auf einem Minisymposium ein paar Wochen später halte ich exakt den gleichen Vortrag noch einmal. Dafür gibt es in OSIRIS eine eigene Vortragskategorie, denn neben Kurz- und Langvoträgen gibt es auch noch "Repetitions". Ich gehe also in OSIRIS und wähle den Vortrag bei der Konferenz aus. Dort finde ich folgenden Knopf:

<div class="demo">
    <div class="btn-group">
    <span class="btn primary">
        <i class="ph ph-regular ph-pencil-simple-line"></i>
        Bearbeiten            
    </span>
    <span class="btn primary active">
        <i class="ph ph-copy"></i>
        Kopie            
    </span>
    </div>
</div>

Darauf klicke ich und bekomme ein Formular angezeigt, in dem alle Daten zu meinem Vortrag schon vorausgefüllt sind. OSIRIS merkt sogar, dass ich einen Vortrag kopiere und wählt automatisch die Kategorie "Repetition" aus. Ich muss jetzt nur noch das Datum, die Konferenz und den Ort anpassen. Titel und Autoren stimmen. 

Noch einfacher ist es beispielsweise bei Reviews, da ich hier nur das Datum austauschen muss, wenn ich mehrmals für das gleiche Journal tätig war. Und wenn mehrere Gäste zur gleichen Veranstaltung kommen, kann ich die Aktivität kopieren und nur die Namen anpassen.
