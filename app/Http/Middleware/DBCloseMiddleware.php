<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Http\Models\DatabaseAccess;

class DBCloseMiddleware implements Middleware {
  /**
   * {@inheritdoc}
   */
  public function process(Request $request, RequestHandler $handler): Response
  {
    $response = $handler->handle($request);

    DatabaseAccess::get_instance()->close();
    return $response;
  }


}
