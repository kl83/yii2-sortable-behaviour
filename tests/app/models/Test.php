<?php
namespace app\models;

class Test extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => 'kl83\behaviours\SortableBehaviour',
                'sortField' => 'idx',
            ],
        ];
    }
}