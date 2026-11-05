<?php

namespace App\Http\Controllers;

use App\Exports\BarangMasukExport;
use App\Http\Requests\BarangMasukRequest;
use App\Services\BarangMasukService;
use App\Models\BarangMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class BarangMasukController extends Controller
{

    private const FILTERS = ['category_id', 'sub_category_id', 'year', 'search', 'sort_by', 'sort_dir'];

    public function __construct(private BarangMasukService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->paginate(
            $request->only(self::FILTERS),
            $request->user()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BarangMasukRequest $request)
    {
        $result = $this->service->create($request->validated(), $request->file('lampiran'));

        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BarangMasuk $barangMasuk)
    {
        Gate::authorize('view', $barangMasuk);

        return $this->service->show($barangMasuk);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BarangMasukRequest $request, BarangMasuk $barangMasuk)
    {
        Gate::authorize('update', $barangMasuk);

        return $this->service->update($barangMasuk, $request->validated(), $request->file('lampiran'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BarangMasuk $barangMasuk)
    {
        Gate::authorize('delete', $barangMasuk);

        $this->service->delete($barangMasuk);

        return response()->noContent();
    }

    public function verify(BarangMasuk $barangMasuk)
    {
        return $this->service->toggleVerify($barangMasuk);
    }

    public function export(Request $request)
    {
        $rows = $this->service->exportRows($request->only(self::FILTERS), $request->user());

        return Excel::download(new BarangMasukExport($rows), 'barang-masuk.xlsx');
    }

    public function print(BarangMasuk $barangMasuk)
    {
        Gate::authorize('view', $barangMasuk);

        return Pdf::loadView('pdf.surat-masuk', ['barangMasuk' => $this->service->show($barangMasuk)])
            ->setPaper('a4')
            ->stream("surat-masuk-{$barangMasuk->id}.pdf");
    }
}
