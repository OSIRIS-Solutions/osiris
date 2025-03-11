from rdflib import Graph, Namespace
import json
import requests
from urllib.parse import urlparse

# SKOS Namespace
SKOS = Namespace("http://www.w3.org/2004/02/skos/core#")

# Funktion zur Extraktion der letzten URI-Komponente als ID
def extract_id(uri):
    return urlparse(uri).path.split("/")[-1]

# TTL-Datei einlesen
def parse_ttl_to_json(ttl_string):
    g = Graph()
    g.parse(data=ttl_string, format="ttl")
    
    concepts = {}
    
    # Erst alle Konzepte einlesen
    for concept in g.subjects(predicate=SKOS.prefLabel):
        concept_id = extract_id(str(concept))
        if concept_id == '': continue
        labels = {label.language: str(label) for label in g.objects(concept, SKOS.prefLabel)}
        scope_notes = {note.language: str(note) for note in g.objects(concept, SKOS.scopeNote)}
        examples = {ex.language: str(ex) for ex in g.objects(concept, SKOS.example)}
        broader = [extract_id(str(b)) for b in g.objects(concept, SKOS.broader)]
        
        concepts[concept_id] = {
            "id": concept_id,
            "labels": labels,
        }
        
        if (broader):
            concepts[concept_id]["scope_notes"] = scope_notes
            concepts[concept_id]["examples"] = examples
            concepts[concept_id]["broader"] = broader
        else:
            concepts[concept_id]["children"] = []
    
    # Dann die Hierarchie aufbauen
    root_nodes = {}
    for concept_id, data in concepts.items():
        if data.get("broader"):
            for parent_id in data["broader"]:
                if parent_id in concepts:
                    concepts[parent_id]["children"].append(data)
        else:
            root_nodes[concept_id] = data
    
    return root_nodes

# Pfad zur TTL-Datei
ttl_file = "https://raw.githubusercontent.com/KDSF-FFK/kdsf-ffk/refs/heads/main/FFK.ttl"

# Download der TTL-Datei
response = requests.get(ttl_file)

json_data = parse_ttl_to_json(response.text)
json_data = list(json_data.values())

# JSON speichern
with open("kdsf-ffk.json", "w", encoding="utf-8") as f:
    json.dump(json_data, f, indent=4, ensure_ascii=False)

print("JSON-Datei wurde gespeichert.")


# Funktion zum Export als PHP Array
def save_as_php_array(data, filename):
    def to_php_array(obj, indent=0):
        if isinstance(obj, dict):
            php_array = "[\n"
            for key, value in obj.items():
                php_array += "    " * (indent + 1) + f"'{key}' => " + to_php_array(value, indent + 1) + ",\n"
            php_array += "    " * indent + "]"
            return php_array
        elif isinstance(obj, list):
            php_array = "[\n"
            for item in obj:
                php_array += "    " * (indent + 1) + to_php_array(item, indent + 1) + ",\n"
            php_array += "    " * indent + "]"
            return php_array
        elif isinstance(obj, bool):
            return "TRUE" if obj else "FALSE"
        elif obj is None:
            return "NULL"
        else:
            return f"'{obj.replace('\'', '\\\'')}'"
    
    php_content = "<?php\nreturn " + to_php_array(data) + ";\n"
    with open(filename, "w", encoding="utf-8") as f:
        f.write(php_content)
    print(f"PHP Array gespeichert: {filename}")

# PHP Array speichern
save_as_php_array(json_data, "php/kdsf-fkk.php")


