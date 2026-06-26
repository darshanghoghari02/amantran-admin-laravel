<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadApiController extends Controller
{
    /**
     * POST /api/uploads/single
     * Upload a single file.
     */
    public function uploadSingle(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No file uploaded.'], 400);
            }

            $file = $request->file('file');
            
            // Determine type and folder
            $type = $request->query('type', 'general');
            // Normalize to plural form
            $typeMap = [
                'template' => 'templates',
                'category' => 'categories',
                'user' => 'users',
                'font' => 'fonts',
                'logo' => 'logos',
            ];
            $type = $typeMap[$type] ?? $type;
            
            $allowedTypes = ['templates', 'categories', 'users', 'fonts', 'logos', 'qr', 'general'];
            if (!in_array($type, $allowedTypes)) {
                $type = 'general';
            }

            // Simple validation
            $extension = strtolower($file->getClientOriginalExtension());
            if ($type === 'fonts') {
                if (!in_array($extension, ['ttf', 'otf', 'woff', 'woff2'])) {
                    return response()->json(['error' => 'Only font files (ttf, otf, woff, woff2) are allowed.'], 400);
                }
            } else {
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    return response()->json(['error' => 'Only image files (jpg, jpeg, png, gif, svg, webp) are allowed.'], 400);
                }
            }

            // Generate clean filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = Str::slug($originalName) . '_' . time() . '.' . $extension;

            $categorySlug = $request->query('categorySlug') ?: $request->query('category') ?: 'general';
            if (str_starts_with($categorySlug, 'cat_') || !empty($categorySlug)) {
                $category = \Illuminate\Support\Facades\DB::table('categories')
                    ->where('id', $categorySlug)
                    ->first();
                if ($category) {
                    $catData = json_decode($category->data, true);
                    if (!empty($catData['slug'])) {
                        $categorySlug = $catData['slug'];
                    }
                }
            }
            $categorySlug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $categorySlug));

            if ($type === 'templates' || $type === 'template') {
                $templateSlug = $request->query('templateSlug') ?: 'template';
                $templateSlug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $templateSlug));
                $folderPath = 'assets/' . $categorySlug . '/' . $templateSlug;
            } elseif ($type === 'categories' || $type === 'category') {
                $folderPath = 'assets/' . $categorySlug;
            } else {
                $folderPath = 'assets/' . $type;
            }

            $fileSize = $file->getSize();
            $destinationPath = public_path($folderPath);
            $file->move($destinationPath, $cleanName);

            $webUrl = '/' . $folderPath . '/' . $cleanName;

            return response()->json([
                'success'     => true,
                'message'     => 'File uploaded successfully.',
                'filePath'    => $webUrl,
                'flutterPath' => $webUrl,
                'fileName'    => $cleanName,
                'size'        => $fileSize,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('UploadSingle Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/uploads/multiple
     * Upload multiple files.
     */
    public function uploadMultiple(Request $request)
    {
        try {
            if (!$request->hasFile('files')) {
                return response()->json(['error' => 'No files were uploaded.'], 400);
            }

            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }

            $type = $request->query('type', 'general');
            // Normalize to plural form
            $typeMap = [
                'template' => 'templates',
                'category' => 'categories',
                'user' => 'users',
                'font' => 'fonts',
                'logo' => 'logos',
            ];
            $type = $typeMap[$type] ?? $type;
            
            $allowedTypes = ['templates', 'categories', 'users', 'fonts', 'logos', 'qr', 'general'];
            if (!in_array($type, $allowedTypes)) {
                $type = 'general';
            }

            $uploadedFiles = [];
            $errors = [];

            foreach ($files as $file) {
                try {
                    $extension = strtolower($file->getClientOriginalExtension());
                    
                    if ($type === 'fonts') {
                        if (!in_array($extension, ['ttf', 'otf', 'woff', 'woff2'])) {
                            throw new \Exception('Invalid font type: ' . $extension);
                        }
                    } else {
                        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                            throw new \Exception('Invalid image type: ' . $extension);
                        }
                    }

                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanName = Str::slug($originalName) . '_' . time() . '.' . $extension;

                    $categorySlug = $request->query('categorySlug') ?: $request->query('category') ?: 'general';
                    if (str_starts_with($categorySlug, 'cat_') || !empty($categorySlug)) {
                        $category = \Illuminate\Support\Facades\DB::table('categories')
                            ->where('id', $categorySlug)
                            ->first();
                        if ($category) {
                            $catData = json_decode($category->data, true);
                            if (!empty($catData['slug'])) {
                                $categorySlug = $catData['slug'];
                            }
                        }
                    }
                    $categorySlug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $categorySlug));

                    if ($type === 'templates' || $type === 'template') {
                        $templateSlug = $request->query('templateSlug') ?: 'template';
                        $templateSlug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $templateSlug));
                        $folderPath = 'assets/' . $categorySlug . '/' . $templateSlug;
                    } elseif ($type === 'categories' || $type === 'category') {
                        $folderPath = 'assets/' . $categorySlug;
                    } else {
                        $folderPath = 'assets/' . $type;
                    }

                    $fileSize = $file->getSize();
                    $destinationPath = public_path($folderPath);
                    $file->move($destinationPath, $cleanName);
                    $webUrl = '/' . $folderPath . '/' . $cleanName;

                    $uploadedFiles[] = [
                        'filePath'    => $webUrl,
                        'flutterPath' => $webUrl,
                        'fileName'    => $cleanName,
                        'size'        => $fileSize,
                    ];
                } catch (\Exception $uploadErr) {
                    $errors[] = [
                        'fileName' => $file->getClientOriginalName(),
                        'error'    => $uploadErr->getMessage(),
                    ];
                }
            }

            if (empty($uploadedFiles) && !empty($errors)) {
                return response()->json([
                    'error'   => 'All file uploads failed.',
                    'details' => $errors,
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' files uploaded successfully!',
                'files'   => $uploadedFiles,
                'errors'  => !empty($errors) ? $errors : null,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('UploadMultiple Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/uploads
     * Delete an uploaded file.
     */
    public function deleteFile(Request $request)
    {
        try {
            $request->validate([
                'filePath' => 'required',
            ]);

            $filePath = $request->filePath;
            $deleted = false;

            $path = parse_url($filePath, PHP_URL_PATH);
            if ($path) {
                if (str_starts_with($path, '/storage/')) {
                    $relativePath = substr($path, 9); // strip '/storage/'
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                        $deleted = true;
                    }
                } else {
                    $fullPath = public_path(ltrim($path, '/'));
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        unlink($fullPath);
                        $deleted = true;
                    }
                }
            }

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "File at {$filePath} deleted successfully.",
                ]);
            }

            return response()->json(['error' => "File at {$filePath} does not exist or could not be deleted."], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/uploads/status
     */
    public function status()
    {
        return response()->json([
            'cloudinaryConfigured' => false,
            'message'              => '✅ Local disk storage is configured and ready for uploads.',
        ]);
    }
}
