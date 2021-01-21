<?php
use yii\helpers\Html;
use panix\mod\shop\models\Category;

//\panix\engine\CMS::dump($data);


$s =Category::find()->dataTree();
//\panix\engine\CMS::dump($s);
?>

<h1><?= $this->context->pageName; ?></h1>
<div class="row">
    <?php foreach ($data as $k => $group) { ?>
        <div class="col-sm-6">
            <h2><?= $k; ?></h2>
            <ul>
                <?php foreach ($group as $item) { ?>
                    <li><?php echo Html::a($item['name'], $item['loc']); ?></li>
                <?php } ?>
                <?php if($k == 'Страницы'){?>
                    <li><?php echo Html::a(Yii::t('default', 'BLOG'), ['/news/default/index']); ?></li>
                <?php } ?>
            </ul>
            <ul>



            </ul>
        </div>
    <?php } ?>
</div>
