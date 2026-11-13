<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Command]
class DumpConfigCommand extends HyperfCommand
{
    #[Inject]
    protected LoggerInterface $logger;
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        parent::__construct('dump:config');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Dump Config Command');
    }

    public function handle()
    {
        $this->logger->info("===== 【合并后最终 databases 配置】 =====");
        var_dump($this->config->get('databases'));

        $this->logger->info("\n===== 【config_center 本身配置（nacos监听配置）】 =====");
        var_dump($this->config->get('config_center'));

        $this->logger->info("\n===== 【原始nacos_config节点（你这里没有挂载到它，大概率null）】 =====");
        var_dump($this->config->get('nacos'));

        return 0;
    }
}
