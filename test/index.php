<!-- <meta http-equiv="refresh"meta http-equiv="refresh" content="1;URL=index.php";> -->
<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script type="text/javascript">
    $(window).load(function() {
        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
    });
    </script>

-->


<style type="text/css">

</style>
<a href="xoa.php">Xoa het</a><br>
<?php
$log = @file_get_contents('../app/tmp/logs/debug.log');
$newlog = explode("\n", $log);

foreach ($newlog as $k => $v) {
    if (!empty($v)) {
        // echo $v;
        // preg_match('/\((.*)\)/', $v, $nhapmoi);
        // $nhap = trim($nhapmoi['0']);
        // $nhap = str_replace(')', '', $nhap);
        // $nhap = str_replace(']', '', $nhap);
        // $nhap = str_replace('( [', '', $nhap);
        // $nhap2 = explode(' [', $nhap);
        // $cuoi = array();
        // $html = '';
        // foreach ($nhap2 as $key => $value) {
        //     $tam = explode("=>", $value);
        //     $cuoi[trim($tam[0])] = trim($tam[1]);
        //     if (trim($tam[0]) != 'full_url') {
        //         $html.='
        //         '. trim($tam[0]) . ' => ' . trim($tam[1]) . '
        //         ';
        //     }

        // }
        echo "-------------------------------------------<br>";
        echo $v."<br>";
    }
}

    // preg_match('/\((.*?)\)/', $nhaptho, $nhapmoi);
    // $nhap = trim($nhapmoi['0']);

    // $nhap = str_replace(')', '', $nhap);
    // $nhap = str_replace(']', '', $nhap);
    // $nhap = str_replace('( [', '', $nhap);
    // $nhap2 = explode(' [', $nhap);
    // $cuoi = array();
    // $html = '';
    // foreach ($nhap2 as $key => $value) {
    //     $tam = explode("=>", $value);
    //     $cuoi[trim($tam[0])] = trim($tam[1]);
    //     if (trim($tam[0]) != 'full_url') {
    //         $html.='
    //         <div class="sss">' . trim($tam[0]) . '</div>
    //         <input type="text" name="' . trim($tam[0]) . '" value="' . trim($tam[1]) . '"><br>
    //         ';
    //     }
    // }




echo"<pre>";
// var_dump($ga);
echo"</pre>";
?>