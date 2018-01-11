<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\SupplierSuccess\Ui\DataProvider\Supplier\DataForm\Modifier;

use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Field;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleRestock extends AbstractModifier
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $dayOfWeek;

    /**
     * @var array
     */
    protected $dayOfMonth;

    /**
     * @var array
     */
    protected $monthOfQuarter;

    /**
     * @var array
     */
    protected $monthOfYear;

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->getSupplierInformation($meta)
        );
        return $meta;
    }

    /**
     * @param $meta
     * @return mixed
     */
    public function getSupplierInformation($meta)
    {
        $meta['schedule_restock']['arguments']['data']['config'] = [
            'label' => __('Restock Schedule'),
            'collapsible' => true,
            'visible' => true,
            'opened' => false,
            'dataScope' => 'data',
            'componentType' => Form\Fieldset::NAME
        ];
        $meta['schedule_restock']['children'] = $this->getSupplierAddressChildren();
        return $meta;
    }

    /**
     * @return array
     */
    public function getSupplierAddressChildren()
    {
        $children = [
            'gaptime' => $this->getField(__('Gap time'), Field::NAME, true, 'text', 'input', ['required-entry' => true, 'validate-number' => true, 'validate-zero-or-greater' => true]),
            'schedule' => $this->getField(__('Schedule'), Field::NAME, true, 'text', 'select', ['required-entry' => true], null, $this->options()),
            'monthOfYear' => $this->monthOfYearField(),
            'monthOfQuarter' => $this->monthOfQuarterField(),
            'dayOfMonth' => $this->dayOfMonthField(),
            'dayOfWeek' => $this->dayOfWeekField(),
            'numberDayPerTime' => $this->numberDayPerTimeField(),
            'startDate' => $this->startDateField()
        ];
        return $children;
    }

    /**
     * Retrieve countries
     *
     * @return array|null
     */
    protected function options()
    {
        if (null === $this->options) {
            $this->options = [
                [
                    'label' => 'Dayly',
                    'value' => 0,
                ],
                [
                    'label' => 'Weekly',
                    'value' => 1,
                ],
                [
                    'label' => 'Monthly',
                    'value' => 2,
                ],
                [
                    'label' => 'Quarterly',
                    'value' => 3,
                ],
                [
                    'label' => 'Yearly',
                    'value' => 4,
                ],
                [
                    'label' => 'Other',
                    'value' => 5,
                ]
            ];
        }

        return $this->options;
    }

    protected function dayOfWeek() {
        if (null === $this->dayOfWeek) {
            $this->dayOfWeek = [
                [
                    'label' => 'Monday',
                    'value' => 0,
                ],
                [
                    'label' => 'Tuesday',
                    'value' => 1,
                ],
                [
                    'label' => 'Wednesday',
                    'value' => 2,
                ],
                [
                    'label' => 'Thursday',
                    'value' => 3,
                ],
                [
                    'label' => 'Friday',
                    'value' => 4,
                ],
                [
                    'label' => 'Saturday',
                    'value' => 5,
                ],
                [
                    'label' => 'Sunday',
                    'value' => 6,
                ]
            ];
        }

        return $this->dayOfWeek;
    }

    protected function dayOfMonth() {
        if (null === $this->dayOfMonth) {
            for($i=0; $i<=27; $i++) {
                $this->dayOfMonth[] =
                    [
                        'label' => (string)($i+1),
                        'value' => $i,
                    ];
            }
            $this->dayOfMonth[] = [
                'label' => 'Last month',
                'value' => 28,
            ];
        }

        return $this->dayOfMonth;
    }

    protected function monthOfQuarter() {
        if (null === $this->monthOfQuarter) {
            $this->monthOfQuarter = [
                [
                    'label' => 'First month',
                    'value' => 0,
                ],
                [
                    'label' => 'Second month',
                    'value' => 1,
                ],
                [
                    'label' => 'Third month',
                    'value' => 2,
                ]
            ];
        }

        return $this->monthOfQuarter;
    }

    protected function monthOfYear() {
        if (null === $this->monthOfYear) {
            $this->monthOfYear = [
                [
                    'label' => 'January',
                    'value' => 0,
                ],
                [
                    'label' => 'February',
                    'value' => 1,
                ],
                [
                    'label' => 'March',
                    'value' => 2,
                ],
                [
                    'label' => 'April',
                    'value' => 3,
                ],
                [
                    'label' => 'May',
                    'value' => 4,
                ],
                [
                    'label' => 'June',
                    'value' => 5,
                ],
                [
                    'label' => 'July',
                    'value' => 6,
                ],
                [
                    'label' => 'August',
                    'value' => 7,
                ],
                [
                    'label' => 'September',
                    'value' => 8,
                ],
                [
                    'label' => 'October',
                    'value' => 9,
                ],
                [
                    'label' => 'November',
                    'value' => 10,
                ],
                [
                    'label' => 'December',
                    'value' => 11,
                ]
            ];
        }

        return $this->monthOfYear;
    }

    protected function monthOfYearField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/month-of-year',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'monthOfYear',
                        'componentType' => Field::NAME,
                        'label' => __('Month Of Year'),
                        'dataType' => 'text',
                        'validation' => ['required-entry' => true],
                        'options' => $this->monthOfYear(),
                        'dataScope' => 'monthOfYear',
                        'sortOrder' => 60,
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }

    protected function monthOfQuarterField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/month-of-quarter',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'monthOfQuarter',
                        'componentType' => Field::NAME,
                        'label' => __('Month Of Quarter'),
                        'dataType' => 'text',
                        'options' => $this->monthOfQuarter(),
                        'dataScope' => 'monthOfQuarter',
                        'sortOrder' => 70,
                        'validation' => ['required-entry' => true],
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }

    protected function dayOfMonthField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/day-of-month',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'dayOfMonth',
                        'componentType' => Field::NAME,
                        'label' => __('Day Of Month'),
                        'dataType' => 'text',
                        'options' => $this->dayOfMonth(),
                        'dataScope' => 'dayOfMonth',
                        'sortOrder' => 80,
                        'validation' => ['required-entry' => true],
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }

    protected function dayOfWeekField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/day-of-week',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'dayOfWeek',
                        'componentType' => Field::NAME,
                        'label' => __('Day Of Week'),
                        'dataType' => 'text',
                        'options' => $this->dayOfWeek(),
                        'dataScope' => 'dayOfWeek',
                        'sortOrder' => 90,
                        'validation' => ['required-entry' => true],
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }

    protected function numberDayPerTimeField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'input',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/number-day-per-time',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'numberDayPerTime',
                        'componentType' => Field::NAME,
                        'label' => __('Number Day Per Time'),
                        'dataType' => 'text',
                        'dataScope' => 'numberDayPerTime',
                        'sortOrder' => 100,
                        'validation' => ['required-entry' => true, 'validate-number' => true, 'validate-greater-than-zero' => true],
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }

    protected function startDateField() {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'input',
                        'component' => 'Magestore_SupplierSuccess/js/form/schedule/element/start-date',
//                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'startDate',
                        'componentType' => Field::NAME,
                        'label' => __('Start Date'),
                        'dataType' => 'date',
                        'dataScope' => 'startDate',
                        'sortOrder' => 100,
                        'tooltip' => [
                            'description' => __('Select date less or equal than current date!'),
                        ],
                        'validation' => ['required-entry' => true, 'validate-date' => true],
                        'visible' => false
                    ],
                ],
            ],
        ];
        return $field;
    }
}
