<?php
abstract class Middleware {
    protected $next;

    public function __construct($next = null) {
        $this->next = $next;
    }

    abstract public function handle($request);

    protected function handleNext($request) {
        if ($this->next) {
            return $this->next->handle($request);
        }
        return true;
    }
}

class MiddlewareStack {
    private $middlewares = [];
    private $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function add(Middleware $middleware) {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function process() {
        $middleware = $this->buildChain();
        if ($middleware) {
            return $middleware->handle($this->request);
        }
        return true;
    }

    private function buildChain() {
        $last = null;
        foreach (array_reverse($this->middlewares) as $middleware) {
            $last = new $middleware($last);
        }
        return $last;
    }
}
?>
