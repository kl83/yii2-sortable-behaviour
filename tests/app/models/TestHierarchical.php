<?php
namespace app\models;

class TestHierarchical extends \yii\db\ActiveRecord
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