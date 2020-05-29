<?
class Group extends AppModel
{
var $name = 'Group';
var $hasMany = 'Customer';
var $hasAndBelongsToMany = array('Permission' =>
array('className' => 'Permission',
'joinTable' => 'groups_permissions'));
}
?>