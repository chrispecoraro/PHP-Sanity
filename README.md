# PHP-Sanity

PHP Sanity is a PHP class providing convience utility functions for performing mutations to https://Sanity.io schemas.
This requires the sanity-php client. https://github.com/sanity-io/sanity-php

### Usage:


// instantiation:

```php
$sanity = new Sanity('1a2b3c4d', 'production',
'skY70CML0Ovm3GfPqQKeLtBNnS....',
'2019-01-29');`
```
-----

`create()` is useful for creating a single record from an array or object:

```php
$arr = ['Tim', 'Jones'];
$sanity->create("employee", $arr, ["firstName","lastName"]);
```

-----

`create()` also works with an object:

```php
$sanity->create("employee", (object)$arr,["firstName","lastName"]);
```

-----

`createFromString()` creates a single document given a delimited string.

```php
$sanity->createFromString("employee",'Ralph|Peters',["firstName","lastName"],'|');
```
-----

` batchCreate()` is useful for importing from an array of arrays or objects

```php
$docs = [
    ['Bob', 'Jones'],
    ['Ron', 'Philips']
];
$sanity->batchCreate("employee", $docs, ["firstName","lastName"]);
$sanity->batchCreate("employee", (object)$docs, ["firstName","lastName"]);
```
-----

`batchCreateFromFile()` is useful for importing from a delimited file

```php
// example file:
// records.csv:
//    Bob|Jones
//    Ron|Philips

$sanity->batchCreateFromFile("employee", "./records.csv", ["firstName","lastName"], "|");
```

-----

`copy()` copies the document source field to the target field's value.
// This is useful for back filling fields

```php
$sanity->copy('employee','telephone','cellular');
```
-----

`attachImage()` works with the Document ID as a parameter

```php
$sanity->attachImage('/home/bob/Documents/Bob-Photo.jpeg',
    documentId: 'fb5b618b-47e4-40c7-964b-2e479cb33');
```
-----

`attachImage()` also works with a documentId set as a class property

```php
$sanity->setDocumentId("fb5b618b-47e4-40c7-964b-29cb33c4");
$sanity->attachImage("/home/bob/Documents/avatar.jpg");
```
-----

`attachImage()` also works with an image URL

```php
$sanity->setDocumentId("fb5b618b-47e4-40c7-964b-279cb33c4");
$sanity->attachImage("https://picsum.photos/400/300");
```
-----

`all()` is a shortcut equivalent a fetch using GROQ
//$results = $sanity->fetch('*[_type=="menuItem"]');

```php
$results = $sanity->all("employee");
```
-----

`set()` sets a single field in a document to a given value.

```php
$sanity->set("lastName","Jones","fb5b618b-47e4-40c7-964b-2e479cb3c4");
```
-----

`deleteAll()` deletes ALL documents of a given schema type.

IMPORTANT: be careful and make a back up (export the dataset first).

```php
$sanity->deleteAll('employee');
```
-----

`deleteById()` deletes a single documents given its id.

IMPORTANT: be careful and make a back up (export the dataset first).

```php
$sanity->deleteById('fb5b618b-47e4-40c7-964b-2e479cb33c');
```
