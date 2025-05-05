<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Daily Report</title>
</head>
<style>
    table {
        width: 100%;

    }
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #7d0630;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
        width: max-content;
    }
    tr:hover {
        background-color: #ddd;
    }
</style>
<body>
    <h2>Bellicks Finance Daily Report - {{ date('Y/m/d') }} KATORO BRANCH</h2>
    <h3>Customers who didn't paid</h3>
    <table >
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone number</th>
                <th>Loan amount</th>
                <th>Outstanding balance</th>
                <th>Paid amount</th>
                <th>Paid days</th>
                <th>Up to date</th>


            </tr>
        </thead>
        <tbody>
            @foreach($not_paid as $item)
                <tr>
                    <td>{{ $item['customer_name'] }}</td>
                    <td>{{ $item['customer_phone'] }}</td>
                    <td>{{ number_format($item['loan_amount']) }}</td>
                    <td>{{ number_format($item['paid_amount']) }}</td>
                    <td>{{ number_format($item['outstanding_balance']) }}</td></td>
                    <td>{{ $item['number_of_days_paid'] }}</td>
                    <td>{{ $item['paid_up_to'] }}</td>

                </tr>

            @endforeach
        </tbody>
    </table>
    <hr>
    <h3>Customers who paid</h3>
    <table >
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone number</th>
                <th>Loan amount</th>
                <th>Outstanding balance</th>
                <th>Paid amount</th>
                <th>Paid days</th>
                <th>Up to date</th>


            </tr>
        </thead>
        <tbody>
            @foreach($paid_more as $item)
                <tr>
                    <td>{{ $item['customer_name'] }}</td>
                    <td>{{ $item['customer_phone'] }}</td>
                    <td>{{ number_format($item['loan_amount']) }}</td>
                    <td>{{ number_format($item['paid_amount']) }}</td>
                    <td>{{ number_format($item['outstanding_balance']) }}</td></td>
                    <td>{{ $item['number_of_days_paid'] }}</td>
                    <td>{{ $item['paid_up_to'] }}</td>

                </tr>

            @endforeach
        </tbody>
    </table>

    <hr>
    <h3>Customers who paid less than expected of day </h3>
    <table >
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone number</th>
                <th>Loan amount</th>
                <th>Outstanding balance</th>
                <th>Paid amount</th>
                <th>Paid days</th>
                <th>Up to date</th>


            </tr>
        </thead>
        <tbody>
            @foreach($paid_less as $item)
                <tr>
                    <td>{{ $item['customer_name'] }}</td>
                    <td>{{ $item['customer_phone'] }}</td>
                    <td>{{ number_format($item['loan_amount']) }}</td>
                    <td>{{ number_format($item['paid_amount']) }}</td>
                    <td>{{ number_format($item['outstanding_balance']) }}</td></td>
                    <td>{{ $item['number_of_days_paid'] }}</td>
                    <td>{{ $item['paid_up_to'] }}</td>

                </tr>

            @endforeach
        </tbody>
    </table>
</body>
</html>
