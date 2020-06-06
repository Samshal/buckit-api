<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace BuckitApi\Modules\User;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Session.
 *
 * Session Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 */
class Session
{
	public static function load()
	{
		$sessionString = file_get_contents(Constant::getGlobals()["session-location"]);
		
		$decodedSessionString = base64_decode($sessionString);

		return unserialize($decodedSessionString);
	}

	public static function save($session)
	{
		$serializedSession = serialize($session);
		$encodedSessionString = base64_encode($serializedSession);

		file_put_contents(Constant::getGlobals()["session-location"], $encodedSessionString);
	}

	public static function activate(int $resourceId){
		$query = ""; //"UPDATE Staffs.StaffPassword SET LoggedIn = 1 WHERE StaffID = $resourceId";

		return; //DBConnectionFactory::getConnection()->exec($query);
	}

	public static function deactivate(int $resourceId){
		$query = ""; //"UPDATE Staffs.StaffPassword SET LoggedIn = 0 WHERE StaffID = $resourceId";

		return; //DBConnectionFactory::getConnection()->exec($query);
	}	
}