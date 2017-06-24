<?php
namespace kl83\behaviours;

use yii\db\ActiveRecord;

class SortableBehaviour extends \yii\base\Behavior
{
    /**
     * Table field to keep sorting
     * @var string
     */
    public $sortField = 'idx';
    /**
     * Default sort value for newly inserted models
     * @var integer
     */
    public $defaultSortVal = 4294967295;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => function(){
                $this->owner->{$this->sortField} = $this->defaultSortVal;
            },
        ];
    }

    /**
     * Reordering models when no more exist free indexes
     */
    private function reorder()
    {
        $className = $this->owner->className();
        $primaryKey = implode(',', $className::primaryKey());
        $data = $className::find()
            ->select("$primaryKey")
            ->orderBy("$this->sortField, $primaryKey")
            ->column();
        foreach ( $data as $i => $itemPK ) {
            $className::updateAll([ $this->sortField => ($i + 1) * 100 ], [ $primaryKey => $itemPK ]);
        }
    }

    /**
     * Find min index
     * @param integer $moreThen
     * @return integer
     */
    private function getMinIdx($moreThen = false)
    {
        $className = $this->owner->className();
        $query = $className::find()
            ->select("MIN($this->sortField)");
        if ( $moreThen !== false ) {
            $query->where([ '>', $this->sortField, $moreThen ]);
        }
        $result = $query->scalar();
        if ( $result === null ) {
            $result = $moreThen + 100;
        }
        return $result;
    }

    /**
     * Find max index
     * @param integer $lessThen
     * @return integer
     */
    private function getMaxIdx($lessThen = false)
    {
        $className = $this->owner->className();
        $query = $className::find()
            ->select("MAX($this->sortField)");
        if ( $lessThen !== false ) {
            $query->where([ '<', $this->sortField, $lessThen ]);
        }
        $result = $query->scalar();
        if ( $result === null ) {
            $result = floor($lessThen / 2);
        }
        return $result;
    }

    /**
     * Move model to first
     */
    public function moveFirst()
    {
        $className = $this->owner->className();
        $primaryKey = implode(',', $className::primaryKey());
        $minIdx = $this->getMinIdx();
        if ( ! $minIdx ) {
            $this->reorder();
            $minIdx = $this->getMinIdx();
        }
        $className::updateAll([ $this->sortField => floor($minIdx / 2) ], [ $primaryKey => $this->owner->primaryKey ]);
    }

    /**
     * Move model to first
     */
    public function moveLast()
    {
        $className = $this->owner->className();
        $primaryKey = implode(',', $className::primaryKey());
        $maxIdx = $this->getMaxIdx();
        if ( $maxIdx == $this->defaultSortVal ) {
            $this->reorder();
            $maxIdx = $this->getMaxIdx();
        }
        $className::updateAll([ $this->sortField => $maxIdx + 100 ], [ $primaryKey => $this->owner->primaryKey ]);
    }

    /**
     * Move this model after $model
     * @param \yii\db\ActiveRecord $model
     */
    public function moveAfter($model)
    {
        $className = $this->owner->className();
        $primaryKey = implode(',', $className::primaryKey());
        if ( is_numeric($model) ) {
            $model = $className::findOne($model);
        }
        if ( $model->{$this->sortField} == $this->defaultSortVal ) {
            $this->reorder();
            $model->refresh();
        }
        $nextIdx = $this->getMinIdx($model->{$this->sortField});
        if ( $nextIdx - $model->{$this->sortField} < 2 ) {
            $this->reorder();
            $model->refresh();
            $nextIdx = $this->getMinIdx($model->{$this->sortField});
        }
        $newIdx = $model->{$this->sortField} + floor(($nextIdx - $model->{$this->sortField})/2);
        $className::updateAll([ $this->sortField => $newIdx ], [ $primaryKey => $this->owner->primaryKey ]);
    }

    /**
     * Move this model before $model
     * @param \yii\db\ActiveRecord $model
     */
    public function moveBefore($model)
    {
        $className = $this->owner->className();
        $primaryKey = implode(',', $className::primaryKey());
        if ( is_numeric($model) ) {
            $model = $className::findOne($model);
        }
        if ( $model->{$this->sortField} < 1 ) {
            $this->reorder();
            $model->refresh();
        }
        $prevIdx = $this->getMaxIdx($model->{$this->sortField});
        if ( $model->{$this->sortField} - $prevIdx < 2 ) {
            $this->reorder();
            $model->refresh();
            $prevIdx = $this->getMaxIdx($model->{$this->sortField});
        }
        $newIdx = $model->{$this->sortField} - floor(($model->{$this->sortField} - $prevIdx)/2);
        $className::updateAll([ $this->sortField => $newIdx ], [ $primaryKey => $this->owner->primaryKey ]);
    }
}
