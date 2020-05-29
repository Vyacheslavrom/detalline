<?
class User extends AppModel
{
var $name = 'User';
var $belongsTo = 'Group';
var $hasAndBelongsToMany = array('Permission' =>
array('className' => 'Permission',
'joinTable' => 'users_permissions'));
var $recursive = 5;
}
?>