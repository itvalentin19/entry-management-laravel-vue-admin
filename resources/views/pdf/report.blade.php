<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .footer {
            font-size: 0.8em;
        }
        .logo {
            width: 150px;
            height: auto;
        }
				.value {
					font-weight: 600;
				}
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logoBase64 }}" alt="EAKVA LOGO" class="logo">
        <h1>{{$firm_name ?? ''}}</h1>
    </div>

    <table>
        <tr>
            <td>Company Name<br><span class="value">{{$entity_name ?? ''}}</span></td>
            <td>Date of Incorporation<br>
						@if(isset($date_created))
						<span class="value">{{ $date_created ? date('m/d/Y', strtotime($date_created)) : '' }}</span>
						@endif
					</td>
            <td>EIN<br><span class="value">{{$ein_number ?? ''}}</span></td>
        </tr>
        <tr>
            <td>Company Address<br><span class="value">{{$address_1 ?? ''}}, {{$address_2 ?? ''}}</span></td>
            <td>DBA<br><span class="value">{{$ein_number ?? ''}}</span></td>
            <td></td>
        </tr>
    </table>

    <h2>Ownership</h2>
    <table>
        <tr>
            <th>First</th>
            <th>Last</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Percent of Ownership</th>
        </tr>
        {{-- Loop through your ownership data here --}}
				@if(isset($owners))
				@foreach ($owners as $owner)
				<tr>
						<td>{{ $owner->first_name }}</td>
						<td>{{ $owner->last_name }}</td>
						<td>{{ $owner->email }}</td>
						<td>{{ $owner->phone }}</td>
						<td>{{ $owner->address1 }}, {{ $owner->address2 }}</td>
						<td>{{ $owner->ownership_stake }}%</td>
				</tr>
				@endforeach
				@endif
    </table>

    <h2>Officers</h2>
    <table>
        <tr>
            <th>First</th>
            <th>Last</th>
            <th>Title</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
        </tr>
        {{-- Loop through your officers data here --}}
				@if(isset($officers))
				@foreach ($officers as $officer)
				<tr>
						<td>{{ $officer->first_name }}</td>
						<td>{{ $officer->last_name }}</td>
						<td>{{ $officer->title }}</td>
						<td>{{ $officer->email }}</td>
						<td>{{ $officer->phone }}</td>
						<td>{{ $officer->address1 }}, {{ $officer->address2 }}</td>
				</tr>
				@endforeach
				@endif
    </table>

    <h2>Jurisdiction</h2>
    <p>{{ $jurisdiction ?? '' }}</p>

    <div class="footer">
        <p>Confidential Report as DATE</p>
        <p>Report prepared by Eakav - All Rights Reserved</p>
        <p>Eakav LLC, 5645 Coral Ridge Drive Suite 410, Coral Springs FL 33076</p>
        <a href="https://www.eakav.com">www.eakav.com</a>
    </div>
</body>
</html>
