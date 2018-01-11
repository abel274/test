<?php

namespace Magestore\SupplierSuccess\Helper;

/**
 * Helper Data.
 * @category Magestore
 * @package  Magestore_SupplierSuccess
 * @module   SupplierSuccess
 * @author   Magestore Developer
 */
    
/**
 * Class Data
 * @package Magestore\SupplierSuccess\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getRestockDate($product_id) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magestore\SupplierSuccess\Model\Supplier\Product $supplierProduct */
        $supplierProduct = $om->get('Magestore\SupplierSuccess\Model\Supplier\Product')->getCollection()
            ->addFieldToFilter('product_id', $product_id)
            ->setPageSize(1)->setCurPage(1)->getFirstItem();

        if($supplierProduct->getId()) {
            $supplierId = $supplierProduct->getSupplierId();
            /** @var \Magestore\SupplierSuccess\Model\Supplier $supplier */
            $supplier = $om->get('Magestore\SupplierSuccess\Model\Supplier')->load($supplierId);
            if($supplier->getId()) {
                $gapTime = (int)$supplier->getData('gaptime');
                // calculate restock date
                switch ($supplier->getData('schedule')){
                    case 0:
                        return $this->getNextDayly($gapTime);
                        break;
                    case 1:
                        return $this->getNextWeekly($gapTime, $supplier);
                        break;
                    case 2:
                        return $this->getNextMonthly($gapTime, $supplier);
                        break;
                    case 3:
                        return $this->getNextQuarterly($gapTime, $supplier);
                        break;
                    case 4:
                        return $this->getNextYearly($gapTime, $supplier);
                        break;
                    case 5:
                        return $this->getNextOther($gapTime, $supplier);
                        break;
                }
                return false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $gapTime
     * @return string
     */
    protected function getNextDayly($gapTime) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $nextDay = $curDate->add(new \DateInterval('P1D'));
        $restockDate = $nextDay->add(new \DateInterval('P'.$gapTime.'D'));
        return $restockDate->format('Y-m-d');
    }

    /**
     * @param $gapTime
     * @param \Magestore\SupplierSuccess\Model\Supplier $supplier
     * @return string
     */
    protected function getNextWeekly($gapTime, $supplier) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $curDateOnWeek = $curDate->format('l');
        $day = 0;
        switch ($curDateOnWeek) {
            case 'Monday':
                $day = 0;
                break;
            case 'Tuesday':
                $day = 1;
                break;
            case 'Wednesday':
                $day = 2;
                break;
            case 'Thursday':
                $day = 3;
                break;
            case 'Friday':
                $day = 4;
                break;
            case 'Saturday':
                $day = 5;
                break;
            case 'Sunday':
                $day = 6;
                break;
        }

        $dateOfWeek = (int)$supplier->getData('dayOfWeek');

        if($dateOfWeek > $day) {
            $restockDate = $curDate->add(new \DateInterval('P'.($dateOfWeek-$day).'D'));
            $restockDate = $restockDate->add(new \DateInterval('P'.$gapTime.'D'));
        } elseif($dateOfWeek == $day) {
            $restockDate = $curDate->add(new \DateInterval('P7D'));
            $restockDate = $restockDate->add(new \DateInterval('P'.$gapTime.'D'));
        } else {
            $restockDate = $curDate->add(new \DateInterval('P'.(7-($day-$dateOfWeek)).'D'));
            $restockDate = $restockDate->add(new \DateInterval('P'.$gapTime.'D'));
        }

        return $restockDate->format('Y-m-d');
    }

    /**
     * @param $gapTime
     * @param \Magestore\SupplierSuccess\Model\Supplier $supplier
     * @return string
     */
    protected function getNextMonthly($gapTime, $supplier) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $curDay = (int)$curDate->format('d');

        $rsDay = (int)$supplier->getData('dayOfMonth');
        if($rsDay != 28) {
            $rsDay += 1;
        } else {
            $rsDay = 29;
        }

        if($rsDay != 29) {
            if ($rsDay > $curDay) {
                $restockDate = $curDate->add(new \DateInterval('P' . ($rsDay - $curDay) . 'D'));
                $restockDate = $restockDate->add(new \DateInterval('P' . $gapTime . 'D'));
            } elseif ($rsDay == $curDay) {
                $restockDate = $curDate->add(new \DateInterval('P1M'));
                $restockDate = $restockDate->add(new \DateInterval('P' . $gapTime . 'D'));
            } else {
                $restockDate = $curDate->add(new \DateInterval('P1M'));
                $restockDate = $restockDate->sub(new \DateInterval('P'.($curDay-$rsDay).'D'));
                $restockDate = $restockDate->add(new \DateInterval('P' . $gapTime . 'D'));
            }
        } else {
            $curDayLastMonth = $this->getDayOfLastMonth($curDate->format('m'),$curDate->format('Y'));
            if ($curDayLastMonth > $curDay) {
                $restockDate = $curDate->add(new \DateInterval('P' . ($curDayLastMonth - $curDay) . 'D'));
                $restockDate = $restockDate->add(new \DateInterval('P' . $gapTime . 'D'));
            } else {
                $lastDayOfNextMonth = $this->getDayOfLastMonth((int)$curDate->format('m')+1,$curDate->format('Y'));

                $restockDate = $curDate->add(new \DateInterval('P' . $lastDayOfNextMonth . 'D'));
                $restockDate = $restockDate->add(new \DateInterval('P' . $gapTime . 'D'));
            }
        }

        return $restockDate->format('Y-m-d');
    }

    protected function getDayOfLastMonth($month, $year) {
        if(in_array($month, ['1', '3', '5', '7', '8', '10', '12', '13'])) {
            return 31;
        } elseif(in_array($month, ['4', '6', '9', '11'])) {
            return 30;
        } else {
            if(((int)$year%4) == 0) {
                return 29;
            } else {
                return 28;
            }
        }
    }

    /**
     * @param $gapTime
     * @param \Magestore\SupplierSuccess\Model\Supplier $supplier
     * @return string
     */
    protected function getNextQuarterly($gapTime, $supplier) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));
//        $curDate = $curDate->add(new \DateInterval('P2M'));
        $curDay = (int)$curDate->format('d');
        $curMonth = (int)$curDate->format('m');
        if(($curMonth%3) == 1) {
            $curMonthOfQuarter = 0;
        } elseif(($curMonth%3) == 2) {
            $curMonthOfQuarter = 1;
        } else {
            $curMonthOfQuarter = 2;
        }

        $monthOfQuarter = (int)$supplier->getData('monthOfQuarter');
        $rsDay = (int)$supplier->getData('dayOfMonth');
        if($rsDay != 28) {
            $rsDay += 1;
        } else {
            $rsDay = 29;
        }

        // Khi thang hien tai chua toi thang restock
        if($monthOfQuarter > $curMonthOfQuarter) {
            $rsMonth = $curMonth + ($monthOfQuarter - $curMonthOfQuarter);
            $lastDayOfCurMonth = $this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
            if($rsDay != 29) {
                $numDay = $rsDay + ((int)$lastDayOfCurMonth - $curDay);
            } else {
                $lastDayOfRsMonth = (int)$this->getDayOfLastMonth($rsMonth, $curDate->format('Y'));
                if(($monthOfQuarter - $curMonthOfQuarter) == 2) {
                    $lastDayOfRsMonth += (int)$this->getDayOfLastMonth($rsMonth-1, $curDate->format('Y'));
                }
                $numDay = $lastDayOfRsMonth + ((int)$lastDayOfCurMonth - $curDay);
            }
            $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
        } elseif ($monthOfQuarter == $curMonthOfQuarter) { // khi thang hien tai trung voi thang restock
            if($rsDay != 29) {
                if($rsDay > $curDay) {
                    $numDay = $rsDay - $curDay;
                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                } else {
                    $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                    $numDay = $lastDayOfCurMonth - $curDay;
                    // Cong voi so ngay cua 2 thang tiep theo
                    $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 1, $curDate->format('Y'));
                    if ($curMonth == 12) {
                        $numDay += (int)$this->getDayOfLastMonth(2, (int)$curDate->format('Y') + 1);
                    } else {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 2, $curDate->format('Y'));
                    }

                    // Cong voi so ngay cua thang cuoi
                    $numDay += $rsDay;

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                }
            } else {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                if($lastDayOfCurMonth > $curDay) {
                    $numDay = $lastDayOfCurMonth - $curDay;
                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                } else {
                    $numDay = 0;
                    // Cong voi so ngay cua 3 thang tiep theo
                    $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 1, $curDate->format('Y'));
                    // truong hop cong voi thang 2
                    if ($curMonth == 12) {
                        $numDay += (int)$this->getDayOfLastMonth(2, (int)$curDate->format('Y') + 1);
                    } else {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 2, $curDate->format('Y'));
                    }
                    if ($curMonth == 11) {
                        $numDay += (int)$this->getDayOfLastMonth(2, (int)$curDate->format('Y') + 1);
                    } else {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 3, $curDate->format('Y'));
                    }

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                }
            }
        } else { // Khi thang hien tai da qua thang restock
            if(($curMonthOfQuarter - $monthOfQuarter) == 2) {
                if($rsDay != 29) {
                    $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                    $numDay = $lastDayOfCurMonth - $curDay;

                    // Cong voi so ngay doi den ngay restock
                    $numDay += $rsDay;

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                } else {
                    $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                    $numDay = $lastDayOfCurMonth - $curDay;

                    // Cong voi so ngay doi den ngay restock
                    $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 1, $curDate->format('Y'));

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                }
            } else {
                if($rsDay != 29) {
                    $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                    $numDay = $lastDayOfCurMonth - $curDay;

                    // Cong voi so ngay cua thang tiep theo
                    $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 1, $curDate->format('Y'));

                    // Cong voi thang restock
                    // Cong voi so ngay restock
                    $numDay += $rsDay;

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                } else {
                    $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                    $numDay = $lastDayOfCurMonth - $curDay;

                    // Cong voi so ngay cua thang tiep theo
                    $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 1, $curDate->format('Y'));

                    // Cong voi thang restock
                    // truong hop cong voi thang 2
                    if ($curMonth == 12) {
                        $numDay += (int)$this->getDayOfLastMonth(2, (int)$curDate->format('Y') + 1);
                    } else {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + 2, $curDate->format('Y'));
                    }

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                }
            }
        }

        return $restockDate->format('Y-m-d');
    }

    /**
     * @param $gapTime
     * @param \Magestore\SupplierSuccess\Model\Supplier $supplier
     * @return string
     */
    protected function getNextYearly($gapTime, $supplier) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));
//        $curDate = $curDate->add(new \DateInterval('P2D'));
        $curDay = (int)$curDate->format('d');
        $curMonth = (int)$curDate->format('m');

        $rsMonth = (int)$supplier->getData('monthOfYear') + 1;
        $rsDay = (int)$supplier->getData('dayOfMonth');
        if($rsDay != 28) {
            $rsDay += 1;
        } else {
            $rsDay = 29;
        }

        if($rsMonth > $curMonth) {
            if($rsDay != 29) {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                $numDay = $lastDayOfCurMonth - $curDay;

                // Cong voi so ngay cua cac thang trung gian
                if(($rsMonth - $curMonth) > 1) { // Neu cach nhau 1 thang thi k co thang trung gian
                    for($i = 1; $i < ($rsMonth - $curMonth); $i++) {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + $i, $curDate->format('Y'));
                    }
                }

                $numDay += $rsDay;

                $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
            } else {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                $numDay = $lastDayOfCurMonth - $curDay;

                // Cong voi so ngay cua cac thang trung gian
                if(($rsMonth - $curMonth) > 1) {
                    for($i = 1; $i < ($rsMonth - $curMonth); $i++) {
                        $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + $i, $curDate->format('Y'));
                    }
                }

                // Cong so ngay cua thang restock
                $numDay += (int)$this->getDayOfLastMonth((int)$curDate->format('m') + ($rsMonth - $curMonth), $curDate->format('Y'));

                $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
            }
        } elseif ($rsMonth == $curMonth) {
            if($rsDay != 29) {
                if ($rsDay > $curDay) {
                    $numDay = $rsDay - $curDay;
                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                } else {
                    if($curMonth > 2) {
                        // Nam tiep theo la nam nhuan thi cong 366 ngay
                        if((((int)$curDate->format('Y') + 1) % 4) == 0) {
                            $numDay = 366;
                        } else {
                            $numDay = 365;
                        }
                    } else {
                        // Nam hien tai la nam nhuan thi cong 366 ngay
                        // Ngoai tru truong hop dang la ngay 29/2
                        if((((int)$curDate->format('Y') % 4) == 0)
                            && ((int)$curDate->format('m') != 2)
                            && ((int)$curDate->format('d') != 29)) {
                            $numDay = 366;
                        } else {
                            $numDay = 365;
                        }
                    }

                    $numDay -= ($curDay - $rsDay);

                    $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
                }
            } else {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                if($lastDayOfCurMonth != $curDay) {
                    $numDay = $lastDayOfCurMonth - $curDay;
                } else {
                    if($curMonth > 2) {
                        // Nam tiep theo la nam nhuan thi cong 366 ngay
                        if((((int)$curDate->format('Y') + 1) % 4) == 0) {
                            $numDay = 366;
                        } else {
                            $numDay = 365;
                        }
                    } else {
                        // Nam hien tai la nam nhuan thi cong 366 ngay
                        // Ngoai tru truong hop dang la ngay 29/2
                        if((((int)$curDate->format('Y') % 4) == 0)
                            && ((int)$curDate->format('m') != 2)) {
                            $numDay = 366;
                        } else {
                            $numDay = 365;
                        }
                    }
                }

                $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
            }
        } else {
            if($rsDay != 29) {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                // So ngay con lai cua thang hien tai
                $numDay = $lastDayOfCurMonth - $curDay;

                // Cong so ngay cua cac thang trung gian
                if(11 - ($curMonth - $rsMonth)) {
                    for($i = 1; $i <= (11 - ($curMonth - $rsMonth)); $i++) {
                        if(($curMonth + $i) <= 12) {
                            // Neu chua sang nam tiep theo
                            $numDay += (int)$this->getDayOfLastMonth(($curMonth + $i), $curDate->format('Y'));
                        } else {
                            // Neu da sang nam tiep theo
                            $numDay += (int)$this->getDayOfLastMonth(($curMonth + $i - 12), (int)$curDate->format('Y') + 1);
                        }
                    }
                }

                // Cong so ngay cua thang restock
                $numDay += $rsDay;

                $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
            } else {
                $lastDayOfCurMonth = (int)$this->getDayOfLastMonth($curDate->format('m'), $curDate->format('Y'));
                // So ngay con lai cua thang hien tai
                $numDay = $lastDayOfCurMonth - $curDay;

                // Cong so ngay cua cac thang trung gian
                if(11 - ($curMonth - $rsMonth)) {
                    for($i = 1; $i <= (11 - ($curMonth - $rsMonth)); $i++) {
                        if(($curMonth + $i) <= 12) {
                            // Neu chua sang nam tiep theo
                            $numDay += (int)$this->getDayOfLastMonth(($curMonth + $i), $curDate->format('Y'));
                        } else {
                            // Neu da sang nam tiep theo
                            $numDay += (int)$this->getDayOfLastMonth(($curMonth + $i - 12), (int)$curDate->format('Y') + 1);
                        }
                    }
                }

                // Cong so ngay cua thang restock (tong so ngay)
                $numDay += (int)$this->getDayOfLastMonth($rsMonth, (int)$curDate->format('Y') + 1);

                $restockDate = $curDate->add(new \DateInterval('P' . ($numDay + (int)$gapTime) . 'D'));
            }
        }

        return $restockDate->format('Y-m-d');
    }

    /**
     * @param $gapTime
     * @param \Magestore\SupplierSuccess\Model\Supplier $supplier
     * @return string
     */
    protected function getNextOther($gapTime, $supplier) {
        $curDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $rsStartDate = $supplier->getData('startDate');
        $rsDatePerTime = (int)$supplier->getData('numberDayPerTime');

        $periodTime = strtotime($curDate->format('Y-m-d')) - strtotime($rsStartDate);
        $periodTime = floor($periodTime/(60*60*24));

        $tmpVal = (int)($periodTime % $rsDatePerTime);

        $restockDate = $curDate->add(new \DateInterval('P' . (($rsDatePerTime-$tmpVal)+(int)$gapTime) . 'D'));

        return $restockDate->format('Y-m-d');
    }
}