<?php

namespace app\controllers;

use app\models\Users;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class ApiController extends Controller
{

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-rows' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionGetRows() {
        $rows = Users::find()->select('*')->asArray()->all();
        return $rows;
    }

    public function actionUpdateRow() {
        $status = true;
        $post = Yii::$app->request->post();
        $id = isset($post['id']) ? $post['id'] : null;
        $column = isset($post['column']) ? $post['column'] : null;
        $value = isset($post['value']) ? $post['value'] : '';
        if($id && $column) {
            $user = Users::findOne($id);
            if($user && $user->$column != $value) {
                $user->$column = $value;
                if(!$user->save()) {
                    $status = false;
                }
            }
        } else {
            $status = false;
        }
        return ['status' => $status];
    }

    public function actionAddRow() {
        $status = true;
        $id = false;
        $row = new Users();
        if($row->save()) {
            $id = $row->id;

        } else {
            $stauts = false;
        };
        return ['status' => $status, 'id' => $id];
    }

    public function actionDeleteRow() {
        $status = true;
        $id = Yii::$app->request->post('id', null);
        if($id) {
            $res = Yii::$app->db->createCommand()
                ->delete(Users::tableName(), ['id' => $id])
                ->execute();
            if(!$res) {
                $status = false;
            }
        } else {
            $status = false;
        }
        return ['status' => $status];
    }
}
