All endpoints are accessible via GET, do not require authentication, and return the following JSON structure:

```json
{
    "status": 200,
    "count": 51,
    "data": [
    // die Daten
    ]
}
```

The following shows each endpoint and the structure of `data`. The :id in the path is a placeholder for the respective ID. If several parameters are enclosed in angle brackets, all alternatives can be used to generate different outputs.

### `/portfolio/units` Get all organisational units

```json
[
    {
        "id": "DSMZ",
        "name": "Leibniz Institute DSMZ",
        "parent": null,
        "level": 0,
        "unit": "Institute",
        "name_de": "Leibniz-Institut DSMZ"
    },
    {
        "id": "MIOS",
        "name": "Microorganisms",
        "parent": "DSMZ",
        "level": 1,
        "unit": "Department",
        "name_de": "Mikroorganismen"
    },
    ...
]
```

### `/portfolio/unit/:id` Find unit by ID

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the element |


```json
{
        "_id": {
            "$oid": "65c5dc41fd518f66913527c7"
        },
        "id": "BID",
        "color": "#5db5b7",
        "name": "Bioinformatics, IT & Databases",
        "parent": "DSMZ",
        "level": 1,
        "unit": {
            "name": "Department",
            "name_de": "Abteilung",
            "head": "Head of Department",
            "head_de": "Abteilungsleitung"
        },
        "description": "<p>Bioinformatics, formerly designated as a key discipline for analysis of nucleotide sequence data, meanwhile covers a much broader range of databases and tools suitable for analysis and understanding of biological data in general. As a result of massive advances within the omics sector, especially within the field of Next Generation Sequencing, (semi-) automated tools and pipelines are needed to cope with data generation.</p>\n<p>Within DSMZ\u2019s bioinformatics department data is generated on site within the DNA and Sequencing Laboratory. Necessary high-performance computing resources for in-depth sequence analyses are powered by Scientific Computing.</p>\n<p>The DSMZ bioinformatics department is involved in the Leibniz Omics- Network LiON and firmly connected to in house research topics.</p>",
        "description_de": "<p>Die Bioinformatik, die fr\u00fcher als Schl\u00fcsseldisziplin f\u00fcr die Analyse von Nukleotidsequenzdaten bezeichnet wurde, umfasst inzwischen ein viel breiteres Spektrum von Datenbanken und Werkzeugen, die f\u00fcr die Analyse und das Verst\u00e4ndnis biologischer Daten im Allgemeinen geeignet sind. Durch die massiven Fortschritte im Omics-Sektor , insbesondere im Bereich des Next Generation Sequencing, werden (halb-)automatisierte Werkzeuge und Pipelines zur Bew\u00e4ltigung der Datengenerierung ben\u00f6tigt.</p>\n<p>In der Bioinformatik-Abteilung der DSMZ werden die Daten vor Ort im DNA- und Sequenzierlabor generiert. Die notwendigen Hochleistungs-Rechenressourcen f\u00fcr tiefgehende Sequenzanalysen werden vom Scientific Computing bereitgestellt.</p>\n<p>Die DSMZ-Bioinformatik-Abteilung ist in das Leibniz-Omics-Netzwerk LiON eingebunden und fest mit hauseigenen Forschungsthemen verbunden.</p>",
        "head": [
            "bob10",
            "lor15"
        ],
        "name_de": "Bioinformatik, IT & Datenbanken",
        "heads": [
            {
                "id": "651cecd8b3c97f11cc28cc47",
                "name": "Boyke Bunk",
                "img": "<img src=\"http://localhost/osiris/img/no-photo.png\" alt=\"Profilbild\" class=\"profile-img small\">",
                "position": null
            },
            {
                "id": "651cecd8b3c97f11cc28cd57",
                "name": "Lorenz Christian Reimer",
                "img": " <img src=\"http://localhost/osiris/img/users/lor15.jpg\" alt=\"Profilbild\" class=\"profile-img small\">",
                "position": "Head of department"
            }
        ]
    }

```

### `/portfolio/unit/:id/research` Get formatted research interests of a group

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the group |


```json
[
    {
        "title": "<i class=\"ph ph-graph\"></i> Knowledge Graph and AI",
        "title_de": "",
        "subtitle": "All data is used to build a comprehensive knowledge graph that gives novel insights into data relationships",
        "subtitle_de": "",
        "info": "<p>All data is used to build a comprehensive knowledge graph that gives novel insights into data relationships.\nWe apply AI-driven methods to predict novel knowledge based on existing data. Furthermore, we will make knowledge more accessible, e.g. by deplying AI-driven documentation and assistence.</p>",
        "info_de": null
    },
    {
        "title": "<i class=\"ph ph-book-open-text\"></i> Data Mining & Semantics",
        "title_de": "",
        "subtitle": "Knowledge extraction is empowered by NLP techniques to acquire new data more efficiantly",
        "subtitle_de": "",
        "info": "<p>Knowledge extraction is empowered by NLP techniques to acquire new data more efficiantly.</p>",
        "info_de": null
    },
    {
        "title": "<i class=\"ph ph-sparkle\"></i> Novel databases & Applications",
        "title_de": "",
        "subtitle": "We are developing novel databases and applications that encompass and exploit all our digital resources",
        "subtitle_de": "",
        "info": "<p>Our plans for exploiting the data</p>",
        "info_de": null
    },
    ...
]

```

### `/portfolio/unit/:id/numbers` Get numbers of data on a group

> Note: General and Research can only be 1 or 0. 0 means no data available. 

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the element |


```json
{
    "general": 1,
    "research": 1,
    "persons": 44,
    "publications": 717,
    "activities": 150,
    "memberships": 23,
    "projects": 2,
    "cooperation": 8
}
```

### `/portfolio/:context/:id/:type` Find

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `context` | One of the following: `unit`, `project`, `person` |
| {String} | `id` | Unique ID of the element |
| {String} | `type` | Type of actvities. One of the following: `publications`, `activities` (excl. publications), `all-activities` (everything) |


```json

[
    {
        "_id": {
            "$oid": "6458fcb30d695c593828763f"
        },
        "type": "publication",
        "year": 2023,
        "month": 5,
        "day": null,
        "subtype": "magazine",
        "html": "<a class='colorless' href='/activity/6458fcb30d695c593828763f'>Metabolism from the magic angle</a><br><small class='text-muted d-block'><a href='/person/651cecd8b3c97f11cc28cfc2'>Koblitz,&nbsp;J.</a><br> <i>Nature Chemical Biology</i> (2023)</small>",
        "search": "Koblitz,&nbsp;J. (2023) Metabolism from the magic angle. Nature Chemical Biology ",
        "icon": "<span data-toggle='tooltip' data-title='Non-refereed'><i class='ph text-publication ph-newspaper'></i></span>"
    },
    {
        "_id": {
            "$oid": "632da4672199cd3df8dbc166"
        },
        "year": 2023,
        "month": 1,
        "day": 6,
        "type": "publication",
        "subtype": "article",
        "html": "<a class='colorless' href='/activity/632da4672199cd3df8dbc166'>Media<i>Dive</i>: the expert-curated cultivation media database </a><br><small class='text-muted d-block'><a href='/person/651cecd8b3c97f11cc28cfc2'>Koblitz,&nbsp;J.</a>, <a href='/person/651cecd8b3c97f11cc28d002'>Halama,&nbsp;P.</a>, <a href='/person/651cecd8b3c97f11cc28ce32'>Spring,&nbsp;S.</a>, <a href='/person/651cecd8b3c97f11cc28cfe8'>Thiel,&nbsp;V.</a>, <a href='/person/651cecd8b3c97f11cc28ce25'>Baschien,&nbsp;C.</a>, <a href='/person/651cecd8b3c97f11cc28cd6f'>Hahnke,&nbsp;R.L.</a>, <a href='/person/651cecd8b3c97f11cc28cc6f'>Pester,&nbsp;M.</a>, <a href='/person/651cecd8b3c97f11cc28ce75'>Overmann,&nbsp;J.</a> and <a href='/person/651cecd8b3c97f11cc28cd57'>Reimer,&nbsp;L.C.</a><br> <i>Nucleic Acids Res</i> 51(D1): D1531\u20138 (2023) <i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i></small>",
        "search": "Koblitz, J., Halama, P., Spring, S., Thiel, V., Baschien, C., Hahnke, R.L., Pester, M., Overmann, J.* and Reimer, L.C.* (2023) MediaDive: the expert-curated cultivation media database. Nucleic Acids Res 51(D1): D1531\u20138. DOI: 10.1093/nar/gkac803",
        "icon": "<span data-toggle='tooltip' data-title='Article (refereed)'><i class='ph text-publication ph-file-text'></i></span>"
    },
    ...
]

```

### `/portfolio/:context/:id/teaching` Get Teaching activity of unit or person

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `context | One of the following: unit, person |
| {String} | `id` | Unique ID of the element |


```json
[
    {
        "id": "645e24be0d695c5938287641",
        "name": "MI03",
        "title": "\u00d6kologie von Mikroorganismen",
        "affiliation": "TU Braunschweig, Braunschweig, Deutschland",
        "count": 4
    },
    {
        "id": "656589327c0d4c58cf1db647",
        "name": "IB20A",
        "title": "Myxobakterien als Wirkstoffproduzenten",
        "affiliation": "TU Braunschweig",
        "count": 3
    },
    {
        "id": "645e3370c902176a283536ad",
        "name": "MI25",
        "title": "Struktur und Funktion mikrobieller Lebensgemeinschaften",
        "affiliation": "TU Braunschweig",
        "count": 2
    }
]

```

### `/portfolio/:context/:id/projects` Get all projects of unit or person

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `context | One of the following: unit, person |
| {String} | `id` | Unique ID of the element |


```json
[
    {
        "_id": {
            "$oid": "65c3789693d2b47e2911a3e2"
        },
        "name": "Bioindustry 4.0",
        "title": "RI services for deep digitalization of Industrial Biotechnology - towards smart biomanufacturing",
        "funder": "EU",
        "funding_organization": "EU Horizon-RIA",
        "funding_number": [
            "Grant agreement ID: 101094287"
        ],
        "role": "partner",
        "start": {
            "year": 2023,
            "month": 1,
            "day": 1
        },
        "end": {
            "year": 2026,
            "month": 12,
            "day": 31
        }
    },
    ...
]

```

### `/portfolio/unit/:id/cooperation` Get data for the coop-Chart

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the element |


Wichtig: nur verfügbar für Level 1-Gruppen (z.B. Abteilungen)!
```json
{
    "matrix": [
        [
            25,
            23
        ],
        [
            23,
            0
        ]
    ],
    "labels": {
        "MuTZ": {
            "id": "MuTZ",
            "name": "Human and Animal Cell Lines",
            "name_de": "Menschliche & Tierische Zellkulturen",
            "color": "#31407b",
            "count": 48,
            "selected": true
        },
        "BID": {
            "id": "BID",
            "name": "Bioinformatics, IT & Databases",
            "name_de": "Bioinformatik, IT & Datenbanken",
            "color": "#5db5b7",
            "count": 23,
            "selected": false
        }
    }
}

```

### `/portfolio/:context/:id/collaborators-map` Get all information for the collaborator map

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `context` | One of the following: unit, project |
| {String} | `id` | Unique ID of the element |


```json
[
    {
        "_id": "https://ror.org/035za2858",
        "count": 1,
        "data": {
            "name": "Biofaction (Austria)",
            "role": "partner",
            "type": "Company",
            "ror": "https://ror.org/035za2858",
            "location": "Vienna, Austria",
            "country": "AT",
            "lat": 48.201891,
            "lng": 16.391971
        }
    },
    {
        "_id": "https://ror.org/00r1edq15",
        "count": 1,
        "data": {
            "name": "University of Greifswald",
            "role": "partner",
            "type": "Education",
            "ror": "https://ror.org/00r1edq15",
            "location": "Greifswald, Germany",
            "country": "DE",
            "lat": 54.09311,
            "lng": 13.38786
        }
    },
    ...
]

```


### `/portfolio/unit/:id/staff` Get all persons associated to this unit

> If you want to exclude a person from showing up in Portfolio, just remove them from all organisational units. Since portfolio shows everything in the context of groups, the person won't show up anymore.
>
> If you want to see all persons, select the institute as unit.

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the group |


```json
[
    {
        "displayname": "Julia Koblitz",
        "academic_title": "Dr.",
        "position": "Group leader",
        "depts": [
            "BID" // Group-IDs
        ],
        "img": " <img src=\"http://localhost/osiris/img/users/juk20.jpg\" alt=\"Profilbild\" class=\"profile-img\">",
        "id": "651cecd8b3c97f11cc28cfc2"
    },
    ...
]
```


### `/portfolio/activity/:id` Get all information on an activity

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the activity |


```json
{
    "print": "<b>Koblitz,&nbsp;J.</b>, <b>Halama,&nbsp;P.</b>, <b>Spring,&nbsp;S.</b>, <b>Thiel,&nbsp;V.</b>, <b>Baschien,&nbsp;C.</b>, <b>Hahnke,&nbsp;R.L.</b>, <b>Pester,&nbsp;M.</b>, <b>Overmann,&nbsp;J.</b><sup>*</sup> and <b>Reimer,&nbsp;L.C.</b><sup>*</sup> (2023) Media<i>Dive</i>: the expert-curated cultivation media database. <i>Nucleic Acids Res</i> 51(D1): D1531\u20138. DOI: <a target='_blank' href='https://doi.org/10.1093/nar/gkac803'>10.1093/nar/gkac803</a> <i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i><br><small style='color:#878787;'> <sup>*</sup> Shared last authors</small>",
    "plain": "Koblitz,&nbsp;J., Halama,&nbsp;P., Spring,&nbsp;S., Thiel,&nbsp;V., Baschien,&nbsp;C., Hahnke,&nbsp;R.L., Pester,&nbsp;M., Overmann,&nbsp;J.* and Reimer,&nbsp;L.C.* (2023) MediaDive: the expert-curated cultivation media database. Nucleic Acids Res 51(D1): D1531\u20138. DOI: 10.1093/nar/gkac803  * Shared last authors",
    "portfolio": "<a class='colorless' href='/activity/632da4672199cd3df8dbc166'>Media<i>Dive</i>: the expert-curated cultivation media database </a><br><small class='text-muted d-block'><a href='/person/651cecd8b3c97f11cc28cfc2'>Koblitz,&nbsp;J.</a>, <a href='/person/651cecd8b3c97f11cc28d002'>Halama,&nbsp;P.</a>, <a href='/person/651cecd8b3c97f11cc28ce32'>Spring,&nbsp;S.</a>, <a href='/person/651cecd8b3c97f11cc28cfe8'>Thiel,&nbsp;V.</a>, <a href='/person/651cecd8b3c97f11cc28ce25'>Baschien,&nbsp;C.</a>, <a href='/person/651cecd8b3c97f11cc28cd6f'>Hahnke,&nbsp;R.L.</a>, <a href='/person/651cecd8b3c97f11cc28cc6f'>Pester,&nbsp;M.</a>, <a href='/person/651cecd8b3c97f11cc28ce75'>Overmann,&nbsp;J.</a> and <a href='/person/651cecd8b3c97f11cc28cd57'>Reimer,&nbsp;L.C.</a><br> <i>Nucleic Acids Res</i> 51(D1): D1531\u20138 (2023) <i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i></small>",
    "web": "<a class='colorless' href='/osiris/activities/view/632da4672199cd3df8dbc166'>Media<i>Dive</i>: the expert-curated cultivation media database </a><br><small class='text-muted d-block'><a href='/osiris/profile/juk20'>Koblitz,&nbsp;J.</a>, <a href='/osiris/profile/phh20'>Halama,&nbsp;P.</a>, <a href='/osiris/profile/ssp'>Spring,&nbsp;S.</a>, <a href='/osiris/profile/vet20'>Thiel,&nbsp;V.</a>, <a href='/osiris/profile/chb14'>Baschien,&nbsp;C.</a>, <a href='/osiris/profile/rih14'>Hahnke,&nbsp;R.L.</a>, <a href='/osiris/profile/mip17'>Pester,&nbsp;M.</a>, <a href='/osiris/profile/joo'>Overmann,&nbsp;J.</a> and <a href='/osiris/profile/lor15'>Reimer,&nbsp;L.C.</a><br> <i>Nucleic Acids Res</i> 51(D1): D1531\u20138 (2023) <i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i> <a href='/uploads/632da4672199cd3df8dbc166/gkac803_after proof.pdf' target='_blank' data-toggle='tooltip' data-title='pdf: gkac803_after proof.pdf' class='file-link'><i class='ph ph-file ph-file-pdf'></i></a></small>",
    "depts": {
        "BID": {
            "en": "Bioinformatics, IT & Databases",
            "de": "Bioinformatik, IT & Datenbanken"
        },
        "MIOS": {
            "en": "Microorganisms",
            "de": "Mikroorganismen"
        },
        "BUG": {
            "en": "Bioresources for Bioeconomy and Health Research",
            "de": "Bio\u00f6konomie und Gesundheitsforschung"
        },
        "M\u00d6D": {
            "en": "Microbial Ecology and Diversity",
            "de": "Mikrobielle \u00d6kologie und Diversit\u00e4t"
        }
    },
    "icon": "<span data-toggle='tooltip' data-title='Article (refereed)'><i class='ph text-publication ph-file-text'></i></span>",
    "type": "publication",
    "subtype": "article",
    "start": "2023-01-06",
    "end": "2023-01-06",
    "title": "Media<i>Dive</i>: the expert-curated cultivation media database ",
    "authors": "<a href='/person/651cecd8b3c97f11cc28cfc2'>Koblitz,&nbsp;J.</a>, <a href='/person/651cecd8b3c97f11cc28d002'>Halama,&nbsp;P.</a>, <a href='/person/651cecd8b3c97f11cc28ce32'>Spring,&nbsp;S.</a>, <a href='/person/651cecd8b3c97f11cc28cfe8'>Thiel,&nbsp;V.</a>, <a href='/person/651cecd8b3c97f11cc28ce25'>Baschien,&nbsp;C.</a>, <a href='/person/651cecd8b3c97f11cc28cd6f'>Hahnke,&nbsp;R.L.</a>, <a href='/person/651cecd8b3c97f11cc28cc6f'>Pester,&nbsp;M.</a>, <a href='/person/651cecd8b3c97f11cc28ce75'>Overmann,&nbsp;J.</a><sup>*</sup> and <a href='/person/651cecd8b3c97f11cc28cd57'>Reimer,&nbsp;L.C.</a><sup>*</sup>",
    "id": "632da4672199cd3df8dbc166",
    "year": 2023,
    "month": 1,
    "abstract": "Abstract We present MediaDive (https://mediadive.dsmz.de), a comprehensive and expert-curated cultivation media database, which comprises recipes, instructions and molecular compositions of &amp;gt;3200 standardized cultivation media for &amp;gt;40 000 microbial strains from all domains of life. MediaDive is designed to enable broad range applications from every-day-use in research and diagnostic laboratories to knowledge-driven support of new media design and artificial intelligence-driven data mining. It offers a number of intuitive search functions and comparison tools, for example to identify media for related taxonomic groups and to integrate strain-specific modifications. Besides classical PDF archiving and printing, the state-of-the-art website allows paperless use of media recipes on mobile devices for convenient wet-lab use. In addition, data can be retrieved using a RESTful web service for large-scale data analyses. An internal editor interface ensures continuous extension and curation of media by cultivation experts from the Leibniz Institute DSMZ, which is interlinked with the growing microbial collections at DSMZ. External user engagement is covered by a dedicated media builder tool. The standardized and programmatically accessible data will foster new approaches for the design of cultivation media to target the vast majority of uncultured microorganisms.",
    "doi": "10.1093/nar/gkac803",
    "pubmed": null,
    "projects": [
        {
            "id": "65cafac751758e649305501a",
            "name": "DiASPora",
            "title": "Digital&nbsp;Approaches for the&nbsp;Synthesis of&nbsp;Poorly&nbsp;Accessible Biodiversity Information",
            "funder": "Leibniz Wettbewerb",
            "funding_organization": "Leibniz SAW",
            "funding_number": [
                "K280/2019"
            ],
            "role": "coordinator",
            "start": {
                "year": 2020,
                "month": 4,
                "day": 1
            },
            "end": {
                "year": 2023,
                "month": 3,
                "day": 31
            }
        }
    ],
    "fields": [
        {
            "key_en": "Date",
            "key_de": "Datum",
            "value": "06.01.2023"
        },
        {
            "key_en": "Journal",
            "key_de": "Journal",
            "value": "Nucleic acids research"
        },
        {
            "key_en": "Issue",
            "key_de": "Issue",
            "value": "D1"
        },
        {
            "key_en": "Volume",
            "key_de": "Volume",
            "value": 51
        },
        {
            "key_en": "Pages",
            "key_de": "Seiten",
            "value": "D1531\u20138"
        },
        {
            "key_en": "Open-Access",
            "key_de": "Open-Access",
            "value": "<i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i> gold"
        },
        {
            "key_en": "Online Ahead Of Print",
            "key_de": "Online Ahead Of Print",
            "value": "<i class=\"ph ph-x text-danger\"></i>"
        }
    ],
    "bibtex": "@article{Koblitz2023,\n  Title = {MediaDive: the expert-curated cultivation media database},\n  Author = {Koblitz, Julia and Halama, Philipp and Spring, Stefan and Thiel, Vera and Baschien, Christiane and Hahnke, Richard\u00a0L and Pester, Michael and Overmann, J\u00f6rg and Reimer, Lorenz\u00a0Christian},\n  Journal = {Nucleic acids research},\n  Year = {2023},\n  Pages = {D1531\u20138},\n  Volume = {51},\n  Doi = {10.1093/nar/gkac803},\n  Abstract = {Abstract We present MediaDive (https://mediadive.dsmz.de), a comprehensive and expert-curated cultivation media database, which comprises recipes, instructions and molecular compositions of &amp;gt;3200 standardized cultivation media for &amp;gt;40 000 microbial strains from all domains of life. MediaDive is designed to enable broad range applications from every-day-use in research and diagnostic laboratories to knowledge-driven support of new media design and artificial intelligence-driven data mining. It offers a number of intuitive search functions and comparison tools, for example to identify media for related taxonomic groups and to integrate strain-specific modifications. Besides classical PDF archiving and printing, the state-of-the-art website allows paperless use of media recipes on mobile devices for convenient wet-lab use. In addition, data can be retrieved using a RESTful web service for large-scale data analyses. An internal editor interface ensures continuous extension and curation of media by cultivation experts from the Leibniz Institute DSMZ, which is interlinked with the growing microbial collections at DSMZ. External user engagement is covered by a dedicated media builder tool. The standardized and programmatically accessible data will foster new approaches for the design of cultivation media to target the vast majority of uncultured microorganisms.},\n}\n",
    "ris": "TY  - JOUR\nAU  - Koblitz, Julia\nAU  - Halama, Philipp\nAU  - Spring, Stefan\nAU  - Thiel, Vera\nAU  - Baschien, Christiane\nAU  - Hahnke, Richard\u00a0L\nAU  - Pester, Michael\nAU  - Overmann, J\u00f6rg\nAU  - Reimer, Lorenz\u00a0Christian\nTI  - Media<i>Dive</i>: the expert-curated cultivation media database\nT2  - Nucleic acids research\nPY  - 2023\nSP  - D1531\u20138\nVL  - 51\nDO  - 10.1093/nar/gkac803\nAB  - Abstract We present MediaDive (https://mediadive.dsmz.de), a comprehensive and expert-curated cultivation media database, which comprises recipes, instructions and molecular compositions of &amp;gt;3200 standardized cultivation media for &amp;gt;40 000 microbial strains from all domains of life. MediaDive is designed to enable broad range applications from every-day-use in research and diagnostic laboratories to knowledge-driven support of new media design and artificial intelligence-driven data mining. It offers a number of intuitive search functions and comparison tools, for example to identify media for related taxonomic groups and to integrate strain-specific modifications. Besides classical PDF archiving and printing, the state-of-the-art website allows paperless use of media recipes on mobile devices for convenient wet-lab use. In addition, data can be retrieved using a RESTful web service for large-scale data analyses. An internal editor interface ensures continuous extension and curation of media by cultivation experts from the Leibniz Institute DSMZ, which is interlinked with the growing microbial collections at DSMZ. External user engagement is covered by a dedicated media builder tool. The standardized and programmatically accessible data will foster new approaches for the design of cultivation media to target the vast majority of uncultured microorganisms.\nER  - \n"
}
```

### `/portfolio/project/:id` Get all information of the project

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the project |


```json
{
    "_id": {
        "$oid": "65cafac751758e649305501a"
    },
    "name": "DiASPora",
    "type": "Drittmittel",
    "title": "Digital&nbsp;Approaches for the&nbsp;Synthesis of&nbsp;Poorly&nbsp;Accessible Biodiversity Information",
    "contact": "joo",
    "status": "finished",
    "funder": "Leibniz Wettbewerb",
    "funding_organization": "Leibniz SAW",
    "funding_number": [
        "K280/2019"
    ],
    "purpose": "research",
    "role": "coordinator",
    "coordinator": "DSMZ",
    "start": {
        "year": 2020,
        "month": 4,
        "day": 1
    },
    "end": {
        "year": 2023,
        "month": 3,
        "day": 31
    },
    "grant_sum": null,
    "grant_income": null,
    "personal": null,
    "website": "https://diaspora-project.de/",
    "abstract": "The digitalization and integration of biodiversity information can generate substantial added value for existing data and yield novel scientific insights of relevance to bioeconomy, biotechnology, human health, and environmental protection. So far this potential has been exploited only rarely due the heterogeneity and fragmentation of data sources, and the little documentation, variable standards, and limited interoperability of data. For bacteria, research data are particularly diverse and broadly distributed; therefore these organisms will serve as the model group for the current project. The project DiASPora will establish an approach for synthesizing information for bacterial species by applying state-of-the-art data science methodology, genomics, and developing user-centric workflows. Extraction of phenotypic data from the microbiological literature will be achieved by large-scale text mining, applying artificial intelligence (AI) techniques that will be trained through the feedback of microbiologist curators. The data recovered will be hosted by the existing BacDive database and transformed into a machine readable and processable format using the Resource Description Framework (RDF). Subsequently, the transformed data will be used to establish a knowledge graph to generate innovative search options for the discovery of hidden data relationships. In parallel, phenotypic predictions will be derived from (meta)genomic data, through the application of metabolic models and comparison with the physiological and habitat data as obtained by data mining, and will be supported by an AI approach. The project is committed to an integral community engagement and an efficient dissemination of results. DiASPora builds upon the complementary expertise of three participating institutions, covering the fields of microbial databases and diversity research, bacterial genomics, text mining, artificial intelligence, and semantic technologies.",
    "public": true,
    "year": 2020,
    "month": 4,
    "created": "2024-02-13",
    "end-delay": "2024-3-31 23:59:59",
    "created_by": "juk20",
    "persons": [
        {
            "role": "Projektleitung",
            "name": "J\u00f6rg Overmann",
            "img": " <img src=\"http://localhost/osiris/img/users/joo.jpg\" alt=\"Profilbild\" class=\"profile-img small mr-20\">",
            "id": "651cecd8b3c97f11cc28ce75",
            "depts": {
                "M\u00d6D": {
                    "en": "Microbial Ecology and Diversity",
                    "de": "Mikrobielle \u00d6kologie und Diversit\u00e4t"
                }
            }
        },
        {
            "role": "Projektmitarbeiter:in",
            "name": "Julia Koblitz",
            "img": " <img src=\"http://localhost/osiris/img/users/juk20.jpg\" alt=\"Profilbild\" class=\"profile-img small mr-20\">",
            "id": "651cecd8b3c97f11cc28cfc2",
            "depts": {
                "BID": {
                    "en": "Bioinformatics, IT & Databases",
                    "de": "Bioinformatik, IT & Datenbanken"
                }
            }
        },
        {
            "role": "Beteiligte Person",
            "name": "Lorenz Christian Reimer",
            "img": " <img src=\"http://localhost/osiris/img/users/lor15.jpg\" alt=\"Profilbild\" class=\"profile-img small mr-20\">",
            "id": "651cecd8b3c97f11cc28cd57",
            "depts": {
                "BID": {
                    "en": "Bioinformatics, IT & Databases",
                    "de": "Bioinformatik, IT & Datenbanken"
                }
            }
        }
    ],
    "collaborators": [
        {
            "name": "German National Library of Medicine",
            "role": "partner",
            "type": "Archive",
            "ror": "https://ror.org/0259fwx54",
            "location": "Cologne, Germany",
            "country": "DE",
            "lat": 50.924697,
            "lng": 6.916419
        },
        {
            "name": "Technische Informationsbibliothek (TIB)",
            "role": "partner",
            "type": "Archive",
            "ror": "https://ror.org/04aj4c181",
            "location": "Hanover, Germany",
            "country": "DE",
            "lat": 52.37052,
            "lng": 9.73322
        }
    ],
    "activities": 13
}

```

### `/portfolio/project/:id/staff` Get staff related to the project

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the project |


```json
[
    {
        "displayname": "J\u00f6rg Overmann",
        "academic_title": "Prof. Dr.",
        "position": "Scientific Director",
        "depts": {
            "M\u00d6D": {
                "en": "Microbial Ecology and Diversity",
                "de": "Mikrobielle \u00d6kologie und Diversit\u00e4t"
            }
        },
        "img": " <img src=\"http://localhost/osiris/img/users/joo.jpg\" alt=\"Profilbild\" class=\"profile-img small mr-20\">",
        "id": "651cecd8b3c97f11cc28ce75",
        "role": "Beteiligte Person"
    },
    {
        "displayname": "Julia Koblitz",
        "academic_title": "Dr.",
        "position": "Group leader",
        "depts": {
            "BID": {
                "en": "Bioinformatics, IT & Databases",
                "de": "Bioinformatik, IT & Datenbanken"
            }
        },
        "img": " <img src=\"http://localhost/osiris/img/users/juk20.jpg\" alt=\"Profilbild\" class=\"profile-img small mr-20\">",
        "id": "651cecd8b3c97f11cc28cfc2",
        "role": "Beteiligte Person"
    },
    ...
]

```

### `/portfolio/person/:id` Get all publicly available info on a person

##### Parameters
| Type | Parameter | Description |
| ---- | ---- | ---- |
| {String} | `id` | Unique ID of the person (not the username) |


```json
{
    "displayname": "Julia Koblitz",
    "last": "Koblitz",
    "first": "Julia",
    "academic_title": "Dr.",
    "position": "Group leader",
    "depts": [
        {
            "id": "DSMZ",
            "name_en": "Leibniz Institute DSMZ",
            "name_de": "Leibniz-Institut DSMZ",
            "unit_en": "Institute",
            "unit_de": "Institut",
            "indent": 0,
            "hasChildren": true
        },
        {
            "id": "BID",
            "name_en": "Bioinformatics, IT & Databases",
            "name_de": "Bioinformatik, IT & Datenbanken",
            "unit_en": "Department",
            "unit_de": "Abteilung",
            "indent": 1,
            "hasChildren": true
        },
        {
            "id": "INTEGR",
            "name_en": "Data integration",
            "name_de": "Datenintegration",
            "unit_en": "Group",
            "unit_de": "Gruppe",
            "indent": 2,
            "hasChildren": false
        }
    ],
    "cv": [
        {
            "from": {
                "month": 8,
                "year": 2023
            },
            "to": {
                "month": null,
                "year": null
            },
            "position": "Head of Team Data Integration",
            "affiliation": "Leibniz institute DSMZ - German collection of microorganisms and cell cultures",
            "time": "8/2023 - Current"
        },
        {
            "from": {
                "month": 7,
                "year": 2020
            },
            "to": {
                "month": 7,
                "year": 2023
            },
            "position": "PostDoc for the DiASPora Project",
            "affiliation": "Leibniz institute DSMZ - German collection of microorganisms and cell cultures",
            "time": "7/2020 - 7/2023"
        },
        {
            "from": {
                "month": 9,
                "year": 2019
            },
            "to": {
                "month": 6,
                "year": 2020
            },
            "position": "PostDoc for the BRENDA Enzyme Database",
            "affiliation": "TU Braunschweig, Department of Bioinformatics and Biochemistry",
            "time": "9/2019 - 6/2020"
        },
        {
            "from": {
                "month": 7,
                "year": 2016
            },
            "to": {
                "month": 10,
                "year": 2019
            },
            "position": "Graduate student in bioinformatics",
            "affiliation": "TU Braunschweig, Department of Bioinformatics and Biochemistry",
            "time": "7/2016 - 10/2019"
        },
        {
            "from": {
                "month": 10,
                "year": 2014
            },
            "to": {
                "month": 5,
                "year": 2016
            },
            "position": "Master of Science Biology",
            "affiliation": "TU Braunschweig",
            "time": "10/2014 - 5/2016"
        },
        {
            "from": {
                "month": 10,
                "year": 2009
            },
            "to": {
                "month": null,
                "year": 2014
            },
            "position": "Bachelor of Science Biology",
            "affiliation": "Universit\u00e4t Bayreuth & Technische Universit\u00e4t Braunschweig",
            "time": "10/2009 - 2014"
        }
    ],
    "contact": {
        "mail": "julia.koblitz@dsmz.de",
        "mail_alternative": "hub@dsmz.de",
        "mail_alternative_comment": "For requests regarding Digital Diversity please contact:",
        "twitter": "JuliaKoblitz",
        "orcid": "0000-0002-7260-2129",
        "researchgate": "Julia-Koblitz",
        "google_scholar": "2G1YzvwAAAAJ",
        "webpage": "julia-koblitz.de"
    },
    "img": " <img src=\"http://localhost/osiris/img/users/juk20.jpg\" alt=\"Profilbild\" class=\"profile-img\">",
    "id": "651cecd8b3c97f11cc28cfc2",
    "highlighted": [
        {
            "id": "632da4672199cd3df8dbc166",
            "icon": "<span data-toggle='tooltip' data-title='Article (refereed)'><i class='ph text-publication ph-file-text'></i></span>",
            "html": "<a class='colorless' href='/activity/632da4672199cd3df8dbc166'>Media<i>Dive</i>: the expert-curated cultivation media database </a><br><small class='text-muted d-block'><a href='/person/651cecd8b3c97f11cc28cfc2'>Koblitz,&nbsp;J.</a>, <a href='/person/651cecd8b3c97f11cc28d002'>Halama,&nbsp;P.</a>, <a href='/person/651cecd8b3c97f11cc28ce32'>Spring,&nbsp;S.</a>, <a href='/person/651cecd8b3c97f11cc28cfe8'>Thiel,&nbsp;V.</a>, <a href='/person/651cecd8b3c97f11cc28ce25'>Baschien,&nbsp;C.</a>, <a href='/person/651cecd8b3c97f11cc28cd6f'>Hahnke,&nbsp;R.L.</a>, <a href='/person/651cecd8b3c97f11cc28cc6f'>Pester,&nbsp;M.</a>, <a href='/person/651cecd8b3c97f11cc28ce75'>Overmann,&nbsp;J.</a> and <a href='/person/651cecd8b3c97f11cc28cd57'>Reimer,&nbsp;L.C.</a><br> <i>Nucleic Acids Res</i> 51(D1): D1531\u20138 (2023) <i class=\"icon-open-access text-success\" title=\"Open Access (gold)\"></i></small>"
        }
    ],
    "numbers": {
        "publications": 15,
        "activities": 50,
        "teaching": 0,
        "projects": 2
    },
    "projects": {
        "current": [
            {
                "id": "65c3789693d2b47e2911a3e2",
                "name": "Bioindustry 4.0",
                "title": "RI services for deep digitalization of Industrial Biotechnology - towards smart biomanufacturing",
                "funder": "EU",
                "funding_organization": "EU Horizon-RIA",
                "role": "partner",
                "start": {
                    "year": 2023,
                    "month": 1,
                    "day": 1
                },
                "end": {
                    "year": 2026,
                    "month": 12,
                    "day": 31
                }
            }
        ],
        "past": [
            {
                "id": "65cafac751758e649305501a",
                "name": "DiASPora",
                "title": "Digital&nbsp;Approaches for the&nbsp;Synthesis of&nbsp;Poorly&nbsp;Accessible Biodiversity Information",
                "funder": "Leibniz Wettbewerb",
                "funding_organization": "Leibniz SAW",
                "role": "coordinator",
                "start": {
                    "year": 2020,
                    "month": 4,
                    "day": 1
                },
                "end": {
                    "year": 2023,
                    "month": 3,
                    "day": 31
                }
            }
        ]
    }
}
```

