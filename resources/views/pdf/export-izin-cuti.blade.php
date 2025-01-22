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
    <h2 class="text-center">IZIN CUTI</h2>
    <hr>
    {{-- <p style="font-size: 12px; text-align: right">Tanggal: {{
        Carbon\Carbon::parse($records->where('izinCutiApprove', '!=', null)
        ->sortBy('izinCutiApprove.mulai_cuti')
        ->first()->izinCutiApprove->mulai_cuti ?? now())
        ->translatedFormat('d-m-Y')
        }} s/d {{
        Carbon\Carbon::parse($records->where('izinCutiApprove', '!=', null)
        ->sortByDesc('izinCutiApprove.mulai_cuti')
        ->first()->izinCutiApprove->mulai_cuti)
        ->translatedFormat('d-m-Y')
        }}</p> --}}
    <table id="customers" style="font-size: 12px; margin-bottom: 30px">
        <thead>
            <tr>
                <th style="text-align: center">No</th>
                <th style="text-align: center">Nama</th>
                <th style="text-align: center">Perusahaan</th>
                <th style="text-align: center">Keperluan Cuti</th>
                <th style="text-align: center">Pilihan</th>
                <th style="text-align: center">Dari Tanggal</th>
                <th style="text-align: center">Sampai Tanggal</th>
                <th style="text-align: center">Lama Cuti</th>
                <th style="text-align: center">Keterangan</th>
                <th style="text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if ($records->count() == 0)
            <tr>
                <td colspan="8" class="text-center">Tidak ada data.</td>
            </tr>
            @else
            @foreach ($records as $record)
            <tr @if($record['status']==2) style="color: red" @endif>
                <td>
                    {{$loop->iteration }}
                </td>
                <td>
                    {{ $record['izinCutiApprove']['userCuti']['first_name']."
                    ".$record['izinCutiApprove']['userCuti']['last_name'] }}
                </td>
                <td>{{ $record['izinCutiApprove']['userCuti']['company']['slug'] }}</td>
                <td>{{ $record['izinCutiApprove']['keterangan_cuti'] }}</td>
                <td>
                    @if($record['izinCutiApprove']['pilihan_cuti'] != NULL)
                    {{$record['izinCutiApprove']['pilihan_cuti']}}
                    @else
                    -
                    @endif
                </td>
                {{-- <td>{{ date('l', strtotime($record->tanggal_izin))}}</td> --}}
                <td>{{ Carbon\Carbon::parse($record['izinCutiApprove']['mulai_cuti'])->translatedFormat('d/m/Y')}}</td>
                <td>{{ Carbon\Carbon::parse($record['izinCutiApprove']['sampai_cuti'])->translatedFormat('d/m/Y')}}</td>
                <td>{{ $record['izinCutiApprove']['lama_cuti'] }}</td>
                <td>{{$record['izinCutiApprove']['pesan_cuti']}}</td>
                <td>{{ $record['status'] == 0 ? 'Processing' : ($record['status'] == 1 ? 'Approved' : 'Rejected') }}
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
    <div style="font-size: 12px; margin-top: -20px">
        <p><span style="margin-right: 37px">Catatan</span> : Jika terdapat warna<span style="color: red"> merah</span>,
            artinya tidak disetujui </p>
        <p><span style="margin-right: 53px">Data</span> : Total data {{$records->count()}}, jumlah data approve
            {{$records->where('status', 1)->count()}}, jumlah data reject {{$records->where('status', 2)->count()}},
            jumlah data proccessing {{$records->where('status', 0)->count()}}
        </p>
        <p><span style="margin-right: 15px; margin-top: -10px">Didownload</span> : {{
            Carbon\Carbon::now()->translatedFormat('d-m-Y, H:i')
            }}</p>
    </div>
</body>

</html>