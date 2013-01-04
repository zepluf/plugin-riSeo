<?php
/**
 * Created by RubikIntegration Team.
 * Date: 12/20/12
 * Time: 9:58 AM
 * Question? Come to our website at http://rubikin.com
 */

namespace plugins\riSeo;

use plugins\riPlugin\Plugin;

class Metas extends \Zepluf\Bundle\StoreBundle\ParameterBag
{
    protected $meta = array();
    protected $settings;

    /**
     * Constructor
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->set('template.title', '<title>%value%</title>');
    }

    /**
     * Get meta data by page
     *
     * @param string $main_page
     * @param string $page_id
     * @param boolean $isAdmin
     * @return array
     *
     */
    public function getMeta($main_page, $page_id = 0, $isAdmin = false)
    {

        global $db;
        $sql = "SELECT sm.seo_id, sm.page_id, smd.meta_name, smd.meta_content
                FROM " . TABLE_META . " sm
                INNER JOIN " . TABLE_META_DATA . " smd
                ON sm.seo_id = smd.seo_id
                WHERE sm.main_page = :main_page
                AND sm.page_id = :page_id
                ORDER BY smd.seo_meta_data_id";

        $sql = $db->bindVars($sql, ":main_page", $main_page, 'string');
        $sql = $db->bindVars($sql, ":page_id", $page_id, 'integer');

        $result = $db->Execute($sql);
        if ($result->RecordCount() > 0) {
            $meta['seo_id'] = $result->fields['seo_id'];
            $meta['main_page'] = $result->fields['main_page'];
            $meta['page_id'] = $result->fields['page_id'];

            while (!$result->EOF) {
                $meta['metas'][$result->fields['meta_name']] = $result->fields['meta_content'];
                $result->MoveNext();
            }
        }

        $fallback = (bool)$this->settings->get('plugins.riseo.zencart_fallback');
        if ($fallback) {
            if ((!isset($meta['metas']['title']) || !isset($meta['metas']['description']) || !isset($meta['metas']['keywords']))) {
                $current_page = $_GET['main_page'];
                $_GET['main_page'] = $main_page;
                /// code cho cai module
                /// $_GET['main_page'] = $current_page;
//                require($_SERVER['DOCUMENT_ROOT'] . '/includes/modules/meta_tags.php');
                global $currencies;
                require("meta_tags.php");
                if (!$isAdmin) {
                    if (!isset($meta['metas']['title'])) {
                        $meta['metas']['title'] = META_TAG_TITLE;
                    }
                    if (!isset($meta['metas']['description'])) {
                        $meta['metas']['description'] = META_TAG_DESCRIPTION;
                    }
                    if (!isset($meta['metas']['keywords'])) {
                        $meta['metas']['keywords'] = META_TAG_KEYWORDS;
                    }
                }
            }
        }

        return $meta;
    }

    /**
     * Save meta data
     *
     * @param array $meta
     * @param array $meta_data_array
     * @return integer
     *
     */
    public function saveMeta($meta, $meta_data_array)
    {
        global $db;
        $meta_data = array();

        if ($meta['seo_id'] == '') {
            zen_db_perform(TABLE_META, $meta);
            $id = $db->insert_ID();
            $meta_data['seo_id'] = $id;
            foreach ($meta_data_array as $name => $content) {
                if (trim($content) != '') {
                    $meta_data['meta_name'] = $name;
                    $meta_data['meta_content'] = $content;
                    zen_db_perform(TABLE_META_DATA, $meta_data);
                }
            }

            return $id;
        } else {
            foreach ($meta_data_array as $name => $content) {
                if ($this->isMetaExist($name, (int)$meta['seo_id'])) {
                    $seo_id = (int)$meta['seo_id'];
                    if (trim($content) != '') {
                        $data = array('meta_content' => $content);
                        zen_db_perform(TABLE_META_DATA, $data, 'update', 'seo_id = ' . $seo_id . " and meta_name = '" . $name . "'");
                    } else {
                        $this->deleteSingleMeta($seo_id, $name);
                    }
                } else {
                    if (trim($content) != '') {
                        $meta_data['seo_id'] = $seo_id;
                        $meta_data['meta_name'] = $name;
                        $meta_data['meta_content'] = $content;
                        zen_db_perform(TABLE_META_DATA, $meta_data);
                    }
                }
            }
        }
    }

    /**
     * Check the main page, get meta data and inject into given content
     *
     * @param string $content
     * @param string $page
     * @return string
     *
     */
    public function processMeta(&$content, $main_page, $page_id = 0)
    {
        $this->meta = $this->getMeta($main_page, $page_id);
        if (!empty($this->meta)) {
            //inject meta data into content and return
            return $this->injectMetas($content, $main_page);
        }
        //return original content
        return $content;
    }

    /**
     * Delete single meta with seo_id in table seo_meta and name
     *
     * @param integer $seo_id
     * @param string $meta_name
     *
     */
    public function deleteSingleMeta($seo_id, $meta_name)
    {
        global $db;
        $sql = "DELETE FROM " . TABLE_META_DATA . " WHERE seo_id = :seo_id AND meta_name = :meta_name";
        $sql = $db->bindVars($sql, ":seo_id", $seo_id, 'integer');
        $sql = $db->bindVars($sql, ":meta_name", $meta_name, 'string');
        $db->Execute($sql);
    }

    /**
     * Inject meta data into HTML rendered content
     *
     * @param string $content
     * @param string $page
     * @return string
     *
     */
    private function injectMetas(&$content, $page)
    {
        foreach ($this->meta['metas'] as $key => $value) {
            if (!$this->has("metas." . $key . "")) {
                if (trim($value) != '') {
                    $this->set("metas." . $key . "", $value);
                }
            }
        }

        $metas = "\r\n";

//        $found_robot_meta = false;
        foreach ($this->get('metas') as $key => $value) {
            if ($key == "robots") {
                $found_robot_meta = true;
            }
            $metas .= str_replace(array('%key%', '%value%'), array($key, $value), $this->get('template.' . $key, '<meta name="%key%" content="%value%"/>')) . "\r\n";
        }

        if (!$found_robot_meta) {
            if ($this->checkRobots($page)) {
                $metas .= str_replace(array('%key%', '%value%'), array('robots', 'noindex, nofollow'), $this->get('template.' . 'robots', '
            <meta name="%key%" content="%value%"/>')) . "\r\n";
            }
        }

        $content = str_replace('<head>', '<head>' . $metas, $content);
        return $content;
    }

    /**
     * Check database whether certain Meta exist or not
     *
     * @param   string  $name   Meta name
     * @param   integer $seo_id Foreign key to meta_data table to identify certain page
     * @return  boolean
     *
     */
    private function isMetaExist($name, $seo_id)
    {
        global $db;
        $sql = "SELECT *
                FROM " . TABLE_META_DATA . "
                WHERE seo_id = :seo_id
                AND meta_name = :meta_name";

        $sql = $db->bindVars($sql, ":seo_id", $seo_id, 'string');
        $sql = $db->bindVars($sql, ":meta_name", $name, 'string');
        $result = $db->Execute($sql);

        if ($result->RecordCount() > 0) {

            return true;
        }

        return false;
    }

    private function checkRobots($page)
    {
        global $breadcrumb, $db;
        if (!isset($robotsNoIndex)) $robotsNoIndex = false;
        // might need isset($_GET['cPath']) later ... right now need $cPath or breaks breadcrumb from sidebox etc.
        if (isset($cPath_array) && isset($cPath)) {
            for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
                $categories_query = "select categories_name
                           from " . TABLE_CATEGORIES_DESCRIPTION . "
                           where categories_id = '" . (int)$cPath_array[$i] . "'
                           and language_id = '" . (int)$_SESSION['languages_id'] . "'";

                $categories = $db->Execute($categories_query);
                //echo 'I SEE ' . (int)$cPath_array[$i] . '<br>';
                if ($categories->RecordCount() > 0) {
                    $breadcrumb->add($categories->fields['categories_name'], zen_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i + 1)))));
                } elseif (SHOW_CATEGORIES_ALWAYS == 0) {
                    // if invalid, set the robots noindex/nofollow for this page
                    $robotsNoIndex = true;
                    break;
                }
            }
        }
        if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($page, explode(",", constant('ROBOTS_PAGES_TO_SKIP'))) || $page == 'down_for_maintenance' || $robotsNoIndex === true) {

            return true;
        }

        return false;
    }
}