<?php

use yii\db\Migration;

/**
 * Handles the creation for table `russian_banks_hb`.
 */
class m160412_204251_ursbankshb_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%russian_banks_hb}}', [
            'bik' => $this->string(10),
            'okpo' => $this->string(8),
            'full_name' => $this->string(128),
            'short_name' => $this->string(128),
            'ks' => $this->string(20),
            'city' => $this->string(128),
            'zip' => $this->integer(),
            'address' => $this->string(256),
            'tel' => $this->string(32),
        ]);

        $this->addPrimaryKey('pk_russian_banks_handbook', '{{%russian_banks_hb}}', 'bik');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%russian_banks_hb}}');
    }
}
