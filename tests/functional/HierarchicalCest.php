<?php

use app\models\TestHierarchical;

class HierarchicalCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function tryToTestGetChildren(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(1);
        $I->assertEquals(
            ["Jelly","Chocolate","Candy"],
            $model->getChildren()->select('name')->asArray()->column()
        );
    }

    public function tryToTestGetAllChildren(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(1);
        $I->assertEquals(
            ["Jelly","Chocolate","Candy","Toblerone","Fazer","Beef"],
            $model->getAllChildren()->select('name')->asArray()->column()
        );
    }

    public function tryToTestGetParents(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5);
        $I->assertEquals(
            ["Juice","Chocolate","Fazer","Beef"],
            $model->getParents(true)->select('name')->asArray()->column()
        );
    }

    public function tryToTestGetRoot(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5)->getRoot();
        $I->assertEquals($model->id, 1);
    }

    public function tryToTestHierarchicalDelete(FunctionalTester $I)
    {
        TestHierarchical::findOne(1)->delete();
        $I->assertEquals(TestHierarchical::find()->count(), 1);
    }

    public function tryToTestFullPath(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5);
        $I->assertEquals($model->getFullPath(' > '), "Juice > Chocolate > Fazer > Beef");
    }

    public function tryToTestFullPathWithCallable(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5);
        $path = $model->getFullPath(' > ', function($model){
            return in_array($model->id, [1, 5]) ? false : strtoupper($model->name);
        });
        $I->assertEquals($path, "CHOCOLATE > FAZER");
    }

    public function tryToTestGetJointParent(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5);
        $I->assertEquals($model->getJointParent(7)->id, 3);
    }

    public function tryToTestGetJointParentIncludeSelf(FunctionalTester $I)
    {
        $model = TestHierarchical::findOne(5);
        $I->assertEquals($model->getJointParent(7, true)->id, 7);
    }

    public function tryToTestAlphabeticalSortAllTable(FunctionalTester $I)
    {
        (new TestHierarchical)->sortAlphabetically();
        $I->assertEquals(
            TestHierarchical::find()->select('name')->orderBy('idx ASC, id ASC')->column(),
            ["Beef","Candy","Chocolate","Fazer","Horse","Jelly","Juice","Toblerone"]
        );
    }

    public function tryToTestAlphabeticalSort(FunctionalTester $I)
    {
        (new TestHierarchical)->sortAlphabetically(1);
        $I->assertEquals(
            TestHierarchical::find()->select('name')->where([ 'parentId' => 1 ])->orderBy('idx ASC, id ASC')->column(),
            ["Candy","Chocolate","Jelly"]
        );
    }
}
