<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

class CheckInstaller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $modulesStatusPath = base_path('modules_statuses.json');

        // Check if the file exists before attempting to read
        if (File::exists($modulesStatusPath)) {
            $modulesStatus = json_decode(File::get($modulesStatusPath), true);

            // Check if Installer module is enabled
            if (isset($modulesStatus['Installer']) && $modulesStatus['Installer']) {
                return redirect()->route('setup.verify');
            }
        }

        return $next($request);
    }
}
