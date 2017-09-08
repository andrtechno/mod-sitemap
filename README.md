mod-sitemap
===========
Module for CORNER CMS

[![Latest Stable Version](https://poser.pugx.org/panix/mod-sitemap/v/stable)](https://packagist.org/packages/panix/mod-sitemap) [![Total Downloads](https://poser.pugx.org/panix/mod-sitemap/downloads)](https://packagist.org/packages/panix/mod-sitemap) [![Monthly Downloads](https://poser.pugx.org/panix/mod-sitemap/d/monthly)](https://packagist.org/packages/panix/mod-sitemap) [![Daily Downloads](https://poser.pugx.org/panix/mod-sitemap/d/daily)](https://packagist.org/packages/panix/mod-sitemap) [![Latest Unstable Version](https://poser.pugx.org/panix/mod-sitemap/v/unstable)](https://packagist.org/packages/panix/mod-sitemap) [![License](https://poser.pugx.org/panix/mod-sitemap/license)](https://packagist.org/packages/panix/mod-sitemap)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist panix/mod-sitemap "*"
```

or add

```
"panix/mod-sitemap": "*"
```

to the require section of your `composer.json` file.

Add to web config.
```
'modules' => [
    'sitemap' => ['class' => 'panix\mod\sitemap\Module'],
],
```