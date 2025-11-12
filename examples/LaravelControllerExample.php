<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Events\UserProfileUpdated;
use App\Models\User;
use Drmmr763\AsyncApi\AsyncApi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

/**
 * Example Laravel Controller demonstrating AsyncAPI integration
 * 
 * This controller shows how to:
 * 1. Generate AsyncAPI specifications on-demand
 * 2. Trigger broadcast events that are documented in AsyncAPI
 * 3. Serve AsyncAPI specs to API consumers
 */
class AsyncApiController extends Controller
{
    public function __construct(
        private AsyncApi $asyncApi
    ) {
    }

    /**
     * Get the AsyncAPI specification in JSON format
     * 
     * GET /api/asyncapi.json
     */
    public function getSpecJson(): JsonResponse
    {
        try {
            $specification = $this->asyncApi->build();
            return response()->json($specification);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate AsyncAPI specification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the AsyncAPI specification in YAML format
     * 
     * GET /api/asyncapi.yaml
     */
    public function getSpecYaml(): \Illuminate\Http\Response
    {
        try {
            $yaml = $this->asyncApi->toYaml();
            
            return response($yaml, 200)
                ->header('Content-Type', 'application/x-yaml')
                ->header('Content-Disposition', 'inline; filename="asyncapi.yaml"');
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download the AsyncAPI specification as a file
     * 
     * GET /api/asyncapi/download?format=yaml
     */
    public function downloadSpec(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
    {
        $format = $request->query('format', 'yaml');
        
        try {
            $filename = storage_path("app/asyncapi-spec.{$format}");
            $this->asyncApi->exportToFile($filename, $format);
            
            return response()->download($filename, "asyncapi.{$format}")
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Render AsyncAPI documentation using AsyncAPI Studio or similar
     * 
     * GET /api/asyncapi/docs
     */
    public function renderDocs(): \Illuminate\Contracts\View\View
    {
        $specUrl = route('asyncapi.json');
        
        // You can use AsyncAPI Studio, AsyncAPI React component, or other tools
        return view('asyncapi.docs', [
            'spec_url' => $specUrl,
            'title' => 'AsyncAPI Documentation'
        ]);
    }
}

/**
 * Example User Controller showing broadcast event integration
 */
class UserController extends Controller
{
    /**
     * Register a new user and broadcast the event
     * 
     * This demonstrates how Laravel broadcast events (documented with AsyncAPI)
     * are triggered in your application.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password'])
        ]);

        // Broadcast the UserRegistered event
        // This event is documented in examples/LaravelBroadcastExample.php
        event(new UserRegistered($user, $request->ip()));

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Update user profile and broadcast the event
     */
    public function updateProfile(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'bio' => 'sometimes|string|max:1000'
        ]);

        // Track changes
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($user->{$key} !== $value) {
                $changes[$key] = [
                    'old' => $user->{$key},
                    'new' => $value
                ];
            }
        }

        $user->update($validated);

        // Broadcast the UserProfileUpdated event if there were changes
        if (!empty($changes)) {
            event(new UserProfileUpdated($user, $changes));
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'changes' => $changes
        ]);
    }
}

/**
 * Example routes for AsyncAPI endpoints
 * 
 * Add these to your routes/api.php file:
 */
class ExampleRoutes
{
    public static function register(): void
    {
        // AsyncAPI specification endpoints
        \Illuminate\Support\Facades\Route::get('/asyncapi.json', [AsyncApiController::class, 'getSpecJson'])
            ->name('asyncapi.json');
        
        \Illuminate\Support\Facades\Route::get('/asyncapi.yaml', [AsyncApiController::class, 'getSpecYaml'])
            ->name('asyncapi.yaml');
        
        \Illuminate\Support\Facades\Route::get('/asyncapi/download', [AsyncApiController::class, 'downloadSpec'])
            ->name('asyncapi.download');
        
        \Illuminate\Support\Facades\Route::get('/asyncapi/docs', [AsyncApiController::class, 'renderDocs'])
            ->name('asyncapi.docs');

        // User endpoints that trigger broadcast events
        \Illuminate\Support\Facades\Route::post('/users/register', [UserController::class, 'register'])
            ->name('users.register');
        
        \Illuminate\Support\Facades\Route::patch('/users/{user}/profile', [UserController::class, 'updateProfile'])
            ->name('users.update-profile')
            ->middleware('auth:sanctum');
    }
}

/**
 * Example Blade view for AsyncAPI documentation
 * 
 * Save this as resources/views/asyncapi/docs.blade.php:
 * 
 * <!DOCTYPE html>
 * <html>
 * <head>
 *     <title>{{ $title }}</title>
 *     <link rel="stylesheet" href="https://unpkg.com/@asyncapi/react-component@latest/styles/default.min.css">
 * </head>
 * <body>
 *     <div id="asyncapi"></div>
 *     
 *     <script src="https://unpkg.com/@asyncapi/react-component@latest/browser/standalone/index.js"></script>
 *     <script>
 *         AsyncApiStandalone.render({
 *             schema: {
 *                 url: '{{ $spec_url }}'
 *             },
 *             config: {
 *                 show: {
 *                     sidebar: true,
 *                     errors: true
 *                 }
 *             }
 *         }, document.getElementById('asyncapi'));
 *     </script>
 * </body>
 * </html>
 */

/**
 * Example middleware to cache AsyncAPI spec generation
 */
class CacheAsyncApiSpec
{
    public function handle(Request $request, \Closure $next)
    {
        $cacheKey = 'asyncapi_spec_' . $request->path();
        $cacheTtl = config('asyncapi.cache.ttl', 3600);

        if (config('asyncapi.cache.enabled', true)) {
            $response = cache()->remember($cacheKey, $cacheTtl, function () use ($next, $request) {
                return $next($request);
            });
        } else {
            $response = $next($request);
        }

        return $response;
    }
}

/**
 * Example Artisan command to generate and save AsyncAPI spec
 * 
 * Usage:
 * php artisan asyncapi:generate --output=public/asyncapi.yaml
 * 
 * This makes your AsyncAPI spec available at:
 * https://yourapp.com/asyncapi.yaml
 */
class GenerateAsyncApiCommand extends \Illuminate\Console\Command
{
    protected $signature = 'app:generate-asyncapi-spec';
    protected $description = 'Generate AsyncAPI specification and save to public directory';

    public function handle(AsyncApi $asyncApi): int
    {
        $this->info('Generating AsyncAPI specification...');

        try {
            // Generate both JSON and YAML versions
            $asyncApi->exportToFile(public_path('asyncapi.json'), 'json');
            $asyncApi->exportToFile(public_path('asyncapi.yaml'), 'yaml');

            $this->info('✓ AsyncAPI specification generated successfully!');
            $this->line('  JSON: ' . url('asyncapi.json'));
            $this->line('  YAML: ' . url('asyncapi.yaml'));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate AsyncAPI specification: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

