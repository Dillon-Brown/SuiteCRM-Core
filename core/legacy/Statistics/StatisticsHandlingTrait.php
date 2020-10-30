<?php

namespace App\Legacy\Statistics;

use App\Entity\Statistic;

trait StatisticsHandlingTrait
{

    /**
     * Build empty response statistic
     * @param string $key
     * @return Statistic
     */
    protected function getEmptyResponse(string $key): Statistic
    {
        $statistic = new Statistic();
        $statistic->setId($key);
        $statistic->setData(['value' => '-']);
        $statistic->setMetadata([
            'type' => 'single-value-statistic',
            'dataType' => 'varchar',
        ]);

        return $statistic;
    }

    /**
     * @param string $key
     * @param string $dataType
     * @param array $result
     * @return Statistic
     */
    protected function buildResponse(string $key, string $dataType, array $result): Statistic
    {
        $statistic = new Statistic();
        $statistic->setId($key);
        $statistic->setData($result);
        $statistic->setMetadata([
            'type' => 'single-value-statistic',
            'dataType' => $dataType,
        ]);

        return $statistic;
    }

    /**
     * @param Statistic $statistic
     * @param array $newMeta
     */
    protected function addMetadata(Statistic $statistic, array $newMeta): void
    {
        $metadata = $statistic->getMetadata() ?? [];
        $metadata = array_merge($metadata, $newMeta);
        $statistic->setMetadata($metadata);
    }

    /**
     * Build currency statistic result
     * @param array $result
     * @return Statistic
     */
    protected function buildCurrencyResult(array $result): Statistic
    {
        $value = $result['value'] ?? 0;

        if (empty($value)) {
            $result = ['value' => 0];
        }

        $statistic = new Statistic();
        $statistic->setId(self::KEY);
        $statistic->setData($result);
        $statistic->setMetadata([
            'type' => 'single-value-statistic',
            'dataType' => 'currency',
        ]);

        return $statistic;
    }

    /**
     * @param array $parts
     * @return string
     */
    protected function joinQueryParts(array $parts): string
    {
        $queryParts = [];
        $queryParts[] = $parts['select'] ?? '';
        $queryParts[] = $parts['from'] ?? '';
        $queryParts[] = $parts['where'] ?? '';
        $queryParts[] = $parts['group_by'] ?? '';

        return implode(' ', $queryParts);
    }

    /**
     * @param array $query
     * @return array
     */
    protected function extractContext(array $query): array
    {
        $module = $query['context']['module'] ?? '';
        $id = $query['context']['id'] ?? '';

        return array($module, $id);
    }

}
