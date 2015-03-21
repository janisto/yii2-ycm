<?php

namespace janisto\ycm;

class YcmAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/janisto/yii2-ycm/src/assets';
    public $css = [
        'css/ycm.css',
    ];
    public $js = [
        //'js/ycm.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
