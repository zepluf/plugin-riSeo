<?php
/**
 * Created by RubikIntegration Team.
 * User: vunguyen
 * Date: 6/26/12
 * Time: 5:58 PM
 * Question? Come to our website at http://rubikin.com
 */

namespace plugins\riSeo;

use Zepluf\Bundle\StoreBundle\PluginCore;
use Zepluf\Bundle\StoreBundle\Event\CoreEvent;
use Zepluf\Bundle\StoreBundle\Events;

class RiSeo extends PluginCore
{
    public function init()
    {
        if (!IS_ADMIN_FLAG) {
            $this->container->get('event_dispatcher')->addListener(Events::onPageEnd, array($this, 'onPageEnd'));

//            global $autoLoadConfig;
//            $autoLoadConfig[190][] = array('autoType' => 'include', 'loadFile' => __DIR__ . '/lib/observers.php');
        }
    }

    public function onPageEnd(Event $event)
    {
        $content = & $event->getContent();
        $content = $this->container->get('riSeo.Metas')->processMeta($content);
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
        return $this->container->get('database_patcher')->executeSqlFile(file(__DIR__ . '/install/sql/install.sql'));
//        }
    }

    public function uninstall()
    {
        return $this->container->get('database_patcher')->executeSqlFile(file(__DIR__ . '/install/sql/uninstall.sql'));
    }
}