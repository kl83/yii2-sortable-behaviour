<?php
namespace app\models;

/**
 *
 * @method null moveFirst(integer $parentId = false)
 * Moves the model instance to the beginning of $parentId domain.
 * If $parentId is not set, then the model instance will be the first in its current domain.
 *
 * @method null moveLast(integer $parentId = false)
 * Moves the model instance to the end of $parentId domain.
 * If $parentId is not set, then the model instance will be the last in its current domain.
 *
 * @method null moveAfter(self|integer $model)
 * Moves the model instance after $model.
 * If $model is empty, then moves the model instance to the beginning of his domain.
 *
 * @method null moveBefore(self|integer $model)
 * Moves the model instance before $model.
 *
 * @method integer[] getChildrenId(boolean $includeSelf = true, boolean $all = true)
 * Returns an array of instance IDs of child models. If $all is true, it also includes all sublevels.
 *
 * @method \yii\db\ActiveQueryInterface getChildren()
 * Returns ActiveQuery to get child models.
 *
 * @method \yii\db\ActiveQueryInterface getAllChildren(boolean $includeSelf = false)
 * Returns ActiveQuery to get instances of child models, including all sublevels.
 *
 * @method self getParent()
 * Returns an instance of the parent model.
 *
 * @method integer[] getParentsId(boolean $includeSelf = false)
 * Returns an array of instance IDs of parent models. Ordered by depth level.
 *
 * @method \yii\db\ActiveQueryInterface getParents(boolean $includeSelf = false)
 * Returns ActiveQuery to get instances of parent models.
 *
 * @method self getRoot()
 * Returns an instance of the root model.
 *
 * @method string getFullPath(string $delimeter = " &rarr; ", callable $callable = false)
 * Returns the full path of the model instance. $callable is function to stringify model for output as path element.
 * Simple $callable example:
 * function($model, $isRoot, $isLeaf){
 *    return $model->title;
 * }
 *
 * @method self|false getJointParent(self $model, boolean $includeSelf = false)
 * Returns an instance of the joint parent of the current model and $model.
 *
 * @method null sortAlphabetically(integer $parentId = false, integer $direction = SORT_ASC)
 * Sorting model instances in alphabetical order.
 *
 * @method integer[] getTreeIds(boolean $includeSelf = true, boolean $includeParents = false)
 * Returns the identifiers of the child model instances, including all sublevels.
 * Also include instance IDs of the parent models, if $includeParents is true.
 *
 * @method \yii\db\ActiveQueryInterface getTree(boolean $includeSelf = true, boolean $includeParents = false)
 * Returns ActiveQuery to get child model instances, including all sublevels.
 * And instances of the parent models, if $includeParents is true.
 */
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