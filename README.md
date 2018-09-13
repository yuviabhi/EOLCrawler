#ENCYCLOPEDIA OF LIFE 



'''
php crawl-eol-providers.php > eol-providers.txt
'''


'''
php crawl-pageids.php <providerID>
'''



'''
php crawl-pages.php
'''



'''
php pages-jsonify.php
'''

##Call 1st:

###Provider Hierarchies
http://eol.org/api/provider_hierarchies/1.0.json

JSON Result: 
id = Provider ID


------------
##Call 2nd:

###Hierarchies (Provider ID = 860)
http://eol.org/api/hierarchies/1.0/860.json?cache_ttl=&language=en

JSON Result: 
taxonConceptID = each PageID
taxonID = each taxon id


------------
##Call 3rd:

###Hierarchy Entries (TaxonID = 24919630)
http://eol.org/api/hierarchy_entries/1.0/24919630.json

JSON Result:
ancestor = An array. Ancestors of that particular nodes
childred = An array.  Descendents of that particular nodes



