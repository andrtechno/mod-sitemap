<?php

namespace panix\mod\sitemap\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Behavior for XML Sitemap Yii2 module.
 *
 * For example:
 *
 * ```php
 * public function behaviors()
 * {
 *  return [
 *       'sitemap' => [
 *           'class' => SitemapBehavior::className(),
 *           'scope' => function ($model) {
 *               $model->select(['url', 'updated_at']);
 *               $model->andWhere(['is_deleted' => 0]);
 *           },
 *           'dataClosure' => function ($model) {
 *              return [
 *                  'loc' => yii\helpers\Url::to($model->url, true),
 *                  'lastmod' => Sitemap::dateToW3C($model->updated_at),
 *                  'changefreq' => Sitemap::DAILY,
 *                  'priority' => 0.8
 *              ];
 *          }
 *       ],
 *  ];
 * }
 * ```
 *
 * @see http://www.sitemaps.org/protocol.html
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @package app\modules\sitemap
 */
class SitemapBehavior extends Behavior
{
    const CHANGEFREQ_ALWAYS = 'always';
    const CHANGEFREQ_HOURLY = 'hourly';
    const CHANGEFREQ_DAILY = 'daily';
    const CHANGEFREQ_WEEKLY = 'weekly';
    const CHANGEFREQ_MONTHLY = 'monthly';
    const CHANGEFREQ_YEARLY = 'yearly';
    const CHANGEFREQ_NEVER = 'never';
    const BATCH_MAX_SIZE = 100;
    /** @var callable */
    public $dataClosure;
    /** @var string|bool */
    public $defaultChangefreq = false;
    /** @var float|bool */
    public $defaultPriority = false;
    /** @var callable */
    public $scope;
    /** @var string */
    public $groupName;

    public function init()
    {
        if (!is_callable($this->dataClosure) && !is_array($this->dataClosure)) {
            throw new InvalidConfigException('SitemapBehavior::$dataClosure isn\'t callable or array.');
        }
    }

    public function generateSiteMap()
    {
        $result = [];
        $n = 0;
        /** @var \yii\db\ActiveRecord $owner */
        $owner = $this->owner;
        $query = $owner::find();
        if (is_array($this->scope)) {
            if (is_callable($this->owner->{$this->scope[1]}())) {
                call_user_func($this->owner->{$this->scope[1]}(), $query);
            }
        } else {
            if (is_callable($this->scope)) {
                call_user_func($this->scope, $query);
            }
        }
        foreach ($query->each(self::BATCH_MAX_SIZE) as $model) {
            if (is_array($this->dataClosure)) {
                $urlData = call_user_func($this->owner->{$this->dataClosure[1]}(), $model);
            } else {
                $urlData = call_user_func($this->dataClosure, $model);
            }
            if (empty($urlData)) {
                continue;
            }
            $result[$n]['loc'] = Yii::$app->urlManager->createAbsoluteUrl($urlData['loc']);
            if (!empty($urlData['lastmod'])) {
                $result[$n]['lastmod'] = $urlData['lastmod'];
            }
            if (isset($urlData['changefreq'])) {
                $result[$n]['changefreq'] = $urlData['changefreq'];
            } elseif ($this->defaultChangefreq !== false) {
                $result[$n]['changefreq'] = $this->defaultChangefreq;
            }
            if (isset($urlData['name'])) {
                $result[$n]['name'] = $urlData['name'];
            }
            if (isset($urlData['priority'])) {
                $result[$n]['priority'] = $urlData['priority'];
            } elseif ($this->defaultPriority !== false) {
                $result[$n]['priority'] = $this->defaultPriority;
            }
            if (isset($urlData['news'])) {
                $result[$n]['news'] = $urlData['news'];
            }
            if (isset($urlData['images'])) {
                $result[$n]['images'] = $urlData['images'];
            }

            if (isset($urlData['xhtml:link'])) {
                $result[$n]['xhtml:link'] = $urlData['xhtml:link'];
            }
            ++$n;
        }
        return $result;
    }


    public function generateSiteMapHtml()
    {
        $result = [];
        $n = 0;
        /** @var \yii\db\ActiveRecord $owner */
        $owner = $this->owner;
        $query = $owner::find();
        if (is_array($this->scope)) {
            if (is_callable($this->owner->{$this->scope[1]}())) {
                call_user_func($this->owner->{$this->scope[1]}(), $query);
            }
        } else {
            if (is_callable($this->scope)) {
                call_user_func($this->scope, $query);
            }
        }

        //  $className2=get_class($owner);
        $className = $this->groupName;

        foreach ($query->each(self::BATCH_MAX_SIZE) as $model) {
            if (is_array($this->dataClosure)) {
                $urlData = call_user_func($this->owner->{$this->dataClosure[1]}(), $model);
            } else {
                $urlData = call_user_func($this->dataClosure, $model);
            }
            if (empty($urlData)) {
                continue;
            }

            $result[$className][$n]['loc'] = Yii::$app->urlManager->createAbsoluteUrl($urlData['loc']);
            if (!empty($urlData['lastmod'])) {
                $result[$className][$n]['lastmod'] = $urlData['lastmod'];
            }
            if (isset($urlData['changefreq'])) {
                $result[$className][$n]['changefreq'] = $urlData['changefreq'];
            } elseif ($this->defaultChangefreq !== false) {
                $result[$className][$n]['changefreq'] = $this->defaultChangefreq;
            }
            if (isset($urlData['name'])) {
                $result[$className][$n]['name'] = $urlData['name'];
            }
            if (isset($urlData['priority'])) {
                $result[$className][$n]['priority'] = $urlData['priority'];
            } elseif ($this->defaultPriority !== false) {
                $result[$className][$n]['priority'] = $this->defaultPriority;
            }
            if (isset($urlData['news'])) {
                $result[$className][$n]['news'] = $urlData['news'];
            }
            if (isset($urlData['images'])) {
                $result[$className][$n]['images'] = $urlData['images'];
            }

            if (isset($urlData['xhtml:link'])) {
                $result[$className][$n]['xhtml:link'] = $urlData['xhtml:link'];
            }
            ++$n;
        }
        return $result;
    }
}
