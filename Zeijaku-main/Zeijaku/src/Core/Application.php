<?php

namespace Zeijaku\Core;

use League\Container\Container;
use League\Event\Emitter;
use League\Event\ListenerAcceptorInterface;
use League\Route\RouteCollection;
use League\Route\Strategy\UriStrategy;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class Application implements HttpKernelInterface, TerminableInterface, \ArrayAccess
{
    protected $container;
    protected $router;
    protected $emitter;
    protected $config;

    /**
     *
     * Constructor Method creates and binds the container, router, and event emitter
     *
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->singleton('app', $this);

        $this->container->singleton('Symfony\Component\HttpFoundation\Request', function () {
            return Request::createFromGlobals();
        });
        $this->container->singleton('Symfony\Component\HttpFoundation\Response', function () {
            return new Response();
        });

        $this->container->singleton('request', $this->getContainer()->get('Symfony\Component\HttpFoundation\Request'));
        $this->container->singleton('response', $this->getContainer()->get('Symfony\Component\HttpFoundation\Response'));

        $this->router = new RouteCollection($this->container);
        $this->router->setStrategy(new UriStrategy());

        $this->emitter = new Emitter();

        $this->config = array();
        $this->container->singleton('config', $this->config);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    /*
     *
     * Paths Helpers
     *
     */

    /**
     * @return string The public path
     */
    public function public_path()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']);
    }

    /**
     * @return string The application's path
     */
    public function app_path()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']).'/../app/';
    }

    /**
     * @return string The storage path for the application
     */
    public function storage_path()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']).'/../app/storage/';
    }

    /*
     *
     * Configuration Methods
     *
     */

    /**
     * Binds component configuration and components to the container
     */
    public function bootstrap()
    {
        $this['config'] = include $this->app_path().'config/config.php';
        $providers = include $this->app_path().'config/providers.php';

        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * @param $config array to be loaded
     */
    public function loadConfig($config)
    {
        $this['config'] = $config;
    }

    /**
     * @param $key      name of the configuration item
     * @param $value    value of the configuration item
     */
    public function setConfig($key, $value)
    {
        // Use temp config array to avoid indirect
        // modification of overlaoded element error
        $config = $this['config'];
        $parsed = explode('.', $key);

        while (count($parsed) > 1) {
            $next = array_shift($parsed);

            if (! isset($config[$next]) || is_array($config[$next])) {
                $config[$next] = [];
            }

            $config =& $config[$next];
        }

        $config[array_shift($parsed)] = $value;
        $this['config'] = $config;
    }

    /**
     * @param $name     The configuration item name
     * @return null     The configuration item value
     */
    public function getConfig($name)
    {
        $config = $this['config'];
        $parsed = explode('.', $name);

        $result = $config;

        while ($parsed) {
            $next = array_shift($parsed);

            if (isset($result[$next])) {
                $result = $result[$next];
            } else {
                return null;
            }
        }

        return $result;
    }

    /*
     *
     * Container Methods
     *
     */

    /**
     * @param $provider Service Provider to be registered
     */
    public function register($provider)
    {
        $this->getContainer()->addServiceProvider($provider);
    }

    /**
     * @param string $key    The service alias
     * @return boolean       Whether or not the key exists
     */
    public function offsetExists($key)
    {
        return $this->getContainer()->offsetExists($key);
    }

    /**
     * @param string $key   The service alias
     * @return mixed        The service
     */
    public function offsetGet($key)
    {
        return $this->getContainer()->offsetGet($key);
    }

    /**
     * @param string $key   The service alias
     * @param mixed $value  The service to be bound
     */
    public function offsetSet($key, $value)
    {
        $this->getContainer()->offsetSet($key, $value);
    }

    /**
     * @param string $key    The service alias
     */
    public function offsetUnset($key)
    {
        $this->getContainer()->offsetUnset($key);
    }

    /*
     *
     * Runner Methods
     *
     */

    /**
     *
     * The HttpKernelInterface handle method.
     *
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if ($this->getConfig('session.startup')) {
            $this['session']->start();
        }

        $this->getEmitter()->emit('request.received', $request);

        $dispatcher = $this->getRouter()->getDispatcher();
        $response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        $this->getEmitter()->emit('response.created', $request, $response);

        return $response;
    }

    /**
     *
     */
    public function run()
    {
        $response = $this->handle($this['request']);
        $response->send();

        $this->terminate($this['request'], $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        $this->getEmitter()->emit('response.sent', $request, $response);
    }

    /*
     *
     * Event Emitter Methods
     *
     */

    /**
     * @param $event
     * @param $listener
     * @param int $priority
     */
    public function subscribe($event, $listener, $priority = ListenerAcceptorInterface::P_NORMAL)
    {
        $this->getEmitter()->addListener($event, $listener, $priority);
    }

    /**
     * @param $event
     */
    public function emit($event)
    {
        $arguments = [$event] + func_get_args();
        $this->getEmitter()->emit($arguments);
    }

    /*
     *
     * Route Methods
     *
     */

    /**
     * @param $route
     * @param $handler
     */
    public function get($route, $handler)
    {
        $this->getRouter()->addRoute('GET', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function post($route, $handler)
    {
        $this->getRouter()->addRoute('POST', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function put($route, $handler)
    {
        $this->getRouter()->addRoute('PUT', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function patch($route, $handler)
    {
        $this->getRouter()->addRoute('PATCH', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function delete($route, $handler)
    {
        $this->getRouter()->addRoute('DELETE', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function head($route, $handler)
    {
        $this->getRouter()->addRoute('HEAD', $route, $handler);
    }

    /**
     * @param $route
     * @param $handler
     */
    public function options($route, $handler)
    {
        $this->getRouter()->addRoute('OPTIONS', $route, $handler);
    }

    /*
     *
     * Request Methods
     *
     */

    /**
     * @param $key
     * @return mixed
     */
    public function request($key)
    {
        return $this['request']->request->get($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function query($key)
    {
        return $this['request']->query->get($key);
    }

    /*
     *
     * Response Methods
     *
     */

    /**
     * @param $content
     * @param int $status
     * @param array $headers
     */
    public function response($content, $status = 200, $headers = array())
    {
        $this['response']->setContent($content);
        $this['response']->setStatusCode($status);
        $this['response']->headers->add($headers);
        $this['response']->send();
    }

    /**
     * @param $url
     * @param int $status
     */
    public function redirect($url, $status = 302)
    {
        $this['response'] = new RedirectResponse($url, $status);
        $this['response']->send();
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     */
    public function json($data = array(), $status = 200, array $headers = array())
    {
        $this['response'] = new JsonResponse($data, $status, $headers);
        $this['response']->send();
    }

    /**
     * @param null $callback
     * @param int $status
     * @param array $headers
     */
    public function stream($callback = null, $status = 200, array $headers = array())
    {
        $this['response'] = new StreamedResponse($callback, $status, $headers);
        $this['response']->send();
    }

    /**
     * @param $file
     * @param int $status
     * @param array $headers
     * @param null $contentDisposition
     */
    public function download($file, $status = 200, array $headers = array(), $contentDisposition = null)
    {
        $this['response'] = new BinaryFileResponse($file, $status, $headers, true, $contentDisposition);
        $this['response']->send();
    }

    /*
     *
     * View Methods
     *
     */

    /**
     * @param $data
     * @param int $flags
     * @param null $encoding
     * @param bool $double_encode
     * @return array|string
     */
    public function escape($data, $flags = ENT_COMPAT, $encoding = null, $double_encode = true)
    {
        if (is_array($data)) {
            array_walk_recursive($data, function(&$string) use ($flags, $encoding, $double_encode) {
                $string = trim(htmlspecialchars($string, $flags, $encoding, $double_encode));
            });
        } else {
            $data = trim(htmlspecialchars($data, $flags, $encoding, $double_encode));
        }

        return $data;
    }

    /**
     * @param $view
     * @param array $data
     * @param bool $escape
     */
    public function render($view, $data = array(), $escape = false)
    {
        if ($escape) {
            $data = $this->escape($data);
        }

        $content = $this['view']->render($view, $data);
        $this['response']->setContent($content);
        $this['response']->send();
    }

    /**
     * @param $data
     * @param null $templates
     */
    public function viewData($data, $templates = null)
    {
        $this['view']->addData($data, $templates);
    }
}