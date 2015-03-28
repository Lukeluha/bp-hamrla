<?php

namespace App\Security;

use Nette\Security\Permission;

/**
 * Class AclFactory
 * Factory for creating access control list
 * @package App\Security
 */
class AclFactory
{
	public static function createAcl()
	{
		$permission = new Permission();
		$permission->addRole('student');
		$permission->addRole('teacher');
		$permission->addRole('admin', 'teacher');

		$permission->addResource('settings');

		$permission->allow('teacher', 'settings');
		$permission->allow('teacher', 'settings', array('students', 'classes'));
		$permission->allow('admin', 'settings', 'schoolYears');

		return $permission;
	}
}