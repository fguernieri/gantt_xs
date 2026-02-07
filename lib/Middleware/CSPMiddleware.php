<?php

namespace OCA\GanttXS\Middleware;

use OCP\AppFramework\Middleware;
use OCP\AppFramework\Http\Response;

/**
 * Middleware que adiciona headers CSP customizado para permitir wasm-unsafe-eval e inline scripts
 * necessário para frappe-gantt WebAssembly
 */
class CSPMiddleware extends Middleware {
  
  public function afterController($controller, $methodName, Response $response) {
    // Adiciona CSP header que permite wasm-unsafe-eval e unsafe-inline para frappe-gantt
    $csp = "script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' blob:; ";
    $csp .= "style-src 'self' 'unsafe-inline'; ";
    $csp .= "img-src 'self' data: blob:; ";
    $csp .= "font-src 'self' data:; ";
    $csp .= "connect-src 'self'; ";
    $csp .= "object-src 'none'; ";
    $csp .= "base-uri 'self'; ";
    $csp .= "frame-ancestors 'none'; ";
    $csp .= "form-action 'self'";
    
    $response->addHeader('Content-Security-Policy', $csp);
    
    return $response;
  }
}
