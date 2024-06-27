<?php

namespace Zus1\Discriminator\Middleware;

use Zus1\Discriminator\Helper\Helper;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserInjection
{
    public function __construct(
        private Helper $helper,
    ){
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(($type = $this->guessUserType($request)) === '') {
            throw new \Exception(
                sprintf(
                    'Request must include user_type query parameter if route is  using %s middleware',
                    __CLASS__
                )
            );
        }

        $modelClass = sprintf('App\\Models\\Users\\%s', $type);
        /** @var Model $model */
        $model = new $modelClass();

        $user = $model->newModelQuery()->find($request->route()->parameter('user'));
        $request->route()->setParameter('user', $user);

        return $next($request);
    }

    private function guessUserType(Request $request): string
    {
        $users = $this->helper->getAvailableUserTypes();
        $possibleFromQuery = $request->query('user_type');

        if($possibleFromQuery !== null && in_array(ucfirst($possibleFromQuery), $users)) {
            $key = array_search($possibleFromQuery, $users);

            return $users[$key];
        }

        return '';
    }
}
