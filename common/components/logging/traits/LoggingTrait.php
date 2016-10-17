<?php
namespace common\components\logging\traits;
use common\components\logging\LoggingProcess;

/**
 * модель логирования по триггерам
 *
 * Class LoggingProcessTrait
 * @var $this \common\models\BaseActiveRecord
 */
trait LoggingTrait {
    /**
     * @var \common\components\logging\LoggingProcess
     */
    private $logger = null;

    /**
     * @var bool
     */
    private $isNewRecordFlag = true;

    /**
     * определим вызывается ли из админки
     * если дейсвтие выполняет админ создаем объек
     * для логирования
     */
    public function init()
    {
        parent::init();
        if(static::isWeb())
            $this->logger = LoggingProcess::contruct($this);
    }

    protected static function isWeb() {
        return strpos(get_class(\Yii::$app), 'yii\web') === 0;
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if(static::isWeb()) {
            if ($this->logger) {
                if ($this->isNewRecordFlag) {
                    //элемент создан, сообщим о нем в базу
                    $this->logger->create();
                }
            }
        }
    }

    /**
     * пометим флаг новой записи
     *
     * @param $insert
     * @return bool
     */
    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            if(static::isWeb()) {
                if ($this->logger) {
                    $this->isNewRecordFlag = $this->isNewRecord;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * перед удалением сохраним данные
     *
     * @param string $condition
     * @param array $params
     */
    public static function deleteAll($condition = '', $params = []) {
        if(static::isWeb()) {
            $logger = LoggingProcess::contruct();
            if ($logger) $logger->deleteAll(static::className(), $condition, $params);
        }

        return parent::deleteAll($condition, $params);
    }

    /**
     * перед обновлением сохраним данные
     *
     * @param $attributes
     * @param string $condition
     * @param array $params
     * @return mixed
     */
    public static function updateAll($attributes, $condition = '', $params = []) {
        if(static::isWeb()) {
            $logger = LoggingProcess::contruct();
            if ($logger) $logger->updateAll(static::className(), $condition, $params);
        }

        return parent::updateAll($attributes, $condition, $params);
    }
}