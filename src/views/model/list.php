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
$this->params['breadcrumbs'][] = ['label' => Yii::t('ycm', 'Content'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ycm-model-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Alert::widget() ?>

    <p>
        <?php
        if ($module->getHideCreate($model) === false) {
            echo Html::a(Yii::t('ycm', 'Create {name}', ['name' => $module->getSingularName($name)]), ['create', 'name' => $name], ['class' => 'btn btn-success']);
        }

        if ($module->getEnableBulk($model)) {
            foreach ($module->getBulkActions($model) as $bulkActionName => $bulkActionTitle) {
                echo Html::a(
                    Yii::t('app', $bulkActionTitle),
                    ['list', 'name' => $name],
                    [
                        'class' => 'btn btn-success',
                        'confirm' => 'Are you sure?',
                        'data' => [
                            'method' => 'post',
                        ],
                        'onClick' =>
                            '$(this).data("params", {"bulkAction":"' . $bulkActionName . '", "pks":$("#w1").yiiGridView("getSelectedRows")});'
                    ]
                );
            }
        }
        ?>
    </p>

    <?= GridView::widget($config); ?>

</div>
