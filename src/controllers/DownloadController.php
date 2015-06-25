<?php

namespace janisto\ycm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DownloadController extends Controller
{
    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['csv', 'mscsv', 'excel'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return in_array(Yii::$app->user->identity->username, $this->module->admins);
                        }
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'csv' => ['get'],
                    'mscsv' => ['get'],
                    'excel' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Download CSV.
     *
     * @param string $name Model name
     */
    public function actionCsv($name)
    {
        /** @var $module \janisto\ycm\Module */
        $module = $this->module;
        /** @var $model \yii\db\ActiveRecord */
        $model = $module->loadModel($name);
        $exclude = $module->getExcludeDownloadFields($name);

        $memoryLimit = 5*1024*1024;  // 5M
        $delimiter = ";";
        $enclosure = '"';
        $header = [];
        $select = '';

        foreach ($model->tableSchema->columns as $column) {
            // skip excluded fields
            if (in_array($column->name, $exclude)) {
                continue;
            }

            // no new lines in CSV format.
            $header[] = str_replace(["\r", "\r\n", "\n"], '', trim($model->getAttributeLabel($column->name)));
            if ($select !== '') {
                $select .= ', ';
            }
            $select .= Yii::$app->db->quoteColumnName($column->name);
        }

        $provider = Yii::$app->db->createCommand('SELECT ' . $select . ' FROM ' . $model->tableSchema->name)->queryAll();

        // Memory limit before php://temp starts using a temporary file
        $fp = fopen("php://temp/maxmemory:$memoryLimit", 'w');

        // Header line
        fputcsv($fp, $header, $delimiter, $enclosure);

        // Content lines
        foreach ($provider as $row) {
            $fields = [];
            foreach ($row as $item) {
                if ($item == 0 || !empty($item)) {
                    // no new lines in CSV format.
                    $fields[] = str_replace(["\r","\r\n","\n"], '', trim(strip_tags($item)));
                } else {
                    $fields[] = '';
                }
            }
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        $filename = $name . '_' . date('Y-m-d') . '.csv';
        $options = [
            'mimeType' => 'text/csv',
            'inline' => false,
        ];
        Yii::$app->response->sendContentAsFile($content, $filename, $options);
        Yii::$app->end();
    }

    /**
     * Download Microsoft formatted CSV.
     *
     * @param string $name Model name
     */
    public function actionMscsv($name)
    {
        /** @var $module \janisto\ycm\Module */
        $module = $this->module;
        /** @var $model \yii\db\ActiveRecord */
        $model = $module->loadModel($name);
        $exclude = $module->getExcludeDownloadFields($name);

        $memoryLimit = 5*1024*1024;  // 5M
        $delimiter = "\t"; // UTF-16LE needs "\t"
        $enclosure = '"';
        $header = [];
        $select = '';

        foreach ($model->tableSchema->columns as $column) {
            // skip excluded fields
            if (in_array($column->name, $exclude)) {
                continue;
            }

            // no new lines in CSV format.
            $header[] = str_replace(["\r", "\r\n", "\n"], '', trim($model->getAttributeLabel($column->name)));
            if ($select !== '') {
                $select .= ', ';
            }
            $select .= Yii::$app->db->quoteColumnName($column->name);
        }

        $provider = Yii::$app->db->createCommand('SELECT ' . $select . ' FROM ' . $model->tableSchema->name)->queryAll();

        // Memory limit before php://temp starts using a temporary file
        $fp = fopen("php://temp/maxmemory:$memoryLimit", 'w');

        // Header line
        fputcsv($fp, $header, $delimiter, $enclosure);

        // Content lines
        foreach ($provider as $row) {
            $fields = [];
            foreach ($row as $item) {
                if ($item == 0 || !empty($item)) {
                    // no new lines in CSV format.
                    $fields[] = str_replace(["\r","\r\n","\n"], '', trim(strip_tags($item)));
                } else {
                    $fields[] = '';
                }
            }
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        $content = chr(255) . chr(254) . mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
        $filename = $name . '_' . date('Y-m-d') . '.csv';
        $options = [
            'mimeType' => 'application/vnd.ms-excel; charset=UTF-16LE',
            'inline' => false,
        ];
        Yii::$app->response->sendContentAsFile($content, $filename, $options);
        Yii::$app->end();
    }

    /**
     * Download Excel.
     *
     * @param string $name Model name
     */
    public function actionExcel($name)
    {
        /** @var $module \janisto\ycm\Module */
        $module = $this->module;
        /** @var $model \yii\db\ActiveRecord */
        $model = $module->loadModel($name);
        $exclude = $module->getExcludeDownloadFields($name);

        $memoryLimit = 5*1024*1024; // 5M
        $select = '';
        $begin = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"><title>' . $name;
        $begin .= '</title></head><body><table cellpadding="3" cellspacing="0" width="100%" border="1">';
        $end = '</table></body></html>';

        $header = '<tr>';
        foreach ($model->tableSchema->columns as $column) {
            // skip excluded fields
            if (in_array($column->name, $exclude)) {
                continue;
            }

            $header .= '<th align="left" style="color: #f74902;">' . trim($model->getAttributeLabel($column->name)) . '</th>';
            if ($select !== '') {
                $select .= ', ';
            }
            $select .= Yii::$app->db->quoteColumnName($column->name);
        }
        $header .= '</tr>';

        $provider = Yii::$app->db->createCommand('SELECT ' . $select . ' FROM ' . $model->tableSchema->name)->queryAll();

        // Memory limit before php://temp starts using a temporary file
        $fp = fopen("php://temp/maxmemory:$memoryLimit", 'w');

        // Header
        fwrite($fp, $begin);

        // Header line
        fwrite($fp, $header);

        // Content lines
        foreach ($provider as $row) {
            $fields = '<tr>';
            foreach ($row as $item) {
                if ($item == 0 || !empty($item)) {
                    $fields .= '<td>' . trim(strip_tags($item)) . '</td>';
                } else {
                    $fields .= '<td>&nbsp;</td>';
                }
            }
            $fields .= '</tr>';
            fwrite($fp, $fields);
        }

        // Footer
        fwrite($fp, $end);

        rewind($fp);
        $content = stream_get_contents($fp);
        $filename = $name . '_' . date('Y-m-d') . '.xls';
        $options = [
            'mimeType' => 'application/vnd.ms-excel; charset=UTF-8',
            'inline' => false,
        ];
        Yii::$app->response->sendContentAsFile($content, $filename, $options);
        Yii::$app->end();
    }
}
