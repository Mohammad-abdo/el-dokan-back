<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminShopDocumentController extends Controller
{
    private static function fullFileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }

    public function index(Shop $shop): JsonResponse
    {
        $documents = $shop->documents()->latest()->get()->map(function ($d) {
            $d->file_url = $d->file_url ? self::fullFileUrl($d->file_url) : null;
            return $d;
        });
        return response()->json(['success' => true, 'data' => $documents]);
    }

    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:permit,license,tax_card,commercial_register,other',
            'title' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'reference_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_verified' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['shop_id'] = $shop->id;
        unset($data['file']);
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $request->file('file')->store('uploads/shop_documents', 'public');
            $data['file_url'] = Storage::url($path);
        }
        if (empty($data['file_url']) && $request->input('file_url')) {
            $data['file_url'] = $request->input('file_url');
        }
        $doc = ShopDocument::create($data);
        $doc->file_url = $doc->file_url ? self::fullFileUrl($doc->file_url) : null;
        return response()->json(['success' => true, 'data' => $doc], 201);
    }

    public function update(Request $request, Shop $shop, ShopDocument $document): JsonResponse
    {
        if ($document->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Document not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|string|in:permit,license,tax_card,commercial_register,other',
            'title' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'reference_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_verified' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        unset($data['file']);
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $request->file('file')->store('uploads/shop_documents', 'public');
            $data['file_url'] = Storage::url($path);
        }
        $document->update($data);
        $document->refresh();
        $document->file_url = $document->file_url ? self::fullFileUrl($document->file_url) : null;
        return response()->json(['success' => true, 'data' => $document]);
    }

    public function destroy(Shop $shop, ShopDocument $document): JsonResponse
    {
        if ($document->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Document not found'], 404);
        }
        $document->delete();
        return response()->json(['success' => true, 'message' => 'Document deleted']);
    }
}
