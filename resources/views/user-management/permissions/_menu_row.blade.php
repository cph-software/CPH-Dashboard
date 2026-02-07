<tr class="menu-row" data-app-id="{{ $menu->aplikasi_id }}">
   <td style="padding-left: {{ $level * 30 + 15 }}px">
      @if ($level > 0)
         <span class="text-muted">└─</span>
         <i class="ri-subtract-line mx-1 text-muted"></i>
      @else
         <i class="ri-folder-shared-line me-2 text-primary"></i>
      @endif
      <span class="{{ $level == 0 ? 'fw-bold text-heading' : '' }}">{{ $menu->name }}</span>
   </td>
   <td class="text-center">
      <div class="form-check d-flex justify-content-center">
         <input class="form-check-input perm-checkbox menu-checkbox" type="checkbox" name="menu_ids[]"
            value="{{ $menu->id }}" id="menuCheck_{{ $menu->id }}">
      </div>
   </td>
</tr>
@foreach ($menu->children()->orderBy('order_no')->get() as $child)
   @include('user-management.permissions._menu_row', ['menu' => $child, 'level' => $level + 1])
@endforeach
