<?php

declare(strict_types=1);

//todo refactor! split responsibility on router & dispatcher
use ApiProblem\ApiProblem;
use Controller\Base\BaseController;

final class Router
{
    private array $routes;

    private array $param;

    private string $controller, $action, $method;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function match(string $url): bool
    {
        foreach ($this->routes as $route) {
            if (preg_match($route[0], $url, $matches) &&
                ($route[3] === $_SERVER['REQUEST_METHOD'] || $_SERVER['REQUEST_METHOD'] == 'OPTIONS')) {

                $this->controller = $route[1];
                $this->action = $route[2];
                $this->method = $route[3];
                $this->param = $matches;

                return true;
            }
        }
        return false;
    }

    public function dispatch(string $url)
    {
        if ($this->match($url)) {
            /**
             * @var $controller BaseController
             */
            $controller = new $this->controller();

            $authorization_header = find_authorization_header();
            $token = $authorization_header ? find_token($authorization_header) : null;

            if ($token) $controller->authentication($token);

            $actionName = $this->action;

            try {
                if ($this->method == 'POST') {
                    $data = $this->get_body();
                    //todo shitfix
                    $param1 = $this->param[1] ?? null;
                    $param2 = $this->param[2] ?? null;
                    $result = $controller->$actionName($data, $param1, $param2);
                } else {
                    //todo shitfix
                    $param1 = $this->param[1] ?? null;
                    $param2 = $this->param[2] ?? null;
                    $param3 = $this->param[3] ?? null;
                    $result = $controller->$actionName($param1, $param2, (int)$param3);
                }

                $this->set_status_code($this->method);
                echo json_encode(['data' => $result]);

            } catch (ApiProblem $apiProblem) {
                http_response_code($apiProblem->getCode());
                echo json_encode($apiProblem->getMessage());
            } catch (\Throwable $throwable) {
                http_response_code(500);
                echo json_encode($throwable->getMessage());
            }
        }
    }

    public function set_status_code(string $method): void
    {
        switch ($method) {
            case 'GET':
                http_response_code(200);
                break;
            case 'POST':
                http_response_code(201);
                break;
        }
    }

    public function get_body(): ?\stdClass
    {
        return json_decode(file_get_contents('php://input'));
    }
}
