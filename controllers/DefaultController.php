<?php
namespace panix\mod\sitemap\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use panix\mod\sitemap\RobotsTxt;

class DefaultController extends Controller {


    public function behaviors2()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index', 'robots-txt'],
                'duration' => Yii::$app->sitemap->cacheExpire,
                'variations' => [Yii::$app->request->get('id')],
            ],
        ];
    }



    public function actionIndex2()
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
     * Action for sitemap/default/robot-txt
     *
     * @access public
     * @return string
     */
    public function actionRobotsTxt()
    {
        $robotsTxt = empty(Yii::$app->components['robotsTxt']) ? new RobotsTxt() : Yii::$app->robotsTxt;
        $robotsTxt->sitemap = Yii::$app->urlManager->createAbsoluteUrl(
            empty($robotsTxt->sitemap) ? [$this->module->id.'/'.$this->id.'/index'] : $robotsTxt->sitemap
        );
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/plain');
        return $robotsTxt->render();
    }

    /**
     * Render sitemap.xml
     */
    public function actionIndex() {
        $cacheKey = 'sitemap.xml.cache';
        $data = Yii::$app->cache->get($cacheKey);

        if (!$data) {

            $data = $this->renderPartial('index', array(
                'urls' => Yii::$app->getModule('sitemap')->getUrls()
                    ), true);

            Yii::$app->cache->set($cacheKey, $data, 3200*12);
        }

        if (!headers_sent())
            header('Content-Type: text/xml');

        echo $data;
    }

}