<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laravel</title>
<!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
<!-- Styles -->
    <style>
    html, body {
        background-color: #fff;
        color: #636b6f;
        font-family: 'Raleway', sans-serif;
        font-weight: 100;
        height: 100vh;
        margin: 0;
    }
.full-height {
        height: 100vh;
    }
.flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }
.position-ref {
        position: relative;
    }
.top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }
.content {
        text-align: center;
    }
.title {
        font-size: 84px;
    }
.links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
.m-b-md {
        margin-bottom: 30px;
    }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
<div class="content">
            <h1>Authorize Payment Integration</h1>
<form class="" action="{{ url('/authorize-checkout') }}" method="post">
                {{ csrf_field() }}
<h3>Credit Card Information</h3>
                <div class="form-group">
                    <label for="cnumber">Card Number</label>
                    <input type="text" class="form-control" id="cnumber" name="cnumber" placeholder="Enter Card Number">
                </div>
                <div class="form-group">
                  <label for="card-expiry-month">Expiration Month</label>
                  <input type="text" required class="form-control" id="card_expiry_month" name="card_expiry_month" placeholder="Enter Expiration Month">
                </div>
                <div class="form-group">
                  <label for="card-expiry-year">Expiration Year</label>
                  <input type="text" required class="form-control" id="card_expiry_year" name="card_expiry_year" placeholder="Enter Expiration Year">
                </div>
                <div class="form-group">
                    <label for="ccode">Card Code</label>
                    <input type="text" class="form-control" id="ccode" name="ccode" placeholder="Enter Card Code">
                </div>
                <div class="form-group">
                    <label for="camount">Amount</label>
                    <input type="text" class="form-control" id="camount" name="camount" placeholder="Enter Amount" >
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
</form>
</div>
    </div>
</body>
</html>