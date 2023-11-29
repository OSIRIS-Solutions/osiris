'''
Script to update publications with open access status and concepts
'''
from pymongo import MongoClient
import configparser
import os

from diophila import OpenAlex

REDO = False

# read the config file
config = configparser.ConfigParser()
path = os.path.dirname(__file__)
config.read(os.path.join(path, 'config.ini'))

# set up openalex configuration
openalex = OpenAlex(config['DEFAULT'].get('AdminMail'))

# set up database connection
client = MongoClient(config['Database']['Connection'])
osiris = client[config['Database']['Database']]

# get all activities that have a doi
activities = osiris['activities'].find({
    'doi': {'$exists': True, '$nin': [None, '']}, 
    '$or': [
        {'oa_status': {'$exists': False}},
        {'concepts': {'$exists': False}}
    ]
    })

# go through all activities and check if data is complete
i = 0
for doc in activities:
    # print(doc['_id'])
    if doc.get('oa_status') and doc.get('concepts'):
        # data is complete so nothing to do
        print(doc['title'])
        continue
    i+=1
    print(i)

    doi = doc['doi']
    print(doi)
    try:
        work = openalex.get_single_work(doi, 'doi')
    except:
        continue
    if not work:
        # not found in open alex
        continue
    
    if not doc.get('oa_status') and work.get('open_access'):
        status = work['open_access'].get('oa_status')
        oa = work['open_access'].get('is_oa')
        osiris['activities'].update_one(
            {'_id': doc['_id']},
            {'$set': {'open_access': oa, 'oa_status': status}}
        )

    if not doc.get('concepts') and work.get('concepts'):
        osiris['activities'].update_one(
            {'_id': doc['_id']},
            {'$set': {'concepts': work.get('concepts')}}
        )
        
    # print(work.get('concepts'))
    # exit()