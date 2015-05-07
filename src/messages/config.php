<?php

/**
 * Make sure your language is listed in languages. If not, add your
 * language there (remember to keep the list in alphabetical order).
 *
 * Usage: vendor/bin/yii message/extract src/messages/config.php
 *
 * @link https://github.com/yiisoft/yii2/blob/master/docs/internals/translation-workflow.md
 */

return [
    'sourcePath' => __DIR__ . '/..',
    'messagePath' => __DIR__,
    'languages' => ['fi'],
    'translator' => 'Yii::t',
    'sort' => false,
    'overwrite' => true,
    'removeUnused' => false,
    'except' => [
        '.svn',
        '.git',
        '.gitattributes',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
    ],
    'only' => ['*.php'],
    'format' => 'php',
];
