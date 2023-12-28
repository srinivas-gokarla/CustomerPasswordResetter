<?php

namespace Srinivas\PasswordResetter\Model\Config;
use Magento\Framework\Option\ArrayInterface;


class Dates implements ArrayInterface
{
    public function toOptionArray()
    {

        return [
            ['value' => 30, 'label' => __('Monthly')],
            ['value' => 90, 'label' => __('Every 3 Months')],
            ['value' => 182, 'label' => __('Half Yearly')],
            ['value' => 365, 'label' => __('Yearly')],
            ['value' => 730, 'label' => __('Every 2 Years')],
            ['value' => 1826, 'label' => __('Every 5 Years')]
        ];
    }
}
