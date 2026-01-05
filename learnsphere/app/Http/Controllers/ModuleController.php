<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ModuleController extends Controller
{
    use AuthorizesRequests;

    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $module = $this->moduleService->create($course, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Module created successfully',
            'module' => $module,
        ]);
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module->course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $module = $this->moduleService->update($module, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Module updated successfully',
            'module' => $module,
        ]);
    }

    /**
     * Reorder modules within a course.
     */
    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:modules,id',
        ]);

        $this->moduleService->reorder($course, $validated['order']);

        return response()->json([
            'success' => true,
            'message' => 'Modules reordered successfully',
        ]);
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Module $module)
    {
        $this->authorize('update', $module->course);

        $this->moduleService->delete($module);

        return response()->json([
            'success' => true,
            'message' => 'Module deleted successfully',
        ]);
    }

    /**
     * Duplicate a module.
     */
    public function duplicate(Module $module)
    {
        $this->authorize('update', $module->course);

        $newModule = $this->moduleService->duplicate($module);

        return response()->json([
            'success' => true,
            'message' => 'Module duplicated successfully',
            'module' => $newModule,
        ]);
    }
}
