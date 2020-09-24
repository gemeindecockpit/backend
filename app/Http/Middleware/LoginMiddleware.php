<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class LoginMiddleware implements Middleware {

  /**
   * @var ResponseFactoryInterface
   */
  private $responseFactory;

  /**
   * The constructor.
   *
   * @param ResponseFactoryInterface $responseFactory The response factory
   */
  public function __construct(ResponseFactoryInterface $responseFactory) {
      $this->responseFactory = $responseFactory;
  }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $routeName = $route->getName();
        $groups = $route->getGroups();
        $methods = $route->getMethods();
        $arguments = $route->getArguments();
        $response;
        # Define routes that user does not have to be logged in with. All other routes, the user
        # needs to be logged in with.
        $publicRoutesArray = array(
          'login',
          'logout'
        );

        if (!isset($_SESSION['user_id']) && !in_array($routeName, $publicRoutesArray)) {
          // redirect the user to the login page and do not proceed.
          $routeParser = RouteContext::fromRequest($request)->getRouteParser();
          $url = $routeParser->urlFor('login');
          //$response->withStatus(302)->withHeader('Location', $url);
          $response = $this->responseFactory->createResponse()->withStatus(403);

        } else {
          $response = $handler->handle($request);
        }

        return $response;
    }
}
