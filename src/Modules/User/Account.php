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
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session as CoreSession;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Account.
 *
 * Account Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 */
class Account
{
    /**
     * Logs a User In
     *
     * @param string $username
     * @param string $password
     */
    public static function login($data)
    {
        $username = $data["username"];
        $password = $data["password"];

        if (Account\Login::isLoginDataValid($username, $password))
        {
            $info = self::getAccountInfo(self::getUserID($username));
            $id = self::getUserID($username);

            \BuckitApi\Modules\User\Session::activate((int) $id);

            return ["status"=>true, "uuid"=>$info['StaffUUID'], "accountActivated"=>$info["AccountActivated"], "id"=>$id];
        }

        return ["status"=>false];
    }

     /**
     * Gets the ID of a user from the db
     *
     * @param string $username
     */
    public static function getUserID(string $username)
    {
        $selectBuilder = (new Builder("QueryBuilder","Select"))->getBuilder();

        try
        {
            $selectBuilder
                ->columns(
                    "user_id"
                )
                ->from(
                    "user"
                )
                ->where(
                    "user_email = ".
                    QB::wrapString($username, "'")
                );

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_SELECT, '', '', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return (int)$result[0]['user_id'];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }
    }

    public static function getAccountInfo(int $userId)
    {
        $selectBuilder = (new Builder("QueryBuilder","Select"))->getBuilder();

        try
        {
            $selectBuilder
                ->columns(
                    "user_id",
                    "user_name",
                    "user_email"
                )
                ->from(
                    "user"
                )
                ->where(
                    "user_id = ".
                    $userId
                );

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_SELECT, '', '', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return $result[0];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }   
    }

    public static function newAccount($data)
    {
        $userName = $data["name"];
        $userEmail = $data["email"];
        $userPassword = $data["password"];

        $userPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        try
        {
            $query = "INSERT INTO user (user_name, user_email, user_password) VALUES ('$userName', '$userEmail', '$userPassword');";

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_INSERT, '', '', (string)$query);
             
             return ["status"=>true];

             throw new UndefinedValueException(
                sprintf(
                    "Unable to register user",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }   
    }
}