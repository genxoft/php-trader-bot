<?php

namespace App\Factory;

use Predis\Client;
use Psr\Container\ContainerInterface;

class PredisFactory
{
    public function __invoke(ContainerInterface $ci): ?Client
    {
        $params = [
            'scheme'   => 'tcp',
            'host'     => $ci->get('redis.host'),
            'port'     => $ci->get('redis.port'),
        ];
        if ($ci->has('redis.password')) {
            $params['password'] = $ci->get('redis.password');
        }

        return new Client($params);
    }

}
