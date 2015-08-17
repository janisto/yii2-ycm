<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

$assetBundle = $module->assetBundle;
$assetBundle::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
NavBar::begin([
    'brandLabel' => Yii::t('ycm', 'Administration'),
    'brandUrl' => ['default/index'],
    'innerContainerOptions' => ['class' => 'container-fluid'],
    'options' => [
        'class' => 'navbar navbar-inverse navbar-fixed-top',
    ],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        Yii::$app->user->isGuest ?
            ['label' => Yii::t('ycm', 'Login'), 'url' => ['/site/login']] :
            ['label' => Yii::t('ycm', 'Logout ({username})', ['username' => Yii::$app->user->identity->username]),
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post']],
    ],
]);
NavBar::end();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <?php
            $sidebarItems = ArrayHelper::merge([
                ['label' => Yii::t('ycm', 'Content'), 'url' => ['model/index']],
            ], $module->sidebarItems);

            echo Nav::widget([
                'options' => ['class' => 'nav nav-sidebar'],
                'activateParents' => true,
                'items' => $sidebarItems,
            ]);
            ?>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>

            <?= $content ?>

        </div>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
