<?php

namespace daxslab\website\models;

use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "metadata_definition".
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property string $params
 * @property int $page_type_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property PageType $pageType
 */
class MetadataDefinition extends \daxslab\website\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'metadata_definition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['params', 'label'], 'string'],
            [['page_type_id'], 'integer'],
            [['name', 'type'], 'string', 'max' => 255],
            [['page_type_id', 'name'], 'unique', 'targetAttribute' => ['page_type_id', 'name']],
            [['page_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PageType::className(), 'targetAttribute' => ['page_type_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return parent::behaviors();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => Yii::t('website','ID'),
            'name' => Yii::t('website','Name'),
            'label' => Yii::t('website','Label'),
            'type' => Yii::t('website','Type'),
            'params' => Yii::t('website','Params'),
            'page_type_id' => Yii::t('website','Page Type ID'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPageType()
    {
        return $this->hasOne(PageType::className(), ['id' => 'page_type_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if($insert){
            foreach($this->pageType->pages as $page){
                $metadata = new Metadata([
                    'page_id' => $page->id,
                    'metadata_definition_id' => $this->id,
                ]);
                $metadata->save();
            }
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }


    /**
     * {@inheritdoc}
     * @return MetadataDefinitionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetadataDefinitionQuery(get_called_class());
    }
}
