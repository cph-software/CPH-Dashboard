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
        $menus = \App\Models\Menu::with(['aplikasi', 'parent'])->orderBy('aplikasi_id')->orderBy('order_no')->get();
        $aplikasi = $this->aplikasiService->getAll();
        $parentMenus = \App\Models\Menu::whereNull('parent_id')->get();

        return view('user-management.menus.index', compact('menus', 'aplikasi', 'parentMenus'));
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
            'parent_id' => $request->parent_id ?: null,
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon ?: 'ri-circle-line',
            'order_no' => $request->order_no ?: 0,
            'is_active' => true,
            'is_header' => $request->has('is_header')
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

        $this->menuService->update($id, [
            'aplikasi_id' => $request->aplikasi_id,
            'parent_id' => $request->parent_id ?: null,
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon ?: 'ri-circle-line',
            'order_no' => $request->order_no ?: 0,
            'is_header' => $request->has('is_header')
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
        $this->menuService->delete($id);
        return redirect()->back()->with('success', 'Menu deleted successfully');
    }
}
