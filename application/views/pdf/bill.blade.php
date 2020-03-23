<!DOCTYPE html>
<html>
    <!-- <html lang="ar"> for arabic only -->
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>Hóa đơn</title>
        <style>
            @media print {
                @page {
                    margin: 0 auto;
                    sheet-size: 300px 250mm;
                }
                html {
                    direction: rtl;
                }
                html,body{margin:0;padding:0}
                #printContainer {
                    width: 250px;
                    margin: auto;
                    /*padding: 10px;*/
                    /*border: 2px dotted #000;*/
                    text-align: justify;
                }

                /*span {
                    display: inline-block;
                    min-width: 350px;
                    white-space: nowrap;
                    background: red;
                }*/
                .text-center{text-align: center;}
            }
        </style>
    </head>
    <body onload="window.print();">
        <!--<h1 id="logo" class="text-center"><img src='<?= base_url() ?>global/site/logo.jpg' alt='Logo'></h1>-->

        <div id='printContainer'>
            <hr>
            <h2 id="slogan" style="margin-top:20px" class="text-center">Hóa Đơn </h2>

            <table>
                <tr>
                    <td>Số hóa đơn</td>
                    <td><b>#{{$cart->id}}</b></td>
                </tr>
                <tr>
                    <td>Ngày</td>
                    <td><b>{{$cart->create_at}}<br></b></td>
                </tr>
                <tr>
                    <td>Nhân viên</td>
                    <td><b>{{$cart->user_name}}<br></b></td>
                </tr>
            </table>
            <!--<p class="text-center"><img src="<?= base_url() ?>global/site/qr.png" alt="QR-code" class="left"/></p>-->
            <hr>

            <table width="100%">
                <tr>
                    <td><b>Tên</b></td>
                    <td><b>Số lượng</b></td>
                    <td><b>Giá</b></td>
                </tr>
                <tr><td colspan="3"><hr></td></tr>
                @foreach($cart->details as $key=>$row)
                <tr>
                    <td>- {{$row->name}}</td>
                    <td>{{$row->quantity}}</td>
                    <td>{{number_format($row->amount,0,",",".")}}</td>
                </tr>
                @endforeach
            </table>
            <hr>

            <table width="100%">
                <tr>
                    <td><b>Tổng tiền</b></td>
                    <td colspan="2" class="text-center">{{number_format($cart->total_amount,0,",",".")}} VND</td></td>
                </tr>
            </table>
            <hr>

        </div>
    </body>
</html>
