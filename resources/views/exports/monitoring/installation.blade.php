<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14pt;">VEHICLE INFORMATION</th>
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
            <td style="font-weight: bold;">Odometer Start</td>
            <td colspan="4">{{ $session->odometer_start }}</td>
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
        <tr>
            <td style="font-weight: bold;">Application</td>
            <td colspan="6">{{ $session->vehicle->application ?? '-' }}</td>
            <td></td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Load Capacity</td>
            <td colspan="6">{{ $session->vehicle->load_capacity ?? '-' }}</td>
            <td></td>
            <td colspan="4"></td>
        </tr>
        <tr></tr>
        <tr style="background-color: #f2f2f2;">
            <th style="font-weight: bold; border: 1px solid #000;">Brand</th>
            <th style="font-weight: bold; border: 1px solid #000;">Pattern</th>
            <th style="font-weight: bold; border: 1px solid #000;">Pos</th>
            <th style="font-weight: bold; border: 1px solid #000;">Serial Number</th>
            <th style="font-weight: bold; border: 1px solid #000;">Size</th>
            <th style="font-weight: bold; border: 1px solid #000;">Inf Press (Rcmd)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Inf Press (Actl)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Date Assembly</th>
            <th style="font-weight: bold; border: 1px solid #000;">Date Inspection</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 1</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 2</th>
            <th style="font-weight: bold; border: 1px solid #000;">RTD 3</th>
            <th style="font-weight: bold; border: 1px solid #000;">Avg RTD</th>
            <th style="font-weight: bold; border: 1px solid #000;">Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($installations as $inst)
        <tr>
            <td style="border: 1px solid #000;">{{ $inst->brand }}</td>
            <td style="border: 1px solid #000;">{{ $inst->pattern }}</td>
            <td style="border: 1px solid #000;">{{ $inst->position }}</td>
            <td style="border: 1px solid #000;">{{ $inst->serial_number }}</td>
            <td style="border: 1px solid #000;">{{ $inst->size }}</td>
            <td style="border: 1px solid #000;">{{ $inst->inf_press_recommended }}</td>
            <td style="border: 1px solid #000;">{{ $inst->inf_press_actual }}</td>
            <td style="border: 1px solid #000;">{{ $inst->install_date }}</td>
            <td style="border: 1px solid #000;">{{ $inst->install_date }}</td>
            <td style="border: 1px solid #000;">{{ $inst->rtd_1 }}</td>
            <td style="border: 1px solid #000;">{{ $inst->rtd_2 }}</td>
            <td style="border: 1px solid #000;">{{ $inst->rtd_3 }}</td>
            <td style="border: 1px solid #000;">{{ $inst->avg_rtd }}</td>
            <td style="border: 1px solid #000;">{{ $inst->notes }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
