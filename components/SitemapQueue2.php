<?php

namespace panix\mod\sitemap\components;

use panix\mod\sitemap\behaviors\SitemapBehavior;
use Yii;
use XMLWriter;
use yii\base\BaseObject;
use yii\data\Pagination;
use yii\queue\JobInterface;

class SitemapQueue2 extends BaseObject implements JobInterface
{

    public $query;
    public $page;
    public $limit;
    public $offset;
    public $filename;
    public $defaultPriority = false;
    public $defaultChangefreq = false;


    public function execute($queue)
    {

        $result = [];
        $n = 0;
        $page = $this->page - 1;

        $countQuery = clone $this->query;

        $this->query->limit($this->limit);
        $this->query->offset($this->offset);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');

        foreach (Yii::$app->sitemap->schemas as $attr => $schemaUrl) {
            $xml->writeAttribute($attr, $schemaUrl);
        }

        foreach ($this->query->each($this->limit) as $data) {
            $xml->startElement('url');
            $urlData = call_user_func(function ($data2) {
                return [
                    'loc' => $data2->getUrl(),
                    'lastmod' => $data2->updated_at,
                    'changefreq' => SitemapBehavior::CHANGEFREQ_DAILY,
                    'priority' => 0.9
                ];
            }, $data);

            if (empty($urlData)) {
                continue;
            }
           // $result[$n]['loc'] = Yii::$app->urlManager->createAbsoluteUrl($urlData['loc']);
            $xml->writeElement('loc', Yii::$app->urlManager->createAbsoluteUrl($urlData['loc']));
            if (!empty($urlData['lastmod'])) {
                //$result[$n]['lastmod'] = Sitemap::dateToW3C($urlData['lastmod']);
                $xml->writeElement('lastmod', Sitemap::dateToW3C($urlData['lastmod']));
            }

             if (isset($urlData['changefreq'])) {
                 //$result[$n]['changefreq'] = $urlData['changefreq'];
                 $xml->writeElement('changefreq', $urlData['changefreq']);
             } elseif ($this->defaultChangefreq !== false) {
                 //$result[$n]['changefreq'] = $this->defaultChangefreq;
                 $xml->writeElement('changefreq', $this->defaultChangefreq);
             }
             if (isset($urlData['name'])) {
                 //$result[$n]['name'] = $urlData['name'];
                 $xml->writeElement('name', $urlData['name']);
             }
             if (isset($urlData['priority'])) {
                 //$result[$n]['priority'] = $urlData['priority'];
                 $xml->writeElement('priority', $urlData['priority']);
             } elseif ($this->defaultPriority !== false) {
                 //$result[$n]['priority'] = $this->defaultPriority;
                 $xml->writeElement('priority', $this->defaultPriority);
             }
            ++$n;
            $xml->endElement();
        }

        $xml->endElement(); // urlset
        $xml->endElement(); // document
        $file = Yii::getAlias('@uploads/' . trim($this->filename.'.xml', '/'));
        file_put_contents($file, $xml->outputMemory());


        return true;
    }

}
