<?php
namespace App\Controllers;

use App\Auth\AuthenticationInterface;
use App\Exceptions\SystemException;
use App\Models\Product;
use Fig\Http\Message\StatusCodeInterface as Status;
use Slim\Psr7\Request as Request; 
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Container\Container;
use Illuminate\Database\QueryException;
use Ramsey\Uuid\Uuid;

class ProductController {

		private AuthenticationInterface $auth;

		public function __construct(Container $container) {
			 // dump($container->get("settings"));
			$this->auth = $container->get(AuthenticationInterface::class);
		}


		public function index(Request $request, Response $response) {
			$response->getBody()->write(json_encode(Product::all()));
			return $response->withHeader('Content-Type', 'application/json')
			->withStatus(Status::STATUS_OK);
		}

		public function read(Request $request, Response $response, array $args) {
			$response->getBody()->write(json_encode(Product::where('uuid', $args['id'])->with('user')->firstOrFail()));

			return $response->withHeader('Content-Type', 'application/json')
			->withStatus(Status::STATUS_OK);
		}

		public function userIndex(Request $request, Response $response) {
			$user = $this->auth->getAuthenticated();
			$response->getBody()->write(json_encode($user->products));
			return $response->withHeader('Content-Type', 'application/json')
			->withStatus(Status::STATUS_OK);
		}



	public function create(Request $request, Response $response) {
		$data = $request->getParsedBody();
		$user = $this->auth->getAuthenticated();
		// TODO: validation
		// $v = new Validator($data);

		try{
			$product = new Product();
			$product->uuid = Uuid::uuid4();
			$product->stripe_id = Uuid::uuid4();
			$product->title = $data['title'];
			$product->slug = Uuid::uuid4();
			$product->desc = $data['desc'];
			$product->price = $data['price'];
			$product->file_path = Uuid::uuid4();
			$product->user()->associate($user);
			$product->save();
		} catch (QueryException $e) {
			// if ($e->getCode() == 23505) 
			$response->getBody()->write(json_encode($e));
            return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(Status::STATUS_INTERNAL_SERVER_ERROR);
		}

		// return body?
		$response->getBody()->write("ok");
		return $response->withStatus(Status::STATUS_CREATED);
	}



}