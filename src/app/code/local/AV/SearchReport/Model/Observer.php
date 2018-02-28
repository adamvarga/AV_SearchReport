<?php

class AV_SearchReport_Model_Observer {

    public function run() {
        return Mage::getModel("av_searchreport/report")->preparationCsv();
    }

}
