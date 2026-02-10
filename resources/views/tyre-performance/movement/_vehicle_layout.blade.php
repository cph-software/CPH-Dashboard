<div class="v-chassis">
   <div class="v-cabin">FRONT</div>

   @php
      $frontAxles = $configuration->details->where('axle_type', 'Front')->groupBy('axle_number');
      $middleAxles = $configuration->details->where('axle_type', 'Middle')->groupBy('axle_number');
      $rearAxles = $configuration->details->where('axle_type', 'Rear')->groupBy('axle_number');
      $spares = $configuration->details->where('is_spare', true);
   @endphp

   {{-- Front Axles --}}
   @foreach ($frontAxles as $positions)
      <div class="v-axle">
         @php
            $left = $positions->where('side', 'Left')->first();
            $right = $positions->where('side', 'Right')->first();
            $leftTyre = $assignedTyres->get($left->id) ?? null;
            $rightTyre = $assignedTyres->get($right->id) ?? null;
         @endphp
         <div class="v-tyre front m-tyre-node {{ $leftTyre ? 'filled' : 'empty' }}" data-position-id="{{ $left->id }}"
            data-code="{{ $left->position_code }}" data-name="{{ $left->position_name }}"
            data-sn="{{ $leftTyre->serial_number ?? '' }}"
            title="{{ $left->position_name }} {{ $leftTyre ? '[' . $leftTyre->serial_number . ']' : '(Kosong)' }}">
            <span class="v-tyre-code">{{ $left->position_code }}</span>
            @if ($leftTyre)
               <span class="v-tyre-sn-hint">{{ substr($leftTyre->serial_number, -4) }}</span>
            @endif
         </div>
         <div class="v-tyre front m-tyre-node {{ $rightTyre ? 'filled' : 'empty' }}"
            data-position-id="{{ $right->id }}" data-code="{{ $right->position_code }}"
            data-name="{{ $right->position_name }}" data-sn="{{ $rightTyre->serial_number ?? '' }}"
            title="{{ $right->position_name }} {{ $rightTyre ? '[' . $rightTyre->serial_number . ']' : '(Kosong)' }}">
            <span class="v-tyre-code">{{ $right->position_code }}</span>
            @if ($rightTyre)
               <span class="v-tyre-sn-hint">{{ substr($rightTyre->serial_number, -4) }}</span>
            @endif
         </div>
      </div>
   @endforeach

   {{-- Middle Axles --}}
   @foreach ($middleAxles as $positions)
      <div class="v-axle">
         <div class="v-group">
            @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
               @php
                  $t = $assignedTyres->get($p->id) ?? null;
               @endphp
               <div class="v-tyre middle m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}"
                  data-name="{{ $p->position_name }}" data-sn="{{ $t->serial_number ?? '' }}"
                  title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                  <span class="v-tyre-code">{{ $p->position_code }}</span>
                  @if ($t)
                     <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                  @endif
               </div>
            @endforeach
         </div>
         <div class="v-group">
            @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
               @php
                  $t = $assignedTyres->get($p->id) ?? null;
               @endphp
               <div class="v-tyre middle m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}"
                  data-name="{{ $p->position_name }}" data-sn="{{ $t->serial_number ?? '' }}"
                  title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                  <span class="v-tyre-code">{{ $p->position_code }}</span>
                  @if ($t)
                     <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                  @endif
               </div>
            @endforeach
         </div>
      </div>
   @endforeach

   {{-- Rear Axles --}}
   @foreach ($rearAxles as $positions)
      <div class="v-axle">
         <div class="v-group">
            @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
               @php $t = $assignedTyres->get($p->id); @endphp
               <div class="v-tyre rear m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}"
                  data-name="{{ $p->position_name }}" data-sn="{{ $t->serial_number ?? '' }}"
                  title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                  <span class="v-tyre-code">{{ $p->position_code }}</span>
                  @if ($t)
                     <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                  @endif
               </div>
            @endforeach
         </div>
         <div class="v-group">
            @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
               @php $t = $assignedTyres->get($p->id); @endphp
               <div class="v-tyre rear m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}"
                  data-name="{{ $p->position_name }}" data-sn="{{ $t->serial_number ?? '' }}"
                  title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                  <span class="v-tyre-code">{{ $p->position_code }}</span>
                  @if ($t)
                     <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                  @endif
               </div>
            @endforeach
         </div>
      </div>
   @endforeach

   {{-- Spares --}}
   @if ($spares->count() > 0)
      <div class="v-spare-list">
         @foreach ($spares as $s)
            @php $t = $assignedTyres->get($s->id); @endphp
            <div class="v-tyre spare m-tyre-node {{ $t ? 'filled' : 'empty' }}" data-position-id="{{ $s->id }}"
               data-code="{{ $s->position_code }}" data-name="{{ $s->position_name }}"
               data-sn="{{ $t->serial_number ?? '' }}"
               title="{{ $s->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
               <span class="v-tyre-code">{{ $s->position_code }}</span>
               @if ($t)
                  <span class="v-tyre-sn-hint"
                     style="bottom: -15px; left: 0; width: 100%;">{{ substr($t->serial_number, -4) }}</span>
               @endif
            </div>
         @endforeach
      </div>
   @endif
</div>

<style>
   .v-chassis {
      position: relative;
      width: 100%;
      max-width: 350px;
      margin: 0 auto;
      background: #fafafa;
      border-radius: 20px;
      padding: 40px 20px;
      border: 2px solid #eee;
      box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.02);
   }

   .v-cabin {
      width: 100px;
      height: 45px;
      background: #333;
      margin: 0 auto 30px auto;
      border-radius: 8px 8px 4px 4px;
      border-bottom: 5px solid #111;
      text-align: center;
      line-height: 40px;
      font-size: 11px;
      color: #fff;
      font-weight: bold;
      letter-spacing: 2px;
   }

   .v-axle {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      position: relative;
   }

   .v-axle::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 65%;
      height: 4px;
      background: #e0e0e0;
      z-index: 1;
      border-radius: 2px;
   }

   .v-tyre {
      width: 32px;
      height: 55px;
      background: #fff;
      border: 2px solid #ddd;
      border-radius: 6px;
      z-index: 2;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
   }

   .v-tyre-code {
      font-size: 9px;
      font-weight: 800;
      color: #666;
   }

   .v-tyre-sn-hint {
      position: absolute;
      bottom: -18px;
      font-size: 8px;
      color: #7367f0;
      white-space: nowrap;
      font-weight: bold;
      opacity: 0;
      transition: opacity 0.3s;
   }

   .v-tyre:hover .v-tyre-sn-hint {
      opacity: 1;
   }

   .v-tyre.filled {
      background: #2d2d2d !important;
      border-color: #1a1a1a;
   }

   .v-tyre.filled .v-tyre-code {
      color: #fff;
   }

   .v-tyre.front {
      border-top: 4px solid #ff9f43;
   }

   .v-tyre.rear {
      border-top: 4px solid #28c76f;
   }

   .v-tyre.middle {
      border-top: 4px solid #7367f0;
   }

   .v-tyre:hover {
      transform: scale(1.15) translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      z-index: 10;
   }

   .v-tyre.selected {
      border-color: #7367f0 !important;
      box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.3);
      animation: pulse-select 1.5s infinite;
   }

   @keyframes pulse-select {
      0% {
         box-shadow: 0 0 0 0px rgba(115, 103, 240, 0.4);
      }

      70% {
         box-shadow: 0 0 0 10px rgba(115, 103, 240, 0);
      }

      100% {
         box-shadow: 0 0 0 0px rgba(115, 103, 240, 0);
      }
   }

   .v-group {
      display: flex;
      gap: 5px;
   }

   .v-spare-list {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
      padding-top: 20px;
      border-top: 2px dashed #eee;
   }

   .v-tyre.spare {
      width: 55px;
      height: 32px;
      border-top: none;
      border-right: 4px solid #00cfe8;
   }

   /* Animation Classes for interactions */
   .tyre-appearing {
      animation: tyre-in 0.5s ease-out forwards;
   }

   .tyre-disappearing {
      animation: tyre-out 0.5s ease-in forwards;
   }

   @keyframes tyre-in {
      from {
         transform: scale(0);
         opacity: 0;
      }

      to {
         transform: scale(1);
         opacity: 1;
      }
   }

   @keyframes tyre-out {
      from {
         transform: scale(1);
         opacity: 1;
      }

      to {
         transform: scale(1.5);
         opacity: 0;
      }
   }
</style>
