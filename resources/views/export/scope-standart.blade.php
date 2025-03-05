<table>
    <tr>
        <td>Nama Proyek</td>
        <td>Major Inspection</td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th>Scope</th>
            <th>Detail</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->name }}</td>
                @foreach ($item->details as $detail)
                    <td>{{ $detail->name }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
