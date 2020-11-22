<?php

namespace App;

use App\Exceptions\RouteNotFoundException;

class App
{
    protected $container;

    public function __construct()
    {
        $this->container = new Container([
            'router' => function () {
                return new Router;
            },
            'response' => function () {
                return new Response;
            },
            'request' => function () {
                return \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
            }
        ]);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function get($uri, $handler)
    {
        $this->container->router->addRoute($uri, $handler, ['GET']);
    }

    public function post($uri, $handler)
    {
        $this->container->router->addRoute($uri, $handler, ['POST']);
    }

    public function map($uri, $handler, array $methods = ['GET'])
    {
        $this->container->router->addRoute($uri, $handler, $methods);
    }

    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $router = $this->container->router;
        $router->setPath($uri ?? '/');

        try {
            $response = $router->getResponse();
        } catch (RouteNotFoundException $e) {
            if ($this->container->has('errorHandler')) {
                $response = $this->container->errorHandler;
            } else {
                return;
            }
        }

        return $this->respond($this->process($response));
    }

    protected function process($callable)
    {
        $request = $this->container->request;
        $response = $this->container->response;

        if (is_array($callable)) {
            if (!is_object($callable[0])) {
                $callable[0] = new $callable[0];
            }

            return call_user_func($callable, $request, $response);
        }

        return $callable($request, $response);
    }

    protected function respond($response)
    {
        if (!$response instanceof Response) {
            echo $response;
            return;
        }

        header(sprintf(
            'HTTP/%s %s %s',
            '1.1',
            $response->getStatusCode(),
            ''
        ));

        foreach ($response->getHeaders() as $header) {
            header($header[0] . ': ' . $header[1]);
        }

        echo $response->getBody();
    }
}
