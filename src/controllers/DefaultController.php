<?php

namespace janisto\ycm\controllers;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
