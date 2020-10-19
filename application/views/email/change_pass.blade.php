<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>

<body>
    <p>Hệ thống gửi tự đông!</p>
    <table>
        <tr>
            <th>
                Mã thiết bị
            </th>
            <th>
                Tên thiết bị
            </th>

            <th>
                Tài khoản
            </th>
            <th>
                Ngày thay đổi gần nhất
            </th>
            <th>
                Ngày hết hạn
            </th>
        </tr>
        @foreach($rows as $row)
        <tr>
            <td>{{$row['equipment_id']}}</td>
            <td>{{$row['equipment_name']}}</td>
            <td>{{$row['username']}}</td>
            <td>{{$row['change_date']}}</td>
            <td>{{$row['next_date']}}</td>
        </tr>
        @endforeach
    </table>
</body>

</html>