<?php

use yii\helpers\Html;
use yii\grid\GridView;
use janisto\ycm\widgets\Alert;

/* @var $this \yii\web\View */
/* @var $config array */
/* @var $model \yii\db\ActiveRecord */
/* @var $name string */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

$this->title = $module->getAdminName($model);
$this->params['breadcrumbs'][] = ['label' => 'Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ycm-model-list">

    <h1><?= Html::encode($module->getAdminName($model)) ?></h1>

    <?= Alert::widget() ?>

    <p>
        <?php
        if ($module->getHideCreate($model) === false) {
            echo Html::a('Create ' . $module->getSingularName($model), ['create', 'name' => $name], ['class' => 'btn btn-success']);
        }
        ?>
    </p>

    <?= GridView::widget($config); ?>

</div>
