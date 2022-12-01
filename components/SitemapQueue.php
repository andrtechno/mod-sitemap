<?php

namespace panix\mod\sitemap\components;

use Yii;
use XMLWriter;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SitemapQueue extends BaseObject implements JobInterface
{

    public $i;
    public $renderedUrls = [];
    public $result;


    public function execute($queue)
    {

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');

        foreach (Yii::$app->sitemap->schemas as $attr => $schemaUrl) {
            $xml->writeAttribute($attr, $schemaUrl);
        }

        foreach ($this->renderedUrls as $data) {
            $xml->startElement('url');
            foreach ($data as $urlKey => $urlValue) {
                if (is_array($urlValue)) {
                    switch ($urlKey) {
                        case 'news':
                            $namespace = 'news:';
                            $xml->startElement($namespace . $urlKey);
                            Sitemap::hashToXML($urlValue, $xml, $namespace);
                            $xml->endElement();
                            break;
                        case 'images':
                            $namespace = 'image:';
                            foreach ($urlValue as $image) {
                                $xml->startElement($namespace . 'image');
                                Sitemap::hashToXML($image, $xml, $namespace);
                                $xml->endElement();
                            }
                            break;
                    }
                } else {
                    $xml->writeElement($urlKey, $urlValue);
                }
            }
            $xml->endElement();
        }


        $xml->endElement(); // urlset
        $xml->endElement(); // document
       // $this->result[$this->i]['xml'] = $xml->outputMemory();
        $file = Yii::getAlias('@uploads/' . trim($this->result[$this->i]['file'], '/'));
        file_put_contents($file, $xml->outputMemory(), FILE_APPEND);
        return true;
    }

}
