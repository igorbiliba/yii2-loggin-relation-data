<?php
namespace common\components\logging;

use yii\base\Component;

/**
 * сканирует схему базы
 * и  вернет fk по таблице
 *
 * Class LoggerSchema
 * @package common\components\logging
 */
class LoggerSchema extends Component
{
    /**
     * кеш схемы
     *
     * @var null
     */
    private static $schema = null;

    /**
     * вернет/инициализирует схему
     *
     * @return null|\yii\db\Schema
     */
    protected static function getSchema() {
        if(self::$schema === null) {
            $db = \Yii::$app->db;
            self::$schema = $db->getSchema();
        }

        return self::$schema;
    }

    /**
     * вернет fk по таблице
     *
     * @param $tableOwner
     * @return array
     */
    public static function getFkByTable($tableOwner) {
        $all = [];
        $schema = self::getSchema();//вся схема
        foreach ($schema->tableSchemas as $table) {//таблицы
            if(!empty($table->foreignKeys)) {
                foreach ($table->foreignKeys as $fks) {//ищем fk по таблице
                    $currentTable = null;
                    $key = null;
                    $field = null;

                    foreach ($fks as $field => $key) {
                        if($field === 0) $currentTable = $key;
                    }

                    if($currentTable == $tableOwner) {
                        $all[$table->name] = [$field => $key];
                    }
                }
            }
        }

        return $all;
    }
}