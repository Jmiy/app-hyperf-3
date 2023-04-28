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
namespace Hyperf\ConfigNacos;

use Hyperf\Collection\Arr;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\ConfigCenter\Contract\ClientInterface as ConfigClientInterface;
use Hyperf\Nacos\Protobuf\ListenHandler\ConfigChangeNotifyRequestHandler;
use Hyperf\Nacos\Protobuf\Response\ConfigQueryResponse;
use Psr\Container\ContainerInterface;

use Hyperf\ConfigCenter\Mode;

class NacosDriver extends AbstractDriver
{
    protected string $driverName = 'nacos';

    /**
     * @var Client
     */
    protected ConfigClientInterface $client;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    public function createMessageFetcherLoop(): void
    {
        if (! $this->config->get('config_center.drivers.nacos.client.grpc.enable', false)) {
            parent::createMessageFetcherLoop();
            return;
        }

        $application = $this->client->getClient();
        $listeners = $this->config->get('config_center.drivers.nacos.listener_config', []);
        foreach ($listeners as $key => $item) {
            $dataId = $item['data_id'];
            $group = $item['group'];
            $tenant = $item['tenant'] ?? '';
            $type = $item['type'] ?? null;

            $client = $application->grpc->get($tenant);
            $client->listenConfig($group, $dataId, new ConfigChangeNotifyRequestHandler(function (ConfigQueryResponse $response) use ($key, $type) {
//                $this->updateConfig([
//                    $key => $this->client->decode($response->getContent(), $type),
//                ]);

                $config = [
                    $key => $this->client->decode($response->getContent(), $type),
                ];
                $prevConfig = [$key => $this->config->get($key, [])];

                $this->updateConfig($config);

                $mode = strtolower($this->config->get('config_center.mode', Mode::PROCESS));
                if ($mode === Mode::PROCESS && $config !== $prevConfig) {
                    $this->syncConfig($config, $prevConfig);
                }

            }));
        }

        foreach ($application->grpc->getClients() as $client) {
            $client->listen();
        }
    }

    protected function updateConfig(array $config): void
    {
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config as $key => $conf) {
            if (is_int($key)) {
                $key = $root;
            }
            if (is_array($conf) && $this->config->get('config_center.drivers.nacos.merge_mode') === Constants::CONFIG_MERGE_APPEND) {
                $conf = Arr::merge($this->config->get($key, []), $conf);
            }

            $this->config->set($key, $conf);
        }
    }
}
