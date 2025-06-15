'''
Script to undo the import of OpenAlex data into the database.
'''

# get the date from the command line or use the current date
import sys
from datetime import datetime
from pymongo import MongoClient
import configparser
import os

date = datetime.now().strftime("%Y-%m-%d")
if len(sys.argv) > 1:
    date = sys.argv[1]

# read the configuration
config = configparser.ConfigParser()
path = os.path.dirname(__file__)
config.read(os.path.join(path, 'config.ini'))

# connect to the database
client = MongoClient(config['Database']['Connection'])
osiris = client[config['Database']['Database']]

# remove the imported data
n = osiris['activities'].delete_many({'imported': date})

print(f"Deleted {n.deleted_count} activities imported on {date}")
