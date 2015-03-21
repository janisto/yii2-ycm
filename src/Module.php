<?php

namespace janisto\ycm;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'janisto\ycm\controllers';
    /**
     * Asset bundle
     *
     * @var string
     */
    public $assetBundle = 'janisto\ycm\YcmAsset';
    /**
     * URL prefix
     *
     * @var string
     */
    public $urlPrefix = 'admin';
    /*
     * URL rules
     *
     * @var array The rules to be used in URL management.
     */
    public $urlRules = [
        '' => 'default/index',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setViewPath(dirname(__FILE__) . '/views');
    }
}
