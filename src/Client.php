<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\RpcMultiplex;

use Hyperf\LoadBalancer\LoadBalancerInterface;
use Multiplex\Constract\IdGeneratorInterface;
use Multiplex\Constract\PackerInterface;
use Multiplex\Constract\SerializerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Client as SwooleClient;

class Client extends \Multiplex\Socket\Client
{
    /**
     * @var callable
     */
    protected $nodeSelector;

    /**
     * @var null|LoadBalancerInterface
     */
    protected $loadBalancer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            '',
            0,
            $container->get(IdGeneratorInterface::class),
            $container->get(SerializerInterface::class),
            $container->get(PackerInterface::class)
        );
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer)
    {
        $this->loadBalancer = $loadBalancer;
    }

    protected function makeClient(): SwooleClient
    {
        $node = $this->getLoadBalancer()->select();

        $this->name = $node->host;
        $this->port = $node->port;

        return parent::makeClient();
    }
}
