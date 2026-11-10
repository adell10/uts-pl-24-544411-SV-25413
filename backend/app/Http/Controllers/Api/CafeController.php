<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CafeRequest;
use App\Http\Resources\CafeResource;
use App\Models\Cafe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class CafeController extends Controller
{
    /**
     * GET /api/cafes → semua kafe milik user yang login.
     * Filter opsional: ?status=visited|wishlist&work_friendly=1&q=seduh
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', Cafe::STATUSES)],
            'work_friendly' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $cafes = $request->user()->cafes()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->boolean('work_friendly'), fn ($q) => $q->where('work_friendly', true))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->input('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('area', 'like', $term));
            })
            ->latest()
            ->get();

        return CafeResource::collection($cafes);
    }

    /** POST /api/cafes → 201 */
    public function store(CafeRequest $request): JsonResponse
    {
        $cafe = $request->user()->cafes()->create($request->validated())->refresh();

        // Hanya ID & status — tidak mencatat nama, catatan, atau data pribadi
        Log::info('Kafe berhasil disimpan', [
            'event' => 'cafe.created',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'menambah kafe',
            'endpoint' => 'POST /api/cafes',
            'cafe_id' => $cafe->id,
            'cafe_status' => $cafe->status,
            'reason' => 'data kafe lolos validasi dan tersimpan',
        ]);

        return (new CafeResource($cafe))
            ->additional(['message' => 'Kafe tersimpan.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/cafes/{id} → 200, 404 kalau tidak ada / bukan miliknya */
    public function show(Request $request, string $id): CafeResource
    {
        return new CafeResource($request->user()->cafes()->findOrFail($id));
    }

    /** PUT/PATCH /api/cafes/{id} → 200 */
    public function update(CafeRequest $request, string $id): CafeResource
    {
        $cafe = $request->user()->cafes()->findOrFail($id);
        $cafe->update($request->validated());

        // Yang dicatat hanya NAMA field yang berubah, bukan isinya
        Log::info('Kafe berhasil diperbarui', [
            'event' => 'cafe.updated',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'mengubah kafe',
            'endpoint' => 'PUT /api/cafes/{id}',
            'cafe_id' => $cafe->id,
            'changed_fields' => array_values(array_diff(array_keys($cafe->getChanges()), ['updated_at'])),
            'reason' => 'data perubahan lolos validasi dan tersimpan',
        ]);

        return (new CafeResource($cafe->refresh()))->additional(['message' => 'Kafe diperbarui.']);
    }

    /** DELETE /api/cafes/{id} → 200 */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $cafe = $request->user()->cafes()->findOrFail($id);
        $cafe->delete();

        Log::info('Kafe berhasil dihapus', [
            'event' => 'cafe.deleted',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'menghapus kafe',
            'endpoint' => 'DELETE /api/cafes/{id}',
            'cafe_id' => $cafe->id,
            'reason' => 'user meminta penghapusan kafe miliknya',
        ]);

        return response()->json(['message' => 'Kafe dihapus.']);
    }
}