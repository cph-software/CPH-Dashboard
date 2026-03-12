<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14pt;">REMOVAL SUMMARY</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Fleet Name</td>
            <td colspan="6">{{ $session->vehicle->fleet_name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Vehicle Number</td>
            <td colspan="6">{{ $session->vehicle->vehicle_number }}</td>
        </tr>
        <tr></tr>
        <tr style="background-color: #f2f2f2;">
            <th style="font-weight: bold; border: 1px solid #000;">Serial Number</th>
            <th style="font-weight: bold; border: 1px solid #000;">Removal Date</th>
            <th style="font-weight: bold; border: 1px solid #000;">Position</th>
            <th style="font-weight: bold; border: 1px solid #000;">Total Mileage (KM)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Final RTD (mm)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Removal Reason</th>
            <th style="font-weight: bold; border: 1px solid #000;">Condition After</th>
            <th style="font-weight: bold; border: 1px solid #000;">Notes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #000;">{{ $removal->serial_number }}</td>
            <td style="border: 1px solid #000;">{{ $removal->removal_date }}</td>
            <td style="border: 1px solid #000;">{{ $removal->position }}</td>
            <td style="border: 1px solid #000;">{{ $removal->total_mileage }}</td>
            <td style="border: 1px solid #000;">{{ $removal->final_rtd }}</td>
            <td style="border: 1px solid #000;">{{ $removal->removal_reason }}</td>
            <td style="border: 1px solid #000;">{{ $removal->tyre_condition_after }}</td>
            <td style="border: 1px solid #000;">{{ $removal->notes }}</td>
        </tr>
    </tbody>
</table>
