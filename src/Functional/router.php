<?php

declare(strict_types=1);

use ApiProblem\ApiProblem;
use DTO\RequestData;
use DTO\VideoCreate;
use Service\TokenService;

function match(array $routes, string $url, string $http_method): ?array
{
    foreach ($routes as $route) {
        if (preg_match($route[0], $url, $matches) && ($route[2] === $http_method)) {
            return [
                'function_name' => $route[1],
                'http_method' => $route[2],
                'named_arguments' => $matches,
                'body' => get_body()
            ];
        }
    }
    return null;
}

//todo refactor!
function dispatch(array $match): void
{
    $parameters = read_parameters_from_function($match['function_name']);

    try {
    $arguments = autowire_arguments($parameters, $match, new Container());

        $result = call_user_func_array($match['function_name'], $arguments);
        set_status_code($match['http_method']);
        echo json_encode(['data' => $result]);

    } catch (ApiProblem $apiProblem) {
        http_response_code($apiProblem->getCode());
        echo json_encode($apiProblem->getMessage());
    } catch (\Throwable $throwable) {
        http_response_code(500);
        echo json_encode($throwable->getMessage());
    }
}

function read_parameters_from_function(string $function_name): array
{
    return array_map(function (ReflectionParameter $reflection_parameter) {
        return [
            'name' => $reflection_parameter->getName(),
            'type' => ($reflection_parameter->getType())->getName(),
        ];
    }, (new ReflectionFunction($function_name))->getParameters());
}

function autowire_arguments(array $parameters, array $match, Container $container)
{
   return array_map(function ($parameter) use ($match, $container) {

       //scalar types
        if (array_key_exists($parameter['name'], $match['named_arguments'])) {
            return $match['named_arguments'][$parameter['name']];
        }

        //requestData
        if ($parameter['type'] === RequestData::class) {
            return autowire_request_data($match);
        }

        //service from container
        return $container->get($parameter['type']);
    }, $parameters);
}

function autowire_request_data(array $match)
{
    $reflect = new ReflectionClass(VideoCreate::class);
    $props = $reflect->getProperties();

    $properties = array_map(fn(ReflectionProperty $property) => $property->getName(), $props);

    foreach ($properties as $property) {
        if ($property === 'user_id') continue;
        if (!property_exists($match['body'], $property)) throw new ApiProblem( //throws invalid argument exception - todo fix
            ["There was a validation error. Missing field: $property", 422]
        );
    }

    $request_data = recast(VideoCreate::class, $match['body']);

    if (in_array('user_id', $properties)) {
        $request_data->user_id = auth();
    }
    return $request_data;
}


function auth(): ?int
{
    $authorization_header = find_authorization_header();
    $token = $authorization_header ? find_token($authorization_header) : null;

    if (!$token){
        return null;
    }

    return (new TokenService())->decode_user_id($token);
}

function set_status_code(string $http_method): void
{
    switch ($http_method) {
        case 'GET':
            http_response_code(200);
            break;
        case 'POST':
            http_response_code(201);
            break;
    }
}

function get_body(): ?\stdClass
{
    return json_decode(file_get_contents('php://input'));
}