<?php

namespace Momocode\Shopware6Base;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Momocode\Shopware6Base\Migration\AbstractMigration;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin as ShopwarePlugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Kernel;

/**
 * @author Moritz Müller <moritz@momocode.de>
 */
class Plugin extends ShopwarePlugin
{
    /**
     * @var Connection
     */
    protected $connection;

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            $migrations = $this->getPluginMigrations();

            foreach ($migrations as $migration) {
                if (!class_exists($migration)) {
                    continue;
                }

                /** @var MigrationStep $migration */
                $migration = new $migration();
                if (!$migration instanceof AbstractMigration) {
                    continue;
                }

                $migration->reverse($this->getConnection());
            }
        }

        parent::uninstall($uninstallContext);
    }

    protected function getPluginMigrations(): array
    {
        $class = addcslashes($this->getMigrationNamespace(), '\\_%') . '%';
        return $this->getConnection()->executeQuery(
            'SELECT class FROM migration WHERE class LIKE :class',
            ['class' => $class]
        )->fetchAll(FetchMode::COLUMN);
    }

    protected function getConnection(): Connection
    {
        return $this->connection ?? $this->connection = Kernel::getConnection();
    }
}
