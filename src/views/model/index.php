<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

$this->title = Yii::t('ycm', 'Content');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ycm-model-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php foreach ($module->models as $name => $class): ?>
        <h3><?= $module->getAdminName($name) ?></h3>

        <?= Html::a(Yii::t('ycm', 'List {name}', ['name' => $module->getPluralName($name)]), ['list', 'name' => $name], ['class' => 'btn btn-primary']) ?>

        <?php
        if ($module->getHideCreate($name) === false) {
            echo Html::a(Yii::t('ycm', 'Create {name}', ['name' => $module->getSingularName($name)]), ['create', 'name' => $name], ['class' => 'btn btn-success']);
        }
        ?>

    <?php endforeach; ?>

</div>
