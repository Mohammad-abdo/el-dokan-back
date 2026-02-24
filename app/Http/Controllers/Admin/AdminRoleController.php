<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminRoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    /**
     * Display the specified role
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions', 'users');

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->update($request->only(['name', 'display_name', 'description']));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deleting admin role
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin role'
            ], 400);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned users'
            ], 400);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all permissions
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }
}


