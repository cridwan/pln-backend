<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .img {
            width: 150px;
            height: 30px;
        }

        .table-header td {
            text-align: center
        }

        .table-header {
            width: 100%;
        }

        .table-content {
            width: 100%;
             border-collapse: collapse;
             border: 1px solid black;
        }

        .table-content th,
        .table-content td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .table-content th {
            background-color: #A1E3F9;
        }

        .b-blue {
            background-color: #A1E3F9;
        }


    @page {
        margin: 20px;
    }

    body {
        margin: 0;
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        word-wrap: break-word;
    }
    </style>
</head>
<body style="width: 100%">
    <table class="table-header">
        <tr>
            <td rowspan="4">
                <img src="{{asset('logo.png')}}" alt="" class="img">
            </td>
            <td class="b-blue">PT. PLN INDONESIA POWER</td>
        </tr>
        <tr>
            <td>
                SUMMARY SCOPE STANDARD PEMELIHARAAN PERIODIK
            </td>
        </tr>
        <tr>
            <td>
                {{$inspection}}
            </td>
        </tr>
        <tr>
            <td>
                {{$machine}}
            </td>
        </tr>
    </table>

    <table class="table-content">
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                </tr>
            </tbody>
        </tbody>
    </table>
</body>
</html>
