<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    /**
     * Create a new module for a course.
     */
    public function create(Course $course, array $data): Module
    {
        $order = $data['order'] ?? ($course->modules()->max('order') + 1);

        return $course->modules()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $order,
        ]);
    }

    /**
     * Update an existing module.
     */
    public function update(Module $module, array $data): Module
    {
        $module->update([
            'title' => $data['title'] ?? $module->title,
            'description' => $data['description'] ?? $module->description,
            'order' => $data['order'] ?? $module->order,
        ]);

        return $module->fresh();
    }

    /**
     * Reorder modules within a course.
     * 
     * @param Course $course
     * @param array $orderedIds Array of module IDs in the new order
     */
    public function reorder(Course $course, array $orderedIds): void
    {
        DB::transaction(function () use ($course, $orderedIds) {
            foreach ($orderedIds as $index => $moduleId) {
                $course->modules()->where('id', $moduleId)->update(['order' => $index + 1]);
            }
        });
    }

    /**
     * Delete a module and all its contents.
     */
    public function delete(Module $module): bool
    {
        return $module->delete();
    }

    /**
     * Duplicate a module with all its lessons.
     */
    public function duplicate(Module $module): Module
    {
        return DB::transaction(function () use ($module) {
            $newModule = $module->replicate();
            $newModule->title = $module->title . ' (Copy)';
            $newModule->order = $module->course->modules()->max('order') + 1;
            $newModule->save();

            foreach ($module->lessons as $lesson) {
                $newLesson = $lesson->replicate();
                $newLesson->module_id = $newModule->id;
                $newLesson->save();
            }

            return $newModule;
        });
    }
}
