Php 7.2 fix count
================


When [migrating to PHP 7.2](http://php.net/manual/en/migration72.php) there is a big incompatible change with the `count()` function.

The new `count()` function works only on countable variables (`array` or object which implements `Countable`).

In the past, that was weird but you could count integer, booleans, etc ... 

The previous php 7.2 behaviour for non-countable argument was :
* `count(<string>) =  1`
* `count(<integer>) =  1`
* `count(<boolean>) =  1`
* `count(<float>) =  1`
* `count(<object>) =  1`
* `count(null) =  0`


### When to use it

* Legacy code you need to migrate *fast* to Php 7.2 (you don't have days or more to go fix all the code while risking bugs)
* Legacy dependencies unmaintened from years your project still need and you don't want/have the time to fork them to fix `count()`
* You admit the tool won't cover 100% use cases (see further) and you will probaby have a few fixes to do manually

### Realistically
 
 * I used this "fix" on a 1M lines legacy project with thousands of count calls without any problem. With vendors
   it grew to 2.5M of lines and more than 13k files. The analysis take on average 500+ files/sec and 100k+ lines/sec
 * It took me later more than one week of full time to migrate manually each "count" in the project, but I keep 
   the fixer in the project because of legacy dependencies unmaintened for several years.
 * In reality, nobody wanted to use `count` on integers, string or else, but arrays coming from various sources 
   (like dbs) produced sometime a `null` and it triggered `E_WARNING`. That was more frequent than what we wanted.


### The "trick" to "fix" count

* Declare a `count()` function which brings back the old behaviour for each namespace where the fixer detected a use


### Use cases unfixable 

* You called `count()` everywhere with the backslash: `\count()`
* You have calls to `count()` into evaluated strings (`eval()` evil)
* You already overrided `count()`  in namespaces
* Calls to `count()` in non namespaced classes
* Other things I cannot imagine ...


### How to use it

In your `composer.json` :

    "require": {
        "arnapou/php72fixcount": "^1.0"
    },
    "scripts": {
        "post-autoload-dump": [
            "@php vendor/bin/php72-fix-count.php --quiet generate src vendor"
        ]
    }

Usage of `php72-fix-count.php` :

    PHP 7.2 FIX COUNT
    
    DESCRIPTION
        This command generate php files which are loaded by composer
        in order to fix/hack the breaking change of the count/sizeof
        breaking change for php 7.2+
    
    SYSNOPSIS
        php php72-fix-count.php [OPTION] COMMAND DIRECTORY...
    
    OPTION
        --quiet    silent mode (usefull for composer post-autoload-dump)
    
    COMMAND
        generate   generate the fixes
        search     search the fixes (same as generate but no write)
        clean      remove the fixes
    
    EXAMPLES
        php php72-fix-count.php --quiet generate src vendor
        php php72-fix-count.php clean


You must add all directories in the same command line because each script execution will override the generated fix, **DON'T** do that :

    "scripts": {
        "post-autoload-dump": [
            "@php vendor/bin/php72-fix-count.php --quiet generate src ",             |  DOES NOT
            "@php vendor/bin/php72-fix-count.php --quiet generate vendor"            |  WORK !!!
        ]
    }



### Annexe from php.net

This is a copy-paste from [this link](http://php.net/manual/en/migration72.incompatible.php) from php.net

An **E_WARNING** will now be emitted when attempting to `count()` non-countable types (this includes the `sizeof()` alias function). 

```php
<?php

var_dump(
    count(null), // NULL is not countable
    count(1), // integers are not countable
    count('abc'), // strings are not countable
    count(new stdclass), // objects not implementing the Countable interface are not countable
    count([1,2]) // arrays are countable
);
```

The above example will output:
```php
Warning: count(): Parameter must be an array or an object that implements Countable in %s on line %d

Warning: count(): Parameter must be an array or an object that implements Countable in %s on line %d

Warning: count(): Parameter must be an array or an object that implements Countable in %s on line %d

Warning: count(): Parameter must be an array or an object that implements Countable in %s on line %d
int(0)
int(1)
int(1)
int(1)
int(2)
```

### PHP support

| Updated    | Tag, Branche | Php       | 
|------------|--------------|-----------|
| 30/01/2022 | 2.x, master  | 5.6 - 8.1 |
| 11/03/2021 | 1.x          | 5.6 - 7.4 |
