<?php

namespace common\components\logging;

use Symfony\Component\Config\Definition\Exception\Exception;
use yii\base\Component;
use yii\base\UnknownClassException;

/**
 * сканирует папму моделей
 * и ищет модель по таблице
 *
 * Class LoggingScaner
 * @package common\components\logging
 */
class LoggingScaner extends Component
{
    /**
     * каталог молелей
     */
    const FOLDER = '\\common\\models\\';

    /**
     * имя таблицы
     */
    const TABLE_METHOD = 'tableName';

    /**
     * список моделй
     *
     * @var
     */
    private static $list = null;

    /**
     * вернет/инициализирует список моделей
     *
     * @var
     */
    public static function getList() {
        if(empty(self::$list)) {
            $list = [];
            $path = \Yii::$app->basePath . '/..' . str_replace('\\', '/', self::FOLDER);
            $dir = scandir($path);
            foreach($dir as $file) {
                if(strpos($file, '.php') > 0) {
                    $class = self::FOLDER.str_replace('.php', '', $file);
                    try {
                        if (method_exists($class, self::TABLE_METHOD)) {
                            $table = call_user_func([$class, self::TABLE_METHOD]);
                            $table = str_replace(['{', '}', '%'], ['', '', ''], $table);
                            $list[$table] = $class;
                        }
                    }
                    catch (\Exception $e) {}
                }
            }
            self::$list = $list;
        }
        
        return self::$list;
    }

    public static function getModelName($tableName) {
        $list = self::getList();

        if(isset($list[$tableName])) {
            return $list[$tableName];
        }

        return null;
    }
}