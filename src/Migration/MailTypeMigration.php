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
        // Nothing
    }

    public function reverse(Connection $connection): void
    {
        $this->removeMailTemplateTypes($connection);
    }

    protected function createMailTemplateTypes(Connection $connection): void
    {
        $definitionMailTypes = $this->getMailTypeMapping();

        foreach ($definitionMailTypes as $typeName => $mailType) {
            // Continue if technical name already exists
            if ($this->getMailTemplateTypeId($connection, $typeName)) {
                continue;
            }

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
            $this->insertTranslations(
                $connection,
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => Uuid::fromHexToBytes($mailType['id']),
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ],
                [
                    'default' => [
                        'name' => $mailType['name'],
                    ],
                    'en-GB' => [
                        'name' => $mailType['name'],
                    ],
                    'de-DE' => [
                        'name' => $mailType['nameDe'],
                    ],
                ]
            );
        }
    }

    protected function removeMailTemplateTypes(Connection $connection): void
    {
        $definitionMailTypes = $this->getMailTypeMapping();

        foreach ($definitionMailTypes as $typeName => $mailType) {
            $typeId = $this->getMailTemplateTypeId($connection, $typeName);
            if ($typeId) {
                $connection->delete('mail_template_type', ['id' => $typeId]);
                $connection->delete('mail_template_type_translation', ['mail_template_type_id' => $typeId]);
            }
        }
    }

    protected function getMailTemplateTypeId(Connection $connection, string $technicalName)
    {
        $sql = <<<SQL
SELECT `mail_template_type`.`id` 
FROM `mail_template_type` 
WHERE `mail_template_type`.`technical_name` = :technicalName
SQL;

        /** @var string|false $typeId */
        $typeId = $connection->executeQuery($sql, ['technicalName' => $technicalName])->fetchColumn();

        return $typeId;
    }

    abstract protected function getMailTypeMapping(): array;
}
