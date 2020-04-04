<?php declare(strict_types=1);

namespace Momocode\Shopware6Base\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @author Moritz Müller <moritz@momocode.de>
 */
abstract class MailTypeMigration extends AbstractMigration
{
    public function update(Connection $connection): void
    {
        $this->createMailTemplateTypes($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    protected function createMailTemplateTypes(Connection $connection): void
    {
        $definitionMailTypes = $this->getMailTypeMapping();

        $languageEn = $this->getLanguageIdByLocale($connection, 'en-GB');
        $languageDe = $this->getLanguageIdByLocale($connection, 'de-DE');

        foreach ($definitionMailTypes as $typeName => $mailType) {
            $availableEntities = null;
            if (array_key_exists('availableEntities', $mailType)) {
                $availableEntities = json_encode($mailType['availableEntities']);
            }

            $connection->insert(
                'mail_template_type',
                [
                    'id' => Uuid::fromHexToBytes($mailType['id']),
                    'technical_name' => $typeName,
                    'available_entities' => $availableEntities,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => Uuid::fromHexToBytes($mailType['id']),
                    'name' => $mailType['name'],
                    'language_id' => $languageEn,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => Uuid::fromHexToBytes($mailType['id']),
                    'name' => $mailType['nameDe'],
                    'language_id' => $languageDe,
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }
    }

    abstract protected function getMailTypeMapping(): array;
}
