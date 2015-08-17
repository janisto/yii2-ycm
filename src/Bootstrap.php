<?php

namespace janisto\ycm;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use yii\web\GroupUrlRule;

class Bootstrap implements BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app)
    {
        /** @var $module Module */
        if ($app->hasModule('ycm') && ($module = $app->getModule('ycm')) instanceof Module) {

            if ($app instanceof ConsoleApplication) {
                $module->controllerNamespace = 'janisto\ycm\commands';
            } else {
                $rules = ArrayHelper::merge($module->urlRules, $module->registerUrlRules);
                $configUrlRule = [
                    'prefix' => $module->urlPrefix,
                    'rules' => $rules,
                ];

                if ($module->urlPrefix != 'ycm') {
                    $configUrlRule['routePrefix'] = 'ycm';
                }

                $app->urlManager->addRules([new GroupUrlRule($configUrlRule)], false);
            }

            if (!isset($app->i18n->translations['ycm'])) {
                $app->i18n->translations['ycm'] = [
                    'class' => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                ];
            }
        }
    }
}
