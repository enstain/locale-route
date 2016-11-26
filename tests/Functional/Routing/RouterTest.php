<?php

namespace Tests\Functional\Routing;

use CaribouFute\LocaleRoute\Routing\Router;
use CaribouFute\LocaleRoute\TestHelpers\EnvironmentSetUp;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class RouterTest extends TestCase
{
    use EnvironmentSetUp;

    public function setUp()
    {
        parent::setUp();
        $this->router = app()->make(Router::class);
    }

    public function testGet()
    {
        $this->makeMethodTest('get');
    }

    public function testPost()
    {
        $this->makeMethodTest('post');
    }

    public function testPut()
    {
        $this->makeMethodTest('put');
    }

    public function testPatch()
    {
        $this->makeMethodTest('patch');
    }

    public function testDelete()
    {
        $this->makeMethodTest('delete');
    }

    public function testOptions()
    {
        $this->makeMethodTest('options');
    }

    public function makeMethodTest($method)
    {
        $this->router->$method('test', ['locale' => 'fr', 'as' => 'route', 'uses' => function () {return 'yes!';}]);

        $this->call($method, 'fr/test');
        $this->assertResponseOk();

        $this->call($method, route('fr.route'));
        $this->assertResponseOk();
    }

    public function testGetWithSameUriAndDifferentLocale()
    {
        $this->router->get('test', ['locale' => 'fr', 'as' => 'route', 'uses' => function () {return 'yé!';}]);
        $this->router->get('test', ['locale' => 'en', 'as' => 'route', 'uses' => function () {return 'yé!';}]);

        //To test that route collection is well refreshed and
        //that there is no duplication of routes
        //after adding changed route.
        $this->assertSame(2, $this->router->getRoutes()->count());

        $this->call('GET', 'fr/test');
        $this->assertResponseOk();

        $this->call('GET', route('fr.route'));
        $this->assertResponseOk();

        $this->call('GET', 'en/test');
        $this->assertResponseOk();

        $this->call('GET', route('en.route'));
        $this->assertResponseOk();

    }

    public function testGetInGroup()
    {
        $router = $this->router;

        $router->group(['as' => 'group.', 'prefix' => 'group'], function () use ($router) {
            $router->get('test', ['locale' => 'fr', 'as' => 'route', 'uses' => function () {return 'yé!';}]);
        });

        $this->call('GET', 'fr/group/test');
        $this->assertResponseOk();

        $this->call('GET', route('fr.group.route'));
        $this->assertResponseOk();
    }

    public function testGetInLocaleGroup()
    {
        $router = $this->router;

        $router->group(['locale' => 'fr', 'as' => 'group.', 'prefix' => 'group'], function () use ($router) {
            $router->get('test', ['as' => 'route', 'uses' => function () {return 'yé!';}]);
        });

        $this->call('GET', 'fr/group/test');
        $this->assertResponseOk();

        $this->call('GET', route('fr.group.route'));
        $this->assertResponseOk();
    }

    public function testLocaleGetInLocaleGroupTakesGetLocale()
    {
        $router = $this->router;

        $router->group(['locale' => 'fr', 'as' => 'group.', 'prefix' => 'group'], function () use ($router) {
            $router->get('test', ['locale' => 'en', 'as' => 'route', 'uses' => function () {return 'yé!';}]);
        });

        $this->call('GET', 'en/group/test');
        $this->assertResponseOk();

        $this->call('GET', route('en.group.route'));
        $this->assertResponseOk();
    }
}