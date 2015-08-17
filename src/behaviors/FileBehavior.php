<?php

namespace janisto\ycm\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

class FileBehavior extends Behavior
{
    /** @var string folder name */
    public $folderName;

    /** @var string upload path  */
    public $uploadPath;

    /** @var string upload URL  */
    public $uploadUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->folderName === null) {
            throw new InvalidConfigException('You must define "folderName".');
        }

        if ($this->uploadPath === null) {
            $this->uploadPath = Yii::getAlias('@uploadPath');
        }

        if ($this->uploadUrl === null) {
            $this->uploadUrl = Yii::getAlias('@uploadUrl');
        }
    }

    /**
     * Get file path.
     *
     * @param string $attribute Model attribute
     * @return string|false Model attribute file path
     */
    public function getFilePath($attribute)
    {
        /** @var $model \yii\db\ActiveRecord */
        $model = $this->owner;

        if ($model->hasAttribute($attribute) && !empty($model->$attribute)) {
            $file = $model->$attribute;
            $path = $this->uploadPath . DIRECTORY_SEPARATOR . strtolower($this->folderName) . DIRECTORY_SEPARATOR . strtolower($attribute);
            return $path . DIRECTORY_SEPARATOR . $file;
        }
        return false;
    }

    /**
     * Get relative file URL.
     *
     * @param string $attribute Model attribute
     * @return string|false Model attribute relative file URL
     */
    public function getFileUrl($attribute)
    {
        /** @var $model \yii\db\ActiveRecord */
        $model = $this->owner;

        if ($model->hasAttribute($attribute) && !empty($model->$attribute)) {
            $file = $model->$attribute;
            $path = $this->uploadUrl . '/' . strtolower($this->folderName) . '/' . strtolower($attribute);
            return $path . '/' . $file;
        }
        return false;
    }

    /**
     * Get absolute file URL.
     *
     * @param string $attribute Model attribute
     * @return string|false Model attribute absolute file URL
     */
    public function getAbsoluteFileUrl($attribute)
    {
        $url = $this->getFileUrl($attribute);
        if ($url !== false) {
            if (strpos($url, '//') === false) {
                return Yii::$app->getRequest()->getHostInfo() . $url;
            } else {
                return $url;
            }
        }
        return false;
    }
}
