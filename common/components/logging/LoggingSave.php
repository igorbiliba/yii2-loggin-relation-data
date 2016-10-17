<?php
namespace common\components\logging;

use common\models\Logging;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * компонент сохранения дыннх и отслеживания
 * релейшенов при удлалении
 *
 * Class Logging
 * @package common\components\logging
 */
class LoggingSave extends Component
{
    /**
     * валидное время
     */
    const FIRST_TIME = '1970-01-01';

    /**
     * имя таблицы текущей модели
     *
     * @var null
     */
    public $ownerClassName = null;

    /**
     * ид транзакции операции
     *
     * @var null
     */
    public $transactionId = null;

    /**
     * id пользователя, выполняшего действие
     *
     * @var null
     */
    public $adminId = null;

    /**
     * тип опреации
     *
     * @var null
     */
    public $type = null;

    /**
     * данные сохранения
     *
     * @var null
     */
    public $data = null;

    /**
     * данные для сохранения в в базу
     * json вида
     *
     * @var array
     */
    public $dataForSave = [];

    /**
     * зависимые реляционные данные
     *
     * иерархия подчиненных релейшенов
     * для логирования на случай удаления
     *
     * формат дерева подчиненных таблиц (has many):
     * ['имя подчиенной модели' => ['поле зависимости' => 'поле ключ']]
     *
     * пример для модели "companies":
     * ['\namespace..\CompaniesParams'] => ['company_id' => 'id'],
     *
     * @var array
     */
    public $relationLogger = null;

    /**
     * обаботает
     * зависимые реляционные данные
     *
     * если переменная пуста, сканирует схему базы
     * и сам подставит параметры
     */
    public function getRelationLogger() {
        if(!empty($this->relationLogger)) return $this->relationLogger;
        $ret = [];

        $fks = LoggerSchema::getFkByTable($this->ownerClassName);
        if(!empty($fks)) {
            foreach ($fks as $table => $relations) {
                $modelClass = LoggingScaner::getModelName($table);
                if($modelClass) {
                    $key = null;
                    $field = null;
                    foreach ($relations as $field => $key);
                    $ret[$modelClass] = [$field => $key];
                }
            }
        }

        return $ret;
    }

    /**
     * загрузит остальные данные
     * и подготовит декущие к привлекательному виду
     */
    public function makeData() {
        if(!empty($this->dataForSave)) return true;

        $all[] = $this->currentDataFormatted;
        //добавит зависимые данные
        if($this->type == LoggingProcess::TYPE_DELETE && $relations = $this->relationsData) {
            foreach ($relations as $table => $datas) {
                $all[] = [$table => $datas];
            }
        }

        //формат для базы
        $this->dataForSave = Json::encode($all);
        $this->fixDataForSave();

        return true;
    }

    /**
     * привести данные к валидноу виду
     */
    protected function fixDataForSave() {
        $this->dataForSave = str_replace('0000-00-00 00:00:00', self::FIRST_TIME, $this->dataForSave);
    }

    /**
     * вернет реляционные данные
     *
     * вертнет данные в формате
     * [
     *      'tableName1' => [data..],
     *      'tableName2' => [data..],
     * ]
     */
    public function getRelationsData() {
        $relationLogger = $this->getRelationLogger();
        if(empty($relationLogger)) return false;
        $list = [];

        //из описания рекурсии вытягиваем данные
        foreach ($relationLogger as $class => $data) {
            $params = [];
            //поля релейшн для связи
            foreach ($data as $field => $key) {
                $params[$field] = $this->data[0][$key];
            }

            //находим список моделей
            $models = $class::find()->where($params)->all();
            if(is_array($models)) {
                foreach ($models as $model) {
                    $list[$model::tableName()][] = $model->attributes;
                    //рекурсивно сканируем подчиненные записи
                    if(!empty($model->relationLogger)) {
                        $recurcive = new LoggingSave([
                            'transactionId' => $this->transactionId,
                            'adminId' => $this->adminId,
                            'type' => $this->type,
                            'data' => [$model->attributes],
                            'relationLogger' => $model->relationLogger,
                            'ownerClassName' => $model->tableName(),
                        ]);
                        //добавляем подчиненные в общий лист восстановления
                        $list = ArrayHelper::merge($list, $recurcive->relationsData);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * вернет главную таблицу
     */
    protected function getTable() {
        if(!empty($this->data)) {
            foreach ($this->data as $table => $val) {
                return $table;
            }
        }

        return null;
    }

    /**
     * данные текущей таблицы в привлекателном виде
     */
    protected function getCurrentDataFormatted() {
        return [
            $this->ownerClassName => $this->data,
        ];
    }

    /**
     * вставит данные в базу
     */
    public function insertData() {
        $model = new Logging();
        $model->admin_id = $this->adminId;
        $model->transaction_id = $this->transactionId;
        $model->data = $this->dataForSave;
        $model->main_table = $this->ownerClassName;
        $model->type = $this->type;
        $model->create_at = time();
        return $model->save();
    }
}