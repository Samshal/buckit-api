<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace BuckitApi\Modules\ShoppingCycles;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Cycle.
 *
 * ShoppingCycles Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 */
class Cycle
{
    /**
     * Creates a new cycle
     *
     * @param array $data
     */
    public static function newCycle(array $data)
    {
        $startDate = $data["start"];
        $user = $data["user"];
        $freq = $data["freq"];

        $startDate = date('Y-m-d', strtotime($startDate));

        $query = "INSERT INTO shopping_cycles (cycle_start_date, cycle_user, cycle_frequency) VALUES ('$startDate', $user, '$freq');";

        try
        {
            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);
             
             return ["status"=>true];

             throw new UndefinedValueException(
                sprintf(
                    "Unable to create cycle",
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

        return $result;
    }

    public static function viewCycles(int $userId){
        $selectBuilder = (new Builder("QueryBuilder","Select"))->getBuilder();

        try
        {
            $selectBuilder
                ->columns(
                    "cycle_start_date",
                    "cycle_frequency"
                )
                ->from(
                    "shopping_cycles"
                )
                ->where(
                    "cycle_user = ".
                    $userId
                );

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);


             foreach ($result as $key => $value) {
                $date = strtotime($value["cycle_start_date"]);
                $result[$key]["startDayMonth"] = date("d/m", $date);
                $result[$key]["startYear"] = date("Y", $date);

                $freq = strtoupper($value["cycle_frequency"]);
                

                switch($freq) {
                    case "MONTHLY": {
                        $nextDate = strtotime(date("Y-m-d", strtotime($value["cycle_start_date"]." +1 month")));
                        break;
                    }
                    case "WEEKLY": {
                        $nextDate = strtotime(date("Y-m-d",strtotime($value["cycle_start_date"]." +1 week")));
                        break;
                    }
                    case "YEARLY": {
                        $nextDate = strtotime(date("Y-m-d", strtotime($value["cycle_start_date"]." +1 year")));
                        break;
                    }
                }

                $result[$key]["nextDayMonth"] = date("d/m", $nextDate);
                $result[$key]["nextYear"] = date("Y", $nextDate);
                $result[$key]["nextCycleDate"] = date("Y-m-d", $nextDate);
                $result[$key]["completedCycles"] = 0;
             }

             return $result;

        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }   
    }
}