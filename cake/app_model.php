<?php
/* SVN FILE: $Id: app_model.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 20:33:56 -0600 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Application model for Cake.
 *
 * This is a placeholder class.
 * Create the same file in app/app_model.php
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package		cake
 * @subpackage	cake.cake
 */
class AppModel extends Model {

function addAssoc($assoc, $assoc_ids, $id = null)
{
        if ($id != null) {
            $this->id = $id;
        }

        $id = $this->id;

        if (is_array($this->id)) {
            $id = $this->id[0];
        }
        
        if ($this->id !== null && $this->id !== false) {
            $db =& ConnectionManager::getDataSource($this->useDbConfig);
            
            $joinTable = $this->hasAndBelongsToMany[$assoc]['joinTable'];
            $table = $db->name($db->fullTableName($joinTable));
            
            $keys[] = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
            $keys[] = $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
            $fields = join(',', $keys);
            
            if(!is_array($assoc_ids)) {
                $assoc_ids = array($assoc_ids);
            }
        
            // to prevent duplicates
            $this->deleteAssoc($assoc,$assoc_ids,$id);
            
            foreach ($assoc_ids as $assoc_id) {
                $values[]  = $db->value($id, $this->getColumnType($this->primaryKey));
                $values[]  = $db->value($assoc_id);
                $values    = join(',', $values);
                
                $db->execute("INSERT INTO {$table} ({$fields}) VALUES ({$values})");
                unset ($values);
            }
            
            return true;
        } else {
            return false;
        }
}


function deleteAssoc($assoc, $assoc_ids, $id = null)
{
        if ($id != null) {
            $this->id = $id;
        }

        $id = $this->id;

        if (is_array($this->id)) {
            $id = $this->id[0];
        }
        
        if ($this->id !== null && $this->id !== false) {
            $db =& ConnectionManager::getDataSource($this->useDbConfig);
            
            $joinTable = $this->hasAndBelongsToMany[$assoc]['joinTable'];    
            $table = $db->name($db->fullTableName($joinTable));
            
            $mainKey = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
            $assocKey = $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
            
            if(!is_array($assoc_ids)) {
                $assoc_ids = array($assoc_ids);
            }
            
            foreach ($assoc_ids as $assoc_id) {
                $db->execute("DELETE FROM {$table} WHERE {$mainKey} = '{$id}' AND {$assocKey} = '{$assoc_id}'");
            }
            
            return true;
        } else {
            return false;
        }
}

}
?>