<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14pt;">VEHICLE INFORMATION (INSPECTION #{{ $checkNumber }})</th>
            <th colspan="5" style="font-weight: bold; font-size: 14pt;">TYRE INFORMATION</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Fleet Name</td>
            <td colspan="6">{{ $session->vehicle->fleet_name }}</td>
            <td style="font-weight: bold;">Tyre Size</td>
            <td colspan="4">{{ $session->tyre_size }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Install Date</td>
            <td colspan="6">{{ $session->install_date }}</td>
            <td style="font-weight: bold;">Original Tread Depth</td>
            <td colspan="4">{{ $session->original_rtd }} mm</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Vehicle Number</td>
            <td colspan="6">{{ $session->vehicle->vehicle_number }}</td>
            <td style="font-weight: bold;">Odometer Check</td>
            <td colspan="4">{{ $checks[0]->odometer ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Driver Name</td>
            <td colspan="6">{{ $session->vehicle->driver_name }}</td>
            <td style="font-weight: bold;">Pattern</td>
            <td colspan="4">{{ $session->pattern ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Phone Number</td>
            <td colspan="6">{{ $session->vehicle->phone_number ?? '-' }}</td>
            <td style="font-weight: bold;">Retase (Psi)</td>
            <td colspan="4">{{ $session->retase ?? '-' }}</td>
        </tr>
        <tr></tr>
        <tr style="background-color: #f2f2f2;">
            <th style="font-weight: bold; border: 1px solid #000;">Pos</th>
            <th style="font-weight: bold; border: 1px solid #000;">Serial Number</th>
            <th style="font-weight: bold; border: 1px solid #000;">Inf Press (Rcmd)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Inf Press (Actl)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Inspection Date</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 1</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 2</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 3</th>
            <th style="font-weight: bold; border: 1px solid #000;">Avg RTD</th>
            <th style="font-weight: bold; border: 1px solid #000;">Op. Mileage (KM)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Worn%</th>
            <th style="font-weight: bold; border: 1px solid #000;">KM/mm</th>
            <th style="font-weight: bold; border: 1px solid #000;">Proj.Life 3mm (KM)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Day</th>
            <th style="font-weight: bold; border: 1px solid #000;">Moon</th>
            <th style="font-weight: bold; border: 1px solid #000;">Condition</th>
            <th style="font-weight: bold; border: 1px solid #000;">Recommendation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($checks as $check)
        <tr>
            <td style="border: 1px solid #000;">{{ $check->position }}</td>
            <td style="border: 1px solid #000;">{{ $check->serial_number }}</td>
            <td style="border: 1px solid #000;">{{ $check->inf_press_recommended }}</td>
            <td style="border: 1px solid #000;">{{ $check->inf_press_actual }}</td>
            <td style="border: 1px solid #000;">{{ $check->check_date }}</td>
            <td style="border: 1px solid #000;">{{ $check->rtd_1 }}</td>
            <td style="border: 1px solid #000;">{{ $check->rtd_2 }}</td>
            <td style="border: 1px solid #000;">{{ $check->rtd_3 }}</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['avg_rtd'] }}</td>
            <td style="border: 1px solid #000;">{{ $check->operation_mileage }}</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['worn_pct'] }}%</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['km_per_mm'] }}</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['proj_life_km'] }}</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['proj_life_day'] }}</td>
            <td style="border: 1px solid #000;">{{ $check->calculated['proj_life_month'] }}</td>
            <td style="border: 1px solid #000;">{{ strtoupper($check->condition) }}</td>
            <td style="border: 1px solid #000;">{{ $check->recommendation }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
