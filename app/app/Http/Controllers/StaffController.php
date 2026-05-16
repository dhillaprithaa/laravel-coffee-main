<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\RoleType;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'staff');
    }

    /**
     * Display a listing of staff.
     */
    public function index(): View
    {
        return view('admin.staff.index', [
            'staffList' => User::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new staff.
     */
    public function create(): View
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->except('password'),
            'password' => bcrypt($request->password),
            'role' => RoleType::STAFF,
        ]);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff {$request->name} berhasil ditambahkan!");
    }

    /**
     * Show the form for editing a staff.
     */
    public function edit(User $staff): View
    {
        return view('admin.staff.edit', [
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified staff.
     */
    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $data = $request->safe()->except('password');
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $staff->update($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff {$staff->name} berhasil diperbarui!");
    }

    /**
     * Remove the specified staff.
     */
    public function destroy(User $staff): RedirectResponse
    {
        $name = $staff->name;
        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff {$name} berhasil dihapus!");
    }
}
