# PHP-Sanity

PHP Sanity is a PHP class providing utility functions for performing mutations to https://Sanity.io schemas.

 Basic functions:
 
`attachImage()` 
Given an image URL, uploads image and attaches it to a specified documentId.

`batchCreate()`
Given a list of fields (either an array of arrays or array of object), imports these records in a single batch operation.

`batchCreateFromFile()`
Given a file, reads this file into an array, separates the columns bases on the field separator, and then creates a document of given type.

`create()`
Given a schemaType, create a new record from an object or array with optional field names.

`createFromString()`
Given a schemaType, create a new record from a string with specified field separator and specified field names.


`copy()`
Given a schemaType, copies all of the values for one field type to another.

