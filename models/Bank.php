<?php

namespace rusbankshb\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Справочник по русским банкам
 *
 * @property string $bik
 * @property string $okpo
 * @property string $full_name
 * @property string $short_name
 * @property string $ks
 * @property string $city
 * @property integer $zip
 * @property string $address
 * @property string $tel
 */
class Bank extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%russian_banks_hb}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bik'], 'required'],
            [['zip'], 'integer'],
            [['bik'], 'string', 'max' => 10],
            [['okpo'], 'string', 'max' => 8],
            [['full_name', 'short_name', 'city'], 'string', 'max' => 128],
            [['ks'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 256],
            [['tel'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bik' => 'БИК',
            'okpo' => 'ОКПО',
            'full_name' => 'Полное название',
            'short_name' => 'Короткое название',
            'ks' => 'Корреспондентский счет',
            'city' => 'Город',
            'zip' => 'Индекс',
            'address' => 'Адрес',
            'tel' => 'Телефоны',
        ];
    }
}
