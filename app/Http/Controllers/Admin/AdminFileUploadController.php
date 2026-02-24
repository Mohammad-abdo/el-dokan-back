<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminFileUploadController extends Controller
{
    /**
     * Display a listing of file uploads
     */
    public function index(): JsonResponse
    {
        $files = FileUpload::latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Upload file
     */
    public function upload(Request $request): JsonResponse
    {
        // Accept both 'file' and 'image' for flexibility
        $file = $request->file('file') ?? $request->file('image');
        
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'No file provided. Please provide either "file" or "image" field.',
                'errors' => ['file' => ['The file field is required.']]
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'sometimes|file|mimes:jpeg,jpg,png,gif,webp|max:10240', // 10MB
            'image' => 'sometimes|file|mimes:jpeg,jpg,png,gif,webp|max:10240', // 10MB
            'type' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $path = $file->store('uploads', 'public');
        $url = Storage::url($path);
        $fullUrl = str_starts_with($url, 'http') ? $url : (rtrim(config('app.url'), '/') . ($url[0] === '/' ? $url : '/' . $url));

        $fileUpload = FileUpload::create([
            'uploadable_type' => null,
            'uploadable_id' => null,
            'file_type' => $request->type ?? 'general',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_url' => $fullUrl,
            'file_size' => (string) $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $fileUpload->id,
                'url' => $fullUrl,
                'file_url' => $fullUrl,
                'original_name' => $fileUpload->file_name,
                'type' => $fileUpload->file_type,
            ]
        ], 201);
    }

    /**
     * Remove the specified file
     */
    public function destroy(FileUpload $fileUpload): JsonResponse
    {
        Storage::disk('public')->delete($fileUpload->file_path);
        $fileUpload->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }
}
