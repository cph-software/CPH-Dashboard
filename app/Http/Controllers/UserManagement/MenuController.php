<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\AplikasiService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use ResponseTrait;

    protected $menuService;
    protected $aplikasiService;

    public function __construct(MenuService $menuService, AplikasiService $aplikasiService)
    {
        $this->menuService = $menuService;
        $this->aplikasiService = $aplikasiService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $menus = \App\Models\Menu::with(['aplikasi'])->orderBy('aplikasi_id')->orderBy('name')->get();
        $aplikasi = $this->aplikasiService->getAll();

        return view('user-management.menus.index', compact('menus', 'aplikasi'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'aplikasi_id' => 'required',
            'name' => 'required',
            'url' => 'required'
        ]);

        $this->menuService->store([
            'aplikasi_id' => $request->aplikasi_id,
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon ?: 'ri-circle-line',
        ]);

        setLogActivity(auth()->id(), 'Menambah menu baru: ' . $request->name, [
            'action_type' => 'create',
            'module' => 'Menus',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Menu created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $menu = $this->menuService->getById($id);
        return response()->json($menu);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'aplikasi_id' => 'required',
            'name' => 'required',
            'url' => 'required'
        ]);

        $menuBefore = \App\Models\Menu::find($id);

        $this->menuService->update($id, [
            'aplikasi_id' => $request->aplikasi_id,
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon ?: 'ri-circle-line',
        ]);

        setLogActivity(auth()->id(), 'Memperbarui menu: ' . $request->name, [
            'action_type' => 'update',
            'module' => 'Menus',
            'data_before' => $menuBefore ? $menuBefore->toArray() : null,
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Menu updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        setLogActivity(auth()->id(), 'Menghapus menu ID: ' . $id, [
            'action_type' => 'delete',
            'module' => 'Menus'
        ]);

        $this->menuService->delete($id);
        return redirect()->back()->with('success', 'Menu deleted successfully');
    }
}
