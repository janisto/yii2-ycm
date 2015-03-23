<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

?>
<div class="ycm-default-index">
    <h1>Administration</h1>
    <?php
    /*
    <p>
        <?= Html::a('Models', ['model/index'], ['class' => 'btn btn-primary']) ?>
    </p>
    */ ?>

    <h2>Models</h2>

    <?php foreach ($module->models as $name => $class): ?>
        <h3><?= $module->getAdminName($name) ?></h3>

        <?= Html::a('List ' . $module->getPluralName($name), ['list', 'name' => $name], ['class' => 'btn btn-primary']) ?>

        <?= Html::a('Create ' . $module->getSingularName($name), ['create', 'name' => $name], ['class' => 'btn btn-success']) ?>
    <?php endforeach; ?>

    <!--
    <p>
        <?php echo Yii::t('ycm', 'Test'); ?>
    </p>
    -->
</div>
