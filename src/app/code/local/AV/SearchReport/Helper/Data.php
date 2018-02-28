<?php

class AV_SearchReport_Helper_Data {
    /*
     * Create actually timestamp
     */

    public function createTimestamp() {
        $date_timestamp = Mage::getModel('core/date')->timestamp(strtotime($rma['created_time']));
        return date('m-d-Y-His', $date_timestamp);
    }

}
