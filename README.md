# Yii2 sortable behaviour
[![Latest Stable Version](https://poser.pugx.org/kl83/yii2-sortable-behaviour/v/stable)](https://packagist.org/packages/kl83/yii2-sortable-behaviour)
[![Total Downloads](https://poser.pugx.org/kl83/yii2-sortable-behaviour/downloads)](https://packagist.org/packages/kl83/yii2-sortable-behaviour)
[![License](https://poser.pugx.org/kl83/yii2-sortable-behaviour/license)](https://packagist.org/packages/kl83/yii2-sortable-behaviour)

Provides functionality for sorting and moving model instances in a hierarchical structure of adjacency table.

## Installation
The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run
~~~
php composer.phar require kl83/yii2-sortable-behaviour ~1.1.0
~~~
or add
~~~
"kl83/yii2-sortable-behaviour": "~1.1.0"
~~~
to the require section of your composer.json file.

## Usage

### Add behaviour to your model.
~~~ php
public function behaviors()
{
    return [
        [
            'class' => 'kl83\behaviours\SortableBehaviour',
            'sortField' => 'sort',
            'parentIdField' => 'parent_id',
        ],
    ];
}
~~~

### Behaviour configuration
Option|Default|Description
------|-------------|-----------
**sortField**|idx|Table field containing the order of model instances.
**parentIdField**|parentId|Table field containing the identifier of the parent model instance.
**titleField**|null|Table field containing the name of the model instance. If not set, tries to use "name" or "title".
**defaultSortVal**|4294967295|The value of the sort field for the new model instance. By default, a new instance of the model will be the last.
**cache**|null|The cache object to use. By default, `Yii::$app->cache` is used, if it is set. If not, then DummyCache is used.

### Behaviour functions
```
null moveFirst(int $parentId = false)
```
Moves the model instance to the beginning of `$parentId` domain. If `$parentId` is not set, then the model instance will be the first in its current domain.
```
null moveLast(int $parentId = false)
```
Moves the model instance to the end of `$parentId` domain. If `$parentId` is not set, then the model instance will be the last in its current domain.
```
null moveAfter(Model|int $model)
```
Moves the model instance after `$model`. If `$model` is empty, then moves the model instance to the beginning of his domain.
```
null moveBefore(Model|int $model)
```
Moves the model instance before `$model`.
```
int[] getChildrenId(bool $includeSelf = true, bool $all = true)
```
Returns an array of instance IDs of child models. If `$all` is true, it also includes all sublevels.
```
\yii\db\ActiveQueryInterface getChildren()
```
Returns ActiveQuery to get child models.
```
\yii\db\ActiveQueryInterface getAllChildren(bool $includeSelf = false)
```
Returns ActiveQuery to get instances of child models, including all sublevels.
```
Model getParent()
```
Returns an instance of the parent model.
```
int[] getParentsId(bool $includeSelf = false)
```
Returns an array of instance IDs of parent models. Ordered by depth level.
```
\yii\db\ActiveQueryInterface getParents(bool $includeSelf = false)
```
Returns ActiveQuery to get instances of parent models.
```
Model getRoot()
```
Returns an instance of the root model.
```
string getFullPath(string $delimeter = " &rarr; ", callable $callable = false)
```
Returns the full path of the model instance. `$callable` is function to stringify model for output as path element. Simple `$callable` example:
``` php
function($model, $isRoot, $isLeaf){
  return $model->title;
}
```
```
Model|false getJointParent(Model $model, bool $includeSelf = false)
```
Returns an instance of the joint parent of the current model and `$model`.
```
null sortAlphabetically(int $parentId = false, int $direction = SORT_ASC)
```
Sorting model instances in alphabetical order.
```
int[] getTreeIds(bool $includeSelf = true, bool $includeParents = false)
```
Returns the identifiers of the child model instances, including all sublevels. Also include instance IDs of the parent models, if `$includeParents` is true.
```
\yii\db\ActiveQueryInterface getTree(bool $includeSelf = true, bool $includeParents = false)
```
Returns ActiveQuery to get child model instances, including all sublevels. And instances of the parent models, if `$includeParents` is true.

## PHPDoc model class heading
~~~
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
 * Returns the full path of the model instance.
 * $callable is function to stringify model for output as path element.
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
~~~

## License
MIT License
