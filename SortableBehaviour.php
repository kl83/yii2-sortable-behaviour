<?php
namespace kl83\behaviours;

use Yii;
use yii\db\ActiveRecord;
use yii\caching\DummyCache;
use yii\caching\TagDependency;

class SortableBehaviour extends \yii\base\Behavior
{
    /**
     * Table field to keep sorting
     * @var string
     */
    public $sortField = 'idx';
    /**
     * Table field represents parent id, if false hierarchical is disabled
     * @var string
     */
    public $parentIdField = 'parentId';
    /**
     * Represents model title. Used for generate model full name.
     * If not set try to use `name` or `title`.
     * @var string
     */
    public $titleField;
    /**
     * Default sort value for newly inserted models
     * @var integer
     */
    public $defaultSortVal = 4294967295;
    /**
     * Cache object. Default is Yii::$app->cache if he exist. DummyCache if not.
     * @var \yii\caching\Cache
     */
    public $cache;
    /**
     * Owner model class name
     * @var string
     */
    private $ownerClassName;
    /**
     * Primary key field name of owner model
     * @var string
     */
    private $primaryKey;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => function(){
                $this->ownerClassName = $this->owner->className();
                $this->primaryKey = implode(',', call_user_func("$this->ownerClassName::primaryKey"));
                if ( ! $this->titleField ) {
                    if ( array_key_exists('name', $this->owner->attributes) ) {
                        $this->titleField = 'name';
                    } elseif ( array_key_exists('title', $this->owner->attributes) ) {
                        $this->titleField = 'title';
                    } else {
                        $this->titleField = $this->primaryKey;
                    }
                }
                if ( ! $this->cache ) {
                    $this->cache = Yii::$app->cache ? Yii::$app->cache : new DummyCache;
                }
            },
            ActiveRecord::EVENT_BEFORE_INSERT => function(){
                $this->owner->{$this->sortField} = $this->defaultSortVal;
            },
            ActiveRecord::EVENT_AFTER_DELETE => function(){
                $this->invalidateTagDependency();
            },
            ActiveRecord::EVENT_AFTER_INSERT => function(){
                $this->invalidateTagDependency();
            },
            ActiveRecord::EVENT_AFTER_UPDATE => function(){
                $this->invalidateTagDependency();
            },
            ActiveRecord::EVENT_BEFORE_DELETE => function(){
                $models = $this->owner->getChildren()->all();
                foreach ( $models as $model ) {
                    $model->delete();
                }
            }
        ];
    }

    private function getTagDependencyTag()
    {
        return implode('-', [
            self::className(),
            $this->owner->tableName(),
        ]);
    }

    private function getTagDependency()
    {
        return new TagDependency(['tags' => $this->getTagDependencyTag()]);
    }

    private function invalidateTagDependency()
    {
        TagDependency::invalidate($this->cache, $this->getTagDependencyTag());
    }

    /**
     * Reordering models when no more exist free indexes
     * @param integer $parentId Reorder child items of this parent
     */
    private function reorder($parentId = 0)
    {
        $query = call_user_func("$this->ownerClassName::find")
            ->select("$this->primaryKey")
            ->orderBy("$this->sortField, $this->primaryKey");
        if ( $this->parentIdField ) {
            $query->where([ $this->parentIdField => $parentId ]);
        }
        $data = $query->column();
        foreach ( $data as $i => $itemPK ) {
            call_user_func("$this->ownerClassName::updateAll",
                [ $this->sortField => ($i + 1) * 100 ],
                [ $this->primaryKey => $itemPK ]);
        }
    }

    /**
     * Find min index
     * @param integer $parentId
     * @param integer $moreThen
     * @return integer
     */
    private function getMinIdx($parentId = 0, $moreThen = false)
    {
        $query = call_user_func("$this->ownerClassName::find")
            ->select("MIN($this->sortField)");
        if ( $moreThen !== false ) {
            $query->where([ '>', $this->sortField, $moreThen ]);
        }
        if ( $this->parentIdField ) {
            $query->andWhere([ $this->parentIdField => $parentId ]);
        }
        $result = $query->scalar();
        if ( $result === null ) {
            $result = $moreThen + 100;
        }
        return $result;
    }

    /**
     * Find max index
     * @param integer $parentId
     * @param integer $lessThen
     * @return integer
     */
    private function getMaxIdx($parentId = 0, $lessThen = false)
    {
        $query = call_user_func("$this->ownerClassName::find")
            ->select("MAX($this->sortField)");
        if ( $lessThen !== false ) {
            $query->where([ '<', $this->sortField, $lessThen ]);
        }
        if ( $this->parentIdField ) {
            $query->andWhere([ $this->parentIdField => $parentId ]);
        }
        $result = $query->scalar();
        if ( $result === null ) {
            $result = floor($lessThen / 2);
        }
        return $result;
    }

    /**
     * Move model to first
     * @param integer $parentId
     */
    public function moveFirst($parentId = false)
    {
        if ( $this->parentIdField && $parentId === false ) {
            $parentId = $this->owner->{$this->parentIdField};
        }
        $minIdx = $this->getMinIdx($parentId);
        if ( ! $minIdx ) {
            $this->reorder($parentId);
            $minIdx = $this->getMinIdx($parentId);
        }
        if ( $this->parentIdField ) {
            call_user_func("$this->ownerClassName::updateAll", [
                $this->sortField => floor($minIdx / 2),
                $this->parentIdField => $parentId,
            ], [ $this->primaryKey => $this->owner->primaryKey ]);
        } else {
            call_user_func("$this->ownerClassName::updateAll",
                [ $this->sortField => floor($minIdx / 2) ],
                [ $this->primaryKey => $this->owner->primaryKey ]);
        }
        $this->invalidateTagDependency();
    }

    /**
     * Move model to last
     * @param integer $parentId
     */
    public function moveLast($parentId = false)
    {
        if ( $this->parentIdField && $parentId === false ) {
            $parentId = $this->owner->{$this->parentIdField};
        }
        $maxIdx = $this->getMaxIdx($parentId);
        if ( $maxIdx == $this->defaultSortVal ) {
            $this->reorder($parentId);
            $maxIdx = $this->getMaxIdx($parentId);
        }
        if ( $this->parentIdField ) {
            call_user_func("$this->ownerClassName::updateAll", [
                $this->sortField => $maxIdx + 100,
                $this->parentIdField => $parentId,
            ], [ $this->primaryKey => $this->owner->primaryKey ]);
        } else {
            call_user_func("$this->ownerClassName::updateAll",
                [ $this->sortField => $maxIdx + 100 ],
                [ $this->primaryKey => $this->owner->primaryKey ]);
        }
        $this->invalidateTagDependency();
    }

    /**
     * Move this model after $model
     * @param \yii\db\ActiveRecord|integer $model
     */
    public function moveAfter($model)
    {
        if ( is_numeric($model) ) {
            $model = call_user_func("$this->ownerClassName::findOne", $model);
        }
        $parentId = $this->parentIdField ? $model->{$this->parentIdField} : 0;
        if ( $model->{$this->sortField} == $this->defaultSortVal ) {
            $this->reorder($parentId);
            $model->refresh();
        }
        $nextIdx = $this->getMinIdx($parentId, $model->{$this->sortField});
        if ( $nextIdx - $model->{$this->sortField} < 2 ) {
            $this->reorder($parentId);
            $model->refresh();
            $nextIdx = $this->getMinIdx($parentId, $model->{$this->sortField});
        }
        $newIdx = $model->{$this->sortField} + floor(($nextIdx - $model->{$this->sortField})/2);
        if ( $this->parentIdField ) {
            call_user_func("$this->ownerClassName::updateAll", [
                $this->sortField => $newIdx,
                $this->parentIdField => $parentId,
            ], [ $this->primaryKey => $this->owner->primaryKey ]);
        } else {
            call_user_func("$this->ownerClassName::updateAll",
                [ $this->sortField => $newIdx ],
                [ $this->primaryKey => $this->owner->primaryKey ]);
        }
        $this->invalidateTagDependency();
    }

    /**
     * Move this model before $model
     * @param \yii\db\ActiveRecord $model
     */
    public function moveBefore($model)
    {
        if ( is_numeric($model) ) {
            $model = call_user_func("$this->ownerClassName::findOne", $model);
        }
        $parentId = $this->parentIdField ? $model->{$this->parentIdField} : 0;
        if ( $model->{$this->sortField} < 1 ) {
            $this->reorder($parentId);
            $model->refresh();
        }
        $prevIdx = $this->getMaxIdx($parentId, $model->{$this->sortField});
        if ( $model->{$this->sortField} - $prevIdx < 2 ) {
            $this->reorder($parentId);
            $model->refresh();
            $prevIdx = $this->getMaxIdx($parentId, $model->{$this->sortField});
        }
        $newIdx = $model->{$this->sortField} - floor(($model->{$this->sortField} - $prevIdx)/2);
        if ( $this->parentIdField ) {
            call_user_func("$this->ownerClassName::updateAll", [
                $this->sortField => $newIdx,
                $this->parentIdField => $parentId,
            ], [ $this->primaryKey => $this->owner->primaryKey ]);
        } else {
            call_user_func("$this->ownerClassName::updateAll",
                [ $this->sortField => $newIdx ],
                [ $this->primaryKey => $this->owner->primaryKey ]);
        }
        $this->invalidateTagDependency();
    }

    /**
     * Recursive method to get all children id, including subid
     * @param array|integer $ids
     * @param string $className
     * @param string $primaryKey
     * @param string $parentIdField
     * @param boolean $recursive
     * @return integer[]
     */
    private static function getChildrenIdRec($ids, $className, $primaryKey, $parentIdField, $recursive)
    {
        $subIds = $className::find()
            ->select($primaryKey)
            ->where([ $parentIdField => $ids ])
            ->column();
        if ( $subIds ) {
            if ( $recursive ) {
                return array_merge($subIds, self::getChildrenIdRec($subIds, $className, $primaryKey, $parentIdField, true));
            } else {
                return $subIds;
            }
        } else {
            return [];
        }
    }

    /**
     * Return all children id, including subid
     * @param boolean $includeSelf
     * @param boolean $all
     * @return integer[]
     */
    public function getChildrenId($includeSelf = true, $all = true)
    {
        $cacheKey = [
            'class' => self::className(),
            'ownerClass' => $this->ownerClassName,
            'method' => 'getChildrenId',
            'id' => $this->owner->{$this->primaryKey},
            'all' => $all,
        ];
        $ids = $this->cache->getOrSet($cacheKey, function() use ( $all ) {
            return self::getChildrenIdRec($this->owner->{$this->primaryKey}, $this->ownerClassName, $this->primaryKey, $this->parentIdField, $all);
        }, null, $this->getTagDependency());
        if ( $includeSelf ) {
            return array_merge([ $this->owner->{$this->primaryKey} ], $ids);
        } else {
            return $ids;
        }
    }

    /**
     * Return next level children
     * @return \yii\db\ActiveQueryInterface
     */
    public function getChildren()
    {
        return $this->owner
            ->hasMany($this->ownerClassName, [ $this->parentIdField => $this->primaryKey ])
            ->orderBy([
                "$this->sortField" => SORT_ASC,
                "$this->primaryKey" => SORT_ASC,
            ]);
    }

    /**
     * Return all children, including subchildren
     * @param boolean $includeSelf
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAllChildren($includeSelf = false)
    {
        return call_user_func("$this->ownerClassName::find")
            ->where([
                $this->primaryKey => $this->getChildrenId($includeSelf),
            ])
            ->orderBy("$this->parentIdField ASC, $this->sortField ASC, $this->primaryKey ASC");
    }

    /**
     * Return parent
     * @return \yii\db\ActiveRecord
     */
    public function getParent()
    {
        return $this->owner
            ->hasOne($this->ownerClassName, [ $this->primaryKey => $this->parentIdField ])
            ->one();
    }

    /**
     * Return array with $id and parents of $id, ordered by level
     * @param integer $id
     * @param string $className
     * @param string $primaryKey
     * @param string $parentIdField
     * @return integer[]
     */
    private function getParentsIdRec($id, $className, $primaryKey, $parentIdField)
    {
        $parentId = $className::find()->select($parentIdField)->where([ $primaryKey => $id ])->scalar();
        if ( $parentId ) {
            return array_merge(self::getParentsIdRec($parentId, $className, $primaryKey, $parentIdField), [$id]);
        } else {
            return [$id];
        }
    }

    /**
     * Return parents id, ordered by level
     * @param boolean $includeSelf
     * @return integer[]
     */
    public function getParentsId($includeSelf = false)
    {
        $cacheKey = [
            'class' => self::className(),
            'ownerClass' => $this->ownerClassName,
            'method' => 'getParentsId',
            'id' => $this->owner->{$this->primaryKey},
        ];
        if ( $this->owner->{$this->parentIdField} ) {
            $parents = $this->cache->getOrSet($cacheKey, function(){
                return self::getParentsIdRec($this->owner->{$this->parentIdField}, $this->ownerClassName, $this->primaryKey, $this->parentIdField);
            }, null, $this->getTagDependency());
        } else {
            $parents = [];
        }
        return $includeSelf ? array_merge($parents, [$this->owner->{$this->primaryKey}]) : $parents;
    }

    /**
     * Return parents, ordered by level
     * @param boolean $includeSelf
     * @return \yii\db\ActiveQueryInterface
     */
    public function getParents($includeSelf = false)
    {
        $parentsId = $this->getParentsId($includeSelf);
        $orderBy = [];
        foreach ( $parentsId as $id ) {
            $orderBy[] = "`$this->primaryKey` = $id DESC";
        }
        return call_user_func("$this->ownerClassName::find")
            ->where([
                $this->primaryKey => $parentsId,
            ])
            ->orderBy(implode(', ', $orderBy));
    }

    /**
     * Return root model of this model
     * @return \yii\db\ActiveRecord
     */
    public function getRoot()
    {
        if ( $this->owner->{$this->parentIdField} ) {
            return $this->getParents()->one();
        } else {
            return $this->owner;
        }
    }

    /**
     * Output model as crumb
     * @param \yii\db\ActiveRecord $model
     * @param callable $callable
     * @param boolean $isRoot
     * @param boolean $isLeaf
     * @return string
     */
    private function prepareCrumb($model, $callable, $isRoot, $isLeaf)
    {
        return $callable ? $callable($model, $isRoot, $isLeaf) : $model->{$this->titleField};
    }

    /**
     * Return full path of model
     * @param string $delimeter
     * @param callable $callable function ( $model, $isRoot, $isLeaf ) { return 'string' or false; }
     * @return string
     */
    public function getFullPath($delimeter = " &rarr; ", $callable = false)
    {
        $parents = $this->owner->getParents()->all();
        if ( ! $parents ) {
            return $this->prepareCrumb($this->owner, $callable, true, true);
        } else {
            $crumbs = [ $this->prepareCrumb(array_shift($parents), $callable, false, false) ];
            foreach ( $parents as $parent ) {
                $crumbs[] = $this->prepareCrumb($parent, $callable, false, false);
            }
            $crumbs[] = $this->prepareCrumb($this->owner, $callable, false, true);
            return implode($delimeter, array_filter($crumbs));
        }
    }

    /**
     * Return joint parent of two models or false if it's not exists.
     * @param \yii\db\ActiveRecord $model
     * @param boolean $includeSelf
     * @return \yii\db\ActiveRecord|false
     */
    public function getJointParent($model, $includeSelf = false)
    {
        if ( is_numeric($model) ) {
            $model = call_user_func("$this->ownerClassName::findOne", $model);
        }
        if ( $this->owner->{$this->parentIdField} == $model->{$this->parentIdField} ) {
            return $this->getParent();
        }
        $ownerParents = $this->owner->getParents($includeSelf)->all();
        $modelParents = $model->getParents($includeSelf)->all();
        $jointParent = false;
        foreach ( $ownerParents as $idx => $parent ) {
            if ( isset($modelParents[$idx]) && $parent->{$this->primaryKey} == $modelParents[$idx]->{$this->primaryKey} ) {
                $jointParent = $parent;
            } else {
                break;
            }
        }
        return $jointParent;
    }
}
