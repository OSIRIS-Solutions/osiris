'''
This script harvests OAI records from an repository and stores them in a MongoDB database.
It uses the Sickle library to interact with the OAI-PMH interface and the pymongo library to interact with MongoDB.

To install the required libraries, run:
pip install pymongo sickle
'''
# Import necessary libraries
from pymongo import MongoClient
import configparser
import os
from sickle import Sickle

# read the config file
config = configparser.ConfigParser()
path = os.path.dirname(__file__)
config.read(os.path.join(path, 'config.ini'))

# set up database connection
client = MongoClient(config['Database']['Connection'])
osiris = client[config['Database']['Database']]

# check if an endpoint is set
settings = osiris['adminGeneral'].find_one({'key': 'oai'})
if settings is None or 'value' not in settings:
    print('No OAI settings found in the database. Please set it up first via Admin interface.')
    exit()
    
settings = settings['value']

endpoint = settings['endpoint']
if endpoint is None:
    print('No OAI endpoint found in the database. Please set it up first via Admin interface.')
    exit()

# set up the OAI-PMH connection
sickle = Sickle(endpoint)
records = sickle.ListRecords(metadataPrefix='oai_dc')
records.next()