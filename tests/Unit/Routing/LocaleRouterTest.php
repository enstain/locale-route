<?php

namespace Tests\Unit\Router;

use CaribouFute\LocaleRoute\Prefix\Route as PrefixRoute;
use CaribouFute\LocaleRoute\Prefix\Url as PrefixUrl;
use CaribouFute\LocaleRoute\Routing\LocaleRouter;
use CaribouFute\LocaleRoute\Routing\Router;
use CaribouFute\LocaleRoute\TestHelpers\EnvironmentSetUp;
use Illuminate\Routing\Route;
use Mockery;
use Orchestra\Testbench\TestCase;

class LocaleRouterTest extends TestCase
{
    use EnvironmentSetUp;

    public function setUp()
    {
        parent::setUp();

        $this->router = Mockery::mock(Router::class);
        $this->routeLocalizer = Mockery::mock(PrefixRoute::class);
        $this->url = Mockery::mock(PrefixUrl::class)->makePartial();

        $this->localeRouter = Mockery::mock(LocaleRouter::class, [$this->router, $this->routeLocalizer, $this->url])->makePartial();
    }

    public function testAddMiddlewareWithoutLocaleRoutesInArray()
    {
        $route = 'route';
        $action = 'ActionController@action';
        $middleware = ['guest', 'auth'];
        $options = ['middleware' => $middleware];

        foreach ($this->locales as $locale) {
            $localeRoute = $locale . '.' . $route;
            $url = $locale . '/url' . $locale;
            $routeObject = Mockery::mock(Route::class);
            $routeMiddleware = $middleware + [2 => 'locale.session:' . $locale];

            $this->routeLocalizer
                ->shouldReceive('addLocale')
                ->with($locale, $route)
                ->once()
                ->andReturn($localeRoute);

            $this->url
                ->shouldReceive('getRouteUrl')
                ->with($locale, $route, $options)
                ->once()
                ->andReturn($url);

            $this->router
                ->shouldReceive('get')
                ->with($url, ['as' => $localeRoute, 'uses' => $action])
                ->once()
                ->andReturn($routeObject);

            $routeObject
                ->shouldReceive('middleware')
                ->with($routeMiddleware)
                ->once();
        }

        $this->localeRouter->get($route, $action, $options);
    }

    public function testAddMiddleware()
    {
        $route = 'route';
        $action = 'ActionController@action';
        $middleware = 'guest';
        $urls = ['fr' => 'routefr', 'en' => 'routeen', 'es' => 'routees', 'middleware' => $middleware];

        foreach ($this->locales as $locale) {
            $localeRoute = $locale . '.' . $route;
            $routeObject = Mockery::mock(Route::class);
            $routeMiddleware = [$middleware, 'locale.session:' . $locale];

            $this->routeLocalizer
                ->shouldReceive('addLocale')
                ->with($locale, $route)
                ->once()
                ->andReturn($localeRoute);

            $this->url
                ->shouldReceive('getRouteUrl')
                ->with($locale, $route, $urls)
                ->once()
                ->andReturn($urls[$locale]);

            $this->router
                ->shouldReceive('get')
                ->with($urls[$locale], ['as' => $localeRoute, 'uses' => $action])
                ->once()
                ->andReturn($routeObject);

            $routeObject
                ->shouldReceive('middleware')
                ->with($routeMiddleware)
                ->once();
        }

        $this->localeRouter->get($route, $action, $urls);
    }

    public function testGet()
    {
        $this->makeRouteTest('get');
    }

    public function testPost()
    {
        $this->makeRouteTest('post');
    }

    public function testPut()
    {
        $this->makeRouteTest('put');
    }

    public function testPatch()
    {
        $this->makeRouteTest('patch');
    }

    public function testDelete()
    {
        $this->makeRouteTest('delete');
    }

    public function testOptions()
    {
        $this->makeRouteTest('options');
    }

    protected function makeRouteTest($method)
    {
        $route = 'route';
        $action = 'ActionController@action';
        $urls = [];
        $routeObjects = [];

        foreach ($this->locales as $locale) {
            $localeRoute = $locale . '.' . $route;
            $url = $locale . '/url' . $locale;
            $routeObject = Mockery::mock(Route::class);

            $this->routeLocalizer
                ->shouldReceive('addLocale')
                ->with($locale, $route)
                ->once()
                ->andReturn($localeRoute);

            $this->url
                ->shouldReceive('getRouteUrl')
                ->with($locale, $route, [])
                ->once()
                ->andReturn($url);

            $this->router
                ->shouldReceive($method)
                ->with($url, ['as' => $localeRoute, 'uses' => $action])
                ->once()
                ->andReturn($routeObject);
            $routeObject->shouldReceive('middleware')->with(['locale.session:' . $locale])->once();
        }

        $this->localeRouter->$method($route, $action);
    }

    public function testGroupWithNoHasAttribute()
    {
        $attributes = ['prefix' => 'url', 'middleware' => 'auth'];
        $callback = function () {
        };

        foreach ($this->locales as $locale) {
            $newAttributes = ['as' => $locale . '.', 'prefix' => $locale . '/url', 'middleware' => ['auth', 'locale.session:' . $locale]];
            $this->routeLocalizer->shouldReceive('addLocale')->with($locale, '')->once()->andReturn($locale . '.');
            $this->router->shouldReceive('group')->with($newAttributes, $callback)->once();
        }

        $this->localeRouter->group($attributes, $callback);
    }

    public function testGroupWithHasAttribute()
    {
        $route = 'route';
        $attributes = ['as' => $route, 'prefix' => 'url', 'middleware' => 'auth'];
        $callback = function () {
        };

        foreach ($this->locales as $locale) {
            $newAttributes = ['as' => $locale . '.' . $route, 'prefix' => $locale . '/url', 'middleware' => ['auth', 'locale.session:' . $locale]];
            $this->routeLocalizer->shouldReceive('addLocale')->with($locale, $route)->once()->andReturn($locale . '.' . $route);
            $this->router->shouldReceive('group')->with($newAttributes, $callback)->once();
        }

        $this->localeRouter->group($attributes, $callback);
    }

    public function testResource()
    {
        $name = 'article';
        $controller = 'Controller';
        $options = ['options' => 'yé'];

        foreach ($this->locales as $locale) {
            $localeName = $locale . '.' . $name;
            $this->routeLocalizer->shouldReceive('addLocale')->with($locale, $name)->andReturn($localeName);
            $this->router->shouldReceive('resource')->with($localeName, $controller, $options);
        }

        $this->localeRouter->resource($name, $controller, $options);
    }
}
