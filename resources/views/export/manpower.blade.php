<table>
    <tr>
        <td>Nama Proyek</td>
        <td>Major Inspection</td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>Nama</th>
            <th>VOL</th>
            <th>Satuan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->type }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
