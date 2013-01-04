<?php
/**
 * Observer class used to handle reward points in an order
 *
 */
namespace plugins\riSeo;
use plugins\riPlugin\Plugin;

class SeoObserver extends \base
{
    function __construct()
    {
        global $zco_notifier;
        $zco_notifier->attach($this, array('NOTIFY_MODULE_START_META_TAGS'));
    }

    function update(&$class, $eventID, $paramArray)
    {
        $this->processMeta();
    }

    public function processMeta()
    {
        global $db;
        $sql = "SELECT seo_meta.seo_id, seo_meta.page_id, seo_meta_data.meta_name, seo_meta_data.meta_content
                        FROM seo_meta
                        INNER JOIN seo_meta_data
                        ON seo_meta.seo_id = seo_meta_data.seo_id
                        WHERE seo_meta.main_page = :main_page
                        ORDER BY seo_meta_data.seo_meta_data_id";

        $sql = $db->bindVars($sql, ":main_page", $_GET['main_page'], 'string');

        $result = $db->Execute($sql);

        if ($result->RecordCount() > 0) {

            while (!$result->EOF) {
                echo "<pre>";var_dump($result->fields['meta_name']);
                $result->MoveNext();
            }
            die();
            define('META_TAG_TITLE', $result->fields['title']);
            define('META_TAG_DESCRIPTION', $result->fields['description']);
            define('META_TAG_KEYWORDS', $result->fields['keywords']);
        }
    }
}

?>
