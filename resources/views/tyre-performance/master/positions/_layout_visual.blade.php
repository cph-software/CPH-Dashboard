<style>
   .v-chassis {
      position: relative;
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
      background: #fff;
      border-radius: 15px;
      padding: 30px 15px;
      border: 1px solid #e0e0e0;
   }

   .v-cabin {
      width: 100px;
      height: 50px;
      background: #f1f1f1;
      margin: 0 auto 20px auto;
      border-radius: 8px 8px 3px 3px;
      border: 2px solid #ddd;
      text-align: center;
      line-height: 50px;
      font-size: 10px;
      color: #999;
      font-weight: bold;
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
      width: 70%;
      height: 4px;
      background: #eee;
      z-index: 1;
   }

   .v-tyre {
      width: 30px;
      height: 55px;
      background: #333;
      border-radius: 5px;
      z-index: 2;
      color: white;
      font-size: 8px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
   }

   .v-tyre.front {
      border-left: 3px solid #ff9f43;
   }

   .v-tyre.rear {
      border-left: 3px solid #28c76f;
   }

   .v-tyre.spare {
      width: 55px;
      height: 30px;
      border-bottom: 3px solid #00cfe8;
      margin: 5px;
   }

   .v-group {
      display: flex;
      gap: 5px;
   }

   .v-spare-list {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 20px;
      border-top: 1px solid #eee;
      padding-top: 10px;
   }
</style>

<div class="v-chassis">
   <div class="v-cabin">FRONT</div>

   @php
      $frontAxles = $configuration->details->where('axle_type', 'Front')->groupBy('axle_number');
      $rearAxles = $configuration->details->where('axle_type', 'Rear')->groupBy('axle_number');
      $spares = $configuration->details->where('is_spare', true);
   @endphp

   @foreach ($frontAxles as $positions)
      <div class="v-axle">
         <div class="v-tyre front">{{ $positions->where('side', 'Left')->first()->position_code ?? '' }}</div>
         <div class="v-tyre front">{{ $positions->where('side', 'Right')->first()->position_code ?? '' }}</div>
      </div>
   @endforeach

   @foreach ($rearAxles as $positions)
      <div class="v-axle">
         <div class="v-group">
            @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
               <div class="v-tyre rear">{{ $p->position_code }}</div>
            @endforeach
         </div>
         <div class="v-group">
            @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
               <div class="v-tyre rear">{{ $p->position_code }}</div>
            @endforeach
         </div>
      </div>
   @endforeach

   @if ($spares->count() > 0)
      <div class="v-spare-list">
         @foreach ($spares as $s)
            <div class="v-tyre spare">{{ $s->position_code }}</div>
         @endforeach
      </div>
   @endif
</div>
