<?php
/**
 * Item Relations
 * @copyright Copyright 2010-2014 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Item Relations Relation table.
 */
class Table_ItemRelationsRelation extends Omeka_Db_Table
{
    /**
     * Get the default select object.
     *
     * Automatically join with both Property and Vocabulary to get all the
     * data necessary to describe a whole relation.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $db = $this->_db;
        return parent::getSelect()
            ->join(
                array('item_relations_properties' => $db->ItemRelationsProperty),
                'item_relations_relations.property_id = item_relations_properties.id',
                array(
                    'property_vocabulary_id' => 'vocabulary_id',
                    'property_local_part' => 'local_part',
                    'property_label' => 'label',
                    'property_description' => 'description'
                )
            )
            ->join(
                array('item_relations_vocabularies' => $db->ItemRelationsVocabulary),
                'item_relations_properties.vocabulary_id = item_relations_vocabularies.id',
                array('vocabulary_namespace_prefix' => 'namespace_prefix')
            );
    }

    /**
     * Find item relations by subject item ID.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @return array
     */
    public function findBySubjectItemId($subjectItemId, $onlyExistingObjectItems = true, $propertyId = null)
    {
        $db = $this->_db;
        $select = $this->getSelect()
            ->where('item_relations_relations.subject_item_id = ?', (int) $subjectItemId);
        if ($onlyExistingObjectItems) {
            $select->join(
                array('items' => $db->Item),
                'items.id = item_relations_relations.object_item_id',
                array()
            );
        }
        if ($propertyId) {
            if (is_array($propertyId)) {
                $select
                    ->where('item_relations_relations.property_id IN (?)', array_map('intval', $propertyId));
            }
            // Single property.
            else{
                $select
                    ->where('item_relations_relations.property_id = ?', (int) $propertyId);
            }
        }
        return $this->fetchObjects($select);
    }

    /**
     * Find item relations by object item ID.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @return array
     */
    public function findByObjectItemId($objectItemId, $onlyExistingSubjectItems = true, $propertyId = null)
    {
        $db = $this->_db;
        $select = $this->getSelect()
            ->where('item_relations_relations.object_item_id = ?', (int) $objectItemId);
        if ($onlyExistingSubjectItems) {
            $select->join(
                array('items' => $db->Item),
                'items.id = item_relations_relations.subject_item_id',
                array()
            );
        }
        if ($propertyId) {
            if (is_array($propertyId)) {
                $select
                    ->where('item_relations_relations.property_id IN (?)', array_map('intval', $propertyId));
            }
            // Single property.
            else{
                $select
                    ->where('item_relations_relations.property_id = ?', (int) $propertyId);
            }
        }
        return $this->fetchObjects($select);
    }

    /**
     * Find all specified relations.
     *
     * @internal This is a short for findBy().
     *
     * @param integer|array $objectItemId
     * @param integer|array $propertyId
     * @param integer|array $subjectItemId
     * @return array
     */
    public function findRelations($subjectItemId = null, $propertyId = null, $objectItemId = null)
    {
        $params = array();
        if (!is_null($subjectItemId)) {
            $params['subject_item_id'] = is_array($subjectItemId)
                ? array_map('intval', $subjectItemId)
                : (integer) $subjectItemId;
        }
        if (!is_null($propertyId)) {
            $params['property_id'] = is_array($propertyId)
                ? array_map('intval', $propertyId)
                : (integer) $propertyId;
        }
        if (!is_null($objectItemId)) {
            $params['object_item_id'] = is_array($objectItemId)
                ? array_map('intval', $objectItemId)
                : (integer) $objectItemId;
        }
        return $this->findBy($params);
    }
}
