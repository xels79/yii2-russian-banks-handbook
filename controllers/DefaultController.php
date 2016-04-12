<?php

namespace rusbankshb\controllers;

use rusbankshb\models\Bank;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * Поиск в справочнике
     *
     * @param string $q
     * @param string $f
     * @return array
     */
    public function actionIndex($q = null, $f = 'bik')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!array_key_exists($f, (new Bank())->getAttributes())) {
            return 'Нельзя искать по запрашиваемому полю.';
        }

        return array_values(ArrayHelper::map(Bank::find()->andFilterWhere(['like', $f, $q])->all(), 'bik', 'attributes'));
    }
}