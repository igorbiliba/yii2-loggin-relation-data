<?php
namespace common\components\logging;

use common\models\User;
use yii\base\Component;

/**
 * базовый компонент логирования
 * в котором определяем текущую транзакцию серий удалений/сохранений
 * по которой можно будет отследить связь между релейшн моделями
 *
 * Class TransactionLogging
 * @package common\components\logging
 */
class TransactionLogging extends Component
{
    /**
     * идентификатор текущей транзакции
     *
     * @var null
     */
    public static $transactionId = null;

    /**
     * определяем текущую транзакцию
     */
    public function init()
    {
        //определяем рандомный id транзакции
        if(static::$transactionId === null)
            static::$transactionId = static::generateTransactionId();
    }

    /**
     * определяем рандомный id транзакции
     */
    public static function generateTransactionId() {
        if(static::$transactionId === null)
            return time() . '_' . rand(999999999, 9999999999);

        return static::$transactionId;
    }
}