<?php
require_once 'vendor/autoload.php';

$settings = [
	'settings'=>[
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true
	]
];

header('Access-Control-Allow-Headers: Content-Type, Cache-Control, X-Requested-With, Authorization');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

$app = new \Slim\App($settings);

$app->post('/{version}/login', function($request, $response, $args){
	$data = $request->getParsedBody();
	
	$pluginResponse = \BuckitApi\Modules\User\Account::login($data);
	
	if (isset($pluginResponse["status"]) && $pluginResponse["status"]){
		$token = substr(str_shuffle(MD5(microtime())), 0, 50);

		$session = \BuckitApi\Modules\User\Session::load();
		$pluginResponse['token'] = $token;
		$session[$token] = $pluginResponse;

		\BuckitApi\Modules\User\Session::save($session);

		return $response->withJson(["errorStatus"=>false, "errorMessage"=>null, "contentData"=>$pluginResponse], 200);
	}

	return $response->withJson(["errorStatus"=>false, "errorMessage"=>null, "contentData"=>["status"=>false, "reason"=>"Invalid Username or Password"]], 200);
});

$app->post('/{version}/register', function($request, $response, $args){
	$data = $request->getParsedBody();
	
	$pluginResponse = \BuckitApi\Modules\User\Account::newAccount($data);

	if (isset($pluginResponse["status"]) && $pluginResponse["status"]){
		
		return $response->withJson(["errorStatus"=>false, "errorMessage"=>null, "contentData"=>$pluginResponse], 200);
	}

	return $response->withJson(["errorStatus"=>false, "errorMessage"=>null, "contentData"=>["status"=>false, "reason"=>"Invalid Username or Password"]], 200);
});

$app->group('/', function(){
	$this->map(
		['GET', 'POST', 'PUT', 'DELETE'],
		'{version}/{module}/{resource}/{action}[/{resourceId}]',
		function($request, $response, $args){
			$globalResponseFormat = [
				"body"=>[
					"errorStatus"=>false,
					"errorMessage"=>NULL,
					"contentData"=>NULL
				],
				"status"=>200
			];

			if ($request->isGet() || $request->isDelete())
			{
				$parsedBody = $request->getQueryParams();
			}
			else
			{
				$parsedBody = $request->getParsedBody();
			}
			
			if (!is_array($parsedBody)){
				$globalResponse = [
					"body"=>[
						"errorStatus"=>true,
						"errorMessage"=>"No data detected."
					],
					"status"=>400
				];

				return $response->withJson($globalResponse["body"], $globalResponse["status"]);
			}

			$options = array_merge($args, $parsedBody);
			
			$globalResponse = array_replace_recursive($globalResponseFormat, BuckitApi\Middleware::processor($options));

			return $response->withJson($globalResponse["body"], $globalResponse["status"]);
	})->add(BuckitApi\Middleware::validateRequest());
})->add(BuckitApi\Middleware::permissionGateway());

$app->run();
