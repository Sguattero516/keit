<?php
namespace Filters;

use Core\Filter\AbstractFilter;
use Core\Locale\LocaleService;
use Traffic\Model\StreamFilter;
use Traffic\RawClick;

/**
 * Filter Example
 */
class example extends AbstractFilter
{
    public function getModes()
    {
        return [
            StreamFilter::ACCEPT => LocaleService::t('filters.binary_options.' . StreamFilter::ACCEPT),
            StreamFilter::REJECT => LocaleService::t('filters.binary_options.' . StreamFilter::REJECT),
        ];
    }
    /**
     * Filter settings template
     */
    public function getTemplate()
    {
        return '<input class="form-control" ng-model="filter.payload" />';
    }

    /**
     * Check if $rawClick passes the filter (true - passed, false - failed)
     */
    public function isPass(StreamFilter $filter, RawClick $rawClick)
    {
        $value = $filter->getPayload();
        return ($filter->getMode() == StreamFilter::ACCEPT && $rawClick->getSubIdN(1) == $value)
            || ($filter->getMode() == StreamFilter::REJECT && $rawClick->getSubId(1) == $value);
    }
}