<?php

namespace panix\mod\sitemap\controllers;

use panix\engine\CMS;
use panix\mod\sitemap\components\RobotsTxt;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{


    public function behaviors2()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'], //, 'robots-txt'
                'duration' => 86400*7,
                'variations' => [Yii::$app->request->get('id')],
            ],
        ];
    }

    public function actionHtml()
    {
        die('zz');
    }

    public function actionXml()
    {
        $module = $this->module;
        if (!$sitemapData = $module->cacheProvider->get($module->cacheKey)) {
            $sitemapData = $module->buildSitemap();
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        if ($module->enableGzip) {
            if (!$module->enableGzipedCache) {
                $sitemapData = gzencode($sitemapData);
            }
            $headers->add('Content-Encoding', 'gzip');
            $headers->add('Content-Length', strlen($sitemapData));
        } elseif ($module->enableGzipedCache) {
            $sitemapData = gzdecode($sitemapData);
        }

        return $sitemapData;
    }

    /**
     * Render sitemap.xml
     */
    public function actionIndex2()
    {
        $cacheKey = 'sitemap.xml.cache';
        $data = Yii::$app->cache->get($cacheKey);

        if (!$data) {

            $data = $this->renderPartial('index', [
                'urls' => Yii::$app->getModule('sitemap')->getUrls()
            ]);

            Yii::$app->cache->set($cacheKey, $data, 86400*7);
        }

        if (!headers_sent())
            header('Content-Type: text/xml');

        echo $data;
    }


}