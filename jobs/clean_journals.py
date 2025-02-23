
from pymongo import MongoClient

# Connect to the MongoDB, change the connection string per your MongoDB environment
client = MongoClient('mongodb://localhost:27017/')
db = client['osiris_dsmz']

# find all journals not connected to any activity
# Aggregation-Pipeline zum Finden von Journals ohne Aktivitäten
pipeline = [
    {
        "$project": {
            "id": {"$toString": "$_id"},
            "journal": 1
        }
    },
    {
        "$lookup": {
            "from": "activities",
            "localField": "id",
            "foreignField": "journal_id",
            "as": "related_activities"
        }
    },
    {
        "$match": {
            "related_activities": { "$size": 0 }  # Journals ohne Aktivitäten
        }
    }
]

# Journals abrufen, die nicht mit Aktivitäten verknüpft sind
unlinked_journals = list(db.journals.aggregate(pipeline))

# IDs der nicht verknüpften Journale ausgeben und löschen
if unlinked_journals:
    journal_ids_to_delete = [journal['_id'] for journal in unlinked_journals]
    print(f"Deleting {len(journal_ids_to_delete)} journals...")
    print(journal_ids_to_delete)

    # Journale löschen
    delete_result = db.journals.delete_many({"_id": {"$in": journal_ids_to_delete}})
    print(f"Deleted {delete_result.deleted_count} journals.")
else:
    print("No unlinked journals found.")