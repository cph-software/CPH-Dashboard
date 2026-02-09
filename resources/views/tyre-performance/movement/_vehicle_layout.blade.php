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
            data-code="{{ $left->position_code }}" data-sn="{{ $leftTyre->serial_number ?? '' }}">
            {{ $left->position_code }}
         </div>
         <div class="v-tyre front m-tyre-node {{ $rightTyre ? 'filled' : 'empty' }}"
            data-position-id="{{ $right->id }}" data-code="{{ $right->position_code }}"
            data-sn="{{ $rightTyre->serial_number ?? '' }}">
            {{ $right->position_code }}
         </div>
      </div>
   @endforeach

   @endforeach

   {{-- Middle Axles (Assume Dual for visual consistency with heavy duty) --}}
   @foreach ($middleAxles as $positions)
      <div class="v-axle">
         <div class="v-group">
            @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
               @php
                  $t = $assignedTyres->get($p->id) ?? null;
               @endphp
               <div class="v-tyre middle m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}">
                  {{ $p->position_code }}
               </div>
            @endforeach
         </div>
         <div class="v-group">
            @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
               @php
                  $t = $assignedTyres->get($p->id) ?? null;
               @endphp
               <div class="v-tyre middle m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}">
                  {{ $p->position_code }}
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
                  data-sn="{{ $t->serial_number ?? '' }}">
                  {{ $p->position_code }}
               </div>
            @endforeach
         </div>
         <div class="v-group">
            @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
               @php $t = $assignedTyres->get($p->id); @endphp
               <div class="v-tyre rear m-tyre-node {{ $t ? 'filled' : 'empty' }}"
                  data-position-id="{{ $p->id }}" data-code="{{ $p->position_code }}"
                  data-sn="{{ $t->serial_number ?? '' }}">
                  {{ $p->position_code }}
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
               data-code="{{ $s->position_code }}" data-sn="{{ $t->serial_number ?? '' }}">
               {{ $s->position_code }}
            </div>
         @endforeach
      </div>
   @endif
</div>

<style>
   /* Inlined style for Ajax partial */
   .v-chassis {
      position: relative;
      width: 100%;
      max-width: 320px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      padding: 25px 15px;
      border: 1px solid #eee;
   }

   .v-cabin {
      width: 80px;
      height: 40px;
      background: #eee;
      margin: 0 auto 20px auto;
      border-radius: 5px;
      border: 1px solid #ddd;
      text-align: center;
      line-height: 40px;
      font-size: 10px;
      color: #777;
      font-weight: bold;
   }

   .v-axle {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
      position: relative;
   }

   .v-axle::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 60%;
      height: 2px;
      background: #eee;
      z-index: 1;
   }

   .v-tyre {
      width: 28px;
      height: 48px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 4px;
      z-index: 2;
      color: #999;
      font-size: 8px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
   }

   .v-tyre.filled {
      background: #333 !important;
      border-color: #000;
      color: #fff;
   }

   .v-tyre.front {
      border-left: 3px solid #ff9f43;
   }

   .v-tyre.rear {
      border-left: 3px solid #28c76f;
   }

   .v-tyre.middle {
      border-left: 3px solid #7367f0;
      /* Use primary color for middle */
   }

   .v-tyre.spare {
      width: 48px;
      height: 28px;
      border-bottom: 3px solid #00cfe8;
      margin: 4px;
   }

   .v-group {
      display: flex;
      gap: 4px;
   }

   .v-spare-list {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 15px;
      border-top: 1px solid #eee;
      padding-top: 10px;
   }
</style>
