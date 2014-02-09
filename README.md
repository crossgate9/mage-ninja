mage-ninja
==========

Useful Magento Snippets Library

Installation
-
<pre>
cd MAGEROOT;
git clone https://github.com/crossgate9/mage-ninja;
cd mage-ninja;
</pre>


Tools list:

- Database Export Shell Script:

	Path: ./backup/export_database.sh
	
	Usage: 
	<pre>
		./export_database.sh
	</pre>
	
	xmllint is preferrable to be installed. 
	<pre>
		// Debian or Ubuntu
		sudo apt-get install libxml2-utils
	</pre>

- CMS Content Diff/Import/Export Script:
	
	Path: ./tools/cms
	
	Usage: The "export" script will output the *CMS Page* and *Static Block* content as Pretty JSON format; The "import" script will import the JSON data into the database; The "diff" script is to diff two JSON files, which make it possible to version control the CMS content. 
