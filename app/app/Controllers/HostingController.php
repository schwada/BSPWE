<?php
namespace App\Controllers;

use App\Auth\AuthenticationInterface;
use App\Models\Hosting;
use Fig\Http\Message\StatusCodeInterface as Status;
use Slim\Psr7\Request as Request; 
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Container\Container;
use Predis\ClientInterface as RedisInterface;
use Ramsey\Uuid\Uuid;
use App\Hosting\Status as HostingStatus;
use App\Models\Database;
use App\Models\Domain;

class HostingController {

	private AuthenticationInterface $auth;
	private RedisInterface $redis;

	public function __construct(Container $container) {
		$this->auth = $container->get(AuthenticationInterface::class);
		$this->redis = $container->get(RedisInterface::class);
	}

	public function index(Request $request, Response $response): Response {
		$user = $this->auth->getAuthenticated();
		$response->getBody()->write(json_encode([
			"hostings" => $user->hostings,
			"domains" => $user->domains,
			"databases" => $user->databases
		]));
		return $response->withHeader('Content-Type', 'application/json')
		->withStatus(Status::STATUS_OK);
	}

	public function create(Request $request, Response $response): Response {
		$user = $this->auth->getAuthenticated();
		$body = $request->getParsedBody();
		$domainwithtld = explode(".",$body["domain"]);
		// do some validation ??
		$hosting = new Hosting();
		$hosting->uuid = Uuid::uuid4();
		$hosting->domain = $domainwithtld[0];
		$hosting->tld = $domainwithtld[1];
		$hosting->status = HostingStatus::Creating->value;
		$hosting->user()->associate($user);
		$saved = $hosting->save();

		// if(!$saved) return;

		$domain = new Domain();
		$domain->uuid = Uuid::uuid4();
		$domain->domain = $domainwithtld[0];
		$domain->tld = $domainwithtld[1];
		$domain->subdomains = "";
		$domain->status = HostingStatus::Creating->value;
		$domain->user()->associate($user);
		$domain->save();

		$db = new Database();
		$db->uuid = Uuid::uuid4();
		$db->domain = $domainwithtld[0];
		$db->tld = $domainwithtld[1];
		$db->status = HostingStatus::Creating->value;
		$db->user()->associate($user);
		$db->save();

		$dbMessage = array(
			"operation" => [\Worker\DatabaseManager::class, "createDatabase"],
			'args' => [ 'domain' => $domainwithtld[0], 'tld' => $domainwithtld[1]]
		);
		$hostingMessage= array(
			"operation" => [\Worker\DnsManager::class, "addHostsfileRecord"],
			'args' => [ 'domain' => $domainwithtld[0], 'hosts_record' =>  "XXX " . $body["domain"] . " #user=" . $domain->id]
		);
		$domainMessage = array(
			"operation" => [\Worker\HttpManager::class, "createHttpFile"],
			'args' => [ 'domain' => $domainwithtld[0], 'tld' => $domainwithtld[1], 'plan' => $body["plan"]]
		);
		$this->redis->rpush('queue', json_encode($hostingMessage));
		$this->redis->rpush('queue', json_encode($domainMessage));
		$this->redis->rpush('queue', json_encode($dbMessage));
		return $response->withHeader('Content-Type', 'application/json')
		->withStatus(Status::STATUS_ACCEPTED);
	}

	public function delete(Request $request, Response $response): Response {
		return $response->withHeader('Content-Type', 'application/json')
		->withStatus(Status::STATUS_ACCEPTED);
	}

	// public function createSubdomain(Request $request, Response $response): Response {
	// 	return $response->withHeader('Content-Type', 'application/json')
	// 	->withStatus(Status::STATUS_ACCEPTED);
	// }

}