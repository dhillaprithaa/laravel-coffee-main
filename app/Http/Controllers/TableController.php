<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\View\View;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreTableRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Table::class);
    }

    /**
     * Display a listing of tables.
     */
    public function index(): View
    {
        $tables = Table::query()->orderBy('number', 'asc')->get();

        return view('admin.tables', [
            'tables' => $tables,
        ]);
    }

    /**
     * Store a newly created table.
     */
    public function store(StoreTableRequest $request): RedirectResponse
    {
        Table::create([
            'number' => $request->number
        ]);

        return back()->with('success', "Meja {$request->number} berhasil ditambahkan!");
    }

    /**
     * Remove the specified table.
     */
    public function destroy(Table $table): RedirectResponse
    {
        $number = $table->number;
        $table->delete();

        return back()->with('success', "Meja {$number} berhasil dihapus!");
    }

    /**
     * Generate a single PDF containing QR codes for all tables.
     */
    public function generate()
    {
        $tables = Table::query()->orderBy('number', 'asc')->get()->map(function (Table $table) {
            $url = route('selforder.show', $table->number);
            $qrcode = QrCode::format('svg')->size(180)->margin(1)->color(26, 10, 0)->generate($url);

            return [
                'url' => $url,
                'qrcode' => $qrcode,
                'number' => $table->number,
            ];
        });

        return Pdf::view('admin.qrcode.all', ['tables' => $tables])
            ->format(Format::A4)
            ->download('table-codes.pdf');
    }

    /**
     * Show the print view for a table's QR code.
     */
    public function show(Table $table): View
    {
        return view('admin.qrcode.single', [
            'table' => $table,
            'url' => route('selforder.show', $table->number),
        ]);
    }
}
