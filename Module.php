<?php

namespace panix\mod\sitemap;

use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\Product;
use panix\mod\sitemap\behaviors\SitemapBehavior;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\db\Query;
use panix\engine\WebModule;
use yii\helpers\Url;

class Module extends WebModule implements BootstrapInterface
{


    public $icon = 'icon-sitemap';

    /**
     * @var string
     */
    public $changeFreq = 'daily';

    /**
     * @var array
     */
    protected $_urls = [];
    public $controllerNamespace = 'panix\mod\sitemap\controllers';
    /** @var int */
    public $cacheExpire = 1;//86400;
    /** @var Cache|string */
    public $cacheProvider = 'cache';
    /** @var string */
    public $cacheKey = 'sitemap';
    /** @var boolean Use php's gzip compressing. */
    public $enableGzip = false;
    /** @var boolean */
    public $enableGzipedCache = false;
    /** @var array */
    public $models = [];
    /** @var array */
    public $urls = [];

    public function init()
    {
        parent::init();
        if (is_string($this->cacheProvider)) {
            $this->cacheProvider = Yii::$app->{$this->cacheProvider};
        }
        if (!$this->cacheProvider instanceof Cache) {
            throw new InvalidConfigException('Invalid `cacheKey` parameter was specified.');
        }

        $this->cacheExpire = 1;
    }

    public function bootstrap($app)
    {
        $app->urlManager->addRules(
            [
              //  ['pattern' => 'robots1', 'route' => 'sitemap/default/robots-txt', 'suffix' => '.txt'],
                ['pattern' => 'sitemap', 'route' => 'sitemap/default/xml', 'suffix' => '.xml'],
                ['pattern' => 'sitemap', 'route' => 'sitemap/default/html'],
            ],
            true
        );
    }


    /**
     * @return array
     */
    public function getUrls()
    {
        $this->loadProducts();
        $this->loadManufacturers();
        $this->loadCategories();
        return $this->_urls;
    }

    /**
     * Load products data
     */
    public function loadProducts()
    {
        $products = (new Query())
            ->select(['slug', 'created_at as date'])
            ->from(Product::tableName())
            ->where(['switch' => 1])
            ->all();
        $this->populateUrls('/shop/product/view', $products);
    }

    /**
     * Load manufacturers data
     */
    public function loadManufacturers()
    {
        $records = (new Query())
            ->select(['slug'])
            ->from(Manufacturer::tableName())
            ->where(['switch' => 1])
            ->all();
        $this->populateUrls('/shop/manufacturer/index', $records);
    }

    /**
     * Load categories data
     */
    public function loadCategories()
    {
        $records = (new Query())
            ->select(['full_path as slug'])
            ->from(Category::tableName())
            ->where('id > 1')
            ->all();
        $this->populateUrls('/shop/catalog/view', $records);
    }

    /**
     * Populate urls data with store records.
     *
     * @param $route
     * @param $records
     * @param string $changefreq
     * @param string $priority
     */
    public function populateUrls($route, $records, $changefreq = 'daily', $priority = '1.0')
    {
        foreach ($records as $p) {
            if (isset($p['slug']) && !empty($p['slug'])) {
                $url = Yii::$app->urlManager->createAbsoluteUrl([$route, 'slug' => $p['slug']], true);

                $this->_urls[$url] = [
                    'changefreq' => $changefreq,
                    'priority' => $priority
                ];

                if (isset($p['date']) && strtotime($p['date']))
                    $this->_urls[$url]['lastmod'] = date('Y-m-d', strtotime($p['date']));
            }
        }
    }

    /**
     * Build and cache a site map.
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     */
    public function buildSitemap()
    {
        $urls = $this->urls;


        foreach ($this->models as $modelName) {
            /** @var behaviors\SitemapBehavior|\yii\db\ActiveRecord $model */
            if (is_array($modelName)) {


                $model = new $modelName['class'];
                if (isset($modelName['behaviors'])) {
                    $model->attachBehaviors($modelName['behaviors']);
                }
            } else {
                $model = new $modelName;
            }
            $urls = array_merge($urls, $model->generateSiteMap());
        }

        $sitemapData = $this->createControllerByID('default')->renderPartial('xml', ['urls' => $urls]);
        if ($this->enableGzipedCache) {
            $sitemapData = gzencode($sitemapData);
        }
        $this->cacheProvider->set($this->cacheKey, $sitemapData, $this->cacheExpire);
        return $sitemapData;
    }

    public function clearCache()
    {
        $this->cacheProvider->delete($this->cacheKey);
    }

}
