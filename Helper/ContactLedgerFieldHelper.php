<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/21/18
 * Time: 5:08 PM
 */

namespace MauticPlugin\MauticContactLedgerBundle\Helper;


class ContactLedgerFieldHelper
{
    public function getDefaultDateFormat()
    {
        return '%b %e, %y';
    }

    public function getAliasedField($field, $alias)
    {
        return "$field AS `$alias`";
    }

    /**
     * @param string $format
     * @param string|bool $alias
     * @param string $dateField
     *
     * @return string
     */
    public function getFormatDateField($dateField = 'date_added', $format = '%b %e, %y', $alias = true)
    {
        $formattedField = sprintf('DATE_FORMAT(%s, "%s")', $dateField, $format);

        if ($alias) {
            if (!is_string($alias)) {
                return $this->getAliasedField($formattedField, $dateField);
            }
            return $this->getAliasedField($formattedField, $alias);
        }
        return $formattedField;
    }

    public function getSumNumericField($numericField, $alias = true)
    {
        $summedField = "SUM($numericField)";
        if ($alias) {
            if (!is_string($alias)) {
                return $this->getAliasedField($summedField, $numericField);
            }
            return $tshis->getAliasedField($summedField, $alias);
        }
        return $summedField;
    }
}