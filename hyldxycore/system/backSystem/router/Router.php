<?php
namespace hyldxycore\system\backSystem\router;
use Exception;

/**
 * Router System
 * @Description That system store the routes and call the right route
 *
 * @Thanks @Grafikart | https://github.com/Grafikart
 * @Comments Adapted for my needs, + PHP types support
 */
class Router {
    private string $url;
    private array  $routes      = [];
    private array  $errorRoutes = [];
    private array  $namedRoutes = [];

    public function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * @param string $path
     * @param $callable
     * @param string|null $name
     * @return Route
     */
    public function addRoute(string $path, $callable, ?string $name = null): Route {
        $route          = new Route($path, $callable);
        $this->routes[] = $route;

        if (is_string($callable) && $name === null) $name                     = $callable;
        if ($name)                                  $this->namedRoutes[$name] = $route;

        return $route;
    }

    /**
     * @param string $path
     * @param $callable
     * @param string|null $name
     * @return Route
     */
    public function addErrorRoute(string $path, $callable, ?string $name = null): Route {
        $route               = new Route($path, $callable);
        $this->errorRoutes[] = $route;

        if (is_string($callable) && $name === null) $name                     = $callable;
        if ($name)                                  $this->namedRoutes[$name] = $route;

        return $route;
    }

    /**
     * @param string $name
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function url(string $name, array $params = []): string {
        if (!isset($this->namedRoutes[$name])) throw new Exception("No route matches this name");

        return $this->namedRoutes[$name]->getUrl($params);
    }

    /**
     * @return mixed|null
     */
    public function dispatch(): mixed {
        foreach ($this->routes as $route) {
            if ($route->match($this->url)) return $route->callMethod();
        }

        $uriParts = explode("/", $this->url);
        if ($uriParts[0] === "api") $this->url = "/" . $uriParts[0];
        else                        $this->url = "/";

        foreach ($this->errorRoutes as $errorRoute) {
            if ($errorRoute->match($this->url)) return $errorRoute->callMethod();
        }

        return null;
    }
}