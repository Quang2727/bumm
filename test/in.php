<html>
<head>
    <title>Test Input App</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
    input{
        width: 300px;
        display: inline-block;
    }
    .sss{
        display: inline-block;
        width: 100px;
    }
    .full{
        width: 500px;
        height: 500px;
        position: absolute;
        top:20px;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
    }
    textarea{
        width: 100%;
        height: 80px;
    }
    </style>
</head>
<body>
    <center>
<form action="in.php" method="post">
    <textarea name="code" type="text"></textarea><br>
    <input type="submit" value ="Ok">
</form>
    </center>
<?php

if (!empty($_POST['code'])):
    $nhaptho = $_POST['code'];

    preg_match('/\((.*?)\)/', $nhaptho, $nhapmoi);
    $nhap = trim($nhapmoi['0']);

    $nhap = str_replace(')', '', $nhap);
    $nhap = str_replace(']', '', $nhap);
    $nhap = str_replace('( [', '', $nhap);
    $nhap2 = explode(' [', $nhap);
    $cuoi = array();
    $html = '';
    foreach ($nhap2 as $key => $value) {
        $tam = explode("=>", $value);
        $cuoi[trim($tam[0])] = trim($tam[1]);
        if (trim($tam[0]) != 'full_url') {
            $html.= trim($tam[0]) . '=' . trim($tam[1]) . '&';
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.224'.$cuoi['full_url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $html);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execCurl = curl_exec($ch);
    curl_close($ch);
    echo"----------------------Input-------------------<pre>";
    print_r($cuoi);
    echo "</pre>";
    echo '----------------------Out-------------------<br>';
    $xong = json_decode($execCurl);
    echo"<pre>";
    print_r($xong);
    echo "</pre>";
    echo '----------------------RAW-------------------<br>';
    echo $execCurl;
?>
<?php endif;?>

</body>
</html>