<?php declare(strict_types=1);

namespace Momocode\Shopware6Base\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

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

    protected function insertTranslations(
        Connection $connection,
        string $tableExpression,
        array $generalData,
        array $translations
    ): void {
        $prepared = [];
        $languageDefault = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        foreach ($translations as $locale => $data) {
            if ($locale === 'default') {
                continue;
            }

            $languageId = $this->getLanguageIdByLocale($connection, $locale);
            // Only create translation if a language exists for that locale
            if ($languageId) {
                $mergedData = array_merge($generalData, $data, ['language_id' => $languageId]);
                // If the current locale is assigned to the default language, handle that translation as default
                // translation. Else handle it as normal translation for that locale.
                if ($languageId === $languageDefault) {
                    $prepared['default'] = $mergedData;
                } else {
                    $prepared[$locale] = $mergedData;
                }
            }
        }

        // If none of the provided locales was assigned to the default language, create a translation for
        // the default language with the default translation.
        if (!isset($prepared['default']) && isset($translations['default'])) {
            $prepared['default'] = array_merge(
                $generalData,
                $translations['default'],
                ['language_id' => $languageDefault]
            );
        }

        // Insert all translations
        foreach ($prepared as $finalData) {
            $connection->insert($tableExpression, $finalData);
        }
    }
}
