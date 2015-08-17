<?php

namespace janisto\ycm;

use Yii;
use janisto\timepicker\TimePicker;
use vova07\imperavi\Widget as RedactorWidget;
use vova07\select2\Widget as Select2Widget;
use yii\base\InvalidConfigException;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\NotFoundHttpException;

/**
 * Main module class for yii2-ycm.
 *
 * You can modify its configuration by adding an array to your application config under `modules`
 * as shown in the following example:
 *
 * 'modules' => [
 *     ...
 *     'ycm' => [
 *         'class' => 'janisto\ycm\Module',
 *         'admins' => ['admin'],
 *         'urlPrefix' => 'xxx',
 *         'registerModels' => [
 *             'test' => 'app\models\Test',
 *             'user' => [
 *                 'class' => 'app\models\User',
 *                 'attribute' => 'value',
 *             ],
 *         ],
 *     ],
 *     ...
 * ],
 *
 * @property array $models Registered models. This property is read-only.
 *
 * @author Jani Mikkonen <janisto@php.net>
 * @license public domain (http://unlicense.org)
 * @link https://github.com/janisto/yii2-ycm
 */
class Module extends \yii\base\Module
{
    /** @inheritdoc */
    public $controllerNamespace = 'janisto\ycm\controllers';

    /** @var array An array of administrator usernames. */
    public $admins = [];

    /** @var string Asset bundle. */
    public $assetBundle = 'janisto\ycm\YcmAsset';

    /** @var string URL prefix. */
    public $urlPrefix = 'admin';

    /** @var array The default URL rules to be used in module. */
    public $urlRules = [
        '' => 'default/index',
        'model/<action:\w+>/<name:\w+>/<pk:\d+>' => 'model/<action>',
        'model/<action:\w+>/<name:\w+>' => 'model/<action>',
        'model/<action:\w+>' => 'model/<action>',
        'download/<action:\w+>/<name:\w+>' => 'download/<action>',
    ];

    /** @var array Register models to module. */
    public $registerModels = [];

    /** @var array Register additional controllers to module. */
    public $registerControllers = [];

    /** @var array Register additional URL rules to module. */
    public $registerUrlRules = [];

    /** @var array Sidebar Nav items. */
    public $sidebarItems = [];

    /** @var array Models. */
    protected $models = [];

    /** @var array Model upload paths. */
    protected $modelPaths = [];

    /** @var array Model upload URLs. */
    protected $modelUrls = [];

    /** @var string Upload path.  */
    public $uploadPath;

    /** @var string Upload URL.  */
    public $uploadUrl;

    /** @var integer Upload permissions for folders. */
    public $uploadPermissions = 0775;

    /** @var boolean Whether to delete the temporary uploaded file after saving.  */
    public $uploadDeleteTempFile = true;

    /** @var boolean Whether to enable redactor image uploads.  */
    public $redactorImageUpload = true;

    /** @var array Redactor image upload validation rules. */
    public $redactorImageUploadOptions = [
        'maxWidth' => 1920,
        'maxHeight' => 1920,
        'maxSize' => 1048576, // 1024 * 1024 = 1MB
    ];

    /** @var boolean Whether to enable redactor file uploads.  */
    public $redactorFileUpload = true;

    /** @var array Redactor file upload validation rules. */
    public $redactorFileUploadOptions = [
        'maxSize' => 8388608, // 1024 * 1024 * 8 = 8MB
    ];

    /** @var integer Number of columns to show in model/list view by default. */
    public $maxColumns = 8;

    protected $attributeWidgets;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setAliases([
            '@ycm' => __DIR__
        ]);

        $this->setViewPath('@ycm/views');

        if ($this->uploadPath === null) {
            $this->uploadPath = Yii::getAlias('@uploadPath');
            if (!is_writable($this->uploadPath)) {
                throw new InvalidConfigException('Make sure "uploads" folder is writable.');
            }
        }

        if ($this->uploadUrl === null) {
            $this->uploadUrl = Yii::getAlias('@uploadUrl');
        }

        foreach ($this->registerModels as $name => $class) {
            if (is_array($class) && isset($class['folderName'])) {
                $folder = strtolower($class['folderName']);
                unset($class['folderName']);
            } else {
                $folder = strtolower($name);
            }
            $model = Yii::createObject($class);
            if (is_subclass_of($model, 'yii\db\ActiveRecord')) {
                $this->models[$name] = $model;
                $this->modelPaths[$name] = $this->uploadPath . DIRECTORY_SEPARATOR . $folder;
                $this->modelUrls[$name] = $this->uploadUrl . '/' . $folder;
            }
        }

        foreach ($this->registerControllers as $name => $class) {
            $this->controllerMap[$name] = $class;
        }
    }

    /**
     * Get models.
     *
     * @return array Models
     */
    public function getModels ()
    {
        return $this->models;
    }

    /**
     * Get Model name from models array.
     *
     * @param \yii\db\ActiveRecord $model Model object
     * @return string Model name
     * @throws NotFoundHttpException
     */
    public function getModelName($model)
    {
        foreach ($this->models as $name => $class) {
            if (get_class($class) === $model->className()) {
                return $name;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Load model.
     *
     * @param string $name Model name
     * @param null|int $pk Primary key
     * @return \yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function loadModel($name, $pk = null)
    {
        $name = (string) $name;
        if (!ArrayHelper::keyExists($name, $this->models)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        /** @var $model \yii\db\ActiveRecord */
        $model = $this->models[$name];

        if ($pk !== null) {
            if (($model = $model->findOne((int) $pk)) !== null) {
                /** @var $model \yii\db\ActiveRecord */
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }

        return $model;
    }

    /**
     * Create ActiveForm widget.
     *
     * @param \yii\widgets\ActiveForm $form
     * @param \yii\db\ActiveRecord $model Model
     * @param string $attribute Model attribute
     */
    public function createWidget($form, $model, $attribute)
    {
        $widget = $this->getAttributeWidget($model, $attribute);
        $tableSchema = $model->getTableSchema();

        switch ($widget) {
            case 'widget':
                echo $this->createField($form, $model, $attribute, [], 'widget');
                break;

            case 'wysiwyg':
                $options = [
                    'widgetClass' => RedactorWidget::className(),
                    'settings' => [
                        'minHeight' => 200,
                        'plugins' => [
                            'video',
                            'fullscreen',
                        ],
                    ]
                ];
                if ($this->redactorImageUpload === true) {
                    $imageOptions =  [
                        'settings' => [
                            'imageManagerJson' => Url::to(['model/redactor-list', 'name' => $this->getModelName($model), 'attr' => $attribute]),
                            'imageUpload' => Url::to(['model/redactor-upload', 'name' => $this->getModelName($model), 'attr' => $attribute]),
                            'imageUploadErrorCallback' => new JsExpression('function(json) { alert(json.error); }'),
                            'plugins' => [
                                'imagemanager',
                            ],
                        ]
                    ];
                    $options = ArrayHelper::merge($options, $imageOptions);
                }
                if ($this->redactorFileUpload === true) {
                    $fileOptions =  [
                        'settings' => [
                            'fileManagerJson' => Url::to(['model/redactor-list', 'name' => $this->getModelName($model), 'attr' => $attribute, 'type' => 'file']),
                            'fileUpload' => Url::to(['model/redactor-upload', 'name' => $this->getModelName($model), 'attr' => $attribute, 'type' => 'file']),
                            'fileUploadErrorCallback' => new JsExpression('function(json) { alert(json.error); }'),
                            'plugins' => [
                                'filemanager',
                            ],
                        ]
                    ];
                    $options = ArrayHelper::merge($options, $fileOptions);
                }
                echo $this->createField($form, $model, $attribute, $options, 'widget');
                break;

            case 'date':
                $options = [
                    'widgetClass' => TimePicker::className(),
                    'mode' => 'date',
                    'clientOptions'=>[
                        'dateFormat' => 'yy-mm-dd',
                    ],
                ];
                echo $this->createField($form, $model, $attribute, $options, 'widget');
                break;

            case 'time':
                $options = [
                    'widgetClass' => TimePicker::className(),
                    'mode' => 'time',
                    'clientOptions'=>[
                        'timeFormat' => 'HH:mm:ss',
                        'showSecond' => true,
                    ],
                ];
                echo $this->createField($form, $model, $attribute, $options, 'widget');
                break;

            case 'datetime':
                $options = [
                    'widgetClass' => TimePicker::className(),
                    'mode' => 'datetime',
                    'clientOptions'=>[
                        'dateFormat' => 'yy-mm-dd',
                        'timeFormat' => 'HH:mm:ss',
                        'showSecond' => true,
                    ],
                ];
                echo $this->createField($form, $model, $attribute, $options, 'widget');
                break;

            case 'select':
                $options = [
                    'options' => [
                        'placeholder' => Yii::t('ycm', 'Choose {name}', ['name' => $model->getAttributeLabel($attribute)]),
                    ],
                    'settings' => [
                        'allowClear' => true,
                        'width' => '100%',
                    ],
                    'items' => [
                        '' => '', // Add empty item for placeholder
                    ],
                ];
                echo $this->createField($form, $model, $attribute, $options, 'select');
                break;

            case 'selectMultiple':
                $options = [
                    'options' => [
                        'multiple' => true,
                        'placeholder' => Yii::t('ycm', 'Choose {name}', ['name' => $model->getAttributeLabel($attribute)]),
                    ],
                    'settings' => [
                        'width' => '100%',
                    ],
                ];
                echo $this->createField($form, $model, $attribute, $options, 'select');
                break;

            case 'image':
                $options = [];
                if (!$model->isNewRecord && !empty($model->$attribute)) {
                    $className = StringHelper::basename($model->className());
                    $inputName = $className . '[' . $attribute . '_delete]';
                    $inputId = strtolower($className . '-' . $attribute . '_delete');
                    $url = $this->getAttributeUrl($this->getModelName($model), $attribute, $model->$attribute);
                    ob_start();
                    echo '<div class="checkbox"><label for="'. $inputId .'">
                        <input type="checkbox" name="' . $inputName . '" id="' . $inputId . '" value="delete"> ' . Yii::t('ycm', 'Delete image') . '
                    </label></div>';
                    Modal::begin([
                        'size' => Modal::SIZE_LARGE,
                        'header' => '<h4>' . Yii::t('ycm', 'Preview image') .'</h4>',
                        'toggleButton' => ['label' => Yii::t('ycm', 'Preview image'), 'class' => 'btn btn-info btn-sm'],
                    ]);
                    echo Html::img($url, ['class'=>'modal-image']);
                    Modal::end();
                    $html = ob_get_clean();
                    $options['hint'] = $html;
                }
                echo $this->createField($form, $model, $attribute, $options, 'fileInput');
                break;

            case 'file':
                $options = [];
                if (!$model->isNewRecord && !empty($model->$attribute)) {
                    $className = StringHelper::basename($model->className());
                    $inputName = $className . '[' . $attribute . '_delete]';
                    $inputId = strtolower($className . '-' . $attribute . '_delete');
                    $url = $this->getAttributeUrl($this->getModelName($model), $attribute, $model->$attribute);
                    $html = '<div class="checkbox"><label for="'. $inputId .'">
                        <input type="checkbox" name="' . $inputName . '" id="' . $inputId . '" value="delete"> ' . Yii::t('ycm', 'Delete file') . '
                    </label></div>';
                    $html .= Html::a(Yii::t('ycm', 'Download file'), $url, ['class'=>'btn btn-info btn-sm']);
                    $options['hint'] = $html;
                }
                echo $this->createField($form, $model, $attribute, $options, 'fileInput');
                break;

            case 'text':
                $options = [
                    'maxlength' => $tableSchema->columns[$attribute]->size,
                ];
                echo $this->createField($form, $model, $attribute, $options, 'textInput');
                break;

            case 'hidden':
                $options = [
                    'maxlength' => $tableSchema->columns[$attribute]->size,
                ];
                $options = $this->getAttributeOptions($attribute, $options);
                echo Html::activeHiddenInput($model, $attribute, $options);
                break;

            case 'password':
                $options = [
                    'maxlength' => $tableSchema->columns[$attribute]->size,
                ];
                echo $this->createField($form, $model, $attribute, $options, 'passwordInput');
                break;

            case 'textarea':
                $options = [
                    'rows' => 6,
                ];
                echo $this->createField($form, $model, $attribute, $options, 'textarea');
                break;

            case 'radio':
                echo $this->createField($form, $model, $attribute, [], 'radio');
                break;

            case 'boolean':
            case 'checkbox':
                echo $this->createField($form, $model, $attribute, [], 'checkbox');
                break;

            case 'dropdown':
                $options = [
                    'prompt' => Yii::t('ycm', 'Choose {name}', ['name' => $model->getAttributeLabel($attribute)]),
                ];
                echo $this->createField($form, $model, $attribute, $options, 'dropDownList');
                break;

            case 'listbox':
                $options = [
                    'prompt' => '',
                ];
                echo $this->createField($form, $model, $attribute, $options, 'listBox');
                break;

            case 'checkboxList':
                echo $this->createField($form, $model, $attribute, [], 'checkboxList');
                break;

            case 'radioList':
                echo $this->createField($form, $model, $attribute, [], 'radioList');
                break;

            case 'disabled':
                $options = [
                    'maxlength' => $tableSchema->columns[$attribute]->size,
                    'readonly' => true,
                ];
                echo $this->createField($form, $model, $attribute, $options, 'textInput');
                break;

            case 'hide':
                break;

            default:
                $options = $this->getAttributeOptions($attribute);
                echo $form->field($model, $attribute)->$widget($options);
                break;
        }
    }

    /**
     * Create ActiveField object.
     *
     * @param \yii\widgets\ActiveForm $form
     * @param \yii\db\ActiveRecord $model Model
     * @param string $attribute Model attribute
     * @param array $options Attribute options
     * @param string $type ActiveField type
     * @return \yii\widgets\ActiveField ActiveField object
     * @throws InvalidConfigException
     */
    protected function createField($form, $model, $attribute, $options, $type = 'textInput')
    {
        $options = $this->getAttributeOptions($attribute, $options);
        $field = $form->field($model, $attribute);
        if (isset($options['hint'])) {
            $hintOptions = [];
            if (isset($options['hintOptions'])) {
                $hintOptions = $options['hintOptions'];
                unset($options['hintOptions']);
            }
            $field->hint($options['hint'], $hintOptions);
            unset($options['hint']);
        }
        if (isset($options['label'])) {
            $labelOptions = [];
            if (isset($options['labelOptions'])) {
                $labelOptions = $options['labelOptions'];
                unset($options['labelOptions']);
            }
            $field->label($options['label'], $labelOptions);
            unset($options['label']);
        }
        if (isset($options['input'])) {
            $input = $options['input'];
            unset($options['input']);
            $field = $field->input($input, $options);
        } else {
            if ($type == 'dropDownList' || $type == 'listBox' || $type == 'checkboxList' || $type == 'radioList') {
                $items = $this->getAttributeChoices($model, $attribute);
                $field->$type($items, $options);
            } elseif ($type == 'select') {
                if (isset($options['items'])) {
                    $options['items'] = $options['items'] + $this->getAttributeChoices($model, $attribute);;
                } else {
                    $options['items'] = $this->getAttributeChoices($model, $attribute);
                }
                $field->widget(Select2Widget::className(), $options);
            } elseif ($type == 'widget') {
                if (isset($options['widgetClass'])) {
                    $class = $options['widgetClass'];
                    unset($options['widgetClass']);
                } else {
                    throw new InvalidConfigException('Widget class missing from configuration.');
                }
                $field->widget($class, $options);
            } else {
                $field->$type($options);
            }
        }
        return $field;
    }

    /**
     * Get attribute file path.
     *
     * @param string $name Model name
     * @param string $attribute Model attribute
     * @return string Model attribute file path
     * @throws NotFoundHttpException
     */
    public function getAttributePath($name, $attribute)
    {
        if (!isset($this->modelPaths[$name])) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->modelPaths[$name] . DIRECTORY_SEPARATOR . strtolower($attribute);
    }

    /**
     * Get attribute file URL.
     *
     * @param string $name Model name
     * @param string $attribute Model attribute
     * @param string $file Filename
     * @return string Model attribute file URL
     * @throws NotFoundHttpException
     */
    public function getAttributeUrl($name, $attribute, $file)
    {
        if (!isset($this->modelUrls[$name])) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->modelUrls[$name] . '/' . strtolower($attribute) . '/' . $file;
    }

    /**
     * Get attributes widget.
     *
     * @param \yii\db\ActiveRecord $model Model
     * @param string $attribute Model attribute
     * @return null|string|object
     */
    public function getAttributeWidget($model, $attribute)
    {
        if ($this->attributeWidgets !== null) {
            if (isset($this->attributeWidgets->$attribute)) {
                return $this->attributeWidgets->$attribute;
            } else {
                $tableSchema = $model->getTableSchema();
                $column = $tableSchema->columns[$attribute];
                if ($column->phpType === 'boolean') {
                    return 'checkbox';
                } elseif ($column->type === 'text') {
                    return 'textarea';
                } elseif (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                    return 'password';
                } else {
                    return 'text';
                }
            }
        }

        $attributeWidgets = [];
        if (method_exists($model, 'attributeWidgets')) {
            $attributeWidgets = $model->attributeWidgets();
        }

        $data = [];
        if (!empty($attributeWidgets)) {
            foreach ($attributeWidgets as $item) {
                if (isset($item[0]) && isset($item[1])) {
                    $data[$item[0]] = $item[1];
                    $data[$item[0].'Options'] = $item;
                }
            }
        }

        $this->attributeWidgets = (object) $data;

        return $this->getAttributeWidget($model, $attribute);
    }

    /**
     * Get an array of attribute choice values.
     * The variable or method name needs ​​to be: attributeChoices.
     *
     * @param \yii\db\ActiveRecord $model Model
     * @param string $attribute Model attribute
     * @return array
     */
    protected function getAttributeChoices($model, $attribute)
    {
        $data = [];
        $choicesName = (string) $attribute . 'Choices';
        if (method_exists($model, $choicesName) && is_array($model->$choicesName())) {
            $data = $model->$choicesName();
        } elseif (isset($model->$choicesName) && is_array($model->$choicesName)) {
            $data = $model->$choicesName;
        }
        return $data;
    }

    /**
     * Get attribute options.
     *
     * @param string $attribute Model attribute
     * @param array $options Model attribute form options
     * @return array
     */
    protected function getAttributeOptions($attribute, $options = [])
    {
        $optionsName = (string) $attribute . 'Options';
        if (isset($this->attributeWidgets->$optionsName)) {
            $attributeOptions = array_slice($this->attributeWidgets->$optionsName, 2);
            if (empty($options)) {
                return $attributeOptions;
            } else {
                if (empty($attributeOptions)) {
                    return $options;
                } else {
                    return ArrayHelper::merge($options, $attributeOptions);
                }
            }
        } else {
            if (empty($options)) {
                return [];
            } else {
                return $options;
            }
        }
    }

    /**
     * Get model's administrative name.
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return string
     */
    public function getAdminName($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (!isset($model->adminNames)) {
            return Inflector::pluralize(Inflector::camel2words(StringHelper::basename($model->className())));
        } else {
            return $model->adminNames[0];
        }
    }

    /**
     * Get model's singular name.
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return string
     */
    public function getSingularName($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (!isset($model->adminNames)) {
            return Inflector::singularize(Inflector::camel2words(StringHelper::basename($model->className())));
        } else {
            return $model->adminNames[1];
        }
    }

    /**
     * Get model's plural name.
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return string
     */
    public function getPluralName($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (!isset($model->adminNames)) {
            return Inflector::pluralize(Inflector::camel2words(StringHelper::basename($model->className())));
        } else {
            return $model->adminNames[2];
        }
    }

    /**
     * Hide create model action?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getHideCreate($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->hideCreateAction)) {
            return (bool) $model->hideCreateAction;
        } else {
            return false;
        }
    }

    /**
     * Hide update model action?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getHideUpdate($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->hideUpdateAction)) {
            return (bool) $model->hideUpdateAction;
        } else {
            return false;
        }
    }

    /**
     * Hide delete model action?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getHideDelete($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->hideDeleteAction)) {
            return (bool) $model->hideDeleteAction;
        } else {
            return false;
        }
    }

    /**
     * Download CSV?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getDownloadCsv($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->downloadCsv)) {
            return $model->downloadCsv;
        } else {
            return false;
        }
    }

    /**
     * Download MS CSV?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getDownloadMsCsv($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->downloadMsCsv)) {
            return $model->downloadMsCsv;
        } else {
            return false;
        }
    }

    /**
     * Download Excel?
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return bool
     */
    public function getDownloadExcel($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->downloadExcel)) {
            return $model->downloadExcel;
        } else {
            return false;
        }
    }

    /**
     * Get excluded download fields.
     *
     * @param string|\yii\db\ActiveRecord $model
     * @return array
     */
    public function getExcludeDownloadFields($model)
    {
        if (is_string($model)) {
            $model = $this->loadModel($model);
        }
        if (isset($model->excludeDownloadFields)) {
            return $model->excludeDownloadFields;
        } else {
            return [];
        }
    }
}
