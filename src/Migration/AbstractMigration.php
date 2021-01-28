<?php declare(strict_types=1);

namespace Momocode\Shopware6Base\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @author Moritz Müller <moritz@momocode.de>
 */
abstract class AbstractMigration extends MigrationStep
{
    abstract public function reverse(Connection $connection): void;

    protected function getLanguageIdByLocale(Connection $connection, string $locale): ?string
    {
        $sql = <<<SQL
SELECT `language`.`id` 
FROM `language` 
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;

        /** @var string|false $languageId */
        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchColumn();

        return is_string($languageId) ? $languageId : null;
    }
}
