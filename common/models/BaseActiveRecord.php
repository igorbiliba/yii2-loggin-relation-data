<?php

namespace common\models;

use common\components\logging\traits\LoggingTrait;
use Yii;

/**
 * базовый класс ActiveRecord для всеъ моделей проекта
 *
 * Class BaseActiveRecord
 * @package common\models
 */
class BaseActiveRecord extends \yii\db\ActiveRecord
{
    /**
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
    public $relationLogger = [];

    //модель логирования по триггерам
    use LoggingTrait;
}
