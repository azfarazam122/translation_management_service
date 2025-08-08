<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Services\TranslationCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TranslationController extends Controller
{
    protected $translationCacheService;
    
    public function __construct(TranslationCacheService $translationCacheService)
    {
        $this->translationCacheService = $translationCacheService;
    }

    /**
     * Display a listing of translations with optional filtering.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Translation::query();

        // Filter by key
        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->key . '%');
        }

        // Filter by locale
        if ($request->has('locale')) {
            $query->where('locale', $request->locale);
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->where('tag', $request->tag);
        }

        // Filter by content
        if ($request->has('content')) {
            $query->where('value', 'like', '%' . $request->content . '%');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $translations = $query->paginate($perPage);

        return response()->json($translations);
    }

    /**
     * Store a newly created translation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'tag' => 'required|string|max:50',
            'value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if translation already exists
        $existingTranslation = Translation::where('key', $request->key)
            ->where('locale', $request->locale)
            ->where('tag', $request->tag)
            ->first();

        if ($existingTranslation) {
            return response()->json([
                'message' => 'Translation with this key, locale, and tag already exists',
                'translation' => $existingTranslation
            ], 409);
        }

        $translation = Translation::create($request->all());

        return response()->json($translation, 201);
    }

    /**
     * Display the specified translation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        return response()->json($translation);
    }

    /**
     * Update the specified translation.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|required|string|max:255',
            'locale' => 'sometimes|required|string|max:10',
            'tag' => 'sometimes|required|string|max:50',
            'value' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if another translation with the same key, locale, and tag already exists
        if ($request->has('key') || $request->has('locale') || $request->has('tag')) {
            $key = $request->key ?? $translation->key;
            $locale = $request->locale ?? $translation->locale;
            $tag = $request->tag ?? $translation->tag;

            $existingTranslation = Translation::where('key', $key)
                ->where('locale', $locale)
                ->where('tag', $tag)
                ->where('id', '!=', $id)
                ->first();

            if ($existingTranslation) {
                return response()->json([
                    'message' => 'Another translation with this key, locale, and tag already exists',
                    'translation' => $existingTranslation
                ], 409);
            }
        }

        $translation->update($request->all());

        return response()->json($translation);
    }

    /**
     * Remove the specified translation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        $translation->delete();

        return response()->json(['message' => 'Translation deleted successfully']);
    }

    /**
     * Export translations as JSON for frontend applications.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $locale = $request->get('locale');
        $tag = $request->get('tag');
        
        // Get translations from cache service (handles lazy loading automatically)
        $translations = $this->translationCacheService->getTranslations($locale, $tag);
        
        return response()->json($translations);
    }
}
