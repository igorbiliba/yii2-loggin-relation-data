<?php

use common\models\Modelhistory;
use nhkey\arh\managers\BaseManager;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ModelhistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Действия пользователей';
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="content">
	<div class="modelhistory-index">
		<div class="box">
			<!-- /.box-header -->
			<div class="box-body">
			    <h1><?= Html::encode($this->title) ?></h1>
<!--			    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>

				<?php $form = ActiveForm::begin([
					'method' => 'get',
				]); ?>
					<div class="row">
						<div class="col-md-2">
							<?= $form->field($searchModel, 'dateFrom')
								->widget(DatePicker::classname(), [
									'language' => 'ru',
									'dateFormat' => 'yyyy-MM-dd',
									'options' => [
										'class' => 'form-control'
									]
								])->widget(\yii\widgets\MaskedInput::className(), [
									'mask' => '9999-99-99',
								])->label('С') ?>
						</div>
						<div class="col-md-2">
							<?= $form->field($searchModel, 'dateTo')
								->widget(DatePicker::classname(), [
									'language' => 'ru',
									'dateFormat' => 'yyyy-MM-dd',
									'options' => [
										'class' => 'form-control'
									]
								])->widget(\yii\widgets\MaskedInput::className(), [
									'mask' => '9999-99-99',
								])->label('До') ?>
						</div>
						<div class="col-md-2">
							<?= Html::submitButton('Найти', ['class' => 'btn btn-info btn-flat form-control',
								'style' => 'margin-top: 25px;']) ?>
						</div>
					</div>
				<?php ActiveForm::end(); ?>

			    <?= GridView::widget([
			    	'summary' => false,
			        'dataProvider' => $dataProvider,
			        'filterModel' => $searchModel,
			        'columns' => [
			            ['class' => 'yii\grid\SerialColumn'],

			            [
			                'attribute' => 'date',
				            'filter' => false,
			            ],

			            [
			            	'attribute' => 'table',
				            'value' => function(Modelhistory $modelhistory) {
				            	/**
					             * @var ActiveRecord $model
					             */
				            	$model = new $modelhistory->namespace;

				            	return method_exists($model, 'entityName')
					                ? $model->entityName()
						            : null;
				            },
				            'filter' => $searchModel->entities,
			            ],

				        [
					        'attribute' => 'field_name',
					        'value' => function(Modelhistory $modelhistory) {
						        /**
						         * @var ActiveRecord $model
						         */
						        $model = new $modelhistory->namespace;

						        return $modelhistory->type == BaseManager::AR_UPDATE
						            ? $model->attributeLabels()[$modelhistory->field_name]
							        : '';
					        }

				        ],

			            'field_id',

				        [
					        'attribute' => 'old_value',
					        'value' => function(Modelhistory $modelhistory) {
				        	    return $modelhistory->type == BaseManager::AR_UPDATE
						            ? StringHelper::truncate($modelhistory->old_value, 30, '...')
						            : '';
					        }
				        ],

				        [
					        'attribute' => 'new_value',
					        'value' => function(Modelhistory $modelhistory) {
						        return $modelhistory->type == BaseManager::AR_UPDATE
							        ?  StringHelper::truncate($modelhistory->new_value, 30, '...')
							        : '';
					        }
				        ],

			             [
			             	'attribute' => 'type',
			                'value' => function(Modelhistory $modelhistory) {
			    	            return Modelhistory::$actions[$modelhistory->type];
			                },
		                    'filter' => Modelhistory::$actions
			             ],

				        [
				        	'attribute' => 'user_id',
					        'value' => function(Modelhistory $modelhistory) use ($searchModel) {
			                	return $searchModel->admins[$modelhistory->user_id];
					        },
					        'filter' => $searchModel->admins,
				        ],
				        [
					        'class' => 'yii\grid\ActionColumn',
					        'buttons' => [
						        'restore' =>  function ($url, $model, $key) {
							        if($model->type == BaseManager::AR_DELETE) {
								        return Html::a(
								        	'<span class="glyphicon glyphicon-repeat"></span>',
									        ['modelhistory/restore', 'modelId' => $model->id],
									        [
										        'title' => \Yii::t('yii', 'Восстановление'),
										        'data-confirm' => \Yii::t('yii', 'Вы хотите восстановить эти данные?'),
										        'data-pjax' => '0',
								            ]
								        );
							        }
						        },
					        ],
					        'template' => '{restore}',
				        ],
			        ],
			    ]); ?>
			</div>
		</div>
	</div>
</section>
