# LocaleRoute, for localized testable routes in Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](license.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**LocaleRoute** is a package to make testable localized routes with Laravel 5. It comes from the need to have localized routes that are fully testable.

LocaleRoute has a syntax close to the original Laravel routing methods, so the installation and learning curve should be quite easy.

For an example of LocaleRoute implementation, please check my [locale-route-example repo](https://github.com/cariboufute/locale-route-example).

**This package is now on beta stage. It should be functional. It just needs other programmers' feedback before having an "official" release. Please check it and test it before using it in production.**

## Change log

Please see [changelog](changelog.md) for more information what has changed recently and what will be added soon.

## Requirements

- PHP 5.6 or later, fully compatible with PHP 7.0
- Laravel 5.1 or later

## Install

First install the package through Composer by typing this line in the terminal at the root of your Laravel application.

``` bash
composer require cariboufute/locale-route 1.0.0-beta4
```

Add the service provider and the ```LocaleRoute``` and ```SubRoute``` aliases in ```config/app.php```.

``` php
'providers' => [
    //...
    CaribouFute\LocaleRoute\LocaleRouteServiceProvider::class,
    //...
],

'aliases' => [
    //...
    'LocaleRoute' => CaribouFute\LocaleRoute\Facades\LocaleRoute::class,
    'SubRoute' => CaribouFute\LocaleRoute\Facades\SubRoute::class,
],
```

In your ```app/Http/Kernel.app``` file, add the ```SetLocale``` middleware in the web middleware group. This will read the locale from the ```locale``` session variable, saved by each localized route and will keep the locale for redirections, even after using unlocalized routes to access models CRUD routes, for instance.

``` php
// app/Http/Kernel.app

protected $middlewareGroups = [
    'web' => [
        //...
        \CaribouFute\LocaleRoute\Middleware\SetLocale::class,
    ],

    //...
];
```

Finally install the config file of the package by typing this line in the terminal at the root of your Laravel application.

``` bash
php artisan vendor:publish

#if for some reason, you only want to get locale-route config file, type this line instead
php artisan vendor:publish --provider "CaribouFute\LocaleRoute\LocaleRouteServiceProvider"
```

Then you should have a ```config/localeroute.php``` installed.

## Configuration


Check your ```config/localeroute.php``` file. Here is the default file.
``` php
<?php

return [

    /**
     * The locales used by routes. Add all
     * locales needed for your routes.
     */
    'locales' => ['fr', 'en'],

    /**
     * Option to add '{locale}/' before given URIs.
     * For LocaleRoute::get('route', ...):
     * true     => '/fr/route'
     * false    => '/route'
     * Default is true.
     */
    'add_locale_to_url' => true,
];

```

### Locales

Add all the locale codes needed for your website in ```locales```.

For instance, if you want English, French, Spanish and German in your site...
``` php
    'locales' => ['en', 'fr', 'es', 'de'],
```

### Add locale automatically to URLs

This option is by default set to true. It prepends all URLs build by locale-route with a ```{locale}/```, according to [Google Multi-regional and multilingual sites guidelines](https://support.google.com/webmasters/answer/182192?hl=en).

If for any reason, you don't want this prefix to be added automatically, just put this option to false, like this.
``` php
    'add_locale_to_url' => false,
```

## Usage

### Adding routes

Adding localized routes is now really easy. Just go to your ```routes/web.php``` file (or ```app/Http/routes.php``` in older versions of Laravel) and add ```LocaleRoute``` declarations almost like you would declare Laravel ```Route``` methods.

``` php
LocaleRoute::get('route', 'Controller@getAction', ['fr' => 'url_fr', 'en' => 'url_en']);
LocaleRoute::post('route', 'Controller@postAction', ['fr' => 'url_fr', 'en' => 'url_en']);
LocaleRoute::put('route', 'Controller@putAction', ['fr' => 'url_fr', 'en' => 'url_en']);
LocaleRoute::patch('route', 'Controller@patchAction', ['fr' => 'url_fr', 'en' => 'url_en']);
LocaleRoute::delete('route', 'Controller@deleteAction', ['fr' => 'url_fr', 'en' => 'url_en']);
LocaleRoute::options('route', 'Controller@optionsAction', ['fr' => 'url_fr', 'en' => 'url_en']);
```

For the first line, it is the equivalent of declaring this in pure Laravel, while having the app locale set to the right locale.
``` php
Route::get('fr/url_fr', ['as' => 'fr.route', 'uses' => 'Controller@getAction']);
Route::get('en/url_en', ['as' => 'en.route', 'uses' => 'Controller@getAction']);
```

So the syntax can be resumed to this.

```
LocaleRoute::{method}({routeName}, {Closure or controller action}, {locale URL array with 'locale' => 'url'});
```

#### Using translator files for URLs

You can also use the Laravel translator to put all your locale URLs in ```resources/lang/{locale}/routes.php``` files. If there is no locale URL array, ```LocaleRoute``` will automatically check for the translated ```routes.php``` files to find URLs. All you need to do is to remove the locale URL array in ```LocaleRoute``` and declare them as ```'route' => 'url'``` in your translated route files, like this. 

``` php
//routes/web.php

LocaleRoute::get('route', 'Controller@routeAction');
```

``` php
//resources/lang/en/routes.php

return [
    'route' => 'url_en',
]
```

``` php
//resources/lang/fr/routes.php

return [
    'route' => 'url_fr',
]
```

### Middleware

If you want to use middleware for your LocaleRoute, add them in the url array (3rd parameter) in the ```'middleware'``` key.

``` php
LocaleRoute::get('route', 'Controller@getAction', ['fr' => 'url_fr', 'en' => 'url_en', 'middleware' => 'guest']);

//To use trans files URL, just add 'middleware'
LocaleRoute::get('route', 'Controller@getAction', ['middleware' => 'guest']);

```

### Grouping

You can use ```LocaleRoute::group``` the same way as you use ```Route::group``` in Laravel. This will add the locale prefixes to the given routes (```'as'```) and URL (```'prefix'```) prefixes defined in the group attributes.

To continue using different URLs according to locale, use the façade ```SubRoute```.

``` php
//web.php or routes.php

LocaleRoute::group(['as' => 'article.', 'prefix' => 'article'], function () {
    Route::get('/', ['as' => 'index', 'ArticleController@index']);
    SubRoute::get('create', 'ArticleController@create', ['fr' => 'creer', 'en' => 'create']]);
});

/*
Will give these routes :

[fr.article.index]  => GET "/fr/article"        => ArticleController::index()
[en.article.index]  => GET "/en/article"        => ArticleController::index()
[fr.article.create] => GET "/fr/article/creer"  => ArticleController::create()
[en.article.create] => GET "/en/article/create" => ArticleController::create()
*/
```

**Important:** Please note that you *must* use normal ```Route``` or ```SubRoute``` methods instead of their ```LocaleRoute``` counterparts (like ```SubRoute::get``` instead of ```LocaleRoute::get```) inside a ```LocaleRoute::group```, to avoid duplications of locale adding to routes and URLs.

Also, the ```LocaleRoute::group``` *must* be at the top of your grouping of routes to keep the locale prefix in URLs and route names at the beginning, so the helper functions work. If you want to have localized and unlocalized routes in the same group, you will need to duplicate the grouping for both situations.

``` php
// Will not work...

Route::group(['as' => 'foo', prefix => 'foo'], function () {
    Route::group(['as' => 'bar', prefix => 'bar'], function () {
        LocaleRoute::get('baz', function () {}, ['en' => 'baz', 'fr' => 'baz']);
        Route::post('post', function () {}, ['en' => 'post', 'fr' => 'post']);
    });
});

/* Will give:
    'foo.bar.en.baz'    => GET '/foo/bar/en/baz'
    'foo.bar.fr.baz'    => GET '/foo/bar/fr/baz'
    'foo.bar.post'      => POST '/foo/bar/post'
*/

// This will...

LocaleRoute::group([], function () {
    Route::group(['as' => 'foo', prefix => 'foo'], function () {
        Route::group(['as' => 'bar', prefix => 'bar'], function () {
            Route::get('baz', ['as' => 'baz']);
        });
    }); 
});

Route::group(['as' => 'foo', prefix => 'foo'], function () {
    Route::group(['as' => 'bar', prefix => 'bar'], function () {
        Route::post('post', ['as' => 'post']);
    });
});

/* Will give:
    'en.foo.bar.baz'    => GET '/en/foo/bar/baz'
    'fr.foo.bar.baz'    => GET '/fr/foo/bar/baz'
    'foo.bar.post'      => POST '/foo/bar/post'
*/

// For DRYer route coding, just keep all routes localized.
// It won't alter any code if no localization is used,
// although the routes will be duplicated.

LocaleRoute::group([], function () {
    Route::group(['as' => 'foo', prefix => 'foo'], function () {
        Route::group(['as' => 'bar', prefix => 'bar'], function () {
            Route::get('baz', ['as' => 'baz']);
            Route::post('post', ['as' => 'post']);
        });
    }); 
});

/* Will give:
    'en.foo.bar.baz'    => GET '/en/foo/bar/baz'
    'fr.foo.bar.baz'    => GET '/fr/foo/bar/baz'
    'en.foo.bar.post'   => POST '/en/foo/bar/post'
    'fr.foo.bar.post'   => POST '/fr/foo/bar/post'
*/
```

### Resource

*Under reconstruction, coming soon...*

### Fetching URLs

```LocaleRoute``` gives three helper functions to help you get your URLs quickly. They are close to the Laravel ```route``` helper function.

#### locale_route

This is the basic helper function. It calls the URL according to the locale, route name and parameters. When put to null, locale and route are set to the current values.
``` php
//locale_route($locale, $route, $parameters)

locale_route('fr', 'route');                //gets the French route URL.
locale_route('es', 'article', ['id' => 1]); //gets the Spanish article route URL with parameter 'id' set to 1
locale_route(null, 'index');                //gets the index route URL in the current locale
locale_route('en');                         //gets the current URL in English
locale_route('en', null, ['id' => 1]);      //gets the current URL in English, with parameter 'id' set to 1
```

For the last three situations, there are clearer helper functions.

#### other_route

Calls another route URL in the same locale. The syntax is the same as Laravel ```route```.
``` php
//other_route($route, $parameters)

other_route('route');                   //gets the route URL in the current locale.
other_route('article', ['id' => 1]);    //gets the article route URL in the current locale with parameter 'id' to 1 in the current locale
other_route('article')                  //gets the article route URL in the current locale with no parameters.
```

#### other_locale

Calls the same route URL in another locale. For the syntax, we just replace the route name by the locale. Perfect for language selectors.
``` php
//other_locale($locale, $parameters)

other_locale('es');                     //gets the same URL in Spanish.
other_locale('en', ['id' => 1]);        //gets the same URL in English with parameter 'id' to 1.
other_locale('fr')                      //gets the same URL in French with current parameters.
other_locale('de', [])                  //gets the same URL in German with no parameters, when there are parameters in the current route.
```

## Contributing

Please see [contributing](contributing.md) and [conduct](conduct.md) for details.

## Credits

- [Frédéric Chiasson][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/cariboufute/locale-route.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/cariboufute/locale-route/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cariboufute/locale-route.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cariboufute/locale-route.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/cariboufute/locale-route.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/cariboufute/locale-route
[link-travis]: https://travis-ci.org/cariboufute/locale-route
[link-scrutinizer]: https://scrutinizer-ci.com/g/cariboufute/locale-route/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cariboufute/locale-route
[link-downloads]: https://packagist.org/packages/cariboufute/locale-route
[link-author]: https://github.com/cariboufute
[link-contributors]: ../../contributors
