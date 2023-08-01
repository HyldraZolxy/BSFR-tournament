<?php
namespace hyldxycore\system\backSystem\router;

/**
 * Route System
 * @Description That system make the route with params
 *
 * @Thanks @Grafikart | https://github.com/Grafikart
 * @Comments Adapted for my needs, + PHP types support
 */
class Route {
    private string $path;
    private        $callable;
    private array  $matches  = [];
    private array  $params   = [];

    public function __construct(string $path, $callable) {
        $this->path     = trim($path, "/");
        $this->callable = $callable;
    }

    /**
     * @param string $param
     * @return string
     */
    private function paramMatch(string $param): string {
        return "(" . ($this->params[$param] ?? "[^/]+") . ")";
    }

    /**
     * @param string $param
     * @param string $regex
     * @return $this
     */
    public function with(string $param, string $regex): self {
        $this->params[$param] = str_replace("(", "(?:", $regex);
        return $this;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function match(string $url): bool {
        $url   = trim($url, "/");
        $regex = "#^" . preg_replace_callback("#:(\w+)#", fn($match) => $this->paramMatch($match[1]), $this->path) . "$#i";

        if (!preg_match($regex, $url, $matches)) return false;

        $this->matches = array_slice($matches, 1);
        return true;
    }

    /**
     * @return mixed
     */
    public function callMethod(): mixed {
        if (is_string($this->callable)) {
            list($controller, $method) = explode("@", $this->callable);
            $controller                = str_replace("/", "\\", $controller);

            if (class_exists($controller)) {
                $object = new $controller();
                if (method_exists($object, $method)) return $object->{$method}(...$this->matches);
            }
        }

        return call_user_func_array($this->callable, $this->matches);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params): string {
        return str_replace(array_map(fn($key) => ":$key", array_keys($params)), array_values($params), $this->path);
    }
}