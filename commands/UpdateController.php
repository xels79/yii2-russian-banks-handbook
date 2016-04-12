<?php
namespace rusbankshb\commands;

use Yii;
use yii\helpers\Console;
use yii\console\Controller;
use rusbankshb\models\Bank;

/**
 * Class UpdateController
 *
 * Обновление справочника банков
 *
 * @package rusbankshb\commands
 */
class UpdateController extends Controller
{
    /**
     * Обновляет справочник банков, используя указанный файл или файл из папки data.
     *
     * Необходимо расширение dbase. Установить расширение dbase можно так: sudo pecl install dbase
     *
     * @param null $file
     * @return int
     */
    public function actionIndex($file = null)
    {
        if (!extension_loaded('dbase')) {
            $this->stdout("Для работы скрипта обновления необходимо установить расширение dbase\n", Console::FG_RED);

            return 1;
        }

        if ($file === null) {
            $file = Yii::getAlias(dirname(__FILE__) . '/../data/bnkseek.dbf');
        } else {
            $file = Yii::getAlias($file);
        }

        if (!file_exists($file)) {
            $this->stdout("Файл $file не найден!\n", Console::FG_RED);

            return 1;
        }

        if ($this->confirm("Обновить справочник банков России, используя файл $file?")) {
            $this->stdout('Выполняю обновление...' . "\n");

            $db = dbase_open($file, 0);

            if (!$db) {
                $this->stdout("Не удалось открыть файл как базу данный dbase!\n", Console::FG_RED);

                return 1;
            }

            $current_db_records_count = Bank::find()->count();
            $data_records_count = dbase_numrecords($db);
            $data_updated = false;

            $this->stdout("Количество банков в текущем справочнике - $current_db_records_count.\n");
            $this->stdout("Количество банков в файле - $data_records_count.\n");

            for ($i = 1; $i <= $data_records_count; $i++) {

                $rec = dbase_get_record_with_names($db, $i);

                /** @var Bank $model */
                $model = Yii::createObject([
                    'class' => Bank::className(),
                    'bik' => $rec["NEWNUM"],
                    'okpo' => $rec["OKPO"],
                    'full_name' => iconv('CP866', 'utf-8', $rec["NAMEP"]),
                    'short_name' => iconv('CP866', 'utf-8', $rec["NAMEN"]),
                    'ks' => $rec["KSNP"],
                    'city' => iconv('CP866', 'utf-8', $rec["NNP"]),
                    'zip' => (int)$rec["IND"],
                    'address' => iconv('CP866', 'utf-8', $rec["ADR"]),
                    'tel' => iconv('CP866', 'utf-8', $rec["TELEF"])
                ]);

                foreach ($model->getAttributes() as $key => $value) {
                    $model->$key = trim($value);
                }

                /** @var Bank $exist */
                $exist = Bank::findOne($model->bik);

                if (!$exist) {
                    $this->stdout("Добавлен новый банк: {$model->bik} {$model->short_name}\n");
                    $data_updated = true;
                    $model->save(false);
                } else {
                    if ($exist->getAttributes() != $model->getAttributes()) {
                        $exist->setAttributes($model->getAttributes());
                        $this->stdout("Обновлены данные банка: {$exist->bik} {$exist->short_name}\n");
                        $data_updated = true;
                        $exist->save(false);
                    }
                }
            }

            dbase_close($db);

            if ($data_updated) {
                $this->stdout('Справочник банков успешно обновлен!' . "\n", Console::FG_GREEN);
            } else {
                $this->stdout('В справочник банков не было внесено изменений.' . "\n", Console::FG_GREEN);
            }
        }

        return 0;
    }

    /**
     * Очищает справочник банков
     *
     * @return int
     */
    public function actionClear()
    {
        if ($this->confirm("Вы уверены, что хотите очистить данные справочника банков России?")) {
            $this->stdout('Удаляю данные...' . "\n");
            Bank::deleteAll();
            $this->stdout('Все данные справочника успешно удалены.' . "\n", Console::FG_GREEN);
        }

        return 0;
    }
}
