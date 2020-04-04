<?php declare(strict_types=1);

namespace Momocode\Shopware6Base\Migration;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @author Moritz Müller <moritz@momocode.de>
 */
abstract class AbstractMigration extends MigrationStep
{
    protected function getLanguageIdByLocale(Connection $connection, string $locale): string
    {
        $sql = <<<SQL
SELECT `language`.`id` 
FROM `language` 
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;

        /** @var string|false $languageId */
        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchColumn();
        if (!$languageId) {
            throw new RuntimeException(sprintf('Language for locale "%s" not found.', $locale));
        }

        return $languageId;
    }
}
