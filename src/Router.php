<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            $pattern = $this->convertToPattern($route['path']);

            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request = $request->withAttribute('params', $params);

                return call_user_func($route['handler'], $request);
            }
        }

        // Если маршрут не найден
        $response = new \Laminas\Diactoros\Response();
        return $response->withStatus(404)->withBody(new \Laminas\Diactoros\Stream('php://temp', 'w'));
    }

    private function convertToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#i';
    }
}