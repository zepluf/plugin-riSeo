<?php
/**
 * Created by RubikIntegration Team.
 * User: vunguyen
 * Date: 6/26/12
 * Time: 5:58 PM
 * Question? Come to our website at http://rubikin.com
 */

namespace plugins\riSeo;

use plugins\riCore\PluginCore;
use plugins\riPlugin\Plugin;
use plugins\riCore\Event;

class RiSeo extends PluginCore
{

    public function init()
    {
        if (!IS_ADMIN_FLAG) {
            Plugin::get('dispatcher')->addListener(\plugins\riCore\Events::onPageEnd, array($this, 'onPageEnd'));

//            global $autoLoadConfig;
//            $autoLoadConfig[190][] = array('autoType' => 'include', 'loadFile' => __DIR__ . '/lib/observers.php');
        }
    }

    public function onPageEnd(Event $event)
    {
        $content = & $event->getContent();
        $content = Plugin::get('riSeo.Metas')->processMeta($content, $_GET['main_page'], $_GET['id']);
        $event->setContent($content);

        return $event;
    }

    public function install()
    {
//        $url = HTTP_SERVER . DIR_WS_CATALOG;
//        $needle = '<meta';
//        $contents = file_get_contents($url);
//        if (strpos($contents, $needle) !== false) {
//            global $messageStack;
//            $messageStack->add("Please remove meta tags in your 'html_header' file", 'error');
//            return false;
//        } else {
        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/install.sql'));
//        }

    }

    public function uninstall()
    {
        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/uninstall.sql'));
    }
}