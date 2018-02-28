<?php

class AV_SearchReport_Model_Report {
    /*
     *  Get catalogsearch collection from last 7 days
     */

    public function getCatalogSearch() {

        $from_date = date('Y-m-d H:i:s', time() - (60 * 60 * 24 * 7));

        $search = Mage::getModel('catalogsearch/query')
                ->getCollection()
                ->setPageSize(500)
                ->addFieldToFilter('updated_at', array('from' => $from_date));

        return $search;
    }

    /*
     * Get date timestamp from helper
     */

    public function getTimestamp() {
        return Mage::helper('av_searchreport')->createTimestamp();
    }

    /*
     *  Prepare CSV to report
     */

    public function preparationCsv() {

        $search_result = $this->getCatalogSearch();
        $csv = new Varien_File_Csv();
        $csvdata = array();
        $path = Mage::getBaseDir('var') . DS . 'search_report';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $name = "searchreport";
        $file = $path . DS . $name . '_' . $this->getTimestamp() . '.csv';
        $_columns = array(
            "Search query",
            "All search result",
            "Number of visit"
        );
        $data = array();
        $csvdata[] = $_columns;

        foreach ($search_result as $result) {
            $data = array();
            $query_text = $result->getQueryText();
            $popularity = $result->getPopularity();
            $num_result = $result->getNumResults();

            $data[] = $query_text;
            $data[] = $num_result;
            $data[] = $popularity;

            $csvdata[] = $data;
            $i++;
        }
        $csv->setDelimiter(',');
        $csv->setEnclosure('"');
        $csv->saveData($file, $csvdata);
        $this->sendMail($file);
    }

    /*
     * Send csv attachements
     */

    public function sendMail($attachements) {

        $mail = new Zend_Mail('utf-8');
        $recipients = array(
            Mage::getStoreConfig('trans_email/ident_custom1/name') => Mage::getStoreConfig('trans_email/ident_custom1/email'),
            Mage::getStoreConfig('trans_email/ident_custom2/name') => Mage::getStoreConfig('trans_email/ident_custom2/email'),
        );
        $mail_body = "Search Report";
        $from_name = Mage::getStoreConfig('trans_email/ident_general/name');
        $mail->setBodyHtml($mail_body)
                ->setSubject('Search Report' . ' ' . $this->getTimestamp())
                ->addTo($recipients)
                ->setFrom(Mage::getStoreConfig('trans_email/ident_general/email'), $from_name);

        $file = $attachements;
        $attachment = file_get_contents($file);
        $mail->createAttachment(
                $attachment, Zend_Mime::TYPE_OCTETSTREAM, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'searchreport' . '_' . $this->getTimestamp() . '.csv'
        );
        try {
            $mail->send();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
