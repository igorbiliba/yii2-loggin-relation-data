<?php
namespace common\components\logging;

use common\models\User;

/**
 * компонент логирования
 *
 * Class Logging
 * @package common\components\logging
 */
class LoggingProcess extends TransactionLogging
{
    /**
     * тип действия "Создание"
     */
    const TYPE_CREATE = 'create';

    /**
     * тип действия "Изменение"
     */
    const TYPE_UPDATE = 'update';

    /**
     * тип действия "Удаление"
     */
    const TYPE_DELETE = 'delete';

    /**
     * данные для записи в базу
     *
     * @var null
     */
    private $data = null;

    /**
     * тип операции
     *
     * @var null
     */
    private $type = null;

    /**
     * id админа, выполнившего действие
     *
     * @var null
     */
    public $adminId = null;

    /**
     * модель
     *
     * @var \common\models\BaseActiveRecord
     */
    public $owner;

    /**
     * конструктор компонента, который инициализирует в зависимости
     * от параметров
     *
     * определим вызывается ли из админки
     * если дейсвтие выполняет админ создаем объек
     * для логирования
     */
    public static function contruct($owner = null) {
        if(!empty(\Yii::$app->user->identity) && \Yii::$app->user->identity->role == User::ROLE_ADMIN &&
            $adminId = \Yii::$app->user->identity->id)
            return new static(compact('owner', 'adminId'));

        return null;
    }

    /**
     * сохранение данных в базу
     */
    protected function toDb() {
        $save = new LoggingSave([
            'transactionId' => static::$transactionId,
            'adminId' => $this->adminId,
            'type' => $this->type,
            'data' => $this->data,
            'relationLogger' => $this->owner->relationLogger,
            'ownerClassName' => $this->owner->tableName(),
        ]);

        //загрузит данные
        if($save->makeData()) {
            //вставит в базу
            if($save->insertData()) {
                //очистка после сохранения
                $this->clear();
                return true;
            }
        }

        return false;
    }

    /**
     * очистка после сохранения
     */
    public function clear() {
        $this->adminId = null;
        $this->type    = null;
        $this->data    = null;
        $this->owner   = null;
        $this->owner   = null;
    }

    /**
     * удаление без предварительной выборки
     */
    public function deleteAll($class, $condition, $params) {
        $query = $class::find();
        if(empty($params)) $query->where($condition);
        else $query->where($condition, $params);

        foreach ($query->all() as $model) {
            $this->type = self::TYPE_DELETE;
            $this->owner = $model;
            $this->data = [$model->attributes];
            $this->toDb();
        }
    }

    /**
     * обновление без предварительной выборки
     *
     * @param $condition
     */
    public function updateAll($class, $condition, $params) {
        $query = $class::find();
        if(empty($params)) $query->where($condition);
        else $query->where($condition, $params);

        foreach ($query->all() as $model) {
            $this->type = self::TYPE_UPDATE;
            $this->owner = $model;
            $this->data = [$model->attributes];
            $this->toDb();
        }
    }

    /**
     * элемент создан, сообщим о нем в базу
     */
    public function create() {
        if($this->owner) {
            $this->data = [$this->owner->attributes];
            $this->type = self::TYPE_CREATE;
            $this->toDb();
        }
    }
}