<?php declare(strict_types=1);

Namespace BuckitApi\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PermissionGatewayMiddleware implements \BuckitApi\MiddlewareInterface
{
	protected static function isUserLoggedIn($userToken)
	{
		$session = \BuckitApi\Modules\User\Session::load();

		return isset($session[$userToken]);
	}

	protected static function isUserPermitted($userToken, $resource, $permission)
	{
		$session = \Buckit\Modules\User\Session::load();

		if(isset($session[$userToken]))
		{
			$uuid = $session[$userToken]["uuid"];

			return true;
		}

		return false;
	}

	public function getStandardResponse()
	{
		return function(RequestInterface $request, ResponseInterface $response, callable $next)
		{
			$args = $request->getAttribute('routeInfo')[2];

			$module = $args['module'];
			$resource = $args['resource'];
			$permission = ((array)json_decode(file_get_contents(\EmmetBlue\Core\Constant::getGlobals()["config-dir"]["whitelists"])))[$request->getMethod()][0];

			$token = (isset($request->getHeaders()["HTTP_HTTP_AUTHORIZATION"][0])) ? $request->getHeaders()["HTTP_HTTP_AUTHORIZATION"][0] : "";

			if (!self::isUserLoggedIn($token))
			{
				$globalResponse = [];

				$globalResponse["status"] = 401;
				$globalResponse["body"]["errorStatus"] = true;
				$globalResponse["body"]["errorMessage"] = "You havent been logged in or your supplied login token is invalid.";

				return $response->withJson($globalResponse["body"], $globalResponse["status"]);
			}

			return $next($request, $response);
		};
	}
}
