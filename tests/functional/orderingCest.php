<?php

use app\models\Test;

class orderingCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function tryToTestDefaultSortFieldValue(FunctionalTester $I)
    {
        $model = new Test([
            'name' => 'Milk',
        ]);
        $model->save();
        $I->assertEquals($model->idx, 4294967295);
    }

    public function tryToTestMoveFirst(FunctionalTester $I)
    {
        $model = Test::find()->orderBy('idx, id')
            ->offset(2)
            ->one();
        $model->moveFirst();
        $firstModel = Test::find()->orderBy('idx, id')
            ->one();
        $I->assertEquals($model->id, $firstModel->id);
    }

    public function tryToTestMoveLast(FunctionalTester $I)
    {
        $model = Test::find()->orderBy('idx, id')
            ->offset(2)
            ->one();
        $model->moveLast();
        $lastModel = Test::find()->orderBy('idx DESC, id DESC')
            ->one();
        $I->assertEquals($model->id, $lastModel->id);
    }

    public function tryToTestMoveAfter(FunctionalTester $I)
    {
        $model = Test::find()->orderBy('idx, id')
            ->offset(1)
            ->one();
        $modelAfter = Test::find()->orderBy('idx, id')
            ->offset(3)
            ->one();
        $model->moveAfter($modelAfter);
        $testModel = Test::find()->orderBy('idx, id')
            ->offset(3)
            ->one();
        $I->assertEquals($model->id, $testModel->id);
    }

    public function tryToTestMoveBefore(FunctionalTester $I)
    {
        $model = Test::find()->orderBy('idx, id')
            ->offset(4)
            ->one();
        $modelBefore = Test::find()->orderBy('idx, id')
            ->offset(2)
            ->one();
        $model->moveBefore($modelBefore);
        $testModel = Test::find()->orderBy('idx, id')
            ->offset(2)
            ->one();
        $I->assertEquals($model->id, $testModel->id);
    }
}
