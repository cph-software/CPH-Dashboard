@forelse ($configuration->details as $pos)
   @php
      $tyre = $tyres[$pos->id] ?? null;
      $index = $loop->index;
   @endphp
   <tr>
      <td class="pos-code">{{ $pos->position_code }}</td>
      <td>
         <input type="hidden" name="details[{{ $index }}][position_id]" value="{{ $pos->id }}">
         @if ($tyre)
            <input type="hidden" name="details[{{ $index }}][tyre_id]" value="{{ $tyre->id }}">
            {{ $tyre->brand->brand_name ?? '-' }}
         @else
            <span class="text-muted small">Kosong</span>
         @endif
      </td>
      <td>{{ $tyre->pattern->name ?? '-' }}</td>
      <td>
         {{ $tyre->size->size ?? '-' }}
         @if ($tyre && $tyre->size && $tyre->size->ply_rating)
            / {{ $tyre->size->ply_rating }} PR
         @endif
      </td>
      <td class="fw-bold">{{ $tyre->serial_number ?? '-' }}</td>
      <td>
         <input type="number" step="0.1" name="details[{{ $index }}][psi]"
            class="form-control form-control-sm text-center" placeholder="100"
            @if (!$tyre) disabled @endif>
      </td>
      <td>
         <input type="number" step="0.1" name="details[{{ $index }}][rtd_1]"
            class="form-control form-control-sm text-center" placeholder="0"
            @if (!$tyre) disabled @endif>
      </td>
      <td>
         <input type="number" step="0.1" name="details[{{ $index }}][rtd_2]"
            class="form-control form-control-sm text-center" placeholder="0"
            @if (!$tyre) disabled @endif>
      </td>
      <td>
         <input type="number" step="0.1" name="details[{{ $index }}][rtd_3]"
            class="form-control form-control-sm text-center" placeholder="0"
            @if (!$tyre) disabled @endif>
      </td>
      <td>
         <select name="details[{{ $index }}][remarks]" class="form-select select2-remarks"
            data-placeholder="Keterangan..." @if (!$tyre) disabled @endif>
            <option value=""></option>
            <option value="Pasang">Pasang</option>
            <option value="Pindah">Pindah</option>
            <option value="Lepas">Lepas</option>
            <option value="Tergores">Tergores</option>
            <option value="Kembung">Kembung</option>
            <option value="Pecah">Pecah</option>
            <option value="Sobek">Sobek</option>
            <option value="Tertusuk">Tertusuk</option>
            <option value="Telapak Lepas">Telapak Lepas</option>
         </select>
      </td>
   </tr>
@empty
   <tr>
      <td colspan="10" class="text-center py-5 text-warning">
         <i class="ri-error-warning-line ri-2x mb-2"></i>
         <p>Konfigurasi axle untuk unit ini tidak ditemukan.</p>
      </td>
   </tr>
@endforelse
