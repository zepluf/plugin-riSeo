<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Kim
 * Date: 11/30/12
 * Time: 11:07 AM
 * To change this template use File | Settings | File Templates.
 */
namespace plugins\riSeo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use plugins\riSimplex\Controller;
use plugins\riPlugin\Plugin;

class AdminSeoController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        //$meta = Plugin::get('riSeo.Metas')->injectMetas();
        $pages = Plugin::get('settings')->get("riSeo.exclude_pages");

//        $ez_pages = $this->getEZPages();
        $this->view->get('holder')->add('main', $this->view->render('riSeo::backend/manager.php', array('pages' => $pages)));

        return $this->render('riSeo::admin_layout.php');
    }

    public function ajaxGetPageMeta(Request $request)
    {
        $page = $request->get('page');

        $meta_data = Plugin::get('riSeo.Metas')->getMeta($page, true);
        if ($meta_data != null) {

            return $this->renderJson($meta_data);
        } else {

            return $this->renderJson(true);
        }
    }

    public function ajaxSavePageMeta(Request $request)
    {
        $meta = array(
            'seo_id' => $request->get('seo-id'),
            'main_page' => $request->get('main-page')
        );
        $meta_data_array = $request->get('metas');

        $additional_meta_name_array = $request->get('add-meta-name');
        $additional_meta_content_array = $request->get('add-meta-content');

        for ($i = 0; $i < count($additional_meta_name_array); $i++) {
            $meta_data_array[$additional_meta_name_array[$i]] = $additional_meta_content_array[$i];
        }

        $result = Plugin::get('riSeo.Metas')->saveMeta($meta, $meta_data_array);

        return $this->renderJson($result);
    }

    public function ajaxDeletePageMeta(Request $request)
    {
        $page = $request->get('page');
        if ($page != null) {
            Plugin::get('riSeo.Metas')->deleteMeta($page);
        }
//        return $this->renderJson($result);
    }

    public function ajaxDeleteSingleMeta(Request $request)
    {
        $seo_id = $request->get('seo_id');
        $meta_name = $request->get('meta_name');

        if ($seo_id != null) {
            Plugin::get('riSeo.Metas')->deleteSingleMeta($seo_id, $meta_name);
        }
    }

    private function getEZPages()
    {
        global $db;
        $sql = "SELECT pages_id, pages_title
                FROM " . TABLE_EZPAGES;

        $result = $db->Execute($sql);

        if ($result->RecordCount() > 0) {
            return true;
        }
    }
}