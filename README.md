Feedseek
======
A simple feed link Auto-discovering tool for PHP

[![Build Status](https://travis-ci.org/norikt/feedseek.svg?branch=master)](https://travis-ci.org/norikt/feedseek)


Requirements
-------

* php>=5.4


Installation
-------
via composer 
```
$ composer require norikt/feedseek dev-master
```

Usage
-------
example:

```php
<?php

Feedseek::find('http://www.example.com/');
// ['http://www.example.com/rss', 'http://www.example.com/atom']

Feedseek::find(['http://www.example.com/','http://www.example2.com/']);
// ['http://www.example.com/' => [...],	'http://www.example2.com/' => [...],]

```
If you set to true second argument and returns the only link that has passed through the Feedvalidation of W3C.

```php
<?php

Feedseek::find('http://www.example.com/', true)

```

License
-------
MIT
