# API compatibility checker for swagger.json

A PHP implementation for finding breaking changes between two `swagger.json` documents.

[![Build Status](https://travis-ci.org/swaggest/api-compat.svg?branch=master)](https://travis-ci.org/swaggest/api-compat)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swaggest/api-compat/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swaggest/api-compat/?branch=master)
[![Code Climate](https://codeclimate.com/github/swaggest/api-compat/badges/gpa.svg)](https://codeclimate.com/github/swaggest/api-compat)
[![Test Coverage](https://codeclimate.com/github/swaggest/api-compat/badges/coverage.svg)](https://codeclimate.com/github/swaggest/api-compat/coverage)
[![Image Size](https://images.microbadger.com/badges/image/swaggest/api-compat.svg)](https://microbadger.com/images/swaggest/api-compat)

## CLI tool

### Usage

```
bin/api-compat --help
v1.0.0 api-compat
API compatibility checker for swagger.json, https://github.com/swaggest/api-compat
Usage: 
   api-compat <originalPath> <newPath>
   originalPath   Path to old (original) swagger.json file
   newPath        Path to new swagger.json file           
   
Options: 
   --verbose    Verbose output
   
Misc: 
   --help               Show usage information    
   --version            Show version              
   --bash-completion    Generate bash completion  
   --install            Install to /usr/local/bin/
```

### Example

After finding breaking changes, `api-compat` will exit with code `1`

```
bin/api-compat tests/resources/petstore1.json tests/resources/petstore2.json --verbose
Optional parameter became required at #/paths/'/pet/{petId}'/post/parameters/2/required
Parameter disposition has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/in
original: "formData"
new: "header"
Parameter type has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/type
original: "string"
new: "integer"
...
Breaking changes detected in new swagger schema
```


## Installation

### Docker

```
docker run -v $(pwd)/tests/resources:/code  swaggest/api-compat api-compat petstore1.json petstore2.json --verbose
Optional parameter became required at #/paths/'/pet/{petId}'/post/parameters/2/required
Parameter disposition has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/in
original: "formData"
new: "header"
Parameter type has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/type
original: "string"
new: "integer"
...
```

### Library

```bash
git clone https://github.com/swaggest/api-compat.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require swaggest/api-compat
```

## Library usage

Create `ApiCompat` object from two values (`original` and `new`).

```php
$ac = new ApiCompat($original, $new);
```

On construction `ApiCompat` will check what has changed in new schema and detect breaking changes.
After that you can access collected breaking changes with `$ac->getBreakingChanges()`.

## Example

```php
$ac = new ApiCompat(
    json_decode(file_get_contents(__DIR__ . '/../resources/petstore1.json')),
    json_decode(file_get_contents(__DIR__ . '/../resources/petstore2.json'))
);

$breakingChanges = $ac->getBreakingChanges();
$log = '';
foreach ($breakingChanges as $breakingChange) {
    $log .= $breakingChange->message . ' at ' . Path::quoteUrldecode($breakingChange->path) . "\n";
    if ($breakingChange->originalValue) {
        $log .= 'original: ' . json_encode($breakingChange->originalValue, JSON_UNESCAPED_SLASHES) . "\n";
    }
    if ($breakingChange->newValue) {
        $log .= 'new: ' . json_encode($breakingChange->newValue, JSON_UNESCAPED_SLASHES) . "\n";
    }
}
$this->assertNotEmpty($breakingChanges);
$expectedLog = <<<'LOG'
Optional parameter became required at #/paths/'/pet/{petId}'/post/parameters/2/required
Parameter disposition has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/in
original: "formData"
new: "header"
Parameter type has changed at #/paths/'/pet/{petId}/uploadImage'/post/parameters/1/type
original: "string"
new: "integer"
Parameter schema has changed at #/paths/'/store/order'/post/parameters/0/schema/'$ref'
original: "#/definitions/Order"
new: "#/definitions/OrderDifferent"
Response schema has changed at #/paths/'/store/order'/post/responses/200/schema/'$ref'
original: "#/definitions/Order"
new: "#/definitions/OrderDifferent"
Parameter schema has changed at #/paths/'/user/createWithList'/post/parameters/0/schema/items/'$ref'
original: "#/definitions/User"
new: "#/definitions/UserBla"
Missing parameter named password at #/paths/'/user/login'/get/parameters/1/name
Body parameter added at #/paths/'/pet/findByStatus'/get/parameters/1
new: {"in":"body","name":"body","description":"Updated user object","schema":{"$ref":"#/definitions/User"}}
Required parameter added at #/paths/'/user/login'/get/parameters/2
new: {"name":"extra","in":"query","description":"The extra required parameter","required":true,"type":"string"}
Body parameter added at #/paths/'/user/{username}'/delete/parameters/1
new: {"in":"body","name":"body","description":"List of user object","schema":{"type":"array","items":{"$ref":"#/definitions/UserBla"}}}
Required constraint added to structure at #/definitions/Category/required
new: ["id"]
Method removed at #/paths/'/pet/{petId}'/delete
original: {"tags":["pet"],"summary":"Deletes a pet","description":"","operationId":"deletePet","produces":["application/xml","application/json"],"parameters":[{"name":"api_key","in":"header","required":false,"type":"string"},{"name":"petId","in":"path","description":"Pet id to delete","required":true,"type":"integer","format":"int64"}],"responses":{"400":{"description":"Invalid ID supplied"},"404":{"description":"Pet not found"}},"security":[{"petstore_auth":["write:pets","read:pets"]}]}
Response for http code removed at #/paths/'/pet/{petId}/uploadImage'/post/responses/200
original: {"description":"successful operation","schema":{"$ref":"#/definitions/ApiResponse"}}
Path removed at #/paths/'/user/logout'
original: {"get":{"tags":["user"],"summary":"Logs out current logged in user session","description":"","operationId":"logoutUser","produces":["application/xml","application/json"],"parameters":[],"responses":{"default":{"description":"successful operation"}}}}
Structure property removed at #/definitions/Order/properties/petId
original: {"type":"integer","format":"int64"}

LOG;

$this->assertSame($expectedLog, $log);
```
