<?php

class Module extends \panix\engine\WebModule {

    /**
     * Иконка модуля
     * @var string 
     */
    public $icon = 'icon-sitemap';

    /**
     * @var string
     */
    public $changeFreq = 'daily';

    /**
     * @var array
     */
    public $urls = array();

    /**
     * @return array
     */
    public function getUrls() {
        $this->loadProducts();
        $this->loadManufacturers();
        $this->loadCategories();

        return $this->urls;
    }


    /**
     * Load products data
     */
    public function loadProducts() {
        $products = Yii::$app->db->createCommand()
                ->from('{{%shop_product}}')
                ->select('seo_alias, date_create as date')
                ->queryAll();

        $this->populateUrls('shop/product/view', $products);
    }

    /**
     * Load manufacturers data
     */
    public function loadManufacturers() {
        $records = Yii::$app->db->createCommand()
                ->from('{{%shop_manufacturer}}')
                ->select('seo_alias')
                ->queryAll();

        $this->populateUrls('shop/manufacturer/index', $records);
    }

    /**
     * Load categories data
     */
    public function loadCategories() {
        $records = Yii::$app->db->createCommand()
                ->from('{{%shop_category}}')
                ->select('full_path as seo_alias')
                ->where('id > 1')
                ->queryAll();

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
            $url = Yii::app()->createAbsoluteUrl($route, array('seo_alias' => $p['seo_alias']));

            $this->urls[$url] = array(
                'changefreq' => $changefreq,
                'priority' => $priority
            );

            if (isset($p['date']) && strtotime($p['date']))
                $this->urls[$url]['lastmod'] = date('Y-m-d', strtotime($p['date']));
        }
    }

    public function getRules() {
        return array(
            '/sitemap.xml' => array('sitemap/default/index'),
        );
    }

}
