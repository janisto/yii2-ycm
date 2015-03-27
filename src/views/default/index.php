<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

?>
<div class="ycm-default-index">
    <h1>Models</h1>

    <?php foreach ($module->models as $name => $class): ?>
        <h3><?= $module->getAdminName($name) ?></h3>

        <?= Html::a('List ' . $module->getPluralName($name), ['model/list', 'name' => $name], ['class' => 'btn btn-primary']) ?>

        <?php
        if ($module->getHideCreate($name) === false) {
            echo Html::a('Create ' . $module->getSingularName($name), ['create', 'name' => $name], ['class' => 'btn btn-success']);
        }
        ?>

    <?php endforeach; ?>

</div>
