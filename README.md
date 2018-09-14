# ENCYCLOPEDIA OF LIFE 


Creates the list of all provider-ids
'''
php crawl-eol-providers.php > eol-providers.txt
'''


Crawls all page-ids using provider-id and save to db
'''
php crawl-pageids.php <providerID>
'''


Crawls all pages using provider-id and save to file in /data/pages/..
'''
php crawl-pages.php <providerID>
'''




## Call 1st:

### Provider Hierarchies
http://eol.org/api/provider_hierarchies/1.0.json

JSON Result: 
id = Provider ID


------------
## Call 2nd:

### Hierarchies (Provider ID = 860)
http://eol.org/api/hierarchies/1.0/860.json?cache_ttl=&language=en

JSON Result: 
taxonConceptID = each PageID
taxonID = each taxon id


------------
## Call 3rd:

### Hierarchy Entries (TaxonID = 24919630)
http://eol.org/api/hierarchy_entries/1.0/24919630.json

JSON Result:
ancestor = An array. Ancestors of that particular nodes
childred = An array.  Descendents of that particular nodes



