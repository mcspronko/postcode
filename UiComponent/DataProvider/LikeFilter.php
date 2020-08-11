<?php

declare(strict_types=1);

namespace Pronko\Postcode\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

class LikeFilter implements FilterApplierInterface
{
    /**
     * @param Collection $collection
     * @param Filter $filter
     * @throws LocalizedException
     */
    public function apply(Collection $collection, Filter $filter)
    {
        if ($this->isShippingAddressField($filter)) {
            $postCodeCondition = $this->getPostCodeCondition($filter);
            $collection->addFieldToFilter($this->getPostCodeField($filter, $postCodeCondition), $postCodeCondition);
        } else {
            $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
        }
    }

    /**
     * @param Filter $filter
     * @return bool
     */
    private function isShippingAddressField(Filter $filter): bool
    {
        return 'shipping_address' === $filter->getField();
    }

    /**
     * @param Filter $filter
     * @return array
     */
    private function getPostCodeCondition(Filter $filter): array
    {
        $postCodes = $this->getPostCodeValues($filter->getValue());

        $result = [];
        foreach ($postCodes as $key => $value) {
            $result['key_' . $key] = [$filter->getConditionType() => sprintf('%%%s%%', $value)];
        }
        return $result;
    }

    /**
     * @param string $postCode
     * @return array
     */
    private function getPostCodeValues(string $postCode): array
    {
        $cleanPostCode = preg_replace("/[^A-Za-z0-9]/", '', $postCode);

        $result[] = $cleanPostCode;

        if (strlen($cleanPostCode) === 5) {
            $postCode = substr($cleanPostCode, 0, 2) . ' ' . substr($cleanPostCode, 2, 3);
        } elseif (strlen($cleanPostCode) === 6) {
            $postCode = substr($cleanPostCode, 0, 3) . ' ' . substr($cleanPostCode, 3, 3);
        } elseif (strlen($cleanPostCode) === 7) {
            $postCode = substr($cleanPostCode, 0, 4) . ' ' . substr($cleanPostCode, 4, 3);
        }
        $result[] = $postCode;

        return $result;
    }

    /**
     * @param Filter $filter
     * @param $condition
     * @return array
     */
    private function getPostCodeField(Filter $filter, $condition): array
    {
        $result = [];
        foreach (array_keys($condition) as $key) {
            $result[$key] = $filter->getField();
        }

        return $result;
    }
}
