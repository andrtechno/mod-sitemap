<?php

namespace panix\mod\sitemap;

use Yii;
use yii\db\Query;
use yii\helpers\Url;

class Module extends \panix\engine\WebModule {

    public $routes = [
        'sitemap.xml' => 'sitemap/default/index',
    ];
    public $icon = 'icon-sitemap';

    /**
     * @var string
     */
    public $changeFreq = 'daily';

    /**
     * @var array
     */
    protected $_urls = [];

    /**
     * @return array
     */
    public function getUrls() {
        $this->loadProducts();
        $this->loadManufacturers();
        $this->loadCategories();
        return $this->_urls;
    }

    /**
     * Load products data
     */
    public function loadProducts() {
        $products = (new Query())
                ->select(['seo_alias', 'date_create as date'])
                ->from('{{%shop_product}}')
                ->all();
        $this->populateUrls('/shop/default/view', $products);
    }

    /**
     * Load manufacturers data
     */
    public function loadManufacturers() {
        $records = (new Query())
                ->select(['seo_alias'])
                ->from('{{%shop_manufacturer}}')
                ->all();
        $this->populateUrls('/shop/manufacturer/index', $records);
    }

    /**
     * Load categories data
     */
    public function loadCategories() {
        $records = (new Query())
                ->select(['full_path as seo_alias'])
                ->from('{{%shop_category}}')
                ->where('id > 1')
                ->all();
        $this->populateUrls('/shop/category/view', $records);
    }

    /**
     * Populate urls data with store records.
     *
     * @param $route
     * @param $records
     * @param string $changefreq
     * @param string $priority
     */
    public function populateUrls($route, $records, $changefreq = 'daily', $priority = '1.0') {
        foreach ($records as $p) {
            if (isset($p['seo_alias']) && !empty($p['seo_alias'])) {
                $url = Yii::$app->urlManager->createAbsoluteUrl([$route, 'seo_alias' => $p['seo_alias']], true);

                $this->_urls[$url] = array(
                    'changefreq' => $changefreq,
                    'priority' => $priority
                );

                if (isset($p['date']) && strtotime($p['date']))
                    $this->_urls[$url]['lastmod'] = date('Y-m-d', strtotime($p['date']));
            }
        }
    }

}
