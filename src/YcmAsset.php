<?php

namespace janisto\ycm;

class YcmAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@ycm/assets';
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
