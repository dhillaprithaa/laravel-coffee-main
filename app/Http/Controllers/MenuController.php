<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Requests\UpdateMenuStockRequest;

class MenuController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Menu::class);
    }

    /**
     * Display a listing of menus.
     */
    public function index(): View
    {
        $menus = Menu::paginate(10);

        return view('admin.menus.index', [
            'menus' => $menus,
        ]);
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): View
    {
        return view('admin.menus.create');
    }

    /**
     * Store a newly created menu.
     */
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = Menu::create($request->validated());

        return redirect()->route('admin.menus.index')
            ->with('success', "Menu {$menu->name} berhasil ditambahkan!");
    }

    /**
     * Show the form for editing a menu.
     */
    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', ['menu' => $menu]);
    }

    /**
     * Update the specified menu.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        return redirect()
            ->route('admin.menus.index')
            ->with('success', "Menu {$menu->name} berhasil diperbarui!");
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $name = $menu->name;
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', "Menu {$name} berhasil dihapus!");
    }

    /**
     * Update the stock of a menu item.
     */
    public function restock(UpdateMenuStockRequest $request, Menu $menu): JsonResponse
    {
        $menu->update([
            'stock' => $request->stock,
        ]);

        return response()->json(['success' => true, 'stock' => $menu->stock]);
    }
}
