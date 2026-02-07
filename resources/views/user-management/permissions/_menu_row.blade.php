<tr class="menu-row" data-app-id="{{ $menu->aplikasi_id }}">
   <td style="padding-left: 15px">
      <i class="ri-folder-shared-line me-2 text-primary"></i>
      <span class="fw-bold text-heading">{{ $menu->name }}</span>
   </td>
   <td class="text-center">
      <div class="form-check d-flex justify-content-center">
         <input class="form-check-input perm-checkbox menu-checkbox" type="checkbox" name="menu_ids[]"
            value="{{ $menu->id }}" id="menuCheck_{{ $menu->id }}">
      </div>
   </td>
</tr>
