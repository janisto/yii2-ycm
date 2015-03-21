<?php

namespace janisto\ycm\controllers;

abstract class Controller extends \yii\web\Controller
{
    public $layout = 'main';

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->view->params['breadcrumbs'][] = [
                'label' => 'Administration',
                'url' => ['default/index'],
            ];
            return true;
        }
        return false;
    }
}
