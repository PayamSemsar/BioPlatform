<?php
/**
 * Router ساده برای مدیریت URLها
 * پشتیبانی از GET, POST, PUT, DELETE
 */

declare(strict_types=1);

class Router
{
    private array $routes = [];
    private string $basePath = '';

    /**
     * تنظیم basePath برای پروژه
     */
    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/');
    }

    /**
     * ثبت route برای GET
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * ثبت route برای POST
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * ثبت route برای PUT
     */
    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * ثبت route برای DELETE
     */
    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * افزودن route به آرایه
     */
    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $path = $this->basePath . '/' . trim($path, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        // تبدیل پارامترهای route مثل {id} به regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * پردازش درخواست جاری
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $uri === '/' ? '/' : rtrim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // استخراج پارامترهای named
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }

    /**
     * اجرای handler
     */
    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            
            if (class_exists($controller)) {
                $instance = new $controller();
                call_user_func_array([$instance, $method], $params);
            } else {
                throw new Exception("Controller {$controller} not found");
            }
        } else {
            call_user_func_array($handler, $params);
        }
    }

    /**
     * ریدایرکت به URL دیگر
     */
    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * تولید URL برای route نام‌گذاری شده
     */
    public static function route(string $name, array $params = []): string
    {
        // می‌تواند در آینده گسترش یابد
        return '/' . $name;
    }
}
