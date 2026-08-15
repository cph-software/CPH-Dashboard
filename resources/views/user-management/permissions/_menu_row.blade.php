@php
   $indentStyle = isset($level) && $level == 1 ? 'padding-left: 2.5rem !important;' : (isset($level) && $level == 2 ? 'padding-left: 4rem !important;' : '');
@endphp
<tr class="menu-row" data-app-id="{{ $menu->aplikasi_id }}" data-menu-id="{{ $menu->id }}">
   <td class="ps-4" style="{{ $indentStyle }}">
      <div class="d-flex align-items-center">
         @if(isset($level) && $level > 0)
            <i class="ri-corner-down-right-line me-2 text-muted" style="font-size: 0.9rem;"></i>
         @endif
         <input class="form-check-input menu-main-check me-2" type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
            id="menuCheck_{{ $menu->id }}">
         <i class="icon-base ri {{ $menu->icon ?: 'ri-circle-fill' }} me-2 {{ isset($level) && $level > 0 ? 'text-secondary' : 'text-primary' }}"></i>
         <label class="fw-bold text-heading mb-0" for="menuCheck_{{ $menu->id }}"
            style="cursor: pointer">{{ $menu->name }}</label>
      </div>
   </td>
   @foreach (['view', 'create', 'update', 'delete', 'export', 'import'] as $perm)
      <td class="text-center">
         <div class="form-check d-inline-block">
            <input class="form-check-input perm-check perm-{{ $perm }} menu-perm-{{ $menu->id }}"
               type="checkbox" name="menu_permissions[{{ $menu->id }}][]" value="{{ $perm }}"
               id="{{ $perm }}_{{ $menu->id }}">
         </div>
      </td>
   @endforeach
</tr>
