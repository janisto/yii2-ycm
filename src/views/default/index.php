<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */

/** @var $module \janisto\ycm\Module */
$module = Yii::$app->controller->module;

?>
<div class="ycm-default-index">
    <h1>Administration</h1>
    <p>
        <?= Html::a('Models', ['model/index'], ['class' => 'btn btn-primary']) ?>
    </p>
    <p>
        <?php echo Yii::t('ycm', 'Test'); ?>
    </p>
</div>
