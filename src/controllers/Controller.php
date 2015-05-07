<?php

namespace janisto\ycm\controllers;

use Yii;

abstract class Controller extends \yii\web\Controller
{
    /** @inheritdoc */
    public $layout = 'main';

    /** @inheritdoc */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->view->params['breadcrumbs'][] = [
                'label' => Yii::t('ycm', 'Administration'),
                'url' => ['default/index'],
            ];
            return true;
        }
        return false;
    }
}
