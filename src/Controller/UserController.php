<?php

declare(strict_types=1);

namespace Controller;


use Controller\Base\BaseController;
use Model\User;
use Repository\UserRepository;
use Service\TokenService;

class UserController extends BaseController
{
    private $user_repository;

    /**
     * @var TokenService
     */
    private $token_service;

    public function __construct()
    {
        $this->user_repository = new UserRepository();
        $this->token_service = new TokenService();
    }

    public function create(object $data)
    {
        $password_encrypted = password_hash($data->password, PASSWORD_BCRYPT);

        $user = new User(
            $data->email,
            $password_encrypted,
            $data->username
        );

        $user_id = $this->user_repository->create($user);

        $token = $this->token_service->create_token($user_id);

        $this->response_created(['token' => $token]);
    }

    public function login(object $data)
    {
        if(!property_exists($data, 'email') ||
            !property_exists($data, 'password')){
            $this->response_unauthorized('Wrong credentials');die;
        }

        $user = $this->user_repository->find($data->email);

        if(!$user) {
            $this->response_unauthorized('Wrong credentials');die;
        }

        if (!password_verify($data->password, $user->password)) {
            $this->response_unauthorized('Password Mismatch');die;
        }

        $this->response();
    }
}