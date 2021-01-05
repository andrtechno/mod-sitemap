<?php

namespace panix\mod\sitemap\controllers;

use panix\engine\CMS;
use panix\mod\sitemap\components\RobotsTxt;
use panix\engine\controllers\WebController;
use Yii;
use yii\web\Response;

class DefaultController extends WebController
{


    public function behaviors2()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'], //, 'robots-txt'
                'duration' => 86400 * 7,
                'variations' => [Yii::$app->request->get('id')],
            ],
        ];
    }

    public function actionHtml()
    {
        $this->pageName = Yii::t('sitemap/default', 'MODULE_NAME');
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $module = $this->module;
        $urls = $module->urls;
        foreach ($module->models as $k => $modelName) {
            /** @var behaviors\SitemapBehavior|\yii\db\ActiveRecord $model */
            if (is_array($modelName)) {


                $model = new $modelName['class'];
                if (isset($modelName['behaviors'])) {
                    $model->attachBehaviors($modelName['behaviors']);
                }
            } else {
                $model = new $modelName;
            }
            $urls = array_merge($urls, $model->generateSiteMapHtml());
        }

        return $this->render('html', ['data' => $urls]);
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

            $data = $this->renderPartial('xml', [
                'urls' => Yii::$app->getModule('sitemap')->getUrls()
            ]);

            Yii::$app->cache->set($cacheKey, $data, 86400 * 7);
        }

        if (!headers_sent())
            header('Content-Type: text/xml');

        echo $data;
    }


}