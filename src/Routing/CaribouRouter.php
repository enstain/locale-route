<?php

namespace CaribouFute\LocaleRoute\Routing;

use CaribouFute\LocaleRoute\Locale\Route as LocaleRoute;
use CaribouFute\LocaleRoute\Locale\Url as LocaleUrl;
use Illuminate\Contracts\Routing\Registrar as IlluminateRouter;
use Illuminate\Routing\Route;

class CaribouRouter
{
    protected $router;
    protected $url;
    protected $route;

    public function __construct(IlluminateRouter $router, LocaleUrl $url, LocaleRoute $route)
    {
        $this->router = $router;
        $this->url = $url;
        $this->route = $route;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function addLocale(Route $route)
    {
        $locale = $this->getActionLocale($route);

        if (!$locale) {
            return $route;
        }

        $route = $this->switchRouteLocale($locale, $route);
        $route = $this->switchUrlLocale($locale, $route);

        return $route;
    }

    public function getActionLocale(Route $route)
    {
        $locales = $this->getActionLocales($route);

        if (!$locales) {
            return null;
        }

        return is_array($locales) ? collect($locales)->last() : $locales;
    }

    public function getActionLocales(Route $route)
    {
        $action = $route->getAction();

        return is_array($action) && isset($action['locale']) ? $action['locale'] : null;
    }

    public function switchRouteLocale($locale, Route $route)
    {
        $name = $route->getName();
        if (!$name) {
            return $route;
        }

        $name = $this->route->switchLocale($locale, $name);

        $action = $route->getAction();
        $action['as'] = $name;
        $route->setAction($action);

        return $route;
    }

    public function switchUrlLocale($locale, Route $route)
    {
        $uri = $this->url->switchLocale($locale, $route->uri());
        $route->setUri($uri);

        return $route;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->router, $method], $arguments);
    }
}
