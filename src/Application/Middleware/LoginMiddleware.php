<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LoginMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $route = $request->getAttribute('route');
        $routeName = $route->getName();
        $groups = $route->getGroups();
        $methods = $route->getMethods();
        $arguments = $route->getArguments();

        # Define routes that user does not have to be logged in with. All other routes, the user
        # needs to be logged in with.
        $publicRoutesArray = array(
          'login'
        );

        if (!isset($_SESSION['user_id']) && !in_array($routeName, $publicRoutesArray))
        {
          // redirect the user to the login page and do not proceed.
          $response = $response->withRedirect('/login');
        }
        else
        {
          // Proceed as normal...
          $response = $next($request, $response);
        }

        return $response;
    }
}
