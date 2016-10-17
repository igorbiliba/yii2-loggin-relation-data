<?php

namespace backend\controllers;

use Yii;
use common\models\Modelhistory;
use backend\models\ModelhistorySearch;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ModelhistoryController implements the CRUD actions for Modelhistory model.
 */
class ModelhistoryController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Modelhistory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ModelhistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Modelhistory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

	/**
	 * @param $modelId
	 * @return \yii\web\Response
	 * @throws NotFoundHttpException
	 */
    public function actionRestore($modelId) {
    	/**
	     * @var Modelhistory $model
	     */
    	$model = $this->findModel($modelId);

	    /**
	     * @var array $data
	     */
	    $data = json_decode($model->data, true);


    	if(!$model || !$data) {
    		throw new NotFoundHttpException('No such data to restore');
	    }

	    try {
		    if(!$model->restore($data)) {
		    	throw new Exception();
		    }

		    Yii::$app->session->setFlash('success', 'Данные успешно восстановлены');
	    } catch (Exception $e) {
	    	Yii::$app->session->setFlash('error', 'Ошибка восстановления данных');
	    }

	    return $this->redirect('index');
    }

    /**
     * Finds the Modelhistory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Modelhistory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Modelhistory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
