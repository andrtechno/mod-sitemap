<?php
namespace panix\mod\sitemap\controllers;

use Yii;
use panix\engine\controllers\WebController;

class DefaultController extends WebController {

    /**
     * Render sitemap.xml
     */
    public function actionIndex() {
        $cacheKey = 'sitemap.xml.cache';
        $data = Yii::$app->cache->get($cacheKey);

        if (!$data) {

            $data = $this->renderPartial('xml', array(
                'urls' => Yii::$app->getModule('sitemap')->getUrls()
                    ), true);

            Yii::$app->cache->set($cacheKey, $data, 3200*12);
        }

        if (!headers_sent())
            header('Content-Type: text/xml');

        echo $data;
    }

}