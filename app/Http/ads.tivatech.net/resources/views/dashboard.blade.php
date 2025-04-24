<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
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

<h2> THÔNG TIN NGƯỜI ĐĂNG NHẬP</h2>

<table>
  <tr>
    <th>HỌ VÀ TÊN</th>
    <th>EMAIL</th>
    <th>GOOGLE ID</th>
  </tr>
  <tr>
    <td>{{ Auth::user()->name }}</td>
    <td>{{ Auth::user()->email }}</td>
    <td>{{ Auth::user()->google_id }}</td>
  </tr>

</table>

</body>
</html>

