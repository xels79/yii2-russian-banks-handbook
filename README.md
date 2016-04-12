# Справочник БИК банков России для Yii2

Это расширение позволяет вам загрузить себе справочник БИК банков России и использовать его внутри приложения, не обращаясь к сторонним сервисам.

Для работы консольного скрипта обновления справочника вам понадобится расширение php [dbase](https://pecl.php.net/package/dbase)

Установить расширение dbase можно так: sudo pecl install dbase
И не забудьте внести в /etc/php5/cli/php.ini запись: extension=dbase.so

## Установка

Используйте [composer](http://getcomposer.org/download/).

```bash
$ composer require romi45/yii2-russian-banks-handbook:~1.0
```

или добавьте

```
"romi45/yii2-russian-banks-handbook": "~1.0"
```
в секцию `require` файла `composer.json`


## Настройка

Добавьте модуль rusbankshb в секцию modules *консольного* конфига - это необходимо для первоначальной заливки и обновления данных.

```php
'modules' => [
    ...
    'rusbankshb' => [
        'class' => 'rusbankshb\Module',
        'controllerNamespace' => 'rusbankshb\commands'
    ],
    ...
]
```

Добавьте модуль rusbankshb в секцию modules *веб* конфига (этого можно не делать, если доступ к поиску нужно ограничить - читайте до конца).

```php
'modules' => [
    ...
    'rusbankshb' => 'class' => 'rusbankshb\Module',
    ...
]
```

Выполните миграцию БД:

```bash
$ php yii migrate/up --migrationPath=@vendor/romi45/yii2-russian-banks-handbook/migrations
```


Для первоначальной загрузки или обновления данных справочника используйте консольную команду:

```bash
$ yii rusbankshb/update [/path/to/file]
```

[/path/to/file] - абсолютный путь или alias до базы данных справочника (указывать не обязательно). Если не указывать - будет использован файл /data/bnkseek.dbf - это база на 13.04.2016



Свежий справочник можно скачать с сайта Центрального Банка РФ в этом разделе http://www.cbr.ru/mcirabis/?PrtId=bic

Скачайте архив вида bik_db_13042016.zip (свежая база), распакуйте и возьмите оттуда файл с названием bnkseek.dbf - в нем содержатся нужные данные.

Далее положите его куда вам хочется выполните команду:

```bash
$ yii rusbankshb/update /абсолютный/путь/до/bnkseek.dbf
```

Повторю - команду можно запускать, не указывая файл:

```bash
$ yii rusbankshb/update
```

В таком случае скрипт возьмет данные из файла /data/bnkseek.dbf - база на 13.04.2016

Банк России обновляет данные каждый рабочий день, но как правило существенные изменения происходят редко так что ваш локальный справочник можно обновлять раз в год или даже реже.


Запросить данные из справочника можно по URL '/rusbankshb?q=04000', где 04000 - это БИК или его часть.

Вы можете так же запрашивать поиск по другим полям справочника, указав название поля вторым параметром: '/rusbankshb?q=Кирова&f=address'.

Доступные поля для поиска:

* bik - БИК
* okpo - код по ОКПО
* full_name - полное название
* ks - корреспондентский счет
* city - город
* zip - почтовый индекс
* address - адрес
* tel - телефон


В ответ на запрос вам вернется JSON вида:

```json
[
   {
    "bik": "042204000",
    "okpo": "25612951",
    "full_name": "РКЦ АРЗАМАС",
    "short_name": "АРЗАМАС",
    "ks": "",
    "city": "АРЗАМАС",
    "zip": 607220,
    "address": "УЛ.КИРОВА,29",
    "tel": "(247)70269,70594"
  },
  {
    "bik": "043004000",
    "okpo": "22906681",
    "full_name": "РКЦ ПАЛАНА",
    "short_name": "ПАЛАНА",
    "ks": "",
    "city": "ПАЛАНА",
    "zip": 688000,
    "address": "УЛ.ПОРОТОВА,14",
    "tel": "(41543)31357,32659"
  },
  {
    "bik": "046404000",
    "okpo": "09277140",
    "full_name": "РКЦ КУРИЛЬСК",
    "short_name": "КУРИЛЬСК",
    "ks": "",
    "city": "КУРИЛЬСК",
    "zip": 694530,
    "address": "УЛ.САХАЛИНСКАЯ,1-А",
    "tel": "(42454)42219,42022"
  }
]
```

Из коробки URL для поиска будет доступен публично.

Если вам нужно ограничить доступ - удалите запись модуля из веб-конфига вашего приложения и напишите код поиска где вам хочется.

Пример кода для поиска с ограничением прав доступа:

```php

namespace rusbankshb\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use rusbankshb\models\Bank;

class DefaultController extends Controller
{
    /**
     * Поиск в справочнике
     *
     * @param string $q
     * @param string $f
     * @return array
     */
    public function actionIndex($q = null, $f = 'bik')
    {
        if(!Yii::$app->getUser()->can('admin')){
            throw new NotFoundHttpException('Оп оп...');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!array_key_exists($f, (new Bank())->getAttributes())) {
            return 'Нельзя искать по запрашиваемому полю.';
        }

        return array_values(ArrayHelper::map(Bank::find()->andFilterWhere(['like', $f, $q])->all(), 'bik', 'attributes'));
    }
}
```


## Лицензия

The MIT License (MIT). Читайте [описание лицензии](LICENSE.md) чтобы узнать больше.