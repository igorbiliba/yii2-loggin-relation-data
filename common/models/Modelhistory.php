<?php

namespace common\models;

use nhkey\arh\managers\BaseManager;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Command;

/**
 * This is the model class for table "modelhistory".
 *
 * @property integer $id
 * @property string $date
 * @property string $table
 * @property string $field_name
 * @property string $field_id
 * @property string $old_value
 * @property string $new_value
 * @property integer $type
 * @property string $user_id
 * @property string $namespace
 * @property string $data
 *
 * @property User $user
 */
class Modelhistory extends \yii\db\ActiveRecord
{
	/**
	 * виды изменения
	 * @var array
	 */
	public static $actions = [
		BaseManager::AR_INSERT => 'Добавление',
		BaseManager::AR_UPDATE => 'Редактирование',
		BaseManager::AR_DELETE => 'Удаление'
	];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modelhistory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'table', 'field_name', 'field_id', 'type', 'user_id', 'namespace'], 'required'],
            [['date'], 'safe'],
            [['old_value', 'new_value', 'namespace', 'data'], 'string'],
            [['type'], 'integer'],
            [['table', 'field_name', 'field_id', 'user_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'table' => 'Имя сущности',
            'field_name' => 'Параметр',
            'field_id' => 'Идентификатор записи',
            'old_value' => 'Старое значение',
            'new_value' => 'Новое значение',
            'type' => 'Действие',
            'user_id' => 'Администратор',
            'namespace' => 'Пространство имени класса',
            'data' => 'Данные для восстановления',
        ];
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
    public function getUser() {
    	return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

	/**
	 * восстанавливает удаленные данные, минуя поведения
	 * @param $data
	 * @return bool|int
	 */
	public function restore($data) {
		if(!empty($data['id']) && !empty($this->table)) {
			/**
			 * @var Command $command
			 */
			$command = Yii::$app->db->createCommand();
			$command->insert($this->table, $data);

			return $command->execute() && $this->remove();
		}

		return false;
	}

	/**
	 * @return int
	 */
	private function remove() {
		/**
		 * @var Command $command
		 */
		$command = Yii::$app->db->createCommand();
		$command->delete($this->tableName(), ['id' => $this->id]);

		return $command->execute();
	}
}
