<?php

namespace backend\models;

use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Modelhistory;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ModelhistorySearch
 * @package backend\models
 *
 * @property User[] $admins
 * @property array $entities
 */
class ModelhistorySearch extends Modelhistory
{
	/**
	 * фильтрация по дате (начиная с текущего числа)
	 * @var string
	 */
	public $dateFrom;

	/**
	 * фильтрация по дате (до текущего числа)
	 * @var string
	 */
	public $dateTo;

	/**
	 * @var array
	 */
	private  $_entities = [];


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type'], 'integer'],
            [['date', 'table', 'field_name', 'field_id', 'old_value', 'new_value', 'user_id', 'dateFrom', 'dateTo'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Modelhistory::find()
            ->innerJoinWith('user');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

	    $dataProvider->sort->attributes['user_id'] = [
		    'asc' => ['user.name' => SORT_ASC],
		    'desc' => ['user.name' => SORT_DESC],
	    ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 'table', $this->table])
            ->andFilterWhere(['like', 'field_name', $this->field_name])
            ->andFilterWhere(['like', 'field_id', $this->field_id])
            ->andFilterWhere(['like', 'old_value', $this->old_value])
            ->andFilterWhere(['like', 'new_value', $this->new_value])
            ->andFilterWhere(['>=', 'date', $this->dateFrom])
            ->andFilterWhere(['<=', 'date', $this->dateTo])
            ->andFilterWhere(['like', 'user_id', $this->user_id]);

        return $dataProvider;
    }

	/**
	 * получение массива всех возможных администраторов
	 *
	 * @return User[]
	 */
    public function getAdmins() {
    	/**
	     * @var array $users
	     */
    	if($users = User::find()->asArray()->all()) {
    		return ArrayHelper::map($users, 'id', 'name');
	    }

    	return null;
    }


	/**
	 * массив наименований изменяемых сущностей
	 * @return array
	 */
    public function getEntities() {
	    if (empty($this->_entities)) {
		    /**
		     * @var Modelhistory[] $history
		     */
		    $history = Modelhistory::find()
			    ->select(['table', 'namespace'])
			    ->groupBy('namespace')
			    ->all();

		    foreach ($history as $item) {
			    /**
			     * @var ActiveRecord $model
			     */
			    $model = new $item->namespace;

			    if (method_exists($model, 'entityName')) {
				    $this->_entities[$item->table] = $model->entityName();
			    }
		    }
	    }

	    return $this->_entities;
    }
}
