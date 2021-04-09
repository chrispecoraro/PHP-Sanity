# PHP-Sanity

PHP Sanity is a PHP class providing convience utility functions for performing mutations to https://Sanity.io schemas.
This requires the sanity-php client. https://github.com/sanity-io/sanity-php

### Usage:


    /**
     * Sanity constructor.
     * @param string $projectId
     * @param string $dataset
     * @param string $token
     * @param string $apiVersion
     */
    
`$sanity = new Sanity('a1b2c3d4',
    'staging',
    'skHDqRYxjC4357Zh5NaVZ9qgXVF4JF0RPPEnzy50RwtubW7fzoWpl9t9JDZ7rNFEIO4Hy2D3423M....',
    '2019-01-29');
    `



 Basic functions:
 
`attachImage()` 
Given an image URL, uploads image and attaches it to a specified documentId.

----
`batchCreate()`
Given a list of fields (either an array of arrays or array of object), imports these records in a single batch operation.

_Example_:
```
$countries = [
    "Afghanistan",
    "Albania",
    "Algeria",
    "Andorra",
    "Angola",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
     ...
     ];
     
 $sanity->batchCreate('country', $countries, ['name']);
```


----
`batchCreateFromFile()`
Given a file, reads this file into an array, separates the columns based on the field separator, and then creates a document of given type.

----
`create()`
Given a schemaType, create a new record from an object or array with optional field names.

_Example_:
`$id = $sanity->create('employee', ['Fred', 'Jones', '1234'], ['firstName', 'lastName', 'ID']);`

----

`createFromString()`
Given a schemaType, create a new record from a string with specified field separator and specified field names.

----

`copy()`
Given a schemaType, copies all of the values for one field type to another.

