<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Surat Izin - PDF</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
             !important
        }


        .text-center {
            text-align: center;
        }

        #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #212529;
            color: white;
        }
    </style>
</head>

<body>
    <h2 class="text-center">SURAT IZIN</h2>
    <hr>
    {{-- <p style="font-size: 12px; text-align: right">Tanggal: {{
        Carbon\Carbon::parse($str_date)->translatedFormat('d-m-Y')}} s/d {{
        Carbon\Carbon::parse($n_date)->translatedFormat('d-m-Y')}}</p> --}}
    <table id="customers" style="font-size: 12px; margin-bottom: 30px">
        <thead>
            <tr>
                <th style="text-align: center">No.</th>
                <th style="text-align: center">Nama User</th>
                <th style="text-align: center">Perusahaan</th>
                <th style="text-align: center">Keperluan Izin</th>
                <th style="text-align: center">Tgl. Izin</th>
                <th style="text-align: center">Sampai Tgl. Izin</th>
                <th style="text-align: center">Lama Izin</th>
                <th style="text-align: center">Jam Izin</th>
                <th style="text-align: center">Sampai Jam</th>
                <th style="text-align: center">Durasi</th>
                <th style="text-align: center">Keterangan</th>
                <th style="text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>
                    {{$loop->iteration }}
                </td>
                <td>{{ $record['suratIzin']['user']['first_name']."
                    ".$record['suratIzin']['user']['last_name'] }}</td>
                <td>{{ $record['suratIzin']['user']['company']['slug'] }}</td>
                <td>{{ $record['suratIzin']['keperluan_izin'] }}</td>
                <td>{{
                    Carbon\Carbon::parse($record['suratIzin']['tanggal_izin'])->translatedFormat('d/m/Y') ?? '-'}}
                </td>
                <td>{{
                    Carbon\Carbon::parse($record['suratIzin']['sampai_tanggal'])->translatedFormat('d/m/Y') ?? '-'}}
                </td>
                <td>{{ $record['suratIzin']['lama_izin'] }}</td>
                <td>{{
                    Carbon\Carbon::parse($record['suratIzin']['jam_izin'])->translatedFormat('H:i') ?? '-'}}
                </td>
                <td>{{
                    Carbon\Carbon::parse($record['suratIzin']['sampai_jam'])->translatedFormat('H:i') ?? '-'}}
                </td>
                <td>{{ $record['suratIzin']['durasi_izin'] }}</td>
                <td>{{ $record['suratIzin']['keterangan_izin'] }}</td>
                <td>{{ $record['status'] == 0 ? 'Processing' : ($record['status'] == 1 ? 'Approved' : 'Rejected') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="font-size: 12px; margin-top: -20px">
        {{-- <p><span style="margin-right: 53px">Data</span> : Total data {{$record->count()}}, jumlah data approve
            {{$record->where('status', 1)->count()}}, jumlah data reject {{$record->where('status',
            2)->count()}}, jumlah data proccessing {{$record->where('status',
            0)->count()}}
        </p> --}}
        <p><span style="margin-right: 15px; margin-top: -10px">Didownload</span> : {{
            Carbon\Carbon::now()->translatedFormat('d/m/Y, H:i')
            }}</p>
    </div>
</body>

</html>