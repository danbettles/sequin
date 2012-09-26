#Sequin#

N.B. This GitHub-hosted version of Sequin supercedes the one still available on SourceForge (https://sourceforge.net/projects/sequin/), the original.

Sequin is a simple PHP library for building query-strings for Lucene-based search engines (e.g. Solr).

Query-strings are assembled using the fluent interface exposed by Sequin's `Query` class.  An instance of `Query` comprises one or more `Term` objects, each of which may be a subquery&mdash;and each of those may also be further subdivided.

##Requirements##

Sequin requires PHP version 5.3 or later; it uses no third-party libraries.

##Installation (via Composer)##

Include the following in your `composer.json` file:

    require: 
    {
        ...
        'danbettles/sequin': 'dev-master'
    }

Then, run `composer.phar update`

##Installation (manual)##

1. Download/clone the library.
2. Import the code into your application by `include`ing `src/boot.php`.

##Usage##

The following&mdash;only slightly contrived&mdash;example demonstrates a good selection of Sequin's features.  Here we build a query-string to search for soundtracks by Thomas Newman in the index of an imaginary online music retailer.

    require_once 'path/to/sequin/src/boot.php';
    
    $oQuery = Sequin\Query::newInstance('"Thomas Newman"')
        ->andTerm('soundtrack', null, 3)
        ->andQuery('music', 'dept')
            ->orTerm('film')
        ->endQuery()
        ->notTerm('"Erin Brockovich"');
    
    print $oQuery;  // => "Thomas Newman" AND soundtrack^3 AND dept:(music OR film) NOT "Erin Brockovich"

##Contribute##

Let me know if you find the library useful or you'd like to contribute&mdash;you're welcome.
