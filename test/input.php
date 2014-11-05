<html>
<head>
    <title>Test Input App</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript">
    // $(document).ready(function(){
    //     $("input").each(function(){
    //         $(this).attr("placeholder", $(this).attr('name'));
    //     });
    // });
    </script>
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
    </style>
</head>
<body>

<?php
if (!empty($_POST['code'])):
//     $nhaptho = '

// 2014-07-25 12:02:00 Debug: Array ( [limit] => 1 [photoShow] => 1 [callApi] => 1 [user_id] => 175 [lat] => 10.768915 [lng] => 106.703536 [dataSearch] => 1 [full_url] => /hc/Api/getListUser/13 )
// ';
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
            $html.='
            <div class="sss">' . trim($tam[0]) . '</div>
            <input type="text" name="' . trim($tam[0]) . '" value="' . trim($tam[1]) . '"><br>
            ';
        }
    }
?>
    <div class="full">
    <div class="sss">Action:</div>
    <input type="text" id="urlPost" value="http://192.168.1.224<?php echo $cuoi['full_url'];?>">
    <br><br>


        <form action="http://192.168.1.224<?php echo $cuoi['full_url'];?>" method="POST" id="FormIn">

            <?php echo $html;?>
    <br>
        <input type="submit" value="OK">
        </form>

        <?php echo"<pre>";
        print_r($cuoi);
        echo "</pre>";?>
    </div>

<?php endif;?>


</body>
</html>