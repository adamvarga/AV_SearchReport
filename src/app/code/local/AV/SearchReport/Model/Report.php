<?php

class AV_SearchReport_Model_Report
{
    /*
     *  Get catalogsearch collection from last 7 days
     */

    public function getCatalogSearch()
    {

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

    public function getTimestamp()
    {
        return Mage::helper('av_searchreport')->createTimestamp();
    }

    /*
     *  Prepare CSV to report
     */

    public function preparationCsv()
    {

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
        }

        $csv->setDelimiter(',');
        $csv->setEnclosure('"');
        $csv->saveData($file, $csvdata);
        $this->sendMail($file);
    }

    /*
    * Reset popularity to 0
    */

    public function cleanDb($result)
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $table = $resource->getTableName('catalogsearch/search_query');

        $write->update(
            $table,
            array('popularity' => 0)
        );
    }

    /*
     * Send csv attachements
     */

    public function sendMail($attachements)
    {

        $file = $attachements;
        $template_id = 'result';
        $msg = 'Search Report ' . $this->getTimestamp();
        $mail = Mage::getModel('core/email_template')->loadDefault($template_id);
        $mail_from = Mage::getStoreConfig('trans_email/ident_general/email');
        $mail_to = Mage::getStoreConfig('trans_email/ident_custom1/email');
        $customer_name = Mage::getStoreConfig('trans_email/ident_general/name');
        $mail_subject = "AV Search Report";
        $mail_name = Mage::getStoreConfig('trans_email/ident_general/name');
        $mail->setSenderName($mail_name);
        $mail->setSenderEmail($mail_to);
        $mail->getMail()->createAttachment(
            file_get_contents($file),
            Zend_Mime::TYPE_OCTETSTREAM,
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            $file
        );

        $email_template_variables = array(
            'customer_name' => $customer_name,
            'message' => $msg
        );

        $mail->setTemplateSubject(trim($mail_subject));
        $mail->setFromEmail($mail_from);
        $mail->setFromName($mail_name);
        $mail->setType('html');


        try {
            $mail->send($mail_to, $customer_name, $email_template_variables);
            $this->cleanDb();
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

}
