<?php

namespace janisto\ycm\widgets;

use Yii;
use yii\bootstrap\Widget;
use yii\web\Application;

class Alert extends Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * @var array the options for rendering the close button tag.
     */
    public $closeButton = [];

    /**
     * @var integer the delay in microseconds after which the alert will be removed.
     */
    public $delay = 5000;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof Application) {
            $view = $this->getView();
            $session = Yii::$app->getSession();
            $flashes = $session->getAllFlashes();
            $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

            foreach ($flashes as $type => $data) {
                if (isset($this->alertTypes[$type])) {
                    $data = (array) $data;
                    foreach ($data as $i => $message) {
                        /* initialize css class for each alert box */
                        $this->options['class'] = $this->alertTypes[$type] . $appendCss;

                        /* assign unique id to each alert box */
                        $this->options['id'] = $this->getId() . '-' . $type . '-' . $i;

                        echo \yii\bootstrap\Alert::widget([
                            'body' => $message,
                            'closeButton' => $this->closeButton,
                            'options' => $this->options,
                        ]);

                        if ($this->delay > 0) {
                            $js = 'jQuery("#' . $this->options['id'] . '").fadeTo(' . $this->delay . ', 0.00, function() {
                                $(this).slideUp("slow", function() {
                                    $(this).remove();
                                });
                            });';
                            $view->registerJs($js);
                        }
                    }
                    $session->removeFlash($type);
                }
            }
        }
    }
}
